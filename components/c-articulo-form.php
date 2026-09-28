<?php
// Formulario "Nuevo artículo" / "Editar artículo". Espera $editando (WP_Post
// o null) en el scope. A página completa (no modal, a diferencia de
// Dispositivos/Servicios): son demasiadas secciones para una modal pequeña.

$a_id = $editando ? $editando->ID : 0;

$get_meta = function( $key, $default = '' ) use ( $a_id ) {
    if ( ! $a_id ) return $default;
    $v = get_post_meta( $a_id, $key, true );
    return $v !== '' ? $v : $default;
};

$nombre        = $editando ? $editando->post_title : '';
$sku           = $get_meta( 'cpt-articulo__sku' );
$barcode       = $get_meta( 'cpt-articulo__barcode' );
$description   = $get_meta( 'cpt-articulo__description' );
$brand         = $get_meta( 'cpt-articulo__brand' );
$model         = $get_meta( 'cpt-articulo__model' );
$image         = $get_meta( 'cpt-articulo__image' );
$status        = $get_meta( 'cpt-articulo__status', 'active' );
$stock         = $get_meta( 'cpt-articulo__stock', 0 );
$stock_min     = $get_meta( 'cpt-articulo__stock-min', 0 );
$stock_max     = $get_meta( 'cpt-articulo__stock-max' );
$location      = $get_meta( 'cpt-articulo__location' );
$unit          = $get_meta( 'cpt-articulo__unit', 'unit' );
$allow_negative = $get_meta( 'cpt-articulo__allow-negative-stock' ) === '1';
$purchase_price = $get_meta( 'cpt-articulo__purchase-price' );
$sale_price     = $get_meta( 'cpt-articulo__sale-price' );
$vat            = $get_meta( 'cpt-articulo__vat', '21' );
$supplier          = $get_meta( 'cpt-articulo__supplier' );
$supplier_ref      = $get_meta( 'cpt-articulo__supplier-reference' );
$supplier_price    = $get_meta( 'cpt-articulo__supplier-price' );
$supplier_url      = $get_meta( 'cpt-articulo__supplier-url' );
$delivery_days     = $get_meta( 'cpt-articulo__delivery-days' );
$usable_in_sat  = $a_id ? ( $get_meta( 'cpt-articulo__usable-in-sat' ) === '1' ) : true;
$sellable       = $a_id ? ( $get_meta( 'cpt-articulo__sellable' ) === '1' ) : true;
$deduct_stock   = $a_id ? ( $get_meta( 'cpt-articulo__deduct-stock' ) === '1' ) : true;
$article_type   = $get_meta( 'cpt-articulo__article-type', 'part' );
$internal_notes = $get_meta( 'cpt-articulo__internal-notes' );

// Datos de componente electrónico (solo relevantes/visibles cuando la
// categoría es "Componentes electrónicos", pero se leen siempre: si el
// artículo tuviera esos datos guardados de una categoría anterior, no se
// pierden aunque ahora mismo no se muestren).
$component_type        = $get_meta( 'cpt-articulo__component-type' );
$marking_code           = $get_meta( 'cpt-articulo__marking-code' );
$package                = $get_meta( 'cpt-articulo__package' );
$board_reference        = $get_meta( 'cpt-articulo__board-reference' );
$component_specs        = $get_meta( 'cpt-articulo__component-specs' );
$component_equivalents  = $get_meta( 'cpt-articulo__component-equivalents' );
$component_application  = $get_meta( 'cpt-articulo__component-application' );

$categoria_actual_id = 0;
if ( $a_id ) {
    $terms = get_the_terms( $a_id, 'articulo_categoria' );
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        $categoria_actual_id = $terms[0]->term_id;
    }
}
$es_electronica_actual = av_articulo_is_electronics_category( $categoria_actual_id );

