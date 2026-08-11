<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Factura SAT #<?php echo esc_html( $sat_id_visible ); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;font-size:13px;color:#111;background:#f0f0f0}

@page{margin:0}
@media print{
    body{background:#fff;-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .no-print{display:none!important}
    .page{margin:0 auto;box-shadow:none;max-width:100%;border-radius:0;padding:40px;min-height:100vh}
}

.no-print{
    position:fixed;top:16px;right:16px;z-index:100;
    display:flex;gap:10px;
}
.btn-print{
    display:inline-flex;align-items:center;gap:8px;
    padding:10px 20px;background:#111;color:#fff;
    border:none;border-radius:8px;font-size:14px;font-weight:600;
    cursor:pointer;text-decoration:none;
}
.btn-print:hover{background:#333}
.btn-close{
    display:inline-flex;align-items:center;gap:8px;
    padding:10px 20px;background:#fff;color:#111;
    border:1.5px solid #ccc;border-radius:8px;font-size:14px;font-weight:600;
    cursor:pointer;
}
.btn-close:hover{border-color:#111;color:#111}

.page{
    max-width:820px;margin:24px auto;padding:52px 56px;
    background:#fff;border-radius:12px;
    box-shadow:0 4px 24px rgba(0,0,0,.10);
    /* La hoja ocupa siempre el alto completo (menos el margen), con el pie
       anclado abajo aunque la factura tenga pocas líneas. */
    min-height:calc(100vh - 48px);
    display:flex;flex-direction:column;
}
/* Empuja el pie al fondo de la hoja manteniendo una separación mínima */
.inv-spacer{flex:1 1 auto;min-height:40px}

/* ── Cabecera ── */
.inv-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    padding-bottom:28px;
    margin-bottom:32px;
    border-bottom:2px solid #111;
}
.inv-brand{}
.inv-logo{
    height:96px;
    width:auto;
    display:block;
    margin-bottom:6px;
    margin-left:-60px;
}
.inv-brand-sub{
    font-size:17px;font-weight:700;color:#111;letter-spacing:-.2px;margin-bottom:6px;
}
.inv-brand-web{
    font-size:11px;color:#111;margin-top:2px;
}
.inv-brand-web--bold{
    font-weight:700;
}

.inv-sat-block{text-align:right}
.inv-sat-label{
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:1px;color:#111;margin-bottom:4px;
}
.inv-sat-num{
    font-size:16px;font-weight:400;color:#111;letter-spacing:0;
}
.inv-sat-dates{
    font-size:11px;color:#111;margin-top:6px;line-height:1.7;
}

/* ── Grid info ── */
.inv-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:28px;
    margin-bottom:36px;
}
.inv-block h3{
    font-size:9.5px;font-weight:700;text-transform:uppercase;
    letter-spacing:1px;color:#111;
    border-bottom:1px solid #ddd;padding-bottom:7px;margin-bottom:11px;
}
.inv-row{display:flex;gap:8px;margin-bottom:4px;font-size:10px;line-height:1.45}
.inv-lbl{color:#111;min-width:70px;flex-shrink:0}
.inv-val{font-weight:600;color:#111;word-break:break-word}

/* ── Tablas ── */
.inv-section{margin-bottom:28px}
.inv-section-head{
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:1px;color:#111;
    margin-bottom:8px;
    display:flex;align-items:center;gap:6px;
}
.inv-section-head::before{
    content:'';display:block;width:3px;height:14px;
    background:#111;border-radius:2px;flex-shrink:0;
}

table{width:100%;border-collapse:collapse;font-size:11px}
thead th{
    background:#f5f5f5;padding:6px 10px;text-align:left;
    font-size:9.5px;font-weight:700;color:#111;
    text-transform:uppercase;letter-spacing:.5px;
    border-top:1px solid #ddd;border-bottom:1px solid #ddd;
}
thead th.r{text-align:right}
tbody tr:nth-child(even){background:#fafafa}
tbody td{
    padding:6px 10px;border-bottom:1px solid #eee;
    color:#111;vertical-align:top;
}
tbody td.r{text-align:right;font-weight:700;white-space:nowrap}
.sub-row td{
    border-bottom:none;padding-top:4px;
    font-size:10px;color:#111;
}
.sub-row td.r{font-weight:400}

/* ── Total ── */
.inv-totals{
    /* En flex los márgenes no colapsan: la separación la pone el bloque anterior */
    margin-top:0;
    border-top:2px solid #111;
    padding-top:18px;
}
.inv-total-sub{
    display:flex;justify-content:flex-end;
    gap:48px;padding:3px 0;font-size:12px;color:#111;
}
.inv-total-sub .lbl{font-weight:400}
.inv-total-sub .val{min-width:90px;text-align:right}
.inv-total-final{
    display:flex;justify-content:flex-end;
    gap:48px;margin-top:10px;
    padding-top:10px;border-top:1px solid #ddd;
}
.inv-total-final .lbl{
    font-size:15px;font-weight:800;color:#111;
    text-transform:uppercase;letter-spacing:.5px;
    align-self:center;
}
.inv-total-final .val{
    font-size:16px;font-weight:700;color:#111;
    min-width:90px;text-align:right;
}

.inv-payment{
    margin-top:12px;text-align:right;
    font-size:11.5px;color:#111;
}

/* ── Garantía ── */
.inv-warranty{
    margin-top:24px;padding:12px 14px;
    border:1px solid #ddd;border-left:3px solid #111;
    border-radius:4px;background:#fafafa;
    page-break-inside:avoid;
}
.inv-warranty-head{
    font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:1px;color:#111;margin-bottom:5px;
}
.inv-warranty-text{
    font-size:11px;line-height:1.55;color:#111;
}

/* ── Pie ── */
.inv-footer{
    padding-top:16px;
    border-top:1px solid #ddd;
    display:flex;justify-content:space-between;align-items:center;
    font-size:10.5px;color:#111;
}
.inv-footer strong{color:#111}
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Imprimir / Guardar PDF
    </button>
    <button class="btn-close" onclick="window.close()">Cerrar</button>
</div>

<div class="page">

    <?php
    // Leer también campos extra de cliente que no venían en la función original
    $client_dni   = $client_id ? get_field( 'cpt-client__dni',   $client_id ) : '';
    $client_email = $client_id ? get_field( 'cpt-client__email', $client_id ) : '';
    $cc = $inv_cfg['cliente'];
    $ce = $inv_cfg['equipo'];
    ?>

    <!-- ── Cabecera ── -->
    <div class="inv-header">
        <div class="inv-brand">
            <?php $inv_logo = av_invoice_logo_url( $inv_cfg ); ?>
            <?php if ( $inv_logo ) : ?>
            <img src="<?php echo esc_url( $inv_logo ); ?>" alt="Logo" class="inv-logo">
            <?php endif; ?>
            <div class="inv-brand-sub">Factura de reparación</div>
            <?php if ( $inv_cfg['razon_social'] ) : ?>
            <div class="inv-brand-web" style="font-weight:700"><?php echo esc_html( $inv_cfg['razon_social'] ); ?></div>
            <?php endif; ?>
            <?php if ( $inv_cfg['nif_cif'] ) : ?>
            <div class="inv-brand-web"><?php echo esc_html( $inv_cfg['nif_cif'] ); ?></div>
            <?php endif; ?>
            <?php if ( $inv_cfg['direccion'] ) : ?>
            <div class="inv-brand-web"><?php echo esc_html( $inv_cfg['direccion'] ); ?></div>
            <?php endif; ?>
            <?php if ( $inv_cfg['web'] ) : ?>
            <div class="inv-brand-web inv-brand-web--bold"><?php echo esc_html( $inv_cfg['web'] ); ?></div>
            <?php endif; ?>
        </div>
        <div class="inv-sat-block">
            <div class="inv-sat-label">Número de Factura</div>
            <div class="inv-sat-num"><?php echo esc_html( $factura_numero ?: '—' ); ?></div>
            <div class="inv-sat-dates">
                Entrada: <strong><?php echo esc_html( $entry_date ); ?></strong>
                <?php if ( $repair_date ) : ?>
                <br>Reparación: <strong><?php echo esc_html( $repair_date ); ?></strong>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Cliente / Equipo ── -->
    <div class="inv-grid">
        <?php if ( $cc['mostrar'] ) : ?>
        <div class="inv-block">
            <h3>Cliente</h3>
            <?php if ( $cc['nombre']   && $client_name  ) : ?><div class="inv-row"><span class="inv-lbl">Nombre</span><span class="inv-val"><?php echo esc_html( $client_name ); ?></span></div><?php endif; ?>
            <?php if ( $cc['telefono'] && $client_phone ) : ?><div class="inv-row"><span class="inv-lbl">Teléfono</span><span class="inv-val"><?php echo esc_html( $client_phone ); ?></span></div><?php endif; ?>
            <?php if ( $cc['dni']      && $client_dni   ) : ?><div class="inv-row"><span class="inv-lbl">DNI/NIE</span><span class="inv-val"><?php echo esc_html( $client_dni ); ?></span></div><?php endif; ?>
            <?php if ( $cc['email']    && $client_email ) : ?><div class="inv-row"><span class="inv-lbl">Email</span><span class="inv-val"><?php echo esc_html( $client_email ); ?></span></div><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ( $ce['mostrar'] ) : ?>
        <div class="inv-block">
            <h3>Equipo</h3>
            <?php if ( $ce['sat_num']    && $sat_id_visible ) : ?><div class="inv-row"><span class="inv-lbl">Nº SAT</span><span class="inv-val">#<?php echo esc_html( $sat_id_visible ); ?></span></div><?php endif; ?>
            <?php if ( $ce['tipo']       && $type_label     ) : ?><div class="inv-row"><span class="inv-lbl">Tipo</span><span class="inv-val"><?php echo esc_html( $type_label ); ?></span></div><?php endif; ?>
            <?php if ( $ce['modelo']     && $model          ) : ?><div class="inv-row"><span class="inv-lbl">Modelo</span><span class="inv-val"><?php echo esc_html( $model ); ?></span></div><?php endif; ?>
            <?php if ( ! empty( $physical_condition )       ) : ?><div class="inv-row"><span class="inv-lbl">Estado</span><span class="inv-val"><?php echo esc_html( $physical_condition ); ?></span></div><?php endif; ?>
            <?php if ( $ce['serial']     && $serial         ) : ?><div class="inv-row"><span class="inv-lbl">S/N · IMEI</span><span class="inv-val"><?php echo esc_html( $serial ); ?></span></div><?php endif; ?>
            <?php if ( $ce['incidencia'] && $incident       ) : ?><div class="inv-row"><span class="inv-lbl">Incidencia</span><span class="inv-val"><?php echo esc_html( $incident ); ?></span></div><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php
    if ( ! isset( $iva_rate ) ) $iva_rate = 1.21;
    $iva_pct_label  = round( ( $iva_rate - 1 ) * 100, 2 ) . '%';
    $currency       = av_currency_symbol( $inv_cfg['moneda'] ?? 'EUR' );
    $repair_sub = 0;
    foreach ( $repair_items as $it ) {
        $p = floatval( str_replace( ',', '.', $it['price'] ?? '' ) );
        if ( $p > 0 ) $repair_sub += $p;
    }
    $parts_sub = 0;
    foreach ( $parts_items as $it ) {
        $p = floatval( str_replace( ',', '.', $it['price'] ?? '' ) );
        if ( $p > 0 ) $parts_sub += $p;
    }
    // Los precios introducidos ya incluyen IVA
    $total_iva  = $repair_sub + $parts_sub;
    $base       = $total_iva / $iva_rate;
    $iva_amount = $total_iva - $base;
    ?>

    <!-- ── Reparación ── -->
    <?php if ( ! empty( $repair_items ) ) : ?>
    <div class="inv-section">
        <div class="inv-section-head">Reparación</div>
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="r" style="width:110px">Sin IVA</th>
                    <th class="r" style="width:110px">Con IVA</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $repair_items as $it ) : ?>
            <tr>
                <td><?php echo esc_html( $it['text'] ?? '' ); ?></td>
                <?php
                $p_raw  = floatval( str_replace( ',', '.', $it['price'] ?? '' ) );
                $p_base = $p_raw > 0 ? $p_raw / $iva_rate : 0;
                ?>
                <td class="r"><?php echo $p_raw > 0 ? number_format( $p_base, 2, ',', '.' ) . ' ' . $currency : '—'; ?></td>
                <td class="r"><?php echo $p_raw > 0 ? number_format( $p_raw,  2, ',', '.' ) . ' ' . $currency : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── Piezas ── -->
    <?php if ( ! empty( $parts_items ) ) : ?>
    <div class="inv-section">
        <div class="inv-section-head">Piezas y materiales</div>
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="r" style="width:110px">Sin IVA</th>
                    <th class="r" style="width:110px">Con IVA</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $parts_items as $it ) : ?>
            <tr>
                <td><?php echo esc_html( $it['text'] ?? '' ); ?></td>
                <?php
                $p_raw  = floatval( str_replace( ',', '.', $it['price'] ?? '' ) );
                $p_base = $p_raw > 0 ? $p_raw / $iva_rate : 0;
                ?>
                <td class="r"><?php echo $p_raw > 0 ? number_format( $p_base, 2, ',', '.' ) . ' ' . $currency : '—'; ?></td>
                <td class="r"><?php echo $p_raw > 0 ? number_format( $p_raw,  2, ',', '.' ) . ' ' . $currency : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── Totales ── -->
    <div class="inv-totals">
        <div class="inv-total-sub">
            <span class="lbl">Base imponible</span>
            <span class="val"><?php echo number_format( $base, 2, ',', '.' ); ?> <?php echo $currency; ?></span>
        </div>
        <div class="inv-total-sub">
            <span class="lbl">IVA (<?php echo esc_html( $iva_pct_label ); ?>)</span>
            <span class="val"><?php echo number_format( $iva_amount, 2, ',', '.' ); ?> <?php echo $currency; ?></span>
        </div>
        <div class="inv-total-final">
            <span class="lbl">Total</span>
            <span class="val"><?php echo number_format( $total_iva, 2, ',', '.' ); ?> <?php echo $currency; ?></span>
        </div>
    </div>

    <?php if ( $price_desc ) : ?>
    <div class="inv-payment">Forma de pago: <strong><?php echo esc_html( ucfirst( $price_desc ) ); ?></strong></div>
    <?php endif; ?>

    <!-- ── Garantía de la reparación ── -->
    <?php $warranty_label = ! empty( $warranty_period ) ? av_sat_warranty_period_label( $warranty_period ) : ''; ?>
    <?php if ( $warranty_label ) : ?>
    <div class="inv-warranty">
        <div class="inv-warranty-head">Garantía de la reparación</div>
        <div class="inv-warranty-text">
            Esta reparación tiene una garantía de <strong><?php echo esc_html( $warranty_label ); ?></strong>
            desde la fecha de esta factura, sobre la pieza sustituida y la mano de obra,
            no cubriendo daños preexistentes ni ajenos a la intervención realizada.
        </div>
    </div>
    <?php endif; ?>

    <div class="inv-spacer"></div>

    <!-- ── Pie ── -->
    <div class="inv-footer">
        <span>Generado el <?php echo wp_date( 'd/m/Y \a \l\a\s H:i' ); ?><?php if ( $attended ) : ?> · <?php echo esc_html( $attended ); ?><?php endif; ?></span>
        <strong>icorebyte.com</strong>
    </div>

</div>

</body>
</html>
