<?php
// ── Módulo WhatsApp: recepción de eventos (webhook de Meta) ────────────────
// Endpoint público (sin login WP, sin nonce): la seguridad se basa en el
// mecanismo propio de Meta — verify_token en la verificación GET, y firma
// X-Hub-Signature-256 (HMAC-SHA256 con el App Secret) en cada POST.
//
// Responsabilidad de este archivo: validar, identificar la cuenta, procesar
// mensajes/statuses e idempotencia. NO construye inbox/chat (Fase 5+) ni
// descarga media (Fase 7): solo guarda lo necesario para que esas fases
// puedan construirse encima.

if ( ! defined( 'ABSPATH' ) ) exit;

// ── GET: verificación del webhook ──────────────────────────────────────────
// Meta manda "hub.mode", "hub.verify_token", "hub.challenge" como query string
// con PUNTOS literales. PHP convierte automáticamente los puntos en guiones
// bajos al poblar $_GET (comportamiento nativo de PHP, no de WordPress), así
// que en el servidor SIEMPRE llegan como hub_mode/hub_verify_token/hub_challenge.
// Se leen de $_GET directamente (no de WP_REST_Request::get_param) para no
// depender de cómo la REST API decida normalizarlos.
function av_whatsapp_rest_webhook_verify( WP_REST_Request $request ) {
    $mode          = isset( $_GET['hub_mode'] )          ? sanitize_text_field( wp_unslash( $_GET['hub_mode'] ) )          : '';
    $verify_token  = isset( $_GET['hub_verify_token'] )  ? (string) wp_unslash( $_GET['hub_verify_token'] )                : '';
    $challenge     = isset( $_GET['hub_challenge'] )     ? (string) wp_unslash( $_GET['hub_challenge'] )                   : '';

    if ( $mode !== 'subscribe' || $verify_token === '' ) {
        av_whatsapp_log( 'warning', 'WEBHOOK_VERIFY_REJECTED hub.mode o hub.verify_token ausentes/incorrectos' );
        status_header( 403 );
        exit;
    }

    $matched = false;
    foreach ( av_whatsapp_get_all_accounts() as $account ) {
        if ( $account['verify_token'] !== '' && hash_equals( $account['verify_token'], $verify_token ) ) {
            $matched = true;
            break;
        }
    }

    if ( ! $matched ) {
        av_whatsapp_log( 'warning', 'WEBHOOK_VERIFY_REJECTED verify_token no coincide con ninguna cuenta configurada' );
        status_header( 403 );
        exit;
    }

    av_whatsapp_log( 'info', 'WEBHOOK_VERIFIED verificación GET aceptada' );

    // Meta espera el valor de hub.challenge tal cual, como texto plano (no JSON).
    status_header( 200 );
    header( 'Content-Type: text/plain' );
    echo intval( $challenge );
    exit;
}

// ── POST: recepción de eventos ──────────────────────────────────────────────
function av_whatsapp_rest_webhook_receive( WP_REST_Request $request ) {
    $raw_body = $request->get_body();
    av_whatsapp_log( 'info', 'WEBHOOK_RECEIVED tamaño=' . strlen( $raw_body ) . ' bytes' );

    $signature = $request->get_header( 'X-Hub-Signature-256' );
    if ( ! av_whatsapp_verify_webhook_signature( $raw_body, $signature ) ) {
        av_whatsapp_log( 'warning', 'WEBHOOK_SIGNATURE_INVALID' );
        return new WP_REST_Response( [ 'error' => 'invalid_signature' ], 403 );
    }

    $payload = json_decode( $raw_body, true );
    if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $payload ) ) {
        av_whatsapp_log( 'warning', 'WEBHOOK_PROCESSING_ERROR JSON inválido' );
        return new WP_REST_Response( [ 'error' => 'invalid_json' ], 400 );
    }

    $entries = is_array( $payload['entry'] ?? null ) ? $payload['entry'] : [];
    if ( empty( $entries ) ) {
        av_whatsapp_log( 'info', 'WEBHOOK_UNKNOWN_EVENT payload sin entry[]' );
        return new WP_REST_Response( [ 'received' => true ], 200 );
    }

    foreach ( $entries as $entry ) {
        $changes = is_array( $entry['changes'] ?? null ) ? $entry['changes'] : [];
        foreach ( $changes as $change ) {
            av_whatsapp_process_webhook_change( is_array( $change['value'] ?? null ) ? $change['value'] : [] );
        }
    }

    // Respuesta rápida y siempre 200 cuando el payload se ha podido leer e
    // interpretar (aunque algún evento concreto se haya ignorado): así Meta
    // no reintenta innecesariamente. No hay operaciones lentas antes de esto
    // (todo el procesamiento de arriba son escrituras ligeras en BD, sin
    // llamadas HTTP salientes ni descarga de media en esta fase).
    return new WP_REST_Response( [ 'received' => true ], 200 );
}

