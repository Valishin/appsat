<?php
// ─── Presupuestos: alta / edición ────────────────────────────────────────────
// Mismo patrón que inc/pedidos/crud.php: admin_post con nonce, líneas como
// JSON a mano en postmeta (NO un ACF Repeater), número asignado por el
// backend con un contador atómico propio (inc/presupuestos/numbering.php).

// ─── Líneas del presupuesto (cpt-presupuesto__lines) ────────────────────────
// Forma de cada línea ya saneada:
//   { article_id, name_snapshot, sku_snapshot, description, quantity, sale_price, vat }
// sale_price se guarda SIEMPRE sin IVA (misma convención que
// cpt-articulo__sale-price/cpt-pedido__lines): la tabla deja escribir el
// precio con IVA o sin IVA indistintamente, pero lo que se guarda es siempre
// la base, con vat aparte — ver av_presupuesto_lines_widget() en src/js/app.js.
//
// La tabla es una única fila editable con las mismas columnas tanto si viene
// de Artículos, de Servicios (cpt-servicios, que no tiene SKU/IVA propios:
// se congelan sku_snapshot vacío y vat=21 por defecto al añadir la línea) o
// se escribe a mano de cero ("+ Añadir fila"): article_id solo se rellena
// cuando la línea viene realmente de un cpt-articulos real; en cualquier
// otro caso queda null y sku_snapshot/name_snapshot/description son texto
// libre editado directamente en la celda.
function av_presupuesto_sanitize_lines( $raw ) {

    if ( empty( $raw ) ) return '';

    $items = is_string( $raw ) ? json_decode( wp_unslash( $raw ), true ) : $raw;
    if ( ! is_array( $items ) ) return '';

    $vat_choices = array_map( 'intval', array_keys( av_articulo_vat_choices() ) );

    $clean = [];
    foreach ( $items as $it ) {
        if ( ! is_array( $it ) ) continue;

        $article_id = ! empty( $it['article_id'] ) ? intval( $it['article_id'] ) : null;
        if ( $article_id && get_post_type( $article_id ) !== 'cpt-articulos' ) {
            $article_id = null;
        }

        $name_snapshot = sanitize_text_field( $it['name_snapshot'] ?? '' );
        $sku_snapshot  = sanitize_text_field( $it['sku_snapshot'] ?? '' );

        // Snapshot: si la línea referencia un artículo real y no trae ya
        // nombre/SKU, se completa desde el artículo EN ESTE INSTANTE. A
        // partir de aquí queda escrito en la línea: si el artículo cambia de
        // nombre/SKU/precio más adelante, esta línea no se entera.
        if ( $article_id ) {
            if ( $name_snapshot === '' ) $name_snapshot = get_the_title( $article_id );
            if ( $sku_snapshot === '' )  $sku_snapshot  = get_post_meta( $article_id, 'cpt-articulo__sku', true );
        }

        // Sin artículo Y sin nombre escrito a mano, la línea no identifica nada.
        if ( $article_id === null && $name_snapshot === '' ) continue;

        $quantity = isset( $it['quantity'] ) ? floatval( $it['quantity'] ) : 1;
        if ( $quantity <= 0 ) $quantity = 1;

        $sale_price = isset( $it['sale_price'] ) ? max( 0, round( floatval( $it['sale_price'] ), 2 ) ) : 0;

        // Si viene de un artículo real y no se ha indicado precio explícito
        // en la línea, se congela el precio ACTUAL del artículo como
        // snapshot inicial.
        if ( $article_id && ! isset( $it['sale_price'] ) ) {
            $sale_price = floatval( get_post_meta( $article_id, 'cpt-articulo__sale-price', true ) );
        }

        $vat = isset( $it['vat'] ) ? intval( $it['vat'] ) : 21;
        if ( ! in_array( $vat, $vat_choices, true ) ) $vat = 21;

        $clean[] = [
            'article_id'    => $article_id,
            'name_snapshot' => $name_snapshot,
            'sku_snapshot'  => $sku_snapshot,
            'description'   => sanitize_text_field( $it['description'] ?? '' ),
            'quantity'      => $quantity,
            'sale_price'    => $sale_price,
            'vat'           => $vat,
        ];
    }

    return ! empty( $clean ) ? wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) : '';
}

