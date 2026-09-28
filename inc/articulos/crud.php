<?php
// ─── Artículos: alta / edición / baja / listado ─────────────────────────────
// Mismo patrón que servicios/dispositivos: admin_post para guardar/eliminar,
// AJAX para el buscador con filtros (clon de av_ajax_filter_clients).

// IVA admitidos (España). Si en el futuro la app tiene una configuración
// fiscal multi-tipo, este es el único sitio a tocar.
function av_articulo_vat_choices() {
    return [ '0' => '0%', '4' => '4%', '10' => '10%', '21' => '21%' ];
}

function av_articulo_unit_choices() {
    return [ 'unit' => 'Unidad', 'meter' => 'Metro', 'liter' => 'Litro', 'kg' => 'Kg', 'box' => 'Caja' ];
}

function av_articulo_type_choices() {
    return [ 'part' => 'Pieza', 'consumable' => 'Consumible', 'accessory' => 'Accesorio', 'product' => 'Producto', 'service' => 'Servicio' ];
}

// Tipos de componente electrónico (mismas claves que av_articulo_component_type_prefixes()
// en inc/articulos/sku.php: el prefijo del SKU de un componente sale de aquí).
function av_articulo_component_type_choices() {
    return [
        'ic'            => 'IC / Controlador',
        'mosfet'        => 'MOSFET',
        'diodo'         => 'Diodo',
        'transistor'    => 'Transistor',
        'resistencia'   => 'Resistencia',
        'condensador'   => 'Condensador',
        'bobina'        => 'Bobina / Inductor',
        'fusible'       => 'Fusible',
        'cristal'       => 'Cristal / Oscilador',
        'optoacoplador' => 'Optoacoplador',
        'conector-smd'  => 'Conector SMD',
        'otro'          => 'Otro',
    ];
}

// Encapsulados habituales, solo como sugerencia (datalist): el campo admite
// cualquier valor personalizado, no está restringido a esta lista.
function av_articulo_component_package_suggestions() {
    return [ 'QFN', 'DFN', 'SOIC', 'SOP', 'TSSOP', 'SOT-23', 'SOT-223', 'TO-220', 'BGA', 'LGA', 'WSON' ];
}

// Sube (o sustituye) la foto del artículo. Reutiliza tal cual las funciones
// genéricas de fotos ya usadas por el SAT (av_sat_upload_photos() etc. no
// son en realidad específicas de SAT, solo llevan ese prefijo histórico).
function av_articulo_save_uploaded_photo( $articulo_id ) {

    $meta_key  = 'cpt-articulo__image';
    $input_name = 'image';

    $current = av_sat_photo_urls( get_post_meta( $articulo_id, $meta_key, true ) );

    $to_remove = av_sat_photos_to_remove( $input_name );
    if ( ! empty( $to_remove ) ) {
        foreach ( $current as $url ) {
            av_sat_delete_photo_attachment( $articulo_id, $url );
        }
        $current = [];
        update_post_meta( $articulo_id, $meta_key, '' );
    }

    $nuevas = av_sat_upload_photos( $articulo_id, $input_name, 1 );
    if ( ! empty( $nuevas ) ) {
        // Una foto nueva sustituye a la anterior (campo de una sola foto).
        foreach ( $current as $url ) {
            av_sat_delete_photo_attachment( $articulo_id, $url );
        }
        update_post_meta( $articulo_id, $meta_key, $nuevas[0] );
    }
}

