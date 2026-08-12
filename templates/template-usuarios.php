<?php
/*
Template Name: Template Usuarios
*/

if ( ! current_user_can( 'administrator' ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

the_post();
get_header();

$tecnicos = get_users( array(
    'role__in' => array( 'administrator', 'editor' ),
    'orderby'  => 'display_name',
    'order'    => 'ASC',
) );

$nuevo_usuario = null;
if ( isset( $_GET['creado'] ) ) {
    $temp = get_transient( 'av_new_user_pass_' . get_current_user_id() );
    if ( $temp ) {
        $nuevo_usuario = $temp;
        delete_transient( 'av_new_user_pass_' . get_current_user_id() );
    }
}

$usuario_actualizado  = isset( $_GET['actualizado'] );
$usuario_deshabilitado = isset( $_GET['deshabilitado'] );
$usuario_habilitado    = isset( $_GET['habilitado'] );
$usuario_eliminado     = isset( $_GET['eliminado'] );

$editando = null;
if ( ! empty( $_GET['edit'] ) ) {
    $editando = get_userdata( intval( $_GET['edit'] ) );
    if ( ! $editando || ! array_intersect( array( 'administrator', 'editor' ), $editando->roles ) ) {
        $editando = null;
    }
}

// El modal se abre solo si venimos de editar o si el guardado falló (para no perder lo escrito)
$abrir_modal = $editando || isset( $_GET['error'] );

?>
    <section class="o-main s-template-usuarios">
        <?php
            include( locate_template('components/c-usuarios.php') );
        ?>
    </section>
<?php
get_footer();
?>
