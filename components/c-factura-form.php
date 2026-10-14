<?php
// Formulario "Nueva factura" / "Editar factura" (factura manual, sin SAT ni
// pedido de origen — ver inc/facturas/manual.php). Espera $editando (WP_Post
// de cpt-facturas o null) en el scope (ver templates/template-facturas.php).
//
// Reutiliza el marcado y las clases del formulario de Pedidos
// (components/c-pedido-form.php) a propósito: el JS de selección de cliente
// (av_pedido_client_picker), del anticipo (av_sat_form__anticipo_toggle +
// validación en av_pedido_form_validate_delivered) y del botón "Guardar solo
// si hay cambios" (av_pedido_dirty_save_button) se engancha por clase, así
// que funciona aquí tal cual sin clonar nada. La tabla de líneas es la misma
// que la del SAT (components/c-sat-lines.php).

$f_id = $editando ? $editando->ID : 0;

$get_meta = function( $key, $default = '' ) use ( $f_id ) {
    if ( ! $f_id ) return $default;
    $v = get_post_meta( $f_id, $key, true );
    return $v !== '' ? $v : $default;
};

$numero          = $get_meta( '_factura_numero' );
$client_id       = intval( $get_meta( '_factura_client_id' ) );
$forma_pago      = $get_meta( '_factura_forma_pago' );
$anticipo        = floatval( $get_meta( '_factura_anticipo' ) );
$anticipo        = $anticipo > 0 ? $anticipo : '';
$anticipo_payment = $get_meta( '_factura_anticipo_forma_pago' );
$notas           = $get_meta( '_factura_notas' );
$lines_json      = $get_meta( '_factura_lines' );
$is_paid         = $forma_pago !== '';
$total           = floatval( $get_meta( '_factura_total' ) );

$client_name  = '';
$client_phone = '';
if ( $client_id && get_post_type( $client_id ) === 'cpt-clients' ) {
    $client_name  = get_field( 'cpt-client__name', $client_id );
    $client_phone = trim( get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id ) );
} else {
    $client_id = 0;
}

// Cliente escrito a mano (sin ficha en la BDD): nombre/teléfono viven en la
// propia factura.
$manual_name   = $client_id ? '' : trim( (string) $get_meta( '_factura_cliente' ) );
$manual_phone  = $client_id ? '' : trim( (string) $get_meta( '_factura_telefono' ) );
$manual_ext    = '34';
$manual_number = $manual_phone;
if ( preg_match( '/^(\d{1,4})\s+(.+)$/', $manual_phone, $mm ) ) {
    $manual_ext    = $mm[1];
    $manual_number = $mm[2];
}
$manual_open = $manual_name !== '';

$errores_factura = [
    'cliente-requerido'   => 'Indica a quién se factura: elige un cliente o escribe su nombre.',
    'lineas-requeridas'   => 'Añade al menos un artículo o servicio a la factura.',
    'falta-pago-anticipo' => 'Has indicado un anticipo: selecciona su forma de pago.',
    'unknown'             => 'No se ha podido completar la acción.',
];

// El modal de "crear cliente" redirige aquí mismo (URL limpia) al guardar; el
// JS de la página lo intercepta y lee el id del nuevo cliente sin recargar
// (av_pedido_client_picker en src/js/app.js).
$client_modal_redirect_to = av_facturas_page_url();

$pdf_url = $f_id ? add_query_arg( [
    'factura_manual_pdf' => '1',
    'factura_id'         => $f_id,
    'nonce'              => wp_create_nonce( 'factura_manual_pdf_' . $f_id ),
], home_url( '/' ) ) : '';
?>

<?php if ( isset( $_GET['creada'] ) ) : ?>
<div class="c-devices__notice"><strong>Factura <?php echo esc_html( $numero ); ?> creada correctamente.</strong></div>
<?php elseif ( isset( $_GET['actualizada'] ) ) : ?>
<div class="c-devices__notice"><strong>Factura actualizada correctamente.</strong></div>
<?php endif; ?>

<?php if ( isset( $_GET['error'] ) ) :
    $err_slug = sanitize_text_field( $_GET['error'] );
    $err_msg  = $errores_factura[ $err_slug ] ?? $errores_factura['unknown'];
?>
<div class="c-devices__error"><?php echo esc_html( $err_msg ); ?></div>
<?php endif; ?>

