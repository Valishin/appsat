<?php
/*
Template Name: Template Analytics
*/

the_post();
get_header();
?>

    <section class="o-main s-template-analytics">
        <?php include( locate_template('components/c-analytics.php') ); ?>
    </section>

<?php get_footer(); ?>
