<?php
/*
  Plugin Name: WooMedia WooCommerce Rewards
  Plugin URI: http://www.woomedia.info
  Description: Rewards system for WooCommerce
  Version: 2.3.6
  Author: WooMedia Inc.
  Author URI: http://www.woomedia.info
  Requires at least: 4.0
  Tested up to: 4.3

  Copyright: © 2009-2015 WooMedia Inc.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

define('SWR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ));
define('SWR_URL', plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) ));
define('SWR_SLUG', plugin_basename(__FILE__));
require('swr-settings.php');
require('swr-subscribe.php');
/* require('swr-product-type.php'); */
/* require('swr-refer-a-friend.php'); */

require_once("syn-includes/syn-functions.php");

function syn_rewards_update_init(){
	$syn_update = new SYN_Auto_Update( get_plugin_data(__FILE__), plugin_basename( __FILE__ ), '2588711', '3kn9fJVdy5lrcDaN5EMhgVB9m' );
}
add_action('admin_init', 'syn_rewards_update_init', 11);

function swr_rewards_init() {
	global $woocommerce, $swr_settings;
	
	include('classes/class-wc-reward.php');
	
	if ( version_compare($woocommerce->version, '2.1.0') >= 0) {
		
		include_once( ABSPATH . 'wp-content/plugins/woocommerce/includes/admin/settings/class-wc-settings-page.php' );
		include('classes/class-wc-rewards-2.php');
		
	}else{
		
		include('classes/class-wc-rewards.php');
		
	}
	
	include('classes/class-wc-rewards-settings.php');
	include('swr-functions.php');
	/* include('swr-user-rewards-details.php'); */
	include('swr-coupon.php');
	
	do_action('swr_init', $woocommerce);
	
	$woocommerce->rewards = new WC_Rewards();
	$woocommerce->rewards->init();
	$swr_settings = $woocommerce->rewards->rewards['rewards_settings'];
	
	do_action('swr_init_after_settings', $woocommerce);
	
	if( $woocommerce->rewards->rewards['rewards_settings']->is_enabled() && ( $swr_settings->current_user_can_use_rewards() || !is_user_logged_in() ) ){
		if($woocommerce->rewards->rewards['rewards_settings']->show_top_cart()){
			add_action('woocommerce_before_cart_table', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_cart_table()){
			add_action('woocommerce_cart_collaterals', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_cart_totals()){
			add_action('woocommerce_after_cart_totals', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_shipping_calculator()){
			add_action('woocommerce_after_shipping_calculator', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_before_order()){
			add_action('woocommerce_before_checkout_form', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_order()){
			add_action('woocommerce_after_checkout_form', 'swr_before_cart_table');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_order_thankyou()){
			add_action('woocommerce_thankyou', 'swr_thankyou_rewards');
		}
		if($woocommerce->rewards->rewards['rewards_settings']->show_below_product_price()){
			add_action('woocommerce_single_product_summary', 'swr_show_product_reward', 15);
		}
		
		switch($swr_settings->settings['swr_use_rewards_where_to_show']){
			case 'before':
			default:
				add_action('woocommerce_checkout_after_customer_details', 'swr_show_use_rewards');
				break;
			case 'top':
				add_action('woocommerce_checkout_before_customer_details', 'swr_show_use_rewards');
				break;
			case 'after':
				add_action('woocommerce_checkout_order_review', 'swr_show_use_rewards');
				break;
		}
		
		add_action('woocommerce_review_order_before_submit', 'swr_update_reward');
		add_action('woocommerce_view_order', 'swr_thankyou_rewards');
		add_action('woocommerce_product_options_pricing', 'swr_product_options_pricing');
		add_action('woocommerce_process_product_meta', 'swr_process_product_meta', 1, 2);
		add_action('wp_footer', 'swr_check_actions');
		add_action('wp_ajax_nopriv_swr_update_product_qty', 'swr_update_product_qty');
		add_action('wp_ajax_swr_update_product_qty', 'swr_update_product_qty');
		add_filter('woocommerce_get_price_html', 'swr_get_price_html', 10, 2);
		/* add_filter('woocommerce_calculated_total', 'swr_calculated_total'); */
		
		add_action('woocommerce_before_checkout_process', 'swr_before_checkout_process');
		add_action('woocommerce_checkout_update_order_review', 'swr_before_calculate_totals');
		add_action( 'woocommerce_review_order_before_order_total', 'swr_review_order_before_order_total' );
		
		if($swr_settings->swr_apply_rewards_before_tax){
		
			add_filter('woocommerce_get_discounted_price', 'swr_calculated_total_before_tax', 10, 3);
			
		}else{
		
			add_action('woocommerce_calculate_totals', 'swr_calculated_total');
			
		}
		
		add_filter( 'woocommerce_coupon_get_discount_amount', 'swr_get_discount_amount', 10, 5 );
		
		add_filter( 'woocommerce_cart_totals_coupon_label', 'swr_coupon_label' );
		add_filter( 'woocommerce_get_shop_coupon_data', 'swr_get_discount_data', 10, 2 );
		
		add_action('woocommerce_checkout_update_order_meta', 'swr_update_order_meta');
		add_filter('woocommerce_get_order_item_totals', 'swr_get_order_item_totals', 10, 2);
		add_action('woocommerce_email_header', 'swr_email_header');
		add_action('woocommerce_email_footer', 'swr_email_footer');
		add_action('woocommerce_order_status_refunded', 'swr_remove_reward');
		add_action('woocommerce_order_status_cancelled', 'swr_remove_reward');
		add_action('woocommerce_order_status_cancelled', 'swr_give_back_rewards');
		add_action('woocommerce_order_status_pending', 'swr_remove_reward');
		add_action('woocommerce_order_status_failed', 'swr_remove_reward');
		add_action('woocommerce_order_status_on-hold', 'swr_remove_reward');
		add_action('woocommerce_order_status_processing', 'swr_remove_reward');
		add_action('woocommerce_product_after_variable_attributes', 'swr_product_after_variable_attributes', 10, 2);
		add_action('woocommerce_order_status_completed', 'swr_add_reward');
		add_action('woocommerce_email_after_order_table', 'swr_email_after_order_table', 10, 3);
		if($swr_settings->review_enabled()){
			add_action( 'comment_post', 'swr_add_comment_rating', 1 );
		}
		if($swr_settings->specific_product()){
			add_action('woocommerce_after_add_to_cart_button', 'swr_after_add_to_cart_button');
		}
		if(current_user_can('manage_options')){
			add_action('show_user_profile', 'swr_profile_rewards');
			add_action('edit_user_profile', 'swr_profile_rewards');
			add_action('personal_options_update', 'swr_profile_save_rewards');
			add_action('edit_user_profile_update', 'swr_profile_save_rewards');
		}
		add_action('wp_enqueue_scripts', 'swr_frontend_scripts');
		add_action('admin_enqueue_scripts', 'swr_admin_scripts');
		do_action('swr_init_enabled', $woocommerce);
	}
	
	do_action('swr_rewards_settings_scripts');
	add_shortcode('swr_cart_amount', 'get_swr_cart_amount');
	add_shortcode('swr_rewards_amount', 'get_swr_rewards_amount');
	add_shortcode('swr_view_rewards', 'get_swr_view_rewards');
	add_shortcode('swr_product_rewards', 'get_swr_product_rewards');
	
	if(isset($_GET['install_swr_pages']) && $_GET['install_swr_pages']==1){
		swr_create_pages();
	}
	if(isset($_GET['give_older_orders_rewards']) && $_GET['give_older_orders_rewards']==1){
		//swr_give_older_orders_rewards();
	}
	
	if (version_compare($woocommerce->version, '2.1.0') < 0 || true) {
		add_filter('woocommerce_settings_tabs_array', 'swr_rewards_tab_filter', 21);
	}
	
	/* $swr_settings->check_expired_rewards(); */
	
}
add_action('woocommerce_init', 'swr_rewards_init');

add_action('init', 'swr_give_older_orders_rewards');

function swr_plugins_loaded() {
	load_plugin_textdomain( 'rewards', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'swr_plugins_loaded');

function swr_rewards_head() {
	if(isset($_GET['page']) && ($_GET['page']=='woocommerce_settings' || $_GET['page']=='woocommerce') && isset($_GET['tab']) && $_GET['tab']=='rewards'){
		if(!isset($_GET['section']))
			$_GET['section'] = 'rewards_settings';
		do_action('swr_'.$_GET['section'].'_scripts');
	}
}
add_action('admin_head', 'swr_rewards_head');

function swr_frontend_scripts(){
	global $woocommerce, $swr_settings;
	if($swr_settings->is_enabled() && is_cart() || is_checkout()){
		wp_enqueue_script('swr_checkout_script', plugins_url('synmedia-woocommerce-rewards/assets/js/jquery.swr.checkout.js'), array('jquery'), '1.0.1');
		if(is_checkout()){
			wp_enqueue_style('swr_orders_rewards', plugins_url('synmedia-woocommerce-rewards/assets/css/swr.checkout.css'));
		}
	}elseif($swr_settings->is_enabled() && 1==2){
		wp_enqueue_script('swr_orders_script', plugins_url('synmedia-woocommerce-rewards/assets/js/jquery.swr.orders.js'), array('jquery'), '1.0');
		?>
		<script type="text/javascript">
			var rewards_title = "<?php echo($swr_settings->settings['swr_rewards_title']); ?>";
		</script>
		<?php
	}elseif($swr_settings->is_enabled() && is_product()){
		wp_enqueue_script('swr_product_script', plugins_url('synmedia-woocommerce-rewards/assets/js/jquery.swr.product.js'), array('jquery'), '1.0');
	}
	if($swr_settings->is_enabled()){
		wp_enqueue_script('swr_base', SWR_URL.'/assets/js/jquery.swr.base.js', array('jquery'), '1.0');
	}
}

function swr_admin_scripts(){
	wp_enqueue_script('swr_admin_settings_script', SWR_URL.'/assets/js/admin.settings.js', array('jquery'), '1.0');
	wp_register_script('swr_writepanel', plugins_url('synmedia-woocommerce-rewards/assets/js/swr.writepanel.js'), array('jquery'));
	wp_enqueue_script('swr_writepanel');
}

function swr_create_pages(){
	global $woocommerce;
	
	do_action('swr_create_pages');
	
	update_option('swr_pages_installed', 1);
}

function swr_give_older_orders_rewards(){
	global $swr_settings, $woocommerce;
	
	if( !isset($_GET['give_older_orders_rewards']) || $_GET['give_older_orders_rewards'] != 1)
		return ;
	
	$orders = get_posts(array(
		'numberposts'	=> -1,
		'post_type'		=> 'shop_order'
	));
	
	if( $orders && count( $orders ) > 0 ){
		
		foreach($orders as $order){
			
			if( version_compare( $woocommerce->version, '2.2.0' ) >= 0 ){
				
				$order = new WC_Order( $order->ID );
				$reward = $swr_settings->get_reward_earned_for_order( $order->id );
				if( ! $reward ){
					
					swr_update_order_meta( $order->id, false );
					
					if( $order->has_status( "completed" ) )
						swr_add_reward( $order->id );
					
				}
				
			}else{
				
				$terms = wp_get_object_terms( $order->ID, 'shop_order_status', array('fields' => 'slugs') );
				$status = (isset($terms[0])) ? $terms[0] : 'pending';
				$reward = $swr_settings->get_reward_earned_for_order($order->ID);
				if(!$reward){
					swr_update_order_meta($order->ID, false);
					if($status=='completed'){
						swr_add_reward($order->ID);
					}
				}
				
			}
			
		}
		
	}
}

function swr_before_cart_table(){
	global $woocommerce, $swr_settings;
	
	$cart_rewards = $swr_settings->get_cart_reward();
	
	if( $cart_rewards <= 0 )
		return ;
	
	$msg = swr_get_text_rewards();
?>
<p class="<?php echo((WOOCOMMERCE_VERSION >= 2)?'woocommerce-info':'woocommerce_info') ?> swr_get_rewards" style="clear:both;"><?php echo($msg); ?></p>
<?php
}

function swr_coupon_label( $label ) {
	global $swr_settings;
	if ( strstr( strtoupper( $label ), 'SWR_POINTS_REDEMPTION' ) ) {
		$label = esc_html( sprintf( __( '%s redemption', 'rewards'), $swr_settings->get_title() ) );
	}

	return $label;
}

function swr_update_product_qty(){
	global $swr_settings;
	
	$new_rewards = $old_rewards = 0;
	
	$products = array();
	$ret = array(
		'swr_new_reward'	=> 0,
		'swr_old_reward'	=> 0,
		'show_old_reward'	=> 0
	);
	
	if( isset( $_POST[ 'variation_id' ] ) && $_POST[ 'variation_id' ] > 0 ){
	
		$prod = new WC_Product_Variation( $_POST[ 'variation_id' ] );
		$products[] = array( 'qty' => $_POST[ 'qty' ], 'product' => $prod );
		
	}elseif( isset( $_POST[ 'product_id' ] ) && $_POST[ 'product_id' ] > 0 ){
	
		$prod = new WC_Product( $_POST[ 'product_id' ] );
		$products[] = array( 'qty' => $_POST[ 'qty' ], 'product' => $prod );
		
	}elseif(isset($_POST['qtys'])){
		$qtys = $_POST['qtys'];
		$qtys = explode('|', $qtys);
		if(count($qtys) > 0){
			foreach($qtys as $pro){
				$pro = explode(':', $pro);
				preg_match('/quantity\[(.+)\]/', $pro[0], $match);
				$prod = new WC_Product($match[1]);
				$products[] = array('qty'=>$pro[1],'product'=>$prod);
			}
		}
	}
	$prod = null;
	unset($prod);
	
	if( count( $products) > 0 ){
		foreach( $products as $pro ){
			$new_rewards += $swr_settings->get_product_extra_rewards( $pro[ 'product' ], $pro[ 'qty' ] );
			$old_rewards += $swr_settings->get_rewards_amount( $pro[ 'product' ]->get_price() * $pro[ 'qty' ] );
		}
		$ret[ 'swr_new_reward' ] = $swr_settings->format_reward( $new_rewards );
		$ret[ 'swr_old_reward' ] = $swr_settings->format_reward( $old_rewards );

		if( $ret[ 'swr_new_reward' ] > $ret[ 'swr_old_reward' ] ){
			$ret[ 'show_old_reward' ] = 1;
		}
	}
	echo(json_encode($ret));
	die();
}

function swr_show_product_reward(){
	global $post, $product, $woocommerce, $swr_settings;
	
	$no_reward = get_post_meta( $product->id, '_no_reward', true );
	
	if( $no_reward )
		return ;
	
	$amount = $swr_settings->get_rewards_amount( $product->get_price(), true );
	$extra_reward = $swr_settings->get_product_extra_rewards( $product, 1, true, true );
?>
<p itemprop="reward" class="reward">

<?php
	
	$old_css = ( !empty( $extra_reward ) && $extra_reward <= $amount ) ? ' style="display:none;"' : '';
	
	$first_amount = $amount;
	$second_amount = ( !empty( $extra_reward ) && $extra_reward > $amount ) ? $extra_reward : $amount;

	if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
		
		echo( sprintf( __( 'You will receive <del class="swr_old_reward"%s>%s</del> <ins class="swr_new_reward">%s</ins> <a href="%s">%s</a>', 'rewards'), $old_css, $first_amount, $second_amount, get_permalink($swr_settings->rewards_page), $swr_settings->get_title() ) );
		
	}else{
		
		echo( sprintf( __( 'You will receive <del class="swr_old_reward"%s>%s</del> <ins class="swr_new_reward">%s</ins> %s by purchasing this product', 'rewards'), $old_css, $first_amount, $second_amount, $swr_settings->get_title() ) );
		
	}
	
?>
</p>
<?php
}

function swr_get_product_extra_reward($product_id){
	return get_post_meta( $product_id, '_reward', true );
}

function swr_show_use_rewards(){

	global $woocommerce, $swr_settings;
	
	$datas = array();
	
	$tmp_datas = explode('&', isset($_POST['post_data'])?$_POST['post_data']:'');
	
	if( !empty($tmp_datas) && !empty($tmp_datas[0]) ){
	
		foreach($tmp_datas as $da){
		
			$tmp = explode('=', $da);
			$datas[$tmp[0]] = $tmp[1];
			
		}
		
	}
	
	$total = $swr_settings->get_cart_applied_total();
	
	$current_rewards = swr_get_user_current_rewards();
	
	if( is_user_logged_in() && !empty($current_rewards) && $current_rewards>0 && !$swr_settings->specific_product() && $swr_settings->get_min_points() <= $current_rewards && is_checkout() && $swr_settings->can_applied_one_table() ){
?>

<h3 id="redeem_rewards"><?php echo( $swr_settings->get_title() ); ?></h3>

<p class="<?php echo((WOOCOMMERCE_VERSION >= 2)?'woocommerce-info':'woocommerce_info') ?> swr_use_rewards">

	<?php if( $swr_settings->get_rewards_used_type() == 'pointsvalue' ){ ?>
	
	<input type="checkbox" name="swr_use_rewards" id="swr_use_rewards" value="1"<?= (isset($datas['swr_use_rewards']) && $datas['swr_use_rewards']) || $swr_settings->auto_apply_rewards()?' checked="checked"':'' ?> /> <label for="swr_use_rewards"><?php echo(sprintf(__('Apply my %s %s to this order.', 'rewards'), $current_rewards, $swr_settings->get_title())); ?></label>
	
	<?php }else if( $swr_settings->get_rewards_used_type() == 'table' ){ ?>
	
	<?php if( count( $swr_settings->table_values ) > 0 ){ ?>
	
	<?php foreach( $swr_settings->table_values as $key => $value ){ if( ( $current_rewards < $value['points_required'] ) || ( $total < $value['points_values'] ) ) continue; ?>
	
	<input type="checkbox" name="swr_use_rewards" id="swr_use_rewards_<?php echo($key); ?>" value="<?php echo( $value['points_required'] ); ?>"<?= (isset($datas['swr_use_rewards']) && $datas['swr_use_rewards'] && $datas['swr_use_rewards'] == $value['points_required'])?' checked="checked"':'' ?> /> <label for="swr_use_rewards_<?php echo($key); ?>"><?php echo(sprintf(__('Apply %s %s for a discount of %s to this order.', 'rewards'), $value['points_required'], $swr_settings->get_title(), woocommerce_price( $value['points_values'] ))); ?></label><?php if( count( ( $key + 1 ) < $swr_settings->table_values ) ){ ?><br /><?php } ?>
	
	<?php } ?>
	
	<?php } ?>
	
	<?php } ?>

</p>

<?php
	}
	
}

function swr_before_calculate_totals( $post_data ){
	global $swr_settings;

	$_SESSION['rewards_used'] = 0;
	$_SESSION['use_rewards'] = $swr_settings->auto_apply_rewards() ? 1 : 0;
	
	if( strpos($post_data, 'swr_use_rewards') !== false ){
		
		$_SESSION['use_rewards'] = 1;
		
		$datas = array();
	
		$post_data = explode('&', $post_data);
		
		if( !empty($post_data) && !empty($post_data[0]) ){
		
			foreach($post_data as $da){
			
				$tmp = explode('=', $da);
				$datas[$tmp[0]] = $tmp[1];
				
			}
			
		}
		
		$_SESSION['rewards_points'] = $datas['swr_use_rewards'];
		
	}else{
		
		$_SESSION['use_rewards'] = 0;
		
	}
	
}

function swr_before_checkout_process(){
	global $swr_settings;

	$_SESSION['rewards_used'] = 0;
	$_SESSION['use_rewards'] = $swr_settings->auto_apply_rewards() ? 1 : 0;
	$_SESSION['rewards_points'] = $_POST['swr_use_rewards'];
	
	if( isset( $_POST['swr_use_rewards'] ) ){
		
		$_SESSION['use_rewards'] = 1;
		
	}else{
		
		$_SESSION['use_rewards'] = 0;
		
	}
	
}

function swr_calculated_total_before_tax($price, $values, $cart){
	global $woocommerce, $swr_settings;
	
	$current_rewards = swr_get_user_current_rewards();
	
	if((isset($_SESSION['use_rewards']) && $_SESSION['use_rewards'] == 1) && is_user_logged_in() && !empty($current_rewards) && ($swr_settings->get_rewards_type() == 'money' || ($swr_settings->get_rewards_type() == 'points' && $swr_settings->get_min_points() <= $current_rewards))){
		
		if( $swr_settings->get_rewards_used_type() == 'table' ){
		
			$points = $_SESSION['rewards_points'];
		
			foreach( $swr_settings->table_values as $key => $value ){
				
				if( $value['points_required'] == $points ){
					$current_rewards = $value['points_values'];
					break;
				}
				
			}
			
		}else{
			
			$current_rewards = swr_get_user_current_rewards(array(
				'convert_to_money' => true
			));
			
		}
		
		if ( $cart->subtotal_ex_tax )
			$discount_percent = ( $values['data']->get_price_excluding_tax() * $values['quantity'] ) / $cart->subtotal_ex_tax;
		else
			$discount_percent = 0;
			
		// Use pence to help prevent rounding errors
		$coupon_amount_pence = $current_rewards * 100;

		// Work out the discount for the row
		$item_discount = $coupon_amount_pence * $discount_percent;

		// Work out discount per item
		$item_discount = $item_discount / $values['quantity'];

		// Pence
		$price = $price * 100;
		
		// Check if discount is more than price
		if ( $price < $item_discount )
			$discount_amount = $price;
		else
			$discount_amount = $item_discount;

		// Take discount off of price (in pence)
		$price = $price - $discount_amount;

		// Back to pounds
		$price = $price / 100;

		// Cannot be below 0
		if ( $price < 0 )
			$price = 0;
		
		
		$_SESSION['rewards_used'] += round( ( $discount_amount * $values['quantity'] ) / 100, 2 );
		$cart->discount_cart += ( ( $discount_amount * $values['quantity'] ) / 100 );
		$swr_settings->discount_applied = true;
		
	}
	return $price;
}

function swr_get_discount_data( $data, $code ){
	if ( strtolower( $code ) != swr_get_discount_code() ) {
		return $data;
	}

	// note: we make our points discount "greedy" so as many points as possible are
	//   applied to the order.  However we also want to play nice with other discounts
	//   so if another coupon is applied we want to use less points than otherwise.
	//   The solution is to make this discount apply post-tax so that both pre-tax
	//   and post-tax discounts can be considered.  At the same time we use the cart
	//   subtotal excluding tax to calculate the maximum points discount, so it
	//   functions like a pre-tax discount in that sense.
	$data = array(
		'id'                         => true,
		'type'                       => 'fixed_cart',
		'amount'                     => 0,
		'coupon_amount'              => 0, // 2.2
		'individual_use'             => 'no',
		'product_ids'                => '',
		'exclude_product_ids'        => '',
		'usage_limit'                => '',
		'usage_count'                => '',
		'expiry_date'                => '',
		'apply_before_tax'           => 'yes',
		'free_shipping'              => 'no',
		'product_categories'         => array(),
		'exclude_product_categories' => array(),
		'exclude_sale_items'         => 'no',
		'minimum_amount'             => '',
		'maximum_amount'             => '',
		'customer_email'             => ''
	);

	return $data;
}

function swr_get_discount_amount( $discount, $discounting_amount, $cart_item, $single, $coupon ){
	
	global $swr_settings, $woocommerce;
	
	if ( strtolower( $coupon->code ) != swr_get_discount_code() ) {
		return $discount;
	}
	
	$discount_percent = 0;

	if ( WC()->cart->subtotal_ex_tax ) {
		$discount_percent = ( $cart_item['data']->get_price_excluding_tax() * $cart_item['quantity'] ) / WC()->cart->subtotal_ex_tax;
	}
	
	$current_rewards = swr_get_user_current_rewards();
	$total_discount = 0;
	
	if( $swr_settings->get_rewards_used_type() == 'table' ){
		
		$points = $_SESSION['rewards_points'];
	
		foreach( $swr_settings->table_values as $key => $value ){
			
			if( $value['points_required'] == $points ){
				$current_rewards = $value['points_values'];
				break;
			}
			
		}
		
	}else{
		
		$current_rewards = swr_get_user_current_rewards(array(
			'convert_to_money' => true
		));

	}
	
	$discount = version_compare($woocommerce->version, '2.3.0') >= 0 ? WC()->cart->get_cart_discount_total() : WC()->cart->discount_total;
	
	$s_total = (WC()->cart->cart_contents_total + WC()->cart->tax_total + ($swr_settings->swr_apply_rewards_to_shipping ? WC()->cart->shipping_tax_total + WC()->cart->shipping_total : 0 ));
	
	$s_total = WC()->cart->subtotal_ex_tax;
	
	if( $s_total - $discount > 0 ){
	
		if( $current_rewards > $s_total - $discount ){
			$_SESSION['rewards_used'] = $s_total - $discount;
			$total_discount += $s_total - $discount;
			//$cart->total = 0;
		}else{
			$_SESSION['rewards_used'] = $current_rewards;
			$total_discount = $current_rewards;
			//$cart->total -= $discount;
		}
		
	}

	$total_discount = min( ( $total_discount * $discount_percent ) / $cart_item['quantity'], $discounting_amount );

	return $total_discount;
	
}

function swr_get_discount_code() {
	if ( WC()->session !== null ) {
		return WC()->session->get( 'swr_points_rewards_discount_code' );
	}
}

function swr_generate_discount_code() {
	// set the discount code to the current user ID + the current time in YYYY_MM_DD_H_M format
	$discount_code = sprintf( 'swr_points_redemption_%s_%s', get_current_user_id(), date( 'Y_m_d_h_i', current_time( 'timestamp' ) ) );

	WC()->session->set( 'swr_points_rewards_discount_code', $discount_code );

	return $discount_code;
}

function swr_calculated_total( $cart ){
	global $woocommerce, $swr_settings;
	
	$current_rewards = swr_get_user_current_rewards();
	
	if((isset($_SESSION['use_rewards']) && $_SESSION['use_rewards'] == 1) && is_user_logged_in() && !empty($current_rewards) && ($swr_settings->get_rewards_type() == 'money' || ($swr_settings->get_rewards_type() == 'points' && $swr_settings->get_min_points() <= $current_rewards)) && ! WC()->cart->has_discount( swr_get_discount_code() )){
		// generate and set unique discount code
		$discount_code = swr_generate_discount_code();
	
		// apply the discount
		WC()->cart->add_discount( $discount_code );
	}else if( WC()->cart->has_discount( swr_get_discount_code() ) && isset( $_SESSION['use_rewards'] ) && $_SESSION['use_rewards'] == 0 ){
		
		WC()->cart->remove_coupon( swr_get_discount_code() );
		
	}
	
	return false;
	
}

function swr_review_order_before_order_total(){
	
	global $swr_settings;
	
	if( isset( $_SESSION[ 'rewards_used' ] ) && ! empty( $_SESSION[ 'rewards_used' ] ) && false ){
		
		?>
		<tr class="cart-discount coupon-rewards">
			<th><?php echo  $swr_settings->get_title(); ?></th>
			<td><?php echo wc_price( '-' . $_SESSION[ 'rewards_used' ] ); ?></td>
		</tr>
		<?php
		
	}
	
}

function swr_update_order_meta($order_id, $extra = true){
	global $woocommerce, $swr_settings;
	$order = new WC_Order($order_id);
	if($order->user_id > 0){
		$current_rewards = swr_get_user_current_rewards(array(
			'user_id' => $order->user_id,
			'convert_to_money' => true
		));
		$current_rewards_non = swr_get_user_current_rewards(array(
			'user_id' => $order->user_id
		));
		if(isset($_POST['swr_use_rewards']) && $_POST['swr_use_rewards'] && $current_rewards > 0){
			$use_rewards = $_SESSION['rewards_used'];
			if($swr_settings->get_rewards_type() == 'points'){
			
				switch( $swr_settings->get_rewards_used_type() ){
					
					case 'pointsvalue':
						$use_rewards = $swr_settings->convert_rewards('money', 'points', $use_rewards, $swr_settings->get_rewards_calculation());
						break;
						
					case 'table':
						$use_rewards = $swr_settings->get_table_based_points( $use_rewards );
						break;
					
				}
			
			}
			$swr_settings->set_user_rewards($order->user_id, $current_rewards_non-$use_rewards);
			$swr_settings->set_reward_used_for_order($order_id, $use_rewards);
			$reward = str_replace('</span>','',str_replace('<span class="amount">', '', $swr_settings->format_reward($current_rewards, true)));
			$order->add_order_note(sprintf(__('Customer applied %s %s to this order.', 'rewards'), $use_rewards, $swr_settings->get_title()));
		}
		$swr_settings->set_reward_earned_for_order($order_id, $swr_settings->get_reward_from_order($order_id, false, $extra));
		$swr_settings->set_reward_status_for_order($order_id, 0);
	}
}

function swr_thankyou_rewards($order_id){
	global $woocommerce, $sitepress, $swr_settings;
	
	$order = new WC_Order($order_id);
	$my_account_page_id = get_option('woocommerce_myaccount_page_id');
	if(function_exists('icl_object_id')){
		$my_account_page_id = icl_object_id($my_account_page_id, 'page', false, $sitepress->get_current_language());
	}
	
	if( $order->rewards_earned <= 0 )
		return ;
	
	if( is_user_logged_in() ){
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
			
			$msg = sprintf(__('You have earned %s <a href="%s">%s</a> for this order', 'rewards'), $swr_settings->format_reward($order->rewards_earned, true), get_permalink($swr_settings->rewards_page), $swr_settings->get_title());
			
		}else{
			
			$msg = sprintf(__('You have earned %s %s for this order', 'rewards'), $swr_settings->format_reward($order->rewards_earned, true), $swr_settings->get_title());
			
		}
	
	}else{
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
		
			$msg = sprintf(__('You\'ve missed a great chance to earn %s <a href="%s">%s</a> for this order.', 'rewards'), $swr_settings->format_reward($order->rewards_earned, true), get_permalink($swr_settings->rewards_page), $swr_settings->get_title());
		
		}else{
			
			$msg = sprintf(__('You\'ve missed a great chance to earn %s %s for this order.', 'rewards'), $swr_settings->format_reward($order->rewards_earned, true), $swr_settings->get_title());
			
		}
		
	}
?>
<p class="<?php echo((WOOCOMMERCE_VERSION >= 2)?'woocommerce-info':'woocommerce_info') ?> swr_get_rewards"><?php echo($msg); ?></p>
<?php
}


function swr_update_reward(){
	global $woocommerce, $swr_settings;
	$msg = swr_get_text_rewards();
	if($swr_settings->is_enabled()){
		echo('<div class="updated_rewards" style="display:none;">'.$msg.'</div>');
	}
}


function swr_get_text_rewards(){
	global $woocommerce, $sitepress, $swr_settings;
	$my_account_page_id = get_option('woocommerce_myaccount_page_id');
	if(function_exists('icl_object_id')){
		$my_account_page_id = icl_object_id($my_account_page_id, 'page', false, $sitepress->get_current_language());
	}
	$datas = array();
	$tmp_datas = explode('&', isset($_POST['post_data'])?$_POST['post_data']:'');
	if(!empty($tmp_datas) && !empty($tmp_datas[0])){
		foreach($tmp_datas as $da){
			$tmp = explode('=', $da);
			$datas[$tmp[0]] = $tmp[1];
		}
	}
	
	$rewards_to_be_earned = $swr_settings->get_cart_reward();
	if( is_user_logged_in() || ( isset($datas['createaccount']) && $datas['createaccount']) ){
	
		$text = $swr_settings->get_no_rewards_text();
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
			
			if( $rewards_to_be_earned <= 0 && ! empty( $text ) ){
				
				$msg = sprintf(__('<a href="%s">%s</a>', 'rewards'), get_permalink($swr_settings->rewards_page), $text);
				
			}else{
				
				$msg = sprintf(__('You\'ll earn %s <a href="%s">%s</a> for this order', 'rewards'), $swr_settings->get_cart_reward(), get_permalink($swr_settings->rewards_page), $swr_settings->get_title());
				
			}
			
		}else{
			
			if( $rewards_to_be_earned <= 0 && ! empty( $text ) ){
				
				$msg = $text;
				
			}else{
				
				$msg = sprintf(__('You\'ll earn %s %s for this order', 'rewards'), $swr_settings->get_cart_reward(), $swr_settings->get_title());
				
			}
			
		}
	
	}else{
	
		if( isset($swr_settings->rewards_page) && !empty($swr_settings->rewards_page) ){
			
			if( $rewards_to_be_earned <= 0 && ! empty( $text ) ){
				
				$msg = sprintf(__('<a href="%s">%s</a>', 'rewards'), get_permalink($swr_settings->rewards_page), $text);
				
			}else{
				
				$msg = sprintf(__('<a href="%s">Create an account</a> and earn %s <a href="%s">%s</a> for this order', 'rewards'), get_permalink($my_account_page_id), $swr_settings->get_cart_reward(), get_permalink($swr_settings->rewards_page), $swr_settings->get_title());
				
			}
		
		}else{
			
			if( $rewards_to_be_earned <= 0 && ! empty( $text ) ){
				
				$msg = sprintf(__('<a href="%s">%s</a>', 'rewards'), get_permalink($my_account_page_id), $text);
				
			}else{
				
				$msg = sprintf(__('<a href="%s">Create an account</a> and earn %s %s for this order', 'rewards'), get_permalink($my_account_page_id), $swr_settings->get_cart_reward(), $swr_settings->get_title());
				
			}
			
		}
		
	}
	
	return $msg;
}

function swr_order_data($load_data){
	$load_data['rewards_earned'] = '';
	$load_data['rewards_completed'] = '';
	$load_data['rewards_used'] = '';
	return $load_data;
}
add_filter('woocommerce_load_order_data', 'swr_order_data');

function swr_add_reward( $order_id ){

	global $woocommerce, $swr_settings;
	
	$order = new WC_Order( $order_id );
	
	if( ! $order->rewards_completed && $order->user_id > 0 && $order->rewards_earned > 0 ){
	
		$swr_settings->set_reward_status_for_order( $order_id, 1 );
		
		$current_rewards = swr_get_user_current_rewards( array(
			'user_id' => $order->user_id
		) );
		
		if( ! $current_rewards )
			$current_rewards = 0;
		
		$current_rewards += $order->rewards_earned;
		
		update_user_meta( $order->user_id, 'swr_rewards', $current_rewards );
		
		$reward = swr_clean_amount( $swr_settings->format_reward( $order->rewards_earned, true ) );
		
		$order->add_order_note( sprintf( __( 'Customer earned %s %s.','rewards'), $reward, $swr_settings->get_title() ) );
		
	}
}

function swr_remove_reward($order_id){
	global $woocommerce, $swr_settings;
	$order = new WC_Order($order_id);
	if($order->rewards_completed && $order->user_id > 0){
		$swr_settings->set_reward_status_for_order($order_id, 0);
		$current_rewards = swr_get_user_current_rewards(array(
			'user_id' => $order->user_id
		));
		if(!$current_rewards)
			$current_rewards = 0;
		$current_rewards -= $order->rewards_earned;
		update_user_meta($order->user_id, 'swr_rewards', $current_rewards);
		$reward = swr_clean_amount($swr_settings->format_reward($order->rewards_earned, true));
		$order->add_order_note(sprintf(__('Removed %s %s.','rewards'), $reward, $swr_settings->get_title()));
	}
}

function swr_give_back_rewards($order_id){
	global $swr_settings;
	$swr_settings->give_back_rewards_for_order($order_id);
}

function swr_clean_amount($amount){
	return str_replace( '</span>', '', str_replace( '<span class="amount">', '', $amount ) );
}

function swr_update_cart_reward(){
	global $woocommerce;
	check_ajax_referer( 'update-shipping-method', 'security' );
	
	if ( ! defined('WOOCOMMERCE_CART') ) define( 'WOOCOMMERCE_CART', true );
	
	if ( isset( $_POST['shipping_method'] ) ) $_SESSION['_chosen_shipping_method'] = $_POST['shipping_method'];
	$woocommerce->cart->calculate_totals();
	$msg = swr_get_text_rewards();
	echo('<span class="updated_rewards" style="display:none;">'.$msg.'</span>');
}
add_action('wp_ajax_woocommerce_update_shipping_method', 'swr_update_cart_reward');
add_action('wp_ajax_nopriv_woocommerce_update_shipping_method', 'swr_update_cart_reward');
 
function swr_rewards_tab_filter($tabs){
	$tabs['rewards'] = 'Rewards';
	return $tabs;
}

function swr_rewards(){
	global $woocommerce;
	
	$rewards = $woocommerce->rewards->get_rewards();
	
	if (version_compare($woocommerce->version, '2.1.0') >= 0 && false) {
		
		foreach( $rewards as $reward ){
			
			$reward->output_sections();
			
		}
		
	}else{				
		$section = empty( $_GET['section'] ) ? key( $rewards ) : urldecode( $_GET['section'] );
		
		foreach ( $rewards as $reward ) {
			$title = ( isset( $reward->method_title ) && $reward->method_title) ? ucwords( $reward->method_title ) : ucwords( $method->id );
			$current = ( $reward->get_id() == $section ) ? 'class="current"' : '';
			
			$links[] = '<a href="' . esc_url( add_query_arg( 'section', $reward->get_id(), admin_url('admin.php?page=woocommerce&tab=rewards') ) ) . '"' . $current . '>' . $title . '</a>';
		}
		
		echo '<ul class="subsubsub"><li>' . implode(' | </li><li>', $links) . '</li></ul><br class="clear" />';
		
		if ( isset( $rewards[ $section ] ) )
			$rewards[ $section ]->admin_options();
		}
	
}
add_action('woocommerce_settings_tabs_rewards', 'swr_rewards');

function get_swr_cart_amount(){
	global $woocommerce, $swr_settings;
	$woocommerce->cart->calculate_totals();
	return '<span class="swr_cart_shortcode_amount">' . $swr_settings->get_cart_reward() . '</span>';
}

function get_swr_rewards_amount(){
	return '<span class="swr_cart_shortcode_amount">' . swr_get_user_current_rewards( array( 'formatted' => true ) ) . '</span>';
}

function get_swr_product_rewards(){
	
	ob_start();
	
	$content = swr_show_product_reward();
	
	return ob_get_clean();
}

function get_swr_view_rewards( $atts ){

	global $woocommerce;
	if (version_compare($woocommerce->version, '2.1.0') >= 0) {
		return WC_Shortcodes::shortcode_wrapper( 'get_swr_wrapper_view_rewards', $atts );
	}else{
		return $woocommerce->shortcode_wrapper( 'get_swr_wrapper_view_rewards', $atts );
	}
}

function get_swr_wrapper_view_rewards(){

	if(is_user_logged_in()){
	
		$recent_orders = 10;
		
		include(SWR_PATH.'/templates/my-rewards.php');
		
	}else{
	
		woocommerce_get_template('myaccount/form-login.php');
		
	}
	
}

function swr_check_actions(){
	global $swr_settings;
	switch($swr_settings->settings['swr_use_rewards_where_to_show']){
		case 'before':
		default:
			$action = 'woocommerce_checkout_after_customer_details';
			break;
		case 'top':
			$action = 'woocommerce_checkout_before_customer_details';
			break;
		case 'after':
			$action = 'woocommerce_checkout_order_review';
			break;
	}
	if(did_action($action)===0){
	?>
	<div id="use_my_rewards_container" style="display:none;"><?php swr_show_use_rewards(); ?></div>
	<script type="text/javascript">
		var use_my_rewards_location = "<?= $swr_settings->settings['swr_use_rewards_where_to_show'] ?>";
		jQuery(function($){
			switch(use_my_rewards_location){
				case 'before':
				default:
					if($("#order_review_heading").length > 0){
						$("#order_review_heading").before($(".swr_use_rewards"));
					}else if($("#customer_details").length > 0){
						$("#customer_details").after($(".swr_use_rewards"));
					}else{
						$("form.checkout").prepend($(".swr_use_rewards"));
					}
					break;
				case 'top':
					$("form.checkout").prepend($(".swr_use_rewards"));
					break;
			}
			$("#use_my_rewards_container").remove();
		});
	</script>
	<?php
	}
}

function swr_profile_rewards($user){
	global $woocommerce, $swr_settings;
	$rewards = swr_get_user_current_rewards(array(
		'user_id' => $user->ID
	));
?>
	<h3><?php _e('Rewards', 'rewards'); ?></h3>
	<table class="form-table">
		<tr>
			<th>
				<label for="gateways"><?php echo(sprintf(__('Current %s earned', 'rewards'), $swr_settings->get_title())); ?></label>
			</th>
			<td>
				<input type="text" name="swr_rewards" id="swr_rewards" value="<?php echo($rewards); ?>" class="regular-text">
			</td>
		</tr>
	</table>
	<?php
	global $woocommerce, $recent_orders, $swr_settings;

/* $swr_settings = $woocommerce->rewards->rewards['rewards_settings']; */

$customer_id = $user->ID;

$args = array(
    'numberposts'     => $recent_orders,
    'meta_key'        => '_customer_user',
    'meta_value'	  => $customer_id,
    'post_type'       => 'shop_order',
    'post_status'     => 'publish' 
);


$customer_orders = get_posts($args);

$msg = sprintf(__('Current %s balance: %s', 'rewards'), $swr_settings->get_title(), swr_get_user_current_rewards(array('user_id'=>$customer_id, 'formatted'=>true)));

if ($customer_orders) :
?>
	<h4><?php _e("Orders", "rewards"); ?></h4>
	<table class="shop_table my_account_orders" style="width:50%;">
	
		<thead>
			<tr>
				<th class="order-number" style="width:35%;"><span class="nobr"><?php _e('Order', 'rewards'); ?></span></th>
				<th class="order-total rewards-earned" style="width:20%;"><span class="nobr"><?php _e('Total earned', 'rewards'); ?></span></th>
				<th class="order-total rewards-used" style="width:20%;"><span class="nobr"><?php _e('Total Used', 'rewards'); ?></span></th>
				<th class="order-total rewards-status" style="width:25%;"><span class="nobr"><?php _e('Status', 'rewards'); ?></span></th>
			</tr>
		</thead>
		
		<tbody><?php
			foreach ($customer_orders as $customer_order) :
				$order = new WC_Order();
				
				$order->populate( $customer_order );
				
				$status = get_term_by('slug', $order->status, 'shop_order_status');
				
				?><tr class="order">
					<td class="order-number" width="1%">
						<a href="<?php echo( admin_url( 'post.php?post=' . $order->id . '&action=edit' ) ); ?>"><?php echo $order->get_order_number(); ?></a> &ndash; <time title="<?php echo esc_attr( strtotime($order->order_date) ); ?>"><?php echo date_i18n(get_option('date_format'), strtotime($order->order_date)); ?></time>
					</td>
					<td class="order-total rewards-earned" width="1%"><?php echo($swr_settings->format_reward($order->rewards_earned)); ?></td>
					<td class="order-total rewards-used" width="1%"><?php echo($swr_settings->format_reward($order->rewards_used)); ?></td>
					<td class="order-total rewards-status" width="1%"><?php _e($status->name, 'woocommerce'); ?></td>
				</tr><?php
			endforeach;
		?></tbody>
	
	</table>
<?php
else :
?>
	<p><?php _e('You have no recent orders.', 'rewards'); ?></p>
<?php
endif;

if($swr_settings->review_enabled()):
$comments = array();
$tmp_comments = get_comments(array(
	'user_id' => $customer_id
));
if($tmp_comments && count($tmp_comments) > 0){
	foreach($tmp_comments as $tmp_comment){
		$met = get_comment_meta($tmp_comment->comment_ID, 'rewards_earned', true);
		if($met){
			$comments[] = array(
				'item_id' => $tmp_comment->comment_post_ID,
				'date' => $tmp_comment->comment_date,
				'reward' => $met,
				'status' => wp_get_comment_status($tmp_comment->comment_ID)
			);
		}
	}
}
if($comments && count($comments) > 0):
?>
<h2><?php _e("Reviews", "rewards"); ?></h2>
	<table class="shop_table my_account_orders">
	
		<thead>
			<tr>
				<th class="order-number" style="width:35%;"><span class="nobr"><?php _e('Date', 'rewards'); ?></span></th>
				<th class="order-total rewards-product" style="width:20%;"><span class="nobr"><?php _e('Product', 'rewards'); ?></span></th>
				<th class="order-total rewards-earned" style="width:20%;"><span class="nobr"><?php _e('Total earned', 'rewards'); ?></span></th>
				<th class="order-total rewards-status" style="width:25%;"><span class="nobr"><?php _e('Status', 'rewards'); ?></span></th>
			</tr>
		</thead>
		
		<tbody><?php
			foreach ($comments as $comment) :
				?><tr class="comment">
					<td class="order-number"><?php echo date_i18n(get_option('date_format'), strtotime($comment['date'])); ?></td>
					<td class="order-total rewards-product"><?php echo(get_the_title($comment['item_id'])); ?></td>
					<td class="order-total rewards-earned"><?php echo($comment['reward']); ?></td>
					<td class="order-total rewards-status"><?php _ex(ucfirst($comment['status']), 'adjective'); ?></td>
				</tr><?php
			endforeach;
		?></tbody>
	
	</table>
<?php else : ?>
	<p><?php _e('You have to review an item first', 'rewards'); ?></p>
<?php endif; ?>
<?php
	endif;
?>
<?php
}

function swr_profile_save_rewards($user_id){
	update_user_meta($user_id, 'swr_rewards', $_POST['swr_rewards']);
}

function swr_add_shortcode_button() {
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) return;
	if ( get_user_option('rich_editing') == 'true') :
		add_filter('mce_external_plugins', 'swr_add_shortcode_tinymce_plugin');
		add_filter('mce_buttons', 'swr_register_shortcode_button');
	endif;
}
add_action('init', 'swr_add_shortcode_button', 12);

function swr_register_shortcode_button($buttons) {
	array_push($buttons, "swr_shortcodes_button");
	return $buttons;
}

function swr_add_shortcode_tinymce_plugin($plugin_array) {
	$plugin_array['SWRShortcodes'] = plugins_url('synmedia-woocommerce-rewards/assets/js/admin.editor_plugin.js');
	return $plugin_array;
}

function swr_email_header(){
	global $SWR_IS_EMAIL;
	$SWR_IS_EMAIL = true;
}

function swr_email_footer(){
	global $SWR_IS_EMAIL;
	$SWR_IS_EMAIL = false;
}

function swr_get_order_item_totals($total_rows, $order){
	global $woocommerce, $swr_settings, $SWR_IS_EMAIL;
	if(isset($SWR_IS_EMAIL) && $SWR_IS_EMAIL){
		$rewards_earned = $swr_settings->get_reward_earned_for_order($order->id);
		$rewards_used = $swr_settings->get_reward_used_for_order($order->id);
		if($rewards_earned > 0){
			$total_rows['rewards_earned'] = array(
				'label' => sprintf(__( '%s earned:', 'rewards' ), $swr_settings->get_title()),
				'value'	=> $rewards_earned
			);
		}
		if($rewards_used > 0){
			$total_rows['rewards_used'] = array(
				'label' => sprintf(__( '%s used:', 'rewards' ), $swr_settings->get_title()),
				'value'	=> $rewards_used
			);
		}
	}
	return $total_rows;
}

function swr_add_comment_rating($comment_id){
	global $woocommerce, $swr_settings;
	if ( isset($_POST['rating']) ) :
		global $post;
		if ( ! $_POST['rating'] || $_POST['rating'] > 5 || $_POST['rating'] < 0 ) return;
		$comment = get_comment($comment_id);
		$already_commented = false;
		$comments = get_comments(array(
			'post_id' => $comment->comment_post_ID,
			'user_id' => $comment->user_id
		));
		if($comments && count($comments) > 0){
			foreach($comments as $tmp_comment){
				$met = get_comment_meta($tmp_comment->comment_ID, 'rewards_earned', true);
				if($met)
					$already_commented = true;
			}
		}
		
		if(swr_user_bought_item($comment->user_id, $comment->comment_post_ID) && !$already_commented){
		
			add_comment_meta($comment_id, 'rewards_earned', $swr_settings->get_review_reward(), true);
			if($comment->comment_approved){
				$current_rewards = $swr_settings->get_user_rewards(array(
					'user_id' => $comment->user_id
				));
				$current_rewards += $swr_settings->get_review_reward();
				update_user_meta($comment->user_id, 'swr_rewards', $current_rewards);
			}
			
		}
	endif;
}

function swr_comment_status_changed($new_status, $old_status, $comment){
	global $swr_settings;
	$reward = get_comment_meta($comment->comment_ID, 'rewards_earned', true);
	switch($new_status){
		case 'approved':
			$current_rewards = $swr_settings->get_user_rewards(array(
				'user_id' => $comment->user_id
			));
			$current_rewards += $reward;
			update_user_meta($comment->user_id, 'swr_rewards', $current_rewards);
			break;
		default:
			if($old_status == 'approved'){
				$current_rewards = $swr_settings->get_user_rewards(array(
					'user_id' => $comment->user_id
				));
				$current_rewards -= $reward;
				update_user_meta($comment->user_id, 'swr_rewards', $current_rewards);
			}
			break;
	}
}
add_action("transition_comment_status", "swr_comment_status_changed", 10, 3);

/* Validate if the user has bought this item or not */
function swr_user_bought_item($user_id = 0, $item_id = 0){
	global $wpdb;
	$has_bought = false;
	if($user_id > 0 && $item_id > 0){
		$orders = get_posts(array(
			'post_type' => 'shop_order',
			'meta_key' => '_customer_user',
			'meta_value' => $user_id
		));
		if($orders && count($orders) > 0){
			foreach($orders as $order){
				$order = new WC_Order($order->ID);
			 	if ( sizeof( $order->get_items() ) > 0 ) {
			 	
					foreach( $order->get_items() as $item ) {
					
						if( (isset($item['id']) && $item['id'] == $item_id) || (isset($item['product_id']) && $item['product_id'] == $item_id ) ){
							
							$has_bought = true;
							break;
							
						}
						
					}
				}
				if($has_bought)
					break;
			}
		}
	}
	
	return $has_bought;
}



/**
 * Queue admin menu icons CSS
 * 
 */
function swr_admin_menu_styles() {
	global $woocommerce;
	wp_enqueue_style('swr_admin_menu_styles', plugins_url('synmedia-woocommerce-rewards/assets/css/swr.admin.css'));
}
add_action('admin_print_styles', 'swr_admin_menu_styles');

function swr_meta_boxes($post_id){
	$data = get_post_custom($post_id);
?>
		<h4><?php _e('Rewards', 'rewards'); ?></h4>
		<ul class="totals">
			
			<li class="left">
				<label><?php _e('Earned:', 'rewards'); ?></label>
				<input type="text" id="_rewards_earned" name="_rewards_earned" placeholder="0.00" value="<?php if (isset($data['_rewards_earned'][0])) echo $data['_rewards_earned'][0];
				?>" class="first rewards" />
			</li>
			
			<li class="right">
				<label><?php _e('Used:', 'rewards'); ?></label>
				<input type="text" name="_rewards_used" id="_rewards_used" value="<?php 
				if (isset($data['_rewards_used'][0])) echo $data['_rewards_used'][0];
				?>" placeholder="0.00" class="rewards" />
			</li>
	
		</ul>
		<div style="display:none;"><span class="calc_rewards_span">&nbsp;<button type="button" class="button calc_rewards"><?php _e('Calc rewards &rarr;', 'rewards'); ?></button></span></div>
		<style>
			.calc_rewards{
				margin-left: 5px;
			}
		</style>
<?php
}
/* add_action( 'woocommerce_admin_order_totals_after_shipping', 'swr_meta_boxes' ); */

function swr_process_shop_order_meta($order_id, $post){
	global $swr_settings;
	$swr_settings->set_reward_earned_for_order($order_id, stripslashes($_POST['_rewards_earned']));
	$swr_settings->set_reward_used_for_order($order_id, stripslashes($_POST['_rewards_used']));
	$completed = $swr_settings->get_reward_status_for_order($order_id);
	if($completed){
		
	}
}
/* add_action('woocommerce_process_shop_order_meta', 'swr_process_shop_order_meta', 1, 2); */

function swr_product_options_pricing(){
	global $swr_settings, $woocommerce;
	
	echo '</div>';
	echo '<div class="options_group pricing show_if_simple show_if_external show_if_variable">';
	
	if($swr_settings->specific_product()){
		woocommerce_wp_text_input(array( 'id' => '_reward_price', 'class' => 'wc_input_price short', 'label' => sprintf(__('Required %s', 'rewards'), $swr_settings->get_title())));
	}
	
	woocommerce_wp_text_input(array( 'id' => '_reward', 'class' => 'short', 'label' => sprintf(__('Extra %s', 'rewards'), $swr_settings->get_title()), 'desc_tip' => 'true', 'description' => __( 'Can be a positive or negative value depending on what you want to achieve.', 'rewards' )));
	
	woocommerce_wp_checkbox( array(
		'id'			=> '_no_reward',
		'class'			=> 'checkbox',
		'label'			=> __('No reward', 'rewards'),
		'cbvalue'		=> 1,
		'desc_tip'		=> 'true',
		'description'	=> sprintf( __( 'By enabling this the customer will not get %s', 'rewards' ), $swr_settings->get_title() )
	));
}

function swr_process_product_meta($post_id, $post){
	if( isset( $_POST['_reward'] ) )
		update_post_meta($post_id, '_reward', stripslashes( $_POST['_reward'] ));
		
	if( isset( $_POST['_no_reward'] ) ){
		update_post_meta($post_id, '_no_reward', isset( $_POST['_no_reward'] ) );
	}else{
		delete_post_meta($post_id, '_no_reward' );
	}
		
	if( isset( $_POST['_reward_price'] ) )
		update_post_meta($post_id, '_reward_price', stripslashes( $_POST['_reward_price'] ));
		
	if( isset( $_POST['_exact_reward'] ) )
		update_post_meta($post_id, '_exact_reward', stripslashes( $_POST['_exact_reward'] ));
}

function swr_get_price_html($price, $product = false){
	global $swr_settings, $woocommerce;
	
	if( ! $product )
		return $price;
	
	$reward_price = get_post_meta($product->id, '_reward_price', true);
	if(!empty($reward_price)){
		$price .= sprintf(__(' or %s %s', 'rewards'), $reward_price, $swr_settings->get_title());
	}
	return $price;
}

function swr_after_add_to_cart_button(){
	global $swr_settings;
	echo('<div style="display:none;" id="buywithpoints_container"><button type="submit" class="button alt buywithpoints">'.sprintf(__('Buy with %s','rewards'), $swr_settings->get_title()).'</button></div>');
}

function swr_product_after_variable_attributes($loop, $variation_data){
	global $swr_settings;
?>
<tr>
	<td><label><?php echo(sprintf(__('Extra %s', 'rewards'), $swr_settings->get_title())); ?><img class="help_tip" data-tip="<?php _e( 'Can be a positive or negative value depending on what you want to achieve.', 'rewards' ); ?>" src="<?php echo( esc_url( WC()->plugin_url() ) )?>/assets/images/help.png" height="16" width="16" /></label><input type="text" size="5" name="extra_rewards[<?php echo $loop; ?>]" value="<?php if (isset($variation_data['_reward'][0])) echo $variation_data['_reward'][0]; ?>" /></td>

	<td>
	<?php if($swr_settings->specific_product()): ?>
	<label><?php echo(sprintf(__('Required %s', 'rewards'), $swr_settings->get_title())); ?></label><input type="text" size="5" name="reward_price[<?php echo $loop; ?>]" value="<?php if (isset($variation_data['_reward_price'][0])) echo $variation_data['_reward_price'][0]; ?>" />
	<?php endif; ?>
	</td>
</tr>
<?php
}

function swr_process_product_meta_variable( $post_id ) {
	global $woocommerce, $wpdb; 
	
	if ( isset($_POST['variable_sku']) ) :
	
		$variable_post_id 		= $_POST['variable_post_id'];
		$extra_rewards			= isset( $_POST['extra_rewards'] ) ? $_POST['extra_rewards'] : 0 ;
		$reward_price			= isset( $_POST['reward_price'] ) ? $_POST['reward_price'] : 0 ;
		
		$max_loop = max( array_keys( $_POST['variable_post_id'] ) );
		
		for ( $i=0; $i <= $max_loop; $i++ ) :
			
			if ( ! isset( $variable_post_id[$i] ) ) continue;
			
			$variation_id = (int) $variable_post_id[$i];
			
			update_post_meta( $variation_id, '_reward', $extra_rewards[$i] );
			update_post_meta( $variation_id, '_reward_price', $reward_price[$i] );
		 	
		 endfor; 
		 
	endif;

}
add_action('woocommerce_process_product_meta_variable', 'swr_process_product_meta_variable');

/**
 * Define columns to show on the users page.
 *
 * @access public
 * @param array $columns Columns on the manage users page
 * @return array The modified columns
 */
function swr_user_columns( $columns ) {
	if ( ! current_user_can( 'manage_woocommerce' ) )
		return $columns;

	$columns['swr_rewards_balance'] = __('Rewards', 'rewards');
	return $columns;
}

add_filter( 'manage_users_columns', 'swr_user_columns', 10, 1 );

/**
 * Define values for custom columns.
 *
 * @access public
 * @param mixed $value The value of the column being displayed
 * @param mixed $column_name The name of the column being displayed
 * @param mixed $user_id The ID of the user being displayed
 * @return string Value for the column
 */
function swr_user_column_values( $value, $column_name, $user_id ) {
	global $woocommerce, $wpdb, $swr_settings;
	switch ($column_name) :
		case "swr_rewards_balance" :

			$value = swr_get_user_current_rewards(array('user_id'=>$user_id, 'formatted'=>true)).' '.$swr_settings->get_title();

		break;
	endswitch;
	return $value;
}

add_action( 'manage_users_custom_column', 'swr_user_column_values', 10, 3 );

function swr_email_after_order_table( $order, $sent_to_admin, $plain_text ){

	global $swr_settings, $woocommerce;
	
	if ( version_compare( $woocommerce->version, '2.2.0' ) >= 0 ) {
		$status = $order->get_status();
	}else{
		$status = $order->status;
	}
	
	$rewards_earned = $swr_settings->get_reward_earned_for_order( $order->id );
	
	if( $rewards_earned > 0 && $status == 'completed' && ! $sent_to_admin ){
	
		echo( '<p>' . sprintf( __( 'Your %s %s are now available.', 'rewards' ), $rewards_earned, $swr_settings->get_title() ) . '</p>' );
		
	}
	
}

?>