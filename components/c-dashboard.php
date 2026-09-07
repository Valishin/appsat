<?php
if ( ! function_exists( 'av_tpl_url' ) ) {
    function av_tpl_url( $tpl ) {
        $pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => $tpl, 'number' => 1 ] );
        return ! empty( $pages ) ? get_permalink( $pages[0]->ID ) : home_url( '/' );
    }
}

$sats_url     = av_tpl_url( 'templates/template-sats.php' );
$clients_url  = av_tpl_url( 'templates/template-clients.php' );
$is_admin     = current_user_can( 'manage_options' );
$current_user = wp_get_current_user();
$user_name    = $current_user->display_name ?: $current_user->user_login;

// ── Cargar todos los IDs de SATs ─────────────────────────────────────────────
$all_ids = get_posts( [
    'post_type'      => 'cpt-sats',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
] );

$meses_nombres = [ 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' ];
$meses_cortos  = [ 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic' ];
$anyo_actual_int = intval( date( 'Y' ) );

// ── Rango real de datos: del primer SAT al último ────────────────────────────
// El selector de mes/año (y el gráfico histórico) no ofrecen periodos sin
// ningún SAT: solo desde que se creó el primero hasta ahora.
$primer_sat = get_posts( [ 'post_type' => 'cpt-sats', 'posts_per_page' => 1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'ASC', 'fields' => 'ids' ] );
if ( $primer_sat ) {
    $fecha_primer_sat = get_post_field( 'post_date', $primer_sat[0] );
    $anyo_min_dato = intval( date( 'Y', strtotime( $fecha_primer_sat ) ) );
    $mes_min_dato  = intval( date( 'm', strtotime( $fecha_primer_sat ) ) );
} else {
    $anyo_min_dato = $anyo_actual_int;
    $mes_min_dato  = intval( date( 'm' ) );
}
// El límite superior nunca es anterior al mes actual (para poder ver "ahora"
// aunque, por lo que sea, el último SAT registrado no sea de hoy).
$anyo_max_dato = $anyo_actual_int;

// ── Mes/año elegido para las estadísticas de "Este mes" (por defecto, el actual) ──
$mes_sel  = isset( $_GET['mes'] ) ? str_pad( intval( $_GET['mes'] ), 2, '0', STR_PAD_LEFT ) : date( 'm' );
if ( ! in_array( $mes_sel, array_map( fn( $n ) => str_pad( $n, 2, '0', STR_PAD_LEFT ), range( 1, 12 ) ), true ) ) {
    $mes_sel = date( 'm' );
}
$anyo_sel = isset( $_GET['anyo'] ) ? intval( $_GET['anyo'] ) : $anyo_actual_int;
if ( $anyo_sel < $anyo_min_dato || $anyo_sel > $anyo_max_dato ) {
    $anyo_sel = $anyo_actual_int;
}
$anyo_sel_str  = (string) $anyo_sel;
$es_mes_actual = ( $mes_sel === date( 'm' ) && $anyo_sel_str === date( 'Y' ) );
$titulo_bloque_mes = $es_mes_actual ? 'Este mes' : ( ucfirst( $meses_nombres[ intval( $mes_sel ) - 1 ] ) . ' ' . $anyo_sel );

// Algunos SATs (antiguos/migrados) tienen las fechas guardadas sin hora
// ('d/m/Y' en vez de 'd/m/Y H:i'), así que se prueban ambos formatos.
$parsear_fecha_flexible = function ( $str ) {
    if ( ! $str ) return false;
    foreach ( [ 'd/m/Y H:i', 'd/m/Y' ] as $formato ) {
        $dt = DateTime::createFromFormat( $formato, $str );
        if ( $dt !== false ) return $dt;
    }
    return false;
};

// Fecha en la que se considera "cerrado" un SAT (para agruparlo por mes, tanto
// en "Este mes" como en el histórico): la de entrega si la tiene, y si no
// (muchos SATs finalizados antes de que se empezara a guardar esa fecha no la
// tienen) se usa la de entrada, para no perder esos ingresos de las estadísticas.
$fecha_cierre_sat = function ( $sid ) use ( $parsear_fecha_flexible ) {
    $delivery = get_post_meta( $sid, 'cpt-sat__delivery-date', true );
    $dt = $delivery ? $parsear_fecha_flexible( $delivery ) : false;
    if ( $dt ) return $dt;
    $entry = get_post_meta( $sid, 'cpt-sat__entry-date', true );
    return $entry ? $parsear_fecha_flexible( $entry ) : false;
};

// Devuelve true si la fecha de cierre del SAT cae dentro del mes/año elegidos.
$en_mes_seleccionado = function ( $sid ) use ( $mes_sel, $anyo_sel_str, $fecha_cierre_sat ) {
    $dt = $fecha_cierre_sat( $sid );
    return $dt && $dt->format( 'm' ) === $mes_sel && $dt->format( 'Y' ) === $anyo_sel_str;
};

$finished    = [ 'finalizado', 'no-reparado', 'garantia' ];

$count_open           = 0;
$count_reparar        = 0;
$count_pieza          = 0;
$count_cliente_espera = 0;
$count_reparado       = 0;
$count_diagnosticar   = 0;
$reparados_mes        = 0;
$no_reparados_mes     = 0;
$garantia_mes         = 0;
$ingresos_mes         = 0.0;
$ingresos_tarjeta     = 0.0;
$ingresos_efectivo    = 0.0;
// "Pendiente de cobro" es un valor de estado actual (SATs reparados sin
// entregar todavía), no depende del mes elegido en el selector.
$pendiente_cobro      = 0.0;
$total_sats           = count( $all_ids );

foreach ( $all_ids as $sid ) {
    $estado = get_post_meta( $sid, 'cpt-sat__status', true );
    $precio = floatval( str_replace( ',', '.', get_post_meta( $sid, 'cpt-sat__price', true ) ) );

    if ( ! in_array( $estado, $finished, true ) ) $count_open++;

    switch ( $estado ) {
        case 'reparar':        $count_reparar++;        break;
        case 'pieza':          $count_pieza++;          break;
        case 'cliente-espera': $count_cliente_espera++; break;
        case 'reparado':
            $count_reparado++;
            $pendiente_cobro += $precio;
            break;
        case 'diagnosticar':   $count_diagnosticar++;   break;
        case 'finalizado':
            if ( $en_mes_seleccionado( $sid ) ) {
                $reparados_mes++;
                $ingresos_mes += $precio;
                $forma_pago = get_post_meta( $sid, 'cpt-sat__price-description', true );
                if ( $forma_pago === 'tarjeta' )        $ingresos_tarjeta  += $precio;
                elseif ( $forma_pago === 'efectivo' )   $ingresos_efectivo += $precio;
            }
            break;
        case 'no-reparado':
            if ( $en_mes_seleccionado( $sid ) ) $no_reparados_mes++;
            break;
        case 'garantia':
            if ( $en_mes_seleccionado( $sid ) ) $garantia_mes++;
            break;
    }
}

// Total de SATs cerrados en el mes elegido (finalizados + no reparados + garantía).
$total_sats_mes = $reparados_mes + $no_reparados_mes + $garantia_mes;

// ── Series mensuales para el gráfico de evolución histórica ──────────────────
// Un punto por cada mes desde el primer SAT hasta el actual, para 3 métricas:
//  - ingresos: suma de precio de los SATs finalizados, por fecha de CIERRE
//    (entrega, o entrada si no tiene fecha de entrega registrada).
//  - sats:     nº de SATs que entraron ese mes (fecha de ENTRADA = volumen de trabajo).
//  - clientes: nº de clientes nuevos registrados ese mes.

$serie_meses = [];
$cy = $anyo_min_dato;
$cm = $mes_min_dato;
while ( $cy < $anyo_actual_int || ( $cy === $anyo_actual_int && $cm <= intval( date( 'm' ) ) ) ) {
    $clave = sprintf( '%04d-%02d', $cy, $cm );
    $serie_meses[ $clave ] = [
        'label'    => $meses_cortos[ $cm - 1 ] . ' ' . $cy,
        'ingresos' => 0.0,
        'sats'     => 0,
        'clientes' => 0,
    ];
    $cm++;
    if ( $cm > 12 ) { $cm = 1; $cy++; }
}

foreach ( $all_ids as $sid ) {
    $entry = get_post_meta( $sid, 'cpt-sat__entry-date', true );
    if ( $entry ) {
        $dt = $parsear_fecha_flexible( $entry );
        if ( $dt ) {
            $clave = $dt->format( 'Y-m' );
            if ( isset( $serie_meses[ $clave ] ) ) $serie_meses[ $clave ]['sats']++;
        }
    }

    if ( get_post_meta( $sid, 'cpt-sat__status', true ) === 'finalizado' ) {
        $dt = $fecha_cierre_sat( $sid );
        if ( $dt ) {
            $clave = $dt->format( 'Y-m' );
            if ( isset( $serie_meses[ $clave ] ) ) {
                $serie_meses[ $clave ]['ingresos'] += floatval( str_replace( ',', '.', get_post_meta( $sid, 'cpt-sat__price', true ) ) );
            }
        }
    }
}

$clientes_ids_todos = get_posts( [ 'post_type' => 'cpt-clients', 'posts_per_page' => -1, 'post_status' => 'publish', 'fields' => 'ids' ] );
foreach ( $clientes_ids_todos as $cid ) {
    $fecha_cliente = get_post_field( 'post_date', $cid );
    if ( ! $fecha_cliente ) continue;
    $clave = date( 'Y-m', strtotime( $fecha_cliente ) );
    if ( isset( $serie_meses[ $clave ] ) ) $serie_meses[ $clave ]['clientes']++;
}

$chart_data = [
    'labels'   => array_values( array_map( fn( $m ) => $m['label'], $serie_meses ) ),
    'sats'     => array_values( array_map( fn( $m ) => $m['sats'], $serie_meses ) ),
    'clientes' => array_values( array_map( fn( $m ) => $m['clientes'], $serie_meses ) ),
];
// Los ingresos son un dato sensible: igual que el resto del dashboard, solo se
// calculan/exponen si el usuario es administrador.
if ( $is_admin ) {
    $chart_data['ingresos'] = array_values( array_map( fn( $m ) => round( $m['ingresos'], 2 ), $serie_meses ) );
}

// ── Técnicos y clientes ──────────────────────────────────────────────────────
$tecnicos       = get_users( [ 'role__in' => [ 'administrator', 'editor' ] ] );
$count_tecnicos = count( $tecnicos );
$count_clientes = wp_count_posts( 'cpt-clients' )->publish ?? 0;

// ── Fecha y saludo ───────────────────────────────────────────────────────────
$dias   = [ 'domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado' ];
$meses  = [ 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' ];
$hora   = (int) date( 'H' );
$saludo = $hora < 13 ? 'Buenos días' : ( $hora < 20 ? 'Buenas tardes' : 'Buenas noches' );
$hoy    = $dias[ date( 'w' ) ] . ', ' . date( 'j' ) . ' de ' . $meses[ (int) date( 'n' ) - 1 ] . ' de ' . date( 'Y' );
?>

<div class="c-dashboard">
<div class="c-dashboard__inner">

    <!-- ── Saludo ───────────────────────────────────────────────────────────── -->
    <div class="c-dashboard__welcome">
        <h1 class="c-dashboard__heading"><?php echo esc_html( $saludo ); ?>, <span><?php echo esc_html( $user_name ); ?></span></h1>
        <p class="c-dashboard__date"><?php echo esc_html( ucfirst( $hoy ) ); ?></p>
    </div>

    <!-- ── BLOQUE 1: Estado actual ──────────────────────────────────────────── -->
    <div class="c-dashboard__section">

        <h2 class="c-dashboard__section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Estado actual
        </h2>

        <div class="c-dashboard__kpis">

            <a href="<?php echo esc_url( add_query_arg( 'filter', 'en-curso', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--blue">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $count_open ); ?></strong>
                    <span class="c-dashboard__kpi-label">SATs abiertos</span>
                </span>
            </a>

            <a href="<?php echo esc_url( add_query_arg( 'estado', 'reparar', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--orange">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $count_reparar ); ?></strong>
                    <span class="c-dashboard__kpi-label">En reparación</span>
                </span>
            </a>

            <a href="<?php echo esc_url( add_query_arg( 'estado', 'pieza', $sats_url ) ); ?>" class="c-dashboard__kpi">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $count_pieza ); ?></strong>
                    <span class="c-dashboard__kpi-label">Esperando pieza</span>
                </span>
            </a>

            <a href="<?php echo esc_url( add_query_arg( 'estado', 'cliente-espera', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--orange">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $count_cliente_espera ); ?></strong>
                    <span class="c-dashboard__kpi-label">Esperando cliente</span>
                </span>
            </a>

            <a href="<?php echo esc_url( add_query_arg( 'estado', 'diagnosticar', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--red">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $count_diagnosticar ); ?></strong>
                    <span class="c-dashboard__kpi-label">Por diagnosticar</span>
                </span>
            </a>

            <a href="<?php echo esc_url( add_query_arg( 'estado', 'reparado', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--green">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $count_reparado ); ?></strong>
                    <span class="c-dashboard__kpi-label">Listos para entrega</span>
                </span>
            </a>

        </div>
    </div>

    <!-- ── BLOQUE 2: Este mes ────────────────────────────────────────────────── -->
    <div class="c-dashboard__section">

        <div class="c-dashboard__section-header">
            <h2 class="c-dashboard__section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo esc_html( $titulo_bloque_mes ); ?>
            </h2>

            <form method="GET" class="c-dashboard__month-picker js-dashboard-month-picker">
                <select name="mes" class="c-dashboard__month-select js-dashboard-month-select">
                    <?php foreach ( $meses_nombres as $i => $nombre ) :
                        $val = str_pad( $i + 1, 2, '0', STR_PAD_LEFT );
                    ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $mes_sel, $val ); ?>><?php echo esc_html( ucfirst( $nombre ) ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="anyo" class="c-dashboard__month-select js-dashboard-month-select">
                    <?php for ( $y = $anyo_max_dato; $y >= $anyo_min_dato; $y-- ) : ?>
                    <option value="<?php echo esc_attr( $y ); ?>" <?php selected( $anyo_sel, $y ); ?>><?php echo esc_html( $y ); ?></option>
                    <?php endfor; ?>
                </select>
                <?php if ( ! $es_mes_actual ) : ?>
                <a href="<?php echo esc_url( remove_query_arg( [ 'mes', 'anyo' ] ) ); ?>" class="c-dashboard__month-reset">Mes actual</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="c-dashboard__kpis">

            <div class="c-dashboard__kpi">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $total_sats_mes ); ?></strong>
                    <span class="c-dashboard__kpi-label">SATs totales</span>
                </span>
            </div>

            <a href="<?php echo esc_url( add_query_arg( 'filter', 'finalizados', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--green">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $reparados_mes ); ?></strong>
                    <span class="c-dashboard__kpi-label">Finalizados</span>
                </span>
            </a>

            <a href="<?php echo esc_url( add_query_arg( 'estado', 'no-reparado', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--red">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $no_reparados_mes ); ?></strong>
                    <span class="c-dashboard__kpi-label">No reparados</span>
                </span>
            </a>

            <a href="<?php echo esc_url( add_query_arg( 'estado', 'garantia', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--blue">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $garantia_mes ); ?></strong>
                    <span class="c-dashboard__kpi-label">En garantía</span>
                </span>
            </a>

            <?php if ( $is_admin ) : ?>
            <!-- Fila propia: los 3 KPIs de ingresos siempre juntos, independientemente
                 de cuántas tarjetas condicionales (tarjeta/efectivo) se acaben mostrando. -->
            <div class="c-dashboard__kpi-row">

                <div class="c-dashboard__kpi c-dashboard__kpi--green">
                    <span class="c-dashboard__kpi-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    </span>
                    <span class="c-dashboard__kpi-body">
                        <strong class="c-dashboard__kpi-value"><?php echo esc_html( number_format( $ingresos_mes, 2, ',', '.' ) ); ?> €</strong>
                        <span class="c-dashboard__kpi-label">Ingresos totales</span>
                    </span>
                </div>

                <?php if ( $ingresos_tarjeta > 0 ) : ?>
                <div class="c-dashboard__kpi c-dashboard__kpi--blue">
                    <span class="c-dashboard__kpi-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </span>
                    <span class="c-dashboard__kpi-body">
                        <strong class="c-dashboard__kpi-value"><?php echo esc_html( number_format( $ingresos_tarjeta, 2, ',', '.' ) ); ?> €</strong>
                        <span class="c-dashboard__kpi-label">Ingresos en tarjeta</span>
                    </span>
                </div>
                <?php endif; ?>

                <?php if ( $ingresos_efectivo > 0 ) : ?>
                <div class="c-dashboard__kpi c-dashboard__kpi--green">
                    <span class="c-dashboard__kpi-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M14.5 9.5a2.5 2 0 0 0-2.5-1.5c-1.38 0-2.5.9-2.5 2s1.12 2 2.5 2 2.5.9 2.5 2-1.12 2-2.5 2a2.5 2 0 0 1-2.5-1.5"/><line x1="12" y1="6" x2="12" y2="8"/><line x1="12" y1="16" x2="12" y2="18"/></svg>
                    </span>
                    <span class="c-dashboard__kpi-body">
                        <strong class="c-dashboard__kpi-value"><?php echo esc_html( number_format( $ingresos_efectivo, 2, ',', '.' ) ); ?> €</strong>
                        <span class="c-dashboard__kpi-label">Ingresos en efectivo</span>
                    </span>
                </div>
                <?php endif; ?>

                <a href="<?php echo esc_url( add_query_arg( 'estado', 'reparado', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--orange">
                    <span class="c-dashboard__kpi-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </span>
                    <span class="c-dashboard__kpi-body">
                        <strong class="c-dashboard__kpi-value"><?php echo esc_html( number_format( $pendiente_cobro, 2, ',', '.' ) ); ?> €</strong>
                        <span class="c-dashboard__kpi-label">Pendiente de cobro</span>
                    </span>
                </a>

            </div>
            <?php else : ?>

            <!-- Sin permiso para ver ingresos: "Pendiente de cobro" se muestra igualmente,
                 suelto (no hay fila de ingresos junto a la que colocarlo). -->
            <a href="<?php echo esc_url( add_query_arg( 'estado', 'reparado', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--orange">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( number_format( $pendiente_cobro, 2, ',', '.' ) ); ?> €</strong>
                    <span class="c-dashboard__kpi-label">Pendiente de cobro</span>
                </span>
            </a>
            <?php endif; ?>

        </div>
    </div>

    <!-- ── BLOQUE: Evolución histórica ──────────────────────────────────────── -->
    <div class="c-dashboard__section">

        <div class="c-dashboard__section-header">
            <h2 class="c-dashboard__section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Evolución histórica
            </h2>

            <select class="c-dashboard__month-select js-dashboard-chart-metric">
                <?php if ( $is_admin ) : ?>
                <option value="ingresos">Ingresos</option>
                <?php endif; ?>
                <option value="sats" <?php selected( ! $is_admin ); ?>>SATs</option>
                <option value="clientes">Clientes</option>
            </select>
        </div>

        <div class="c-dashboard__chart js-dashboard-chart" data-chart="<?php echo esc_attr( wp_json_encode( $chart_data ) ); ?>">
            <svg class="c-dashboard__chart-svg js-dashboard-chart-svg"></svg>
            <p class="c-dashboard__chart-empty js-dashboard-chart-empty is-hidden">Todavía no hay datos suficientes para mostrar esta estadística.</p>
        </div>

    </div>

    <!-- ── BLOQUE 3: Equipo y clientes ──────────────────────────────────────── -->
    <div class="c-dashboard__section">

        <h2 class="c-dashboard__section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Equipo y clientes
        </h2>

        <div class="c-dashboard__cards-row">

            <div class="c-dashboard__card">
                <h3 class="c-dashboard__card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Técnicos (<?php echo esc_html( $count_tecnicos ); ?>)
                </h3>
                <ul class="c-dashboard__tech-list">
                    <?php foreach ( $tecnicos as $tec ) :
                        $initials = strtoupper( mb_substr( $tec->display_name ?: $tec->user_login, 0, 2 ) );
                        $name     = $tec->display_name ?: $tec->user_login;
                        $is_admin_user = in_array( 'administrator', $tec->roles );
                    ?>
                    <li class="c-dashboard__tech-item">
                        <span class="c-dashboard__tech-avatar"><?php echo esc_html( $initials ); ?></span>
                        <span class="c-dashboard__tech-name"><?php echo esc_html( $name ); ?></span>
                        <span class="c-dashboard__tech-role<?php echo $is_admin_user ? ' c-dashboard__tech-role--admin' : ''; ?>"><?php echo $is_admin_user ? 'Admin' : 'Técnico'; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <a href="<?php echo esc_url( $clients_url ); ?>" class="c-dashboard__card c-dashboard__card--link">
                <h3 class="c-dashboard__card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Clientes registrados
                </h3>
                <div class="c-dashboard__card-stat">
                    <strong class="c-dashboard__card-value"><?php echo esc_html( $count_clientes ); ?></strong>
                    <span class="c-dashboard__card-stat-label">clientes</span>
                </div>
            </a>

            <div class="c-dashboard__card">
                <h3 class="c-dashboard__card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                    SATs histórico total
                </h3>
                <div class="c-dashboard__card-stat">
                    <strong class="c-dashboard__card-value"><?php echo esc_html( $total_sats ); ?></strong>
                    <span class="c-dashboard__card-stat-label">SATs</span>
                </div>
            </div>

        </div>
    </div>

</div><!-- /.c-dashboard__inner -->
</div><!-- /.c-dashboard -->
