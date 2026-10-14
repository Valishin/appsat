<?php
// Formulario de alta/edición de cliente. Se usa en 3 sitios: la página
// completa de alta/edición (c-client-choice.php), la modal de alta rápida
// (c-client-modal.php) y el paso "crear cliente nuevo" al elegir cliente
// para un SAT (también vía c-client-modal.php).
$type_client                 = $type_client ?? 'particular';
$in_modal                    = $in_modal ?? false;
$cif                         = $cif ?? '';
$razon_social                = $razon_social ?? '';
$direccion_fiscal            = $direccion_fiscal ?? '';
$direccion_fiscal_cp         = $direccion_fiscal_cp ?? '';
$direccion_fiscal_municipio  = $direccion_fiscal_municipio ?? '';
$direccion_fiscal_provincia  = $direccion_fiscal_provincia ?? '';
$direccion_fiscal_pais       = $direccion_fiscal_pais ?: 'España';
$notes                       = $notes ?? '';

// Segundo teléfono (opcional, hasta 2 por cliente) — ver
// components/c-client-form.php más abajo y av_client_phone2_toggle() en
// src/js/app.js. Al editar un cliente que ya tiene uno guardado, el bloque
// empieza visible en vez de detrás del botón "+ Añadir teléfono".
$phone2         = $phone2 ?? '';
$phone2_type    = $phone2_type ?: 'movil';
$phone2_extension = $phone2_extension ?: '34';
$phone2_open    = $phone2 !== '';

// Para "Profesional" el nombre del cliente pasa a ser la razón social (no
// tiene sentido pedir las dos cosas): mismo input/name="nombre" en los dos
// casos -> mismo cpt-client__name de siempre (usado en listados, buscadores,
// facturas...), solo cambia la etiqueta. El DNI se oculta (el CIF ya
// identifica fiscalmente a la empresa) y el orden de los campos cambia vía
// CSS (order en el grid, ver c-client-form.scss) para no duplicar campos
// compartidos (Teléfono/Email) entre los dos modos.
$is_profesional = $type_client === 'profesional';
$nombre_label   = $is_profesional ? 'Razón social' : 'Nombre y apellido / Empresa';

