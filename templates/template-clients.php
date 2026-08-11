<?php
/*
Template Name: Template Clients
*/

the_post();
get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$posts = new WP_Query( av_build_clients_query_args( $_GET, $paged ) );

$clients = $posts->posts;
?>
    <section class="o-main s-template-clients">
        <?php
            include( locate_template('components/c-list-cpt-clients.php') );
        ?>
    </section>
<?php
get_footer();
?>
