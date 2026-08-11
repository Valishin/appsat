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
    </section>

<?php

get_footer(); 

?>