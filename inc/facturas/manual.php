<?php
// ─── Facturas manuales (sin SAT ni pedido de origen) ────────────────────────
// Una factura creada a mano desde el listado de Facturas. Reutiliza TODO lo
// que ya existe, sin duplicar lógica de negocio:
//   - mismo CPT (cpt-facturas) y mismas metas _factura_* que las facturas de
//     SAT/Pedido, así que el listado, filtros, badge de "Pagada", totales y
//     la numeración correlativa por año (av_factura_seq_{year}, una única
//     serie para todo el negocio) funcionan sin tocarlos;
//   - mismo cliente que Pedidos (uno de la BDD, o escrito a mano con la opción
//     de guardarlo también en la BDD: av_create_client_record());
//   - misma tabla de líneas "Artículos y servicios" que el SAT
//     (components/c-sat-lines.php, saneada con av_sat_sanitize_lines());
//   - mismo anticipo + forma de pago del anticipo y mismo tipo de pago que
//     Pedidos (av_pedido_payment_choices());
//   - misma plantilla imprimible (components/c-invoice.php).
//
// Lo único propio de una factura manual es la marca _factura_manual = '1' (que
// es lo que hace que el listado la incluya, ver av_build_facturas_query_args())
// y _factura_lines (las líneas tal cual se escribieron, con su IVA y precio
// sin IVA exactos, para poder volver a editarlas: _factura_piezas ya es la
// versión "de impresión", con importes redondeados).

function av_facturas_page_url() {
    $page = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-facturas.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    return ! empty( $page ) ? get_permalink( $page[0] ) : home_url( '/' );
}

// Misma serie de numeración que av_sat_factura_upsert() y
// av_pedido_factura_upsert() (functions.php / inc/pedidos/venta.php): el
// número se asigna una sola vez al crear y nunca se vuelve a tocar.
function av_factura_manual_next_numero() {
    $year    = intval( wp_date( 'Y' ) );
    $opt_key = 'av_factura_seq_' . $year;
    $seq     = intval( get_option( $opt_key, 0 ) ) + 1;
    update_option( $opt_key, $seq, false );
    return $year . '-' . str_pad( $seq, 6, '0', STR_PAD_LEFT );
}

// Líneas saneadas (JSON de av_sat_sanitize_lines()) -> líneas de impresión
// con base/IVA/total calculados línea a línea (av_line_amounts(): cada línea
// puede llevar un IVA distinto), mismo formato que ya consume
// components/c-invoice.php para Pedidos/Presupuestos/SAT.
function av_factura_manual_build_items( $lines_json ) {
    $lines = json_decode( (string) $lines_json, true );
    if ( ! is_array( $lines ) ) $lines = [];

    $parts_items = [];
    $base_total  = 0;
    $iva_total   = 0;

    foreach ( $lines as $line ) {
        $qty         = floatval( $line['quantity'] ?? 1 );
        $sale_price  = floatval( $line['sale_price'] ?? 0 );
        $vat         = floatval( $line['vat'] ?? 21 );
        $nombre      = trim( (string) ( $line['name_snapshot'] ?? '' ) );
        $description = trim( (string) ( $line['description'] ?? '' ) );
        if ( $nombre === '' ) continue;

        $amounts     = av_line_amounts( $sale_price, $qty, $vat );
        $base_total += $amounts['base'];
        $iva_total  += $amounts['iva'];

        $texto = $nombre . ( $description !== '' ? ' — ' . $description : '' );

        $parts_items[] = [
            'text'       => $texto,
            'qty'        => $qty,
            'unit_price' => number_format( round( $sale_price, 2 ), 2, '.', '' ),
            'price'      => number_format( $amounts['total'], 2, '.', '' ),
            'base'       => number_format( $amounts['base'], 2, '.', '' ),
        ];
    }

    $base_total = round( $base_total, 2 );
    $iva_total  = round( $iva_total, 2 );

    return [
        'parts_items' => $parts_items,
        'base'        => $base_total,
        'iva'         => $iva_total,
        'total'       => round( $base_total + $iva_total, 2 ),
    ];
}

