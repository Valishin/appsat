<?php
// ─── Pedidos: alta / edición ─────────────────────────────────────────────────
// Mismo patrón que inc/articulos/crud.php: admin_post para guardar, con
// nonce desde el principio (a diferencia del SAT histórico, que no lo tiene).
// Fase 1: solo backend (registrar, guardar, validar). Sin listado ni
// formulario visual todavía.

// IVA admitidos — misma lista que av_articulo_vat_choices() (config fiscal
// española ya usada en Artículos); no se reutiliza literalmente esa función
// porque vive en el módulo de artículos, pero es la misma lista.
function av_pedido_vat_choices() {
    return [ 0 => '0%', 4 => '4%', 10 => '10%', 21 => '21%' ];
}

// ─── Subtotal: SIEMPRE al vuelo, nunca almacenado ───────────────────────────
function av_pedido_line_subtotal( $sale_price, $quantity ) {
    return round( floatval( $sale_price ) * floatval( $quantity ), 2 );
}

// ─── Líneas del pedido (cpt-pedido__lines) ──────────────────────────────────
// JSON array a mano en postmeta, exactamente como cpt-sat__articulos-usados
// (ver inc/articulos/sat-integration.php / av_sat_sanitize_articulos_usados).
// NO es un ACF Repeater.
//
// Forma de cada línea ya saneada:
//   { article_id, name_snapshot, sku_snapshot, description, quantity,
//     sale_price, vat, supplier, received_date }
//
// Fase 7: se retiraron purchase_price/supplier_reference/supplier_order_number/
// estimated_date/internal_notes de la línea — la tabla de líneas pasa a tener
// exactamente los mismos campos que Presupuestos (Nombre, Descripción, Cant.,
// IVA, Sin IVA, Con IVA, Subtotal), más Proveedor, que sí se mantiene.
//
// Fase 6: se retiró supplier_status — el criterio de "línea recibida" pasa a
// ser received_date no vacío (ver av_ajax_pedido_line_received()).
//
// article_id puede ser null: permite registrar un producto que todavía no
// existe en cpt-articulos ("producto no catalogado").
function av_pedido_sanitize_lines( $raw ) {

    if ( empty( $raw ) ) return '';

    $items = is_string( $raw ) ? json_decode( wp_unslash( $raw ), true ) : $raw;
    if ( ! is_array( $items ) ) return '';

    $vat_choices = array_keys( av_pedido_vat_choices() );

    $clean = [];
    foreach ( $items as $it ) {
        if ( ! is_array( $it ) ) continue;

        // article_id: si viene y es un artículo real, se usa; si no, la línea
        // sigue siendo válida como "producto no catalogado" (article_id null).
        $article_id = ! empty( $it['article_id'] ) ? intval( $it['article_id'] ) : null;
        if ( $article_id && get_post_type( $article_id ) !== 'cpt-articulos' ) {
            $article_id = null;
        }

        $name_snapshot = sanitize_text_field( $it['name_snapshot'] ?? '' );
        $sku_snapshot  = sanitize_text_field( $it['sku_snapshot'] ?? '' );

        // SNAPSHOT: si la línea referencia un artículo real y no trae ya
        // nombre/SKU (p.ej. se construyó a mano en una prueba, o el llamante
        // solo mandó el id), se completa desde el artículo EN ESTE INSTANTE.
        // Sigue siendo un snapshot: a partir de aquí queda escrito en la
        // línea y ya no se vuelve a leer el artículo — si mañana cambia su
        // nombre/SKU/precio, esta línea no se entera.
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
        // snapshot inicial (a partir de aquí, independiente de él).
        if ( $article_id && ! isset( $it['sale_price'] ) ) {
            $sale_price = floatval( get_post_meta( $article_id, 'cpt-articulo__sale-price', true ) );
        }

        $vat = isset( $it['vat'] ) ? intval( $it['vat'] ) : 21;
        if ( ! in_array( $vat, $vat_choices, true ) ) $vat = 21;

        $clean[] = [
            'article_id'    => $article_id,
            'name_snapshot' => $name_snapshot,
            'sku_snapshot'  => $sku_snapshot,
            'description'   => sanitize_textarea_field( $it['description'] ?? '' ),
            'quantity'      => $quantity,
            'sale_price'    => $sale_price,
            'vat'           => $vat,
            'supplier'      => sanitize_text_field( $it['supplier'] ?? '' ),
            'received_date' => sanitize_text_field( $it['received_date'] ?? '' ),
        ];
    }

    return ! empty( $clean ) ? wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) : '';
}

