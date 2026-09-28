<?php
// ─── Integración de Artículos dentro del SAT ────────────────────────────────
// Sección nueva "Artículos utilizados", independiente de Reparación/Piezas
// pedidas (texto libre): las líneas aquí referencian artículos reales del
// catálogo (inc/articulos/) y su uso descuenta stock de verdad.
//
// Meta del SAT: cpt-sat__articulos-usados — JSON array de líneas:
//   { article_id, name_snapshot, sku_snapshot, quantity, unit_price, vat, subtotal }
// Los snapshots de nombre/SKU/precio se guardan en el momento de añadir la
// línea: si el artículo cambia de nombre o precio más tarde, un SAT antiguo
// sigue mostrando los datos tal como eran cuando se usó.

// Sanitiza el JSON que llega de $_POST['articulos-usados']. Nunca confía en
// article_id/nombre/precio del navegador para nada que no sea guardarlo como
// snapshot de texto: el descuento de stock recalcula el precio/; nombre desde
// el propio artículo en av_sat_apply_articulos_stock(), no desde este JSON.
function av_sat_sanitize_articulos_usados( $raw ) {

    if ( empty( $raw ) ) return '';

    $items = json_decode( $raw, true );
    if ( ! is_array( $items ) ) return '';

    $clean = [];
    foreach ( $items as $it ) {
        $article_id = intval( $it['article_id'] ?? 0 );
        $quantity   = floatval( $it['quantity'] ?? 0 );
        if ( ! $article_id || $quantity <= 0 ) continue;
        if ( get_post_type( $article_id ) !== 'cpt-articulos' ) continue;

        $unit_price = round( floatval( $it['unit_price'] ?? 0 ), 2 );
        $clean[] = [
            'article_id'    => $article_id,
            'name_snapshot' => sanitize_text_field( $it['name_snapshot'] ?? '' ),
            'sku_snapshot'  => sanitize_text_field( $it['sku_snapshot'] ?? '' ),
            'quantity'      => $quantity,
            'unit_price'    => $unit_price,
            'vat'           => sanitize_text_field( (string) ( $it['vat'] ?? '' ) ),
            'subtotal'      => round( $quantity * $unit_price, 2 ),
        ];
    }

    return ! empty( $clean ) ? wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) : '';
}

function av_sat_articulos_usados_to_map( $json ) {
    $items = json_decode( (string) $json, true );
    if ( ! is_array( $items ) ) return [];

    $map = [];
    foreach ( $items as $it ) {
        $id = intval( $it['article_id'] ?? 0 );
        if ( ! $id ) continue;
        $map[ $id ] = ( $map[ $id ] ?? 0 ) + floatval( $it['quantity'] ?? 0 );
    }
    return $map;
}

/**
 * Comprueba ANTES de guardar el SAT que hay stock suficiente para todo lo que
 * se vaya a consumir de más. Se llama antes de wp_insert_post()/wp_update_post()
 * para no dejar guardada una línea de artículo cuyo stock luego resulta
 * insuficiente (eso dejaría la línea y el inventario desincronizados).
 *
 * @return true|WP_Error
 */
function av_sat_validate_articulos_stock( $before_json, $after_json ) {

    $before = av_sat_articulos_usados_to_map( $before_json );
    $after  = av_sat_articulos_usados_to_map( $after_json );

    foreach ( $after as $article_id => $qty_after ) {

        $qty_before = $before[ $article_id ] ?? 0;
        $delta_uso  = $qty_after - $qty_before;
        if ( $delta_uso <= 0 ) continue; // se usa igual o menos: nunca hace falta más stock

        if ( get_post_meta( $article_id, 'cpt-articulo__deduct-stock', true ) !== '1' ) continue;

        $stock          = floatval( get_post_meta( $article_id, 'cpt-articulo__stock', true ) );
        $allow_negative = get_post_meta( $article_id, 'cpt-articulo__allow-negative-stock', true ) === '1';

        if ( ! $allow_negative && $delta_uso > $stock ) {
            return new WP_Error(
                'stock-insuficiente',
                sprintf(
                    'Stock insuficiente de "%s". Stock disponible: %s',
                    get_the_title( $article_id ),
                    av_articulo_format_qty( $stock )
                )
            );
        }
    }

    return true;
}

/**
 * Compara las líneas de artículos ANTES/DESPUÉS de un guardado de SAT y
 * aplica solo la diferencia como movimientos de stock (sat_usage / sat_return).
 * Protegido contra doble-submit/doble-AJAX/recarga mediante un token de
 * envío: si el token ya se aplicó para este SAT, no se repite el movimiento
 * (el resto de campos del SAT se siguen guardando con normalidad).
 */
