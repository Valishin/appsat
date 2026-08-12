
<div class="c-servicios">
    <div class="c-servicios__inner">
        <div class="c-servicios__container o-container">
            <div class="c-servicios__col o-col-12@md o-col-8@sm o-col-4@xs">

                <div class="c-servicios__toolbar">
                    <div class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                        <?php echo sprintf( _n( 'Total %d servicio', 'Total %d servicios', count( $servicios ), 'appsat' ), count( $servicios ) ); ?>
                    </div>
                    <button type="button" class="c-servicios__new-btn js-servicios-open-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        <span>Nuevo servicio</span>
                    </button>
                </div>

                <?php if ( $servicio_creado ) : ?>
                    <div class="c-servicios__notice">
                        <strong>Servicio creado correctamente.</strong>
                    </div>
                <?php elseif ( $servicio_actualizado ) : ?>
                    <div class="c-servicios__notice">
                        <strong>Servicio actualizado correctamente.</strong>
                    </div>
                <?php elseif ( $servicio_eliminado ) : ?>
                    <div class="c-servicios__notice">
                        <strong>Servicio eliminado.</strong>
                    </div>
                <?php endif; ?>

                <?php
                $errores_servicio = array(
                    'missing' => 'Indica un título y un precio válido (0 o mayor).',
                    'unknown' => 'No se ha podido completar la acción.',
                );
                if ( isset( $_GET['error'] ) && isset( $errores_servicio[ $_GET['error'] ] ) ) : ?>
                    <div class="c-servicios__error"><?php echo esc_html( $errores_servicio[ $_GET['error'] ] ); ?></div>
                <?php endif; ?>

                <div class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Precio</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( empty( $servicios ) ) : ?>
                            <tr class="c-list-cpt-sats__row">
                                <td colspan="3" class="c-servicios__empty">Todavía no hay servicios. Crea el primero con «Nuevo servicio».</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ( $servicios as $servicio ) :
                                $precio = floatval( get_post_meta( $servicio->ID, '_servicio_precio', true ) );
                            ?>
                                <tr class="c-list-cpt-sats__row">
                                    <td><?php echo esc_html( $servicio->post_title ); ?></td>
                                    <td><?php echo esc_html( number_format( $precio, 2, ',', '.' ) ); ?> €</td>
                                    <td>
                                        <div class="c-servicios__actions">
                                            <button type="button"
                                                class="c-servicios__action-btn js-servicios-edit"
                                                title="Editar"
                                                data-id="<?php echo esc_attr( $servicio->ID ); ?>"
                                                data-titulo="<?php echo esc_attr( $servicio->post_title ); ?>"
                                                data-precio="<?php echo esc_attr( number_format( $precio, 2, '.', '' ) ); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>

                                            <a class="c-servicios__action-btn c-servicios__action-btn--danger"
                                               href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'av_eliminar_servicio', 'id' => $servicio->ID ], admin_url( 'admin-post.php' ) ), 'av_eliminar_servicio_' . $servicio->ID ) ); ?>"
                                               title="Eliminar"
                                               onclick="return confirm('¿Eliminar el servicio «<?php echo esc_js( $servicio->post_title ); ?>»? Esta acción no se puede deshacer.');">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Modal crear / editar servicio ─────────────────────────────────── -->
    <div class="c-servicios__modal js-servicios-modal<?php echo $abrir_modal ? ' is-active' : ''; ?>">
        <div class="c-servicios__modal-overlay js-servicios-close-modal"></div>
        <div class="c-servicios__modal-panel c-servicios__modal-panel--sm">
            <div class="c-servicios__modal-head">
                <span class="c-servicios__modal-title js-servicios-modal-title"><?php echo $editando ? 'Editar servicio' : 'Nuevo servicio'; ?></span>
                <button type="button" class="c-servicios__modal-close js-servicios-close-modal" aria-label="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form class="c-servicios__form js-servicios-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'av_servicio_nonce', 'nonce' ); ?>
                <input type="hidden" name="action" value="av_guardar_servicio">
                <input type="hidden" name="id" class="js-servicios-field-id" value="<?php echo $editando ? esc_attr( $editando->ID ) : ''; ?>">

                <div class="c-servicios__form-grid">
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="s-titulo">Título</label>
                        <input id="s-titulo" class="c-list-cpt-sats__search js-servicios-field-titulo" type="text" name="titulo" placeholder="Ej. Cambio de pantalla" value="<?php echo $editando ? esc_attr( $editando->post_title ) : ''; ?>" required>
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="s-precio">Precio</label>
                        <div class="c-servicios__price-input">
                            <input id="s-precio" class="c-list-cpt-sats__search js-servicios-field-precio" type="number" step="0.01" min="0" name="precio" placeholder="0,00" value="<?php echo $editando ? esc_attr( number_format( floatval( get_post_meta( $editando->ID, '_servicio_precio', true ) ), 2, '.', '' ) ) : ''; ?>" required>
                            <span>€</span>
                        </div>
                    </div>
                </div>

                <div class="c-servicios__form-actions">
                    <button type="button" class="c-servicios__cancel-btn js-servicios-close-modal">Cancelar</button>
                    <button type="submit" class="o-button o-button--style-1 js-servicios-submit"><?php echo $editando ? 'Guardar cambios' : 'Crear servicio'; ?></button>
                </div>
            </form>
        </div>
    </div>

</div>
