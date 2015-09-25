<?php

// Check if WooCommerce is active

// Flatsome Admin Bar helper

function flatsome_admin_bar_helper(){

    global $wp_admin_bar;

      $optionUrl = get_admin_url().'themes.php?page=optionsframework';
      $adminUrl = get_admin_url();


    if(is_category() || is_home()){
     $wp_admin_bar->add_menu( array(
             'parent' => false,
             'id' => 'admin_bar_helper',
             'title' => 'Blog Layout',
             'href' => $optionUrl.'&tab=of-option-blog',
      ));
    }


     if(ux_is_woocommerce_active()) { 

         if(is_checkout() || is_cart() ){
                 $wp_admin_bar->add_menu( array(
                     'parent' => false,
                     'id' => 'admin_bar_helper',
                     'title' => 'Checkout Settings',
                     'href' => $adminUrl.'admin.php?page=wc-settings&tab=checkout',
                 ));
          }


          if(is_product()){
                 $wp_admin_bar->add_menu( array(
                     'parent' => false,
                     'id' => 'admin_bar_helper',
                     'title' => 'Product Page Layout',
                     'href' => $optionUrl.'&tab=of-option-productpage',
                 ));
          }


            if(is_account_page()){
                 $wp_admin_bar->add_menu( array(
                     'parent' => false,
                     'id' => 'admin_bar_helper',
                     'title' => 'My Account Page',
                     'href' => $adminUrl.'admin.php?page=wc-settings&tab=account',
                 ));
             }



              if(is_shop() || is_product_category()){
                 $wp_admin_bar->add_menu( array(
                     'parent' => false,
                     'id' => 'admin_bar_helper',
                     'title' => 'Shop Settings',
                 ));

                  $wp_admin_bar->add_menu( array(
                     'parent' => 'admin_bar_helper',
                     'id' => 'admin_bar_helper_flatsome',
                     'title' => 'Category Page Layout',
                     'href' => $optionUrl.'&tab=of-option-categorypage',
                 ));

                  $wp_admin_bar->add_menu( array(
                     'parent' => 'admin_bar_helper',
                     'id' => 'admin_bar_helper_woocommerce',
                     'title' => 'Shop Page Display',
                     'href' => $adminUrl.'admin.php?page=wc-settings&tab=products&section=display',
                 ));
          }

      }
 
}

add_action( 'wp_before_admin_bar_render', 'flatsome_admin_bar_helper' , 1 );