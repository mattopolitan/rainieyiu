jQuery( function( $ ) {
	var wc_shipment_shipany_label_items = {
		// init Class
		init: function() {
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'click', '#shipany-label-button', this.save_shipany_label );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'click', '#shipany-label-rate-query-button', this.rate_query );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'click', '#shipany-label-button-recreate', this.save_shipany_label_recreate );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'click', '#shipany-pickup-button', this.pickup_fn );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_product', this.change_courier );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_courier_additional_service', this.change_courier_additional_service );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_paid_by_rec', this.change_show_paid_by_rcvr );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_couier_service_plan', this.change_couier_service_plan );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'click', '#shipany_delete_label', this.delete_shipany_label );
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_return_address_enabled', this.show_hide_return );
			wc_shipment_shipany_label_items.show_hide_return();

			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_identcheck', this.show_hide_ident );
			wc_shipment_shipany_label_items.show_hide_ident();

			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_routing', this.show_hide_routing);
			wc_shipment_shipany_label_items.show_hide_routing();

			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_product', this.validate_product_return );
		
			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', 'select#pr_shipany_total_packages', this.process_package_action );

			$( '#woocommerce-shipment-shipany-label' )
				.on( 'change', '#pr_shipany_multi_packages_enabled', this.show_hide_packages );
			wc_shipment_shipany_label_items.show_hide_packages();
			
			// hide Courier Additional Service, tunnel, Additional Requirements if not lalamove
			if ($( '#pr_shipany_product' ).find(":selected").text() != 'Lalamove' ) {
				if ($( '#pr_shipany_product' ).find(":selected").text() != 'ZeekDash' ) {
					$('.lalamove_additional_requirements__field').hide()
				}
				$('.pr_shipany_courier_additional_service_field').hide()
				$('.lalamove_tunnel__field').hide()
			}
			//lalamove change create order button
			$(document).ready(function() { 
				$('.shipany_lalamove_checkbox').change(function() {
					$('#shipany-label-button').text('Query Rate')
					$('#shipany-label-button').attr('id','shipany-label-rate-query-button')
				});
			});
			//lalamove write checkbox value
			if (shipany_label_data.shipany_order_detail["add-ons"] != undefined) {
				const tunnels = shipany_label_data.shipany_order_detail["add-ons"]['tunnel']
				const additional_services = shipany_label_data.shipany_order_detail["add-ons"]['additional_services']

				for (const key1 in tunnels) {
					$("input[name='lalamove_tunnel_"+ tunnels[key1].desc +"']").prop('checked', true);
				}
				for (const key2 in additional_services) {
					$("input[name='lalamove_additional_requirements_" + additional_services[key2].desc +"']").prop('checked', true);
				}
				
				$(function(){ 
					$('#shipment-shipany-label-form').each(function(i, div) {
						$(div).find('input').each(function(j, element){
						   $(element).prop('disabled', 'disabled');
						});
					});
				});
			}
			if (typeof shipany_label_data.courier_service_plans_error == 'string') {
				$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">Error: ' + shipany_label_data.courier_service_plans_error + '</p>');
			}
			if ($("[name='pr_shipany_couier_service_plan']").has('option').length < 1 ) {
				$("[name='pr_shipany_couier_service_plan']").prop( "disabled", true )
			}
		},

		rate_query: function(e) {
			$( '#shipment-shipany-label-form .wc_shipany_error' ).remove()
			//debugger
			var data = {
				action:                   'wc_shipment_shipany_update_courier_lalamove_addons',
				order_id:                 woocommerce_admin_meta_boxes.post_id
			};
			data['courier'] = $( '#pr_shipany_product' ).find(":selected").text()
			data['courier_uid'] = $( '#pr_shipany_product' ).find(":selected").val()
			data['courier_service'] = $( 'select[name="pr_shipany_courier_additional_service"]' ).find(":selected").text()
			var $form = $('#shipment-shipany-label-form');
			data[ 'lalamove_tunnel' ] = []
			data[ 'lalamove_additional' ] = []
			$form.each(function(i, div) {

			    $(div).find('input').each(function(j, element){

			        if( $(element).attr('type') == 'checkbox' ) {
			        	if ( $(element).prop('checked') ) {
							if ( $(element).attr('name').includes('lalamove_tunnel') ) {
								data[ 'lalamove_tunnel' ].push($(element).val())
							} else if ( $(element).attr('name').includes('lalamove_additional') ){
								data[ 'lalamove_additional' ].push($(element).val())
							} else {
								data[ $(element).attr('name') ] = 'yes'
							}
			        	} else {
				        	data[ $(element).attr('name') ] = 'no'
			        	}
			        } else {
			        	var eName = $(element).attr('name');
			        	// Do NOT add array inputs here!
			        	if (eName.indexOf("[]") == -1) {
			        		data[ $(element).attr('name') ] = $(element).val();
			        	}
			        }
			    });
	    	});

			$( '#shipment-shipany-label-form' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			} );
			$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
				$( '#shipment-shipany-label-form' ).unblock();
				$("[name='pr_shipany_couier_service_plan']").empty()
				if ( response.error ) {
					$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">Error: ' + response.error + '</p>');
					$('.pr_shipany_couier_service_plan_field').hide();
				} else {
					$('.pr_shipany_couier_service_plan_field').show()
					for (const plan in response.plans) {
						// $("[name='pr_shipany_couier_service_plan']").append('<option value=' + plan + '>' + response.plans[plan] +'</option>')
						var option = document.createElement("option");
						option.text = response.plans[plan];
						option.value = plan;
						var select = document.getElementById("pr_shipany_couier_service_plan");
						select.appendChild(option);
					}
					// recover the create order button
					$('#shipany-label-rate-query-button').text('Create ShipAny Order')
					$('#shipany-label-rate-query-button').attr('id','shipany-label-button')
				}
			});
			return false

		},

		change_show_paid_by_rcvr: function(e) {
			$( '#shipment-shipany-label-form .wc_shipany_error' ).remove()
			if (this.checked) {
				$("#pr_shipany_couier_service_plan option").each(function(index, element)
				{
					if (!shipany_label_data.service_plan_show_paid_by_rec.includes(this.innerHTML.split(' - ')[0])) {
						window.removedOption = this
						$(this).remove()
					}
				});
			} else if (!this.checked && window.removedOption != undefined) {
				$("#pr_shipany_couier_service_plan").append(window.removedOption)
			}
		},

		change_couier_service_plan: function(e) {
			$( '#shipment-shipany-label-form .wc_shipany_error' ).remove()
				if (shipany_label_data.service_plan_show_paid_by_rec.includes($( 'select[name="pr_shipany_couier_service_plan"]' ).find(":selected").text().split(' - ')[0])) {
					$('.pr_shipany_paid_by_rec_field').show()
				} else {
					$('#pr_shipany_paid_by_rec').prop( "checked", false )
					$('.pr_shipany_paid_by_rec_field').hide()
				}
		},

		change_courier_additional_service: function(e) {
			$( '#shipment-shipany-label-form .wc_shipany_error' ).remove()
			//always change the create order button to rate query button
			$('#shipany-label-button').text('Query Rate')
			$('#shipany-label-button').attr('id','shipany-label-rate-query-button')
			$('.shipany_lalamove_checkbox').parent().parent().remove()
			for (const key in shipany_label_data.lalamove_addons) {
				if (shipany_label_data.lalamove_addons[key].cour_svc_pl == $( 'select[name="pr_shipany_courier_additional_service"]' ).find(":selected").text()) {
					var additional_services = shipany_label_data.lalamove_addons[key]['add-ons']['additional_services']
					for (const key2 in additional_services) {
						$('.lalamove_additional_requirements__field > ul').append(
							'<li><label><input name="lalamove_additional_requirements_' + additional_services[key2]['code'] + '" value="'+ additional_services[key2]['code'] +'" type="checkbox" class="shipany_lalamove_checkbox"> ' + additional_services[key2]['desc'] + '</label></li>'
						)
					}
					var tunnel = shipany_label_data.lalamove_addons[key]['add-ons']['tunnel']
					for (const key3 in tunnel) {
						$('.lalamove_tunnel__field > ul').append(
							'<li><label><input name="lalamove_tunnel_' + tunnel[key3]['code'] + '" value="'+ tunnel[key3]['code'] +'" type="checkbox" class="shipany_lalamove_checkbox"> ' + tunnel[key3]['desc'] + '</label></li>'
						)
					}
				//lalamove change create order button
				$(document).ready(function() { 
					$('.shipany_lalamove_checkbox').change(function() {
						$('#shipany-label-button').text('Query Rate')
						$('#shipany-label-button').attr('id','shipany-label-rate-query-button')
					}); 
				});
				break
				}
			}
			// var courier_service=e.target.value;
			// var data = {
			// 	action:                   'wc_shipment_shipany_update_courier_additional_service',
			// 	order_id:                 woocommerce_admin_meta_boxes.post_id,
			// 	courier_service:          courier_service
			// };
			// $( '#shipment-shipany-label-form' ).block( {
			// 	message: null,
			// 	overlayCSS: {
			// 		background: '#fff',
			// 		opacity: 0.6
			// 	}
			// } );
			// $.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
			// 	//debugger
			// 	$( '#shipment-shipany-label-form' ).unblock();
			// 	$("[name='pr_shipany_couier_service_plan']").empty()
			// 	if ( response.error ) {
			// 		$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">Error: ' + response.error + '</p>');
			// 		$('.pr_shipany_couier_service_plan_field').hide();
			// 	} else {
			// 		$('.pr_shipany_couier_service_plan_field').show()
			// 		for (const plan in response.plans) {
			// 			// $("[name='pr_shipany_couier_service_plan']").append('<option value=' + plan + '>' + response.plans[plan] +'</option>')
			// 			var option = document.createElement("option");
			// 			option.text = response.plans[plan];
			// 			option.value = plan;
			// 			var select = document.getElementById("pr_shipany_couier_service_plan");
			// 			select.appendChild(option);
			// 		}
			// 	}
			// });
		},

		change_courier: function(e) {
			$courier_id=e.target.value;
			const courierNeedServicePlans = ['UPS','lalamove','ZeekDash','SF Express','Zeek2Door', 'Quantium', 'Jumppoint', 'Hongkong Post']
			const targetCourier = $(e.target).find('option:selected')[0].text || ''
			let courierNeedSelectServicePlan = false
			courierNeedServicePlans.forEach(elem => {
				if (elem.toUpperCase().indexOf(targetCourier.toUpperCase()) > -1 || targetCourier.toUpperCase().indexOf(elem.toUpperCase()) > -1) {
					courierNeedSelectServicePlan = true
				}
			})
			// lalamove
			if (targetCourier == 'Lalamove'){
				$('.shipany_lalamove_checkbox').parent().parent().remove()
				$('.pr_shipany_courier_additional_service_field').show()
				$("#pr_shipany_courier_additional_service").empty()
				$('.lalamove_tunnel__field').show()
				$('.lalamove_additional_requirements__field').show()
				var optionsAsStringAdd = "";

				for (var addon in shipany_label_data.lalamove_addons_name_key_pair) {
					optionsAsStringAdd +='<option value ="' + addon + '">' + addon + '</option>';
				}

				$( 'select[name="pr_shipany_courier_additional_service"]' ).append( optionsAsStringAdd );

				for (const key in shipany_label_data.lalamove_addons) {
					if (shipany_label_data.lalamove_addons[key].cour_svc_pl == $( 'select[name="pr_shipany_courier_additional_service"]' ).find(":selected").text()) {
						var additional_services = shipany_label_data.lalamove_addons[key]['add-ons']['additional_services']
						for (const key2 in additional_services) {
							$('.lalamove_additional_requirements__field > ul').append(
								'<li><label><input name="lalamove_additional_requirements_' + additional_services[key2]['code'] + '" value="'+ additional_services[key2]['code'] +'" type="checkbox" class="shipany_lalamove_checkbox"> ' + additional_services[key2]['desc'] + '</label></li>'
							)
						}
						var tunnel = shipany_label_data.lalamove_addons[key]['add-ons']['tunnel']
						for (const key3 in tunnel) {
							$('.lalamove_tunnel__field > ul').append(
								'<li><label><input name="lalamove_tunnel_' + tunnel[key3]['code'] + '" value="'+ tunnel[key3]['code'] +'" type="checkbox" class="shipany_lalamove_checkbox"> ' + tunnel[key3]['desc'] + '</label></li>'
							)
						}
					//lalamove change create order button
					$(document).ready(function() { 
						$('.shipany_lalamove_checkbox').change(function() {
							$('#shipany-label-button').text('Query Rate')
							$('#shipany-label-button').attr('id','shipany-label-rate-query-button')
						}); 
					});
					break
					}
				}
			} else if (targetCourier == 'ZeekDash') {
				$('.shipany_lalamove_checkbox').parent().parent().remove()
				$('.pr_shipany_courier_additional_service_field').hide()
				$('.lalamove_tunnel__field').hide()
				$('.lalamove_additional_requirements__field').show()

				for (const key in shipany_label_data.zeekDash_addons) {

						var additional_services = shipany_label_data.zeekDash_addons[key]['add-ons']['additional_services']
						for (const key2 in additional_services) {
							$('.lalamove_additional_requirements__field > ul').append(
								'<li><label><input name="lalamove_additional_requirements_' + additional_services[key2]['code'] + '" value="'+ additional_services[key2]['code'] +'" type="checkbox" class="shipany_lalamove_checkbox"> ' + additional_services[key2]['desc'] + '</label></li>'
							)
						}
					//lalamove change create order button
					$(document).ready(function() { 
						$('.shipany_lalamove_checkbox').change(function() {
							$('#shipany-label-button').text('Query Rate')
							$('#shipany-label-button').attr('id','shipany-label-rate-query-button')
						}); 
					});
					break
					
				}
			} else {
				$('.shipany_lalamove_checkbox').parent().parent().remove()
				$('.pr_shipany_courier_additional_service_field').hide()
				$('.lalamove_tunnel__field').hide()
				$('.lalamove_additional_requirements__field').hide()
			}

			// show paid by rec field in order details page
			$('#pr_shipany_paid_by_rec').prop('checked', false);
			if (targetCourier == 'SF Express') {
				$('.pr_shipany_paid_by_rec_field').show()
			} else {
				$('#pr_shipany_paid_by_rec').prop( "checked", false )
				$('.pr_shipany_paid_by_rec_field').hide()
			}
			
			if (['c6e80140-a11f-4662-8b74-7dbc50275ce2','f403ee94-e84b-4574-b340-e734663cdb39','7b3b5503-6938-4657-acab-2ff31c3a3f45','2ba434b5-fa1d-4541-bc43-3805f8f3a26d','1d22bb21-da34-4a3c-97ed-60e5e575a4e5','1bbf947d-8f9d-47d8-a706-a7ce4a9ddf52','c74daf26-182a-4889-924b-93a5aaf06e19'].includes($courier_id)){
				$("#pr_shipany_storage_type").empty()
				var optionsAsString = "";
				optionsAsString +='<option value ="Air Conditioned">Air Conditioned (17°C - 22°C)</option>';
				optionsAsString +='<option value ="Chilled">Chilled (0°C - 4°C)</option>';
				optionsAsString +='<option value ="Frozen">Frozen (-18°C - -15°C)</option>';
				$( 'select[name="pr_shipany_storage_type"]' ).append( optionsAsString );
			}

			else if (['79703b17-4198-472b-80b3-e195cd8250a4','6e494226-985a-4ca0-bb0b-d5994a051932','60a9855e-9983-4e1c-ad5f-373d3e25a0f1'].includes($courier_id)){
				$("#pr_shipany_storage_type").empty()
				var optionsAsString = "";
				optionsAsString +='<option value ="Normal">Normal</option>';
				optionsAsString +='<option value ="Chilled">Chilled (0°C - 4°C)</option>';
				optionsAsString +='<option value ="Frozen">Frozen (-18°C - -15°C)</option>';
				$( 'select[name="pr_shipany_storage_type"]' ).append( optionsAsString );
			}

			else {
				$("#pr_shipany_storage_type").empty()
				var optionsAsString = "";
				optionsAsString +='<option value ="">Normal</option>';
				$( 'select[name="pr_shipany_storage_type"]' ).append( optionsAsString );
			}

			// Remove error msg if exist
			$( '#shipment-shipany-label-form .wc_shipany_error' ).remove()

			$("[name='pr_shipany_couier_service_plan']").empty()
			if (courierNeedSelectServicePlan) {
				$( '#shipment-shipany-label-form' ).block( {
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				} );
				var selected_additional_service = $( 'select[name="pr_shipany_courier_additional_service"]' ).find(":selected").text();
				var selected_storage_type = $( 'select[name="pr_shipany_storage_type"]' ).find(":selected").val();
				$('.pr_shipany_couier_service_plan_field').show()
				var data = {
					action:                   'wc_shipment_shipany_update_service_plan',
					order_id:                 woocommerce_admin_meta_boxes.post_id,
					targetCourier:            targetCourier,
					selectedAdditionalService: selected_additional_service,
					cour_uid:	$courier_id,
					selectedStorageType: selected_storage_type
				};

				$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
					$( '#shipment-shipany-label-form' ).unblock();
					if ( response.error ) {
						$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">Error: ' + response.error + '</p>');
						$('.pr_shipany_couier_service_plan_field').hide();
					} else {
						for (const plan in response.plans) {
							// $("[name='pr_shipany_couier_service_plan']").append('<option value=' + plan + '>' + response.plans[plan] +'</option>')
							var option = document.createElement("option");
							option.text = response.plans[plan];
							option.value = plan;
							var select = document.getElementById("pr_shipany_couier_service_plan");
							select.appendChild(option);
						}

						// enable back the courier service plan selector if it is disabled in initial time
						if ($("[name='pr_shipany_couier_service_plan']").has('option').length < 1 ) {
							$("[name='pr_shipany_couier_service_plan']").prop( "disabled", true )
						}else{
							$("[name='pr_shipany_couier_service_plan']").prop( "disabled", false )
						}
						if (targetCourier == 'SF Express' && $("[name='pr_shipany_couier_service_plan']").text().indexOf('International') >=0 ){
							// TODO: pass the full courier list and check the field
							$('.pr_shipany_paid_by_rec_field').hide()
						}
					}
				});
			} else {
				// normal courier
				$('.pr_shipany_couier_service_plan_field').hide()
			}

		},
		
		// Extract the entries for the given package attribute
		get_package_array: function($form, attrib) {
			var $element = $form.find('input[name="pr_shipany_packages_'+attrib+'[]"]');
			var result = [];

			if ('undefined' !== typeof $element && $element) {
				result = $element.map(function() {
					return $(this).val();
				}).get();
			}

			return result;
		},

		// Extract all user inputted packages. Retrieving all available
		// package info or attributes.
		get_packages_for_saving: function($form, required) {
			var total = $form.find('select#pr_shipany_total_packages').val();
			var packages = [],
				error = false,
				invalid_number = false;

			var numbers = this.get_package_array($form, 'number');
			var weights = this.get_package_array($form, 'weight');
			var lengths = this.get_package_array($form, 'length');
			var widths = this.get_package_array($form, 'width');
			var heights = this.get_package_array($form, 'height');

			for (var i=0; i<parseInt(total); i++) {
				if (required) {
					if (!numbers[i].length || !weights[i].length || !lengths[i].length || !widths[i].length || !heights[i].length) {
						error = true;
						break;
					} else {
						if (!$.isNumeric(weights[i]) || !$.isNumeric(lengths[i]) || !$.isNumeric(widths[i]) || !$.isNumeric(heights[i])) {
							invalid_number = true;
							break;
						}
					}
				}

				packages.push({
					number: numbers[i], weight: weights[i], length: lengths[i], width: widths[i], height: heights[i]
				});
			}

			if (invalid_number) {
				return 'invalid_number';
			}

			return (!error) ? packages : false;
		},

		// Process the cloning (adding) and removing of package entries based
		// on the total packages selected by the user.
		process_package_action: function() {
			var old_value = $(this).data('current');
			var value = $(this).val();
			var $container = $('.total_packages_container');

			if (parseInt(old_value) < parseInt(value)) {
				var new_value = parseInt(value) - parseInt(old_value);
				var $clone, $package_number, new_number;

				for (var i=0; i<new_value; i++) {
					$clone = $container.find('.package_item:last').clone();
					$package_number = parseInt($clone.find('.package_number > input').data('sequence'));
					new_number = parseInt($package_number)+1;

					// We'll update both the cache and DOM to make sure that we get
					// the expected behaviour when pulling the sequence number for processing.
					$clone.find('.package_number > input').attr('data-sequence', new_number); // this updates the DOM
					$clone.find('.package_number > input').data('sequence', new_number); // this updates the jquery cache

					$clone.find('.package_number > input').val(new_number);
					$clone.find('.package_item_field.clearable > input').val('');
				$container.append($clone);
				}
			} else {
				$container.find('.package_item').slice(value).remove();
			}
			
			$(this).data('current', value);
		},
	
		validate_product_return: function () {
			var selected_product = $( '#pr_shipany_product' ).val();

			if( selected_product != 'V01PAK' && selected_product != 'V01PRIO' ) {
				$('#pr_shipany_return_address_enabled').prop('checked', false).trigger('change');
				$('#pr_shipany_return_address_enabled').prop('disabled', 'disabled');
			} else {
				$('#pr_shipany_return_address_enabled').removeAttr('disabled');
			}

		},

		show_hide_return: function () {
			var is_checked = $( '#pr_shipany_return_address_enabled' ).prop('checked');

			$( '#shipment-shipany-label-form' ).children().each( function () {
				
				// If class exists, and is not 'pr_shipany_return_address_enabled' but is 'pr_shipany_return_' field
			    if( ( $(this).attr("class") ) &&
			    	( $(this).attr("class").indexOf('pr_shipany_return_address_enabled') == -1 ) &&
			    	( $(this).attr("class").indexOf('pr_shipany_return') >= 0 ) 
			    ) {
			    	
			    	if ( is_checked ) {
			    		$(this).show();
			    	} else {
			    		$(this).hide();
			    	}
			    }
			});
		},
	
		show_hide_ident: function () {
			var is_checked = $( '#pr_shipany_identcheck' ).prop('checked');

			$( '#shipment-shipany-label-form' ).children().each( function () {
				
				// If class exists, and is not 'pr_shipany_return_address_enabled' but is 'pr_shipany_return_' field
			    if( ( $(this).attr("class") ) &&
			    	( $(this).attr("class").indexOf('pr_shipany_identcheck_field ') == -1 ) &&
			    	( $(this).attr("class").indexOf('pr_shipany_identcheck') >= 0 ) 
			    ) {
			    	
			    	if ( is_checked ) {
			    		$(this).show();
			    	} else {
			    		$(this).hide();
			    	}
			    }
			});
		},

		show_hide_routing: function () {
			var is_checked = $( '#pr_shipany_routing' ).prop('checked');

			$( '#shipment-shipany-label-form' ).children().each( function () {

				// If class exists, and is not 'pr_shipany_return_address_enabled' but is 'pr_shipany_return_' field
			    if( ( $(this).attr("class") ) &&
			    	( $(this).attr("class").indexOf('pr_shipany_routing_field ') == -1 ) &&
			    	( $(this).attr("class").indexOf('pr_shipany_routing') >= 0 )
			    ) {

			    	if ( is_checked ) {
			    		$(this).show();
			    	} else {
			    		$(this).hide();
			    	}
			    }
			});
		},

		show_hide_packages: function () {
			// Only relevant for Paket so check if exists
			if ( ! $( '#pr_shipany_multi_packages_enabled' ).length ) {
			    return;
			}

			var is_checked = $( '#pr_shipany_multi_packages_enabled' ).prop('checked');

			if ( is_checked ) {
	    		$('#pr_shipany_weight').prop('disabled', 'disabled');
			} else {
				$('#pr_shipany_weight').removeAttr('disabled');
	    	}

			$( '#shipment-shipany-label-form' ).children().each( function () {
				// If class exists, and is not 'pr_shipany_multi_packages_enabled' but is 'pr_shipany_total_packages' or 'total_packages_container' fields
			    if( ( $(this).attr("class") ) &&
			    	( $(this).attr("class").indexOf('pr_shipany_multi_packages_enabled') == -1 ) &&
			    	( ( $(this).attr("class").indexOf('pr_shipany_total_packages') >= 0 ) || 
			    	( $(this).attr("class").indexOf('total_packages_container') >= 0 ) )			    	
			    ) {
			    	
			    	if ( is_checked ) {
			    		$(this).show();
			    	} else {
			    		$(this).hide();
			    	}
			    }
			});
		},

		pickup_fn: function (e) {
			document.getElementById('shipany-pickup-button').innerHTML='Loading...';
			document.getElementById('shipany-pickup-button').style.cursor = 'default';
			document.getElementById('shipany-pickup-button').style.pointerEvents = 'none';
			e.preventDefault();
			var data = {
				action:                   'wc_send_pick_up_request',
				order_id:                 woocommerce_admin_meta_boxes.post_id,
			};
			var $form = $('#shipment-shipany-label-form');
			data[ 'lalamove_tunnel' ] = []
			data[ 'lalamove_additional' ] = []
			data['courier'] = $( '#pr_shipany_product' ).find(":selected").text()
			data['courier_uid'] = $( '#pr_shipany_product' ).find(":selected").val()
			$form.each(function(i, div) {

			    $(div).find('input').each(function(j, element){

			        if( $(element).attr('type') == 'checkbox' ) {
			        	if ( $(element).prop('checked') ) {
							if ( $(element).attr('name').includes('lalamove_tunnel') ) {
								data[ 'lalamove_tunnel' ].push($(element).val())
							} else if ( $(element).attr('name').includes('lalamove_additional') ){
								data[ 'lalamove_additional' ].push($(element).val())
							} else {
								data[ $(element).attr('name') ] = 'yes'
							}
			        	} else {
				        	data[ $(element).attr('name') ] = 'no'
			        	}
			        } else {
			        	var eName = $(element).attr('name');
			        	// Do NOT add array inputs here!
			        	if (eName.indexOf("[]") == -1) {
			        		data[ $(element).attr('name') ] = $(element).val();
			        	}
			        }
			    });
	    	});
			if ( $( '#pr_shipany_product' ).find(":selected").text() == 'ZeekDash') {
				$('.shipany-main-dialog-sendpickup').show();
				$('.shipany-dialog > .title').text('Reminder');
				$('.shipany-dialog > .detail').html('For ZeekDash delivery, there can be a surcharge of HKD 75-100 if any of the below applies to the package: <br>- actual weight > 10kg <br>- any of the dimension > 170cm <br>- length + width + height > 220cm <br><br>Additionally, if no lift/elevator is available at the receiver’s address and the courier needs to deliver upstairs, there can be a surcharge of HKD 30/floor (max. 8 floors).');
				$('.shipany-yes-btn').click(function (){
					$('.shipany-main-dialog-sendpickup > .title').text('');
					$('.shipany-main-dialog-sendpickup > .detail').text('');
					$('.shipany-main-dialog-sendpickup').hide();
					$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
						if (response.lab_url) {
							document.getElementById('order_status').innerHTML='Order Status: Pickup Request Received'
							$('#shipment-shipany-label-form').each(function(i, div) {
								$(div).find('button').each(function(j, element){
									$(element).prop('disabled','disabled');
								});
							});
							$( '#shipany-label-print').css('cursor', 'pointer');
							$( '#shipany-label-print').css( 'pointer-events', 'auto' );
							$( '#shipany-label-print').css('opacity', '1');
							$( '#shipany-label-print').removeAttr("disabled");
							$( '#shipany-label-print').attr("href", response.lab_url );
							document.getElementById('shipany-pickup-button').innerHTML='Send Pickup Request';
						}
					});
				})
				$('.shipany-close-btn').click(function (){
					$('#shipany-pickup-button').css('pointer-events','');
					$('#shipany-pickup-button').css('cursor','');
					$('#shipany-pickup-button').text('Send Pickup Request')
				})
				return false
			}
			$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
				$( '#shipment-shipany-label-form .wc_shipany_no_credit_error' ).remove()
				if (response.error_detail) {
					$( '#shipment-shipany-label-form' ).append("<div class='wc_shipany_no_credit_error' style='color: #AA0000'>" + response.error_detail +"</div>");
					document.getElementById('shipany-pickup-button').style.cursor = '';
					document.getElementById('shipany-pickup-button').style.pointerEvents = '';
				} else if (response.error_expired) {
					document.getElementById('shipany-pickup-button').innerHTML='Loading...';
					document.getElementById('shipany-pickup-button').style.cursor = 'default';
					document.getElementById('shipany-pickup-button').style.pointerEvents = 'none';

					var data_sec = {
						action:                   'wc_patch_quot_id',
						order_id:                 woocommerce_admin_meta_boxes.post_id,
						quot_id:                 response.quot_uid,
						shipany_order_id:        response.shipany_order_id
					};

					// No need confirm if the quoted price is less than or equal to the original price
					var shipany_courier_service_str = $('#courier_service_plan').text()
					var shipany_courier_price_value_str = shipany_courier_service_str.split(" ")[shipany_courier_service_str.split(" ").length-1]
					if (response.val <= shipany_courier_price_value_str) {
						$.post( woocommerce_admin_meta_boxes.ajax_url, data_sec, function( response ) {
							if (response.lab_url) {
								document.getElementById('order_status').innerHTML='Order Status: Pickup Request Received'
								$('#shipment-shipany-label-form').each(function(i, div) {
									$(div).find('button').each(function(j, element){
										$(element).prop('disabled','disabled');
									});
								});
								$( '#shipany-label-print').css('cursor', 'pointer');
								$( '#shipany-label-print').css( 'pointer-events', 'auto' );
								$( '#shipany-label-print').css('opacity', '1');
								$( '#shipany-label-print').removeAttr("disabled");
								$( '#shipany-label-print').attr("href", response.lab_url );
								document.getElementById('shipany-pickup-button').innerHTML='Send Pickup Request';
							}
						});
					} else {
						$('.shipany-main-dialog-sendpickup').show();
						$('.shipany-dialog > .title').text('The quotation has expired');
						$('.shipany-dialog > .detail').text('The new quotation is HKD ' + response.val +', continue to send pickup request?');
						$('.shipany-yes-btn').click(function (){
							$('.shipany-main-dialog-sendpickup > .title').text('');
							$('.shipany-main-dialog-sendpickup > .detail').text('');
							$('.shipany-main-dialog-sendpickup').hide();

							$.post( woocommerce_admin_meta_boxes.ajax_url, data_sec, function( response ) {
								if (response.lab_url) {
									document.getElementById('order_status').innerHTML='Order Status: Pickup Request Received'
									$('#shipment-shipany-label-form').each(function(i, div) {
										$(div).find('button').each(function(j, element){
											$(element).prop('disabled','disabled');
										});
									});
									$( '#shipany-label-print').css('cursor', 'pointer');
									$( '#shipany-label-print').css( 'pointer-events', 'auto' );
									$( '#shipany-label-print').css('opacity', '1');
									$( '#shipany-label-print').removeAttr("disabled");
									$( '#shipany-label-print').attr("href", response.lab_url );
									document.getElementById('shipany-pickup-button').innerHTML='Send Pickup Request';
								}
							});
						})
						$('.shipany-close-btn').click(function (){
							$('#shipany-pickup-button').css('pointer-events','');
							$('#shipany-pickup-button').css('cursor','');
							$('#shipany-pickup-button').text('Send Pickup Request')
						})
					}

				} else {
					$(function(){
						$('#shipment-shipany-label-form').each(function(i, div) {
							$(div).find('button').each(function(j, element){
								$(element).prop('disabled','disabled');
							});
						});
						$( '#shipany-label-print').css('cursor', 'pointer');
						$( '#shipany-label-print').css( 'pointer-events', 'auto' );
						$( '#shipany-label-print').css('opacity', '1');
						$( '#shipany-label-print').removeAttr("disabled");
						// zeek2door update url 
						if (response.lab_url) {
							$( '#shipany-label-print').attr("href", response.lab_url );
						}
						if (response.dt_beg_str && response.dt_end_str) {
							$( '#shipment-shipany-label-form' ).append("<div style='color: #52c41a'>The courier will pick up the package between " + response.dt_beg_str + " to " + response.dt_end_str + "</div>");
						}
					});
					if (response.cur_stat) {
						document.getElementById('order_status').innerHTML = 'Order Status: ' + response.cur_stat
					}
				}
				if (!response.error_expired) document.getElementById('shipany-pickup-button').innerHTML='Send Pickup Request';
			});

		},


		save_shipany_label: function () {
					
			// loop through inputs within id 'shipment-shipany-label-form'
			var data = {
				action:                   'wc_shipment_shipany_gen_label',
				order_id:                 woocommerce_admin_meta_boxes.post_id,
			};
			
			// In case an error has occured.
			//debugger
			var abort = false;
			var $form = $('#shipment-shipany-label-form');
			data[ 'lalamove_tunnel' ] = []
			data[ 'lalamove_additional' ] = []
			$form.each(function(i, div) {

			    $(div).find('input').each(function(j, element){

			        if( $(element).attr('type') == 'checkbox' ) {
			        	if ( $(element).prop('checked') ) {
							if ( $(element).attr('name').includes('lalamove_tunnel') ) {
								data[ 'lalamove_tunnel' ].push($(element).val())
							} else if ( $(element).attr('name').includes('lalamove_additional') ){
								data[ 'lalamove_additional' ].push($(element).val())
							} else {
								data[ $(element).attr('name') ] = 'yes'
							}
			        	} else {
				        	data[ $(element).attr('name') ] = 'no'
			        	}
			        } else {
			        	var eName = $(element).attr('name');
			        	// Do NOT add array inputs here!
			        	if (eName.indexOf("[]") == -1) {
			        		data[ $(element).attr('name') ] = $(element).val();
			        	}
			        }
			    });

			    $(div).find('select').each(function(j, element){
		        	data[ $(element).attr('name') ] = $(element).val();
			    });

			    $(div).find('textarea').each(function(j, element){
		        	data[ $(element).attr('name') ] = $(element).val();
			    });

				$(div).find('textarea').each(function(j, element){
		        	data[ $(element).attr('name') ] = $(element).val();
			    });
	    	});

			// get user selected storage type
			var selected_storage_type = $( 'select[name="pr_shipany_storage_type"]' ).find(":selected").val();
			if (selected_storage_type){
				data['pr_shipany_storage_type'] = selected_storage_type;
			}
			if (data['pr_shipany_courier_additional_service'] != undefined && data['pr_shipany_courier_additional_service'].includes('DOOR-TO-DOOR')) {
				$('.shipany-main-dialog-sendpickup').show();
				$('.shipany-dialog > .title').text('Reminder');
				$('.shipany-dialog > .detail').text('The selected courier service DOES NOT support door-to-door delivery. Proceed to create the order?')
				$('.shipany-yes-btn').click(function (){
					$('.shipany-main-dialog-sendpickup').hide();
					if (!abort) {
						// Remove any errors from last attempt to create label
						$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();
		
						$( '#shipment-shipany-label-form' ).block( {
							message: null,
							overlayCSS: {
								background: '#fff',
								opacity: 0.6
							}
						} );
						$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
							$( '#shipment-shipany-label-form' ).unblock();
							if ( response.error ) {
								$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">' + response.error + '</p>');
							} else if ( response == "0" ) {
								$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">' + 'Missing order details' + '</p>');
							} else if (response.insufficient_balance || response.ext_order_not_created) {
								$(function(){ 
									$('#shipment-shipany-label-form').each(function(i, div) {
										$(div).find('input').each(function(j, element){
											$(element).prop('disabled', 'disabled');
										});
										$(div).find('select').each(function(j, element){
											$(element).prop('disabled', 'disabled');
										});
										$(div).find('textarea').each(function(j, element){
											$(element).prop('disabled','disabled');
										});
									});
								});
								$( '#shipany-label-button').remove();
								$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();
								$( '#shipment-shipany-label-form' ).prepend('<div id="courier_service_plan" class="tooltip" data-title="The quoted price is just an estimated value.">Courier Service Plan: ' + response.courier_service_plan + '</div>')
								$( '#shipment-shipany-label-form' ).prepend('<p>')
								$( '#shipment-shipany-label-form' ).prepend('<div id="order_status">Order Status: Order Drafted</div>')
								if (response.ext_order_not_created) {
									$( '#shipment-shipany-label-form' ).append('<button id="shipany-label-button-recreate" class="button button-primary button-save-form tooltip" data-title="Since error occur during create ShipAny order, the order falls to draft state">Create ShipAny Order (From Draft)</button>');
									$( '#shipment-shipany-label-form' ).append("<div class='wc_shipany_no_credit_error' style='color: #AA0000'>" + response.response_details + "</div>");
								} else {
									$( '#shipment-shipany-label-form' ).append('<button id="shipany-label-button-recreate" class="button button-primary button-save-form tooltip" data-title="Since ShipAny account is out of credit, the order falls to draft state">Create ShipAny Order (From Draft)</button>');
									$( '#shipment-shipany-label-form' ).append("<div class='wc_shipany_no_credit_error' style='color: #AA0000'>Please top up account and create order again.</div>");
								}
							} else {
								// Disable all form items
								$(function(){ 
									$('#shipment-shipany-label-form').each(function(i, div) {
										$(div).find('input').each(function(j, element){
										   $(element).prop('disabled', 'disabled');
										});
										$(div).find('select').each(function(j, element){
											$(element).prop('disabled', 'disabled');
										});
										$(div).find('textarea').each(function(j, element){
											$(element).prop('disabled','disabled');
										});
									});
								});
								$( '#shipment-shipany-label-form .wc_shipany_no_credit_error' ).remove();
								$( '#shipment-shipany-label-form' ).prepend('<div id="courier_service_plan" class="tooltip" data-title="The quoted price is just an estimated value.">Courier Service Plan: ' + response.courier_service_plan + '</div>')
								$( '#shipment-shipany-label-form' ).prepend('<p>')
								$( '#shipment-shipany-label-form' ).prepend('<div id="order_status">Order Status: Order Created</div>')
								$( '#shipany-label-button').remove();
								$( '#shipment-shipany-label-form' ).append(shipany_label_data.print_button);
								$( '#shipment-shipany-label-form' ).append('<p>');
								$( '#shipment-shipany-label-form' ).append(shipany_label_data.pickup_request);
								$( '#shipany-label-print').attr("href", response.label_url ); // update new url
		
								if (response.commercial_invoice_url != '' && response.commercial_invoice_url != null) {
									$( '#shipment-shipany-label-form' ).append('<p>');
									$( '#shipment-shipany-label-form' ).append(shipany_label_data.inv_print_button);
									$( '#shipany-invoice-print').attr("href", response.commercial_invoice_url );
								}
		
								// $( '#shipment-shipany-label-form' ).append(shipany_label_data.delete_label);
								if (response.get_file_size == 0) {
									$( '#shipany-label-print').css('cursor', 'default');
									$( '#shipany-label-print').css( 'pointer-events', 'none' );
									$( '#shipany-label-print').css('opacity', '0.5');
								}
								if (response.insufficient_balance) {
									document.getElementById('order_status').innerHTML='Order Status: Order Drafted'
									$( '#shipany-pickup-button').css('cursor', 'default');
									$( '#shipany-pickup-button').css( 'pointer-events', 'none' );
									$( '#shipany-pickup-button').css('opacity', '0.5');
									$( '#shipment-shipany-label-form' ).append("<div style='color: #AA0000'>Please top up account and create order again.</div>");
								} else if (response.ext_order_not_created == 'x') {
									document.getElementById('order_status').innerHTML='Order Status: Order Drafted'
									$( '#shipany-pickup-button').css('cursor', 'default');
									$( '#shipany-pickup-button').css( 'pointer-events', 'none' );
									$( '#shipany-pickup-button').css('opacity', '0.5');
									$( '#shipment-shipany-label-form' ).append("<div style='color: #AA0000'>" + response.response_details + "</div>");
								} else if (response.asn_id) {
									$( '#shipany-pickup-button').css('cursor', 'default');
									$( '#shipany-pickup-button').css( 'pointer-events', 'none' );
									$( '#shipany-pickup-button').css('opacity', '0.5');				
								}
		
								$( document ).trigger( 'pr_shipany_saved_label' );
							}
							if( response.tracking_note ) {
		
								$( '#woocommerce-order-notes' ).block({
									message: null,
									overlayCSS: {
										background: '#fff',
										opacity: 0.6
									}
								});
								
								var data = {
									action:                   'woocommerce_add_order_note',
									post_id:                  woocommerce_admin_meta_boxes.post_id,
									note_type: 				  response.tracking_note_type,
									note:					  response.tracking_note,
									security:                 woocommerce_admin_meta_boxes.add_order_note_nonce
								};
		
								$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response_note ) {
									// alert(response_note);
									$( 'ul.order_notes' ).prepend( response_note );
									$( '#woocommerce-order-notes' ).unblock();
									$( '#add_order_note' ).val( '' );
								});							
							}
						});
					}	
				})
				return false;
			}
			if (!abort) {
				// Remove any errors from last attempt to create label
				$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();

				$( '#shipment-shipany-label-form' ).block( {
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				} );
				$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
					$( '#shipment-shipany-label-form' ).unblock();
					if ( response.error ) {
						$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">' + response.error + '</p>');
					} else if ( response == "0" ) {
						$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">' + 'Missing order details' + '</p>');
					} else if (response.insufficient_balance || response.ext_order_not_created) {
						$(function(){ 
							$('#shipment-shipany-label-form').each(function(i, div) {
								$(div).find('input').each(function(j, element){
									$(element).prop('disabled', 'disabled');
								});
								$(div).find('select').each(function(j, element){
									$(element).prop('disabled', 'disabled');
								});
								$(div).find('textarea').each(function(j, element){
									$(element).prop('disabled','disabled');
								});
							});
						});
						$( '#shipany-label-button').remove();
						$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();
						$( '#shipment-shipany-label-form' ).prepend('<div id="courier_service_plan" class="tooltip" data-title="The quoted price is just an estimated value.">Courier Service Plan: ' + response.courier_service_plan + '</div>')
						$( '#shipment-shipany-label-form' ).prepend('<p>')
						$( '#shipment-shipany-label-form' ).prepend('<div id="order_status">Order Status: Order Drafted</div>')
						if (response.ext_order_not_created) {
							$( '#shipment-shipany-label-form' ).append('<button id="shipany-label-button-recreate" class="button button-primary button-save-form tooltip" data-title="Since error occur during create ShipAny order, the order falls to draft state">Create ShipAny Order (From Draft)</button>');
							$( '#shipment-shipany-label-form' ).append("<div class='wc_shipany_no_credit_error' style='color: #AA0000'>" + response.response_details + "</div>");
						} else {
							$( '#shipment-shipany-label-form' ).append('<button id="shipany-label-button-recreate" class="button button-primary button-save-form tooltip" data-title="Since ShipAny account is out of credit, the order falls to draft state">Create ShipAny Order (From Draft)</button>');
							$( '#shipment-shipany-label-form' ).append("<div class='wc_shipany_no_credit_error' style='color: #AA0000'>Please top up account and create order again.</div>");
						}
					} else {
						// Disable all form items
						$(function(){ 
							$('#shipment-shipany-label-form').each(function(i, div) {
							    $(div).find('input').each(function(j, element){
							       $(element).prop('disabled', 'disabled');
							    });
							    $(div).find('select').each(function(j, element){
							        $(element).prop('disabled', 'disabled');
							    });
							    $(div).find('textarea').each(function(j, element){
							        $(element).prop('disabled','disabled');
							    });
					    	});
					    });
						$( '#shipment-shipany-label-form .wc_shipany_no_credit_error' ).remove();
						$( '#shipment-shipany-label-form' ).prepend('<div id="courier_service_plan" class="tooltip" data-title="The quoted price is just an estimated value.">Courier Service Plan: ' + response.courier_service_plan + '</div>')
						$( '#shipment-shipany-label-form' ).prepend('<p>')
						$( '#shipment-shipany-label-form' ).prepend('<div id="order_status">Order Status: Order Created</div>')
						$( '#shipany-label-button').remove();
						$( '#shipment-shipany-label-form' ).append(shipany_label_data.print_button);
						$( '#shipment-shipany-label-form' ).append('<p>');
						$( '#shipment-shipany-label-form' ).append(shipany_label_data.pickup_request);
						$( '#shipany-label-print').attr("href", response.label_url_s3 ); // update new url

						if (response.commercial_invoice_url != '' && response.commercial_invoice_url != null) {
							$( '#shipment-shipany-label-form' ).append('<p>');
							$( '#shipment-shipany-label-form' ).append(shipany_label_data.inv_print_button);
							$( '#shipany-invoice-print').attr("href", response.commercial_invoice_url );
						}

						// $( '#shipment-shipany-label-form' ).append(shipany_label_data.delete_label);
						if (response.get_file_size == 0) {
							$( '#shipany-label-print').css('cursor', 'default');
							$( '#shipany-label-print').css( 'pointer-events', 'none' );
							$( '#shipany-label-print').css('opacity', '0.5');
						}
						if (response.insufficient_balance) {
							document.getElementById('order_status').innerHTML='Order Status: Order Drafted'
							$( '#shipany-pickup-button').css('cursor', 'default');
							$( '#shipany-pickup-button').css( 'pointer-events', 'none' );
							$( '#shipany-pickup-button').css('opacity', '0.5');
							$( '#shipment-shipany-label-form' ).append("<div style='color: #AA0000'>Please top up account and create order again.</div>");
						} else if (response.ext_order_not_created == 'x') {
							document.getElementById('order_status').innerHTML='Order Status: Order Drafted'
							$( '#shipany-pickup-button').css('cursor', 'default');
							$( '#shipany-pickup-button').css( 'pointer-events', 'none' );
							$( '#shipany-pickup-button').css('opacity', '0.5');
							$( '#shipment-shipany-label-form' ).append("<div style='color: #AA0000'>" + response.response_details + "</div>");
						} else if (response.asn_id) {
							$( '#shipany-pickup-button').css('cursor', 'default');
							$( '#shipany-pickup-button').css( 'pointer-events', 'none' );
							$( '#shipany-pickup-button').css('opacity', '0.5');				
						}

						$( document ).trigger( 'pr_shipany_saved_label' );
					}
					if( response.tracking_note ) {

						$( '#woocommerce-order-notes' ).block({
							message: null,
							overlayCSS: {
								background: '#fff',
								opacity: 0.6
							}
						});
						
						var data = {
							action:                   'woocommerce_add_order_note',
							post_id:                  woocommerce_admin_meta_boxes.post_id,
							note_type: 				  response.tracking_note_type,
							note:					  response.tracking_note,
							security:                 woocommerce_admin_meta_boxes.add_order_note_nonce
						};

						$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response_note ) {
							// alert(response_note);
							$( 'ul.order_notes' ).prepend( response_note );
							$( '#woocommerce-order-notes' ).unblock();
							$( '#add_order_note' ).val( '' );
						});							
					}
				});
			}

			return false;
		},

		save_shipany_label_recreate: function () {
					
			// loop through inputs within id 'shipment-shipany-label-form'
			var data = {
				action:                   'wc_shipment_shipany_gen_label_recreate',
				order_id:                 woocommerce_admin_meta_boxes.post_id,
				shipany_order_id:			shipany_label_data.label_tracking_info == undefined? '': shipany_label_data.label_tracking_info.shipment_id,
				mch_id:			shipany_label_data.mch_uid == undefined? '': shipany_label_data.mch_uid
			};

			// Remove any errors from last attempt to create label
			$( '#shipment-shipany-label-form .wc_shipany_no_credit_error' ).remove();

			$( '#shipment-shipany-label-form' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			} );

			// get user selected storage type
			var selected_storage_type = $( 'select[name="pr_shipany_storage_type"]' ).find(":selected").val();
			if (selected_storage_type){
				data['selected_storage_type'] = selected_storage_type;
			}

			$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
				$( '#shipment-shipany-label-form' ).unblock();
				if ( response.error == 'error' ) {
					$(function(){ 
						$('#shipment-shipany-label-form').each(function(i, div) {
							$(div).find('input').each(function(j, element){
								$(element).prop('disabled', 'disabled');
							});
							$(div).find('select').each(function(j, element){
								$(element).prop('disabled', 'disabled');
							});
							$(div).find('textarea').each(function(j, element){
								$(element).prop('disabled','disabled');
							});
						});
					});
					// $( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">' + response.error + '</p>');
					$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();
					$( '#shipment-shipany-label-form' ).append("<div class='wc_shipany_no_credit_error' style='color: #AA0000'>Please top up account and create order again.</div>");
				} else if ( response.error_detail){
					$(function(){ 
						$('#shipment-shipany-label-form').each(function(i, div) {
							$(div).find('input').each(function(j, element){
								$(element).prop('disabled', 'disabled');
							});
							$(div).find('select').each(function(j, element){
								$(element).prop('disabled', 'disabled');
							});
							$(div).find('textarea').each(function(j, element){
								$(element).prop('disabled','disabled');
							});
						});
					});
					// $( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">' + response.error + '</p>');
					$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();
					$( '#shipment-shipany-label-form' ).append("<div class='wc_shipany_no_credit_error' style='color: #AA0000'>" + response.error_detail + "</div>");
				} else if ( response.error !='' && response.error !=undefined){
					$(function(){ 
						$('#shipment-shipany-label-form').each(function(i, div) {
							$(div).find('input').each(function(j, element){
								$(element).prop('disabled', 'disabled');
							});
							$(div).find('select').each(function(j, element){
								$(element).prop('disabled', 'disabled');
							});
							$(div).find('textarea').each(function(j, element){
								$(element).prop('disabled','disabled');
							});
						});
					});
					// $( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">' + response.error + '</p>');
					$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();
					$( '#shipment-shipany-label-form' ).append("<div class='wc_shipany_no_credit_error' style='color: #AA0000'>" + response.error + "</div>");
				} else {
					$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();
					// Disable all form items
					$(function(){ 
						$('#shipment-shipany-label-form').each(function(i, div) {
							$(div).find('input').each(function(j, element){
								$(element).prop('disabled', 'disabled');
							});
							$(div).find('select').each(function(j, element){
								$(element).prop('disabled', 'disabled');
							});
							$(div).find('textarea').each(function(j, element){
								$(element).prop('disabled','disabled');
							});
						});
					});

					$( '#shipany-label-button-recreate').remove();
					$( '#shipment-shipany-label-form' ).append(shipany_label_data.print_button);
					$( '#shipment-shipany-label-form' ).append('<p>');
					$( '#shipment-shipany-label-form' ).append(shipany_label_data.pickup_request);
					$( '#shipany-label-print').attr("href", response.label_url ); // update new url
					$( '#shipany-label-print').css( 'pointer-events', 'auto' );
					$( '#shipany-label-print').removeAttr('disabled');
					if (response.commercial_invoice_url != '' && response.commercial_invoice_url != null) {
						$( '#shipment-shipany-label-form' ).append('<p>');
						$( '#shipment-shipany-label-form' ).append(shipany_label_data.inv_print_button);
						$( '#shipany-invoice-print').attr("href", response.commercial_invoice_url );
					}

					document.getElementById('order_status').innerHTML='Order Status: Order Created'

					if (response.courier_id == '85cc2f44-8508-4b46-b49d-28b7b4c65da4') {
						$( '#shipany-label-print').css('cursor', 'default');
						$( '#shipany-label-print').css( 'pointer-events', 'none' );
						$( '#shipany-label-print').css('opacity', '0.5');
					}			
					if( response.tracking_note ) {

						$( '#woocommerce-order-notes' ).block({
							message: null,
							overlayCSS: {
								background: '#fff',
								opacity: 0.6
							}
						});
						
						var data = {
							action:                   'woocommerce_add_order_note',
							post_id:                  woocommerce_admin_meta_boxes.post_id,
							note_type: 				  response.tracking_note_type,
							note:					  response.tracking_note,
							security:                 woocommerce_admin_meta_boxes.add_order_note_nonce
						};

						$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response_note ) {
							// alert(response_note);
							$( 'ul.order_notes' ).prepend( response_note );
							$( '#woocommerce-order-notes' ).unblock();
							$( '#add_order_note' ).val( '' );
						});							
					}

					$( document ).trigger( 'pr_shipany_saved_label' );
				}
			});
			

			return false;
		},

		delete_shipany_label: function () {

			$( '#shipment-shipany-label-form .wc_shipany_error' ).remove();

			$( '#shipment-shipany-label-form' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			} );
			
			var data = {
				action:                   'wc_shipment_shipany_delete_label',
				order_id:                 woocommerce_admin_meta_boxes.post_id,
				pr_shipany_label_nonce:       $( '#pr_shipany_label_nonce' ).val()
			};
			
			$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
				$( '#shipment-shipany-label-form' ).unblock();
				if ( response.error ) {
					$( '#shipment-shipany-label-form' ).append('<p class="wc_shipany_error">Error: ' + response.error + '</p>');
				} else {

					$( '#shipment-shipany-label-form .wc_shipany_delete' ).remove();
					// Enable all form items
					$(function(){ 
						$('#shipment-shipany-label-form').each(function(i, div) {

						    $(div).find('input').each(function(j, element){
						       $(element).removeAttr('disabled');
						    });

						    $(div).find('select').each(function(j, element){
						        $(element).removeAttr('disabled');
						    });

						    $(div).find('textarea').each(function(j, element){
						        $(element).removeAttr('disabled');
						    });

				    	});
				    });
					
					$( '#shipany-label-print').remove();
					$( '#shipment-shipany-label-form' ).append(shipany_label_data.main_button);

					if( response.shipany_tracking_num ) {
						var tracking_note;
						$('ul.order_notes li').each(function(i) {
						   tracking_note = $(this);
						   tracking_note_html = $(this).html()
						   if (tracking_note_html.indexOf(response.shipany_tracking_num) >= 0) {
							
								// var tracking_note = $( this ).closest( 'li.tracking_note' ); 
								$( tracking_note ).block({
									message: null,
									overlayCSS: {
										background: '#fff',
										opacity: 0.6
									}
								});

								var data_note = {
									action:   'woocommerce_delete_order_note',
									note_id:  $( tracking_note ).attr( 'rel' ),
									security: woocommerce_admin_meta_boxes.delete_order_note_nonce
								};

								$.post( woocommerce_admin_meta_boxes.ajax_url, data_note, function() {
									$( tracking_note ).remove();
								});
								
							   	return false;					   	
							}
						});
					}

					$( document ).trigger( 'pr_shipany_deleted_label' );
				}
			});

			return false;
		},
	};
	
	wc_shipment_shipany_label_items.init();

} );
