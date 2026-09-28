<?php
// Espera $posts (WP_Query) y $presupuestos (array de WP_Post) en el scope.
// Usado tanto en la carga normal de la página como en la respuesta del
// buscador asíncrono (AJAX). Mismo patrón que components/c-pedidos-list.php.
?>
<table>
    <thead>
        <tr>
            <th>Número</th>
            <th>Cliente</th>
            <th>Artículos</th>
            <th>Sin IVA</th>
            <th>Con IVA</th>
            <th>Fecha</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ( empty( $presupuestos ) ) : ?>
        <tr class="c-list-cpt-sats__row">
            <td colspan="7" class="c-devices__empty">No se han encontrado presupuestos.</td>
        </tr>
        <?php endif; ?>
        <?php foreach ( $presupuestos as $presupuesto ) :
            $pr_id           = $presupuesto->ID;
            $pr_numero       = get_post_meta( $pr_id, 'cpt-presupuesto__presupuesto-id', true );
            $client_id       = intval( get_post_meta( $pr_id, 'cpt-presupuesto__client-id', true ) );
            $client_name     = av_presupuesto_resolve_client_name( $client_id );
            $entry_date      = get_post_meta( $pr_id, 'cpt-presupuesto__entry-date', true );
            $lines           = av_presupuesto_get_lines( $pr_id );
            $product_summary = av_presupuesto_product_summary( $lines );

            $data  = av_presupuesto_build_data( $pr_id );
            $base  = $data['base'];
            $total = $data['total'];

            $pdf_url = esc_url( add_query_arg( [
                'presupuesto_pdf' => '1',
                'presupuesto_id'  => $pr_id,
                'nonce'           => wp_create_nonce( 'presupuesto_pdf_' . $pr_id ),
            ], home_url( '/' ) ) );

            $delete_url = esc_url( wp_nonce_url(
                add_query_arg( [ 'action' => 'av_eliminar_presupuesto', 'id' => $pr_id ], admin_url( 'admin-post.php' ) ),
                'av_eliminar_presupuesto_' . $pr_id
            ) );
        ?>
        <tr class="c-list-cpt-sats__row">
            <td>
                <a class="c-list-cpt-sats__client-name" href="<?php echo esc_url( add_query_arg( 'id', $pr_id, av_presupuestos_page_url() ) ); ?>">
                    <?php echo esc_html( $pr_numero ); ?>
                </a>
            </td>
            <td class="c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $client_name ); ?>"><span class="c-list-cpt-sats__cell-truncate"><?php echo esc_html( $client_name ); ?></span></td>
            <td class="c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $product_summary ); ?>"><span class="c-list-cpt-sats__cell-truncate"><?php echo esc_html( $product_summary ); ?></span></td>
            <td><?php echo esc_html( number_format( $base, 2, ',', '.' ) ); ?> €</td>
            <td><strong><?php echo esc_html( number_format( $total, 2, ',', '.' ) ); ?> €</strong></td>
            <td><?php echo esc_html( $entry_date ?: '—' ); ?></td>
            <td>
                <div class="c-devices__actions">
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Editar"
                        href="<?php echo esc_url( add_query_arg( 'id', $pr_id, av_presupuestos_page_url() ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Imprimir / PDF"
                        href="<?php echo $pdf_url; ?>"
                        target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    </a>
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Eliminar"
                        href="<?php echo $delete_url; ?>"
                        onclick="return confirm('¿Eliminar este presupuesto? No se puede deshacer.');">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
if ( $posts->max_num_pages > 1 ) {

    $pagination_base = ! empty( $presupuestos_page_url )
        ? add_query_arg( 'paged', 999999999, $presupuestos_page_url )
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
