<?php
// Tabla editable de líneas del pedido: MISMOS campos que
// av_presupuesto_lines_widget()/components/c-presupuesto-lines.php (Nombre,
// Descripción, Cantidad, IVA, Sin IVA, Con IVA, Subtotal), más una columna
// extra de Proveedor (con autocompletado de proveedores ya usados, sin crear
// ningún catálogo nuevo). Ya no se pide precio de compra/margen ni datos de
// proveedor más allá del nombre (referencia, nº de pedido, fecha estimada,
// notas por línea) — ver inc/pedidos/crud.php.
//
// El buscador de arriba es el mismo de siempre (av_ajax_search_articulos_picker,
// sin tocar); "+ Añadir fila" crea una fila en blanco para lo que no esté en
// el catálogo.
//
// Espera $lines_json (string JSON o '') en el scope, ya puesto por
// c-pedido-form.php.
?>
<?php
// "Recepción" por línea (marcar recibido → entra stock): solo al editar un
// pedido ya guardado y mientras no esté cancelado. Ver av_pedido_lines_widget()
// en src/js/app.js y av_ajax_pedido_line_received() en inc/pedidos/crud.php.
$show_receive = ! empty( $p_id ) && ( $status ?? '' ) !== 'cancelled';
?>
<div class="c-pedido-lines-table js-pedido-lines-widget"
     data-can-receive="<?php echo $show_receive ? '1' : '0'; ?>"
     data-pedido-id="<?php echo esc_attr( $p_id ?? 0 ); ?>"
     data-nonce="<?php echo esc_attr( wp_create_nonce( 'av_pedido_nonce' ) ); ?>">

    <div class="c-pedido-lines-table__toolbar">
        <div class="c-pedido-lines-table__search-wrap">
            <input type="text" class="c-list-cpt-sats__search js-pedido-line-search" placeholder="Buscar artículo por nombre, SKU, EAN, marca, modelo..." autocomplete="off">
            <div class="c-pedido-lines-table__results js-pedido-line-results is-hidden"></div>
        </div>
    </div>

    <div class="c-pedido-lines-table__scroll">
        <table class="c-pedido-lines-table__table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="c-pedido-lines-table__col-qty">Cant.</th>
                    <th class="c-pedido-lines-table__col-vat">IVA</th>
                    <th class="c-pedido-lines-table__col-price">Sin IVA</th>
                    <th class="c-pedido-lines-table__col-price">Con IVA</th>
                    <th class="c-pedido-lines-table__col-price">Subtotal</th>
                    <th class="c-pedido-lines-table__col-supplier">Proveedor</th>
                    <?php if ( $show_receive ) : ?><th class="c-pedido-lines-table__col-receive">Recepción</th><?php endif; ?>
                    <th class="c-pedido-lines-table__col-remove"></th>
                </tr>
            </thead>
            <tbody class="js-pedido-lines-tbody"></tbody>
            <tfoot>
                <tr class="c-pedido-lines-table__add-row">
                    <td colspan="<?php echo $show_receive ? 10 : 9; ?>">
                        <button type="button" class="c-pedido-lines-table__add-btn js-pedido-line-add-blank">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Añadir fila</span>
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <p class="c-devices__error js-pedido-lines-error is-hidden">Añade al menos una línea al pedido.</p>

    <input type="hidden" name="lines" class="js-pedido-lines-hidden" value="<?php echo esc_attr( $lines_json ); ?>">

    <script type="application/json" class="js-pedido-vat-choices"><?php echo wp_json_encode( av_pedido_vat_choices() ); ?></script>

</div>
