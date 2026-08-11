<?php
// Espera $posts (WP_Query) en el scope. Usado tanto en la carga normal
// de la pagina como en la respuesta del buscador asincrono (AJAX).
?>
<?php echo sprintf( _n( 'Total %d cliente', 'Total %d clientes', $posts->found_posts, 'appsat' ), $posts->found_posts ); ?>
