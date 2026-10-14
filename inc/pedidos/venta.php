<?php
// ─── Generar factura desde un Pedido (Fase 5, renombrado/alineado con SAT en
// la Fase 6: el botón pasa a llamarse "Generar factura" como en el SAT; la
// idempotencia — candado atómico + cpt-pedido__sale-id — es la misma de la
// Fase 5, sin reconstruir) ────────────────────────────────────────────────
// No existe en este proyecto un CPT/tabla "venta" independiente: el único
// sistema que registra una transacción cerrada con cliente + líneas + total
// es cpt-facturas (hoy ligado siempre a un SAT vía _factura_sat_id — ver
// av_sat_factura_upsert() en functions.php). Reutilizamos esa MISMA CPT y la
// MISMA convención de metas "_factura_*", en vez de inventar una segunda
// estructura de "venta" paralela — con una clave de enlace nueva y propia
// (_factura_pedido_id) para no tocar ni un solo campo de las facturas de SAT.
//
// Las líneas de una factura en este sistema son siempre planas: un array de
// {text, price} (ver av_sat_build_invoice_data() — así es como ya se pliegan
// los "artículos usados" reales del catálogo dentro de una factura de SAT).
// Por eso una línea de pedido "no catalogada" (article_id null) no necesita
// ningún tratamiento especial al convertir: catalogada o no, se pliega igual,
// como texto + importe.
//
// Impresión/PDF: reutiliza components/c-invoice.php (el mismo que usa
// av_sat_invoice_page() en functions.php) — se generalizó con unas pocas
// variables opcionales (título, etiquetas, base/IVA precalculados) para que
// sirva también para un pedido sin tocar nada de cómo se ve/comporta una
// factura de SAT. Ver av_pedido_invoice_page() más abajo.

function av_pedido_facturas_page_url() {
    $page = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-facturas.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    return ! empty( $page ) ? get_permalink( $page[0] ) : home_url( '/' );
}

// Busca si el pedido ya tiene una factura asociada (por _factura_pedido_id,
// no por cpt-pedido__sale-id: esto sirve también para detectar una fila
// huérfana si algo llegase a fallar entre crear la factura y guardar el id
// en el pedido).
function av_pedido_find_factura_id( $pedido_id ) {
    $existing = get_posts( [
        'post_type'      => 'cpt-facturas',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => '_factura_pedido_id', 'value' => $pedido_id, 'compare' => '=' ] ],
    ] );
    return ! empty( $existing ) ? intval( $existing[0] ) : 0;
}

