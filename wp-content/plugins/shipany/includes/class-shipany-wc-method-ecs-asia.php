<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// // Create hidden checkout field type
// add_filter( 'woocommerce_form_field_hidden', 'create_checkout_hidden_field_type', 5, 4 );
// function create_checkout_hidden_field_type( $field, $key, $args, $value ){
//     return '<input type="hidden" name="'.esc_attr($key).'" id="'.esc_attr($args['id']).'" value="'.esc_attr($args['default']).'" />';
// }

/**
 * Shipping Method.
 */

if ( ! class_exists( 'SHIPANY_WC_Method_eCS_Asia' ) ) :

class SHIPANY_WC_Method_eCS_Asia extends WC_Shipping_Method {

	/**
	 * Init and hook in the integration.
	 */
	public function __construct( $instance_id = 0 ) {
		// add_action( 'init', array( $this, 'init' ), 0 );
		$this->id = 'shipany_ecs_asia';
		$this->instance_id = absint( $instance_id );
		$this->method_title = __( 'ShipAny', 'pr-shipping-shipany' ); #shipany
		$this->init();
	}

	/**
	 * init function.
	 */
	private function init() {
		
		// add_action( 'wp_ajax_update_default_courier', array( $this, 'update_default_courier' ) );
		// Load the settings.
		$this->init_settings();
		$this->init_form_fields();
		// $this->init_settings();
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		// add_action( 'woocommerce_after_settings_shipping', array( $this, 'after_load_shipping_page'));
		if (isset($_REQUEST["page"]) && $_REQUEST["page"] =='wc-settings') {
			wp_enqueue_script( 'wc-shipany-setting-js', SHIPANY_PLUGIN_DIR_URL . '/assets/js/shipany-setting.js', array('jquery'), SHIPANY_VERSION );
			wp_localize_script( 'wc-shipany-setting-js', 'shipany_setting_val', $this->get_params_to_rest() + array('courier_show_paid_by_rec' => $this->settings["courier_show_paid_by_rec"]) + array('store_url' => home_url()) );
			wp_enqueue_script( 'wc-shipany-setting-js-md5', SHIPANY_PLUGIN_DIR_URL . '/assets/js/md5.js', array('jquery'), SHIPANY_VERSION );
			wp_localize_script( 'wc-shipany-setting-js-md5', 'shipany_setting_val2', array() );
		}
		// wp_enqueue_script( 'wc-shipany-setting-js', SHIPANY_PLUGIN_DIR_URL . '/assets/js/TestConnection.js', array('jquery'), SHIPANY_VERSION );
		// wp_localize_script( 'wc-shipany-setting-js', 'shipany_label_data', $dump );
        wp_enqueue_script( 'wc-dialog-js', SHIPANY_PLUGIN_DIR_URL . '/assets/js/dialog.js', array('jquery'), SHIPANY_VERSION );
		wp_enqueue_script( 'wc-dialog-sendpickup-js', SHIPANY_PLUGIN_DIR_URL . '/assets/js/dialog-sendpickup.js', array('jquery'), SHIPANY_VERSION );
    }

