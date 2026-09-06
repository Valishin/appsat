<?php
// ── Módulo WhatsApp: configuración centralizada ─────────────────────────────
// Un único sitio para la versión de Graph API y el tenant activo, para que
// el resto del módulo nunca tenga que repetir el string de versión ni asumir
// "el taller actual" a mano.

if ( ! defined( 'ABSPATH' ) ) exit;

// Súbela aquí cuando Meta publique una versión nueva de Graph API: todo el
// módulo (envío, media, test de conexión) la toma de este único sitio.
define( 'AV_WHATSAPP_GRAPH_API_VERSION', 'v25.0' );

function av_whatsapp_graph_api_version() {
    return apply_filters( 'av_whatsapp_graph_api_version', AV_WHATSAPP_GRAPH_API_VERSION );
}

// Construye una URL completa de la Graph API a partir de una ruta relativa,
// p.ej. av_whatsapp_graph_api_url( $phone_number_id . '/messages' ).
function av_whatsapp_graph_api_url( $path ) {
    return 'https://graph.facebook.com/' . av_whatsapp_graph_api_version() . '/' . ltrim( $path, '/' );
}

// Tenant fijo mientras la app sea de un solo taller. Centralizado aquí para
// poder sustituirlo más adelante (usuario logueado, dominio, etc.) sin tener
// que tocar el resto del módulo de WhatsApp.
function av_whatsapp_current_tenant_id() {
    return apply_filters( 'av_whatsapp_current_tenant_id', 1 );
}

// URL de la página del inbox (Template WhatsApp). Independiente de
// av_tpl_url() de blocks/b-header.php: esa función no está garantizado que
// ya esté definida cuando se localiza el script en wp_enqueue_scripts.
function av_whatsapp_inbox_url() {
    $pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'templates/template-whatsapp.php', 'number' => 1 ] );
    return ! empty( $pages ) ? get_permalink( $pages[0]->ID ) : home_url( '/' );
}

// Logging propio del módulo: nunca debe recibir tokens/secretos como argumento,
// solo mensajes ya saneados (ver whatsapp-api.php).
function av_whatsapp_log( $level, $message ) {
    error_log( sprintf( '[WhatsApp][%s] %s', strtoupper( $level ), $message ) );
}
