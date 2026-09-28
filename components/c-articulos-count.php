<?php
// Espera $posts (WP_Query) en el scope. Usado tanto en la carga normal
// de la página como en la respuesta del buscador asíncrono (AJAX).
?>
<?php echo sprintf( _n( 'Total %d artículo', 'Total %d artículos', $posts->found_posts, 'appsat' ), $posts->found_posts ); ?>
