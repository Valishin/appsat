<?php
// ── Módulo WhatsApp: normalización de teléfono ──────────────────────────────
// Función ÚNICA y centralizada para convertir un número en cualquier formato
// (+34 600000000 / 0034 600000000 / 600000000) al formato de solo dígitos que
// usa Meta (p.ej. "34600000000", sin "+"). Ningún otro archivo debe tener su
// propia lógica de normalización.

if ( ! defined( 'ABSPATH' ) ) exit;

// $default_prefix: prefijo de país a anteponer SOLO si el número no parece
// llevar ya uno (p.ej. el prefijo guardado en la ficha del cliente). Se usa
// desde la Fase 8 para reconstruir el número completo a partir de
// cpt-client__extension + cpt-client__phone; el webhook de Meta (Fase 4) no
// lo necesita porque Meta ya manda el número completo con prefijo.
//
// Si el resultado no es un número de teléfono razonable (muy corto/muy largo),
// devuelve '' en vez de inventar un país — el llamante decide qué hacer
// (dejar la conversación sin cliente, etc.).
function av_whatsapp_normalize_phone( $raw, $default_prefix = '' ) {
    $digits = preg_replace( '/\D/', '', (string) $raw );
    if ( $digits === '' ) return '';

    // "00" es el equivalente internacional a "+" fuera de EE.UU./Canadá.
    if ( strlen( $digits ) > 2 && substr( $digits, 0, 2 ) === '00' ) {
        $digits = substr( $digits, 2 );
    }

    $default_prefix = preg_replace( '/\D/', '', (string) $default_prefix );
    if ( $default_prefix !== '' && strpos( $digits, $default_prefix ) !== 0 ) {
        // Heurística deliberadamente conservadora: solo se antepone el prefijo
        // si el número es "corto" (pinta de número local sin prefijo, p.ej. un
        // móvil español de 9 dígitos). Si ya es largo, es más probable que sea
        // un número internacional completo con OTRO prefijo, y anteponer aquí
        // generaría un número inventado incorrecto.
        if ( strlen( $digits ) <= 9 ) {
            $digits = $default_prefix . $digits;
        }
    }

    // E.164: entre 8 y 15 dígitos en total. Fuera de ese rango no se puede
    // confiar en el resultado.
    if ( strlen( $digits ) < 8 || strlen( $digits ) > 15 ) {
        return '';
    }

    return $digits;
}
