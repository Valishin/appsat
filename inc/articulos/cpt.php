<?php
// ─── CPT Artículos (piezas, consumibles, accesorios, productos) ─────────────
// Catálogo de inventario. Mismo patrón que cpt-servicios/cpt-device: la app
// tiene su propia interfaz (listado + formulario propios), así que aquí no
// se expone el editor nativo de WordPress.

add_action( 'init', 'av_register_cpt_articulos' );
function av_register_cpt_articulos() {
    register_post_type( 'cpt-articulos', [
        'labels' => [
            'name'               => 'Artículos',
            'singular_name'      => 'Artículo',
            'menu_name'          => 'Artículos',
            'all_items'          => 'Todos los artículos',
            'edit_item'          => 'Editar artículo',
            'view_item'          => 'Ver artículo',
            'search_items'       => 'Buscar artículos',
            'not_found'          => 'No se encontraron artículos',
            'not_found_in_trash' => 'No hay artículos en la papelera',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'menu_position'     => 28,
        'menu_icon'         => 'dashicons-archive',
        'supports'          => [ 'title' ],
        'taxonomies'        => [ 'articulo_categoria' ],
        'capabilities'      => [
            // El alta se hace siempre desde la app (con sus validaciones),
            // nunca desde el "Añadir nuevo" de wp-admin.
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'      => true,
    ] );
}

// ─── CPT Movimientos de stock ────────────────────────────────────────────────
// Un post = un movimiento de stock de un artículo. Nunca se edita/borra a
// mano: solo lo crea av_articulo_registrar_movimiento() (inc/articulos/stock.php).
add_action( 'init', 'av_register_cpt_articulo_movimiento' );
function av_register_cpt_articulo_movimiento() {
    register_post_type( 'cpt-articulo-mov', [
        'labels' => [
            'name'          => 'Movimientos de stock',
            'singular_name' => 'Movimiento de stock',
            'menu_name'     => 'Movimientos de stock',
        ],
        'public'            => false,
        'show_ui'           => false,
        'show_in_menu'      => false,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'supports'          => [ 'title' ],
        'capabilities'      => [
            'create_posts' => 'do_not_allow',
            'edit_post'    => 'do_not_allow',
            'delete_post'  => 'do_not_allow',
        ],
        'map_meta_cap'      => true,
    ] );
}

// ─── Taxonomía de categorías de artículo ─────────────────────────────────────
// Mismo patrón que "cpt-device-category": CRUD + reordenar vía AJAX desde su
// propia pantalla (components/c-articulo-categories.php).
add_action( 'init', 'av_register_articulo_categoria_taxonomy' );
function av_register_articulo_categoria_taxonomy() {
    register_taxonomy( 'articulo_categoria', [ 'cpt-articulos' ], [
        'labels' => [
            'name'          => 'Categorías de artículo',
            'singular_name' => 'Categoría de artículo',
            'menu_name'     => 'Categorías de artículo',
            'search_items'  => 'Buscar categorías',
            'all_items'     => 'Todas las categorías',
            'edit_item'     => 'Editar categoría',
            'update_item'   => 'Actualizar categoría',
            'add_new_item'  => 'Añadir categoría',
            'new_item_name' => 'Nombre de la categoría nueva',
        ],
        'hierarchical'      => false,
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'show_admin_column' => true,
    ] );
}

// Semillas iniciales (slug => nombre), igual que las categorías de dispositivo.
function av_articulo_categoria_seed_names() {
    return [
        'pantallas'      => 'Pantallas',
        'baterias'       => 'Baterías',
        'componentes'    => 'Componentes',
        'accesorios'     => 'Accesorios',
        'consumibles'    => 'Consumibles',
        'almacenamiento' => 'Almacenamiento',
        'cables'         => 'Cables',
        'otros'          => 'Otros',
    ];
}

// Se crean una sola vez; si el administrador borra alguna después, no vuelve
// a aparecer sola.
add_action( 'init', 'av_seed_articulo_categorias', 20 );
function av_seed_articulo_categorias() {

    if ( get_option( 'av_articulo_categorias_seeded' ) ) return;

    $order = 0;
    foreach ( av_articulo_categoria_seed_names() as $slug => $nombre ) {
        $order++;
        if ( ! term_exists( $slug, 'articulo_categoria' ) ) {
            $result = wp_insert_term( $nombre, 'articulo_categoria', [ 'slug' => $slug ] );
            if ( ! is_wp_error( $result ) ) {
                update_term_meta( $result['term_id'], 'av_articulo_order', $order );
            }
        }
    }

    update_option( 'av_articulo_categorias_seeded', '1', false );
}

// Orden manual de las categorías (meta "av_articulo_order" — con nombre propio
// y distinto del "av_order" de dispositivos: wp_termmeta es una tabla global
// sin distinguir taxonomía, así que reutilizar la misma clave contaminaría el
// cálculo del "siguiente hueco" de un catálogo con el del otro).
function av_get_articulo_categorias() {
    $categorias = get_terms( [
        'taxonomy'   => 'articulo_categoria',
        'hide_empty' => false,
        'meta_key'   => 'av_articulo_order',
        'orderby'    => 'meta_value_num',
        'order'      => 'ASC',
    ] );
    return is_wp_error( $categorias ) ? [] : $categorias;
}

function av_articulo_categoria_next_order() {
    global $wpdb;
    $max = $wpdb->get_var( "SELECT MAX(CAST(meta_value AS SIGNED)) FROM {$wpdb->termmeta} WHERE meta_key = 'av_articulo_order'" );
    return $max !== null ? intval( $max ) + 1 : 1;
}

function av_articulos_page_url() {
    $page = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-articulos.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    return ! empty( $page ) ? get_permalink( $page[0] ) : home_url( '/' );
}