// Construye los datos de factura a partir de las líneas del pedido — snapshot
// histórico: usa SIEMPRE sale_price/vat guardados en la propia línea, nunca
// el precio actual del artículo (que puede haber cambiado desde entonces).
function av_pedido_build_factura_fields( $pedido_id ) {
    $lines        = av_pedido_get_lines( $pedido_id );
    $client_info  = av_pedido_client_info( $pedido_id );
    $client_name  = $client_info['name'];
    $client_phone = $client_info['phone'];

    $parts_items = [];
    $inv_base    = 0;
    $inv_iva     = 0;

    foreach ( $lines as $line ) {
        $qty         = floatval( $line['quantity'] ?? 1 );
        $sale_price  = floatval( $line['sale_price'] ?? 0 ); // snapshot de la línea, nunca el precio actual del artículo
        $vat         = floatval( $line['vat'] ?? 21 );
        $nombre      = trim( (string) ( $line['name_snapshot'] ?? '' ) );
        if ( $nombre === '' ) continue;

        // av_line_amounts(): total CON IVA calculado primero del precio exacto,
        // base/IVA derivados de él (ver inc/pedidos/crud.php).
        $amounts        = av_line_amounts( $sale_price, $qty, $vat );
        $subtotal_base  = $amounts["base"];
        $subtotal_iva   = $amounts["iva"];
        $subtotal_total = $amounts["total"];

        $inv_base += $subtotal_base;
        $inv_iva  += $subtotal_iva;

        // 'base' va aparte del formato {text, price} habitual (ver
        // components/c-invoice.php): un pedido puede mezclar líneas a IVA
        // distinto, así que la columna "Sin IVA" de cada línea no se puede
        // derivar del total dividiendo por un único tipo, como sí vale para
        // el SAT. 'qty' aparte también: columna "Cantidad" propia, ya no
        // se mete "xN" dentro del texto. 'unit_price': precio de una unidad
        // sin IVA — columna "Precio unit." propia.
        $parts_items[] = [
            'text'       => $nombre,
            'qty'        => $qty,
            'unit_price' => number_format( round( $sale_price, 2 ), 2, '.', '' ),
            'price'      => number_format( $subtotal_total, 2, '.', '' ),
            'base'       => number_format( $subtotal_base, 2, '.', '' ),
        ];
    }

    $inv_base  = round( $inv_base, 2 );
    $inv_iva   = round( $inv_iva, 2 );
    $inv_total = round( $inv_base + $inv_iva, 2 );

    // Datos de pago (Fase 6, mismo patrón que av_sat_build_invoice_data()):
    // el anticipo se descuenta del total a pagar, pero no afecta a la base
    // imponible/IVA (igual que en el SAT).
    $price_description = get_post_meta( $pedido_id, 'cpt-pedido__price-description', true );
    $anticipo_raw       = get_post_meta( $pedido_id, 'cpt-pedido__anticipo', true );
    $anticipo           = $anticipo_raw !== '' ? round( floatval( $anticipo_raw ), 2 ) : 0;
    $total_pagar        = max( 0, round( $inv_total - $anticipo, 2 ) );
    $anticipo_payment   = $anticipo > 0 ? get_post_meta( $pedido_id, 'cpt-pedido__anticipo-payment', true ) : '';

    return [
        'pedido_id'          => $pedido_id,
        'client_name'        => $client_name,
        'client_phone'       => $client_phone,
        'parts_items'        => $parts_items,
        'base'               => $inv_base,
        'iva'                => $inv_iva,
        'total'              => $inv_total,
        'price_description'  => $price_description,
        'anticipo'           => $anticipo,
        'anticipo_payment'   => $anticipo_payment,
        'total_pagar'        => $total_pagar,
    ];
}

// Crea (o, si ya existiera por _factura_pedido_id, actualiza) el registro de
// factura para este pedido. Mismo patrón exacto que av_sat_factura_upsert():
// mismo CPT, misma numeración correlativa por año (av_factura_seq_{year},
// compartida con las facturas de SAT — una única serie de numeración para
// todo el negocio), mismo criterio de "el número no se vuelve a tocar".
function av_pedido_factura_upsert( array $data ) {
    $pedido_id = $data['pedido_id'];

    $factura_id = av_pedido_find_factura_id( $pedido_id );

    if ( ! $factura_id ) {
        $year    = intval( wp_date( 'Y' ) );
        $opt_key = 'av_factura_seq_' . $year;
        $seq     = intval( get_option( $opt_key, 0 ) ) + 1;
        update_option( $opt_key, $seq, false );
        $numero = $year . '-' . str_pad( $seq, 6, '0', STR_PAD_LEFT );

        $factura_id = wp_insert_post( [
            'post_type'   => 'cpt-facturas',
            'post_title'  => $numero,
            'post_status' => 'publish',
        ] );
        if ( ! $factura_id || is_wp_error( $factura_id ) ) {
            return new WP_Error( 'factura-error', 'No se ha podido crear la venta.' );
        }
        update_post_meta( $factura_id, '_factura_numero', $numero );
    }

    update_post_meta( $factura_id, '_factura_pedido_id', $pedido_id );
    update_post_meta( $factura_id, '_factura_fecha',      wp_date( 'd/m/Y H:i' ) );
    update_post_meta( $factura_id, '_factura_cliente',    $data['client_name'] );
    update_post_meta( $factura_id, '_factura_telefono',   $data['client_phone'] );
    // Sin equivalente para una venta de pedido (no hay reparación/equipo):
    // se dejan vacíos en vez de inventar datos falsos. El listado de facturas
    // ya trata estos campos como opcionales (célda "SAT" vacía si no hay sat_id).
    update_post_meta( $factura_id, '_factura_sat_num',    '' );
    update_post_meta( $factura_id, '_factura_tipo',       '' );
    update_post_meta( $factura_id, '_factura_modelo',     '' );
    update_post_meta( $factura_id, '_factura_serial',     '' );
    // wp_slash: update_post_meta() hace su propio wp_unslash() interno, que
    // sin esto se comería el escape del JSON en cuanto un texto llevara una
    // comilla o barra invertida (ver el mismo comentario en functions.php,
    // av_sat_add_history()).
    update_post_meta( $factura_id, '_factura_reparacion', wp_slash( wp_json_encode( [], JSON_UNESCAPED_UNICODE ) ) );
    update_post_meta( $factura_id, '_factura_piezas',     wp_slash( wp_json_encode( $data['parts_items'], JSON_UNESCAPED_UNICODE ) ) );
    update_post_meta( $factura_id, '_factura_base',       $data['base'] );
    update_post_meta( $factura_id, '_factura_iva',        $data['iva'] );
    update_post_meta( $factura_id, '_factura_total',      $data['total'] );
    update_post_meta( $factura_id, '_factura_anticipo',            $data['anticipo'] );
    update_post_meta( $factura_id, '_factura_anticipo_forma_pago', $data['anticipo_payment'] );
    update_post_meta( $factura_id, '_factura_total_pagar',         $data['total_pagar'] );
    // "Pagado" (badge del listado de Facturas) se deriva de que forma_pago
    // tenga valor — igual que con el SAT: aquí se guarda el tipo de pago real
    // del pedido (Objetivo 2), nunca "Contado" a ciegas.
    update_post_meta( $factura_id, '_factura_forma_pago', $data['price_description'] );
    update_post_meta( $factura_id, '_factura_tecnico',    wp_get_current_user()->display_name ?: wp_get_current_user()->user_login );
    update_post_meta( $factura_id, '_factura_garantia',   '' );

    return $factura_id;
}

