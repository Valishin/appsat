<?php
$crear_sat_url = get_permalink();
?>
<div class="c-sat-client-picker">
    <div class="c-sat-client-picker__inner">
        <div class="c-sat-client-picker__container o-container">
            <div class="c-sat-client-picker__col o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">

                <h1 class="c-sat-client-picker__title o-font-display-2">Selecciona un cliente</h1>
                <p class="c-sat-client-picker__subtitle">Antes de crear el SAT, busca al cliente. Si no existe todavía, puedes crearlo aquí mismo.</p>

                <div class="c-sat-client-picker__search-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="c-sat-client-picker__search js-sat-client-picker__search" placeholder="Buscar por nombre, DNI o teléfono...">
                </div>

                <div class="c-sat-client-picker__results js-sat-client-picker__results"></div>

                <button type="button" class="c-sat-client-picker__toggle-new js-client-modal-open">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>¿No existe? Crear cliente nuevo</span>
                </button>

            </div>
        </div>
    </div>
    <?php
        $client_modal_redirect_to = $crear_sat_url;
        include( locate_template('components/c-client-modal.php') );
    ?>
</div>
