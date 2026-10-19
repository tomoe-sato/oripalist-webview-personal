<?php

/* 子テーマのfunctions.phpは、親テーマのfunctions.phpより先に読み込まれることに注意してください。 */

require_once get_stylesheet_directory() . '/inc/oripa-helpers.php';

/**
 * アプリ内遷移用カスタムスキームを esc_url() で許可する
 */
add_filter( 'kses_allowed_protocols', function ( $protocols ) {
	$protocols[] = 'orist';
	return array_unique( $protocols );
} );

/**
 * 親テーマのfunctions.phpのあとで読み込みたいコードはこの中に。
 */
// add_filter('after_setup_theme', function(){
// }, 11);


/**
 * 子テーマでのファイルの読み込み
 */
add_action('wp_enqueue_scripts', function() {
	
	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/style.css' ) );
	wp_enqueue_style( 'child_style', get_stylesheet_directory_uri() .'/style.css', [], $timestamp );

	/* その他の読み込みファイルはこの下に記述 */

}, 11 );

/**
 * 商品カード用タクソノミースラッグ（※実際のスラッグに合わせて変更）
 */
define( 'ORIPA_TAXONOMY_SHOP', 'shops' );
define( 'ORIPA_TAXONOMY_TAG', 'tags' );
define( 'ORIPA_TAXONOMY_TYPE', 'oripa_type' ); // オリパタイプ（※実際のスラッグに合わせて変更）

/**
 * ショップ詳細ページはサイドバーなし1カラム
 */
add_filter( 'swell_is_show_sidebar', function ( $show ) {
	if ( is_singular( 'shop' ) ) {
		return false;
	}
	return $show;
} );

/**
 * 価格表示用フォーマット
 *
 * @param mixed $price 価格フィールド値
 * @return string
 */
function oripa_format_price( $price ) {
	if ( $price === '' || $price === null || $price === false ) {
		return '';
	}

	$normalized = str_replace( [ '¥', '円', ',', ' ' ], '', (string) $price );
	if ( is_numeric( $normalized ) ) {
		return '¥' . number_format( (float) $normalized );
	}

	return (string) $price;
}

/**
 * アプリ商品URLを取得
 *
 * ACFのURLフィールド（文字列）とリンクフィールド（配列）の両方に対応する。
 *
 * @param int $post_id 商品投稿ID
 * @return string URLが未入力の場合は空文字
 */
/**
 * 商品カードHTMLを生成
 *
 * @param int $post_id 商品投稿ID
 * @return string
 */
function oripa_get_product_card_html( $post_id ) {
	$post_id = absint( $post_id );
	$post    = get_post( $post_id );

	if ( ! $post || 'product' !== $post->post_type || 'publish' !== $post->post_status ) {
		return '';
	}

	$price           = get_field( '価格', $post_id );
	$total_slots     = get_field( '全口数', $post_id );
	$remaining_slots = get_field( '残口数', $post_id );
	$thumbnail_url   = get_field( 'サムネイル画像', $post_id );
	$product_title   = get_field( '商品タイトル', $post_id );
	// CTAのリンク先
	$button_url = get_field( 'アプリ商品url', $post_id );

	// サムネイルのalt属性用（カード上には表示しない）
	$display_title   = $product_title ?: get_the_title( $post_id );

	$shop_terms = get_the_terms( $post_id, ORIPA_TAXONOMY_SHOP );
	if ( is_wp_error( $shop_terms ) || empty( $shop_terms ) ) {
		$shop_terms = [];
	}

	$tag_terms = get_the_terms( $post_id, ORIPA_TAXONOMY_TAG );
	if ( is_wp_error( $tag_terms ) || empty( $tag_terms ) ) {
		$tag_terms = [];
	}

	$formatted_price = oripa_format_price( $price );
	$has_stock       = $remaining_slots || $total_slots;

	ob_start();
	?>
	<div class="oripa-card">
		<?php if ( $thumbnail_url ) : ?>
			<div class="oripa-card__image">
				<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $display_title ); ?>" loading="lazy">
			</div>
		<?php endif; ?>

		<div class="oripa-card__body">
			<?php if ( $tag_terms ) : ?>
				<div class="oripa-card__tags">
					<?php foreach ( $tag_terms as $tag_term ) : ?>
						<span class="oripa-card__tag"><?php echo esc_html( $tag_term->name ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $has_stock ) : ?>
				<div class="oripa-card__title-row oripa-card__title-row--stock-only">
					<p class="oripa-card__stock">
						<?php if ( $remaining_slots ) : ?>
							<span><?php echo esc_html( $remaining_slots ); ?>口</span>
						<?php endif; ?>
						<?php if ( $remaining_slots && $total_slots ) : ?>
							<span class="oripa-card__stock-sep">/</span>
						<?php endif; ?>
						<?php if ( $total_slots ) : ?>
							<span><?php echo esc_html( $total_slots ); ?>口</span>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $formatted_price || $shop_terms ) : ?>
				<div class="oripa-card__price-row">
					<?php if ( $formatted_price ) : ?>
						<p class="oripa-card__price"><?php echo esc_html( $formatted_price ); ?></p>
					<?php else : ?>
						<div class="oripa-card__price oripa-card__price--empty"></div>
					<?php endif; ?>

					<?php if ( $shop_terms ) : ?>
						<div class="oripa-card__shops">
							<?php foreach ( $shop_terms as $shop_term ) : ?>
								<span class="oripa-card__shop"><?php echo esc_html( $shop_term->name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $button_url ) : ?>
	<a href="<?php echo esc_url( $button_url ); ?>" class="oripa-card__btn">
		<span>詳細を見る！</span>
					<svg class="oripa-card__btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
						<path d="M14 3h7v7M10 14L21 3M21 10v11H3V3h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * 商品カードショートコード [oripa_product id="投稿ID"]
 *
 * @param array $atts ショートコード属性
 * @return string
 */
