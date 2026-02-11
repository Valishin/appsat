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

if (isset($_GET['nombre-cliente']) && !empty($_GET['nombre-cliente'])) {

    $search_term = sanitize_text_field($_GET['nombre-cliente']);

    // 1️⃣ Buscar clientes por nombre
    $clients = get_posts(array(
        'post_type'   => 'cpt-clients',
        'numberposts' => -1,
        'fields'      => 'ids',
        'meta_query'  => array(
            array(
                'key'     => 'cpt-client__name',
                'value'   => $search_term,
                'compare' => 'LIKE'
            )
        )
    ));

    if (!empty($clients)) {

        // 2️⃣ Pasar array de IDs a la query SAT
        $args['meta_query'][] = array(
            'key'     => 'cpt-sat__client-id',
            'value'   => $clients,
            'compare' => 'IN'
        );

    } else {
        // Si no hay clientes, forzar que no devuelva nada
        $args['post__in'] = array(0);
    }
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