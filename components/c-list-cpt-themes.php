<?php
    $title = get_field('c-list-cpt-themes__title');
?>
<div class="c-list-cpt-themes">
    <div class="c-list-cpt-themes__inner">  
        <div class="c-list-cpt-themes__container o-container">
            <div class="c-list-cpt-themes__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-list-cpt-themes__wrapper-title">
                    <div class="c-list-cpt-themes__title o-font-display-2"><?php echo $title; ?></div>
                </div>         
            </div>
        </div> 
        <div class="c-list-cpt-themes__container o-container">
        <?php
            foreach ($themes as $key => $theme) {
                $theme_id = $theme->ID;
                $title_project = get_the_title($theme_id);                
                $image_project = wp_get_attachment_image( get_post_thumbnail_id($theme_id), 'custom-full', "", array("class" => "c-list-cpt-themes__image o-bg-s__image js-anim-image") );                
                ?>
                    <div class="c-list-cpt-themes__col c-list-cpt-themes__col--theme o-col-4@md o-col-4@sm o-col-4@xs">
                        <a class="c-list-cpt-themes__wrapper-link" href="<?php echo get_permalink($theme_id); ?>">
                            <div class="c-list-cpt-themes__wrapper-project">
                                <div class="c-list-cpt-themes__image-content">
                                    <div class="c-list-cpt-themes__wrapper-image">
                                        <?php echo $image_project; ?>
                                    </div>
                                </div>
                                <div class="c-list-cpt-themes__text-content">                                        
                                    <div class="c-list-cpt-themes__wrapper-theme-title">
                                        <div class="c-list-cpt-themes__theme-title o-font-display-body"><?php echo $title_project; ?></div>
                                    </div>   
                                    <svg class="c-list-cpt-themes__link-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 3h7v7m-1.5-5.5L20 12m-3-7H8a3 3 0 0 0-3 3v16a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3v-9"/></svg>                         
                                </div>
                            </div>
                        </a>
                    </div>
                <?php
            }
        ?>                    
        </div> 
    </div>
</div>