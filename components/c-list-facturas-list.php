<?php
// Espera $facturas, $query (WP_Query) y $paged en el scope. En AJAX recibe
// ademas $facturas_page_url para construir la paginacion.
?>
<table>
    <thead>
        <tr>
            <th>Nº Factura</th>
            <th>Fecha</th>
            <th>SAT</th>
            <th>Cliente</th>
            <th>Equipo</th>
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
            $fac_sat_id = get_post_meta( $fac->ID, '_factura_sat_id',    true );
            $fac_sat_n  = get_post_meta( $fac->ID, '_factura_sat_num',   true );
            $fac_cli    = get_post_meta( $fac->ID, '_factura_cliente',   true );
            $fac_tipo   = get_post_meta( $fac->ID, '_factura_tipo',      true );
            $fac_modelo = get_post_meta( $fac->ID, '_factura_modelo',    true );
            $fac_base      = floatval( get_post_meta( $fac->ID, '_factura_base',       true ) );
            $fac_iva       = floatval( get_post_meta( $fac->ID, '_factura_iva',        true ) );
            $fac_total     = floatval( get_post_meta( $fac->ID, '_factura_total',      true ) );
            $fac_forma_pago = get_post_meta( $fac->ID, '_factura_forma_pago', true );
            // El cobro no se marca a mano: una factura esta pagada cuando el SAT
            // se finaliza con metodo de pago, que es lo que rellena forma_pago.
            $fac_pagado     = ! empty( $fac_forma_pago );

            $sat_url     = $fac_sat_id ? get_permalink( intval( $fac_sat_id ) ) : '';
            $pdf_url     = $fac_sat_id ? esc_url( add_query_arg( [
                'sat_invoice' => '1',
                'sat_id'      => intval( $fac_sat_id ),
                'nonce'       => wp_create_nonce( 'sat_invoice_' . intval( $fac_sat_id ) ),
            ], home_url( '/' ) ) ) : '';

            $fmt = fn( $n ) => number_format( $n, 2, ',', '.' ) . ' €';
        ?>
        <tr class="c-list-cpt-sats__row">
            <td><strong><?php echo esc_html( $fac_num ); ?></strong></td>
            <td><?php echo esc_html( $fac_fecha ); ?></td>
            <td>
                <?php if ( $sat_url ) : ?>
                    <a href="<?php echo esc_url( $sat_url ); ?>">#<?php echo esc_html( $fac_sat_n ); ?></a>
                <?php else : ?>
                    #<?php echo esc_html( $fac_sat_n ); ?>
                <?php endif; ?>
            </td>
            <td><?php echo esc_html( $fac_cli ); ?></td>
            <td title="<?php echo esc_attr( $fac_modelo ); ?>"><?php echo esc_html( $fac_tipo ); ?></td>
            <td><?php echo esc_html( $fmt( $fac_base ) ); ?></td>
            <td><?php echo esc_html( $fmt( $fac_iva ) ); ?></td>
            <td><strong><?php echo esc_html( $fmt( $fac_total ) ); ?></strong></td>
            <td>
                <?php
                $tooltip = $fac_pagado
                    ? ucfirst( $fac_forma_pago )
                    : 'Se marcará al finalizar el SAT con método de pago';
                ?>
                <span class="fac-pago-badge <?php echo $fac_pagado ? 'fac-pago-badge--ok' : 'fac-pago-badge--pte'; ?>"
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
                    <?php if ( $sat_url ) : ?>
                    <a href="<?php echo esc_url( $sat_url ); ?>" title="Ir al SAT">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ( $pdf_url ) : ?>
                    <a href="<?php echo $pdf_url; ?>" target="_blank" title="Imprimir / Descargar PDF">
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
