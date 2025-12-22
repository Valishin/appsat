<div class="c-client-form">
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
                            <input class="c-client-form__input" type="hidden" name="action" value="crear_contacto_cpt">
                        </div>
                        <div class="c-client-form__wrapper-form-input">                    
                            <label>Nombre y apellido*</label>
                            <input class="c-client-form__input" type="text" name="nombre" value="<?= $name ?? '' ?>" required>
                        </div>
                        <div class="c-client-form__wrapper-form-input">
                            <label>DNI</label>
                            <input class="c-client-form__input" type="text" name="dni" value="<?= $dni ?? '' ?>">
                        </div>
                        <div class="c-client-form__wrapper-form-input">
                            <label>Teléfono*</label>
                            <input class="c-client-form__input" type="text" name="telefono" value="<?= $phone ?? '' ?>" required>
                        </div>
                        <button type="submit"><?= isset($post_id) ? 'Modificar' : 'Crear'; ?> Cliente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>