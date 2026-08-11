<?php
/*
Template Name: Template Facturas
*/

the_post();
get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

// Misma construccion de la consulta que usa el buscador asincrono
$args     = av_build_facturas_query_args( $_GET, $paged );
$query    = new WP_Query( $args );
$facturas = $query->posts;
?>

<section class="o-main s-template-facturas">
    <?php include locate_template( 'components/c-list-facturas.php' ); ?>
</section>

<?php
get_footer();
?>
