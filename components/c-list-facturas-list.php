<?php
// Espera $facturas, $query (WP_Query) y $paged en el scope. En AJAX recibe
// ademas $facturas_page_url para construir la paginacion.
?>
<table>
    <thead>
        <tr>
            <th>Nº Factura</th>
            <th>Fecha</th>
            <th>Nº SAT</th>
            <th>Cliente</th>
            <th>Tipo de pago</th>
            <th>Base</th>
            <th>IVA 21%</th>
            <th>Total</th>
            <th>Pago</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if ( empty( $facturas ) ) : ?>
        <tr>
            <td colspan="10" style="text-align:center;padding:24px;opacity:.6;">
                No se ha encontrado ninguna factura con estos filtros.
            </td>
        </tr>
    <?php else : ?>
        <?php foreach ( $facturas as $fac ) :
            $fac_num    = get_post_meta( $fac->ID, '_factura_numero',    true );
            $fac_fecha  = get_post_meta( $fac->ID, '_factura_fecha',     true );
            $fac_sat_id    = get_post_meta( $fac->ID, '_factura_sat_id',    true );
            $fac_sat_n     = get_post_meta( $fac->ID, '_factura_sat_num',   true );
            $fac_cli    = get_post_meta( $fac->ID, '_factura_cliente',   true );
            $fac_base      = floatval( get_post_meta( $fac->ID, '_factura_base',       true ) );
            $fac_iva       = floatval( get_post_meta( $fac->ID, '_factura_iva',        true ) );
            $fac_total     = floatval( get_post_meta( $fac->ID, '_factura_total',      true ) );
            $fac_forma_pago = get_post_meta( $fac->ID, '_factura_forma_pago', true );
            // El cobro no se marca a mano: una factura esta pagada cuando el SAT
            // se finaliza con metodo de pago, que es lo que rellena forma_pago.
            $fac_pagado     = ! empty( $fac_forma_pago );

            // Factura manual (creada desde "Nueva factura", sin SAT ni pedido):
            // se edita y se imprime desde aquí mismo.
            $fac_manual = get_post_meta( $fac->ID, '_factura_manual', true ) === '1';

            $sat_url = $fac_sat_id ? get_permalink( intval( $fac_sat_id ) ) : '';

            // Este listado es solo de facturas de SAT (ver
            // av_build_facturas_query_args(), functions.php — las de Pedido se
            // consultan/imprimen desde el propio listado de Pedidos), así que
            // el PDF siempre usa av_sat_invoice_page(); es idempotente: abrirlo
            // no crea una factura nueva, solo reabre la que ya representa esta fila.
            $pdf_url = $fac_sat_id ? esc_url( add_query_arg( [
                'sat_invoice' => '1',
                'sat_id'      => intval( $fac_sat_id ),
                'nonce'       => wp_create_nonce( 'sat_invoice_' . intval( $fac_sat_id ) ),
            ], home_url( '/' ) ) ) : '';

            $edit_url = '';
            if ( $fac_manual ) {
                $pdf_url  = esc_url( add_query_arg( [
                    'factura_manual_pdf' => '1',
                    'factura_id'         => $fac->ID,
                    'nonce'              => wp_create_nonce( 'factura_manual_pdf_' . $fac->ID ),
                ], home_url( '/' ) ) );
                $edit_url = add_query_arg( 'id', $fac->ID, av_facturas_page_url() );
            }

            $fmt = fn( $n ) => number_format( $n, 2, ',', '.' ) . ' €';
        ?>
        <tr class="c-list-cpt-sats__row">
            <td><strong><?php echo esc_html( $fac_num ); ?></strong></td>
            <td><?php echo esc_html( $fac_fecha ); ?></td>
            <td>
                <?php if ( $fac_manual ) : ?>
                    <a href="<?php echo esc_url( $edit_url ); ?>">Manual</a>
                <?php elseif ( $sat_url ) : ?>
                    <a href="<?php echo esc_url( $sat_url ); ?>">#<?php echo esc_html( $fac_sat_n ); ?></a>
                <?php else : ?>
                    #<?php echo esc_html( $fac_sat_n ); ?>
                <?php endif; ?>
            </td>
            <td><?php echo esc_html( $fac_cli ); ?></td>
            <td><?php echo esc_html( $fac_forma_pago ? ucfirst( $fac_forma_pago ) : '—' ); ?></td>
            <td><?php echo esc_html( $fmt( $fac_base ) ); ?></td>
            <td><?php echo esc_html( $fmt( $fac_iva ) ); ?></td>
            <td><strong><?php echo esc_html( $fmt( $fac_total ) ); ?></strong></td>
            <td>
                <?php
                $tooltip = $fac_pagado
                    ? ucfirst( $fac_forma_pago )
                    : ( $fac_manual ? 'Se marca al indicar el tipo de pago en la factura' : 'Se marcará al finalizar el SAT con método de pago' );
                ?>
                <span class="fac-pago-badge c-list-cpt-sats__tooltip <?php echo $fac_pagado ? 'fac-pago-badge--ok' : 'fac-pago-badge--pte'; ?>"
                    data-tooltip="<?php echo esc_attr( $tooltip ); ?>">
                    <?php if ( $fac_pagado ) : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Pagado
                    <?php else : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Pendiente
                    <?php endif; ?>
                </span>
            </td>
            <td>
                <div class="c-list-cpt-sats__wrapper-client" style="gap:8px">
                    <?php if ( $edit_url ) : ?>
                    <a href="<?php echo esc_url( $edit_url ); ?>" class="c-list-cpt-sats__tooltip" data-tooltip="Editar factura">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ( $pdf_url ) : ?>
                    <a href="<?php echo $pdf_url; ?>" target="_blank" class="c-list-cpt-sats__tooltip" data-tooltip="Imprimir / Descargar PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php
if ( $query->max_num_pages > 1 ) {

    $pagination_base = ! empty( $facturas_page_url )
        ? add_query_arg( 'paged', 999999999, $facturas_page_url )
        : get_pagenum_link( 999999999 );

    echo '<div class="c-list-cpt-sats__pagination">';

    echo paginate_links(array(
        'base'      => str_replace( 999999999, '%#%', esc_url( $pagination_base ) ),
        'format'    => '?paged=%#%',
        'current'   => max( 1, intval( $paged ) ),
        'total'     => $query->max_num_pages,
        'prev_text' => '« Anterior',
        'next_text' => 'Siguiente »',
    ));

    echo '</div>';
}
?>
