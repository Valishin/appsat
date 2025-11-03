<?php
/*
Template Name: Template Clients
*/

the_post();
get_header();

$args = array(
    'post_type'         => 'cpt-clients',
    'post_status'       => 'publish',
    'posts_per_page'    => '-1',
    'orderby'           => 'date',
    'order'             => 'DESC',
);

$posts = new WP_Query( $args );

$clients = $posts->posts;


?>

    <section class="o-main s-template-clients">
        <?php        
            include( locate_template('components/c-list-cpt-clients.php') );
        ?>
    </section>

<?php

get_footer(); 

?>