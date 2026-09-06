<?php
// ── Módulo WhatsApp: modelo de datos ────────────────────────────────────────
// Tablas propias (no CPT): los mensajes son un log de alto volumen con
// necesidad de índices compuestos reales (idempotencia por whatsapp_message_id,
// paginación por conversación), algo para lo que wp_posts/postmeta no está
// pensado. Es la única parte del tema que usa tablas custom; el resto de la
// app sigue funcionando exclusivamente con CPTs/ACF, sin tocar nada de eso.

if ( ! defined( 'ABSPATH' ) ) exit;

// Súbela cada vez que cambie el esquema: la próxima carga de página aplicará
// la migración automáticamente (ver av_whatsapp_maybe_upgrade_db más abajo).
define( 'AV_WHATSAPP_DB_VERSION', '1.1.0' );

// Nombre de tabla completo (con prefijo de WP) para una tabla del módulo.
// Centralizado aquí para no repartir "wp_sat_whatsapp_..." a mano por el código.
function av_whatsapp_table( $name ) {
    global $wpdb;
    $allowed = [ 'accounts', 'conversations', 'messages' ];
    if ( ! in_array( $name, $allowed, true ) ) return '';
    return $wpdb->prefix . 'sat_whatsapp_' . $name;
}

add_action( 'init', 'av_whatsapp_maybe_upgrade_db' );
function av_whatsapp_maybe_upgrade_db() {
    if ( get_option( 'av_whatsapp_db_version' ) === AV_WHATSAPP_DB_VERSION ) {
        return;
    }
    av_whatsapp_install_or_upgrade_db();
}

function av_whatsapp_install_or_upgrade_db() {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset_collate = $wpdb->get_charset_collate();

    $accounts      = av_whatsapp_table( 'accounts' );
    $conversations = av_whatsapp_table( 'conversations' );
    $messages      = av_whatsapp_table( 'messages' );

    // Credenciales por número de WhatsApp conectado. Hoy siempre habrá una
    // sola fila (tenant_id = 1); el campo ya existe para cuando se conecten
    // números de otros talleres sin tener que rediseñar la tabla.
    $sql_accounts = "CREATE TABLE $accounts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        label VARCHAR(191) NOT NULL DEFAULT '',
        phone_number_id VARCHAR(64) NOT NULL DEFAULT '',
        whatsapp_business_account_id VARCHAR(64) NOT NULL DEFAULT '',
        display_phone_number VARCHAR(32) NOT NULL DEFAULT '',
        access_token_enc TEXT NULL,
        app_secret_enc TEXT NULL,
        verify_token_enc TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'disconnected',
        last_error TEXT NULL,
        connected_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY phone_number_id (phone_number_id),
        KEY tenant_id (tenant_id)
    ) $charset_collate;";

    // Una conversación por número de cliente y número de WhatsApp conectado.
    // customer_id/sat_id son opcionales: una conversación puede no tener
    // cliente asociado todavía (se asigna a mano desde el chat).
    $sql_conversations = "CREATE TABLE $conversations (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        whatsapp_account_id BIGINT UNSIGNED NOT NULL,
        customer_id BIGINT UNSIGNED NULL,
        sat_id BIGINT UNSIGNED NULL,
        phone_number VARCHAR(32) NOT NULL,
        contact_name VARCHAR(191) NOT NULL DEFAULT '',
        last_message_at DATETIME NULL,
        last_message_preview VARCHAR(191) NOT NULL DEFAULT '',
        unread_count INT UNSIGNED NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'open',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY account_phone (whatsapp_account_id, phone_number),
        KEY tenant_id (tenant_id),
        KEY customer_id (customer_id),
        KEY sat_id (sat_id),
        KEY last_message_at (last_message_at)
    ) $charset_collate;";

    // whatsapp_message_id es UNIQUE: si Meta reenvía un webhook o lo procesamos
    // dos veces, el segundo INSERT falla por duplicado en vez de crear un
    // mensaje repetido (idempotencia real, no solo "comprobar antes de insertar").
    // NULL se permite (mensajes salientes que fallan antes de tener ID de Meta);
    // MySQL no considera duplicados dos NULL en una clave UNIQUE.
    $sql_messages = "CREATE TABLE $messages (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        conversation_id BIGINT UNSIGNED NOT NULL,
        whatsapp_message_id VARCHAR(128) NULL,
        direction VARCHAR(10) NOT NULL,
        sender_phone VARCHAR(32) NULL,
        message_type VARCHAR(20) NOT NULL DEFAULT 'text',
        text_body TEXT NULL,
        media_id VARCHAR(128) NULL,
        media_attachment_id BIGINT UNSIGNED NULL,
        filename VARCHAR(191) NULL,
        mime_type VARCHAR(100) NULL,
        caption TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        error_message TEXT NULL,
        sent_by BIGINT UNSIGNED NULL,
        wa_timestamp DATETIME NULL,
        raw_payload LONGTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY whatsapp_message_id (whatsapp_message_id),
        KEY conversation_created (conversation_id, created_at),
        KEY status (status)
    ) $charset_collate;";

    dbDelta( $sql_accounts );
    dbDelta( $sql_conversations );
    dbDelta( $sql_messages );

    update_option( 'av_whatsapp_db_version', AV_WHATSAPP_DB_VERSION );
}
