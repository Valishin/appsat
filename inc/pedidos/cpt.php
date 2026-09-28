<?php
// ─── CPT Pedidos (encargos a cliente sin stock, "quiero una tinta HP 305 XL") ─
// Mismo patrón que cpt-articulos: la app tiene su propia interfaz (listado +
// formulario propios, todavía no implementados en esta fase), así que aquí no
// se expone el editor nativo de WordPress para crear pedidos nuevos.

add_action( 'init', 'av_register_cpt_pedidos' );
function av_register_cpt_pedidos() {
    register_post_type( 'cpt-pedidos', [
        'labels' => [
            'name'               => 'Pedidos',
            'singular_name'      => 'Pedido',
            'menu_name'          => 'Pedidos',
            'all_items'          => 'Todos los pedidos',
            'edit_item'          => 'Editar pedido',
            'view_item'          => 'Ver pedido',
            'search_items'       => 'Buscar pedidos',
            'not_found'          => 'No se encontraron pedidos',
            'not_found_in_trash' => 'No hay pedidos en la papelera',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'menu_position'     => 29,
        'menu_icon'         => 'dashicons-clipboard',
        'supports'          => [ 'title' ],
        'capabilities'      => [
            // El alta se hace siempre desde la app (con sus validaciones),
            // nunca desde el "Añadir nuevo" de wp-admin.
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'      => true,
    ] );
}

// Misma forma que av_articulos_page_url()/av_servicios_page_url(): busca la
// página con esta plantilla asignada. Un único template sirve listado, alta,
// edición y detalle (igual que Artículos): sin parámetros = listado; ?new=1 =
// crear; ?id=X = editar; ?view=X = detalle de solo lectura.
function av_pedidos_page_url() {
    $page = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-pedidos.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    return ! empty( $page ) ? get_permalink( $page[0] ) : home_url( '/' );
}

// ─── Estados del pedido ──────────────────────────────────────────────────────
// Misma convención que el SAT (av_sat_status_label): un select con lista fija
// en PHP, nunca una taxonomía ni un CPT de estados.
// Fase 6: se eliminó "ready" (Listo para recoger) — un pedido pasa
// directamente de received a delivered (ver av_pedido_migrate_schema_v2()
// más abajo para la migración de los pedidos reales que estuvieran en ready).
function av_pedido_status_choices() {
    return [
        'pending'   => 'Pendiente',
        'ordered'   => 'Pedido',
        'received'  => 'Recibido',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado',
    ];
}

function av_pedido_status_label( $status ) {
    $choices = av_pedido_status_choices();
    return $choices[ $status ] ?? ucfirst( str_replace( '-', ' ', $status ) );
}

// ─── Datos de pago (Fase 6, mismo patrón que el SAT) ────────────────────────
// El SAT no centraliza estas opciones en ninguna función (van sueltas como
// <option> en components/c-sat-form.php y como array literal en
// functions.php): se replican aquí los mismos dos valores/etiquetas en vez de
// tocar el SAT para "centralizarlas", que no es lo que se ha pedido.
function av_pedido_payment_choices() {
    return [
        'tarjeta'  => 'Tarjeta',
        'efectivo' => 'Efectivo',
    ];
}

// "Pagado" no es un campo propio ni en el SAT ni aquí: se deriva de que haya
// tipo de pago informado — exactamente el mismo criterio que ya usa
// components/c-list-facturas-list.php ($fac_pagado = !empty($fac_forma_pago)).
function av_pedido_is_paid( $pedido_id ) {
    return get_post_meta( $pedido_id, 'cpt-pedido__price-description', true ) !== '';
}

// ─── Helpers de presentación para el listado (Fase 3) ───────────────────────
// El pedido solo guarda el ID del cliente (cpt-pedido__client-id, texto
// plano — ver inc/pedidos/crud.php): el nombre se resuelve aquí, nunca se
// duplica en el propio pedido.
function av_pedido_resolve_client_name( $client_id ) {
    $client_id = intval( $client_id );
    if ( ! $client_id || get_post_type( $client_id ) !== 'cpt-clients' ) return '—';
    $name = get_field( 'cpt-client__name', $client_id );
    return $name ?: '—';
}

// "1 línea" -> el propio nombre; "varias" -> "N productos".
function av_pedido_product_summary( array $lines ) {
    $count = count( $lines );
    if ( $count === 0 ) return '—';
    if ( $count === 1 ) return $lines[0]['name_snapshot'] ?? '—';
    return $count . ' productos';
}

// Proveedor único si todas las líneas comparten el mismo; "Varios" si difieren;
// "—" si ninguna línea tiene proveedor indicado.
function av_pedido_supplier_summary( array $lines ) {
    $suppliers = array_values( array_unique( array_filter( array_map(
        fn( $l ) => trim( $l['supplier'] ?? '' ),
        $lines
    ) ) ) );
    if ( empty( $suppliers ) ) return '—';
    if ( count( $suppliers ) === 1 ) return $suppliers[0];
    return 'Varios';
}

// ─── Aviso al cliente (Fase 5) ───────────────────────────────────────────────
// Mensaje de "pedido listo para recoger". Deliberadamente NO incluye nada que
// el cliente no deba ver: ni notas internas (del pedido ni de ninguna línea),
// ni precio de compra, ni margen, ni datos de proveedor (nombre/referencia/
// nº de pedido al proveedor) — solo nombre, resumen de producto(s) (el mismo
// texto que ya se ve en el listado) y el aviso de recogida.
function av_pedido_build_whatsapp_message( $pedido_id ) {
    $client_id = intval( get_post_meta( $pedido_id, 'cpt-pedido__client-id', true ) );
    $lines     = av_pedido_get_lines( $pedido_id );

    $client_name = av_pedido_resolve_client_name( $client_id );
    $summary     = av_pedido_product_summary( $lines );
    $pedido_num  = get_post_meta( $pedido_id, 'cpt-pedido__pedido-id', true );

    return sprintf(
        "Hola %s, tu pedido %s (%s) ya está disponible para recoger. ¡Te esperamos!",
        $client_name,
        $pedido_num,
        $summary
    );
}

// ─── Migración de esquema (Fase 6) ───────────────────────────────────────────
// Mismo patrón que av_migrate_config_factura_template() (functions.php): hook
// en 'init' guardado por una opción, para que se ejecute una única vez sobre
// los datos reales ya existentes.
//
// Qué migra:
//  1. cpt-pedido__status === 'ready' -> 'received' (el estado intermedio
//     desaparece; recibido y listo para recoger pasan a ser lo mismo).
//  2. Cada línea de cpt-pedido__lines: se retira supplier_status del array
//     (ya no se usa como guarda de nada — el nuevo criterio de "línea
//     recibida" es received_date no vacío). Si una línea tenía
//     supplier_status === 'received' pero por lo que sea se quedó sin
//     received_date, se rellena con la fecha de creación del pedido (mejor
//     una fecha aproximada real que perder la marca de "ya se recibió").
//
// Qué NO se toca: cpt-pedido__estimated-date / received-date / ready-date a
// nivel de pedido general se dejan tal cual en la base de datos (huérfanos,
// ya no los lee ningún campo ACF ni pantalla) — no se borran físicamente,
// siguiendo el mismo criterio de "nunca borrar histórico" del resto del
// proyecto. Las fechas POR LÍNEA (estimated_date/received_date dentro de
// cada línea) no se tocan en absoluto: siguen operativas.
add_action( 'init', 'av_pedido_migrate_schema_v2' );
function av_pedido_migrate_schema_v2() {

    if ( get_option( 'av_pedido_schema_v2_migrated' ) ) return;

    $pedidos = get_posts( [
        'post_type'      => 'cpt-pedidos',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );

    foreach ( $pedidos as $pedido_id ) {

        $status = get_post_meta( $pedido_id, 'cpt-pedido__status', true );
        if ( $status === 'ready' ) {
            update_post_meta( $pedido_id, 'cpt-pedido__status', 'received' );
        }

        $lines   = av_pedido_get_lines( $pedido_id );
        $changed = false;

        foreach ( $lines as &$line ) {
            if ( ! array_key_exists( 'supplier_status', $line ) ) continue;

            if ( $line['supplier_status'] === 'received' && empty( $line['received_date'] ) ) {
                $entry_date = get_post_meta( $pedido_id, 'cpt-pedido__entry-date', true );
                $fallback   = $entry_date ? DateTime::createFromFormat( 'd/m/Y H:i', $entry_date ) : false;
                $line['received_date'] = $fallback ? $fallback->format( 'Y-m-d' ) : wp_date( 'Y-m-d' );
            }

            unset( $line['supplier_status'] );
            $changed = true;
        }
        unset( $line );

        if ( $changed ) {
            // wp_slash: update_post_meta() hace su propio wp_unslash()
            // interno (ver el mismo comentario en av_sat_add_history(),
            // functions.php) — sin esto se comería el escape del JSON en
            // cuanto una línea llevara una comilla o barra invertida.
            update_post_meta( $pedido_id, 'cpt-pedido__lines', wp_slash( wp_json_encode( $lines, JSON_UNESCAPED_UNICODE ) ) );
        }
    }

    update_option( 'av_pedido_schema_v2_migrated', 1 );
}
