<?php
// Formulario "Nueva compra" / "Editar compra" (ver inc/compras/compras.php).
// Espera $editando (WP_Post de cpt-compras o null) en el scope.
//
// El vendedor reutiliza el marcado/clases del cliente de Pedidos
// (components/c-pedido-form.php): el JS del selector (av_pedido_client_picker)
// se engancha por clase, así que funciona aquí tal cual. Lo mismo con el botón
// "Guardar cambios solo si hay cambios" (av_pedido_dirty_save_button).

$c_id = $editando ? $editando->ID : 0;

$get_meta = function( $key, $default = '' ) use ( $c_id ) {
    if ( ! $c_id ) return $default;
    $v = get_post_meta( $c_id, $key, true );
    return $v !== '' ? $v : $default;
};

$numero     = $get_meta( '_compra_numero' );
$client_id  = intval( $get_meta( '_compra_client_id' ) );
$dni        = $get_meta( '_compra_vendedor_dni' );
$direccion  = $get_meta( '_compra_vendedor_direccion' );
$forma_pago = $get_meta( '_compra_forma_pago', 'efectivo' );
$notas      = $get_meta( '_compra_notas' );
$items      = $c_id ? av_compra_get_items( $c_id ) : [];
if ( empty( $items ) ) {
    $items = [ [ 'tipo' => '', 'modelo' => '', 'imei' => '', 'estado' => '', 'accesorios' => '', 'precio' => '' ] ];
}
$total = 0;
foreach ( $items as $it ) { $total += floatval( $it['precio'] ?? 0 ); }

$client_name  = '';
$client_phone = '';
if ( $client_id && get_post_type( $client_id ) === 'cpt-clients' ) {
    $client_name  = get_field( 'cpt-client__name', $client_id );
    $client_phone = trim( get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id ) );
    if ( $dni === '' ) $dni = (string) get_field( 'cpt-client__dni', $client_id );
} else {
    $client_id = 0;
}

// Vendedor escrito a mano (sin ficha en la BDD).
$manual_name   = $client_id ? '' : trim( (string) $get_meta( '_compra_vendedor_nombre' ) );
$manual_phone  = $client_id ? '' : trim( (string) $get_meta( '_compra_vendedor_telefono' ) );
$manual_ext    = '34';
$manual_number = $manual_phone;
if ( preg_match( '/^(\d{1,4})\s+(.+)$/', $manual_phone, $mm ) ) {
    $manual_ext    = $mm[1];
    $manual_number = $mm[2];
}
$manual_open = $manual_name !== '';

$errores_compra = [
    'vendedor-requerido' => 'Indica quién vende: elige un cliente o escribe su nombre.',
    'dni-requerido'      => 'El DNI/NIE del vendedor es obligatorio.',
    'items-requeridos'   => 'Añade al menos un artículo con su marca/modelo.',
    'importe-invalido'   => 'El importe pagado debe ser mayor que 0.',
    'unknown'            => 'No se ha podido completar la acción.',
];

$client_modal_redirect_to = av_compras_page_url();

$print_url = $c_id ? add_query_arg( [
    'compra_print' => '1',
    'compra_id'    => $c_id,
    'nonce'        => wp_create_nonce( 'compra_print_' . $c_id ),
], home_url( '/' ) ) : '';

