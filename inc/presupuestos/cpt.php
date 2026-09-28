<?php
// ─── CPT Presupuestos ────────────────────────────────────────────────────────
// Mismo patrón que cpt-pedidos/cpt-facturas: la app tiene su propia interfaz
// (listado + formulario propios), así que aquí no se expone el editor nativo
// de WordPress para crear presupuestos nuevos.

add_action( 'init', 'av_register_cpt_presupuestos' );
function av_register_cpt_presupuestos() {
    register_post_type( 'cpt-presupuestos', [
        'labels' => [
            'name'               => 'Presupuestos',
            'singular_name'      => 'Presupuesto',
            'menu_name'          => 'Presupuestos',
            'all_items'          => 'Todos los presupuestos',
            'edit_item'          => 'Editar presupuesto',
            'view_item'          => 'Ver presupuesto',
            'search_items'       => 'Buscar presupuestos',
            'not_found'          => 'No se encontraron presupuestos',
            'not_found_in_trash' => 'No hay presupuestos en la papelera',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'menu_position'     => 26,
        'menu_icon'         => 'dashicons-media-spreadsheet',
        'supports'          => [ 'title' ],
        'capabilities'      => [
            // El alta se hace siempre desde la app (con sus validaciones),
            // nunca desde el "Añadir nuevo" de wp-admin.
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'      => true,
    ] );
}

// Misma forma que av_pedidos_page_url(): busca la página con esta plantilla
// asignada. Un único template sirve listado, alta y edición: sin parámetros =
// listado; ?new=1 = crear; ?id=X = editar.
function av_presupuestos_page_url() {
    $page = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-presupuestos.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    return ! empty( $page ) ? get_permalink( $page[0] ) : home_url( '/' );
}

// El presupuesto no duplica el nombre del cliente (solo guarda su ID, igual
// que cpt-pedido__client-id): se resuelve aquí, nunca se almacena aparte.
function av_presupuesto_resolve_client_name( $client_id ) {
    $client_id = intval( $client_id );
    if ( ! $client_id || get_post_type( $client_id ) !== 'cpt-clients' ) return '—';
    $name = get_field( 'cpt-client__name', $client_id );
    return $name ?: '—';
}

// "1 línea" -> el propio nombre; "varias" -> "N artículos".
function av_presupuesto_product_summary( array $lines ) {
    $count = count( $lines );
    if ( $count === 0 ) return '—';
    if ( $count === 1 ) return $lines[0]['name_snapshot'] ?? '—';
    return $count . ' artículos';
}
