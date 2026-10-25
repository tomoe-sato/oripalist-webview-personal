<?php
/**
 * 商品一覧
 * - /product/                 新着オリパ
 * - /product/?event=1         イベント中のオリパ
 * - /product/?price_max=500   初心者向け（指定価格以下）のオリパ
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$price_max = oripa_current_price_max();

if ( $price_max ) {
	$title = '初心者向けオリパ（' . number_format( $price_max ) . '円以下）';
} elseif ( get_query_var( 'event' ) ) {
	$title = 'イベント中のオリパ';
} else {
	$title = '新着オリパ';
}

oripa_render_product_archive( $title );
