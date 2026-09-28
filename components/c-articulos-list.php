<?php
// Espera $posts (WP_Query) y $articulos (array de WP_Post) en el scope. Usado
// tanto en la carga normal de la página como en la respuesta del buscador
// asíncrono (AJAX).
$stock_state_labels = [
    'ok'  => 'Stock correcto',
    'low' => 'Stock bajo',
    'out' => 'Sin stock',
];
$stock_state_icons = [
    'ok'  => '🟢',
    'low' => '🟠',
    'out' => '🔴',
];
?>
<table>
    <thead>
        <tr>
            <th></th>
            <th>Artículo</th>
            <th>SKU</th>
            <th>Categoría</th>
            <th>Stock</th>
            <th>Stock mín.</th>
            <th>Coste</th>
            <th>PVP</th>
            <th>Margen</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ( empty( $articulos ) ) : ?>
        <tr class="c-list-cpt-sats__row">
            <td colspan="11" class="c-devices__empty">No se han encontrado artículos.</td>
        </tr>
        <?php endif; ?>
        <?php foreach ( $articulos as $articulo ) :
            $a_id        = $articulo->ID;
            $sku         = get_post_meta( $a_id, 'cpt-articulo__sku', true );
            $image       = get_post_meta( $a_id, 'cpt-articulo__image', true );
            $status      = get_post_meta( $a_id, 'cpt-articulo__status', true ) ?: 'active';
            $stock       = floatval( get_post_meta( $a_id, 'cpt-articulo__stock', true ) );
            $stock_min   = floatval( get_post_meta( $a_id, 'cpt-articulo__stock-min', true ) );
            $sale_price  = get_post_meta( $a_id, 'cpt-articulo__sale-price', true );
            $terms       = get_the_terms( $a_id, 'articulo_categoria' );
            $categoria   = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : '—';
            $stock_state = av_articulo_stock_state( $stock, $stock_min );
            $margin      = av_articulo_margin( $a_id );
            $cost        = av_articulo_average_cost( $a_id );
        ?>
        <tr class="c-list-cpt-sats__row">
            <td>
                <?php if ( $image ) : ?>
                <img class="c-articulos__thumb" src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy">
                <?php else : ?>
                <span class="c-articulos__thumb c-articulos__thumb--empty" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </span>
                <?php endif; ?>
            </td>
            <td class="c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $articulo->post_title ); ?>"><span class="c-list-cpt-sats__cell-truncate"><?php echo esc_html( $articulo->post_title ); ?></span></td>
            <td><?php echo esc_html( $sku ); ?></td>
            <td><?php echo esc_html( $categoria ); ?></td>
            <td>
                <span class="c-articulos__stock-badge c-articulos__stock-badge--<?php echo esc_attr( $stock_state ); ?> c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $stock_state_labels[ $stock_state ] ); ?>">
                    <?php echo $stock_state_icons[ $stock_state ]; ?> <?php echo esc_html( av_articulo_format_qty( $stock ) ); ?>
                </span>
            </td>
            <td><?php echo esc_html( av_articulo_format_qty( $stock_min ) ); ?></td>
            <td><?php echo $cost > 0 ? esc_html( number_format( $cost, 2, ',', '.' ) ) . ' €' : '—'; ?></td>
            <td><?php echo $sale_price !== '' ? esc_html( number_format( floatval( $sale_price ), 2, ',', '.' ) ) . ' €' : '—'; ?></td>
            <td class="<?php echo $margin['amount'] < 0 ? 'c-articulos__margin--negative' : ''; ?>">
                <?php echo $sale_price !== '' ? esc_html( number_format( $margin['amount'], 2, ',', '.' ) ) . ' € (' . esc_html( number_format( $margin['percent'], 1, ',', '.' ) ) . '%)' : '—'; ?>
            </td>
            <td>
                <span class="c-articulos__status-badge c-articulos__status-badge--<?php echo esc_attr( $status ); ?>"><?php echo $status === 'active' ? 'Activo' : 'Inactivo'; ?></span>
            </td>
            <td>
                <div class="c-devices__actions">
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                        data-tooltip="Editar"
                        href="<?php echo esc_url( add_query_arg( 'edit', $a_id, av_articulos_page_url() ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <button type="button" class="c-devices__action-btn js-articulo-stock-open c-list-cpt-sats__tooltip"
                        data-tooltip="Ajustar stock"
                        data-id="<?php echo esc_attr( $a_id ); ?>"
                        data-nombre="<?php echo esc_attr( $articulo->post_title ); ?>"
                        data-stock="<?php echo esc_attr( $stock ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"/><line x1="16" y1="8" x2="2" y2="22"/><line x1="17.5" y1="15" x2="9" y2="15"/></svg>
                    </button>
                    <?php if ( $status === 'active' ) : ?>
                    <a class="c-devices__action-btn c-devices__action-btn--danger c-list-cpt-sats__tooltip"
                       data-tooltip="Desactivar"
                       href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'av_eliminar_articulo', 'id' => $a_id ], admin_url( 'admin-post.php' ) ), 'av_eliminar_articulo_' . $a_id ) ); ?>"
                       onclick="return confirm('¿Desactivar «<?php echo esc_js( $articulo->post_title ); ?>»? Dejará de poder usarse en nuevos SATs, pero no se borra ni pierde su historial.');">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </a>
                    <?php else : ?>
                    <a class="c-devices__action-btn c-list-cpt-sats__tooltip"
                       data-tooltip="Reactivar"
                       href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'av_reactivar_articulo', 'id' => $a_id ], admin_url( 'admin-post.php' ) ), 'av_reactivar_articulo_' . $a_id ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
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

    $pagination_base = ! empty( $articulos_page_url )
        ? add_query_arg( 'paged', 999999999, $articulos_page_url )
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
