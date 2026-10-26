# オリパリスト WebView（SWELL 子テーマ）

オリパリストアプリの WebView で表示する WordPress サイト用の SWELL 子テーマです。

## 構成

```
swell_child/
├── style.css              # テーマ情報・商品カードのスタイル
├── functions.php          # ショートコード、アセット読み込み、WebView アクセス制御
├── inc/oripa-helpers.php  # ショップ詳細の商品一覧などのヘルパー
├── single-shop.php        # ショップ詳細テンプレート
├── single-product.php     # 商品詳細テンプレート
└── assets/                # オリパ診断チャートの CSS / JS
```

## 前提

- 親テーマ **SWELL**（有料テーマのため、このリポジトリには含めていません）
- **Advanced Custom Fields**（`get_field()` を使用）
- カスタム投稿タイプ `shop` / `product`、タクソノミー `shops` / `tags` / `oripa_type` / `tcgtitle`（テーマ外のプラグイン等で登録）

## 設置

1. `swell_child/` を `wp-content/themes/` にアップロードし、テーマを有効化します。
2. WebView サイトの `wp-config.php`（`oripa-list.jp/public_html/web.oripa-list.jp/wp-config.php`）に、WebView 用の秘密キーを追加します（アプリ側の `X-Oripalist-App` ヘッダーの値と同じもの）。
   **必ず `require_once ABSPATH . 'wp-settings.php';` より上に書いてください。** 下に書くとテーマより後に読み込まれ、キーが未設定扱いになってアプリからのアクセスも 403 になります。

   ```php
   define( 'ORIPALIST_APP_SECRET', 'ここに秘密キー' );   // ← ここに書く

   /* That's all, stop editing! Happy publishing. */
   require_once ABSPATH . 'wp-settings.php';
   ```

   > **注意:** この定義がないと、管理画面・REST API・静的ファイル以外のページはすべて 403 になります。
   > 秘密キーは公開リポジトリにコミットしないでください。
   > WordPress にログイン中の編集者・管理者（投稿の編集権限があるユーザー）は、PC のブラウザからでも閲覧・プレビューできます。

## 動きのある表示（ブロックエディタで使う）

ブロックの設定サイドバー →「高度な設定」→「追加 CSS クラス」に指定すると有効になります。

| クラス | 対象ブロック | 動き |
|---|---|---|
| `oripa-slider` | ギャラリー | 自動スワイプのカルーセル（4秒ごとに次へ・指でスワイプ可・ドット表示）。画像のリンクはそのまま使えます |
| `oripa-reveal` | どのブロックでも | 画面に入ったらふわっとフェードイン |

- 間隔などは `swell_child/assets/oripa-motion.js` 冒頭の設定値で変更できます。
- 端末の「視差効果を減らす」設定が ON の場合、自動スワイプとアニメーションは無効になります。
- サイト側のヘッダー（SWELL）はアプリ側と重複するため、`style.css` で非表示にしています。

## 一覧ページ

| URL | 内容 | テンプレート |
|---|---|---|
| `/shop/` | ショップ一覧 | `archive-shop.php` |
| `/product/` | 新着オリパ（公開日が新しい順） | `archive-product.php` |
| `/product/?event=1` | 「イベント中」が ON の商品一覧 | `archive-product.php` |
| `/product/?price_max=500` | 初心者向け（価格 500 円以下）の商品一覧 | `archive-product.php` |
| `/tags/タグのスラッグ/` | 商品タグごとの商品一覧 | `taxonomy-tags.php` |

商品一覧・商品タグ一覧には、TCG タイトル（ポケカ・ワンピース・遊戯王・ホビー）の切り替えボタンが出ます。入りきらない場合は折り返して表示します。`?tcg=pokeca` のように絞り込み、該当する商品がないタイトルのボタンは表示しません。

- ショップ・商品の一覧ページはコード側（`inc/oripa-listings.php`）で有効にしています。初回アクセス時にパーマリンクを自動で再生成します。
- 一覧が 404 になる場合は、管理画面の「設定 → パーマリンク」で何も変えずに「変更を保存」を押してください。

## パリス（公式キャラクター）のパーツ

| 使い方 | 内容 |
|---|---|
| `[paris_hero]` | パリスのあいさつ。セリフとポーズが5秒ごとに入れ替わり、タップでも次へ進みます |
| `[paris_shindan]` | 診断への呼びかけ（`url="..."` でリンク先を変更可。初期値 `/shindan/`） |
| 見出しの追加 CSS クラス `paris-heading` | 見出しの横に小さなパリスを表示（SWELL の見出し装飾は外れます） |

- 見出しのパリスのポーズは、`paris-heading` に加えて `paris-heading--search`（虫めがね）/ `--excited`（ワクワク）/ `--think`（考え中）/ `--surprise`（びっくり）を付けると変わります。何も付けなければにっこりです。
- セリフは `inc/paris.php` の `paris_hero_lines()` で変更できます。画像は `assets/paris/` にあります。

## ショートコード

- `[oripa_product id="投稿ID"]` … 商品カードを1件表示
- `[oripa_products type="オリパタイプのスラッグ" limit="6"]` … オリパタイプ別の商品カード一覧
- `[oripa_shortcuts]` … トップページのショートカット（初心者向け・新着・イベント・コラム・確率計算・人気ショップ）
  - 「初心者向け」は価格 500 円以下の商品一覧（`/product/?price_max=500`）へリンクします。上限は `ORIPA_BEGINNER_MAX_PRICE` で変更できます。価格が未入力の商品は含みません。
  - コラムのリンク先は `ORIPA_COLUMN_URL`（初期値 `/column/`）です。

