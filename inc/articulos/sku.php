<?php
// ─── Generación automática de SKU ────────────────────────────────────────────
// El SKU nunca lo escribe el usuario: se genera en el backend a partir del
// prefijo de la categoría elegida + un contador propio de ese prefijo.
// Formato: PREFIJO-NNNN (p.ej. LCD-0001). Una vez asignado a un artículo, el
// SKU es inmutable: cambiar la categoría, el nombre, el proveedor o cualquier
// otro dato NUNCA vuelve a tocar cpt-articulo__sku.

// Prefijos con los que arranca el catálogo (solo se usan para el backfill de
// las categorías sembradas en cpt.php — el origen de verdad en tiempo de
// ejecución es SIEMPRE el term meta "article_category_prefix" de cada
// categoría, no este mapa).
function av_articulo_categoria_prefix_seed_map() {
    return [
        'pantallas'      => 'LCD',
        'baterias'       => 'BAT',
        'componentes'    => 'CMP',
        'accesorios'     => 'ACC',
        'consumibles'    => 'CON',
        'almacenamiento' => 'STO',
        'cables'         => 'CAB',
        'otros'          => 'OTH',
    ];
}

// Rellena "article_category_prefix" en las categorías que ya existían antes
// de este cambio (sembradas en av_seed_articulo_categorias(), que no conocía
// todavía el concepto de prefijo). Solo se ejecuta una vez; si el
// administrador cambia después un prefijo a mano, no se vuelve a tocar.
add_action( 'init', 'av_backfill_articulo_categoria_prefixes', 23 );
function av_backfill_articulo_categoria_prefixes() {

    if ( get_option( 'av_articulo_categoria_prefixes_backfilled' ) ) return;

    foreach ( av_articulo_categoria_prefix_seed_map() as $slug => $prefix ) {
        $term = get_term_by( 'slug', $slug, 'articulo_categoria' );
        if ( ! $term ) continue;
        if ( get_term_meta( $term->term_id, 'article_category_prefix', true ) === '' ) {
            update_term_meta( $term->term_id, 'article_category_prefix', $prefix );
        }
    }

    update_option( 'av_articulo_categoria_prefixes_backfilled', '1', false );
}

// Prefijo de una categoría (por term_id). Preferentemente viene del term meta
// "article_category_prefix" (lo que permite añadir categorías nuevas —p.ej.
// "Herramientas" → TOO— sin tocar ni una línea de este generador). Si una
// categoría no tiene prefijo configurado (se creó a mano en wp-admin, o se
// olvidó al crearla), se deriva uno razonable de su slug para que el sistema
// nunca se quede bloqueado sin poder generar un SKU.
function av_articulo_categoria_get_prefix( $term_id ) {

    $term_id = intval( $term_id );
    if ( ! $term_id ) return 'OTH';

    $prefix = get_term_meta( $term_id, 'article_category_prefix', true );
    if ( is_string( $prefix ) && $prefix !== '' ) {
        return strtoupper( $prefix );
    }

    $term = get_term( $term_id, 'articulo_categoria' );
    if ( ! $term || is_wp_error( $term ) ) return 'OTH';

    // Deriva del slug: primeras letras (sin guiones/números), 3 caracteres.
    $derived = strtoupper( substr( preg_replace( '/[^a-zA-Z]/', '', $term->slug ), 0, 3 ) );

    return $derived !== '' ? $derived : 'OTH';
}

// ─── Componentes electrónicos: prefijo por tipo de componente ───────────────
// Para esta categoría en concreto, el prefijo del SKU NO sale de la categoría
// (sería "ELE" para todos, indistinguible) sino del tipo de componente, tal
// como se pidió explícitamente. Se integra en el MISMO generador de SKU
// (av_articulo_generate_sku más abajo) en vez de crear un flujo aparte: sigue
// usando el mismo contador atómico, el mismo formato y la misma comprobación
// de unicidad que el resto de categorías.

// Slug estable de la categoría "Componentes electrónicos" (no se referencia
// por term_id: un ID puede no coincidir entre entornos si la categoría se
// vuelve a crear a mano en otra instalación).
function av_articulo_electronics_category_slug() {
    return 'componentes-electronicos';
}

