<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://smartarget.online
 * @since      1.0.0
 *
 * @package    Smartarget
 * @subpackage Smartarget/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Smartarget
 * @subpackage Smartarget/admin
 * @author     Erez <erezson@gmail.com >
 */
class Smartarget_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_options = get_option($this->plugin_name);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smartarget_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smartarget_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smartarget-admin.css', array(), $this->version, 'all' );

		$version = true ? random_int(0, 100000) : $this->version;

		wp_register_style( 'icons.css', 'https://smartarget.online/wp-dashboard/styles.css', array(), $version, 'all' );
		wp_enqueue_style( 'icons.css' );

		wp_register_style( 'styles.css', 'https://unicons.iconscout.com/release/v3.0.3/css/line.css', array(), $version, 'all' );
		wp_enqueue_style( 'styles.css' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smartarget_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smartarget_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$version = true ? random_int(0, 100000) : $this->version;

		wp_register_script('package', 'https://smartarget.online/wp-dashboard/package.js', [], $version, true);
		wp_enqueue_script( 'package' );

		wp_register_script('loader', 'https://smartarget.online/loader.js', [], $version, true);
		wp_enqueue_script( 'loader' );

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */

	public function add_plugin_admin_menu() {


		function smartarget_plugin_notice() {

			global $current_user;

			$user_id = $current_user->ID;

			if (!get_user_meta($user_id, 'smartarget_plugin_notice_ignore2')) {

				echo '<div style="position: relative !important;" data-dismissible="smartarget-review" class="updated notice notice-success">
      <h3 style="margin-bottom: 5px">💜 Smartarget - Rate our plugin</h3>
      <p>We work very hard on our plugin to help you improve your website, so you can sell more. Please support us by leaving feedback for our plugin. <a target="_blank" href="https://wordpress.org/support/plugin/smartarget-contact-us/reviews/#new-post">Write a review</a></p>
      <a target="_blank" style="text-decoration: none;" href="?smartarget-plugin-ignore-notice" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
    
      </div>';

			}

		}
		add_action('admin_notices', 'smartarget_plugin_notice');

		function smartarget_plugin_notice_ignore2() {

			global $current_user;

			$user_id = $current_user->ID;

			if (isset($_GET['smartarget-plugin-ignore-notice'])) {

				add_user_meta($user_id, 'smartarget_plugin_notice_ignore2', 'true', true);

			}

		}
		add_action('admin_init', 'smartarget_plugin_notice_ignore2');


		/*
		 * Add a settings page for this plugin to the Settings menu.
		*/
		add_menu_page( 'Smartarget.online Integration Settings', 'Smartarget', 'read', $this -> plugin_name, array($this, 'display_plugin_setup_page'), 'dashicons-heart' );
		add_options_page( 'Smartarget.online Integration Settings', 'Smartarget', 'manage_options', $this -> plugin_name, array($this, 'display_plugin_setup_page')
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 */

	public function add_action_links( $links ) {

        $settings_link = [
            '<a target="_blank" style="color:#1da867;border: 1px solid #1da867; background-color: #1da86720; border-radius: 3px; padding: 3px 5px;" href="' . 'https://app.smartarget.online/#/subscription/buy' . '">' . __('Upgrade to Pro', $this->plugin_name) . '</a>',
            '<a target="_blank" href="' . 'https://smartarget.online/page_contact.html' . '">' . __('Support', $this->plugin_name) . '</a>',
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
        ];
		return array_merge(  $settings_link, $links );

	}

	/**
	 * Render the settings page for this plugin.
	 */

	public function display_plugin_setup_page() {

		include_once( 'partials/smartarget-admin-display.php' );
	}

	/**
	 * Validate options
	 */
	public function validate($input) {
		$valid = array();
		$valid['smartarget_user_id'] = (isset($input['smartarget_user_id']) && !empty($input['smartarget_user_id'])) ? $input['smartarget_user_id'] : '';
		return $valid;
	}

	/**
	 * Update all options
	 */
	public function options_update() {
		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
	}
}
