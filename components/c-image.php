<?php
if(av_component_get_option__is_active($component_global_settings) ){    

    if(!av_component_get_option__hide_mobile($component_global_settings)){

    $options = av_component_get_options($component_global_settings);

    $tag = av_get_tag_for_title();

    $img = wp_get_attachment_image( is_single() ? get_post_thumbnail_id($post_id) : $c_image__image['ID'], 'custom-full', "", array("class" => "c-image__image o-bg-s__image js-anim-image") );

    ?>
        <!-- C IMAGE -->
        <div <?php echo $options['id-attr']; ?> class="c-image | js-image | <?php echo $options['special-classes']; ?> | js-anim-inview">
            <div class="c-image__inner | js-video__inner | <?php echo $options['color-classes']; ?>">
                <div class="c-image__container">
                    <div class="c-image__wrapper-image">
                        <div class="c-image__image"><?php echo $img; ?></div>
                    </div>
                </div>
            </div>                
        </div>

    <?php
    }
}