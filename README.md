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

## ショートコード

- `[oripa_product id="投稿ID"]` … 商品カードを1件表示
- `[oripa_products type="オリパタイプのスラッグ" limit="6"]` … オリパタイプ別の商品カード一覧

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
