
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-list-cpt-sats__toolbar">
                    <div id="clients-count" class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                        <?php include locate_template('components/c-list-cpt-clients-count.php'); ?>
                    </div>
                    <button type="button" class="c-list-cpt-sats__new-btn js-client-modal-open">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        <span>Crear cliente</span>
                    </button>
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
                        [
                            'name'    => 'tipo-cliente',
                            'label'   => 'Tipo cliente',
                            'type'    => 'select',
                            'options' => [ '' => 'Todos', 'particular' => 'Particular', 'profesional' => 'Profesional' ],
                        ],
                    ],
                ];
                include locate_template( 'components/c-filters.php' );
                ?>
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
</div>
