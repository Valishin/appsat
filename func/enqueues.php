<?php

function av_enqueues(){

    if(!is_admin()){        

        // CSS

            // CUSTOM CSS

                // Fuente Montserrat (woff2/woff) autohospedada via @font-face en app.css

                // APP CSS
                wp_register_style('app-css',     DIST_DIRECTORY       . '/app.css', false, filemtime( SERVER_DIST_DIRECTORY . '/app.css') );
                wp_enqueue_style('app-css');                


        // JS

            // CUSTOM JS

                // APP JS
                wp_register_script('app-js',        DIST_DIRECTORY    . '/app.bundle.js',  false, filemtime( SERVER_DIST_DIRECTORY . '/app.bundle.js' ) );
                wp_enqueue_script('app-js');
        

                // AV DATA
                $av_data = array(
                    'av_ajax_url'  => admin_url( 'admin-ajax.php' ),
                    'logo_url'     => get_template_directory_uri() . '/assets/imgs/iCB_SAT_icon.png',
                    'nonce_pdf'    => wp_create_nonce( 'sat_guardar_pdf' ),
                    'logout_url'   => wp_logout_url( home_url( '/login' ) ),
                );

                // SEND CUSTOM DATA TO CUSTOM JS
                wp_localize_script( 'app-js', 'av_data', $av_data );

    }

}
add_action('wp_enqueue_scripts', 'av_enqueues', 100);

function add_rel_preload($html, $handle, $href, $media){
    if (is_admin())
        return $html;
    if(!isBrowser('Firefox'))
    $html = <<<EOT
<link rel='preload' as='style' onload="this.onload=null;this.rel='stylesheet'" id='$handle' href='$href' type='text/css' media='all' />
EOT;
    return $html;
}