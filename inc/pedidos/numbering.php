<?php
// ─── Numeración de pedidos ───────────────────────────────────────────────────
// Formato PED-{año}-{6 dígitos}, p.ej. PED-2026-000001. Un contador por año
// (como el número de factura), pero con el MISMO mecanismo atómico que ya
// usa el SKU de artículos (inc/articulos/sku.php: UPDATE ... = ... + 1 sobre
// una fila de wp_options) en vez del escaneo-del-máximo que usan SAT/factura
// — ese mecanismo sí tiene una ventana de carrera real bajo concurrencia; el
// de wp_options con UPDATE no, porque MySQL/InnoDB serializa esa fila.
// No se reutiliza literalmente av_articulo_next_sku_number() (es específica
// de prefijos de artículo) pero sí exactamente el mismo mecanismo.

function av_pedido_counter_option( $year ) {
    return 'av_pedido_seq_' . intval( $year );
}

function av_pedido_next_sequence( $year ) {
    global $wpdb;

    $option_name = av_pedido_counter_option( $year );

    // add_option() no sobrescribe si la fila ya existe: solo inicializa la
    // primera vez que se pide un número para ese año.
    if ( get_option( $option_name ) === false ) {
        add_option( $option_name, 0, '', 'no' );
    }

    $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s",
        $option_name
    ) );

    // Lectura directa (no get_option(), que podría devolver una copia en
    // caché de antes del UPDATE) + limpieza de la caché de objetos para que
    // cualquier get_option() posterior en la misma petición vea el valor real.
    wp_cache_delete( $option_name, 'options' );
    $next = $wpdb->get_var( $wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        $option_name
    ) );

    return intval( $next );
}

function av_pedido_number_exists( $numero, $exclude_id = 0 ) {
    $found = get_posts( [
        'post_type'      => 'cpt-pedidos',
        'posts_per_page' => 1,
        'post_status'    => 'any',
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => 'cpt-pedido__pedido-id', 'value' => $numero, 'compare' => '=' ] ],
    ] );
    return ! empty( $found ) && intval( $found[0] ) !== intval( $exclude_id );
}

// Genera el número definitivo de un pedido nuevo. Se llama una única vez, en
// av_guardar_pedido() al crear (nunca al editar: el número, como el SKU, es
// inmutable una vez asignado).
function av_pedido_generate_number() {

    $year = intval( wp_date( 'Y' ) );

    for ( $intentos = 0; $intentos < 20; $intentos++ ) {
        $seq    = av_pedido_next_sequence( $year );
        $numero = sprintf( 'PED-%d-%06d', $year, $seq );

        if ( ! av_pedido_number_exists( $numero ) ) {
            return $numero;
        }
        // Colisión (no debería ocurrir nunca con el contador recién
        // estrenado, pero se cubre por si el propio contador se manipulara
        // a mano en wp_options): se descarta y se pide el siguiente.
    }

    return new WP_Error( 'pedido-number-failed', 'No se ha podido generar un número de pedido único. Inténtalo de nuevo.' );
}
