<?php
/*
    Single
*/

the_post();
get_header();

    ?>

        <section class="o-main s-template-single-cpt-themes">
            <?php
                $c_themes            = get_post();            
            ?>

            <?php include( locate_template('components/c-single-cpt-sats.php') ); ?>

        </section>

    <?php

get_footer(); 

?>