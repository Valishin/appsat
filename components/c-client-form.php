<div class="c-client-form <?= isset($post_id) ? 'modificar' : 'crear'; ?>">
    <div class="c-client-form__inner-form">
        <div class="c-client-form__container-form o-container">
            <div class="c-client-form__col-form o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">
                <div class="c-client-form__box-form">
                    <div class="c-client-form__wrapper-form-text">
                        <div class="c-client-form__wrapper-form-title">
                            <div class="c-client-form__title-form o-font-display-2"><?php echo $title; ?></div>                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="c-client-form__col-form o-col-6@md o-col-push-3@md o-col-8@sm o-col-4@xs">
                <div class="c-client-form__wrapper-form-form">
                    <form class="c-client-form__form-form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                        <div class="c-client-form__wrapper-form-input">
                            <input class="c-client-form__input" type="hidden" name="action" value="save_contact">
                            <?php if(isset($post_id)){ ?>
                                <input type="hidden" name="id" value="<?php echo $post_id; ?>">
                            <?php } ?>
                        </div>
                        <div class="c-client-form__wrapper-form-input">                    
                            <label>Nombre y apellido*</label>
                            <input class="c-client-form__input c-client-form__input-name js-check-user" data-id="name" type="text" name="nombre" value="<?= $name ?? '' ?>" required>
                        </div>
                        <div class="c-client-form__wrapper-form-input">
                            <label>DNI</label>
                            <input class="c-client-form__input c-client-form__input-dni js-check-user" data-id="dni" type="text" name="dni" value="<?= $dni ?? '' ?>">
                        </div>                       
                        <div class="c-client-form__wrapper-form-input">                            
                            <label>Teléfono*</label>
                            <div class="c-client-form__wrapper-box-1">
                                <input class="c-client-form__input c-client-form__input-phone-extension js-check-user" data-id="phone-ext" type="number" name="telefono-ext" value="<?= $extension ?? '34' ?>" required>
                                <input class="c-client-form__input c-client-form__input-phone js-check-user" data-id="phone" type="number" name="telefono" value="<?= $phone ?? '' ?>" required>
                            </div>
                        </div>
                        <div class="c-client-form__wrapper-message">
                            <span class="c-client-form__message">El cliente ya existe</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="c-client-form__eye js-user-details for-open" viewBox="0 0 24 24"><path fill="currentColor" d="M9 12a2.996 2.996 0 0 1 4.07-2.803a.5.5 0 0 0 .358-.934A4 4 0 0 0 7.998 12c0 2.21 1.79 4 4 4a4 4 0 0 0 3.737-5.43a.5.5 0 1 0-.934.358a2.996 2.996 0 0 1-2.803 4.07c-1.66 0-3-1.34-3-3z"/><path fill="currentColor" fill-rule="evenodd" d="M12 5C5.89 5 2.25 9.73 1.2 11.25a1.34 1.34 0 0 0 0 1.5C2.21 14.27 5.85 19 12 19c6.11 0 9.75-4.73 10.8-6.25a1.34 1.34 0 0 0 0-1.5C21.79 9.73 18.15 5 12 5m-9.94 6.81C3.043 10.34 6.42 6 12 6s8.95 4.33 9.94 5.81a.34.34 0 0 1 0 .39c-.983 1.47-4.36 5.81-9.94 5.81s-8.95-4.33-9.94-5.81a.34.34 0 0 1 0-.39" clip-rule="evenodd"/></svg>
                        </div>
                        <button class="c-client-form__save is-disabled" disabled="disabled" type="submit"><?= isset($post_id) ? 'Modificar' : 'Crear'; ?> Cliente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>