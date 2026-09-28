<?php
// Espera en el scope (desde templates/template-presupuestos.php): $posts,
// $presupuestos, $paged, $editando, $abrir_form. Mismo patrón que
// components/c-pedidos.php: un único punto de entrada que decide entre
// listado y formulario.
?>
<div class="c-list-cpt-sats c-pedidos">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">

                <?php if ( $abrir_form ) : ?>

                    <?php include( locate_template( 'components/c-presupuesto-form.php' ) ); ?>

                <?php else : ?>

                    <div class="c-list-cpt-sats__toolbar">
                        <div id="presupuestos-count" class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                            <?php include locate_template( 'components/c-presupuestos-count.php' ); ?>
                        </div>
                    </div>

                    <?php if ( isset( $_GET['eliminado'] ) ) : ?>
                        <div class="c-devices__notice"><strong>Presupuesto eliminado.</strong></div>
                    <?php endif; ?>

                    <?php
                    $filters = [
                        'form_class'   => 'js-presupuestos-filter-form',
                        'submit_class' => 'js-presupuestos-filter-submit',
                        'clear_id'     => 'presupuestos-clear-filters',
                        'nonce'        => 'av_presupuestos_filter_nonce',
                        'fields'       => [
                            [ 'name' => 'buscar-presupuesto', 'label' => 'Buscar', 'placeholder' => 'Número, cliente, artículo…', 'quick' => true ],
                        ],
                    ];
                    include locate_template( 'components/c-filters.php' );
                    ?>

                    <div class="c-list-cpt-sats__results-wrapper">
                        <div class="c-list-cpt-sats__loader js-presupuestos-loader">
                            <span class="c-list-cpt-sats__loader-spinner"></span>
                        </div>
                        <div id="presupuestos-list" class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                            <?php include locate_template( 'components/c-presupuestos-list.php' ); ?>
                        </div>
                    </div>

                    <!-- "Nuevo presupuesto" fijo abajo a la derecha: mismo componente/CSS
                         que la barra de acciones del formulario de SAT. -->
                    <div class="c-sat-form__wrapper-ctas">
                        <div class="c-sat-form__ctas-inner">
                            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary c-sat-form__ctas-group--solo">
                                <a href="<?php echo esc_url( add_query_arg( 'new', '1', av_presupuestos_page_url() ) ); ?>" class="c-sat-form__cta c-sat-form__cta--primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Nuevo presupuesto</span>
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
