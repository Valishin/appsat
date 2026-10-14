<?php
// Tabla de líneas "Artículos y servicios" del SAT: MISMA tabla de Pedidos
// (components/c-pedido-lines.php) — se reutilizan tal cual sus clases
// c-pedido-lines-table__* (cero CSS nuevo) — pero sin columna "Recepción"
// (no aplica al SAT) y con el buscador combinado Artículos + Servicios que ya
// usa Presupuestos (components/c-presupuesto-lines.php). Sustituye a los
// antiguos widgets de texto libre "Reparación" (cpt-sat__repair) y "Piezas
// pedidas" (cpt-sat__ordered-parts).
//
// NO es "Artículos utilizados" (más abajo en c-sat-form.php): esa sigue
// siendo su propia tabla aparte porque descuenta stock real del catálogo.
//
// Espera $lines_json (string JSON o '') en el scope.
?>
<div class="c-pedido-lines-table js-sat-lines-widget"
     data-nonce="<?php echo esc_attr( wp_create_nonce( 'av_pedido_nonce' ) ); ?>">

    <div class="c-pedido-lines-table__toolbar">
        <div class="c-pedido-lines-table__search-wrap">
            <input type="text" class="c-list-cpt-sats__search js-sat-lines-search" placeholder="Busca un artículo o servicio guardado..." autocomplete="off">
            <div class="c-pedido-lines-table__results js-sat-lines-results is-hidden"></div>
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
                    <th class="c-pedido-lines-table__col-remove"></th>
                </tr>
            </thead>
            <tbody class="js-sat-lines-tbody"></tbody>
            <tfoot>
                <tr class="c-pedido-lines-table__add-row">
                    <td colspan="9">
                        <button type="button" class="c-pedido-lines-table__add-btn js-sat-lines-add-blank">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Añadir fila</span>
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <input type="hidden" name="sat-lines" class="js-sat-lines-hidden" value="<?php echo esc_attr( $lines_json ); ?>">

    <script type="application/json" class="js-sat-lines-vat-choices"><?php echo wp_json_encode( av_pedido_vat_choices() ); ?></script>

</div>
