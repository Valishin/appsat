
<div class="b-menu-dropdown js-menu-dropdown">
    <div class="b-menu-dropdown__wrapper">
        <div class="b-menu-dropdown__inner">
            <div class="b-menu-dropdown__wrapper-menu | js-menu-dropdown-menu">                     
                <div class="b-menu-dropdown__menu">
                    <nav class="b-menu-dropdown__nav" role="navigation">
                        <?php 
                            wp_nav_menu( array(
                                'theme_location' 	=>  'header_menu',
                                'container'     	=>  '',
                                'menu_class'    	=> 'nav b-menu-dropdown__menu main-menu menu-depth-0 o-font-display-headline',
                                'walker'  			=> new WPDocs_Walker_Nav_Menu() // custom walker
                            ));
                        ?>
                    </nav>
                </div>
            </div>
        </div>            
    </div>
</div>
