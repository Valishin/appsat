<?php
$logo_url      = get_template_directory_uri() . '/assets/imgs/logo_icb.png';
$current_user  = wp_get_current_user();
$user_name     = $current_user->display_name ?: $current_user->user_login;
$user_initials = strtoupper( mb_substr( $user_name, 0, 2 ) );

// Devuelve la URL de la primera página que usa ese template
function av_tpl_url( $tpl ) {
    $pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => $tpl, 'number' => 1 ] );
    return ! empty( $pages ) ? get_permalink( $pages[0]->ID ) : home_url( '/' );
}

$nav = [
    [
        'section' => 'General',
        'items'   => [
            [
                'label'  => 'Dashboard',
                'url'    => av_tpl_url( 'templates/template-dashboard.php' ),
                'active' => is_page_template( 'templates/template-dashboard.php' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
            ],
        ],
    ],
    [
        'section' => 'Reparaciones',
        'items'   => [
            [
                'label'  => 'SATs',
                'url'    => av_tpl_url( 'templates/template-sats.php' ),
                'active' => is_page_template( 'templates/template-sats.php' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>',
            ],
            [
                'label'  => 'Nuevo SAT',
                'url'    => av_tpl_url( 'templates/template-crear-sat.php' ),
                'active' => is_page_template( 'templates/template-crear-sat.php' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>',
            ],
        ],
    ],
    [
        'section' => 'Clientes',
        'items'   => [
            [
                'label'  => 'Clientes',
                'url'    => av_tpl_url( 'templates/template-clients.php' ),
                'active' => is_page_template( 'templates/template-clients.php' ) || is_singular( 'cpt-clients' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            ],
        ],
    ],
    [
        'section' => 'Facturación',
        'items'   => [
            [
                'label'  => 'Facturas',
                'url'    => av_tpl_url( 'templates/template-facturas.php' ),
                'active' => is_page_template( 'templates/template-facturas.php' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
            ],
        ],
    ],
    [
        'section' => 'Informes',
        'items'   => [
            [
                'label'  => 'Analíticas',
                'url'    => av_tpl_url( 'templates/template-analytics.php' ),
                'active' => is_page_template( 'templates/template-analytics.php' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
            ],
        ],
    ],
    // Sección solo para administradores
    [
        'section' => 'Configuración',
        'items'   => ! current_user_can( 'administrator' ) ? [] : [
            [
                'label'  => 'Configuración general',
                'url'    => av_tpl_url( 'templates/template-config-general.php' ),
                'active' => is_page_template( 'templates/template-config-general.php' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
            ],
            [
                'label'  => 'Usuarios',
                'url'    => av_tpl_url( 'templates/template-usuarios.php' ),
                'active' => is_page_template( 'templates/template-usuarios.php' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><circle cx="19" cy="11" r="2"/><path d="M19 8v1M19 13v1M21.6 9.5l-.9.5M17.3 12l-.9.5M21.6 12.5l-.9-.5M17.3 10l-.9-.5"/></svg>',
            ],
            [
                'label'  => 'Servicios',
                'url'    => av_tpl_url( 'templates/template-servicios.php' ),
                'active' => is_page_template( 'templates/template-servicios.php' ),
                'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
            ],
        ],
    ],
];
?>

<script>
// Estado plegado/desplegado del menú, antes de pintar, para que no dé un salto.
// Si no hay preferencia guardada, se pliega solo en pantallas estrechas.
(function () {
    try {
        var guardado = localStorage.getItem('av_sidebar_collapsed');
        var ancho    = window.innerWidth;
        var plegado  = guardado !== null ? guardado === '1' : (ancho > 1190 && ancho <= 1440);
        if (plegado) document.documentElement.classList.add('is-sidebar-collapsed');
    } catch (e) {}
})();
</script>

<header class="b-header js-header">

    <!-- ── Barra superior mobile ───────────────────────────────────────────── -->
    <div class="b-header__mobile-bar">
        <button class="b-header__toggle js-header__hamburguer" aria-label="Menú">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="b-header__brand-link-mobile">
            <img src="<?php echo esc_url( $logo_url ); ?>" alt="iCB SAT" class="b-header__brand-logo-mobile">
        </a>
    </div>

    <!-- ── Sidebar panel ──────────────────────────────────────────────────── -->
    <div class="b-header__sidebar">

        <!-- Brand + botón de plegar -->
        <div class="b-header__top">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="b-header__brand">
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="iCB SAT" class="b-header__brand-logo">
                <div class="b-header__brand-text">
                    <span class="b-header__brand-name">iCB SAT</span>
                    <span class="b-header__brand-sub">APP Informática</span>
                </div>
            </a>
            <button type="button" class="b-header__collapse js-header__collapse" aria-label="Plegar o desplegar el menú" title="Plegar menú">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="b-header__nav" role="navigation">
            <?php foreach ( $nav as $group ) : ?>
                <?php if ( empty( $group['items'] ) ) continue; // no imprimir secciones sin ítems ?>

                <span class="b-header__nav-section"><?php echo esc_html( $group['section'] ); ?></span>

                <?php foreach ( $group['items'] as $item ) : ?>

                    <?php if ( ! empty( $item['disabled'] ) ) : // pendiente de desarrollar: se muestra pero no es clicable ?>
                        <span class="b-header__nav-item is-disabled" data-label="<?php echo esc_attr( $item['label'] ); ?>" title="Disponible próximamente">
                            <?php echo $item['icon']; ?>
                            <span><?php echo esc_html( $item['label'] ); ?></span>
                            <span class="b-header__nav-badge">Pronto</span>
                        </span>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $item['url'] ); ?>"
                           class="b-header__nav-item<?php echo ! empty( $item['active'] ) ? ' is-active' : ''; ?>"
                           data-label="<?php echo esc_attr( $item['label'] ); ?>">
                            <?php echo $item['icon']; ?>
                            <span><?php echo esc_html( $item['label'] ); ?></span>
                        </a>
                    <?php endif; ?>

                <?php endforeach; ?>

            <?php endforeach; ?>
        </nav>

        <!-- User + logout -->
        <div class="b-header__user">
            <div class="b-header__user-info">
                <span class="b-header__user-avatar"><?php echo esc_html( $user_initials ); ?></span>
                <span class="b-header__user-name"><?php echo esc_html( $user_name ); ?></span>
            </div>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"
               class="b-header__logout" title="Cerrar sesión">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </a>
        </div>

    </div><!-- /.b-header__sidebar -->

    <!-- Overlay mobile -->
    <div class="b-header__overlay js-header__overlay"></div>

</header>
