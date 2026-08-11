<?php
/**
 * Pestaña "Facturación" de la Configuración general.
 * Variables disponibles desde el shell: $cfg (av_get_invoice_config()).
 */
?>
<form method="POST" action="<?php echo esc_url( get_permalink() ); ?>" class="c-cfg__form">
    <?php wp_nonce_field( 'av_invoice_config' ); ?>
    <input type="hidden" name="av_invoice_config_save" value="1">
    <input type="hidden" name="av_cfg_tab" value="facturacion">

    <!-- ── Fila superior: Emisor + Visibilidad ── -->
    <div class="c-cfg__row">

        <!-- Datos del emisor -->
        <div class="c-cfg__section">
            <div class="c-cfg__section-title">Datos del emisor</div>

            <?php
            $logo_preview_url = av_invoice_logo_url( $cfg );
            ?>
            <div class="c-cfg__field">
                <label>Logo</label>
                <input type="hidden" name="logo_attachment_id" id="cfg-logo-id"
                       value="<?php echo esc_attr( $cfg['logo_attachment_id'] ?? 0 ); ?>">
                <input type="hidden" name="logo_url" id="cfg-logo-url"
                       value="<?php echo esc_attr( $cfg['logo_url'] ?? '' ); ?>">
                <div class="c-cfg__media-row">
                    <span id="cfg-logo-name" class="c-cfg__input" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:default;color:var(--color-text-muted,#64748b)">
                        <?php echo $logo_preview_url ? basename( $logo_preview_url ) : 'Sin imagen seleccionada'; ?>
                    </span>
                    <button type="button" id="cfg-logo-btn" class="c-cfg__media-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Seleccionar
                    </button>
                </div>
                <?php if ( $logo_preview_url ) : ?>
                <img src="<?php echo esc_url( $logo_preview_url ); ?>" id="cfg-logo-preview"
                     style="max-height:48px;margin-top:8px;display:block;">
                <?php else : ?>
                <img id="cfg-logo-preview" style="max-height:48px;margin-top:8px;display:none;">
                <?php endif; ?>
            </div>

            <div class="c-cfg__field">
                <label>Razón social</label>
                <input type="text" name="razon_social"
                       value="<?php echo esc_attr( $cfg['razon_social'] ); ?>"
                       placeholder="iCOREBYTE S.L." class="c-cfg__input">
            </div>

            <div class="c-cfg__field">
                <label>NIF / CIF</label>
                <input type="text" name="nif_cif"
                       value="<?php echo esc_attr( $cfg['nif_cif'] ); ?>"
                       placeholder="B12345678" class="c-cfg__input">
            </div>

            <div class="c-cfg__field">
                <label>Web</label>
                <input type="text" name="web"
                       value="<?php echo esc_attr( $cfg['web'] ); ?>"
                       placeholder="icorebyte.com" class="c-cfg__input">
            </div>

            <div class="c-cfg__field">
                <label>Dirección</label>
                <input type="text" name="direccion"
                       value="<?php echo esc_attr( $cfg['direccion'] ); ?>"
                       placeholder='Calle Ejemplo 123, local "A"' class="c-cfg__input">
            </div>

            <div class="c-cfg__field">
                <label>País</label>
                <select name="pais" id="cfg-pais" class="c-cfg__input">
                    <?php
                    $paises_list = [
                        'España','Francia','Alemania','Italia','Portugal',
                        'Países Bajos','Bélgica','Austria','Grecia','Irlanda',
                        'Finlandia','Suecia','Noruega','Dinamarca','Suiza',
                        'Polonia','República Checa','Hungría','Rumanía',
                        'Reino Unido','Estados Unidos','México','Argentina',
                        'Colombia','Chile','Brasil','Perú',
                    ];
                    $pais_actual = $cfg['pais'] ?? 'España';
                    foreach ( $paises_list as $p ) :
                    ?>
                        <option value="<?php echo esc_attr( $p ); ?>" <?php selected( $pais_actual, $p ); ?>><?php echo esc_html( $p ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="c-cfg__field c-cfg__field--inline">
                <div>
                    <label>Moneda</label>
                    <input type="text" name="moneda" id="cfg-moneda" maxlength="4"
                           value="<?php echo esc_attr( $cfg['moneda'] ?? 'EUR' ); ?>"
                           placeholder="EUR" class="c-cfg__input c-cfg__input--short">
                </div>
                <div>
                    <label>IVA por defecto</label>
                    <div class="c-cfg__iva-wrap">
                        <input type="number" name="iva_pct" id="cfg-iva" min="0" max="100" step="0.1"
                               value="<?php echo esc_attr( $cfg['iva_pct'] ?? '21' ); ?>"
                               class="c-cfg__input c-cfg__input--short">
                        <span class="c-cfg__iva-pct">%</span>
                    </div>
                </div>
            </div>

            <div class="c-cfg__field">
                <label class="c-cfg__toggle-label">
                    <input type="checkbox" name="iva_auto" id="cfg-iva-auto" value="1"
                           <?php checked( $cfg['iva_auto'] ?? '1', '1' ); ?>>
                    Aplicar IVA y moneda automáticamente según el país
                </label>
            </div>
        </div>

        <!-- Mostrar / ocultar campos -->
        <div class="c-cfg__col-checks">

            <!-- Cliente -->
            <div class="c-cfg__section">
                <div class="c-cfg__section-title">
                    <label class="c-cfg__toggle-label">
                        <input type="checkbox" name="cliente_mostrar" value="1"
                               <?php checked( $cfg['cliente']['mostrar'], '1' ); ?>>
                        Datos del cliente
                    </label>
                </div>
                <div class="c-cfg__checks-label">Mostrar / ocultar campos</div>
                <div class="c-cfg__checks">
                    <label><input type="checkbox" name="cliente_nombre"   value="1" <?php checked( $cfg['cliente']['nombre'],   '1' ); ?>> Nombre completo</label>
                    <label><input type="checkbox" name="cliente_telefono" value="1" <?php checked( $cfg['cliente']['telefono'], '1' ); ?>> Teléfono</label>
                    <label><input type="checkbox" name="cliente_dni"      value="1" <?php checked( $cfg['cliente']['dni'],      '1' ); ?>> DNI / NIE / NIF</label>
                    <label><input type="checkbox" name="cliente_email"    value="1" <?php checked( $cfg['cliente']['email'],    '1' ); ?>> Email</label>
                </div>
            </div>

            <!-- Equipo -->
            <div class="c-cfg__section">
                <div class="c-cfg__section-title">
                    <label class="c-cfg__toggle-label">
                        <input type="checkbox" name="equipo_mostrar" value="1"
                               <?php checked( $cfg['equipo']['mostrar'], '1' ); ?>>
                        Datos del equipo
                    </label>
                </div>
                <div class="c-cfg__checks-label">Mostrar / ocultar campos</div>
                <div class="c-cfg__checks">
                    <label><input type="checkbox" name="equipo_sat_num"    value="1" <?php checked( $cfg['equipo']['sat_num'],    '1' ); ?>> Nº SAT</label>
                    <label><input type="checkbox" name="equipo_tipo"       value="1" <?php checked( $cfg['equipo']['tipo'],       '1' ); ?>> Tipo de equipo</label>
                    <label><input type="checkbox" name="equipo_modelo"     value="1" <?php checked( $cfg['equipo']['modelo'],     '1' ); ?>> Modelo</label>
                    <label><input type="checkbox" name="equipo_serial"     value="1" <?php checked( $cfg['equipo']['serial'],     '1' ); ?>> S/N · IMEI</label>
                    <label><input type="checkbox" name="equipo_incidencia" value="1" <?php checked( $cfg['equipo']['incidencia'], '1' ); ?>> Incidencia</label>
                </div>
            </div>

        </div><!-- /.c-cfg__col-checks -->
    </div><!-- /.c-cfg__row -->

    <?php
    $preview_nonce = wp_create_nonce( 'av_factura_preview' );
    $preview_url   = esc_url( home_url( '/?factura_preview=1&nonce=' . $preview_nonce ) );
    ?>
    <div class="c-cfg__actions">
        <a href="<?php echo $preview_url; ?>" target="_blank" class="c-sat-form__cta c-sat-form__cta--secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Previsualizar factura
        </a>
        <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Guardar configuración
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── Auto-fill país → moneda + IVA ──
    const PAISES = {
        'España':          { moneda: 'EUR', iva: '21'   },
        'Francia':         { moneda: 'EUR', iva: '20'   },
        'Alemania':        { moneda: 'EUR', iva: '19'   },
        'Italia':          { moneda: 'EUR', iva: '22'   },
        'Portugal':        { moneda: 'EUR', iva: '23'   },
        'Países Bajos':    { moneda: 'EUR', iva: '21'   },
        'Bélgica':         { moneda: 'EUR', iva: '21'   },
        'Austria':         { moneda: 'EUR', iva: '20'   },
        'Grecia':          { moneda: 'EUR', iva: '24'   },
        'Irlanda':         { moneda: 'EUR', iva: '23'   },
        'Finlandia':       { moneda: 'EUR', iva: '25.5' },
        'Suecia':          { moneda: 'SEK', iva: '25'   },
        'Noruega':         { moneda: 'NOK', iva: '25'   },
        'Dinamarca':       { moneda: 'DKK', iva: '25'   },
        'Suiza':           { moneda: 'CHF', iva: '8.1'  },
        'Polonia':         { moneda: 'PLN', iva: '23'   },
        'República Checa': { moneda: 'CZK', iva: '21'   },
        'Hungría':         { moneda: 'HUF', iva: '27'   },
        'Rumanía':         { moneda: 'RON', iva: '19'   },
        'Reino Unido':     { moneda: 'GBP', iva: '20'   },
        'Estados Unidos':  { moneda: 'USD', iva: '0'    },
        'México':          { moneda: 'MXN', iva: '16'   },
        'Argentina':       { moneda: 'ARS', iva: '21'   },
        'Colombia':        { moneda: 'COP', iva: '19'   },
        'Chile':           { moneda: 'CLP', iva: '19'   },
        'Brasil':          { moneda: 'BRL', iva: '12'   },
        'Perú':            { moneda: 'PEN', iva: '18'   },
    }
    const paisSel   = document.getElementById('cfg-pais')
    const monedaInp = document.getElementById('cfg-moneda')
    const ivaInp    = document.getElementById('cfg-iva')
    const ivaAuto   = document.getElementById('cfg-iva-auto')

    if (paisSel && monedaInp && ivaInp && ivaAuto) {
        paisSel.addEventListener('change', () => {
            if (!ivaAuto.checked) return
            const data = PAISES[paisSel.value]
            if (data) {
                monedaInp.value = data.moneda
                ivaInp.value    = data.iva
            }
        })
    }

    const btn     = document.getElementById('cfg-logo-btn')
    const idInput = document.getElementById('cfg-logo-id')
    const urlInput= document.getElementById('cfg-logo-url')
    const nameEl  = document.getElementById('cfg-logo-name')
    const preview = document.getElementById('cfg-logo-preview')

    if (btn && typeof wp !== 'undefined' && wp.media) {
        btn.addEventListener('click', () => {
            const frame = wp.media({
                title: 'Seleccionar logo de factura',
                button: { text: 'Usar esta imagen' },
                multiple: false,
                library: { type: 'image' },
            })
            frame.on('select', () => {
                const att = frame.state().get('selection').first().toJSON()
                idInput.value  = att.id
                urlInput.value = att.url
                nameEl.textContent = att.filename || att.url.split('/').pop()
                preview.src    = att.url
                preview.style.display = 'block'
            })
            frame.open()
        })
    }
})
</script>
