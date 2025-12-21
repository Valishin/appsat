<?php
/*
    Single
*/

the_post();
get_header();

    ?>

        <section class="o-main s-template-single-cpt-sat">

            <?php include( locate_template('components/c-single-cpt-sat.php') ); ?>

        </section>

    <?php

get_footer(); 

?>