function av_articulo_is_electronics_category( $category_term_id ) {
    $category_term_id = intval( $category_term_id );
    if ( ! $category_term_id ) return false;
    $term = get_term( $category_term_id, 'articulo_categoria' );
    return $term && ! is_wp_error( $term ) && $term->slug === av_articulo_electronics_category_slug();
}

function av_articulo_component_type_prefixes() {
    return [
        'ic'            => 'IC',
        'mosfet'        => 'MOS',
        'diodo'         => 'DIO',
        'transistor'    => 'TRN',
        'resistencia'   => 'RES',
        'condensador'   => 'CAP',
        'bobina'        => 'IND',
        'fusible'       => 'FUS',
        'cristal'       => 'OSC',
        'optoacoplador' => 'OPT',
        'conector-smd'  => 'CON',
        'otro'          => 'OTR',
    ];
}

// Resuelve qué prefijo usar para un artículo, combinando categoría y (si
// aplica) tipo de componente. Único punto de entrada para "qué prefijo le
// toca a este artículo" — así una futura categoría con lógica propia de
// prefijo (no solo "Componentes electrónicos") se añadiría aquí mismo, sin
// tocar av_articulo_generate_sku().
function av_articulo_resolve_sku_prefix( $category_term_id, $component_type = '' ) {

    if ( av_articulo_is_electronics_category( $category_term_id ) ) {
        $prefixes = av_articulo_component_type_prefixes();
        if ( $component_type && isset( $prefixes[ $component_type ] ) ) {
            return $prefixes[ $component_type ];
        }
        // Categoría electrónica pero sin tipo válido todavía (p.ej. vista
        // previa antes de elegir tipo): cae al prefijo de la propia
        // categoría (ELE) en vez de fallar.
    }

    return av_articulo_categoria_get_prefix( $category_term_id );
}

// Nombre de la opción-contador de wp_options para un prefijo dado. Un
// contador independiente por prefijo (no global): así "stock bajo" de
// Baterías no consume números de Pantallas.
function av_articulo_sku_counter_option( $prefix ) {
    return 'av_articulo_sku_seq_' . strtoupper( $prefix );
}

// Arranque del contador para un prefijo que todavía no tiene fila propia:
// en vez de empezar siempre en 0, mira los SKU YA EXISTENTES con ese prefijo
// (de antes de este cambio, cuando el SKU se escribía a mano) y arranca por
// encima del más alto. Así nunca se regenera un SKU manual ya usado.
function av_articulo_bootstrap_sku_counter( $prefix ) {
    global $wpdb;

    $like = $wpdb->esc_like( strtoupper( $prefix ) . '-' ) . '%';
    $skus = $wpdb->get_col( $wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'cpt-articulo__sku' AND meta_value LIKE %s",
        $like
    ) );

    $max = 0;
    $pattern = '/^' . preg_quote( strtoupper( $prefix ), '/' ) . '-(\d{4})$/';
    foreach ( $skus as $sku ) {
        if ( preg_match( $pattern, $sku, $m ) ) {
            $max = max( $max, intval( $m[1] ) );
        }
    }

    return $max;
}

// Siguiente número SIN reservarlo (solo lectura): usado por la vista previa
// del formulario al elegir categoría. Nunca debe usarse para asignar un SKU
// real — dos usuarios previsualizando a la vez verían el mismo número, y eso
// es aceptable para una vista previa, pero NO para el valor final guardado
// (para eso está av_articulo_next_sku_number(), más abajo).
function av_articulo_peek_next_sku_number( $prefix ) {
    $option_name = av_articulo_sku_counter_option( $prefix );
    $current = get_option( $option_name );
    if ( $current === false ) {
        $current = av_articulo_bootstrap_sku_counter( $prefix );
    }
    return intval( $current ) + 1;
}

