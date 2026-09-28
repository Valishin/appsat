<?php
// Detalle de solo lectura de un pedido. Espera $pedido (WP_Post) en el scope
// (ver templates/template-pedidos.php, ?view=X). No es un formulario: los
// datos se muestran calculados al vuelo (av_pedido_line_subtotal, con IVA
// incluido igual que en Presupuestos), nunca se guarda nada desde aquí salvo
// por las acciones AJAX de abajo (cancelar / marcar línea recibida / generar
// factura).
//
// Fase 6: se eliminó el estado "ready" (recibido = listo para recoger), el
// selector libre de estado (ahora los cambios de estado son siempre
// acciones concretas: recibir líneas, cancelar, generar factura), y las
// fechas generales estimated-date/received-date/ready-date (las fechas por
// línea SIGUEN existiendo, no se han tocado). El bloque de acciones se
// mueve al pie fijo, mismo componente/CSS que el SAT
// (c-sat-form__wrapper-ctas, ver components/c-sat-form.php).

$ped_id         = $pedido->ID;
$pedido_numero  = get_post_meta( $ped_id, 'cpt-pedido__pedido-id', true );
$client_id      = intval( get_post_meta( $ped_id, 'cpt-pedido__client-id', true ) );
$status         = get_post_meta( $ped_id, 'cpt-pedido__status', true ) ?: 'pending';
$entry_date     = get_post_meta( $ped_id, 'cpt-pedido__entry-date', true );
$delivered_date = get_post_meta( $ped_id, 'cpt-pedido__delivered-date', true );
$location       = get_post_meta( $ped_id, 'cpt-pedido__location', true );
$client_notes   = get_post_meta( $ped_id, 'cpt-pedido__client-notes', true );
$internal_notes = get_post_meta( $ped_id, 'cpt-pedido__internal-notes', true );
$sale_id        = intval( get_post_meta( $ped_id, 'cpt-pedido__sale-id', true ) );

$price_description = get_post_meta( $ped_id, 'cpt-pedido__price-description', true );
$anticipo           = get_post_meta( $ped_id, 'cpt-pedido__anticipo', true );
$payment_choices    = av_pedido_payment_choices();
$is_paid            = av_pedido_is_paid( $ped_id );

$client_name  = av_pedido_resolve_client_name( $client_id );
$client_phone = $client_id ? trim( get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id ) ) : '';
$client_email = $client_id ? get_field( 'cpt-client__email', $client_id ) : '';

// Mismo mecanismo exacto que el botón WhatsApp del SAT (components/c-sat-form.php):
// $wa_phone = str_replace(' ', '', $client_phone) + wa.me/{$wa_phone}?text={urlencode}.
// Sin reimplementar ninguna normalización nueva de teléfono. Solo tiene
// sentido mientras el pedido está "received" (disponible para recoger): antes
// no ha llegado, y tras "delivered" ya se recogió.
$wa_phone = str_replace( ' ', '', $client_phone );
$wa_href  = ( $client_phone && $status === 'received' ) ? 'https://wa.me/' . $wa_phone . '?text=' . urlencode( av_pedido_build_whatsapp_message( $ped_id ) ) : '';

$lines = av_pedido_get_lines( $ped_id );
$total = av_pedido_total( $ped_id );

$format_date = function( $ymd ) {
    if ( ! $ymd ) return '—';
    $d = DateTime::createFromFormat( 'Y-m-d', $ymd );
    return $d ? $d->format( 'd/m/Y' ) : esc_html( $ymd );
};

// Disponibilidad del botón de factura: desde received en adelante (ya no hay
// "ready" intermedio), incluido delivered (pedido que llegó a entregarse sin
// haber generado nunca su factura — caso real encontrado al auditar los
// pedidos existentes antes de esta fase), pero nunca en cancelled. Igual que
// en el SAT, generar/ver la factura no exige tipo de pago: son dos acciones
// independientes (ver av_pedido_invoice_page()).
$puede_generar_factura = ! in_array( $status, [ 'pending', 'ordered', 'cancelled' ], true );

