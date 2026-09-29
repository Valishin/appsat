<?php
// Formulario "Nuevo pedido" / "Editar pedido". Espera $editando (WP_Post o
// null) en el scope (ver templates/template-pedidos.php). Misma arquitectura
// que components/c-articulo-form.php: página completa, acordeones
// plegables (.c-sat-form__accordion), sin usar la pantalla nativa de ACF.
//
// Esta pantalla es a la vez el "detalle" del pedido (ya no existe una vista
// de solo lectura aparte): editando un pedido guardado se puede modificar
// todo, marcar líneas como recibidas, marcarlo entregado/cancelado, ver el
// tipo de pago, enviar el aviso por WhatsApp y generar/ver la factura o el
// recibo, todo desde aquí.

$p_id = $editando ? $editando->ID : 0;

$get_meta = function( $key, $default = '' ) use ( $p_id ) {
    if ( ! $p_id ) return $default;
    $v = get_post_meta( $p_id, $key, true );
    return $v !== '' ? $v : $default;
};

$pedido_numero      = $get_meta( 'cpt-pedido__pedido-id' );
$client_id          = intval( $get_meta( 'cpt-pedido__client-id' ) );
$status             = $get_meta( 'cpt-pedido__status', 'pending' );
$entry_date         = $get_meta( 'cpt-pedido__entry-date' );
$delivered_date     = $get_meta( 'cpt-pedido__delivered-date' );
$location           = $get_meta( 'cpt-pedido__location' );
$price_description  = $get_meta( 'cpt-pedido__price-description' );
$anticipo           = $get_meta( 'cpt-pedido__anticipo' );
$anticipo_payment   = $get_meta( 'cpt-pedido__anticipo-payment' );
$client_notes       = $get_meta( 'cpt-pedido__client-notes' );
$internal_notes     = $get_meta( 'cpt-pedido__internal-notes' );
$lines_json         = $p_id ? get_post_meta( $p_id, 'cpt-pedido__lines', true ) : '';

// Acciones de un pedido ya guardado (antes vivían en la vista de detalle).
$sale_id            = $p_id ? intval( get_post_meta( $p_id, 'cpt-pedido__sale-id', true ) ) : 0;
$is_paid            = $p_id ? av_pedido_is_paid( $p_id ) : false;
$wa_href            = '';
if ( $p_id ) {
    $wa_phone = str_replace( ' ', '', av_pedido_client_info( $p_id )['phone'] );
    // Solo tiene sentido mientras el pedido está "received" (disponible para
    // recoger): antes no ha llegado, y tras "delivered" ya se recogió.
    if ( $wa_phone && $status === 'received' ) {
        $wa_href = 'https://wa.me/' . $wa_phone . '?text=' . urlencode( av_pedido_build_whatsapp_message( $p_id ) );
    }
}
// Factura: desde received en adelante (incluido delivered), nunca en
// cancelled. En pending/ordered no se genera factura de verdad (quemaría un
// número real), pero sí se puede imprimir un recibo del encargo.
$puede_generar_factura = $p_id && ! in_array( $status, [ 'pending', 'ordered', 'cancelled' ], true );
$puede_imprimir_recibo = $p_id && ! $sale_id && in_array( $status, [ 'pending', 'ordered' ], true );

$client_name  = '';
$client_phone = '';
if ( $client_id ) {
    $client_name  = get_field( 'cpt-client__name', $client_id );
    $client_phone = trim( get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id ) );
}

// Cliente escrito a mano (sin ficha en la BDD) — ver av_pedido_validate_post().
$manual_name    = $get_meta( 'cpt-pedido__client-name-manual' );
$manual_phone   = trim( (string) $get_meta( 'cpt-pedido__client-phone-manual' ) );
$manual_ext     = '34';
$manual_number  = $manual_phone;
if ( preg_match( '/^(\d{1,4})\s+(.+)$/', $manual_phone, $mm ) ) {
    $manual_ext    = $mm[1];
    $manual_number = $mm[2];
}
$manual_open    = $manual_name !== '' && ! $client_id;