function oripa_product_shortcode( $atts ) {
	$atts = shortcode_atts(
		[
			'id' => 0,
		],
		$atts,
		'oripa_product'
	);

	$post_id = absint( $atts['id'] );
	if ( ! $post_id ) {
		return '';
	}

	return oripa_get_product_card_html( $post_id );
}
add_shortcode( 'oripa_product', 'oripa_product_shortcode' );

/**
 * オリパタイプ別 商品カード一覧ショートコード [oripa_products type="タームスラッグ" limit="6"]
 *
 * @param array $atts ショートコード属性
 * @return string
 */
function oripa_products_shortcode( $atts ) {
	$atts = shortcode_atts(
		[
			'type'  => '',
			'limit' => 6,
		],
		$atts,
		'oripa_products'
	);

	$type_slug = sanitize_title( $atts['type'] );
	if ( ! $type_slug ) {
		return '';
	}

	$limit = absint( $atts['limit'] );
	if ( $limit < 1 ) {
		$limit = 6;
	}

	$query = new WP_Query(
		[
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'no_found_rows'  => true,
			'tax_query'      => [
				[
					'taxonomy' => ORIPA_TAXONOMY_TYPE,
					'field'    => 'slug',
					'terms'    => $type_slug,
				],
			],
		]
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return '';
	}

	ob_start();
	?>
	<div class="oripa-cards">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oripa_get_product_card_html() 内でエスケープ済み
			echo oripa_get_product_card_html( get_the_ID() );
		endwhile;
		?>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'oripa_products', 'oripa_products_shortcode' );

/**
 * オリパ診断チャート用アセット読み込み
 */
function orilist_shindan_enqueue_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $version   = (string) max(
        filemtime( $theme_dir . '/assets/oripa-shindan.css' ),
        filemtime( $theme_dir . '/assets/oripa-shindan.js' )
    );

    wp_enqueue_style(
        'orilist-shindan',
        $theme_uri . '/assets/oripa-shindan.css',
        array(),
        $version
    );

    wp_enqueue_script(
        'orilist-shindan',
        $theme_uri . '/assets/oripa-shindan.js',
        array(),
        $version,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'orilist_shindan_enqueue_assets' );
/**
 * オリパリスト WebView専用アクセス制御
 */
add_action('init', function () {

    // ===== 設定ここから =====

    // アプリ側と共有するヘッダー名・秘密キー
    $header_name = 'HTTP_X_ORIPALIST_APP';

    // 秘密キーは wp-config.php で define( 'ORIPALIST_APP_SECRET', '...' ); として定義する
    // （リポジトリに秘密キーを含めないため）
    $secret_key = defined('ORIPALIST_APP_SECRET') ? (string) ORIPALIST_APP_SECRET : '';

    // Cookie名
    $cookie_name = 'oripalist_webview_auth';

    // Cookieの有効期限：30日
    $cookie_lifetime = 60 * 60 * 24 * 30;

    // ===== 設定ここまで =====


    $request_uri = $_SERVER['REQUEST_URI'] ?? '';

    // 管理画面・ログイン画面はPCから入れるように除外
    if (
        strpos($request_uri, '/wp-admin') === 0 ||
        strpos($request_uri, '/wp-login.php') === 0
    ) {
        return;
    }

    // WordPressの管理系AjaxやREST APIも必要に応じて除外
    if (
        strpos($request_uri, '/wp-json') === 0 ||
        strpos($request_uri, '/wp-admin/admin-ajax.php') === 0
    ) {
        return;
    }

    // 静的ファイルは除外
    if (preg_match('/\.(css|js|jpg|jpeg|png|gif|webp|svg|ico|woff|woff2|ttf|eot)$/i', $request_uri)) {
        return;
    }

    // 秘密キー未設定時は空文字同士の照合で全員通過してしまうため、ブロックする
    if ($secret_key === '') {
        status_header(403);
        nocache_headers();
        wp_die(
            'このページはアプリ内からのみ閲覧できます。',
            '403 Forbidden',
            ['response' => 403]
        );
    }

    // すでにCookieがある場合は通す
    if (
        isset($_COOKIE[$cookie_name]) &&
        hash_equals($secret_key, $_COOKIE[$cookie_name])
    ) {
        return;
    }

    // 初回アクセス時：アプリからのカスタムHTTPヘッダーを確認
    $incoming_key = $_SERVER[$header_name] ?? '';

    if (hash_equals($secret_key, $incoming_key)) {

        // Cookieを発行
        setcookie($cookie_name, $secret_key, [
            'expires'  => time() + $cookie_lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return;
    }

    // Cookieもヘッダーもない場合はブロック
    status_header(403);
    nocache_headers();

    wp_die(
        'このページはアプリ内からのみ閲覧できます。',
        '403 Forbidden',
        ['response' => 403]
    );
});