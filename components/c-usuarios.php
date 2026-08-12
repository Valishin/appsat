
<div class="c-usuarios">
    <div class="c-usuarios__inner">
        <div class="c-usuarios__container o-container">
            <div class="c-usuarios__col o-col-12@md o-col-8@sm o-col-4@xs">

                <div class="c-usuarios__toolbar">
                    <div class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                        <?php echo sprintf( _n( 'Total %d usuario', 'Total %d usuarios', count( $tecnicos ), 'appsat' ), count( $tecnicos ) ); ?>
                    </div>
                    <button type="button" class="c-usuarios__new-btn js-usuarios-open-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        <span>Nuevo técnico</span>
                    </button>
                </div>

                <?php if ( $nuevo_usuario ) : ?>
                    <div class="c-usuarios__notice">
                        <strong>Técnico creado correctamente.</strong>
                        <p>Usuario: <code><?php echo esc_html( $nuevo_usuario['login'] ); ?></code> — Contraseña: <code><?php echo esc_html( $nuevo_usuario['pass'] ); ?></code></p>
                        <p>Apúntala ahora, no volverá a mostrarse.</p>
                    </div>
                <?php elseif ( $usuario_actualizado ) : ?>
                    <div class="c-usuarios__notice">
                        <strong>Usuario actualizado correctamente.</strong>
                    </div>
                <?php elseif ( $usuario_deshabilitado ) : ?>
                    <div class="c-usuarios__notice">
                        <strong>Técnico deshabilitado.</strong> Ya no puede iniciar sesión ni se le podrán asignar nuevos SATs.
                    </div>
                <?php elseif ( $usuario_habilitado ) : ?>
                    <div class="c-usuarios__notice">
                        <strong>Técnico habilitado de nuevo.</strong>
                    </div>
                <?php elseif ( $usuario_eliminado ) : ?>
                    <div class="c-usuarios__notice">
                        <strong>Usuario eliminado.</strong> Los SATs y facturas que ya gestionó se conservan.
                    </div>
                <?php endif; ?>

                <?php
                $errores_usuario = array(
                    'missing'      => 'Rellena al menos el nombre y el usuario.',
                    'user_exists'  => 'Ese nombre de usuario ya existe.',
                    'bad_email'    => 'El email no es válido.',
                    'email_exists' => 'Ese email ya está en uso por otro usuario.',
                    'self_action'  => 'No puedes hacer esto sobre tu propia cuenta.',
                    'last_admin'   => 'No se puede eliminar: es el único administrador que queda.',
                    'unknown'      => 'No se ha podido completar la acción.',
                );
                if ( isset( $_GET['error'] ) && isset( $errores_usuario[ $_GET['error'] ] ) ) : ?>
                    <div class="c-usuarios__error"><?php echo esc_html( $errores_usuario[ $_GET['error'] ] ); ?></div>
                <?php endif; ?>

                <div class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $tecnicos as $tecnico ) :
                                $es_admin_row  = in_array( 'administrator', $tecnico->roles, true );
                                $es_uno_mismo  = $tecnico->ID === get_current_user_id();
                                $deshabilitado = av_user_is_disabled( $tecnico->ID );
                            ?>
                                <tr class="c-list-cpt-sats__row c-usuarios__row<?php echo $deshabilitado ? ' c-usuarios__row--disabled' : ''; ?>">
                                    <td><?php echo esc_html( $tecnico->display_name ); ?></td>
                                    <td><?php echo esc_html( $tecnico->user_login ); ?></td>
                                    <td><?php echo esc_html( $tecnico->user_email ); ?></td>
                                    <td><?php echo $es_admin_row ? 'Administrador' : 'Técnico'; ?></td>
                                    <td>
                                        <?php if ( $deshabilitado ) : ?>
                                        <span class="c-usuarios__status c-usuarios__status--disabled">Deshabilitado</span>
                                        <?php else : ?>
                                        <span class="c-usuarios__status c-usuarios__status--active">Activo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="c-usuarios__actions">
                                            <button type="button"
                                                class="c-usuarios__action-btn js-usuarios-edit"
                                                title="Editar"
                                                data-id="<?php echo esc_attr( $tecnico->ID ); ?>"
                                                data-nombre="<?php echo esc_attr( $tecnico->display_name ); ?>"
                                                data-usuario="<?php echo esc_attr( $tecnico->user_login ); ?>"
                                                data-email="<?php echo esc_attr( $tecnico->user_email ); ?>"
                                                data-rol="<?php echo esc_attr( $es_admin_row ? 'administrator' : 'editor' ); ?>"
                                                data-uno-mismo="<?php echo $es_uno_mismo ? '1' : '0'; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>

                                            <?php if ( ! $es_uno_mismo ) : ?>
                                            <a class="c-usuarios__action-btn"
                                               href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'av_toggle_usuario_estado', 'id' => $tecnico->ID ], admin_url( 'admin-post.php' ) ), 'av_toggle_usuario_' . $tecnico->ID ) ); ?>"
                                               title="<?php echo $deshabilitado ? 'Habilitar' : 'Deshabilitar'; ?>"
                                               onclick="return confirm('<?php echo $deshabilitado ? '¿Habilitar de nuevo a este técnico? Podrá volver a iniciar sesión.' : '¿Deshabilitar a este técnico? No podrá iniciar sesión ni se le podrán asignar nuevos SATs.'; ?>');">
                                                <?php if ( $deshabilitado ) : ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                                                <?php else : ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                <?php endif; ?>
                                            </a>

                                            <a class="c-usuarios__action-btn c-usuarios__action-btn--danger"
                                               href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'av_eliminar_usuario', 'id' => $tecnico->ID ], admin_url( 'admin-post.php' ) ), 'av_eliminar_usuario_' . $tecnico->ID ) ); ?>"
                                               title="Eliminar"
                                               onclick="return confirm('¿Eliminar a «<?php echo esc_js( $tecnico->display_name ); ?>»? Esta acción no se puede deshacer. Los SATs y facturas que ya gestionó se conservan.');">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                            </a>
                                            <?php else : ?>
                                            <span class="c-usuarios__action-btn c-usuarios__action-btn--disabled" title="No puedes hacer esto sobre tu propia cuenta">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                            </span>
                                            <?php endif; ?>
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

    <!-- ── Modal crear / editar técnico ──────────────────────────────────── -->
    <div class="c-usuarios__modal js-usuarios-modal<?php echo $abrir_modal ? ' is-active' : ''; ?>">
        <div class="c-usuarios__modal-overlay js-usuarios-close-modal"></div>
        <div class="c-usuarios__modal-panel">
            <div class="c-usuarios__modal-head">
                <span class="c-usuarios__modal-title js-usuarios-modal-title"><?php echo $editando ? 'Editar técnico' : 'Nuevo técnico'; ?></span>
                <button type="button" class="c-usuarios__modal-close js-usuarios-close-modal" aria-label="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form class="c-usuarios__form js-usuarios-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'av_crear_usuario_nonce', 'nonce' ); ?>
                <input type="hidden" name="action" value="guardar_usuario">
                <input type="hidden" name="id" class="js-usuarios-field-id" value="<?php echo $editando ? esc_attr( $editando->ID ) : ''; ?>">

                <div class="c-usuarios__form-grid">
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="u-nombre">Nombre completo</label>
                        <input id="u-nombre" class="c-list-cpt-sats__search js-usuarios-field-nombre" type="text" name="nombre" value="<?php echo $editando ? esc_attr( $editando->display_name ) : ''; ?>" required>
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="u-usuario">Usuario</label>
                        <input id="u-usuario" class="c-list-cpt-sats__search js-usuarios-field-usuario" type="text" name="usuario" value="<?php echo $editando ? esc_attr( $editando->user_login ) : ''; ?>" <?php echo $editando ? 'disabled title="El usuario no se puede cambiar"' : 'required'; ?>>
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="u-email">Email</label>
                        <input id="u-email" class="c-list-cpt-sats__search js-usuarios-field-email" type="email" name="email" value="<?php echo $editando ? esc_attr( $editando->user_email ) : ''; ?>">
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="u-password">Contraseña</label>
                        <input id="u-password" class="c-list-cpt-sats__search js-usuarios-field-password" type="text" name="password" placeholder="<?php echo $editando ? 'Déjalo en blanco para no cambiarla' : 'Déjalo en blanco para generarla'; ?>" autocomplete="off">
                    </div>
                    <div class="c-list-cpt-sats__field">
                        <label class="c-list-cpt-sats__field-label" for="u-rol">Rol</label>
                        <?php
                        $rol_actual   = $editando && in_array( 'administrator', $editando->roles, true ) ? 'administrator' : 'editor';
                        $es_uno_mismo = $editando && $editando->ID === get_current_user_id();
                        ?>
                        <select id="u-rol" class="c-list-cpt-sats__search js-usuarios-field-rol" name="rol" <?php echo $es_uno_mismo ? 'disabled title="No puedes cambiar tu propio rol"' : ''; ?>>
                            <option value="editor" <?php selected( $rol_actual, 'editor' ); ?>>Técnico</option>
                            <option value="administrator" <?php selected( $rol_actual, 'administrator' ); ?>>Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="c-usuarios__form-actions">
                    <button type="button" class="c-usuarios__cancel-btn js-usuarios-close-modal">Cancelar</button>
                    <button type="submit" class="o-button o-button--style-1 js-usuarios-submit"><?php echo $editando ? 'Guardar cambios' : 'Crear técnico'; ?></button>
                </div>
            </form>
        </div>
    </div>

</div>
