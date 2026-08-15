
<?php
// Datos del dispositivo en edición (si los hay), para precargar el formulario.
$editando_brand    = '';
$editando_model    = '';
$editando_ref      = '';
$editando_maker    = '';
$editando_specs    = [];
$editando_cat_id   = 0;
$editando_cat_slug = '';

if ( $editando ) {
    $editando_brand  = get_post_meta( $editando->ID, 'cpt-device__brand', true );
    $editando_model  = get_post_meta( $editando->ID, 'cpt-device__model', true );
    $editando_ref    = get_post_meta( $editando->ID, 'cpt-device__reference', true );
    $editando_maker  = get_post_meta( $editando->ID, 'cpt-device__manufacturer', true );
    $raw_specs       = get_post_meta( $editando->ID, 'cpt-device__specs', true );
    $decoded_specs   = $raw_specs ? json_decode( $raw_specs, true ) : [];
    $editando_specs  = is_array( $decoded_specs ) ? $decoded_specs : [];

    $terms = get_the_terms( $editando->ID, 'cpt-device-category' );
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        $editando_cat_id   = $terms[0]->term_id;
        $editando_cat_slug = $terms[0]->slug;
    }
}

$field_groups = av_device_field_groups();

// Qué bloque de campos técnicos debe verse ya al abrir la modal (edición):
// el de su categoría si tiene uno específico, si no el genérico de notas.
$initial_specs_group = '';
if ( $editando ) {
    $initial_specs_group = isset( $field_groups[ $editando_cat_slug ] ) ? $editando_cat_slug : '__generic__';
}
?>
<div class="c-devices">
    <div class="c-devices__inner">
        <div class="c-devices__container o-container">
            <div class="c-devices__col o-col-12@md o-col-8@sm o-col-4@xs">

                <div class="c-devices__toolbar">
                    <div class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                        <?php echo sprintf( _n( 'Total %d dispositivo', 'Total %d dispositivos', count( $devices ), 'appsat' ), count( $devices ) ); ?>
                    </div>
                    <button type="button" class="c-devices__new-btn js-devices-open-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/><line x1="12" y1="9" x2="12" y2="15"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                        <span>Nuevo dispositivo</span>
                    </button>
                </div>

                <?php if ( $device_creado ) : ?>
                    <div class="c-devices__notice">
                        <strong>Dispositivo creado correctamente.</strong>
                    </div>
                <?php elseif ( $device_actualizado ) : ?>
                    <div class="c-devices__notice">
                        <strong>Dispositivo actualizado correctamente.</strong>
                    </div>
                <?php elseif ( $device_eliminado ) : ?>
                    <div class="c-devices__notice">
                        <strong>Dispositivo eliminado.</strong>
                    </div>
                <?php endif; ?>

                <?php
                $errores_device = array(
                    'missing' => 'Indica al menos el nombre y la categoría del dispositivo.',
                    'unknown' => 'No se ha podido completar la acción.',
                );
                if ( isset( $_GET['error'] ) && isset( $errores_device[ $_GET['error'] ] ) ) : ?>
                    <div class="c-devices__error"><?php echo esc_html( $errores_device[ $_GET['error'] ] ); ?></div>
                <?php endif; ?>

                <div class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( empty( $devices ) ) : ?>
                            <tr class="c-list-cpt-sats__row">
                                <td colspan="5" class="c-devices__empty">Todavía no hay dispositivos. Crea el primero con «Nuevo dispositivo».</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ( $devices as $device ) :
                                $d_brand = get_post_meta( $device->ID, 'cpt-device__brand', true );
                                $d_model = get_post_meta( $device->ID, 'cpt-device__model', true );
                                $d_terms = get_the_terms( $device->ID, 'cpt-device-category' );
                                $d_cat   = ( ! empty( $d_terms ) && ! is_wp_error( $d_terms ) ) ? $d_terms[0] : null;
                            ?>
                                <tr class="c-list-cpt-sats__row">
                                    <td><?php echo esc_html( $device->post_title ); ?></td>
                                    <td><?php echo $d_cat ? esc_html( $d_cat->name ) : '—'; ?></td>
                                    <td><?php echo esc_html( $d_brand ?: '—' ); ?></td>
                                    <td><?php echo esc_html( $d_model ?: '—' ); ?></td>
                                    <td>
                                        <div class="c-devices__actions">
                                            <a class="c-devices__action-btn js-devices-edit"
                                                title="Editar"
                                                href="<?php echo esc_url( add_query_arg( 'edit', $device->ID, get_permalink() ) ); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>

                                            <a class="c-devices__action-btn c-devices__action-btn--danger"
                                               href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'av_eliminar_device', 'id' => $device->ID ], admin_url( 'admin-post.php' ) ), 'av_eliminar_device_' . $device->ID ) ); ?>"
                                               title="Eliminar"
                                               onclick="return confirm('¿Eliminar el dispositivo «<?php echo esc_js( $device->post_title ); ?>»? Esta acción no se puede deshacer.');">
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

    <!-- ── Modal crear / editar dispositivo ──────────────────────────────── -->
    <div class="c-devices__modal js-devices-modal<?php echo $abrir_modal ? ' is-active' : ''; ?>">
        <div class="c-devices__modal-overlay js-devices-close-modal"></div>
        <div class="c-devices__modal-panel">
            <div class="c-devices__modal-head">
                <span class="c-devices__modal-title js-devices-modal-title"><?php echo $editando ? 'Editar dispositivo' : 'Nuevo dispositivo'; ?></span>
                <button type="button" class="c-devices__modal-close js-devices-close-modal" aria-label="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form class="c-devices__form js-devices-form" method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                <?php wp_nonce_field( 'av_device_nonce', 'nonce' ); ?>
                <input type="hidden" name="action" value="av_guardar_device">
                <input type="hidden" name="id" class="js-devices-field-id" value="<?php echo $editando ? esc_attr( $editando->ID ) : ''; ?>">

                <div class="c-devices__section-title">Datos básicos</div>
                <div class="c-devices__form-grid">
                    <div class="c-list-cpt-sats__field c-devices__field--full">
                        <label class="c-list-cpt-sats__field-label" for="dev-nombre">Nombre del dispositivo</label>
                        <input id="dev-nombre" class="c-list-cpt-sats__search js-devices-field-nombre" type="text" name="nombre" placeholder="Ej. iPhone 15 Pro Max" value="<?php echo $editando ? esc_attr( $editando->post_title ) : ''; ?>" required>
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="dev-categoria">Categoría</label>
                        <div class="c-devices__category-row">
                            <select id="dev-categoria" class="c-list-cpt-sats__search js-devices-field-categoria" name="categoria" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ( $categorias as $categoria ) : ?>
                                <option value="<?php echo esc_attr( $categoria->term_id ); ?>" data-slug="<?php echo esc_attr( $categoria->slug ); ?>" <?php selected( $editando_cat_id, $categoria->term_id ); ?>><?php echo esc_html( $categoria->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="c-devices__category-add-btn js-devices-category-add" title="Crear una categoría nueva">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="c-devices__category-new js-devices-category-new is-hidden">
                            <input type="text" class="c-list-cpt-sats__search js-devices-category-new-input" placeholder="Nombre de la categoría nueva">
                            <button type="button" class="c-devices__category-new-confirm js-devices-category-new-confirm">Añadir</button>
                            <button type="button" class="c-devices__category-new-cancel js-devices-category-new-cancel" aria-label="Cancelar">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <small class="c-devices__category-error js-devices-category-error"></small>
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="dev-marca">Marca</label>
                        <input id="dev-marca" class="c-list-cpt-sats__search" type="text" name="marca" placeholder="Ej. Apple" value="<?php echo esc_attr( $editando_brand ); ?>">
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="dev-modelo">Modelo</label>
                        <input id="dev-modelo" class="c-list-cpt-sats__search" type="text" name="modelo" placeholder="Ej. A2849" value="<?php echo esc_attr( $editando_model ); ?>">
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="dev-referencia">Referencia / Model Number</label>
                        <input id="dev-referencia" class="c-list-cpt-sats__search" type="text" name="referencia" placeholder="Ej. A2849" value="<?php echo esc_attr( $editando_ref ); ?>">
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="dev-fabricante">Fabricante</label>
                        <input id="dev-fabricante" class="c-list-cpt-sats__search" type="text" name="fabricante" placeholder="Ej. Apple" value="<?php echo esc_attr( $editando_maker ); ?>">
                    </div>
                </div>

                <div class="c-devices__section-title">Información técnica</div>
                <p class="c-devices__section-hint js-devices-specs-hint<?php echo $initial_specs_group ? ' is-hidden' : ''; ?>">Elige antes una categoría para ver sus campos.</p>

                <?php foreach ( $field_groups as $group_slug => $fields ) : ?>
                <div class="c-devices__specs-group js-device-specs-group<?php echo $initial_specs_group === $group_slug ? '' : ' is-hidden'; ?>" data-category="<?php echo esc_attr( $group_slug ); ?>">
                    <div class="c-devices__form-grid">
                        <?php foreach ( $fields as $field ) :
                            $f_name  = $field['name'];
                            $f_type  = $field['type'] ?? 'text';
                            $f_value = $editando_specs[ $f_name ] ?? '';
                        ?>
                            <?php if ( $f_type === 'checkbox' ) : ?>
                            <div class="c-list-cpt-sats__field c-devices__field--checkbox">
                                <label class="c-devices__checkbox-label">
                                    <input type="checkbox" name="spec[<?php echo esc_attr( $f_name ); ?>]" value="1" <?php checked( $f_value, '1' ); ?>>
                                    <span><?php echo esc_html( $field['label'] ); ?></span>
                                </label>
                            </div>
                            <?php else : ?>
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label"><?php echo esc_html( $field['label'] ); ?></label>
                                <input class="c-list-cpt-sats__search" type="text" name="spec[<?php echo esc_attr( $f_name ); ?>]" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>" value="<?php echo esc_attr( $f_value ); ?>">
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="c-devices__specs-group js-device-specs-group<?php echo $initial_specs_group === '__generic__' ? '' : ' is-hidden'; ?>" data-category="__generic__">
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label">Notas técnicas</label>
                        <textarea class="c-list-cpt-sats__search" name="spec[notes]" rows="4"><?php echo esc_textarea( $editando_specs['notes'] ?? '' ); ?></textarea>
                    </div>
                </div>

                <div class="c-devices__form-actions">
                    <button type="button" class="c-devices__cancel-btn js-devices-close-modal">Cancelar</button>
                    <button type="submit" class="o-button o-button--style-1 js-devices-submit"><?php echo $editando ? 'Guardar cambios' : 'Crear dispositivo'; ?></button>
                </div>
            </form>
        </div>
    </div>

</div>