// En pending/ordered (encargo aún no recibido) no se genera factura de
// verdad — quemaría un número de factura real en pedidos que ni siquiera han
// llegado y podrían acabar cancelados — pero sí se puede imprimir un simple
// recibo del encargo (mismo PDF, sin numeración ni registro en cpt-facturas:
// ver av_pedido_receipt_page(), inc/pedidos/venta.php), para poder dárselo al
// cliente al hacer el encargo, antes de que esté entregado o pagado.
$puede_imprimir_recibo = ! $sale_id && in_array( $status, [ 'pending', 'ordered' ], true );
?>

<div class="c-pedido-detail">

<a class="c-articulo-form__back" href="<?php echo esc_url( av_pedidos_page_url() ); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    <span>Volver al listado</span>
</a>

<div class="c-articulo-form__head">
    <h1 class="c-articulo-form__title">Pedido <?php echo esc_html( $pedido_numero ); ?></h1>
</div>

<!-- ── Cliente ────────────────────────────────────────────────────── -->
<div class="c-sat-form__accordion">
    <div class="c-sat-form__accordion-title">Cliente</div>
    <div class="c-sat-form__accordion-content">
        <div class="c-pedido-detail__grid">
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Nombre</span>
                <span class="c-pedido-detail__value"><?php echo $client_id ? '<a href="' . esc_url( get_permalink( $client_id ) ) . '">' . esc_html( $client_name ) . '</a>' : esc_html( $client_name ); ?></span>
            </div>
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Teléfono</span>
                <span class="c-pedido-detail__value"><?php echo esc_html( $client_phone ?: '—' ); ?></span>
            </div>
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Email</span>
                <span class="c-pedido-detail__value"><?php echo esc_html( $client_email ?: '—' ); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- ── Líneas del pedido ──────────────────────────────────────────── -->
