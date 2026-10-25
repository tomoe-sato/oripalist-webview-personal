<?php
/**
 * ショップ（shop）カスタム投稿タイプ 詳細ページテンプレート
 *
 * ACFフィールドグループ: ショップ詳細ページ
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_stylesheet_directory() . '/inc/oripa-helpers.php';

get_header();

while ( have_posts() ) :
	the_post();

	$the_id = get_the_ID();

	/**
	 * ACFフィールド名
	 */
	$acf_fields = [
		'logo'                 => 'ショップロゴ',
		'kobutsu_license'      => '古物許可証',
		'kobutsu_number'       => '古物許可証_番号',
		'is_corporate'         => '法人運営',
		'company_name'         => '運営会社',
		'shipping_speed'       => '発送',
		'price_range'          => '価格帯',
		'payment_method'       => '支払い方法',
		'login_bonus'          => 'ログインボーナス',
		'first_bonus'          => '初回特典',
		'rank_system'          => 'ランク制度',
		'jackpot'              => 'ジャックポット',
		'subscription_system'  => 'サブスク制度',
		'invite_code'          => '招待コード',
		'support_response'     => 'お問い合わせ対応',
		'strengths'            => '強み',
		'weaknesses'           => '弱み',
		'recommended_users'    => 'おすすめユーザー',
		'return_rate_trend'    => '総還元率の傾向',
		'point_return_rate'    => 'ポイント還元率',
		'catch_copy'           => 'キャッチコピー',
		'shop_intro'           => 'ショップ紹介',
	];

	$get_acf = function ( $key ) use ( $the_id, $acf_fields ) {
		return get_field( $acf_fields[ $key ], $the_id );
	};

	$logo_url            = $get_acf( 'logo' );
	$kobutsu_license     = oripa_format_acf_display_value( $get_acf( 'kobutsu_license' ) );
	$kobutsu_number      = oripa_format_acf_display_value( $get_acf( 'kobutsu_number' ) );
	$is_corporate        = oripa_format_acf_display_value( $get_acf( 'is_corporate' ) );
	$company_name        = oripa_format_acf_display_value( $get_acf( 'company_name' ) );
	$shipping_speed      = oripa_format_acf_display_value( $get_acf( 'shipping_speed' ) );
	$price_range         = oripa_format_acf_display_value( $get_acf( 'price_range' ) );
	$payment_method      = $get_acf( 'payment_method' );
	$payment_method_text = oripa_format_acf_display_value( $payment_method );
	$login_bonus         = oripa_format_acf_display_value( $get_acf( 'login_bonus' ) );
	$first_bonus         = oripa_format_acf_display_value( $get_acf( 'first_bonus' ) );
	$rank_system         = oripa_format_acf_display_value( $get_acf( 'rank_system' ) );
	$jackpot             = oripa_format_acf_display_value( $get_acf( 'jackpot' ) );
	$subscription_system = oripa_format_acf_display_value( $get_acf( 'subscription_system' ) );
	$invite_code         = oripa_format_acf_display_value( $get_acf( 'invite_code' ) );
	$support_response    = oripa_format_acf_display_value( $get_acf( 'support_response' ) );
	$return_rate_trend   = oripa_format_acf_display_value( $get_acf( 'return_rate_trend' ) );
	$point_return_rate   = oripa_format_acf_display_value( $get_acf( 'point_return_rate' ) );
	$catch_copy          = oripa_format_acf_display_value( get_field( 'キャッチコピー', $the_id ) );

	$shop_intro          = oripa_format_acf_display_value( get_field( 'ショップ紹介', $the_id ) );
	$shop_url            = get_field( '公式サイト', $the_id ); // ※ACFに未登録の場合はフィールド名を変更
	$app_search_url      = oripa_get_app_shop_search_url( $the_id );

	$display_title = get_the_title();

	// 取扱TCGタイトル（タクソノミー: tcgtitle）
	// 1件なら「◯◯専門店」、2件以上なら「 / 」区切りで表示
	$tcg_items = [];
	$tcg_terms = get_the_terms( $the_id, 'tcgtitle' );
	if ( ! is_wp_error( $tcg_terms ) && ! empty( $tcg_terms ) ) {
		$tcg_items = wp_list_pluck( $tcg_terms, 'name' );
	}
	// ACF等で配列・文字列が渡ってきた場合にも対応
	if ( ! $tcg_items ) {
		$tcg_raw = get_field( '取扱TCGタイトル', $the_id );
		if ( is_array( $tcg_raw ) ) {
			$tcg_items = array_values( array_filter( array_map( 'strval', $tcg_raw ) ) );
		} elseif ( '' !== trim( (string) $tcg_raw ) ) {
			$tcg_items = [ trim( (string) $tcg_raw ) ];
		}
	}

	$tcg_title = '';
	if ( 1 === count( $tcg_items ) ) {
		$tcg_title = $tcg_items[0] . '専門店';
	} elseif ( count( $tcg_items ) >= 2 ) {
		$tcg_title = implode( ' / ', $tcg_items );
	}

	// ヒーロー下部ステータスカード（ACFグループフィールド: ファーストビューバッチ）
	$fv_icon_map = [
		'shield'   => 'fa-shield-halved',
		'building' => 'fa-building',
		'truck'    => 'fa-truck',
		'gift'     => 'fa-gift',
		'coins'    => 'fa-coins',
		'star'     => 'fa-star',
		'ticket'   => 'fa-ticket',
		'calendar' => 'fa-calendar',
		'crown'    => 'fa-crown',
		'bolt'     => 'fa-bolt',
		'box'      => 'fa-box',
	];
	$fv_default_icon = 'fa-circle-check';

	$status_cards = [];
	$fv_badges    = get_field( 'ファーストビューバッチ', $the_id );
	if ( is_array( $fv_badges ) ) {
		for ( $i = 1; $i <= 3; $i++ ) {
			$text = isset( $fv_badges[ "fv{$i}-text" ] ) ? trim( (string) $fv_badges[ "fv{$i}-text" ] ) : '';
			if ( '' === $text ) {
				continue;
			}
			$icon_key       = isset( $fv_badges[ "fv{$i}-icon" ] ) ? (string) $fv_badges[ "fv{$i}-icon" ] : '';
			$status_cards[] = [
				'icon' => $fv_icon_map[ $icon_key ] ?? $fv_default_icon,
				'text' => $text,
			];
		}
	}

	// 送料（文字列・配列どちらにも対応）
	$shipping_fee = oripa_format_acf_display_value( get_field( '送料', $the_id ) );

	/**
	 * 改行区切りテキスト・配列を項目配列に変換
	 *
	 * @param mixed $value ACFフィールド値
	 * @return string[]
	 */
	$to_items = function ( $value ) {
		if ( is_array( $value ) ) {
			$items = array_map( 'strval', $value );
		} else {
			$items = preg_split( '/\r\n|\r|\n/', (string) $value );
		}
		return array_values( array_filter( array_map( 'trim', $items ), 'strlen' ) );
	};

	// セクション1: ショップの傾向
	$strength_items  = $to_items( $get_acf( 'strengths' ) );
	$weakness_items  = $to_items( $get_acf( 'weaknesses' ) );
	$recommend_items = $to_items( $get_acf( 'recommended_users' ) );

	$has_trend_section = $price_range || $return_rate_trend || $point_return_rate || $strength_items || $weakness_items || $recommend_items;

	// セクション2: ショップの機能・サービス（2列カードグリッド）
	$service_cards = array_filter(
		[
			[
				'icon'  => 'fa-gift',
				'label' => '初回特典',
				'value' => $first_bonus,
			],
			[
				'icon'  => 'fa-star',
				'label' => 'ログインボーナス',
				'value' => $login_bonus,
			],
			[
				'icon'  => 'fa-trophy',
				'label' => 'ランク制度',
				'value' => $rank_system,
			],
			[
				'icon'  => 'fa-bolt',
				'label' => 'ジャックポット',
				'value' => $jackpot,
			],
			[
				'icon'  => 'fa-repeat',
				'label' => 'サブスク制度',
				'value' => $subscription_system,
			],
			[
				'icon'  => 'fa-share-nodes',
				'label' => '招待コード',
				'value' => $invite_code,
			],
		],
		function ( $card ) {
			return '' !== $card['value'];
		}
	);

	// セクション3: ご利用ガイド（購入前に確認したい情報）
	$guide_items = array_filter(
		[
			[
				'icon'      => 'fa-credit-card',
				'label'     => '支払い方法',
				'value'     => $payment_method_text,
				'highlight' => false,
			],
			[
				'icon'      => 'fa-yen-sign',
				'label'     => '送料',
				'value'     => $shipping_fee,
				'highlight' => true,
			],
			[
				'icon'      => 'fa-truck',
				'label'     => '発送速度',
				'value'     => $shipping_speed,
				'highlight' => true,
			],
			[
				'icon'      => 'fa-headset',
				'label'     => 'お問い合わせ対応',
				'value'     => $support_response,
				'highlight' => false,
			],
		],
		function ( $item ) {
			return '' !== $item['value'];
		}
	);

	// セクション4: 基本情報
	$basic_info = array_filter(
		[
			'運営会社'       => $company_name,
			'法人運営'       => $is_corporate,
			'古物商許可'     => $kobutsu_license,
			'古物商許可番号' => $kobutsu_number,
		]
	);

	$shop_products       = oripa_get_shop_products_listing( $the_id );
	$shop_products_html  = $shop_products['html'];
	$shop_products_total = $shop_products['total'];
	$section_num         = 0;
	?>
