<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooCommerce Reward Class
 *
 * Extended by reward to handle shipping calculations etc.
 *
 * @class 		WC_Reward
 * @version		1.6.4
 * @package		WooCommerce/Abstracts
 * @category	Abstract Class
 * @author 		WooThemes
 */
abstract class WC_Reward extends WC_Settings_API {

	/** @var string Unique ID for the shipping method - must be set. */
	var $id;

	/** @var int Optional instance ID. */
	var $number;

	/** @var string Method title */
	var $method_title;

	/** @var string User set title */
	var $title;
	/** @var bool Enabled for disabled */
	var $enabled			= false;

	/** @var bool Whether the method has settings or not (In WooCommerce > Settings > Shipping) */
	var $has_settings		= true;

	/**
	 * has_settings function.
	 *
	 * @access public
	 * @return bool
	 */
	function has_settings() {
		return ( $this->has_settings );
	}

	/**
	 * Return the gateways title
	 *
	 * @return string
	 */
	public function get_title() {
		return apply_filters( 'woocommerce_shipping_method_title', $this->title, $this->id );
	}
}
