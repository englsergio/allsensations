<?php
	
function swr_settings_scripts(){
	wp_enqueue_script('swr_admin_settings_script', SWR_URL.'/assets/js/admin.settings.js', array('jquery'), '1.0');
}
add_action('swr_rewards_settings_scripts', 'swr_settings_scripts');

function swr_add_settings_integration($rewards) {
	$rewards[] = 'WC_Rewards_Settings';
	return($rewards);
}
add_filter('woocommerce_rewards', 'swr_add_settings_integration');

function swr_settings_create_pages(){
	
	$slug = esc_sql( _x('view-rewards', 'page_slug', 'rewards') );
	$page_title = __('View Rewards', 'rewards');
	$page_content = '[swr_view_rewards]';
	$post_parent = woocommerce_get_page_id('myaccount');
	
	$page_data = array(
        'post_status'       => 'publish',
        'post_type'         => 'page',
        'post_author'       => 1,
        'post_name'         => $slug,
        'post_title'        => $page_title,
        'post_content'      => $page_content,
        'post_parent'       => $post_parent,
        'comment_status'    => 'closed'
    );
    $page_id = wp_insert_post( $page_data );

}
add_action('swr_create_pages', 'swr_settings_create_pages');
	
?>