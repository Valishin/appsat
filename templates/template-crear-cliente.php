<?php
/*
Template Name: Template Crear Cliente
*/

the_post();
get_header();

$name   = '';
$dni    = '';
$phone  = '';
$email  = '';
$title  = 'Nuevo Cliente';

?>

    <section class="o-main s-template-crear-cliente">
        <?php        
            include( locate_template('components/c-client-choice.php') );
        ?>
    </section>

<?php

get_footer(); 

?>