function av_presupuesto_get_lines( $presupuesto_id ) {
    $raw   = get_post_meta( $presupuesto_id, 'cpt-presupuesto__lines', true );
    $items = $raw ? json_decode( $raw, true ) : [];
    return is_array( $items ) ? $items : [];
}

function av_presupuesto_line_subtotal( $sale_price, $quantity ) {
    return round( floatval( $sale_price ) * floatval( $quantity ), 2 );
}

// Suma de subtotales (sin IVA) de todas las líneas (al vuelo, no se almacena).
function av_presupuesto_total( $presupuesto_id ) {
    $total = 0;
    foreach ( av_presupuesto_get_lines( $presupuesto_id ) as $line ) {
        $total += av_presupuesto_line_subtotal( $line['sale_price'] ?? 0, $line['quantity'] ?? 1 );
    }
    return round( $total, 2 );
}

function av_presupuesto_validate_post( $editing_id = 0 ) {

    // El cliente es opcional (presupuesto de mostrador sin ficha de cliente
    // todavía): si se manda uno, tiene que ser válido.
    $client_id = isset( $_POST['client-id'] ) ? intval( $_POST['client-id'] ) : 0;
    if ( $client_id && get_post_type( $client_id ) !== 'cpt-clients' ) {
        return [ 'ok' => false, 'error' => 'cliente-invalido' ];
    }

    $lines_json = av_presupuesto_sanitize_lines( $_POST['lines'] ?? '' );
    if ( $lines_json === '' ) {
        return [ 'ok' => false, 'error' => 'lineas-requeridas' ];
    }

    return [
        'ok'   => true,
        'data' => [
            'client_id' => $client_id,
            'lines'     => $lines_json,
            'notes'     => isset( $_POST['notes'] ) ? sanitize_textarea_field( $_POST['notes'] ) : '',
        ],
    ];
}

function av_presupuesto_meta_input( array $d ) {
    return [
        'cpt-presupuesto__client-id' => $d['client_id'],
        // wp_slash: update_post_meta()/wp_insert_post() hacen su propio
        // wp_unslash() interno sobre cada valor de meta_input (pensado para
        // poder pasarles $_POST tal cual) — $d['lines'] ya es JSON final
        // (av_presupuesto_sanitize_lines() ya hizo su propio wp_unslash() al
        // leer el $_POST original), así que sin este wp_slash aquí, ese
        // segundo unslash de más se comía el escape del JSON en cuanto una
        // línea llevara una comilla o barra invertida (p.ej. 15.6" en una
        // descripción), dejando el JSON guardado inválido y esa línea
        // ilegible/perdida al releerlo.
        'cpt-presupuesto__lines'     => wp_slash( $d['lines'] ),
        'cpt-presupuesto__notes'     => $d['notes'],
    ];
}

