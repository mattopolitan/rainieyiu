<?php
/**
 * Singleton class for handling the theme's customizer integration.
 *
 * @since  1.0.0
 * @access public
 */
final class Mega_Store_Woocommerce_Customize {

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $mega_store_woocommerce_instance = null;

		if ( is_null( $mega_store_woocommerce_instance ) ) {
			$mega_store_woocommerce_instance = new self;
			$mega_store_woocommerce_instance->setup_actions();
		}

		return $mega_store_woocommerce_instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {

		// Register panels, sections, settings, controls, and partials.
		add_action( 'customize_register', array( $this, 'sections' ) );

		// Register scripts and styles for the controls.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_control_scripts' ), 0 );
	}

	/**
	 * Sets up the customizer sections.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $manager
	 * @return void
	*/
	public function sections( $manager ) {

		// Load custom sections.
		load_template( trailingslashit( get_template_directory() ) . '/core/includes/upgrade-pro.php' );

		// Register custom section types.
		$manager->register_section_type( 'Mega_Store_Woocommerce_Customize_Section_Pro' );

		$manager->add_section(
			new Mega_Store_Woocommerce_Customize_Section_Pro(
				$manager,
				'mega_store_woocommerce_upgrade_pro',
				array(
					'title'       => esc_html__( 'Upgrade to Mega Store PRO', 'mega-store-woocommerce' ),
					'description' => esc_html__( 'Unlock premium features: Multiple Sections, Color Pallete, Typography, Premium Support and much more...', 'mega-store-woocommerce' ),
					'pro_text'    => esc_html__( 'View Mega Store PRO', 'mega-store-woocommerce' ),
					'pro_url'     => 'https://www.misbahwp.com/themes/megastore-wordpress-theme/',
					'priority'    => 1,
				)
			)
		);
	}

	/**
	 * Loads theme customizer CSS.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue_control_scripts() {

		wp_enqueue_script( 'mega-store-woocommerce-customize-controls', trailingslashit( get_template_directory_uri() ) . '/js/customize-controls.js', array( 'customize-controls' ) );

		wp_enqueue_style( 'mega-store-woocommerce-customize-controls', trailingslashit( get_template_directory_uri() ) . '/css/customize-controls.css' );
	}
}

// Doing this customizer thang!
Mega_Store_Woocommerce_Customize::get_instance();