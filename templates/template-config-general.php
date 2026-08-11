<?php
/*
Template Name: Template Config General
*/

the_post();

if ( ! current_user_can( 'administrator' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

// Procesar guardado aquí para tener acceso a get_permalink()
av_process_invoice_config_save( get_permalink() );

get_header();

$cfg   = av_get_invoice_config();
$saved = isset( $_GET['saved'] );
?>

<section class="o-main s-template-config-general">
    <?php include locate_template( 'components/c-config-general.php' ); ?>
</section>

<?php get_footer(); ?>
