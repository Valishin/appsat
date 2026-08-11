<?php
/**
 * Configuración general — shell con pestañas.
 *
 * Para añadir una pestaña nueva basta con:
 *   1. Añadir una entrada a $tabs (clave = slug que se usa en la URL ?tab=).
 *   2. Crear el archivo del panel en components/config/.
 *
 * Variables disponibles: $cfg, $saved (desde el template).
 */

$tabs = [
    'facturacion' => [
        'label' => 'Facturación',
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
        'panel' => 'components/config/c-config-tab-facturacion.php',
    ],
];

// Pestaña activa: la de la URL si existe, si no la primera.
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
if ( ! isset( $tabs[ $active_tab ] ) ) {
    $active_tab = array_key_first( $tabs );
}
?>
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">

                <?php if ( $saved ) : ?>
                <div class="c-cfg__notice">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Configuración guardada correctamente.
                </div>
                <?php endif; ?>

                <div class="c-cfg__tabs js-cfg-tabs" role="tablist">
                    <?php foreach ( $tabs as $slug => $tab ) : ?>
                        <button type="button"
                                role="tab"
                                class="c-cfg__tab js-cfg-tab<?php echo $slug === $active_tab ? ' is-active' : ''; ?>"
                                data-tab="<?php echo esc_attr( $slug ); ?>"
                                aria-selected="<?php echo $slug === $active_tab ? 'true' : 'false'; ?>">
                            <?php echo $tab['icon']; ?>
                            <span><?php echo esc_html( $tab['label'] ); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ( $tabs as $slug => $tab ) : ?>
                    <div class="c-cfg__panel js-cfg-panel<?php echo $slug === $active_tab ? ' is-active' : ''; ?>"
                         data-tab="<?php echo esc_attr( $slug ); ?>"
                         role="tabpanel">
                        <?php include locate_template( $tab['panel'] ); ?>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>

<style>
.c-cfg__notice{
    display:flex;align-items:center;gap:8px;
    background:rgba(22,163,74,.1);color:#15803d;
    border-radius:8px;padding:12px 16px;margin-bottom:20px;
    font-size:13px;font-weight:600;
}

/* ── Pestañas ── */
.c-cfg__tabs{
    display:flex;flex-wrap:wrap;gap:4px;
    border-bottom:1px solid var(--color-border,#e2e8f0);
    margin-bottom:24px;
}
.c-cfg__tab{
    display:inline-flex;align-items:center;gap:7px;
    padding:10px 16px;margin-bottom:-1px;
    background:none;border:none;border-bottom:2px solid transparent;
    font-family:inherit;font-size:13px;font-weight:600;
    color:var(--color-text-muted,#64748b);
    cursor:pointer;transition:color .15s,border-color .15s;
}
.c-cfg__tab:hover{ color:var(--color-text,#1e293b); }
.c-cfg__tab.is-active{
    color:var(--token-primary,#0467F7);
    border-bottom-color:var(--token-primary,#0467F7);
}
.c-cfg__tab svg{ flex-shrink:0; }

.c-cfg__panel{ display:none; }
.c-cfg__panel.is-active{ display:block; }

.c-cfg__form{ max-width:900px; }
.c-cfg__row{
    display:grid;grid-template-columns:1fr 1fr;gap:20px;
    align-items:start;
}
@media(max-width:640px){ .c-cfg__row{ grid-template-columns:1fr; } }
.c-cfg__col-checks{ display:flex;flex-direction:column;gap:20px; }
.c-cfg__section{
    border:1px solid var(--color-border,#e2e8f0);
    border-radius:10px;padding:18px 20px;
}
.c-cfg__section-title{
    font-size:12px;font-weight:700;text-transform:uppercase;
    letter-spacing:.6px;margin-bottom:14px;color:var(--token-primary,#0467F7);
}
.c-cfg__field{ margin-bottom:14px; }
.c-cfg__field:last-child{ margin-bottom:0; }
.c-cfg__field > label{
    display:block;font-size:11px;font-weight:600;
    margin-bottom:5px;color:var(--color-text-muted,#64748b);
    text-transform:uppercase;letter-spacing:.4px;
}
.c-cfg__input{
    width:100%;padding:8px 11px;
    border:1.5px solid var(--color-border,#e2e8f0);
    border-radius:8px;font-size:13px;
    background:var(--token-surface,#fff);
    color:var(--color-text,#1e293b);
    transition:border-color .15s;
}
.c-cfg__input:focus{ outline:none;border-color:var(--token-primary,#0467F7); }
.c-cfg__media-row{ display:flex;gap:8px;align-items:center; }
.c-cfg__media-row .c-cfg__input{ flex:1; }
.c-cfg__media-btn{
    display:inline-flex;align-items:center;gap:5px;white-space:nowrap;
    padding:8px 12px;border:1.5px solid var(--color-border,#e2e8f0);
    border-radius:8px;background:var(--token-surface,#fff);
    font-size:12px;font-weight:600;cursor:pointer;
    color:var(--color-text,#1e293b);transition:border-color .15s;
}
.c-cfg__media-btn:hover{ border-color:var(--token-primary,#0467F7); }
.c-cfg__checks-label{
    font-size:11px;font-weight:600;text-transform:uppercase;
    letter-spacing:.5px;color:var(--color-text-muted,#64748b);
    margin-bottom:8px;
}
.c-cfg__checks{ display:flex;flex-direction:column;gap:9px; }
.c-cfg__checks label{
    display:flex;align-items:center;gap:8px;
    font-size:13px;cursor:pointer;
}
.c-cfg__checks input[type="checkbox"]{
    width:15px;height:15px;cursor:pointer;accent-color:var(--token-primary,#0467F7);
}
.c-cfg__toggle-label{
    display:flex;align-items:center;gap:8px;cursor:pointer;
}
.c-cfg__toggle-label input[type="checkbox"]{
    width:15px;height:15px;cursor:pointer;accent-color:var(--token-primary,#0467F7);
}
.c-cfg__actions{
    position:fixed;left:0;right:0;bottom:0;z-index:500;
    padding:12px 16px calc(12px + env(safe-area-inset-bottom,0px));
    background:var(--token-surface,#fff);
    border-top:1px solid var(--color-border,#e2e8f0);
    display:flex;align-items:center;justify-content:flex-end;gap:10px;
}
@media(min-width:768px){ .c-cfg__actions{ left:220px; } }
.c-cfg__form{ padding-bottom:72px; }
.c-cfg__field--inline{ display:flex;gap:12px; }
.c-cfg__field--inline > div{ flex:1; }
.c-cfg__input--short{ width:100%; }
.c-cfg__iva-wrap{ display:flex;align-items:center;gap:6px; }
.c-cfg__iva-wrap .c-cfg__input--short{ flex:1; }
.c-cfg__iva-pct{ font-size:13px;font-weight:600;color:var(--color-text-muted,#64748b); }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs   = document.querySelectorAll('.js-cfg-tab');
    const panels = document.querySelectorAll('.js-cfg-panel');
    if (!tabs.length) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            tabs.forEach(t => {
                const active = t === tab;
                t.classList.toggle('is-active', active);
                t.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panels.forEach(p => p.classList.toggle('is-active', p.dataset.tab === target));

            // Refleja la pestaña en la URL sin recargar, para poder compartir el enlace
            const url = new URL(window.location);
            url.searchParams.set('tab', target);
            url.searchParams.delete('saved');
            window.history.replaceState({}, '', url);
        });
    });
});
</script>
