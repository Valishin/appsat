<?php
/*
Template Name: Template Projects
*/

the_post();
get_header();

$args = array(
    'post_type'         => 'cpt-project',
    'post_status'       => 'publish',
    'posts_per_page'    => '-1',
    'orderby'           => 'date',
    'order'             => 'DESC',
);

$posts = new WP_Query( $args );

$projects = $posts->posts;


?>

    <section class="o-main s-template-projects">
        <?php        
            include( locate_template('components/c-list-cpt-projects.php') );
        ?>
    </section>

<?php

get_footer(); 

?>