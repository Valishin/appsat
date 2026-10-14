<?php
/*
Template Name: Template Facturas
*/

the_post();
get_header();

// Un único template sirve las pantallas, igual que template-pedidos.php: sin
// parámetros = listado; ?new=1 = nueva factura (manual); ?id=X = ver/editar
// una factura manual. Las facturas de SAT/Pedido no se editan aquí (se
// generan desde su propio SAT/Pedido), solo se listan e imprimen.
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

// Misma construccion de la consulta que usa el buscador asincrono
$args     = av_build_facturas_query_args( $_GET, $paged );
$query    = new WP_Query( $args );
$facturas = $query->posts;

$editando = null;
if ( ! empty( $_GET['id'] ) ) {
    $editando_id = intval( $_GET['id'] );
    if ( get_post_type( $editando_id ) === 'cpt-facturas'
        && get_post_meta( $editando_id, '_factura_manual', true ) === '1' ) {
        $editando = get_post( $editando_id );
    }
}

// El formulario (a página completa) se abre al crear, editar, o si el
// guardado falló (para no perder lo escrito).
$abrir_form = $editando || isset( $_GET['new'] ) || isset( $_GET['error'] );
?>

<section class="o-main s-template-facturas">
    <?php if ( $abrir_form ) : ?>
    <div class="c-list-cpt-sats c-pedidos">
        <div class="c-list-cpt-sats__inner">
            <div class="c-list-cpt-sats__container o-container">
                <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                    <?php include locate_template( 'components/c-factura-form.php' ); ?>
                </div>
            </div>
        </div>
    </div>
    <?php else : ?>
        <?php include locate_template( 'components/c-list-facturas.php' ); ?>
    <?php endif; ?>
</section>

<?php
get_footer();
?>
