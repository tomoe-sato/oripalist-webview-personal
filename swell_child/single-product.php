<?php
/**
 * 商品（product）カスタム投稿タイプ 詳細ページテンプレート
 *
 * ACFフィールドグループ: 商品情報
 *
 * タクソノミースラッグ（実際の登録名に合わせて変更してください）
 * - ショップ名: shops
 * - 商品タグ: tags
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$the_id = get_the_ID();

	// タクソノミースラッグ（※実際のスラッグに合わせて変更）
	$taxonomy_shop = 'shops';
	$taxonomy_tag  = 'tags';

	$shop_terms = get_the_terms( $the_id, $taxonomy_shop );
	if ( is_wp_error( $shop_terms ) || empty( $shop_terms ) ) {
		$shop_terms = [];
	}

	$tag_terms = get_the_terms( $the_id, $taxonomy_tag );
	if ( is_wp_error( $tag_terms ) || empty( $tag_terms ) ) {
		$tag_terms = [];
	}

	// ACFフィールド取得
	$price            = get_field( '価格', $the_id );
	$total_slots      = get_field( '全口数', $the_id );
	$remaining_slots  = get_field( '残口数', $the_id );
	$thumbnail_url    = get_field( 'サムネイル画像', $the_id );
	$product_title    = get_field( '商品タイトル', $the_id );
	$affiliate_link   = get_field( 'アフィリエイトリンク', $the_id );
	$event_active     = (bool) get_field( 'イベント中', $the_id );

	$display_title = $product_title ?: get_the_title();

	// 当たりカード（URL + ALT）
	$winning_cards = [];
	for ( $i = 1; $i <= 6; $i++ ) {
		$image_url = get_field( '当たりカード' . $i . '枚目', $the_id );
		if ( ! $image_url ) {
			continue;
		}
		$alt = get_field( '当たりカード' . $i . '枚目alt', $the_id );
		$winning_cards[] = [
			'url' => $image_url,
			'alt' => $alt ?: $display_title,
		];
	}

	$has_meta = $shop_terms || $tag_terms || $price || $total_slots || $remaining_slots;
	?>
<main id="main_content" class="l-mainContent l-article">
	<article class="l-mainContent__inner oripa-product" data-clarity-region="article">
		<style>
			.oripa-product__inner {
				display: grid;
				gap: 2rem;
				margin-top: 1.5rem;
			}
			@media (min-width: 768px) {
				.oripa-product__inner {
					grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
					align-items: start;
				}
			}
			.oripa-product__image img {
				display: block;
				width: 100%;
				height: auto;
				border-radius: 8px;
			}
			.oripa-product__title {
				margin: 0 0 1rem;
				font-size: clamp(1.5rem, 4vw, 2rem);
				line-height: 1.4;
			}
			.oripa-product__title-row {
				display: flex;
				flex-wrap: wrap;
				align-items: center;
				gap: 0.5rem 0.75rem;
				margin: 0 0 1rem;
			}
			.oripa-product__title-row .oripa-product__title {
				margin: 0;
				flex: 0 1 auto;
				width: auto;
				min-width: 0;
			}
			.oripa-product__event-badge {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				flex: 0 0 auto;
				padding: 0.3rem 0.75rem;
				font-size: 0.8rem;
				font-weight: 700;
				line-height: 1;
				letter-spacing: 0.03em;
				color: #e60039;
				background:
					linear-gradient(135deg, #fff 0%, #fff0f4 100%) padding-box,
					linear-gradient(135deg, #ff0033, #ff6aa2, #ffffff) border-box;
				border: 1.5px solid transparent;
				border-radius: 999px;
				box-shadow: 0 2px 8px rgba(255, 0, 80, 0.15);
				white-space: nowrap;
			}
			.oripa-product__meta {
				display: grid;
				gap: 0.75rem;
				margin: 0 0 1.5rem;
				padding: 1rem 1.25rem;
				background: var(--color_gray, #f5f5f5);
				border-radius: 8px;
			}
			.oripa-product__meta-item {
				display: flex;
				flex-wrap: wrap;
				gap: 0.5rem 1rem;
				align-items: baseline;
			}
			.oripa-product__meta-label {
				flex: 0 0 5em;
				font-size: 0.875rem;
				font-weight: 700;
				color: var(--color_text_sub, #666);
			}
			.oripa-product__meta-value {
				flex: 1 1 auto;
				font-size: 1rem;
			}
			.oripa-product__price {
				font-size: clamp(1.25rem, 3vw, 1.75rem);
				font-weight: 700;
				color: var(--color_main, #0073aa);
			}
			.oripa-product__tags {
				display: flex;
				flex-wrap: wrap;
				gap: 0.5rem;
			}
			.oripa-product__tag {
				display: inline-block;
				padding: 0.25rem 0.75rem;
				font-size: 0.875rem;
				background: #fff;
				border: 1px solid var(--color_border, #ddd);
				border-radius: 999px;
			}
			.oripa-product__cards {
				margin-bottom: 1.5rem;
			}
			.oripa-product__cards-title {
				margin: 0 0 1rem;
				font-size: 1.125rem;
				font-weight: 700;
			}
			.oripa-product__cards-grid {
				display: grid;
				grid-template-columns: repeat(2, minmax(0, 1fr));
				gap: 1rem;
			}
			@media (min-width: 600px) {
				.oripa-product__cards-grid {
					grid-template-columns: repeat(3, minmax(0, 1fr));
				}
			}
			.oripa-product__card img {
				display: block;
				width: 100%;
				height: auto;
				border-radius: 6px;
			}
			.oripa-product__content {
				margin-bottom: 1.5rem;
				line-height: 1.8;
			}
			.oripa-product__action {
				text-align: center;
			}
			.oripa-product__btn {
				display: block;
				width: 70%;
				max-width: 360px;
				margin: 0 auto;
				padding: 1rem 1.5rem;
				font-size: 1.0625rem;
				font-weight: 700;
				text-align: center;
				text-decoration: none;
				color: #fff;
				background: linear-gradient(135deg, #f15a24 0%, #f7931e 100%);
				border-radius: 14px;
				box-shadow: 0 8px 20px rgba(241, 90, 36, 0.35);
				transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
			}
			.oripa-product__btn:hover {
				color: #fff;
				transform: translateY(-3px);
				filter: brightness(1.04) saturate(1.05);
				box-shadow: 0 14px 30px rgba(247, 147, 30, 0.45);
			}
			.oripa-product__btn:active {
				transform: translateY(2px);
				box-shadow: 0 3px 10px rgba(241, 90, 36, 0.3);
			}
		</style>

		<div class="oripa-product__inner">
			<?php if ( $thumbnail_url ) : ?>
				<div class="oripa-product__image">
					<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $display_title ); ?>" loading="lazy">
				</div>
			<?php endif; ?>

			<div class="oripa-product__body">
				<div class="oripa-product__title-row">
					<h1 class="oripa-product__title c-postTitle__ttl"><?php echo esc_html( $display_title ); ?></h1>
					<?php if ( $event_active ) : ?>
						<span class="oripa-product__event-badge">イベント中</span>
					<?php endif; ?>
				</div>

				<?php if ( $has_meta ) : ?>
					<dl class="oripa-product__meta">
						<?php if ( $shop_terms ) : ?>
							<div class="oripa-product__meta-item">
								<dt class="oripa-product__meta-label">ショップ</dt>
								<dd class="oripa-product__meta-value">
									<?php
									$shop_names = array_map(
										function ( $term ) {
											return esc_html( $term->name );
										},
										$shop_terms
									);
									echo implode( ', ', $shop_names );
									?>
								</dd>
							</div>
						<?php endif; ?>

						<?php if ( $tag_terms ) : ?>
							<div class="oripa-product__meta-item">
								<dt class="oripa-product__meta-label">タグ</dt>
								<dd class="oripa-product__meta-value">
									<span class="oripa-product__tags">
										<?php foreach ( $tag_terms as $tag_term ) : ?>
											<span class="oripa-product__tag"><?php echo esc_html( $tag_term->name ); ?></span>
										<?php endforeach; ?>
									</span>
								</dd>
							</div>
						<?php endif; ?>

						<?php if ( $price ) : ?>
							<div class="oripa-product__meta-item">
								<dt class="oripa-product__meta-label">価格</dt>
								<dd class="oripa-product__meta-value oripa-product__price">￥<?php echo esc_html( $price ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( $total_slots ) : ?>
							<div class="oripa-product__meta-item">
								<dt class="oripa-product__meta-label">全口数</dt>
								<dd class="oripa-product__meta-value"><?php echo esc_html( $total_slots ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( $remaining_slots ) : ?>
							<div class="oripa-product__meta-item">
								<dt class="oripa-product__meta-label">残口数</dt>
								<dd class="oripa-product__meta-value"><?php echo esc_html( $remaining_slots ); ?></dd>
							</div>
						<?php endif; ?>
					</dl>
				<?php endif; ?>

				<?php if ( $winning_cards ) : ?>
					<section class="oripa-product__cards">
						<h2 class="oripa-product__cards-title">当たりカード</h2>
						<div class="oripa-product__cards-grid">
							<?php foreach ( $winning_cards as $card ) : ?>
								<figure class="oripa-product__card">
									<img src="<?php echo esc_url( $card['url'] ); ?>" alt="<?php echo esc_attr( $card['alt'] ); ?>" loading="lazy">
								</figure>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<?php if ( trim( get_post_field( 'post_content', $the_id ) ) ) : ?>
					<div class="oripa-product__content post_content">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>

				<?php if ( $affiliate_link ) : ?>
					<p class="oripa-product__action">
						<a href="<?php echo esc_url( $affiliate_link ); ?>" class="oripa-product__btn" target="_blank" rel="noopener noreferrer">
							商品ページを見る！
						</a>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</article>
</main>
	<?php
endwhile;

get_footer();
