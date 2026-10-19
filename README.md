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
2. `wp-config.php` に WebView 用の秘密キーを追加します（アプリ側の `X-Oripalist-App` ヘッダーの値と同じもの）。

   ```php
   define( 'ORIPALIST_APP_SECRET', 'ここに秘密キー' );
   ```

   > **注意:** この定義がないと、管理画面・REST API・静的ファイル以外のページはすべて 403 になります。
   > 秘密キーは公開リポジトリにコミットしないでください。

## ショートコード

- `[oripa_product id="投稿ID"]` … 商品カードを1件表示
- `[oripa_products type="オリパタイプのスラッグ" limit="6"]` … オリパタイプ別の商品カード一覧
