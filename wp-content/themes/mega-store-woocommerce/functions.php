<?php

/*-----------------------------------------------------------------------------------*/
/* Enqueue script and styles */
/*-----------------------------------------------------------------------------------*/

function mega_store_woocommerce_enqueue_google_fonts() {

	require_once get_theme_file_path( 'core/includes/wptt-webfont-loader.php' );

	wp_enqueue_style( 'google-fonts-poppins', 'https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap' );
}
add_action( 'wp_enqueue_scripts', 'mega_store_woocommerce_enqueue_google_fonts' );

if (!function_exists('mega_store_woocommerce_enqueue_scripts')) {

	function mega_store_woocommerce_enqueue_scripts() {

		wp_enqueue_style(
			'bootstrap-css',
			get_template_directory_uri() . '/css/bootstrap.css',
			array(),'4.5.0'
		);

		wp_enqueue_style(
			'fontawesome-css',
			get_template_directory_uri() . '/css/fontawesome-all.css',
			array(),'4.5.0'
		);

		wp_enqueue_style(
			'owl.carousel-css',
			get_template_directory_uri() . '/css/owl.carousel.css',
			array(),'2.3.4'
		);

		wp_enqueue_style('mega-store-woocommerce-style', get_stylesheet_uri(), array() );

		wp_style_add_data('mega-store-woocommerce-style', 'rtl', 'replace');

		wp_enqueue_style(
			'mega-store-woocommerce-media-css',
			get_template_directory_uri() . '/css/media.css',
			array(),'2.3.4'
		);

		wp_enqueue_style(
			'mega-store-woocommerce-woocommerce-css',
			get_template_directory_uri() . '/css/woocommerce.css',
			array(),'2.3.4'
		);

		wp_enqueue_script(
			'mega-store-woocommerce-navigation',
			esc_url( get_template_directory_uri() ) . '/js/navigation.js',
			FALSE,
			'1.0',
			TRUE
		);

		wp_enqueue_script(
			'owl.carousel-js',
			get_template_directory_uri() . '/js/owl.carousel.js',
			array('jquery'),
			'2.3.4',
			TRUE
		);

		wp_enqueue_script(
			'mega-store-woocommerce-script',
			get_template_directory_uri() . '/js/script.js',
			array('jquery'),
			'1.0',
			TRUE
		);

		if ( is_singular() ) wp_enqueue_script( 'comment-reply' );

		$mega_store_woocommerce_css = '';

		if ( get_header_image() ) :

			$mega_store_woocommerce_css .=  '
				#site-navigation,.page-template-frontpage #site-navigation{
					background-image: url('.esc_url(get_header_image()).');
					-webkit-background-size: cover !important;
					-moz-background-size: cover !important;
					-o-background-size: cover !important;
					background-size: cover !important;
				}';

		endif;

		wp_add_inline_style( 'mega-store-woocommerce-style', $mega_store_woocommerce_css );

		// Theme Customize CSS.
		require get_template_directory(). '/core/includes/inline.php';
		wp_add_inline_style( 'mega-store-woocommerce-style',$mega_store_woocommerce_custom_css );

	}

	add_action( 'wp_enqueue_scripts', 'mega_store_woocommerce_enqueue_scripts' );

}

/*-----------------------------------------------------------------------------------*/
/* Setup theme */
/*-----------------------------------------------------------------------------------*/

