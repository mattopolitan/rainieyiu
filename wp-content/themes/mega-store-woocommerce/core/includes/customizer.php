<?php

if ( class_exists("Kirki")){

	// LOGO

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'slider',
		'settings'    => 'mega_store_woocommerce_logo_resizer',
		'label'       => esc_html__( 'Adjust Your Logo Size ', 'mega-store-woocommerce' ),
		'section'     => 'title_tagline',
		'default'     => 70,
		'choices'     => [
			'min'  => 10,
			'max'  => 300,
			'step' => 10,
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_enable_logo_text',
		'section'     => 'title_tagline',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Enable / Disable Site Title and Tagline', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_display_header_title',
		'label'       => esc_html__( 'Site Title Enable / Disable Button', 'mega-store-woocommerce' ),
		'section'     => 'title_tagline',
		'default'     => '1',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_display_header_text',
		'label'       => esc_html__( 'Tagline Enable / Disable Button', 'mega-store-woocommerce' ),
		'section'     => 'title_tagline',
		'default'     => false,
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	//FONT STYLE TYPOGRAPHY

	Kirki::add_panel( 'mega_store_woocommerce_panel_id', array(
	    'priority'    => 10,
	    'title'       => esc_html__( 'Typography', 'mega-store-woocommerce' ),
	) );

	Kirki::add_section( 'mega_store_woocommerce_font_style_section', array(
		'title'      => esc_attr__( 'Typography Option',  'mega-store-woocommerce' ),
		'priority'   => 2,
		'capability' => 'edit_theme_options',
	) );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_all_headings_typography',
		'section'     => 'mega_store_woocommerce_font_style_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Heading Of All Sections',  'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	Kirki::add_field( 'global', array(
		'type'        => 'typography',
		'settings'    => 'mega_store_woocommerce_all_headings_typography',
		'label'       => esc_attr__( 'Heading Typography',  'mega-store-woocommerce' ),
		'description' => esc_attr__( 'Select the typography options for your heading.',  'mega-store-woocommerce' ),
		'help'        => esc_attr__( 'The typography options you set here will override the Body Typography options for all headers on your site (post titles, widget titles etc).',  'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_font_style_section',
		'priority'    => 10,
		'default'     => array(
			'font-family'    => '',
			'variant'        => '',
		),
		'output' => array(
			array(
				'element' => array( 'h1','h2','h3','h4','h5','h6', ),
			),
		),
	) );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_body_content_typography',
		'section'     => 'mega_store_woocommerce_font_style_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Body Content',  'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	Kirki::add_field( 'global', array(
		'type'        => 'typography',
		'settings'    => 'mega_store_woocommerce_body_content_typography',
		'label'       => esc_attr__( 'Content Typography',  'mega-store-woocommerce' ),
		'description' => esc_attr__( 'Select the typography options for your content.',  'mega-store-woocommerce' ),
		'help'        => esc_attr__( 'The typography options you set here will override the Body Typography options for all headers on your site (post titles, widget titles etc).',  'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_font_style_section',
		'priority'    => 10,
		'default'     => array(
			'font-family'    => '',
			'variant'        => '',
		),
		'output' => array(
			array(
				'element' => array( 'body', ),
			),
		),
	) );

	// PANEL

	Kirki::add_panel( 'mega_store_woocommerce_panel_id', array(
	    'priority'    => 10,
	    'title'       => esc_html__( 'Theme Options', 'mega-store-woocommerce' ),
	) );

	// Additional Settings

	Kirki::add_section( 'mega_store_woocommerce_additional_settings', array(
	    'title'          => esc_html__( 'Additional Settings', 'mega-store-woocommerce' ),
	    'description'    => esc_html__( 'Scroll To Top', 'mega-store-woocommerce' ),
	    'panel'          => 'mega_store_woocommerce_panel_id',
	    'priority'       => 160,
	) );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'toggle',
		'settings'    => 'mega_store_woocommerce_scroll_enable_setting',
		'label'       => esc_html__( 'Here you can enable or disable your scroller.', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_additional_settings',
		'default'     => '1',
		'priority'    => 10,
	] );

	new \Kirki\Field\Select(
	[
		'settings'    => 'menu_text_transform_mega_store_woocommerce',
		'label'       => esc_html__( 'Menus Text Transform', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_additional_settings',
		'default'     => 'UPPERCASE',
		'placeholder' => esc_html__( 'Choose an option', 'mega-store-woocommerce' ),
		'choices'     => [
			'CAPITALISE' => esc_html__( 'CAPITALISE', 'mega-store-woocommerce' ),
			'UPPERCASE' => esc_html__( 'UPPERCASE', 'mega-store-woocommerce' ),
			'LOWERCASE' => esc_html__( 'LOWERCASE', 'mega-store-woocommerce' ),

		],
	]);

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'toggle',
		'settings'    => 'mega_store_woocommerce_sticky_header',
		'label'       => esc_html__( 'Here you can enable or disable your Sticky Header.', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_additional_settings',
		'default'     => false,
		'priority'    => 10,
	] );

	// Woocommerce Settings

	Kirki::add_section( 'mega_store_woocommerce_woocommerce_settings', array(
			'title'          => esc_html__( 'Woocommerce Settings', 'mega-store-woocommerce' ),
			'description'    => esc_html__( 'Shop Page', 'mega-store-woocommerce' ),
			'panel'          => 'mega_store_woocommerce_panel_id',
			'priority'       => 160,
	) );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'toggle',
		'settings'    => 'mega_store_woocommerce_shop_sidebar',
		'label'       => esc_html__( 'Here you can enable or disable shop page sidebar.', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_woocommerce_settings',
		'default'     => '1',
		'priority'    => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'toggle',
		'settings'    => 'mega_store_woocommerce_product_sidebar',
		'label'       => esc_html__( 'Here you can enable or disable product page sidebar.', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_woocommerce_settings',
		'default'     => '1',
		'priority'    => 10,
	] );


	Kirki::add_field( 'theme_config_id', [
		'type'        => 'slider',
		'settings'    => 'mega_store_woocommerce_container_width',
		'label'       => esc_html__( 'Theme Container Width', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_additional_settings',
		'default'     => 100,
		'choices'     => [
			'min'  => 50,
			'max'  => 100,
			'step' => 1,
		],
	] );


	// POST SECTION

	Kirki::add_section( 'mega_store_woocommerce_section_post', array(
	    'title'          => esc_html__( 'Post Settings', 'mega-store-woocommerce' ),
	    'description'    => esc_html__( 'Here you can get different post settings', 'mega-store-woocommerce' ),
	    'panel'          => 'mega_store_woocommerce_panel_id',
	    'priority'       => 160,
	) );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_enable_post_heading',
		'section'     => 'mega_store_woocommerce_section_post',
		'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Enable / Disable Post Settings.', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_blog_admin_enable',
		'label'       => esc_html__( 'Post Author Enable / Disable Button', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_section_post',
		'default'     => '1',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_blog_comment_enable',
		'label'       => esc_html__( 'Post Comment Enable / Disable Button', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_section_post',
		'default'     => '1',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'slider',
		'settings'    => 'mega_store_woocommerce_post_excerpt_number',
		'label'       => esc_html__( 'Number of text to show', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_section_post',
		'default'     => 15,
		'choices'     => [
			'min'  => 0,
			'max'  => 100,
			'step' => 1,
		],
	] );

	// HEADER SECTION

	Kirki::add_section( 'mega_store_woocommerce_section_header', array(
	    'title'          => esc_html__( 'Header Settings', 'mega-store-woocommerce' ),
	    'description'    => esc_html__( 'Here you can add header information.', 'mega-store-woocommerce' ),
	    'panel'          => 'mega_store_woocommerce_panel_id',
	    'priority'       => 160,
	) );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_links_heading',
		'section'     => 'mega_store_woocommerce_section_header',
		'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Top Header Text and URL', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'     => 'text',
		'label'       => esc_html__( 'Text 1', 'mega-store-woocommerce' ),
		'settings' => 'mega_store_woocommerce_free_delivery_text',
		'section'  => 'mega_store_woocommerce_section_header',
		'default'  => '',
		'priority' => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'     => 'url',
		'label'       => esc_html__( 'URL 1', 'mega-store-woocommerce' ),
		'settings' => 'mega_store_woocommerce_free_delivery_link',
		'section'  => 'mega_store_woocommerce_section_header',
		'default'  => '',
		'priority' => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'     => 'text',
		'label'       => esc_html__( 'Text 2', 'mega-store-woocommerce' ),
		'settings' => 'mega_store_woocommerce_return_policy_text',
		'section'  => 'mega_store_woocommerce_section_header',
		'default'  => '',
		'priority' => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'     => 'url',
		'label'       => esc_html__( 'URL 2', 'mega-store-woocommerce' ),
		'settings' => 'mega_store_woocommerce_return_policy_link',
		'section'  => 'mega_store_woocommerce_section_header',
		'default'  => '',
		'priority' => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_phone_number_heading',
		'section'     => 'mega_store_woocommerce_section_header',
		'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Phone Number', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'     => 'text',
		'settings' => 'mega_store_woocommerce_phone_number',
		'section'  => 'mega_store_woocommerce_section_header',
		'default'  => '',
		'priority' => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_myaccount_link_heading',
		'section'     => 'mega_store_woocommerce_section_header',
		'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'My Account URL', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'     => 'url',
		'settings' => 'mega_store_woocommerce_myaccount_link',
		'section'  => 'mega_store_woocommerce_section_header',
		'default'  => '',
		'priority' => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_enable_google_translation',
		'section'     => 'mega_store_woocommerce_section_header',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Enable / Disable Google Translation Box', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_header_google_translation',
		'section'     => 'mega_store_woocommerce_section_header',
		'default'     => '0',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_enable_search',
		'section'     => 'mega_store_woocommerce_section_header',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Enable / Disable Search Box', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_search_box_enable',
		'section'     => 'mega_store_woocommerce_section_header',
		'default'     => '1',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_enable_socail_link',
		'section'     => 'mega_store_woocommerce_section_header',
		'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Social Media Link', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'repeater',
		'section'     => 'mega_store_woocommerce_section_header',
		'priority'    => 10,
		'row_label' => [
			'type'  => 'field',
			'value' => esc_html__( 'Social Icon', 'mega-store-woocommerce' ),
			'field' => 'link_text',
		],
		'button_label' => esc_html__('Add New Social Icon', 'mega-store-woocommerce' ),
		'settings'     => 'mega_store_woocommerce_social_links_settings',
		'default'      => '',
		'fields' 	   => [
			'link_text' => [
				'type'        => 'text',
				'label'       => esc_html__( 'Icon', 'mega-store-woocommerce' ),
				'description' => esc_html__( 'Add the fontawesome class ex: "fab fa-facebook-f".', 'mega-store-woocommerce' ),
				'default'     => '',
			],
			'link_url' => [
				'type'        => 'url',
				'label'       => esc_html__( 'Social Link', 'mega-store-woocommerce' ),
				'description' => esc_html__( 'Add the social icon url here.', 'mega-store-woocommerce' ),
				'default'     => '',
			],
		],
		'choices' => [
			'limit' => 5
		],
	] );

	// SLIDER SECTION

	Kirki::add_section( 'mega_store_woocommerce_blog_slide_section', array(
        'title'          => esc_html__( ' Slider Settings', 'mega-store-woocommerce' ),
        'description'    => esc_html__( 'You have to select post category to show slider.', 'mega-store-woocommerce' ),
        'panel'          => 'mega_store_woocommerce_panel_id',
        'priority'       => 160,
    ) );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_enable_heading',
		'section'     => 'mega_store_woocommerce_blog_slide_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Enable / Disable Slider', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_blog_box_enable',
		'label'       => esc_html__( 'Section Enable / Disable', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_blog_slide_section',
		'default'     => false,
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_title_unable_disable',
		'label'       => esc_html__( 'Slide Title Enable / Disable', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_blog_slide_section',
		'default'     => '1',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_text_unable_disable',
		'label'       => esc_html__( 'Slide Text Enable / Disable', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_blog_slide_section',
		'default'     => '1',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_button_unable_disable',
		'label'       => esc_html__( 'Slide Button Enable / Disable', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_blog_slide_section',
		'default'     => '1',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_slider_heading',
		'section'     => 'mega_store_woocommerce_blog_slide_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Slider', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'number',
		'settings'    => 'mega_store_woocommerce_blog_slide_number',
		'label'       => esc_html__( 'Number of slides to show', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_blog_slide_section',
		'default'     => 3,
		'choices'     => [
			'min'  => 0,
			'max'  => 80,
			'step' => 1,
		],
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'select',
		'settings'    => 'mega_store_woocommerce_blog_slide_category',
		'label'       => esc_html__( 'Select the category to show slider ( Image Dimension 1600 x 600 )', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_blog_slide_section',
		'default'     => '',
		'placeholder' => esc_html__( 'Select an category...', 'mega-store-woocommerce' ),
		'priority'    => 10,
		'choices'     => mega_store_woocommerce_get_categories_select(),
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'slider',
		'settings'    => 'mega_store_woocommerce_slide_excerpt_number',
		'label'       => esc_html__( 'Number of text to show', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_blog_slide_section',
		'default'     => 15,
		'choices'     => [
			'min'  => 0,
			'max'  => 100,
			'step' => 1,
		],
	] );

	new \Kirki\Field\Select(
	[
		'settings'    => 'mega_store_woocommerce_slider_content_alignment',
		'label'       => esc_html__( 'Slider Content Alignment', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_blog_slide_section',
		'default'     => 'CENTER-ALIGN',
		'placeholder' => esc_html__( 'Choose an option', 'mega-store-woocommerce' ),
		'choices'     => [
			'LEFT-ALIGN' => esc_html__( 'LEFT-ALIGN', 'mega-store-woocommerce' ),
			'CENTER-ALIGN' => esc_html__( 'CENTER-ALIGN', 'mega-store-woocommerce' ),
			'RIGHT-ALIGN' => esc_html__( 'RIGHT-ALIGN', 'mega-store-woocommerce' ),

		],
	] );

	// PRODUCT CATEGORY SECTION

	Kirki::add_section( 'mega_store_woocommerce_product_category_section', array(
        'title'          => esc_html__( ' Product Category Settings', 'mega-store-woocommerce' ),
        'description'    => esc_html__( 'You have to add images and title for product category', 'mega-store-woocommerce' ),
        'panel'          => 'mega_store_woocommerce_panel_id',
        'priority'       => 160,
    ) );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_enable_category_heading',
		'section'     => 'mega_store_woocommerce_product_category_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Enable / Disable Product Category', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_product_category_box_enable',
		'label'       => esc_html__( 'Section Enable / Disable', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_product_category_section',
		'default'     => false,
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_product_category_heading_1',
		'section'     => 'mega_store_woocommerce_product_category_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Product Category Section ',  'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
        'type'     => 'text',
        'settings' => 'mega_store_woocommerce_product_category_section_heading' ,
        'label'    => esc_html__( 'Heading',  'mega-store-woocommerce' ),
        'section'  => 'mega_store_woocommerce_product_category_section',
    ] );

    kirki::add_field( 'theme_config_id', [
		'type'        => 'number',
		'settings'    => 'mega_store_woocommerce_product_category_increament',
		'label'       => esc_html__( 'Number of product category to show ',  'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_product_category_section',
		'default'     => 0,
		'choices'     => [
			'min'  => 0,
			'max'  => 80,
			'step' => 1,
		],
	] );

    $mega_store_woocommerce_product_category = get_theme_mod('mega_store_woocommerce_product_category_increament','');
    for ( $j = 1; $j <= $mega_store_woocommerce_product_category; $j++ ) {

    	new \Kirki\Field\Image(
			[
				'settings'    => 'mega_store_woocommerce_product_category_box_images'.$j,
				'label'       => esc_html__( ' Category Image', 'mega-store-woocommerce' ),
				'section'     => 'mega_store_woocommerce_product_category_section',
				'default'     => '',
			]
		);

		Kirki::add_field( 'theme_config_id', [
	        'type'     => 'text',
	        'settings' => 'mega_store_woocommerce_product_category_box_title'.$j ,
	        'label'    => esc_html__( 'Category Text',  'mega-store-woocommerce' ),
	        'section'  => 'mega_store_woocommerce_product_category_section',
	    ] );

		Kirki::add_field( 'theme_config_id', [
	        'type'     => 'url',
	        'settings' => 'mega_store_woocommerce_product_category_box_title_url'.$j ,
	        'label'    => esc_html__( 'Category URL',  'mega-store-woocommerce' ),
	        'section'  => 'mega_store_woocommerce_product_category_section',
	    ] );

	}

	//OUR COLECTION SECTION

	Kirki::add_section( 'mega_store_woocommerce_our_collection_section', array(
	    'title'          => esc_html__( 'Our Collection Settings', 'mega-store-woocommerce' ),
	    'description'    => esc_html__( 'Here you can add different type of social icons.', 'mega-store-woocommerce' ),
	    'panel'          => 'mega_store_woocommerce_panel_id',
	    'priority'       => 160,
	) );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_enable_heading',
		'section'     => 'mega_store_woocommerce_our_collection_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Enable / Disable Our Collection',  'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 1,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_our_collection_section_enable',
		'label'       => esc_html__( 'Section Enable / Disable',  'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_our_collection_section',
		'default'     => false,
		'priority'    => 2,
		'choices'     => [
			'on'  => esc_html__( 'Enable',  'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable',  'mega-store-woocommerce' ),
		],
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_our_collection_heading_1',
		'section'     => 'mega_store_woocommerce_our_collection_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Our Collection Section ',  'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 3,
	] );

	Kirki::add_field( 'theme_config_id', [
        'type'     => 'text',
        'settings' => 'mega_store_woocommerce_our_collection_sub_heading' ,
        'label'    => esc_html__( 'Sub Heading',  'mega-store-woocommerce' ),
        'section'  => 'mega_store_woocommerce_our_collection_section',

    ] );

	Kirki::add_field( 'theme_config_id', [
        'type'     => 'text',
        'settings' => 'mega_store_woocommerce_our_collection_heading' ,
        'label'    => esc_html__( 'Heading',  'mega-store-woocommerce' ),
        'section'  => 'mega_store_woocommerce_our_collection_section',
    ] );

    kirki::add_field( 'theme_config_id', [
		'type'        => 'number',
		'settings'    => 'mega_store_woocommerce_our_collection_tab_number',
		'label'       => esc_html__( 'Number of post to show ',  'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_our_collection_section',
		'default'     => 4,
		'choices'     => [
			'min'  => 0,
			'max'  => 80,
			'step' => 1,
		],
	] );

	// FOOTER SECTION

	Kirki::add_section( 'mega_store_woocommerce_footer_section', array(
        'title'          => esc_html__( 'Footer Settings', 'mega-store-woocommerce' ),
        'description'    => esc_html__( 'Here you can change copyright text', 'mega-store-woocommerce' ),
        'panel'          => 'mega_store_woocommerce_panel_id',
        'priority'       => 160,
    ) );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_footer_text_heading',
		'section'     => 'mega_store_woocommerce_footer_section',
			'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Footer Copyright Text', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'     => 'text',
		'settings' => 'mega_store_woocommerce_footer_text',
		'section'  => 'mega_store_woocommerce_footer_section',
		'default'  => '',
		'priority' => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_footer_enable_heading',
		'section'     => 'mega_store_woocommerce_footer_section',
		'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Enable / Disable Footer Link', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

    Kirki::add_field( 'theme_config_id', [
		'type'        => 'switch',
		'settings'    => 'mega_store_woocommerce_copyright_enable',
		'label'       => esc_html__( 'Section Enable / Disable', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_footer_section',
		'default'     => '1',
		'priority'    => 10,
		'choices'     => [
			'on'  => esc_html__( 'Enable', 'mega-store-woocommerce' ),
			'off' => esc_html__( 'Disable', 'mega-store-woocommerce' ),
		],
	] );

		Kirki::add_field( 'theme_config_id', [
		'type'        => 'custom',
		'settings'    => 'mega_store_woocommerce_footer_text_heading_2',
		'section'     => 'mega_store_woocommerce_footer_section',
		'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Footer Copyright Alignment', 'mega-store-woocommerce' ) . '</h3>',
		'priority'    => 10,
	] );

	new \Kirki\Field\Select(
	[
		'settings'    => 'mega_store_woocommerce_copyright_text_alignment',
		'label'       => esc_html__( 'Copyright text Alignment', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_footer_section',
		'default'     => 'LEFT-ALIGN',
		'placeholder' => esc_html__( 'Choose an option', 'mega-store-woocommerce' ),
		'choices'     => [
			'LEFT-ALIGN' => esc_html__( 'LEFT-ALIGN', 'mega-store-woocommerce' ),
			'CENTER-ALIGN' => esc_html__( 'CENTER-ALIGN', 'mega-store-woocommerce' ),
			'RIGHT-ALIGN' => esc_html__( 'RIGHT-ALIGN', 'mega-store-woocommerce' ),

		],
	] );

	Kirki::add_field( 'theme_config_id', [
	'type'        => 'custom',
	'settings'    => 'mega_store_woocommerce_footer_text_heading_1',
	'section'     => 'mega_store_woocommerce_footer_section',
		'default'         => '<h3 style="color: #2271b1; padding:10px; background:#fff; margin:0; border-left: solid 5px #2271b1; ">' . __( 'Footer Copyright Background Color', 'mega-store-woocommerce' ) . '</h3>',
	'priority'    => 10,
	] );

	Kirki::add_field( 'theme_config_id', [
		'type'        => 'color',
		'settings'    => 'mega_store_woocommerce_copyright_bg',
		'label'       => __( 'Choose Your Copyright Background Color', 'mega-store-woocommerce' ),
		'section'     => 'mega_store_woocommerce_footer_section',
		'default'     => '',
	] );
}

add_action( 'customize_register', 'mega_store_woocommerce_customizer_settings' );
function mega_store_woocommerce_customizer_settings( $wp_customize ) {
	$wp_customize->add_setting('mega_store_woocommerce_our_collection_tab_number',array(
		'default' => '',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('mega_store_woocommerce_our_collection_tab_number',array(
		'type' => 'number',
		'label' => __('Show number of product tab','mega-store-woocommerce'),
		'section' => 'mega_store_woocommerce_our_collection_section',
	));

	$mega_store_woocommerce_collection_post = get_theme_mod('mega_store_woocommerce_our_collection_tab_number','');
    for ( $j = 1; $j <= $mega_store_woocommerce_collection_post; $j++ ) {

		$wp_customize->add_setting('mega_store_woocommerce_our_collection_tabs_text'.$j,array(
			'default' => '',
			'sanitize_callback' => 'sanitize_text_field',
		));
		$wp_customize->add_control('mega_store_woocommerce_our_collection_tabs_text'.$j,array(
			'type' => 'text',
			'label' => __('Tab Text ','mega-store-woocommerce').$j,
			'section' => 'mega_store_woocommerce_our_collection_section',
		));

		$mega_store_woocommerce_args = array(
	       'type'                     => 'product',
	        'child_of'                 => 0,
	        'parent'                   => '',
	        'orderby'                  => 'term_group',
	        'order'                    => 'ASC',
	        'hide_empty'               => false,
	        'hierarchical'             => 1,
	        'number'                   => '',
	        'taxonomy'                 => 'product_cat',
	        'pad_counts'               => false
	    );
		$categories = get_categories($mega_store_woocommerce_args);
		$cat_posts = array();
		$m = 0;
		$cat_posts[]='Select';
		foreach($categories as $category){
			if($m==0){
				$default = $category->slug;
				$m++;
			}
			$cat_posts[$category->slug] = $category->name;
		}

		$wp_customize->add_setting('mega_store_woocommerce_our_collection_category'.$j,array(
			'default'	=> 'select',
			'sanitize_callback' => 'mega_store_woocommerce_sanitize_select',
		));

		$wp_customize->add_control('mega_store_woocommerce_our_collection_category'.$j,array(
			'type'    => 'select',
			'choices' => $cat_posts,
			'label' => __('Select category to display products ','mega-store-woocommerce').$j,
			'section' => 'mega_store_woocommerce_our_collection_section',
		));
	}
}