<div class="c-sat-form__accordion">
    <div class="c-sat-form__accordion-title">Líneas del pedido (<?php echo count( $lines ); ?>)</div>
    <div class="c-sat-form__accordion-content">
        <ul class="c-pedido-detail__lines">
            <?php foreach ( $lines as $line_index => $line ) :
                $line_vat      = intval( $line['vat'] ?? 21 );
                $line_net      = floatval( $line['sale_price'] ?? 0 );
                $line_gross    = round( $line_net * ( 1 + $line_vat / 100 ), 2 );
                // Sigue siendo el subtotal SIN IVA (av_pedido_line_subtotal,
                // sin tocar): mismo criterio que "Total del pedido" más abajo
                // (av_pedido_total()), para que la suma de líneas cuadre.
                $subtotal      = av_pedido_line_subtotal( $line['sale_price'] ?? 0, $line['quantity'] ?? 1 );
                $line_received = ! empty( $line['received_date'] );
            ?>
            <li class="c-pedido-detail__line" data-line-index="<?php echo esc_attr( $line_index ); ?>">
                <div class="c-pedido-detail__line-head">
                    <strong><?php echo esc_html( $line['name_snapshot'] ?? '—' ); ?></strong>
                    <?php if ( ! empty( $line['sku_snapshot'] ) ) : ?>
                    <small>SKU: <?php echo esc_html( $line['sku_snapshot'] ); ?></small>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $line['description'] ) ) : ?>
                <p class="c-pedido-detail__line-description"><?php echo esc_html( $line['description'] ); ?></p>
                <?php endif; ?>

                <div class="c-pedido-detail__grid">
                    <div class="c-pedido-detail__field">
                        <span class="c-pedido-detail__label">Cantidad</span>
                        <span class="c-pedido-detail__value"><?php echo esc_html( av_articulo_format_qty( $line['quantity'] ?? 1 ) ); ?></span>
                    </div>
                    <div class="c-pedido-detail__field">
                        <span class="c-pedido-detail__label">IVA</span>
                        <span class="c-pedido-detail__value"><?php echo esc_html( $line_vat ); ?>%</span>
                    </div>
                    <div class="c-pedido-detail__field">
                        <span class="c-pedido-detail__label">Sin IVA</span>
                        <span class="c-pedido-detail__value"><?php echo esc_html( number_format( $line_net, 2, ',', '.' ) ); ?> €</span>
                    </div>
                    <div class="c-pedido-detail__field">
                        <span class="c-pedido-detail__label">Con IVA</span>
                        <span class="c-pedido-detail__value"><?php echo esc_html( number_format( $line_gross, 2, ',', '.' ) ); ?> €</span>
                    </div>
                    <div class="c-pedido-detail__field">
                        <span class="c-pedido-detail__label">Subtotal</span>
                        <span class="c-pedido-detail__value"><strong><?php echo esc_html( number_format( $subtotal, 2, ',', '.' ) ); ?> €</strong></span>
                    </div>
                    <div class="c-pedido-detail__field">
                        <span class="c-pedido-detail__label">Proveedor</span>
                        <span class="c-pedido-detail__value"><?php echo esc_html( $line['supplier'] ?: '—' ); ?></span>
                    </div>
                    <div class="c-pedido-detail__field">
                        <span class="c-pedido-detail__label">Fecha recepción línea</span>
                        <span class="c-pedido-detail__value js-pedido-line-received-date"><?php echo esc_html( $format_date( $line['received_date'] ?? '' ) ); ?></span>
                    </div>
                </div>

                <?php if ( $status !== 'cancelled' ) : ?>
                <div class="c-pedido-detail__line-foot">
                    <?php if ( $line_received ) : ?>
                    <span class="c-pedido-detail__line-received-mark js-pedido-line-received-mark">
                        Recibido el <?php echo esc_html( $format_date( $line['received_date'] ) ); ?>
                    </span>
                    <?php else : ?>
                    <button type="button"
                        class="o-button o-button--style-2 c-pedido-detail__line-receive-btn js-pedido-line-receive"
                        data-id="<?php echo esc_attr( $ped_id ); ?>"
                        data-line-index="<?php echo esc_attr( $line_index ); ?>"
                        data-nonce="<?php echo esc_attr( wp_create_nonce( 'av_pedido_nonce' ) ); ?>">
                        Marcar recibido
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="c-pedido-summary">
            <div class="c-pedido-summary__row c-pedido-summary__row--total">
                <span>Total del pedido</span>
                <strong><?php echo esc_html( number_format( $total, 2, ',', '.' ) ); ?> €</strong>
            </div>
        </div>
    </div>
</div>

<!-- ── Datos generales ────────────────────────────────────────────── -->
<div class="c-sat-form__accordion">
    <div class="c-sat-form__accordion-title">Datos generales</div>
    <div class="c-sat-form__accordion-content">
        <div class="c-pedido-detail__grid">
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Estado</span>
                <span class="c-pedido-detail__value">
                    <span class="c-pedidos__status-badge c-pedidos__status-badge--<?php echo esc_attr( $status ); ?> js-pedido-status-badge"><?php echo esc_html( av_pedido_status_label( $status ) ); ?></span>
                </span>
            </div>
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Fecha de creación</span>
                <span class="c-pedido-detail__value"><?php echo esc_html( $entry_date ?: '—' ); ?></span>
            </div>
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Fecha de entrega</span>
                <span class="c-pedido-detail__value js-pedido-delivered-date"><?php echo esc_html( $format_date( $delivered_date ) ); ?></span>
            </div>
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Ubicación</span>
                <span class="c-pedido-detail__value"><?php echo esc_html( $location ?: '—' ); ?></span>
            </div>
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Tipo de pago</span>
                <span class="c-pedido-detail__value"><?php echo esc_html( $price_description ? ( $payment_choices[ $price_description ] ?? $price_description ) : '—' ); ?></span>
            </div>
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Anticipo</span>
                <span class="c-pedido-detail__value"><?php echo $anticipo !== '' ? esc_html( number_format( floatval( $anticipo ), 2, ',', '.' ) ) . ' €' : '—'; ?></span>
            </div>
            <div class="c-pedido-detail__field">
                <span class="c-pedido-detail__label">Pagado</span>
                <span class="c-pedido-detail__value">
                    <span class="c-pedido-detail__paid-badge <?php echo $is_paid ? 'c-pedido-detail__paid-badge--ok' : 'c-pedido-detail__paid-badge--pte'; ?>">
                        <?php echo $is_paid ? 'Sí' : 'No'; ?>
                    </span>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ── Notas ──────────────────────────────────────────────────────── -->
