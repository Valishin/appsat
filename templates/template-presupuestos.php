<?php
/*
Template Name: Template Presupuestos
*/

// Cualquier técnico logueado puede crear/editar presupuestos (misma
// capability que av_guardar_presupuesto()).
if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

the_post();
get_header();

// Un único template sirve listado + alta + edición (mismo patrón que
// templates/template-pedidos.php): sin parámetros = listado; ?new=1 = crear;
// ?id=X = editar. Se decide y se pinta todo dentro de components/c-presupuestos.php.
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$posts        = new WP_Query( av_build_presupuestos_query_args( $_GET, $paged ) );
$presupuestos = $posts->posts;

$editando = null;
if ( ! empty( $_GET['id'] ) ) {
    $editando_id = intval( $_GET['id'] );
    if ( get_post_type( $editando_id ) === 'cpt-presupuestos' ) {
        $editando = get_post( $editando_id );
    }
}

// El formulario (a página completa) se abre al crear, editar, o si el
// guardado falló (para no perder lo escrito).
$abrir_form = $editando || isset( $_GET['new'] ) || isset( $_GET['error'] );

?>
    <section class="o-main s-template-presupuestos">
        <?php
            include( locate_template( 'components/c-presupuestos.php' ) );
        ?>
    </section>
<?php
get_footer();
?>
