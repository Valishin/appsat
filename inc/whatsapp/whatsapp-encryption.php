<?php
// ── Módulo WhatsApp: cifrado de credenciales ────────────────────────────────
// access_token / app_secret / verify_token de Meta se guardan cifrados en BD
// (nunca en claro). La clave AES se deriva de las claves secretas propias de
// WordPress (definidas en wp-config.php, jamás en la base de datos) — sin
// añadir ninguna dependencia nueva, OpenSSL es núcleo de PHP.

if ( ! defined( 'ABSPATH' ) ) exit;

function av_whatsapp_encryption_key() {
    $material = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : '' )
        . ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '' )
        . ( defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : '' );

    return hash_hmac( 'sha256', 'av_whatsapp_credentials', $material, true );
}

// Devuelve una cadena base64 (IV + texto cifrado) lista para guardar en una
// columna *_enc. Cadena vacía de entrada -> cadena vacía de salida (no cifra
// "nada" innecesariamente, y evita que un campo sin rellenar parezca cifrado).
function av_whatsapp_encrypt( $plaintext ) {
    $plaintext = (string) $plaintext;
    if ( $plaintext === '' ) return '';

    $iv     = random_bytes( 16 );
    $cipher = openssl_encrypt( $plaintext, 'aes-256-cbc', av_whatsapp_encryption_key(), OPENSSL_RAW_DATA, $iv );

    if ( $cipher === false ) return '';

    return base64_encode( $iv . $cipher );
}

function av_whatsapp_decrypt( $encoded ) {
    $encoded = (string) $encoded;
    if ( $encoded === '' ) return '';

    $raw = base64_decode( $encoded, true );
    if ( $raw === false || strlen( $raw ) <= 16 ) return '';

    $iv     = substr( $raw, 0, 16 );
    $cipher = substr( $raw, 16 );
    $plain  = openssl_decrypt( $cipher, 'aes-256-cbc', av_whatsapp_encryption_key(), OPENSSL_RAW_DATA, $iv );

    return $plain === false ? '' : $plain;
}
