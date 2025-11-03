<?php
    $footer__socials            = get_field('theme-settings-footer__socials', 'option' );
?>

<!-- FOOTER -->
<footer class="b-footer js-footer js-anim-inview">
    <div class="b-footer__inner">                     
        <div class="b-footer__container | o-container">
           <div class="b-footer__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="b-footer__wrapper-title">
                    <img class="b-footer__title" src="<?php echo IMG_DIRECTORY . '/MAKECODED.svg'; ?>" alt="Logotipo MAKECODED">
                </div>
                <div class="b-footer__wrapper-menu">
                    <nav class="b-header__nav" role="navigation">
                        <?php 
                            wp_nav_menu( array(
                                'theme_location' 	=>  'footer_menu',
                                'container'     	=>  '',
                                'menu_class'    	=> 'nav b-footer__menu main-menu menu-depth-0 o-font-display-overline',
                                'walker'  			=> new WPDocs_Walker_Nav_Menu() // custom walker
                            ));
                        ?>
                    </nav>
                </div>
           </div> 
        </div>
    </div>
</footer>
<!-- END FOOTER -->
