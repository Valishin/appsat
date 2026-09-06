<?php
// ── Módulo WhatsApp: repositorio de conversaciones ──────────────────────────
// Toda lectura/escritura de wp_sat_whatsapp_conversations para el inbox pasa
// por aquí. Todas las consultas van acotadas por tenant_id (vía
// av_whatsapp_current_tenant_id(), Fase 3) — nunca "1" a mano.

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AV_WHATSAPP_CONVERSATIONS_PER_PAGE', 20 );

// Listado paginado con búsqueda/filtro. $args: filter(all|unread), search, page.
function av_whatsapp_list_conversations( $args = [] ) {
    global $wpdb;
    $table     = av_whatsapp_table( 'conversations' );
    $tenant_id = av_whatsapp_current_tenant_id();

    $filter   = ( $args['filter'] ?? 'all' ) === 'unread' ? 'unread' : 'all';
    $search   = trim( (string) ( $args['search'] ?? '' ) );
    $page     = max( 1, intval( $args['page'] ?? 1 ) );
    $per_page = AV_WHATSAPP_CONVERSATIONS_PER_PAGE;
    $offset   = ( $page - 1 ) * $per_page;

    $where_sql    = 'tenant_id = %d';
    $where_values = [ $tenant_id ];

    if ( $filter === 'unread' ) {
        $where_sql .= ' AND unread_count > 0';
    }

    if ( $search !== '' ) {
        $like = '%' . $wpdb->esc_like( $search ) . '%';

        $search_sql      = '(contact_name LIKE %s OR phone_number LIKE %s OR last_message_preview LIKE %s';
        $where_values[]  = $like;
        $where_values[]  = $like;
        $where_values[]  = $like;

        // "Cliente relacionado si es posible" (aprobado en Fase 5, punto 4): si el
        // término coincide con el nombre de algún cliente, también se incluyen sus
        // conversaciones aunque el texto no aparezca literal en contact_name/preview.
        $client_ids = av_whatsapp_find_client_ids_by_name( $search );
        if ( ! empty( $client_ids ) ) {
            $placeholders = implode( ',', array_fill( 0, count( $client_ids ), '%d' ) );
            $search_sql  .= " OR customer_id IN ($placeholders)";
            foreach ( $client_ids as $cid ) $where_values[] = $cid;
        }
        $search_sql .= ')';

        $where_sql .= ' AND ' . $search_sql;
    }

    $total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $where_sql", $where_values ) );

    $query_values = array_merge( $where_values, [ $per_page, $offset ] );
    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table WHERE $where_sql ORDER BY last_message_at DESC, id DESC LIMIT %d OFFSET %d",
        $query_values
    ), ARRAY_A );

    return [
        'items'       => array_map( 'av_whatsapp_shape_conversation_summary', $rows ?: [] ),
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $per_page,
        'total_pages' => $per_page > 0 ? (int) ceil( $total / $per_page ) : 0,
    ];
}

// Conversación completa (con cliente/SAT resueltos), acotada al tenant actual.
// Devuelve null si no existe O si pertenece a otro tenant — deliberadamente el
// mismo resultado en ambos casos (no se distingue "no existe" de "no es tuya").
function av_whatsapp_get_conversation( $id ) {
    global $wpdb;
    $table     = av_whatsapp_table( 'conversations' );
    $tenant_id = av_whatsapp_current_tenant_id();

    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND tenant_id = %d", $id, $tenant_id
    ), ARRAY_A );

    if ( ! $row ) return null;

    $customer = null;
    if ( ! empty( $row['customer_id'] ) ) {
        $cid = (int) $row['customer_id'];
        if ( get_post_type( $cid ) === 'cpt-clients' ) {
            $customer = [
                'id'   => $cid,
                'name' => get_field( 'cpt-client__name', $cid ) ?: get_the_title( $cid ),
                'url'  => get_permalink( $cid ),
            ];
        }
    }

    $sat = null;
    if ( ! empty( $row['sat_id'] ) ) {
        $sid = (int) $row['sat_id'];
        if ( get_post_type( $sid ) === 'cpt-sats' ) {
            $sat = [
                'id'             => $sid,
                'sat_id_visible' => get_post_meta( $sid, 'cpt-sat__sat-id', true ),
                'label'          => av_whatsapp_build_sat_label( $sid ),
                'status'         => get_post_meta( $sid, 'cpt-sat__status', true ),
                'url'            => get_permalink( $sid ),
            ];
        }
    }

    return [
        'id'                   => (int) $row['id'],
        'contact_name'         => $row['contact_name'],
        'phone_number'         => $row['phone_number'],
        'customer'             => $customer,
        'sat'                  => $sat,
        'unread_count'         => (int) $row['unread_count'],
        'status'               => $row['status'],
        'last_message_at'      => $row['last_message_at'],
        'last_message_preview' => $row['last_message_preview'],
        'created_at'           => $row['created_at'],
    ];
}