$errores_articulo = [
    'missing'                    => 'Indica al menos el nombre del artículo.',
    'categoria-requerida'        => 'Elige una categoría: el SKU se genera automáticamente a partir de ella.',
    'tipo-componente-requerido'  => 'Para «Componentes electrónicos» indica el tipo de componente: también determina el prefijo del SKU.',
    'sku-generation-failed'      => 'No se ha podido generar un SKU único. Inténtalo de nuevo.',
    'barcode-duplicado'          => 'Ya existe un artículo con ese código de barras.',
    'iva-invalido'               => 'El IVA indicado no es válido.',
    'precio-invalido'            => 'Revisa los precios: deben ser números válidos y no negativos.',
    'stock-invalido'             => 'Revisa las cantidades de stock: no pueden ser negativas (salvo que actives «Permitir stock negativo»).',
    'ajuste-invalido'            => 'Indica una cantidad y un motivo para el ajuste de stock.',
    'unknown'                    => 'No se ha podido completar la acción.',
];

$margin = $a_id ? av_articulo_margin( $a_id ) : [ 'amount' => 0, 'percent' => 0 ];
$cost   = $a_id ? av_articulo_average_cost( $a_id ) : 0;
?>
<div class="c-devices__toolbar">
    <a href="<?php echo esc_url( av_articulos_page_url() ); ?>" class="c-articulo-form__back">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span>Volver al listado</span>
    </a>
</div>

<?php if ( isset( $_GET['error'] ) ) :
    $err_slug = sanitize_text_field( $_GET['error'] );
    $err_msg  = $err_slug === 'stock-insuficiente' && ! empty( $_GET['msg'] )
        ? sanitize_text_field( rawurldecode( $_GET['msg'] ) )
        : ( $errores_articulo[ $err_slug ] ?? $errores_articulo['unknown'] );
?>
<div class="c-devices__error"><?php echo esc_html( $err_msg ); ?></div>
<?php endif; ?>

