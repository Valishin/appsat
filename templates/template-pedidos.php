<?php
/*
Template Name: Template Pedidos
*/

// Cualquier técnico logueado puede crear/editar pedidos (misma capability
// que av_guardar_pedido()), no solo administradores — a diferencia de
// Artículos/Servicios/Dispositivos.
if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

the_post();
get_header();

// Un único template sirve las tres pantallas, igual que
// templates/template-articulos.php con c-articulos.php: sin parámetros =
// listado; ?new=1 = crear; ?id=X = detalle/edición del pedido (una sola
// pantalla). Se deciden y se pintan dentro de components/c-pedidos.php.
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$posts   = new WP_Query( av_build_pedidos_query_args( $_GET, $paged ) );
$pedidos = $posts->posts;

$editando = null;
if ( ! empty( $_GET['id'] ) ) {
    $editando_id = intval( $_GET['id'] );
    if ( get_post_type( $editando_id ) === 'cpt-pedidos' ) {
        $editando = get_post( $editando_id );
    }
}

// ?view=X (enlaces antiguos) ya no tiene pantalla propia: el detalle y la
// edición son la misma pantalla (?id=X).
if ( ! empty( $_GET['view'] ) && get_post_type( intval( $_GET['view'] ) ) === 'cpt-pedidos' ) {
    wp_redirect( add_query_arg( 'id', intval( $_GET['view'] ), av_pedidos_page_url() ) );
    exit;
}

// El formulario (a página completa) se abre al crear, editar, o si el
// guardado falló (para no perder lo escrito) — igual que Artículos.
$abrir_form = $editando || isset( $_GET['new'] ) || isset( $_GET['error'] );

?>
    <section class="o-main s-template-pedidos">
        <?php
            include( locate_template( 'components/c-pedidos.php' ) );
        ?>
    </section>
<?php
get_footer();
?>
