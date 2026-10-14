<?php
// Pantalla "Condiciones del recibo" (solo administrador): texto de la letra
// pequeña que sale impresa en cada hoja de compra. Ver inc/compras/compras.php.
?>
<div class="c-list-cpt-sats c-pedidos">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">

                <div class="c-articulo-form__head">
                    <h1 class="c-articulo-form__title">Condiciones del recibo de compra</h1>
                </div>

                <?php if ( isset( $_GET['guardado'] ) ) : ?>
                <div class="c-devices__notice"><strong>Condiciones guardadas.</strong></div>
                <?php endif; ?>

                <p class="o-font-display-caption" style="margin-bottom:12px">
                    Este texto sale impreso (letra pequeña) en todas las hojas de compra. Te recomiendo que lo revise tu gestor o abogado antes de usarlo. Si lo dejas vacío se usa el texto de ejemplo.
                </p>

                <form class="c-pedido-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'av_compras_terms_nonce', 'nonce' ); ?>
                    <input type="hidden" name="action" value="av_guardar_compras_terms">
                    <div class="c-list-cpt-sats__field c-devices__field--full">
                        <textarea class="c-list-cpt-sats__search" name="terms" rows="18" style="width:100%"><?php echo esc_textarea( av_compras_get_terms() ); ?></textarea>
                    </div>
                    <div class="c-sat-form__wrapper-ctas">
                        <div class="c-sat-form__ctas-inner">
                            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
                                <a class="c-sat-form__cta c-sat-form__cta--secondary" href="<?php echo esc_url( av_compras_page_url() ); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                                    <span>Volver a compras</span>
                                </a>
                            </div>
                            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">
                                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    <span>Guardar condiciones</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
