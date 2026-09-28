<?php
// Tabla editable de líneas del presupuesto (estilo hoja de cálculo): cada
// fila es un artículo o servicio, catalogado o no, con SKU/nombre/descripción/
// cantidad/IVA/precio editables directamente en la celda. El precio tiene dos
// columnas sincronizadas (con IVA / sin IVA): se puede escribir cualquiera de
// las dos y la otra se recalcula sola — ver av_presupuesto_lines_widget() en
// src/js/app.js. Lo que se guarda siempre es sale_price sin IVA + vat.
//
// El buscador de arriba consulta a la vez Artículos (av_ajax_search_articulos_picker)
// y Servicios (av_ajax_search_servicios_picker), sin tocar ninguno de los dos
// endpoints; "+ Añadir fila" crea una fila en blanco para lo que no esté en
// ningún catálogo.
//
// Espera $lines_json (string JSON o '') en el scope, ya puesto por
// c-presupuesto-form.php.
?>
<div class="c-presupuesto-table js-presupuesto-lines-widget">

    <div class="c-presupuesto-table__toolbar">
        <div class="c-presupuesto-table__search-wrap">
            <input type="text" class="c-list-cpt-sats__search js-presupuesto-line-search" placeholder="Buscar artículo o servicio por nombre o SKU..." autocomplete="off">
            <div class="c-presupuesto-table__results js-presupuesto-line-results is-hidden"></div>
        </div>
    </div>

    <div class="c-presupuesto-table__scroll">
        <table class="c-presupuesto-table__table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="c-presupuesto-table__col-qty">Cant.</th>
                    <th class="c-presupuesto-table__col-vat">IVA</th>
                    <th class="c-presupuesto-table__col-price">Sin IVA</th>
                    <th class="c-presupuesto-table__col-price">Con IVA</th>
                    <th class="c-presupuesto-table__col-price">Subtotal</th>
                    <th class="c-presupuesto-table__col-remove"></th>
                </tr>
            </thead>
            <tbody class="js-presupuesto-lines-tbody"></tbody>
            <tfoot>
                <tr class="c-presupuesto-table__add-row">
                    <td colspan="8">
                        <button type="button" class="c-presupuesto-table__add-btn js-presupuesto-line-add-blank">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Añadir fila</span>
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <p class="c-devices__error js-presupuesto-lines-error is-hidden">Añade al menos una línea al presupuesto.</p>

    <input type="hidden" name="lines" class="js-presupuesto-lines-hidden" value="<?php echo esc_attr( $lines_json ); ?>">

    <script type="application/json" class="js-presupuesto-vat-choices"><?php echo wp_json_encode( av_articulo_vat_choices() ); ?></script>

</div>