function av_sat_apply_articulos_stock( $sat_id, $before_json, $after_json, $token = '' ) {

    if ( $token !== '' ) {
        $applied_token = get_post_meta( $sat_id, 'cpt-sat__articulos-stock-token', true );
        if ( $applied_token === $token ) {
            // Esta misma tanda de cambios ya generó sus movimientos de stock
            // (reenvío del mismo formulario): no se vuelve a tocar el stock.
            return;
        }
    }

    $before = av_sat_articulos_usados_to_map( $before_json );
    $after  = av_sat_articulos_usados_to_map( $after_json );

    $article_ids   = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );
    $any_movement  = false;

    foreach ( $article_ids as $article_id ) {

        $qty_before = $before[ $article_id ] ?? 0;
        $qty_after  = $after[ $article_id ] ?? 0;
        $delta_uso  = $qty_after - $qty_before; // >0: se usa más; <0: se usa menos / se quita

        if ( $delta_uso == 0 ) continue;

        // Si el artículo tiene desactivado "descontar stock al usar en SAT",
        // la línea se guarda igualmente (precio/nombre en la factura) pero no
        // se toca el inventario.
        $deduct = get_post_meta( $article_id, 'cpt-articulo__deduct-stock', true );
        if ( $deduct !== '1' ) continue;

        if ( $delta_uso > 0 ) {
            $result = av_articulo_registrar_movimiento(
                $article_id, 'sat_usage', -$delta_uso,
                'Usado en SAT', 'sat', $sat_id
            );
        } else {
            $result = av_articulo_registrar_movimiento(
                $article_id, 'sat_return', -$delta_uso, // -delta_uso es positivo aquí
                'Devuelto del SAT', 'sat', $sat_id
            );
        }

        // av_sat_validate_articulos_stock() ya comprobó la disponibilidad antes
        // de guardar el SAT: llegar aquí a un WP_Error solo puede deberse a un
        // cambio de stock justo entre medias (carrera muy poco probable). Se
        // registra para poder investigarlo, sin interrumpir el resto del guardado.
        if ( is_wp_error( $result ) ) {
            error_log( '[Artículos] No se pudo aplicar movimiento de stock del SAT #' . $sat_id . ' / artículo #' . $article_id . ': ' . $result->get_error_message() );
            continue;
        }

        $any_movement = true;
    }

    if ( $token !== '' ) {
        update_post_meta( $sat_id, 'cpt-sat__articulos-stock-token', $token );
    }

    // Constancia también en el propio histórico del SAT (el detalle
    // movimiento a movimiento ya queda en el historial de cada artículo).
    // Solo si hubo un cambio real: si no, cada guardado del SAT (aunque sea
    // por otro campo) spamearía el histórico sin que pasara nada con el stock.
    if ( $any_movement ) {
        av_sat_add_history( $sat_id, 'Artículos utilizados actualizados', 'articulos' );
    }
}

