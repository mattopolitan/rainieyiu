<?php
/**
 * @package ShipAny
 */
/*
Plugin Name: ShipAny
Plugin URI: http://wordpress.org/plugins/shipany
Description: ShipAny one-stop logistics platform interconnects WooCommerce to multiple logistics service providers (including SF Express, Kerry Express, Zeek, SF Cold-Chain, Alfred Locker, Hongkong Post, SF Locker, Convenience Store, etc.) so merchants can enjoy full-set features of logistics automation which disrupt the manual logistics process and bring E-Commerce to new generation.
Version: 1.0.69
Author: ShipAny
Author URI: https://www.shipany.io
License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'activated_plugin', 'shipany_activation_redirect');
add_action( 'woocommerce_after_shipping_rate', 'addClickAndCollectWidget',  10, 2 );
add_action( 'wp_enqueue_scripts', 'themeslug_enqueue_script' );
add_filter( 'woocommerce_cart_shipping_method_full_label', 'rename_popup_local_pickup', 10, 2 );
add_filter( 'woocommerce_package_rates', 'hide_shipping_methods', 8, 1 );
add_filter( 'woocommerce_package_rates', 'customizing_shipping_methods', 9, 2 );
add_action( 'woocommerce_package_rates', 'shipany_blocks', 10, 1 );

function shipany_blocks($rates) {
	if ( in_array( 'woo-gutenberg-products-block/woocommerce-gutenberg-products-block.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		wp_enqueue_script(
			'wc-shipment-blocks',
			SHIPANY_PLUGIN_DIR_URL . '/assets/js/shipany-blocks.js',
			array( 'jquery' ),
			SHIPANY_VERSION
		);

		wp_localize_script( 'wc-shipment-blocks', 'shipany_setting', array() );
	} 
	return $rates;
}

$GLOBALS['Courier_uid_mapping'] = array('SF Express' => array('6ae8b366-0d42-49c8-a543-71823226204f', '5ec1c56d-c2cd-4e41-a83d-ef11b0a0fefe', 'b92add3c-a9cb-4025-b938-33a2e9f7a3a7'),
                                        'UPS' => array('c7f6452b-567f-42c9-9007-2bdbc8cbea15', 'afed5748-dbb5-44db-be22-3b9a28172cd9', 'afed5748-dbb5-44db-be22-3b9a28172cd9'),
                                        'ZeekDash' => array('cb6d3491-1215-420f-beb1-dbfa6803d89c', 'cb2f0d03-cb53-4a2b-9539-e245f5df05b7', '94c7dbc2-e200-43d5-a2b8-1423b91fa2a4'),
                                        'Lalamove' => array('37577614-9806-4e1c-9163-53b0b2d8163f', 'c6175c19-ef5c-44b1-b719-ce784a96025c', '2cef1de7-841b-4493-a681-5d2694a8faee'),
                                        'ZTO Express' => array('540013ae-1d5f-4688-b03a-772d38bd257d', 'ad4b9127-5126-4247-9ff8-d7893ae2d2bb', 'f3d59685-0994-49cc-be4d-42af1d9557fe'),
                                        'Hongkong Post' => array('93562f05-0de4-45cb-876b-c1e449c09d77', '167ba23f-199f-41eb-90b9-a231f5ec2436', '83b5a09a-a446-4b61-9634-5602bf24d6a0'),
                                        'Zeek2Door' => array('998d3a95-3c8c-41c9-90d8-8e7bcf95e38d', '85cc2f44-8508-4b46-b49d-28b7b4c65da4', '651bb29b-68a8-402d-bca6-57cf31de065c'),
                                        'HAVI (Cold Chain)' => array('f403ee94-e84b-4574-b340-e734663cdb39', 'c6e80140-a11f-4662-8b74-7dbc50275ce2', '2ba434b5-fa1d-4541-bc43-3805f8f3a26d'),
                                        'Quantium' => array('a9edf457-6515-4111-bcac-738a29d0b58b', '2124fd86-dc2b-4762-acd6-625bd406bbcb', 'ccdf3c16-d34f-4e77-996c-1b00ed8a925e'),
                                        'Zeek' => array('c04cb957-7c8f-4754-ba29-4df0c276392b','fe08c066-acbe-4fac-b395-4289bd0e02d6', '0864b67a-cb87-462a-b4f7-69c30691cdea'),
                                        'Jumppoint' => array('79703b17-4198-472b-80b3-e195cd8250a4', '6e494226-985a-4ca0-bb0b-d5994a051932', '60a9855e-9983-4e1c-ad5f-373d3e25a0f1'),
										'SF Express (Cold Chain)' => array('1d22bb21-da34-4a3c-97ed-60e5e575a4e5','1bbf947d-8f9d-47d8-a706-a7ce4a9ddf52','c74daf26-182a-4889-924b-93a5aaf06e19')
                                       );
// New Multi Checkbox field for woocommerce backend
function weight_convert( $value, $unit, $from_shipany = false) {
	if ($from_shipany == false) {
		if ($unit == 'kg') {
			return $value;
		} else if ($unit == 'g') {
			return $value * 0.001;
		} else if ($unit == 'lbs') {
			return $value * 0.453592;
		} else if ($unit == 'oz') {
			return $value * 0.0283495;
		}
	} else if ($from_shipany == true) {
		if ($unit == 'kg') {
			return $value;
		} else if ($unit == 'g') {
			return $value * 1000;
		} else if ($unit == 'lbs') {
			return round($value * 2.20462, 2);
		} else if ($unit == 'oz') {
			return round($value * 35.274, 2);
		}
	}

}
function woocommerce_wp_multi_checkbox( $field ) {
    global $thepostid, $post;

    if( ! $thepostid ) {
        $thepostid = $post->ID;
    }

    // $field['value'] = get_post_meta( $thepostid, $field['id'], true );
	
    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
    $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : array();
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;

    echo '<fieldset class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
    <legend>' . wp_kses_post( $field['label'] ) . '</legend>';

    if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
        echo wc_help_tip( $field['description'] );
    }

    echo '<ul>';

    foreach ( $field['options'] as $key => $value ) {

        echo '<li><label><input
                name="' . esc_attr( $field['name'] . $value ) . '"
                value="' . esc_attr( $key ) . '"
                type="checkbox"
                class="' . esc_attr( $field['class'] ) . '"
                style="' . esc_attr( $field['style'] ) . '"
                ' . ( is_array( $field['value'] ) && in_array( $key, $field['value'] ) ? 'checked="checked"' : '' ) . ' /> ' . esc_html( $value ) . '</label>
        </li>';
    }
    echo '</ul>';

    if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
        echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
    }

    echo '</fieldset>';
}

function customizing_shipping_methods( $rates, $package )
{
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return $rates;
	$min_cost = SHIPANY()->get_shipping_shipany_settings()['shipany_locker_free_cost'];
	if ($min_cost != '') {
		// // Iterating through Shipping Methods
		foreach ( $rates as $rate_values ) {
			$method_id = $rate_values->method_id;
			$rate_id = $rate_values->id;
			// For "Local pickup" Shipping" Method only
			if ( 'local_pickup' === $method_id ) {
				if($package["contents_cost"] >= floatval($min_cost) || $package['cart_subtotal'] >= floatval($min_cost)) {
					// Set the rate calculated cost based on cart items count
					$rates[$rate_id]->cost = 0;
				}
			}
		}
	}

    return $rates;
}

function hide_shipping_methods( $rates ){

	$default_courier_id = SHIPANY()->get_shipping_shipany_settings()['shipany_default_courier'];
	$max_try = 5;
	$cur_try = 0;
	while ($cur_try < $max_try) {
		$request = wp_remote_get('https://apps.shipany.io/woocommerce/locationList.json');
		if( is_wp_error( $request ) ) {
			$cur_try += 1;
		} else {
			break;
		}
	}
	if ($cur_try >= 5) {
		return $rates;
	}
	$request_body_decode = json_decode($request['body']);
	$couriers = $request_body_decode->couriers;
	$courier_exist_in_list = false;
	foreach($couriers as $courier) {
		if ($courier->courier_id == $default_courier_id) {
			$courier_exist_in_list = true;
			break;
		}
	}
	
	if (!$courier_exist_in_list) {
		foreach($rates as $rate_id => $rate) { 
			if ('local_pickup' !== $rate->method_id) {
				$rates_arr[ $rate_id ] = $rate;
			}
		}
	}

    return !empty( $rates_arr ) ? $rates_arr : $rates;
}

function rename_popup_local_pickup( $label, $method ) {

	// call list in checkout page
	?>
	<div style='display:none !important'>
	<script type="text/javascript">

	var closeModalNew = () => {
		var modal_new = document.getElementById("shipany-woo-plugin-modal");
		modal_new.classList.remove("shipany-woo-plugin-showModal");
		var radioBtns = document.querySelectorAll('input[type="radio"]');

		radioBtns.forEach((item) => {
			item.style.display = null;
		});
	};

	var shipping_methods = document.getElementsByClassName('shipping_method')
	for (let shipping_method of shipping_methods) {
		if (!shipping_method.id.includes('local')) {
			shipping_method.onclick = closeModalNew
		}
	}
	
	function trigger_list() {
		jQuery('.wc-proceed-to-checkout').css('pointer-events','none');
		jQuery('.wc-proceed-to-checkout').css('opacity','0.5');
		setTimeout(function() {
			jQuery('.wc-proceed-to-checkout').css('pointer-events','');
			jQuery('.wc-proceed-to-checkout').css('opacity','1');
		}, 5000);
		jQuery('input[name="shipany_locker_collect"]').click();
	}
	var att = document.createAttribute('onclick')
	att.value = "trigger_list()"
	pickupRadioButton = document.querySelector('[id^="shipping_method_0_local_pickup"]')
    if(pickupRadioButton){
        pickupRadioButton.setAttributeNode(att)
    }
    if (window.location.href.includes('checkout')) {
        let aTag = document.getElementById('onChangeLocation');
        // add change component
        const createChangeLocationElement = function () {
            var div = document.createElement('div');
            div.style.marginLeft = '6px'
            div.style.display = 'inline'
            let defaultLabelName = 'Change address'
            if (window?.shipany_setting?.shipany_enable_locker_list2_1) {

				defaultLabelName = shipany_setting.shipany_enable_locker_list2_1

			}
			
            var componentButtonTemplate = `
        <div>
        <a style="cursor: pointer;" id="onChangeLocation">${defaultLabelName}</a>
        </div>
    `		
            div.innerHTML = componentButtonTemplate.trim();
			if (document.querySelector('[for^="shipping_method_0_local_pickup"]') != null) {
				document.querySelector('[for^="shipping_method_0_local_pickup"]').parentNode.insertBefore(
                div, document.querySelector('[for^="shipping_method_0_local_pickup"]').nextSibling
                   )
			}
            aTag = document.getElementById('onChangeLocation');
            return new Promise((resolve => {
                resolve(aTag)
            }))
        }
        if(!aTag){
            createChangeLocationElement().then(elem => {
                if (elem) {
                    elem.addEventListener('click', () => {
                        trigger_list()
                    })
                }
            })
        }
		if (document.getElementById("shipping_method").getElementsByTagName("li").length == 1) {
			var shipping_method_ori = document.querySelector('[for^="shipping_method_"]')
			if (shipping_method_ori != null && shipping_method_ori.outerHTML.includes('local_pickup')) {
				shipping_method_ori.style.display = 'none'
				var textContent = shipping_method_ori.textContent
				document.getElementById('onChangeLocation').text = textContent
				document.getElementById('onChangeLocation').style.color = 'blue'
				document.getElementById('onChangeLocation').style.textDecoration = 'underline'
			}
		}
    }

	</script>
	</div>
	<?php
	if ( 'local_pickup' === $method->method_id && !isset($_COOKIE['LocalPickUpExisted'])) {
		if (array_key_exists('shipany_enable_locker_list2',SHIPANY()->get_shipping_shipany_settings())){
			$label = SHIPANY()->get_shipping_shipany_settings()['shipany_enable_locker_list2']? SHIPANY()->get_shipping_shipany_settings()['shipany_enable_locker_list2']:"Pick up at locker/store";
		} else {
			$label = "Pick up at locker/store";
		}
		if ($method->cost > 0) {
			$label = $label . ': $' . strval($method->cost);
		}
		$_COOKIE['LocalPickUpExisted'] = true;
	}
	return $label;
}

function shipany_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        if(wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipany_ecs_asia' ))){
            exit;
        }
    }
}

 function addClickAndCollectWidget($method, $index){
 	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
 	$chosen_shipping = $chosen_methods[0];
	
	if (strpos($chosen_shipping, 'local_pickup') === 0 || strpos($method->get_id(), 'local_pickup') === 0) {
		include ("pages/click-collect-widget.php");
	}
 	    
 }

 function themeslug_enqueue_script() {
 	wp_enqueue_script( 'easywidgetjs', plugin_dir_url( __FILE__ ) . "pages/easywidgetSDK/easywidget.js?" . time(), array('jquery'), null, true );
 	$script_params = array( 'path' =>  plugin_dir_url( __FILE__ ), 'courier_id' => isset(get_option('woocommerce_shipany_ecs_asia_settings')['shipany_default_courier'])? get_option('woocommerce_shipany_ecs_asia_settings')['shipany_default_courier'] : "", 'lang' => strval(get_locale()) );
 	wp_localize_script( 'easywidgetjs', 'scriptParams', $script_params );
	wp_enqueue_style( 'wc-shipment-shipany-label-css', SHIPANY_PLUGIN_DIR_URL . '/assets/css/shipany-admin.css' );
	wp_enqueue_script(
		'wc-shipment-rename-localpickup-js',
		SHIPANY_PLUGIN_DIR_URL . '/assets/js/shipany-rename-localpickup.js',
		array( 'jquery' ),
		SHIPANY_VERSION
	);
	$temp_setting = SHIPANY()->get_shipping_shipany_settings();
	if (isset($temp_setting['shipany_api_key'])) {
		unset($temp_setting['shipany_api_key']);
	}
	if ( in_array( 'woo-gutenberg-products-block/woocommerce-gutenberg-products-block.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		include ("pages/click-collect-widget.php");
	} 
	wp_localize_script( 'wc-shipment-rename-localpickup-js', 'shipany_setting', $temp_setting );
 }

if ( ! class_exists( 'SHIPANY_WC' ) ) :

class SHIPANY_WC {
	public static $list;
	private $version = "1.0.69";

	protected static $_instance = null;
	
	public $shipping_shipany_order = null;
	
	// protected $shipping_shipany_product = null;

	protected $logger = null;

	private $payment_gateway_titles = array();

	protected $base_country_code = '';

	// 'LI', 'CH', 'NO'
	protected $eu_iso2 = array( 'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SI', 'SK', 'ES', 'SE');

	protected $us_territories = array( 'US', 'GU', 'AS', 'PR', 'UM', 'VI' );
		
	/**
	* Construct the plugin.
	*/
	public function __construct() {
        add_action( 'init', array( $this, 'load_plugin' ), 0 );

		$upload_dir =  wp_upload_dir();
		if ( $file_handle = @fopen( trailingslashit( $upload_dir['basedir'] . '/woocommerce_shipany_label' ) . '.htaccess', 'w' ) ) {
			fwrite( $file_handle, '' );
			fclose( $file_handle );
		}
		global $COURIER_LALAMOVE;
		$COURIER_LALAMOVE = [
			'37577614-9806-4e1c-9163-53b0b2d8163f',
			'f3bbaf88-e389-4f70-b70e-979c508da4c9',
			'c6175c19-ef5c-44b1-b719-ce784a96025c',
			'2cef1de7-841b-4493-a681-5d2694a8faee'
		];
    }

	/**
	 *
	 * Ensures only one instance is loaded or can be loaded.
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Define WC Constants.
	 */
	private function define_constants() {
		$upload_dir = wp_upload_dir();

		// Path related defines
		$this->define( 'SHIPANY_PLUGIN_FILE', __FILE__ );
		$this->define( 'SHIPANY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'SHIPANY_PLUGIN_DIR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		$this->define( 'SHIPANY_PLUGIN_DIR_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

		$this->define( 'SHIPANY_VERSION', $this->version );

		$this->define( 'SHIPANY_LOG_DIR', $upload_dir['basedir'] . '/wc-logs/' );

		$this->define( 'SHIPANY_ECS_ASIA_TRACKING_URL', 'https://portal.shipany.io/tracking?id=' );
		$this->define( 'PR_SHIPANY_BUTTON_TEST_CONNECTION', __( 'Test Connection', 'shipany-for-woocommerce' ) );
	}
	
	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		// Auto loader class
		include_once( 'includes/class-shipany-autoloader.php' );
		// Load abstract classes
		include_once( 'includes/abstract-shipany-wc-order.php' );
		include_once ("lib/PDFMerger-master/PDFMerger.php");

		// Composer autoloader
		include_once( 'vendor/autoload.php' );
	}

	/**
	* Determine which plugin to load.
	*/
	public function load_plugin() {
		// Checks if WooCommerce is installed.
		if ( class_exists( 'WC_Shipping_Method' ) ) {
			$this->base_country_code = $this->get_base_country();

			$shipany_parcel_countries = array('NL', 'BE', 'LU');

			if (!in_array($this->base_country_code, $shipany_parcel_countries) || apply_filters('shipping_shipany_bypass_load_plugin', false)) {
				$this->define_constants();
				$this->includes();
				$this->init_hooks();
                $this->init_ajax_action();
			}
		} else {
			// Throw an admin error informing the user this plugin needs WooCommerce to function
			add_action( 'admin_notices', array( $this, 'notice_wc_required' ) );
		}

	}

    /**
     * Initialize the plugin.
     */
    public function init() {
        add_action( 'admin_notices', array( $this, 'environment_check' ) );
        // $this->get_shipany_wc_product();
        $this->get_shipany_wc_order();
    }

    public function init_hooks() {
        add_action( 'init', array( $this, 'init' ), 1 );
        add_action( 'init', array( $this, 'load_textdomain' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'shipany_enqueue_scripts') );

        add_action( 'woocommerce_shipping_init', array( $this, 'includes' ) );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );

		// add_filter( 'woocommerce_states', array( $this, 'custom_woocommerce_states' ));
		add_action( 'wp_ajax_test_shipany_connection', array( $this, 'test_shipany_connection_callback' ) );
		add_action( 'wp_ajax_update_default_courier', array( $this, 'update_default_courier' ) );
    }

	public function update_default_courier() {
		check_ajax_referer( 'shipany-test-con', 'test_con_nonce' );
		$api_key_temp= $_POST['val'];


		try {

			$shipany_obj = $this->get_shipany_factory();
			// $response = $shipany_obj->api_client->get_test_con('couriers/',$api_key_temp);
			// if ( $response->status != 200 ) {
			// 	return;
			// }
			$connection_msg = __('Connection Successful!', 'shipany-for-woocommerce');
			$this->log_msg( $connection_msg );
			$courier_list = $response->body->data->objects;
			self::$list=$courier_list;
			
			
			wp_send_json( array( 
				'connection_success' 	=> $connection_msg,
				'button_txt'			=> PR_SHIPANY_BUTTON_TEST_CONNECTION,
				'courier_list'			=> $courier_list
				) );
				
		} catch (Exception $e) {
			$this->log_msg($e->getMessage());

			wp_send_json( array( 
				'connection_error' => sprintf( __('Connection Failed: %s Make sure to save the settings before testing the connection. ', 'shipany-for-woocommerce'), $e->getMessage() ),
				'button_txt'			=> PR_SHIPANY_BUTTON_TEST_CONNECTION
				 ) );
		}

		wp_die();
	}


	public function test_shipany_connection_callback() {

		$api_key_temp= $_POST['val'];
		$region = $_POST['region'];
        $temp_api_endpoint = 'https://api.shipany.io/';
		//dev3
        if (strpos($api_key_temp, 'SHIPANYDEV') !== false){
            $temp_api_endpoint = 'https://api-dev3.shipany.io/';
            $api_key_temp = str_replace("SHIPANYDEV", "", $api_key_temp);
        }

		//demo1
        if (strpos($api_key_temp, 'SHIPANYDEMO') !== false){
            $temp_api_endpoint = 'https://api-demo1.shipany.io/';
            $api_key_temp = str_replace("SHIPANYDEMO", "", $api_key_temp);
        }

		//sbx1
        if (strpos($api_key_temp, 'SHIPANYSBX1') !== false){
            $temp_api_endpoint = 'https://api-sbx1.shipany.io/';
            $api_key_temp = str_replace("SHIPANYSBX1", "", $api_key_temp);
        }

		//sbx2
        if (strpos($api_key_temp, 'SHIPANYSBX2') !== false){
            $temp_api_endpoint = 'https://api-sbx2.shipany.io/';
            $api_key_temp = str_replace("SHIPANYSBX2", "", $api_key_temp);
        }

		if ($region == 'Singapore') {
			$temp_api_endpoint = str_replace("api", "api-sg" , $temp_api_endpoint);
		}		

		$response = wp_remote_get($temp_api_endpoint.'merchants/self/', array(
			'headers' => array(
				'api-tk'=> $api_key_temp
			)
		));
		$status_code = wp_remote_retrieve_response_code($response);
		wp_send_json( array( 
				'connection_success' 	=> $status_code
				) );
		// check_ajax_referer( 'shipany-test-con', 'test_con_nonce' );
		// $api_key_temp= $_POST['val'];
		// try {

		// 	$shipany_obj = $this->get_shipany_factory();
		// 	$response = $shipany_obj->api_client->get_merchant_info_test_con($api_key_temp);
		// 	if ( $response->status != 200 ) {
		// 		return;
		// 	}

		// 	$connection_msg = __('Connection Successful!', 'shipany-for-woocommerce');
		// 	$this->log_msg( $connection_msg );

		// 	wp_send_json( array( 
		// 		'connection_success' 	=> $connection_msg,
		// 		'button_txt'			=> PR_SHIPANY_BUTTON_TEST_CONNECTION
		// 		) );

		// } catch (Exception $e) {
		// 	$this->log_msg($e->getMessage());

		// 	wp_send_json( array( 
		// 		'connection_error' => sprintf( __('Connection Failed: %s Make sure to save the settings before testing the connection. ', 'shipany-for-woocommerce'), $e->getMessage() ),
		// 		'button_txt'			=> PR_SHIPANY_BUTTON_TEST_CONNECTION
		// 		 ) );
		// }

		// wp_die();
	}

	public function get_shipany_wc_order() {
		if ( ! isset( $this->shipping_shipany_order ) ){
			try {
				$shipany_obj = $this->get_shipany_factory();
				
				if( $shipany_obj->is_shipany_ecs_asia() ) {
					$this->shipping_shipany_order = new SHIPANY_WC_Order_eCS_Asia();
				}
				// Ensure folder exists
				$this->shipany_label_folder_check();
			} catch (Exception $e) {
				add_action( 'admin_notices', array( $this, 'environment_check' ) );
			}
		}

		return $this->shipping_shipany_order;
	}

	/**
	 * Localisation
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'pr-shipping-shipany', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
	}

	public function shipany_enqueue_scripts() {
		// Enqueue Styles
		wp_enqueue_style( 'wc-shipment-shipany-label-css', SHIPANY_PLUGIN_DIR_URL . '/assets/css/shipany-admin.css' );
        wp_enqueue_style( 'wc-shipment-shipany-loader-css', SHIPANY_PLUGIN_DIR_URL . '/assets/css/shipany-loader.css' );
        wp_enqueue_style( 'wc-shipment-shipany-common-css', SHIPANY_PLUGIN_DIR_URL . '/assets/css/shipany-common.css' );

        // Enqueue Scripts
        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
		$test_con_data = array(
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
			'loader_image'   => admin_url( 'images/loading.gif' ),
			'test_con_nonce' => wp_create_nonce( 'shipany-test-con' ),
		);
		wp_enqueue_script(
			'wc-shipment-shipany-testcon-js',
			SHIPANY_PLUGIN_DIR_URL . '/assets/js/shipany-test-connection.js',
			array( 'jquery' ),
			SHIPANY_VERSION
		);
		wp_localize_script( 'wc-shipment-shipany-testcon-js', 'shipany_test_con_obj', $test_con_data );
	}


	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	public function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Add a new integration to WooCommerce.
	 */
	public function add_shipping_method( $shipping_method ) {
		// Check country somehow
		try {
			$shipany_obj = $this->get_shipany_factory();

			if($shipany_obj->is_shipany_ecs_asia() ) {
				$shipany_ship_meth = 'SHIPANY_WC_Method_eCS_Asia';
				$shipping_method['shipany_ecs'] = $shipany_ship_meth;
			}

		} catch (Exception $e) {
			// do nothing
		}

		return $shipping_method;
	}

	/**
	 * Admin error notifying user that WC is required
	 */
	public function notice_wc_required() {
	?>
		<div class="error">
			<p><?php _e( 'requires WooCommerce to be installed and activated!', 'pr-shipping-shipany' ); ?></p>
		</div>
	<?php
	}

	/**
	 * environment_check function.
	 */
	public function environment_check() {
		// Try to get the shipany object...if exception if thrown display to user, mainly to check country support.
		try {
			$this->get_shipany_factory();
		} catch (Exception $e) {
			echo '<div class="error"><p>' . esc_html($e->getMessage()) . '</p></div>';
		}
	}

	public function get_base_country() {
		$country_code = wc_get_base_location();
		return apply_filters( 'shipping_shipany_base_country', $country_code['country'] );
	}

	/**
	 * Create an object from the factory based on country.
	 */
	public function get_shipany_factory() {

		$base_country_code = $this->get_base_country();
		
		try {	
			$shipany_obj = SHIPANY_API_Factory::make_shipany( $base_country_code );		
		} catch (Exception $e) {
			throw $e;
		}

		return $shipany_obj;
	}

	public function get_shipany_factory_test_con($api_key_temp) {

		$base_country_code = $this->get_base_country();
		
		try {	
			$shipany_obj = SHIPANY_API_Factory::make_shipany_test_con( $base_country_code,$api_key_temp );		
		} catch (Exception $e) {
			throw $e;
		}

		return $shipany_obj;
	}

	public function get_api_url() {

		try {

			$shipany_obj = $this->get_shipany_factory();
			
			if( $shipany_obj->is_shipany_ecs_asia() ) {

				return $shipany_obj->get_api_url();

			}
			
		} catch (Exception $e) {
			throw new Exception('Cannot get shipany api credentials!');			
		}
	}

	public function get_shipping_shipany_settings( ) {
		$shipany_settings = array();

		try {
			$shipany_obj = $this->get_shipany_factory();
			
			if( $shipany_obj->is_shipany_ecs_asia() ) {
				$shipany_settings = $shipany_obj->get_settings();
			}

		} catch (Exception $e) {
			throw $e;
		}

		return $shipany_settings;
	}

	public function log_msg( $msg )	{

		try {
			$shipping_shipnay_settings = $this->get_shipping_shipany_settings();
			$shipany_debug = isset( $shipping_shipnay_settings['shipany_debug'] ) ? $shipping_shipnay_settings['shipany_debug'] : 'yes';
			
			if( ! $this->logger ) {
				$this->logger = new SHIPANY_Logger( $shipany_debug );
			}

			$this->logger->write( $msg );
			
		} catch (Exception $e) {
			// do nothing
		}
	}

	public function get_log_url( )	{

		try {
			$shipping_shipnay_settings = $this->get_shipping_shipany_settings();
			$shipany_debug = isset( $shipping_shipnay_settings['shipany_debug'] ) ? $shipping_shipnay_settings['shipany_debug'] : 'yes';
			
			if( ! $this->logger ) {
				$this->logger = new SHIPANY_Logger( $shipany_debug );
			}
			
			return $this->logger->get_log_url( );
			
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Function return whether the sender and receiver country is the same territory
	 */
	public function is_shipping_domestic( $country_receiver ) {   	 

		// If base is US territory
		if( in_array( $this->base_country_code, $this->us_territories ) ) {
			
			// ...and destination is US territory, then it is "domestic"
			if( in_array( $country_receiver, $this->us_territories ) ) {
				return true;
			} else {
				return false;
			}

		} elseif( $country_receiver == $this->base_country_code ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Function return whether the sender and receiver country is "crossborder" i.e. needs CUSTOMS declarations (outside EU)
	 */
	public function is_crossborder_shipment( $country_receiver ) {

		if ($this->is_shipping_domestic( $country_receiver )) {
			return false;
		}

		// Is sender country in EU...
		if ( in_array( $this->base_country_code, $this->eu_iso2 ) ) {
			// ... and receiver country is in EU means NOT crossborder!
			if ( in_array( $country_receiver, $this->eu_iso2 ) ) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

	/**
     * Installation functions
     *
     * Create temporary folder and files. labels will be stored here as required
     *
     * empty_pdf_task will delete them hourly
     */
    public function create_shipany_label_folder() {
        // Install files and folders for uploading files and prevent hotlinking
        $upload_dir =  wp_upload_dir();

        $files = array(
            array(
                'base'      => $upload_dir['basedir'] . '/woocommerce_shipany_label',
                'file'      => '.htaccess',
                'content'   => ''
            ),
            array(
                'base'      => $upload_dir['basedir'] . '/woocommerce_shipany_label',
                'file'      => 'index.html',
                'content'   => ''
            )
        );

        foreach ( $files as $file ) {

            if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {

                if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
                    fwrite( $file_handle, $file['content'] );
                    fclose( $file_handle );
                }

            }

        }
    }

    public function shipany_label_folder_check() {
        $upload_dir =  wp_upload_dir();
            $this->create_shipany_label_folder();
    }

    public function get_shipany_label_folder_dir() {
        $upload_dir =  wp_upload_dir();
            return $upload_dir['basedir'] . '/woocommerce_shipany_label/';
        return '';
    }

    public function get_shipany_label_folder_url() {
        $upload_dir =  wp_upload_dir();
            return $upload_dir['baseurl'] . '/woocommerce_shipany_label/';
        return '';
    }
    public function init_ajax_action(){
        // hook into admin-ajax
        // the text after 'wp_ajax_' and 'wp_ajax_no_priv_' in the add_action() calls
        // that follow is what you will use as the value of data.action in the ajax
        // call in your JS
        // if the ajax call will be made from JS executed when user is logged into WP,
        // then use this version
        add_action ('wp_ajax_on_change_load_couriers', array($this, 'on_change_load_couriers')) ;
		add_action ('wp_ajax_on_click_update_address', array($this, 'on_click_update_address')) ;
        // if the ajax call will be made from JS executed when no user is logged into WP,
        // then use this version
        add_action ('wp_ajax_nopriv_on_change_load_couriers', array($this, 'on_change_load_couriers'));
        add_action('wp_ajax_set_default_courier', array($this, 'set_default_courier'));
		add_action('wp_ajax_set_default_storage_type', array($this, 'set_default_storage_type'));
        add_action ('wp_ajax_nopriv_on_change_set_default_courier', array($this, 'set_default_courier')) ;
    }

    public function on_click_update_address(){


        $api_tk = SHIPANY()->get_shipping_shipany_settings()['shipany_api_key'];
		
        $temp_api_endpoint = 'https://api.shipany.io/';

		// dev3
        if (strpos($api_tk, 'SHIPANYDEV') !== false){
            $temp_api_endpoint = 'https://api-dev3.shipany.io/';
            $api_tk = str_replace("SHIPANYDEV", "", $api_tk);
        }

		//demo1
        if (strpos($api_tk, 'SHIPANYDEMO') !== false){
            $temp_api_endpoint = 'https://api-demo1.shipany.io/';
            $api_tk = str_replace("SHIPANYDEMO", "", $api_tk);
        }

		//sbx1
        if (strpos($api_tk, 'SHIPANYSBX1') !== false){
            $temp_api_endpoint = 'https://api-sbx1.shipany.io/';
            $api_tk = str_replace("SHIPANYSBX1", "", $api_tk);
        }

		//sbx2
        if (strpos($api_tk, 'SHIPANYSBX2') !== false){
            $temp_api_endpoint = 'https://api-sbx2.shipany.io/';
            $api_tk = str_replace("SHIPANYSBX2", "", $api_tk);
        }		

		if (SHIPANY()->get_shipping_shipany_settings()['shipany_region'] == '1') {
			$temp_api_endpoint = str_replace("api", "api-sg" , $temp_api_endpoint);
		}
		$response = wp_remote_get($temp_api_endpoint.'merchants/self/', array(
			'headers' => array(
				'api-tk'=> $api_tk
			)
		));
		if (wp_remote_retrieve_response_code($response) == 200) {
			// $merchant_info = json_decode($merchant_resp['body'])->data->objects[0];
			$merchant_info = $response['body'];
			$address = json_decode($merchant_info)->data->objects[0]->co_info->org_ctcs[0]->addr;
			$update = get_option('woocommerce_shipany_ecs_asia_settings');
			$update['merchant_info'] = $merchant_info;
			update_option('woocommerce_shipany_ecs_asia_settings', $update);
			wp_send_json_success (array('success' => true, 'address_line1' => $address->ln, 'address_line2' => $address->ln2, 'distr' => $address->distr, 'cnty' => $address->cnty));
		}
    }

    public function on_change_load_couriers(){
        if (!isset ($_POST['api_tk'])) {
            // set the return value you want on error
            // return value can be ANY data type (e.g., array())
            $return_value = 'Invalid API token' ;

            wp_send_json_error ($return_value) ;
        }
        $region = $_POST['region'];
        $api_tk = $_POST['api_tk'];
        $temp_api_endpoint = 'https://api.shipany.io/';

		// dev3
        if (strpos($api_tk, 'SHIPANYDEV') !== false){
            $temp_api_endpoint = 'https://api-dev3.shipany.io/';
            $api_tk = str_replace("SHIPANYDEV", "", $api_tk);
        }

		//demo1
        if (strpos($api_tk, 'SHIPANYDEMO') !== false){
            $temp_api_endpoint = 'https://api-demo1.shipany.io/';
            $api_tk = str_replace("SHIPANYDEMO", "", $api_tk);
        }

		//sbx1
        if (strpos($api_tk, 'SHIPANYSBX1') !== false){
            $temp_api_endpoint = 'https://api-sbx1.shipany.io/';
            $api_tk = str_replace("SHIPANYSBX1", "", $api_tk);
        }

		//sbx2
        if (strpos($api_tk, 'SHIPANYSBX2') !== false){
            $temp_api_endpoint = 'https://api-sbx2.shipany.io/';
            $api_tk = str_replace("SHIPANYSBX2", "", $api_tk);
        }		
		if ($region == 'Singapore') {
			$temp_api_endpoint = str_replace("api", "api-sg" , $temp_api_endpoint);
		}
        $response = wp_remote_get($temp_api_endpoint.'couriers/', array(
            'headers' => array(
                'api-tk'=> $api_tk
            )
        ));
        $status_code = wp_remote_retrieve_response_code($response);
		if (empty($status_code)) {
            $return_value = array(
                'success' => false,
                'data' => array(
                    'error_title' => 'no endpoint',
                    'error_detail' => 'no endpoint'
                )
            );

            wp_send_json_error ($return_value);
		}
        $body = wp_remote_retrieve_body($response);
        if ($status_code !== 200){
            $resp_body = json_decode($body);
            $error_title = $resp_body->result->descr;
            $error_detail = implode('.', $resp_body->result->details);
            $return_value = array(
                'success' => false,
                'data' => array(
                    'error_title' => $error_title,
                    'error_detail' => $error_detail
                )
            );

            wp_send_json_error ($return_value);
        }

        $body = json_decode($body)->data->objects;
        // do processing you want based on $id
        $rv_cour_list = array();
        foreach ($body as $key => $value){
            $rv_cour_list[$value->uid] = $value->name;
        }
        // set the return value you want on success
        // return value can be ANY data type (e.g., array())


		$response_merchant = wp_remote_get($temp_api_endpoint.'merchants/self/', array(
			'headers' => array(
				'api-tk'=> $api_tk
			)
		));	
		$status_code = wp_remote_retrieve_response_code($response_merchant);
		$body = '';
		if ($status_code == 200) {
			$body = wp_remote_retrieve_body($response_merchant);
			$body = json_decode($body)->data->objects;
			if($body[0]->activated !== true){
				wp_send_json_error ([ 'data' => [
					'error_title' => __('Please activate your account first.', 'pr-shipping-shipany'),
					'error_detail' => ''
				]]) ;
				return false;
			}
		}
        $return_value = array( 'success' => true, 'data' => $rv_cour_list, 'asn_mode' => $body[0]->asn_mode);

        wp_send_json_success ($return_value) ;
    }

    public function set_default_storage_type(){
        $storage_value = $_POST['storage_value'];
		$storage_name = $_POST['storage_name'];
        $is_success = set_transient('temp_storage_value', $storage_value, 600);
		$is_success_name = set_transient('temp_storage_name', $storage_name, 600);

        $return_value = array( 'success' => $is_success);
        wp_send_json_success ($return_value) ;
    }

    public function set_default_courier(){
        $default_courier = $_POST['cour_uid'];
		$default_courier_name = $_POST['cour_name'];
		$temp_key = $_POST['temp_key'];
        $is_success = set_transient('temp_dflt_cour', $default_courier, 600);
		$is_success_name = set_transient('temp_dflt_cour_name', $default_courier_name, 600);
        $return_value = array( 'success' => $is_success);
        wp_send_json_success ($return_value) ;
    }

}

endif;

if( ! function_exists('SHIPANY') ) {

	/**
	 * Activation hook.
	 */
	function shipany_activate() {
		// Flag for permalink flushed
		update_option('shipany_permalinks_flushed', 0);
	}
	register_activation_hook( __FILE__, 'shipany_activate' );

	function SHIPANY() {
		return SHIPANY_WC::instance();
	}

	$SHIPANY_WC = SHIPANY();
}
