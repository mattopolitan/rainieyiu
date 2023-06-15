<?php if ( get_theme_mod('mega_store_woocommerce_product_category_box_enable',false) ) : ?>

<div class="product_category pt-5 text-center">
  <div class="container">
    <?php if ( get_theme_mod('mega_store_woocommerce_product_category_section_heading',true) ) : ?>
      <h3 class="text-center mb-5"><?php echo esc_html(get_theme_mod('mega_store_woocommerce_product_category_section_heading')); ?></h3>
    <?php endif; ?>
    <div class="row">
      <?php $mega_store_woocommerce_product_category = get_theme_mod('mega_store_woocommerce_product_category_increament');
      for ($mega_store_woocommerce_i=1; $mega_store_woocommerce_i <= $mega_store_woocommerce_product_category; $mega_store_woocommerce_i++) { ?>
        <div class="col-lg-2 col-md-4 col-sm-4">
          <div class="product_category-box mb-3">
            <div class="product_category-inner-box">
              <?php if ( get_theme_mod('mega_store_woocommerce_product_category_box_images'.$mega_store_woocommerce_i) ) : ?>
                <img src="<?php echo esc_url(get_theme_mod('mega_store_woocommerce_product_category_box_images'.$mega_store_woocommerce_i)); ?>">
              <?php endif; ?>
              <?php if ( get_theme_mod('mega_store_woocommerce_product_category_box_title_url'.$mega_store_woocommerce_i) || get_theme_mod('mega_store_woocommerce_product_category_box_title'.$mega_store_woocommerce_i) ) : ?>
                <h4><a href="<?php echo esc_url(get_theme_mod('mega_store_woocommerce_product_category_box_title_url'.$mega_store_woocommerce_i)); ?>"><?php echo esc_html(get_theme_mod('mega_store_woocommerce_product_category_box_title'.$mega_store_woocommerce_i)); ?></a></h4>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php }?>
    </div>
  </div>
</div>

<?php endif; ?>
