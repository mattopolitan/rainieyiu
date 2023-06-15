<?php

add_action( 'admin_menu', 'mega_store_woocommerce_getting_started' );
function mega_store_woocommerce_getting_started() {
	add_theme_page( esc_html__('Get Started', 'mega-store-woocommerce'), esc_html__('Get Started', 'mega-store-woocommerce'), 'edit_theme_options', 'mega-store-woocommerce-guide-page', 'mega_store_woocommerce_test_guide');
}

function mega_store_woocommerce_admin_enqueue_scripts() {
	wp_enqueue_style( 'mega-store-woocommerce-admin-style', esc_url( get_template_directory_uri() ).'/css/main.css' );
}
add_action( 'admin_enqueue_scripts', 'mega_store_woocommerce_admin_enqueue_scripts' );

if ( ! defined( 'MEGA_STORE_WOOCOMMERCE_DOCS_FREE' ) ) {
define('MEGA_STORE_WOOCOMMERCE_DOCS_FREE',__('https://www.misbahwp.com/docs/mega-store-woocommerce-free-docs/','mega-store-woocommerce'));
}
if ( ! defined( 'MEGA_STORE_WOOCOMMERCE_DOCS_PRO' ) ) {
define('MEGA_STORE_WOOCOMMERCE_DOCS_PRO',__('https://www.misbahwp.com/docs/mega-store-woocommerce-pro-docs','mega-store-woocommerce'));
}
if ( ! defined( 'MEGA_STORE_WOOCOMMERCE_BUY_NOW' ) ) {
define('MEGA_STORE_WOOCOMMERCE_BUY_NOW',__('https://www.misbahwp.com/themes/megastore-wordpress-theme/','mega-store-woocommerce'));
}
if ( ! defined( 'MEGA_STORE_WOOCOMMERCE_SUPPORT_FREE' ) ) {
define('MEGA_STORE_WOOCOMMERCE_SUPPORT_FREE',__('https://wordpress.org/support/theme/mega-store-woocommerce','mega-store-woocommerce'));
}
if ( ! defined( 'MEGA_STORE_WOOCOMMERCE_REVIEW_FREE' ) ) {
define('MEGA_STORE_WOOCOMMERCE_REVIEW_FREE',__('https://wordpress.org/support/theme/mega-store-woocommerce/reviews/#new-post','mega-store-woocommerce'));
}
if ( ! defined( 'MEGA_STORE_WOOCOMMERCE_DEMO_PRO' ) ) {
define('MEGA_STORE_WOOCOMMERCE_DEMO_PRO',__('https://www.misbahwp.com/demo/mega-store-woocommerce/','mega-store-woocommerce'));
}

function mega_store_woocommerce_test_guide() { ?>
	<?php $mega_store_woocommerce_theme = wp_get_theme(); ?>

	<div class="wrap" id="main-page">
		<div id="lefty">
			<div id="admin_links">
				<a href="<?php echo esc_url( MEGA_STORE_WOOCOMMERCE_DOCS_FREE ); ?>" target="_blank" class="blue-button-1"><?php esc_html_e( 'Documentation', 'mega-store-woocommerce' ) ?></a>
				<a href="<?php echo esc_url( admin_url('customize.php') ); ?>" id="customizer" target="_blank"><?php esc_html_e( 'Customize', 'mega-store-woocommerce' ); ?> </a>
				<a class="blue-button-1" href="<?php echo esc_url( MEGA_STORE_WOOCOMMERCE_SUPPORT_FREE ); ?>" target="_blank" class="btn3"><?php esc_html_e( 'Support', 'mega-store-woocommerce' ) ?></a>
				<a class="blue-button-2" href="<?php echo esc_url( MEGA_STORE_WOOCOMMERCE_REVIEW_FREE ); ?>" target="_blank" class="btn4"><?php esc_html_e( 'Review', 'mega-store-woocommerce' ) ?></a>
			</div>
			<div id="description">
				<h3><?php esc_html_e('Welcome! Thank you for choosing ','mega-store-woocommerce'); ?><?php echo esc_html( $mega_store_woocommerce_theme ); ?>  <span><?php esc_html_e('Version: ', 'mega-store-woocommerce'); ?><?php echo esc_html($mega_store_woocommerce_theme['Version']);?></span></h3>
				<img class="img_responsive" style="width:100%;" src="<?php echo esc_url( get_template_directory_uri() ); ?>/screenshot.png">
				<div id="description-inside">
					<?php
						$mega_store_woocommerce_theme = wp_get_theme();
						echo wp_kses_post( apply_filters( 'misbah_theme_description', esc_html( $mega_store_woocommerce_theme->get( 'Description' ) ) ) );
					?>
				</div>
			</div>
		</div>

		<div id="righty">
			<div class="postbox donate">
				<div class="d-table">
			    <ul class="d-column">
			      <li class="feature"><?php esc_html_e('Features','mega-store-woocommerce'); ?></li>
			      <li class="free"><?php esc_html_e('Pro','mega-store-woocommerce'); ?></li>
			      <li class="plus"><?php esc_html_e('Free','mega-store-woocommerce'); ?></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('24hrs Priority Support','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-yes"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Kirki Framework','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-yes"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Advance Posttype','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-no"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('One Click Demo Import','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-no"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Section Reordering','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-no"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Enable / Disable Option','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-yes"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Multiple Sections','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-no"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Advance Color Pallete','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-no"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Advance Widgets','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-yes"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Page Templates','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-no"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Advance Typography','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-no"></span></li>
			    </ul>
			    <ul class="d-row">
			      <li class="points"><?php esc_html_e('Section Background Image / Color ','mega-store-woocommerce'); ?></li>
			      <li class="right"><span class="dashicons dashicons-yes"></span></li>
			      <li class="wrong"><span class="dashicons dashicons-no"></span></li>
			    </ul>
	  		</div>
				<h3 class="hndle"><?php esc_html_e( 'Upgrade to Premium', 'mega-store-woocommerce' ); ?></h3>
				<div class="inside">
					<p><?php esc_html_e('Discover upgraded pro features with premium version click to upgrade.','mega-store-woocommerce'); ?></p>
					<div id="admin_pro_links">
						<a class="blue-button-2" href="<?php echo esc_url( MEGA_STORE_WOOCOMMERCE_BUY_NOW ); ?>" target="_blank"><?php esc_html_e( 'Go Pro', 'mega-store-woocommerce' ); ?></a>
						<a class="blue-button-1" href="<?php echo esc_url( MEGA_STORE_WOOCOMMERCE_DEMO_PRO ); ?>" target="_blank"><?php esc_html_e( 'Live Demo', 'mega-store-woocommerce' ) ?></a>
						<a class="blue-button-2" href="<?php echo esc_url( MEGA_STORE_WOOCOMMERCE_DOCS_PRO ); ?>" target="_blank"><?php esc_html_e( 'Pro Docs', 'mega-store-woocommerce' ) ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php } ?>
