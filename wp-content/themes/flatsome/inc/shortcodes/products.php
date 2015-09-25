<?php

// Flatsome Products
function ux_products($atts, $content = null, $tag) {
	global $woocommerce;
	$sliderrandomid = rand();
	extract(shortcode_atts(array(
		'title' => '',
		'products' => '8',
		'columns' => '4',
		'orderby' => '', // normal, sales, rand, date
		'type' => 'slider', // slider, masonry, normal, lookbook.
		'cat' => '', 
		'show' => '', //featured, onsale
		'tags' => '',
		'ids' => '',
	 	'infinitive' => 'false',
	 	'auto_slide' => 'false',
	 	'arrows' => 'true',
	), $atts));


	if($tag == 'ux_bestseller_products') {
		if(!$orderby) $atts['orderby'] = 'sales';
	} else if($tag == 'ux_featured_products'){
		$atts['show'] = 'featured';
		
	} else if($tag == 'ux_sale_products'){
		$atts['show'] = 'onsale';

	} else if($tag == 'ux_custom_products'){

	} else if($tag == 'product_lookbook'){
		$type = 'lookbook';

	} else if($tag == 'products_pinterest_style'){
		$type = 'masonry';
		if($products == '8') {
			$atts['products'] = '999';
			$columns = '3';
		}
	}

	if($type== 'lookbook' ){
		$infinitive = 'true';
	}

	ob_start();
	?>
    
    <?php 
	/**
	* Check if WooCommerce is active
	**/
	if(function_exists('wc_print_notices')) {
	?>
		<?php if($title){?> 
		<div class="row">
			<div class="large-12 columns">
				<h3 class="section-title"><span><?php echo $title ?></span></h3>
			</div>
		</div><!-- end .title -->
		<?php } ?>

	    <?php if($type !== 'lookbook') { ?><div class="row"><?php } ?>
		
		<?php if($type == 'normal') { ?>
			<div class="large-12 columns"> 
			<ul class="large-block-grid-<?php echo $columns; ?> small-block-grid-2"><?php } ?>
        <?php if($type == 'lookbook') { ?>
        	<div id="slider_<?php echo $sliderrandomid ?>" class="iosSlider lookbook-slider" style="overflow:hidden;height:350px;min-height:350px;">
            <ul class="slider large-block-grid-<?php echo $columns; ?> small-block-grid-2">
        	<?php } ?>
       	<?php if($type == 'masonry') { ?>
        	<div class="large-12 columns">
            <ul class="pinterest-style large-block-grid-<?php echo $columns; ?> small-block-grid-2">
         	<?php } ?>
        <?php if($type == 'slider') { ?> 
        	<div id="slider_<?php echo $sliderrandomid ?>" class="iosSlider column-slider" style="overflow:hidden;height:250px;min-height:250px;">
            <ul class="slider large-block-grid-<?php echo $columns; ?> small-block-grid-2">
        	<?php } ?>
			<?php

			$r = ux_list_products($atts);
            
            if ( $r->have_posts() ) : ?>
                        
                <?php while ( $r->have_posts() ) : $r->the_post(); ?>

                <?php
                    if($type == 'lookbook'){
                    	woocommerce_get_template_part( 'content', 'product-lookbook' );
                    }
                    else if($type == 'masonry'){
                   		 woocommerce_get_template_part( 'content', 'product-pinterest-style' );
                    }
                    else {
                    	woocommerce_get_template_part( 'content', 'product' );
                    }
                ?>
                <?php endwhile; // end of the loop. ?>
                
            <?php
            
            endif; 
            wp_reset_query();
            
            ?>
          </ul>   <!-- .products -->  
           <?php if($type == 'slider' || $type == 'lookbook' || $arrows == 'true') { ?> 
	            		<div class="sliderControlls <?php if($type == 'lookbook') { ?>dark<?php } ?>">
					        <div class="sliderNav <?php if($type !== 'lookbook') { ?>small<?php } ?> hide-for-small">
					       		 <a href="javascript:void(0)" class="nextSlide disabled prev_<?php echo $sliderrandomid ?>"><span class="icon-angle-left"></span></a>
					       		 <a href="javascript:void(0)" class="prevSlide next_<?php echo $sliderrandomid ?>"><span class="icon-angle-right"></span></a>
					        </div>
	       			   </div><!-- .sliderControlls -->
       		 <?php 
       		 	 // Run slider script
       		 	 slider_script($sliderrandomid,$columns,$infinitive);
       			}
       		?>
		</div>
	<?php if($type !== 'lookbook') { ?></div><!-- .row --><?php } ?>

   	<?php if($type == 'masonry') { ?> 
	 <script>
		/* PACKERY GRID */
		jQuery(document).ready(function ($) {
		    var $container = $(".pinterest-style");
		    // initialize
		    $container.packery({
		      itemSelector: "li",
		      gutter: 0
		    });

		    imagesLoaded( document.querySelector('.pinterest-style'), function( instance, container ) {
	  			$container.packery('layout');
			});
		 });
	</script>
    <?php } ?>

    <?php } // if woocommerce is active ?>

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}
add_shortcode("ux_bestseller_products", "ux_products");
add_shortcode("ux_featured_products", "ux_products");
add_shortcode("ux_sale_products", "ux_products");
add_shortcode("ux_latest_products", "ux_products");
add_shortcode("ux_custom_products", "ux_products");
add_shortcode("product_lookbook", "ux_products");
add_shortcode("products_pinterest_style", "ux_products");