<form class="c-pedido-form js-pedido-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'av_factura_manual_nonce', 'nonce' ); ?>
    <input type="hidden" name="action" value="av_guardar_factura_manual">
    <input type="hidden" name="id" value="<?php echo esc_attr( $f_id ); ?>">
    <input type="hidden" name="client-id" class="js-pedido-client-id" value="<?php echo esc_attr( $client_id ); ?>">

    <div class="c-articulo-form__head">
        <h1 class="c-articulo-form__title">
            <?php echo $editando ? 'Factura ' . esc_html( $numero ) : 'Nueva factura'; ?>
        </h1>
        <?php if ( $editando ) : ?>
        <div class="c-pedido-form__badges">
            <span class="c-pedido-detail__paid-badge <?php echo $is_paid ? 'c-pedido-detail__paid-badge--ok' : 'c-pedido-detail__paid-badge--pte'; ?>"><?php echo $is_paid ? 'Pagada' : 'Pendiente de pago'; ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Cliente ────────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Cliente</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full c-pedido-client">

                <div class="c-pedido-client__selected js-pedido-client-selected<?php echo $client_id ? '' : ' is-hidden'; ?>">
                    <div class="c-sat-client-picker__result c-pedido-client__card">
                        <span class="c-sat-client-picker__result-name js-pedido-client-name"><?php echo esc_html( $client_name ); ?></span>
                        <span class="c-sat-client-picker__result-meta js-pedido-client-meta"><?php echo esc_html( $client_phone ); ?></span>
                    </div>
                    <button type="button" class="c-devices__cancel-btn js-pedido-client-change">Cambiar cliente</button>
                </div>

                <div class="c-pedido-client__search-wrap js-pedido-client-search-wrap<?php echo $client_id ? ' is-hidden' : ''; ?>">
                    <input type="text" class="c-list-cpt-sats__search js-pedido-client-search" placeholder="Buscar cliente por nombre, teléfono o email..." autocomplete="off">
                    <div class="c-pedido-client__results js-pedido-client-results is-hidden"></div>
                    <div class="c-pedido-client__actions">
                        <button type="button" class="o-button o-button--style-2 js-client-modal-open">+ Crear cliente</button>
                        <button type="button" class="o-button o-button--style-2 js-pedido-client-manual-toggle"><?php echo $manual_open ? 'Buscar cliente existente' : 'Escribir cliente a mano'; ?></button>
                    </div>

                    <!-- Cliente sin ficha en la BDD: solo nombre y teléfono, se
                         guardan en la propia factura. El check permite darlo
                         de alta también como cliente. Clases propias
                         (c-pedido-client__manual-*) para no chocar con
                         av_check_user() — ver el comentario equivalente en
                         c-pedido-form.php. -->
                    <div class="c-pedido-client__manual js-pedido-client-manual<?php echo $manual_open ? '' : ' is-hidden'; ?>">
                        <div class="c-pedido-client__manual-grid">
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="fac-manual-name">Nombre del cliente</label>
                                <input id="fac-manual-name" class="c-list-cpt-sats__search js-pedido-manual-name" type="text" name="manual-client-name" value="<?php echo esc_attr( $manual_name ); ?>">
                            </div>
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="fac-manual-phone">Teléfono</label>
                                <div class="c-pedido-client__manual-phone">
                                    <input class="c-list-cpt-sats__search c-pedido-client__manual-ext" type="number" name="manual-client-ext" value="<?php echo esc_attr( $manual_ext ); ?>">
                                    <input id="fac-manual-phone" class="c-list-cpt-sats__search c-pedido-client__manual-number" type="text" name="manual-client-phone" value="<?php echo esc_attr( $manual_number ); ?>">
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
        </div>
    </div>

    <!-- ── Artículos y servicios + pago ───────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Artículos, servicios y pago</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <?php include locate_template( 'components/c-sat-lines.php' ); ?>
            </div>
        </div>

        <!-- Tipo de pago + anticipo + forma de pago del anticipo: mismos
             campos, clases y reglas que en Pedidos (el JS del anticipo se
             engancha por clase). Con tipo de pago la factura queda "Pagada". -->
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input-row">
                <div class="c-sat-form__wrapper-input">
                    <label for="fac-price-description">Tipo de pago</label>
                    <select class="c-sat-form__select" name="price-description" id="fac-price-description">
                        <option value="">Seleccione...</option>
                        <?php foreach ( av_pedido_payment_choices() as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $forma_pago, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="c-sat-form__help-text">Si lo indicas, la factura queda como pagada.</small>
                </div>
                <div class="c-sat-form__wrapper-input">
                    <label for="fac-anticipo">Anticipo (paga y señal)</label>
                    <div class="c-sat-form__wrapper-price-input">
                        <input class="c-sat-form__input js-sat-form__anticipo" type="number" step="any" min="0" name="anticipo" id="fac-anticipo" value="<?php echo esc_attr( $anticipo ); ?>"><span>€</span>
                    </div>
                    <small class="c-sat-form__help-text">Se descuenta del total a pagar en la factura.</small>
                </div>
                <div class="c-sat-form__wrapper-input js-sat-form__anticipo-payment-wrapper<?php echo empty( $anticipo ) ? ' is-hidden' : ''; ?>">
                    <label for="fac-anticipo-payment">Forma de pago del anticipo</label>
                    <select class="c-sat-form__select js-sat-form__anticipo-payment" name="anticipo-payment" id="fac-anticipo-payment" <?php echo ! empty( $anticipo ) ? 'required' : ''; ?>>
                        <option value="">Seleccione...</option>
                        <?php foreach ( av_pedido_payment_choices() as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $anticipo_payment, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Notas (salen impresas en la factura) ───────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Notas</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="fac-notas">Notas de la factura</label>
                <textarea id="fac-notas" class="c-list-cpt-sats__search" name="notas" rows="3" placeholder="Opcional. Aparece impresa en la factura."><?php echo esc_textarea( $notas ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- ── Acciones fijas (mismo componente/CSS que SAT y Pedidos) ─────── -->
    <div class="c-sat-form__wrapper-ctas">
        <div class="c-sat-form__ctas-inner">
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
                <a class="c-sat-form__cta c-sat-form__cta--secondary" href="<?php echo esc_url( av_facturas_page_url() ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    <span>Volver a facturas</span>
                </a>
            </div>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">
                <?php if ( $pdf_url ) : ?>
                <a class="c-sat-form__cta c-sat-form__cta--invoice" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" title="Imprimir / guardar PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>Ver / imprimir factura</span>
                </a>
                <?php endif; ?>

                <!-- En edición, "Guardar cambios" solo se enseña cuando algo ha
                     cambiado (av_pedido_dirty_save_button), igual que en
                     Pedidos; al crear se enseña siempre. -->
                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary<?php echo $editando ? ' js-pedido-save-btn is-hidden' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span><?php echo $editando ? 'Guardar cambios' : 'Crear factura'; ?></span>
                </button>
            </div>
        </div>
    </div>
</form>

<?php include locate_template( 'components/c-client-modal.php' ); ?>
