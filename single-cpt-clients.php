<?php
/*
    Single
*/

the_post();
get_header();

    $title = 'Modificar Cliente';
    $post_id = get_the_ID();
    $name = get_field('cpt-client__name', $post_id); 
    $extension = get_field('cpt-client__extension', $post_id); 
    $phone = get_field('cpt-client__phone', $post_id); 
    $email = get_field('cpt-client__email', $post_id); 
    $dni = get_field('cpt-client__dni', $post_id); 
    $type_client = get_field('cpt-client__type-client', $post_id);

    ?>

        <section class="o-main s-template-single-cpt-clients">
            <?php
                $c_project            = get_post();            
            ?>

            <?php include( locate_template('components/c-client-choice.php') ); ?>

        </section>

    <?php

get_footer(); 

?>