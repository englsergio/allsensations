<?php

// CONTENT
//  - Blog post navigation (flatsome_content_nav)
//  - Comments (flatsome_comments)
//  - Post meta top (flatsome_posted_on)
//  - Blog categories (flatsome_categorized_blog)
//  - Custom dropdown for main menu (FlatsomeNavDropdown)
//  - Add to cart dropdown (flatsome_add_to_cart_dropdown)
//  - Next / Prev navigation on product pages
//  - Blog - Add "Read more" links
//  - Product Quick View
//  - Catalog Mode
//  - Mobile menu
//  - Continue Shopping
//  - Add product description in grid
//  - Add grid button if enabled

global $flatsome_opt;

if ( ! function_exists( 'flatsome_content_nav' ) ) :
/**
 * Display navigation to next/previous pages when applicable
 */
function flatsome_content_nav( $nav_id ) {
    global $wp_query, $post;

    // Don't print empty markup on single pages if there's nowhere to navigate.
    if ( is_single() ) {
        $previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
        $next = get_adjacent_post( false, '', false );

        if ( ! $next && ! $previous )
            return;
    }

    // Don't print empty markup in archives if there's only one page.
    if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
        return;

    $nav_class = ( is_single() ) ? 'navigation-post' : 'navigation-paging';

    ?>
    <nav role="navigation" id="<?php echo esc_attr( $nav_id ); ?>" class="<?php echo $nav_class; ?>">
    <?php if ( is_single() ) : // navigation links for single posts ?>

        <?php previous_post_link( '<div class="nav-previous left">%link</div>', '<span class="icon-angle-left">' . _x( '', 'Previous post link', 'flatsome' ) . '</span> %title' ); ?>
        <?php next_post_link( '<div class="nav-next right">%link</div>', '%title <span class="icon-angle-right">' . _x( '', 'Next post link', 'flatsome' ) . '</span>' ); ?>

    <?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

        <?php if ( get_next_posts_link() ) : ?>
        <div class="nav-previous"><?php next_posts_link( __( '<span class="icon-angle-left"></span> Older posts', 'flatsome' ) ); ?></div>
        <?php endif; ?>

        <?php if ( get_previous_posts_link() ) : ?>
        <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="icon-angle-right"></span>', 'flatsome' ) ); ?></div>
        <?php endif; ?>

    <?php endif; ?>

    </nav><!-- #<?php echo esc_html( $nav_id ); ?> -->
    <?php
}
endif; // flatsome_content_nav


if ( ! function_exists( 'flatsome_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function flatsome_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'flatsome' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'flatsome' ), '<span class="edit-link">', '<span>' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment-inner">

            <div class="row collapse">
                <div class="large-2 columns">
                    <div class="comment-author">
                    <?php echo get_avatar( $comment, 80 ); ?>
                </div>
                </div><!-- .large-3 -->

                <div class="large-10 columns">
                    <?php printf( __( '%s <span class="says">says:</span>', 'flatsome' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
                    <?php if ( $comment->comment_approved == '0' ) : ?>
                    <em><?php _e( 'Your comment is awaiting moderation.', 'flatsome' ); ?></em>
                    <br />
                     <?php endif; ?>

                <div class="comment-content"><?php comment_text(); ?></div>


                 <div class="comment-meta commentmetadata">
                    <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><time datetime="<?php comment_time( 'c' ); ?>">
                    <?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'flatsome' ), get_comment_date(), get_comment_time() ); ?>
                    </time></a>
                    <?php edit_comment_link( __( 'Edit', 'flatsome' ), '<span class="edit-link">', '<span>' ); ?>
                    
                    <div class="reply right">
            <?php
                comment_reply_link( array_merge( $args,array(
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                ) ) );
            ?>
            </div><!-- .reply -->


                </div><!-- .comment-meta .commentmetadata -->

                </div><!-- .large-10 columns -->

            </div><!-- .row -->

		</article>
    <!-- #comment -->

	<?php
			break;
	endswitch;
}
endif; // ends check for flatsome_comment()

if ( ! function_exists( 'flatsome_posted_on' ) ) :


    
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function flatsome_posted_on() {
    $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
    if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
    }

    $time_string = sprintf( $time_string,
        esc_attr( get_the_date( 'c' ) ),
        esc_html( get_the_date() ),
        esc_attr( get_the_modified_date( 'c' ) ),
        esc_html( get_the_modified_date() )
    );

    $posted_on = sprintf(
        esc_html_x( 'Posted on %s', 'post date', 'flatsome' ),
        '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
    );

    $byline = sprintf(
        esc_html_x( 'by %s', 'post author', 'flatsome' ),
        '<span class="meta-author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
    );

    echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>';

}
endif;



