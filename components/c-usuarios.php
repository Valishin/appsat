
<div class="c-usuarios">
    <div class="c-usuarios__inner">
        <div class="c-usuarios__container o-container">
            <div class="c-usuarios__col o-col-12@md o-col-8@sm o-col-4@xs">

                <div class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                    <?php echo sprintf( _n( 'Total %d usuario', 'Total %d usuarios', count( $tecnicos ), 'appsat' ), count( $tecnicos ) ); ?>
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
                <?php endif; ?>

                <?php
                $errores_usuario = array(
                    'missing'      => 'Rellena al menos el nombre y el usuario.',
                    'user_exists'  => 'Ese nombre de usuario ya existe.',
                    'bad_email'    => 'El email no es válido.',
                    'email_exists' => 'Ese email ya está en uso por otro usuario.',
                    'unknown'      => 'No se ha podido guardar el usuario.',
                );
                if ( isset( $_GET['error'] ) && isset( $errores_usuario[ $_GET['error'] ] ) ) : ?>
                    <div class="c-usuarios__error"><?php echo esc_html( $errores_usuario[ $_GET['error'] ] ); ?></div>
                <?php endif; ?>

                <div class="c-usuarios__wrapper-form">
                    <div class="c-usuarios__form-title o-font-display-headline">
                        <?php echo $editando ? 'Editar técnico' : 'Nuevo técnico'; ?>
                    </div>
                    <form class="c-usuarios__form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( 'av_crear_usuario_nonce', 'nonce' ); ?>
                        <input type="hidden" name="action" value="guardar_usuario">
                        <?php if ( $editando ) : ?>
                            <input type="hidden" name="id" value="<?php echo esc_attr( $editando->ID ); ?>">
                        <?php endif; ?>
                        <div class="c-usuarios__form-grid">
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="u-nombre">Nombre completo</label>
                                <input id="u-nombre" class="c-list-cpt-sats__search" type="text" name="nombre" value="<?php echo $editando ? esc_attr( $editando->display_name ) : ''; ?>" required>
                            </div>
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="u-usuario">Usuario</label>
                                <input id="u-usuario" class="c-list-cpt-sats__search" type="text" name="usuario" value="<?php echo $editando ? esc_attr( $editando->user_login ) : ''; ?>" <?php echo $editando ? 'disabled title="El usuario no se puede cambiar"' : 'required'; ?>>
                            </div>
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="u-email">Email</label>
                                <input id="u-email" class="c-list-cpt-sats__search" type="email" name="email" value="<?php echo $editando ? esc_attr( $editando->user_email ) : ''; ?>">
                            </div>
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="u-password">Contraseña</label>
                                <input id="u-password" class="c-list-cpt-sats__search" type="text" name="password" placeholder="<?php echo $editando ? 'Déjalo en blanco para no cambiarla' : 'Déjalo en blanco para generarla'; ?>" autocomplete="off">
                            </div>
                            <div class="c-list-cpt-sats__field">
                                <label class="c-list-cpt-sats__field-label" for="u-rol">Rol</label>
                                <?php
                                $rol_actual = $editando && in_array( 'administrator', $editando->roles, true ) ? 'administrator' : 'editor';
                                $es_uno_mismo = $editando && $editando->ID === get_current_user_id();
                                ?>
                                <select id="u-rol" class="c-list-cpt-sats__search" name="rol" <?php echo $es_uno_mismo ? 'disabled title="No puedes cambiar tu propio rol"' : ''; ?>>
                                    <option value="editor" <?php selected( $rol_actual, 'editor' ); ?>>Técnico</option>
                                    <option value="administrator" <?php selected( $rol_actual, 'administrator' ); ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="c-usuarios__form-actions">
                            <?php if ( $editando ) : ?>
                                <a href="<?php echo esc_url( get_permalink() ); ?>" class="c-list-cpt-sats__clear-filters">Cancelar</a>
                            <?php endif; ?>
                            <button type="submit" class="o-button o-button--style-1"><?php echo $editando ? 'Guardar cambios' : 'Crear técnico'; ?></button>
                        </div>
                    </form>
                </div>

                <div class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $tecnicos as $tecnico ) : ?>
                                <tr class="c-list-cpt-sats__row<?php echo ( $editando && $editando->ID === $tecnico->ID ) ? ' is-editing' : ''; ?>">
                                    <td><?php echo esc_html( $tecnico->display_name ); ?></td>
                                    <td><?php echo esc_html( $tecnico->user_login ); ?></td>
                                    <td><?php echo esc_html( $tecnico->user_email ); ?></td>
                                    <td><?php echo in_array( 'administrator', $tecnico->roles, true ) ? 'Administrador' : 'Técnico'; ?></td>
                                    <td>
                                        <a class="c-usuarios__edit-link" href="<?php echo esc_url( add_query_arg( 'edit', $tecnico->ID, get_permalink() ) ); ?>">Editar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
