#!/bin/bash
#
# サーバー上のすべての WordPress を見回り、前回からの差分を通知する（cron で5分ごとに実行）。
#
#   - プラグインの追加・削除（plugins / mu-plugins / wp-content 直下のドロップイン / 有効化状態）
#   - ユーザーの追加・削除・権限やメールアドレスの変更
#   - ~/.ssh/authorized_keys の変更（SSH で入れる鍵の追加）
#
# 使い方:
#   bash wp-watch.sh          … 見回り（初回は現在の状態を記録するだけで通知しません）
#   bash wp-watch.sh --test   … 通知のテスト送信
#   bash wp-watch.sh --show   … 現在の状態を表示（記録・通知はしません）
#
# 設定は同じディレクトリの wp-watch.conf（wp-watch.conf.example をコピー）に書きます。

set -u -o pipefail
umask 077
export LC_ALL=C

SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

# ---- 設定の初期値（wp-watch.conf で上書き） ----
SEARCH_ROOTS="$HOME"
MAX_DEPTH=5
STATE_DIR="$HOME/.wp-watch"
PHP_BIN="php"
MAIL_TO=""
MAIL_FROM=""
SENDMAIL="/usr/sbin/sendmail"
SLACK_WEBHOOK_URL=""
DISCORD_WEBHOOK_URL=""
WATCH_SSH_KEYS=1
WATCH_OS_USERS=0

CONF="${WP_WATCH_CONF:-$SCRIPT_DIR/wp-watch.conf}"
# shellcheck source=/dev/null
[ -f "$CONF" ] && . "$CONF"

HOST_LABEL="${HOSTNAME:-$(uname -n)}"
SNAP_DIR="$STATE_DIR/snapshots"
mkdir -p "$SNAP_DIR" || exit 1
chmod 700 "$STATE_DIR"

log() {
	printf '%s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" >>"$STATE_DIR/wp-watch.log"
}

# ---------------------------------------------------------------- 通知

json_payload() { # $1 キー名 $2 本文
	"$PHP_BIN" -r 'echo json_encode(array($argv[1] => $argv[2]), defined("JSON_INVALID_UTF8_SUBSTITUTE") ? JSON_INVALID_UTF8_SUBSTITUTE : 0);' -- "$1" "$2"
}

truncate_text() { # $1 本文 $2 最大文字数（マルチバイト文字の途中で切らない）
	"$PHP_BIN" -r 'echo preg_match("/^.{0," . (int) $argv[2] . "}/su", $argv[1], $m) ? $m[0] : substr($argv[1], 0, (int) $argv[2]);' -- "$1" "$2"
}

post_webhook() { # $1 URL $2 JSON
	printf '%s' "$2" | curl -sS -f -m 20 -X POST -H 'Content-Type: application/json' --data-binary @- "$1" >/dev/null
}

send_mail() { # $1 件名 $2 本文
	local subject
	subject="=?UTF-8?B?$(printf '%s' "$1" | base64 | tr -d '\n')?="
	{
		printf 'To: %s\n' "$MAIL_TO"
		[ -n "$MAIL_FROM" ] && printf 'From: %s\n' "$MAIL_FROM"
		printf 'Subject: %s\n' "$subject"
		printf 'MIME-Version: 1.0\nContent-Type: text/plain; charset=UTF-8\nContent-Transfer-Encoding: 8bit\n\n'
		printf '%s\n' "$2"
	} | "$SENDMAIL" -t -i
}

