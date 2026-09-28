<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">

                <div class="c-list-cpt-sats__toolbar">
                    <div id="facturas-count" class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                        <?php include locate_template( 'components/c-list-facturas-count.php' ); ?>
                    </div>
                </div>

                <?php
                $filters = [
                    'form_class'   => 'js-facturas-filter-form',
                    'submit_class' => 'js-facturas-filter-submit',
                    'clear_id'     => 'facturas-clear-filters',
                    'nonce'        => 'av_facturas_filter_nonce',
                    'fields'       => [
                        [ 'name' => 'numero-factura', 'label' => 'Nº Factura', 'placeholder' => 'F-2026-001', 'quick' => true ],
                        [ 'name' => 'nombre-cliente', 'label' => 'Cliente',    'placeholder' => 'Nombre del cliente', 'quick' => true ],
                        [ 'name' => 'numero-sat',     'label' => 'Nº SAT',     'placeholder' => '335', 'quick' => true ],
                        [
                            'name'    => 'estado-pago',
                            'label'   => 'Estado de pago',
                            'type'    => 'select',
                            'options' => [ '' => 'Todas', 'pagadas' => 'Pagadas', 'pendientes' => 'Pendientes' ],
                        ],
                        [ 'name' => 'fecha-desde', 'label' => 'Desde', 'type' => 'date' ],
                        [ 'name' => 'fecha-hasta', 'label' => 'Hasta', 'type' => 'date' ],
                    ],
                ];
                include locate_template( 'components/c-filters.php' );
                ?>

                <div class="c-list-cpt-sats__results-wrapper">
                    <div class="c-list-cpt-sats__loader js-facturas-loader">
                        <span class="c-list-cpt-sats__loader-spinner"></span>
                    </div>
                    <div id="facturas-list" class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                        <?php include locate_template( 'components/c-list-facturas-list.php' ); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* ── Badge pago ── */
.fac-pago-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    border: none;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    cursor: default;
    white-space: nowrap;
    font-family: inherit;
    position: relative;
}
.fac-pago-badge--ok  { background: rgba(22,163,74,.12); color: #15803d; }
.fac-pago-badge--pte { background: rgba(234,88,12,.10); color: #c2410c; }
</style>
