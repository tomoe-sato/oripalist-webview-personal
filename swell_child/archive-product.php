<?php
/**
 * 商品一覧（/product/ は新着順、イベント中は /product/?event=1）
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

oripa_render_product_archive( get_query_var( 'event' ) ? 'イベント中のオリパ' : '新着オリパ' );