$paises_list = [
    'España','Francia','Alemania','Italia','Portugal',
    'Países Bajos','Bélgica','Austria','Grecia','Irlanda',
    'Finlandia','Suecia','Noruega','Dinamarca','Suiza',
    'Polonia','República Checa','Hungría','Rumanía',
    'Reino Unido','Estados Unidos','México','Argentina',
    'Colombia','Chile','Brasil','Perú',
];
?>
<div class="c-client-form <?= isset($post_id) ? 'modificar' : 'crear'; ?><?php echo $in_modal ? '' : ' c-client-form--page'; ?>">
    <form class="c-client-form__form-form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
        <input type="hidden" name="action" value="save_contact">
        <?php if ( isset( $post_id ) ) : ?>
            <input type="hidden" name="id" value="<?php echo esc_attr( $post_id ); ?>">
        <?php endif; ?>
        <?php if ( ! empty( $redirect_to ) ) : ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">
        <?php endif; ?>

        <div class="c-client-form__type-toggle">
            <button type="button" class="c-client-form__type-btn js-client-type-btn<?php echo $type_client !== 'profesional' ? ' is-active' : ''; ?>" data-value="particular">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg>
                <span>Particular</span>
            </button>
            <button type="button" class="c-client-form__type-btn js-client-type-btn<?php echo $type_client === 'profesional' ? ' is-active' : ''; ?>" data-value="profesional">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                <span>Profesional</span>
            </button>
        </div>
        <input type="hidden" name="type-client" class="js-client-form__type-client" value="<?php echo esc_attr( $type_client ); ?>">

        <!-- Un único grid con los dos modos (Particular/Profesional): los
             campos que no aplican a cada modo se ocultan y el orden se
             reordena vía CSS (order), en vez de duplicar Teléfono/Email en
             dos bloques distintos. Nada se limpia al cambiar de modo, para
             no perder lo escrito si el usuario cambia de pestaña por error
             (ver av_client_type_toggle en src/js/app.js). -->
        <div class="c-client-form__grid js-client-form__grid<?php echo $is_profesional ? ' is-profesional' : ''; ?>">

            <div class="c-list-cpt-sats__field c-client-form__field--nombre">
                <label class="c-list-cpt-sats__field-label js-client-form__nombre-label" for="cf-nombre"><?php echo esc_html( $nombre_label ); ?></label>
                <input id="cf-nombre" class="c-list-cpt-sats__search c-client-form__input-name js-check-user js-client-form__nombre-input" data-id="name" type="text" name="nombre" placeholder="<?php echo esc_attr( $nombre_label ); ?>" value="<?php echo esc_attr( $name ?? '' ); ?>" required>
            </div>

            <div class="c-list-cpt-sats__field c-client-form__field--email">
                <label class="c-list-cpt-sats__field-label" for="cf-email">Email</label>
                <input id="cf-email" class="c-list-cpt-sats__search js-check-user" data-id="email" type="text" name="email" value="<?php echo esc_attr( $email ?? '' ); ?>">
            </div>

            <!-- Solo Particular: con el CIF (más abajo) ya es suficiente para un Profesional. -->
            <div class="c-list-cpt-sats__field c-client-form__field--dni">
                <label class="c-list-cpt-sats__field-label" for="cf-dni">DNI / NIE / NIF</label>
                <input id="cf-dni" class="c-list-cpt-sats__search js-check-user" data-id="dni" type="text" name="dni" value="<?php echo esc_attr( $dni ?? '' ); ?>">
            </div>

            <div class="c-list-cpt-sats__field c-client-form__field--telefono">
                <label class="c-list-cpt-sats__field-label" for="cf-phone">Teléfono</label>
                <div class="c-client-form__phone-group">
                    <input class="c-list-cpt-sats__search c-client-form__input-phone-extension" type="number" name="telefono-ext" value="<?php echo esc_attr( $extension ?? '34' ); ?>" required>
                    <input id="cf-phone" class="c-list-cpt-sats__search c-client-form__input-phone js-check-user" data-id="phone" type="number" name="telefono" value="<?php echo esc_attr( $phone ?? '' ); ?>" required>
                    <!-- Segundo teléfono, opcional (hasta 2 por cliente):
                         botón pequeño junto al teléfono principal en vez de
                         un botón de ancho completo — abre el bloque de abajo
                         (ver av_client_phone2_toggle() en src/js/app.js). -->
                    <button type="button" class="c-client-form__phone2-add js-client-form__phone2-toggle<?php echo $phone2_open ? ' is-hidden' : ''; ?>" title="Añadir otro teléfono" aria-label="Añadir otro teléfono">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Bloque del segundo teléfono en sí: puede ser Fijo o Móvil —
                 el campo de extensión solo tiene sentido (y solo se enseña)
                 para Móvil. -->
            <div class="c-list-cpt-sats__field c-devices__field--full c-client-form__field--telefono2">
                <div class="c-client-form__phone2 js-client-form__phone2<?php echo $phone2_open ? '' : ' is-hidden'; ?>">
                    <div class="c-client-form__phone2-head">
                        <label class="c-list-cpt-sats__field-label">Segundo teléfono</label>
                        <button type="button" class="c-client-form__phone2-remove js-client-form__phone2-remove" aria-label="Quitar segundo teléfono">×</button>
                    </div>
                    <div class="c-client-form__phone-group">
                        <select class="c-list-cpt-sats__search c-client-form__phone2-type js-client-form__phone2-type" name="telefono-2-tipo">
                            <option value="movil" <?php selected( $phone2_type, 'movil' ); ?>>Móvil</option>
                            <option value="fijo" <?php selected( $phone2_type, 'fijo' ); ?>>Fijo</option>
                        </select>
                        <input class="c-list-cpt-sats__search js-client-form__phone2-extension<?php echo $phone2_type === 'fijo' ? ' is-hidden' : ''; ?>" type="number" name="telefono-2-ext" value="<?php echo esc_attr( $phone2_extension ); ?>">
                        <input class="c-list-cpt-sats__search" type="number" name="telefono-2" placeholder="Número" value="<?php echo esc_attr( $phone2 ); ?>">
                    </div>
                </div>
            </div>

            <!-- Solo Profesional, a partir de aquí. -->
            <div class="c-list-cpt-sats__field c-client-form__field--cif">
                <label class="c-list-cpt-sats__field-label" for="cf-cif">CIF</label>
                <input id="cf-cif" class="c-list-cpt-sats__search" type="text" name="cif" value="<?php echo esc_attr( $cif ); ?>">
            </div>

            <div class="c-list-cpt-sats__field c-client-form__field--direccion c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="cf-direccion-fiscal">Dirección fiscal</label>
                <input id="cf-direccion-fiscal" class="c-list-cpt-sats__search" type="text" name="direccion-fiscal" value="<?php echo esc_attr( $direccion_fiscal ); ?>">
            </div>

            <div class="c-list-cpt-sats__field c-client-form__field--cp">
                <label class="c-list-cpt-sats__field-label" for="cf-direccion-fiscal-cp">Código postal</label>
                <input id="cf-direccion-fiscal-cp" class="c-list-cpt-sats__search" type="text" name="direccion-fiscal-cp" value="<?php echo esc_attr( $direccion_fiscal_cp ); ?>">
            </div>

            <div class="c-list-cpt-sats__field c-client-form__field--municipio">
                <label class="c-list-cpt-sats__field-label" for="cf-direccion-fiscal-municipio">Municipio</label>
                <input id="cf-direccion-fiscal-municipio" class="c-list-cpt-sats__search" type="text" name="direccion-fiscal-municipio" value="<?php echo esc_attr( $direccion_fiscal_municipio ); ?>">
            </div>

            <div class="c-list-cpt-sats__field c-client-form__field--provincia">
                <label class="c-list-cpt-sats__field-label" for="cf-direccion-fiscal-provincia">Provincia</label>
                <input id="cf-direccion-fiscal-provincia" class="c-list-cpt-sats__search" type="text" name="direccion-fiscal-provincia" value="<?php echo esc_attr( $direccion_fiscal_provincia ); ?>">
            </div>

            <div class="c-list-cpt-sats__field c-client-form__field--pais">
                <label class="c-list-cpt-sats__field-label" for="cf-direccion-fiscal-pais">País</label>
                <select id="cf-direccion-fiscal-pais" class="c-list-cpt-sats__search" name="direccion-fiscal-pais">
                    <?php foreach ( $paises_list as $p ) : ?>
                    <option value="<?php echo esc_attr( $p ); ?>" <?php selected( $direccion_fiscal_pais, $p ); ?>><?php echo esc_html( $p ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div class="c-list-cpt-sats__field">
            <label class="c-list-cpt-sats__field-label" for="cf-notes">Notas internas</label>
            <textarea id="cf-notes" class="c-list-cpt-sats__search" name="notes" rows="2" placeholder="Notas visibles solo para el equipo, no se imprimen"><?php echo esc_textarea( $notes ); ?></textarea>
        </div>

        <div class="c-client-form__wrapper-message">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="c-client-form__message">Este cliente ya existe</span>
            <svg class="c-client-form__eye js-user-details for-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9 12a2.996 2.996 0 0 1 4.07-2.803a.5.5 0 0 0 .358-.934A4 4 0 0 0 7.998 12c0 2.21 1.79 4 4 4a4 4 0 0 0 3.737-5.43a.5.5 0 1 0-.934.358a2.996 2.996 0 0 1-2.803 4.07c-1.66 0-3-1.34-3-3z"/><path fill="currentColor" fill-rule="evenodd" d="M12 5C5.89 5 2.25 9.73 1.2 11.25a1.34 1.34 0 0 0 0 1.5C2.21 14.27 5.85 19 12 19c6.11 0 9.75-4.73 10.8-6.25a1.34 1.34 0 0 0 0-1.5C21.79 9.73 18.15 5 12 5m-9.94 6.81C3.043 10.34 6.42 6 12 6s8.95 4.33 9.94 5.81a.34.34 0 0 1 0 .39c-.983 1.47-4.36 5.81-9.94 5.81s-8.95-4.33-9.94-5.81a.34.34 0 0 1 0-.39" clip-rule="evenodd"/></svg>
        </div>

        <?php if ( $in_modal ) : ?>
        <!-- Dentro de la modal: fila de acciones normal, al final del propio
             formulario (igual que antes) — la barra fija de abajo es solo
             para la página completa. -->
        <div class="c-client-form__form-actions">
            <button type="button" class="c-client-form__cancel-btn js-client-modal-close">Cancelar</button>
            <button class="c-client-form__save o-button o-button--style-1 is-disabled" disabled="disabled" type="submit"><?= isset($post_id) ? 'Guardar cambios' : 'Crear cliente'; ?></button>
        </div>
        <?php else : ?>
        <!-- Página completa: mismas acciones fijas abajo que los demás
             formularios de creación (SAT/Pedido/Presupuesto), reutilizando
             tal cual sus clases .c-sat-form__wrapper-ctas/__cta. -->
        <div class="c-sat-form__wrapper-ctas">
            <div class="c-sat-form__ctas-inner">
                <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary c-sat-form__ctas-group--solo">
                    <?php if ( isset( $post_id ) ) : ?>
                    <a class="c-sat-form__cta c-sat-form__cta--secondary" href="<?php
                        $page = get_page_by_path('crear-sat');
                        echo esc_url( get_permalink($page->ID) . '?id=' . intval($post_id) );
                    ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Crear SAT</span>
                    </a>
                    <a href="#" class="c-sat-form__cta c-sat-form__cta--whatsapp js-whatsapp-open-btn" data-client-id="<?php echo esc_attr( $post_id ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                    <?php endif; ?>
                    <button class="c-client-form__save c-sat-form__cta c-sat-form__cta--primary is-disabled" disabled="disabled" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span><?= isset($post_id) ? 'Guardar cambios' : 'Crear cliente'; ?></span>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ( isset( $post_id ) && ! $in_modal ) : ?>
        <div class="c-client-form__whatsapp-empty js-whatsapp-empty-msg is-hidden">
            Todavía no hay conversación de WhatsApp con este cliente.
        </div>
        <?php endif; ?>
    </form>
</div>