// Valida y normaliza los datos comunes de alta/edición. Devuelve
// ['ok'=>true, 'data'=>[...]] o ['ok'=>false, 'error'=>'slug-del-error'].
function av_articulo_validate_post( $editing_id = 0 ) {

    $nombre = isset( $_POST['nombre'] ) ? sanitize_text_field( $_POST['nombre'] ) : '';

    if ( $nombre === '' ) {
        return [ 'ok' => false, 'error' => 'missing' ];
    }

    $category = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;

    // El SKU se genera solo a partir de la categoría (ver inc/articulos/sku.php):
    // sin categoría no hay prefijo, así que al CREAR es obligatoria. Al editar
    // no (el SKU ya existe y no se regenera aunque se cambie de categoría).
    if ( ! $editing_id && ! $category ) {
        return [ 'ok' => false, 'error' => 'categoria-requerida' ];
    }

    // "Componentes electrónicos" necesita su tipo de componente siempre que
    // esa sea la categoría elegida (al crear Y al editar: si se cambia la
    // categoría A electrónicos más tarde, también hace falta el tipo — el SKU
    // ya existente no se toca, pero el campo sigue siendo obligatorio para
    // que la ficha tenga sentido).
    $component_type = isset( $_POST['component-type'] ) ? sanitize_text_field( $_POST['component-type'] ) : '';
    if ( av_articulo_is_electronics_category( $category ) ) {
        if ( ! array_key_exists( $component_type, av_articulo_component_type_choices() ) ) {
            return [ 'ok' => false, 'error' => 'tipo-componente-requerido' ];
        }
    } elseif ( $component_type && ! array_key_exists( $component_type, av_articulo_component_type_choices() ) ) {
        $component_type = '';
    }

    $barcode = isset( $_POST['barcode'] ) ? sanitize_text_field( $_POST['barcode'] ) : '';
    if ( $barcode !== '' ) {
        $existing_barcode = get_posts( [
            'post_type'      => 'cpt-articulos',
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'fields'         => 'ids',
            'meta_query'     => [ [ 'key' => 'cpt-articulo__barcode', 'value' => $barcode, 'compare' => '=' ] ],
        ] );
        if ( ! empty( $existing_barcode ) && intval( $existing_barcode[0] ) !== intval( $editing_id ) ) {
            return [ 'ok' => false, 'error' => 'barcode-duplicado' ];
        }
    }

    $vat = isset( $_POST['vat'] ) ? sanitize_text_field( $_POST['vat'] ) : '21';
    if ( ! array_key_exists( $vat, av_articulo_vat_choices() ) ) {
        return [ 'ok' => false, 'error' => 'iva-invalido' ];
    }

    $unit = isset( $_POST['unit'] ) ? sanitize_text_field( $_POST['unit'] ) : 'unit';
    if ( ! array_key_exists( $unit, av_articulo_unit_choices() ) ) {
        $unit = 'unit';
    }

    $article_type = isset( $_POST['article-type'] ) ? sanitize_text_field( $_POST['article-type'] ) : 'part';
    if ( ! array_key_exists( $article_type, av_articulo_type_choices() ) ) {
        $article_type = 'part';
    }

    $allow_negative = ! empty( $_POST['allow-negative-stock'] );

    $to_float = function( $key, $default = '' ) {
        $raw = isset( $_POST[ $key ] ) ? trim( str_replace( ',', '.', (string) $_POST[ $key ] ) ) : '';
        if ( $raw === '' ) return $default;
        return is_numeric( $raw ) ? round( floatval( $raw ), 2 ) : false;
    };

    $purchase_price   = $to_float( 'purchase-price', '' );
    $sale_price       = $to_float( 'sale-price', '' );
    $supplier_price   = $to_float( 'supplier-price', '' );

    if ( $purchase_price === false || $sale_price === false || $supplier_price === false ) {
        return [ 'ok' => false, 'error' => 'precio-invalido' ];
    }
    if ( ( $purchase_price !== '' && $purchase_price < 0 ) || ( $sale_price !== '' && $sale_price < 0 ) || ( $supplier_price !== '' && $supplier_price < 0 ) ) {
        return [ 'ok' => false, 'error' => 'precio-invalido' ];
    }

    $stock_min = isset( $_POST['stock-min'] ) && $_POST['stock-min'] !== '' ? intval( $_POST['stock-min'] ) : 0;
    $stock_max = isset( $_POST['stock-max'] ) && $_POST['stock-max'] !== '' ? intval( $_POST['stock-max'] ) : '';
    if ( $stock_min < 0 || ( $stock_max !== '' && $stock_max < 0 ) ) {
        return [ 'ok' => false, 'error' => 'stock-invalido' ];
    }

    // El stock inicial (solo se usa al CREAR, ver av_guardar_articulo) también
    // respeta la regla de no-negativo salvo que se permita expresamente.
    $stock_inicial = isset( $_POST['stock'] ) && $_POST['stock'] !== '' ? intval( $_POST['stock'] ) : 0;
    if ( $stock_inicial < 0 && ! $allow_negative ) {
        return [ 'ok' => false, 'error' => 'stock-invalido' ];
    }

    return [
        'ok'   => true,
        'data' => [
            'nombre'               => $nombre,
            'barcode'              => $barcode,
            'description'          => isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '',
            'category'             => $category,
            'brand'                => isset( $_POST['brand'] ) ? sanitize_text_field( $_POST['brand'] ) : '',
            'model'                => isset( $_POST['model'] ) ? sanitize_text_field( $_POST['model'] ) : '',
            'status'               => ( isset( $_POST['status'] ) && $_POST['status'] === 'inactive' ) ? 'inactive' : 'active',
            'stock_inicial'        => $stock_inicial,
            'stock_min'            => $stock_min,
            'stock_max'            => $stock_max,
            'location'             => isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : '',
            'unit'                 => $unit,
            'allow_negative_stock' => $allow_negative,
            'purchase_price'       => $purchase_price,
            'sale_price'           => $sale_price,
            'vat'                  => $vat,
            'supplier'             => isset( $_POST['supplier'] ) ? sanitize_text_field( $_POST['supplier'] ) : '',
            'supplier_reference'   => isset( $_POST['supplier-reference'] ) ? sanitize_text_field( $_POST['supplier-reference'] ) : '',
            'supplier_price'       => $supplier_price,
            'supplier_url'         => isset( $_POST['supplier-url'] ) ? esc_url_raw( $_POST['supplier-url'] ) : '',
            'delivery_days'        => isset( $_POST['delivery-days'] ) && $_POST['delivery-days'] !== '' ? max( 0, intval( $_POST['delivery-days'] ) ) : '',
            'usable_in_sat'        => ! empty( $_POST['usable-in-sat'] ),
            'sellable'             => ! empty( $_POST['sellable'] ),
            'deduct_stock'         => ! empty( $_POST['deduct-stock'] ),
            'article_type'         => $article_type,
            'internal_notes'       => isset( $_POST['internal-notes'] ) ? sanitize_textarea_field( $_POST['internal-notes'] ) : '',
            // Datos de componente electrónico: se guardan siempre que llegan,
            // sin importar la categoría actual — así, si se cambia a
            // electrónicos y de vuelta a otra categoría, no se pierden (punto
            // 6 del encargo: "no perder los valores si se cambia
            // temporalmente de categoría").
            'component_type'          => $component_type,
            'marking_code'            => isset( $_POST['marking-code'] ) ? sanitize_text_field( $_POST['marking-code'] ) : '',
            'package'                 => isset( $_POST['package'] ) ? sanitize_text_field( $_POST['package'] ) : '',
            'board_reference'         => isset( $_POST['board-reference'] ) ? sanitize_text_field( $_POST['board-reference'] ) : '',
            'component_specs'         => isset( $_POST['component-specs'] ) ? sanitize_textarea_field( $_POST['component-specs'] ) : '',
            'component_equivalents'   => isset( $_POST['component-equivalents'] ) ? sanitize_textarea_field( $_POST['component-equivalents'] ) : '',
            'component_application'   => isset( $_POST['component-application'] ) ? sanitize_text_field( $_POST['component-application'] ) : '',
        ],
    ];
}

