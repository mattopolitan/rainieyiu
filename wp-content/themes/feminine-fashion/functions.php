<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * After setup theme hook
 */
function feminine_fashion_theme_setup(){

    /**
     * Add Custom Images sizes.
    */ 
    add_image_size( 'chic-lite-blog-three', 420, 502, true );   

    /*
     * Make child theme available for translation.
     * Translations can be filed in the /languages/ directory.
     */
    load_child_theme_textdomain( 'feminine-fashion', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'feminine_fashion_theme_setup', 100 );

/**
 * Load assets.
 */
function feminine_fashion_enqueue_styles() {
    $my_theme = wp_get_theme();
    $version = $my_theme['Version'];
    
    wp_enqueue_style( 'chic-lite', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'feminine-fashion', get_stylesheet_directory_uri() . '/style.css', array( 'chic-lite' ), $version );
    wp_enqueue_script( 'feminine-fashion', get_stylesheet_directory_uri() . '/js/custom.js', array( 'jquery' ), $version, true );

    $array = array( 
        'rtl'           => is_rtl(),
        'auto'          => (bool) get_theme_mod( 'slider_auto', true ),
        'loop'          => (bool) get_theme_mod( 'slider_loop', true ),
    );
    
    wp_localize_script( 'feminine-fashion', 'feminine_fashion_data', $array );

}
add_action( 'wp_enqueue_scripts', 'feminine_fashion_enqueue_styles', 10 );

function feminine_fashion_remove_parent_filters(){
    remove_action( 'customize_register', 'chic_lite_customize_register_appearance' );
    remove_action( 'customize_register', 'chic_lite_customize_register_color' );
    remove_action( 'wp_head', 'chic_lite_dynamic_css', 99 );
}
add_action( 'init', 'feminine_fashion_remove_parent_filters' );

/**
 * Layout Settings
 */
function feminine_fashion_customizer_register( $wp_customize ){

    $wp_customize->add_section( 'theme_info', 
        array(
            'title'    => __( 'Information Links', 'feminine-fashion' ),
            'priority' => 6,
        )
    );

    /** Important Links */
    $wp_customize->add_setting( 'theme_info_theme',
        array(
            'default' => '',
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    
    $theme_info = '<p>';
    $theme_info .= sprintf( __( 'Demo Link: %1$sClick here.%2$s', 'feminine-fashion' ),  '<a href="' . esc_url( 'https://rarathemes.com/previews/?theme=feminine-fashion' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p><p>';
    $theme_info .= sprintf( __( 'Documentation Link: %1$sClick here.%2$s', 'feminine-fashion' ),  '<a href="' . esc_url( 'https://docs.rarathemes.com/docs/feminine-fashion/' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p>';

    $wp_customize->add_control( new Chic_Lite_Note_Control( $wp_customize,
        'theme_info_theme', 
            array(
                'section'     => 'theme_info',
                'description' => $theme_info
            )
        )
    );

    /** Header Layout Settings */
    $wp_customize->add_section(
        'header_layout_section',
        array(
            'priority'  => 5,
            'title'     => __( 'Header Layout', 'feminine-fashion' ),
            'panel'     => 'layout_settings',
        )
    );

    $wp_customize->add_setting( 
        'header_menu_layout', 
        array(
            'default'           => 'nine',
            'sanitize_callback' => 'chic_lite_sanitize_radio'
        ) 
    );

    $wp_customize->add_control(
        new Chic_Lite_Radio_Image_Control(
            $wp_customize,
            'header_menu_layout',
            array(
                'section'     => 'header_layout_section',
                'label'       => __( 'Header Layout', 'feminine-fashion' ),
                'description' => __( 'Choose the layout of the header for your site.', 'feminine-fashion' ),
                'choices'     => array(
                    'two'   => get_stylesheet_directory_uri() . '/images/header/two.jpg',
                    'nine'  => get_stylesheet_directory_uri() . '/images/header/nine.jpg',
                )
            )
        )
    );

    /** Slider Layout Settings */
    $wp_customize->add_section(
        'banner_layout_section',
        array(
            'priority'  => 10,
            'title'     => __( 'Banner Layout', 'feminine-fashion' ),
            'panel'     => 'layout_settings',
        )
    );

    $wp_customize->add_setting( 
        'banner_layout', 
        array(
            'default'           => 'one',
            'sanitize_callback' => 'chic_lite_sanitize_radio'
        ) 
    );

    $wp_customize->add_control(
        new Chic_Lite_Radio_Image_Control(
            $wp_customize,
            'banner_layout',
            array(
                'section'     => 'banner_layout_section',
                'label'       => __( 'Banner Layout', 'feminine-fashion' ),
                'description' => __( 'Choose the layout for the banner for your site.', 'feminine-fashion' ),
                'choices'     => array(
                    'one'   => get_stylesheet_directory_uri() . '/images/banner/one.jpg',
                    'eight' => get_stylesheet_directory_uri() . '/images/banner/eight.jpg',
                )
            )
        )
    );

    $wp_customize->add_section(
        'homepage_layout_section',
        array(
            'priority'  => 10,
            'title'     => __( 'Homepage Layout', 'feminine-fashion' ),
            'panel'     => 'layout_settings',
        )
    );

    $wp_customize->add_setting( 
        'homepage_layout', 
        array(
            'default'           => 'twelve',
            'sanitize_callback' => 'chic_lite_sanitize_radio'
        ) 
    );

    $wp_customize->add_control(
        new Chic_Lite_Radio_Image_Control(
            $wp_customize,
            'homepage_layout',
            array(
                'section'     => 'homepage_layout_section',
                'label'       => __( 'Homepage Layout', 'feminine-fashion' ),
                'description' => __( 'Choose the layout for the homepage of your site.', 'feminine-fashion' ),
                'choices'     => array(
                    'one'    => get_stylesheet_directory_uri() . '/images/home/one.jpg',
                    'twelve' => get_stylesheet_directory_uri() . '/images/home/twelve.jpg',
                )
            )
        )
    );

    /** Appearance Settings */
    $wp_customize->add_panel( 
    'appearance_settings',
        array(
        'priority'    => 50,
        'capability'  => 'edit_theme_options',
        'title'       => __( 'Appearance Settings', 'feminine-fashion' ),
        'description' => __( 'Customize Typography, Header Image & Background Image', 'feminine-fashion' ),
        ) 
    );

    /** Move Background Image section to appearance panel */
    $wp_customize->get_section( 'colors' )->panel              = 'appearance_settings';
    $wp_customize->get_section( 'colors' )->priority           = 10;
    $wp_customize->get_section( 'background_image' )->panel    = 'appearance_settings';
    $wp_customize->get_section( 'background_image' )->priority = 15;

    /** Typography */
    $wp_customize->add_section(
        'typography_settings',
        array(
            'title'    => __( 'Typography', 'feminine-fashion' ),
            'priority' => 20,
            'panel'    => 'appearance_settings',
        )
    );
    
    /** Primary Font */
    $wp_customize->add_setting(
        'primary_font',
        array(
            'default'           => 'Nunito Sans',
            'sanitize_callback' => 'chic_lite_sanitize_select'
        )
    );

    $wp_customize->add_control(
        new Chic_Lite_Select_Control(
            $wp_customize,
            'primary_font',
            array(
                'label'       => __( 'Primary Font', 'feminine-fashion' ),
                'description' => __( 'Primary font of the site.', 'feminine-fashion' ),
                'section'     => 'typography_settings',
                'choices'     => chic_lite_get_all_fonts(), 
            )
        )
    );
    
    /** Secondary Font */
    $wp_customize->add_setting(
        'secondary_font',
        array(
            'default'           => 'Cormorant',
            'sanitize_callback' => 'chic_lite_sanitize_select'
        )
    );

    $wp_customize->add_control(
        new Chic_Lite_Select_Control(
            $wp_customize,
            'secondary_font',
            array(
                'label'       => __( 'Secondary Font', 'feminine-fashion' ),
                'description' => __( 'Secondary font of the site.', 'feminine-fashion' ),
                'section'     => 'typography_settings',
                'choices'     => chic_lite_get_all_fonts(), 
            )
        )
    );
    
    /** Font Size*/
    $wp_customize->add_setting( 
        'font_size', 
        array(
            'default'           => 18,
            'sanitize_callback' => 'chic_lite_sanitize_number_absint'
        ) 
    );
    
    $wp_customize->add_control(
        new Chic_Lite_Slider_Control( 
            $wp_customize,
            'font_size',
            array(
                'section'     => 'typography_settings',
                'label'       => __( 'Font Size', 'feminine-fashion' ),
                'description' => __( 'Change the font size of your site.', 'feminine-fashion' ),
                'choices'     => array(
                    'min'   => 10,
                    'max'   => 50,
                    'step'  => 1,
                )                 
            )
        )
    );

    $wp_customize->add_setting(
        'ed_localgoogle_fonts',
        array(
            'default'           => false,
            'sanitize_callback' => 'chic_lite_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
        new Chic_Lite_Toggle_Control( 
            $wp_customize,
            'ed_localgoogle_fonts',
            array(
                'section'       => 'typography_settings',
                'label'         => __( 'Load Google Fonts Locally', 'feminine-fashion' ),
                'description'   => __( 'Enable to load google fonts from your own server instead from google\'s CDN. This solves privacy concerns with Google\'s CDN and their sometimes less-than-transparent policies.', 'feminine-fashion' )
            )
        )
    );   

    $wp_customize->add_setting(
        'ed_preload_local_fonts',
        array(
            'default'           => false,
            'sanitize_callback' => 'chic_lite_sanitize_checkbox',
        )
    );
    
    $wp_customize->add_control(
        new Chic_Lite_Toggle_Control( 
            $wp_customize,
            'ed_preload_local_fonts',
            array(
                'section'       => 'typography_settings',
                'label'         => __( 'Preload Local Fonts', 'feminine-fashion' ),
                'description'   => __( 'Preloading Google fonts will speed up your website speed.', 'feminine-fashion' ),
                'active_callback' => 'chic_lite_ed_localgoogle_fonts'
            )
        )
    );   

    ob_start(); ?>
        
        <span style="margin-bottom: 5px;display: block;"><?php esc_html_e( 'Click the button to reset the local fonts cache', 'feminine-fashion' ); ?></span>
        
        <input type="button" class="button button-primary chic-lite-flush-local-fonts-button" name="chic-lite-flush-local-fonts-button" value="<?php esc_attr_e( 'Flush Local Font Files', 'feminine-fashion' ); ?>" />
    <?php
    $feminine_fashion_flush_button = ob_get_clean();

    $wp_customize->add_setting(
        'ed_flush_local_fonts',
        array(
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    
    $wp_customize->add_control(
        'ed_flush_local_fonts',
        array(
            'label'         => __( 'Flush Local Fonts Cache', 'feminine-fashion' ),
            'section'       => 'typography_settings',
            'description'   => $feminine_fashion_flush_button,
            'type'          => 'hidden',
            'active_callback' => 'chic_lite_ed_localgoogle_fonts'
        )
    );

    /** Primary Color*/
    $wp_customize->add_setting( 
        'primary_color', 
        array(
            'default'           => '#b67458',
            'sanitize_callback' => 'sanitize_hex_color',
        ) 
    );

    $wp_customize->add_control( 
        new WP_Customize_Color_Control( 
            $wp_customize, 
            'primary_color', 
            array(
                'label'       => __( 'Primary Color', 'feminine-fashion' ),
                'description' => __( 'Primary color of the theme.', 'feminine-fashion' ),
                'section'     => 'colors',
                'priority'    => 5,
            )
        )
    );
}
add_action( 'customize_register', 'feminine_fashion_customizer_register', 40 );

/**
 * Adding body class
 */
function chic_lite_body_classes( $classes ) {
    $editor_options      = get_option( 'classic-editor-replace' );
    $allow_users_options = get_option( 'classic-editor-allow-users' );
    $homepage_layout     = get_theme_mod( 'homepage_layout', 'twelve' );
    
    // Adds a class of hfeed to non-singular pages.
    if ( ! is_singular() ) {
        $classes[] = 'hfeed';
    }
    
    // Adds a class of custom-background-image to sites with a custom background image.
    if ( get_background_image() ) {
        $classes[] = 'custom-background-image';
    }
    
    // Adds a class of custom-background-color to sites with a custom background color.
    if ( get_background_color() != 'ffffff' ) {
        $classes[] = 'custom-background-color';
    }

    if ( ( is_archive() && !( chic_lite_is_woocommerce_activated() && ( is_shop() || is_product_category() || is_product_tag() ) ) && !( chic_lite_is_delicious_recipe_activated() && ( is_post_type_archive( 'recipe' ) || is_tax( 'recipe-course' ) || is_tax( 'recipe-cuisine' ) || is_tax( 'recipe-cooking-method' ) || is_tax( 'recipe-key' ) || is_tax( 'recipe-tag' ) ) ) ) || is_search() ) {
        $classes[] = 'post-layout-one';
    }

    if ( is_home() ) {
        $classes[] = 'post-layout-' . $homepage_layout;
    }

    if ( !chic_lite_is_classic_editor_activated() || ( chic_lite_is_classic_editor_activated() && $editor_options == 'block' ) || ( chic_lite_is_classic_editor_activated() && $allow_users_options == 'allow' && has_blocks() ) ) {
        $classes[] = 'chic-lite-has-blocks';
    }

    if( is_singular( 'post' ) ){        
        $classes[] = 'single-style-four';
    }

    $classes[] = chic_lite_sidebar( true );
    
    return $classes;

}

/**
 * Header Start
*/
function chic_lite_header(){
    $header_layout = get_theme_mod( 'header_menu_layout', 'nine' ); 
    ?>
    <header id="masthead" class="site-header style-<?php echo esc_attr( $header_layout ); ?>" itemscope itemtype="http://schema.org/WPHeader">
        <?php
        if( $header_layout == 'two' ){
            echo '<div class="header-mid"><div class="container">'; 
                chic_lite_site_branding();
            echo '</div></div>';
        } ?>
        <div class="header-bottom">
            <div class="container">         
                <?php 
                    chic_lite_secondary_navigation(); 

                    if( $header_layout == 'nine' ) chic_lite_site_branding();

                    chic_lite_primary_nagivation(); 
                ?>
                <div class="right">
                    <?php 
                        if( chic_lite_social_links( false ) ) {
                            echo '<div class="header-social">';
                            chic_lite_social_links();
                            echo '</div>';
                        }
                        chic_lite_search_cart(); 
                    ?>
                </div><!-- .right -->
            </div>
        </div><!-- .header-bottom -->
    </header>
<?php }

/**
 * Banner Section 
*/
function chic_lite_banner(){
    if( is_front_page() || is_home() ) {
        $ed_banner       = get_theme_mod( 'ed_banner_section', 'slider_banner' );
        $slider_type     = get_theme_mod( 'slider_type', 'latest_posts' ); 
        $slider_cat      = get_theme_mod( 'slider_cat' );
        $posts_per_page  = get_theme_mod( 'no_of_slides', 3 );
        $ed_caption      = get_theme_mod( 'slider_caption', true ); 
        $banner_title    = get_theme_mod( 'banner_title', __( 'Find Your Best Holiday', 'feminine-fashion' ) );
        $banner_subtitle = get_theme_mod( 'banner_subtitle' , __( 'Find great adventure holidays and activities around the planet.', 'feminine-fashion' ) ) ;
        $banner_button   = get_theme_mod( 'banner_button', __( 'Read More', 'feminine-fashion' ) );
        $banner_url      = get_theme_mod( 'banner_url', '#' );  
        $banner_layout   = get_theme_mod( 'banner_layout', 'eight' );    
        
        if( $banner_layout =='one' ){
            $image_size = 'chic-lite-slider-one';
        }else{
            $image_size = 'chic-lite-slider';
        }
        
        if( $ed_banner == 'static_banner' && has_custom_header() ){ ?>
            <div class="site-banner<?php if( has_header_video() ) echo esc_attr( ' video-banner' ); echo ' static-cta-banner'; ?>">
                <?php 
                the_custom_header_markup();
                if( $banner_title || $banner_subtitle || ( $banner_button && $banner_url ) ){ ?>
                    <div class="banner-caption">
                        <div class="container">
                            <?php 
                            if( $banner_title ) echo '<h2 class="banner-title">' . esc_html( $banner_title ) . '</h2>';
                            if( $banner_subtitle ) echo '<div class="banner-desc">' . wp_kses_post( $banner_subtitle ) . '</div>';
                            if( $banner_button && $banner_url ) {
                                $banner_url_new_tab = ( get_theme_mod( 'banner_url_new_tab', false ) ) ? 'target="_blank"' : '';
                                echo '<a href="'.esc_url( $banner_url ).'" class="btn btn-green"' . $banner_url_new_tab . '><span>'.esc_html( $banner_button ).'</span></a>';
                            }
                            ?>
                        </div>
                    </div> <?php 
                }
                ?>
            </div>
            <?php
        }elseif( $ed_banner == 'slider_banner' ){

            if( $slider_type == 'latest_posts' || $slider_type == 'cat' || ( chic_lite_is_delicious_recipe_activated() && $slider_type == 'latest_recipes' ) ){
            
                $args = array(
                    'post_status'         => 'publish',            
                    'ignore_sticky_posts' => true
                );
                
                if( chic_lite_is_delicious_recipe_activated() && $slider_type == 'latest_recipes' ){
                    $args['post_type']      = DELICIOUS_RECIPE_POST_TYPE;
                    $args['posts_per_page'] = $posts_per_page;          
                }elseif( $slider_type === 'cat' && $slider_cat ){
                    $args['post_type']      = 'post';
                    $args['cat']            = $slider_cat; 
                    $args['posts_per_page'] = -1;  
                }else{
                    $args['post_type']      = 'post';
                    $args['posts_per_page'] = $posts_per_page;
                }
                    
                $qry = new WP_Query( $args );
            
                if( $qry->have_posts() ){ ?>

                    <div id="banner_section" class="site-banner style-<?php echo esc_attr( $banner_layout ); ?>">
                        <div class="item-wrap owl-carousel">
                            <?php while( $qry->have_posts() ){ $qry->the_post(); ?>
                                <div class="item">
                                    <?php 
                                    if( has_post_thumbnail() ){
                                        the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );    
                                    }else{ 
                                        chic_lite_get_fallback_svg( $image_size );//fallback
                                    }
                                    if( $ed_caption ){ ?>
                                        <div class="banner-caption">
                                            <div class="container">
                                                <div class="cat-links">
                                                    <?php if( chic_lite_is_delicious_recipe_activated() && DELICIOUS_RECIPE_POST_TYPE == get_post_type() ) {
                                                        chic_lite_recipe_category(); 
                                                    }else{
                                                        chic_lite_category(); 
                                                    } ?>
                                                </div>
                                                <h2 class="banner-title">
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </h2>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>                            
                        </div>
                    </div>
                    <?php
                    wp_reset_postdata(); 
                }
            }           
        }
    }   
}

function chic_lite_post_thumbnail() {
    global $wp_query;
    $image_size  = 'thumbnail';
    $sidebar     = chic_lite_sidebar();
    $ed_crop_blog = get_theme_mod( 'ed_crop_blog', false );
    $homepage_layout = get_theme_mod( 'homepage_layout', 'twelve' );

    if( is_home() ){      

        if( $wp_query->current_post == 0 && $homepage_layout == 'one') :                
            $image_size = ( $sidebar ) ? 'chic-lite-blog-one' : 'chic-lite-slider-one';
        elseif( $homepage_layout == 'twelve' ):
            $image_size = ( $sidebar ) ? 'chic-lite-blog-three' : 'chic-lite-blog-one';
        else :
            $image_size = ( $sidebar ) ? 'chic-lite-blog' : 'chic-lite-featured-four';
        endif;

        if ( has_post_thumbnail() ) {
            echo '<figure class="post-thumbnail"><a href="' . esc_url( get_permalink() ) . '" class="post-thumbnail">';
                if ( ! $ed_crop_blog ) {
                    the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );
                }else{
                    the_post_thumbnail();
                }
            echo '</a></figure>';
        }else{
            echo '<figure class="post-thumbnail"><a href="' . esc_url( get_permalink() ) . '" class="post-thumbnail">';
                chic_lite_get_fallback_svg( $image_size );//fallback
            echo '</a></figure>';
        }

    }elseif( is_archive() || is_search() ){      
        
        if( $wp_query->current_post == 0 ) :                
            $image_size = ( $sidebar ) ? 'chic-lite-blog-one' : 'chic-lite-slider-one';
        else:
            $image_size = ( $sidebar ) ? 'chic-lite-blog' : 'chic-lite-featured-four';
        endif;
        
        if ( has_post_thumbnail() ) {  
            echo '<figure class="post-thumbnail"><a href="' . esc_url( get_permalink() ) . '" class="post-thumbnail">';          
                if ( ! $ed_crop_blog ) {
                    the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) ); 
                }else{
                    the_post_thumbnail();
                }
            echo '</a></figure>';
        }else{
            echo '<figure class="post-thumbnail"><a href="' . esc_url( get_permalink() ) . '" class="post-thumbnail">';
                chic_lite_get_fallback_svg( $image_size );//fallback
            echo '</a></figure>';
        }
    }elseif( is_page() ){
        echo '<figure class="post-thumbnail">';
            $image_size = ( $sidebar ) ? 'chic-lite-sidebar' : 'chic-lite-slider-one';
            the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) ); 
        echo '</figure>';
    }
}

/**
* Entry Header
*/
function chic_lite_entry_first_header(){
    $homepage_layout = get_theme_mod( 'homepage_layout', 'twelve' );
    global $wp_query;

    if ( is_home() && $wp_query->current_post == 0 && $homepage_layout =='one') {
        chic_lite_entry_header_first();
    }

    if ( ( is_archive() || is_search() ) && $wp_query->current_post == 0 ) {
        chic_lite_entry_header_first();
    }

    if ( is_page() ) {
        ?>
        <header class="page-header">
            <h1 class="page-title"><?php the_title(); ?></h1>
        </header> 
        <?php
    }

}

/**
 * Entry Header
*/
function chic_lite_entry_header(){ 
    $homepage_layout = get_theme_mod( 'homepage_layout', 'twelve' );
    global $wp_query;

    if( $wp_query->current_post == 0 && $homepage_layout == 'one' ) return false;
    ?>
    
    <header class="entry-header">
        <?php                                      
            if( 'post' === get_post_type() ){
                chic_lite_category();
            }   

            the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );

            echo '<div class="entry-meta">';
                chic_lite_posted_by();
                chic_lite_posted_on(); 
            echo '</div>'; 
        ?>
    </header> 
    <?php  
}

/**
 * Footer Bottom
*/
function chic_lite_footer_bottom(){ ?>
    <div class="footer-b">
        <div class="container">
            <div class="copyright">
                <?php 
                chic_lite_get_footer_copyright();
                // echo esc_html__( ' Feminine Fashion | Developed By ', 'feminine-fashion' ); 
                // echo '<a href="' . esc_url( 'https://rarathemes.com/' ) .'" rel="nofollow" target="_blank">' . esc_html__( 'Rara Themes', 'feminine-fashion' ) . '</a>.';                
                printf( esc_html__( ' Developed by %s. ', 'MatTopolitan Tech' ), '<a href="'. esc_url( __( 'https://www.instagram.com/mattopolitan', 'mattopolitan' ) ) .'" target="_blank">MatTopolitan Tech</a>' );

                if( function_exists( 'the_privacy_policy_link' ) ){
                    the_privacy_policy_link();
                } ?>
                
            </div>
            <div class="footer-menu">
                <?php chic_lite_footer_navigation(); ?>
            </div>
            
        </div>
    </div> <!-- .footer-b -->
    <?php
}

/**
 * Ajax Callback
 */
function chic_lite_dynamic_mce_css_ajax_callback(){
 
    /* Check nonce for security */
    $nonce = isset( $_REQUEST['_nonce'] ) ? $_REQUEST['_nonce'] : '';
    if( ! wp_verify_nonce( $nonce, 'chic_lite_dynamic_mce_nonce' ) ){
        die(); // don't print anything
    }
 
    $primary_font    = get_theme_mod( 'primary_font', 'Nunito Sans' );
    $primary_fonts   = chic_lite_get_fonts( $primary_font, 'regular' );
    $secondary_font  = get_theme_mod( 'secondary_font', 'Cormorant' );
    $secondary_fonts = chic_lite_get_fonts( $secondary_font, 'regular' );

    $primary_color    = get_theme_mod( 'primary_color', '#b67458' );

    $rgb = chic_lite_hex2rgb( chic_lite_sanitize_hex_color( $primary_color ) );
 
    /* Set File Type and Print the CSS Declaration */
    header( 'Content-type: text/css' );
    echo ':root .mce-content-body {
        --primary-color: ' . chic_lite_sanitize_hex_color( $primary_color ) . ';
        --primary-color-rgb: ' . sprintf( '%1$s, %2$s, %3$s', $rgb[0], $rgb[1], $rgb[2] ) . ';
        --primary-font: ' . esc_html( $primary_fonts['font'] ) . ';
        --secondary-font: ' . esc_html( $secondary_fonts['font'] ) . ';
    }
    
    .mce-content-body blockquote::before {
    background-image: url(\'data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" width="16.139" height="12.576" viewBox="0 0 16.139 12.576"><path d="M154.714,262.991c-.462.312-.9.614-1.343.9-.3.2-.612.375-.918.56a2.754,2.754,0,0,1-2.851.133,1.764,1.764,0,0,1-.771-.99,6.549,6.549,0,0,1-.335-1.111,5.386,5.386,0,0,1-.219-1.92,16.807,16.807,0,0,1,.3-1.732,2.392,2.392,0,0,1,.424-.8c.394-.534.808-1.053,1.236-1.56a3.022,3.022,0,0,1,.675-.61,2.962,2.962,0,0,0,.725-.749c.453-.576.923-1.137,1.38-1.71a3.035,3.035,0,0,0,.208-.35c.023-.038.044-.09.079-.107.391-.185.777-.383,1.179-.54.284-.11.5.141.739.234a.316.316,0,0,1-.021.2c-.216.411-.442.818-.663,1.226-.5.918-1.036,1.817-1.481,2.761a7.751,7.751,0,0,0-.915,3.069c-.009.326.038.653.053.98.009.2.143.217.288.2a1.678,1.678,0,0,0,1.006-.491c.2-.2.316-.207.537-.027.283.23.552.479.825.723a.174.174,0,0,1,.06.116,1.424,1.424,0,0,1-.327,1C154.281,262.714,154.285,262.755,154.714,262.991Z" transform="translate(-139.097 -252.358)" fill="' . chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ) . '"/><path d="M222.24,262.76a5.243,5.243,0,0,1-2.138,1.427,1.623,1.623,0,0,0-.455.26,3.112,3.112,0,0,1-2.406.338,1.294,1.294,0,0,1-1.021-1.2,6.527,6.527,0,0,1,.449-2.954c.015-.043.04-.083.053-.127a13.25,13.25,0,0,1,1.295-2.632,14.155,14.155,0,0,1,1.224-1.677c.084.14.132.238.2.324.133.176.3.121.414-.06a1.248,1.248,0,0,0,.1-.23c.055-.149.143-.214.315-.111-.029-.308,0-.607.3-.727.114-.045.295.079.463.131.093-.161.227-.372.335-.6.029-.06-.012-.16-.033-.238-.042-.154-.1-.3-.137-.458a1.117,1.117,0,0,1,.27-.933c.154-.207.286-.431.431-.646a.586.586,0,0,1,1.008-.108,2.225,2.225,0,0,0,.336.306.835.835,0,0,0,.356.087,1.242,1.242,0,0,0,.294-.052c-.067.145-.114.257-.17.364-.7,1.34-1.422,2.665-2.082,4.023-.488,1.005-.891,2.052-1.332,3.08a.628.628,0,0,0-.032.11c-.091.415.055.542.478.461.365-.07.607-.378.949-.463a2.8,2.8,0,0,1,.823-.064c.174.01.366.451.317.687a2.48,2.48,0,0,1-.607,1.26C222.081,262.492,222.011,262.615,222.24,262.76Z" transform="translate(-216.183 -252.301)" fill="' . chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ) . '"/></svg>\');
    }';
    die(); // end ajax process.
}

/**
 * Gutenberg Dynamic Style
 */
function chic_lite_gutenberg_inline_style(){
 
    /* Get Link Color */
    $primary_font    = get_theme_mod( 'primary_font', 'Nunito Sans' );
    $primary_fonts   = chic_lite_get_fonts( $primary_font, 'regular' );
    $secondary_font  = get_theme_mod( 'secondary_font', 'Cormorant' );
    $secondary_fonts = chic_lite_get_fonts( $secondary_font, 'regular' );

    $primary_color    = get_theme_mod( 'primary_color', '#f4bcab' );

    $rgb = chic_lite_hex2rgb( chic_lite_sanitize_hex_color( $primary_color ) );
 
    $custom_css = ':root .block-editor-page {
        --primary-color: ' . chic_lite_sanitize_hex_color( $primary_color ) . ';
        --primary-color-rgb: ' . sprintf( '%1$s, %2$s, %3$s', $rgb[0], $rgb[1], $rgb[2] ) . ';
        --primary-font: ' . esc_html( $primary_fonts['font'] ) . ';
        --secondary-font: ' . esc_html( $secondary_fonts['font'] ) . ';
    }
    
    blockquote.wp-block-quote::before {
    background-image: url(\'data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" width="16.139" height="12.576" viewBox="0 0 16.139 12.576"><path d="M154.714,262.991c-.462.312-.9.614-1.343.9-.3.2-.612.375-.918.56a2.754,2.754,0,0,1-2.851.133,1.764,1.764,0,0,1-.771-.99,6.549,6.549,0,0,1-.335-1.111,5.386,5.386,0,0,1-.219-1.92,16.807,16.807,0,0,1,.3-1.732,2.392,2.392,0,0,1,.424-.8c.394-.534.808-1.053,1.236-1.56a3.022,3.022,0,0,1,.675-.61,2.962,2.962,0,0,0,.725-.749c.453-.576.923-1.137,1.38-1.71a3.035,3.035,0,0,0,.208-.35c.023-.038.044-.09.079-.107.391-.185.777-.383,1.179-.54.284-.11.5.141.739.234a.316.316,0,0,1-.021.2c-.216.411-.442.818-.663,1.226-.5.918-1.036,1.817-1.481,2.761a7.751,7.751,0,0,0-.915,3.069c-.009.326.038.653.053.98.009.2.143.217.288.2a1.678,1.678,0,0,0,1.006-.491c.2-.2.316-.207.537-.027.283.23.552.479.825.723a.174.174,0,0,1,.06.116,1.424,1.424,0,0,1-.327,1C154.281,262.714,154.285,262.755,154.714,262.991Z" transform="translate(-139.097 -252.358)" fill="' . chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ) . '"/><path d="M222.24,262.76a5.243,5.243,0,0,1-2.138,1.427,1.623,1.623,0,0,0-.455.26,3.112,3.112,0,0,1-2.406.338,1.294,1.294,0,0,1-1.021-1.2,6.527,6.527,0,0,1,.449-2.954c.015-.043.04-.083.053-.127a13.25,13.25,0,0,1,1.295-2.632,14.155,14.155,0,0,1,1.224-1.677c.084.14.132.238.2.324.133.176.3.121.414-.06a1.248,1.248,0,0,0,.1-.23c.055-.149.143-.214.315-.111-.029-.308,0-.607.3-.727.114-.045.295.079.463.131.093-.161.227-.372.335-.6.029-.06-.012-.16-.033-.238-.042-.154-.1-.3-.137-.458a1.117,1.117,0,0,1,.27-.933c.154-.207.286-.431.431-.646a.586.586,0,0,1,1.008-.108,2.225,2.225,0,0,0,.336.306.835.835,0,0,0,.356.087,1.242,1.242,0,0,0,.294-.052c-.067.145-.114.257-.17.364-.7,1.34-1.422,2.665-2.082,4.023-.488,1.005-.891,2.052-1.332,3.08a.628.628,0,0,0-.032.11c-.091.415.055.542.478.461.365-.07.607-.378.949-.463a2.8,2.8,0,0,1,.823-.064c.174.01.366.451.317.687a2.48,2.48,0,0,1-.607,1.26C222.081,262.492,222.011,262.615,222.24,262.76Z" transform="translate(-216.183 -252.301)" fill="' . chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ) . '"/></svg>\');
    }';

    return $custom_css;
}

/**
 * Typography
*/ 
function chic_lite_fonts_url(){
    $fonts_url = '';
    
    $primary_font       = get_theme_mod( 'primary_font', 'Nunito Sans' );
    $ig_primary_font    = chic_lite_is_google_font( $primary_font );    
    $secondary_font     = get_theme_mod( 'secondary_font', 'Cormorant' );
    $ig_secondary_font  = chic_lite_is_google_font( $secondary_font );    
    $site_title_font    = get_theme_mod( 'site_title_font', array( 'font-family'=>'Nanum Myeongjo', 'variant'=>'regular' ) );
    $ig_site_title_font = chic_lite_is_google_font( $site_title_font['font-family'] );
        
    /* Translators: If there are characters in your language that are not
    * supported by respective fonts, translate this to 'off'. Do not translate
    * into your own language.
    */
    $primary    = _x( 'on', 'Primary Font: on or off', 'feminine-fashion' );
    $secondary  = _x( 'on', 'Secondary Font: on or off', 'feminine-fashion' );
    $site_title = _x( 'on', 'Site Title Font: on or off', 'feminine-fashion' );
    
    
    if ( 'off' !== $primary || 'off' !== $secondary || 'off' !== $site_title ) {
        
        $font_families = array();
     
        if ( 'off' !== $primary && $ig_primary_font ) {
            $primary_variant = chic_lite_check_varient( $primary_font, 'regular', true );
            if( $primary_variant ){
                $primary_var = ':' . $primary_variant;
            }else{
                $primary_var = '';    
            }            
            $font_families[] = $primary_font . $primary_var;
        }
         
        if ( 'off' !== $secondary && $ig_secondary_font ) {
            $secondary_variant = chic_lite_check_varient( $secondary_font, 'regular', true );
            if( $secondary_variant ){
                $secondary_var = ':' . $secondary_variant;    
            }else{
                $secondary_var = '';
            }
            $font_families[] = $secondary_font . $secondary_var;
        }
        
        if ( 'off' !== $site_title && $ig_site_title_font ) {
            
            if( ! empty( $site_title_font['variant'] ) ){
                $site_title_var = ':' . chic_lite_check_varient( $site_title_font['font-family'], $site_title_font['variant'] );    
            }else{
                $site_title_var = '';
            }
            $font_families[] = $site_title_font['font-family'] . $site_title_var;
        }
        
        $font_families = array_diff( array_unique( $font_families ), array('') );
        
        $query_args = array(
            'family' => urlencode( implode( '|', $font_families ) ),            
        );
        
        $fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
    }

    if( get_theme_mod( 'ed_localgoogle_fonts', false ) ) {
        $fonts_url = chic_lite_get_webfont_url( add_query_arg( $query_args, 'https://fonts.googleapis.com/css' ) );
    }
    
    return esc_url( $fonts_url );
}

/**
 * Dynamic css
*/ 
function feminine_fashion_dynamic_css(){
    
    $primary_font    = get_theme_mod( 'primary_font', 'Nunito Sans' );
    $primary_fonts   = chic_lite_get_fonts( $primary_font, 'regular' );
    $secondary_font  = get_theme_mod( 'secondary_font', 'Cormorant' );
    $secondary_fonts = chic_lite_get_fonts( $secondary_font, 'regular' );
    $font_size       = get_theme_mod( 'font_size', 18 );
    $primary_color   = get_theme_mod( 'primary_color', '#b67458' );
    
    $site_title_font      = get_theme_mod( 'site_title_font', array( 'font-family'=>'Nanum Myeongjo', 'variant'=>'regular' ) );
    $site_title_fonts     = chic_lite_get_fonts( $site_title_font['font-family'], $site_title_font['variant'] );
    $site_title_font_size = get_theme_mod( 'site_title_font_size', 30 );
    $site_logo_size       = get_theme_mod( 'site_logo_size', 70 );
    
    $rgb = chic_lite_hex2rgb( chic_lite_sanitize_hex_color( $primary_color ) );
    
    echo "<style type='text/css' media='all'>"; ?>
     
    .content-newsletter .blossomthemes-email-newsletter-wrapper.bg-img:after,
    .widget_blossomthemes_email_newsletter_widget .blossomthemes-email-newsletter-wrapper:after{
        <?php echo 'background: rgba(' . $rgb[0] . ', ' . $rgb[1] . ', ' . $rgb[2] . ', 0.8);'; ?>
    }
    
    /*Typography*/

    body,
    button,
    input,
    select,
    optgroup,
    textarea{
        font-family : <?php echo esc_html( $primary_fonts['font'] ); ?>;
        font-size   : <?php echo absint( $font_size ); ?>px;        
    }

    :root {
        --primary-font: <?php echo esc_html( $primary_fonts['font'] ); ?>;
        --secondary-font: <?php echo esc_html( $secondary_fonts['font'] ); ?>;
        --primary-color: <?php echo chic_lite_sanitize_hex_color( $primary_color ); ?>;
        --primary-color-rgb: <?php printf('%1$s, %2$s, %3$s', $rgb[0], $rgb[1], $rgb[2] ); ?>;
    }
    
    .site-branding .site-title-wrap .site-title{
        font-size   : <?php echo absint( $site_title_font_size ); ?>px;
        font-family : <?php echo esc_html( $site_title_fonts['font'] ); ?>;
        font-weight : <?php echo esc_html( $site_title_fonts['weight'] ); ?>;
        font-style  : <?php echo esc_html( $site_title_fonts['style'] ); ?>;
    }

    .single-post .entry-header h1.entry-title,
    .single-page .entry-header h1.entry-title {
        font-family : <?php echo esc_html( $secondary_fonts['font'] ); ?>
    }
    
    .custom-logo-link img{
        width: <?php echo absint( $site_logo_size ); ?>px;
        max-width: 100%;
    }

    .comment-body .reply .comment-reply-link:hover:before {
        background-image: url('data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" width="18" height="15" viewBox="0 0 18 15"><path d="M934,147.2a11.941,11.941,0,0,1,7.5,3.7,16.063,16.063,0,0,1,3.5,7.3c-2.4-3.4-6.1-5.1-11-5.1v4.1l-7-7,7-7Z" transform="translate(-927 -143.2)" fill="<?php echo chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ); ?>"/></svg>');
    }

    .site-header.style-five .header-mid .search-form .search-submit:hover {
        background-image: url('data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="<?php echo chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ); ?>" d="M508.5 468.9L387.1 347.5c-2.3-2.3-5.3-3.5-8.5-3.5h-13.2c31.5-36.5 50.6-84 50.6-136C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c52 0 99.5-19.1 136-50.6v13.2c0 3.2 1.3 6.2 3.5 8.5l121.4 121.4c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17zM208 368c-88.4 0-160-71.6-160-160S119.6 48 208 48s160 71.6 160 160-71.6 160-160 160z"></path></svg>');
    }

    .site-header.style-seven .header-bottom .search-form .search-submit:hover {
        background-image: url('data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="<?php echo chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ); ?>" d="M508.5 468.9L387.1 347.5c-2.3-2.3-5.3-3.5-8.5-3.5h-13.2c31.5-36.5 50.6-84 50.6-136C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c52 0 99.5-19.1 136-50.6v13.2c0 3.2 1.3 6.2 3.5 8.5l121.4 121.4c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17zM208 368c-88.4 0-160-71.6-160-160S119.6 48 208 48s160 71.6 160 160-71.6 160-160 160z"></path></svg>');
    }

    .site-header.style-fourteen .search-form .search-submit:hover {
        background-image: url('data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="<?php echo chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ); ?>" d="M508.5 468.9L387.1 347.5c-2.3-2.3-5.3-3.5-8.5-3.5h-13.2c31.5-36.5 50.6-84 50.6-136C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c52 0 99.5-19.1 136-50.6v13.2c0 3.2 1.3 6.2 3.5 8.5l121.4 121.4c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17zM208 368c-88.4 0-160-71.6-160-160S119.6 48 208 48s160 71.6 160 160-71.6 160-160 160z"></path></svg>');
    }

    .search-results .content-area > .page-header .search-submit:hover {
        background-image: url('data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="<?php echo chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ); ?>" d="M508.5 468.9L387.1 347.5c-2.3-2.3-5.3-3.5-8.5-3.5h-13.2c31.5-36.5 50.6-84 50.6-136C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c52 0 99.5-19.1 136-50.6v13.2c0 3.2 1.3 6.2 3.5 8.5l121.4 121.4c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17zM208 368c-88.4 0-160-71.6-160-160S119.6 48 208 48s160 71.6 160 160-71.6 160-160 160z"></path></svg>');
    }
    .main-navigation li.menu-item-has-children a::after {
        background-image: url('data:image/svg+xml; utf-8, <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"> <path fill="<?php echo chic_lite_hash_to_percent23( chic_lite_sanitize_hex_color( $primary_color ) ); ?>" d="M207.029 381.476L12.686 187.132c-9.373-9.373-9.373-24.569 0-33.941l22.667-22.667c9.357-9.357 24.522-9.375 33.901-.04L224 284.505l154.745-154.021c9.379-9.335 24.544-9.317 33.901.04l22.667 22.667c9.373 9.373 9.373 24.569 0 33.941L240.971 381.476c-9.373 9.372-24.569 9.372-33.942 0z"></path></svg>');
    }

    <?php echo "</style>";
}
add_action( 'wp_head', 'feminine_fashion_dynamic_css', 99 );
