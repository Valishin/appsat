<?php
/*
    Single
*/

the_post();
get_header();

    ?>

        <section class="o-main s-template-single-cpt-project">
            <?php
                $c_project            = get_post();            
            ?>

            <?php include( locate_template('components/c-single-cpt-project.php') ); ?>

        </section>

    <?php

get_footer(); 

?>