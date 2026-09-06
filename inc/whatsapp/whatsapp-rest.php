<?php
// ── Módulo WhatsApp: endpoints REST internos ────────────────────────────────
// Primer uso de la REST API de WP en este tema — exclusivo para el módulo de
// WhatsApp (el resto de la app sigue con admin-post.php/admin-ajax.php tal
// cual). Cada ruta exige sesión + rol de administrador; nunca se confía en
// el frontend para permisos.

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', 'av_whatsapp_register_rest_routes' );
function av_whatsapp_register_rest_routes() {
    register_rest_route( 'sat/v1', '/whatsapp/settings/test-connection', [
        'methods'             => 'POST',
        'callback'            => 'av_whatsapp_rest_test_connection',
        'permission_callback' => 'av_whatsapp_rest_require_admin',
    ] );

    // Webhook de Meta: público por naturaleza (Meta no manda sesión ni nonce
    // de WordPress). La autorización se hace DENTRO de cada callback con el
    // mecanismo propio de Meta (verify_token / firma X-Hub-Signature-256),
    // no con el sistema de permisos de la REST API.
    register_rest_route( 'sat/v1', '/whatsapp/webhook', [
        'methods'             => 'GET',
        'callback'            => 'av_whatsapp_rest_webhook_verify',
        'permission_callback' => '__return_true',
    ] );

    register_rest_route( 'sat/v1', '/whatsapp/webhook', [
        'methods'             => 'POST',
        'callback'            => 'av_whatsapp_rest_webhook_receive',
        'permission_callback' => '__return_true',
    ] );
}

// Autorización backend real: sesión válida + rol administrador. El nonce
// (X-WP-Nonce) lo exige el propio núcleo de WP para peticiones autenticadas
// por cookie a wp-json (protección CSRF de la REST API).
function av_whatsapp_rest_require_admin( WP_REST_Request $request ) {
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'av_whatsapp_forbidden', 'No autorizado.', [ 'status' => 401 ] );
    }
    if ( ! current_user_can( 'administrator' ) ) {
        return new WP_Error( 'av_whatsapp_forbidden', 'No tienes permiso para esta acción.', [ 'status' => 403 ] );
    }
    return true;
}

// La respuesta es siempre HTTP 200: "conexión fallida" es un resultado de
// negocio (success:false + message), no un error de la petición en sí.
function av_whatsapp_rest_test_connection( WP_REST_Request $request ) {
    $result = av_whatsapp_test_connection();
    return new WP_REST_Response( $result, 200 );
}
