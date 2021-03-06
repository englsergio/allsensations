<?php
// [ux_slider]
function shortcode_ux_slider($atts, $content=null) {
    $sliderrandomid = rand();
    ob_start();
    extract( shortcode_atts( array(
        'timer' => '5000',
        'bullets' => 'true',
        'auto_slide' => 'true',
        'arrows' => 'true',
        'hide_nav' => 'true',
        'nav_color' => '',
        'infinitive' => 'true',
        'columns' => '',
        'height' => '',
        'top_padding' => '',
        'mobile' => '',
        'class' => ''
    ), $atts ) );
    ?>

<div class="ux_slider_wrapper <?php echo $class; ?>">
<div id="slider_<?php echo $sliderrandomid ?>" class="iosSlider default <?php if($mobile == "false") echo 'slider-hide-for-small'; ?>" style="<?php if($height) echo 'min-height:'.$height.'; height:'.$height; ?>">
         <div class="slider <?php if($columns) echo 'columns-'.$columns; ?> <?php if($mobile == "false") echo 'hide-for-small'; ?>">
            <?php echo fixShortcode($content); ?>
         </div>
        <div class="sliderControlls <?php if(!$mobile) echo 'hide-for-small'; ?> <?php echo $nav_color; ?> <?php if($hide_nav == 'false') echo 'dont_hide_nav'; ?>">
            <?php if($arrows == 'true'){ ?> 
              <div class="sliderNav hide-for-small">
                 <a href="javascript:void(0)" class="nextSlide next_<?php echo $sliderrandomid; ?>"><span class="icon-angle-left"></span></a>
                 <a href="javascript:void(0)" class="prevSlide prev_<?php echo $sliderrandomid; ?>"><span class="icon-angle-right"></span></a>
              </div>
            <?php } ?>
            <div class="sliderBullets"></div>
        </div><!-- .sliderControlls -->
        <div class="ux-loading dark"><i></i><i></i><i></i><i></i></div>
</div><!-- #slider -->
<script type="text/javascript">
     jQuery(document).ready(function($) { 

      // Remove breakes
      $('.slider > br').remove();

      // Remove empty sliders
      if ($('.slider > p').is(':empty')) {
          $('.slider > p').remove();
      } 
      
      /* install slider */
      $('#slider_<?php echo $sliderrandomid; ?>').iosSlider({
          snapToChildren: true,
          desktopClickDrag: true,
          snapFrictionCoefficient: 0.8,
          autoSlideTransTimer: 500,
          horizontalSlideLockThreshold:3,
          slideStartVelocityThreshold:3,
          infiniteSlider:<?php echo $infinitive; ?>,
          autoSlide: <?php echo $auto_slide; ?>,
          autoSlideTimer: <?php echo $timer; ?>,
          navPrevSelector: $('.next_<?php echo $sliderrandomid; ?>'),
          navNextSelector: $('.prev_<?php echo $sliderrandomid; ?>'),
          onSliderLoaded: startSlider,
          onSlideChange: slideChange,
          onSliderResize: slideResize,
      }); 

      function slideChange(args) {

        $(args.sliderContainerObject).find('.inner-wrap').each(function(){
          $(this).removeClass($(this).attr('data-animate'));
        });
        $(args.sliderContainerObject).find('.scroll-animate').each(function(){
          $(this).removeClass($(this).attr('data-animate')).removeClass('animated');
        });

       /* start text animation */
       $(args.currentSlideObject).find('.inner-wrap').addClass($(args.currentSlideObject).find('.inner-wrap').attr('data-animate'));
       $(args.currentSlideObject).find('.scroll-animate').each(function(){
          $(this).addClass('animated').addClass($(this).attr('data-animate'));
       });

       /* change slider height */
       var slide_height = $(args.currentSlideObject).outerHeight();
       $(args.sliderContainerObject).css('min-height',slide_height);
       $(args.sliderContainerObject).css('height','auto');

       /* add current class to slide */
       $(args.sliderContainerObject).find('.current').removeClass('current');
       $(args.currentSlideObject).addClass('current');

       /* update bullets */
       $(args.sliderContainerObject).find('.sliderBullets .bullet').removeClass('active');
       $(args.sliderContainerObject).find('.sliderBullets .bullet:eq(' + (args.currentSlideNumber - 1) + ')').addClass('active');
    


      }
     function slideResize(args) {
        /* set height of first slide */
        setTimeout(function(){
              var slide_height = $(args.currentSlideObject).outerHeight();
              $(args.sliderContainerObject).css('min-height',slide_height);
              $(args.sliderContainerObject).css('height','auto');
        },300);
 
      }

     function startSlider(args){ 
        /* remove spinner when slider is loaded */
        $(args.sliderContainerObject).find('.ux-loading').fadeOut();


        /* add current class to first slide */
        $(args.currentSlideObject).addClass('current');

        /* add parallax class if contains paralaxx slides */
        $(args.sliderContainerObject).find('.ux_parallax').parent().parent().parent().addClass('parallax_slider');
           
        /* animate first slide */
        $(args.currentSlideObject).find('.inner-wrap').addClass($(args.currentSlideObject).find('.inner-wrap').attr('data-animate'));
          $(args.currentSlideObject).find('.scroll-animate').each(function(){
          $(this).addClass('animated').addClass($(this).attr('data-animate'));
        });       

        /* set height of first slide */
        imagesLoaded( document.querySelector('.ux_slider_wrapper'), function( instance, container ) {
             $(window).resize();
        });

        /* fix texts */
        $(args.sliderContainerObject).find('.ux_banner .inner br').remove();
      
      
        <?php if($bullets == 'true'){ ?> 
        /* add slider bullets */
        var slide_id = 1;
        $(args.sliderContainerObject).find(".slider > *").each(function(){
            $(args.sliderContainerObject).find('.sliderBullets').append('<div class="bullet" data-slide="'+slide_id+'"></div>');
            slide_id++;
        });
        
        /* add current class to bullets */
        $(args.sliderContainerObject).find('.sliderBullets .bullet:first').addClass('active');
        
        /* make bullets clickable */
        $(args.sliderContainerObject).find('.bullet').click(function(){
            $(args.sliderContainerObject).iosSlider('goToSlide', $(this).data('slide'));
         });
         <?php } ?>
     }
    });
    </script>
    <?php if($top_padding) { ?><style>#slider_<?php echo $sliderrandomid; ?> .ux-section-content > .row, #slider_<?php echo $sliderrandomid; ?> .ux_banner .row{margin-top: <?php echo $top_padding; ?>}</style><?php } ?>
</div><!-- .ux_slider_wrapper -->
<?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
add_shortcode("ux_slider", "shortcode_ux_slider");