if (!function_exists('mega_store_woocommerce_after_setup_theme')) {

	function mega_store_woocommerce_after_setup_theme() {

		if ( ! isset( $mega_store_woocommerce_content_width ) ) $mega_store_woocommerce_content_width = 900;

		register_nav_menus( array(
			'main-menu' => esc_html__( 'Main menu', 'mega-store-woocommerce' ),
		));

		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'align-wide' );
		add_theme_support('title-tag');
		add_theme_support('automatic-feed-links');
		add_theme_support( 'wp-block-styles' );
		add_theme_support('post-thumbnails');
		add_theme_support( 'custom-background', array(
		  'default-color' => 'f3f3f3'
		));

		add_theme_support( 'custom-logo', array(
			'height'      => 70,
			'width'       => 70,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		add_theme_support( 'custom-header', array(
			'header-text' => false,
			'width' => 1920,
			'height' => 100,
			'flex-height' => true,
			'flex-width' => true,
		));

		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		add_editor_style( array( '/css/editor-style.css' ) );
	}

	add_action( 'after_setup_theme', 'mega_store_woocommerce_after_setup_theme', 999 );

}

require get_template_directory() .'/core/includes/main.php';
require get_template_directory() .'/core/includes/tgm.php';
require get_template_directory() . '/core/includes/customizer.php';
load_template( trailingslashit( get_template_directory() ) . '/core/includes/class-upgrade-pro.php' );

/*-----------------------------------------------------------------------------------*/
/* Enqueue theme logo style */
/*-----------------------------------------------------------------------------------*/
function mega_store_woocommerce_logo_resizer() {

    $mega_store_woocommerce_theme_logo_size_css = '';
    $mega_store_woocommerce_logo_resizer = get_theme_mod('mega_store_woocommerce_logo_resizer');

	$mega_store_woocommerce_theme_logo_size_css = '
		.custom-logo{
			height: '.esc_attr($mega_store_woocommerce_logo_resizer).'px !important;
			width: '.esc_attr($mega_store_woocommerce_logo_resizer).'px !important;
		}
	';
    wp_add_inline_style( 'mega-store-woocommerce-style',$mega_store_woocommerce_theme_logo_size_css );

}
add_action( 'wp_enqueue_scripts', 'mega_store_woocommerce_logo_resizer' );

/*-----------------------------------------------------------------------------------*/
/* Enqueue Global color style */
/*-----------------------------------------------------------------------------------*/
function mega_store_woocommerce_global_color() {

    $theme_color_css = '';
    $mega_store_woocommerce_copyright_bg = get_theme_mod('mega_store_woocommerce_copyright_bg');

	$theme_color_css = '
    .copyright {
			background: '.esc_attr($mega_store_woocommerce_copyright_bg).';
		}
	';
    wp_add_inline_style( 'mega-store-woocommerce-style',$theme_color_css );
    wp_add_inline_style( 'mega-store-woocommerce-woocommerce-css',$theme_color_css );

}
add_action( 'wp_enqueue_scripts', 'mega_store_woocommerce_global_color' );


/*-----------------------------------------------------------------------------------*/
/* Get post comments */
/*-----------------------------------------------------------------------------------*/

if (!function_exists('mega_store_woocommerce_comment')) :
    /**
     * Template for comments and pingbacks.
     *
     * Used as a callback by wp_list_comments() for displaying the comments.
     */
    function mega_store_woocommerce_comment($comment, $mega_store_woocommerce_args, $depth){

        if ('pingback' == $comment->comment_type || 'trackback' == $comment->comment_type) : ?>

            <li id="comment-<?php comment_ID(); ?>" <?php comment_class('media'); ?>>
            <div class="comment-body">
                <?php esc_html_e('Pingback:', 'mega-store-woocommerce');
                comment_author_link(); ?><?php edit_comment_link(__('Edit', 'mega-store-woocommerce'), '<span class="edit-link">', '</span>'); ?>
            </div>

        <?php else : ?>

        <li id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($mega_store_woocommerce_args['has_children']) ? '' : 'parent'); ?>>
            <article id="div-comment-<?php comment_ID(); ?>" class="comment-body media mb-4">
                <a class="pull-left" href="#">
                    <?php if (0 != $mega_store_woocommerce_args['avatar_size']) echo get_avatar($comment, $mega_store_woocommerce_args['avatar_size']); ?>
                </a>
                <div class="media-body">
                    <div class="media-body-wrap card">
                        <div class="card-header">
                            <h5 class="mt-0"><?php /* translators: %s: author */ printf('<cite class="fn">%s</cite>', get_comment_author_link() ); ?></h5>
                            <div class="comment-meta">
                                <a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>">
                                    <time datetime="<?php comment_time('c'); ?>">
                                        <?php /* translators: %s: Date */ printf( esc_attr('%1$s at %2$s', '1: date, 2: time', 'mega-store-woocommerce'), esc_attr( get_comment_date() ), esc_attr( get_comment_time() ) ); ?>
                                    </time>
                                </a>
                                <?php edit_comment_link( __( 'Edit', 'mega-store-woocommerce' ), '<span class="edit-link">', '</span>' ); ?>
                            </div>
                        </div>

                        <?php if ('0' == $comment->comment_approved) : ?>
                            <p class="comment-awaiting-moderation"><?php esc_html_e('Your comment is awaiting moderation.', 'mega-store-woocommerce'); ?></p>
                        <?php endif; ?>

                        <div class="comment-content card-block">
                            <?php comment_text(); ?>
                        </div>

                        <?php comment_reply_link(
                            array_merge(
                                $mega_store_woocommerce_args, array(
                                    'add_below' => 'div-comment',
                                    'depth' => $depth,
                                    'max_depth' => $mega_store_woocommerce_args['max_depth'],
                                    'before' => '<footer class="reply comment-reply card-footer">',
                                    'after' => '</footer><!-- .reply -->'
                                )
                            )
                        ); ?>
                    </div>
                </div>
            </article>

            <?php
        endif;
    }
