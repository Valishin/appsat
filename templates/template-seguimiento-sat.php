<?php
/*
Template Name: Template Seguimiento SAT
*/

// Página pública para el cliente: no usa el header/footer del panel interno (CRM),
// solo lo imprescindible de WordPress (wp_head/wp_footer) para estilos y compatibilidad.

$token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';
$sat   = null;

if ( $token !== '' ) {
    $found = get_posts([
        'post_type'      => 'cpt-sats',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_query'     => [
            [
                'key'   => 'cpt-sat__tracking-token',
                'value' => $token,
            ],
        ],
    ]);
    $sat = $found ? $found[0] : null;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Seguimiento de tu reparación</title>
    <?php wp_head(); ?>
</head>
<body class="s-template-seguimiento-sat">
    <?php include( locate_template( 'components/c-sat-tracking.php' ) ); ?>
    <?php wp_footer(); ?>
</body>
</html>