# 設定されたすべての通知先に送る。1つでも届けば成功。通知先が未設定なら標準出力のみ（cron のメール通知で届く）
notify() { # $1 件名 $2 本文
	local channels=0 sent=0
	printf '%s\n\n%s\n' "$1" "$2"
	if [ -n "$MAIL_TO" ]; then
		channels=$((channels + 1))
		send_mail "$1" "$2" && sent=1 || log "メール送信に失敗しました"
	fi
	if [ -n "$SLACK_WEBHOOK_URL" ]; then
		channels=$((channels + 1))
		post_webhook "$SLACK_WEBHOOK_URL" "$(json_payload text "*$1*"$'\n```\n'"$2"$'\n```')" && sent=1 || log "Slack 送信に失敗しました"
	fi
	if [ -n "$DISCORD_WEBHOOK_URL" ]; then
		channels=$((channels + 1))
		# Discord は1メッセージ2000文字まで
		post_webhook "$DISCORD_WEBHOOK_URL" "$(json_payload content "**$1**"$'\n```\n'"$(truncate_text "$2" 1800)"$'\n```')" && sent=1 || log "Discord 送信に失敗しました"
	fi
	[ "$channels" -eq 0 ] || [ "$sent" -eq 1 ]
}

# ---------------------------------------------------------------- 状態の取得

# wp-settings.php があるディレクトリ = WordPress のインストール先
find_sites() {
	local root
	for root in $SEARCH_ROOTS; do
		[ -d "$root" ] || continue
		find "$root" -maxdepth "$MAX_DEPTH" -type f -name wp-settings.php \
			-not -path '*/wp-content/*' -not -path "$STATE_DIR/*" 2>/dev/null
	done | sed 's#/wp-settings\.php$##' | sort -u
}

snapshot_ssh_keys() {
	local file line
	for file in "$HOME/.ssh/authorized_keys" "$HOME/.ssh/authorized_keys2"; do
		[ -f "$file" ] || continue
		while IFS= read -r line || [ -n "$line" ]; do
			case "$line" in '' | '#'*) continue ;; esac
			# 鍵本体は載せず、識別用のハッシュとコメント（末尾）だけ
			printf 'ssh_key\t%s\t%s\t%s\n' "${file##*/}" "$(printf '%s' "$line" | sha256sum | cut -c1-16)" "${line##* }"
		done <"$file"
	done
}

snapshot_os_users() {
	awk -F: '{ printf "os_user\t%s\tuid=%s\t%s\n", $1, $3, $7 }' /etc/passwd
}

# ---------------------------------------------------------------- 比較

REPORT=""
PENDING=()   # 通知が届いてから確定する "新しい状態ファイル 保存先" の組

label_of() {
	case "$1" in
	plugin) echo "プラグイン" ;;
	mu-plugin) echo "mu-plugin（管理画面に出ない常時有効プラグイン）" ;;
	dropin) echo "wp-content 直下の PHP（ドロップイン）" ;;
	active_plugin) echo "有効化プラグイン" ;;
	user) echo "ユーザー" ;;
	site) echo "WordPress のインストール" ;;
	ssh_key) echo "SSH 公開鍵" ;;
	os_user) echo "OS ユーザー" ;;
	*) echo "$1" ;;
	esac
}

format_lines() { # $1 記号（+ / -） 標準入力: 状態の行
	local sign=$1 kind rest mark
	while IFS=$'\t' read -r kind rest; do
		mark=""
		if [ "$kind" = user ] && [ "$sign" = "+" ] && printf '%s' "$rest" | grep -q 'administrator'; then
			mark="  ⚠ 管理者"
		fi
		printf '  %s %s: %s%s\n' "$sign" "$(label_of "$kind")" "${rest//$'\t'/ / }" "$mark"
	done
}

# 新しい状態と前回の状態を比べ、差分を REPORT に追記する
compare() { # $1 見出し $2 新しい状態ファイル $3 保存先
	local title=$1 new=$2 old=$3 added removed
	if [ ! -f "$old" ]; then
		mv "$new" "$old"
		log "初回の状態を記録しました: $title"
		return
	fi
	added=$(comm -13 "$old" "$new")
	removed=$(comm -23 "$old" "$new")
	if [ -n "$added$removed" ]; then
		REPORT+="■ $title"$'\n'
		[ -n "$added" ] && REPORT+=$(printf '%s\n' "$added" | format_lines "+")$'\n'
		[ -n "$removed" ] && REPORT+=$(printf '%s\n' "$removed" | format_lines "-")$'\n'
		REPORT+=$'\n'
		{
			printf '%s\n' "$title"
			[ -n "$added" ] && printf '%s\n' "$added" | sed 's/^/+ /'
			[ -n "$removed" ] && printf '%s\n' "$removed" | sed 's/^/- /'
		} >>"$STATE_DIR/changes.log"
		PENDING+=("$new" "$old")
	else
		rm -f "$new"
	fi
}

