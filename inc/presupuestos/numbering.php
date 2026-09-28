<?php
// ─── Numeración de presupuestos ──────────────────────────────────────────────
// Formato PRE-{año}-{6 dígitos}, p.ej. PRE-2026-000001. Mismo mecanismo
// atómico que inc/pedidos/numbering.php (UPDATE ... = ... + 1 sobre una fila
// de wp_options, que MySQL/InnoDB serializa) — nunca comparte contador con
// Facturas (av_factura_seq_*) ni con Pedidos (av_pedido_seq_*): un
// presupuesto no es un documento fiscal, así que necesita su propia serie.

function av_presupuesto_counter_option( $year ) {
    return 'av_presupuesto_seq_' . intval( $year );
}

function av_presupuesto_next_sequence( $year ) {
    global $wpdb;

    $option_name = av_presupuesto_counter_option( $year );

    if ( get_option( $option_name ) === false ) {
        add_option( $option_name, 0, '', 'no' );
    }

    $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s",
        $option_name
    ) );

    wp_cache_delete( $option_name, 'options' );
    $next = $wpdb->get_var( $wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        $option_name
    ) );

    return intval( $next );
}

function av_presupuesto_number_exists( $numero, $exclude_id = 0 ) {
    $found = get_posts( [
        'post_type'      => 'cpt-presupuestos',
        'posts_per_page' => 1,
        'post_status'    => 'any',
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => 'cpt-presupuesto__presupuesto-id', 'value' => $numero, 'compare' => '=' ] ],
    ] );
    return ! empty( $found ) && intval( $found[0] ) !== intval( $exclude_id );
}

// Genera el número definitivo de un presupuesto nuevo. Se llama una única
// vez, al crear (nunca al editar: el número es inmutable una vez asignado).
function av_presupuesto_generate_number() {

    $year = intval( wp_date( 'Y' ) );

    for ( $intentos = 0; $intentos < 20; $intentos++ ) {
        $seq    = av_presupuesto_next_sequence( $year );
        $numero = sprintf( 'PRE-%d-%06d', $year, $seq );

        if ( ! av_presupuesto_number_exists( $numero ) ) {
            return $numero;
        }
    }

    return new WP_Error( 'presupuesto-number-failed', 'No se ha podido generar un número de presupuesto único. Inténtalo de nuevo.' );
}
