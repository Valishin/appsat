
<div class="c-device-categories">
    <div class="c-device-categories__inner">
        <div class="c-device-categories__container o-container">
            <div class="c-device-categories__col o-col-12@md o-col-8@sm o-col-4@xs">

                <div class="c-device-categories__toolbar">
                    <div class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                        <?php echo sprintf( _n( 'Total %d categoría', 'Total %d categorías', count( $categorias ), 'appsat' ), count( $categorias ) ); ?>
                    </div>
                    <button type="button" class="c-device-categories__new-btn js-device-categories-open-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Nueva categoría</span>
                    </button>
                </div>

                <?php if ( $categoria_creada ) : ?>
                    <div class="c-device-categories__notice">
                        <strong>Categoría creada correctamente.</strong>
                    </div>
                <?php elseif ( $categoria_actualizada ) : ?>
                    <div class="c-device-categories__notice">
                        <strong>Categoría actualizada correctamente.</strong>
                    </div>
                <?php elseif ( $categoria_eliminada ) : ?>
                    <div class="c-device-categories__notice">
                        <strong>Categoría eliminada.</strong> Los dispositivos que la tenían asignada se han quedado sin categoría.
                    </div>
                <?php endif; ?>

                <?php
                $errores_categoria = array(
                    'missing' => 'Escribe un nombre para la categoría.',
                    'exists'  => 'Ya existe una categoría con ese nombre.',
                    'unknown' => 'No se ha podido completar la acción.',
                );
                if ( isset( $_GET['error'] ) && isset( $errores_categoria[ $_GET['error'] ] ) ) : ?>
                    <div class="c-device-categories__error"><?php echo esc_html( $errores_categoria[ $_GET['error'] ] ); ?></div>
                <?php endif; ?>

                <div class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                    <table>
                        <thead>
                            <tr>
                                <th class="c-device-categories__order-col"></th>
                                <th>Nombre</th>
                                <th>Dispositivos</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="js-device-categories-tbody">
                            <?php if ( empty( $categorias ) ) : ?>
                            <tr class="c-list-cpt-sats__row">
                                <td colspan="4" class="c-device-categories__empty">Todavía no hay categorías. Crea la primera con «Nueva categoría».</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ( $categorias as $categoria ) : ?>
                                <tr class="c-list-cpt-sats__row js-device-category-row" data-id="<?php echo esc_attr( $categoria->term_id ); ?>" data-nombre="<?php echo esc_attr( $categoria->name ); ?>">
                                    <td class="c-device-categories__order-col">
                                        <span class="c-device-categories__drag-handle js-device-categories-drag-handle" draggable="true" title="Arrastrar para reordenar">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html( $categoria->name ); ?></td>
                                    <td><?php echo intval( $categoria->count ); ?></td>
                                    <td>
                                        <div class="c-device-categories__actions">
                                            <button type="button"
                                                class="c-device-categories__action-btn js-device-categories-edit"
                                                title="Editar"
                                                data-id="<?php echo esc_attr( $categoria->term_id ); ?>"
                                                data-nombre="<?php echo esc_attr( $categoria->name ); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>

                                            <a class="c-device-categories__action-btn c-device-categories__action-btn--danger"
                                               href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'av_eliminar_device_category', 'id' => $categoria->term_id ], admin_url( 'admin-post.php' ) ), 'av_eliminar_device_category_' . $categoria->term_id ) ); ?>"
                                               title="Eliminar"
                                               onclick="return confirm('¿Eliminar la categoría «<?php echo esc_js( $categoria->name ); ?>»?<?php echo $categoria->count ? ' ' . intval( $categoria->count ) . ' dispositivo(s) se quedarán sin categoría.' : ''; ?> Esta acción no se puede deshacer.');">
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

    <!-- Aviso flotante: origen/destino tras arrastrar una categoría -->
    <div class="c-device-categories__toast js-device-categories-toast" role="status" aria-live="polite"></div>

    <!-- ── Modal crear / editar categoría ────────────────────────────────── -->
    <div class="c-device-categories__modal js-device-categories-modal<?php echo $abrir_modal ? ' is-active' : ''; ?>">
        <div class="c-device-categories__modal-overlay js-device-categories-close-modal"></div>
        <div class="c-device-categories__modal-panel c-device-categories__modal-panel--sm">
            <div class="c-device-categories__modal-head">
                <span class="c-device-categories__modal-title js-device-categories-modal-title"><?php echo $editando ? 'Editar categoría' : 'Nueva categoría'; ?></span>
                <button type="button" class="c-device-categories__modal-close js-device-categories-close-modal" aria-label="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form class="c-device-categories__form js-device-categories-form" method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                <?php wp_nonce_field( 'av_device_category_nonce', 'nonce' ); ?>
                <input type="hidden" name="action" value="av_guardar_device_category">
                <input type="hidden" name="id" class="js-device-categories-field-id" value="<?php echo $editando ? esc_attr( $editando->term_id ) : ''; ?>">

                <div class="c-device-categories__form-grid">
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="dc-nombre">Nombre</label>
                        <input id="dc-nombre" class="c-list-cpt-sats__search js-device-categories-field-nombre" type="text" name="nombre" placeholder="Ej. Móvil" value="<?php echo $editando ? esc_attr( $editando->name ) : ''; ?>" required>
                    </div>
                </div>

                <div class="c-device-categories__form-actions">
                    <button type="button" class="c-device-categories__cancel-btn js-device-categories-close-modal">Cancelar</button>
                    <button type="submit" class="o-button o-button--style-1 js-device-categories-submit"><?php echo $editando ? 'Guardar cambios' : 'Crear categoría'; ?></button>
                </div>
            </form>
        </div>
    </div>

</div>
