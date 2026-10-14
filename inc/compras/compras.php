<?php
// ─── Compras (a particulares) ───────────────────────────────────────────────
// Cuando la tienda COMPRA algo a un cliente (p. ej. un iPhone usado) y tiene
// que darle una hoja de conformidad de la compra, con la letra pequeña. Es lo
// contrario a una factura de venta: aquí es la tienda la que paga, y la hoja
// la firma el vendedor en papel. Por eso:
//   - CPT propio (cpt-compras), NO cpt-facturas, y numeración propia
//     (C-{año}-{000001}, option av_compra_seq_{año}) para no gastar nunca la
//     serie de facturas de venta.
//   - El vendedor es un cliente de la BDD o uno escrito a mano (mismo selector
//     que Pedidos/Facturas, reutilizando su JS); el DNI/NIE es obligatorio.
//   - Una compra puede tener varios artículos, cada uno con su IMEI/nº de
//     serie, estado, accesorios y precio pagado.
//   - Las condiciones ("letra pequeña") son un texto editable, guardado en la
//     option av_compras_terms (pantalla "Condiciones" del propio módulo).
//   - La hoja imprimible es propia (components/c-compra-print.php): no es una
//     factura, así que no comparte plantilla con c-invoice.php.

add_action( 'init', 'av_register_cpt_compras' );
function av_register_cpt_compras() {
    register_post_type( 'cpt-compras', [
        'labels' => [
            'name'               => 'Compras',
            'singular_name'      => 'Compra',
            'menu_name'          => 'Compras',
            'all_items'          => 'Todas las compras',
            'edit_item'          => 'Ver compra',
            'view_item'          => 'Ver compra',
            'search_items'       => 'Buscar compras',
            'not_found'          => 'No se encontraron compras',
            'not_found_in_trash' => 'No hay compras en la papelera',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'menu_position'     => 27,
        'menu_icon'         => 'dashicons-cart',
        'supports'          => [ 'title' ],
        'capabilities'      => [
            // El alta se hace siempre desde la app (con sus validaciones).
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'      => true,
    ] );
}

function av_compras_page_url() {
    $page = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-compras.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    return ! empty( $page ) ? get_permalink( $page[0] ) : home_url( '/' );
}

// La página "Compras" se crea sola una vez (para no obligar a crearla a mano
// en wp-admin y asignarle el template). Si alguien la borra a propósito, no
// vuelve a aparecer sola.
add_action( 'init', 'av_compras_ensure_page', 30 );
function av_compras_ensure_page() {
    if ( get_option( 'av_compras_page_created' ) ) return;

    $existing = get_posts( [
        'post_type'   => 'page',
        'post_status' => 'any',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-compras.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );

    if ( empty( $existing ) ) {
        $page_id = wp_insert_post( [
            'post_type'   => 'page',
            'post_title'  => 'Compras',
            'post_name'   => 'compras',
            'post_status' => 'publish',
        ] );
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            update_post_meta( $page_id, '_wp_page_template', 'templates/template-compras.php' );
        } else {
            return; // se reintenta en la siguiente carga
        }
    }

    update_option( 'av_compras_page_created', 1, false );
}

// ─── Condiciones (letra pequeña) ────────────────────────────────────────────
// Texto de partida razonable, pensado para revisarlo con el gestor/abogado:
// una vez guardado, el texto editado manda siempre y esto ya no se usa.
function av_compras_default_terms() {
    return "El vendedor declara bajo su responsabilidad que:\n"
        . "1. Es el legítimo propietario del/los artículo(s) descritos en este documento y tiene plena capacidad para venderlos.\n"
        . "2. El/los artículo(s) están libres de cargas, embargos, reservas de dominio y reclamaciones de terceros, y no proceden de ningún delito (robo, hurto, apropiación indebida u otro).\n"
        . "3. El/los artículo(s) no figuran como perdidos o robados, no están bloqueados ni vinculados a ninguna cuenta de usuario (iCloud, Google u otras) y ha eliminado de ellos sus datos personales.\n"
        . "4. Los datos de identificación del vendedor y los datos del artículo (marca, modelo, IMEI/número de serie) son veraces.\n\n"
        . "El vendedor transmite al comprador la propiedad del/los artículo(s) por el precio total indicado, que declara haber recibido en este acto, sin que exista cantidad alguna pendiente de pago ni nada más que reclamar por esta operación.\n\n"
        . "El comprador podrá poner en conocimiento de las autoridades cualquier irregularidad sobre la procedencia del artículo, y el vendedor responderá de los daños y perjuicios derivados de la falsedad de lo declarado.\n\n"
        . "Protección de datos: los datos personales facilitados serán tratados por el comprador con la finalidad de documentar esta compraventa y cumplir sus obligaciones legales, y se conservarán durante el tiempo exigido por la normativa aplicable. El vendedor puede ejercer sus derechos de acceso, rectificación y supresión dirigiéndose al comprador.";
}

function av_compras_get_terms() {
    $saved = get_option( 'av_compras_terms', '' );
    return trim( (string) $saved ) !== '' ? $saved : av_compras_default_terms();
}

add_action( 'admin_post_av_guardar_compras_terms', 'av_guardar_compras_terms' );
function av_guardar_compras_terms() {
    if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Acceso denegado.' );
    }
    check_admin_referer( 'av_compras_terms_nonce', 'nonce' );

    $terms = sanitize_textarea_field( wp_unslash( $_POST['terms'] ?? '' ) );
    update_option( 'av_compras_terms', $terms, false );

    wp_redirect( add_query_arg( [ 'condiciones' => '1', 'guardado' => '1' ], av_compras_page_url() ) );
    exit;
}

// ─── Forma de pago (la tienda paga al vendedor) ─────────────────────────────
function av_compra_payment_choices() {
    return [
        'efectivo'      => 'Efectivo',
        'transferencia' => 'Transferencia',
    ];
}

// ─── Numeración propia ──────────────────────────────────────────────────────
function av_compra_next_numero() {
    $year    = intval( wp_date( 'Y' ) );
    $opt_key = 'av_compra_seq_' . $year;
    $seq     = intval( get_option( $opt_key, 0 ) ) + 1;
    update_option( $opt_key, $seq, false );
    return 'C-' . $year . '-' . str_pad( $seq, 6, '0', STR_PAD_LEFT );
}

// Artículos de una compra (array de arrays; vacío si no hay).
function av_compra_get_items( $compra_id ) {
    $items = json_decode( (string) get_post_meta( $compra_id, '_compra_items', true ), true );
    return is_array( $items ) ? $items : [];
}

// "iPhone 12" -> el propio nombre; varios -> "N artículos".
function av_compra_items_summary( array $items ) {
    $n = count( $items );
    if ( $n === 0 ) return '—';
    if ( $n === 1 ) return trim( ( $items[0]['tipo'] ?? '' ) . ' ' . ( $items[0]['modelo'] ?? '' ) ) ?: '—';
    return $n . ' artículos';
}

// ─── Listado (filtros por GET, sin AJAX) ────────────────────────────────────
function av_build_compras_query_args( array $params, $paged = 1 ) {
    $meta_query = [ 'relation' => 'AND' ];

    if ( ! empty( $params['buscar-compra'] ) ) {
        $term = sanitize_text_field( $params['buscar-compra'] );
        $meta_query[] = [
            'relation' => 'OR',
            [ 'key' => '_compra_numero',          'value' => $term, 'compare' => 'LIKE' ],
            [ 'key' => '_compra_vendedor_nombre', 'value' => $term, 'compare' => 'LIKE' ],
            [ 'key' => '_compra_vendedor_dni',    'value' => $term, 'compare' => 'LIKE' ],
            [ 'key' => '_compra_items',           'value' => $term, 'compare' => 'LIKE' ],
        ];
    }

    $args = [
        'post_type'      => 'cpt-compras',
        'post_status'    => 'publish',
        'posts_per_page' => 25,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'paged'          => $paged,
    ];

    if ( count( $meta_query ) > 1 ) {
        $args['meta_query'] = $meta_query;
    }

    $date_query = [];
    if ( ! empty( $params['fecha-desde'] ) ) {
        $date_query[] = [ 'after' => sanitize_text_field( $params['fecha-desde'] ), 'inclusive' => true ];
    }
    if ( ! empty( $params['fecha-hasta'] ) ) {
        $date_query[] = [ 'before' => sanitize_text_field( $params['fecha-hasta'] ) . ' 23:59:59', 'inclusive' => true ];
    }
    if ( $date_query ) {
        $args['date_query'] = $date_query;
    }

    return $args;
}

// ─── Guardado ───────────────────────────────────────────────────────────────
add_action( 'admin_post_av_guardar_compra', 'av_guardar_compra' );
function av_guardar_compra() {

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Acceso denegado.' );
    }
    check_admin_referer( 'av_compra_nonce', 'nonce' );

    $editing_id  = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    $list_url    = av_compras_page_url();
    $form_url    = $editing_id ? add_query_arg( 'id', $editing_id, $list_url ) : add_query_arg( 'new', '1', $list_url );

    if ( $editing_id && get_post_type( $editing_id ) !== 'cpt-compras' ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $list_url ) );
        exit;
    }

    // ── Vendedor: cliente de la BDD o escrito a mano ──
    $client_id = isset( $_POST['client-id'] ) ? intval( $_POST['client-id'] ) : 0;
    if ( $client_id && get_post_type( $client_id ) !== 'cpt-clients' ) {
        $client_id = 0;
    }

    $dni       = strtoupper( trim( sanitize_text_field( wp_unslash( $_POST['vendedor-dni'] ?? '' ) ) ) );
    $direccion = sanitize_text_field( wp_unslash( $_POST['vendedor-direccion'] ?? '' ) );
    $nombre    = '';
    $telefono  = '';

    if ( $client_id ) {
        $nombre   = (string) get_field( 'cpt-client__name', $client_id );
        $telefono = trim( get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id ) );
        // Sin DNI escrito en el formulario se usa el de la ficha del cliente.
        if ( $dni === '' ) {
            $dni = strtoupper( trim( (string) get_field( 'cpt-client__dni', $client_id ) ) );
        }
    } else {
        $nombre = trim( sanitize_text_field( wp_unslash( $_POST['manual-client-name'] ?? '' ) ) );
        $m_ext  = trim( sanitize_text_field( wp_unslash( $_POST['manual-client-ext'] ?? '' ) ) );
        $m_tel  = trim( sanitize_text_field( wp_unslash( $_POST['manual-client-phone'] ?? '' ) ) );
        $telefono = $m_tel !== '' ? trim( $m_ext . ' ' . $m_tel ) : '';
    }

    if ( $nombre === '' ) {
        wp_redirect( add_query_arg( 'error', 'vendedor-requerido', $form_url ) );
        exit;
    }
    if ( $dni === '' ) {
        wp_redirect( add_query_arg( 'error', 'dni-requerido', $form_url ) );
        exit;
    }

    // ── Artículos comprados (campos en array, una fila por artículo) ──
    $col = function( $key ) {
        $v = $_POST[ $key ] ?? [];
        return is_array( $v ) ? array_map( fn( $s ) => sanitize_text_field( wp_unslash( $s ) ), $v ) : [];
    };
    $tipos      = $col( 'item_tipo' );
    $modelos    = $col( 'item_modelo' );
    $imeis      = $col( 'item_imei' );
    $estados    = $col( 'item_estado' );
    $accesorios = $col( 'item_accesorios' );
    $precios    = $col( 'item_precio' );

    $items = [];
    $total = 0;
    $n     = max( count( $modelos ), count( $imeis ), count( $precios ) );
    for ( $i = 0; $i < $n; $i++ ) {
        $modelo = trim( $modelos[ $i ] ?? '' );
        $imei   = trim( $imeis[ $i ] ?? '' );
        $precio_raw = trim( str_replace( ',', '.', $precios[ $i ] ?? '' ) );
        // Fila vacía: se ignora.
        if ( $modelo === '' && $imei === '' && $precio_raw === '' ) continue;

        $precio = is_numeric( $precio_raw ) ? round( floatval( $precio_raw ), 2 ) : 0;
        $items[] = [
            'tipo'       => trim( $tipos[ $i ] ?? '' ),
            'modelo'     => $modelo,
            'imei'       => $imei,
            'estado'     => trim( $estados[ $i ] ?? '' ),
            'accesorios' => trim( $accesorios[ $i ] ?? '' ),
            'precio'     => $precio,
        ];
        $total += $precio;
    }

    if ( empty( $items ) || count( array_filter( $items, fn( $it ) => $it['modelo'] !== '' ) ) === 0 ) {
        wp_redirect( add_query_arg( 'error', 'items-requeridos', $form_url ) );
        exit;
    }
    $total = round( $total, 2 );
    if ( $total <= 0 ) {
        wp_redirect( add_query_arg( 'error', 'importe-invalido', $form_url ) );
        exit;
    }

    $forma_pago = sanitize_text_field( wp_unslash( $_POST['forma-pago'] ?? '' ) );
    if ( ! array_key_exists( $forma_pago, av_compra_payment_choices() ) ) {
        $forma_pago = 'efectivo';
    }
    $notas = sanitize_textarea_field( wp_unslash( $_POST['notas'] ?? '' ) );

    // "Guardar también este cliente en la base de datos" (solo vendedor a mano).
    if ( ! $client_id && ! empty( $_POST['manual-client-save'] ) ) {
        $m_ext = trim( sanitize_text_field( wp_unslash( $_POST['manual-client-ext'] ?? '' ) ) );
        $m_tel = trim( sanitize_text_field( wp_unslash( $_POST['manual-client-phone'] ?? '' ) ) );
        $new_id = av_create_client_record( $nombre, $m_ext, $m_tel );
        if ( $new_id ) {
            $client_id = $new_id;
            update_post_meta( $client_id, 'cpt-client__dni', $dni );
        }
    } elseif ( $client_id && $dni !== '' && trim( (string) get_field( 'cpt-client__dni', $client_id ) ) === '' ) {
        // La ficha no tenía DNI y ahora se ha escrito: se completa.
        update_post_meta( $client_id, 'cpt-client__dni', $dni );
    }

    // ── Crear / actualizar ──
    if ( $editing_id ) {
        $compra_id = $editing_id;
    } else {
        $numero    = av_compra_next_numero();
        $compra_id = wp_insert_post( [
            'post_type'   => 'cpt-compras',
            'post_title'  => $numero,
            'post_status' => 'publish',
        ] );
        if ( ! $compra_id || is_wp_error( $compra_id ) ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $form_url ) );
            exit;
        }
        // Se asigna una sola vez y nunca se vuelve a tocar.
        update_post_meta( $compra_id, '_compra_numero', $numero );
        update_post_meta( $compra_id, '_compra_fecha', wp_date( 'd/m/Y H:i' ) );
        update_post_meta( $compra_id, '_compra_usuario', wp_get_current_user()->display_name ?: wp_get_current_user()->user_login );
    }

    update_post_meta( $compra_id, '_compra_client_id',         $client_id );
    update_post_meta( $compra_id, '_compra_vendedor_nombre',   $nombre );
    update_post_meta( $compra_id, '_compra_vendedor_dni',      $dni );
    update_post_meta( $compra_id, '_compra_vendedor_telefono', $telefono );
    update_post_meta( $compra_id, '_compra_vendedor_direccion', $direccion );
    // wp_slash: update_post_meta() hace su propio wp_unslash() interno, que
    // sin esto se comería el escape del JSON (ver el resto de módulos).
    update_post_meta( $compra_id, '_compra_items',      wp_slash( wp_json_encode( $items, JSON_UNESCAPED_UNICODE ) ) );
    update_post_meta( $compra_id, '_compra_total',      $total );
    update_post_meta( $compra_id, '_compra_forma_pago', $forma_pago );
    update_post_meta( $compra_id, '_compra_notas',      $notas );

    wp_redirect( add_query_arg( [ 'id' => $compra_id, $editing_id ? 'actualizada' : 'creada' => '1' ], $list_url ) );
    exit;
}

// ─── Hoja imprimible ────────────────────────────────────────────────────────
add_action( 'template_redirect', 'av_compra_print_page' );
function av_compra_print_page() {
    if ( ! isset( $_GET['compra_print'] ) ) return;

    $compra_id = intval( $_GET['compra_id'] ?? 0 );
    if ( ! $compra_id ) wp_die( 'Compra no válida.' );
    if ( ! is_user_logged_in() ) wp_die( 'Acceso denegado.' );
    if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'compra_print_' . $compra_id ) ) wp_die( 'Nonce inválido.' );
    if ( get_post_type( $compra_id ) !== 'cpt-compras' ) wp_die( 'Compra no encontrada.' );

    include locate_template( 'components/c-compra-print.php' );
    exit;
}
