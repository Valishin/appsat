<?php
// ─── Líneas de "Artículos y servicios" del SAT (cpt-sat__lines) ─────────────
// Sustituye a los antiguos widgets de texto libre "Reparación"
// (cpt-sat__repair) y "Piezas pedidas" (cpt-sat__ordered-parts) por la MISMA
// tabla de líneas que Pedidos/Presupuestos (ver components/c-pedido-lines.php/
// av_pedido_lines_widget() en src/js/app.js), sin la columna "Recepción" (no
// aplica al SAT) y con el buscador combinado Artículos + Servicios que ya usa
// Presupuestos (av_presupuesto_lines_widget()). Reutiliza tal cual
// av_line_amounts() y av_pedido_vat_choices() (inc/pedidos/crud.php): son
// utilidades genéricas, no específicas de Pedidos — ya reutilizadas también
// por Presupuestos (inc/presupuestos/pdf.php).
//
// NO toca "Artículos usados" (cpt-sat__articulos-usados, ver
// inc/articulos/sat-integration.php): ese widget descuenta stock real y tiene
// su propio mecanismo de idempotencia — se deja tal cual, sin mezclarlo con
// esta tabla, que es puramente informativa/de facturación.
//
// Forma de cada línea ya saneada (igual que Pedidos, sin received_date):
//   { article_id, name_snapshot, sku_snapshot, description, quantity,
//     sale_price, vat, supplier }
function av_sat_sanitize_lines( $raw ) {

    if ( empty( $raw ) ) return '';

    $items = is_string( $raw ) ? json_decode( wp_unslash( $raw ), true ) : $raw;
    if ( ! is_array( $items ) ) return '';

    $vat_choices = array_keys( av_pedido_vat_choices() );

    $clean = [];
    foreach ( $items as $it ) {
        if ( ! is_array( $it ) ) continue;

        // article_id: si viene y es un artículo real, se usa; si no, la línea
        // sigue siendo válida como línea suelta (artículo no catalogado o
        // servicio), con article_id null.
        $article_id = ! empty( $it['article_id'] ) ? intval( $it['article_id'] ) : null;
        if ( $article_id && get_post_type( $article_id ) !== 'cpt-articulos' ) {
            $article_id = null;
        }

        $name_snapshot = sanitize_text_field( $it['name_snapshot'] ?? '' );
        $sku_snapshot  = sanitize_text_field( $it['sku_snapshot'] ?? '' );

        if ( $article_id ) {
            if ( $name_snapshot === '' ) $name_snapshot = get_the_title( $article_id );
            if ( $sku_snapshot === '' )  $sku_snapshot  = get_post_meta( $article_id, 'cpt-articulo__sku', true );
        }

        if ( $article_id === null && $name_snapshot === '' ) continue;

        $quantity = isset( $it['quantity'] ) ? floatval( $it['quantity'] ) : 1;
        if ( $quantity <= 0 ) $quantity = 1;

        $sale_price = isset( $it['sale_price'] ) ? max( 0, round( floatval( $it['sale_price'] ), 6 ) ) : 0;
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
        ];
    }

    return ! empty( $clean ) ? wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) : '';
}

function av_sat_get_lines( $sat_id ) {
    $raw   = $sat_id ? get_post_meta( $sat_id, 'cpt-sat__lines', true ) : '';
    $items = $raw ? json_decode( $raw, true ) : [];
    return is_array( $items ) ? $items : [];
}

// Compatibilidad con SATs creados antes de esta tabla: si el SAT todavía no
// tiene cpt-sat__lines (nunca se ha vuelto a guardar con el formulario
// nuevo), se reconstruye AL VUELO a partir de las dos fuentes antiguas —
// "Reparación" y "Piezas pedidas" (mismo formato JSON {text, price} las
// dos) — sin tocar la base de datos: es solo para que el técnico las vea ya
// en la tabla nueva al abrir el SAT. En cuanto se guarde una vez, cpt-sat__lines
// queda como fuente real y esta reconstrucción ya no hace falta para ese SAT.
function av_sat_lines_for_display( $sat_id ) {
    if ( ! $sat_id ) return '';

    $existing = get_post_meta( $sat_id, 'cpt-sat__lines', true );
    if ( $existing !== '' ) return $existing;

    $legacy = [];
    $add_legacy_items = function( $raw ) use ( &$legacy ) {
        $items = json_decode( (string) $raw, true );
        if ( ! is_array( $items ) ) return;
        foreach ( $items as $it ) {
            $text = trim( (string) ( $it['text'] ?? '' ) );
            if ( $text === '' ) continue;
            $price_raw = trim( (string) ( $it['price'] ?? '' ) );
            $gross     = $price_raw !== '' ? floatval( str_replace( ',', '.', $price_raw ) ) : 0;
            // Los precios antiguos de "Reparación"/"Piezas pedidas" eran un
            // único importe CON IVA, sin tipo propio: se asume el 21% de
            // siempre para poder mostrarlos ya en la tabla nueva.
            $vat = 21;
            $net = $gross > 0 ? round( $gross / ( 1 + $vat / 100 ), 6 ) : 0;
            $legacy[] = [
                'article_id'    => null,
                'name_snapshot' => $text,
                'sku_snapshot'  => '',
                'description'   => '',
                'quantity'      => 1,
                'sale_price'    => $net,
                'vat'           => $vat,
                'supplier'      => '',
            ];
        }
    };
    $add_legacy_items( get_post_meta( $sat_id, 'cpt-sat__repair', true ) );
    $add_legacy_items( get_post_meta( $sat_id, 'cpt-sat__ordered-parts', true ) );

    return ! empty( $legacy ) ? wp_json_encode( $legacy, JSON_UNESCAPED_UNICODE ) : '';
}