function av_pedido_get_lines( $pedido_id ) {
    $raw   = get_post_meta( $pedido_id, 'cpt-pedido__lines', true );
    $items = $raw ? json_decode( $raw, true ) : [];
    return is_array( $items ) ? $items : [];
}

// Suma de subtotales de todas las líneas (al vuelo, no se almacena).
function av_pedido_total( $pedido_id ) {
    $total = 0;
    foreach ( av_pedido_get_lines( $pedido_id ) as $line ) {
        $total += av_pedido_line_subtotal( $line['sale_price'] ?? 0, $line['quantity'] ?? 1 );
    }
    return round( $total, 2 );
}

// ─── Validación y guardado ───────────────────────────────────────────────────
// Mismo mensaje/mecanismo que el SAT usa en el FRONTEND (src/js/app.js,
// "Antes de finalizar el SAT debes indicar el tipo de pago") pero aquí
// también en el backend — el SAT no lo comprueba del lado servidor, así que
// esto no es "copiar" esa parte sino cerrar el mismo hueco para Pedidos.
function av_pedido_validate_delivered_payment( $status, $price_description ) {
    return $status !== 'delivered' || $price_description !== '';
}

// Valida y normaliza los datos comunes de alta/edición. Devuelve
// ['ok'=>true, 'data'=>[...]] o ['ok'=>false, 'error'=>'slug-del-error'].
function av_pedido_validate_post( $editing_id = 0 ) {

    // El cliente ya no es obligatorio (p.ej. encargos de mostrador sin ficha
    // de cliente): si se manda uno, tiene que ser válido; si no se manda,
    // el pedido se guarda sin cliente vinculado (av_pedido_resolve_client_name()
    // y el resto de la ficha ya muestran "—" cuando no hay client_id).
    $client_id = isset( $_POST['client-id'] ) ? intval( $_POST['client-id'] ) : 0;
    if ( $client_id && get_post_type( $client_id ) !== 'cpt-clients' ) {
        return [ 'ok' => false, 'error' => 'cliente-requerido' ];
    }

    $lines_json = av_pedido_sanitize_lines( $_POST['lines'] ?? '' );
    if ( $lines_json === '' ) {
        return [ 'ok' => false, 'error' => 'lineas-requeridas' ];
    }

    $status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'pending';
    if ( ! array_key_exists( $status, av_pedido_status_choices() ) ) {
        $status = 'pending';
    }

    $price_description = isset( $_POST['price-description'] ) ? sanitize_text_field( $_POST['price-description'] ) : '';
    if ( ! array_key_exists( $price_description, av_pedido_payment_choices() ) ) {
        $price_description = '';
    }

    if ( ! av_pedido_validate_delivered_payment( $status, $price_description ) ) {
        return [ 'ok' => false, 'error' => 'falta-tipo-pago' ];
    }

    $anticipo = isset( $_POST['anticipo'] ) ? trim( str_replace( ',', '.', (string) $_POST['anticipo'] ) ) : '';
    $anticipo = ( $anticipo !== '' && is_numeric( $anticipo ) && floatval( $anticipo ) > 0 ) ? round( floatval( $anticipo ), 2 ) : '';

    return [
        'ok'   => true,
        'data' => [
            'client_id'          => $client_id,
            'lines'              => $lines_json,
            'status'             => $status,
            'price_description'  => $price_description,
            'anticipo'           => $anticipo,
            'delivered_date'     => isset( $_POST['delivered-date'] ) ? sanitize_text_field( $_POST['delivered-date'] ) : '',
            'location'           => isset( $_POST['location'] )       ? sanitize_text_field( $_POST['location'] )        : '',
            'client_notes'       => isset( $_POST['client-notes'] )   ? sanitize_textarea_field( $_POST['client-notes'] )   : '',
            'internal_notes'     => isset( $_POST['internal-notes'] ) ? sanitize_textarea_field( $_POST['internal-notes'] ) : '',
        ],
    ];
}

