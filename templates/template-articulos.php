<?php
/*
Template Name: Template Artículos
*/

if ( ! current_user_can( 'administrator' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

the_post();
get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$posts     = new WP_Query( av_build_articulos_query_args( $_GET, $paged ) );
$articulos = $posts->posts;

$categorias = av_get_articulo_categorias();

$articulo_creado      = isset( $_GET['creado'] );
$articulo_actualizado = isset( $_GET['actualizado'] );
$articulo_desactivado = isset( $_GET['desactivado'] );
$articulo_reactivado  = isset( $_GET['reactivado'] );
$stock_ajustado       = isset( $_GET['stock-ajustado'] );

$editando = null;
if ( ! empty( $_GET['edit'] ) ) {
    $editando_id = intval( $_GET['edit'] );
    if ( get_post_type( $editando_id ) === 'cpt-articulos' ) {
        $editando = get_post( $editando_id );
    }
}

// El formulario (a página completa, no modal: son demasiados campos para una
// modal pequeña) se abre al crear, editar, o si el guardado falló (para no
// perder lo escrito).
$abrir_form = $editando || isset( $_GET['new'] ) || isset( $_GET['error'] );

?>
    <section class="o-main s-template-articulos">
        <?php
            include( locate_template( 'components/c-articulos.php' ) );
        ?>
    </section>
<?php
get_footer();
?>