// Una fila de artículo; con $it vacío sirve de plantilla para "Añadir artículo".
$render_item_row = function( array $it ) {
    $v = fn( $k ) => esc_attr( $it[ $k ] ?? '' );
    ?>
    <div class="c-compra-items__row js-compra-item">
        <div class="c-list-cpt-sats__field">
            <label class="c-list-cpt-sats__field-label">Tipo</label>
            <input class="c-list-cpt-sats__search" type="text" name="item_tipo[]" value="<?php echo $v( 'tipo' ); ?>" placeholder="Móvil, portátil, consola...">
        </div>
        <div class="c-list-cpt-sats__field">
            <label class="c-list-cpt-sats__field-label">Marca / modelo</label>
            <input class="c-list-cpt-sats__search" type="text" name="item_modelo[]" value="<?php echo $v( 'modelo' ); ?>" placeholder="Apple iPhone 12 64GB">
        </div>
        <div class="c-list-cpt-sats__field">
            <label class="c-list-cpt-sats__field-label">IMEI / Nº serie</label>
            <input class="c-list-cpt-sats__search" type="text" name="item_imei[]" value="<?php echo $v( 'imei' ); ?>">
        </div>
        <div class="c-list-cpt-sats__field">
            <label class="c-list-cpt-sats__field-label">Estado</label>
            <input class="c-list-cpt-sats__search" type="text" name="item_estado[]" value="<?php echo $v( 'estado' ); ?>" placeholder="Funciona, pantalla rayada...">
        </div>
        <div class="c-list-cpt-sats__field">
            <label class="c-list-cpt-sats__field-label">Accesorios</label>
            <input class="c-list-cpt-sats__search" type="text" name="item_accesorios[]" value="<?php echo $v( 'accesorios' ); ?>" placeholder="Cargador, funda...">
        </div>
        <div class="c-list-cpt-sats__field">
            <label class="c-list-cpt-sats__field-label">Precio pagado (€)</label>
            <input class="c-list-cpt-sats__search js-compra-item-precio" type="number" step="0.01" min="0" name="item_precio[]" value="<?php echo $v( 'precio' ); ?>">
        </div>
        <button type="button" class="c-compra-items__remove js-compra-item-remove" aria-label="Quitar artículo">×</button>
    </div>
    <?php
};
?>

<?php if ( isset( $_GET['creada'] ) ) : ?>
<div class="c-devices__notice"><strong>Compra <?php echo esc_html( $numero ); ?> guardada. Ya puedes imprimir la hoja.</strong></div>
<?php elseif ( isset( $_GET['actualizada'] ) ) : ?>
<div class="c-devices__notice"><strong>Compra actualizada correctamente.</strong></div>
<?php endif; ?>

<?php if ( isset( $_GET['error'] ) ) :
    $err_slug = sanitize_text_field( $_GET['error'] );
    $err_msg  = $errores_compra[ $err_slug ] ?? $errores_compra['unknown'];
?>
<div class="c-devices__error"><?php echo esc_html( $err_msg ); ?></div>
<?php endif; ?>

