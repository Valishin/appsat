<?php
// Mensaje dinámico desde el servidor
$success_message = isset($success_message)
    ? $success_message
    : 'Se ha guardado correctamente';

// Control de visibilidad
$show_success = true; // ajusta según tu lógica
?>

<?php if ( $show_success ) : ?>
    <div class="b-save" role="alert">
        <span class="b-save__icon">✔</span>
        <p class="b-save__text">
            <?php echo esc_html( $success_message ); ?>
        </p>
    </div>
<?php endif; ?>