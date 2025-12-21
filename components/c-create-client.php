<div class="c-create-client">
    <div class="c-create-client__inner">
        <div class="c-create-client__container o-container">
            <div class="c-create-client__col o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">
                <div class="c-create-client__box">
                    <div class="c-create-client__wrapper-text">
                        <div class="c-create-client__wrapper-title">
                            <div class="c-create-client__title o-font-display-2">Crear Cliente</div>                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="c-create-client__col o-col-6@md o-col-push-3@md o-col-8@sm o-col-4@xs">
                <div class="c-create-client__wrapper-form">
                    <form class="c-create-client__form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                        <div class="c-create-client__wrapper-input">
                            <input class="c-create-client__input" type="hidden" name="action" value="crear_contacto_cpt">
                        </div>
                        <div class="c-create-client__wrapper-input">
                            <label>DNI</label>
                            <input class="c-create-client__input" type="text" name="dni">
                        </div>
                        <div class="c-create-client__wrapper-input">                    
                            <label>Nombre y apellido*</label>
                            <input class="c-create-client__input" type="text" name="nombre" required>
                        </div>
                        <div class="c-create-client__wrapper-input">
                            <label>Teléfono*</label>
                            <input class="c-create-client__input" type="text" name="telefono" required>
                        </div>
                        <button type="submit">Crear Cliente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>