<?php
/**
 * WooCommerce Shipping Settings
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Rewards' ) ) :

/**
 * WC_Settings_Shipping
 */
class WC_Rewards extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'rewards';
		$this->label = __( 'Rewards', 'woocommerce' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 26 );
		//add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		//add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	function init() {
		do_action('woocommerce_rewards_init');
		
		$load_rewards = apply_filters('woocommerce_rewards', array());
		
		// Load reward classes
		foreach ( $load_rewards as $reward ) {
			
			$load_reward = new $reward();
			
			$this->rewards[$load_reward->get_id()] = $load_reward;
			
		}
		
	}
	
	function get_rewards() {
		return $this->rewards;
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		return array();
	}

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		// Load shipping methods so we can show any global options they may have
		$shipping_methods = WC()->shipping->load_shipping_methods();

		if ( $current_section ) {
 			foreach ( $shipping_methods as $method ) {
				if ( strtolower( get_class( $method ) ) == strtolower( $current_section ) && $method->has_settings() ) {
					$method->admin_options();
					break;
				}
			}
 		} else {
			$settings = $this->get_settings();

			WC_Admin_Settings::output_fields( $settings );
		}
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		if( $current_section == 'rewards_settings' )
			$current_section = 'WC_Rewards_Settings';

		$current_section_class = new $current_section();

		do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section_class->get_id() );
	}
}

endif;

return new WC_Rewards();
