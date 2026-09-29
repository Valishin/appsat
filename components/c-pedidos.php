<?php
// Espera en el scope (desde templates/template-pedidos.php): $posts, $pedidos,
// $paged, $editando, $abrir_form. Mismo patrón que components/c-articulos.php:
// un único punto de entrada que decide entre listado y formulario (el
// formulario es también el detalle del pedido).
?>
<div class="c-list-cpt-sats c-pedidos">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">

                <?php if ( $abrir_form ) : ?>

                    <?php include( locate_template( 'components/c-pedido-form.php' ) ); ?>

                <?php else : ?>

                    <div class="c-list-cpt-sats__toolbar">
                        <div id="pedidos-count" class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                            <?php include locate_template( 'components/c-pedidos-count.php' ); ?>
                        </div>
                    </div>

                    <?php if ( isset( $_GET['cancelado'] ) ) : ?>
                        <div class="c-devices__notice"><strong>Pedido cancelado.</strong></div>
                    <?php endif; ?>

                    <?php
                    $filters = [
                        'form_class'   => 'js-pedidos-filter-form',
                        'submit_class' => 'js-pedidos-filter-submit',
                        'clear_id'     => 'pedidos-clear-filters',
                        'nonce'        => 'av_pedidos_filter_nonce',
                        'fields'       => [
                            [ 'name' => 'buscar-pedido', 'label' => 'Buscar', 'placeholder' => 'Número, cliente, teléfono, producto…', 'quick' => true ],
                            [ 'name' => 'filter', 'type' => 'hidden' ],
                        ],
                    ];
                    include locate_template( 'components/c-filters.php' );
                    ?>

                    <?php
                    // Todos/En curso/Finalizados: mismo diseño y mismo
                    // mecanismo que el filtro de Particulares/Profesionales
                    // de Clientes (escribe en el campo oculto de arriba y
                    // relanza la búsqueda async, ver av_pill_tab_filter en
                    // src/js/app.js) — "Finalizado" aquí es "Entregado" o
                    // "Cancelado" (av_pedidos_current_tab(), inc/pedidos/crud.php).
                    $pedido_tab_filtro = isset( $_GET['filter'] ) ? sanitize_text_field( $_GET['filter'] ) : 'en-curso';
                    ?>
                    <div class="c-list-cpt-sats__wrapper-menu js-pedido-status-filter">
                        <div class="c-list-cpt-sats__menu-item js-pill-tab-btn<?php echo $pedido_tab_filtro === 'todos' ? ' c-list-cpt-sats__menu-item--active' : ''; ?>" data-value="todos">
                            <span>Todos</span>
                        </div>
                        <div class="c-list-cpt-sats__menu-item js-pill-tab-btn<?php echo $pedido_tab_filtro === 'en-curso' ? ' c-list-cpt-sats__menu-item--active' : ''; ?>" data-value="en-curso">
                            <span>En curso</span>
                        </div>
                        <div class="c-list-cpt-sats__menu-item js-pill-tab-btn<?php echo $pedido_tab_filtro === 'finalizados' ? ' c-list-cpt-sats__menu-item--active' : ''; ?>" data-value="finalizados">
                            <span>Finalizados</span>
                        </div>
                    </div>

                    <div class="c-list-cpt-sats__results-wrapper">
                        <div class="c-list-cpt-sats__loader js-pedidos-loader">
                            <span class="c-list-cpt-sats__loader-spinner"></span>
                        </div>
                        <div id="pedidos-list" class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                            <?php include locate_template( 'components/c-pedidos-list.php' ); ?>
                        </div>
                    </div>

                    <!-- "Nuevo pedido" fijo abajo a la derecha: mismo componente/CSS
                         que la barra de acciones del formulario de SAT (un solo botón
                         -> modificador --solo, ver components/c-sat-form.php). -->
                    <div class="c-sat-form__wrapper-ctas">
                        <div class="c-sat-form__ctas-inner">
                            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary c-sat-form__ctas-group--solo">
                                <a href="<?php echo esc_url( add_query_arg( 'new', '1', av_pedidos_page_url() ) ); ?>" class="c-sat-form__cta c-sat-form__cta--primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Nuevo pedido</span>
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