// Valida y crea/actualiza la factura. NO toca cpt-pedido__status ni exige
// tipo de pago — generar/imprimir la factura es una acción independiente de
// "marcar como entregado", exactamente igual que en el SAT (generar la
// factura de un SAT no lo finaliza: son dos botones y dos momentos
// distintos). Quien llama (av_pedido_invoice_page()) sí guarda el id de la
// factura en el pedido, para poder mostrar "Ver factura" en vez de
// "Generar factura" la próxima vez.
function av_pedido_convert_to_venta( $pedido_id ) {

    if ( get_post_type( $pedido_id ) !== 'cpt-pedidos' ) {
        return new WP_Error( 'pedido-invalido', 'Pedido no válido.' );
    }

    // Vale un cliente de la BDD o uno escrito a mano en el pedido.
    $client_info = av_pedido_client_info( $pedido_id );
    if ( ! $client_info['client_id'] && ! $client_info['manual'] ) {
        return new WP_Error( 'cliente-invalido', 'El pedido no tiene un cliente válido.' );
    }

    $lines = av_pedido_get_lines( $pedido_id );
    if ( empty( $lines ) ) {
        return new WP_Error( 'sin-lineas', 'El pedido no tiene líneas que convertir.' );
    }

    $data = av_pedido_build_factura_fields( $pedido_id );

    return av_pedido_factura_upsert( $data );
}

