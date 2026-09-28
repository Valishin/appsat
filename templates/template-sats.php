<?php
/*
Template Name: Template SATS
*/

the_post();
get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$posts = new WP_Query( av_build_sats_query_args( $_GET, $paged ) );

$sats = $posts->posts;


?>

    <section class="o-main s-template-sats">
        <?php
            include( locate_template('components/c-list-cpt-sats.php') );
        ?>

        <!-- "Crear SAT" fijo abajo a la derecha, además del icono de acceso
             rápido en el propio ítem "SATs" del menú lateral (ver el array
             $nav en blocks/b-header.php): mismo componente/CSS que "Nuevo
             pedido" (un solo botón -> modificador --solo, ver
             components/c-sat-form.php). Solo en esta página (no en el mini
             listado de SATs embebido en la ficha de un cliente, que reutiliza
             el mismo components/c-list-cpt-sats.php). -->
        <div class="c-sat-form__wrapper-ctas">
            <div class="c-sat-form__ctas-inner">
                <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary c-sat-form__ctas-group--solo">
                    <a href="<?php echo esc_url( av_crear_sat_page_url() ); ?>" class="c-sat-form__cta c-sat-form__cta--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Crear SAT</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php

get_footer(); 

?>