key_of() {
	printf '%s' "$1" | sha256sum | cut -c1-16
}

# ---------------------------------------------------------------- メイン

case "${1:-}" in
--test)
	notify "[wp-watch] テスト通知 ($HOST_LABEL)" "wp-watch からのテスト通知です。この通知が届けば設定は完了です。" && echo "送信しました" || { echo "送信に失敗しました（$STATE_DIR/wp-watch.log を確認）" >&2; exit 1; }
	exit 0
	;;
--show)
	find_sites | while IFS= read -r site; do
		echo "== $site"
		"$PHP_BIN" "$SCRIPT_DIR/wp-snapshot.php" "$site" | sort
	done
	[ "$WATCH_SSH_KEYS" = 1 ] && echo "== SSH" && snapshot_ssh_keys
	exit 0
	;;
'') ;;
*)
	echo "使い方: $0 [--test|--show]" >&2
	exit 2
	;;
esac

# 前回の実行が終わっていなければ何もしない
if command -v flock >/dev/null 2>&1; then
	exec 9>"$STATE_DIR/lock"
	flock -n 9 || exit 0
fi

TMP=$(mktemp -d "$STATE_DIR/tmp.XXXXXX") || exit 1
trap 'rm -rf "$TMP"' EXIT

SITES=$(find_sites)

# WordPress 自体が増えた・消えた
printf '%s\n' "$SITES" | sed '/^$/d; s/^/site\t/' | sort >"$TMP/sites"
compare "サーバー全体" "$TMP/sites" "$SNAP_DIR/sites.txt"

while IFS= read -r site; do
	[ -n "$site" ] || continue
	key=$(key_of "$site")
	if "$PHP_BIN" "$SCRIPT_DIR/wp-snapshot.php" "$site" 2>"$TMP/err" | sort >"$TMP/$key"; then
		rm -f "$SNAP_DIR/$key.error"
		compare "$site" "$TMP/$key" "$SNAP_DIR/$key.txt"
	else
		# 取得に失敗したら前回の状態は残し、失敗は1回だけ知らせる
		log "取得に失敗しました: $site: $(cat "$TMP/err")"
		if [ ! -f "$SNAP_DIR/$key.error" ]; then
			REPORT+="■ $site"$'\n'"  ! 状態を取得できませんでした: $(cat "$TMP/err")"$'\n\n'
			PENDING+=("$TMP/err" "$SNAP_DIR/$key.error")
		fi
	fi
done <<<"$SITES"

if [ "$WATCH_SSH_KEYS" = 1 ]; then
	snapshot_ssh_keys | sort >"$TMP/ssh"
	compare "SSH ($HOME/.ssh)" "$TMP/ssh" "$SNAP_DIR/ssh_keys.txt"
fi

if [ "$WATCH_OS_USERS" = 1 ]; then
	snapshot_os_users | sort >"$TMP/os"
	compare "OS ユーザー (/etc/passwd)" "$TMP/os" "$SNAP_DIR/os_users.txt"
fi

[ -n "$REPORT" ] || exit 0

BODY="$HOST_LABEL で前回の確認から次の変化がありました。
心当たりがない場合は、すぐに該当のプラグイン・ユーザー・鍵を確認してください。

${REPORT}詳細: $STATE_DIR/changes.log"

if notify "[wp-watch] 要確認: プラグイン/ユーザーの変化 ($HOST_LABEL)" "$BODY"; then
	# 通知が届いたら今回の状態を「前回」として確定する（届かなければ次回もう一度知らせる）
	for ((i = 0; i < ${#PENDING[@]}; i += 2)); do
		mv "${PENDING[i]}" "${PENDING[i + 1]}"
	done
	log "通知しました"
else
	log "どの通知先にも送れませんでした。次回もう一度通知します"
	exit 1
fi
