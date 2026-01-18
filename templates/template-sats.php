<?php
/*
Template Name: Template SATS
*/

the_post();
get_header();

$args = array(
    'post_type'         => 'cpt-sats',
    'post_status'       => 'publish',
    'posts_per_page'    => '-1',
    'orderby'           => 'date',
    'order'             => 'DESC',
);

if (isset($_GET['id-sat']) && !empty($_GET['id-sat'])) {
    $search_term = $_GET['id-sat'];

    $args['meta_query'] = array(        
        array(
            'key'     => 'cpt-sat__sat-id',  
            'value'   => $search_term,
            'compare' => 'LIKE', 
        ),       
    );    
}

$posts = new WP_Query( $args );

$sats = $posts->posts;


?>

    <section class="o-main s-template-sats">
        <?php        
            include( locate_template('components/c-list-cpt-sats.php') );
        ?>
    </section>

<?php

get_footer(); 

?>