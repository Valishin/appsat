<?php
// Formulario "Nuevo presupuesto" / "Editar presupuesto". Espera $editando
// (WP_Post o null) en el scope. Misma arquitectura que components/c-pedido-form.php:
// página completa, acordeones plegables (.c-sat-form__accordion).

$pr_id = $editando ? $editando->ID : 0;

$get_meta = function( $key, $default = '' ) use ( $pr_id ) {
    if ( ! $pr_id ) return $default;
    $v = get_post_meta( $pr_id, $key, true );
    return $v !== '' ? $v : $default;
};

$presupuesto_numero = $get_meta( 'cpt-presupuesto__presupuesto-id' );
$client_id          = intval( $get_meta( 'cpt-presupuesto__client-id' ) );
$entry_date         = $get_meta( 'cpt-presupuesto__entry-date' );
$notes              = $get_meta( 'cpt-presupuesto__notes' );
$lines_json         = $pr_id ? get_post_meta( $pr_id, 'cpt-presupuesto__lines', true ) : '';

$client_name  = '';
$client_phone = '';
if ( $client_id ) {
    $client_name  = get_field( 'cpt-client__name', $client_id );
    $client_phone = trim( get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id ) );
}

$errores_presupuesto = [
    'cliente-invalido'          => 'El cliente elegido no es válido.',
    'lineas-requeridas'         => 'Añade al menos una línea al presupuesto.',
    'numero-generation-failed'  => 'No se ha podido generar un número de presupuesto único. Inténtalo de nuevo.',
    'unknown'                   => 'No se ha podido completar la acción.',
];

// El modal de "crear cliente" redirige aquí mismo al guardar: el JS de la
// página intercepta ese envío y lee el id del cliente nuevo de la URL final
// sin llegar a recargar (mismo mecanismo que Pedidos, ver
// av_presupuesto_client_picker() en src/js/app.js).
$client_modal_redirect_to = av_presupuestos_page_url();
?>

<?php if ( isset( $_GET['creado'] ) ) : ?>
<div class="c-devices__notice"><strong>Presupuesto <?php echo esc_html( $presupuesto_numero ); ?> creado correctamente.</strong></div>
<?php elseif ( isset( $_GET['actualizado'] ) ) : ?>
<div class="c-devices__notice"><strong>Presupuesto actualizado correctamente.</strong></div>
<?php endif; ?>

<?php if ( isset( $_GET['error'] ) ) :
    $err_slug = sanitize_text_field( $_GET['error'] );
    $err_msg  = $errores_presupuesto[ $err_slug ] ?? $errores_presupuesto['unknown'];
?>
<div class="c-devices__error"><?php echo esc_html( $err_msg ); ?></div>
<?php endif; ?>

<form class="c-pedido-form js-presupuesto-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'av_presupuesto_nonce', 'nonce' ); ?>
    <input type="hidden" name="action" value="av_guardar_presupuesto">
    <input type="hidden" name="id" value="<?php echo esc_attr( $pr_id ); ?>">
    <input type="hidden" name="client-id" class="js-presupuesto-client-id" value="<?php echo esc_attr( $client_id ); ?>">

    <div class="c-articulo-form__head">
        <h1 class="c-articulo-form__title">
            <?php echo $editando ? 'Editar presupuesto ' . esc_html( $presupuesto_numero ) : 'Nuevo presupuesto'; ?>
        </h1>
    </div>

    <?php if ( $pr_id ) : ?>
    <!-- Imprimir: fijo arriba a la derecha, solo icono (mismo patrón de
         posición que c-sat-form__fixed-badges-right, pero como botón en vez
         de insignia informativa). -->
    <a class="c-presupuesto-form__print-fixed"
       target="_blank"
       title="Imprimir / PDF"
       href="<?php echo esc_url( add_query_arg( [
           'presupuesto_pdf' => '1',
           'presupuesto_id'  => $pr_id,
           'nonce'           => wp_create_nonce( 'presupuesto_pdf_' . $pr_id ),
       ], home_url( '/' ) ) ); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    </a>
    <?php endif; ?>

    <!-- ── Cliente ────────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Cliente</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full c-pedido-client">

                <div class="c-pedido-client__selected js-presupuesto-client-selected<?php echo $client_id ? '' : ' is-hidden'; ?>">
                    <div class="c-sat-client-picker__result c-pedido-client__card">
                        <span class="c-sat-client-picker__result-name js-presupuesto-client-name"><?php echo esc_html( $client_name ); ?></span>
                        <span class="c-sat-client-picker__result-meta js-presupuesto-client-meta"><?php echo esc_html( $client_phone ); ?></span>
                    </div>
                    <button type="button" class="c-devices__cancel-btn js-presupuesto-client-change">Cambiar cliente</button>
                </div>

                <div class="c-pedido-client__search-wrap js-presupuesto-client-search-wrap<?php echo $client_id ? ' is-hidden' : ''; ?>">
                    <input type="text" class="c-list-cpt-sats__search js-presupuesto-client-search" placeholder="Buscar cliente por nombre, teléfono o email... (opcional)" autocomplete="off">
                    <div class="c-pedido-client__results js-presupuesto-client-results is-hidden"></div>
                    <button type="button" class="o-button o-button--style-2 js-client-modal-open">+ Crear cliente</button>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Líneas del presupuesto ─────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Artículos y servicios</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <?php include locate_template( 'components/c-presupuesto-lines.php' ); ?>
            </div>
        </div>
    </div>

    <!-- ── Notas ──────────────────────────────────────────────────────── -->
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Notas</div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-list-cpt-sats__field c-devices__field--full">
                <label class="c-list-cpt-sats__field-label" for="pre-notes">Notas del presupuesto</label>
                <textarea id="pre-notes" class="c-list-cpt-sats__search" name="notes" rows="3" placeholder="Se imprimen en el PDF"><?php echo esc_textarea( $notes ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- ── Resumen (no plegable: siempre visible antes de guardar) ─────── -->
    <div class="c-pedido-summary js-presupuesto-summary">
        <div class="c-pedido-summary__row">
            <span>Cliente</span>
            <strong class="js-presupuesto-summary-client"><?php echo $client_name ? esc_html( $client_name ) : 'Sin elegir'; ?></strong>
        </div>
        <div class="c-pedido-summary__row">
            <span>Líneas</span>
            <strong class="js-presupuesto-summary-count">0</strong>
        </div>
        <div class="c-pedido-summary__row c-pedido-summary__row--total">
            <span>Total (con IVA)</span>
            <strong class="js-presupuesto-summary-total">0,00 €</strong>
        </div>
    </div>

    <!-- ── Acciones fijas (mismo componente/CSS que el SAT) ────────────── -->
    <div class="c-sat-form__wrapper-ctas">
        <div class="c-sat-form__ctas-inner">
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
                <a class="c-sat-form__cta c-sat-form__cta--secondary"
                   href="<?php echo esc_url( av_presupuestos_page_url() ); ?>"
                   onclick="return confirm('¿Salir sin guardar los cambios?');">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    <span>Cancelar</span>
                </a>
            </div>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">
                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span><?php echo $editando ? 'Guardar cambios' : 'Crear presupuesto'; ?></span>
                </button>
            </div>
        </div>
    </div>
</form>

<?php include( locate_template( 'components/c-client-modal.php' ) ); ?>
