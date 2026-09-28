<?php
// Espera $posts (WP_Query) y $pedidos (array de WP_Post) en el scope. Usado
// tanto en la carga normal de la página como en la respuesta del buscador
// asíncrono (AJAX). Mismo patrón que components/c-articulos-list.php.
?>
<table>
    <thead>
        <tr>
            <th>Número</th>
            <th>Cliente</th>
            <th>Producto</th>
            <th>Proveedor</th>
            <th>Estado</th>
            <th>Fecha creación</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ( empty( $pedidos ) ) : ?>
        <tr class="c-list-cpt-sats__row">
            <td colspan="7" class="c-devices__empty">No se han encontrado pedidos.</td>
        </tr>
        <?php endif; ?>
        <?php foreach ( $pedidos as $pedido ) :
            $ped_id          = $pedido->ID;
            $pedido_numero   = get_post_meta( $ped_id, 'cpt-pedido__pedido-id', true );
            $client_id       = intval( get_post_meta( $ped_id, 'cpt-pedido__client-id', true ) );
            $client_name     = av_pedido_resolve_client_name( $client_id );
            $status          = get_post_meta( $ped_id, 'cpt-pedido__status', true ) ?: 'pending';
            $entry_date      = get_post_meta( $ped_id, 'cpt-pedido__entry-date', true );
            $lines           = av_pedido_get_lines( $ped_id );
            $product_summary = av_pedido_product_summary( $lines );
            $supplier_summary = av_pedido_supplier_summary( $lines );
            $is_paid         = av_pedido_is_paid( $ped_id );

            // Mismo dato que ya guarda av_pedido_invoice_page() (inc/pedidos/venta.php)
            // en cada visita — evita una consulta aparte por fila solo para saber
            // si ya existe factura.
            $sale_id = intval( get_post_meta( $ped_id, 'cpt-pedido__sale-id', true ) );
            $print_url = $sale_id ? esc_url( add_query_arg( [
                'pedido_invoice' => '1',
                'pedido_id'      => $ped_id,
                'nonce'          => wp_create_nonce( 'pedido_invoice_' . $ped_id ),
            ], home_url( '/' ) ) ) : '';
        ?>
        <tr class="c-list-cpt-sats__row">
            <td>
                <a class="c-list-cpt-sats__client-name" href="<?php echo esc_url( add_query_arg( 'view', $ped_id, av_pedidos_page_url() ) ); ?>">
                    <?php echo esc_html( $pedido_numero ); ?>
                </a>
            </td>
            <td class="c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $client_name ); ?>"><span class="c-list-cpt-sats__cell-truncate"><?php echo esc_html( $client_name ); ?></span></td>
            <td class="c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $product_summary ); ?>"><span class="c-list-cpt-sats__cell-truncate"><?php echo esc_html( $product_summary ); ?></span></td>
            <td><?php echo esc_html( $supplier_summary ); ?></td>
            <td style="white-space:nowrap">
                <div style="display:flex;align-items:center;gap:6px">
                    <span class="c-pedidos__status-badge c-pedidos__status-badge--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( av_pedido_status_label( $status ) ); ?></span>
                    <span class="c-pedido-detail__paid-badge <?php echo $is_paid ? 'c-pedido-detail__paid-badge--ok' : 'c-pedido-detail__paid-badge--pte'; ?>">
                        <?php echo $is_paid ? 'Pagado' : 'Pendiente'; ?>
                    </span>
                </div>
            </td>
            <td><?php echo esc_html( $entry_date ?: '—' ); ?></td>
            <td>
                <div class="c-devices__actions">
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Ver"
                        href="<?php echo esc_url( add_query_arg( 'view', $ped_id, av_pedidos_page_url() ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Editar"
                        href="<?php echo esc_url( add_query_arg( 'id', $ped_id, av_pedidos_page_url() ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <?php if ( $print_url ) : ?>
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Imprimir factura"
                        href="<?php echo $print_url; ?>"
                        target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
if ( $posts->max_num_pages > 1 ) {

    $pagination_base = ! empty( $pedidos_page_url )
        ? add_query_arg( 'paged', 999999999, $pedidos_page_url )
        : get_pagenum_link( 999999999 );

    echo '<div class="c-list-cpt-sats__pagination">';

    echo paginate_links( array(
        'base'      => str_replace( 999999999, '%#%', esc_url( $pagination_base ) ),
        'format'    => '?paged=%#%',
        'current'   => max( 1, intval( $paged ) ),
        'total'     => $posts->max_num_pages,
        'prev_text' => '« Anterior',
        'next_text' => 'Siguiente »',
    ) );

    echo '</div>';
}
?>
