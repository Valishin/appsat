<?php
// Delega al template personalizado asignado a la página configurada como portada
// (Ajustes → Lectura → Una página estática).
// Sin este fichero, WordPress usaría front-page.php en lugar del custom template.
$custom_tpl = get_page_template();
if ( $custom_tpl ) {
    load_template( $custom_tpl );
    return;
}

// Fallback: renderizado de página por defecto
the_post();
get_header();
?>
<section class="o-main s-template-default">
    <?php include locate_template( 'components/components.php' ); ?>
</section>
<?php get_footer(); ?>
