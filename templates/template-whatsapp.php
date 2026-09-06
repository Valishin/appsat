<?php
/*
Template Name: Template WhatsApp
*/

// Mismo criterio de acceso que el resto del CRM: administrador o técnico
// (editor). Todo lo demás (qué conversaciones se ven, etc.) lo comprueba el
// backend en cada endpoint REST, esto solo evita renderizar la página a quien
// no debería ni verla.
if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'edit_posts' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

the_post();
get_header();
?>
<section class="o-main s-template-whatsapp">
    <?php include( locate_template( 'components/c-whatsapp-inbox.php' ) ); ?>
</section>
<?php get_footer(); ?>
