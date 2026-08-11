<?php

if(av_component_get_option__is_active($component_global_settings)){

    if(!av_component_get_option__hide_mobile($component_global_settings)){

    $options = av_component_get_options($component_global_settings);

    $tag = av_get_tag_for_title();

    $the_img_left = wp_get_attachment_image( $c_two_image_text__image_left['ID'], 'custom-full', "", array("class" => "c-two-image-text__image c-two-image-text__image--left o-bg-s__image js-anim-image", "data-speed" => "0.9") );
    $the_img_right = wp_get_attachment_image( $c_two_image_text__image_right['ID'], 'custom-full', "", array("class" => "c-two-image-text__image c-two-image-text__image--right o-bg-s__image js-anim-image", "data-speed" => "0.9") );
    ?>
        <!-- C TWO IMAGE TEXT -->
        <div 
            <?php echo $options['id-attr'];?>
            class="c-two-image-text | js-two-image-text | <?php echo $options['special-classes']; ?> | js-anim-inview"
        >
            <div 
                class="c-two-image-text__inner | js-two-image-text__inner | <?php echo $options['color-classes']; ?> | <?php echo $options['padding-classes']; ?>"
            >
                <div class="c-two-image-text__container o-container">
                    <div class="c-two-image-text__col o-col-8@md o-col-push-2@md o-col-6@sm o-col-push-1@sm o-col-4@xs">
                        <?php if(!!$c_two_image_text__overline){ ?>
                        <div class="c-two-image-text__wrapper-overline">
                            <div class="c-two-image-text__overline o-font-display-button js-split-text">
                                <?php echo $c_two_image_text__overline; ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if(!!$c_two_image_text__title){ ?>
                        <div class="c-two-image-text__wrapper-title">
                            <div class="c-two-image-text__title o-font-display-headline js-split-text">
                                <?php echo $c_two_image_text__title; ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="c-two-image-text__col o-col-6@md o-col-4@sm o-col-4@xs">
                        <?php if(!!$the_img_left){ ?>
                        <div class="c-two-image-text__wrapper-image c-two-image-text__wrapper-image--left">
                            <?php echo $the_img_left; ?>
                            <?php if(!!$c_two_image_text__cta_left){ ?>
                            <a href="<?php echo $c_two_image_text__cta_left['url'];?>" target="<?php echo $c_two_image_text__cta_left['target']; ?>">
                                <div class="c-two-image-text__wrapper-cta">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="black" d="m16.288 7.208l-9.765 9.746q-.14.14-.344.13t-.344-.15t-.14-.334t.14-.335L15.58 6.5H6.788q-.212 0-.356-.144t-.144-.357t.144-.356t.356-.143h9.693q.343 0 .575.232t.232.576V16q0 .213-.143.356t-.357.144t-.356-.144t-.144-.356z"/></svg>
                                </div>
                            </a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <?php if(!!$c_two_image_text__title_left){ ?>
                        <div class="c-two-image-text__wrapper-title c-two-image-text__wrapper-title--image">
                            <div class="c-two-image-text__title c-two-image-text__title--image o-font-display-button js-split-text">
                                <?php echo $c_two_image_text__title_left; ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if(!!$c_two_image_text__text_left){ ?>
                        <div class="c-two-image-text__wrapper-text">
                            <div class="c-two-image-text__text c-two-image-text__text--image o-font-display-button js-split-text">
                                <?php echo $c_two_image_text__text_left; ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="c-two-image-text__col o-col-6@md o-col-4@sm o-col-4@xs">
                        <?php if(!!$the_img_right){ ?>
                        <div class="c-two-image-text__wrapper-image c-two-image-text__wrapper-image--right">
                            <?php echo $the_img_right; ?>
                            <?php if(!!$c_two_image_text__cta_right){ ?>
                            <a href="<?php echo $c_two_image_text__cta_right['url'];?>" target="<?php echo $c_two_image_text__cta_right['target']; ?>">
                                <div class="c-two-image-text__wrapper-cta">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="black" d="m16.288 7.208l-9.765 9.746q-.14.14-.344.13t-.344-.15t-.14-.334t.14-.335L15.58 6.5H6.788q-.212 0-.356-.144t-.144-.357t.144-.356t.356-.143h9.693q.343 0 .575.232t.232.576V16q0 .213-.143.356t-.357.144t-.356-.144t-.144-.356z"/></svg>
                                </div>
                            </a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <?php if(!!$c_two_image_text__title_right){ ?>
                        <div class="c-two-image-text__wrapper-title c-two-image-text__wrapper-title--image">
                            <div class="c-two-image-text__title c-two-image-text__title--image o-font-display-button js-split-text">
                                <?php echo $c_two_image_text__title_right; ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if(!!$c_two_image_text__text_right){ ?>
                        <div class="c-two-image-text__wrapper-text">
                            <div class="c-two-image-text__text c-two-image-text__text--image o-font-display-button js-split-text">
                                <?php echo $c_two_image_text__text_right; ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

    <?php
    }
}