<div class="c-sat-form__accordion">
    <div class="c-sat-form__accordion-title">Notas</div>
    <div class="c-sat-form__accordion-content">
        <div class="c-pedido-detail__field c-pedido-detail__field--full">
            <span class="c-pedido-detail__label">Notas del cliente</span>
            <span class="c-pedido-detail__value"><?php echo $client_notes ? nl2br( esc_html( $client_notes ) ) : '—'; ?></span>
        </div>
        <div class="c-pedido-detail__field c-pedido-detail__field--full">
            <span class="c-pedido-detail__label">Notas internas</span>
            <span class="c-pedido-detail__value"><?php echo $internal_notes ? nl2br( esc_html( $internal_notes ) ) : '—'; ?></span>
        </div>
    </div>
</div>

</div><!-- /.c-pedido-detail -->

<!-- ── Bloque de acciones fijo (mismo componente/CSS que el SAT) ────── -->
<div class="c-sat-form__wrapper-ctas">
    <div class="c-sat-form__ctas-inner">

        <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
            <a class="c-sat-form__cta c-sat-form__cta--secondary" href="<?php echo esc_url( add_query_arg( 'id', $ped_id, av_pedidos_page_url() ) ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>Editar</span>
            </a>

            <?php if ( $status !== 'cancelled' ) : ?>
            <button type="button" class="c-sat-form__cta c-sat-form__cta--secondary js-pedido-cancel" data-id="<?php echo esc_attr( $ped_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'av_pedido_nonce' ) ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                <span>Cancelar</span>
            </button>
            <?php endif; ?>
        </div>

        <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">

            <?php if ( $wa_href ) : ?>
            <a class="c-sat-form__cta c-sat-form__cta--whatsapp" href="<?php echo esc_url( $wa_href ); ?>" target="_blank" title="<?php echo '+' . esc_attr( $client_phone ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Mensaje</span>
            </a>
            <?php elseif ( $status === 'received' ) : ?>
            <span class="c-sat-form__cta c-sat-form__cta--whatsapp" style="opacity:.45;pointer-events:none;" title="Este cliente no tiene teléfono guardado">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Mensaje</span>
            </span>
            <?php endif; ?>

            <?php if ( $puede_generar_factura ) : ?>
            <a class="c-sat-form__cta c-sat-form__cta--invoice<?php echo $sale_id ? ' c-sat-form__cta--invoice-done' : ''; ?>"
               href="<?php echo esc_url( add_query_arg( [ 'pedido_invoice' => '1', 'pedido_id' => $ped_id, 'nonce' => wp_create_nonce( 'pedido_invoice_' . $ped_id ) ], home_url( '/' ) ) ); ?>"
               target="_blank"
               title="<?php echo $sale_id ? 'Ver factura generada' : 'Generar factura en PDF'; ?>">
                <?php if ( $sale_id ) : ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="9 15 11 17 15 13"/></svg>
                    <span>Ver Factura</span>
                <?php else : ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Generar Factura</span>
                <?php endif; ?>
            </a>
            <?php elseif ( $puede_imprimir_recibo ) : ?>
            <a class="c-sat-form__cta c-sat-form__cta--secondary"
               href="<?php echo esc_url( add_query_arg( [ 'pedido_receipt' => '1', 'pedido_id' => $ped_id, 'nonce' => wp_create_nonce( 'pedido_receipt_' . $ped_id ) ], home_url( '/' ) ) ); ?>"
               target="_blank"
               title="Imprimir recibo del encargo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Imprimir Recibo</span>
            </a>
            <?php endif; ?>

        </div>

    </div>
</div>
