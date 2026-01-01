<?php
/*
    Single
*/

the_post();
get_header();

    $title = 'Modificar Cliente';
    $post_id = get_the_ID();
    $name = get_field('cpt-client__name', $post_id); 
    $phone = get_field('cpt-client__phone', $post_id); 
    $dni = get_field('cpt-client__dni', $post_id); 

    ?>

        <section class="o-main s-template-single-cpt-clients">
            <?php
                $c_project            = get_post();            
            ?>

            <?php include( locate_template('components/c-client-form.php') ); ?>

        </section>

    <?php

get_footer(); 

?>