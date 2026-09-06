<?php
// ── Módulo WhatsApp: repositorio de mensajes (lectura para el inbox) ───────
// La comprobación de que la conversación pertenece al tenant actual se hace
// ANTES de llamar a estas funciones (en el endpoint REST, vía
// av_whatsapp_get_conversation()) — aquí ya se asume conversation_id válido.

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AV_WHATSAPP_MESSAGES_PER_PAGE', 30 );

// Paginación por cursor (before_id), no por número de página: en una lista que
// crece por el final (mensajes nuevos), la paginación por página se desincroniza;
// con un cursor de ID siempre se pide "lo anterior a X", que es estable.
function av_whatsapp_list_messages( $conversation_id, $args = [] ) {
    global $wpdb;
    $table = av_whatsapp_table( 'messages' );

    $limit     = min( 100, max( 1, intval( $args['limit'] ?? AV_WHATSAPP_MESSAGES_PER_PAGE ) ) );
    $before_id = isset( $args['before_id'] ) ? max( 0, intval( $args['before_id'] ) ) : 0;

    $where_sql = 'conversation_id = %d';
    $values    = [ $conversation_id ];

    if ( $before_id > 0 ) {
        $where_sql .= ' AND id < %d';
        $values[]   = $before_id;
    }

    // Se piden limit+1 para saber si hay más sin una segunda consulta.
    $values[] = $limit + 1;
    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table WHERE $where_sql ORDER BY id DESC LIMIT %d",
        $values
    ), ARRAY_A );

    $rows     = $rows ?: [];
    $has_more = count( $rows ) > $limit;
    if ( $has_more ) array_pop( $rows );

    // Se devuelven en orden ascendente (más antiguo primero) para pintar de
    // arriba a abajo, como cualquier chat.
    $rows = array_reverse( $rows );

    return [
        'messages' => array_map( 'av_whatsapp_shape_message', $rows ),
        'has_more' => $has_more,
    ];
}

// Solo los mensajes con id > since_id (para el polling: "qué hay nuevo desde
// lo último que ya tengo pintado"), en orden ascendente.
function av_whatsapp_list_new_messages( $conversation_id, $since_id ) {
    global $wpdb;
    $table = av_whatsapp_table( 'messages' );

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table WHERE conversation_id = %d AND id > %d ORDER BY id ASC LIMIT 100",
        $conversation_id, max( 0, intval( $since_id ) )
    ), ARRAY_A );

    return array_map( 'av_whatsapp_shape_message', $rows ?: [] );
}

// Forma segura para REST: nunca incluye raw_payload, ni nada de la cuenta
// (access_token/app_secret/verify_token) — esta función ni siquiera toca esa
// tabla, así que no hay manera de que se filtren por aquí.
function av_whatsapp_shape_message( array $row ) {
    $media_url = null;
    if ( ! empty( $row['media_attachment_id'] ) ) {
        $url = wp_get_attachment_url( (int) $row['media_attachment_id'] );
        if ( $url ) $media_url = $url;
    }

    return [
        'id'                  => (int) $row['id'],
        'whatsapp_message_id' => $row['whatsapp_message_id'],
        'direction'           => $row['direction'],
        'message_type'        => $row['message_type'],
        'text_body'           => $row['text_body'],
        'caption'             => $row['caption'],
        'filename'            => $row['filename'],
        'mime_type'           => $row['mime_type'],
        'media_url'           => $media_url,
        'status'              => $row['status'],
        // El estado de envío/error solo tiene sentido para mensajes salientes.
        'error_message'       => $row['direction'] === 'outbound' ? $row['error_message'] : null,
        'wa_timestamp'        => $row['wa_timestamp'],
        'created_at'          => $row['created_at'],
    ];
}