// OJO: no incluye 'cpt-pedido__pedido-id' a propósito, por la misma razón que
// av_articulo_meta_input() no incluye el SKU: el número se asigna una única
// vez al crear (av_pedido_generate_number(), en av_guardar_pedido()) y nunca
// se vuelve a escribir en una edición.
function av_pedido_meta_input( array $d ) {
    return [
        'cpt-pedido__client-id'         => $d['client_id'],
        // wp_slash: update_post_meta()/wp_insert_post() hacen su propio
        // wp_unslash() interno sobre cada valor de meta_input (pensado para
        // poder pasarles $_POST tal cual) — $d['lines'] ya es JSON final
        // (av_pedido_sanitize_lines() ya hizo su propio wp_unslash() al leer
        // el $_POST original), así que sin este wp_slash aquí, ese segundo
        // unslash de más se comería el escape del JSON en cuanto una línea
        // llevara una comilla o barra invertida (p.ej. 15.6" en la
        // descripción de un producto).
        'cpt-pedido__lines'             => wp_slash( $d['lines'] ),
        'cpt-pedido__status'            => $d['status'],
        'cpt-pedido__price-description' => $d['price_description'],
        'cpt-pedido__anticipo'          => $d['anticipo'],
        'cpt-pedido__delivered-date'    => $d['delivered_date'],
        'cpt-pedido__location'          => $d['location'],
        'cpt-pedido__client-notes'      => $d['client_notes'],
        'cpt-pedido__internal-notes'    => $d['internal_notes'],
    ];
}

