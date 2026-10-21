<?php
/**
 * パリス（公式キャラクター）のトップページ用パーツ
 *
 * - [paris_hero]    : パリスのあいさつ（セリフとポーズが5秒ごとに入れ替わる）
 * - [paris_shindan] : 診断への呼びかけ
 * - 見出しブロックの追加CSSクラス paris-heading : 見出しの横に小さなパリスを表示
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * パリス画像のURL
 *
 * @param string $pose smile / joy / excited / surprise / think / search / recommend / shindan
 * @return string
 */
function paris_image_url( $pose ) {
	return get_stylesheet_directory_uri() . '/assets/paris/paris-' . sanitize_key( $pose ) . '.webp';
}

/**
 * あいさつのセリフとポーズ
 *
 * @return array<int, array{text:string, pose:string}>
 */
function paris_hero_lines() {
	$lines = [
		[
			'text' => 'こんにちは！今日も気になるオリパ、一緒に探しに行こう！',
			'pose' => 'smile',
		],
		[
			'text' => 'あちこち走り回って、新しいオリパ情報を集めてきたよ！',
			'pose' => 'excited',
		],
		[
			'text' => 'おすすめをお知らせ！ニブイチのコツ、もうチェックした？',
			'pose' => 'recommend',
		],
		[
			'text' => 'いいオリパ見つけた！注目の特集を見てみてね',
			'pose' => 'joy',
		],
	];

	return apply_filters( 'paris_hero_lines', $lines );
}

/**
 * パリスのあいさつ [paris_hero]
 *
 * @return string
 */
function paris_hero_shortcode() {
	$lines = paris_hero_lines();
	if ( ! $lines ) {
		return '';
	}

	$data = array_map(
		function ( $line ) {
			return [
				'text' => $line['text'],
				'img'  => paris_image_url( $line['pose'] ),
			];
		},
		$lines
	);

	ob_start();
	?>
	<section class="paris-hero" data-paris-lines="<?php echo esc_attr( wp_json_encode( $data ) ); ?>">
		<p class="paris-hero__label">パリスの今日のひとこと</p>
		<div class="paris-hero__body">
			<button type="button" class="paris-hero__paris" aria-label="パリスに話しかける">
				<img class="paris-hero__img" src="<?php echo esc_url( $data[0]['img'] ); ?>" alt="パリス" width="136" height="150">
			</button>
			<p class="paris-hero__bubble" aria-live="polite"><?php echo esc_html( $data[0]['text'] ); ?></p>
		</div>
		<?php if ( count( $data ) > 1 ) : ?>
			<div class="paris-hero__dots" aria-hidden="true">
				<?php foreach ( $data as $i => $line ) : ?>
					<span class="paris-hero__dot<?php echo 0 === $i ? ' is-current' : ''; ?>"></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'paris_hero', 'paris_hero_shortcode' );

/**
 * 診断への呼びかけ [paris_shindan url="..."]
 *
 * @param array $atts ショートコード属性
 * @return string
 */
function paris_shindan_shortcode( $atts ) {
	$atts = shortcode_atts(
		[
			'url' => home_url( '/shindan/' ),
		],
		$atts,
		'paris_shindan'
	);

	ob_start();
	?>
	<section class="paris-shindan">
		<div class="paris-shindan__body">
			<img class="paris-shindan__img" src="<?php echo esc_url( paris_image_url( 'shindan' ) ); ?>" alt="診断ボードを持つパリス" width="112" height="112" loading="lazy">
			<div>
				<p class="paris-shindan__title">どのオリパがいいか、<br>一緒に探そう！</p>
				<p class="paris-shindan__desc">かんたんな質問で、あなたのオリパタイプがわかるよ</p>
			</div>
		</div>
		<a class="paris-shindan__btn" href="<?php echo esc_url( $atts['url'] ); ?>">
			オリパタイプ診断をはじめる
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
		</a>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'paris_shindan', 'paris_shindan_shortcode' );

/**
 * 見出しの横のパリス（paris-heading クラス）で使う画像をJSに渡す
 */
function paris_heading_images() {
	return [
		'search'   => paris_image_url( 'search' ),
		'excited'  => paris_image_url( 'excited' ),
		'think'    => paris_image_url( 'think' ),
		'surprise' => paris_image_url( 'surprise' ),
		'smile'    => paris_image_url( 'smile' ),
	];
}

/**
 * パリス用アセット読み込み
 */
function paris_enqueue_assets() {
	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();
	$version   = (string) max(
		filemtime( $theme_dir . '/assets/paris.css' ),
		filemtime( $theme_dir . '/assets/paris.js' )
	);

	wp_enqueue_style( 'paris', $theme_uri . '/assets/paris.css', [], $version );
	wp_enqueue_script( 'paris', $theme_uri . '/assets/paris.js', [], $version, true );
	wp_add_inline_script( 'paris', 'window.PARIS_IMAGES = ' . wp_json_encode( paris_heading_images() ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'paris_enqueue_assets' );
