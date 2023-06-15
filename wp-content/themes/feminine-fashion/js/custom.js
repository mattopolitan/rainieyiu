jQuery(document).ready(function($) {


    var slider_auto, slider_loop, rtl;
    
    if( feminine_fashion_data.auto == '1' ){
        slider_auto = true;
    }else{
        slider_auto = false;
    }
    
    if( feminine_fashion_data.loop == '1' ){
        slider_loop = true;
    }else{
        slider_loop = false;
    }
    
    if( feminine_fashion_data.rtl == '1' ){
        rtl = true;
    }else{
        rtl = false;
    }

    $('.site-header.style-nine .secondary-menu .toggle-btn').click(function() {
        $('body').addClass('menu-active');
        $(this).siblings('div').animate({
            width: 'toggle',
        });
    });


   //Banner slider js
    $('.site-banner.style-one .item-wrap').owlCarousel({
        items: 1,
        autoplay: slider_auto,
        loop: slider_loop,
        nav: true,
        dots: false,
        autoplaySpeed : 800,
        autoplayTimeout: feminine_fashion_data.speed,
        rtl : rtl,
        responsive : {
            0 : {
                margin: 10,
                stagePadding: 20,
            }, 
            768 : {
                margin: 10,
                stagePadding: 80,
            }, 
            1025 : {
                margin: 40,
                stagePadding: 150,
            }, 
            1200 : {
                margin: 60,
                stagePadding: 200,
            }, 
            1367 : {
                margin: 80,
                stagePadding: 300,
            }, 
            1501 : {
                margin: 110,
                stagePadding: 342,
            }
        }
    });
});
