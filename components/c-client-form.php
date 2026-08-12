<?php
// Formulario de alta/edición de cliente. Se usa en 3 sitios: la página
// completa de alta/edición (c-client-choice.php), la modal de alta rápida
// (c-client-modal.php) y el paso "crear cliente nuevo" al elegir cliente
// para un SAT (también vía c-client-modal.php).
$type_client = $type_client ?? 'particular';
$in_modal    = $in_modal ?? false;
?>
<div class="c-client-form <?= isset($post_id) ? 'modificar' : 'crear'; ?>">
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

        <div class="c-client-form__grid">
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="cf-nombre">Nombre y apellido / Empresa</label>
                <input id="cf-nombre" class="c-list-cpt-sats__search c-client-form__input-name js-check-user" data-id="name" type="text" name="nombre" value="<?php echo esc_attr( $name ?? '' ); ?>" required>
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="cf-email">Email</label>
                <input id="cf-email" class="c-list-cpt-sats__search js-check-user" data-id="email" type="text" name="email" value="<?php echo esc_attr( $email ?? '' ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="cf-dni">DNI / NIE / NIF</label>
                <input id="cf-dni" class="c-list-cpt-sats__search js-check-user" data-id="dni" type="text" name="dni" value="<?php echo esc_attr( $dni ?? '' ); ?>">
            </div>
            <div class="c-list-cpt-sats__field">
                <label class="c-list-cpt-sats__field-label" for="cf-phone">Teléfono</label>
                <div class="c-client-form__phone-group">
                    <input class="c-list-cpt-sats__search c-client-form__input-phone-extension" type="number" name="telefono-ext" value="<?php echo esc_attr( $extension ?? '34' ); ?>" required>
                    <input id="cf-phone" class="c-list-cpt-sats__search c-client-form__input-phone js-check-user" data-id="phone" type="number" name="telefono" value="<?php echo esc_attr( $phone ?? '' ); ?>" required>
                </div>
            </div>
        </div>

        <div class="c-client-form__wrapper-message">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="c-client-form__message">Este cliente ya existe</span>
            <svg class="c-client-form__eye js-user-details for-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M9 12a2.996 2.996 0 0 1 4.07-2.803a.5.5 0 0 0 .358-.934A4 4 0 0 0 7.998 12c0 2.21 1.79 4 4 4a4 4 0 0 0 3.737-5.43a.5.5 0 1 0-.934.358a2.996 2.996 0 0 1-2.803 4.07c-1.66 0-3-1.34-3-3z"/><path fill="currentColor" fill-rule="evenodd" d="M12 5C5.89 5 2.25 9.73 1.2 11.25a1.34 1.34 0 0 0 0 1.5C2.21 14.27 5.85 19 12 19c6.11 0 9.75-4.73 10.8-6.25a1.34 1.34 0 0 0 0-1.5C21.79 9.73 18.15 5 12 5m-9.94 6.81C3.043 10.34 6.42 6 12 6s8.95 4.33 9.94 5.81a.34.34 0 0 1 0 .39c-.983 1.47-4.36 5.81-9.94 5.81s-8.95-4.33-9.94-5.81a.34.34 0 0 1 0-.39" clip-rule="evenodd"/></svg>
        </div>

        <div class="c-client-form__form-actions">
            <?php if ( $in_modal ) : ?>
                <button type="button" class="c-client-form__cancel-btn js-client-modal-close">Cancelar</button>
            <?php endif; ?>
            <button class="c-client-form__save o-button o-button--style-1 is-disabled" disabled="disabled" type="submit"><?= isset($post_id) ? 'Guardar cambios' : 'Crear cliente'; ?></button>
            <?php if (isset($post_id)) : ?>
                <a class="c-client-form__crear-sat o-button o-button--style-1" href="<?php
                $page = get_page_by_path('crear-sat');
                echo esc_url( get_permalink($page->ID) . '?id=' . intval($post_id) );
                ?>">Crear SAT</a>
            <?php endif; ?>
        </div>
    </form>
</div>
