<?php
// Modal de alta rápida de cliente. Se reutiliza en dos sitios:
//   - Listado de clientes: botón "Crear cliente".
//   - Alta de SAT nuevo (c-sat-client-picker.php): "¿No existe? Crear cliente nuevo".
// $client_modal_redirect_to (opcional, definida por quien incluye este archivo):
// a dónde volver tras crear el cliente. Sin indicarla, save_contact() redirige
// por defecto al listado de clientes; en el flujo de "Nuevo SAT" se pasa la
// URL de creación del SAT para que, al crear el cliente, vuelva ahí ya elegido.
$name        = '';
$dni         = '';
$phone       = '';
$email       = '';
$extension   = '34';
$type_client = 'particular';
$redirect_to = $client_modal_redirect_to ?? '';
$in_modal    = true;
?>
<div class="c-client-modal js-client-modal">
    <div class="c-client-modal__overlay js-client-modal-close"></div>
    <div class="c-client-modal__panel">
        <div class="c-client-modal__head">
            <span class="c-client-modal__title">Nuevo cliente</span>
            <button type="button" class="c-client-modal__close js-client-modal-close" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <?php include( locate_template('components/c-client-form.php') ); ?>
    </div>
</div>