/* CUSTOM NAV WALKER */
class FlatsomeNavDropdown extends Walker_Nav_Menu
{
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $display_depth = ($depth + 1); // because it counts the first submenu as 0
        if($display_depth == '1'){$class_names = 'nav-dropdown';}
        else {$class_names = 'nav-column-links';}
        $indent = str_repeat("\t", $depth);
             $output .= "\n$indent<div class=".$class_names."><ul>\n";
    }

    function end_lvl( &$output, $depth = 1, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul></div>\n";
    }

    function start_el ( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
    // Most of this code is copied from original Walker_Nav_Menu
    global $wp_query;
    $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

    $class_names = $value = '';

    $classes = empty( $item->classes ) ? array() : (array) $item->classes;
    $classes[] = 'menu-item-' . $item->ID;

    $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
    $class_names = ' class="' . esc_attr( $class_names ) . '"';

    $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
    $id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

    $output .= $indent . '<li' . $id . $value . $class_names .'>';

    $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
    $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
    $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
    $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

    // Check if menu item is in main menu
    if ( $depth == 0 ) {
        // These lines adds your custom class and attribute
        $attributes .= ' class="nav-top-link"';
    }

    $description = '';
    if(strpos($class_names,'image-column') !== false){$description = '<img src="'.$item->description.'" alt=" "/>';}

    $item_output = $args->before;
    $item_output .= '<a'. $attributes .'>';
    $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
    $item_output .= $description;
    $item_output .= '</a>';
    $item_output .= $args->after;

    $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
  } 

}


add_filter( 'wp_nav_menu_objects', 'add_menu_parent_class' );
function add_menu_parent_class( $items ) {
	$parents = array();
	foreach ( $items as $item ) {
		if ( $item->menu_item_parent && $item->menu_item_parent > 0 ) {
			$parents[] = $item->menu_item_parent;
		}
	}
	
	foreach ( $items as $item ) {
		if ( in_array( $item->ID, $parents ) ) {
			$item->classes[] = 'menu-parent-item'; 
		}
	}
	
	return $items;    
}




