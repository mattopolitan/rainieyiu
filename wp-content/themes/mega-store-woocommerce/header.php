<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>

<meta http-equiv="Content-Type" content="<?php echo esc_attr(get_bloginfo('html_type')); ?>; charset=<?php echo esc_attr(get_bloginfo('charset')); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.2, user-scalable=yes" />

<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<?php
	if ( function_exists( 'wp_body_open' ) )
	{
		wp_body_open();
	}else{
		do_action('wp_body_open');
	}
?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'mega-store-woocommerce' ); ?></a>

<div class="topheader py-2">
	<div class="container">
		<div class="row">
			<div class="col-lg-3 col-md-6 col-sm-6 align-self-center">
				<?php if ( get_theme_mod('mega_store_woocommerce_free_delivery_link') || get_theme_mod('mega_store_woocommerce_free_delivery_text') ) : ?>
					<a href="<?php echo esc_url( get_theme_mod('mega_store_woocommerce_free_delivery_link' ) ); ?>" class="myacunt-url mr-2"><i class="fas fa-truck mr-2"></i><?php echo esc_html( get_theme_mod('mega_store_woocommerce_free_delivery_text' ) ); ?></a>
				<?php endif; ?>
				<?php if ( get_theme_mod('mega_store_woocommerce_return_policy_link') || get_theme_mod('mega_store_woocommerce_return_policy_text') ) : ?>
					<a href="<?php echo esc_url( get_theme_mod('mega_store_woocommerce_return_policy_text' ) ); ?>" class="myacunt-url"><?php echo esc_html( get_theme_mod('mega_store_woocommerce_return_policy_text' ) ); ?></a>
				<?php endif; ?>
			</div>
			<div class="col-lg-4 col-md-6 col-sm-6 align-self-center">
				<?php $mega_store_woocommerce_settings = get_theme_mod( 'mega_store_woocommerce_social_links_settings' ); ?>
				<div class="social-links text-center text-md-left py-2 py-md-0">
					<?php if ( is_array($mega_store_woocommerce_settings) || is_object($mega_store_woocommerce_settings) ){ ?>
						<span class="mr-2"><?php esc_html_e('FOllOW US','mega-store-woocommerce'); ?></span>
				    	<?php foreach( $mega_store_woocommerce_settings as $mega_store_woocommerce_setting ) { ?>
					        <a href="<?php echo esc_url( $mega_store_woocommerce_setting['link_url'] ); ?>">
					            <i class="<?php echo esc_attr( $mega_store_woocommerce_setting['link_text'] ); ?> mr-3"></i>
					        </a>
				    	<?php } ?>
					<?php } ?>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 col-sm-6 align-self-center">
				<?php if ( get_theme_mod('mega_store_woocommerce_phone_number') ) : ?>
					<span class="mr-3 phone-color"><i class="fas fa-mobile mr-2"></i><a href="callto:<?php echo esc_html(get_theme_mod('mega_store_woocommerce_phone_number','')); ?>"><?php echo esc_html(get_theme_mod('mega_store_woocommerce_phone_number','')); ?></a></span>
				<?php endif; ?>

				<?php if ( get_theme_mod('mega_store_woocommerce_myaccount_link') || get_theme_mod('mega_store_woocommerce_myaccount_text') ) : ?>
					<a href="<?php echo esc_url( get_theme_mod('mega_store_woocommerce_myaccount_link' ) ); ?>" class="myacunt-url"><?php esc_html_e('MY ACCOUNT','mega-store-woocommerce'); ?></a>
				<?php endif; ?>
			</div>
			<div class="col-lg-2 col-md-6 col-sm-6 align-self-center translation-box text-md-right">
				<?php if ( get_theme_mod('mega_store_woocommerce_header_google_translation') ) : ?>
					<?php echo do_shortcode('[google-translator]'); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div class="<?php if( get_theme_mod( 'mega_store_woocommerce_sticky_header', false) != '') { ?>sticky-header<?php } else { ?>close-sticky main-menus<?php } ?>">
	<header id="site-navigation" class="header text-center text-md-left py-2">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-3 align-self-center">
					<div class="logo">
			    		<div class="logo-image">
			    			<?php echo esc_url( the_custom_logo() ); ?>
				    	</div>
				    	<div class="logo-content">
					    	<?php
					    		if ( get_theme_mod('mega_store_woocommerce_display_header_title', true) == true ) :
						      		echo '<a href="' . esc_url(home_url('/')) . '" title="' . esc_attr(get_bloginfo('name')) . '">';
						      			echo esc_attr(get_bloginfo('name'));
						      		echo '</a>';
						      	endif;

						      	if ( get_theme_mod('mega_store_woocommerce_display_header_text', false) == true ) :
					      			echo '<span>'. esc_attr(get_bloginfo('description')) . '</span>';
					      		endif;
				    		?>
						</div>
					</div>
			   	</div>
				<div class="col-lg-7 col-md-7 col-sm-7 align-self-center">
					<?php if(has_nav_menu('main-menu')){ ?>
						<button class="menu-toggle my-2 py-2 px-3" aria-controls="top-menu" aria-expanded="false" type="button">
							<span aria-hidden="true"><?php esc_html_e( 'Menu', 'mega-store-woocommerce' ); ?></span>
						</button>
						<nav id="main-menu" class="close-panal">
							<?php
								wp_nav_menu( array(
									'theme_location' => 'main-menu',
									'container' => 'false'
								));
							?>
							<button class="close-menu my-2 p-2" type="button">
								<span aria-hidden="true"><i class="fa fa-times"></i></span>
							</button>
						</nav>
					<?php }?>
				</div>
				<div class="col-lg-1 col-md-1 col-sm-1 col-6 align-self-center">
					<div class="header-search text-center text-md-right py-3 py-md-0">
			        	<?php if ( get_theme_mod('mega_store_woocommerce_search_box_enable', true) == true ) : ?>
			                <a class="open-search-form" href="#search-form"><i class="fa fa-search" aria-hidden="true"></i></a>
			                <div class="search-form"><?php get_search_form();?></div>
			        	<?php endif; ?>
			        </div>
		       	</div>
				<div class="col-lg-1 col-md-1 col-sm-1 col-6 align-self-center text-center text-md-right">
					<?php if ( get_theme_mod('mega_store_woocommerce_myaccount_link') ) : ?>
						<a href="<?php echo esc_url( get_theme_mod('mega_store_woocommerce_myaccount_link' ) ); ?>" class=""><i class="far fa-user mr-2"></i></a>
					<?php endif; ?>
					<?php if ( get_theme_mod('mega_store_woocommerce_cart_box_enable', true) == true ) : ?>
						<?php if ( class_exists( 'woocommerce' ) ) {?>
							<a class="cart-customlocation" href="<?php if(function_exists('wc_get_cart_url')){ echo esc_url(wc_get_cart_url()); } ?>" title="<?php esc_attr_e( 'View Shopping Cart','mega-store-woocommerce' ); ?>"><i class="fas fa-shopping-cart"></i><span class="cart-item-box"><?php echo esc_html(wp_kses_data( WC()->cart->get_cart_contents_count() ));?></span></a>
						<?php }?>
					<?php endif; ?>
				</div>
		   	</div>
		</div>
	</header>
</div>