	private function get_params_to_rest() {
		//TODO: Add parmams for shipping area
		$has_token = false;
		if (isset($this->settings["shipany_api_key"]) && $this->settings["shipany_api_key"] !=''){
			$api_tk = $this->settings["shipany_api_key"];
			$temp_api_endpoint = 'https://api.shipany.io/';
			//dev3
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
			if ($this->settings['shipany_region'] == '1') {
				$temp_api_endpoint = str_replace("api", "api-sg" , $temp_api_endpoint);
			}
			$merchant_resp = wp_remote_get($temp_api_endpoint.'merchants/self/', array(
				'headers' => array(
					'api-tk'=> $api_tk
				)
			));
			if (wp_remote_retrieve_response_code($merchant_resp) == 200) {
				// $merchant_info = json_decode($merchant_resp['body'])->data->objects[0];
				$merchant_info = $merchant_resp['body'];
				$merchant_info_decode = json_decode($merchant_info);
				if (json_decode($merchant_info)->data->objects[0]->asn_mode == "Disable") {
					update_option('shipany_has_asn', false);
				} else {
					update_option('shipany_has_asn', true);
				}
				$stores = json_decode($merchant_info)->data->objects[0]->configs->stores;
				foreach($stores as $store) {
					if ($store->pltf =='woocommerce'){
						if ($store->token !='') {
							$has_token = true;
							break;
						}
					}
				}
			}
		}
		
		$rv = array();
		$rv['ajax_url'] = admin_url('admin-ajax.php');
		$rv['rest_url'] = get_rest_url();
		if (isset($this->settings["merchant_info"]) && $this->settings["merchant_info"] != '') {
			$merchant_info = json_decode($this->settings["merchant_info"])->data->objects[0];
			$rv['mch_uid'] = $merchant_info->uid;
		} else {
			$rv['mch_uid'] = '';
		}
		$rv['shipany_api_key'] = isset($this->settings["shipany_api_key"])? $this->settings["shipany_api_key"] : '';
		$rv['has_token'] = $has_token;
		return $rv;
	}

