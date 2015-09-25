jQuery(document).ready(function($){
	var swrTime = null;
	$( document ).on( "change", ".qty", swr_options_changed );
	$('.single_variation_wrap').bind('show_variation', swr_variations_options_changed);
	$(".variations_button").append($(".buywithpoints"));
	$("#buywithpoints_container").remove();
	
	function swr_variations_options_changed(){
		if($('form input[name=variation_id]').val() != ''){
			swr_options_changed();
		}
	}
	
	function swr_options_changed(){
		var product_id = 0;
		var variation_id = 0;
		var qtys = '';
		if( $( 'form.cart input[name="variation_id]' ).length > 0 ){
			variation_id = $('form.cart input[name="variation_id]').val();
		}else if($("form.cart .qty").length > 1){
			qtys = $("form.cart .qty").serialize();
			qtys = qtys.replace(/\&/g, '|');
			qtys = qtys.replace(/\=/g, ':');
		}else{
			product_id = $( 'form.cart input[name="add-to-cart"]' ).val();
		}
		
		$.ajax({
			type: "POST",
			url: woocommerce_params.ajax_url,
			data: "action=swr_update_product_qty" + ( product_id > 0 ? "&product_id=" + product_id : '' ) + ( variation_id > 0 ? "&variation_id=" + variation_id : '' ) + "&qty=" + $(".qty").val(),
			dataType: "json",
			success: function(data){
				if(data != undefined){
				
					if( data.show_old_reward == '1' ){
						$(".swr_old_reward").show();
					}else{
						$(".swr_old_reward").hide();
					}
					
					$(".swr_new_reward").html( data.swr_new_reward );
					$(".swr_old_reward").html( data.swr_old_reward );
				}
			}
		});
	}
	
});