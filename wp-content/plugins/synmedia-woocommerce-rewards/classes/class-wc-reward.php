<?php
/**
 * WooCommerce Reward class
 * 
 * Extended by individual integrations to offer additional functionality.
 *
 * @class 		WC_Reward
 * @package		WooCommerce
 * @category	Reward
 * @author		WooThemes
 */
class WC_Reward extends WC_Settings_API {
	
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
	 * Admin Options
	 *
	 * Setup the gateway settings screen.
	 * Override this in your gateway.
	 *
	 * @since 1.0.0
	 */
	function admin_options() {
	?>
		
		<h3><?php echo isset( $this->method_title ) ? $this->method_title : __( 'Settings', 'woocommerce' ) ; ?></h3>
		
		<?php echo isset( $this->method_description ) ? wpautop( $this->method_description ) : ''; ?>
		
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		
		<!-- Section -->
		<div><input type="hidden" name="section" value="<?php echo $this->id; ?>" /></div>
		
		<?php
	}
	
	public function generate_text_html( $key, $data ) {
    	$html = $this->insert_tooltip($key, $data, 'generate_text_html', '.titledesc');
    	return $html;
    }
    
    public function generate_select_html( $key, $data ) {
    	$html = $this->insert_tooltip($key, $data, 'generate_select_html', '.titledesc');
    	return $html;
    }
    
    public function generate_checkbox_html( $key, $data ) {
    	$html = $this->insert_tooltip($key, $data, 'generate_checkbox_html', '.titledesc');
    	return $html;
    }
    
	private function insert_tooltip($key, $data, $func, $append_to){
		global $woocommerce;
		
		$data['desc_tip'] = isset( $data['tip'] ) ? $data['tip'] : '';
		$html = parent::$func( $key, $data );
		
		return $html;
	}

}