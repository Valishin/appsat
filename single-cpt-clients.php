<?php
/*
    Single
*/

the_post();
get_header();

    $title = 'Modificar Cliente';
    $post_id = get_the_ID();
    $name = get_field('cpt-client__name', $post_id); 
    $extension = get_field('cpt-client__extension', $post_id); 
    $phone = get_field('cpt-client__phone', $post_id);
    $phone2_type = get_field('cpt-client__phone-2-type', $post_id);
    $phone2_extension = get_field('cpt-client__phone-2-extension', $post_id);
    $phone2 = get_field('cpt-client__phone-2', $post_id);
    $email = get_field('cpt-client__email', $post_id);
    $dni = get_field('cpt-client__dni', $post_id);
    $type_client = get_field('cpt-client__type-client', $post_id);
    $cif = get_field('cpt-client__cif', $post_id);
    $razon_social = get_field('cpt-client__razon-social', $post_id);
    $direccion_fiscal = get_field('cpt-client__direccion-fiscal', $post_id);
    $direccion_fiscal_cp = get_field('cpt-client__direccion-fiscal-cp', $post_id);
    $direccion_fiscal_municipio = get_field('cpt-client__direccion-fiscal-municipio', $post_id);
    $direccion_fiscal_provincia = get_field('cpt-client__direccion-fiscal-provincia', $post_id);
    $direccion_fiscal_pais = get_field('cpt-client__direccion-fiscal-pais', $post_id);
    $notes = get_field('cpt-client__notes', $post_id);

    $args = array(
        'post_type'         => 'cpt-sats',
        'post_status'       => 'publish',
        'posts_per_page'    => '20',
        'orderby'           => 'date',
        'order'             => 'DESC',
        'paged'             => $paged,
        'meta_query'  => array(
            array(
                'key'     => 'cpt-sat__client-id',
                'value'   => $post_id,
                'compare' => '='
            )
        )
    );

    $posts = new WP_Query( $args );

    $sats = $posts->posts;

    ?>

        <section class="o-main s-template-single-cpt-clients">
            <?php
                $c_project            = get_post();            
            ?>

            <?php include( locate_template('components/c-client-choice.php') ); ?>

        </section>

    <?php

get_footer(); 

?>