// OJO: no incluye 'cpt-articulo__sku' a propósito. El SKU se asigna una única
// vez al crear (av_articulo_generate_sku(), en av_guardar_articulo()) y nunca
// se escribe desde aquí: si se incluyera en este array genérico, un guardado
// de edición con un valor de "sku" manipulado en el navegador (el campo es
// readonly, pero readonly no impide inspeccionar/editar el DOM) podría
// sobrescribir un SKU ya asignado. Mantenerlo fuera de esta función es lo que
// garantiza que el SKU sea estable pase lo que pase en el formulario.
function av_articulo_meta_input( array $d ) {
    return [
        'cpt-articulo__barcode'              => $d['barcode'],
        'cpt-articulo__description'          => $d['description'],
        'cpt-articulo__brand'                => $d['brand'],
        'cpt-articulo__model'                => $d['model'],
        'cpt-articulo__status'               => $d['status'],
        'cpt-articulo__stock-min'            => $d['stock_min'],
        'cpt-articulo__stock-max'            => $d['stock_max'],
        'cpt-articulo__location'             => $d['location'],
        'cpt-articulo__unit'                 => $d['unit'],
        'cpt-articulo__allow-negative-stock' => $d['allow_negative_stock'] ? '1' : '',
        'cpt-articulo__purchase-price'       => $d['purchase_price'],
        'cpt-articulo__sale-price'           => $d['sale_price'],
        'cpt-articulo__vat'                  => $d['vat'],
        'cpt-articulo__supplier'             => $d['supplier'],
        'cpt-articulo__supplier-reference'   => $d['supplier_reference'],
        'cpt-articulo__supplier-price'       => $d['supplier_price'],
        'cpt-articulo__supplier-url'         => $d['supplier_url'],
        'cpt-articulo__delivery-days'        => $d['delivery_days'],
        'cpt-articulo__usable-in-sat'        => $d['usable_in_sat'] ? '1' : '',
        'cpt-articulo__sellable'             => $d['sellable'] ? '1' : '',
        'cpt-articulo__deduct-stock'         => $d['deduct_stock'] ? '1' : '',
        'cpt-articulo__article-type'         => $d['article_type'],
        'cpt-articulo__internal-notes'       => $d['internal_notes'],
        'cpt-articulo__component-type'          => $d['component_type'],
        'cpt-articulo__marking-code'            => $d['marking_code'],
        'cpt-articulo__package'                 => $d['package'],
        'cpt-articulo__board-reference'         => $d['board_reference'],
        'cpt-articulo__component-specs'         => $d['component_specs'],
        'cpt-articulo__component-equivalents'   => $d['component_equivalents'],
        'cpt-articulo__component-application'   => $d['component_application'],
    ];
}

