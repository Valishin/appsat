<?php
// ── Módulo WhatsApp: cuenta/credenciales (wp_sat_whatsapp_accounts) ────────
// Toda lectura/escritura de la fila de credenciales pasa por aquí. Ningún
// otro archivo debe hacer SELECT/UPDATE directo sobre esta tabla.

if ( ! defined( 'ABSPATH' ) ) exit;

// Fila completa CON credenciales descifradas. Uso exclusivo interno para
// llamadas reales a la API (whatsapp-api.php) — nunca se expone tal cual
// a una plantilla, a REST ni a JS.
function av_whatsapp_get_account( $tenant_id = null ) {
    global $wpdb;
    $tenant_id = $tenant_id ?? av_whatsapp_current_tenant_id();
    $table     = av_whatsapp_table( 'accounts' );

    $row = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM $table WHERE tenant_id = %d ORDER BY id ASC LIMIT 1", $tenant_id ),
        ARRAY_A
    );
    if ( ! $row ) return null;

    $row['access_token'] = av_whatsapp_decrypt( $row['access_token_enc'] ?? '' );
    $row['app_secret']   = av_whatsapp_decrypt( $row['app_secret_enc'] ?? '' );
    $row['verify_token'] = av_whatsapp_decrypt( $row['verify_token_enc'] ?? '' );
    unset( $row['access_token_enc'], $row['app_secret_enc'], $row['verify_token_enc'] );

    return $row;
}

// Cuenta identificada por el Phone Number ID que Meta manda en el webhook
// (metadata.phone_number_id). Con credenciales descifradas — uso interno,
// nunca se expone tal cual al webhook ni a REST/JS.
function av_whatsapp_get_account_by_phone_number_id( $phone_number_id ) {
    global $wpdb;
    $table = av_whatsapp_table( 'accounts' );

    $row = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM $table WHERE phone_number_id = %s LIMIT 1", $phone_number_id ),
        ARRAY_A
    );
    if ( ! $row ) return null;

    $row['access_token'] = av_whatsapp_decrypt( $row['access_token_enc'] ?? '' );
    $row['app_secret']   = av_whatsapp_decrypt( $row['app_secret_enc'] ?? '' );
    $row['verify_token'] = av_whatsapp_decrypt( $row['verify_token_enc'] ?? '' );
    unset( $row['access_token_enc'], $row['app_secret_enc'], $row['verify_token_enc'] );

    return $row;
}

// Todas las cuentas configuradas, con secretos descifrados. Uso exclusivo
// interno del webhook: la verificación GET y la firma POST no saben de
// antemano a qué cuenta pertenece la petición, así que se prueban todas las
// que haya (hoy 1, mañana una por taller conectado).
function av_whatsapp_get_all_accounts() {
    global $wpdb;
    $table = av_whatsapp_table( 'accounts' );

    $rows = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A );
    if ( ! $rows ) return [];

    return array_map( function ( $row ) {
        $row['access_token'] = av_whatsapp_decrypt( $row['access_token_enc'] ?? '' );
        $row['app_secret']   = av_whatsapp_decrypt( $row['app_secret_enc'] ?? '' );
        $row['verify_token'] = av_whatsapp_decrypt( $row['verify_token_enc'] ?? '' );
        unset( $row['access_token_enc'], $row['app_secret_enc'], $row['verify_token_enc'] );
        return $row;
    }, $rows );
}

// Versión SEGURA para pantalla/REST: jamás incluye un secreto, solo si está
// relleno o no ("Configurado ✓" se decide con has_access_token, etc.).
function av_whatsapp_get_account_public( $tenant_id = null ) {
    $account = av_whatsapp_get_account( $tenant_id );
    if ( ! $account ) {
        return [
            'id' => 0, 'label' => '', 'phone_number_id' => '', 'whatsapp_business_account_id' => '',
            'display_phone_number' => '', 'status' => 'disconnected', 'last_error' => '', 'connected_at' => '',
            'has_access_token' => false, 'has_app_secret' => false, 'has_verify_token' => false,
        ];
    }

    return [
        'id'                           => (int) $account['id'],
        'label'                        => $account['label'],
        'phone_number_id'              => $account['phone_number_id'],
        'whatsapp_business_account_id' => $account['whatsapp_business_account_id'],
        'display_phone_number'         => $account['display_phone_number'],
        'status'                       => $account['status'],
        'last_error'                   => $account['last_error'],
        'connected_at'                 => $account['connected_at'],
        'has_access_token'             => $account['access_token'] !== '',
        'has_app_secret'               => $account['app_secret'] !== '',
        'has_verify_token'             => $account['verify_token'] !== '',
    ];
}

// ── Validaciones ─────────────────────────────────────────────────────────
// Todos los campos "identificador" de Meta son numéricos (IDs de Graph API).
function av_whatsapp_validate_meta_id( $value ) {
    $value = trim( (string) $value );
    if ( $value === '' ) return true; // opcional: se puede dejar sin rellenar
    return (bool) preg_match( '/^\d{5,32}$/', $value );
}