endif; // ends check for mega_store_woocommerce_comment()

if (!function_exists('mega_store_woocommerce_widgets_init')) {

	function mega_store_woocommerce_widgets_init() {

		register_sidebar(array(

			'name' => esc_html__('Sidebar','mega-store-woocommerce'),
			'id'   => 'mega-store-woocommerce-sidebar',
			'description'   => esc_html__('This sidebar will be shown next to the content.', 'mega-store-woocommerce'),
			'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="title">',
			'after_title'   => '</h4>'

		));

		register_sidebar(array(

			'name' => esc_html__('Footer sidebar','mega-store-woocommerce'),
			'id'   => 'mega-store-woocommerce-footer-sidebar',
			'description'   => esc_html__('This sidebar will be shown next at the bottom of your content.', 'mega-store-woocommerce'),
			'before_widget' => '<div id="%1$s" class="col-lg-3 col-md-3 %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="title">',
			'after_title'   => '</h4>'

		));

	}

	add_action( 'widgets_init', 'mega_store_woocommerce_widgets_init' );

}

function mega_store_woocommerce_get_categories_select() {
	$mega_store_woocommerce_teh_cats = get_categories();
	$results = array();
	$mega_store_woocommerce_count = count($mega_store_woocommerce_teh_cats);
	for ($mega_store_woocommerce_i=0; $mega_store_woocommerce_i < $mega_store_woocommerce_count; $mega_store_woocommerce_i++) {
	if (isset($mega_store_woocommerce_teh_cats[$mega_store_woocommerce_i]))
  		$results[$mega_store_woocommerce_teh_cats[$mega_store_woocommerce_i]->slug] = $mega_store_woocommerce_teh_cats[$mega_store_woocommerce_i]->name;
	else
  		$mega_store_woocommerce_count++;
	}
	return $results;
}

function mega_store_woocommerce_sanitize_select( $mega_store_woocommerce_input, $setting ) {
	// Ensure input is a slug
	$mega_store_woocommerce_input = sanitize_key( $mega_store_woocommerce_input );

	// Get list of choices from the control
	// associated with the setting
	$choices = $setting->manager->get_control( $setting->id )->choices;

	// If the input is a valid key, return it;
	// otherwise, return the default
	return ( array_key_exists( $mega_store_woocommerce_input, $choices ) ? $mega_store_woocommerce_input : $setting->default );
}

/**
 * Change number or products per row to 3
 */
add_filter('loop_shop_columns', 'mega_store_woocommerce_loop_columns', 999);
if (!function_exists('mega_store_woocommerce_loop_columns')) {
	function mega_store_woocommerce_loop_columns() {
		return 3; // 3 products per row
	}
}

//redirect
Function mega_store_woocommerce_notice(){
    global $pagenow;
    if ( is_admin() && ('themes.php' == $pagenow) && isset( $_GET['activated'] ) ) {
   		wp_safe_redirect( admin_url("themes.php?page=mega-store-woocommerce-guide-page") );
   	}
}
add_action('after_setup_theme', 'mega_store_woocommerce_notice');

?>