<main id="main_content" class="l-mainContent l-article oripashop-page">
	<article class="l-mainContent__inner" data-clarity-region="article">
		<div class="oripashop-outer">
			<div class="oripashop-app">

				<!-- ヒーロー -->
				<section class="oripashop-hero">
					<div class="oripashop-hero__top">
						<?php if ( $logo_url ) : ?>
							<div class="oripashop-hero__logo-card">
								<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $display_title ); ?>" loading="lazy">
							</div>
						<?php else : ?>
							<div class="oripashop-hero__logo-card oripashop-hero__logo-card--text">
								<span class="oripashop-hero__logo-initial"><?php echo esc_html( mb_substr( $display_title, 0, 1 ) ); ?></span>
								<span class="oripashop-hero__logo-name"><?php echo esc_html( $display_title ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $catch_copy || $shop_intro ) : ?>
							<div class="oripashop-hero__meta">
								<?php if ( $catch_copy ) : ?>
									<p class="oripashop-hero__badge"><?php echo esc_html( $catch_copy ); ?></p>
								<?php endif; ?>
								<?php if ( $shop_intro ) : ?>
									<p class="oripashop-hero__desc"><?php echo wp_kses_post( nl2br( esc_html( $shop_intro ) ) ); ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>

					<h1 class="oripashop-hero__title"><?php echo esc_html( $display_title ); ?></h1>

					<?php if ( $tcg_title ) : ?>
						<p class="oripashop-tcg-title"><?php echo esc_html( $tcg_title ); ?></p>
					<?php endif; ?>

					<?php if ( $status_cards ) : ?>
						<div class="oripashop-status">
							<?php foreach ( $status_cards as $card ) : ?>
								<div class="oripashop-status__card">
									<span class="oripashop-status__icon" aria-hidden="true">
										<i class="fa-solid <?php echo esc_attr( $card['icon'] ); ?>"></i>
									</span>
									<p class="oripashop-status__label"><?php echo esc_html( $card['text'] ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>

				<div class="oripashop-body">

					<?php if ( $has_trend_section ) : ++$section_num; ?>
						<section class="oripashop-section">
							<h2 class="oripashop-section__title">
								<span class="oripashop-section__num"><?php echo esc_html( (string) $section_num ); ?></span>
								ショップの傾向
							</h2>

							<?php if ( $price_range ) : ?>
								<div class="oripashop-price-card">
									<p class="oripashop-price-card__label">価格帯</p>
									<p class="oripashop-price-card__value"><?php echo esc_html( $price_range ); ?></p>
								</div>
							<?php endif; ?>

							<?php if ( $return_rate_trend || $point_return_rate ) : ?>
								<div class="oripashop-trend-grid">
									<?php if ( $return_rate_trend ) : ?>
										<div class="oripashop-trend-card">
											<p class="oripashop-trend-card__label">総還元率の傾向</p>
											<p class="oripashop-trend-card__value oripashop-trend-card__value--orange"><?php echo esc_html( $return_rate_trend ); ?></p>
										</div>
									<?php endif; ?>
									<?php if ( $point_return_rate ) : ?>
										<div class="oripashop-trend-card">
											<p class="oripashop-trend-card__label">ポイント還元率</p>
											<p class="oripashop-trend-card__value oripashop-trend-card__value--blue"><?php echo esc_html( $point_return_rate ); ?></p>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( $strength_items ) : ?>
								<div class="oripashop-review-card">
									<h3 class="oripashop-review-card__title">
										<span class="oripashop-review-card__icon oripashop-review-card__icon--plus" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
										強み
									</h3>
									<ul class="oripashop-review-card__list">
										<?php foreach ( $strength_items as $item ) : ?>
											<li class="oripashop-review-card__item oripashop-review-card__item--blue"><?php echo esc_html( $item ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<?php if ( $weakness_items ) : ?>
								<div class="oripashop-review-card">
									<h3 class="oripashop-review-card__title">
										<span class="oripashop-review-card__icon oripashop-review-card__icon--minus" aria-hidden="true"><i class="fa-solid fa-minus"></i></span>
										弱み
									</h3>
									<ul class="oripashop-review-card__list">
										<?php foreach ( $weakness_items as $item ) : ?>
											<li class="oripashop-review-card__item oripashop-review-card__item--gray"><?php echo esc_html( $item ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<?php if ( $recommend_items ) : ?>
								<div class="oripashop-review-card">
									<h3 class="oripashop-review-card__title">
										<span class="oripashop-review-card__icon oripashop-review-card__icon--user" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
										おすすめユーザー層
									</h3>
									<div class="oripashop-review-card__tags">
										<?php foreach ( $recommend_items as $item ) : ?>
											<span class="oripashop-review-tag"><?php echo esc_html( $item ); ?></span>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

						</section>
					<?php endif; ?>

					<?php if ( $service_cards ) : ++$section_num; ?>
						<section class="oripashop-section">
							<h2 class="oripashop-section__title">
								<span class="oripashop-section__num"><?php echo esc_html( (string) $section_num ); ?></span>
								ショップの機能・サービス
							</h2>
							<div class="oripashop-service-grid">
								<?php foreach ( $service_cards as $card ) : ?>
									<div class="oripashop-service-card">
										<span class="oripashop-service-card__icon" aria-hidden="true">
											<i class="fa-solid <?php echo esc_attr( $card['icon'] ); ?>"></i>
										</span>
										<p class="oripashop-service-card__label"><?php echo esc_html( $card['label'] ); ?></p>
										<p class="oripashop-service-card__value"><?php echo esc_html( $card['value'] ); ?></p>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>

					<?php if ( $guide_items ) : ++$section_num; ?>
						<section class="oripashop-section">
							<h2 class="oripashop-section__title">
								<span class="oripashop-section__num"><?php echo esc_html( (string) $section_num ); ?></span>
								ご利用ガイド
							</h2>
							<div class="oripashop-guide-card">
								<p class="oripashop-guide-card__lead">
									<i class="fa-solid fa-circle-info" aria-hidden="true"></i>
									購入前にチェック
								</p>
								<ul class="oripashop-guide-list">
									<?php foreach ( $guide_items as $item ) : ?>
										<li class="oripashop-guide-list__item">
											<span class="oripashop-guide-list__icon" aria-hidden="true">
												<i class="fa-solid <?php echo esc_attr( $item['icon'] ); ?>"></i>
											</span>
											<div class="oripashop-guide-list__content">
												<p class="oripashop-guide-list__label"><?php echo esc_html( $item['label'] ); ?></p>
												<p class="oripashop-guide-list__value<?php echo $item['highlight'] ? ' oripashop-guide-list__value--highlight' : ''; ?>"><?php echo esc_html( $item['value'] ); ?></p>
											</div>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</section>
					<?php endif; ?>

					<?php if ( $basic_info ) : ++$section_num; ?>
						<section class="oripashop-section">
							<h2 class="oripashop-section__title">
								<span class="oripashop-section__num"><?php echo esc_html( (string) $section_num ); ?></span>
								基本情報
							</h2>
							<div class="oripashop-card">
								<ul class="oripashop-list">
									<?php foreach ( $basic_info as $label => $value ) : ?>
										<li class="oripashop-list__item">
											<span class="oripashop-list__icon" aria-hidden="true"></span>
											<div class="oripashop-list__content">
												<p class="oripashop-list__label"><?php echo esc_html( $label ); ?></p>
												<p class="oripashop-list__value"><?php echo esc_html( $value ); ?></p>
											</div>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</section>
					<?php endif; ?>

					<?php if ( $shop_url ) : ?>
						<div class="oripashop-cta">
							<a href="<?php echo esc_url( $shop_url ); ?>" class="oripashop-cta__btn" target="_blank" rel="noopener noreferrer">
								公式サイトへ
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 3h7v7M10 14L21 3M21 10v11H3V3h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</a>
							<p class="oripashop-cta__note"><?php echo esc_html( $display_title ); ?>の公式サイトが開きます</p>
						</div>
					<?php endif; ?>

					<?php if ( $app_search_url && ! $shop_products_html ) : ?>
						<a href="<?php echo esc_url( $app_search_url ); ?>" class="oripashop-app-search">
							ショップページの商品一覧を見る
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2.2"/><path d="M20 20l-4-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
						</a>
					<?php endif; ?>

					<?php if ( $shop_products_html ) : ++$section_num; ?>
						<section class="oripashop-section oripashop-section--products">
							<h2 class="oripashop-section__title">
								<span class="oripashop-section__num"><?php echo esc_html( (string) $section_num ); ?></span>
								<span class="oripashop-section__label">このショップの商品一覧（<span id="oripashop-products-count"><?php echo esc_html( number_format_i18n( $shop_products_total ) ); ?></span>件）</span>
							</h2>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oripa_get_shop_products_listing() 内でエスケープ済み
							echo $shop_products_html;
							?>
							<?php if ( $app_search_url ) : ?>
							<a href="<?php echo esc_url( $app_search_url ); ?>" class="oripashop-app-search">
								ショップページの商品一覧を見る
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2.2"/><path d="M20 20l-4-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
							</a>
							<?php endif; ?>
							<script>
							(function () {
								var list = document.getElementById('oripashop-products');
								if (!list) {
									return;
								}
								var more = document.querySelector('.oripashop-more');
								var filters = document.querySelectorAll('.oripashop-tcg-filters__btn');
								var countEl = document.getElementById('oripashop-products-count');
								var step = more ? (parseInt(more.getAttribute('data-step'), 10) || 5) : 5;
								var active = '';

								function formatCount(value) {
									return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
								}

								function slotList() {
									return list.querySelectorAll('.oripa-card-slot');
								}

								function matches(slot) {
									if (!active) {
										return true;
									}
									var tcgs = (slot.getAttribute('data-tcg') || '').split(/\s+/);
									for (var i = 0; i < tcgs.length; i++) {
										if (tcgs[i] === active) {
											return true;
										}
									}
									return false;
								}

								function updateMore() {
									if (!more) {
										return;
									}
									more.hidden = !list.querySelector('.oripa-card-slot.is-hidden:not(.is-filtered-out)');
								}

								function applyFilter() {
									var matched = [];
									var slots = slotList();
									var i;
									for (i = 0; i < slots.length; i++) {
										if (matches(slots[i])) {
											matched.push(slots[i]);
										} else {
											slots[i].classList.add('is-filtered-out');
											slots[i].classList.add('is-hidden');
											slots[i].hidden = true;
										}
									}
									for (i = 0; i < matched.length; i++) {
										var hide = i >= step;
										matched[i].classList.remove('is-filtered-out');
										matched[i].classList.toggle('is-hidden', hide);
										matched[i].hidden = hide;
									}
									if (countEl) {
										countEl.textContent = formatCount(matched.length);
									}
									updateMore();
								}

								for (var f = 0; f < filters.length; f++) {
									filters[f].addEventListener('click', function () {
										var slug = this.getAttribute('data-tcg') || '';
										active = this.classList.contains('is-active') ? '' : slug;
										for (var j = 0; j < filters.length; j++) {
											var on = filters[j] === this && active !== '';
											filters[j].classList.toggle('is-active', on);
											filters[j].setAttribute('aria-pressed', on ? 'true' : 'false');
										}
										applyFilter();
									});
								}

								if (more) {
									more.addEventListener('click', function () {
										var hidden = list.querySelectorAll('.oripa-card-slot.is-hidden:not(.is-filtered-out)');
										var count = Math.min(step, hidden.length);
										for (var i = 0; i < count; i++) {
											hidden[i].classList.remove('is-hidden');
											hidden[i].hidden = false;
										}
										updateMore();
									});
								}
							})();
							</script>
						</section>
					<?php endif; ?>

				</div><!-- .oripashop-body -->
			</div><!-- .oripashop-app -->
		</div><!-- .oripashop-outer -->
	</article>
</main>

<style>
/* SWELLレイアウトのリセット */
.oripashop-page.l-mainContent {
	float: none;
	width: 100%;
	max-width: none;
	padding: 0;
	margin: 0;
}
.oripashop-page .l-mainContent__inner {
	max-width: none;
	padding: 0;
	margin: 0;
}
.oripashop-page + #sidebar,
.oripashop-page ~ #sidebar {
	display: none !important;
}

/* アプリシェル */
.oripashop-outer {
	width: 100%;
	min-height: 100vh;
	padding: 0;
	background: #fff;
}
.oripashop-app {
	max-width: 420px;
	margin: 0 auto;
	background: #fff;
}

/* ヒーロー */
.oripashop-hero {
	padding: 1.25rem 1rem 1rem;
	background: linear-gradient(160deg, #ff9a3c 0%, #f58220 55%, #e86f10 100%);
	border-radius: 0 0 20px 20px;
}
.oripashop-hero__top {
	display: flex;
	gap: 0.75rem;
	align-items: flex-start;
	margin-bottom: 0.75rem;
}
.oripashop-hero__logo-card {
	flex: 0 0 88px;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 88px;
	height: 88px;
	padding: 0.5rem;
	background: #fff;
	border-radius: 14px;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}
.oripashop-hero__logo-card img {
	display: block;
	max-width: 100%;
	max-height: 72px;
	width: auto;
	height: auto;
	object-fit: contain;
}
.oripashop-hero__logo-card--text {
	flex-direction: column;
	gap: 0.15rem;
}
.oripashop-hero__logo-initial {
	font-size: 2rem;
	font-weight: 700;
	line-height: 1;
	color: #f58220;
}
.oripashop-hero__logo-name {
	font-size: 0.625rem;
	font-weight: 700;
	color: #333;
}
.oripashop-hero__meta {
	flex: 1 1 auto;
	min-width: 0;
}
.oripashop-hero__badge {
	display: inline-flex;
	align-items: center;
	gap: 0.3rem;
	margin: 0 0 0.5rem;
	padding: 0.3rem 0.6rem;
	font-size: 0.6875rem;
	font-weight: 700;
	color: #007aff;
	background: #fff;
	border-radius: 999px;
}
.oripashop-hero__desc {
	margin: 0;
	font-size: 0.75rem;
	line-height: 1.55;
	color: #fff;
}
.oripashop-hero__title {
	margin: 0 0 1rem;
	font-size: 1.375rem;
	font-weight: 700;
	line-height: 1.3;
	color: #fff;
}
.oripashop-tcg-title {
	margin: -0.75rem 0 1rem;
	font-size: 0.75rem;
	font-weight: 500;
	line-height: 1.5;
	color: rgba(255, 255, 255, 0.85);
}
.oripashop-status {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
	gap: 0.5rem;
}
.oripashop-status__card {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: flex-start;
	height: 100%;
	padding: 0.625rem 0.35rem;
	text-align: center;
	background: #fff;
	border-radius: 10px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.oripashop-status__icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 28px;
	height: 28px;
	margin: 0 auto 0.35rem;
	background: #e8f2ff;
	border-radius: 8px;
}
.oripashop-status__icon i {
	font-size: 14px;
	color: #007aff;
}
.oripashop-status__label {
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
	overflow: hidden;
	margin: 0;
	font-size: 0.625rem;
	font-weight: 700;
	line-height: 1.3;
	min-height: calc(0.625rem * 1.3 * 2);
	color: #333;
	word-break: break-word;
}

/* 本文エリア */
.oripashop-body {
	padding: 1rem 0.75rem 2rem;
}

/* セクション見出し */
.oripashop-section {
	margin-bottom: 1.25rem;
}
.oripashop-section__title {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	margin: 0 0 0.75rem;
	padding: 0.5rem 0.75rem;
	font-size: 0.9375rem;
	font-weight: 700;
	color: #007aff;
	background: linear-gradient(90deg, #e8f2ff 0%, transparent 100%);
	border-radius: 8px;
}
.oripashop-section__num {
	display: flex;
	align-items: center;
	justify-content: center;
	min-width: 24px;
	height: 24px;
	padding: 0 0.35rem;
	font-size: 0.8125rem;
	font-weight: 700;
	color: #fff;
	background: #007aff;
	border-radius: 6px;
}

/* カード・リスト */
.oripashop-card {
	padding: 0.25rem 0;
	background: #fff;
	border-radius: 14px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
	overflow: hidden;
}
.oripashop-list {
	margin: 0;
	padding: 0;
	list-style: none;
}
.oripashop-list__item {
	display: flex;
	gap: 0.75rem;
	align-items: flex-start;
	padding: 0.875rem 1rem;
	border-bottom: 1px solid #f0f0f0;
}
.oripashop-list__item:last-child {
	border-bottom: none;
}
.oripashop-list__icon {
	flex: 0 0 32px;
	width: 32px;
	height: 32px;
	background: #fff3e8;
	border: 1px solid #ffd4b0;
	border-radius: 8px;
}
.oripashop-list__content {
	flex: 1 1 auto;
	min-width: 0;
}
.oripashop-list__label {
	margin: 0 0 0.2rem;
	font-size: 0.6875rem;
	font-weight: 600;
	color: #999;
}
.oripashop-list__value {
	margin: 0;
	font-size: 0.875rem;
	font-weight: 700;
	line-height: 1.5;
	color: #222;
	word-break: break-word;
}
.oripashop-list__value--multiline {
	font-weight: 500;
}

/* ショップの傾向（数値カード・レビューカード） */
.oripashop-price-card {
	margin-bottom: 14px;
	padding: 1.125rem 1rem;
	text-align: center;
	background: linear-gradient(135deg, #fff7ef 0%, #fff3e8 100%);
	border: 1.5px solid #f58220;
	border-radius: 16px;
	box-shadow: 0 2px 12px rgba(245, 130, 32, 0.15);
}
.oripashop-price-card__label {
	margin: 0 0 0.3rem;
	font-size: 0.75rem;
	font-weight: 700;
	color: #c96812;
}
.oripashop-price-card__value {
	margin: 0;
	font-size: 1.5rem;
	font-weight: 700;
	line-height: 1.3;
	color: #f58220;
	word-break: break-word;
}
.oripashop-trend-grid {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 12px;
	margin-bottom: 14px;
}
.oripashop-trend-card {
	padding: 1.125rem 0.75rem;
	text-align: center;
	background: #fff;
	border-radius: 16px;
	box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}
.oripashop-trend-card__label {
	margin: 0 0 0.4rem;
	font-size: 0.75rem;
	font-weight: 700;
	color: #666;
}
.oripashop-trend-card__value {
	margin: 0;
	font-size: 1.25rem;
	font-weight: 700;
	line-height: 1.3;
	word-break: break-word;
}
.oripashop-trend-card__value--orange {
	color: #f58220;
}
.oripashop-trend-card__value--blue {
	color: #007aff;
}
.oripashop-review-card {
	margin-bottom: 14px;
	padding: 1rem;
	background: #fff;
	border-radius: 16px;
	box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}
.oripashop-review-card__title {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	margin: 0 0 0.75rem;
	font-size: 0.9375rem;
	font-weight: 700;
	color: #333;
}
.oripashop-review-card__icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 24px;
	height: 24px;
	font-size: 12px;
	color: #fff;
	border-radius: 50%;
}
.oripashop-review-card__icon--plus {
	background: #2e9fff;
}
.oripashop-review-card__icon--minus {
	background: #9aa3ad;
}
.oripashop-review-card__icon--user {
	background: #f58220;
}
.oripashop-review-card__list {
	display: grid;
	gap: 8px;
	margin: 0;
	padding: 0;
	list-style: none;
}
.oripashop-review-card__item {
	padding: 0.625rem 0.875rem;
	font-size: 0.875rem;
	line-height: 1.6;
	color: #333;
	border-radius: 10px;
}
.oripashop-review-card__item--blue {
	background: #e8f4ff;
}
.oripashop-review-card__item--gray {
	background: #f2f4f6;
}
.oripashop-review-card__tags {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
}
.oripashop-review-tag {
	display: inline-block;
	padding: 0.35rem 0.875rem;
	font-size: 0.8125rem;
	font-weight: 700;
	color: #f58220;
	background: #fff3e8;
	border-radius: 999px;
}

/* ショップの機能・サービス（2列カードグリッド） */
.oripashop-service-grid {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 16px;
}
.oripashop-service-card {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 0.5rem;
	padding: 1.25rem 0.75rem;
	text-align: center;
	background: #fff;
	border-radius: 18px;
	box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}
.oripashop-service-card__icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 48px;
	height: 48px;
	background: #e8f2ff;
	border-radius: 14px;
}
.oripashop-service-card__icon i {
	font-size: 26px;
	color: #007aff;
}
.oripashop-service-card__label {
	margin: 0;
	font-size: 0.8125rem;
	font-weight: 700;
	line-height: 1.4;
	color: #444;
}
.oripashop-service-card__value {
	margin: 0;
	font-size: 0.9375rem;
	font-weight: 700;
	line-height: 1.4;
	color: #f58220;
	word-break: break-word;
}

/* ご利用ガイド */
.oripashop-guide-card {
	padding: 1rem;
	background: #fff;
	border: 1px solid #ffe2c7;
	border-radius: 16px;
	box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.oripashop-guide-card__lead {
	display: inline-flex;
	align-items: center;
	gap: 0.4rem;
	margin: 0 0 0.875rem;
	padding: 0.3rem 0.75rem;
	font-size: 0.75rem;
	font-weight: 700;
	color: #f58220;
	background: #fff3e8;
	border-radius: 999px;
}
.oripashop-guide-card__lead i {
	font-size: 0.875rem;
}
.oripashop-guide-list {
	display: grid;
	gap: 0;
	margin: 0;
	padding: 0;
	list-style: none;
}
.oripashop-guide-list__item {
	display: flex;
	gap: 0.75rem;
	align-items: center;
	padding: 0.75rem 0.25rem;
	border-bottom: 1px solid #f3f3f3;
}
.oripashop-guide-list__item:last-child {
	border-bottom: none;
	padding-bottom: 0.25rem;
}
.oripashop-guide-list__icon {
	display: flex;
	align-items: center;
	justify-content: center;
	flex: 0 0 36px;
	width: 36px;
	height: 36px;
	background: #e8f2ff;
	border-radius: 10px;
}
.oripashop-guide-list__icon i {
	font-size: 16px;
	color: #007aff;
}
.oripashop-guide-list__content {
	flex: 1 1 auto;
	min-width: 0;
}
.oripashop-guide-list__label {
	margin: 0 0 0.15rem;
	font-size: 0.6875rem;
	font-weight: 600;
	color: #999;
}
.oripashop-guide-list__value {
	margin: 0;
	font-size: 0.875rem;
	font-weight: 700;
	line-height: 1.5;
	color: #222;
	word-break: break-word;
}
.oripashop-guide-list__value--highlight {
	font-size: 0.9375rem;
	color: #f58220;
}

/* CTA */
.oripashop-cta {
	margin: 1.5rem 0;
	text-align: center;
}
.oripashop-cta__btn {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.4rem;
	width: 100%;
	padding: 1rem 1.25rem;
	font-size: 1rem;
	font-weight: 700;
	text-decoration: none;
	color: #fff;
	background: linear-gradient(90deg, #f58220 0%, #3b9eff 100%);
	border-radius: 12px;
	box-shadow: 0 4px 14px rgba(245, 130, 32, 0.35);
	transition: opacity 0.2s;
}
.oripashop-cta__btn:hover {
	opacity: 0.9;
	color: #fff;
}
.oripashop-cta__note {
	margin: 0.5rem 0 0;
	font-size: 0.75rem;
	color: #999;
}

/* 商品一覧 */
.oripashop-section--products .oripashop-section__label {
	min-width: 0;
	line-height: 1.4;
}
.oripashop-section--products .oripa-cards {
	grid-template-columns: 1fr;
	gap: 1rem;
	margin: 0;
}
.oripashop-section--products .oripa-card {
	max-width: none;
	margin: 0;
}
.oripa-card-slot.is-hidden,
.oripa-card-slot.is-filtered-out {
	display: none;
}
.oripashop-tcg-filters {
	display: flex;
	flex-wrap: wrap;
	gap: 0.5rem;
	margin: 0 0 0.75rem;
}
.oripashop-tcg-filters__btn {
	flex: 1 1 calc(50% - 0.5rem);
	min-height: 40px;
	padding: 0.45rem 0.5rem;
	font-size: 0.8125rem;
	font-weight: 700;
	line-height: 1.3;
	color: #333;
	background: #fff;
	border: 1px solid #e6e6e6;
	border-radius: 999px;
	cursor: pointer;
}
.oripashop-tcg-filters__btn.is-active {
	color: #fff;
	background: #f58220;
	border-color: #f58220;
}
.oripashop-more {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	margin-top: 1rem;
	padding: 0.9rem 1.25rem;
	font-size: 0.9375rem;
	font-weight: 700;
	line-height: 1.4;
	color: #f58220;
	background: #fff;
	border: 2px solid #f58220;
	border-radius: 12px;
	cursor: pointer;
}
.oripashop-more:hover {
	background: #fff7f0;
}
.oripashop-app-search {
	box-sizing: border-box;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.4rem;
	width: 100%;
	min-height: 52px;
	margin-top: 1rem;
	padding: 0.9rem 1.25rem;
	font-size: 0.9375rem;
	font-weight: 700;
	line-height: 1.4;
	color: #fff;
	text-decoration: none;
	background: #0069E1;
	border-radius: 12px;
	box-shadow: 0 4px 0 #004FDE;
}
.oripashop-app-search:hover {
	color: #fff;
}
.oripashop-app-search:active {
	transform: translateY(3px);
	box-shadow: 0 1px 0 #004FDE;
}
.oripashop-more:disabled {
	opacity: 0.6;
	cursor: wait;
}
</style>
	<?php
endwhile;

get_footer();
