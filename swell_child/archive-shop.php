<?php
/**
 * ショップ一覧（/shop/）
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main_content" class="l-mainContent l-article oripa-list-page">
	<div class="l-mainContent__inner">
		<h1 class="oripa-list-page__title">ショップ一覧</h1>
		<?php if ( have_posts() ) : ?>
			<div class="oripa-shop-list">
				<?php
				while ( have_posts() ) :
					the_post();
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oripa_get_shop_card_html() 内でエスケープ済み
					echo oripa_get_shop_card_html( get_the_ID() );
				endwhile;
				?>
			</div>
		<?php else : ?>
			<p class="oripa-list-page__empty">ショップはまだありません。</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
