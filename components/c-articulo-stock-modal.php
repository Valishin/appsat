<?php
// Modal "Ajustar stock": reutiliza el mismo patrón visual que el banner de
// finalizar SAT del listado (c-list-cpt-sats__finalize-banner). Los datos del
// artículo (id, nombre, stock actual) los rellena el JS al pulsar el botón de
// la fila (data-id/data-nombre/data-stock en c-articulos-list.php).
?>
<div class="c-list-cpt-sats__finalize-banner js-articulo-stock-modal">
    <div class="c-list-cpt-sats__finalize-banner-inner">
        <p class="c-list-cpt-sats__finalize-banner-title">Ajustar stock</p>
        <p class="c-list-cpt-sats__finalize-banner-text js-articulo-stock-nombre"></p>

        <form class="js-articulo-stock-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="av_ajustar_stock_articulo">
            <input type="hidden" name="id" class="js-articulo-stock-id" value="">
            <?php wp_nonce_field( 'av_articulo_stock_nonce', 'nonce' ); ?>

            <div class="c-list-cpt-sats__finalize-banner-field">
                <label>Stock actual</label>
                <input type="text" class="js-articulo-stock-actual" value="" disabled>
            </div>
            <div class="c-list-cpt-sats__finalize-banner-field">
                <label for="articulo-stock-nueva-cantidad">Nueva cantidad</label>
                <input type="number" step="any" id="articulo-stock-nueva-cantidad" name="nueva-cantidad" class="js-articulo-stock-nueva-cantidad" required>
            </div>
            <div class="c-list-cpt-sats__finalize-banner-field">
                <label for="articulo-stock-motivo">Motivo</label>
                <input type="text" id="articulo-stock-motivo" name="motivo" placeholder="Ej. Inventario físico" required>
            </div>

            <p class="c-list-cpt-sats__finalize-banner-error js-articulo-stock-error"></p>

            <div class="c-list-cpt-sats__finalize-banner-ctas">
                <button type="button" class="o-button o-button--style-2 js-articulo-stock-cancel">Cancelar</button>
                <button type="submit" class="o-button o-button--style-1">Guardar ajuste</button>
            </div>
        </form>
    </div>
</div>
