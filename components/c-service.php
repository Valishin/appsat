<?php

if(av_component_get_option__is_active($component_global_settings)){

    if(!av_component_get_option__hide_mobile($component_global_settings)){

    $options = av_component_get_options($component_global_settings);

    ?>
        <!-- C SLIDER -->
        <div 
            <?php echo $options['id-attr'];?>
            class="c-service | js-service | <?php echo $options['special-classes']; ?> | js-anim-inview"
        >
            <div 
                class="c-service__inner | js-service__inner | <?php echo $options['color-classes']; ?> | <?php echo $options['padding-classes']; ?>"
            >            
            <div class="c-service__container o-container">                
                <div class="c-service__col o-col-10@md o-col-push-1@md o-col-6@sm o-col-push-1@sm o-col-4@xs">
                    <div class="c-service__wrapper-content">                                       
                        <?php
                            foreach ($c_service__services as $key => $service) {
                                $the_img = wp_get_attachment_image( $service['image']['ID'], 'custom-full', "", array("class" => "c-service__image") );
                                ?>
                                    <div class="c-service__box">
                                        <div class="c-service__wrapper-image">
                                            <?php echo $the_img; ?>
                                        </div> 
                                        <div class="c-service__wrapper-title">
                                            <div class="c-service__title o-font-display-headline"><?php echo $service['title']; ?></div>
                                        </div>   
                                        <div class="c-service__wrapper-description">
                                            <div class="c-service__description"><?php echo $service['description']; ?></div>
                                        </div>                                          
                                    </div>

                                <?php
                            }
                        ?>                        
                    </div>
                </div>
            </div>
        </div>
        </div>                   
    <?php
    }
}