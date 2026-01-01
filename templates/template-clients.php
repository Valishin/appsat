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

if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $search_term = sanitize_text_field($_GET['buscar']);    

    $args['meta_query'] = array(
        'relation' => 'OR', 
        array(
            'key'     => 'cpt-client__name',  
            'value'   => $search_term,
            'compare' => 'LIKE', 
        ),
        array(
            'key'     => 'cpt-client__dni',  
            'value'   => $search_term,
            'compare' => 'LIKE',
        ),
        array(
            'key'     => 'cpt-client__phone',
            'value'   => $search_term,
            'compare' => 'LIKE',
        ),
    );
}

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