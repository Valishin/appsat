<?php
// ─── Historial del artículo (auditoría) ─────────────────────────────────────
// Clon exacto de av_sat_add_history(): un JSON array en un meta del propio
// post. Se usa para creación, cambios de precio, ajustes de stock, uso en
// SAT, devoluciones y desactivación.
function av_articulo_add_history( $articulo_id, $label, $type = 'status', $text = '' ) {
    $raw     = get_post_meta( $articulo_id, 'cpt-articulo__history', true );
    $history = ( $raw && is_string( $raw ) ) ? json_decode( $raw, true ) : [];
    if ( ! is_array( $history ) ) $history = [];

    $user  = wp_get_current_user();
    $entry = [
        'ts'    => date_i18n( 'd/m/Y H:i' ),
        'label' => $label,
        'type'  => $type,
        'user'  => $user->display_name ?: $user->user_login,
    ];
    if ( $text !== '' ) $entry['text'] = $text;

    $history[] = $entry;
    // wp_slash: update_post_meta() hace su propio wp_unslash() interno (ver
    // el mismo comentario en av_sat_add_history(), functions.php) — sin esto
    // se comería el escape del JSON en cuanto un texto llevara una comilla o
    // barra invertida.
    update_post_meta( $articulo_id, 'cpt-articulo__history', wp_slash( wp_json_encode( $history, JSON_UNESCAPED_UNICODE ) ) );
}

// ─── Movimientos de stock ────────────────────────────────────────────────────
// Única función autorizada a tocar "cpt-articulo__stock". Nada más en todo el
// código debe hacer update_post_meta(..., 'cpt-articulo__stock', ...)
// directamente (salvo la creación del artículo, que lo inicializa a 0 antes
// de que el primer movimiento — "initial_stock" — lo lleve a su valor real).

function av_articulo_movement_types() {
    return [
        'purchase'        => 'Entrada (compra)',
        'sale'            => 'Venta',
        'sat_usage'       => 'Usado en SAT',
        'sat_return'      => 'Devuelto de SAT',
        'adjustment'      => 'Ajuste manual',
        'return_supplier' => 'Devolución a proveedor',
        'initial_stock'   => 'Stock inicial',
    ];
}

function av_articulo_format_qty( $n ) {
    $n = floatval( $n );
    // Sin decimales si es un número entero (la mayoría de artículos se
    // cuentan por unidad), con hasta 2 si no (metros, litros...).
    return ( floor( $n ) == $n ) ? number_format( $n, 0, ',', '.' ) : number_format( $n, 2, ',', '.' );
}

/**
 * Registra un movimiento de stock y actualiza el stock del artículo.
 *
 * @param int    $articulo_id
 * @param string $movement_type  Uno de av_articulo_movement_types().
 * @param float  $delta          Cambio CON SIGNO (positivo suma, negativo resta).
 * @param string $reason
 * @param string $ref_type       'sat', 'manual', etc.
 * @param int    $ref_id
 * @param string $notes
 * @return int|WP_Error  ID del movimiento creado, o WP_Error si no se pudo aplicar.
 */
function av_articulo_registrar_movimiento( $articulo_id, $movement_type, $delta, $reason = '', $ref_type = '', $ref_id = 0, $notes = '' ) {

    if ( get_post_type( $articulo_id ) !== 'cpt-articulos' ) {
        return new WP_Error( 'articulo-invalido', 'Artículo no válido.' );
    }

    if ( ! array_key_exists( $movement_type, av_articulo_movement_types() ) ) {
        return new WP_Error( 'tipo-invalido', 'Tipo de movimiento no válido.' );
    }

    $delta = floatval( $delta );
    if ( $delta === 0.0 ) {
        return new WP_Error( 'movimiento-vacio', 'La cantidad del movimiento no puede ser 0.' );
    }

    $stock_before   = floatval( get_post_meta( $articulo_id, 'cpt-articulo__stock', true ) );
    $stock_after    = $stock_before + $delta;
    $allow_negative = get_post_meta( $articulo_id, 'cpt-articulo__allow-negative-stock', true ) === '1';

    if ( $stock_after < 0 && ! $allow_negative ) {
        return new WP_Error(
            'stock-insuficiente',
            sprintf( 'Stock insuficiente. Stock disponible: %s', av_articulo_format_qty( $stock_before ) )
        );
    }

    $user = wp_get_current_user();

    $mov_id = wp_insert_post( [
        'post_type'   => 'cpt-articulo-mov',
        // Título interno solo para identificarlo en listados técnicos; no se
        // muestra tal cual al usuario (el historial usa av_articulo_movement_types()).
        'post_title'  => sprintf( '%s · artículo #%d · %+g', $movement_type, $articulo_id, $delta ),
        'post_status' => 'publish',
        'meta_input'  => [
            'article_id'     => $articulo_id,
            'movement_type'  => $movement_type,
            'quantity'       => $delta,
            'stock_before'   => $stock_before,
            'stock_after'    => $stock_after,
            'reason'         => $reason,
            'reference_type' => $ref_type,
            'reference_id'   => $ref_id,
            'user_id'        => $user ? $user->ID : 0,
            'notes'          => $notes,
        ],
    ] );

    if ( is_wp_error( $mov_id ) || ! $mov_id ) {
        return new WP_Error( 'movimiento-error', 'No se pudo registrar el movimiento de stock.' );
    }

    update_post_meta( $articulo_id, 'cpt-articulo__stock', $stock_after );

    $labels = av_articulo_movement_types();
    av_articulo_add_history(
        $articulo_id,
        sprintf( '%s (%+g)', $labels[ $movement_type ], $delta ),
        'stock',
        $reason
    );

    return $mov_id;
}

