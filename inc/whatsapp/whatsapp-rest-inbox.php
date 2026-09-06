<?php
// ── Módulo WhatsApp: endpoints REST del inbox (Fase 5, solo lectura) ───────
// Ninguno de estos endpoints envía mensajes: eso es la Fase 6. Todos exigen
// sesión + rol autorizado (administrador o técnico), y todas las consultas
// van acotadas al tenant actual — nunca se confía en el frontend.

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', 'av_whatsapp_register_inbox_rest_routes' );
function av_whatsapp_register_inbox_rest_routes() {

    register_rest_route( 'sat/v1', '/whatsapp/conversations', [
        'methods'             => 'GET',
        'callback'            => 'av_whatsapp_rest_list_conversations',
        'permission_callback' => 'av_whatsapp_rest_require_staff',
        'args'                => [
            'filter' => [ 'type' => 'string', 'default' => 'all' ],
            'search' => [ 'type' => 'string', 'default' => '' ],
            'page'   => [ 'type' => 'integer', 'default' => 1, 'sanitize_callback' => 'absint' ],
        ],
    ] );

    register_rest_route( 'sat/v1', '/whatsapp/conversations/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'av_whatsapp_rest_get_conversation',
        'permission_callback' => 'av_whatsapp_rest_require_staff',
        'args'                => [
            'id' => [ 'validate_callback' => 'is_numeric', 'sanitize_callback' => 'absint' ],
        ],
    ] );

    register_rest_route( 'sat/v1', '/whatsapp/conversations/(?P<id>\d+)/messages', [
        'methods'             => 'GET',
        'callback'            => 'av_whatsapp_rest_list_messages',
        'permission_callback' => 'av_whatsapp_rest_require_staff',
        'args'                => [
            'id'        => [ 'validate_callback' => 'is_numeric', 'sanitize_callback' => 'absint' ],
            'before_id' => [ 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ],
            'since_id'  => [ 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ],
            'limit'     => [ 'type' => 'integer', 'default' => 30, 'sanitize_callback' => 'absint' ],
        ],
    ] );

    register_rest_route( 'sat/v1', '/whatsapp/conversations/(?P<id>\d+)/read', [
        'methods'             => 'POST',
        'callback'            => 'av_whatsapp_rest_mark_conversation_read',
        'permission_callback' => 'av_whatsapp_rest_require_staff',
        'args'                => [
            'id' => [ 'validate_callback' => 'is_numeric', 'sanitize_callback' => 'absint' ],
        ],
    ] );

    register_rest_route( 'sat/v1', '/whatsapp/unread-count', [
        'methods'             => 'GET',
        'callback'            => 'av_whatsapp_rest_unread_count',
        'permission_callback' => 'av_whatsapp_rest_require_staff',
    ] );

    register_rest_route( 'sat/v1', '/whatsapp/find-conversation', [
        'methods'             => 'GET',
        'callback'            => 'av_whatsapp_rest_find_conversation',
        'permission_callback' => 'av_whatsapp_rest_require_staff',
        'args'                => [
            'client_id' => [ 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ],
            'sat_id'    => [ 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ],
        ],
    ] );
}

// Admin y técnico (editor) pueden ver el inbox — igual que el resto de la app
// (current_user_can('edit_posts') es la comprobación de "técnico" ya usada en
// todo el tema). Sesión inválida -> 401; rol insuficiente -> 403.
function av_whatsapp_rest_require_staff( WP_REST_Request $request ) {
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'av_whatsapp_forbidden', 'No autorizado.', [ 'status' => 401 ] );
    }
    if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'edit_posts' ) ) {
        return new WP_Error( 'av_whatsapp_forbidden', 'No tienes permiso para esta acción.', [ 'status' => 403 ] );
    }
    return true;
}

function av_whatsapp_rest_list_conversations( WP_REST_Request $request ) {
    $result = av_whatsapp_list_conversations( [
        'filter' => $request->get_param( 'filter' ),
        'search' => $request->get_param( 'search' ),
        'page'   => $request->get_param( 'page' ),
    ] );
    return new WP_REST_Response( $result, 200 );
}

function av_whatsapp_rest_get_conversation( WP_REST_Request $request ) {
    $conversation = av_whatsapp_get_conversation( (int) $request->get_param( 'id' ) );
    if ( ! $conversation ) {
        return new WP_Error( 'av_whatsapp_not_found', 'Conversación no encontrada.', [ 'status' => 404 ] );
    }
    return new WP_REST_Response( $conversation, 200 );
}

function av_whatsapp_rest_list_messages( WP_REST_Request $request ) {
    $conversation_id = (int) $request->get_param( 'id' );

    // La pertenencia al tenant se comprueba SIEMPRE a través de
    // av_whatsapp_get_conversation() antes de tocar ningún mensaje: si la
    // conversación no es de este tenant (o no existe), aquí ya no se llega.
    if ( ! av_whatsapp_get_conversation( $conversation_id ) ) {
        return new WP_Error( 'av_whatsapp_not_found', 'Conversación no encontrada.', [ 'status' => 404 ] );
    }

    $since_id = (int) $request->get_param( 'since_id' );
    if ( $since_id > 0 ) {
        return new WP_REST_Response( [
            'messages' => av_whatsapp_list_new_messages( $conversation_id, $since_id ),
            'has_more' => false,
        ], 200 );
    }

    $result = av_whatsapp_list_messages( $conversation_id, [
        'before_id' => $request->get_param( 'before_id' ),
        'limit'     => $request->get_param( 'limit' ),
    ] );
    return new WP_REST_Response( $result, 200 );
}

function av_whatsapp_rest_mark_conversation_read( WP_REST_Request $request ) {
    $conversation_id = (int) $request->get_param( 'id' );

    if ( ! av_whatsapp_get_conversation( $conversation_id ) ) {
        return new WP_Error( 'av_whatsapp_not_found', 'Conversación no encontrada.', [ 'status' => 404 ] );
    }

    av_whatsapp_mark_conversation_read( $conversation_id );
    return new WP_REST_Response( [ 'success' => true ], 200 );
}

function av_whatsapp_rest_unread_count( WP_REST_Request $request ) {
    return new WP_REST_Response( av_whatsapp_get_unread_summary(), 200 );
}

// Usado por el botón "WhatsApp" de la ficha de cliente y la sección del SAT:
// busca (sin crear nada) si ya existe una conversación para ese cliente/SAT.
function av_whatsapp_rest_find_conversation( WP_REST_Request $request ) {
    $client_id = (int) $request->get_param( 'client_id' );
    $sat_id    = (int) $request->get_param( 'sat_id' );

    if ( $sat_id > 0 && get_post_type( $sat_id ) === 'cpt-sats' ) {
        $client_id = (int) get_post_meta( $sat_id, 'cpt-sat__client-id', true );
    }

    if ( ! $client_id || get_post_type( $client_id ) !== 'cpt-clients' ) {
        return new WP_REST_Response( [ 'conversation_id' => null ], 200 );
    }

    $phone = av_whatsapp_get_client_normalized_phone( $client_id );
    $conversation_id = av_whatsapp_find_conversation_by_phone( $phone );

    return new WP_REST_Response( [ 'conversation_id' => $conversation_id ], 200 );
}