/*  ADD TO CART DROPDOWN (gets inserted with ajax) */
add_filter('add_to_cart_fragments', 'flatsome_add_to_cart_dropdown'); 
function flatsome_add_to_cart_dropdown( $fragments ) {
	global $woocommerce;
    global $flatsome_opt;
	ob_start();
	?>
	<div class="cart-inner">
	<a href="<?php echo esc_url( $woocommerce->cart->get_cart_url() ); ?>" class="cart-link">
                    <strong class="cart-name hide-for-small"><?php _e('Cart', 'woocommerce'); ?></strong> 
					<span class="cart-price hide-for-small">/ <?php echo $woocommerce->cart->get_cart_subtotal(); ?></span> 
                        
					<!-- cart icon -->
					<div class="cart-icon">
                        <?php if ($flatsome_opt['custom_cart_icon']){ ?> 
                        <div class="custom-cart-inner">
                        <div class="custom-cart-count"><?php echo $woocommerce->cart->cart_contents_count; ?></div>
                        <img class="custom-cart-icon" src="<?php echo $flatsome_opt['custom_cart_icon']?>"/> 
                        </div><!-- .custom-cart-inner -->
                        <?php } else { ?> 

                         <strong><?php echo $woocommerce->cart->cart_contents_count; ?></strong>
                         <span class="cart-icon-handle"></span>
                        <?php } ?>
					</div><!-- end cart icon -->

					</a>
							<div  class="nav-dropdown">
                                <div id="mini-cart-content" class="nav-dropdown-inner">
                                <div class="cart_list">
                                <?php                                    
                                    if (sizeof($woocommerce->cart->cart_contents)>0) : foreach ($woocommerce->cart->cart_contents as $cart_item_key => $cart_item) :
                                        $_product = $cart_item['data'];                                            
                                        if ($_product->exists() && $cart_item['quantity']>0) :  ?>  

                                      	<div class="row mini-cart-item collapse">
                                      		<div class="small-2 large-2 columns">
                                      			<?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf('<a href="%s" class="remove" title="%s"><span class="icon-close"></span></a>', esc_url( $woocommerce->cart->get_remove_url( $cart_item_key ) ), __('Remove this item', 'woocommerce') ), $cart_item_key ); ?>
                                      		</div>
                                      		<div class="small-7 large-7 columns"><?php 
                                      			 $product_title = $_product->get_title();
                                                 echo '<a class="cart_list_product_title" href="'.get_permalink($cart_item['product_id']).'">' . apply_filters('woocommerce_cart_widget_product_title', $product_title, $_product) . '</a>';
                                                 echo '<div class="cart_list_product_price">'.woocommerce_price($_product->get_price()).'</div>';
                                                 echo '<div class="cart_list_product_quantity"> / '.__('Quantity', 'woocommerce').': '.$cart_item['quantity'].'</div>';

                                      		?></div>
                                      		<div class="small-3 large-3 columns">
                                                <?php  echo '<a class="cart_list_product_img" href="'.get_permalink($cart_item['product_id']).'">' . str_replace( array( 'http:', 'https:' ), '', $_product->get_image() ).'</a>'; ?>
                                      		</div>
                                      	</div><!-- end row -->

                                <?php                                        
                                    endif;                                        
                                    endforeach;
                                ?>

                                </div><!-- Cart list -->
                                            
                                    <div class="minicart_total_checkout">                                        
                                        <?php _e('Subtotal', 'woocommerce'); ?><span><?php echo $woocommerce->cart->get_cart_subtotal(  ); ?></span>                                   
                                    </div>
                                    
                                    <a href="<?php echo esc_url( $woocommerce->cart->get_cart_url() ); ?>" class="button expand uppercase"><?php _e('View Cart', 'woocommerce'); ?></a>   
                                    
                                    <a href="<?php echo esc_url( $woocommerce->cart->get_checkout_url() ); ?>" class="button secondary expand uppercase"><?php _e( 'Proceed to Checkout', 'woocommerce' ); ?></a>
                                    
                                    <?php                                        
                                    else: echo '<p class="empty">'.__('No products in the cart.','woocommerce').'</p>'; endif;                                    
                                ?>                                                                        
                            </div><!-- .nav-dropdown-inner -->
						</div><!-- .nav-dropdown -->
	</div><!-- .cart-inner -->

	<?php
	$fragments['.cart-inner'] = ob_get_clean();
	return $fragments;
}


 /* PRODUCT PAGE REVIEW STARS */
  function ProductShowReviews(){
            if ( comments_open() ) {
                global $wpdb;
                global $post;
            
                $count = $wpdb->get_var("
                    SELECT COUNT(meta_value) FROM $wpdb->commentmeta
                    LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
                    WHERE meta_key = 'rating'
                    AND comment_post_ID = $post->ID
                    AND comment_approved = '1'
                    AND meta_value > 0
                ");
            
                $rating = $wpdb->get_var("
                    SELECT SUM(meta_value) FROM $wpdb->commentmeta
                    LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
                    WHERE meta_key = 'rating'
                    AND comment_post_ID = $post->ID
                    AND comment_approved = '1'
                ");
            
                if ( $count > 0 ) {
            
                    $average = number_format($rating / $count, 2);
            
                    echo '<a href="#tab-reviews" class="scroll-to-reviews"><div class="star-rating tip-top" title="'.$count.' review(s)"><span style="width:'.($average*16).'px"><span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="rating"><span itemprop="ratingValue">'.$average.'</span><span itemprop="reviewCount" class="hidden">'.$count.'</span></span> '.__('out of 5', 'woocommerce').'</span></div></a>';

                }
                
            }}

add_action('woocommerce_single_product_summary','ProductShowReviews', 15);
            

/* NAXT / PREV NAV ON PRODUCT PAGES */
function next_post_link_product() {
    global $post;
    $next_post = get_next_post(true,'','product_cat');
    if ( is_a( $next_post , 'WP_Post' ) ) { ?>
       <div class="prod-dropdown">
                <a href="<?php echo get_the_permalink( $next_post->ID ); ?>" rel="next" class="icon-angle-left next"></a>
                <div class="nav-dropdown" style="display: none;">
                  <a title="<?php echo get_the_title( $next_post->ID ); ?>" href="<?php echo get_the_permalink( $next_post->ID ); ?>">
                  <?php echo get_the_post_thumbnail($next_post->ID, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' )) ?></a>

                </div>
            </div>
    <?php }
}

function previous_post_link_product() {
    global $post;
    $prev_post = get_previous_post(true,'','product_cat');
    if ( is_a( $prev_post , 'WP_Post' ) ) { ?>
       <div class="prod-dropdown">
                <a href="<?php echo get_the_permalink( $prev_post->ID ); ?>" rel="next" class="icon-angle-right prev"></a>
                <div class="nav-dropdown" style="display: none;">
                    <a title="<?php echo get_the_title( $prev_post->ID ); ?>" href="<?php echo get_the_permalink( $prev_post->ID ); ?>">
                    <?php echo get_the_post_thumbnail($prev_post->ID, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' )) ?></a>
                </div>
            </div>
    <?php }
}


/* BLOG - Add class to read more on blogs */
function flatsome_add_morelink_class( $link, $text )
{
    return str_replace(
         'more-link'
        ,'more-link button small'
        ,$link
    );
}
add_action( 'the_content_more_link', 'flatsome_add_morelink_class', 10, 2 );

add_action('wp_ajax_jck_quickview', 'jck_quickview');
add_action('wp_ajax_nopriv_jck_quickview', 'jck_quickview');

/** The Quickview Ajax Output **/
function jck_quickview() {
    global $post, $product, $woocommerce;
    $prod_id =  $_POST["product"];
    $post = get_post($prod_id);
    $product = get_product($prod_id);
    ob_start();
?>

<?php woocommerce_get_template( 'content-single-product-lightbox.php'); ?>

<?php
    $output = ob_get_contents();
    ob_end_clean();
    echo $output;
    die();
}


/* SHOW PRODUCTS IN STOCK */
if(isset($flatsome_opt['show_in_stock'])){
    if($flatsome_opt['show_in_stock']){
    // Hook in
    add_filter( 'woocommerce_get_availability', 'custom_override_get_availability', 1, 2);

    // Our hooked in function $availablity is passed via the filter!
    function custom_override_get_availability( $availability, $_product ) {
        if ( $_product->is_in_stock() ) $availability['availability'] = __('In stock', 'woocommerce');
        return $availability;
    }
    }
}


/* PRODUCT QUICK VIEW HOOKS */
add_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_meta', 40 );
add_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_add_to_cart', 30 );


/* CATALOG MODE SETTINGS */
if(isset($_GET["catalog-mode"]) || $flatsome_opt['catalog_mode']){
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
    remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
    remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
    remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
    remove_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_add_to_cart', 30 );

        if(isset($_GET["catalog-mode"]) || $flatsome_opt['catalog_mode_prices']){
                remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
                remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
                remove_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_price', 10 );
        }

        function catalog_mode_product(){
            global $flatsome_opt;
            echo '<div class="catalog-product-text">';
            echo do_shortcode($flatsome_opt['catalog_mode_product']);
            echo '</div>';
        }
        add_action('woocommerce_single_product_summary', 'catalog_mode_product', 30);

        function catalog_mode_lightbox(){
            global $flatsome_opt;
            echo '<div class="catalog-product-text">';
            echo do_shortcode($flatsome_opt['catalog_mode_lightbox']);
            echo '</div>';
        }
        add_action( 'woocommerce_single_product_lightbox_summary', 'catalog_mode_lightbox', 30 );

}

/**
 * Edit $wp_query before it is being run.
 */

function flatsome_pre_get_posts_action( $query ) {
    global $flatsome_opt;

    $action = isset($_GET['action']) ? $_GET['action'] : '';
    // Stop if searching from admin
    if($action == 'woocommerce_json_search_products') {
        return;
    }

    if($action == 'woocommerce_json_search_products_and_variations') {
        return;
    }
    // Include posts and pages in ajax search.
    if(defined('DOING_AJAX') && DOING_AJAX && !empty($query->query_vars['s']) && $flatsome_opt['search_result']) {
        $query->query_vars['post_type'] = array( $query->query_vars['post_type'], 'post', 'page' );
        $query->query_vars['meta_query'] = new WP_Meta_Query( array( 'relation' => 'OR', $query->query_vars['meta_query'] ) );
    }
}

add_action('pre_get_posts', 'flatsome_pre_get_posts_action');



/* Mobile menu */
function flatsome_mobile_menu(){ 
 global $flatsome_opt, $woocommerce; ?>

<!-- Mobile Popup -->
<div id="jPanelMenu" class="mfp-hide">
    <div class="mobile-sidebar">
        <?php if($flatsome_opt['catalog_mode']) { ?>
        <ul class="html-blocks">
            <li class="html-block">
                 <?php echo do_shortcode($flatsome_opt['catalog_mode_header']); ?>
            </li>
        </ul>
        <?php } ?>

        <ul class="mobile-main-menu">
        <?php if ($flatsome_opt['search_pos'] !== 'hide') { ?>
        <li class="search">
            <?php if(function_exists('get_product_search_form')) {
                get_product_search_form();
            } else {
                get_search_form();
            } ?>    
        </li><!-- .search-dropdown -->
        <?php } ?>

        <?php 
        if ( has_nav_menu( 'primary_mobile' ) ) { 
        // Load custom mobile menu if set
            wp_nav_menu(array(
                'theme_location' => 'primary_mobile',
                'container'       => false,
                'items_wrap'      => '%3$s',
                'depth'           => 0,
            ));

        } else {
        // Load default menu
            wp_nav_menu(array(
            'theme_location' => 'primary',
            'container'       => false,
            'items_wrap'      => '%3$s',
            'depth'           => 0,
           ));

        }
        ?>

        <?php if(ux_is_woocommerce_active() && $flatsome_opt['myaccount_dropdown']) { ?>

        <li class="menu-item menu-account-item menu-item-has-children">
            <?php
            if ( is_user_logged_in() ) { ?> 
                <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>">
                    <?php _e('My Account', 'woocommerce'); ?>
                </a>
                <ul class="sub-menu">
                <?php if ( has_nav_menu( 'my_account' ) ) : ?>
                <?php  
                wp_nav_menu(array(
                    'theme_location' => 'my_account',
                    'container'       => false,
                    'items_wrap'      => '%3$s',
                    'depth'           => 0,
                ));
                ?>
                <?php else: ?>
                    <li>Define your My Account dropdown menu in <b>Apperance > Menus</b></li>
                <?php endif; ?> 
                </ul>

            <?php } else { ?>
            <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>"><?php _e('Login', 'woocommerce'); ?></a>
            <?php } ?>                      
        </li>
        <?php } // end My account dropdown ?>
        </ul>

        <?php if($flatsome_opt['topbar_show']) { ?>
        <ul class="top-bar-mob">
             <?php if ( has_nav_menu( 'top_bar_nav' ) ) : ?>
            <?php  
                wp_nav_menu(array(
                    'theme_location' => 'top_bar_nav',
                    'menu_class' => 'top-bar-mob',
                    'container'       => false,
                    'items_wrap'      => '%3$s',
                    'depth' => 2,
                ));
            ?>
            <?php endif; ?>

             <?php if($flatsome_opt['top_right_text']) { ?>
            <li class="html-block">
                <?php echo do_shortcode($flatsome_opt['top_right_text']); ?>
            </li>
            <?php } ?>

            <?php if($flatsome_opt['topbar_right']) { ?>
            <li class="html-block">
               <?php echo do_shortcode($flatsome_opt['topbar_right']); ?>
            </li>
            <?php } ?>

        </ul>
        <?php } // end top bar ?>

       <?php if($flatsome_opt['nav_position'] == 'bottom') { ?>
        <ul class="html-blocks">
            <li class="html-block">
                <?php echo do_shortcode($flatsome_opt['nav_position_text']); ?>
            </li>
            <li class="html-block">
                <?php echo do_shortcode($flatsome_opt['nav_position_text_top']); ?>
            </li>
        </ul>
        <?php } ?>

        <?php if($flatsome_opt['nav_position'] == 'bottom_center') { ?>
        <ul class="html-blocks">
            <li class="html-block">
                 <?php echo do_shortcode($flatsome_opt['nav_position_text_top']); ?>
            </li>
        </ul>
        <?php } ?>



           
    </div><!-- inner -->
</div><!-- #mobile-menu -->

<?php
}

add_action('wp_footer', 'flatsome_mobile_menu');



// Continue Shopping button
if(isset($flatsome_opt['continue_shopping']) && $flatsome_opt['continue_shopping']){
    function flatsome_continue_shopping(){
     ?> 
     <a class="button-continue-shopping button alt-button small"  href="<?php echo wc_get_page_permalink( 'shop' ); ?>">
        &#8592; <?php echo __( 'Continue Shopping', 'woocommerce' ) ?></a> 
     <?php
    }

    add_action('woocommerce_after_cart_contents', 'flatsome_continue_shopping', 0);
    add_action('woocommerce_thankyou', 'flatsome_continue_shopping');
}


/* Show short description in grid */
if(ux_is_woocommerce_active() && function_exists('is_shop')){
    if($flatsome_opt['category_row_count'] == '1' || is_shop()){
       add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_single_excerpt', 30);
    } else if($flatsome_opt['short_description_in_grid']) {
       add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_single_excerpt', 30);
    }
}


/* Show button in grid */
if($flatsome_opt['add_to_cart_icon'] == "button") {
    function flatsome_add_button_in_grid (){
        global $product;
            echo apply_filters( 'woocommerce_loop_add_to_cart_link',
                sprintf( '<div class="add-to-cart-button"><a href="%s" rel="nofollow" data-product_id="%s" class="%s product_type_%s button alt-button small clearfix">%s</a></div>',
                    esc_url( $product->add_to_cart_url() ),
                    esc_attr( $product->id ),
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                    esc_attr( $product->product_type ),
                    esc_html( $product->add_to_cart_text() )
                ),
            $product );
       }
     add_action('woocommerce_after_shop_loop_item_title', 'flatsome_add_button_in_grid', 30);
}