	/**
	 * Get message
	 * @return string Error
	 */
	private function get_message( $message, $type = 'notice notice-error is-dismissible' ) {

		ob_start();
		?>
		<div class="<?php echo esc_attr($type) ?>">
			<p><?php echo esc_attr($message) ?></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$log_path = SHIPANY()->get_log_url();
		// header("Location: http://localhost/appcider/wc-auth/v1/authorize?app_name=My+App+Name&scope=write&user_id=1&return_url=http%3A%2F%2Fgoogle.com&callback_url=https%3A%2F%2Fwebhook.site%2F510a1c48-01d8-465a-b921-5d253c6572ea");
		try {
			$select_shipany_courier_int = '<empty>';
			$lalamove_addons_name_key_pair = array();
			if (isset($_GET["section"])){
				if ($_GET["section"] == "shipany_ecs_asia"){
					$shipany_obj = SHIPANY()->get_shipany_factory();
					$select_shipany_courier_int = $shipany_obj->get_shipany_courier();

					$courier_uid_name_key_pair = array();
					$lalamove_addons = '';
					
					$courier_show_paid_by_rec = array();
					foreach ($select_shipany_courier_int as $key => $value){
						if ($value->name == 'Lalamove') {
							$courier_uid_name_key_pair[$value->uid] = $value->name;
							$lalamove_addons = $value->cour_svc_plans;
						} else {
							$courier_uid_name_key_pair[$value->uid] = $value->name;
						}
						if ($value->cour_props->delivery_services->paid_by_rcvr) {
							array_push($courier_show_paid_by_rec,$value->uid);
						}
					}
					$select_shipany_courier_int = $courier_uid_name_key_pair;
					if ($lalamove_addons !=''){
						foreach ($lalamove_addons as $key => $value) {
							$lalamove_addons_name_key_pair[$value->cour_svc_pl] = $value->cour_svc_pl;
						}
					}
					$this->settings['courier_show_paid_by_rec'] = $courier_show_paid_by_rec;
				}
			}


		} catch (Exception $e) {
			SHIPANY()->log_msg( __('Products not displaying - ', 'pr-shipping-shipany') . $e->getMessage() );
		}
		
		$weight_units = get_option( 'woocommerce_weight_unit' );

		if (isset($this->settings["shipany_default_courier"]) && in_array($this->settings["shipany_default_courier"],array('c6e80140-a11f-4662-8b74-7dbc50275ce2','f403ee94-e84b-4574-b340-e734663cdb39','7b3b5503-6938-4657-acab-2ff31c3a3f45','2ba434b5-fa1d-4541-bc43-3805f8f3a26d','1d22bb21-da34-4a3c-97ed-60e5e575a4e5','1bbf947d-8f9d-47d8-a706-a7ce4a9ddf52','c74daf26-182a-4889-924b-93a5aaf06e19'))) {
			$this->form_fields = array(
				'shipany_api'           => array(
					'title'           => __( 'API Settings', 'pr-shipping-shipany' ),
					'type'            => 'title',
					'description'     => __( 'Please find the information from ShipAny portal, no account? ', 'pr-shipping-shipany' ),
					'class'			  => 'shipany-register-descr',
				),
				'shipany_region' => array(
					'title'             => __( 'Region', 'pr-shipping-shipany' ),
					'type'              => 'select',
					'description'       => __( 'Please select region.', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
					'options'           => array('Hong Kong', 'Singapore'),
					'class'				=> 'wc-enhanced-select shipany-region',
					'custom_attributes' => array( 'required' => 'required' )
				),
				'shipany_api_key' => array(
					'title'             => __( 'API Token', 'pr-shipping-shipany' ),
					'type'              => 'text',
					'description'       => __( 'The API Token (a 36 digits alphanumerical string made from 5 blocks) is required for authentication and you could find it in "Settings" after login ShipAny portal.', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
					'custom_attributes' => array( 'required' => 'required' ),
					'default'           => '',
				),
				'shipany_test_connection_button' => array(
					'title'             => PR_SHIPANY_BUTTON_TEST_CONNECTION,
					'type'              => 'button',
					'custom_attributes' => array(
						'onclick' => "shipanyTestConnection('woocommerce_shipany_ecs_asia_shipany_test_connection_button');"
					),
					'description'       => __( 'Press the button for testing the connection.', 'shipany-for-woocommerce' ),
					'desc_tip'          => true,
				),
				'shipany_default_courier' => array(
					'title'             => __( 'Default Courier', 'pr-shipping-shipany' ),
					'type'              => 'select',
					'description'       => __( 'Please select default courier (You could see a list of available courier after you fill in the API Token and save).', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
					'options'           => $select_shipany_courier_int,
					'class'				=> 'wc-enhanced-select default-courier-selector',
					'custom_attributes' => array( 'required' => 'required' )
				),
				'set_default_storage_type' => array(
					'title'             => __( 'Default Temperature Type', 'pr-shipping-shipany' ),
					'type'              => 'select',
					'description'       => __( 'Please select default temperature type (Please keep as Normal for non-Cold-Chain courier).', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
					'options'  => array(
						'Air Conditioned'        => __( 'Air Conditioned (17°C - 22°C)', 'woocommerce' ),
						'Chilled' => __( 'Chilled (0°C - 4°C)', 'woocommerce' ),
						'Frozen'   => __( 'Frozen (-18°C - -15°C)', 'woocommerce' ),
					),
					'class'				=> 'wc-enhanced-select default-storage-type',
				),
				'set_default_create' => array(
					'title'             => __( 'Auto Create ShipAny Order', 'pr-shipping-shipany' ),
					'type'              => 'checkbox',
					'label'             => __( ' ', 'pr-shipping-shipany' ),
					'default'           => 'no',
					'description'       => __( 'Please tick here if you want to create ShipAny Order automatically for a new WooCommerce Order', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
				),
				'set_default_create' => array(
					'title'             => __( 'Auto Create ShipAny Order', 'pr-shipping-shipany' ),
					'type'              => 'checkbox',
					'label'             => __( ' ', 'pr-shipping-shipany' ),
					'default'           => 'no',
					'description'       => __( 'Please tick here if you want to create ShipAny Order automatically for a new WooCommerce Order', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
				),
				'shipany_tracking_note_enable' => array(
					'title'             => __( 'Enable writing the Tracking Note to Order notes', 'pr-shipping-shipany' ),
					'type'              => 'checkbox',
					'label'             => __( ' ', 'pr-shipping-shipany' ),
					'default'           => 'yes',
					'description'       => __( 'Please tick here if you want to write the Tracking Note to WooCommerce Order notes', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
				),
				'shipany_tracking_note_txt' => array(
					'title'             => __( 'Tracking Note Text', 'pr-shipping-shipany' ),
					'type'            	=> 'text',
					'default'  			=> 'ShipAny Tracking Number:',
					'placeholder'		=> 'Tracking note prefix in order details page',
					'label'             => __( ' ', 'pr-shipping-shipany' ),
					'description'       => __( 'Customize tracking note text in order details page.'),
					'desc_tip'          => true,
					'class'				=> 'shipany_tracking_note'
				),	
				'shipany_customize_order_id' => array(
					'title'             => __( 'ShipAny Order Ref Suffix', 'pr-shipping-shipany' ),
					'type'            	=> 'text',
					'description'       => __( 'Specify a string to be appended to WooCommerce Order ID to form the Order Ref when an order is created onto ShipAny. This is particularly useful if you have multiple WooCommerce stores connected.'),
					'desc_tip'          => true
				),	
				'merchant_info' => array(
					'type'              => 'hidden',
					'default'           => '',
				),
			);
		} else {
			$this->form_fields = array(
				'shipany_api'           => array(
					'title'           => __( 'API Settings', 'pr-shipping-shipany' ),
					'type'            => 'title',
					'description'     => __( 'Please find the information from ShipAny portal, no account? ', 'pr-shipping-shipany' ),
					'class'			  => 'shipany-register-descr',
				),
				'shipany_region' => array(
					'title'             => __( 'Region', 'pr-shipping-shipany' ),
					'type'              => 'select',
					'description'       => __( 'Please select region.', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
					'options'           => array('Hong Kong', 'Singapore'),
					'class'				=> 'wc-enhanced-select shipany-region',
					'custom_attributes' => array( 'required' => 'required' )
				),
				'shipany_api_key' => array(
					'title'             => __( 'API Token', 'pr-shipping-shipany' ),
					'type'              => 'text',
					'description'       => __( 'The API Token (a 36 digits alphanumerical string made from 5 blocks) is required for authentication and you could find it in "Settings" after login ShipAny portal.', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
					'custom_attributes' => array( 'required' => 'required' ),
					'default'           => '',
				),
				'shipany_test_connection_button' => array(
					'title'             => PR_SHIPANY_BUTTON_TEST_CONNECTION,
					'type'              => 'button',
					'custom_attributes' => array(
						'onclick' => "shipanyTestConnection('woocommerce_shipany_ecs_asia_shipany_test_connection_button');"
					),
					'description'       => __( 'Press the button for testing the connection.', 'shipany-for-woocommerce' ),
					'desc_tip'          => true,
				),
				'shipany_default_courier' => array(
					'title'             => __( 'Default Courier', 'pr-shipping-shipany' ),
					'type'              => 'select',
					'description'       => __( 'Please select default courier (You could see a list of available courier after you fill in the API Token and save).', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
					'options'           => $select_shipany_courier_int,
					'class'				=> 'wc-enhanced-select default-courier-selector',
					'custom_attributes' => array( 'required' => 'required' )
				),
				'set_default_storage_type' => array(
					'title'             => __( 'Default Temperature Type', 'pr-shipping-shipany' ),
					'type'              => 'select',
					'description'       => __( 'Please select default temperature type (Please keep as Normal for non-Cold-Chain courier).', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
					'options'  => array(
						'Normal'        => __( 'Normal', 'woocommerce' )
					),
					'class'				=> 'wc-enhanced-select default-storage-type',
				),
				'set_default_create' => array(
					'title'             => __( 'Auto Create ShipAny Order', 'pr-shipping-shipany' ),
					'type'              => 'checkbox',
					'label'             => __( ' ', 'pr-shipping-shipany' ),
					'default'           => 'no',
					'description'       => __( 'Please tick here if you want to create ShipAny Order automatically for a new WooCommerce Order', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
				),
				'shipany_tracking_note_enable' => array(
					'title'             => __( 'Enable writing the Tracking Note to Order notes', 'pr-shipping-shipany' ),
					'type'              => 'checkbox',
					'label'             => __( ' ', 'pr-shipping-shipany' ),
					'default'           => 'yes',
					'description'       => __( 'Please tick here if you want to write the Tracking Note to WooCommerce Order notes', 'pr-shipping-shipany' ),
					'desc_tip'          => true,
				),
				'shipany_tracking_note_txt' => array(
					'title'             => __( 'Tracking Note Text', 'pr-shipping-shipany' ),
					'type'            	=> 'text',
					'default'  			=> 'ShipAny Tracking Number:',
					'placeholder'		=> 'Tracking note prefix in order details page',
					'label'             => __( ' ', 'pr-shipping-shipany' ),
					'description'       => __( 'Customize tracking note text in order details page.'),
					'desc_tip'          => true,
					'class'				=> 'shipany_tracking_note'
				),
				'shipany_customize_order_id' => array(
					'title'             => __( 'ShipAny Order Ref Suffix', 'pr-shipping-shipany' ),
					'type'            	=> 'text',
					'description'       => __( 'Specify a string to be appended to WooCommerce Order ID to form the Order Ref when an order is created onto ShipAny. This is particularly useful if you have multiple WooCommerce stores connected.'),
					'desc_tip'          => true
				),	
				'merchant_info' => array(
					'type'              => 'hidden',
					'default'           => '',
				),
			);
		}

		// append locker setting field v2
		$insert_locker_setting1 = array(
			'title'             => __( 'Enable Locker/Store List', 'pr-shipping-shipany' ),
			'type'            	=> 'text',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'description'       => __( 'Locker/Store List is available for SF Express and ZTO Express'),
			'desc_tip'          => true,
			'class'			  	=> 'shipany-enable-locker'
		);
		$insert_locker_setting2 = array(
			'title'             => __( 'Locker/Store List Display Name', 'pr-shipping-shipany' ),
			'type'            	=> 'text',
			'default'  			=> 'Pick up at locker/store',
			'placeholder'		=> 'Display name in checkout page',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'description'       => __( 'Customize shipping method display name in checkout page.'),
			'desc_tip'          => true,
			'class'			  	=> 'shipany-enable-locker-2'
		);
		$insert_locker_setting2_1 = array(
			'title'             => __( 'Locker/Store List Change Address Button Display Name', 'pr-shipping-shipany' ),
			'type'            	=> 'text',
			'default'  			=> 'Change address',
			'placeholder'		=> 'Change Address Button Display Name in checkout page',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'description'       => __( 'Customize shipping method change address button display name in checkout page.'),
			'desc_tip'          => true,
			'class'			  	=> 'shipany-enable-locker-2'
		);
		$insert_locker_setting3 = array(
			'title'             => __( 'Locker/Store Address Writing to Shipping Address Only', 'pr-shipping-shipany' ),
			'type'              => 'checkbox',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'default'           => 'no',
			'description'       => __( 'By default, the Locker/Store address being written to both billing address and shipping address during checkout. Please tick here if you want the Locker/Store address being written to shipping address only.', 'pr-shipping-shipany' ),
			'desc_tip'          => true,
		);
		$insert_locker_setting4 = array(
			'title'             => __( 'Locker/Store List Minimum Checkout Amount for Free Shipping', 'pr-shipping-shipany' ),
			'type'            	=> 'text',
			'placeholder'		=> 'Minimum checkout amount for free shipping',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'description'       => __( '1) If empty, the shipping fee will be the Cost defined in Local pickup settings.<br><br>2) If value input AND the checkout amount is equal to or larger than this value, the shipping fee will be Zero.<br><br>3) If value input AND the checkout amount is less than this value, the shipping fee will be the Cost defined in Local pickup settings.'),
			'desc_tip'          => true,
			'class'			  	=> 'shipany_tracking_note'
		);
		$insert_locker_setting5 = array(
			'title'             => __( 'Locker/Store List include Macau location', 'pr-shipping-shipany' ),
			'type'              => 'checkbox',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'default'           => 'yes',
			'description'       => __( 'Please tick here if you want the Locker/Store List include Macau location.', 'pr-shipping-shipany' ),
			'desc_tip'          => true,
		);
		$insert_locker_setting6 = array(
			'title'             => __( 'Locker/Store Address Length Limit', 'pr-shipping-shipany' ),
			'type'              => 'text',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'default'           => 0,
			'description'       => __( 'Specify the length limit (in number of characters) of Locker/Store addresses to be prefilled to checkout form. This may be necessary if there are other plugins consuming the checkout form but requiring a specific address length limit.', 'pr-shipping-shipany' ),
			'desc_tip'          => true,
		);
		$get_token = array(
			'title'             => __( 'Active Notification', 'pr-shipping-shipany' ),
			'type'            	=> 'button',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'description'       => __( 'Grant permission for ShipAny to notify WooCommerce the latest order status and tracking number.'),
			'desc_tip'          => true
		);
		$update_address = array(
			'title'             => 'Refresh Sender Address',
			'type'              => 'button',
			'description'       => __( 'Press the button to refresh the sender address. You might need to do this after updating Pickup Address Settings in ShipAny Portal.', 'shipany-for-woocommerce' ),
			'desc_tip'          => true,
			'class'				=> 'button-secondary update-address',
		);
		$default_weight = array(
			'title'             => 'Always overwrite shipment order weight to 1kg',
			'type'              => 'checkbox',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'default'           => 'no',
			'description'       => __( 'By default, the shipment order weight is calculated based on the product weight. Please tick here if you want to always overwrite shipment order weight to 1kg. The shipment will be charged based on logistics courier final measurement.', 'shipany-for-woocommerce' ),
			'desc_tip'          => true
		);
		$default_courier_additional_service = array(
			'title'             => __( 'Default Courier Additional Service', 'pr-shipping-shipany' ),
			'type'              => 'select',
			'description'       => __( 'Please select default courier additional service.', 'pr-shipping-shipany' ),
			'desc_tip'          => true,
			'options'           => $lalamove_addons_name_key_pair,
			'class'				=> 'wc-enhanced-select'
		);
		$paid_by_rec = array(
			'title'             => 'Paid by Receiver',
			'type'              => 'checkbox',
			'label'             => __( ' ', 'pr-shipping-shipany' ),
			'default'           => 'no',
			'description'       => __( 'Please tick here if you want the shipping fee to be paid by the receiver.', 'shipany-for-woocommerce' ),
			'desc_tip'          => true
		);

		$this->form_fields = array_slice($this->form_fields, 0, 4, true) + array(
			"shipany_default_courier_additional_service" => $default_courier_additional_service,
			"shipany_paid_by_rec" => $paid_by_rec,
			"shipany_rest_token" => $get_token,
			"shipany_enable_locker_list" => $insert_locker_setting1,
			"shipany_enable_locker_list2" => $insert_locker_setting2,
			"shipany_enable_locker_list2_1" => $insert_locker_setting2_1,
			"shipany_bypass_billing_address" => $insert_locker_setting3,
			"shipany_locker_free_cost" => $insert_locker_setting4,
			"shipany_locker_include_macuo" => $insert_locker_setting5,
			"shipany_locker_length_truncate" => $insert_locker_setting6,
			'shipany_update_address' => $update_address,
			'default_weight' => $default_weight
		) + array_slice($this->form_fields, 4, count($this->form_fields) - 1, true) ;

	
		if (isset($this->settings["shipany_api_key"]) && !in_array(md5($this->settings["shipany_api_key"]), array('8241d0678fb9abe65a77fe6d69f7063c', '7df5eeebe4116acfefa81a7a7c3f12ed'))) {
			$update_temp = get_option('woocommerce_shipany_ecs_asia_settings');
			$update_temp['default_weight'] = 'no';
			update_option('woocommerce_shipany_ecs_asia_settings', $update_temp);
		}

		// if not lalamove, remove the db value shipany_default_courier_additional_service
		if (isset($this->settings["shipany_default_courier"]) && !in_array($this->settings["shipany_default_courier"],$GLOBALS['COURIER_LALAMOVE'])) {
			$update_temp = get_option('woocommerce_shipany_ecs_asia_settings');
			$update_temp['shipany_default_courier_additional_service'] = '';
			update_option('woocommerce_shipany_ecs_asia_settings', $update_temp);
		}
		// get rid of the shipany_tracking_note_txt field if merchant asn_mode is enabled
		// if (json_decode($this->settings["merchant_info"])->data->objects[0]->asn_mode != "Disable" || get_option('shipany_has_asn')) {
		// 	$this->form_fields = array_slice($this->form_fields, 0, 9, true) + array_slice($this->form_fields, 10, true);
		// }
	}

	/**
	 * Generate Button HTML.
	 *
	 * @access public
	 * @param mixed $key
	 * @param mixed $data
	 * @since 1.0.0
	 * @return string
	 */
	public function generate_button_html( $key, $data ) {
		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'class'             => 'button-secondary',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'title'             => '',
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<button class="<?php echo esc_attr( $data['class'] ); ?>" type="button" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo wp_kses_post( $data['title'] ); ?></button>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
	 */
	public function process_admin_options() {
		header("Refresh:0");
		try {

			$shipany_obj = SHIPANY()->get_shipany_factory();
			$shipany_obj->shipany_reset_connection();
            $dflt_cour_uid = get_transient('temp_dflt_cour');
            if(!empty($dflt_cour_uid)){
                $this->update_option('shipany_default_courier', $dflt_cour_uid);
				$this->form_fields["shipany_default_courier"]["options"]=get_transient('temp_dflt_cour_name');
				
				if (!in_array($dflt_cour_uid,array('c6e80140-a11f-4662-8b74-7dbc50275ce2','f403ee94-e84b-4574-b340-e734663cdb39','7b3b5503-6938-4657-acab-2ff31c3a3f45','2ba434b5-fa1d-4541-bc43-3805f8f3a26d','1d22bb21-da34-4a3c-97ed-60e5e575a4e5','1bbf947d-8f9d-47d8-a706-a7ce4a9ddf52','c74daf26-182a-4889-924b-93a5aaf06e19'))){
					$this->form_fields["set_default_storage_type"]["options"]='Normal';
				} else {
					$this->form_fields["set_default_storage_type"]["options"]=get_transient('temp_storage_name');
				}
            }

		} catch (Exception $e) {

			echo $this->get_message( __('Could not reset connection: ', 'pr-shipping-shipany') . $e->getMessage() );
			// throw $e;
		}
		$api_tk = get_transient('temp_key');
		if ($api_tk ==''){
			$api_tk = $_POST["woocommerce_shipany_ecs_asia_shipany_api_key"];
		}
		$temp_api_endpoint = 'https://api.shipany.io/';
		//dev3
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
		if ($this->shipping_shipnay_settings['shipany_region'] == '1' || $this->settings["shipany_region"] == '1' || $_POST["woocommerce_shipany_ecs_asia_shipany_region"] == '1') {
			$temp_api_endpoint = str_replace("api", "api-sg" , $temp_api_endpoint);
		}
		$para = [
			"store" => [
				  "domain" => get_permalink( wc_get_page_id( 'shop' )),
				  "src_platform" => "woocommerce", 
				  "meta" => [
					 "store_id" => get_bloginfo( 'name' ),
					 "shop_display_name" => home_url()
				  ] 
			   ] 
		 ]; 
	
		$merchant_resp = wp_remote_get($temp_api_endpoint.'merchants/self/', array(
			'headers' => array(
				'api-tk'=> $api_tk
			)
		));
		if (wp_remote_retrieve_response_code($merchant_resp) == 200) {
			// $merchant_info = json_decode($merchant_resp['body'])->data->objects[0];
			$merchant_info = $merchant_resp['body'];
			$_POST["woocommerce_shipany_ecs_asia_merchant_info"] = $merchant_info;
			if (json_decode($merchant_info)->data->objects[0]->asn_mode == "Disable") {
				update_option('shipany_has_asn', false);
			} else {
				update_option('shipany_has_asn', true);
			}
		}
		// connect store
		$shipany_obj = SHIPANY()->get_shipany_factory();
		$response = $shipany_obj->api_client->post_connect('ecommerce/connect/ ', $para,$api_tk, $temp_api_endpoint);

		return parent::process_admin_options();
	}
}

endif;
