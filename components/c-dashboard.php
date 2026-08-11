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

$mes_actual  = date( 'm' );
$anyo_actual = date( 'Y' );
$finished    = [ 'finalizado', 'no-reparado', 'garantia' ];

$count_open           = 0;
$count_reparar        = 0;
$count_pieza          = 0;
$count_cliente_espera = 0;
$count_reparado       = 0;
$count_diagnosticar   = 0;
$reparados_mes        = 0;
$ingresos_mes         = 0.0;
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
            $delivery = get_post_meta( $sid, 'cpt-sat__delivery-date', true );
            if ( $delivery ) {
                $dt = DateTime::createFromFormat( 'd/m/Y H:i', $delivery );
                if ( $dt && $dt->format( 'm' ) === $mes_actual && $dt->format( 'Y' ) === $anyo_actual ) {
                    $reparados_mes++;
                    $ingresos_mes += $precio;
                }
            }
            break;
    }
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

        <h2 class="c-dashboard__section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Este mes
        </h2>

        <div class="c-dashboard__kpis">

            <a href="<?php echo esc_url( add_query_arg( 'filter', 'finalizados', $sats_url ) ); ?>" class="c-dashboard__kpi c-dashboard__kpi--green">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11 18 15 14"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( $reparados_mes ); ?></strong>
                    <span class="c-dashboard__kpi-label">Finalizados este mes</span>
                </span>
            </a>

            <?php if ( $is_admin ) : ?>
            <div class="c-dashboard__kpi c-dashboard__kpi--green">
                <span class="c-dashboard__kpi-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </span>
                <span class="c-dashboard__kpi-body">
                    <strong class="c-dashboard__kpi-value"><?php echo esc_html( number_format( $ingresos_mes, 2, ',', '.' ) ); ?> €</strong>
                    <span class="c-dashboard__kpi-label">Ingresos del mes</span>
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
