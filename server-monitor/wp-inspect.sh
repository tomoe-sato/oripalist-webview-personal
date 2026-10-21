#!/bin/bash
#
# すでに侵入されていないかを1回だけ点検する（読み取りのみ。サーバー上のファイルは変更しません）。
# wp-watch.sh の初回記録の前に実行し、結果に見覚えのないものがないか確認します。
#
# 使い方: bash wp-inspect.sh [日数]   … 日数: 「最近変更されたファイル」として見る期間（初期値 14）
#
# 結果にはユーザーのメールアドレスが含まれます。公開の場所には貼らないでください。

set -u
export LC_ALL=C

DAYS="${1:-14}"
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
SNAPSHOT="$SCRIPT_DIR/wp-snapshot.php"
LIMIT=150   # 1項目あたりの表示件数の上限

section() {
	printf '\n========== %s ==========\n' "$1"
}

limited() { # 標準入力を LIMIT 行までに切る
	awk -v max="$LIMIT" 'NR <= max { print } END { if (NR > max) printf "  …ほか %d 件\n", NR - max; if (NR == 0) print "  （なし）" }'
}

# PHP 7.0 以上で mysqli が使える php を選ぶ
pick_php() {
	local bin
	for bin in /usr/bin/php8.5 /usr/bin/php8.4 /usr/bin/php8.3 /usr/bin/php8.2 /usr/bin/php8.1 /usr/bin/php8.0 /usr/bin/php7.4 "$(command -v php)"; do
		[ -x "$bin" ] || continue
		"$bin" -r 'exit(version_compare(PHP_VERSION, "7.0.0", ">=") && extension_loaded("mysqli") ? 0 : 1);' 2>/dev/null && { echo "$bin"; return; }
	done
}

PHP_BIN=$(pick_php)

section "点検日時・環境"
echo "日時: $(date '+%Y-%m-%d %H:%M:%S %Z')"
echo "最近の変更として見る期間: ${DAYS} 日"
echo "使える PHP:"
for bin in /usr/bin/php /usr/bin/php[0-9]*; do
	[ -x "$bin" ] && printf '  %s → %s\n' "$bin" "$("$bin" -r 'echo PHP_VERSION;' 2>/dev/null)"
done
echo "点検に使う PHP: ${PHP_BIN:-（見つかりません。DB の点検は省略）}"
echo "wp-cli: $(command -v wp || echo なし)"

SITES=$(find "$HOME" -maxdepth 5 -type f -name wp-settings.php -not -path '*/wp-content/*' 2>/dev/null | sed 's#/wp-settings\.php$##' | sort)

section "WordPress の一覧"
printf '%s\n' "$SITES" | sed '/^$/d; s/^/  /' | limited

while IFS= read -r site; do
	[ -n "$site" ] || continue
	section "サイト: $site"

	version=$(sed -n "s/^\$wp_version = '\(.*\)';/\1/p" "$site/wp-includes/version.php" 2>/dev/null)
	echo "WordPress のバージョン: ${version:-不明}"

	echo
	echo "--- PHP バージョンの指定（.htaccess） ---"
	grep -iE 'x-httpd-php|AddHandler|SetHandler|php[0-9]' "$site/.htaccess" 2>/dev/null | sed 's/^/  /' | limited

	echo
	echo "--- プラグイン・ユーザー（DB を直接確認） ---"
	if [ -n "$PHP_BIN" ]; then
		if [ -f "$SNAPSHOT" ]; then
			"$PHP_BIN" "$SNAPSHOT" "$site" --detail 2>&1 | sort | sed 's/\t/ | /g; s/^/  /'
		else
			echo "  wp-snapshot.php が見つかりません（wp-inspect.sh と同じ場所に置いてください）"
		fi
	fi

	if command -v wp >/dev/null 2>&1; then
		echo
		echo "--- WordPress 本体の改ざんチェック（公式のファイルと比較） ---"
		WP_CLI_PHP="${PHP_BIN:-php}" timeout 180 wp --path="$site" core verify-checksums --skip-plugins --skip-themes 2>&1 | sed 's/^/  /' | limited
		echo
		echo "--- プラグインの改ざんチェック（公式ディレクトリにあるプラグインのみ） ---"
		WP_CLI_PHP="${PHP_BIN:-php}" timeout 300 wp --path="$site" plugin verify-checksums --all --skip-plugins --skip-themes 2>&1 | sed 's/^/  /' | limited
	fi

	echo
	echo "--- アップロードフォルダ内の PHP（通常は存在しない） ---"
	find "$site/wp-content/uploads" -type f \( -iname '*.php*' -o -iname '*.phtml' -o -iname '*.phar' -o -iname '*.pht' \) 2>/dev/null | sed 's/^/  /' | limited

	echo
	echo "--- 最近 ${DAYS} 日以内に変更された PHP ファイル（新しい順） ---"
	find "$site" -type f \( -iname '*.php' -o -iname '*.phtml' -o -iname '.htaccess' -o -iname '.user.ini' \) -mtime "-$DAYS" \
		-not -path '*/wp-content/cache/*' -printf '%TY-%Tm-%Td %TH:%TM  %p\n' 2>/dev/null | sort -r | sed 's/^/  /' | limited