// Comprueba X-Hub-Signature-256 (HMAC-SHA256 del cuerpo crudo con el App
// Secret) contra TODAS las cuentas configuradas, no una fija: el payload aún
// no se ha leído/validado en este punto, así que no se puede saber de
// antemano a qué cuenta pertenece. Con una sola cuenta (caso actual) esto es
// una comparación; con varias (futuro multiempresa) sigue siendo correcto
// porque normalmente todas las cuentas comparten la misma App de Meta.
// Comparación timing-safe con hash_equals().
function av_whatsapp_verify_webhook_signature( $raw_body, $signature_header ) {
    if ( empty( $signature_header ) || strpos( $signature_header, 'sha256=' ) !== 0 ) {
        return false;
    }
    $given = substr( $signature_header, 7 );

    $accounts = av_whatsapp_get_all_accounts();
    if ( empty( $accounts ) ) {
        return false; // nada configurado todavía: no hay nada con lo que validar
    }

    foreach ( $accounts as $account ) {
        if ( $account['app_secret'] === '' ) continue;
        $expected = hash_hmac( 'sha256', $raw_body, $account['app_secret'] );
        if ( hash_equals( $expected, $given ) ) {
            return true;
        }
    }

    return false;
}

// Un "change" es messages y/o statuses para UN número de WhatsApp concreto
// (metadata.phone_number_id). Se identifica la cuenta por ese ID — nunca se
// asume "la cuenta 1" — y si no hay cuenta configurada para ese número, el
// evento se registra y se ignora sin tocar ninguna tabla de conversación/mensaje.
function av_whatsapp_process_webhook_change( array $value ) {
    $phone_number_id = $value['metadata']['phone_number_id'] ?? '';
    if ( $phone_number_id === '' ) {
        av_whatsapp_log( 'info', 'WEBHOOK_UNKNOWN_EVENT sin metadata.phone_number_id' );
        return;
    }

    $account = av_whatsapp_get_account_by_phone_number_id( $phone_number_id );
    if ( ! $account ) {
        av_whatsapp_log( 'warning', "WEBHOOK_ACCOUNT_NOT_FOUND phone_number_id=$phone_number_id" );
        return;
    }

    $contacts = is_array( $value['contacts'] ?? null ) ? $value['contacts'] : [];
    $messages = is_array( $value['messages'] ?? null ) ? $value['messages'] : [];
    $statuses = is_array( $value['statuses'] ?? null ) ? $value['statuses'] : [];

    foreach ( $messages as $msg ) {
        av_whatsapp_process_incoming_message( is_array( $msg ) ? $msg : [], $contacts, $account );
    }

    foreach ( $statuses as $status_event ) {
        av_whatsapp_process_status_event( is_array( $status_event ) ? $status_event : [], $account );
    }

    if ( empty( $messages ) && empty( $statuses ) ) {
        av_whatsapp_log( 'info', 'WEBHOOK_UNKNOWN_EVENT change sin messages ni statuses reconocidos' );
    }
}

