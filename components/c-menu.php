<?php
if(av_component_get_option__is_active($component_global_settings) || is_page_template( 'templates/template-projects.php' )){    

    if(!av_component_get_option__hide_mobile($component_global_settings)){

    $options = av_component_get_options($component_global_settings);

    $tag = av_get_tag_for_title();

    ?>
        <!-- C MENU -->
        <div <?php echo $options['id-attr']; ?> class="c-menu | js-menu | <?php echo $options['special-classes']; ?> | js-anim-inview">
            <div class="c-menu__inner | js-menu__inner | <?php echo $options['color-classes']; ?>">
                <div class="c-menu__container o-container">
                    <div class="c-menu__col o-col-8@md o-col-push-2@md o-col-6@sm o-col-push-1@sm o-col-4@xs">                        
                        <div class="c-menu__wrapper-title">
                            <div class="c-menu__title o-font-display-1"><?php echo $c_menu__title; ?></div>
                        </div>
                        <?php
                            foreach ($c_menu__menu as $key => $value) {   
                                // var_dump($value);                             
                                $img = wp_get_attachment_image(get_post_thumbnail_id($value->ID), 'custom-full', "", array("class" => "c-menu__bg-image o-bg-s__image"));
                                ?>
                                <div class="c-menu__wrapper-menu">
                                    <?php if(!!$img){ ?>
                                        <div class="c-menu__bg-wrapper-image">
                                            <div class="c-menu__bg-velo"></div>
                                            <div class="c-menu__wrapper-image">
                                                <?php echo $img; ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="c-menu__menu">
                                        <div class="c-menu__wrapper-cta o-font-display-1" >
                                            <?php 
                                                av_print_button(
                                                    $value->guid,
                                                    $value->post_title,
                                                    'style-2',
                                                    '_blank',
                                                    'c-menu__cta'
                                                ); 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                </div>
            </div>                
        </div>

    <?php
    }
}