// Cualquier técnico logueado (no solo administrador, a diferencia de
// Artículos/Servicios/Dispositivos): un pedido es una operación de mostrador
// del día a día, igual que crear un SAT. crear_sat_cpt() en sí no comprueba
// ninguna capability (deuda histórica) y hasta admite invitados
// (admin_post_nopriv_crear_sat_cpt); en vez de copiar eso literalmente, se
// aplica aquí el equivalente real ya usado en otras acciones "de cualquier
// técnico" de la app (p.ej. av_sat_finalizar_como_garantia): edit_posts, la
// capability que ya tienen tanto 'editor' (técnico) como 'administrator', y
// que NO tiene 'subscriber'.
add_action( 'admin_post_av_guardar_pedido', 'av_guardar_pedido' );
function av_guardar_pedido() {

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    check_admin_referer( 'av_pedido_nonce', 'nonce' );

    $redirect_to = av_pedidos_page_url();
    $editing_id  = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

    if ( $editing_id ) {
        // "id" (no "edit"): así es como template-pedidos.php reconoce el modo
        // edición — si un guardado falla, la redirección con el error vuelve
        // a abrir ESTE MISMO formulario en vez de caer al listado.
        $redirect_to = add_query_arg( 'id', $editing_id, $redirect_to );
    }

    $validated = av_pedido_validate_post( $editing_id );
    if ( ! $validated['ok'] ) {
        wp_redirect( add_query_arg( 'error', $validated['error'], $redirect_to ) );
        exit;
    }
    $d = $validated['data'];

    if ( $editing_id ) {

        if ( get_post_type( $editing_id ) !== 'cpt-pedidos' ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        foreach ( av_pedido_meta_input( $d ) as $key => $value ) {
            update_post_meta( $editing_id, $key, $value );
        }

        wp_redirect( add_query_arg( [ 'id' => $editing_id, 'actualizado' => $editing_id ], av_pedidos_page_url() ) );
        exit;
    }

    // El número lo decide siempre el backend (contador atómico), nunca lo que
    // llegue en el POST: se genera antes de crear el post para poder usarlo
    // como post_title (mismo patrón que av_sat_factura_upsert() con el
    // número de factura).
    $numero = av_pedido_generate_number();
    if ( is_wp_error( $numero ) ) {
        wp_redirect( add_query_arg( 'error', 'numero-generation-failed', $redirect_to ) );
        exit;
    }

    $nuevo_id = wp_insert_post( [
        'post_type'   => 'cpt-pedidos',
        'post_title'  => $numero,
        'post_status' => 'publish',
        'meta_input'  => array_merge(
            av_pedido_meta_input( $d ),
            [
                'cpt-pedido__pedido-id'  => $numero,
                'cpt-pedido__entry-date' => wp_date( 'd/m/Y H:i' ),
            ]
        ),
    ] );

    if ( is_wp_error( $nuevo_id ) || ! $nuevo_id ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    wp_redirect( add_query_arg( [ 'id' => $nuevo_id, 'creado' => $nuevo_id ], av_pedidos_page_url() ) );
    exit;
}

// ─── Sugerencias de proveedor (línea del pedido) ─────────────────────────────
// "No existe CPT de proveedores, no crear uno todavía": consulta simple de
// valores DISTINCT ya usados en cpt-articulo__supplier, sin catálogo nuevo.
add_action( 'wp_ajax_av_ajax_pedido_supplier_suggestions', 'av_ajax_pedido_supplier_suggestions' );
function av_ajax_pedido_supplier_suggestions() {

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
    }

    check_ajax_referer( 'av_pedido_nonce', 'nonce' );

    $term = isset( $_POST['term'] ) ? sanitize_text_field( $_POST['term'] ) : '';

    global $wpdb;
    $like = '%' . $wpdb->esc_like( $term ) . '%';

    $suppliers = $wpdb->get_col( $wpdb->prepare(
        "SELECT DISTINCT pm.meta_value
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = 'cpt-articulo__supplier'
           AND pm.meta_value <> ''
           AND pm.meta_value LIKE %s
           AND p.post_type = 'cpt-articulos'
         ORDER BY pm.meta_value ASC
         LIMIT 8",
        $like
    ) );

    wp_send_json_success( array_values( $suppliers ) );
}

// ─── Listado con filtros (clon de av_build_articulos_query_args / av_ajax_filter_articulos) ───

// Pestaña efectiva del listado de pedidos: "en-curso" (por defecto),
// "finalizados", o "todos" en cuanto se usa el buscador — mismo patrón que
// av_sats_current_tab() (functions.php): el buscador ignora la pestaña activa,
// si no un pedido finalizado buscado por número no aparecería en "en curso".
function av_pedidos_current_tab( array $params ) {
    $has_search = ! empty( $params['buscar-pedido'] );
    if ( $has_search ) return 'todos';
    return ! empty( $params['filter'] ) ? $params['filter'] : 'en-curso';
}

function av_build_pedidos_query_args( array $params, $paged = 1 ) {

    $meta_query = [ 'relation' => 'AND' ];

    // "Finalizado" = entregado o cancelado (mismo criterio que ya usa
    // av_pedido_maybe_mark_received(), inc/pedidos/crud.php), no un meta de
    // finalización aparte como en el SAT (el pedido no tiene ese concepto).
    $filter = av_pedidos_current_tab( $params );
    $pedido_terminal_statuses = [ 'delivered', 'cancelled' ];

    if ( $filter === 'en-curso' || empty( $filter ) ) {
        $meta_query[] = [ 'key' => 'cpt-pedido__status', 'value' => $pedido_terminal_statuses, 'compare' => 'NOT IN' ];
    } elseif ( $filter === 'finalizados' ) {
        $meta_query[] = [ 'key' => 'cpt-pedido__status', 'value' => $pedido_terminal_statuses, 'compare' => 'IN' ];
    }

    if ( ! empty( $params['buscar-pedido'] ) ) {
        $term = sanitize_text_field( $params['buscar-pedido'] );

        // El pedido no duplica el nombre del cliente (solo guarda su ID, ver
        // cpt-pedido__client-id): para poder buscar "por cliente" hay que
        // resolver primero qué clientes coinciden con el término.
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
        $or[] = [ 'key' => 'cpt-pedido__pedido-id', 'value' => $term, 'compare' => 'LIKE' ];
        // Busca dentro del JSON de líneas (name_snapshot, sku_snapshot...):
        // un LIKE simple sobre el meta, mismo criterio que usaría cualquier
        // otro campo JSON de la app cuando hace falta buscar dentro (no hay
        // un buscador de texto dentro de JSON más fino en el resto de App SAT).
        $or[] = [ 'key' => 'cpt-pedido__lines', 'value' => $term, 'compare' => 'LIKE' ];
        if ( ! empty( $matching_client_ids ) ) {
            $or[] = [ 'key' => 'cpt-pedido__client-id', 'value' => $matching_client_ids, 'compare' => 'IN' ];
        }
        $meta_query[] = $or;
    }

    $args = [
        'post_type'      => 'cpt-pedidos',
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

add_action( 'wp_ajax_av_ajax_filter_pedidos', 'av_ajax_filter_pedidos' );
function av_ajax_filter_pedidos() {

    check_ajax_referer( 'av_pedidos_filter_nonce', 'nonce' );

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error();
    }

    $paged = isset( $_REQUEST['paged'] ) ? max( 1, intval( $_REQUEST['paged'] ) ) : 1;
    $args  = av_build_pedidos_query_args( $_REQUEST, $paged );

    $posts   = new WP_Query( $args );
    $pedidos = $posts->posts;

    $page_posts = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-pedidos.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    $pedidos_page_url = ! empty( $page_posts ) ? get_permalink( $page_posts[0] ) : home_url( '/' );
    $public_params = $_REQUEST;
    unset( $public_params['action'], $public_params['nonce'], $public_params['paged'] );
    $pedidos_page_url = add_query_arg( $public_params, $pedidos_page_url );

    ob_start();
    include locate_template( 'components/c-pedidos-count.php' );
    $count_html = ob_get_clean();

    ob_start();
    include locate_template( 'components/c-pedidos-list.php' );
    $list_html = ob_get_clean();

    wp_send_json_success( [ 'count' => $count_html, 'list' => $list_html ] );
}

// ─── Cambio rápido de estado (desde el detalle, sin pasar por el formulario) ─
add_action( 'wp_ajax_av_ajax_pedido_quick_status', 'av_ajax_pedido_quick_status' );
function av_ajax_pedido_quick_status() {

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Acceso denegado', 403 );
    }

    check_ajax_referer( 'av_pedido_nonce', 'nonce' );

    $pedido_id = intval( $_POST['id'] ?? 0 );
    $status    = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

    if ( get_post_type( $pedido_id ) !== 'cpt-pedidos' ) {
        wp_send_json_error( 'Pedido no válido', 404 );
    }

    if ( ! array_key_exists( $status, av_pedido_status_choices() ) ) {
        wp_send_json_error( 'Estado no válido', 400 );
    }

    // Mismo mensaje/mecanismo que el SAT, también en el backend (ver
    // av_pedido_validate_delivered_payment()): no se puede entregar sin
    // tipo de pago informado.
    $price_description = get_post_meta( $pedido_id, 'cpt-pedido__price-description', true );
    if ( ! av_pedido_validate_delivered_payment( $status, $price_description ) ) {
        wp_send_json_error( 'Antes de marcar el pedido como entregado debes indicar el tipo de pago.', 400 );
    }

    update_post_meta( $pedido_id, 'cpt-pedido__status', $status );

    if ( $status === 'delivered' && ! get_post_meta( $pedido_id, 'cpt-pedido__delivered-date', true ) ) {
        update_post_meta( $pedido_id, 'cpt-pedido__delivered-date', wp_date( 'Y-m-d' ) );
    }

    wp_send_json_success( [
        'status' => $status,
        'label'  => av_pedido_status_label( $status ),
    ] );
}

// ─── Recepción de líneas (Fase 4) ────────────────────────────────────────────
// Candado atómico anti-doble-clic para "marcar recibido" en una línea. Mismo
// principio que el contador de PED-xxxx/SKU (inc/pedidos/numbering.php,
// inc/articulos/sku.php): una única sentencia INSERT que se apoya en el
// índice UNIQUE de wp_options.option_name — si dos peticiones llegan a la
// vez para la MISMA línea, solo una consigue insertar la fila; la otra falla
// por clave duplicada y sabe que ha llegado tarde. No hace falta una
// transacción explícita (este proyecto no usa START TRANSACTION/GET_LOCK en
// ningún sitio): el propio índice único de MySQL basta.
//
// El candado se libera (se borra la fila) SOLO si el movimiento de stock
// falla, para no dejar la línea bloqueada para siempre tras un error
// transitorio. Si todo sale bien, la fila se queda (nunca vuelve a hacer
// falta: a partir de ahí el propio received_date no vacío de la línea ya
// basta como guarda para cualquier reintento posterior).
function av_pedido_line_receive_lock_name( $pedido_id, $line_index ) {
    return 'av_pedido_line_received_lock_' . intval( $pedido_id ) . '_' . intval( $line_index );
}

function av_pedido_line_receive_lock_acquire( $pedido_id, $line_index ) {
    global $wpdb;
    $lock_name = av_pedido_line_receive_lock_name( $pedido_id, $line_index );
    $result = $wpdb->query( $wpdb->prepare(
        "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '1', 'no')",
        $lock_name
    ) );
    return $result !== false;
}

function av_pedido_line_receive_lock_release( $pedido_id, $line_index ) {
    global $wpdb;
    $wpdb->delete( $wpdb->options, [ 'option_name' => av_pedido_line_receive_lock_name( $pedido_id, $line_index ) ] );
}

add_action( 'wp_ajax_av_ajax_pedido_line_received', 'av_ajax_pedido_line_received' );
function av_ajax_pedido_line_received() {

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Acceso denegado', 403 );
    }

    check_ajax_referer( 'av_pedido_nonce', 'nonce' );

    $pedido_id  = intval( $_POST['id'] ?? 0 );
    $line_index = isset( $_POST['line_index'] ) ? intval( $_POST['line_index'] ) : -1;

    if ( get_post_type( $pedido_id ) !== 'cpt-pedidos' ) {
        wp_send_json_error( 'Pedido no válido', 404 );
    }

    $pedido_status = get_post_meta( $pedido_id, 'cpt-pedido__status', true ) ?: 'pending';
    if ( $pedido_status === 'cancelled' ) {
        wp_send_json_error( 'Este pedido está cancelado: no se pueden recibir líneas.', 400 );
    }

    $lines = av_pedido_get_lines( $pedido_id );
    if ( $line_index < 0 || ! isset( $lines[ $line_index ] ) ) {
        wp_send_json_error( 'Línea no válida', 404 );
    }

    $line = $lines[ $line_index ];

    // ── Idempotencia (guarda principal): si esta línea YA tiene received_date,
    // la petición es un no-op seguro — no se repite el movimiento de stock ni
    // se vuelve a escribir nada. Cubre el caso normal de doble clic/reintento
    // tras que la primera petición ya haya terminado con éxito.
    if ( ! empty( $line['received_date'] ) ) {
        wp_send_json_success( [
            'already_received'        => true,
            'line_index'              => $line_index,
            'received_date'           => $line['received_date'],
            'received_date_formatted' => date_i18n( 'd/m/Y', strtotime( $line['received_date'] ) ),
            'pedido_status'           => $pedido_status,
            'pedido_status_label'     => av_pedido_status_label( $pedido_status ),
        ] );
    }

    // ── Idempotencia (guarda de carrera): si dos peticiones llegan casi a la
    // vez para la MISMA línea (antes de que la primera haya podido guardar su
    // received_date de arriba), esta es la que de verdad evita duplicar el
    // movimiento de stock.
    if ( ! av_pedido_line_receive_lock_acquire( $pedido_id, $line_index ) ) {
        wp_send_json_error( 'Esta línea ya se está marcando como recibida (otra petición en curso). Recarga para ver el estado actual.', 409 );
    }

    $article_id = ! empty( $line['article_id'] ) ? intval( $line['article_id'] ) : null;
    $quantity   = floatval( $line['quantity'] ?? 1 );

    // Producto no catalogado (article_id null): no se toca stock ni se crea
    // ningún artículo nuevo automáticamente — solo se marca la línea.
    if ( $article_id && get_post_type( $article_id ) === 'cpt-articulos' ) {
        $pedido_numero = get_post_meta( $pedido_id, 'cpt-pedido__pedido-id', true );
        $result = av_articulo_registrar_movimiento(
            $article_id,
            'purchase',
            $quantity,
            'Recepción del pedido ' . $pedido_numero,
            'pedido',
            $pedido_id
        );

        // Si el stock no se pudo actualizar, la línea NO se marca como
        // recibida (nada queda en un estado inconsistente) y se libera el
        // candado para permitir un reintento legítimo más adelante.
        if ( is_wp_error( $result ) ) {
            av_pedido_line_receive_lock_release( $pedido_id, $line_index );
            wp_send_json_error( $result->get_error_message(), 400 );
        }
    }

    $today = wp_date( 'Y-m-d' );

    $lines[ $line_index ]['received_date'] = $today;

    // wp_slash: ver el mismo comentario en av_pedido_meta_input() más arriba.
    update_post_meta( $pedido_id, 'cpt-pedido__lines', wp_slash( wp_json_encode( $lines, JSON_UNESCAPED_UNICODE ) ) );

    // ¿Todas las líneas recibidas (received_date no vacío)? Si es así, y el
    // pedido no está ya entregado ni cancelado, se promociona el estado
    // general del pedido a "received" automáticamente. Ya no hay paso
    // intermedio a "ready" (Fase 6): recibido y listo para recoger son el
    // mismo estado.
    $all_received = true;
    foreach ( $lines as $l ) {
        if ( empty( $l['received_date'] ) ) { $all_received = false; break; }
    }

    $new_pedido_status = $pedido_status;
    if ( $all_received && ! in_array( $pedido_status, [ 'delivered', 'cancelled' ], true ) ) {
        $new_pedido_status = 'received';
        update_post_meta( $pedido_id, 'cpt-pedido__status', 'received' );
    }

    wp_send_json_success( [
        'line_index'              => $line_index,
        'received_date'           => $today,
        'received_date_formatted' => date_i18n( 'd/m/Y', strtotime( $today ) ),
        'all_received'            => $all_received,
        'pedido_status'           => $new_pedido_status,
        'pedido_status_label'     => av_pedido_status_label( $new_pedido_status ),
    ] );
}
