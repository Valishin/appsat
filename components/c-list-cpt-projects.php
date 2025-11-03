<?php
    $c_text__title = get_field('c-list-cpt-projects__title');    

    include( locate_template('components/c-text.php') );
?>

<div class="c-list-cpt-projects">
    <div class="c-list-cpt-projects__inner">  
        <div class="c-list-cpt-projects__wrapper-title">
            <div class="c-list-cpt-projects__title o-font-display-2"><?php echo $c_text__title; ?></div>
        </div>      
        <?php
            foreach ($projects as $key => $project) {
                $project_id = $project->ID;
                $title_project = get_the_title($project_id);                
                $image_project = wp_get_attachment_image( get_post_thumbnail_id($project_id), 'custom-full', "", array("class" => "c-list-cpt-projects__image o-bg-s__image js-anim-image") );
                $direction = 'image-content';
                $cpt_projects__link = get_field('cpt-projects__link', $project_id);                
                if ($key % 2 == 0) {
                    $direction = 'content-image';
                }
                ?>                
                <div class="c-list-cpt-projects__wrapper-project <?php echo $direction; ?>">
                    <div class="c-list-cpt-projects__text-content">
                        <a href="<?php echo $cpt_projects__link['url']; ?>" target="_blank">
                            <svg class="c-list-cpt-projects__link-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 3h7v7m-1.5-5.5L20 12m-3-7H8a3 3 0 0 0-3 3v16a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3v-9"/></svg>
                            <div class="c-list-cpt-projects__wrapper-title-project">
                                <div class="c-list-cpt-projects__title-project o-font-display-2"><?php echo $title_project; ?></div>
                            </div>
                            <p class="c-list-cpt-projects__view-project js-view-project o-font-display-headline">Ver proyecto</p>
                        </a>
                    </div>
                    <div class="c-list-cpt-projects__image-content">
                        <div class="c-list-cpt-projects__wrapper-image js-hover-node">
                            <div class="c-list-cpt-projects__velo"></div>
                            <?php echo $image_project; ?>
                            <p class="c-list-cpt-projects__view-detail o-font-display-headline">Ver detalle</p>
                        </div>
                    </div>
                </div>                
                <?php
            }
        ?>      
    </div>
</div>