add_action( 'admin_post_av_guardar_articulo', 'av_guardar_articulo' );
function av_guardar_articulo() {

    if ( ! is_user_logged_in() || ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    check_admin_referer( 'av_articulo_nonce', 'nonce' );

    $redirect_to = av_articulos_page_url();
    $editing_id  = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

    if ( $editing_id ) {
        $redirect_to = add_query_arg( 'edit', $editing_id, $redirect_to );
    }

    $validated = av_articulo_validate_post( $editing_id );
    if ( ! $validated['ok'] ) {
        wp_redirect( add_query_arg( 'error', $validated['error'], $redirect_to ) );
        exit;
    }
    $d = $validated['data'];

    if ( $editing_id ) {

        if ( get_post_type( $editing_id ) !== 'cpt-articulos' ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        $before_price  = get_post_meta( $editing_id, 'cpt-articulo__sale-price', true );
        $before_status = get_post_meta( $editing_id, 'cpt-articulo__status', true );

        wp_update_post( [ 'ID' => $editing_id, 'post_title' => $d['nombre'] ] );

        foreach ( av_articulo_meta_input( $d ) as $key => $value ) {
            update_post_meta( $editing_id, $key, $value );
        }

        if ( $d['category'] ) {
            wp_set_object_terms( $editing_id, [ $d['category'] ], 'articulo_categoria', false );
        } else {
            wp_set_object_terms( $editing_id, [], 'articulo_categoria', false );
        }

        av_articulo_save_uploaded_photo( $editing_id );

        // Auditoría: solo se anota si de verdad cambió algo relevante, para
        // no llenar el histórico de ruido en cada guardado sin cambios reales.
        if ( $before_price !== '' && floatval( $before_price ) !== floatval( $d['sale_price'] ) ) {
            av_articulo_add_history( $editing_id, 'Precio de venta actualizado', 'price', number_format( floatval( $before_price ), 2, ',', '.' ) . ' € → ' . number_format( floatval( $d['sale_price'] ), 2, ',', '.' ) . ' €' );
        }
        if ( $before_status !== $d['status'] ) {
            av_articulo_add_history( $editing_id, $d['status'] === 'inactive' ? 'Artículo desactivado' : 'Artículo reactivado', 'status' );
        }

        wp_redirect( add_query_arg( 'actualizado', $editing_id, av_articulos_page_url() ) );
        exit;
    }

    // El SKU lo decide siempre el backend a partir de la categoría (y, si es
    // un componente electrónico, también del tipo de componente — ver
    // inc/articulos/sku.php), nunca lo que llegue en $_POST['sku'] (el campo
    // es de solo lectura en el formulario, pero el backend tampoco confía en
    // eso: simplemente no lo lee).
    $sku = av_articulo_generate_sku( $d['category'], $d['component_type'] );
    if ( is_wp_error( $sku ) ) {
        wp_redirect( add_query_arg( 'error', 'sku-generation-failed', $redirect_to ) );
        exit;
    }

    $nuevo_id = wp_insert_post( [
        'post_type'   => 'cpt-articulos',
        'post_title'  => $d['nombre'],
        'post_status' => 'publish',
        'meta_input'  => array_merge( av_articulo_meta_input( $d ), [ 'cpt-articulo__sku' => $sku ] ),
    ] );

    if ( is_wp_error( $nuevo_id ) || ! $nuevo_id ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    // El stock siempre nace de 0 y llega a su valor inicial mediante un
    // movimiento real (initial_stock): así el historial de cualquier
    // artículo, incluso el primero, explica de dónde sale cada unidad.
    update_post_meta( $nuevo_id, 'cpt-articulo__stock', 0 );
    if ( $d['category'] ) {
        wp_set_object_terms( $nuevo_id, [ $d['category'] ], 'articulo_categoria', false );
    }

    av_articulo_save_uploaded_photo( $nuevo_id );

    av_articulo_add_history( $nuevo_id, 'Artículo creado', 'creation', 'SKU asignado: ' . $sku );

    if ( $d['stock_inicial'] > 0 ) {
        av_articulo_registrar_movimiento( $nuevo_id, 'initial_stock', $d['stock_inicial'], 'Stock inicial al crear el artículo' );
    }

    wp_redirect( add_query_arg( 'creado', $nuevo_id, av_articulos_page_url() ) );
    exit;
}

// Baja lógica: nunca se borra físicamente un artículo (podría estar asociado
// a SATs/facturas históricos). "Eliminar" en la interfaz es desactivar.
add_action( 'admin_post_av_eliminar_articulo', 'av_eliminar_articulo' );
function av_eliminar_articulo() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $articulo_id = intval( $_GET['id'] ?? 0 );
    $redirect_to = av_articulos_page_url();

    check_admin_referer( 'av_eliminar_articulo_' . $articulo_id );

    if ( get_post_type( $articulo_id ) !== 'cpt-articulos' ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    update_post_meta( $articulo_id, 'cpt-articulo__status', 'inactive' );
    av_articulo_add_history( $articulo_id, 'Artículo desactivado', 'status' );

    wp_redirect( add_query_arg( 'desactivado', '1', $redirect_to ) );
    exit;
}

add_action( 'admin_post_av_reactivar_articulo', 'av_reactivar_articulo' );
function av_reactivar_articulo() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $articulo_id = intval( $_GET['id'] ?? 0 );
    $redirect_to = av_articulos_page_url();

    check_admin_referer( 'av_reactivar_articulo_' . $articulo_id );

    if ( get_post_type( $articulo_id ) !== 'cpt-articulos' ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    update_post_meta( $articulo_id, 'cpt-articulo__status', 'active' );
    av_articulo_add_history( $articulo_id, 'Artículo reactivado', 'status' );

    wp_redirect( add_query_arg( 'reactivado', '1', $redirect_to ) );
    exit;
}

// Alta rápida de categoría desde el propio formulario del artículo (botón "+"),
// igual que av_ajax_crear_categoria_device.
add_action( 'wp_ajax_av_ajax_crear_categoria_articulo', 'av_ajax_crear_categoria_articulo' );
function av_ajax_crear_categoria_articulo() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_send_json_error( 'Acceso denegado', 403 );
    }

    check_ajax_referer( 'av_articulo_nonce', 'nonce' );

    $nombre = isset( $_POST['nombre'] ) ? sanitize_text_field( $_POST['nombre'] ) : '';
    $prefix = isset( $_POST['prefix'] ) ? strtoupper( sanitize_text_field( $_POST['prefix'] ) ) : '';

    if ( $nombre === '' ) {
        wp_send_json_error( 'Indica un nombre para la categoría.', 400 );
    }

    // El prefijo es lo que hace que el SKU de esta categoría nunca choque con
    // el de otra (ver inc/articulos/sku.php): se exige aquí en vez de dejar
    // que quede vacío y descubrirlo más tarde al generar el primer SKU.
    if ( ! preg_match( '/^[A-Z]{2,6}$/', $prefix ) ) {
        wp_send_json_error( 'Indica un prefijo de 2 a 6 letras (p.ej. LCD, BAT, TOO…).', 400 );
    }

    if ( term_exists( $nombre, 'articulo_categoria' ) ) {
        wp_send_json_error( 'Ya existe una categoría con ese nombre.', 400 );
    }

    // Dos categorías con el mismo prefijo generarían SKU indistinguibles
    // entre sí (LCD-0001 de "Pantallas" y LCD-0001 de otra categoría con el
    // mismo prefijo serían, de hecho, el MISMO artículo a efectos de
    // unicidad) — se bloquea explícitamente.
    $existing_terms = get_terms( [ 'taxonomy' => 'articulo_categoria', 'hide_empty' => false ] );
    if ( ! is_wp_error( $existing_terms ) ) {
        foreach ( $existing_terms as $t ) {
            if ( av_articulo_categoria_get_prefix( $t->term_id ) === $prefix ) {
                wp_send_json_error( 'Ya existe una categoría con ese prefijo («' . $t->name . '»). Elige otro.', 400 );
            }
        }
    }

    $result = wp_insert_term( $nombre, 'articulo_categoria' );
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message(), 400 );
    }

    update_term_meta( $result['term_id'], 'av_articulo_order', av_articulo_categoria_next_order() );
    update_term_meta( $result['term_id'], 'article_category_prefix', $prefix );

    wp_send_json_success( [ 'id' => $result['term_id'], 'name' => $nombre, 'prefix' => $prefix ] );
}