// Siguiente número REAL, reservado de forma atómica: protege contra dos
// artículos creados a la vez con el mismo prefijo (usuario A y B guardando en
// el mismo instante). Se apoya en un UPDATE ... SET valor = valor + 1 sobre
// una única fila de wp_options: MySQL/InnoDB serializa ese UPDATE a nivel de
// fila, así que si dos peticiones llegan a la vez, una espera a la otra y
// cada una obtiene un número distinto — nunca el mismo. No se usa una tabla
// propia porque todo el theme resuelve este tipo de contadores con wp_options
// (mismo patrón que av_device_category_next_order(), solo que ahí no hace
// falta atomicidad porque el orden de categorías no se crea en paralelo).
function av_articulo_next_sku_number( $prefix ) {
    global $wpdb;

    $option_name = av_articulo_sku_counter_option( $prefix );

    // add_option() no sobrescribe si la fila ya existe (es un INSERT normal
    // que WordPress protege comprobando antes); la única situación en la que
    // esto NO bastaría por sí solo es la primerísima vez que se usa un
    // prefijo, con dos creaciones simultáneas de ese prefijo justo en ese
    // instante — un caso extremo que el chequeo de unicidad final (ver
    // av_articulo_generate_sku()) sigue cubriendo de todas formas.
    if ( get_option( $option_name ) === false ) {
        add_option( $option_name, av_articulo_bootstrap_sku_counter( $prefix ), '', 'no' );
    }

    $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s",
        $option_name
    ) );

    // Se lee con una consulta directa (no get_option(), que podría devolver
    // una copia en caché de antes del UPDATE) y se limpia la caché de objetos
    // para que cualquier get_option() posterior en la misma petición vea el
    // valor real.
    wp_cache_delete( $option_name, 'options' );
    $next = $wpdb->get_var( $wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        $option_name
    ) );

    return intval( $next );
}

function av_articulo_sku_exists( $sku, $exclude_id = 0 ) {
    $found = get_posts( [
        'post_type'      => 'cpt-articulos',
        'posts_per_page' => 1,
        'post_status'    => 'any',
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => 'cpt-articulo__sku', 'value' => $sku, 'compare' => '=' ] ],
    ] );
    return ! empty( $found ) && intval( $found[0] ) !== intval( $exclude_id );
}

// Genera un SKU único para una categoría (y, si es "Componentes
// electrónicos", también según el tipo de componente — ver
// av_articulo_resolve_sku_prefix()). Combina el contador atómico (que ya
// evita por sí solo la inmensa mayoría de colisiones) con una comprobación de
// unicidad final y reintento (por si un SKU manual antiguo, de antes de este
// cambio, ocupase justo ese número): así se cumple "backend debe volver a
// validar que el SKU sea único; si hay conflicto, generar otro número".
function av_articulo_generate_sku( $category_term_id, $component_type = '' ) {

    $prefix = av_articulo_resolve_sku_prefix( $category_term_id, $component_type );
    if ( ! preg_match( '/^[A-Z]{2,6}$/', $prefix ) ) {
        $prefix = 'OTH';
    }

    for ( $intentos = 0; $intentos < 20; $intentos++ ) {
        $numero = av_articulo_next_sku_number( $prefix );
        $sku    = sprintf( '%s-%04d', $prefix, $numero );

        if ( ! preg_match( '/^[A-Z]{2,6}-[0-9]{4}$/', $sku ) ) continue; // defensivo, no debería pasar nunca

        if ( ! av_articulo_sku_exists( $sku ) ) {
            return $sku;
        }
        // Colisión con un SKU preexistente (p.ej. escrito a mano antes de
        // este cambio): se descarta este número y se pide el siguiente.
    }

    return new WP_Error( 'sku-generation-failed', 'No se ha podido generar un SKU único. Inténtalo de nuevo.' );
}

// Vista previa del SKU en el formulario de "Nuevo artículo", al elegir
// categoría. NO reserva número (ver av_articulo_peek_next_sku_number): el
// número definitivo se fija al guardar, en el backend, ignorando cualquier
// valor que llegue del navegador.
add_action( 'wp_ajax_av_ajax_preview_articulo_sku', 'av_ajax_preview_articulo_sku' );
function av_ajax_preview_articulo_sku() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_send_json_error( 'Acceso denegado', 403 );
    }

    check_ajax_referer( 'av_articulo_nonce', 'nonce' );

    $category_id    = intval( $_POST['category'] ?? 0 );
    $component_type = isset( $_POST['component_type'] ) ? sanitize_text_field( $_POST['component_type'] ) : '';
    if ( ! $category_id || get_term( $category_id, 'articulo_categoria' ) === null ) {
        wp_send_json_error( 'Categoría no válida.', 400 );
    }

    $prefix = av_articulo_resolve_sku_prefix( $category_id, $component_type );
    $numero = av_articulo_peek_next_sku_number( $prefix );

    wp_send_json_success( [ 'sku' => sprintf( '%s-%04d', $prefix, $numero ) ] );
}