// ─── Imprimir / generar factura del pedido ──────────────────────────────────
// Mismo mecanismo exacto que av_sat_invoice_page() (functions.php): un enlace
// GET, idempotente, que al visitarse crea (o reutiliza) la factura y muestra
// la plantilla imprimible. No hay AJAX ni confirm() — es un <a target="_blank">
// como en el SAT. Al no depender de un candado de un solo uso, dos pestañas
// abriendo el mismo enlace a la vez no son un problema: la segunda visita
// simplemente actualiza (upsert) el mismo registro, igual que recargar la
// factura de un SAT la re-sincroniza.
//
// Adrede NO exige tipo de pago ni toca cpt-pedido__status: el usuario necesita
// poder imprimir/entregar el pedido ANTES de marcarlo como pagado. Esa es
// precisamente la separación que ya tiene el SAT (generar su factura no lo
// finaliza) y que esta función replica para Pedidos.
add_action( 'template_redirect', 'av_pedido_invoice_page' );
function av_pedido_invoice_page() {
    if ( ! isset( $_GET['pedido_invoice'] ) ) return;

    $pedido_id = intval( $_GET['pedido_id'] ?? 0 );
    if ( ! $pedido_id ) wp_die( 'Pedido no válido.' );
    if ( ! is_user_logged_in() ) wp_die( 'Acceso denegado.' );
    if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'pedido_invoice_' . $pedido_id ) ) wp_die( 'Nonce inválido.' );
    if ( get_post_type( $pedido_id ) !== 'cpt-pedidos' ) wp_die( 'Pedido no encontrado.' );

    // El estado solo bloquea GENERAR una factura nueva. Si ya existe (p.ej. se
    // abre desde el listado de Facturas, o el pedido se canceló después de
    // facturarse), se puede volver a ver siempre — igual que el SAT, cuyo
    // av_sat_invoice_page() no comprueba el estado en absoluto.
    if ( ! av_pedido_find_factura_id( $pedido_id ) ) {
        $status = get_post_meta( $pedido_id, 'cpt-pedido__status', true ) ?: 'pending';
        if ( $status === 'cancelled' ) {
            wp_die( 'Este pedido está cancelado: no se puede generar factura.' );
        }
        // Mismo criterio que antes (Fase 6): disponible desde "received" en
        // adelante (también "delivered", para pedidos que llegaron a entregarse
        // sin haber generado nunca su factura).
        if ( in_array( $status, [ 'pending', 'ordered' ], true ) ) {
            wp_die( 'El pedido debe estar recibido antes de generar la factura.' );
        }
    }

    $factura_id = av_pedido_convert_to_venta( $pedido_id );
    if ( is_wp_error( $factura_id ) ) {
        wp_die( esc_html( $factura_id->get_error_message() ) );
    }

    // Guarda de referencia (para que la ficha del pedido pueda mostrar "Ver
    // factura" en vez de "Generar factura"); no forma parte de la
    // idempotencia real, que sigue siendo _factura_pedido_id.
    update_post_meta( $pedido_id, 'cpt-pedido__sale-id', $factura_id );

    $data = av_pedido_build_factura_fields( $pedido_id );

    // ── Variables que espera components/c-invoice.php (mismo contrato que
    // usa av_sat_invoice_page() más abajo, en functions.php) ──
    $invoice_title_prefix = 'Pedido';
    $invoice_subtitle     = 'Factura de venta';
    $sat_row_label        = 'Nº Pedido';
    $hide_equipment_block = true;
    $parts_section_label  = 'Productos';

    $sat_id_visible = get_post_meta( $pedido_id, 'cpt-pedido__pedido-id', true );
    $client_id      = intval( get_post_meta( $pedido_id, 'cpt-pedido__client-id', true ) );
    $client_name    = $data['client_name'];
    $client_phone   = $data['client_phone'];
    $type_label     = '';
    $model          = '';
    $serial         = '';
    $physical_condition = '';
    $incident       = '';
    $entry_date     = get_post_meta( $pedido_id, 'cpt-pedido__entry-date', true );
    $repair_date    = '';
    $attended       = wp_get_current_user()->display_name ?: wp_get_current_user()->user_login;
    $price_desc       = $data['price_description'];
    $anticipo         = $data['anticipo'] > 0 ? number_format( $data['anticipo'], 2, '.', '' ) : '';
    $anticipo_payment = $data['anticipo_payment'];
    $warranty_period  = '';
    $repair_items     = [];
    $parts_items      = $data['parts_items'];

    $inv_cfg = av_get_invoice_config();

    // Base/IVA/Total ya calculados línea a línea en av_pedido_build_factura_fields()
    // (un pedido puede tener líneas a IVA distinto): c-invoice.php los usa tal
    // cual en vez de recalcularlos dividiendo por un único $iva_rate.
    $base       = $data['base'];
    $iva_amount = $data['iva'];
    $total_iva  = $data['total'];

    // Etiqueta "IVA (X%)" del total: si todas las líneas comparten el mismo
    // tipo se muestra ese tipo (como en el SAT); si hay tipos distintos, un
    // único porcentaje sería engañoso, así que se muestra sin porcentaje.
    $vats = array_unique( array_map( fn( $l ) => floatval( $l['vat'] ?? 21 ), av_pedido_get_lines( $pedido_id ) ) );
    $iva_pct_label = count( $vats ) === 1 ? ( reset( $vats ) . '%' ) : 'varios tipos';

    $factura_numero = get_post_meta( $factura_id, '_factura_numero', true );

    include locate_template( 'components/c-invoice.php' );
    exit;
}

