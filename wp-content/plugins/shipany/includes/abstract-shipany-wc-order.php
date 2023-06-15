<?php

use SHIPANY\Utils\CommonUtils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'SHIPANY_WC_Order' ) ) :
function get_courier_tunnel_option ( $service_plans, $current_service_plan) {
	$rv = array();
	foreach ($service_plans as $service_plan) {
		if ($service_plan->cour_svc_pl == $current_service_plan) {
			$tunnels = $service_plan->{"add-ons"}->tunnel;
			foreach ($tunnels as $tunnel) {
				$rv[$tunnel->code] = $tunnel->desc;
			}
			break;
		}
	}
	return $rv;
}

function get_courier_additional_requirements_option ( $service_plans, $current_service_plan) {
	$rv = array();
	if ($service_plans == '') {
		return $rv;
	}
	foreach ($service_plans as $service_plan) {
		if ($service_plan->cour_svc_pl == $current_service_plan || $current_service_plan == '') {
			$additional_services = $service_plan->{"add-ons"}->additional_services;
			foreach ($additional_services as $additional_service) {
				$rv[$additional_service->code] = $additional_service->desc;
			}
			break;
		}
	}
	return $rv;
}
abstract class SHIPANY_WC_Order {
	const WP_POST_TIMEOUT = 30;
	
	const SHIPANY_DOWNLOAD_ENDPOINT = 'download_label';

	protected $shipping_shipnay_settings = array();

    protected $service 	= 'ShipAny';

	protected $carrier 	= '';

	/**
	 * Init and hook in the integration.
	 */
	public function __construct( ) {
		$this->define_constants();
		$this->init_hooks();

		$this->shipping_shipnay_settings = SHIPANY()->get_shipping_shipany_settings();
	}

	protected function define_constants() {
	}