done <<<"$SITES"

section "不正コードによく使われる書き方を含むファイル（候補。正規のプラグインでも当たることがあります）"
find "$HOME" -maxdepth 12 -type f \( -iname '*.php' -o -iname '*.phtml' -o -iname '*.inc' \) -size -3M -not -path "$HOME/.wp-watch/*" -print0 2>/dev/null |
	xargs -0 grep -lIE \
		-e 'eval[[:space:]]*\([[:space:]]*(base64_decode|gzinflate|gzuncompress|gzdecode|str_rot13|strrev|hex2bin|\$_(POST|GET|REQUEST|COOKIE|SERVER))' \
		-e 'assert[[:space:]]*\([[:space:]]*\$_(POST|GET|REQUEST|COOKIE)' \
		-e '(shell_exec|passthru|system|proc_open|popen|pcntl_exec)[[:space:]]*\([[:space:]]*\$_(POST|GET|REQUEST|COOKIE)' \
		-e '\$_(POST|GET|REQUEST|COOKIE)[[:space:]]*\[[^]]*\][[:space:]]*\(' \
		-e 'FilesMan|b374k|c99shell|r57shell|WSOsetcookie|Uname:.*Php:' 2>/dev/null | sed 's/^/  /' | limited

section "auto_prepend_file などの設定（全ファイルに不正コードを差し込む手口）"
find "$HOME" -maxdepth 8 -type f \( -name '.htaccess' -o -name '.user.ini' -o -name 'php.ini' \) -print0 2>/dev/null |
	xargs -0 grep -HiE 'auto_(prepend|append)_file' 2>/dev/null | sed 's/^/  /' | limited

section "名前が「.」で始まる PHP ファイル（隠しファイル）"
find "$HOME" -maxdepth 10 -type f -name '.*.php' 2>/dev/null | sed 's/^/  /' | limited

section "SSH で入れる鍵（~/.ssh/authorized_keys）"
for file in "$HOME/.ssh/authorized_keys" "$HOME/.ssh/authorized_keys2"; do
	[ -f "$file" ] || continue
	echo "  $file（最終変更: $(date -r "$file" '+%Y-%m-%d %H:%M')）"
	if command -v ssh-keygen >/dev/null 2>&1; then
		ssh-keygen -lf "$file" 2>&1 | sed 's/^/    /'
	else
		grep -vE '^[[:space:]]*(#|$)' "$file" | awk '{ print "    " $1, $NF }'
	fi
done

section "cron（自動実行の設定）"
crontab -l 2>&1 | sed 's/^/  /' | limited

section "動いているプロセス（このユーザー分）"
ps -u "$(id -u)" -o pid,etime,args 2>/dev/null | grep -vE '[w]p-inspect|ps -u|sed |awk |grep ' | sed 's/^/  /' | limited

printf '\n点検は以上です。\n'
