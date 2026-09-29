<?php
// ─── Impresión / PDF de un presupuesto ──────────────────────────────────────
// Reutiliza components/c-invoice.php (el mismo que usa av_sat_invoice_page()
// para las facturas de SAT y av_pedido_invoice_page()/av_pedido_receipt_page()
// para Pedidos) con sus variables opcionales de override, sin tocar nada de
// cómo se ve/comporta una factura de SAT.
//
// A diferencia de una factura, un presupuesto:
//   - NO crea ni actualiza ningún registro en cpt-facturas.
//   - NO consume la numeración de factura (av_factura_seq_*): usa su propia
//     serie (cpt-presupuesto__presupuesto-id, ver numbering.php).
//   - Es un documento no vinculante: se puede volver a imprimir tantas veces
//     como haga falta, siempre con los datos actuales del presupuesto.

// Construye los datos de línea a línea — un presupuesto puede mezclar
// artículos con IVA distinto, así que la base/IVA de cada línea se calcula
// aparte (igual que av_pedido_build_factura_fields(), nunca dividiendo un
// total entre un único tipo, como sí vale para el SAT con IVA único).
function av_presupuesto_build_data( $presupuesto_id ) {
    $client_id = intval( get_post_meta( $presupuesto_id, 'cpt-presupuesto__client-id', true ) );
    $lines     = av_presupuesto_get_lines( $presupuesto_id );

    $client_name  = av_presupuesto_resolve_client_name( $client_id );
    $client_phone = '';
    if ( $client_id ) {
        $ext   = get_field( 'cpt-client__extension', $client_id );
        $phone = get_field( 'cpt-client__phone', $client_id );
        $client_phone = trim( $ext . ' ' . $phone );
    }

    $parts_items = [];
    $base_total  = 0;
    $iva_total   = 0;

    foreach ( $lines as $line ) {
        $qty         = floatval( $line['quantity'] ?? 1 );
        $sale_price  = floatval( $line['sale_price'] ?? 0 );
        $vat         = floatval( $line['vat'] ?? 21 );
        $nombre      = trim( (string) ( $line['name_snapshot'] ?? '' ) );
        $sku         = trim( (string) ( $line['sku_snapshot'] ?? '' ) );
        $description = trim( (string) ( $line['description'] ?? '' ) );
        if ( $nombre === '' ) continue;

        // av_line_amounts(): total CON IVA calculado primero del precio exacto,
        // base/IVA derivados de él (ver inc/pedidos/crud.php).
        $amounts        = av_line_amounts( $sale_price, $qty, $vat );
        $subtotal_base  = $amounts["base"];
        $subtotal_iva   = $amounts["iva"];
        $subtotal_total = $amounts["total"];

        $base_total += $subtotal_base;
        $iva_total  += $subtotal_iva;

        $texto = $sku !== '' ? $sku . ' · ' . $nombre : $nombre;
        if ( $qty != 1 ) $texto .= ' x' . av_articulo_format_qty( $qty );
        if ( $description !== '' ) $texto .= ' — ' . $description;

        $parts_items[] = [
            'text'  => $texto,
            'price' => number_format( $subtotal_total, 2, '.', '' ),
            'base'  => number_format( $subtotal_base, 2, '.', '' ),
        ];
    }

    $base_total = round( $base_total, 2 );
    $iva_total  = round( $iva_total, 2 );
    $total      = round( $base_total + $iva_total, 2 );

    return [
        'client_id'    => $client_id,
        'client_name'  => $client_name,
        'client_phone' => $client_phone,
        'parts_items'  => $parts_items,
        'base'         => $base_total,
        'iva'          => $iva_total,
        'total'        => $total,
    ];
}

add_action( 'template_redirect', 'av_presupuesto_pdf_page' );
function av_presupuesto_pdf_page() {
    if ( ! isset( $_GET['presupuesto_pdf'] ) ) return;

    $presupuesto_id = intval( $_GET['presupuesto_id'] ?? 0 );
    if ( ! $presupuesto_id ) wp_die( 'Presupuesto no válido.' );
    if ( ! is_user_logged_in() ) wp_die( 'Acceso denegado.' );
    if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'presupuesto_pdf_' . $presupuesto_id ) ) wp_die( 'Nonce inválido.' );
    if ( get_post_type( $presupuesto_id ) !== 'cpt-presupuestos' ) wp_die( 'Presupuesto no encontrado.' );

    $lines = av_presupuesto_get_lines( $presupuesto_id );
    if ( empty( $lines ) ) {
        wp_die( 'El presupuesto no tiene líneas que mostrar.' );
    }

    $data = av_presupuesto_build_data( $presupuesto_id );

    // ── Variables que espera components/c-invoice.php ──
    // $invoice_title_prefix se deja vacío a propósito: el título de la
    // pestaña (c-invoice.php) junta "$invoice_doc_label $invoice_title_prefix
    // #numero" con un espacio fijo entre medias — con los dos a "Presupuesto"
    // saldría duplicado ("Presupuesto Presupuesto #..."), a diferencia del
    // SAT/Pedido, donde sí son dos palabras distintas ("Factura SAT #123").
    $invoice_doc_label    = 'Presupuesto';
    $invoice_title_prefix = '';
    $invoice_subtitle     = 'Presupuesto';
    $sat_row_label        = 'Nº Presupuesto';
    $entry_date_label     = 'Fecha';
    $incident_label       = 'Notas';
    $hide_equipment_block = true;
    $parts_section_label  = 'Artículos y servicios';

    $presupuesto_numero = get_post_meta( $presupuesto_id, 'cpt-presupuesto__presupuesto-id', true );
    $sat_id_visible      = $presupuesto_numero;
    $client_id           = $data['client_id'];
    $client_name         = $data['client_name'];
    $client_phone        = $data['client_phone'];
    $type_label          = '';
    $model               = '';
    $serial              = '';
    $physical_condition  = '';
    $incident            = get_post_meta( $presupuesto_id, 'cpt-presupuesto__notes', true );
    $entry_date          = get_post_meta( $presupuesto_id, 'cpt-presupuesto__entry-date', true );
    $repair_date         = '';
    $attended            = wp_get_current_user()->display_name ?: wp_get_current_user()->user_login;
    $price_desc          = '';
    $anticipo            = '';
    $anticipo_payment    = '';
    $warranty_period     = '';
    $repair_items        = [];
    $parts_items         = $data['parts_items'];

    $inv_cfg = av_get_invoice_config();

    // Base/IVA/Total ya calculados línea a línea (ver av_presupuesto_build_data()).
    $base       = $data['base'];
    $iva_amount = $data['iva'];
    $total_iva  = $data['total'];

    $vats = array_unique( array_map( fn( $l ) => floatval( $l['vat'] ?? 21 ), $lines ) );
    $iva_pct_label = count( $vats ) === 1 ? ( reset( $vats ) . '%' ) : 'varios tipos';

    // No hay número de factura real: el propio número de presupuesto hace de
    // referencia del documento — no consume la numeración de facturas.
    $factura_numero = $presupuesto_numero;

    include locate_template( 'components/c-invoice.php' );
    exit;
}
