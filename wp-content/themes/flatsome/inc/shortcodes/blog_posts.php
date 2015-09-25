<?php
// [blog_posts]
function shortcode_latest_from_blog($atts, $content = null) {
	global $flatsome_opt;
	$element_id = rand();
	extract(shortcode_atts(array(
		"posts" => '8',
		"columns" => '4',
		"category" => '',
		"style" => 'text-normal',
		"type" => "slider", // Slider / Grid / Masonry
		"image_height" => 'auto',
		"show_date" => 'true',
		"excerpt" => 'true',
	), $atts));

	if($type == 'masonry'){
		$style = 'text-boxed';
		$image_height = 'auto';
	}

	ob_start();
	?>
      	<div id="id-<?php echo $element_id; ?>" class="row column-<?php echo $type; ?> blog-posts">
            <?php if($type == 'slider'){ ?>
            	<div id="slider_<?php echo $element_id ?>" class="iosSlider <?php if($style  == 'text-overlay') { ?>slider-center-arrows<?php } ?>" style="min-height:<?php echo $image_height; ?>;height:<?php echo $image_height; ?>;">
            <?php } else { 
            	echo '<div class="large-12 columns"> ';
            } 
            ?>
                <ul class="<?php echo $type; ?> large-block-grid-<?php echo $columns ?> small-block-grid-2">

					<?php
                    $args = array(
                        'post_status' => 'publish',
                        'post_type' => 'post',
						'category_name' => $category,
                        'posts_per_page' => $posts
                    );

                    $recentPosts = new WP_Query( $args );

                    if ( $recentPosts->have_posts() ) : ?>

                        <?php while ( $recentPosts->have_posts() ) : $recentPosts->the_post(); ?>

						<li class="ux-box text-center post-item ux-<?php echo $style; ?>">
						    <div class="inner">
						      <div class="inner-wrap">
							    <a href="<?php the_permalink() ?>">
							      <div class="ux-box-image">
								        <div class="entry-image-attachment" style="max-height:<?php echo  $image_height; ?>;overflow:hidden;">
											<?php the_post_thumbnail('medium'); ?>
										</div>
							      </div><!-- .ux-box-image -->
							      <div class="ux-box-text text-vertical-center">
							         	<h3 class="from_the_blog_title"><?php the_title(); ?></h3>
							         	<div class="tx-div small"></div>
							            <?php if($excerpt != 'false') { ?>
								            <p class="from_the_blog_excerpt small-font show-next"><?php
								                $excerpt = get_the_excerpt();
								                echo string_limit_words($excerpt,15) . '[...]';
								            ?>
								     	   </p>
								     	 <?php } ?>
							           <p class="from_the_blog_comments uppercase smallest-font"><?php echo get_comments_number( get_the_ID() ); ?> comments</p>
							        	
							         </div><!-- .post_shortcode_text -->
							    </a>

								   <?php if($show_date != 'false') {?>
											            <div class="post-date">
												                <span class="post-date-day"><?php echo get_the_time('d', get_the_ID()); ?></span>
												                <span class="post-date-month"><?php echo get_the_time('M', get_the_ID()); ?></span>
												         </div>
									<?php } ?>
								</div><!-- .inner-wrap -->
						    </div><!-- .inner -->
						</li><!-- .blog-item -->
                          
                        <?php endwhile; // end of the loop. ?>

                    <?php

                    endif;
					wp_reset_query();

                    ?>
         </ul>

		<?php if($type == 'slider'){ ?>
		   <div class="sliderControlls dark">
		        <div class="sliderNav small hide-for-small">
		  		      <a href="javascript:void(0)" class="nextSlide prev_<?php echo $element_id ?>"><span class="icon-angle-left"></span></a>
		   		     <a href="javascript:void(0)" class="prevSlide next_<?php echo $element_id?>"><span class="icon-angle-right"></span></a>
		        </div>
		   	</div><!-- .sliderControlls -->
		
			<script>
			jQuery(document).ready(function($) {
				$(window).load(function() {
					/* items_slider */
					$('#slider_<?php echo $element_id ?>').iosSlider({
						snapToChildren: true,
						desktopClickDrag: true,
						infiniteSlider: true,
						navPrevSelector: '.prev_<?php echo $element_id ?>',
						navNextSelector: '.next_<?php echo $element_id ?>',
						onSliderLoaded: slideLoad,
						onSliderResize: slideLoad
					});
					function slideLoad(args){
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
				   		  },10);
				    }
				});
			});
			</script>
		<?php } ?>

		<?php if($type == 'masonry'){ ?>
			<script>
			jQuery(document).ready(function ($) {
			    imagesLoaded( document.querySelector('#id-<?php echo $element_id; ?>'), function( instance, container ) {
			    	var $container = $("#id-<?php echo $element_id; ?> ul.masonry");
				    // initialize
				    $container.packery({
				      itemSelector: ".ux-box",
				      gutter: 0,
				    });
					$container.packery('layout');
				});
			 });
			</script>
		<?php } ?>

        </div> <!-- .iOsslider / .large-12 -->
    </div><!-- .row  -->

	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

function string_limit_words($string, $word_limit) {
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit)
	array_pop($words);
	return implode(' ', $words);
}

add_shortcode("blog_posts", "shortcode_latest_from_blog");