add_action( 'admin_post_av_guardar_factura_manual', 'av_guardar_factura_manual' );
function av_guardar_factura_manual() {

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    check_admin_referer( 'av_factura_manual_nonce', 'nonce' );

    $editing_id  = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    $redirect_to = av_facturas_page_url();
    // "id" (no "edit"): así template-facturas.php reconoce el modo edición —
    // si el guardado falla, la redirección con el error vuelve a abrir ESTE
    // MISMO formulario en vez de caer al listado (igual que Pedidos).
    $form_url = $editing_id ? add_query_arg( 'id', $editing_id, $redirect_to ) : add_query_arg( 'new', '1', $redirect_to );

    if ( $editing_id ) {
        if ( get_post_type( $editing_id ) !== 'cpt-facturas'
            || get_post_meta( $editing_id, '_factura_manual', true ) !== '1' ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }
    }

    // ── Cliente: uno de la BDD, o escrito a mano (opcionalmente guardado
    // también en la BDD). Misma lógica que av_pedido_validate_post(). ──
    $client_id = isset( $_POST['client-id'] ) ? intval( $_POST['client-id'] ) : 0;
    if ( $client_id && get_post_type( $client_id ) !== 'cpt-clients' ) {
        $client_id = 0;
    }

    $client_name  = '';
    $client_phone = '';

    if ( $client_id ) {
        $client_name  = (string) get_field( 'cpt-client__name', $client_id );
        $client_phone = trim( get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id ) );
    } else {
        $m_name  = isset( $_POST['manual-client-name'] )  ? trim( sanitize_text_field( $_POST['manual-client-name'] ) )  : '';
        $m_ext   = isset( $_POST['manual-client-ext'] )   ? trim( sanitize_text_field( $_POST['manual-client-ext'] ) )   : '';
        $m_phone = isset( $_POST['manual-client-phone'] ) ? trim( sanitize_text_field( $_POST['manual-client-phone'] ) ) : '';

        if ( $m_name !== '' && ! empty( $_POST['manual-client-save'] ) ) {
            $new_client_id = av_create_client_record( $m_name, $m_ext, $m_phone );
            if ( ! $new_client_id ) {
                wp_redirect( add_query_arg( 'error', 'unknown', $form_url ) );
                exit;
            }
            $client_id    = $new_client_id;
            $client_name  = $m_name;
            $client_phone = trim( ( $m_ext !== '' ? $m_ext : '34' ) . ' ' . $m_phone );
        } else {
            $client_name  = $m_name;
            $client_phone = $m_phone !== '' ? trim( $m_ext . ' ' . $m_phone ) : '';
        }
    }

    // Una factura necesita saber a quién se factura.
    if ( $client_name === '' ) {
        wp_redirect( add_query_arg( 'error', 'cliente-requerido', $form_url ) );
        exit;
    }

    // ── Líneas ──
    $lines_json = av_sat_sanitize_lines( wp_unslash( $_POST['sat-lines'] ?? '' ) );
    if ( $lines_json === '' ) {
        wp_redirect( add_query_arg( 'error', 'lineas-requeridas', $form_url ) );
        exit;
    }
    $built = av_factura_manual_build_items( $lines_json );

    // ── Pago: tipo de pago + anticipo con su forma de pago (mismas reglas
    // que Pedidos: sin cantidad de anticipo no se guarda forma de pago
    // suelta, y con cantidad la forma de pago es obligatoria). ──
    $forma_pago = isset( $_POST['price-description'] ) ? sanitize_text_field( $_POST['price-description'] ) : '';
    if ( ! array_key_exists( $forma_pago, av_pedido_payment_choices() ) ) {
        $forma_pago = '';
    }

    $anticipo_raw = isset( $_POST['anticipo'] ) ? trim( str_replace( ',', '.', (string) $_POST['anticipo'] ) ) : '';
    $anticipo     = ( $anticipo_raw !== '' && is_numeric( $anticipo_raw ) && floatval( $anticipo_raw ) > 0 ) ? round( floatval( $anticipo_raw ), 2 ) : 0;

    $anticipo_forma = isset( $_POST['anticipo-payment'] ) ? sanitize_text_field( $_POST['anticipo-payment'] ) : '';
    if ( $anticipo <= 0 || ! array_key_exists( $anticipo_forma, av_pedido_payment_choices() ) ) {
        $anticipo_forma = '';
    }
    if ( $anticipo > 0 && $anticipo_forma === '' ) {
        wp_redirect( add_query_arg( 'error', 'falta-pago-anticipo', $form_url ) );
        exit;
    }

    $total_pagar = max( 0, round( $built['total'] - $anticipo, 2 ) );
    $notas       = sanitize_textarea_field( wp_unslash( $_POST['notas'] ?? '' ) );

    // ── Crear / actualizar ──
    if ( $editing_id ) {
        $factura_id = $editing_id;
    } else {
        $numero     = av_factura_manual_next_numero();
        $factura_id = wp_insert_post( [
            'post_type'   => 'cpt-facturas',
            'post_title'  => $numero,
            'post_status' => 'publish',
        ] );
        if ( ! $factura_id || is_wp_error( $factura_id ) ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $form_url ) );
            exit;
        }
        // Se asigna una sola vez y nunca se vuelve a tocar.
        update_post_meta( $factura_id, '_factura_numero', $numero );
        update_post_meta( $factura_id, '_factura_fecha',  wp_date( 'd/m/Y H:i' ) );
        update_post_meta( $factura_id, '_factura_tecnico', wp_get_current_user()->display_name ?: wp_get_current_user()->user_login );
    }

    update_post_meta( $factura_id, '_factura_manual',    '1' );
    update_post_meta( $factura_id, '_factura_client_id', $client_id );
    update_post_meta( $factura_id, '_factura_cliente',   $client_name );
    update_post_meta( $factura_id, '_factura_telefono',  $client_phone );
    // Sin equivalente en una factura manual (no hay equipo ni SAT): vacíos,
    // el listado ya los trata como opcionales.
    update_post_meta( $factura_id, '_factura_sat_num', '' );
    update_post_meta( $factura_id, '_factura_tipo',    '' );
    update_post_meta( $factura_id, '_factura_modelo',  '' );
    update_post_meta( $factura_id, '_factura_serial',  '' );
    // wp_slash en los JSON: update_post_meta() hace su propio wp_unslash()
    // interno, que sin esto se comería el escape en cuanto un texto llevara
    // una comilla o barra invertida (mismo motivo que en el resto de módulos).
    update_post_meta( $factura_id, '_factura_lines',      wp_slash( $lines_json ) );
    update_post_meta( $factura_id, '_factura_reparacion', wp_slash( wp_json_encode( [], JSON_UNESCAPED_UNICODE ) ) );
    update_post_meta( $factura_id, '_factura_piezas',     wp_slash( wp_json_encode( $built['parts_items'], JSON_UNESCAPED_UNICODE ) ) );
    update_post_meta( $factura_id, '_factura_base',       $built['base'] );
    update_post_meta( $factura_id, '_factura_iva',        $built['iva'] );
    update_post_meta( $factura_id, '_factura_total',      $built['total'] );
    update_post_meta( $factura_id, '_factura_anticipo',            $anticipo );
    update_post_meta( $factura_id, '_factura_anticipo_forma_pago', $anticipo_forma );
    update_post_meta( $factura_id, '_factura_total_pagar',         $total_pagar );
    // "Pagada" (badge/filtro del listado) se deriva de que forma_pago tenga
    // valor — el mismo criterio que en SAT y Pedidos.
    update_post_meta( $factura_id, '_factura_forma_pago', $forma_pago );
    update_post_meta( $factura_id, '_factura_garantia',   '' );
    update_post_meta( $factura_id, '_factura_notas',      $notas );

    wp_redirect( add_query_arg( [ 'id' => $factura_id, $editing_id ? 'actualizada' : 'creada' => '1' ], $redirect_to ) );
    exit;
}

