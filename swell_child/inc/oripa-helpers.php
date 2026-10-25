<?php
/**
 * オリパリスト共通ヘルパー関数
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ORIPA_PRODUCT_SHOP_TAXONOMY' ) ) {
	define( 'ORIPA_PRODUCT_SHOP_TAXONOMY', 'shops' ); // 商品に紐づくショップタクソノミー
}

if ( ! defined( 'ORIPA_SHOP_PRODUCTS_PER_PAGE' ) ) {
	define( 'ORIPA_SHOP_PRODUCTS_PER_PAGE', 5 );
}

if ( ! function_exists( 'oripa_tcg_filters' ) ) {
	/**
	 * 絞り込みボタンに使うTCGタイトル（タクソノミー tcgtitle のスラッグ => 表示名）
	 *
	 * @return array<string, string>
	 */
	function oripa_tcg_filters() {
		return [
			'pokeca'   => 'ポケカ',
			'onepiece' => 'ワンピース',
			'yugioh'   => '遊戯王',
			'hobby'    => 'ホビー',
		];
	}
}

if ( ! function_exists( 'oripa_format_acf_display_value' ) ) {
	/**
	 * ACFフィールド値を表示用文字列に変換
	 *
	 * @param mixed $value フィールド値
	 * @return string
	 */
	function oripa_format_acf_display_value( $value ) {
		if ( is_array( $value ) ) {
			$items = array_filter( array_map( 'strval', $value ) );
			return implode( '、', $items );
		}
		if ( $value === null || $value === false || $value === '' ) {
			return '';
		}
		return (string) $value;
	}
}

if ( ! function_exists( 'oripa_query_shop_products' ) ) {
	/**
	 * ショップに紐づく公開商品を取得する
	 *
	 * @param int $shop_post_id ショップ投稿ID
	 * @param int $limit        取得件数
	 * @param int $offset       開始位置
	 * @return WP_Query|null
	 */
	function oripa_query_shop_products( $shop_post_id, $limit, $offset = 0 ) {
		$shop_post_id = absint( $shop_post_id );
		$shop_slug    = get_post_field( 'post_name', $shop_post_id );

		if ( ! $shop_slug ) {
			return null;
		}

		$limit  = (int) $limit;
		$offset = absint( $offset );
		if ( 0 === $limit || $limit < -1 ) {
			$limit = ORIPA_SHOP_PRODUCTS_PER_PAGE;
		}

		return new WP_Query(
			[
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'posts_per_page'      => $limit,
				'offset'              => $offset,
				'orderby'             => [
					'date' => 'DESC',
					'ID'   => 'DESC',
				],
				'ignore_sticky_posts' => true,
				'tax_query'           => [
					[
						'taxonomy' => ORIPA_PRODUCT_SHOP_TAXONOMY,
						'field'    => 'slug',
						'terms'    => $shop_slug,
					],
				],
			]
		);
	}
}

if ( ! function_exists( 'oripa_get_shop_products_listing' ) ) {
	/**
	 * ショップ詳細の商品一覧（初回5件と総件数）
	 *
	 * @param int $shop_post_id ショップ投稿ID
	 * @return array{html:string,total:int}
	 */
	function oripa_get_shop_products_listing( $shop_post_id ) {
		$empty = [
			'html'  => '',
			'total' => 0,
		];

		if ( ! function_exists( 'oripa_get_product_card_html' ) ) {
			return $empty;
		}

		$query = oripa_query_shop_products( $shop_post_id, -1, 0 );
		if ( ! $query || empty( $query->posts ) ) {
			return $empty;
		}

		$filters = oripa_tcg_filters();
		$counts  = array_fill_keys( array_keys( $filters ), 0 );
		$items   = [];

		foreach ( $query->posts as $product ) {
			$slugs = [];
			$terms = get_the_terms( $product->ID, 'tcgtitle' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					if ( ! isset( $filters[ $term->slug ] ) ) {
						continue;
					}
					$slugs[] = $term->slug;
					++$counts[ $term->slug ];
				}
			}
			$items[] = [
				'id'  => (int) $product->ID,
				'tcg' => implode( ' ', array_values( array_unique( $slugs ) ) ),
			];
		}

		$total = count( $items );
		if ( $total < 1 ) {
			return $empty;
		}

		ob_start();
		$visible_filters = array_filter( $counts );
		if ( $visible_filters ) :
			?>
			<div class="oripashop-tcg-filters" role="group" aria-label="TCGタイトル">
				<?php foreach ( $filters as $slug => $label ) : ?>
					<?php if ( empty( $counts[ $slug ] ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<button type="button" class="oripashop-tcg-filters__btn" data-tcg="<?php echo esc_attr( $slug ); ?>" aria-pressed="false"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</div>
			<?php
		endif;
		?>
		<div class="oripa-cards" id="oripashop-products">
			<?php foreach ( $items as $index => $item ) : ?>
				<?php $hidden = $index >= ORIPA_SHOP_PRODUCTS_PER_PAGE; ?>
				<div class="oripa-card-slot<?php echo $hidden ? ' is-hidden' : ''; ?>"<?php echo $hidden ? ' hidden' : ''; ?> data-tcg="<?php echo esc_attr( $item['tcg'] ); ?>">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oripa_get_product_card_html() 内でエスケープ済み
					echo oripa_get_product_card_html( $item['id'] );
					?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $total > ORIPA_SHOP_PRODUCTS_PER_PAGE ) : ?>
			<button type="button" class="oripashop-more" data-step="<?php echo esc_attr( (string) ORIPA_SHOP_PRODUCTS_PER_PAGE ); ?>">もっと見る</button>
		<?php endif; ?>
		<?php

		return [
			'html'  => ob_get_clean(),
			'total' => $total,
		];
	}
}