// Marca la conversación como leída EN NUESTRO INBOX (unread_count = 0). No
// manda "read" a Meta — eso, si se implementa, será una fase posterior aparte.
function av_whatsapp_mark_conversation_read( $id ) {
    global $wpdb;
    $table     = av_whatsapp_table( 'conversations' );
    $tenant_id = av_whatsapp_current_tenant_id();

    return $wpdb->update( $table, [ 'unread_count' => 0 ], [ 'id' => $id, 'tenant_id' => $tenant_id ] ) !== false;
}

// Para el badge del menú y el polling: nº de conversaciones con mensajes sin
// leer, y la fecha de actividad más reciente (para que el polling sepa si algo
// ha cambiado sin tener que volver a traer todo el listado en cada tick).
function av_whatsapp_get_unread_summary() {
    global $wpdb;
    $table     = av_whatsapp_table( 'conversations' );
    $tenant_id = av_whatsapp_current_tenant_id();

    $unread        = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE tenant_id = %d AND unread_count > 0", $tenant_id ) );
    $last_activity = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(updated_at) FROM $table WHERE tenant_id = %d", $tenant_id ) );

    return [ 'unread_conversations' => $unread, 'last_activity_at' => $last_activity ?: '' ];
}

// Búsqueda de conversación por teléfono normalizado (lectura, sin escribir
// nada): la usan el botón de WhatsApp en la ficha del cliente y la sección del
// SAT para saber si ya existe conversación. NO es la asociación automática
// completa (eso es la Fase 8: escribir customer_id, resolver ambigüedades...),
// solo una consulta de solo lectura.
function av_whatsapp_find_conversation_by_phone( $phone_normalized ) {
    if ( $phone_normalized === '' ) return null;

    global $wpdb;
    $table     = av_whatsapp_table( 'conversations' );
    $tenant_id = av_whatsapp_current_tenant_id();

    $id = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM $table WHERE tenant_id = %d AND phone_number = %s ORDER BY last_message_at DESC LIMIT 1",
        $tenant_id, $phone_normalized
    ) );

    return $id ? (int) $id : null;
}

function av_whatsapp_get_client_normalized_phone( $client_id ) {
    $ext   = get_field( 'cpt-client__extension', $client_id );
    $phone = get_field( 'cpt-client__phone', $client_id );
    return av_whatsapp_normalize_phone( $phone, $ext );
}

// ── Helpers de presentación ─────────────────────────────────────────────
function av_whatsapp_find_client_ids_by_name( $search ) {
    $search = trim( (string) $search );
    if ( $search === '' ) return [];

    return get_posts( [
        'post_type'      => 'cpt-clients',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        's'              => $search,
        'fields'         => 'ids',
    ] );
}

function av_whatsapp_build_sat_label( $sat_id ) {
    $sat_num        = get_post_meta( $sat_id, 'cpt-sat__sat-id', true );
    $type_equipment = get_post_meta( $sat_id, 'cpt-sat__type-equipment', true );
    $model          = get_post_meta( $sat_id, 'cpt-sat__model', true );

    $equipment_labels = [
        'pc' => 'PC Torre', 'portatil' => 'Portátil', 'tablet' => 'Tablet',
        'movil' => 'Móvil', 'impresora' => 'Impresora', 'tv' => 'TV',
        'consola' => 'Consola', 'mando' => 'Mando', 'otro' => 'Otro',
    ];
    $type_label = $equipment_labels[ $type_equipment ] ?? $type_equipment;

    $label = trim( implode( ' ', array_filter( [ $type_label, $model ] ) ) );

    return $sat_num ? ( '#' . $sat_num . ( $label ? " · $label" : '' ) ) : $label;
}

function av_whatsapp_shape_conversation_summary( array $row ) {
    $customer_name = '';
    if ( ! empty( $row['customer_id'] ) ) {
        $cid = (int) $row['customer_id'];
        $customer_name = get_field( 'cpt-client__name', $cid ) ?: get_the_title( $cid );
    }

    $sat_label = ! empty( $row['sat_id'] ) ? av_whatsapp_build_sat_label( (int) $row['sat_id'] ) : '';

    return [
        'id'                   => (int) $row['id'],
        'contact_name'         => $row['contact_name'],
        'phone_number'         => $row['phone_number'],
        'customer_id'          => $row['customer_id'] ? (int) $row['customer_id'] : null,
        'customer_name'        => $customer_name,
        'sat_id'               => $row['sat_id'] ? (int) $row['sat_id'] : null,
        'sat_label'            => $sat_label,
        'last_message_at'      => $row['last_message_at'],
        'last_message_preview' => $row['last_message_preview'],
        'unread_count'         => (int) $row['unread_count'],
        'status'               => $row['status'],
    ];
}