// Cualquier técnico logueado (edit_posts), igual que Pedidos/SAT: un
// presupuesto es una operación de mostrador del día a día.
add_action( 'admin_post_av_guardar_presupuesto', 'av_guardar_presupuesto' );
function av_guardar_presupuesto() {

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    check_admin_referer( 'av_presupuesto_nonce', 'nonce' );

    $redirect_to = av_presupuestos_page_url();
    $editing_id  = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

    if ( $editing_id ) {
        $redirect_to = add_query_arg( 'id', $editing_id, $redirect_to );
    }

    $validated = av_presupuesto_validate_post( $editing_id );
    if ( ! $validated['ok'] ) {
        wp_redirect( add_query_arg( 'error', $validated['error'], $redirect_to ) );
        exit;
    }
    $d = $validated['data'];

    if ( $editing_id ) {

        if ( get_post_type( $editing_id ) !== 'cpt-presupuestos' ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        foreach ( av_presupuesto_meta_input( $d ) as $key => $value ) {
            update_post_meta( $editing_id, $key, $value );
        }

        wp_redirect( add_query_arg( [ 'id' => $editing_id, 'actualizado' => $editing_id ], av_presupuestos_page_url() ) );
        exit;
    }

    // El número lo decide siempre el backend (contador atómico), nunca lo
    // que llegue en el POST.
    $numero = av_presupuesto_generate_number();
    if ( is_wp_error( $numero ) ) {
        wp_redirect( add_query_arg( 'error', 'numero-generation-failed', $redirect_to ) );
        exit;
    }

    $nuevo_id = wp_insert_post( [
        'post_type'   => 'cpt-presupuestos',
        'post_title'  => $numero,
        'post_status' => 'publish',
        'meta_input'  => array_merge(
            av_presupuesto_meta_input( $d ),
            [
                'cpt-presupuesto__presupuesto-id' => $numero,
                'cpt-presupuesto__entry-date'     => wp_date( 'd/m/Y H:i' ),
            ]
        ),
    ] );

    if ( is_wp_error( $nuevo_id ) || ! $nuevo_id ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    wp_redirect( add_query_arg( [ 'id' => $nuevo_id, 'creado' => $nuevo_id ], av_presupuestos_page_url() ) );
    exit;
}

// Un presupuesto no tiene valor fiscal: a diferencia de una factura, sí se
// puede borrar de verdad si ya no hace falta.
add_action( 'admin_post_av_eliminar_presupuesto', 'av_eliminar_presupuesto' );
function av_eliminar_presupuesto() {

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $presupuesto_id = intval( $_GET['id'] ?? 0 );
    check_admin_referer( 'av_eliminar_presupuesto_' . $presupuesto_id );

    if ( $presupuesto_id && get_post_type( $presupuesto_id ) === 'cpt-presupuestos' ) {
        wp_delete_post( $presupuesto_id, true );
    }

    wp_redirect( add_query_arg( 'eliminado', '1', av_presupuestos_page_url() ) );
    exit;
}

// ─── Listado con filtros (clon de av_build_pedidos_query_args) ─────────────
function av_build_presupuestos_query_args( array $params, $paged = 1 ) {

    $meta_query = [ 'relation' => 'AND' ];

    if ( ! empty( $params['buscar-presupuesto'] ) ) {
        $term = sanitize_text_field( $params['buscar-presupuesto'] );

        $matching_client_ids = get_posts( [
            'post_type'      => 'cpt-clients',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'OR',
                [ 'key' => 'cpt-client__name',  'value' => $term, 'compare' => 'LIKE' ],
                [ 'key' => 'cpt-client__phone', 'value' => $term, 'compare' => 'LIKE' ],
            ],
        ] );

        $or = [ 'relation' => 'OR' ];
        $or[] = [ 'key' => 'cpt-presupuesto__presupuesto-id', 'value' => $term, 'compare' => 'LIKE' ];
        $or[] = [ 'key' => 'cpt-presupuesto__lines', 'value' => $term, 'compare' => 'LIKE' ];
        if ( ! empty( $matching_client_ids ) ) {
            $or[] = [ 'key' => 'cpt-presupuesto__client-id', 'value' => $matching_client_ids, 'compare' => 'IN' ];
        }
        $meta_query[] = $or;
    }

    $args = [
        'post_type'      => 'cpt-presupuestos',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'paged'          => $paged,
    ];

    if ( count( $meta_query ) > 1 ) {
        $args['meta_query'] = $meta_query;
    }

    return $args;
}

add_action( 'wp_ajax_av_ajax_filter_presupuestos', 'av_ajax_filter_presupuestos' );
function av_ajax_filter_presupuestos() {

    check_ajax_referer( 'av_presupuestos_filter_nonce', 'nonce' );

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error();
    }

    $paged = isset( $_REQUEST['paged'] ) ? max( 1, intval( $_REQUEST['paged'] ) ) : 1;
    $args  = av_build_presupuestos_query_args( $_REQUEST, $paged );

    $posts        = new WP_Query( $args );
    $presupuestos = $posts->posts;

    $page_posts = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-presupuestos.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    $presupuestos_page_url = ! empty( $page_posts ) ? get_permalink( $page_posts[0] ) : home_url( '/' );
    $public_params = $_REQUEST;
    unset( $public_params['action'], $public_params['nonce'], $public_params['paged'] );
    $presupuestos_page_url = add_query_arg( $public_params, $presupuestos_page_url );

    ob_start();
    include locate_template( 'components/c-presupuestos-count.php' );
    $count_html = ob_get_clean();

    ob_start();
    include locate_template( 'components/c-presupuestos-list.php' );
    $list_html = ob_get_clean();

    wp_send_json_success( [ 'count' => $count_html, 'list' => $list_html ] );
}
