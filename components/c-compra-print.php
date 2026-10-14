<?php
// Hoja de conformidad de una COMPRA a un particular (ver inc/compras/compras.php).
// Espera $compra_id en el scope. Se imprime en A4 y la firman a mano el
// vendedor y la tienda — por eso lleva espacio de firma y no firma digital.
$inv_cfg  = av_get_invoice_config();
$currency = av_currency_symbol( $inv_cfg['moneda'] ?? 'EUR' );
$logo     = av_invoice_logo_url( $inv_cfg );

$numero   = get_post_meta( $compra_id, '_compra_numero', true );
$fecha    = get_post_meta( $compra_id, '_compra_fecha', true );
$nombre   = get_post_meta( $compra_id, '_compra_vendedor_nombre', true );
$dni      = get_post_meta( $compra_id, '_compra_vendedor_dni', true );
$telefono = get_post_meta( $compra_id, '_compra_vendedor_telefono', true );
$direccion = get_post_meta( $compra_id, '_compra_vendedor_direccion', true );
$items    = av_compra_get_items( $compra_id );
$total    = floatval( get_post_meta( $compra_id, '_compra_total', true ) );
$forma    = get_post_meta( $compra_id, '_compra_forma_pago', true );
$notas    = get_post_meta( $compra_id, '_compra_notas', true );
$pago_lbl = av_compra_payment_choices()[ $forma ] ?? ucfirst( (string) $forma );
$terms    = av_compras_get_terms();
$fmt      = fn( $n ) => number_format( floatval( $n ), 2, ',', '.' ) . ' ' . $currency;
// Solo la fecha (sin la hora) para "En ..., a ..." al pie.
$fecha_dia = trim( explode( ' ', (string) $fecha )[0] ?? '' );
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Recibo de compra <?php echo esc_html( $numero ); ?></title>
<style>
@page{size:A4;margin:14mm}
*{box-sizing:border-box}
body{margin:0;font-family:Arial,Helvetica,sans-serif;color:#111;font-size:12px;line-height:1.4;background:#fff}
.no-print{padding:12px;text-align:center;background:#f3f4f6;border-bottom:1px solid #ddd}
.no-print button{padding:8px 16px;border:0;border-radius:6px;background:#2563eb;color:#fff;font-size:13px;cursor:pointer;margin:0 4px}
.no-print .btn-close{background:#6b7280}
.page{max-width:780px;margin:0 auto;padding:24px}
.head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;border-bottom:2px solid #111;padding-bottom:12px;margin-bottom:16px}
.head .brand img{max-height:54px;max-width:200px;display:block;margin-bottom:6px}
.head .brand div{font-size:11px;color:#333}
.head .doc{text-align:right}
.head .doc h1{margin:0 0 4px;font-size:20px;letter-spacing:.5px;text-transform:uppercase}
.head .doc .num{font-size:15px;font-weight:700}
.head .doc div{font-size:11px;color:#333}
h2{font-size:11px;text-transform:uppercase;letter-spacing:1px;margin:16px 0 6px;color:#111}
.box{border:1px solid #bbb;border-radius:4px;padding:8px 10px}
.row{display:flex;gap:8px;margin:2px 0}
.row .l{min-width:110px;color:#555}
.row .v{font-weight:600}
table{width:100%;border-collapse:collapse;font-size:11.5px}
th,td{border:1px solid #bbb;padding:6px 7px;text-align:left;vertical-align:top}
th{background:#f3f4f6;font-size:10px;text-transform:uppercase;letter-spacing:.5px}
td.r,th.r{text-align:right;white-space:nowrap}
.total{margin-top:8px;text-align:right;font-size:14px}
.total strong{font-size:16px}
.terms{font-size:9.5px;line-height:1.45;color:#222;white-space:pre-line;border:1px solid #bbb;border-radius:4px;padding:8px 10px;text-align:justify}
.sign{display:flex;gap:30px;margin-top:34px}
.sign .col{flex:1;text-align:center;font-size:11px}
.sign .line{border-top:1px solid #111;margin-top:70px;padding-top:5px}
.place{margin-top:20px;font-size:11px}
@media print{.no-print{display:none}.page{padding:0}}
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">Imprimir / Guardar PDF</button>
    <button class="btn-close" onclick="window.close()">Cerrar</button>
</div>

<div class="page">

    <div class="head">
        <div class="brand">
            <?php if ( $logo ) : ?><img src="<?php echo esc_url( $logo ); ?>" alt=""><?php endif; ?>
            <?php if ( ! empty( $inv_cfg['razon_social'] ) ) : ?><div><strong><?php echo esc_html( $inv_cfg['razon_social'] ); ?></strong></div><?php endif; ?>
            <?php if ( ! empty( $inv_cfg['nif_cif'] ) ) : ?><div><?php echo esc_html( $inv_cfg['nif_cif'] ); ?></div><?php endif; ?>
            <?php if ( ! empty( $inv_cfg['direccion'] ) ) : ?><div><?php echo esc_html( $inv_cfg['direccion'] ); ?></div><?php endif; ?>
        </div>
        <div class="doc">
            <h1>Recibo de compra</h1>
            <div class="num">Nº <?php echo esc_html( $numero ); ?></div>
            <div>Fecha: <strong><?php echo esc_html( $fecha ); ?></strong></div>
        </div>
    </div>

    <h2>Vendedor</h2>
    <div class="box">
        <div class="row"><span class="l">Nombre</span><span class="v"><?php echo esc_html( $nombre ); ?></span></div>
        <div class="row"><span class="l">DNI / NIE</span><span class="v"><?php echo esc_html( $dni ); ?></span></div>
        <?php if ( $telefono ) : ?><div class="row"><span class="l">Teléfono</span><span class="v"><?php echo esc_html( $telefono ); ?></span></div><?php endif; ?>
        <?php if ( $direccion ) : ?><div class="row"><span class="l">Dirección</span><span class="v"><?php echo esc_html( $direccion ); ?></span></div><?php endif; ?>
    </div>

    <h2>Artículo<?php echo count( $items ) > 1 ? 's' : ''; ?> comprado<?php echo count( $items ) > 1 ? 's' : ''; ?></h2>
    <table>
        <thead>
            <tr>
                <th>Artículo</th>
                <th>IMEI / Nº serie</th>
                <th>Estado</th>
                <th>Accesorios</th>
                <th class="r">Precio</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $items as $it ) : ?>
            <tr>
                <td><?php echo esc_html( trim( ( $it['tipo'] ?? '' ) . ' ' . ( $it['modelo'] ?? '' ) ) ); ?></td>
                <td><?php echo esc_html( $it['imei'] ?? '' ); ?></td>
                <td><?php echo esc_html( $it['estado'] ?? '' ); ?></td>
                <td><?php echo esc_html( $it['accesorios'] ?? '' ); ?></td>
                <td class="r"><?php echo esc_html( $fmt( $it['precio'] ?? 0 ) ); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="total">
        Importe total pagado al vendedor (<?php echo esc_html( $pago_lbl ); ?>): <strong><?php echo esc_html( $fmt( $total ) ); ?></strong>
    </div>

    <?php if ( $notas ) : ?>
    <h2>Observaciones</h2>
    <div class="box"><?php echo nl2br( esc_html( $notas ) ); ?></div>
    <?php endif; ?>

    <h2>Condiciones de la compraventa</h2>
    <div class="terms"><?php echo esc_html( $terms ); ?></div>

    <div class="place">
        En <?php echo esc_html( ! empty( $inv_cfg['direccion'] ) ? $inv_cfg['direccion'] : '________________' ); ?>, a <?php echo esc_html( $fecha_dia ); ?>.
    </div>

    <div class="sign">
        <div class="col">
            <div class="line">El vendedor<br><small>Firma y DNI/NIE: <?php echo esc_html( $dni ); ?></small></div>
        </div>
        <div class="col">
            <div class="line">El comprador<br><small><?php echo esc_html( $inv_cfg['razon_social'] ?: 'Firma y sello' ); ?></small></div>
        </div>
    </div>

</div>
</body>
</html>