// ─── Imprimir una factura manual ────────────────────────────────────────────
// Mismo mecanismo que av_pedido_invoice_page()/av_sat_invoice_page(): un
// enlace GET con nonce que muestra la plantilla imprimible. A diferencia de
// aquéllas no crea nada (la factura ya existe al guardarse el formulario),
// solo la muestra tal y como se guardó.
add_action( 'template_redirect', 'av_factura_manual_invoice_page' );
function av_factura_manual_invoice_page() {
    if ( ! isset( $_GET['factura_manual_pdf'] ) ) return;

    $factura_id = intval( $_GET['factura_id'] ?? 0 );
    if ( ! $factura_id ) wp_die( 'Factura no válida.' );
    if ( ! is_user_logged_in() ) wp_die( 'Acceso denegado.' );
    if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'factura_manual_pdf_' . $factura_id ) ) wp_die( 'Nonce inválido.' );
    if ( get_post_type( $factura_id ) !== 'cpt-facturas'
        || get_post_meta( $factura_id, '_factura_manual', true ) !== '1' ) {
        wp_die( 'Factura no encontrada.' );
    }

    $parts_items = json_decode( (string) get_post_meta( $factura_id, '_factura_piezas', true ), true );
    if ( ! is_array( $parts_items ) ) $parts_items = [];

    // ── Variables que espera components/c-invoice.php (mismo contrato que
    // av_pedido_invoice_page()) ──
    $invoice_title_prefix = 'Factura';
    $invoice_subtitle     = 'Factura';
    $entry_date_label     = 'Fecha';
    $incident_label       = 'Notas';
    $hide_equipment_block = true;
    $parts_section_label  = 'Artículos y servicios';

    $sat_id_visible     = '';
    $client_id          = intval( get_post_meta( $factura_id, '_factura_client_id', true ) );
    $client_name        = get_post_meta( $factura_id, '_factura_cliente', true );
    $client_phone       = get_post_meta( $factura_id, '_factura_telefono', true );
    $type_label         = '';
    $model              = '';
    $serial             = '';
    $physical_condition = '';
    $incident           = get_post_meta( $factura_id, '_factura_notas', true );
    $entry_date         = get_post_meta( $factura_id, '_factura_fecha', true );
    $repair_date        = '';
    $attended           = get_post_meta( $factura_id, '_factura_tecnico', true );
    $price_desc         = get_post_meta( $factura_id, '_factura_forma_pago', true );
    $anticipo_val       = floatval( get_post_meta( $factura_id, '_factura_anticipo', true ) );
    $anticipo           = $anticipo_val > 0 ? number_format( $anticipo_val, 2, '.', '' ) : '';
    $anticipo_payment   = get_post_meta( $factura_id, '_factura_anticipo_forma_pago', true );
    $warranty_period    = '';
    $repair_items       = [];

    $inv_cfg = av_get_invoice_config();

    // Base/IVA/Total ya calculados línea a línea al guardar.
    $base       = floatval( get_post_meta( $factura_id, '_factura_base', true ) );
    $iva_amount = floatval( get_post_meta( $factura_id, '_factura_iva', true ) );
    $total_iva  = floatval( get_post_meta( $factura_id, '_factura_total', true ) );

    $saved_lines = json_decode( (string) get_post_meta( $factura_id, '_factura_lines', true ), true );
    $vats = is_array( $saved_lines )
        ? array_unique( array_map( fn( $l ) => floatval( $l['vat'] ?? 21 ), $saved_lines ) )
        : [];
    $iva_pct_label = count( $vats ) === 1 ? ( reset( $vats ) . '%' ) : 'varios tipos';

    $factura_numero = get_post_meta( $factura_id, '_factura_numero', true );

    include locate_template( 'components/c-invoice.php' );
    exit;
}