<form class="c-articulo-form js-articulo-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
    <?php wp_nonce_field( 'av_articulo_nonce', 'nonce' ); ?>
    <input type="hidden" name="action" value="av_guardar_articulo">
    <input type="hidden" name="id" value="<?php echo esc_attr( $a_id ); ?>">

    <div class="c-articulo-form__head">
        <h1 class="c-articulo-form__title"><?php echo $editando ? 'Editar artículo' : 'Nuevo artículo'; ?></h1>
    </div>

    <!-- ── Identificación ─────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Identificación</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-categoria">Categoría</label>
                <div class="c-devices__category-row">
                    <select id="art-categoria" class="c-list-cpt-sats__search js-articulo-categoria-select" name="category" <?php echo ! $editando ? 'required' : ''; ?>>
                        <option value="">Sin categoría</option>
                        <?php foreach ( av_get_articulo_categorias() as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>" data-slug="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $categoria_actual_id, $cat->term_id ); ?>><?php echo esc_html( $cat->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="c-devices__category-add-btn js-articulo-category-add" title="Crear una categoría nueva">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                </div>
                <div class="c-devices__category-new js-articulo-category-new is-hidden">
                    <input type="text" class="c-list-cpt-sats__search js-articulo-category-new-input" placeholder="Nombre de la categoría nueva">
                    <input type="text" class="c-list-cpt-sats__search js-articulo-category-new-prefix" placeholder="Prefijo (ej. TOO)" maxlength="6" style="text-transform:uppercase;max-width:140px;">
                    <button type="button" class="c-devices__category-new-confirm js-articulo-category-new-confirm">Añadir</button>
                    <button type="button" class="c-devices__category-new-cancel js-articulo-category-new-cancel" aria-label="Cancelar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <small class="c-devices__category-error js-articulo-category-error"></small>
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-sku">SKU</label>
                <input id="art-sku" class="c-list-cpt-sats__search js-articulo-sku-field" type="text" name="sku"
                    placeholder="<?php echo $editando ? '' : 'Se generará al seleccionar una categoría'; ?>"
                    value="<?php echo esc_attr( $sku ); ?>" readonly data-mode="<?php echo $editando ? 'edit' : 'create'; ?>">
                <small class="js-articulo-sku-hint<?php echo ( $editando || $sku ) ? '' : ' is-hidden'; ?>">Generado automáticamente. No cambia aunque modifiques la categoría.</small>
            </div>
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="art-nombre">Nombre del artículo</label>
                <input id="art-nombre" class="c-list-cpt-sats__search" type="text" name="nombre" placeholder="Ej. Batería iPhone 11" value="<?php echo esc_attr( $nombre ); ?>" required>
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-barcode">Código de barras / EAN</label>
                <input id="art-barcode" class="c-list-cpt-sats__search" type="text" name="barcode" value="<?php echo esc_attr( $barcode ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-marca">Fabricante</label>
                <input id="art-marca" class="c-list-cpt-sats__search" type="text" name="brand" placeholder="Ej. Apple, ANPEC..." value="<?php echo esc_attr( $brand ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-modelo">Modelo / MPN (Part Number)</label>
                <input id="art-modelo" class="c-list-cpt-sats__search" type="text" name="model" placeholder="Ej. APW8713" value="<?php echo esc_attr( $model ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-estado">Estado</label>
                <select id="art-estado" class="c-list-cpt-sats__search" name="status">
                    <option value="active" <?php selected( $status, 'active' ); ?>>Activo</option>
                    <option value="inactive" <?php selected( $status, 'inactive' ); ?>>Inactivo</option>
                </select>
            </div>
            <?php
            $photo_field = [ 'name' => 'image', 'label' => 'Imagen', 'value' => $image, 'display' => 'thumb', 'max' => 1 ];
            include locate_template( 'components/c-sat-photo-field.php' );
            ?>
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="art-descripcion">Descripción</label>
                <textarea id="art-descripcion" class="c-list-cpt-sats__search" name="description" rows="3"><?php echo esc_textarea( $description ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- ── Datos componente electrónico (solo si la categoría es "Componentes
         electrónicos"; oculto por CSS para el resto, nunca se borran sus
         valores al cambiar de categoría) ──────────────────────────────── -->
    <div class="c-sat-form__accordion js-articulo-component-section<?php echo $es_electronica_actual ? '' : ' is-hidden'; ?>">
        <div class="c-sat-form__accordion-title">Datos del componente electrónico</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-component-type">Tipo de componente</label>
                <select id="art-component-type" class="c-list-cpt-sats__search js-articulo-component-type" name="component-type" <?php echo $es_electronica_actual ? 'required' : ''; ?>>
                    <option value="">Seleccione...</option>
                    <?php foreach ( av_articulo_component_type_choices() as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $component_type, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Determina también el prefijo del SKU (IC-, MOS-, DIO-…).</small>
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-marking-code">Código de marcado</label>
                <input id="art-marking-code" class="c-list-cpt-sats__search" type="text" name="marking-code" placeholder="Ej. VR489" value="<?php echo esc_attr( $marking_code ); ?>">
                <small>El texto grabado físicamente en el componente. No es el MPN.</small>
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-package">Encapsulado</label>
                <input id="art-package" class="c-list-cpt-sats__search" type="text" name="package" list="art-package-suggestions" placeholder="Ej. QFN" value="<?php echo esc_attr( $package ); ?>">
                <datalist id="art-package-suggestions">
                    <?php foreach ( av_articulo_component_package_suggestions() as $pkg ) : ?>
                    <option value="<?php echo esc_attr( $pkg ); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-board-reference">Referencia en placa</label>
                <input id="art-board-reference" class="c-list-cpt-sats__search" type="text" name="board-reference" placeholder="Ej. PU4901" value="<?php echo esc_attr( $board_reference ); ?>">
                <small>La referencia de este componente dentro de una placa concreta. No es el MPN.</small>
            </div>
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="art-component-specs">Especificaciones técnicas</label>
                <textarea id="art-component-specs" class="c-list-cpt-sats__search" name="component-specs" rows="4" placeholder="VIN: 2.7-28 V&#10;IOUT: 8 A&#10;Tipo: Buck síncrono"><?php echo esc_textarea( $component_specs ); ?></textarea>
            </div>
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="art-component-equivalents">Equivalentes / sustitutos</label>
                <textarea id="art-component-equivalents" class="c-list-cpt-sats__search" name="component-equivalents" rows="3" placeholder="Un equivalente por línea"><?php echo esc_textarea( $component_equivalents ); ?></textarea>
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-component-application">Aplicación / función</label>
                <input id="art-component-application" class="c-list-cpt-sats__search" type="text" name="component-application" placeholder="Ej. Regulador de alimentación" value="<?php echo esc_attr( $component_application ); ?>">
            </div>
        </div>
    </div>

    <!-- ── Inventario ─────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Inventario</div>
        <div class="c-sat-form__wrapper-box">
            <?php if ( ! $editando ) : ?>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-stock-inicial">Stock inicial</label>
                <input id="art-stock-inicial" class="c-list-cpt-sats__search" type="number" step="any" name="stock" value="0">
            </div>
            <?php else : ?>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label">Stock actual</label>
                <input class="c-list-cpt-sats__search" type="text" value="<?php echo esc_attr( av_articulo_format_qty( $stock ) ); ?>" disabled title="Se cambia con «Ajustar stock», no editando este campo directamente.">
            </div>
            <?php endif; ?>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-stock-min">Stock mínimo</label>
                <input id="art-stock-min" class="c-list-cpt-sats__search" type="number" step="any" min="0" name="stock-min" value="<?php echo esc_attr( $stock_min ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-stock-max">Stock máximo</label>
                <input id="art-stock-max" class="c-list-cpt-sats__search" type="number" step="any" min="0" name="stock-max" value="<?php echo esc_attr( $stock_max ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-ubicacion">Ubicación</label>
                <input id="art-ubicacion" class="c-list-cpt-sats__search" type="text" name="location" placeholder="Ej. Estantería B - Balda 2" value="<?php echo esc_attr( $location ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-unidad">Unidad</label>
                <select id="art-unidad" class="c-list-cpt-sats__search" name="unit">
                    <?php foreach ( av_articulo_unit_choices() as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $unit, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="c-list-cpt-sats__field c-devices__field--checkbox">
                <label class="c-devices__checkbox-label">
                    <input type="checkbox" name="allow-negative-stock" value="1" <?php checked( $allow_negative ); ?>>
                    <span>Permitir stock negativo</span>
                </label>
            </div>
        </div>

        <?php if ( $editando ) : ?>
        <div class="c-articulo-form__stock-actions">
            <button type="button" class="o-button o-button--style-2 js-articulo-stock-open"
                data-id="<?php echo esc_attr( $a_id ); ?>"
                data-nombre="<?php echo esc_attr( $nombre ); ?>"
                data-stock="<?php echo esc_attr( $stock ); ?>">
                Ajustar stock
            </button>
        </div>

        <div class="c-articulo-form__history">
            <h2 class="c-articulo-form__history-title">Historial de stock</h2>
            <?php $movimientos = av_articulo_get_movimientos( $a_id ); ?>
            <?php if ( empty( $movimientos ) ) : ?>
            <p class="c-devices__empty">Todavía no hay movimientos de stock.</p>
            <?php else : ?>
            <div class="c-list-cpt-sats__wrapper-list">
                <table>
                    <thead>
                        <tr><th>Fecha</th><th>Tipo</th><th>Cantidad</th><th>Antes</th><th>Después</th><th>Motivo</th><th>Usuario</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $movimientos as $mov ) : ?>
                        <tr class="c-list-cpt-sats__row">
                            <td><?php echo esc_html( $mov['date'] ); ?></td>
                            <td><?php echo esc_html( av_articulo_movement_types()[ $mov['movement_type'] ] ?? $mov['movement_type'] ); ?></td>
                            <td><?php echo esc_html( sprintf( '%+g', $mov['quantity'] ) ); ?></td>
                            <td><?php echo esc_html( av_articulo_format_qty( $mov['stock_before'] ) ); ?></td>
                            <td><?php echo esc_html( av_articulo_format_qty( $mov['stock_after'] ) ); ?></td>
                            <td><?php echo esc_html( $mov['reason'] ?: '—' ); ?></td>
                            <td><?php echo esc_html( $mov['user_name'] ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Precios ────────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Precios</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-precio-compra">Precio de compra</label>
                <input id="art-precio-compra" class="c-list-cpt-sats__search" type="number" step="0.01" min="0" name="purchase-price" value="<?php echo esc_attr( $purchase_price ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-precio-venta">Precio de venta</label>
                <input id="art-precio-venta" class="c-list-cpt-sats__search" type="number" step="0.01" min="0" name="sale-price" value="<?php echo esc_attr( $sale_price ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-iva">IVA</label>
                <select id="art-iva" class="c-list-cpt-sats__search" name="vat">
                    <?php foreach ( av_articulo_vat_choices() as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $vat, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ( $editando ) : ?>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label">Precio con IVA</label>
                <input class="c-list-cpt-sats__search" type="text" value="<?php echo esc_attr( number_format( av_articulo_sale_price_with_vat( $a_id ), 2, ',', '.' ) ); ?> €" disabled>
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label">Coste medio</label>
                <input class="c-list-cpt-sats__search" type="text" value="<?php echo esc_attr( number_format( $cost, 2, ',', '.' ) ); ?> €" disabled title="Se calcula a partir del precio de compra: siempre está al día.">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label">Margen</label>
                <input class="c-list-cpt-sats__search" type="text" value="<?php echo esc_attr( number_format( $margin['amount'], 2, ',', '.' ) ); ?> € (<?php echo esc_attr( number_format( $margin['percent'], 1, ',', '.' ) ); ?>%)" disabled>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Proveedor ──────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Proveedor</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-proveedor">Proveedor</label>
                <input id="art-proveedor" class="c-list-cpt-sats__search" type="text" name="supplier" value="<?php echo esc_attr( $supplier ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-proveedor-ref">Referencia del proveedor</label>
                <input id="art-proveedor-ref" class="c-list-cpt-sats__search" type="text" name="supplier-reference" value="<?php echo esc_attr( $supplier_ref ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-proveedor-precio">Precio del proveedor</label>
                <input id="art-proveedor-precio" class="c-list-cpt-sats__search" type="number" step="0.01" min="0" name="supplier-price" value="<?php echo esc_attr( $supplier_price ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-proveedor-url">URL del producto</label>
                <input id="art-proveedor-url" class="c-list-cpt-sats__search" type="url" name="supplier-url" placeholder="https://" value="<?php echo esc_attr( $supplier_url ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-proveedor-dias">Días de entrega</label>
                <input id="art-proveedor-dias" class="c-list-cpt-sats__search" type="number" step="1" min="0" name="delivery-days" value="<?php echo esc_attr( $delivery_days ); ?>">
            </div>
        </div>
    </div>

    <!-- ── SAT ────────────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">SAT</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="art-tipo">Tipo de artículo</label>
                <select id="art-tipo" class="c-list-cpt-sats__search" name="article-type">
                    <?php foreach ( av_articulo_type_choices() as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $article_type, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="c-list-cpt-sats__field c-devices__field--checkbox">
                <label class="c-devices__checkbox-label">
                    <input type="checkbox" name="usable-in-sat" value="1" <?php checked( $usable_in_sat ); ?>>
                    <span>Se puede utilizar en SAT</span>
                </label>
            </div>
            <div class="c-list-cpt-sats__field c-devices__field--checkbox">
                <label class="c-devices__checkbox-label">
                    <input type="checkbox" name="sellable" value="1" <?php checked( $sellable ); ?>>
                    <span>Se puede vender</span>
                </label>
            </div>
            <div class="c-list-cpt-sats__field c-devices__field--checkbox">
                <label class="c-devices__checkbox-label">
                    <input type="checkbox" name="deduct-stock" value="1" <?php checked( $deduct_stock ); ?>>
                    <span>Descontar stock al utilizar en SAT</span>
                </label>
            </div>
        </div>
    </div>

    <!-- ── Notas ──────────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Notas</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="art-notas">Notas internas</label>
                <textarea id="art-notas" class="c-list-cpt-sats__search" name="internal-notes" rows="4"><?php echo esc_textarea( $internal_notes ); ?></textarea>
                <small>No aparecen al cliente.</small>
            </div>
        </div>
    </div>

    <!-- ── Acciones fijas (mismo componente/CSS que el SAT y que Pedidos, ver
         components/c-sat-form.php / components/c-pedido-form.php) ────────── -->
    <div class="c-sat-form__wrapper-ctas">
        <div class="c-sat-form__ctas-inner">
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
                <a class="c-sat-form__cta c-sat-form__cta--secondary"
                   href="<?php echo esc_url( av_articulos_page_url() ); ?>"
                   onclick="return confirm('¿Salir sin guardar los cambios?');">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    <span>Cancelar</span>
                </a>
            </div>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">
                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span><?php echo $editando ? 'Guardar cambios' : 'Crear artículo'; ?></span>
                </button>
            </div>
        </div>
    </div>
</form>
