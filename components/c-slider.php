<?php

if(av_component_get_option__is_active($component_global_settings)){

    if(!av_component_get_option__hide_mobile($component_global_settings)){

    $options = av_component_get_options($component_global_settings);

    $tag = av_get_tag_for_title();

    ?>
        <!-- C SLIDER -->
        <div 
            <?php echo $options['id-attr'];?>
            class="c-slider | js-slider | <?php echo $options['special-classes']; ?> | js-anim-inview"
        >
            <div 
                class="c-slider__inner | js-slider__inner | <?php echo $options['color-classes']; ?> | <?php echo $options['padding-classes']; ?>"
            >
            <div class="c-slider__top-bg"></div>
            <div class="c-slider__bottom-bg"></div>
            <div class="c-slider__container o-container">
                <div class="c-slider__col o-col-8@md o-col-push-2@md o-col-6@sm o-col-push-1@sm o-col-4@xs">
                    <div class="c-slider__wrapper-overline">
                        <div class="c-slider__overline o-font-display-button js-split-text"><?php echo $c_slider__overline; ?></div>
                    </div>
                    <div class="c-slider__wrapper-title">
                        <div class="c-slider__title o-font-display-headline js-split-text"><?php echo $c_slider__title; ?></div>
                    </div>
                </div>
                <div class="c-slider__col o-col-10@md o-col-push-1@md o-col-6@sm o-col-push-1@sm o-col-4@xs">
                    <div class="c-slider__swiper swiper js-swiper__swiper">                
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">                                                        
                            <?php
                                foreach ($c_slider__slider as $key => $slide) {
                                    $the_img = wp_get_attachment_image( $slide['image']['ID'], 'custom-full', "", array("class" => "c-slider__image", "data-speed" => "0.9") );
                                    ?>
                                        <div class="c-slider__swiper-slide swiper-slide">
                                            <div class="c-slider__wrapper-image">
                                                <?php echo $the_img; ?>
                                            </div>                                            
                                        </div>

                                    <?php
                                }
                            ?>
                        </div>
                        <!-- If we need pagination -->
                        <div class="c-slider__pagination swiper-pagination"></div>
        
                        <!-- If we need navigation buttons -->
                        <div class="c-slider__navigation swiper-button-prev">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="black" d="M9.904 17.308L4.596 12l5.308-5.308l.708.72L6.523 11.5h12.88v1H6.524l4.089 4.088z"/></svg>
                        </div>
                        <div class="c-slider__navigation swiper-button-next">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="black" d="M9.904 17.308L4.596 12l5.308-5.308l.708.72L6.523 11.5h12.88v1H6.524l4.089 4.088z"/></svg>
                        </div>
        
                        <!-- If we need scrollbar -->
                        <div class="swiper-scrollbar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php
    }
}