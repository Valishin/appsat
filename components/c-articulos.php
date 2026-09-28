<?php
// Espera en el scope (desde templates/template-articulos.php): $posts,
// $articulos, $categorias, $paged, $editando, $abrir_form y los flags
// $articulo_creado / $articulo_actualizado / $articulo_desactivado /
// $articulo_reactivado / $stock_ajustado.
?>
<div class="c-list-cpt-sats c-articulos">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">

                <?php if ( $abrir_form ) : ?>

                    <?php include( locate_template( 'components/c-articulo-form.php' ) ); ?>

                <?php else : ?>

                    <div class="c-list-cpt-sats__toolbar">
                        <div id="articulos-count" class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                            <?php include locate_template( 'components/c-articulos-count.php' ); ?>
                        </div>
                    </div>

                    <?php if ( $articulo_creado ) : ?>
                        <div class="c-devices__notice"><strong>Artículo creado correctamente.</strong></div>
                    <?php elseif ( $articulo_actualizado ) : ?>
                        <div class="c-devices__notice"><strong>Artículo actualizado correctamente.</strong></div>
                    <?php elseif ( $articulo_desactivado ) : ?>
                        <div class="c-devices__notice"><strong>Artículo desactivado.</strong></div>
                    <?php elseif ( $articulo_reactivado ) : ?>
                        <div class="c-devices__notice"><strong>Artículo reactivado.</strong></div>
                    <?php elseif ( $stock_ajustado ) : ?>
                        <div class="c-devices__notice"><strong>Stock ajustado correctamente.</strong></div>
                    <?php endif; ?>

                    <?php
                    $filters = [
                        'form_class'   => 'js-articulos-filter-form',
                        'submit_class' => 'js-articulos-filter-submit',
                        'clear_id'     => 'articulos-clear-filters',
                        'nonce'        => 'av_articulos_filter_nonce',
                        'fields'       => [
                            [ 'name' => 'nombre-articulo', 'label' => 'Buscar', 'placeholder' => 'Nombre, SKU, EAN, marca, modelo…', 'quick' => true ],
                            [
                                'name'    => 'categoria-articulo',
                                'label'   => 'Categoría',
                                'type'    => 'select',
                                'quick'   => true,
                                'options' => array_merge( [ '' => 'Todas' ], array_combine(
                                    wp_list_pluck( $categorias, 'term_id' ),
                                    wp_list_pluck( $categorias, 'name' )
                                ) ),
                            ],
                            [ 'name' => 'marca-articulo', 'label' => 'Marca', 'placeholder' => 'Ej. Apple' ],
                            [
                                'name'    => 'tipo-articulo',
                                'label'   => 'Tipo',
                                'type'    => 'select',
                                'options' => array_merge( [ '' => 'Todos' ], av_articulo_type_choices() ),
                            ],
                            [
                                'name'    => 'estado-articulo',
                                'label'   => 'Estado',
                                'type'    => 'select',
                                'options' => [ '' => 'Todos', 'active' => 'Activo', 'inactive' => 'Inactivo' ],
                            ],
                            [
                                'name'    => 'stock-articulo',
                                'label'   => 'Stock',
                                'type'    => 'select',
                                'quick'   => true,
                                'options' => [ '' => 'Todos', 'bajo' => 'Stock bajo', 'sin-stock' => 'Sin stock' ],
                            ],
                        ],
                    ];
                    include locate_template( 'components/c-filters.php' );
                    ?>

                    <div class="c-list-cpt-sats__results-wrapper">
                        <div class="c-list-cpt-sats__loader js-articulos-loader">
                            <span class="c-list-cpt-sats__loader-spinner"></span>
                        </div>
                        <div id="articulos-list" class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                            <?php include locate_template( 'components/c-articulos-list.php' ); ?>
                        </div>
                    </div>

                    <!-- "Nuevo artículo" fijo abajo a la derecha: mismo componente/CSS
                         que "Nuevo pedido" (un solo botón -> modificador --solo, ver
                         components/c-sat-form.php). -->
                    <div class="c-sat-form__wrapper-ctas">
                        <div class="c-sat-form__ctas-inner">
                            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary c-sat-form__ctas-group--solo">
                                <a href="<?php echo esc_url( add_query_arg( 'new', '1', av_articulos_page_url() ) ); ?>" class="c-sat-form__cta c-sat-form__cta--primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Nuevo artículo</span>
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <?php include( locate_template( 'components/c-articulo-stock-modal.php' ) ); ?>
</div>
