<?php
// Espera $posts (WP_Query) en el scope. Usado tanto en la carga normal de la
// pagina como en la respuesta del buscador asincrono (AJAX).
?>
<?php echo sprintf( _n( 'Total %d presupuesto', 'Total %d presupuestos', $posts->found_posts, 'appsat' ), $posts->found_posts ); ?>
