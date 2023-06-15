<?php if ( get_theme_mod('mega_store_woocommerce_blog_box_enable',false) ) : ?>

<?php $mega_store_woocommerce_args = array(
  'post_type' => 'post',
  'post_status' => 'publish',
  'category_name' =>  get_theme_mod('mega_store_woocommerce_blog_slide_category'),
  'posts_per_page' => get_theme_mod('mega_store_woocommerce_blog_slide_number'),
); ?>

<div class="slider">
  <div class="owl-carousel">
    <?php $mega_store_woocommerce_arr_posts = new WP_Query( $mega_store_woocommerce_args );
    if ( $mega_store_woocommerce_arr_posts->have_posts() ) :
      while ( $mega_store_woocommerce_arr_posts->have_posts() ) :
        $mega_store_woocommerce_arr_posts->the_post();
        ?>
        <div class="blog_inner_box">
          <?php
            if ( has_post_thumbnail() ) :
              the_post_thumbnail();
            endif;
          ?>
          <div class="blog_box pt-3 pt-md-0">
            <?php if ( get_theme_mod('mega_store_woocommerce_title_unable_disable',true) ) : ?>
            <h3 class="my-3"><?php the_title(); ?></a></h3>
            <?php endif; ?>
            <?php if ( get_theme_mod('mega_store_woocommerce_text_unable_disable',true) ) : ?>
            <p class="slider-text"><?php echo wp_trim_words( get_the_content(), get_theme_mod('mega_store_woocommerce_post_excerpt_number',22) ); ?><p>
              <?php endif; ?>
            <?php if ( get_theme_mod('mega_store_woocommerce_button_unable_disable',true) ) : ?>
            <p class="slider-button mt-4">
              <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"><?php esc_html_e('SHOP NOW','mega-store-woocommerce'); ?></a>
              <?php endif; ?>
            </p>
          </div>
        </div>
      <?php
    endwhile;
    wp_reset_postdata();
    endif; ?>
  </div>
</div>

<?php endif; ?>
