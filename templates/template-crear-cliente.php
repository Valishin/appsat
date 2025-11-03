<?php
/*
Template Name: Template Crear Cliente
*/

the_post();
get_header();


?>

    <section class="o-main s-template-crear-cliente">
        <?php        
            include( locate_template('components/c-create-client.php') );
        ?>
    </section>

<?php

get_footer(); 

?>