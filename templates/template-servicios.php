<?php
/*
Template Name: Template Servicios
*/

if ( ! current_user_can( 'administrator' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

the_post();
get_header();

$servicios = get_posts( array(
    'post_type'      => 'cpt-servicios',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'post_status'    => 'publish',
) );

$servicio_creado      = isset( $_GET['creado'] );
$servicio_actualizado = isset( $_GET['actualizado'] );
$servicio_eliminado   = isset( $_GET['eliminado'] );

$editando = null;
if ( ! empty( $_GET['edit'] ) ) {
    $editando_id = intval( $_GET['edit'] );
    if ( get_post_type( $editando_id ) === 'cpt-servicios' ) {
        $editando = get_post( $editando_id );
    }
}

// El modal se abre solo si venimos de editar o si el guardado falló (para no perder lo escrito)
$abrir_modal = $editando || isset( $_GET['error'] );

?>
    <section class="o-main s-template-servicios">
        <?php
            include( locate_template('components/c-servicios.php') );
        ?>
    </section>
<?php
get_footer();
?>
