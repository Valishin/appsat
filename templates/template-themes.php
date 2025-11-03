<?php
/*
Template Name: Template Themes
*/

the_post();
get_header();

$args = array(
    'post_type'         => 'cpt-theme',
    'post_status'       => 'publish',
    'posts_per_page'    => '-1',
    'orderby'           => 'date',
    'order'             => 'DESC',
);

$posts = new WP_Query( $args );

$themes = $posts->posts;


?>

    <section class="o-main s-template-themes">
        <?php        
            include( locate_template('components/c-list-cpt-themes.php') );
        ?>
    </section>

<?php

get_footer(); 

?>