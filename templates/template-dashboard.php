<?php
/*
Template Name: Template Dashboard
*/

the_post();
get_header();
?>
<section class="o-main s-template-dashboard">
    <?php include locate_template('components/c-dashboard.php'); ?>
</section>
<?php get_footer(); ?>