// ─── Recibo del encargo (pending/ordered, antes de recibir el pedido) ───────
// Un pedido "pending"/"ordered" todavía no puede facturarse de verdad (ver
// av_pedido_invoice_page(): eso quemaría un número de factura real en un
// encargo que ni siquiera ha llegado, y podría acabar cancelado), pero sí
// hace falta poder darle al cliente algo en papel al hacer el encargo. Este
// recibo reutiliza la MISMA plantilla (components/c-invoice.php) que la
// factura, con los mismos datos de líneas/precios, pero:
//   - NO crea ni actualiza ningún registro en cpt-facturas.
//   - NO consume numeración de factura (usa el propio número del pedido).
//   - NO exige ningún estado ni tipo de pago: es solo informativo.
add_action( 'template_redirect', 'av_pedido_receipt_page' );
function av_pedido_receipt_page() {
    if ( ! isset( $_GET['pedido_receipt'] ) ) return;

    $pedido_id = intval( $_GET['pedido_id'] ?? 0 );
    if ( ! $pedido_id ) wp_die( 'Pedido no válido.' );
    if ( ! is_user_logged_in() ) wp_die( 'Acceso denegado.' );
    if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'pedido_receipt_' . $pedido_id ) ) wp_die( 'Nonce inválido.' );
    if ( get_post_type( $pedido_id ) !== 'cpt-pedidos' ) wp_die( 'Pedido no encontrado.' );

    // El cliente ya no es obligatorio en un pedido (ver av_pedido_validate_post()):
    // si no hay, el recibo se imprime igual, con "—" en los datos de cliente —
    // mismo criterio que la ficha del pedido (av_pedido_resolve_client_name()).
    $lines = av_pedido_get_lines( $pedido_id );
    if ( empty( $lines ) ) {
        wp_die( 'El pedido no tiene líneas que mostrar en el recibo.' );
    }

    $data = av_pedido_build_factura_fields( $pedido_id );

    // ── Mismas variables que av_pedido_invoice_page(), pero sin factura real ──
    $invoice_doc_label    = 'Recibo'; // título de la pestaña: "Recibo Pedido #X", no "Factura..."
    $invoice_title_prefix = 'Pedido';
    $invoice_subtitle     = 'Recibo de encargo';
    $sat_row_label        = 'Nº Pedido';
    $hide_equipment_block = true;
    $parts_section_label  = 'Productos';

    $pedido_numero  = get_post_meta( $pedido_id, 'cpt-pedido__pedido-id', true );
    $sat_id_visible = $pedido_numero;
    $client_name    = $data['client_name'];
    $client_phone   = $data['client_phone'];
    $type_label     = '';
    $model          = '';
    $serial         = '';
    $physical_condition = '';
    $incident       = '';
    $entry_date     = get_post_meta( $pedido_id, 'cpt-pedido__entry-date', true );
    $repair_date    = '';
    $attended       = wp_get_current_user()->display_name ?: wp_get_current_user()->user_login;
    $price_desc       = $data['price_description'];
    $anticipo         = $data['anticipo'] > 0 ? number_format( $data['anticipo'], 2, '.', '' ) : '';
    $anticipo_payment = $data['anticipo_payment'];
    $warranty_period  = '';
    $repair_items     = [];
    $parts_items      = $data['parts_items'];

    $inv_cfg = av_get_invoice_config();

    $base       = $data['base'];
    $iva_amount = $data['iva'];
    $total_iva  = $data['total'];

    $vats = array_unique( array_map( fn( $l ) => floatval( $l['vat'] ?? 21 ), $lines ) );
    $iva_pct_label = count( $vats ) === 1 ? ( reset( $vats ) . '%' ) : 'varios tipos';

    // No hay número de factura real: el propio número de pedido hace de
    // referencia del documento (no es una factura, no consume su numeración).
    $factura_numero = $pedido_numero;

    include locate_template( 'components/c-invoice.php' );
    exit;
}
