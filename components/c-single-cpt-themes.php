<?php 
    $post_id = get_the_ID();
    $title = get_the_title();
    $previsualition = get_field('cpt-themes__previsualition', $post_id);
    $description = get_field('cpt-themes__description', $post_id);
    $gallery_desktop = get_field('cpt-themes__gallery-desktop', $post_id);
    $gallery_tablet = get_field('cpt-themes__gallery-tablet', $post_id);
    $gallery_mobile = get_field('cpt-themes__gallery-mobile', $post_id);
    $video = get_field('cpt-themes__video', $post_id);
    $image_id = get_post_thumbnail_id( $post_id );
    $image = wp_get_attachment_image( $image_id, 'custom-full', "", array("class" => "c-single-cpt-themes__image o-bg-s__image") );    
?>
<div class="c-single-cpt-themes">
    <div class="c-single-cpt-themes__inner">
        <div class="c-single-cpt-themes__container o-container">
            <div class="c-single-cpt-themes__col o-col-6@md o-col-4@sm o-col-4@xs">
                <div class="c-single-cpt-themes__box-1">
                    <div class="c-single-cpt-themes__wrapper-title">
                        <div class="c-single-cpt-themes__title o-font-display-2"><?php echo $title; ?></div>
                    </div>
                    <div class="c-single-cpt-themes__wrapper-button">
                        <?php 
                            av_print_button(
                                $previsualition['url'],
                                $previsualition['title'],
                                'style-1',
                                $previsualition['target'],
                                'c-single-cpt-themes__cta'
                            ); 
                        ?>
                    </div>
                </div>
                <div class="c-single-cpt-themes__box-2">
                    <div class="c-single-cpt-themes__wrapper-description">
                        <div class="c-single-cpt-themes__description o-font-display-body"><?php echo $description; ?></div>
                    </div>
                </div>
            </div>
            <div class="c-single-cpt-themes__col c-single-cpt-themes__col--theme-image o-col-6@md o-col-4@sm o-col-4@xs">
                <div class="c-single-cpt-themes__wrapper-video js-single-cpt-themes__video-play js-single-cpt-themes__video-pin js-hover" data-hover="js-single-cpt-themes__hover-active">
                    <div class="c-single-cpt-themes__wrapper-velo"></div>                                                        
                    <svg class="c-single-cpt-themes__image-icon c-single-cpt-themes__image-icon--play" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="currentColor" d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0M1.5 8a6.5 6.5 0 1 0 13 0a6.5 6.5 0 0 0-13 0m4.879-2.773l4.264 2.559a.25.25 0 0 1 0 .428l-4.264 2.559A.25.25 0 0 1 6 10.559V5.442a.25.25 0 0 1 .379-.215"/></svg>
                    <svg class="c-single-cpt-themes__image-icon c-single-cpt-themes__image-icon--pause hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="currentColor" d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m0 14.5a6.5 6.5 0 1 1 0-13a6.5 6.5 0 0 1 0 13M5 5h2v6H5zm4 0h2v6H9z"/></svg>
                    <video class="c-single-cpt-themes__video" src="<?php echo $video['url']; ?>" muted></video>                    
                </div>
            </div>
        </div>
    </div>
</div>