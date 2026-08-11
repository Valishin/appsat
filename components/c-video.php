<?php
if(av_component_get_option__is_active($component_global_settings) ){    

    if(!av_component_get_option__hide_mobile($component_global_settings)){

    $options = av_component_get_options($component_global_settings);

    $tag = av_get_tag_for_title();

    ?>
        <!-- C VIDEO -->
        <div <?php echo $options['id-attr']; ?> class="c-video | js-video | <?php echo $options['special-classes']; ?> | js-anim-inview">
            <div class="c-video__inner | js-video__inner | <?php echo $options['color-classes']; ?>">
                <div class="c-video__container o-container">
                    <div class="c-video__col o-col-8@md o-col-push-2@md o-col-6@sm o-col-push-1@sm o-col-4@xs">
                        <video class="js-video__toggle-scroll" src="<?php echo $c_video__video['url']; ?>" autoplay muted loop></video>
                    </div>
                </div>
            </div>                
        </div>

    <?php
    }
}