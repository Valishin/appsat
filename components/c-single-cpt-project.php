<?php 
    $post_id = get_the_ID();
    $c_text__title = get_the_title();
    $c_text__text = get_field('cpt-projects__description', $post_id);
    $c_text__year = get_field('cpt-projects__date', $post_id);
    $c_text__details = get_field('cpt-projects__details', $post_id);
?>
<div class="c-single-cpt-project">
    <div class="c-single-cpt-project__inner">
        <?php include( locate_template('components/c-text.php') ); ?>
        <?php 
            $component_global_settings = av_component_get_component_global_settings_default();
            include( locate_template('components/c-image.php') ); 
        ?>
        <?php include( locate_template('components/components.php') ); ?>
    </div>
</div>