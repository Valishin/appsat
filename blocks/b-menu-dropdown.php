
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
                        <a class="b-menu-dropdown__logout" href="<?php echo wp_logout_url(home_url()); ?>" class="logout-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v10"></path>
                            <path d="M7.5 4.21a9 9 0 1 0 9 0"></path>
                        </svg>                           
                    </a>
                    </nav>
                </div>
            </div>
        </div>            
    </div>
</div>
