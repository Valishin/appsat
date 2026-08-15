<?php
/*
Template Name: Template Categorías de dispositivo
*/

if ( ! current_user_can( 'administrator' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

the_post();
get_header();

$categorias = av_get_device_categories();

$categoria_creada      = isset( $_GET['creado'] );
$categoria_actualizada = isset( $_GET['actualizado'] );
$categoria_eliminada    = isset( $_GET['eliminado'] );

$editando = null;
if ( ! empty( $_GET['edit'] ) ) {
    $editando_term = get_term( intval( $_GET['edit'] ), 'cpt-device-category' );
    if ( $editando_term && ! is_wp_error( $editando_term ) ) {
        $editando = $editando_term;
    }
}

// El modal se abre solo si venimos de editar o si el guardado falló (para no perder lo escrito)
$abrir_modal = $editando || isset( $_GET['error'] );

?>
    <section class="o-main s-template-device-categories">
        <?php
            include( locate_template('components/c-device-categories.php') );
        ?>
    </section>
<?php
get_footer();
?>
