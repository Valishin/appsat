<?php
if( !is_page_template( 'templates/template-projects.php' )){    

    if(!av_component_get_option__hide_mobile($component_global_settings)){

    $options = av_component_get_options($component_global_settings);

    $tag = av_get_tag_for_title();

    ?>
        <!-- C TEXT -->
        <div <?php echo $options['id-attr']; ?> class="c-text | js-text | <?php echo $options['special-classes']; ?> | js-anim-inview border-<?php echo $c_text__border; ?>">
            <div class="c-text__inner | js-text__inner | <?php echo $options['color-classes']; ?>">
                <div class="c-text__container | o-container">                    
                    <div class="c-text__col o-col-12@md | o-col-4@sm | o-col-4@xs">                                
                        <?php                                   
                            if(!!$c_text__title){
                                ?>
                                    <div class="c-text__wrapper-title" >
                                        <<?php echo $tag; ?> class="c-text__title o-font-display-2 | js-split-text js-anim-split-title" ><?php echo $c_text__title; ?></<?php echo $tag; ?>>
                                    </div>
                                <?php
                            }
                        ?>
                    </div>
                    <div class="c-text__col o-col-10@md o-col-push-1@md | o-col-4@sm | o-col-4@xs">                                        
                        <?php if(!!$c_text__text){
                            ?>
                                <div class="c-text__wrapper-text <?php echo 'text-align-' . $c_text__text_align; ?>">
                                    <div class="c-text__text js-split-text | s-content o-font-display-body">
                                        <?php echo av_breaklines($c_text__text); ?>
                                    </div>
                                </div>
                            <?php
                            
                        } ?>                    
                    </div>
                    <?php if((!!$c_text__cta)){ ?>
                        <div class="c-text__col o-col-12@md | o-col-4@sm | o-col-4@xs">                                                         
                            <div class="c-text__wrapper-cta" >
                                <?php 
                                    av_print_button(
                                        $c_text__cta['url'],
                                        $c_text__cta['title'],
                                        $c_text__cta_style,
                                        $c_text__cta['target'],
                                        'c-text__cta'
                                    ); 
                                ?>
                            </div>                                                                                                                        
                        </div>
                    <?php } ?>                    
                </div>                
            </div>                
        </div>

    <?php
    }
}