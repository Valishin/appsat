
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div id="sats-count" class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                    <?php include locate_template('components/c-list-cpt-sats-count.php'); ?>
                </div>
                <?php
                $filters = [
                    'form_class'   => 'js-sats-filter-form',
                    'submit_class' => 'js-sats-filter-submit',
                    'clear_id'     => 'sats-clear-filters',
                    'nonce'        => 'av_sats_filter_nonce',
                    'fields'       => [
                        [ 'name' => 'nombre-cliente', 'label' => 'Cliente',    'placeholder' => 'Nombre del cliente', 'quick' => true ],
                        [ 'name' => 'dni-cliente',    'label' => 'DNI cliente', 'placeholder' => '12345678A', 'quick' => true ],
                        [ 'name' => 'numero-sat',     'label' => 'Número SAT',  'placeholder' => 'Ej. 128', 'quick' => true ],
                        [
                            'name'    => 'estado',
                            'label'   => 'Estado',
                            'type'    => 'select',
                            'options' => array_merge( [ '' => 'Todos' ], [
                                'diagnosticar'   => 'Por diagnosticar',
                                'cliente-espera' => 'En espera cliente',
                                'pieza'          => 'Esperando pieza',
                                'otro-sat'       => 'Enviado otro SAT',
                                'reparar'        => 'Por reparar',
                                'reparado'       => 'Reparado',
                                'no-reparado'    => 'No reparado',
                                'garantia'       => 'Garantía',
                                'finalizado'     => 'Finalizado',
                            ] ),
                        ],
                        [ 'name' => 'importe', 'label' => 'Importe', 'type' => 'number', 'placeholder' => '0,00', 'step' => '0.01', 'min' => '0', 'suffix' => '€' ],
                        [ 'name' => 'fecha',   'label' => 'Fecha entrada', 'type' => 'date' ],
                    ],
                ];
                include locate_template( 'components/c-filters.php' );
                ?>
                <div class="c-list-cpt-sats__wrapper-menu o-font-display-caption">
                    <div class="c-list-cpt-sats__menu-item o-button o-button--style-1 js-filter-all" data-id="todos">Todos</div>
                    <div class="c-list-cpt-sats__menu-item o-button o-button--style-1 c-list-cpt-sats__menu-item--active js-filter-all" data-id="en-curso">En curso</div>
                    <div class="c-list-cpt-sats__menu-item o-button o-button--style-1 js-filter-all" data-id="finalizados">Finalizados</div>
                </div>
                <div class="c-list-cpt-sats__results-wrapper">
                    <div class="c-list-cpt-sats__loader js-sats-loader">
                        <span class="c-list-cpt-sats__loader-spinner"></span>
                    </div>
                    <div id="sats-list" class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                        <?php include locate_template('components/c-list-cpt-sats-list.php'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="c-list-cpt-sats__finalize-banner js-list-cpt-sats__finalize-banner">
            <div class="c-list-cpt-sats__finalize-banner-inner">
                <p class="c-list-cpt-sats__finalize-banner-title js-list-cpt-sats__finalize-banner-title">Faltan datos para finalizar el SAT</p>
                <p class="c-list-cpt-sats__finalize-banner-text js-list-cpt-sats__finalize-banner-text"></p>
                <div class="c-list-cpt-sats__finalize-banner-field js-list-cpt-sats__finalize-banner-field-repair">
                    <label>Reparación</label>
                    <textarea rows="3" class="js-list-cpt-sats__finalize-banner-repair-input" placeholder="Describe la acción realizada…"></textarea>
                </div>
                <div class="c-list-cpt-sats__finalize-banner-field js-list-cpt-sats__finalize-banner-field-price">
                    <label>Precio final</label>
                    <div class="c-list-cpt-sats__finalize-banner-price-wrap">
                        <input type="number" step="any" min="0" class="js-list-cpt-sats__finalize-banner-price-input">
                        <span>€</span>
                    </div>
                </div>
                <div class="c-list-cpt-sats__finalize-banner-field js-list-cpt-sats__finalize-banner-field-payment">
                    <label>Tipo de pago</label>
                    <select class="js-list-cpt-sats__finalize-banner-payment-select">
                        <option value="">Seleccione...</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="efectivo">Efectivo</option>
                    </select>
                </div>
                <p class="c-list-cpt-sats__finalize-banner-error js-list-cpt-sats__finalize-banner-error"></p>
                <div class="c-list-cpt-sats__finalize-banner-ctas">
                    <button type="button" class="o-button o-button--style-2 js-list-cpt-sats__finalize-banner-cancel">Cancelar</button>
                    <button type="button" class="o-button o-button--style-1 js-list-cpt-sats__finalize-banner-confirm">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
