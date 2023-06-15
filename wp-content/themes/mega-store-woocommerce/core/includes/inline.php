<?php


$mega_store_woocommerce_custom_css = '';

	/*---------------------------text-transform-------------------*/

	$mega_store_woocommerce_text_transform = get_theme_mod( 'menu_text_transform_mega_store_woocommerce','UPPERCASE');
    if($mega_store_woocommerce_text_transform == 'CAPITALISE'){

		$mega_store_woocommerce_custom_css .='#main-menu ul li a{';

			$mega_store_woocommerce_custom_css .='text-transform: capitalize;';

		$mega_store_woocommerce_custom_css .='}';

	}else if($mega_store_woocommerce_text_transform == 'UPPERCASE'){

		$mega_store_woocommerce_custom_css .='#main-menu ul li a{';

			$mega_store_woocommerce_custom_css .='text-transform: uppercase;';

		$mega_store_woocommerce_custom_css .='}';

	}else if($mega_store_woocommerce_text_transform == 'LOWERCASE'){

		$mega_store_woocommerce_custom_css .='#main-menu ul li a{';

			$mega_store_woocommerce_custom_css .='text-transform: lowercase;';

		$mega_store_woocommerce_custom_css .='}';
	}

	/*---------------------------Container Width-------------------*/

$mega_store_woocommerce_container_width = get_theme_mod('mega_store_woocommerce_container_width');

	$mega_store_woocommerce_custom_css .='body{';

		$mega_store_woocommerce_custom_css .='width: '.esc_attr($mega_store_woocommerce_container_width).'%; margin: auto;';

	$mega_store_woocommerce_custom_css .='}';

	/*---------------------------Slider-content-alignment-------------------*/


$mega_store_woocommerce_slider_content_alignment = get_theme_mod( 'mega_store_woocommerce_slider_content_alignment','CENTER-ALIGN');

 if($mega_store_woocommerce_slider_content_alignment == 'LEFT-ALIGN'){

		$mega_store_woocommerce_custom_css .='.blog_box{';

			$mega_store_woocommerce_custom_css .='text-align:left;';

		$mega_store_woocommerce_custom_css .='}';


	}else if($mega_store_woocommerce_slider_content_alignment == 'CENTER-ALIGN'){

		$mega_store_woocommerce_custom_css .='.blog_box{';

			$mega_store_woocommerce_custom_css .='text-align:center;';

		$mega_store_woocommerce_custom_css .='}';


	}else if($mega_store_woocommerce_slider_content_alignment == 'RIGHT-ALIGN'){

		$mega_store_woocommerce_custom_css .='.blog_box{';

			$mega_store_woocommerce_custom_css .='text-align:right;';

		$mega_store_woocommerce_custom_css .='}';

	}

	/*---------------------------Copyright Text alignment-------------------*/

$mega_store_woocommerce_copyright_text_alignment = get_theme_mod( 'mega_store_woocommerce_copyright_text_alignment','LEFT-ALIGN');

 if($mega_store_woocommerce_copyright_text_alignment == 'LEFT-ALIGN'){

		$mega_store_woocommerce_custom_css .='.copy-text p{';

			$mega_store_woocommerce_custom_css .='text-align:left;';

		$mega_store_woocommerce_custom_css .='}';


	}else if($mega_store_woocommerce_copyright_text_alignment == 'CENTER-ALIGN'){

		$mega_store_woocommerce_custom_css .='.copy-text p{';

			$mega_store_woocommerce_custom_css .='text-align:center;';

		$mega_store_woocommerce_custom_css .='}';


	}else if($mega_store_woocommerce_copyright_text_alignment == 'RIGHT-ALIGN'){

		$mega_store_woocommerce_custom_css .='.copy-text p{';

			$mega_store_woocommerce_custom_css .='text-align:right;';

		$mega_store_woocommerce_custom_css .='}';

	}