// ── Mensajes entrantes ───────────────────────────────────────────────────
function av_whatsapp_process_incoming_message( array $msg, array $contacts, array $account ) {
    $wamid = $msg['id'] ?? '';
    if ( $wamid === '' ) {
        av_whatsapp_log( 'warning', 'WEBHOOK_PROCESSING_ERROR mensaje entrante sin id' );
        return;
    }

    // Comprobación previa: evita trabajo innecesario (normalizar teléfono,
    // resolver conversación...) si ya sabemos que es un duplicado. NO es la
    // protección definitiva contra condiciones de carrera: esa la da el
    // UNIQUE KEY de whatsapp_message_id en el INSERT de más abajo.
    if ( av_whatsapp_message_exists( $wamid ) ) {
        av_whatsapp_log( 'info', 'WEBHOOK_MESSAGE_DUPLICATE (comprobación previa)' );
        return;
    }

    $from = av_whatsapp_normalize_phone( $msg['from'] ?? '' );
    if ( $from === '' ) {
        av_whatsapp_log( 'warning', 'WEBHOOK_PROCESSING_ERROR teléfono de origen no reconocible' );
        return;
    }

    $type    = is_string( $msg['type'] ?? null ) ? $msg['type'] : 'unknown';
    $content = av_whatsapp_extract_message_content( $msg, $type );

    $contact_name = '';
    foreach ( $contacts as $c ) {
        if ( ( $c['wa_id'] ?? '' ) === ( $msg['from'] ?? '' ) ) {
            $contact_name = $c['profile']['name'] ?? '';
            break;
        }
    }

    $conversation_id = av_whatsapp_upsert_conversation( $account, $from, $contact_name );
    $wa_timestamp     = isset( $msg['timestamp'] ) && is_numeric( $msg['timestamp'] )
        ? wp_date( 'Y-m-d H:i:s', intval( $msg['timestamp'] ) )
        : null;

    global $wpdb;
    $inserted = $wpdb->insert( av_whatsapp_table( 'messages' ), [
        'conversation_id'     => $conversation_id,
        'whatsapp_message_id' => $wamid,
        'direction'           => 'inbound',
        'sender_phone'        => $from,
        'message_type'        => $type,
        'text_body'           => $content['text_body'],
        'media_id'            => $content['media_id'],
        'filename'            => $content['filename'],
        'mime_type'           => $content['mime_type'],
        'caption'             => $content['caption'],
        'status'              => 'received',
        'wa_timestamp'        => $wa_timestamp,
        // Solo el objeto de ESTE mensaje, no el payload completo del webhook
        // (que puede incluir varios mensajes/contactos): tamaño razonable,
        // sin tokens/secretos (Meta nunca los incluye en el contenido de un
        // mensaje).
        'raw_payload'         => wp_json_encode( $msg, JSON_UNESCAPED_UNICODE ),
    ] );

    if ( $inserted === false ) {
        // Caso esperado si dos entregas casi simultáneas del mismo evento
        // pasaron la comprobación previa a la vez: el UNIQUE KEY las separa
        // aquí, garantizando que solo una prospere.
        av_whatsapp_log( 'info', 'WEBHOOK_MESSAGE_DUPLICATE (bloqueado por UNIQUE KEY en el insert)' );
        return;
    }

    $preview = av_whatsapp_message_preview_text( $type, $content['text_body'] );
    av_whatsapp_touch_conversation_after_message( $conversation_id, $preview, $wa_timestamp );

    av_whatsapp_log( 'info', "WEBHOOK_MESSAGE_RECEIVED type=$type" );
}

// Extrae el contenido estructurado disponible SIN descargar nada (la Fase 7
// se encargará de traer el binario de media). Un tipo no reconocido no rompe
// nada: simplemente no hay contenido estructurado que extraer.
function av_whatsapp_extract_message_content( array $msg, $type ) {
    $result = [ 'text_body' => null, 'media_id' => null, 'mime_type' => null, 'filename' => null, 'caption' => null ];

    switch ( $type ) {
        case 'text':
            $result['text_body'] = $msg['text']['body'] ?? '';
            break;

        case 'image':
        case 'video':
        case 'sticker':
        case 'audio':
            $media = is_array( $msg[ $type ] ?? null ) ? $msg[ $type ] : [];
            $result['media_id']  = $media['id'] ?? null;
            $result['mime_type'] = $media['mime_type'] ?? null;
            $result['caption']   = $media['caption'] ?? null;
            break;

        case 'document':
            $media = is_array( $msg['document'] ?? null ) ? $msg['document'] : [];
            $result['media_id']  = $media['id'] ?? null;
            $result['mime_type'] = $media['mime_type'] ?? null;
            $result['filename']  = $media['filename'] ?? null;
            $result['caption']   = $media['caption'] ?? null;
            break;

        case 'location':
            $loc = is_array( $msg['location'] ?? null ) ? $msg['location'] : [];
            if ( isset( $loc['latitude'], $loc['longitude'] ) ) {
                $result['text_body'] = $loc['latitude'] . ',' . $loc['longitude'];
            }
            break;

        // 'contacts' y cualquier tipo futuro/desconocido: sin contenido
        // estructurado propio, se guarda igualmente el message_type y el
        // raw_payload íntegro del mensaje para no perder información.
    }

    return $result;
}

function av_whatsapp_message_preview_text( $type, $text_body ) {
    if ( $type === 'text' ) {
        return $text_body !== null ? mb_substr( $text_body, 0, 190 ) : '';
    }
    $labels = [
        'image' => '📷 Imagen', 'video' => '🎬 Vídeo', 'audio' => '🎵 Audio',
        'document' => '📄 Documento', 'sticker' => '🩹 Sticker',
        'location' => '📍 Ubicación', 'contacts' => '👤 Contacto',
    ];
    return $labels[ $type ] ?? 'Mensaje';
}

function av_whatsapp_message_exists( $wamid ) {
    global $wpdb;
    $table = av_whatsapp_table( 'messages' );
    return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE whatsapp_message_id = %s", $wamid ) );
}