add_shortcode("ux_products", "ux_products");


// Product Slider Script
function slider_script($sliderrandomid,$columns,$infinitive){
	?>
	<script type="text/javascript">

	jQuery(document).ready(function($) {

	
		$('#slider_<?php echo $sliderrandomid ?>').iosSlider({
			snapToChildren: true,
			desktopClickDrag: true,
			horizontalSlideLockThreshold:2,
			infiniteSlider:<?php echo $infinitive ?>,
        	slideStartVelocityThreshold:2,
        	verticalSlideLockThreshold: 2,
			navPrevSelector: '.prev_<?php echo $sliderrandomid ?>',
			navNextSelector: '.next_<?php echo $sliderrandomid ?>',
			onSliderLoaded: slideResize,
			onSliderResize: slideResize,
			onSlideChange: slideChange,
		});

		function slideResize(args) {
			 imagesLoaded( '#slider_<?php echo $sliderrandomid ?>', function() {
			 	setTimeout(function(){
			 			var t=0;
					var t_elem;
					$(args.sliderContainerObject).find('li').each(function () {
					 $this = $(this);
					    if ( $this.outerHeight() > t ) {
					        t_elem=this;
					        t=$this.outerHeight();
						}
						});
					$(args.sliderContainerObject).css('min-height',t);
					$(args.sliderContainerObject).css('height','auto');

			 	}, 100);
			 }); // images loaded 
    	 }

    	 function slideChange(args,slider_count) {
    	 	<?php if($infinitive == 'false') { ?>
    	 	 var slider_count = $('#slider_<?php echo $sliderrandomid ?>').find('li').length;
    	 	 var slider_count = slider_count - <?php echo $columns; ?>;
    	 	 if(args.currentSlideNumber > slider_count){
			 	 $('.next_<?php echo $sliderrandomid ?>').addClass('disabled');
			 } else {
			 	 $('.next_<?php echo $sliderrandomid ?>').removeClass('disabled');
			 }
			 if(args.currentSlideNumber == '1'){
			 	 $('.prev_<?php echo $sliderrandomid ?>').addClass('disabled');
			 } else {
			 	 $('.prev_<?php echo $sliderrandomid ?>').removeClass('disabled');
			 }
			<?php } ?>
    	 	}
	  
	});
	</script>

<?php }