<form class="c-pedido-form js-pedido-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'av_compra_nonce', 'nonce' ); ?>
    <input type="hidden" name="action" value="av_guardar_compra">
    <input type="hidden" name="id" value="<?php echo esc_attr( $c_id ); ?>">
    <input type="hidden" name="client-id" class="js-pedido-client-id" value="<?php echo esc_attr( $client_id ); ?>">

    <div class="c-articulo-form__head">
        <h1 class="c-articulo-form__title">
            <?php echo $editando ? 'Compra ' . esc_html( $numero ) : 'Nueva compra'; ?>
        </h1>
    </div>

    <!-- ── Vendedor ───────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Vendedor</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full c-pedido-client">

                <div class="c-pedido-client__selected js-pedido-client-selected<?php echo $client_id ? '' : ' is-hidden'; ?>">
                    <div class="c-sat-client-picker__result c-pedido-client__card">
                        <span class="c-sat-client-picker__result-name js-pedido-client-name"><?php echo esc_html( $client_name ); ?></span>
                        <span class="c-sat-client-picker__result-meta js-pedido-client-meta"><?php echo esc_html( $client_phone ); ?></span>
                    </div>
                    <button type="button" class="c-devices__cancel-btn js-pedido-client-change">Cambiar vendedor</button>
                </div>

                <div class="c-pedido-client__search-wrap js-pedido-client-search-wrap<?php echo $client_id ? ' is-hidden' : ''; ?>">
                    <input type="text" class="c-list-cpt-sats__search js-pedido-client-search" placeholder="Buscar cliente por nombre, teléfono o email..." autocomplete="off">
                    <div class="c-pedido-client__results js-pedido-client-results is-hidden"></div>
                    <div class="c-pedido-client__actions">
                        <button type="button" class="o-button o-button--style-2 js-client-modal-open">+ Crear cliente</button>
                        <button type="button" class="o-button o-button--style-2 js-pedido-client-manual-toggle"><?php echo $manual_open ? 'Buscar cliente existente' : 'Escribir vendedor a mano'; ?></button>
                    </div>

                    <div class="c-pedido-client__manual js-pedido-client-manual<?php echo $manual_open ? '' : ' is-hidden'; ?>">
                        <div class="c-pedido-client__manual-grid">
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="cmp-manual-name">Nombre y apellidos</label>
                                <input id="cmp-manual-name" class="c-list-cpt-sats__search js-pedido-manual-name" type="text" name="manual-client-name" value="<?php echo esc_attr( $manual_name ); ?>">
                            </div>
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="cmp-manual-phone">Teléfono</label>
                                <div class="c-pedido-client__manual-phone">
                                    <input class="c-list-cpt-sats__search c-pedido-client__manual-ext" type="number" name="manual-client-ext" value="<?php echo esc_attr( $manual_ext ); ?>">
                                    <input id="cmp-manual-phone" class="c-list-cpt-sats__search c-pedido-client__manual-number" type="text" name="manual-client-phone" value="<?php echo esc_attr( $manual_number ); ?>">
                                </div>
                            </div>
                        </div>
                        <label class="c-devices__checkbox-label">
                            <input type="checkbox" name="manual-client-save" value="1">
                            <span>Guardar también este cliente en la base de datos</span>
                        </label>
                    </div>
                </div>

            </div>

            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="cmp-dni">DNI / NIE <strong>(obligatorio)</strong></label>
                <input id="cmp-dni" class="c-list-cpt-sats__search" type="text" name="vendedor-dni" value="<?php echo esc_attr( $dni ); ?>" autocomplete="off">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="cmp-direccion">Dirección (opcional)</label>
                <input id="cmp-direccion" class="c-list-cpt-sats__search" type="text" name="vendedor-direccion" value="<?php echo esc_attr( $direccion ); ?>">
            </div>
        </div>
    </div>

    <!-- ── Artículos comprados ────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Artículos comprados</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full c-compra-items js-compra-items">
                <div class="js-compra-items-list">
                    <?php foreach ( $items as $it ) { $render_item_row( $it ); } ?>
                </div>
                <template class="js-compra-item-template"><?php $render_item_row( [] ); ?></template>

                <button type="button" class="o-button o-button--style-2 js-compra-item-add">+ Añadir otro artículo</button>

                <div class="c-compra-items__total">
                    Total a pagar al vendedor: <strong class="js-compra-total"><?php echo esc_html( number_format( $total, 2, ',', '.' ) ); ?> €</strong>
                </div>
            </div>
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input-row">
                <div class="c-sat-form__wrapper-input">
                    <label for="cmp-forma-pago">Forma de pago al vendedor</label>
                    <select class="c-sat-form__select" name="forma-pago" id="cmp-forma-pago">
                        <?php foreach ( av_compra_payment_choices() as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $forma_pago, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Observaciones (salen impresas) ─────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Observaciones</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="cmp-notas">Observaciones</label>
                <textarea id="cmp-notas" class="c-list-cpt-sats__search" name="notas" rows="3" placeholder="Opcional. Aparece impresa en la hoja."><?php echo esc_textarea( $notas ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- ── Acciones fijas (mismo componente/CSS que SAT y Pedidos) ─────── -->
    <div class="c-sat-form__wrapper-ctas">
        <div class="c-sat-form__ctas-inner">
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
                <a class="c-sat-form__cta c-sat-form__cta--secondary" href="<?php echo esc_url( av_compras_page_url() ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    <span>Volver a compras</span>
                </a>
            </div>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">
                <?php if ( $print_url ) : ?>
                <a class="c-sat-form__cta c-sat-form__cta--invoice" href="<?php echo esc_url( $print_url ); ?>" target="_blank" title="Imprimir la hoja para que la firme el vendedor">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>Imprimir hoja</span>
                </a>
                <?php endif; ?>

                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary<?php echo $editando ? ' js-pedido-save-btn is-hidden' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span><?php echo $editando ? 'Guardar cambios' : 'Guardar compra'; ?></span>
                </button>
            </div>
        </div>
    </div>
</form>

<?php include locate_template( 'components/c-client-modal.php' ); ?>