$errores_pedido = [
    'cliente-requerido'      => 'Elige un cliente (o crea uno nuevo) antes de guardar.',
    'lineas-requeridas'      => 'Añade al menos una línea al pedido.',
    'numero-generation-failed' => 'No se ha podido generar un número de pedido único. Inténtalo de nuevo.',
    // Mismo mensaje que usa el frontend (src/js/app.js,
    // av_pedido_form_validate_delivered) — este es el aviso si esa
    // validación se saltó (JS deshabilitado, POST directo...).
    'falta-tipo-pago'        => 'Antes de marcar el pedido como entregado debes indicar el tipo de pago.',
    'falta-pago-anticipo'    => 'Has indicado un anticipo: selecciona su forma de pago.',
    'unknown'                => 'No se ha podido completar la acción.',
];

// El modal de "crear cliente" redirige aquí mismo (URL limpia, sin ?id=) al
// guardar: el JS de la página intercepta ese envío y lee el id del cliente
// nuevo de la URL final sin llegar a recargar (ver av_pedido_client_picker()
// en src/js/app.js). Mismo componente que usa "Crear SAT", sin tocarlo.
$client_modal_redirect_to = av_pedidos_page_url();
?>

<?php if ( isset( $_GET['creado'] ) ) : ?>
<div class="c-devices__notice"><strong>Pedido <?php echo esc_html( $pedido_numero ); ?> creado correctamente.</strong></div>
<?php elseif ( isset( $_GET['actualizado'] ) ) : ?>
<div class="c-devices__notice"><strong>Pedido actualizado correctamente.</strong></div>
<?php endif; ?>

<?php if ( isset( $_GET['error'] ) ) :
    $err_slug = sanitize_text_field( $_GET['error'] );
    $err_msg  = $errores_pedido[ $err_slug ] ?? $errores_pedido['unknown'];
?>
<div class="c-devices__error"><?php echo esc_html( $err_msg ); ?></div>
<?php endif; ?>

