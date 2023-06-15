jQuery(function ($) {

    const commonUtils = {
        isValidUUID: function (paramString) {
            const uuidV4Regex = /^[A-F\d]{8}-[A-F\d]{4}-4[A-F\d]{3}-[89AB][A-F\d]{3}-[A-F\d]{12}$/i;
            return uuidV4Regex.test(paramString.replace('SHIPANYSBX2','').replace('SHIPANYSBX1','').replace('SHIPANYDEV','').replace('SHIPANYDEMO',''));
        },
        getOffset: function (elem){
            const rect = elem.getBoundingClientRect();
            return {
                left: rect.left + window.scrollX,
                top: rect.top + window.scrollY,
                width: rect.width,
                height: rect.height
            };
        }
    }
    const appendLoader = function (cssSelector){
        $(cssSelector).after('<div class="lds-dual-ring"></div>');
        let targetElementInDom = $.find(cssSelector)
        let loaderDom = $.find('.lds-dual-ring');
        if(Array.isArray(targetElementInDom) && targetElementInDom.length > 0){
            targetElementInDom = targetElementInDom[0]
            const position = commonUtils.getOffset(targetElementInDom)
            let loaderPosition = position.left + position.width - 176;
            loaderDom[0].style.left = `${loaderPosition}px`;
        }
    }
    const removeLoader = function (){
        let loaderDoms = $.find('.lds-dual-ring');
        if(Array.isArray(loaderDoms) && loaderDoms.length > 0){
            for(const elem of loaderDoms){
                elem.remove();
            }
        }
    }

    const hideDefaultWeight = function (){
        let keyVal = $("input[name='woocommerce_shipany_ecs_asia_shipany_api_key']").val()
        var keyValMd5 = MD5(keyVal)
        if (!['8241d0678fb9abe65a77fe6d69f7063c', '7df5eeebe4116acfefa81a7a7c3f12ed'].includes(keyValMd5)) {
            $('#woocommerce_shipany_ecs_asia_default_weight').prop('checked', false);
            $('label[for="woocommerce_shipany_ecs_asia_default_weight"]').parent().parent().hide()
        }
    }
    
    const appendRegisterLink = function (){
        let store_url = shipany_setting_val.store_url
        let textElem = $.find('.shipany-register-descr + p');
        let regionSuffix = ''
        if ($("#woocommerce_shipany_ecs_asia_shipany_region").find(":selected").text() == 'Singapore') {
            regionSuffix = '-sg'
        }
        if(Array.isArray(textElem) && textElem.length > 0){
            textElem = textElem[0];
            $('.shipany-register-descr + p').append('<a class="shipany-portal-link" target="_blank" href="https://portal'+ regionSuffix +'.shipany.io/user/register?referrer=woocommerce&store_url=' + store_url +'">Register now</a>')
            $('.shipany-register-descr').hide();
        }
    }
    const appendShippingMethodLink = function (){
        let elem = $('.shipany-enable-locker')
        let currentUrl = window.location.href
        let newUrl = currentUrl.replace('wc-settings&tab=shipping&section=shipany_ecs_asia', 'wc-settings&tab=shipping&zone_id=1')

        if (elem[0] !== undefined) {
            // elem.parent().parent().append('To enable Locker/Store List, please add "Local pickup" in <a href="' + newUrl + '">Shipping zones</a>')
            elem.parent().parent().append('Add "Local pickup" in <a href="' + newUrl + '">Shipping zones</a> to enable Locker/Store List. If more than one Local pickup is defined, the first one will always be the one linking to the locker list.')

        }

        // disable the checkbox
        // $("label[for='"+'woocommerce_shipany_ecs_asia_enable_locker_list'+"']").css('cursor','not-allowed')
    }

    const appendGetTokenLink = function (){
        // rest_url => http://localhost/appcider/wp-json/ , trim the /wp-json/
        let currentUrl = window.location.href
        let rest_url = shipany_setting_val.rest_url.replace('/wp-json/', '')
        let callback_url_prefix = 'https://api.shipany.io/'
        let mch_uid = shipany_setting_val.mch_uid
        
        if (shipany_setting_val.shipany_api_key != null ) {
            if (shipany_setting_val.shipany_api_key.includes('SHIPANYDEV')) callback_url_prefix = 'https://api-dev3.shipany.io/'
            else if (shipany_setting_val.shipany_api_key.includes('SHIPANYDEMO')) callback_url_prefix = 'https://api-demo1.shipany.io/'
            else if (shipany_setting_val.shipany_api_key.includes('SHIPANYSBX2')) callback_url_prefix = 'https://api-sbx2.shipany.io/'
            else if (shipany_setting_val.shipany_api_key.includes('SHIPANYSBX1')) callback_url_prefix = 'https://api-sbx1.shipany.io/'
        }


        const endpoint = '/wc-auth/v1/authorize';
        const params = {
          app_name: 'ShipAny',
          scope: 'read_write',
          user_id: 1,
          return_url: currentUrl,
          callback_url: callback_url_prefix + 'woocommerce/webhooks/receive-rest-token/?mch_uid='+ mch_uid + '&store_url=' + rest_url
        };
        var queryString = $.param(params)

        // console.log(rest_url + endpoint + '?' + queryString );

        // let elem = $('.shipany-rest')
        // if (elem[0] !== undefined) {
        //     // elem.parent().parent().append('To enable Locker/Store List, please add "Local pickup" in <a href="' + newUrl + '">Shipping zones</a>')
        //     elem.parent().parent().append('Click <a href="' + rest_url + endpoint + '?' + queryString + '">Here</a> to register REST API token')
        // }
        if (document.getElementById('woocommerce_shipany_ecs_asia_shipany_rest_token').innerHTML != null) {
            document.getElementById('woocommerce_shipany_ecs_asia_shipany_rest_token').innerHTML = 'Grant Permission'
        }
        

        document.getElementById("woocommerce_shipany_ecs_asia_shipany_rest_token").onclick = function () {
            location.href = rest_url + endpoint + '?' + queryString;
        };
        if (mch_uid == null || shipany_setting_val.shipany_api_key == null) {
            document.getElementById("woocommerce_shipany_ecs_asia_shipany_rest_token").onclick = function () {
                alert('Save all changes including (API Token, Default Courier) before enable ShipAny Active Notification')
                return false;
            };              
        } else if (shipany_setting_val.has_token) {
            document.getElementById("woocommerce_shipany_ecs_asia_shipany_rest_token").onclick = function () {
                alert('Already enabled ShipAny Active Notification')
                return false;
            };              
        }
 
    }
    const updateStorageType = function (getTargetValue){
        // $( '.default-storage-type option' )
        // $( 'select[name="woocommerce_shipany_ecs_asia_set_default_storage_type"]' )
        if (['c6e80140-a11f-4662-8b74-7dbc50275ce2','f403ee94-e84b-4574-b340-e734663cdb39','7b3b5503-6938-4657-acab-2ff31c3a3f45','2ba434b5-fa1d-4541-bc43-3805f8f3a26d','1d22bb21-da34-4a3c-97ed-60e5e575a4e5','1bbf947d-8f9d-47d8-a706-a7ce4a9ddf52','c74daf26-182a-4889-924b-93a5aaf06e19'].includes(getTargetValue)){
            $('.default-storage-type option').each(function() {
                $(this).remove();
            });
            $(".default-storage-type option[value='']").each(function() {$(this).remove();});
            var optionsAsString = "";
            optionsAsString +='<option value ="Air Conditioned">Air Conditioned (17°C - 22°C)</option>';
            optionsAsString +='<option value ="Chilled">Chilled (0°C - 4°C)</option>';
            optionsAsString +='<option value ="Frozen">Frozen (-18°C - -15°C)</option>';
            $( 'select[name="woocommerce_shipany_ecs_asia_set_default_storage_type"]' ).append( optionsAsString );               
        } else {
            $('.default-storage-type option').each(function() {
                $(this).remove();
            });
            $(".default-storage-type option[value='']").each(function() {$(this).remove();});
            var optionsAsString = "";
            optionsAsString +='<option value ="">Normal</option>';
            $( 'select[name="woocommerce_shipany_ecs_asia_set_default_storage_type"]' ).append( optionsAsString );    
        }

    }
    const updateAdditionalServicePlan = function (targetText){
        if (targetText == 'Lalamove') {
            $('label[for="woocommerce_shipany_ecs_asia_shipany_default_courier_additional_service"]').parent().parent().show()
        } else {
            $('label[for="woocommerce_shipany_ecs_asia_shipany_default_courier_additional_service"]').parent().parent().hide()
        }
    }
    const updatePaidByRec = function (targetValue){
        $('#woocommerce_shipany_ecs_asia_shipany_paid_by_rec').prop('checked', false)
        if (shipany_setting_val['courier_show_paid_by_rec'].includes(targetValue)){
            $('label[for="woocommerce_shipany_ecs_asia_shipany_paid_by_rec"]').parent().parent().show()
        } else {
            $('label[for="woocommerce_shipany_ecs_asia_shipany_paid_by_rec"]').parent().parent().hide()
        }
    }
    var wc_shipping_setting = {
        // init Class
        init: function () {
            // hide paid by rec if the courier not support
            if (shipany_setting_val['courier_show_paid_by_rec'] != null) {
                if (!shipany_setting_val['courier_show_paid_by_rec'].includes($(".default-courier-selector option:selected").val())){
                    $('#woocommerce_shipany_ecs_asia_shipany_paid_by_rec').prop('checked', false);
                    $('label[for="woocommerce_shipany_ecs_asia_shipany_paid_by_rec"]').parent().parent().hide()
                }
            }
            hideDefaultWeight();
            appendRegisterLink();
            appendShippingMethodLink();
            appendGetTokenLink();
            // hide additional service plan if the courier not support
            if ($('#select2-woocommerce_shipany_ecs_asia_shipany_default_courier-container')[0] != null) {
                if ($('#select2-woocommerce_shipany_ecs_asia_shipany_default_courier-container')[0].innerHTML != 'Lalamove') {
                    $('label[for="woocommerce_shipany_ecs_asia_shipany_default_courier_additional_service"]').parent().parent().hide()
                }
            } else if ($('#woocommerce_shipany_ecs_asia_shipany_default_courier').find(":selected").text() != 'Lalamove') {
                $('label[for="woocommerce_shipany_ecs_asia_shipany_default_courier_additional_service"]').parent().parent().hide()
            }
            let isLoadedCourierList = false;
            let isLoading = false;
            let currentInputToken = $("input[name='woocommerce_shipany_ecs_asia_shipany_api_key']").val();
            $("input[name='woocommerce_shipany_ecs_asia_shipany_api_key']").bind('onChangeAccessToken', function (e) {
                // $("button[name='save']").trigger('click');
                let targetTokenVal = e.target.value.trim();
                let shipanyRegion = $("#woocommerce_shipany_ecs_asia_shipany_region").find(":selected").text()
                // if(currentInputToken === targetTokenVal){
                //     return;
                // }else{
                //     isLoadedCourierList = false;
                // }
                isLoadedCourierList = false;
                if (commonUtils.isValidUUID(targetTokenVal)) {
                    console.log('is UUID');
                    if (!isLoadedCourierList) {
                        // isLoadedCourierList = true
                        if(isLoading){
                            return;
                        }
                        console.log('Going to trigger API call');
                        $('.default-courier-selector').prop('disabled', true);
                        appendLoader("input[name='woocommerce_shipany_ecs_asia_shipany_api_key");
                        isLoading = true;
                        $.ajax({
                            url: shipany_setting_val.ajax_url,
                            method: 'POST',
                            dataType: 'JSON',
                            data:{
                                // the value of data.action is the part AFTER 'wp_ajax_' in
                                // the add_action ('wp_ajax_xxx', 'yyy') in the PHP above
                                action: 'on_change_load_couriers',
                                // ANY other properties of data are passed to your_function()
                                // in the PHP global $_REQUEST (or $_POST in this case)
                                api_tk : targetTokenVal,
                                region: shipanyRegion
                            },
                            success: function (response){
                                isLoading = false
                                if(response.success){
                                    console.log('get response')
                                    const courierList = {...response.data.data}
                                    var optionsAsString = "";

                                    // check if need remove old mounted dom here
                                    $('.default-courier-selector option').each(function() {
                                        $(this).remove();
                                    });
                                    for(const courierUUID of Object.keys(courierList)){
                                        optionsAsString += "<option value='" + courierUUID + "'>" + courierList[courierUUID] + "</option>";
                                    }
                                    $( 'select[name="woocommerce_shipany_ecs_asia_shipany_default_courier"]' ).append( optionsAsString );

                                    $(".default-courier-selector option[value='']").each(function() {
                                        $(this).remove();
                                    });

                                    if (response.data.asn_mode != 'Disable'){
                                        $('label[for="woocommerce_shipany_ecs_asia_shipany_tracking_note_txt"]').hide()
                                        if (document.getElementById('woocommerce_shipany_ecs_asia_shipany_tracking_note_txt') != null) document.getElementById('woocommerce_shipany_ecs_asia_shipany_tracking_note_txt').style='display:none'
                                    }
                                }else{
                                    console.log('failed to get success')
                                    $('.default-courier-selector option').each(function() {
                                        $(this).remove();
                                    });
                                    let errorTitle = response.data.data.error_title;
                                    let errorDetail = response.data.data.error_detail;
                                    $('.shipany-main-dialog').show();
                                    $('.shipany-dialog > .title').text(errorTitle);
                                    $('.shipany-dialog > .detail').text(errorDetail);
                                    isLoadedCourierList = false;
                                    isLoading = false;
                                }
                                isLoadedCourierList = true;
                                currentInputToken = targetTokenVal;
                                removeLoader();
                                $('.default-courier-selector').prop('disabled', false);
                            },
                            error: function (xhr, ajaxOptions, thrownError){
                                console.log('error');
                                isLoadedCourierList = false;
                                isLoading = false;

                            }
                        })
                    }
                } else {
                    console.log('No an UUID, do nothing');
                    $('.default-courier-selector option').each(function() {
                        $(this).remove();
                    });
                }        
            }).change(function () {
                $(this).trigger('onChangeAccessToken'); //call onChangeAccessToken on blur
            }).keyup(function (e) {
                var code = (e.keyCode || e.which);
                if(code == 17) {
                    return;
                }
                $(this).trigger('onChangeAccessToken'); //call onChangeAccessToken on blur
            });

            // handle in case end user select default courier from rendered options
            $('.default-courier-selector').bind('onChangeSelectDefaultCourier', function (e){
                const targetValue = e.target.value;
                const targetText = $(".default-courier-selector option:selected").text();
                const tempKey= document.getElementById('woocommerce_shipany_ecs_asia_shipany_api_key').value;
                updateStorageType(targetValue);
                updateAdditionalServicePlan(targetText);
                updatePaidByRec(targetValue);
                console.log(targetValue);
                console.log(targetText);
                $.ajax({
                    url: shipany_setting_val.ajax_url,
                    method: 'POST',
                    dataType: 'JSON',
                    data: {
                        action: 'set_default_courier',
                        cour_uid: targetValue,
                        cour_name: targetText,
                        temp_key: tempKey
                    },
                    success: function (response){
                        if(response.success){
                            console.log('get response');
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError){
                        console.log('error')
                    }

                })
            }).change(function (){
                $(this).trigger('onChangeSelectDefaultCourier'); //call onChangeAccessToken on blur
            });

            // handle in case end user select storage type from rendered options
            $('.default-storage-type').bind('onChangeSelectDefaultStorageType', function (e){
                const targetValue = e.target.value;
                const targetText = $(".default-storage-type option:selected").text();
                $.ajax({
                    url: shipany_setting_val.ajax_url,
                    method: 'POST',
                    dataType: 'JSON',
                    data: {
                        action: 'set_default_storage_type',
                        storage_value: targetValue,
                        storage_name: targetText
                    },
                    success: function (response){
                        if(response.success){
                            console.log('get response');
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError){
                        console.log('error')
                    }

                })
            }).change(function (){
                $(this).trigger('onChangeSelectDefaultStorageType'); //call onChangeAccessToken on blur
            })

            // handle update address
            $('.update-address').click('onUpdateAddress', function (e){
                document.getElementById('woocommerce_shipany_ecs_asia_shipany_update_address').innerHTML='Loading...';
                document.getElementById('woocommerce_shipany_ecs_asia_shipany_update_address').style.cursor = 'default';
                document.getElementById('woocommerce_shipany_ecs_asia_shipany_update_address').style.pointerEvents = 'none';
                $.ajax({
                    url: shipany_setting_val.ajax_url,
                    method: 'POST',
                    dataType: 'JSON',
                    data: {
                        action: 'on_click_update_address',
                    },
                    success: function (response){
                        if(response.success){
                            alert('Sender Address update to: ' + response.data.address_line1 + ' ' + response.data.distr + ' ' + response.data.cnty);
                        } else {
                            alert('Update failed.')
                        }
                        document.getElementById('woocommerce_shipany_ecs_asia_shipany_update_address').innerHTML='Refresh Sender Address';
                        document.getElementById('woocommerce_shipany_ecs_asia_shipany_update_address').style.cursor = 'pointer';
                        document.getElementById('woocommerce_shipany_ecs_asia_shipany_update_address').style.pointerEvents = '';
                    }
                })
            })

            // Handle change region, empty api-tk and courier list
            $("#woocommerce_shipany_ecs_asia_shipany_region").bind('onChangeSelectRegion', function (e){
                // Clear the api-tk
                $('#woocommerce_shipany_ecs_asia_shipany_api_key').val('')

                // Update register portal url
                var shipany_region = $("#woocommerce_shipany_ecs_asia_shipany_region").find(":selected").text()
                var oldUrl = $(".shipany-portal-link").attr("href")
                if (shipany_region == 'Singapore' && !oldUrl.includes('-sg')) {
                    var newUrl = oldUrl.replace("portal", "portal-sg");
                    $(".shipany-portal-link").attr("href", newUrl);
                } else if (shipany_region == 'Hong Kong' && oldUrl.includes('-sg')) {
                    var newUrl = oldUrl.replace("portal-sg", "portal");
                    $(".shipany-portal-link").attr("href", newUrl);
                }

                // Clear Courier list
                $('.default-courier-selector option').each(function() {
                    $(this).remove();
                });
            }).change(function (){
                $(this).trigger('onChangeSelectRegion'); 
            })
        },
    };

    wc_shipping_setting.init();
    console.log('test');

});
