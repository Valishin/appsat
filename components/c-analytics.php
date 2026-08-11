<?php
// ── Todos los SATs publicados ────────────────────────────────────────────────
$all_sats = get_posts([
    'post_type'      => 'cpt-sats',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
]);
$total = count($all_sats);

// ── Contadores por estado ────────────────────────────────────────────────────
$count_fin  = 0;
$count_norep = 0;
$count_gar  = 0;
$ingresos   = 0.0;

foreach ( $all_sats as $sid ) {
    $estado = get_post_meta( $sid, 'cpt-sat__status', true );
    $precio = floatval( str_replace( ',', '.', get_post_meta( $sid, 'cpt-sat__price', true ) ) );

    if ( $estado === 'finalizado' )   { $count_fin++;   $ingresos += $precio; }
    if ( $estado === 'no-reparado' )  $count_norep++;
    if ( $estado === 'garantia' )     $count_gar++;
}

$pct_fin   = $total ? round( $count_fin   / $total * 100 ) : 0;
$pct_norep = $total ? round( $count_norep / $total * 100 ) : 0;
$pct_gar   = $total ? round( $count_gar   / $total * 100 ) : 0;

// ── Top 5 clientes con más SATs ──────────────────────────────────────────────
$client_counts = [];
foreach ( $all_sats as $sid ) {
    $cid = get_post_meta( $sid, 'cpt-sat__client-id', true );
    if ( $cid ) $client_counts[ $cid ] = ( $client_counts[ $cid ] ?? 0 ) + 1;
}
arsort( $client_counts );
$top5      = array_slice( $client_counts, 0, 5, true );
$max_count = $top5 ? max( $top5 ) : 1;
?>

<div class="c-analytics">
    <div class="c-analytics__inner">
        <div class="c-analytics__container o-container">
            <div class="c-analytics__col o-col-10@md o-col-push-1@md o-col-10@sm o-col-4@xs">
                <h2 class="c-analytics__title o-font-display-2">Analíticas</h2>

                <div class="c-analytics__grid">

                    <!-- KPIs superiores -->
                    <div class="c-analytics__kpis">

                        <div class="c-analytics__kpi">
                            <div class="c-analytics__kpi-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                            </div>
                            <div class="c-analytics__kpi-body">
                                <span class="c-analytics__kpi-value"><?php echo $total; ?></span>
                                <span class="c-analytics__kpi-label">SATs totales</span>
                            </div>
                        </div>

                        <div class="c-analytics__kpi c-analytics__kpi--green">
                            <div class="c-analytics__kpi-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div class="c-analytics__kpi-body">
                                <span class="c-analytics__kpi-value"><?php echo $count_fin; ?></span>
                                <span class="c-analytics__kpi-label">Finalizados</span>
                            </div>
                        </div>

                        <div class="c-analytics__kpi c-analytics__kpi--red">
                            <div class="c-analytics__kpi-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            </div>
                            <div class="c-analytics__kpi-body">
                                <span class="c-analytics__kpi-value"><?php echo $count_norep; ?></span>
                                <span class="c-analytics__kpi-label">No reparados</span>
                            </div>
                        </div>

                        <div class="c-analytics__kpi c-analytics__kpi--orange">
                            <div class="c-analytics__kpi-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <div class="c-analytics__kpi-body">
                                <span class="c-analytics__kpi-value"><?php echo $count_gar; ?></span>
                                <span class="c-analytics__kpi-label">Garantías</span>
                            </div>
                        </div>

                        <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <div class="c-analytics__kpi c-analytics__kpi--blue">
                            <div class="c-analytics__kpi-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            </div>
                            <div class="c-analytics__kpi-body">
                                <span class="c-analytics__kpi-value"><?php echo number_format( $ingresos, 2, ',', '.' ); ?> €</span>
                                <span class="c-analytics__kpi-label">Ingresos finalizados</span>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- Porcentajes -->
                    <div class="c-analytics__card">
                        <h3 class="c-analytics__card-title">Estado de reparaciones</h3>
                        <div class="c-analytics__bars">

                            <div class="c-analytics__bar-row">
                                <span class="c-analytics__bar-label">Finalizados</span>
                                <div class="c-analytics__bar-track">
                                    <div class="c-analytics__bar-fill c-analytics__bar-fill--green" style="width:<?php echo $pct_fin; ?>%"></div>
                                </div>
                                <span class="c-analytics__bar-pct"><?php echo $pct_fin; ?>%</span>
                            </div>

                            <div class="c-analytics__bar-row">
                                <span class="c-analytics__bar-label">No reparados</span>
                                <div class="c-analytics__bar-track">
                                    <div class="c-analytics__bar-fill c-analytics__bar-fill--red" style="width:<?php echo $pct_norep; ?>%"></div>
                                </div>
                                <span class="c-analytics__bar-pct"><?php echo $pct_norep; ?>%</span>
                            </div>

                            <div class="c-analytics__bar-row">
                                <span class="c-analytics__bar-label">Garantía</span>
                                <div class="c-analytics__bar-track">
                                    <div class="c-analytics__bar-fill c-analytics__bar-fill--orange" style="width:<?php echo $pct_gar; ?>%"></div>
                                </div>
                                <span class="c-analytics__bar-pct"><?php echo $pct_gar; ?>%</span>
                            </div>

                        </div>
                    </div>

                    <!-- Top 5 clientes -->
                    <div class="c-analytics__card">
                        <h3 class="c-analytics__card-title">Top 5 clientes</h3>
                        <div class="c-analytics__top">
                            <?php foreach ( $top5 as $cid => $num ) :
                                $name  = get_field( 'cpt-client__name', $cid ) ?: get_the_title( $cid );
                                $pct   = round( $num / $max_count * 100 );
                            ?>
                            <div class="c-analytics__top-row">
                                <a class="c-analytics__top-name" href="<?php echo get_permalink( $cid ); ?>"><?php echo esc_html( $name ); ?></a>
                                <div class="c-analytics__bar-track">
                                    <div class="c-analytics__bar-fill c-analytics__bar-fill--purple" style="width:<?php echo $pct; ?>%"></div>
                                </div>
                                <span class="c-analytics__top-count"><?php echo $num; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