<form class="c-pedido-form js-pedido-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'av_pedido_nonce', 'nonce' ); ?>
    <input type="hidden" name="action" value="av_guardar_pedido">
    <input type="hidden" name="id" value="<?php echo esc_attr( $p_id ); ?>">
    <input type="hidden" name="client-id" class="js-pedido-client-id" value="<?php echo esc_attr( $client_id ); ?>">

    <div class="c-articulo-form__head">
        <h1 class="c-articulo-form__title">
            <?php echo $editando ? 'Pedido ' . esc_html( $pedido_numero ) : 'Nuevo pedido'; ?>
        </h1>
        <?php if ( $editando ) : ?>
        <div class="c-pedido-form__badges">
            <span class="c-pedidos__status-badge c-pedidos__status-badge--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( av_pedido_status_label( $status ) ); ?></span>
            <span class="c-pedido-detail__paid-badge <?php echo $is_paid ? 'c-pedido-detail__paid-badge--ok' : 'c-pedido-detail__paid-badge--pte'; ?>"><?php echo $is_paid ? 'Pagado' : 'Pendiente de pago'; ?></span>
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
                        <button type="button" class="o-button o-button--style-2 js-pedido-client-manual-toggle">Escribir cliente a mano</button>
                    </div>

                    <!-- Cliente sin ficha en la BDD: solo nombre y teléfono, se
                         guardan en el propio pedido. El check permite darlo de
                         alta también como cliente (ver av_pedido_validate_post()). -->
                    <div class="c-pedido-client__manual js-pedido-client-manual<?php echo $manual_open ? '' : ' is-hidden'; ?>">
                        <div class="c-pedido-client__manual-grid">
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="ped-manual-name">Nombre del cliente</label>
                                <input id="ped-manual-name" class="c-list-cpt-sats__search js-pedido-manual-name" type="text" name="manual-client-name" value="<?php echo esc_attr( $manual_name ); ?>">
                            </div>
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="ped-manual-phone">Teléfono</label>
                                <!-- Clases propias (no c-client-form__input-phone*): av_check_user() y el
                                     resto del JS del formulario de cliente buscan la PRIMERA
                                     coincidencia de esas clases en toda la página, y este bloque
                                     va antes que el modal de "Crear cliente". -->
                                <div class="c-pedido-client__manual-phone">
                                    <input class="c-list-cpt-sats__search c-pedido-client__manual-ext" type="number" name="manual-client-ext" value="<?php echo esc_attr( $manual_ext ); ?>">
                                    <input id="ped-manual-phone" class="c-list-cpt-sats__search c-pedido-client__manual-number" type="text" name="manual-client-phone" value="<?php echo esc_attr( $manual_number ); ?>">
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

    <!-- ── Líneas del pedido ──────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Líneas y pago</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <?php include locate_template( 'components/c-pedido-lines.php' ); ?>
            </div>
        </div>
        <!-- Datos de pago, justo debajo de las líneas: mismo campo/estilo que el
             SAT (Tipo de pago + Anticipo + forma de pago del anticipo, ver
             components/c-sat-form.php). "Pagado" no es un campo propio: se
             deriva de que Tipo de pago tenga valor. -->
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input-row">
                <div class="c-sat-form__wrapper-input">
                    <label for="ped-price-description">Tipo de pago</label>
                    <select class="c-sat-form__select" name="price-description" id="ped-price-description">
                        <option value="">Seleccione...</option>
                        <?php foreach ( av_pedido_payment_choices() as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $price_description, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="c-sat-form__help-text">Obligatorio para marcar el pedido como entregado.</small>
                </div>
                <div class="c-sat-form__wrapper-input">
                    <label for="ped-anticipo">Anticipo (paga y señal)</label>
                    <div class="c-sat-form__wrapper-price-input">
                        <input class="c-sat-form__input js-sat-form__anticipo" type="number" step="any" min="0" name="anticipo" id="ped-anticipo" value="<?php echo esc_attr( $anticipo ); ?>"><span>€</span>
                    </div>
                    <small class="c-sat-form__help-text">Se descuenta del total al generar la factura.</small>
                </div>
                <div class="c-sat-form__wrapper-input js-sat-form__anticipo-payment-wrapper<?php echo empty( $anticipo ) ? ' is-hidden' : ''; ?>">
                    <label for="ped-anticipo-payment">Forma de pago del anticipo</label>
                    <select class="c-sat-form__select js-sat-form__anticipo-payment" name="anticipo-payment" id="ped-anticipo-payment" <?php echo ! empty( $anticipo ) ? 'required' : ''; ?>>
                        <option value="">Seleccione...</option>
                        <?php foreach ( av_pedido_payment_choices() as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $anticipo_payment, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Datos generales ────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Datos generales</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="ped-status">Estado del pedido</label>
                <select id="ped-status" class="c-list-cpt-sats__search" name="status">
                    <?php foreach ( av_pedido_status_choices() as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $status, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ( $entry_date ) : ?>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label">Fecha de creación</label>
                <input class="c-list-cpt-sats__search" type="text" value="<?php echo esc_attr( $entry_date ); ?>" disabled>
            </div>
            <?php endif; ?>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="ped-delivered-date">Fecha de entrega</label>
                <input id="ped-delivered-date" class="c-list-cpt-sats__search" type="date" name="delivered-date" value="<?php echo esc_attr( $delivered_date ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="ped-location">Ubicación</label>
                <input id="ped-location" class="c-list-cpt-sats__search" type="text" name="location" placeholder="Ej. Estantería 2" value="<?php echo esc_attr( $location ); ?>">
            </div>
        </div>
    </div>

    <!-- ── Notas ──────────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Notas</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="ped-client-notes">Notas del cliente</label>
                <textarea id="ped-client-notes" class="c-list-cpt-sats__search" name="client-notes" rows="3" placeholder="Lo que ha pedido el cliente, en sus palabras"><?php echo esc_textarea( $client_notes ); ?></textarea>
            </div>
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="ped-internal-notes">Notas internas del pedido</label>
                <textarea id="ped-internal-notes" class="c-list-cpt-sats__search" name="internal-notes" rows="3"><?php echo esc_textarea( $internal_notes ); ?></textarea>
                <small>No aparecen al cliente.</small>
            </div>
        </div>
    </div>

    <!-- ── Resumen (no plegable: siempre visible antes de guardar) ─────── -->
    <div class="c-pedido-summary js-pedido-summary">
        <div class="c-pedido-summary__row">
            <span>Cliente</span>
            <strong class="js-pedido-summary-client"><?php echo $client_name ? esc_html( $client_name ) : 'Sin elegir'; ?></strong>
        </div>
        <div class="c-pedido-summary__row">
            <span>Líneas</span>
            <strong class="js-pedido-summary-count">0</strong>
        </div>
        <div class="c-pedido-summary__row c-pedido-summary__row--total">
            <span>Total venta</span>
            <strong class="js-pedido-summary-total">0,00 €</strong>
        </div>
    </div>

    <!-- ── Acciones fijas (mismo componente/CSS que el SAT, ver
         components/c-sat-form.php) ─────────────────────────────────────── -->
    <div class="c-sat-form__wrapper-ctas">
        <div class="c-sat-form__ctas-inner">
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
                <a class="c-sat-form__cta c-sat-form__cta--secondary"
                   href="<?php echo esc_url( av_pedidos_page_url() ); ?>"
                   onclick="return confirm('¿Salir sin guardar los cambios?');">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    <span>Salir</span>
                </a>

                <?php if ( $editando && ! in_array( $status, [ 'cancelled' ], true ) ) : ?>
                <!-- Cancelar / entregar = poner el estado y guardar TODO el
                     formulario (así no se pierde nada de lo editado). El
                     tipo de pago sigue siendo obligatorio para entregar
                     (av_pedido_form_validate_delivered). -->
                <button type="button" class="c-sat-form__cta c-sat-form__cta--secondary js-pedido-set-status" data-status="cancelled" data-confirm="¿Cancelar este pedido? El cliente dejará de esperar el producto.">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    <span>Cancelar pedido</span>
                </button>
                <?php endif; ?>
            </div>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">

                <?php if ( $editando && ! in_array( $status, [ 'delivered', 'cancelled' ], true ) ) : ?>
                <button type="button" class="c-sat-form__cta c-sat-form__cta--secondary js-pedido-set-status" data-status="delivered">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Marcar entregado</span>
                </button>
                <?php endif; ?>

                <?php if ( $wa_href ) : ?>
                <a class="c-sat-form__cta c-sat-form__cta--whatsapp" href="<?php echo esc_url( $wa_href ); ?>" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Mensaje</span>
                </a>
                <?php endif; ?>

                <?php if ( $puede_generar_factura ) : ?>
                <a class="c-sat-form__cta c-sat-form__cta--invoice<?php echo $sale_id ? ' c-sat-form__cta--invoice-done' : ''; ?>"
                   href="<?php echo esc_url( add_query_arg( [ 'pedido_invoice' => '1', 'pedido_id' => $p_id, 'nonce' => wp_create_nonce( 'pedido_invoice_' . $p_id ) ], home_url( '/' ) ) ); ?>"
                   target="_blank"
                   title="<?php echo $sale_id ? 'Ver factura generada' : 'Generar factura en PDF'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><?php if ( $sale_id ) : ?><polyline points="9 15 11 17 15 13"/><?php else : ?><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><?php endif; ?></svg>
                    <span><?php echo $sale_id ? 'Ver Factura' : 'Generar Factura'; ?></span>
                </a>
                <?php elseif ( $puede_imprimir_recibo ) : ?>
                <a class="c-sat-form__cta c-sat-form__cta--secondary"
                   href="<?php echo esc_url( add_query_arg( [ 'pedido_receipt' => '1', 'pedido_id' => $p_id, 'nonce' => wp_create_nonce( 'pedido_receipt_' . $p_id ) ], home_url( '/' ) ) ); ?>"
                   target="_blank"
                   title="Imprimir recibo del encargo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>Imprimir Recibo</span>
                </a>
                <?php endif; ?>

                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span><?php echo $editando ? 'Guardar cambios' : 'Crear pedido'; ?></span>
                </button>
            </div>
        </div>
    </div>
</form>

<?php include( locate_template( 'components/c-client-modal.php' ) ); ?>