## 自動デプロイ（GitHub Actions → Xserver）

`main` ブランチの `swell_child/` に変更が入ると、`.github/workflows/deploy.yml` が SSH（rsync）で Xserver の
`~/oripa-list.jp/public_html/web.oripa-list.jp/wp-content/themes/swell_child/` に反映します。
Actions タブの「Deploy theme to Xserver」→「Run workflow」から手動実行もできます。

### 必要な GitHub Secrets（Settings → Secrets and variables → Actions）

| 名前 | 値 |
|---|---|
| `XSERVER_HOST` | SSH ホスト名（例: `sv12345.xserver.jp`） |
| `XSERVER_USER` | サーバー ID |
| `XSERVER_SSH_KEY` | デプロイ用 SSH 秘密鍵（パスフレーズなし） |

### 注意

- サーバー上で削除されたファイルは同期しません（Git から消したファイルはサーバー側で手動削除）。
- 管理画面のテーマファイルエディターで直接編集した内容は、次回のデプロイで上書きされます。

## サーバー監視（不正プラグイン・不正ユーザーの検知）

`server-monitor/` は Xserver 上で cron 実行する監視スクリプトです。サーバー内の **すべての WordPress**（ホーム以下の全ドメイン・サブドメイン）を5分ごとに見回り、前回から変化があればメール / Slack / Discord に通知します。

| 検知するもの | 見ている場所 |
|---|---|
| プラグインの追加・削除 | `wp-content/plugins/` のフォルダ |
| 管理画面に出ないプラグイン | `wp-content/mu-plugins/`、`wp-content/` 直下の PHP（`object-cache.php` などのドロップイン） |
| プラグインの有効化・無効化 | DB の `active_plugins` |
| ユーザーの追加・削除・権限変更・メール変更 | DB の `users` / `usermeta`（管理者の追加は「⚠ 管理者」と表示） |
| SSH で入れる鍵の追加 | `~/.ssh/authorized_keys` |
| WordPress 自体の追加・削除 | ホーム以下の `wp-settings.php` |

- WordPress を読み込まずにファイルと DB を直接見るため、不正プラグインが管理画面や wp-cli から自分や作った管理者を隠していても検知できます。
- 初回は現在の状態を記録するだけで、通知はしません。通知が送れなかった場合は状態を更新せず、次回もう一度通知します。
- DB に接続できないなど状態を取得できないときは、その旨を1回だけ通知します。

### 設置（SSH で1回だけ）

1. **ファイルを置く** — `main` に入ると GitHub Actions が `~/wp-watch/` に配置します（Actions の「Run workflow」で今すぐ配置も可）。
2. **SSH でログインして設定を作る**

   ```sh
   ssh -p 10022 サーバーID@sv12345.xserver.jp
   ls /usr/bin/php*                                   # 使える PHP を確認（例: /usr/bin/php8.2）
   cp ~/wp-watch/wp-watch.conf.example ~/wp-watch/wp-watch.conf
   vi ~/wp-watch/wp-watch.conf                        # MAIL_TO / SLACK_WEBHOOK_URL / PHP_BIN などを設定
   chmod 600 ~/wp-watch/wp-watch.conf
   ```

3. **動作確認**

   ```sh
   bash ~/wp-watch/wp-watch.sh --test   # 通知が届くか
   bash ~/wp-watch/wp-watch.sh --show   # 検出したサイト・プラグイン・ユーザーの一覧
   bash ~/wp-watch/wp-watch.sh          # 初回の状態を記録（この時点の状態が「正しい状態」になる）
   ```

   `--show` の一覧に見覚えのないプラグイン・ユーザー・鍵がないか、**初回記録の前に必ず確認**してください（すでに侵入されている場合、その状態が基準になってしまいます）。
   より詳しく点検するには `bash ~/wp-watch/wp-inspect.sh` を実行します（読み取りのみ。アップロードフォルダ内の PHP、不正コードによくある書き方、`auto_prepend_file`、SSH 鍵、cron、WordPress 本体・プラグインの改ざんなどを確認）。結果にはメールアドレスが含まれるので、公開の場所には貼らないでください。

4. **cron に登録** — サーバーパネル →「Cron 設定」で、分 `*/5`・時 `*`・日 `*`・月 `*`・曜日 `*`、コマンドに次を設定します。

   ```
   /bin/bash /home/サーバーID/wp-watch/wp-watch.sh
   ```

   Cron 設定の「通知用メールアドレス」を入れておくと、Webhook が届かない場合でも cron の出力メールで差分が届きます。

### 通知が来たら

- 心当たりがあれば（自分でプラグインを入れた等）対応は不要です。次回からはその状態が基準になります。
- 心当たりがない場合は、該当のプラグインを無効化・削除、ユーザーを削除し、残りの管理者のパスワード変更、`authorized_keys` の不要な鍵の削除を行ってください。
- 変化の履歴は `~/.wp-watch/changes.log`、実行ログは `~/.wp-watch/wp-watch.log` にあります。基準を作り直すときは `~/.wp-watch/snapshots/` を削除して手順3をやり直します。

### 注意

- このスクリプトは PHP と同じユーザー権限で動くため、サーバーを完全に乗っ取られた場合は止められる可能性があります。通知が外部（メール・Slack）に飛ぶ時点で検知の目的は果たせますが、定期的に `~/.wp-watch/wp-watch.log` が更新されているかも確認してください。
- 既存プラグインのファイル改ざんは対象外です。必要に応じて `wp plugin verify-checksums --all` などを併用してください。