// ── Conversación ─────────────────────────────────────────────────────────
// Una conversación por (cuenta de WhatsApp, número). Puede quedar sin
// customer_id/sat_id: la Fase 8 se encargará de esa asociación.
function av_whatsapp_upsert_conversation( array $account, $phone_normalized, $contact_name ) {
    global $wpdb;
    $table = av_whatsapp_table( 'conversations' );

    $existing = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, contact_name FROM $table WHERE whatsapp_account_id = %d AND phone_number = %s",
        $account['id'], $phone_normalized
    ) );

    if ( $existing ) {
        // No se sobrescribe un nombre ya guardado con uno vacío que venga en
        // este evento concreto.
        if ( $contact_name !== '' && $contact_name !== $existing->contact_name ) {
            $wpdb->update( $table, [ 'contact_name' => $contact_name ], [ 'id' => $existing->id ] );
        }
        return (int) $existing->id;
    }

    $wpdb->insert( $table, [
        'tenant_id'            => $account['tenant_id'],
        'whatsapp_account_id'  => $account['id'],
        'phone_number'         => $phone_normalized,
        'contact_name'         => $contact_name,
        'status'               => 'open',
    ] );

    return (int) $wpdb->insert_id;
}

function av_whatsapp_touch_conversation_after_message( $conversation_id, $preview, $wa_timestamp ) {
    global $wpdb;
    $table = av_whatsapp_table( 'conversations' );
    $wpdb->query( $wpdb->prepare(
        "UPDATE $table SET last_message_at = %s, last_message_preview = %s, unread_count = unread_count + 1, updated_at = %s WHERE id = %d",
        $wa_timestamp ?: current_time( 'mysql' ),
        $preview,
        current_time( 'mysql' ),
        $conversation_id
    ) );
}

// ── Statuses (sent/delivered/read/failed) ───────────────────────────────
// DECISIÓN: si el status llega para un whatsapp_message_id que todavía no
// existe en nuestra tabla de mensajes (p.ej. orden de entrega distinto por
// parte de Meta, o un mensaje que no se originó desde esta app), NO se crea
// una fila de mensaje "hueca" — no tendríamos conversation_id/direction/
// message_type reales y dejaría datos corruptos. Se registra en el log y se
// descarta. Cuando en la Fase 6 se envíen mensajes salientes desde esta app,
// el mensaje se inserta de forma SÍNCRONA en la misma petición que hace la
// llamada a la Cloud API (el id de Meta llega en la respuesta HTTP, no por
// webhook), así que en la práctica el mensaje local ya existirá antes de que
// pueda llegar cualquier status sobre él — el hueco solo debería darse con
// mensajes ajenos a esta app, que de todas formas no nos interesa rastrear.
function av_whatsapp_process_status_event( array $status_event, array $account ) {
    $wamid  = $status_event['id'] ?? '';
    $status = $status_event['status'] ?? '';

    if ( $wamid === '' || $status === '' ) {
        av_whatsapp_log( 'info', 'WEBHOOK_UNKNOWN_EVENT status sin id o sin status' );
        return;
    }

    $allowed = [ 'sent', 'delivered', 'read', 'failed' ];
    if ( ! in_array( $status, $allowed, true ) ) {
        av_whatsapp_log( 'info', "WEBHOOK_UNKNOWN_EVENT status desconocido: $status" );
        return;
    }

    global $wpdb;
    $table = av_whatsapp_table( 'messages' );
    $row   = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM $table WHERE whatsapp_message_id = %s", $wamid ) );

    if ( ! $row ) {
        av_whatsapp_log( 'info', 'WEBHOOK_STATUS_RECEIVED sin mensaje local todavía (se ignora, ver comentario de diseño)' );
        return;
    }

    // No retroceder un status más avanzado por uno más antiguo que llegue
    // desordenado (p.ej. "sent" llegando después de que ya se registrase "read").
    $rank = [ 'pending' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3, 'failed' => 9 ];
    if ( ( $rank[ $status ] ?? 0 ) < ( $rank[ $row->status ] ?? -1 ) ) {
        av_whatsapp_log( 'info', 'WEBHOOK_STATUS_RECEIVED ignorado por llegar desordenado (más antiguo que el actual)' );
        return;
    }

    $error_message = null;
    if ( $status === 'failed' && ! empty( $status_event['errors'][0] ) ) {
        $err = $status_event['errors'][0];
        $error_message = trim( ( $err['code'] ?? '' ) . ' ' . ( $err['title'] ?? $err['message'] ?? '' ) );
    }

    $wpdb->update( $table, [
        'status'        => $status,
        'error_message' => $error_message,
        // Solo el evento de status en sí (pequeño), no el payload completo del webhook.
        'raw_payload'   => wp_json_encode( $status_event, JSON_UNESCAPED_UNICODE ),
    ], [ 'id' => $row->id ] );

    av_whatsapp_log( 'info', "WEBHOOK_STATUS_RECEIVED status=$status" );
}
