<?php
// Espera $query (WP_Query) en el scope. Usado tanto en la carga normal
// de la pagina como en la respuesta del buscador asincrono (AJAX).
?>
<?php echo sprintf( _n( 'Total %d factura', 'Total %d facturas', $query->found_posts, 'appsat' ), $query->found_posts ); ?>
