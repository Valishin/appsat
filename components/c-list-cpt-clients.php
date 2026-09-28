
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-list-cpt-sats__toolbar">
                    <div id="clients-count" class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                        <?php include locate_template('components/c-list-cpt-clients-count.php'); ?>
                    </div>
                </div>

                <?php
                $filters = [
                    'form_class'   => 'js-clients-filter-form',
                    'submit_class' => 'js-clients-filter-submit',
                    'clear_id'     => 'clients-clear-filters',
                    'nonce'        => 'av_clients_filter_nonce',
                    'fields'       => [
                        [ 'name' => 'nombre-cliente', 'label' => 'Nombre',   'placeholder' => 'Nombre del cliente', 'quick' => true ],
                        [ 'name' => 'dni-cliente',    'label' => 'DNI',      'placeholder' => '12345678A', 'quick' => true ],
                        [ 'name' => 'telefono',       'label' => 'Teléfono', 'placeholder' => '600 000 000' ],
                        [ 'name' => 'email',          'label' => 'Email',    'placeholder' => 'correo@ejemplo.com' ],
                        [ 'name' => 'tipo-cliente', 'type' => 'hidden' ],
                    ],
                ];
                include locate_template( 'components/c-filters.php' );
                ?>

                <?php
                // Segmentado Todos/Particulares/Profesionales: sustituye al
                // <select> "Tipo cliente" de los filtros (sigue existiendo
                // como campo, pero oculto -> ver 'type' => 'hidden' más arriba).
                // Misma ubicación y mismo diseño "pill" que las pestañas
                // Todos/En curso/Finalizados del listado de SATs
                // (c-list-cpt-sats__wrapper-menu, reutilizado tal cual).
                // Escribe en el campo oculto y relanza la búsqueda async al
                // momento (ver av_pill_tab_filter en src/js/app.js, reutilizado
                // también por Pedidos para su propio Todos/En curso/Finalizados).
                $tipo_cliente_filtro = isset( $_GET['tipo-cliente'] ) ? sanitize_text_field( $_GET['tipo-cliente'] ) : '';
                ?>
                <div class="c-list-cpt-sats__wrapper-menu js-client-type-filter">
                    <div class="c-list-cpt-sats__menu-item js-pill-tab-btn<?php echo $tipo_cliente_filtro === '' ? ' c-list-cpt-sats__menu-item--active' : ''; ?>" data-value="">
                        <span>Todos</span>
                    </div>
                    <div class="c-list-cpt-sats__menu-item js-pill-tab-btn<?php echo $tipo_cliente_filtro === 'particular' ? ' c-list-cpt-sats__menu-item--active' : ''; ?>" data-value="particular">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg>
                        <span>Particulares</span>
                    </div>
                    <div class="c-list-cpt-sats__menu-item js-pill-tab-btn<?php echo $tipo_cliente_filtro === 'profesional' ? ' c-list-cpt-sats__menu-item--active' : ''; ?>" data-value="profesional">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Profesionales</span>
                    </div>
                </div>

                <div class="c-list-cpt-sats__results-wrapper">
                    <div class="c-list-cpt-sats__loader js-clients-loader">
                        <span class="c-list-cpt-sats__loader-spinner"></span>
                    </div>
                    <div id="clients-list" class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                        <?php include locate_template('components/c-list-cpt-clients-list.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include( locate_template('components/c-client-modal.php') ); ?>

    <!-- "Crear cliente" fijo abajo a la derecha: mismo componente/CSS que
         "Nuevo servicio"/"Nuevo presupuesto" (ver components/c-servicios.php),
         en vez de vivir suelto en la barra de arriba junto al contador. Sigue
         abriendo el mismo modal de siempre (js-client-modal-open, sin tocar
         su JS: av_client_modal engancha por querySelectorAll, no por id). -->
    <div class="c-sat-form__wrapper-ctas">
        <div class="c-sat-form__ctas-inner">
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary c-sat-form__ctas-group--solo">
                <button type="button" class="c-sat-form__cta c-sat-form__cta--primary js-client-modal-open">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    <span>Crear cliente</span>
                </button>
            </div>
        </div>
    </div>
</div>
