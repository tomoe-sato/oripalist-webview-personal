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

if ( ! defined( 'ORIPA_BEGINNER_MAX_PRICE' ) ) {
	define( 'ORIPA_BEGINNER_MAX_PRICE', 500 ); // 初心者向け（低価格）のオリパの上限価格
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
	$vars[] = 'tcg';
	$vars[] = 'price_max';
	return $vars;
} );

add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'product' ) || $query->is_tax( ORIPA_TAXONOMY_TAG ) ) {
		$query->set( 'posts_per_page', ORIPA_LIST_PER_PAGE );
		$query->set( 'post_type', 'product' );
		// 新着順（公開日が新しい順、同じ日時なら後から登録したもの）
		$query->set(
			'orderby',
			[
				'date' => 'DESC',
				'ID'   => 'DESC',
			]
		);

		$tcg = oripa_current_tcg( $query );
		if ( $tcg ) {
			$query->set(
				'tax_query',
				[
					[
						'taxonomy' => 'tcgtitle',
						'field'    => 'slug',
						'terms'    => $tcg,
					],
				]
			);
		}

		if ( $query->is_post_type_archive( 'product' ) ) {
			$meta_query = oripa_product_meta_query( (bool) $query->get( 'event' ), oripa_current_price_max( $query ) );
			if ( $meta_query ) {
				$query->set( 'meta_query', $meta_query );
			}
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
 * 価格の上限（?price_max=500）。0以下や数字以外は無視する
 *
 * @param WP_Query|null $query クエリ（省略時はメインクエリ）
 * @return int 上限なしは 0
 */
function oripa_current_price_max( $query = null ) {
	$value = $query ? $query->get( 'price_max' ) : get_query_var( 'price_max' );
	return max( 0, absint( $value ) );
}

/**
 * 商品一覧の ACF 条件（イベント中・価格の上限）
 *
 * @param bool $is_event  イベント中の商品だけにする
 * @param int  $price_max 価格の上限（0 は上限なし）
 * @return array
 */
function oripa_product_meta_query( $is_event, $price_max ) {
	$meta_query = [];

	if ( $is_event ) {
		$meta_query[] = [
			'key'   => 'イベント中',
			'value' => '1',
		];
	}

	if ( $price_max > 0 ) {
		// 価格が未入力の商品（数値比較で0扱いになる）は除く
		$meta_query[] = [
			'key'     => '価格',
			'value'   => '',
			'compare' => '!=',
		];
		$meta_query[] = [
			'key'     => '価格',
			'value'   => $price_max,
			'compare' => '<=',
			'type'    => 'NUMERIC',
		];
	}

	return $meta_query;
}

/**
 * 選択中のTCGタイトル（?tcg=スラッグ）。絞り込み対象外の値は無視する
 *
 * @param WP_Query|null $query クエリ（省略時はメインクエリ）
 * @return string
 */
function oripa_current_tcg( $query = null ) {
	$tcg = $query ? (string) $query->get( 'tcg' ) : (string) get_query_var( 'tcg' );
	return array_key_exists( $tcg, oripa_tcg_filters() ) ? $tcg : '';
}

/**
 * 商品一覧のTCGタイトル切り替えボタン
 *
 * 今の一覧（イベント中・商品タグ）の条件のまま、TCGタイトルごとの件数を数え、
 * 該当する商品があるタイトルだけボタンを出す。
 *
 * @return string
 */
function oripa_get_tcg_filter_html() {
	$is_event  = (bool) get_query_var( 'event' );
	$price_max = oripa_current_price_max();
	$base_tax  = [];

	if ( is_tax( ORIPA_TAXONOMY_TAG ) ) {
		$term     = get_queried_object();
		$base_url = get_term_link( $term );
		$base_tax = [
			[
				'taxonomy' => ORIPA_TAXONOMY_TAG,
				'field'    => 'term_id',
				'terms'    => $term->term_id,
			],
		];
	} else {
		$base_url = get_post_type_archive_link( 'product' );
	}
	if ( is_wp_error( $base_url ) || ! $base_url ) {
		return '';
	}
	if ( $is_event ) {
		$base_url = add_query_arg( 'event', '1', $base_url );
	}
	if ( $price_max ) {
		$base_url = add_query_arg( 'price_max', $price_max, $base_url );
	}
	$meta_query = is_tax( ORIPA_TAXONOMY_TAG ) ? [] : oripa_product_meta_query( $is_event, $price_max );

	$buttons = [];
	foreach ( oripa_tcg_filters() as $slug => $label ) {
		$args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'tax_query'      => array_merge(
				$base_tax,
				[
					[
						'taxonomy' => 'tcgtitle',
						'field'    => 'slug',
						'terms'    => $slug,
					],
				]
			),
		];
		if ( $meta_query ) {
			$args['meta_query'] = $meta_query;
		}
		$count = ( new WP_Query( $args ) )->found_posts;
		if ( $count > 0 ) {
			$buttons[ $slug ] = $label;
		}
	}

	if ( ! $buttons ) {
		return '';
	}

	$current = oripa_current_tcg();
	$items   = [ '' => 'すべて' ] + $buttons;

	ob_start();
	?>
	<nav class="oripa-tcg-filter" aria-label="TCGタイトルで絞り込む">
		<?php foreach ( $items as $slug => $label ) : ?>
			<?php $url = '' === $slug ? $base_url : add_query_arg( 'tcg', $slug, $base_url ); ?>
			<a class="oripa-tcg-filter__btn<?php echo $slug === $current ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"<?php echo $slug === $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php
	return ob_get_clean();
}

/**
 * 商品一覧（商品アーカイブ・商品タグ）を商品カードで表示
 *
 * @param string $title 見出し
 */
function oripa_render_product_archive( $title ) {
	$tcg = oripa_current_tcg();
	if ( $tcg ) {
		$title .= '（' . oripa_tcg_filters()[ $tcg ] . '）';
	}

	get_header();
	?>
	<main id="main_content" class="l-mainContent l-article oripa-list-page">
		<div class="l-mainContent__inner">
			<h1 class="oripa-list-page__title"><?php echo esc_html( $title ); ?></h1>
			<?php echo oripa_get_tcg_filter_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 関数内でエスケープ済み ?>
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
 *
 * group は oripa（オリパ一覧）/ tool（便利な機能）
 *
 * @return array<int, array{group:string, label:string, desc:string, url:string, icon:string}>
 */
function oripa_get_shortcut_items() {
	$items = [
		[
			'group' => 'oripa',
			'label' => '初心者向け',
			'desc'  => '500円以下',
			'url'   => add_query_arg( 'price_max', ORIPA_BEGINNER_MAX_PRICE, get_post_type_archive_link( 'product' ) ),
			'icon'  => '<path d="M3 5l9 3 9-3v13l-9 3-9-3z"/><path d="M12 8v13"/>',
		],
		[
			'group' => 'oripa',
			'label' => '新着',
			'desc'  => '新しく追加',
			'url'   => get_post_type_archive_link( 'product' ),
			'icon'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		],
		[
			'group' => 'oripa',
			'label' => 'イベント',
			'desc'  => '開催中',
			'url'   => add_query_arg( 'event', '1', get_post_type_archive_link( 'product' ) ),
			'icon'  => '<rect x="3" y="8" width="18" height="13" rx="1"/><path d="M3 12h18M12 8v13"/><path d="M12 8c-2-4-6-4-6-1s6 1 6 1c0 0 6 2 6-1s-4-3-6 1z"/>',
		],
		[
			'group' => 'tool',
			'label' => 'コラム',
			'desc'  => '攻略・解説',
			'url'   => ORIPA_COLUMN_URL,
			'icon'  => '<path d="M4 4h6a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H4z"/><path d="M20 4h-6a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h6z"/>',
		],
		[
			'group' => 'tool',
			'label' => '確率計算',
			'desc'  => '当たり確率',
			'url'   => home_url( '/probability/' ),
			'icon'  => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8"/><path d="M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/>',
		],
		[
			'group' => 'tool',
			'label' => '人気ショップ',
			'desc'  => 'ショップ一覧',
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

	$groups = [
		'oripa' => 'オリパをさがす',
		'tool'  => 'べんりな機能',
	];

	ob_start();
	?>
	<nav class="oripa-shortcuts" aria-label="ショートカット">
		<?php foreach ( $groups as $group => $group_label ) : ?>
			<?php
			$group_items = array_filter(
				$items,
				function ( $item ) use ( $group ) {
					return $item['group'] === $group;
				}
			);
			if ( ! $group_items ) {
				continue;
			}
			?>
			<div class="oripa-shortcuts__group oripa-shortcuts__group--<?php echo esc_attr( $group ); ?>">
				<p class="oripa-shortcuts__group-label"><?php echo esc_html( $group_label ); ?></p>
				<div class="oripa-shortcuts__grid">
					<?php foreach ( $group_items as $item ) : ?>
						<a class="oripa-shortcuts__item" href="<?php echo esc_url( $item['url'] ); ?>">
							<svg class="oripa-shortcuts__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定のSVGパス ?>
							</svg>
							<span class="oripa-shortcuts__label"><?php echo esc_html( $item['label'] ); ?></span>
							<span class="oripa-shortcuts__desc"><?php echo esc_html( $item['desc'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</nav>
	<?php
	return ob_get_clean();
}
add_shortcode( 'oripa_shortcuts', 'oripa_shortcuts_shortcode' );
