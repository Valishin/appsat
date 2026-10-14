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
            <th>Teléfono</th>
            <th>Producto</th>
            <th>Proveedor</th>
            <th>Estado</th>
            <th>Pago</th>
            <th>Total</th>
            <th>Fecha creación</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ( empty( $pedidos ) ) : ?>
        <tr class="c-list-cpt-sats__row">
            <td colspan="10" class="c-devices__empty">No se han encontrado pedidos.</td>
        </tr>
        <?php endif; ?>
        <?php foreach ( $pedidos as $pedido ) :
            $ped_id          = $pedido->ID;
            $pedido_numero   = get_post_meta( $ped_id, 'cpt-pedido__pedido-id', true );
            // Cliente de la BDD o escrito a mano en el pedido (ver
            // av_pedido_client_info(), inc/pedidos/cpt.php).
            $client_info     = av_pedido_client_info( $ped_id );
            $client_name     = $client_info['name'];
            $client_phone    = $client_info['phone'];
            // Solo el número, sin el prefijo de extensión (ver
            // components/c-pedido-form.php para el mismo patrón de split).
            $client_phone_local = $client_phone;
            if ( preg_match( '/^\d{1,4}\s+(.+)$/', $client_phone, $ppm ) ) {
                $client_phone_local = $ppm[1];
            }
            $client_wa_phone = str_replace( ' ', '', $client_phone );
            $total           = av_pedido_total( $ped_id ); // con IVA
            $status          = get_post_meta( $ped_id, 'cpt-pedido__status', true ) ?: 'pending';
            $entry_date      = get_post_meta( $ped_id, 'cpt-pedido__entry-date', true );
            $lines           = av_pedido_get_lines( $ped_id );
            $product_summary = av_pedido_product_summary( $lines );
            $supplier_summary = av_pedido_supplier_summary( $lines );
            $is_paid         = av_pedido_is_paid( $ped_id );

        ?>
        <tr class="c-list-cpt-sats__row">
            <td>
                <a class="c-list-cpt-sats__client-name" href="<?php echo esc_url( add_query_arg( 'id', $ped_id, av_pedidos_page_url() ) ); ?>">
                    <?php echo esc_html( $pedido_numero ); ?>
                </a>
            </td>
            <td class="c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $client_name ); ?>"><span class="c-list-cpt-sats__cell-truncate"><?php echo esc_html( $client_name ); ?></span></td>
            <td style="white-space:nowrap">
                <?php if ( $client_phone ) : ?>
                <div class="c-devices__actions c-pedidos__phone-cell">
                    <span><?php echo esc_html( $client_phone_local ); ?></span>
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Enviar WhatsApp"
                        href="<?php echo esc_url( 'https://wa.me/' . $client_wa_phone ); ?>"
                        target="_blank" rel="noopener">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </a>
                </div>
                <?php else : ?>
                —
                <?php endif; ?>
            </td>
            <td class="c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $product_summary ); ?>"><span class="c-list-cpt-sats__cell-truncate"><?php echo esc_html( $product_summary ); ?></span></td>
            <td><?php echo esc_html( $supplier_summary ); ?></td>
            <td style="white-space:nowrap">
                <span class="c-pedidos__status-badge c-pedidos__status-badge--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( av_pedido_status_label( $status ) ); ?></span>
            </td>
            <td style="white-space:nowrap">
                <span class="c-pedido-detail__paid-badge <?php echo $is_paid ? 'c-pedido-detail__paid-badge--ok' : 'c-pedido-detail__paid-badge--pte'; ?>">
                    <?php echo $is_paid ? 'Pagado' : 'Pendiente'; ?>
                </span>
            </td>
            <td style="white-space:nowrap"><strong><?php echo esc_html( number_format( $total, 2, ',', '.' ) ); ?> €</strong></td>
            <td><?php echo esc_html( $entry_date ?: '—' ); ?></td>
            <td>
                <!-- Un único acceso: el detalle es también la pantalla de edición
                     (modificar, recibir líneas, entregar, cobrar, facturar...). -->
                <div class="c-devices__actions">
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Abrir pedido"
                        href="<?php echo esc_url( add_query_arg( 'id', $ped_id, av_pedidos_page_url() ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
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
