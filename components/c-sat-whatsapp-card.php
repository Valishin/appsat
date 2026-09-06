<?php
/**
 * Sección "WhatsApp" del detalle del SAT: muestra la conversación asociada
 * (si existe) sin crear ni enviar nada. $sat_id viene del scope de
 * c-single-cpt-sat.php.
 */
?>
<div class="c-sat-whatsapp-card">
    <div class="c-sat-whatsapp-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        WhatsApp
    </div>
    <div class="c-sat-whatsapp-card__body js-whatsapp-sat-card" data-sat-id="<?php echo esc_attr( $sat_id ); ?>">
        <div class="c-sat-whatsapp-card__loading">Cargando...</div>
    </div>
</div>
