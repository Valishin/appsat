<?php
// Listado de compras a particulares (ver inc/compras/compras.php). Espera
// $query (WP_Query) y $paged en el scope. Filtros por GET (sin AJAX).
$compras = $query->posts;
$f_buscar = sanitize_text_field( $_GET['buscar-compra'] ?? '' );
$f_desde  = sanitize_text_field( $_GET['fecha-desde'] ?? '' );
$f_hasta  = sanitize_text_field( $_GET['fecha-hasta'] ?? '' );
$hay_filtros = $f_buscar !== '' || $f_desde !== '' || $f_hasta !== '';
$fmt = fn( $n ) => number_format( floatval( $n ), 2, ',', '.' ) . ' €';
?>
<div class="c-list-cpt-sats c-pedidos">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">

                <div class="c-list-cpt-sats__toolbar">
                    <div class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                        <span class="c-list-cpt-sats__count">Total <?php echo intval( $query->found_posts ); ?> compras</span>
                    </div>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <a class="o-button o-button--style-2" href="<?php echo esc_url( add_query_arg( 'condiciones', '1', av_compras_page_url() ) ); ?>">Condiciones del recibo</a>
                    <?php endif; ?>
                </div>

                <form class="c-compras-filter" method="GET" action="<?php echo esc_url( av_compras_page_url() ); ?>">
                    <div class="c-compras-filter__field">
                        <label class="c-list-cpt-sats__field-label" for="f-buscar-compra">Buscar</label>
                        <input id="f-buscar-compra" class="c-list-cpt-sats__search" type="text" name="buscar-compra" value="<?php echo esc_attr( $f_buscar ); ?>" placeholder="Nº, vendedor, DNI, artículo, IMEI...">
                    </div>
                    <div class="c-compras-filter__field">
                        <label class="c-list-cpt-sats__field-label" for="f-compra-desde">Desde</label>
                        <input id="f-compra-desde" class="c-list-cpt-sats__search" type="date" name="fecha-desde" value="<?php echo esc_attr( $f_desde ); ?>">
                    </div>
                    <div class="c-compras-filter__field">
                        <label class="c-list-cpt-sats__field-label" for="f-compra-hasta">Hasta</label>
                        <input id="f-compra-hasta" class="c-list-cpt-sats__search" type="date" name="fecha-hasta" value="<?php echo esc_attr( $f_hasta ); ?>">
                    </div>
                    <div class="c-compras-filter__actions">
                        <button type="submit" class="o-button o-button--style-1">Buscar</button>
                        <?php if ( $hay_filtros ) : ?>
                        <a class="o-button o-button--style-2" href="<?php echo esc_url( av_compras_page_url() ); ?>">Limpiar</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="c-list-cpt-sats__results-wrapper">
                    <div class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nº</th>
                                    <th>Fecha</th>
                                    <th>Vendedor</th>
                                    <th>DNI / NIE</th>
                                    <th>Artículos</th>
                                    <th>Pago</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ( empty( $compras ) ) : ?>
                                <tr><td colspan="8" class="c-devices__empty">No se han encontrado compras.</td></tr>
                            <?php endif; ?>
                            <?php foreach ( $compras as $c ) :
                                $cid      = $c->ID;
                                $edit_url = add_query_arg( 'id', $cid, av_compras_page_url() );
                                $print    = add_query_arg( [
                                    'compra_print' => '1',
                                    'compra_id'    => $cid,
                                    'nonce'        => wp_create_nonce( 'compra_print_' . $cid ),
                                ], home_url( '/' ) );
                                $items    = av_compra_get_items( $cid );
                                $summary  = av_compra_items_summary( $items );
                                $forma    = get_post_meta( $cid, '_compra_forma_pago', true );
                            ?>
                                <tr class="c-list-cpt-sats__row">
                                    <td><a class="c-list-cpt-sats__client-name" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( get_post_meta( $cid, '_compra_numero', true ) ); ?></a></td>
                                    <td style="white-space:nowrap"><?php echo esc_html( get_post_meta( $cid, '_compra_fecha', true ) ); ?></td>
                                    <td><?php echo esc_html( get_post_meta( $cid, '_compra_vendedor_nombre', true ) ); ?></td>
                                    <td><?php echo esc_html( get_post_meta( $cid, '_compra_vendedor_dni', true ) ); ?></td>
                                    <td class="c-list-cpt-sats__tooltip" data-tooltip="<?php echo esc_attr( $summary ); ?>"><span class="c-list-cpt-sats__cell-truncate"><?php echo esc_html( $summary ); ?></span></td>
                                    <td><?php echo esc_html( av_compra_payment_choices()[ $forma ] ?? '—' ); ?></td>
                                    <td style="white-space:nowrap"><strong><?php echo esc_html( $fmt( get_post_meta( $cid, '_compra_total', true ) ) ); ?></strong></td>
                                    <td>
                                        <div class="c-devices__actions">
                                            <a class="c-devices__action-btn c-list-cpt-sats__tooltip" data-tooltip="Editar compra" href="<?php echo esc_url( $edit_url ); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                            </a>
                                            <a class="c-devices__action-btn c-list-cpt-sats__tooltip" data-tooltip="Imprimir hoja" target="_blank" href="<?php echo esc_url( $print ); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php
                        if ( $query->max_num_pages > 1 ) {
                            $base = add_query_arg( array_merge( array_filter( [
                                'buscar-compra' => $f_buscar, 'fecha-desde' => $f_desde, 'fecha-hasta' => $f_hasta,
                            ] ), [ 'paged' => 999999999 ] ), av_compras_page_url() );
                            echo '<div class="c-list-cpt-sats__pagination">';
                            echo paginate_links( [
                                'base'      => str_replace( 999999999, '%#%', esc_url( $base ) ),
                                'format'    => '?paged=%#%',
                                'current'   => max( 1, intval( $paged ) ),
                                'total'     => $query->max_num_pages,
                                'prev_text' => '« Anterior',
                                'next_text' => 'Siguiente »',
                            ] );
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- "Nueva compra" fijo abajo a la derecha (mismo componente/CSS que "Nuevo pedido"). -->
                <div class="c-sat-form__wrapper-ctas">
                    <div class="c-sat-form__ctas-inner">
                        <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary c-sat-form__ctas-group--solo">
                            <a href="<?php echo esc_url( add_query_arg( 'new', '1', av_compras_page_url() ) ); ?>" class="c-sat-form__cta c-sat-form__cta--primary">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Nueva compra</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