// ─── Listado con filtros (clon de av_build_clients_query_args / av_ajax_filter_clients) ───

function av_build_articulos_query_args( array $params, $paged = 1 ) {

    $meta_query = [ 'relation' => 'AND' ];

    if ( ! empty( $params['nombre-articulo'] ) ) {
        // Nombre O SKU O EAN: se busca en el título por separado (no es meta).
        $meta_query[] = [
            'relation' => 'OR',
            [ 'key' => 'cpt-articulo__sku',     'value' => sanitize_text_field( $params['nombre-articulo'] ), 'compare' => 'LIKE' ],
            [ 'key' => 'cpt-articulo__barcode', 'value' => sanitize_text_field( $params['nombre-articulo'] ), 'compare' => 'LIKE' ],
            [ 'key' => 'cpt-articulo__brand',   'value' => sanitize_text_field( $params['nombre-articulo'] ), 'compare' => 'LIKE' ],
            [ 'key' => 'cpt-articulo__model',   'value' => sanitize_text_field( $params['nombre-articulo'] ), 'compare' => 'LIKE' ],
        ];
    }

    if ( ! empty( $params['marca-articulo'] ) ) {
        $meta_query[] = [ 'key' => 'cpt-articulo__brand', 'value' => sanitize_text_field( $params['marca-articulo'] ), 'compare' => 'LIKE' ];
    }

    if ( ! empty( $params['tipo-articulo'] ) ) {
        $meta_query[] = [ 'key' => 'cpt-articulo__article-type', 'value' => sanitize_text_field( $params['tipo-articulo'] ), 'compare' => '=' ];
    }

    if ( ! empty( $params['estado-articulo'] ) ) {
        $meta_query[] = [ 'key' => 'cpt-articulo__status', 'value' => sanitize_text_field( $params['estado-articulo'] ), 'compare' => '=' ];
    }

    if ( ! empty( $params['stock-articulo'] ) && $params['stock-articulo'] === 'sin-stock' ) {
        $meta_query[] = [ 'key' => 'cpt-articulo__stock', 'value' => '0', 'compare' => '=' ];
    }

    $args = [
        'post_type'      => 'cpt-articulos',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'paged'          => $paged,
    ];

    if ( ! empty( $params['categoria-articulo'] ) ) {
        $args['tax_query'] = [ [
            'taxonomy' => 'articulo_categoria',
            'field'    => 'term_id',
            'terms'    => intval( $params['categoria-articulo'] ),
        ] ];
    }

    if ( count( $meta_query ) > 1 ) {
        $args['meta_query'] = $meta_query;
    }

    // "Stock bajo" compara DOS metas del mismo post (stock <= stock_min), algo
    // que WP_Meta_Query no soporta entre sí. Se resuelve trayendo los ids que
    // cumplen la condición y filtrando por post__in, para no romper la
    // paginación (found_posts) que un filtrado en PHP tras la query sí rompería.
    if ( ! empty( $params['stock-articulo'] ) && $params['stock-articulo'] === 'bajo' ) {
        $args['post__in'] = av_articulos_resolve_low_stock_ids();
    }

    return $args;
}

