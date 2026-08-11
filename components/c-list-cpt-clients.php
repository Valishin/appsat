
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div id="clients-count" class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                    <?php include locate_template('components/c-list-cpt-clients-count.php'); ?>
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
</div>
