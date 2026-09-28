<?php
// Formulario "Nuevo pedido" / "Editar pedido". Espera $editando (WP_Post o
// null) en el scope (ver templates/template-pedidos.php). Misma arquitectura
// que components/c-articulo-form.php: página completa, acordeones
// plegables (.c-sat-form__accordion), sin usar la pantalla nativa de ACF.
//
// Fase 2: todavía no hay listado de pedidos (eso es una fase posterior), así
// que no hay enlace de "volver".

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
$client_notes       = $get_meta( 'cpt-pedido__client-notes' );
$internal_notes     = $get_meta( 'cpt-pedido__internal-notes' );
$lines_json         = $p_id ? get_post_meta( $p_id, 'cpt-pedido__lines', true ) : '';

$client_name  = '';
$client_phone = '';
if ( $client_id ) {
    $client_name  = get_field( 'cpt-client__name', $client_id );
    $client_phone = trim( get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id ) );
}

$errores_pedido = [
    'cliente-requerido'      => 'Elige un cliente (o crea uno nuevo) antes de guardar.',
    'lineas-requeridas'      => 'Añade al menos una línea al pedido.',
    'numero-generation-failed' => 'No se ha podido generar un número de pedido único. Inténtalo de nuevo.',
    // Mismo mensaje que usa el frontend (src/js/app.js,
    // av_pedido_form_validate_delivered) — este es el aviso si esa
    // validación se saltó (JS deshabilitado, POST directo...).
    'falta-tipo-pago'        => 'Antes de marcar el pedido como entregado debes indicar el tipo de pago.',
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
            <?php echo $editando ? 'Editar pedido ' . esc_html( $pedido_numero ) : 'Nuevo pedido'; ?>
        </h1>
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
                    <button type="button" class="o-button o-button--style-2 js-client-modal-open">+ Crear cliente</button>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Líneas del pedido ──────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Líneas del pedido</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <?php include locate_template( 'components/c-pedido-lines.php' ); ?>
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

        <!-- Datos de pago: mismo campo/estilo que el SAT (Tipo de pago +
             Anticipo, ver components/c-sat-form.php). "Pagado" no es un campo
             propio: se deriva de que Tipo de pago tenga valor. -->
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
                        <input class="c-sat-form__input" type="number" step="any" min="0" name="anticipo" id="ped-anticipo" value="<?php echo esc_attr( $anticipo ); ?>"><span>€</span>
                    </div>
                    <small class="c-sat-form__help-text">Se descuenta del total al generar la factura.</small>
                </div>
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

    <!-- ── Acciones fijas (mismo componente/CSS que el SAT y que el detalle
         de pedido, ver components/c-sat-form.php) ─────────────────────── -->
    <div class="c-sat-form__wrapper-ctas">
        <div class="c-sat-form__ctas-inner">
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
                <a class="c-sat-form__cta c-sat-form__cta--secondary"
                   href="<?php echo esc_url( $editando ? add_query_arg( 'view', $p_id, av_pedidos_page_url() ) : av_pedidos_page_url() ); ?>"
                   onclick="return confirm('¿Salir sin guardar los cambios?');">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    <span>Cancelar</span>
                </a>
            </div>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">
                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span><?php echo $editando ? 'Guardar cambios' : 'Crear pedido'; ?></span>
                </button>
            </div>
        </div>
    </div>
</form>

<?php include( locate_template( 'components/c-client-modal.php' ) ); ?>
