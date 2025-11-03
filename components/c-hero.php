<?php

if(av_component_get_option__is_active($component_global_settings)){

    if(!av_component_get_option__hide_mobile($component_global_settings)){

    $options = av_component_get_options($component_global_settings);

    $tag = av_get_tag_for_title();
    ?>
        <!-- C HERO -->
        <div 
            <?php echo $options['id-attr'];?>
            class="c-hero | js-hero | <?php echo $options['special-classes']; ?> | js-anim-inview"
        >
            <div 
                class="c-hero__inner | js-hero__inner | <?php echo $options['color-classes']; ?> | <?php echo $options['padding-classes']; ?>"
            >         
                <div class="c-hero__container o-container">

                    <div class="c-hero__col | o-col-12@md | o-col-8@sm | o-col-4@xs">
                            <?php                                                                                               
                                 if(!!$c_hero__title){
                                    ?>
                                        <div class="c-hero__wrapper-title">
                                            <<?php echo $tag; ?> class="c-hero__title o-font-display-1 <?= !$tag ? 'js-split-text' : ''; ?>"><?php echo $c_hero__title; ?></<?php echo $tag; ?>>
                                        </div>
                                    <?php
                                }else{
                                    ?>
                                        <h1 class="hide">MAKECODED</h1>
                                    <?php
                                } 
                                if(!!$c_hero__image){
                                    ?>
                                        <div class="c-hero__wrapper-image">
                                            <img src="<?php echo $c_hero__image['url']; ?>" alt="<?php echo $c_hero__image['alt']; ?>">
                                        </div>
                                    <?php
                                } 
                                if(!!$c_hero__description){                                    
                                    ?>
                                    <div class="c-hero__wrapper-description">
                                        <div class="c-hero__description o-font-display-headline js-split-text"><?php echo $c_hero__description; ?></div>
                                    </div>                                     
                                    <?php
                                }   
                                if(!!$c_hero__text){                                    
                                    ?>
                                    <div class="c-hero__wrapper-text">
                                        <div class="c-hero__text o-font-display-body js-split-text"><?php echo $c_hero__text; ?></div>
                                    </div>                                     
                                    <?php
                                }                              
                            ?>                               
                            <?php if(!!$c_hero__cta){ ?>
                                <div class="c-hero__wrapper-cta | c-hero__wrapper-cta--1">
                                    <?php 
                                        av_print_button(
                                            $c_hero__cta['url'],
                                            $c_hero__cta['title'],
                                            'style-1',
                                            $c_hero__cta['target'],
                                            'c-hero__cta'
                                        ); 
                                    ?>
                                </div>
                            <?php } ?>
                    </div>
                </div>                                                   
            </div>
        </div>
    <?php
    }
    
}
