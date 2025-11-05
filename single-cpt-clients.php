<?php
/*
    Single
*/

the_post();
get_header();

    ?>

        <section class="o-main s-template-single-cpt-clients">
            <?php
                $c_project            = get_post();            
            ?>

            <?php include( locate_template('components/c-single-cpt-clients.php') ); ?>

        </section>

    <?php

get_footer(); 

?>