function av_whatsapp_validate_display_phone( $value ) {
    $value = trim( (string) $value );
    if ( $value === '' ) return true;
    $digits = preg_replace( '/\D/', '', $value );
    return strlen( $digits ) >= 8 && strlen( $digits ) <= 15; // E.164: máx. 15 dígitos
}

function av_whatsapp_validate_secret_length( $value ) {
    $value = trim( (string) $value );
    if ( $value === '' ) return true; // vacío = "no cambiar" (campo de sustitución)
    return strlen( $value ) >= 20 && strlen( $value ) <= 4096;
}

// ── Guardado desde la pestaña Configuración → WhatsApp ─────────────────────
// Mismo patrón que av_process_invoice_config_save(): POST-redirect-GET,
// llamado desde el template antes de get_header().
function av_whatsapp_process_config_save( $permalink ) {
    if ( empty( $_POST['av_whatsapp_config_save'] ) ) return false;

    // Defensa en profundidad: la plantilla ya bloquea a no-administradores,
    // pero este guardado no debe depender solo de eso.
    if ( ! is_user_logged_in() || ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }
    if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'av_whatsapp_config' ) ) {
        wp_die( 'Nonce inválido.' );
    }

    $label           = sanitize_text_field( wp_unslash( $_POST['wa_label'] ?? '' ) );
    $phone_number_id = sanitize_text_field( wp_unslash( $_POST['wa_phone_number_id'] ?? '' ) );
    $waba_id         = sanitize_text_field( wp_unslash( $_POST['wa_waba_id'] ?? '' ) );
    $display_phone   = sanitize_text_field( wp_unslash( $_POST['wa_display_phone'] ?? '' ) );
    $access_token    = trim( (string) wp_unslash( $_POST['wa_access_token'] ?? '' ) );
    $app_secret      = trim( (string) wp_unslash( $_POST['wa_app_secret'] ?? '' ) );
    $verify_token    = trim( (string) wp_unslash( $_POST['wa_verify_token'] ?? '' ) );

    $errors = [];
    if ( ! av_whatsapp_validate_meta_id( $phone_number_id ) )    $errors[] = 'Phone Number ID no válido (solo dígitos).';
    if ( ! av_whatsapp_validate_meta_id( $waba_id ) )            $errors[] = 'WhatsApp Business Account ID no válido (solo dígitos).';
    if ( ! av_whatsapp_validate_display_phone( $display_phone ) ) $errors[] = 'Número visible no válido.';
    if ( ! av_whatsapp_validate_secret_length( $access_token ) ) $errors[] = 'Access Token con longitud no válida.';
    if ( ! av_whatsapp_validate_secret_length( $app_secret ) )   $errors[] = 'App Secret con longitud no válida.';
    if ( ! av_whatsapp_validate_secret_length( $verify_token ) ) $errors[] = 'Verify Token con longitud no válida.';

    if ( $errors ) {
        set_transient( 'av_whatsapp_config_errors_' . get_current_user_id(), $errors, 60 );
        wp_safe_redirect( add_query_arg( [ 'tab' => 'whatsapp', 'wa_error' => '1' ], $permalink ) );
        exit;
    }

    global $wpdb;
    $table     = av_whatsapp_table( 'accounts' );
    $tenant_id = av_whatsapp_current_tenant_id();

    $existing = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, access_token_enc, app_secret_enc, verify_token_enc FROM $table WHERE tenant_id = %d ORDER BY id ASC LIMIT 1",
        $tenant_id
    ) );

    $data = [
        'tenant_id'                    => $tenant_id,
        'label'                        => $label,
        'phone_number_id'              => $phone_number_id,
        'whatsapp_business_account_id' => $waba_id,
        'display_phone_number'         => $display_phone,
        // Cambiar cualquier credencial invalida el último resultado de "Probar conexión".
        'status'                       => 'disconnected',
        'last_error'                   => null,
    ];

    // Los tres secretos son "campos de sustitución": si llegan vacíos se
    // conserva lo que ya había guardado, nunca se borra por omisión.
    $data['access_token_enc'] = $access_token !== '' ? av_whatsapp_encrypt( $access_token ) : ( $existing->access_token_enc ?? '' );
    $data['app_secret_enc']   = $app_secret   !== '' ? av_whatsapp_encrypt( $app_secret )   : ( $existing->app_secret_enc ?? '' );
    $data['verify_token_enc'] = $verify_token !== '' ? av_whatsapp_encrypt( $verify_token ) : ( $existing->verify_token_enc ?? '' );

    if ( $existing ) {
        $wpdb->update( $table, $data, [ 'id' => $existing->id ] );
    } else {
        $wpdb->insert( $table, $data );
    }

    wp_safe_redirect( add_query_arg( [ 'saved' => '1', 'tab' => 'whatsapp' ], $permalink ) );
    exit;
}
