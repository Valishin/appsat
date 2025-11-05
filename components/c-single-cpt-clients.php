<?php 
    $post_id = get_the_ID();
    $name = get_the_title();
    $phone = get_field('cpt-client__phone', $post_id);    
?>
<div class="c-single-cpt-clients">
    <div class="c-single-cpt-clients__inner">
        <div class="c-single-cpt-clients__container o-container">
            <div class="c-single-cpt-clients__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-single-cpt-clients__wrapper-title">
                    <div class="c-single-cpt-clients__title o-font-display-2">Modificar Cliente</div>                            
                </div>
            </div>
            <div class="c-create-client__col o-col-6@md o-col-push-3@md o-col-8@sm o-col-4@xs">
                <form class="c-create-client__form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                    <div class="c-create-client__wrapper-input">
                        <input type="hidden" name="action" value="crear_contacto_cpt">
                        <input type="hidden" name="id" value="<?php echo $post_id; ?>">
                    </div>
                    <div class="c-create-client__wrapper-input">                    
                        <label>Nombre y apellido</label>
                        <input class="c-create-client__input" type="text" name="nombre" required value="<?php echo $name; ?>">
                    </div>
                    <div class="c-create-client__wrapper-input">
                        <label>Teléfono</label>
                        <input class="c-create-client__input" type="text" name="telefono" required value="<?php echo $phone; ?>">
                    </div>
                    <button type="submit">Modificar</button>
                </form>
            </div>
        </div>
    </div>
</div>