// ─── Buscador de artículos para el SAT (autocomplete) ───────────────────────
// A diferencia del buscador de clientes (que recorre TODOS los ids en PHP:
// funciona bien con pocos cientos de clientes), este usa WP_Query + meta_query
// porque un catálogo de piezas puede crecer mucho más.
add_action( 'wp_ajax_av_ajax_search_articulos_picker', 'av_ajax_search_articulos_picker' );
function av_ajax_search_articulos_picker() {

    check_ajax_referer( 'av_articulos_picker_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
    }

    $term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
    if ( mb_strlen( $term ) < 2 ) {
        wp_send_json_success( [] );
    }

    $args = [
        'post_type'      => 'cpt-articulos',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        's'              => $term,
        'meta_query'      => [
            'relation' => 'AND',
            [ 'key' => 'cpt-articulo__status',        'value' => 'active', 'compare' => '=' ],
            [ 'key' => 'cpt-articulo__usable-in-sat',  'value' => '1',      'compare' => '=' ],
        ],
    ];

    // "s" (búsqueda por título) no encuentra por SKU/EAN/marca/modelo: se
    // añade un OR de meta_query search y se combina con el título mediante
    // el filtro estándar de WP mezclando posts__in de ambas búsquedas.
    $by_meta = get_posts( [
        'post_type'      => 'cpt-articulos',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'AND',
            [ 'key' => 'cpt-articulo__status',       'value' => 'active', 'compare' => '=' ],
            [ 'key' => 'cpt-articulo__usable-in-sat', 'value' => '1',      'compare' => '=' ],
            [
                'relation' => 'OR',
                [ 'key' => 'cpt-articulo__sku',     'value' => $term, 'compare' => 'LIKE' ],
                [ 'key' => 'cpt-articulo__barcode', 'value' => $term, 'compare' => 'LIKE' ],
                [ 'key' => 'cpt-articulo__brand',   'value' => $term, 'compare' => 'LIKE' ],
                [ 'key' => 'cpt-articulo__model',   'value' => $term, 'compare' => 'LIKE' ],
            ],
        ],
    ] );

    $by_title = get_posts( array_merge( $args, [ 'fields' => 'ids' ] ) );

    // También por nombre de categoría (p.ej. buscar "pantallas" encuentra
    // todos los artículos de esa categoría, no solo los que llevan la palabra
    // en el propio nombre/SKU): se resuelven primero los términos que
    // coinciden y se busca por tax_query, igual de aditivo que $by_meta.
    $matching_terms = get_terms( [
        'taxonomy'   => 'articulo_categoria',
        'name__like' => $term,
        'hide_empty' => false,
        'fields'     => 'ids',
    ] );
    $by_category = is_wp_error( $matching_terms ) || empty( $matching_terms ) ? [] : get_posts( [
        'post_type'      => 'cpt-articulos',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'AND',
            [ 'key' => 'cpt-articulo__status',       'value' => 'active', 'compare' => '=' ],
            [ 'key' => 'cpt-articulo__usable-in-sat', 'value' => '1',      'compare' => '=' ],
        ],
        'tax_query'      => [
            [ 'taxonomy' => 'articulo_categoria', 'field' => 'term_id', 'terms' => $matching_terms ],
        ],
    ] );

    $ids = array_slice( array_unique( array_merge( $by_title, $by_meta, $by_category ) ), 0, 8 );

    $results = [];
    foreach ( $ids as $id ) {
        $stock = floatval( get_post_meta( $id, 'cpt-articulo__stock', true ) );
        $category_terms = get_the_terms( $id, 'articulo_categoria' );
        $results[] = [
            'id'         => $id,
            // get_the_title() pasa el título por wptexturize() (pensado para
            // salida HTML directa: convierte " - " en la entidad numérica
            // "&#8211;"); aquí se envía como JSON y el JS ya escapa el texto
            // al pintarlo, así que hay que deshacerlo (html_entity_decode,
            // no wp_specialchars_decode: esta no toca entidades numéricas)
            // o sale literal.
            'name'       => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ),
            'sku'        => get_post_meta( $id, 'cpt-articulo__sku', true ),
            'image'      => get_post_meta( $id, 'cpt-articulo__image', true ),
            'stock'      => $stock,
            'stock_text' => av_articulo_format_qty( $stock ),
            'unit_price' => floatval( get_post_meta( $id, 'cpt-articulo__sale-price', true ) ),
            // Añadido para el picker de líneas de Pedidos (Fase 2): el widget
            // de Artículos usados del SAT no lo usa, así que añadirlo aquí no
            // le afecta en nada.
            'purchase_price' => floatval( get_post_meta( $id, 'cpt-articulo__purchase-price', true ) ),
            'vat'        => get_post_meta( $id, 'cpt-articulo__vat', true ),
            'allow_negative_stock' => get_post_meta( $id, 'cpt-articulo__allow-negative-stock', true ) === '1',
            // Igualmente aditivo: nombre de categoría, para mostrarlo junto al
            // artículo en el resultado del buscador (ver src/js/app.js).
            'category'   => ( ! is_wp_error( $category_terms ) && ! empty( $category_terms ) ) ? $category_terms[0]->name : '',
            // Añadido para el picker de líneas de Presupuestos: al elegir un
            // artículo, la descripción de la línea se autocompleta con estos
            // tres datos (ver av_presupuesto_lines_widget() en src/js/app.js).
            // cpt-articulo__description es un campo wysiwyg: se limpia de
            // HTML para que la línea del presupuesto sea texto plano.
            'description' => wp_strip_all_tags( get_post_meta( $id, 'cpt-articulo__description', true ) ),
            'model'       => get_post_meta( $id, 'cpt-articulo__model', true ),
            'brand'       => get_post_meta( $id, 'cpt-articulo__brand', true ),
        ];
    }

    wp_send_json_success( $results );
}
