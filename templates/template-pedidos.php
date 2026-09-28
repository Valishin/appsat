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

// Fase 3: un único template sirve las cuatro pantallas, exactamente igual que
// templates/template-articulos.php con c-articulos.php: sin parámetros =
// listado; ?new=1 = crear; ?id=X = editar; ?view=X = detalle de solo lectura.
// El listado, el formulario y el detalle se deciden y se pintan todos dentro
// de components/c-pedidos.php (mismo punto de entrada único).
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

$pedido_visto = null;
if ( ! empty( $_GET['view'] ) ) {
    $view_id = intval( $_GET['view'] );
    if ( get_post_type( $view_id ) === 'cpt-pedidos' ) {
        $pedido_visto = get_post( $view_id );
    }
}

// El formulario (a página completa) se abre al crear, editar, o si el
// guardado falló (para no perder lo escrito) — igual que Artículos.
$abrir_form = $editando || isset( $_GET['new'] ) || isset( $_GET['error'] );

// El detalle solo se ve si NO hay formulario que mostrar: ?id= (editar)
// gana siempre sobre ?view=, por si llegasen los dos a la vez.
$ver_detalle = ! $abrir_form && $pedido_visto;

?>
    <section class="o-main s-template-pedidos">
        <?php
            include( locate_template( 'components/c-pedidos.php' ) );
        ?>
    </section>
<?php
get_footer();
?>
