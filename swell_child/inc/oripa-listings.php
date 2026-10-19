<?php
/**
 * 一覧ページ（ショップ一覧・商品一覧・商品タグ一覧）とトップページのショートカット
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ORIPA_COLUMN_URL' ) ) {
	define( 'ORIPA_COLUMN_URL', home_url( '/column/' ) ); // コラム記事一覧ページのURL
}

if ( ! defined( 'ORIPA_LIST_PER_PAGE' ) ) {
	define( 'ORIPA_LIST_PER_PAGE', 20 );
}

// アーカイブを有効化したら値を変えて、パーマリンクを1回だけ再生成させる
const ORIPA_REWRITE_VERSION = '1';

/**
 * ショップ（/shop/）と商品（/product/）の一覧ページを有効にする
 */
add_filter( 'register_post_type_args', function ( $args, $post_type ) {
	if ( in_array( $post_type, [ 'shop', 'product' ], true ) ) {
		$args['has_archive'] = true;
	}
	return $args;
}, 10, 2 );

add_action( 'init', function () {
	if ( get_option( 'oripa_rewrite_version' ) !== ORIPA_REWRITE_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'oripa_rewrite_version', ORIPA_REWRITE_VERSION );
	}
}, 99 );

/**
 * 「イベント中」の商品だけを表示するためのクエリ変数（/product/?event=1）
 */
add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'event';
	return $vars;
} );

add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'product' ) || $query->is_tax( ORIPA_TAXONOMY_TAG ) ) {
		$query->set( 'posts_per_page', ORIPA_LIST_PER_PAGE );
		$query->set( 'post_type', 'product' );

		if ( $query->is_post_type_archive( 'product' ) && $query->get( 'event' ) ) {
			$query->set(
				'meta_query',
				[
					[
						'key'   => 'イベント中',
						'value' => '1',
					],
				]
			);
		}
	}

	if ( $query->is_post_type_archive( 'shop' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set(
			'orderby',
			[
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			]
		);
	}
} );

/**
 * 商品一覧（商品アーカイブ・商品タグ）を商品カードで表示
 *
 * @param string $title 見出し
 */
function oripa_render_product_archive( $title ) {
	get_header();
	?>
	<main id="main_content" class="l-mainContent l-article oripa-list-page">
		<div class="l-mainContent__inner">
			<h1 class="oripa-list-page__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( have_posts() ) : ?>
				<div class="oripa-cards">
					<?php
					while ( have_posts() ) :
						the_post();
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oripa_get_product_card_html() 内でエスケープ済み
						echo oripa_get_product_card_html( get_the_ID() );
					endwhile;
					?>
				</div>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<p class="oripa-list-page__empty">該当するオリパはまだありません。</p>
			<?php endif; ?>
		</div>
	</main>
	<?php
	get_footer();
}

/**
 * ショップカードHTMLを生成
 *
 * @param int $post_id ショップ投稿ID
 * @return string
 */
function oripa_get_shop_card_html( $post_id ) {
	$name      = get_the_title( $post_id );
	$logo_url  = get_field( 'ショップロゴ', $post_id );
	$catch     = get_field( 'キャッチコピー', $post_id );
	$tcg_terms = get_the_terms( $post_id, 'tcgtitle' );
	$tcg_names = ( $tcg_terms && ! is_wp_error( $tcg_terms ) ) ? wp_list_pluck( $tcg_terms, 'name' ) : [];

	ob_start();
	?>
	<a class="oripa-shop-card" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
		<span class="oripa-shop-card__logo">
			<?php if ( $logo_url ) : ?>
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="" loading="lazy">
			<?php else : ?>
				<span class="oripa-shop-card__initial"><?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?></span>
			<?php endif; ?>
		</span>
		<span class="oripa-shop-card__body">
			<span class="oripa-shop-card__name"><?php echo esc_html( $name ); ?></span>
			<?php if ( $catch ) : ?>
				<span class="oripa-shop-card__catch"><?php echo esc_html( $catch ); ?></span>
			<?php endif; ?>
			<?php if ( $tcg_names ) : ?>
				<span class="oripa-shop-card__tcg"><?php echo esc_html( implode( ' / ', $tcg_names ) ); ?></span>
			<?php endif; ?>
		</span>
	</a>
	<?php
	return ob_get_clean();
}

/**
 * トップページのショートカット項目
 *
 * 商品タグは名前で探すため、スラッグが何であっても動く。
 * 該当するタグが見つからない項目は表示しない。
 *
 * @return array<int, array{label:string, url:string, icon:string}>
 */
function oripa_get_shortcut_items() {
	$tag_url = function ( $name ) {
		$term = get_term_by( 'name', $name, ORIPA_TAXONOMY_TAG );
		if ( ! $term ) {
			return '';
		}
		$link = get_term_link( $term );
		return is_wp_error( $link ) ? '' : $link;
	};

	$items = [
		[
			'label' => '初心者向け',
			'url'   => $tag_url( '初心者向け' ),
			'icon'  => '<path d="M3 5l9 3 9-3v13l-9 3-9-3z"/><path d="M12 8v13"/>',
		],
		[
			'label' => '高還元',
			'url'   => $tag_url( '高還元' ),
			'icon'  => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
		],
		[
			'label' => 'イベント',
			'url'   => add_query_arg( 'event', '1', get_post_type_archive_link( 'product' ) ),
			'icon'  => '<rect x="3" y="8" width="18" height="13" rx="1"/><path d="M3 12h18M12 8v13"/><path d="M12 8c-2-4-6-4-6-1s6 1 6 1c0 0 6 2 6-1s-4-3-6 1z"/>',
		],
		[
			'label' => 'コラム',
			'url'   => ORIPA_COLUMN_URL,
			'icon'  => '<path d="M4 4h6a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H4z"/><path d="M20 4h-6a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h6z"/>',
		],
		[
			'label' => '確率計算',
			'url'   => home_url( '/probability/' ),
			'icon'  => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8"/><path d="M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/>',
		],
		[
			'label' => '人気ショップ',
			'url'   => get_post_type_archive_link( 'shop' ),
			'icon'  => '<path d="M3 9l2-5h14l2 5"/><path d="M4 9v11h16V9"/><path d="M3 9h18"/><path d="M10 20v-6h4v6"/>',
		],
	];

	return array_values(
		array_filter(
			$items,
			function ( $item ) {
				return '' !== $item['url'] && false !== $item['url'];
			}
		)
	);
}

/**
 * トップページのショートカット [oripa_shortcuts]
 *
 * @return string
 */
function oripa_shortcuts_shortcode() {
	$items = oripa_get_shortcut_items();
	if ( ! $items ) {
		return '';
	}

	ob_start();
	?>
	<nav class="oripa-shortcuts" aria-label="ショートカット">
		<?php foreach ( $items as $item ) : ?>
			<a class="oripa-shortcuts__item" href="<?php echo esc_url( $item['url'] ); ?>">
				<svg class="oripa-shortcuts__icon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定のSVGパス ?>
				</svg>
				<span><?php echo esc_html( $item['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
	return ob_get_clean();
}
add_shortcode( 'oripa_shortcuts', 'oripa_shortcuts_shortcode' );