	public function init_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 20 );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_meta_box' ), 0, 2 );
		// Order page metabox actions
		add_action( 'wp_ajax_wc_shipment_shipany_gen_label', array( $this, 'save_meta_box_ajax' ) );
		add_action( 'wp_ajax_wc_shipment_shipany_gen_label_recreate', array( $this, 'save_meta_box_ajax_recreate' ) );
		add_action( 'wp_ajax_wc_shipment_shipany_delete_label', array( $this, 'delete_label_ajax' ) );
		add_action( 'wp_ajax_wc_send_pick_up_request',  array( $this, 'send_pickup_request' ));	
		// add_action( 'woocommerce_thankyou', array( $this, 'save_meta_box_ajax_auto' ) );
		add_action('woocommerce_payment_successful_result', array( $this, 'save_meta_box_ajax_auto' ), 10, 2);
		$subs_version = class_exists( 'WC_Subscriptions' ) && ! empty( WC_Subscriptions::$version ) ? WC_Subscriptions::$version : null;
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'downloads_bulk_actions_edit_product'));
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'downloads_handle_bulk_action_edit_shop_order'), 10, 3 );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'creates_handle_bulk_action_edit_shop_order'), 10, 3 );
		// add_action( 'admin_notices', array( $this, 'downloads_bulk_action_admin_notice' ));
		// Prevent data being copied to subscriptions
		if ( null !== $subs_version && version_compare( $subs_version, '2.0.0', '>=' ) ) {
			add_filter( 'wcs_renewal_order_meta_query', array( $this, 'woocommerce_subscriptions_renewal_order_meta_query' ), 10 );
		} else {
			add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', array( $this, 'woocommerce_subscriptions_renewal_order_meta_query' ), 10 );
		}

		// display admin notices for bulk actions
		add_action( 'admin_notices', array( $this, 'render_messages' ) );

		// add_action( 'init', array( $this, 'add_download_label_endpoint' ) );
		add_action( 'parse_query', array( $this, 'process_download_label' ) );
		// add {tracking_note} placeholder
		add_filter( 'woocommerce_email_format_string' , array( $this, 'add_tracking_note_email_placeholder' ), 10, 2 );
		
		add_shortcode( 'shipany_tracking_note', array( $this, 'tracking_note_shortcode') );
		add_shortcode( 'shipany_tracking_link', array( $this, 'tracking_link_shortcode') );

		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'woocommerce_process_shop_order'));

		add_action( 'wp_ajax_wc_shipment_shipany_update_service_plan', array( $this, 'update_service_plan' ) );
		// add_action( 'wp_ajax_wc_shipment_shipany_update_courier_additional_service', array( $this, 'update_courier_additional_service' ) );
		add_action( 'wp_ajax_wc_shipment_shipany_update_courier_lalamove_addons', array( $this, 'update_courier_lalamove_addons' ) );
		add_action( 'wp_ajax_wc_patch_quot_id',  array( $this, 'patch_quot_id' ));
	}

	public function patch_quot_id(){
		// this function handle the rate query 5mins expired
		$shipany_order_id = $_POST[ 'shipany_order_id' ];
		$quot_id = $_POST[ 'quot_id' ];
		// $quot_id = '9919ce0f-9089-4ee0-9252-02fffd684fff';
		$api_key_temp = $this->shipping_shipnay_settings['shipany_api_key'];
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
		$myObj = new stdClass();
		$myObj->quot_uid = $quot_id;
		$myJSON = json_encode($myObj);
		$wp_shipany_rest_response = wp_remote_request(
			$temp_api_endpoint . 'orders/'.$shipany_order_id.'/ ',
			array( 'headers' => array('api-tk'=> $api_key_temp),
					'body' => $myJSON,
					'sslverify' => false,
					'timeout' => self::WP_POST_TIMEOUT,
					'method' => 'PATCH')
		);
		if ($wp_shipany_rest_response["response"]["code"] == 200) {
			$pickup_request_result = SHIPANY()->get_shipany_factory()->send_pickup_request( $shipany_order_id);
			if ($pickup_request_result->body->data->objects[0]->cour_api_typ == 'Lalamove') {
				$lab_url = $pickup_request_result->body->data->objects[0]->lab_url;
				$response= wp_remote_get($lab_url, array( 'sslverify' => false ));
				$label_pdf_data = wp_remote_retrieve_body( $response );
				$shipment_id = $pickup_request_result->body->data->objects[0]->uid;
				$shipany_obj = new SHIPANY_API_eCS_Asia( 'dum' );
				$shipany_obj->save_shipany_label_file( 'item', $shipment_id, $label_pdf_data );
				wp_send_json( array( 
					'lab_url' => is_multisite() ? get_site_url().'/wp-content/uploads/sites/'.get_current_blog_id().'/woocommerce_shipany_label/shipany-'.$shipment_id.'.pdf' : get_site_url().'/wp-content/uploads/woocommerce_shipany_label/shipany-'.$shipment_id.'.pdf'
				) );
			}
		}
	}

	public function update_courier_lalamove_addons( ) {
		// click query rate button
		$woo_order_details = wc_get_order($_POST[ 'order_id' ]);
		$this->save_meta_box( $_POST[ 'order_id' ] );
		$label_args = $this->get_label_args( $_POST[ 'order_id' ] );
		$items =$label_args['items'];
		$items_array = array();
		$alpha_three_country_code = CommonUtils::convert_country_code($woo_order_details->get_shipping_country());
		foreach ($items as $item) {
			$item_array = array(
				"sku" => $item["sku"],
				"name" => $item["item_description"],
				"typ" => "",
				"descr" => "",
				"ori" => "",
				"unt_price" => array(
					"val" => floatval($item["item_value"]),
					"ccy" => "HKD"
				),
				"qty" => floatval($item["qty"]),
				"wt" => array(
					"val" => floatval($item["item_weight"]),
					"unt" => "kg"
				),
				"dim" => array(
					"len" => floatval($item["length"]),
					"wid" => floatval($item["width"]),
					"hgt" => floatval($item["height"]),
					"unt" => 1
				),
				"stg" => ""
			);
			$items_array[] = $item_array;
		}
		$data = array(
			"cour_svc_pl" => $_POST[ 'courier_service' ],
			"wt" => array(
				"val" => floatval($this->calculate_order_weight( $_POST["order_id"] )),
				"unt" => 'kg'
			),
			"dim" => array(
				"len" => 1,
				"wid" => 1,
				"hgt" => 1,
				"unt" => 1
			),
			"items" => $items_array,
			"mch_ttl_val" => array(
				"val" => 1,
				"ccy" => "HKD"
			),
			"sndr_ctc" => array(
				"ctc" => array(
					"tit" => "",
					"f_name" => $woo_order_details->data["shipping"]["first_name"],
					"l_name" => $woo_order_details->data["shipping"]["last_name"],
					"phs" => array(
						array(
							"typ" => "",
							"cnty_code" => "",
							"ar_code" => "",
							"num" => "",
							"ext_no" => ""
						)
					),
					"email" => "",
					"note" => ""
				),
				"addr" => array(
					"typ" => "Residential",
					"ln" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->ln,
					"ln2" => "",
					"ln3" => "",
					"distr" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->distr,
					"city" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->city,
					"cnty" => "HKG",
					"state"=> json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->state
				)
			),
			"rcvr_ctc" => array(
				"ctc" => array(
					"tit" => "",
					"f_name" => "",
					"l_name" => "",
					"phs" => array(
						array(
							"typ" => "Residential",
							"cnty_code" => "",
							"ar_code" => "",
							"num" => "",
							"ext_no" => ""
						)
					),
					"email" => "",
					"note" => ""
				),
				"addr" => array(
					"typ" => "Residential",
					"ln" => $woo_order_details->data["shipping"]["address_1"],
					"ln2" => $woo_order_details->data["shipping"]["address_2"],
					"ln3" => "",
					"distr" => $woo_order_details->data["shipping"]["city"],
					"cnty" => $alpha_three_country_code,
					'state' => $label_args["shipping_address"]["state"],
					"city" => $woo_order_details->data["shipping"]["city"],
					"zc" => $woo_order_details->data["shipping"]["postcode"]
				)
			)
		);

		$data["add-ons"]["tunnel"] = array();
		foreach ($_POST["lalamove_tunnel"] as $tunnel_element) {
			array_push($data["add-ons"]["tunnel"], array("code" => $tunnel_element));

		}
		$data["add-ons"]["additional_services"] = array();
		foreach ($_POST["lalamove_additional"] as $services_element) {
			array_push($data["add-ons"]["additional_services"], array("code" => $services_element));
		}

		// the trick here we need to consider if empty rate return but have error
		$courier_service_plans = SHIPANY()->get_shipany_factory()->api_client->query_rate($data, array('cour-uid' => $_POST[ 'courier_uid' ] ));
		$plans = array();
		$price = array();
		if(is_array($courier_service_plans)){
			foreach ($courier_service_plans as $key => $row)
			{
				$price[$key] = $row->cour_ttl_cost->val;
			}
			// array_multisort($price, SORT_ASC, $courier_service_plans);

			foreach ($courier_service_plans as $courier_service_plan) {
				$plans[json_encode($courier_service_plan)] = $courier_service_plan->cour_svc_pl . ' - '. $courier_service_plan->cour_ttl_cost->val . $courier_service_plan->cour_ttl_cost->ccy;
			};
			wp_send_json( array(
				'plans' => $plans,
			) );
		}else{
			wp_send_json( array(
				'error' => $courier_service_plans,
			) );
		}
	}

	public function update_service_plan( ) {
		// Tiggered when changed on Courier selected:
        $target_cour_uid = $_POST['cour_uid'];
        $storage_type = $_POST['selectedStorageType'];
		$woo_order_details = wc_get_order($_POST[ 'order_id' ]);
		if ($_POST['targetCourier'] == 'Lalamove' || $_POST['targetCourier'] == 'ZeekDash' || $_POST['targetCourier'] == 'SF Express' || $_POST['targetCourier'] == 'Zeek2Door' || $_POST['targetCourier'] == 'Jumppoint' || $_POST['targetCourier'] == 'Hongkong Post' || $_POST['targetCourier'] == 'SF Express (Cold Chain)') {
			$this->save_meta_box( $_POST[ 'order_id' ] );
			$label_args = $this->get_label_args( $_POST[ 'order_id' ] );
			$stored_merchant_json = json_decode($this->shipping_shipnay_settings["merchant_info"]);
			$items =$label_args['items'];
			$items_array = array();
			$alpha_three_country_code = CommonUtils::convert_country_code($woo_order_details->get_shipping_country());
			foreach ($items as $item) {
				$item_array = array(
					"sku" => $item["sku"],
					"name" => $item["item_description"],
					"typ" => "",
					"descr" => "",
					"ori" => "",
					"unt_price" => array(
						"val" => floatval($item["item_value"]),
						"ccy" => "HKD"
					),
					"qty" => floatval($item["qty"]),
					"wt" => array(
						"val" => floatval($item["item_weight"]),
						"unt" => "kg"
					),
					"dim" => array(
						"len" => floatval($item["length"]),
						"wid" => floatval($item["width"]),
						"hgt" => floatval($item["height"]),
						"unt" => 1
					),
					"stg" => $storage_type
				);
				$items_array[] = $item_array;
			}
			$data = array(
				"cour_svc_pl" => $_POST['selectedAdditionalService'],
                "cour_uid" => $target_cour_uid,
				"wt" => array(
					"val" => floatval($this->calculate_order_weight( $_POST["order_id"] )),
					"unt" => 'kg'
				),
				"dim" => array(
					"len" => 1,
					"wid" => 1,
					"hgt" => 1,
					"unt" => 1
				),
				"items" => $items_array,
				"mch_ttl_val" => array(
					"val" => 1,
					"ccy" => "HKD"
				),
				"sndr_ctc" => array(
					"ctc" => array(
						"tit" => "",
						"f_name" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->ctc->f_name,
						"l_name" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->ctc->l_name,
						"phs" => array(
							array(
								"typ" => "",
								"cnty_code" => "",
								"ar_code" => "",
								"num" => "",
								"ext_no" => ""
							)
						),
						"email" => "",
						"note" => ""
					),
					"addr" => array(
						"typ" => "Residential",
						"ln" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->ln,
						"ln2" => "",
						"ln3" => "",
						"distr" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->distr,
						"city" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->city,
						"cnty" => isset($stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->cnty) ? json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->cnty : 'HKG',
						"state"=> $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->state
					)
				),
				"rcvr_ctc" => array(
					"ctc" => array(
						"tit" => "",
						"f_name" => "",
						"l_name" => "",
						"phs" => array(
							array(
								"typ" => "Residential",
								"cnty_code" => "",
								"ar_code" => "",
								"num" => "",
								"ext_no" => ""
							)
						),
						"email" => "",
						"note" => ""
					),
					"addr" => array(
						"typ" => "Residential",
						"ln" => $woo_order_details->data["shipping"]["address_1"],
						"ln2" => $woo_order_details->data["shipping"]["address_2"],
						"ln3" => "",
						"distr" => "",
						"cnty" => $alpha_three_country_code,
						'state' => $label_args["shipping_address"]["state"],
						"city" => $woo_order_details->data["shipping"]["city"],
						"zc" => $woo_order_details->data["shipping"]["postcode"]
					)
				)
			);
			if (in_array($target_cour_uid , $GLOBALS['Courier_uid_mapping']['Hongkong Post'])) {
				$data['self_drop_off'] = true;
			}
			// the trick here we need to consider if empty rate return but have error
			$courier_service_plans = SHIPANY()->get_shipany_factory()->api_client->query_rate($data, array('cour-uid' => $target_cour_uid));
			$plans = array();
			$price = array();
			if(is_array($courier_service_plans)){
				foreach ($courier_service_plans as $key => $row)
				{
					$price[$key] = $row->cour_ttl_cost->val;
				}
				// array_multisort($price, SORT_ASC, $courier_service_plans);

				foreach ($courier_service_plans as $courier_service_plan) {
					$plans[json_encode($courier_service_plan)] = $courier_service_plan->cour_svc_pl . ' - '. $courier_service_plan->cour_ttl_cost->ccy . ' ' . $courier_service_plan->cour_ttl_cost->val;
				};
				wp_send_json( array(
					'plans' => $plans,
				) );
			}else{
				wp_send_json( array(
					'error' => $courier_service_plans,
				) );
			}
		} else {
			if ($woo_order_details->get_shipping_country() != "HK") {
				$this->save_meta_box( $_POST[ 'order_id' ] );
				$label_args = $this->get_label_args( $_POST[ 'order_id' ] );
				$items =$label_args['items'];
				$items_array = array();
				$alpha_three_country_code = CommonUtils::convert_country_code($woo_order_details->get_shipping_country());
				foreach ($items as $item) {
					$item_array = array(
						"sku" => $item["sku"],
						"name" => $item["item_description"],
						"typ" => "",
						"descr" => "",
						"ori" => "",
						"unt_price" => array(
							"val" => floatval($item["item_value"]),
							"ccy" => "HKD"
						),
						"qty" => floatval($item["qty"]),
						"wt" => array(
							"val" => floatval($item["item_weight"]),
							"unt" => "kg"
						),
						"dim" => array(
							"len" => floatval($item["length"]),
							"wid" => floatval($item["width"]),
							"hgt" => floatval($item["height"]),
							"unt" => 1
						),
						"stg" => ""
					);
					$items_array[] = $item_array;
				}
				$data = array(
					"cour_uid" => $target_cour_uid,
					"wt" => array(
						"val" => floatval($this->calculate_order_weight( $_POST["order_id"] )),
						"unt" => 'kg'
					),
					"dim" => array(
						"len" => 1,
						"wid" => 1,
						"hgt" => 1,
						"unt" => 1
					),
					"items" => $items_array,
					"mch_ttl_val" => array(
						"val" => 1,
						"ccy" => "HKD"
					),
					"sndr_ctc" => array(
						"ctc" => array(
							"tit" => "",
							"f_name" => $woo_order_details->data["shipping"]["first_name"],
							"l_name" => $woo_order_details->data["shipping"]["last_name"],
							"phs" => array(
								array(
									"typ" => "",
									"cnty_code" => "",
									"ar_code" => "",
									"num" => "",
									"ext_no" => ""
								)
							),
							"email" => "",
							"note" => ""
						),
						"addr" => array(
							"typ" => "Residential",
							"ln" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->ln,
							"ln2" => "",
							"ln3" => "",
							"distr" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->distr,
							"city" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->city,
							"cnty" => "HKG",
							"state"=> json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->state
						)
					),
					"rcvr_ctc" => array(
						"ctc" => array(
							"tit" => "",
							"f_name" => "",
							"l_name" => "",
							"phs" => array(
								array(
									"typ" => "Residential",
									"cnty_code" => "",
									"ar_code" => "",
									"num" => "",
									"ext_no" => ""
								)
							),
							"email" => "",
							"note" => ""
						),
						"addr" => array(
							"typ" => "Residential",
							"ln" => $woo_order_details->data["shipping"]["address_1"],
							"ln2" => $woo_order_details->data["shipping"]["address_2"],
							"ln3" => "",
							"distr" => "",
							"cnty" => $alpha_three_country_code,
							'state' => $label_args["shipping_address"]["state"],
							"city" => $woo_order_details->data["shipping"]["city"],
							"zc" => $woo_order_details->data["shipping"]["postcode"]
						)
					)
				);
	
				// the trick here we need to consider if empty rate return but have error
				$courier_service_plans = SHIPANY()->get_shipany_factory()->api_client->query_rate($data);
				$plans = array();
				$price = array();
				if(is_array($courier_service_plans)){
					foreach ($courier_service_plans as $key => $row)
					{
						$price[$key] = $row->cour_ttl_cost->val;
					}
					// array_multisort($price, SORT_ASC, $courier_service_plans);
	
					foreach ($courier_service_plans as $courier_service_plan) {
						$plans[json_encode($courier_service_plan)] = $courier_service_plan->cour_svc_pl . ' - '. $courier_service_plan->cour_ttl_cost->val . $courier_service_plan->cour_ttl_cost->ccy;
					};
					wp_send_json( array(
						'plans' => $plans,
					) );
				}else{
					wp_send_json( array(
						'error' => $courier_service_plans,
					) );
				}
			} else {
				wp_send_json( array(
					'error' => 'UPS do not support domestic shipment.',
				) );			
			}
		}

	}

	public function downloads_bulk_actions_edit_product( $actions ) {
		//register action to the pull down list
		if (get_option('order_list_counter') > 0) {
			$order_list_counter = get_option('order_list_counter');

			$msg =
			'Total Select: ' . $order_list_counter['total_select_count'] . '<br>'
			.'Create Success: ' . $order_list_counter['create_from_empty_count_success'] . '<br>'
			.'Create Fail: ' . $order_list_counter['create_from_empty_count_fail'] . '<br>'
			.'Create (From Draft) Success: ' . $order_list_counter['create_from_draft_count_success'] . '<br>'
			.'Create (From Draft) Fail: ' . $order_list_counter['create_from_draft_count_fail'];
			echo '<div class="updated"><p>' .$msg. '</p></div>';

			update_option('order_list_counter',0);
		}
		$actions['write_downloads'] = __( 'Download ShipAny Label', 'woocommerce' );
		$actions['create_shipany_order'] = __( 'Create ShipAny Order', 'woocommerce' );
		return $actions;
	}

	public function creates_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
		
		if ( $action !== 'create_shipany_order' )
			return $redirect_to; // Exit
		
		$total_select_count = count($post_ids);
		$create_from_draft_count_success = 0;
		$create_from_draft_count_fail = 0;
		$create_from_empty_count_success = 0;
		$create_from_empty_count_fail = 0;


		$api_key_temp = $this->shipping_shipnay_settings['shipany_api_key'];
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

		if ($this->shipping_shipnay_settings['shipany_region'] == '1') {
			$temp_api_endpoint = str_replace("api", "api-sg" , $temp_api_endpoint);
		}

		$shipany_tracking_note_enable = $this->shipping_shipnay_settings['shipany_tracking_note_enable'];

		// paid by receiver
		if ($this->shipping_shipnay_settings["shipany_paid_by_rec"] == 'yes') {
			$_POST["pr_shipany_paid_by_rec"] = 'yes';
		}
		foreach ( $post_ids as $post_id ) {
			$order = wc_get_order( $post_id );
			$order_data = $order->get_data();


			$order_info = $this->get_shipany_label_tracking($order_data["id"]);
			// exit if order not created/drafted
			if ($order_info == '') {
				$order_id = $order->get_id();

				$this->save_meta_box( $order_id );

				// Gather args for API call
				$args = $this->get_label_args( $order_id );
				if (isset($this->shipping_shipnay_settings["set_default_storage_type"])){
					$args["order_details"]['storage_type'] = $this->shipping_shipnay_settings["set_default_storage_type"];
				}	
				$args = apply_filters('shipping_shipany_label_args', $args, $order_id );

				if ($this->shipping_shipnay_settings["default_weight"] == 'yes') {
					$args['order_details']['weight'] = 1;
				} else {
					foreach ($args["items"] as $element) {
						$args['order_details']['weight'] = floatval($args['order_details']['weight']) + ($element['qty'] * floatval($element['item_weight']));
					}
					$args['order_details']['weight'] = strval($args['order_details']['weight']);
				}

				$shipany_obj = SHIPANY()->get_shipany_factory();
				$label_tracking_info = $shipany_obj->get_shipany_label( $args);
				
				if (isset($label_tracking_info["tracking_number"])) {
					$create_from_empty_count_success += 1;
					$this->save_shipany_label_tracking( $order_id, $label_tracking_info );

					if (empty($shipany_tracking_note_enable) || $shipany_tracking_note_enable == 'yes') {
						$tracking_note = $this->get_tracking_note( $order_id );
						$tracking_note_type = $this->get_tracking_note_type();
						$order = wc_get_order( $order_id );
						$order->add_order_note( $tracking_note, $tracking_note_type, true );
					}
					do_action( 'pr_shipping_shipany_label_created', $order_id );
				} else {
					$create_from_empty_count_fail += 1;
				}


			} else {
				// the order is created/drafted, call charge order api
				$myObj = new stdClass();
				$myObj->mch_uid = json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->uid;
				$myObj->uid = $order_info['shipment_id'];
				$myJSON = json_encode($myObj);
				$wp_shipany_rest_response = wp_remote_post(
					$temp_api_endpoint . 'orders/charge-order/ ',
					array( 'headers' => array('api-tk'=> $api_key_temp),
							'body' => $myJSON,
							'sslverify' => false,
							'timeout' => self::WP_POST_TIMEOUT)
				);		
				if ($wp_shipany_rest_response["response"]["code"] == 200) {
					$create_from_draft_count_success += 1;

					// download label to local
					$wp_shipany_rest_response_decode = json_decode($wp_shipany_rest_response['body']);
					$label_url = $wp_shipany_rest_response_decode->data->objects[0]->lab_url;
					$response_label = wp_remote_get($label_url, array( 'sslverify' => false ));
					$label_pdf_data = wp_remote_retrieve_body( $response_label );
					file_put_contents( SHIPANY()->get_shipany_label_folder_dir() . 'shipany-' . $myObj->uid . '.pdf', $label_pdf_data );
				} else if ($wp_shipany_rest_response["response"]["code"] == 400){
					$create_from_empty_count_fail += 1;
				} else {
					$create_from_draft_count_fail += 1;
				}
			}

		}

		update_option('order_list_counter', array( 'total_select_count' => $total_select_count, 
		'create_from_draft_count_success' => $create_from_draft_count_success, 
		'create_from_draft_count_fail' => $create_from_draft_count_fail, 
		'create_from_empty_count_success' => $create_from_empty_count_success, 
		'create_from_empty_count_fail' => $create_from_empty_count_fail ));
		return $redirect_to;
		
		
	}
    public function download_remote_file($file_url, $save_to) {
        $content = file_get_contents($file_url);
        file_put_contents($save_to, $content);
    }

	public function downloads_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
		if ( $action !== 'write_downloads' )
			return $redirect_to; // Exit

		$api_key_temp = $this->shipping_shipnay_settings['shipany_api_key'];
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

		if ($this->shipping_shipnay_settings['shipany_region'] == '1') {
			$temp_api_endpoint = str_replace("api", "api-sg" , $temp_api_endpoint);
		}

		$multi_files = array();
		$multi_files_order_id = '';
		foreach ( $post_ids as $post_id ) {
			$order = wc_get_order( $post_id );
			$order_data = $order->get_data();

			// exit if order not create/draft
			if ($this->get_shipany_label_tracking($order_data["id"]) == '') {
				continue;
			}
			$order_info = $this->get_shipany_label_tracking($order_data["id"]);
			$response = wp_remote_get($temp_api_endpoint . 'orders/' . $order_info['tracking_number'], array(
				'headers' => array(
					'api-tk'=> $api_key_temp,
				)
			));
			if (isset($response["response"]["code"])){
				if ($response["response"]["code"] == 200) {
					$response_body_decode = json_decode($response["body"]);			
					if (isset($response_body_decode->data->objects[0]->lab_url)) {
						if ($response_body_decode->data->objects[0]->lab_url !='') {
							$this->download_remote_file($response_body_decode->data->objects[0]->lab_url , 'uploads/temp.pdf');
							$pdf_local_path = $order_info['label_path'];
							if (filesize($pdf_local_path)>1000) {
								array_push($multi_files, $pdf_local_path);
								$multi_files_order_id .= '_' . $order_data["id"];
							}
						}
					}
				}
			}
		}

		if (count($multi_files)>0) {
			$pdf = new \PDFMerger\PDFMerger;
			foreach ( $multi_files as $multi_file ) {
				$pdf->addPDF($multi_file);
			}
			ob_end_clean();
			$pdf->merge('download',$multi_files_order_id.'.pdf');
		}
		return $redirect_to;
		// return $redirect_to = add_query_arg( array(
		// 	'write_downloads' => '1',
		// 	'processed_count' => count( $processed_ids ),
		// 	'processed_ids' => implode( ',', $processed_ids ),
		// ), $redirect_to );
	}

	// public function downloads_bulk_action_admin_notice() {
	// 	if ( empty( $_REQUEST['write_downloads'] ) ) return; // Exit
	
	// 	$count = intval( $_REQUEST['processed_count'] );
	
	// 	printf( '<div id="message" class="updated fade"><p>' .
	// 		_n( 'Processed %s Order for downloads.',
	// 		'Processed %s Orders for downloads.',
	// 		$count,
	// 		'write_downloads'
	// 	) . '</p></div>', $count );
	// }

	public function woocommerce_process_shop_order() {

		$label_tracking = $this->get_shipany_label_tracking($_POST["post_ID"]);
		// update order only if the order is created(drafted)
		if ($label_tracking != ''){
			
			$order_detail = $this->get_label_args($_POST["post_ID"]);
			$parm = array ('ops' =>array());

			// update product table
			$order = wc_get_order( $_POST["post_ID"] );
			$country_name = WC()->countries->countries[ $order->get_shipping_country() ];
			$items = $order->get_items();
			foreach ( $items as $item ) {
				$product = $item->get_product();
				foreach ($order_detail["items"] as $key=>$order_detail_item) {
					if ($product->get_id() == $order_detail_item["product_id"]){
						if ($_POST["order_item_qty"][$item->get_id()] != $order_detail_item["qty"]) {
							$path = array('op' => 'replace','path' => '/items/' . $key . '/qty','value' => intval($_POST["order_item_qty"][$item->get_id()]),);
							array_push($parm["ops"],$path);
						}
					}
				}
			}

			// update first name
			if ($_POST["_shipping_first_name"] != $order_detail["shipping_address"]["first_name"]) {
				array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/ctc/f_name','value' => $_POST["_shipping_first_name"],));
			}

			// update last name
			if ($_POST["_shipping_last_name"] != $order_detail["shipping_address"]["last_name"]) {
				array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/ctc/l_name','value' => $_POST["_shipping_last_name"],));
			}

			// update company name
			if ($_POST["_shipping_company"] != $order_detail["shipping_address"]["company"]) {
				array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/ctc/co_name','value' => $_POST["_shipping_company"],));
			}

			// update address line1
			if ($_POST["_shipping_address_1"] != $order_detail["shipping_address"]["address_1"]) {
				array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/addr/ln','value' => $_POST["_shipping_address_1"],));
			}

			// update address line2
			if ($_POST["_shipping_address_2"] != $order_detail["shipping_address"]["address_2"]) {
				array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/addr/ln2','value' => $_POST["_shipping_address_2"],));
			}

			// update phone
			if ($_POST["_shipping_phone"] != '' && $_POST["_shipping_phone"] != $order_detail["shipping_address"]["phone"]){
				array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/ctc/phs/0/num','value' => $_POST["_shipping_phone"],));
			} else if ($_POST["_billing_phone"] != $order_detail["shipping_address"]["phone"]) {
				array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/ctc/phs/0/num','value' => $_POST["_billing_phone"],));
			}


			if ($country_name != 'Hong Kong') {
				// update postcode
				if ($_POST["_shipping_postcode"] != $order_detail["shipping_address"]["postcode"]) {
					array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/addr/zc','value' => $_POST["_shipping_postcode"],));
				}

				// update cnty_code(phone)
				if ($_POST["_shipping_country"] != $order->get_shipping_country()) {
					array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/ctc/phs/0/cnty_code','value' => WC()->countries->get_country_calling_code($_POST["_shipping_country"]),));
					array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/addr/cnty','value' => $country_name,));
				}

				// update city
				if ($_POST["_shipping_city"] != $order_detail["shipping_address"]["city"]) {
					array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/addr/city','value' => $_POST["_shipping_city"],));
				}

				// update state
				if ($_POST["_shipping_state"] != $order_detail["shipping_address"]["state"]) {
					array_push($parm["ops"],array('op' => 'replace','path' => '/rcvr_ctc/addr/state','value' => $_POST["_shipping_state"],));
				}
			}
			// send request only when any of above changed
			if (count($parm["ops"]) > 0) {
				$response = SHIPANY()->get_shipany_factory()->api_client->update_order($label_tracking["shipment_id"],$parm);
				if ($response->status == 200) {
					$label_url = $response->body->data->objects[0]->lab_url;
					$response= wp_remote_get($label_url, array( 'sslverify' => false ));
					$label_pdf_data = wp_remote_retrieve_body( $response );
					file_put_contents( SHIPANY()->get_shipany_label_folder_dir() . 'shipany-' . $label_tracking["shipment_id"] . '.pdf', $label_pdf_data );
				}

			}
		}
	}

	/**
	 * Add the meta box for shipment info on the order page
	 *
	 * @access public
	 */
	public function add_meta_box() {
		#shipany # Right meta box title with yellow background 
		add_meta_box( 'woocommerce-shipment-shipany-label', sprintf( __( '%s', 'pr-shipping-shipany' ), "<img src='".SHIPANY_PLUGIN_DIR_URL . "/assets/img/shipany_logo_official_banner_white_bg.png' width='170' height='50'>"), 
		array( $this, 'meta_box' ), 'shop_order', 'side', 'high' );
	}

	/**
	 * Show the meta box for shipment info on the order page
	 *
	 * @access public
	 */
	public function meta_box() {
		global $woocommerce, $post;
		
		$order_id = $post->ID;	
		// Get saved label input fields or set default values
		$shipany_label_items = $this->get_shipany_label_items( $order_id );
		// Get tracking info if it exists
		// empty( $label_tracking_info means order not create
		$label_tracking_info = $this->get_shipany_label_tracking( $order_id );
		if ( $label_tracking_info != '' ) {
			$order_detail = $this->get_order_detail($label_tracking_info["shipment_id"]);
		} else {
			$order_detail = '';
		}


		// get shipping method
		if ($order_id != '') {
			$is_local_pickup = wc_get_order($order_id)->has_shipping_method('local_pickup');
		}
		
		// get the stg type
		if (isset($this->shipping_shipnay_settings["set_default_storage_type"])){
			if ($order_detail != ''){
				if ($order_detail->cour_api_typ == 'SfExpressColdChain') {
					$selected_stg = $order_detail->items[0]->stg;
				} else {
					$selected_stg = $this->shipping_shipnay_settings["set_default_storage_type"];
				}
			} else {
				$selected_stg = $this->shipping_shipnay_settings["set_default_storage_type"];
			}
		}

		$selected_weight_val = $this->calculate_order_weight( $order_id );
		$weight_units = get_option( 'woocommerce_weight_unit', 'kg' );

		// Get saved product, otherwise get the default product in settings
		if( ! empty( $order_detail->cour_uid ) ) {
			$selected_courier = $order_detail->cour_uid;
		} else {
			$selected_courier = $this->get_default_courier( $order_id );
		}

		// Get the list of domestic and international services
		try {
			$shipany_obj = SHIPANY()->get_shipany_factory();
			$shipany_courier_list = $shipany_obj->get_shipany_courier();

			$courier_uid_name_key_pair = array();
			$lalamove_addons = '';
			$lalamove_addons_name_key_pair = array();
			$zeek_dash_addons = '';
			$zeek_dash_addons_addons_name_key_pair = array();
			$sf_service_plan = '';
			$sf_service_plan_name_key_pair = array();
			$zeek2_service_plan = '';
			$zeek2_service_plan_name_key_pair = array();
			$service_plan_show_paid_by_rec = array();
            $jumppoint_service_plan = '';
            $jumppoint_service_plan_name_key_pair = array();
			foreach ($shipany_courier_list as $key => $value){
				if ($value->name == 'Lalamove') {
					$courier_uid_name_key_pair[$value->uid] = $value->name;
					$lalamove_addons = $value->cour_svc_plans;
				} else if ($value->name == 'ZeekDash') {
					$courier_uid_name_key_pair[$value->uid] = $value->name;
					$zeek_dash_addons = $value->cour_svc_plans;
				} else if ($value->name == 'Zeek2Door') {
					$courier_uid_name_key_pair[$value->uid] = $value->name;
					$zeek2_service_plan = $value->cour_svc_plans;
				} else if ($value->name == 'SF Express') {
					$courier_uid_name_key_pair[$value->uid] = $value->name;
					$sf_service_plan = $value->cour_svc_plans;
				} else if ($value->name == 'Jumppoint') {
                    $courier_uid_name_key_pair[$value->uid] = $value->name;
                    $jumppoint_service_plan = $value->cour_svc_plans;
                } else if ($value->name == 'Hongkong Post') {
					$courier_uid_name_key_pair[$value->uid] = $value->name;
					$hkpost_service_plan = $value->cour_svc_plans;
				} else {
					$courier_uid_name_key_pair[$value->uid] = $value->name;
				}
				// if ($value->cour_props->delivery_services->paid_by_rcvr) {
				// 	array_push($service_plan_show_paid_by_rec,$value->uid);
				// }
			}
			$shipany_courier_list = $courier_uid_name_key_pair;
			if ($lalamove_addons !=''){
				foreach ($lalamove_addons as $key => $value) {
					$lalamove_addons_name_key_pair[$value->cour_svc_pl] = $value->cour_svc_pl;
				}
			}
			if ($zeek_dash_addons !=''){
				foreach ($zeek_dash_addons as $key => $value) {
					$zeek_dash_addons_addons_name_key_pair[$value->cour_svc_pl] = $value->cour_svc_pl;
				}
			}
			if ($sf_service_plan !=''){
				foreach ($sf_service_plan as $key => $value) {
					$sf_service_plan_name_key_pair[$value->cour_svc_pl] = $value->cour_svc_pl;
					if ($value->paid_by_rcvr) {
						array_push($service_plan_show_paid_by_rec,$value->cour_svc_pl);
					}
				}
			}
			if ($zeek2_service_plan !=''){
				foreach ($zeek2_service_plan as $key => $value) {
					$zeek2_service_plan_name_key_pair[$value->cour_svc_pl] = $value->cour_svc_pl;
				}
			}

            if ($jumppoint_service_plan !=''){
                foreach ($jumppoint_service_plan as $key => $value) {
                    $jumppoint_service_plan_name_key_pair[$value->cour_svc_pl] = $value->cour_svc_pl;
                    if ($value->paid_by_rcvr) {
                        array_push($service_plan_show_paid_by_rec,$value->cour_svc_pl);
                    }
                }
            }
			if ($hkpost_service_plan !=''){
				foreach ($hkpost_service_plan as $key => $value) {
					$hkpost_service_plan_name_key_pair[$value->cour_svc_pl] = $value->cour_svc_pl;
				}
			}
		} catch (Exception $e) {

			echo '<p class="wc_shipany_error">' . esc_html($e->getMessage()) . '</p>';
		}

		$delete_label = '';
		if ($this->can_delete_label($order_id)) {
			$delete_label = '<span class="wc_shipany_delete"><a href="#" id="shipany_delete_label">' . __('Delete Label', 'pr-shipping-shipany') . '</a></span>';
		}

		//shipany
		//$main_button = '<button id="shipany-label-button" class="button button-primary button-save-form">' . __( 'Generate Label', 'pr-shipping-shipany' ) . '</button>';
		$main_button = '<button id="shipany-label-button" class="button button-primary button-save-form">' . __( 'Create ShipAny Order', 'pr-shipping-shipany' ) . '</button>';
		$inv_print_button_pre = '<a href="" id="shipany-invoice-print" class="button button-primary" target="_blank">' .__( 'Download Commercial Invoice', '' ) . '</a>';

		// Get tracking info if it exists
		// empty( $label_tracking_info means order not create
		// $label_tracking_info = $this->get_shipany_label_tracking( $order_id );
		// if ( $label_tracking_info != '' ) {
		// 	$order_detail = $this->get_order_detail($label_tracking_info["shipment_id"]);
		// } else {
		// 	$order_detail = '';
		// }

		// case1: order not create
		// case2: order success create
		if ( $order_detail == '' || ($order_detail->pay_stat != "Insufficient balance" && $order_detail->pay_stat != "Insufficient Credit") && $order_detail->ext_order_not_created != "x") {
			if ($order_detail !='') {
				echo '<div id="order_status">Order Status: '. $order_detail->cur_stat . '</div>';
				echo '<p>';
				echo '<div id="courier_service_plan" class="tooltip" data-title="The quoted price is just an estimated value.">Courier Service Plan: '. $order_detail->cour_svc_pl . ' - ' . $order_detail->cour_ttl_cost->ccy .' '. $order_detail->cour_ttl_cost->val . '</div>';
			}
			
		
			// Check whether the label has already been created or not -> empty( $label_tracking_info ) means not yet create
			if( empty( $label_tracking_info ) ) {
				$is_disabled = '';
				
				$print_button = '<a href="#" id="shipany-label-print" class="button button-primary" download target="_blank">' . __( 'Download Label', 'pr-shipping-shipany' ) . '</a>';

			} else {
				$is_disabled = 'disabled';
				if ($order_detail->lab_url == '') {
					// Check state handle Download button
					// asn disable
					$print_button = '<a href="'. $this->get_download_label_url( $order_id ) .'" id="shipany-label-print" class="button button-primary" download target="_blank" disabled="disabled" style="pointer-events: none">' .__( 'Download Label', 'pr-shipping-shipany' ) . '</a>';
				} else {
					// if (is_multisite()) {
					// 	$print_button = '<a href="'. get_site_url().'/wp-content/uploads/sites/'.get_current_blog_id().'/woocommerce_shipany_label/shipany-'.$label_tracking_info['shipment_id'].'.pdf' .'" id="shipany-label-print" class="button button-primary" download target="_blank">' .__( 'Download Label', 'pr-shipping-shipany' ) . '</a>';
					// } else {
					// 	$print_button = '<a href="'. get_site_url().'/wp-content/uploads/woocommerce_shipany_label/shipany-'.$label_tracking_info['shipment_id'].'.pdf' .'" id="shipany-label-print" class="button button-primary" download target="_blank">' .__( 'Download Label', 'pr-shipping-shipany' ) . '</a>';
					// }
					$print_button = '<a href="'. $order_detail->lab_url .'" id="shipany-label-print" class="button button-primary" download target="_blank">' .__( 'Download Label', 'pr-shipping-shipany' ) . '</a>';
                    $has_commercial_invoice = @$label_tracking_info['commercial_invoice_url'] !== null && @$label_tracking_info->commercial_invoice_url !== '';
                    if($has_commercial_invoice){
                        $inv_print_button = '<a style="margin-top: 14px;" href="'. $label_tracking_info['commercial_invoice_url'].'" id="shipany-invoice-print" class="button button-primary" target="_blank">' .__( 'Download Commercial Invoice', '' ) . '</a>';
                    }
				}
			}

			if( empty( $label_tracking_info ) ) {
				$is_disabled = '';
				
				$pickup_request = '<button id="shipany-pickup-button" class="button button-primary button-save-form">' . __( 'Send Pickup Request', 'pr-shipping-shipany' ) . '</button>';

			} else {
				$is_disabled = 'disabled';

				// $pickup_request = '<a href="'. $this->send_pickup_request( $label_tracking_info ) .'" id="send_pickup_request" class="button button-primary">' .__( 'Send Pickup Request', 'pr-shipping-shipany' ) . '</a>';
				$pickup_request = '<button id="shipany-pickup-button" class="button button-primary button-save-form">' . __( 'Send Pickup Request', 'pr-shipping-shipany' ) . '</button>';
			}

			// when load page, get the order detail, if 
			if (!empty($label_tracking_info)) {
				// Check state handle Pickup Request button
				// asn disable
				if ($order_detail->cur_stat == 'Pickup Request Sent' || $order_detail->cur_stat == 'Pickup Request Received' || $order_detail->cur_stat == 'Order Cancelled' || $order_detail->asn_id !='') {
					$pickup_request = '<button id="shipany-pickup-button" disabled="disabled" class="button button-primary button-save-form">' . __( 'Send Pickup Request', 'pr-shipping-shipany' ) . '</button>';
					$sndr_pf_dt_beg = $order_detail->sndr_pf_dt_beg;
					$sndr_pf_dt_end = $order_detail->sndr_pf_dt_end;
					if (!empty($sndr_pf_dt_beg) && !empty($sndr_pf_dt_end)) {
						$dt_beg = new DateTime($sndr_pf_dt_beg, new DateTimeZone('UTC'));
						$dt_beg->setTimezone(new DateTimeZone('HONGKONG'));
						$dt_beg_str = $dt_beg->format('Y-m-d H:i:s');
		
						$dt_end = new DateTime($sndr_pf_dt_end, new DateTimeZone('UTC'));
						$dt_end->setTimezone(new DateTimeZone('HONGKONG'));
						$dt_end_str = $dt_end->format('Y-m-d H:i:s');
					}
				}
			}


			$shipany_label_data = array(
				'main_button' => $main_button,
				'delete_label' => $delete_label,
				'print_button' => $print_button,
				'pickup_request' => $pickup_request,
				'inv_print_button' => $inv_print_button_pre
			);


			echo '<div id="shipment-shipany-label-form">';

			if( !empty( $shipany_courier_list ) ) {
			

				woocommerce_wp_hidden_input( array(
					'id'    => 'pr_shipany_label_nonce',
					'value' => wp_create_nonce( 'create-shipany-label' )
				) );

				if ($is_local_pickup && $order_detail == '') {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_product',
						'label'       		=> __( 'Courier selected:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '<p style="color:#a00;font-size: 10px;">Cannot change courier since Locker/Store List is selected by customer</p>',
						'value'       		=> $selected_courier,
						'options'			=> $shipany_courier_list,
						// 'custom_attributes'	=> array( $is_disabled => $is_disabled )
						'custom_attributes'	=> array( 'disabled' => 'disabled' )
					) );
				} else {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_product',
						'label'       		=> __( 'Courier selected:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'value'       		=> $selected_courier,
						'options'			=> $shipany_courier_list,
						'custom_attributes'	=> array( $is_disabled => $is_disabled )
						// 'custom_attributes'	=> array( 'disabled' => 'disabled' )
					) );
				}
				if ($order_detail == '') {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_courier_additional_service',
						'label'       		=> __( 'Courier Additional Service:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'value'             => $this->shipping_shipnay_settings["shipany_default_courier_additional_service"],
						'options'  => $lalamove_addons_name_key_pair,
						'custom_attributes'	=> array( $is_disabled => $is_disabled )
					) );
				} else {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_courier_additional_service',
						'label'       		=> __( 'Courier Additional Service:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						// 'value'             => explode(' ', $order_detail->cour_svc_pl)[1],
						'value'				=> $order_detail->cour_svc_pl,
						'options'  => $lalamove_addons_name_key_pair,
						'custom_attributes'	=> array( $is_disabled => $is_disabled )
					) );
				}
				if ($order_detail == '') {
					woocommerce_wp_multi_checkbox( array(
						'id'      => 'lalamove_tunnel_',
						'class'   => 'shipany_lalamove_checkbox',
						'label'   => __('Tunnel Options:', 'woocommerce'),
						'options' => get_courier_tunnel_option($lalamove_addons, $this->shipping_shipnay_settings["shipany_default_courier_additional_service"])
					) );
				} else {
					woocommerce_wp_multi_checkbox( array(
						'id'      => 'lalamove_tunnel_',
						'class'   => 'shipany_lalamove_checkbox',
						'label'   => __('Tunnel Options:', 'woocommerce'),
						'options' => get_courier_tunnel_option($lalamove_addons, $order_detail->cour_svc_pl)
					) );
				}
				$addons = '';
				if (in_array($selected_courier, $GLOBALS['Courier_uid_mapping']['Lalamove'])) {
					$addons = $lalamove_addons;
				} else if (in_array($selected_courier, $GLOBALS['Courier_uid_mapping']['ZeekDash'])) {
					$addons = $zeek_dash_addons;
				}
				 
				if ($order_detail == '') {
					woocommerce_wp_multi_checkbox( array(
						'id'      => 'lalamove_additional_requirements_',
						'class'   => 'shipany_lalamove_checkbox',
						'label'   => __('Additional Requirements:', 'woocommerce'),
						'options' => get_courier_additional_requirements_option($addons, $this->shipping_shipnay_settings["shipany_default_courier_additional_service"])
					) );
				} else {
					woocommerce_wp_multi_checkbox( array(
						'id'      => 'lalamove_additional_requirements_',
						'class'   => 'shipany_lalamove_checkbox',
						'label'   => __('Additional Requirements:', 'woocommerce'),
						'options' => get_courier_additional_requirements_option($addons, $order_detail->cour_svc_pl)
					) );
				}

                $courier_need_select_service_plans = array('UPS', 'Lalamove', 'ZeekDash', 'Zeek2Door', 'SF Express', 'Quantium', 'Zeek', 'Jumppoint', 'Hongkong Post', 'SF Express (Cold Chain)');
                $is_selected_courier_need_render_service_plan_option = false;
                foreach ($shipany_courier_list as $courier_uuid => $courier_name){
                    if(in_array($courier_name, $courier_need_select_service_plans) && $selected_courier === $courier_uuid){
                        $is_selected_courier_need_render_service_plan_option = true;
                        break;
                    }
                }
				$woo_order_details = wc_get_order($order_id);
				// select courier service plan
				if ($order_detail != '' && $order_detail->cour_api_typ == 'Lalamove') {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_couier_service_plan',
						'label'       		=> __( 'Shipping Service:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'options'           => array($label_tracking_info["courier_service_plan"]),
						'custom_attributes'	=> array( $is_disabled => $is_disabled ),
						'style'				=> 'font-size:11px'
					) );
				} else if ($is_selected_courier_need_render_service_plan_option) {
					// ups, lalamove
					$this->save_meta_box( $order_id );
					$label_args = $this->get_label_args( $order_id );
					$stored_merchant_json = json_decode($this->shipping_shipnay_settings["merchant_info"]);
					$items =$label_args['items'];
                    $items_array = array();
                    // if the ShipAny order already created, then we should use shipany order items to do the rate query
                    // if the ShipAny order not yet created, then we should use Sale order items to do the rate query
                    if (isset($order_detail) && !empty($order_detail->items)) {
                        $items_array = $order_detail->items;
                    } else {
                        foreach ($items as $item) {
                            $item_array = array(
                                "sku" => $item["sku"],
                                "name" => $item["item_description"],
                                "typ" => "",
                                "descr" => "",
                                "ori" => "",
                                "unt_price" => array(
                                    "val" => floatval($item["item_value"]),
                                    "ccy" => "HKD"
                                ),
                                "qty" => floatval($item["qty"]),
                                "wt" => array(
                                    "val" => floatval($item["item_weight"]),
                                    "unt" => "kg"
                                ),
                                "dim" => array(
                                    "len" => floatval($item["length"]),
                                    "wid" => floatval($item["width"]),
                                    "hgt" => floatval($item["height"]),
                                    "unt" => 1
                                ),
                                "stg" => isset($selected_stg) ? $selected_stg : 'Normal'
                            );
                            $items_array[] = $item_array;
                        }
                    }
                        $data = array(
                            "cour_svc_pl" => $this->shipping_shipnay_settings["shipany_default_courier_additional_service"],
                            "wt" => array(
                                "val" => $selected_weight_val,
                                "unt" => 'kg'
                            ),
                            "dim" => array(
                                "len" => 1,
                                "wid" => 1,
                                "hgt" => 1,
                                "unt" => 1
                            ),
                            "items" => $items_array,
                            "mch_ttl_val" => array(
                                "val" => 1,
                                "ccy" => "HKD"
                            ),
                            "sndr_ctc" => array(
                                "ctc" => array(
                                    "tit" => "",
                                    "f_name" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->ctc->f_name,
                                    "l_name" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->ctc->l_name,
                                    "phs" => array(
                                        array(
                                            "typ" => "",
                                            "cnty_code" => "",
                                            "ar_code" => "",
                                            "num" => "",
                                            "ext_no" => ""
                                        )
                                    ),
                                    "email" => "",
                                    "note" => ""
                                ),
                                "addr" => array(
                                    "typ" => "Residential",
                                    "ln" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->ln,
                                    "ln2" => "",
                                    "ln3" => "",
                                    "distr" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->distr,
                                    "city" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->city,
                                    "cnty" => isset($stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->cnty) ? json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->cnty : 'HKG',
                                    "state" => $stored_merchant_json->data->objects[0]->co_info->org_ctcs[0]->addr->state
                                )
                            ),
                            "rcvr_ctc" => array(
                                "ctc" => array(
                                    "tit" => "",
                                    "f_name" => "",
                                    "l_name" => "",
                                    "phs" => array(
                                        array(
                                            "typ" => "Residential",
                                            "cnty_code" => "",
                                            "ar_code" => "",
                                            "num" => "",
                                            "ext_no" => ""
                                        )
                                    ),
                                    "email" => "",
                                    "note" => ""
                                ),
                                "addr" => array(
                                    "typ" => "Residential",
                                    "ln" => $woo_order_details->data["shipping"]["address_1"],
                                    "ln2" => $woo_order_details->data["shipping"]["address_2"],
                                    "ln3" => "",
                                    "distr" => $woo_order_details->data["shipping"]["city"],
                                    "cnty" => CommonUtils::convert_country_code($woo_order_details->get_shipping_country()),
                                    'state' => $label_args["shipping_address"]["state"],
                                    "city" => $woo_order_details->data["shipping"]["city"],
                                    "zc" => $woo_order_details->data["shipping"]["postcode"]
                                )
                            )
                        );

                    // the trick here we need to consider if empty rate return but have error
					if (isset($selected_courier)) {
						if (in_array($selected_courier, $GLOBALS['Courier_uid_mapping']['Hongkong Post'])) {
							$data['self_drop_off'] = true;
						}
						$courier_service_plans = SHIPANY()->get_shipany_factory()->api_client->query_rate($data, array('cour-uid' => $selected_courier));
					} else {
						$courier_service_plans = SHIPANY()->get_shipany_factory()->api_client->query_rate($data);
					}
                    $plans = array();
                    $price = array();
                    if(is_array($courier_service_plans)){
                        foreach ($courier_service_plans as $key => $row)
                        {
                            $price[$key] = $row->cour_ttl_cost->val;
                        }
                        // array_multisort($price, SORT_ASC, $courier_service_plans);

                        foreach ($courier_service_plans as $courier_service_plan) {
                            $plans[json_encode($courier_service_plan)] = $courier_service_plan->cour_svc_pl . ' - '. $courier_service_plan->cour_ttl_cost->ccy . ' ' . $courier_service_plan->cour_ttl_cost->val;
                        };
                    }else{
                        $plans = array();
                    }

					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_couier_service_plan',
						'label'       		=> __( 'Shipping Service:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'options'  			=> $plans,
						'custom_attributes'	=> array( $is_disabled => $is_disabled ),
						'style'				=> 'font-size:0.85vw; width:100%'
					) );
				} else {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_couier_service_plan',
						'label'       		=> __( 'Shipping Service:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'options'  => array(),
						'custom_attributes'	=> array( $is_disabled => $is_disabled ),
						'style'				=> 'font-size:0.85vw; width:100%'
					) );
					?>
					<script type="text/javascript">
						document.getElementsByClassName('pr_shipany_couier_service_plan_field')[0].style.display = 'none';
						document.getElementsByClassName('pr_shipany_courier_additional_service_field')[0].style.display = 'none';
						jQuery('.lalamove_tunnel__field').hide()
						jQuery('.lalamove_additional_requirements__field').hide()
					</script>
					<?php
				}

				if (isset($courier_service_plans) && is_array($courier_service_plans) && count($courier_service_plans) > 0 && in_array($courier_service_plans[0]->cour_svc_pl,$service_plan_show_paid_by_rec)) {
					woocommerce_wp_checkbox( array(
						'id'          		=> 'pr_shipany_paid_by_rec',
						'label'       		=> __( 'Paid By Receiver: ', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'value'       		=> isset($this->shipping_shipnay_settings['shipany_paid_by_rec'])?$this->shipping_shipnay_settings['shipany_paid_by_rec']:'No'
					) );
				} else {
					woocommerce_wp_checkbox( array(
						'id'          		=> 'pr_shipany_paid_by_rec',
						'label'       		=> __( 'Paid By Receiver: ', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'value'       		=> isset($this->shipping_shipnay_settings['shipany_paid_by_rec'])?$this->shipping_shipnay_settings['shipany_paid_by_rec']:'No'
					) );
					?>
					<script type="text/javascript">
						jQuery('#pr_shipany_paid_by_rec').prop('checked', false)
						jQuery('.pr_shipany_paid_by_rec_field').hide()
						// document.getElementsByClassName('pr_shipany_paid_by_rec_field')[0].style.display = 'none';
					</script>
					<?php
				}

				if (in_array($selected_courier,array('c6e80140-a11f-4662-8b74-7dbc50275ce2','f403ee94-e84b-4574-b340-e734663cdb39','7b3b5503-6938-4657-acab-2ff31c3a3f45','2ba434b5-fa1d-4541-bc43-3805f8f3a26d','1d22bb21-da34-4a3c-97ed-60e5e575a4e5','1bbf947d-8f9d-47d8-a706-a7ce4a9ddf52','c74daf26-182a-4889-924b-93a5aaf06e19'))) {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_storage_type',
						'label'       		=> __( 'Temperature type:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'value'             => $selected_stg,
						'options'  => array(
							'Air Conditioned'        => __( 'Air Conditioned (17°C - 22°C)', 'woocommerce' ),
							'Chilled' => __( 'Chilled (0°C - 4°C)', 'woocommerce' ),
							'Frozen'   => __( 'Frozen (-18°C - -15°C)', 'woocommerce' ),
						),
						'custom_attributes'	=> array( $is_disabled => $is_disabled )
					) );
				}
                else if (in_array($selected_courier, $GLOBALS['Courier_uid_mapping']['Jumppoint'])) {
                    woocommerce_wp_select ( array(
                        'id'          		=> 'pr_shipany_storage_type',
                        'label'       		=> __( 'Temperature type:', 'pr-shipping-shipany' ), #shipany #rename button
                        'description'		=> '',
                        'value'             => $selected_stg,
                        'options'  => array(
                            'Normal'        => __( 'Normal', 'woocommerce' ),
                            'Chilled' => __( 'Chilled (0°C - 4°C)', 'woocommerce' ),
                            'Frozen'   => __( 'Frozen (-18°C - -15°C)', 'woocommerce' ),
                        ),
                        'custom_attributes'	=> array( $is_disabled => $is_disabled )
                    ) );
                }

                else {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_storage_type',
						'label'       		=> __( 'Temperature type:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'options'  => array(
							'Normal'        => __( 'Normal', 'woocommerce' )
						),
						'custom_attributes'	=> array( $is_disabled => $is_disabled )
					) );
				}

				// Get weight UoM and add in label
				if ($order_detail !='') { 
					woocommerce_wp_text_input( array(
						'id'          		=> 'shipany_weight',
						'label'       		=> sprintf( __( 'Estimated shipment weight (%s) based on items ordered: ', 'pr-shipping-shipany' ), $weight_units),
						'placeholder' 		=> 'Please enter package weight',
						'description'		=> '',
						'value'       		=> weight_convert($order_detail->wt->val, $weight_units, true),
						'custom_attributes'	=> array( $is_disabled => $is_disabled ),
						'class'				=> 'wc_input_decimal' // adds JS to validate input is in price format
					) );
				} else {
					woocommerce_wp_text_input( array(
						'id'          		=> 'shipany_weight',
						'label'       		=> sprintf( __( 'Estimated shipment weight (%s) based on items ordered: ', 'pr-shipping-shipany' ), $weight_units),
						'placeholder' 		=> 'Please enter package weight',
						'description'		=> '',
						'value'       		=> $selected_weight_val,
						'custom_attributes'	=> array( $is_disabled => $is_disabled ),
						'class'				=> 'wc_input_decimal' // adds JS to validate input is in price format
					) );
				}

				$this->additional_meta_box_fields( $order_id, $is_disabled, $shipany_label_items, $shipany_obj );


				// A label has been generated already, allow to delete
				if( empty( $label_tracking_info ) ) {
					echo wp_kses_post($main_button);
				} else {
					echo $print_button;
					echo "<p>";
					echo wp_kses_post($pickup_request);
                    echo "<br/>";
                    echo isset($inv_print_button)?$inv_print_button:'';
					if (!empty($dt_beg_str) && !empty($dt_end_str)) echo "<div style='color: #52c41a'>The courier will pick up the package between " . $dt_beg_str . " to " . $dt_end_str . "</div>";
				}
				
				wp_enqueue_script( 'wc-shipment-shipany-label-js', SHIPANY_PLUGIN_DIR_URL . '/assets/js/shipany.js', array('jquery'), SHIPANY_VERSION );
				wp_localize_script( 'wc-shipment-shipany-label-js', 'shipany_label_data', $shipany_label_data + array('lalamove_addons_name_key_pair' => $lalamove_addons_name_key_pair) 
				                                                                                              + array( 'lalamove_addons'=> $lalamove_addons) 
																											  + array( 'zeekDash_addons'=> $zeek_dash_addons) 
																											  + array( 'SF_servicePlans'=> $sf_service_plan) 
																											  + array( 'zeek2_servicePlans'=> $zeek2_service_plan) 
																											  + array('shipany_order_detail' => $order_detail) 
																											  + array('service_plan_show_paid_by_rec' => $service_plan_show_paid_by_rec) 
																											  + array('courier_service_plans_error' => $courier_service_plans)
																											);
				
			} else {
				echo wp_kses_post('<p class="wc_shipany_error">' . __('There are no services available for the destination country!', 'pr-shipping-shipany') . '</p>');
			}
			
			echo '</div>';
		# handle out of credit condition.
		// case3: order draft(out of credit)
		// case4: order draft(error)
		} else if ($order_detail != '' && ($order_detail->pay_stat == "Insufficient balance" || $order_detail->pay_stat == "Insufficient Credit") || $order_detail->ext_order_not_created == "x") {
			// Check whether the label has already been created or not
			if ($order_detail->ext_order_not_created == "x") {
				$main_button = '<button id="shipany-label-button-recreate" class="button button-primary button-save-form tooltip" data-title="Since error occur during create ShipAny order, the order falls to draft state">' . __( 'Create ShipAny Order (From Draft)', 'pr-shipping-shipany' ) . '</button>';
			} else {
				$main_button = '<button id="shipany-label-button-recreate" class="button button-primary button-save-form tooltip" data-title="Since ShipAny account is out of credit, the order falls to draft state">' . __( 'Create ShipAny Order (From Draft)', 'pr-shipping-shipany' ) . '</button>';
			}
			
			if( empty( $label_tracking_info ) ) {
				$is_disabled = '';
				
				$print_button = '<a href="#" id="shipany-label-print" class="button button-primary" download target="_blank">' . __( 'Download Label', 'pr-shipping-shipany' ) . '</a>';

			} else {
				$is_disabled = 'disabled';

				if ($order_detail->lab_url == '') {
					$print_button = '<a href="'. $this->get_download_label_url( $order_id ) .'" id="shipany-label-print" class="button button-primary" download target="_blank" disabled="disabled" style="pointer-events: none">' .__( 'Download Label', 'pr-shipping-shipany' ) . '</a>';
				} else {
					if (is_multisite()) {
						$print_button = '<a href="'. get_site_url().'/wp-content/uploads/sites/'.get_current_blog_id().'/woocommerce_shipany_label/shipany-'.$label_tracking_info['shipment_id'].'.pdf' .'" id="shipany-label-print" class="button button-primary" download target="_blank">' .__( 'Download Label', 'pr-shipping-shipany' ) . '</a>';
					} else {
						$print_button = '<a href="'. get_site_url().'/wp-content/uploads/woocommerce_shipany_label/shipany-'.$label_tracking_info['shipment_id'].'.pdf' .'" id="shipany-label-print" class="button button-primary" download target="_blank">' .__( 'Download Label', 'pr-shipping-shipany' ) . '</a>';
					}
				}
			}

			if( empty( $label_tracking_info ) ) {
				$is_disabled = '';
				
				$pickup_request = '<button id="shipany-pickup-button" class="button button-primary button-save-form">' . __( 'Send Pickup Request', 'pr-shipping-shipany' ) . '</button>';

			} else {
				$is_disabled = 'disabled';

				// $pickup_request = '<a href="'. $this->send_pickup_request( $label_tracking_info ) .'" id="send_pickup_request" class="button button-primary">' .__( 'Send Pickup Request', 'pr-shipping-shipany' ) . '</a>';
				$pickup_request = '<button id="shipany-pickup-button" class="button button-primary button-save-form">' . __( 'Send Pickup Request', 'pr-shipping-shipany' ) . '</button>';
			}

			// todo: chk if reach
			if (!empty($label_tracking_info)) {
				if ($order_detail->cur_stat == 'Pickup Request Sent' || $order_detail->cur_stat == 'Pickup Request Received' || $order_detail->asn_id !='') {
					$pickup_request = '<button id="shipany-pickup-button" disabled="disabled" class="button button-primary button-save-form">' . __( 'Send Pickup Request', 'pr-shipping-shipany' ) . '</button>';
				}
			}


			$shipany_label_data = array(
				'main_button' => $main_button,
				'delete_label' => $delete_label,
				'print_button' => $print_button,
				'pickup_request' => $pickup_request,
				'label_tracking_info' => $label_tracking_info,
				'mch_uid' => $order_detail->mch_uid,
				'inv_print_button' => $inv_print_button_pre
			);


			echo '<div id="shipment-shipany-label-form">';

			if( !empty( $shipany_courier_list ) ) {

				echo '<div id="order_status">Order Status: '. $order_detail->cur_stat . '</div>';
				echo '<p>';
				echo '<div id="courier_service_plan" class="tooltip" data-title="The quoted price is just an estimated value.">Courier Service Plan: '. $order_detail->cour_svc_pl . ' - ' . $order_detail->cour_ttl_cost->ccy .' '. $order_detail->cour_ttl_cost->val . '</div>';
				woocommerce_wp_hidden_input( array(
					'id'    => 'pr_shipany_label_nonce',
					'value' => wp_create_nonce( 'create-shipany-label' )
				) );

				if ($is_local_pickup) {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_product',
						'label'       		=> __( 'Courier selected:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'value'       		=> $selected_courier,
						'options'			=> $shipany_courier_list,
						// 'custom_attributes'	=> array( $is_disabled => $is_disabled )
						'custom_attributes'	=> array( 'disabled' => 'disabled' )
					) );
				} else {
					woocommerce_wp_select ( array(
						'id'          		=> 'pr_shipany_product',
						'label'       		=> __( 'Courier selected:', 'pr-shipping-shipany' ), #shipany #rename button 
						'description'		=> '',
						'value'       		=> $selected_courier,
						'options'			=> $shipany_courier_list,
						'custom_attributes'	=> array( $is_disabled => $is_disabled )
						// 'custom_attributes'	=> array( 'disabled' => 'disabled' )
					) );
				}

				// Get weight UoM and add in label
				woocommerce_wp_text_input( array(
					'id'          		=> 'shipany_weight',
					'label'       		=> sprintf( __( 'Estimated shipment weight (%s) based on items ordered: ', 'pr-shipping-shipany' ), $weight_units),
					'placeholder' 		=> 'Please enter package weight',
					'description'		=> '',
					'value'       		=> $selected_weight_val,
					'custom_attributes'	=> array( $is_disabled => $is_disabled ),
					'class'				=> 'wc_input_decimal' // adds JS to validate input is in price format
				) );

				$this->additional_meta_box_fields( $order_id, $is_disabled, $shipany_label_items, $shipany_obj );


				// A label has been generated already, allow to delete
				// if( empty( $label_tracking_info ) ) {
				// 	echo wp_kses_post($main_button);
				// } else {
				// 	echo $print_button;
				// 	echo "<p>";
				// 	echo wp_kses_post($pickup_request);
				// }

				// do not vendor create button if order cancelled
				if ($order_detail->cur_stat != 'Order Cancelled') {
					echo wp_kses_post($main_button);
				}
				
				
				wp_enqueue_script( 'wc-shipment-shipany-label-js', SHIPANY_PLUGIN_DIR_URL . '/assets/js/shipany.js', array('jquery'), SHIPANY_VERSION );
				wp_localize_script( 'wc-shipment-shipany-label-js', 'shipany_label_data', $shipany_label_data + array('lalamove_addons_name_key_pair' => $lalamove_addons_name_key_pair) + array('shipany_order_detail' => $order_detail));
				
			} else {
				echo wp_kses_post('<p class="wc_shipany_error">' . __('There are no services available for the destination country!', 'pr-shipping-shipany') . '</p>');
			}
			
			echo '</div>';
		}
		
	}
	
	public function get_order_detail($order_id) {
		$response = SHIPANY()->get_shipany_factory()->api_client->get_order_info($order_id);
		if (!empty($response->body->data->objects[0]->cur_stat)) {
			return $response->body->data->objects[0];
		}
		return '';
	}

	protected function can_delete_label($order_id) {
		return true;
	}

	abstract public function additional_meta_box_fields( $order_id, $is_disabled, $shipany_label_items, $shipany_obj );


	public function save_meta_box( $post_id, $post = null ) {
		// loop through inputs within id 'shipment-shipany-label-form'
		$meta_box_ids = array( 'pr_shipany_product', 'shipany_weight');

		$additional_meta_box_ids = $this->get_additional_meta_ids( );
		$meta_box_ids = array_merge( $meta_box_ids, $additional_meta_box_ids );
		foreach ($meta_box_ids as $key => $value) {
			// Save value if it exists
			if ( isset( $_POST[ $value ] ) ) {
				$args[ $value ]	 = wc_clean( $_POST[ $value ] );
			} else {
                $args[ $value ]	 = '';
            }
		}

		if( isset( $args ) ) {
			$this->save_shipany_label_items( $post_id, $args );
			return $args;
		}
	}
	
	abstract public function get_additional_meta_ids();
	/**
	 * Order Tracking Save AJAX
	 *
	 * Function for saving tracking items
	 */
	public function save_meta_box_ajax_recreate( ) {
		try {
			$order_id = wc_clean( $_POST[ 'order_id' ] );
			// Save inputted data first
			$this->save_meta_box( $order_id );

			$api_tk = $this->shipping_shipnay_settings["shipany_api_key"];
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

			if ($this->shipping_shipnay_settings['shipany_region'] == '1') {
				$temp_api_endpoint = str_replace("api", "api-sg" , $temp_api_endpoint);
			}

			$myObj = new stdClass();
			$myObj->mch_uid = wc_clean( $_POST[ 'mch_id' ] );
			if ($myObj->mch_uid ==''){
				$response = wp_remote_get($temp_api_endpoint.'merchants/self/', array(
					'headers' => array(
						'api-tk'=> $api_tk,
						'sslverify' => false
					)
				));

				// handle timeout; TODO: retry
				if (is_wp_error( $response )){
					wp_send_json( array(
						'error' => $response->get_error_message()
					) );
				}

				if ($response["response"]["code"] == 200) {
					$myObj->mch_uid = json_decode($response['body'])->data->objects[0]->uid;
				}
			}
			$myObj->uid = wc_clean( $_POST[ 'shipany_order_id' ] );
			if ($myObj->uid == ''){
				$myObj->uid = $this->get_shipany_label_tracking( $order_id)['shipment_id'];
			}
			$myJSON = json_encode($myObj);


			$wp_shipany_rest_response = wp_remote_post(
				$temp_api_endpoint . 'orders/charge-order/ ',
				array( 'headers' => array('api-tk'=> $api_tk),
						'body' => $myJSON,
						'sslverify' => false,
						'timeout' => self::WP_POST_TIMEOUT)
			);

			# handle timeout;
			if (is_wp_error( $wp_shipany_rest_response )){
				$this->logger = new SHIPANY_Logger( 'yes' );
				$this->logger->write( __FUNCTION__ . '-' . $wp_shipany_rest_response->get_error_message() );
				wp_send_json( array(
					'error' => $wp_shipany_rest_response->get_error_message()
				) );
			}

			# Still no enough credit
			if ($wp_shipany_rest_response["response"]["code"] == 402) {
				wp_send_json( array(
					'error' => 'error',
					) );
				
				wp_die();
			} else if ($wp_shipany_rest_response["response"]["code"] != 200) { 
				wp_send_json( array(
					'error_detail' => json_decode($wp_shipany_rest_response["body"])->result->details[0]
					) );
				
				wp_die();
			} else if ($wp_shipany_rest_response["response"]["code"] == 200) {


				$label_tracking_info = $this->get_shipany_label_tracking( $order_id );
				$order_detail = $this->get_order_detail($label_tracking_info["shipment_id"]);
				$label_tracking_info['courier_tracking_url'] = $order_detail->trk_url;
				$label_tracking_info['courier_tracking_number'] = $order_detail->trk_no;
				$label_tracking_info['commercial_invoice_url'] = $order_detail->comm_invoice_url;
				$this->save_shipany_label_tracking( $order_id, $label_tracking_info );

				$label_url = $order_detail->lab_url;
				$response= wp_remote_get($label_url, array( 'sslverify' => false ));
				$label_pdf_data = wp_remote_retrieve_body( $response );
				file_put_contents( SHIPANY()->get_shipany_label_folder_dir() . 'shipany-' . $label_tracking_info["shipment_id"] . '.pdf', $label_pdf_data );


				$shipany_tracking_note_enable = $this->shipping_shipnay_settings['shipany_tracking_note_enable'];
				if (empty($shipany_tracking_note_enable) || $shipany_tracking_note_enable == 'yes') { 
					$tracking_note = $this->get_tracking_note( $order_id );
					$tracking_note_type = $this->get_tracking_note_type();
				} else {
					$tracking_note = '';
					$tracking_note_type = '';
				}

				$get_file_size = filesize($label_tracking_info["label_path"]);
				$insufficient_balance = $label_tracking_info["insufficient_balance"];

				wp_send_json( array(
					'button_txt' => __( 'Download Label', 'pr-shipping-shipany' ),
					'label_url' => $label_url,
					'get_file_size' => $get_file_size,
					'insufficient_balance' => $insufficient_balance,
					'courier_id' => $order_detail->cour_uid,
					'tracking_note'	  => $tracking_note,
					'tracking_note_type' => $tracking_note_type,
					'commercial_invoice_url'=> $order_detail->comm_invoice_url
					) );
				
				wp_die();
			}
		} catch (Exception $e) {
			$this->logger = new SHIPANY_Logger( 'yes' );
			$this->logger->write( __FUNCTION__ . '-' . $e );
		}
	
	}
	public function save_meta_box_ajax( ) {
		try {
			check_ajax_referer( 'create-shipany-label', 'pr_shipany_label_nonce' );
			$order_id = wc_clean( $_POST[ 'order_id' ] );
			$storage_type = wc_clean($_POST["pr_shipany_storage_type"]);
			// Save inputted data first
			$this->save_meta_box( $order_id );

			// Gather args for API call
			$args = $this->get_label_args( $order_id );
			$args["order_details"]['storage_type'] = $storage_type;
			// Allow third parties to modify the args to the APIs
			$args = apply_filters('shipping_shipany_label_args', $args, $order_id );

			$shipany_obj = SHIPANY()->get_shipany_factory();
			$label_tracking_info = $shipany_obj->get_shipany_label( $args );

			$this->save_shipany_label_tracking( $order_id, $label_tracking_info );
			$shipany_tracking_note_enable = $this->shipping_shipnay_settings['shipany_tracking_note_enable'];

			if (empty($shipany_tracking_note_enable) || $shipany_tracking_note_enable == 'yes') {
				$tracking_note = $this->get_tracking_note( $order_id );
				$tracking_note_type = $this->get_tracking_note_type();
			} else {
				$tracking_note = '';
				$tracking_note_type = '';
			}

			$label_url = $this->get_download_label_url( $order_id );

			if ($label_tracking_info['asn_id'] != '') {
				$tracking_note ='';
			}

			//check 0 size pdf
			$get_file_size = filesize($label_tracking_info["label_path"]);
			$insufficient_balance = $label_tracking_info["insufficient_balance"];
			$ext_order_not_created = $label_tracking_info["ext_order_not_created"];
			$response_details = $label_tracking_info["response_details"];
			$commercial_invoice_url = $label_tracking_info["commercial_invoice_url"];
			do_action( 'pr_shipping_shipany_label_created', $order_id );


			wp_send_json( array( 
				'download_msg' => __('Your label is ready to download, click the "Download Label" button above"', 'pr-shipping-shipany'),
				'button_txt' => __( 'Download Label', 'pr-shipping-shipany' ),
				'label_url' => is_multisite() ? get_site_url().'/wp-content/uploads/sites/'.get_current_blog_id().'/woocommerce_shipany_label/shipany-'.$label_tracking_info['shipment_id'].'.pdf' : get_site_url().'/wp-content/uploads/woocommerce_shipany_label/shipany-'.$label_tracking_info['shipment_id'].'.pdf',
				'label_url_s3' => $label_tracking_info["label_path_s3"],
				'tracking_note'	  => $tracking_note,
				'tracking_note_type' => $tracking_note_type,
				'get_file_size' => $get_file_size,
				'insufficient_balance' => $insufficient_balance,
				'courier_service_plan' => $label_tracking_info['courier_service_plan'],
				'asn_id' => $label_tracking_info['asn_id'],
				'ext_order_not_created' => $ext_order_not_created,
				'response_details' => $response_details,
				'commercial_invoice_url' => $commercial_invoice_url
				) );
			
			wp_die();
		} catch (Exception $e) {

			wp_send_json( array('error' => $e->getMessage() ));

			$this->logger = new SHIPANY_Logger( 'yes' );
			$this->logger->write( __FUNCTION__ . '-' . $e );
		}

	}
	public function save_meta_box_ajax_auto($result, $order_id ) {

		try {
			// set auto prefix
			$auto = true;

			// $order_id = wc_clean( $_POST[ 'order_id' ] );
			// Save inputted data first
			if ($this->shipping_shipnay_settings['set_default_create'] == 'no') {
				return $result;
			}
			$this->save_meta_box( $order_id );

			// Gather args for API call
			$args = $this->get_label_args( $order_id );
			if (isset($this->shipping_shipnay_settings["set_default_storage_type"])){
				$args["order_details"]['storage_type'] = $this->shipping_shipnay_settings["set_default_storage_type"];
			}
			
			// Allow third parties to modify the args to the APIs
			$args = apply_filters('shipping_shipany_label_args', $args, $order_id );
			// $args['order_details']['weight']=$args['items'][0]['item_weight'] * $args['items'][0]['qty'];

			if ($this->shipping_shipnay_settings["default_weight"] == 'yes') {
				$args['order_details']['weight'] = 1;
			} else {
				foreach ($args["items"] as $element) {
					$args['order_details']['weight'] = floatval($args['order_details']['weight']) + ($element['qty'] * floatval($element['item_weight']));
				}
			}

			$args['order_details']['weight'] = strval($args['order_details']['weight']);
			$shipany_obj = SHIPANY()->get_shipany_factory();
			$label_tracking_info = $shipany_obj->get_shipany_label( $args, $auto );
			if ($label_tracking_info == false) {
				return $result;
			}
			$this->save_shipany_label_tracking( $order_id, $label_tracking_info );
			$shipany_tracking_note_enable = $this->shipping_shipnay_settings['shipany_tracking_note_enable'];

			if (empty($shipany_tracking_note_enable) || $shipany_tracking_note_enable == 'yes') {
				$tracking_note = $this->get_tracking_note( $order_id );
				$tracking_note_type = $this->get_tracking_note_type();
				if ($label_tracking_info['asn_id'] == '') {
					$order = wc_get_order( $order_id );
					$order->add_order_note( $tracking_note, $tracking_note_type, true );
				}
			}

			$label_url = $this->get_download_label_url( $order_id );

			
			do_action( 'pr_shipping_shipany_label_created', $order_id );

			return $result;

		} catch (Exception $e) {
			$this->logger = new SHIPANY_Logger( 'yes' );
			$this->logger->write( __FUNCTION__ . '-' . $e );
			do_action( 'shutdown' );
		}
	}
	public function delete_label_ajax( ) {
		check_ajax_referer( 'create-shipany-label', 'pr_shipany_label_nonce' );
		$order_id = wc_clean( $_POST[ 'order_id' ] );

		try {

			$args = $this->delete_label_args( $order_id );
			$shipany_obj = SHIPANY()->get_shipany_factory();
			
			// Delete meta data first in case there is an error with the API call
			$this->delete_shipany_label_tracking( $order_id ); 
			$shipany_obj->delete_shipany_label( $args );
			
			$tracking_num = $args['tracking_number'];

			wp_send_json( array( 
				'download_msg' => __('Your label is ready to download, click the "Download Label" button above"', 'pr-shipping-shipany'), 
				//shipany
				//'button_txt' => __( 'Generate Label', 'pr-shipping-shipany' ), 
				'button_txt' => __( 'Create ShipAny Order', 'pr-shipping-shipany' ), 
				'shipany_tracking_num'	  => $tracking_num
				) );

		} catch (Exception $e) {

			wp_send_json( array( 'error' => $e->getMessage() ) );
		}
	}

	public function send_pickup_request( ) {
		$order_id = wc_clean( $_POST[ 'order_id' ] );
		$order=wc_get_order($order_id);
		$args = $this->get_shipany_label_tracking( $order_id );
		// $shipany_obj = SHIPANY()->get_shipany_factory();
		$pickup_request_result = SHIPANY()->get_shipany_factory()->send_pickup_request( $args["tracking_number"]);

		if ($pickup_request_result->status === 200) {
			$lab_url = $pickup_request_result->body->data->objects[0]->lab_url;
		
			$order->update_meta_data( '_pr_shipment_shipany_order_state', 'Pickup_Request_Sent' );
			$order->save();
			if ($pickup_request_result->body->data->objects[0]->cour_api_typ == 'Zeek2Door') {
				$lab_url = $pickup_request_result->body->data->objects[0]->lab_url;
				$response= wp_remote_get($lab_url, array( 'sslverify' => false ));
				$label_pdf_data = wp_remote_retrieve_body( $response );
				$shipment_id = $pickup_request_result->body->data->objects[0]->uid;
				$shipany_obj = new SHIPANY_API_eCS_Asia( 'dum' );
				$shipany_obj->save_shipany_label_file( 'item', $shipment_id, $label_pdf_data );

				$sndr_pf_dt_beg = $pickup_request_result->body->data->objects[0]->sndr_pf_dt_beg;
				$dt_beg = new DateTime($sndr_pf_dt_beg, new DateTimeZone('UTC'));
				$dt_beg->setTimezone(new DateTimeZone('HONGKONG'));
				$dt_beg_str = $dt_beg->format('Y-m-d H:i:s');

				$sndr_pf_dt_end = $pickup_request_result->body->data->objects[0]->sndr_pf_dt_end;
				$dt_end = new DateTime($sndr_pf_dt_end, new DateTimeZone('UTC'));
				$dt_end->setTimezone(new DateTimeZone('HONGKONG'));
				$dt_end_str = $dt_end->format('Y-m-d H:i:s');

				wp_send_json( array( 
					'lab_url' => is_multisite() ? get_site_url().'/wp-content/uploads/sites/'.get_current_blog_id().'/woocommerce_shipany_label/shipany-'.$shipment_id.'.pdf' : get_site_url().'/wp-content/uploads/woocommerce_shipany_label/shipany-'.$shipment_id.'.pdf',
					'dt_beg_str' => $dt_beg_str,
					'dt_end_str' => $dt_end_str
				) );
			} else if ($pickup_request_result->body->data->objects[0]->cour_api_typ == 'UPS') {
				$sndr_pf_dt_beg = $pickup_request_result->body->data->objects[0]->sndr_pf_dt_beg;
				$dt_beg = new DateTime($sndr_pf_dt_beg, new DateTimeZone('UTC'));
				$dt_beg->setTimezone(new DateTimeZone('HONGKONG'));
				$dt_beg_str = $dt_beg->format('Y-m-d H:i:s');

				$sndr_pf_dt_end = $pickup_request_result->body->data->objects[0]->sndr_pf_dt_end;
				$dt_end = new DateTime($sndr_pf_dt_end, new DateTimeZone('UTC'));
				$dt_end->setTimezone(new DateTimeZone('HONGKONG'));
				$dt_end_str = $dt_end->format('Y-m-d H:i:s');
				wp_send_json( array( 
					'dt_beg_str' => $dt_beg_str,
					'dt_end_str' => $dt_end_str
				) );
			} else if (in_array($pickup_request_result->body->data->objects[0]->cour_api_typ, array('Lalamove', 'ZeekDash', 'Zeek', 'Jumppoint')) || ($pickup_request_result->body->data->objects[0]->cour_api_typ == 'SfExpressV2' && strpos($pickup_request_result->body->data->objects[0]->cour_svc_pl, 'International') != false)) {
				// TODO: Should compare the order body and api response to decide render the button or not
				$lab_url = $pickup_request_result->body->data->objects[0]->lab_url;
				$response= wp_remote_get($lab_url, array( 'sslverify' => false ));
				$label_pdf_data = wp_remote_retrieve_body( $response );
				$shipment_id = $pickup_request_result->body->data->objects[0]->uid;
				$shipany_obj = new SHIPANY_API_eCS_Asia( 'dum' );
				$shipany_obj->save_shipany_label_file( 'item', $shipment_id, $label_pdf_data );
				wp_send_json( array( 
					'lab_url' => is_multisite() ? get_site_url().'/wp-content/uploads/sites/'.get_current_blog_id().'/woocommerce_shipany_label/shipany-'.$shipment_id.'.pdf' : get_site_url().'/wp-content/uploads/woocommerce_shipany_label/shipany-'.$shipment_id.'.pdf'
				) );
			}
			wp_send_json( array(
				'cur_stat' => $pickup_request_result->body->data->objects[0]->cur_stat
			) );
		} else if ($pickup_request_result->status === 400 && strpos($pickup_request_result->body->result->details[0], 'expired') !== false) {
			$woo_order_details = wc_get_order($_POST[ 'order_id' ]);
			$courier_service_pl = trim(explode('-', $args["courier_service_plan"])[0]);
			$this->save_meta_box( $_POST[ 'order_id' ] );
			$label_args = $this->get_label_args( $_POST[ 'order_id' ] );
			$items =$label_args['items'];
			$items_array = array();
			$alpha_three_country_code = CommonUtils::convert_country_code($woo_order_details->get_shipping_country());
			foreach ($items as $item) {
				$item_array = array(
					"sku" => $item["sku"],
					"name" => $item["item_description"],
					"typ" => "",
					"descr" => "",
					"ori" => "",
					"unt_price" => array(
						"val" => floatval($item["item_value"]),
						"ccy" => "HKD"
					),
					"qty" => floatval($item["qty"]),
					"wt" => array(
						"val" => floatval($item["item_weight"]),
						"unt" => "kg"
					),
					"dim" => array(
						"len" => floatval($item["length"]),
						"wid" => floatval($item["width"]),
						"hgt" => floatval($item["height"]),
						"unt" => 1
					),
					"stg" => ""
				);
				$items_array[] = $item_array;
			}
			$data = array(
				"stg" => "Normal",
				"cour_svc_pl" => $courier_service_pl,
				"wt" => array(
					"val" => floatval($this->calculate_order_weight( $_POST["order_id"] )),
					"unt" => 'kg'
				),
				"dim" => array(
					"len" => 1,
					"wid" => 1,
					"hgt" => 1,
					"unt" => 1
				),
				"items" => $items_array,
				"mch_ttl_val" => array(
					"val" => 1,
					"ccy" => "HKD"
				),
				"sndr_ctc" => array(
					"ctc" => array(
						"tit" => "",
						"f_name" => $woo_order_details->data["shipping"]["first_name"],
						"l_name" => $woo_order_details->data["shipping"]["last_name"],
						"phs" => array(
							array(
								"typ" => "",
								"cnty_code" => "",
								"ar_code" => "",
								"num" => "",
								"ext_no" => ""
							)
						),
						"email" => "",
						"note" => ""
					),
					"addr" => array(
						"typ" => "Residential",
						"ln" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->ln,
						"ln2" => "",
						"ln3" => "",
						"distr" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->distr,
						"city" => json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->city,
						"cnty" => "HKG",
						"state"=> json_decode($this->shipping_shipnay_settings["merchant_info"])->data->objects[0]->co_info->org_ctcs[0]->addr->state
					)
				),
				"rcvr_ctc" => array(
					"ctc" => array(
						"tit" => "",
						"f_name" => "",
						"l_name" => "",
						"phs" => array(
							array(
								"typ" => "Residential",
								"cnty_code" => "",
								"ar_code" => "",
								"num" => "",
								"ext_no" => ""
							)
						),
						"email" => "",
						"note" => ""
					),
					"addr" => array(
						"typ" => "Residential",
						"ln" => $woo_order_details->data["shipping"]["address_1"],
						"ln2" => $woo_order_details->data["shipping"]["address_2"],
						"ln3" => "",
						"distr" => $woo_order_details->data["shipping"]["city"],
						"cnty" => $alpha_three_country_code,
						'state' => $label_args["shipping_address"]["state"],
						"city" => $woo_order_details->data["shipping"]["city"],
						"zc" => $woo_order_details->data["shipping"]["postcode"]
					)
				)
			);

			//lalamove
			$data["add-ons"]["tunnel"] = array();
			foreach ($_POST["lalamove_tunnel"] as $tunnel_element) {
				array_push($data["add-ons"]["tunnel"], array("code" => $tunnel_element));
	
			}
			$data["add-ons"]["additional_services"] = array();
			foreach ($_POST["lalamove_additional"] as $services_element) {
				array_push($data["add-ons"]["additional_services"], array("code" => $services_element));
			}

			// the trick here we need to consider if empty rate return but have error
			$courier_service_plans = SHIPANY()->get_shipany_factory()->api_client->query_rate($data, array('cour-uid' => $_POST["courier_uid"]));
			wp_send_json( array(
				'error_expired' => $pickup_request_result->body->result->details[0],
				'val' => $courier_service_plans[0]->cour_ttl_cost->val,
				'quot_uid' =>$courier_service_plans[0]->quot_uid,
				'shipany_order_id' => $args["tracking_number"]
			) );
		} else {
			wp_send_json( array(
				'error_detail' => $pickup_request_result->body->result->details[0]
			) );
		}
	}

	protected function get_download_label_url( $order_id ) {
		
		if( empty( $order_id ) ) {
			return '';
		}

		$label_tracking_info = $this->get_shipany_label_tracking( $order_id );
		// Check whether the label has already been created or not
		if( empty( $label_tracking_info ) ) {
			return '';
		}
		
		// If no 'label_path' isset but a 'label_url' is set them return it...
		// ... this indicates an old download style label!
		if ( ! isset( $label_tracking_info['label_path'] ) && isset( $label_tracking_info['label_url'] ) ){
			return $label_tracking_info['label_url'];
		}

		// Override URL with our solution's download label endpoint:
		return $this->generate_download_url( '/' . self::SHIPANY_DOWNLOAD_ENDPOINT . '/' . $order_id );
	}

	protected function get_tracking_note( $order_id ) {
		
		$courier_tracking_no = '';
		if ( $this->get_shipany_label_tracking( $order_id ) != "") {
			$label_info = $this->get_shipany_label_tracking( $order_id );
			$courier_tracking_no = $label_info['courier_tracking_number'];
			$courier_name = strtok($label_info['courier_service_plan'], '-' );
			$courier_url = $label_info['courier_tracking_url'];
		}

		if( ! empty( $this->shipping_shipnay_settings['shipany_tracking_note_txt'] ) ) {
			$tracking_note_txt = $this->shipping_shipnay_settings['shipany_tracking_note_txt'];
			$tracking_note = sprintf( __( $tracking_note_txt . ' {tracking-link}' . "\n" . $courier_name . 'Tracking Number: <a href="'. $courier_url .'" target="_blank">'. $courier_tracking_no .'</a>', 'pr-shipping-shipany' ));
		} else {
			if ($courier_tracking_no == '') {
				$tracking_note = sprintf( __( 'ShipAny Tracking Number: {tracking-link}', 'pr-shipping-shipany' ));
			} else {
				$tracking_note = sprintf( __( 'ShipAny Tracking Number: {tracking-link}' . "\n" . $courier_name . 'Tracking Number: <a href="'. $courier_url .'" target="_blank">'. $courier_tracking_no .'</a>', 'pr-shipping-shipany' ));
			}
		}
		
		$tracking_link = $this->get_tracking_link( $order_id );

		if( empty( $tracking_link ) ) {
		    return '';
        }

		$tracking_note_new = str_replace('{tracking-link}', $tracking_link, $tracking_note, $count);
		if( $count == 0 ) {
			$tracking_note_new = $tracking_note . ' ' . $tracking_link;
		}
		
		return $tracking_note_new;
	}

	protected function get_tracking_link( $order_id ) {
		
		$label_tracking_info = $this->get_shipany_label_tracking( $order_id );
		if( empty( $label_tracking_info['tracking_number'] ) ) {
			return '';
		}

		return sprintf( __( '<a href="%s%s" target="_blank">%s</a>', 'pr-shipping-shipany' ), $this->get_tracking_url(), $label_tracking_info['tracking_number'], $label_tracking_info['tracking_number']);
	}

	abstract protected function get_tracking_url();

	protected function get_tracking_note_type() {
		if( isset( $this->shipping_shipnay_settings['shipany_tracking_note'] ) && ( $this->shipping_shipnay_settings['shipany_tracking_note'] == 'yes' ) ) {
			return '';
		} else {
			return 'customer';
		}
	}

	public function add_tracking_note_email_placeholder( $string, $email ) {

		$placeholder = '{shipany_tracking_note}'; // The corresponding placeholder to be used
		
    	$order = $email->object; // Get the instance of the WC_Order Object
		
		// Ensure the object is an order and not another type
		if ( ! ( $order instanceof WC_Order ) ) {
    		return $string;
    	}

		$tracking_note = $this->get_tracking_note( $order->get_id() );

    	// Return the clean replacement tracking_note string for "{tracking_note}" placeholder
    	return str_replace( $placeholder, $tracking_note, $string );
	}
	
	public function tracking_note_shortcode( $atts, $content ) {

		extract(shortcode_atts(array(
			'order_id' => ''
		), $atts));

		if( $order = wc_get_order( $order_id ) ){

			return $this->get_tracking_note( $order->get_id() );

		}

    	return '';
	}

	public function tracking_link_shortcode( $atts, $content ) {

		extract(shortcode_atts(array(
			'order_id' => ''
		), $atts));

		if( $order = wc_get_order( $order_id ) ){

			return $this->get_tracking_link( $order->get_id() );

		}

    	return '';
	}

	/**
	 * Saves the tracking items array to post_meta.
	 *
	 * @param int   $order_id       Order ID
	 * @param array $tracking_items List of tracking item
	 *
	 * @return void
	 */
	public function save_shipany_label_tracking( $order_id, $tracking_items ) {

		if( isset( $tracking_items['label_path'] ) && validate_file( $tracking_items['label_path'] ) === 2 ){
			$tracking_items['label_path'] = wp_slash( $tracking_items['label_path'] );
		}
		
		update_post_meta( $order_id, '_pr_shipment_shipany_label_tracking', $tracking_items );

		$tracking_details = array(
			'carrier' 			=> $this->carrier,
			'tracking_number' 	=> $tracking_items['tracking_number'],
			'ship_date' 		=> date( "Y-m-d", time() )
		);

		// Primarily added for "Advanced Tracking" plugin integration
		do_action( 'pr_save_shipany_label_tracking', $order_id, $tracking_details );
	}

	/*
	 * Gets all tracking items fron the post meta array for an order
	 *
	 * @param int  $order_id  Order ID
	 *
	 * @return tracking items
	 */
	public function get_shipany_label_tracking( $order_id ) {
		return get_post_meta( $order_id, '_pr_shipment_shipany_label_tracking', true );
	}

	/**
	 * Delete the tracking items array to post_meta.
	 *
	 * @param int   $order_id       Order ID
	 *
	 * @return void
	 */
	public function delete_shipany_label_tracking( $order_id ) {
		delete_post_meta( $order_id, '_pr_shipment_shipany_label_tracking' );

		do_action( 'pr_delete_shipany_label_tracking', $order_id );
	}

	/**
	 * Saves the label items array to post_meta.
	 *
	 * @param int   $order_id       Order ID
	 * @param array $tracking_items List of tracking item
	 *
	 * @return void
	 */
	public function save_shipany_label_items( $order_id, $tracking_items ) {
		update_post_meta( $order_id, '_pr_shipment_shipany_label_items', $tracking_items );
	}

	/*
	 * Gets all label items fron the post meta array for an order
	 *
	 * @param int  $order_id  Order ID
	 *
	 * @return label items
	 */
	public function get_shipany_label_items( $order_id ) {
		return get_post_meta( $order_id, '_pr_shipment_shipany_label_items', true );
	}

	/*
	 * Save default fields, used by bulk create label
	 *
	 * @param int  $order_id  Order ID
	 *
	 * @return default label items
	 */
	protected function save_default_shipany_label_items( $order_id ) {
		$shipany_label_items = $this->get_shipany_label_items( $order_id );

		if( empty( $shipany_label_items ) ) {
			$shipany_label_items = array();
		}

		if( empty( $shipany_label_items['shipany_weight'] ) ) {
			// Set default weight
			$shipany_label_items['shipany_weight'] = $this->calculate_order_weight( $order_id );
		}

		// if( empty( $shipany_label_items['pr_shipany_product'] ) ) {
		// 	// Set default product
		// 	$shipany_label_items['pr_shipany_product'] = $this->get_default_courier( $order_id );
		// }

		// Save default items
		$this->save_shipany_label_items( $order_id, $shipany_label_items );
	}

	protected function get_default_courier( $order_id ) {
		// $this->shipping_shipnay_settings = SHIPANY()->get_shipping_shipany_settings();
		return $this->shipping_shipnay_settings['shipany_default_courier'];
	}

	protected function calculate_order_weight( $order_id ) {
		
		if ($this->shipping_shipnay_settings["default_weight"] == 'yes') {

			$total_weight = 1;

		} else {

			$total_weight 	= 0;
			$order 			= wc_get_order( $order_id );
	
			if( false === $order ){
				return apply_filters('shipping_shipany_order_weight', $total_weight, $order_id );	
			}
	
			$ordered_items = $order->get_items( );
	
			if( is_array( $ordered_items ) && count( $ordered_items ) > 0 ){
	
				foreach ($ordered_items as $key => $item) {
						
					if( ! empty( $item['variation_id'] ) ) {
						$product = wc_get_product($item['variation_id']);
					} else {
						$product = wc_get_product( $item['product_id'] );
					}
					
					if ( $product ) {
						$product_weight = $product->get_weight();
						if( $product_weight ) {
							$total_weight += ( $item['qty'] * $product_weight );
						}
					}
				}
	
			}
	
			if ( ! empty( $this->shipping_shipnay_settings['shipany_add_weight'] ) ) {
	
				if ( $this->shipping_shipnay_settings['shipany_add_weight_type'] == 'absolute' ) {
					$total_weight += $this->shipping_shipnay_settings['shipany_add_weight'];
				} elseif ( $this->shipping_shipnay_settings['shipany_add_weight_type'] == 'percentage' ) {
					$total_weight += $total_weight * ( $this->shipping_shipnay_settings['shipany_add_weight'] / 100 );
				}
			}
		}

		return apply_filters('shipping_shipany_order_weight', $total_weight, $order_id );
	}

	protected function is_shipping_domestic( $order_id ) {   	 
		$order = wc_get_order( $order_id );
		$shipping_address = $order->get_address( 'shipping' );
		$shipping_country = $shipping_address['country'];

		if( SHIPANY()->is_shipping_domestic( $shipping_country ) ) {
			return true;
		} else {
			return false;
		}
	}

	protected function is_crossborder_shipment( $order_id ) {   	 
		$order = wc_get_order( $order_id );
		$shipping_address = $order->get_address( 'shipping' );
		$shipping_country = $shipping_address['country'];

		if( SHIPANY()->is_crossborder_shipment( $shipping_country ) ) {
			return true;
		} else {
			return false;
		}
	}

	// This function gathers all of the data from WC to send to API
	public function get_label_args( $order_id ) {

		$shipany_label_items = $this->get_shipany_label_items( $order_id );

		if($shipany_label_items["shipany_description"]==""){
			$shipany_label_items["shipany_description"] = " ";
		}
		// Get settings from child implementation
		$args = $this->get_label_args_settings( $order_id, $shipany_label_items );		
		
		$order = wc_get_order( $order_id );
		// Get service product
		$args['order_details']['shipany_product'] = $shipany_label_items['pr_shipany_product'];
		// $args['order_details']['duties'] = $shipany_label_items['shipping_shipany_duties'];
		$args['order_details']['weight'] = $shipany_label_items['shipany_weight'];

		// Get WC specific details; order id, currency, units of measure, COD amount (if COD used)
		$args['order_details']['order_id'] = $order_id;
		// $args['order_details']['currency'] = get_woocommerce_currency();
		$args['order_details']['currency'] = $this->get_wc_currency( $order_id );
		$args['order_details']['weightUom'] = 'kg';

		$args['order_details']['dimUom'] = get_option( 'woocommerce_dimension_unit' );

		if( $this->is_cod_payment_method( $order_id ) ) {
			$args['order_details']['cod_value']	= $order->get_total();			
		}

		// calculate the additional fee
		$additional_fees = 0;
		if( count( $order->get_fees() ) > 0 ){
			foreach( $order->get_fees() as $fee ){

				if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' ) ){

					$additional_fees += floatval( $fee->get_total() );

				}else{
					
					$additional_fees += floatval( $fee['line_total'] );
					
				}
				
			}
		}

		$args['order_details']['additional_fee'] 	= $additional_fees;

		if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' ) ){

			$args['order_details']['shipping_fee'] 		= $order->get_shipping_total();

		}else{

			$args['order_details']['shipping_fee'] 		= $order->get_total_shipping();

		}
		
		
		$args['order_details']['total_value'] = $order->get_total();			
		
		// Get address related information 
		$billing_address = $order->get_address();
		$shipping_address = $order->get_address( 'shipping' );

		// If shipping phone number doesn't exist, try to get billing phone number
		// if( ! isset( $shipping_address['phone'] ) && isset( $billing_address['phone'] ) ) {
		
		if ($shipping_address['phone'] != '') {
			$shipping_address['phone'] = $shipping_address['phone'];
		} else if( $billing_address['phone'] != '' ) {
			$shipping_address['phone'] = $billing_address['phone'];			
		}
		
		// If shipping email doesn't exist, try to get billing email
		if( !isset( $shipping_address['email'] ) && isset( $billing_address['email'] ) ) {
			$shipping_address['email'] = $billing_address['email'];
		}

		// Merge first and last name into "name"
		$shipping_address['name'] = '';
		if ( isset( $shipping_address['first_name'] ) ) {
			$shipping_address['name'] = $shipping_address['first_name'];
			// unset( $shipping_address['first_name'] );
		}

		if ( isset( $shipping_address['last_name'] ) ) {
			if( ! empty( $shipping_address['name'] ) ) {
				$shipping_address['name'] .= ' ';
			}

			$shipping_address['name'] .= $shipping_address['last_name'];
			// unset( $shipping_address['last_name'] );
		}
		
		// If not USA or Australia, then change state from ISO code to name
		if ( $shipping_address['country'] != 'US' && $shipping_address['country'] != 'AU' ) {
			// Get all states for a country
			$states = WC()->countries->get_states( $shipping_address['country'] );

			// If the state is empty, it was entered as free text
			if ( ! empty($states) && ! empty( $shipping_address['state'] ) ) {
				// Change the state to be the name and not the code
				$shipping_address['state'] = $states[ $shipping_address['state'] ];
				
				// Remove anything in parentheses (e.g. TH)
				$ind = strpos($shipping_address['state'], " (");
				if( false !== $ind ) {
					$shipping_address['state'] = substr( $shipping_address['state'], 0, $ind );
				}
			}
		}

		// Check if post number exists then send over
		if( $shipping_shipany_postnum = get_post_meta( $order_id, '_shipping_shipany_postnum', true ) ) {
			$shipping_address['shipany_postnum'] = $shipping_shipany_postnum;
		}

		$args['shipping_address'] = $shipping_address;

		// Get order item specific data
		$ordered_items = $order->get_items( );
		$args['items'] = array();
		// Sum value of ordered items
		$args['order_details']['items_value'] = 0;
		foreach ($ordered_items as $key => $item) {
            // Reset array
            $new_item = array();

			$new_item['qty'] = $item['qty'];
			// Get 1 item value not total items, based on ordered items in case currency is different that set product price
			$new_item['item_value'] = ( $item['line_total'] / $item['qty'] );
			// Sum 'line_total' to get items total value w/ discounts!
			$args['order_details']['items_value'] += $item['line_total'];

			$product = wc_get_product( $item['product_id'] );

			// If product does not exist (i.e. was deleted) OR is virtual, skip it
			if ( empty( $product ) || $product->is_virtual() ) {
				continue;
			}
			
			// get item dimension
			$new_item['length'] = $product->get_length();
			$new_item['width'] = $product->get_width();
			$new_item['height'] = $product->get_height();

		    $country_value = get_post_meta( $item['product_id'], '_shipany_manufacture_country', true );
		    if( ! empty( $country_value ) ) {
		    	$new_item['country_origin'] = $country_value;
		    }

			$hs_code = get_post_meta( $item['product_id'], '_shipany_hs_code', true );
			if( ! empty( $hs_code ) ) {
				$new_item['hs_code'] = $hs_code;
			}

			$new_item['item_description'] = $product->get_title();
			// $new_item['line_total'] = $item['line_total'];

			if( ! empty( $item['variation_id'] ) ) {
				$product_variation = wc_get_product($item['variation_id']);

				// If product variation does not exist (i.e. was deleted) OR is virtual, skip it
				if ( empty( $product_variation ) || $product_variation->is_virtual() ) {
					continue;
				}

				// place 'sku' in a variable before validating using 'empty' to be compatible with PHP v5.4
				$product_sku = $product_variation->get_sku();
				// Ensure id is string and not int
				$new_item['product_id'] = intval( $item['variation_id'] );
				$new_item['sku'] = empty( $product_sku ) ? strval( $item['variation_id'] ) : $product_sku;

				// If value is empty due to discounts, set variation price instead
				if ( empty( $new_item['item_value'] ) ) {
					$new_item['item_value'] = $product_variation->get_price();
				}
				
				$new_item['item_weight'] = $product_variation->get_weight();

				$product_attribute = wc_get_product_variation_attributes($item['variation_id']);
				$new_item['item_description'] .= ' : ' . current( $product_attribute );

			} else {
				// place 'sku' in a variable before validating using 'empty' to be compatible with PHP v5.4
				$product_sku = $product->get_sku();
				// Ensure id is string and not int
				$new_item['product_id'] = intval( $item['product_id'] );
				$new_item['sku'] = empty( $product_sku ) ? strval( $item['product_id'] ) : $product_sku;

				// If value is empty due to discounts, set product price instead
				if ( empty( $new_item['item_value'] ) ) {
					$new_item['item_value'] = $product->get_price();
				}

				$new_item['item_weight'] = $product->get_weight();
			}

			$new_item += $this->get_label_item_args( $item['product_id'], $args );
			// if( ! empty( $product->post->post_excerpt ) ) {
			// 	$new_item['item_description'] = $product->post->post_excerpt;
			// } elseif ( ! empty( $product->post->post_content ) ) {
			// 	$new_item['item_description'] = $product->post->post_content;
			// }

			array_push($args['items'], $new_item);
		}

		return $args;
	}

	abstract protected function get_label_args_settings( $order_id, $shipany_label_items );

	protected function delete_label_args( $order_id ) {
		return $this->get_shipany_label_tracking( $order_id );
	}

	// Pass args by reference to modify DG if needed
	protected function get_label_item_args( $product_id, &$args ) {
		$new_item = array();
		return $new_item;
	}

	protected function is_cod_payment_method( $order_id ) {
		$is_code = false;
		$order = wc_get_order( $order_id );
		// WC 3.0 comaptibilty
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			$payment_method = $order->get_payment_method();
			if ( $payment_method == 'cod' ) {
				$is_code = true;
			}
		}
		else {
			if ( isset( $order->payment_method ) && ( $order->payment_method == 'cod' ) ) {
				$is_code = true;
			}
		}

		return $is_code;
	}

	protected function get_wc_currency( $order_id ) {
		$order = wc_get_order( $order_id );
		// WC 3.0 comaptibilty
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			$order_currency = $order->get_currency();
		}
		else {
			$order_currency = $order->get_order_currency();
		}
		return $order_currency;
	}

	/**
	 * Prevents data being copied to subscription renewals
	 */
	public function woocommerce_subscriptions_renewal_order_meta_query( $order_meta_query ) {
		$order_meta_query .= " AND `meta_key` NOT IN ( '_pr_shipment_shipany_label_tracking' )";

		return $order_meta_query;
	}

	/**
	 * Display messages on order view screen
	 */	
	public function render_messages( $current_screen = null ) {
		if ( ! $current_screen instanceof WP_Screen ) {
			$current_screen = get_current_screen();
		}

		if ( isset( $current_screen->id ) && in_array( $current_screen->id, array( 'shop_order', 'edit-shop_order' ), true ) ) {

			$bulk_action_message_opt = get_option( '_shipany_bulk_action_confirmation' );

			if ( ( $bulk_action_message_opt ) && is_array( $bulk_action_message_opt ) ) {

				// $user_id = key( $bulk_action_message_opt );
				// remove first element from array and verify if it is the user id
				$user_id = array_shift( $bulk_action_message_opt );
				if ( get_current_user_id() !== (int) $user_id ) {
					return;
				}

				foreach ($bulk_action_message_opt as $key => $value) {
					$message = wp_kses_post( $value['message'] );
					$type = wp_kses_post( $value['type'] );

					switch ($type) {
                        case 'error':
                            echo '<div class="notice notice-error"><ul><li>' . esc_html($message) . '</li></ul></div>';
                            break;
                        case 'success':
                            echo '<div class="notice notice-success"><ul><li><strong>' . esc_html($message) . '</strong></li></ul></div>';
                            break;
                        default:
                            echo '<div class="notice notice-warning"><ul><li><strong>' . esc_html($message) . '</strong></li></ul></div>';
                    }
				}

				delete_option( '_shipany_bulk_action_confirmation' );
			}
		}
	}
	
	public function validate_bulk_actions( $action, $order_ids ) {
		return '';
	}
	
	public function process_bulk_actions( $action, $order_ids, $orders_count, $shipany_force_product = false, $is_force_product_dom = false ) {
		$label_count = 0;
		$merge_files = array();
		$array_messages = array();

		if ( 'shipany_create_labels' === $action ) {
			
			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );

				try {
					// Create label if one has not been created before
					if( empty( $label_tracking_info = $this->get_shipany_label_tracking( $order_id ) ) ) {

							$this->save_default_shipany_label_items( $order_id );

							// Gather args for API call
							$args = $this->get_label_args( $order_id );

							// Force the use of this Product for all bulk label creation
							if ( $shipany_force_product ) {

								// If forced product is domestic AND order is domestic
								if( $is_force_product_dom && $this->is_shipping_domestic( $order_id ) ) {
									$args['order_details']['shipany_product'] = $shipany_force_product;
								}

								// If forced product is international AND order is international
								if( ! $is_force_product_dom && ! $this->is_shipping_domestic( $order_id ) ) {
									$args['order_details']['shipany_product'] = $shipany_force_product;
								}
							}

							// Allow settings to override saved order data, ONLY for bulk action
							$args = $this->get_bulk_settings_override( $args );
							
							// Allow third parties to modify the args to the APIs
							$args = apply_filters('shipping_shipany_label_args', $args, $order_id );

							$shipany_obj = SHIPANY()->get_shipany_factory();
							$label_tracking_info = $shipany_obj->get_shipany_label( $args );

							$this->save_shipany_label_tracking( $order_id, $label_tracking_info );
							$tracking_note = $this->get_tracking_note( $order_id );
							
							$tracking_note_type = $this->get_tracking_note_type();
							$tracking_note_type = empty( $tracking_note_type ) ? 0 : 1;
							// $label_url = $label_tracking_info['label_url'];

							$order->add_order_note( $tracking_note, $tracking_note_type, true );
							
							++$label_count;

							array_push($array_messages, array(
                                'message' => sprintf( __( 'Order #%s: label Created', 'smart-send-shipping'), $order->get_order_number() ),
                                'type' => 'success',
                            ));

                            do_action( 'pr_shipping_shipany_label_created', $order_id );

					}

					if( ! empty( $label_tracking_info['label_path'] ) ) {
						array_push($merge_files, $label_tracking_info['label_path']);
					}

				} catch (Exception $e) {
					array_push($array_messages, array(
	                    'message' => sprintf( __( 'Order #%s: %s', 'smart-send-shipping'), $order->get_order_number(), $e->getMessage() ),
	                    'type' => 'error',
	                ));
				}
			}

			try {
				
				$file_bulk = $this->merge_label_files( $merge_files );
				
				if ( file_exists( $file_bulk['file_bulk_path'] ) ) {
					// $message .= sprintf( __( ' - %sdownload labels file%s', 'pr-shipping-shipany' ), '<a href="' . $file_bulk['file_bulk_url'] . '" target="_blank">', '</a>' );

	                // We're saving the bulk file path temporarily and access it later during the download process.
		    		// This information expires in 3 minutes (180 seconds), just enough for the user to see the 
		    		// displayed link and click it if he or she wishes to download the bulk labels
					set_transient( '_shipany_bulk_download_labels_file_' . get_current_user_id(), $file_bulk['file_bulk_path'], 180);	

					// Construct URL pointing to the download label endpoint (with bulk param):
					$bulk_download_label_url = $this->generate_download_url( '/' . self::SHIPANY_DOWNLOAD_ENDPOINT . '/bulk' );

					array_push($array_messages, array(
	                    'message' => sprintf( __( 'Bulk labels file created - %sdownload file%s', 'pr-shipping-shipany' ), '<a href="' . $bulk_download_label_url . '" download>', '</a>' ),
	                    'type' => 'success',
	                ));

		        } else {
					array_push($array_messages, array(
	                    'message' => __( 'Could not create bulk  label file, download individually.', 'pr-shipping-shipany' ),
	                    'type' => 'error',
	                ));
		        }

			} catch (Exception $e) {
				array_push($array_messages, array(
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ));
			}
		}

		return $array_messages;
	}

	/**
	 * Generates the download label URL
	 *
	 * @param string $endpoint_path
	 * @return string - The download URL for the label
	 */
	public function generate_download_url( $endpoint_path ) {

		// If we get a different URL addresses from the General settings then we're going to
		// construct the expected endpoint url for the download label feature manually
		if ( site_url() != home_url() ) {

			// You can use home_url() here as well, it really doesn't matter
			// as we're only after for the "scheme" and "host" info.
			$result = parse_url( site_url() );	

			if ( !empty( $result['scheme'] ) && !empty( $result['host'] ) ) {
				return $result['scheme'] . '://' . $result['host'] . $endpoint_path;
			}
		}

		// Defaults to the "Site Address URL" from the general settings along
		// with the the custom endpoint path (with parameters)
		return home_url( $endpoint_path );
	}

	protected function get_bulk_settings_override( $args ) {
		return $args;
	}

	protected function merge_label_files( $files ) {

		if( empty( $files ) ) {
			throw new Exception( __('There are no files to merge.', 'pr-shipping-shipany') );
		}

		if( ! empty( $files[0] ) ) {
			$base_ext = pathinfo($files[0], PATHINFO_EXTENSION);
		} else {
			throw new Exception( __('The first file is empty.', 'pr-shipping-shipany') );
		}

		if ( method_exists( $this, 'merge_label_files_' . $base_ext ) ) {
			return call_user_func( array( $this, 'merge_label_files_' . $base_ext ), $files );
		} else {
			throw new Exception( __('File format not supported.', 'pr-shipping-shipany') );
		}
	}

	/**
	 * Creates a custom endpoint to download the label
	 */
	public function add_download_label_endpoint() {
		add_rewrite_endpoint(  self::SHIPANY_DOWNLOAD_ENDPOINT, EP_ROOT );
		flush_rewrite_rules();
		update_option('shipany_permalinks_flushed', 1);
		//Flush permalink if it is not flushed yet.
		if( !get_option( 'shipany_permalinks_flushed') ){
			flush_rewrite_rules();
			update_option('shipany_permalinks_flushed', 1);
		}
	}

	/**
	 * Processes the download label request
	 *
	 * @return void
	 */
	public function process_download_label() {
	    global $wp_query;

	    if ( ! current_user_can( 'edit_shop_orders' ) ) {
  			return;
  		}
  		
		if ( ! isset($wp_query->query_vars[ self::SHIPANY_DOWNLOAD_ENDPOINT ] ) ) {
			return;
		}
		
	    // If we fail to add the "SHIPANY_DOWNLOAD_ENDPOINT" then we bail, otherwise, we
	    // will continue with the process below.
	    $endpoint_param = $wp_query->query_vars[ self::SHIPANY_DOWNLOAD_ENDPOINT ];
	    if ( ! isset( $endpoint_param ) ) {
	    	return;
	    }

	    $array_messages = get_option( '_shipany_bulk_action_confirmation' );
    	if ( empty( $array_messages ) || !is_array( $array_messages ) ) {
    		$array_messages = array( 'msg_user_id' => get_current_user_id() );
		}

	    if ( $endpoint_param == 'bulk' ) {

	    	$bulk_file_path = get_transient( '_shipany_bulk_download_labels_file_' . get_current_user_id() );

	    	if ( false == $this->download_label( $bulk_file_path ) ) {
	    		array_push($array_messages, array(
                    'message' => __( 'There are currently no bulk label file to download or the download link for the bulk label file has already expired. Please try again.', 'pr-shipping-shipany' ),
                    'type' => 'error'
                ));
			}

			$redirect_url  = admin_url( 'edit.php?post_type=shop_order' );
	    } else {
	    	$order_id = $endpoint_param;

	    	// Get tracking info if it exists
			$label_tracking_info = $this->get_shipany_label_tracking( $order_id );
			// Check whether the label has already been created or not
			if( empty( $label_tracking_info ) ) {
				return;
			}
			
			$label_path = $label_tracking_info['label_path'];

			if ( false == $this->download_label( $label_path ) ) {
	    		array_push($array_messages, array(
                    'message' => __( 'Unable to download file. Label appears to be invalid or is missing. Please try again.', 'pr-shipping-shipany' ),
                    'type' => 'error'
                ));
			}
			
			$redirect_url  = admin_url( 'post.php?post=' . $order_id . '&action=edit' );
	    }

	    update_option( '_shipany_bulk_action_confirmation', $array_messages );

	    // If there are errors redirect to the shop_orders and display error
	    if ( $this->has_error_message( $array_messages ) ) {
            wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), $redirect_url ) );
            exit;
		}
	}

	/**
	 * Checks whether the current "messages" collection has an
	 * error message waiting to be rendered.
	 *
	 * @param array $messages
	 * @return boolean
	 */
	protected function has_error_message( $messages ) {
		$has_error = false;

		foreach ( $messages as $key => $value ) {
			if ( $value['type'] == 'error' ) {
				$has_error = true;
				break;
			}
		}

		return $has_error;
	}

	/**
	 * Downloads the generated label file
	 *
	 * @param string $file_path
	 * @return boolean|void
	 */
	protected function download_label( $file_path ) {
		if ( !empty( $file_path ) && is_string( $file_path ) && file_exists( $file_path ) ) {
			// Check if buffer exists, then flush any buffered output to prevent it from being included in the file's content
			if ( ob_get_contents() ) {
				ob_clean();
			}

			$filename = basename( $file_path );

		    header( 'Content-Description: File Transfer' );
		    header( 'Content-Type: application/octet-stream' );
		    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		    header( 'Expires: 0' );
		    header( 'Cache-Control: must-revalidate' );
		    header( 'Pragma: public' );
		    header( 'Content-Length: ' . filesize( $file_path ) );

		    readfile( $file_path );
		    exit;
		} else {
			return false;
		}
	}

}

endif;