function av_articulos_resolve_low_stock_ids() {
    $ids = get_posts( [
        'post_type'      => 'cpt-articulos',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );
    $bajos = [];
    foreach ( $ids as $id ) {
        $stock     = floatval( get_post_meta( $id, 'cpt-articulo__stock', true ) );
        $stock_min = floatval( get_post_meta( $id, 'cpt-articulo__stock-min', true ) );
        if ( $stock <= $stock_min ) $bajos[] = $id;
    }
    return $bajos ?: [ 0 ];
}

add_action( 'wp_ajax_av_ajax_filter_articulos', 'av_ajax_filter_articulos' );
function av_ajax_filter_articulos() {

    check_ajax_referer( 'av_articulos_filter_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
    }

    $paged = isset( $_REQUEST['paged'] ) ? max( 1, intval( $_REQUEST['paged'] ) ) : 1;
    $args  = av_build_articulos_query_args( $_REQUEST, $paged );

    $posts    = new WP_Query( $args );
    $articulos = $posts->posts;

    $page_posts = get_posts( [
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-articulos.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    $articulos_page_url = ! empty( $page_posts ) ? get_permalink( $page_posts[0] ) : home_url( '/' );
    $public_params = $_REQUEST;
    unset( $public_params['action'], $public_params['nonce'], $public_params['paged'] );
    $articulos_page_url = add_query_arg( $public_params, $articulos_page_url );

    ob_start();
    include locate_template( 'components/c-articulos-count.php' );
    $count_html = ob_get_clean();

    ob_start();
    include locate_template( 'components/c-articulos-list.php' );
    $list_html = ob_get_clean();

    wp_send_json_success( [ 'count' => $count_html, 'list' => $list_html ] );
}
