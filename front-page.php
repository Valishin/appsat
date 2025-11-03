<?php 

    the_post();
    get_header();

    ?>

        <section class="o-main s-template-home js-template-home">

            <?php

                include( locate_template('components/components.php') );

            ?>

        </section>

    <?php

    get_footer(); 

?>