// Historial de movimientos de un artículo, más reciente primero.
function av_articulo_get_movimientos( $articulo_id, $limit = -1 ) {
    $posts = get_posts( [
        'post_type'      => 'cpt-articulo-mov',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [ [ 'key' => 'article_id', 'value' => $articulo_id, 'compare' => '=' ] ],
    ] );

    $out = [];
    foreach ( $posts as $p ) {
        $user_id = intval( get_post_meta( $p->ID, 'user_id', true ) );
        $user    = $user_id ? get_userdata( $user_id ) : null;
        $out[] = [
            'id'             => $p->ID,
            'date'           => get_the_date( 'd/m/Y H:i', $p ),
            'movement_type'  => get_post_meta( $p->ID, 'movement_type', true ),
            'quantity'       => floatval( get_post_meta( $p->ID, 'quantity', true ) ),
            'stock_before'   => floatval( get_post_meta( $p->ID, 'stock_before', true ) ),
            'stock_after'    => floatval( get_post_meta( $p->ID, 'stock_after', true ) ),
            'reason'         => get_post_meta( $p->ID, 'reason', true ),
            'reference_type' => get_post_meta( $p->ID, 'reference_type', true ),
            'reference_id'   => get_post_meta( $p->ID, 'reference_id', true ),
            'notes'          => get_post_meta( $p->ID, 'notes', true ),
            'user_name'      => $user ? ( $user->display_name ?: $user->user_login ) : '—',
        ];
    }
    return $out;
}

// ─── Estado de stock (para los avisos 🟢/🟠/🔴 del listado) ──────────────────
function av_articulo_stock_state( $stock, $stock_min ) {
    $stock     = floatval( $stock );
    $stock_min = floatval( $stock_min );
    if ( $stock <= 0 ) return 'out';
    if ( $stock <= $stock_min ) return 'low';
    return 'ok';
}

// ─── Precios / márgenes (calculados al vuelo, nunca almacenados) ────────────
// No hay en la interfaz un flujo de "registrar compra con su coste" (no
// solicitado): sin ese dato por movimiento, el coste medio real ponderado no
// se puede reconstruir del historial. Se deriva por tanto del precio de
// compra vigente del artículo, que es el único dato de coste que existe y
// que además nunca queda desactualizado (se lee en el momento).
function av_articulo_average_cost( $articulo_id ) {
    return floatval( get_post_meta( $articulo_id, 'cpt-articulo__purchase-price', true ) );
}

function av_articulo_margin( $articulo_id ) {
    $sale = floatval( get_post_meta( $articulo_id, 'cpt-articulo__sale-price', true ) );
    $cost = av_articulo_average_cost( $articulo_id );
    return [
        'amount'  => round( $sale - $cost, 2 ),
        'percent' => $sale > 0 ? round( ( ( $sale - $cost ) / $sale ) * 100, 2 ) : 0.0,
    ];
}

function av_articulo_sale_price_with_vat( $articulo_id ) {
    $sale = floatval( get_post_meta( $articulo_id, 'cpt-articulo__sale-price', true ) );
    $vat  = floatval( get_post_meta( $articulo_id, 'cpt-articulo__vat', true ) );
    return round( $sale * ( 1 + $vat / 100 ), 2 );
}

// ─── Ajuste manual de stock ──────────────────────────────────────────────────
// Formulario "Ajustar stock": el usuario indica la NUEVA cantidad total (no
// un delta), y aquí se calcula el delta real y se registra como 'adjustment'.
add_action( 'admin_post_av_ajustar_stock_articulo', 'av_ajustar_stock_articulo' );
function av_ajustar_stock_articulo() {

    if ( ! is_user_logged_in() || ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $articulo_id = intval( $_POST['id'] ?? 0 );
    // Nonce único (no por id): el modal de ajuste es un único elemento del
    // DOM que se rellena por JS con los datos de la fila pulsada, así que no
    // hay forma de imprimir server-side un nonce distinto por artículo sin un
    // viaje AJAX extra. La comprobación de que el id es un artículo real
    // (justo debajo) sigue evitando que se ajuste cualquier otra cosa.
    check_admin_referer( 'av_articulo_stock_nonce', 'nonce' );

    $redirect_to = add_query_arg( 'edit', $articulo_id, av_articulos_page_url() );

    if ( get_post_type( $articulo_id ) !== 'cpt-articulos' ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    $nueva_cantidad = isset( $_POST['nueva-cantidad'] ) ? trim( str_replace( ',', '.', (string) $_POST['nueva-cantidad'] ) ) : '';
    $motivo         = isset( $_POST['motivo'] ) ? sanitize_text_field( $_POST['motivo'] ) : '';

    if ( $nueva_cantidad === '' || ! is_numeric( $nueva_cantidad ) || $motivo === '' ) {
        wp_redirect( add_query_arg( 'error', 'ajuste-invalido', $redirect_to ) );
        exit;
    }

    $stock_actual = floatval( get_post_meta( $articulo_id, 'cpt-articulo__stock', true ) );
    $delta        = round( floatval( $nueva_cantidad ) - $stock_actual, 2 );

    if ( $delta === 0.0 ) {
        // Nada que ajustar: no se crea un movimiento vacío.
        wp_redirect( add_query_arg( 'stock-sin-cambios', '1', $redirect_to ) );
        exit;
    }

    $result = av_articulo_registrar_movimiento( $articulo_id, 'adjustment', $delta, $motivo, 'manual', get_current_user_id() );

    if ( is_wp_error( $result ) ) {
        wp_redirect( add_query_arg( [ 'error' => 'stock-insuficiente', 'msg' => rawurlencode( $result->get_error_message() ) ], $redirect_to ) );
        exit;
    }

    wp_redirect( add_query_arg( 'stock-ajustado', '1', $redirect_to ) );
    exit;
}
