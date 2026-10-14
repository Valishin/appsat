<?php
/*
Template Name: Template Compras
*/

// Cualquier técnico logueado puede registrar compras (misma capability que
// Pedidos); las condiciones del recibo solo las edita un administrador.
if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

the_post();
get_header();

// Un único template, igual que Pedidos/Facturas: sin parámetros = listado;
// ?new=1 = nueva compra; ?id=X = ver/editar; ?condiciones=1 = letra pequeña.
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$query = new WP_Query( av_build_compras_query_args( $_GET, $paged ) );

$editando = null;
if ( ! empty( $_GET['id'] ) ) {
    $editando_id = intval( $_GET['id'] );
    if ( get_post_type( $editando_id ) === 'cpt-compras' ) {
        $editando = get_post( $editando_id );
    }
}

$ver_condiciones = isset( $_GET['condiciones'] ) && current_user_can( 'manage_options' );
$abrir_form      = $editando || isset( $_GET['new'] ) || isset( $_GET['error'] );
?>
<section class="o-main s-template-compras">
<?php if ( $ver_condiciones ) : ?>
    <?php include locate_template( 'components/c-compras-terms.php' ); ?>
<?php elseif ( $abrir_form ) : ?>
    <div class="c-list-cpt-sats c-pedidos">
        <div class="c-list-cpt-sats__inner">
            <div class="c-list-cpt-sats__container o-container">
                <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                    <?php include locate_template( 'components/c-compra-form.php' ); ?>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <?php include locate_template( 'components/c-compras-list.php' ); ?>
<?php endif; ?>
</section>
<?php
get_footer();
