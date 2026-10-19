<?php
/**
 * 商品タグ一覧（/tags/タグのスラッグ/）
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

oripa_render_product_archive( single_term_title( '', false ) . 'のオリパ' );
