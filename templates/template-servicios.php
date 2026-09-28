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

// El modal se abre solo si venimos de editar, si el guardado falló (para no
// perder lo escrito), o si se llega con ?new=1 (atajo "Nuevo servicio" del
// menú lateral, ver blocks/b-header.php — mismo patrón que ?new=1 en
// Pedidos/Artículos, aunque aquí el alta es un modal y no una página aparte).
$abrir_modal = $editando || isset( $_GET['error'] ) || isset( $_GET['new'] );

?>
    <section class="o-main s-template-servicios">
        <?php
            include( locate_template('components/c-servicios.php') );
        ?>
    </section>
<?php
get_footer();
?>
