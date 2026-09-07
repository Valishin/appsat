
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
            <div class="c-list-cpt-sats__finalize-banner-inner c-list-cpt-sats__finalize-banner-inner--wide">
                <p class="c-list-cpt-sats__finalize-banner-title js-list-cpt-sats__finalize-banner-title">Faltan datos para finalizar el SAT</p>
                <p class="c-list-cpt-sats__finalize-banner-text js-list-cpt-sats__finalize-banner-text"></p>
                <!-- Formulario "local" (no se llega a enviar, es solo para que el
                     widget de reparación/piezas encuentre un campo [name="price"]
                     al que sumar el total automáticamente, igual que en el
                     detalle del SAT). -->
                <!-- OJO: NO llevar la clase "c-sat-form__form" aquí. Varias
                     funciones del detalle del SAT (av_check_form_changed,
                     validate_finalizado...) hacen document.querySelector('.c-sat-form__form')
                     y se quedan con el PRIMERO que encuentren en la página:
                     si este formulario también la llevara, en el listado de
                     SATs se "engancharían" a este en vez de no hacer nada,
                     provocando entre otras cosas el aviso de "cambios sin
                     guardar" al salir aunque ya se hubiera guardado por AJAX.
                     recalcTotal() en av_repair_list sigue encontrando este
                     formulario igual, por su segundo intento .closest('form'). -->
                <form class="js-list-cpt-sats__finalize-banner-form" onsubmit="return false">
                    <div class="c-list-cpt-sats__finalize-banner-field js-list-cpt-sats__finalize-banner-field-repair">
                        <label>Reparación</label>
                        <div class="c-sat-form__repair-box js-repair-widget">
                            <div class="c-sat-form__repair-add">
                                <input type="text" class="c-sat-form__input js-repair-input" placeholder="Describe la acción realizada…">
                                <input type="number" step="any" class="c-sat-form__input c-sat-form__repair-price-input js-repair-price" placeholder="Precio €">
                                <button type="button" class="c-sat-form__repair-btn js-repair-add">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Añadir
                                </button>
                            </div>
                            <ul class="c-sat-form__repair-list js-repair-list"></ul>
                            <input type="hidden" class="js-repair-hidden js-list-cpt-sats__finalize-banner-repair-hidden" value="[]">
                        </div>
                    </div>
                    <div class="c-list-cpt-sats__finalize-banner-field js-list-cpt-sats__finalize-banner-field-parts">
                        <label>Piezas pedidas</label>
                        <div class="c-sat-form__repair-box js-repair-widget">
                            <div class="c-sat-form__repair-add">
                                <input type="text" class="c-sat-form__input js-repair-input" placeholder="Añade una pieza o material…">
                                <input type="number" step="any" class="c-sat-form__input c-sat-form__repair-price-input js-repair-price" placeholder="Precio €">
                                <button type="button" class="c-sat-form__repair-btn js-repair-add">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Añadir
                                </button>
                            </div>
                            <ul class="c-sat-form__repair-list js-repair-list"></ul>
                            <input type="hidden" class="js-repair-hidden js-list-cpt-sats__finalize-banner-parts-hidden" value="[]">
                        </div>
                    </div>
                    <div class="c-list-cpt-sats__finalize-banner-field js-list-cpt-sats__finalize-banner-field-price">
                        <label>Precio final</label>
                        <div class="c-list-cpt-sats__finalize-banner-price-wrap">
                            <input type="number" step="any" min="0" name="price" readonly class="js-list-cpt-sats__finalize-banner-price-input" title="Se calcula solo sumando las líneas de Reparación y Piezas pedidas">
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
                </form>
                <p class="c-list-cpt-sats__finalize-banner-error js-list-cpt-sats__finalize-banner-error"></p>
                <div class="c-list-cpt-sats__finalize-banner-ctas">
                    <button type="button" class="o-button o-button--style-2 js-list-cpt-sats__finalize-banner-cancel">Cancelar</button>
                    <button type="button" class="o-button o-button--style-1 js-list-cpt-sats__finalize-banner-confirm">Guardar</button>
                </div>
            </div>
        </div>

        <!-- Al finalizar un SAT desde el listado: preguntar si se marca ya la
             entrega como firmada (no es obligatorio). -->
        <div class="c-list-cpt-sats__finalize-banner js-list-cpt-sats__delivery-modal">
            <div class="c-list-cpt-sats__finalize-banner-inner">
                <p class="c-list-cpt-sats__finalize-banner-title">¿Deseas marcar como firmada la entrega de este SAT?</p>
                <p class="c-list-cpt-sats__finalize-banner-text">El cliente recoge el equipo ahora mismo y firma la entrega.</p>
                <div class="c-list-cpt-sats__finalize-banner-ctas">
                    <button type="button" class="o-button o-button--style-2 js-list-cpt-sats__delivery-modal-no">Cancelar</button>
                    <button type="button" class="o-button o-button--style-1 js-list-cpt-sats__delivery-modal-yes">Sí, firmada</button>
                </div>
            </div>
        </div>
    </div>
</div>
