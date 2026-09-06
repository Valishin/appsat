<?php
// ── Módulo WhatsApp: cliente HTTP centralizado hacia la Cloud API de Meta ──
// Ningún otro archivo debe llamar a graph.facebook.com directamente: todo
// pasa por av_whatsapp_api_request(), que se encarga de autenticación,
// timeout, parseo de errores y de que un secreto no salga nunca de aquí.
// Usa wp_remote_request() (núcleo de WP) — no se añade Guzzle ni Composer,
// no hay ninguna necesidad técnica real que lo justifique.

if ( ! defined( 'ABSPATH' ) ) exit;

// $args soporta:
//   'body'         => array (se serializa a JSON) | string (ya preparado, p.ej. multipart)
//   'is_multipart' => true  (evita que un body-array se fuerce a JSON)
//   'headers'      => array adicional de cabeceras
function av_whatsapp_api_request( $method, $path, array $args = [], $account = null ) {
    $account = $account ?: av_whatsapp_get_account();

    if ( ! $account || empty( $account['access_token'] ) ) {
        return new WP_Error( 'av_whatsapp_not_configured', 'WhatsApp no está configurado.' );
    }

    $request_args = [
        'method'  => $method,
        'timeout' => 20,
        'headers' => array_merge(
            [ 'Authorization' => 'Bearer ' . $account['access_token'] ],
            $args['headers'] ?? []
        ),
    ];

    if ( isset( $args['body'] ) ) {
        if ( is_array( $args['body'] ) && empty( $args['is_multipart'] ) ) {
            $request_args['headers']['Content-Type'] = 'application/json';
            $request_args['body'] = wp_json_encode( $args['body'] );
        } else {
            $request_args['body'] = $args['body'];
        }
    }

    $response = wp_remote_request( av_whatsapp_graph_api_url( $path ), $request_args );

    return av_whatsapp_parse_api_response( $response );
}

// Normaliza la respuesta: devuelve el array decodificado en éxito, o un
// WP_Error con un mensaje seguro (nunca el payload crudo) en fallo.
function av_whatsapp_parse_api_response( $response ) {
    if ( is_wp_error( $response ) ) {
        av_whatsapp_log( 'error', 'Fallo de red hacia Meta: ' . $response->get_error_message() );
        return new WP_Error( 'av_whatsapp_network_error', 'No se ha podido contactar con WhatsApp. Inténtalo de nuevo.' );
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $code >= 200 && $code < 300 ) {
        return is_array( $body ) ? $body : [];
    }

    $meta_code    = is_array( $body ) ? ( $body['error']['code'] ?? $code ) : $code;
    $meta_subcode = is_array( $body ) ? ( $body['error']['error_subcode'] ?? '' ) : '';
    $meta_type    = is_array( $body ) ? ( $body['error']['type'] ?? '' ) : '';

    // Deliberadamente NO se registra el "message" de texto libre de Meta: en
    // algunos errores (p.ej. token malformado) Meta lo devuelve incluyendo el
    // propio valor recibido, así que solo se loguean campos estructurados
    // (códigos/tipo), nunca texto libre que pueda contener el token.
    av_whatsapp_log( 'error', "Meta devolvió HTTP $code (código Meta: $meta_code, subcódigo: $meta_subcode, tipo: $meta_type)" );

    return new WP_Error( 'av_whatsapp_api_error', av_whatsapp_map_api_error( $code, (int) $meta_code ), [ 'http_code' => $code ] );
}

// Traduce el error de Meta a un mensaje entendible SIN revelar tokens,
// cabeceras ni el payload original.
function av_whatsapp_map_api_error( $http_code, $meta_code ) {
    if ( $meta_code === 190 || $http_code === 401 ) {
        return 'El Access Token no es válido o ha caducado.';
    }
    if ( $http_code === 400 || $http_code === 404 ) {
        return 'Los datos de conexión (Phone Number ID / WhatsApp Business Account ID) no son correctos.';
    }
    if ( $http_code === 403 ) {
        return 'La cuenta no tiene permiso para usar la API con estas credenciales.';
    }
    if ( $http_code >= 500 ) {
        return 'WhatsApp no está disponible en este momento. Inténtalo más tarde.';
    }
    return 'No se ha podido establecer conexión con WhatsApp.';
}

// ── Test de conexión real ───────────────────────────────────────────────
// 1) GET del número (comprueba token + Phone Number ID a la vez).
// 2) Si hay WABA ID relleno, GET también de la cuenta (comprueba que
//    coincide con el token y no es un ID copiado de otra cuenta).
// Actualiza status/last_error/connected_at en la tabla de cuentas.
function av_whatsapp_test_connection( $tenant_id = null ) {
    $account = av_whatsapp_get_account( $tenant_id );

    if ( ! $account || empty( $account['access_token'] ) || empty( $account['phone_number_id'] ) ) {
        return [
            'success' => false,
            'message' => 'Faltan credenciales por configurar (Access Token y Phone Number ID son obligatorios).',
        ];
    }

    $phone_result = av_whatsapp_api_request(
        'GET',
        $account['phone_number_id'] . '?fields=verified_name,display_phone_number,quality_rating,code_verification_status',
        [],
        $account
    );

    global $wpdb;
    $table = av_whatsapp_table( 'accounts' );

    if ( is_wp_error( $phone_result ) ) {
        $wpdb->update( $table, [
            'status'     => 'error',
            'last_error' => $phone_result->get_error_message(),
        ], [ 'id' => $account['id'] ] );

        return [ 'success' => false, 'message' => $phone_result->get_error_message() ];
    }

    if ( ! empty( $account['whatsapp_business_account_id'] ) ) {
        $waba_result = av_whatsapp_api_request(
            'GET',
            $account['whatsapp_business_account_id'] . '?fields=id,name',
            [],
            $account
        );

        if ( is_wp_error( $waba_result ) ) {
            $message = 'El Phone Number ID es correcto, pero el WhatsApp Business Account ID no. ' . $waba_result->get_error_message();
            $wpdb->update( $table, [ 'status' => 'error', 'last_error' => $message ], [ 'id' => $account['id'] ] );
            return [ 'success' => false, 'message' => $message ];
        }
    }

    $wpdb->update( $table, [
        'status'               => 'connected',
        'last_error'           => null,
        'connected_at'         => current_time( 'mysql' ),
        'display_phone_number' => $phone_result['display_phone_number'] ?? $account['display_phone_number'],
    ], [ 'id' => $account['id'] ] );

    return [
        'success'              => true,
        'message'              => 'Conexión correcta.',
        'verified_name'        => $phone_result['verified_name'] ?? '',
        'display_phone_number' => $phone_result['display_phone_number'] ?? '',
        'quality_rating'       => $phone_result['quality_rating'] ?? '',
    ];
}
