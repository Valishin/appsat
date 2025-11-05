<?php
/*
Template Name: Template Crear Sat
*/

the_post();
get_header();


?>

    <section class="o-main s-template-crear-sat">
        <?php        
            include( locate_template('components/c-create-sat.php') );
        ?>
    </section>

<?php

get_footer(); 

?>