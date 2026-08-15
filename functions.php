<?php
/*
All the functions are in the PHP pages in the func/ folder.
*/

// THEME FIELDS
// $theme = wp_get_theme();
define('GOOGLE_MAP_API_KEY', '');

// URL PATHS
define ( "CONFIG_DIRECTORY",            get_template_directory_uri()    . "/config" );
define ( "FUNC_DIRECTORY",              get_template_directory_uri()    . "/func" );
define ( "ASSETS_DIRECTORY",            get_template_directory_uri()    . "/assets" );
define ( "PARTS_DIRECTORY",             get_template_directory_uri()    . "/parts" );
define ( "DIST_DIRECTORY",              get_template_directory_uri()    . "/dist" );
define ( "IMG_DIRECTORY",               ASSETS_DIRECTORY                . "/imgs" );
define ( "CSS_DIRECTORY",               ASSETS_DIRECTORY                . "/css" );
define ( "JS_DIRECTORY",                ASSETS_DIRECTORY                . "/js" );

// SERVER PATHS
define ( "SERVER_FUNC_DIRECTORY",       get_template_directory()        . "/func" );
define ( "SERVER_CONFIG_DIRECTORY",     get_template_directory()        . "/config" );
define ( "SERVER_ASSETS_DIRECTORY",     get_template_directory()        . "/assets" );
define ( "SERVER_PARTS_DIRECTORY",      get_template_directory()        . "/parts" );
define ( "SERVER_DIST_DIRECTORY",       get_template_directory()        . "/dist" );
define ( "SERVER_IMG_DIRECTORY",        SERVER_ASSETS_DIRECTORY         . "/imgs" );
define ( "SERVER_CSS_DIRECTORY",        SERVER_ASSETS_DIRECTORY         . "/css" );
define ( "SERVER_JS_DIRECTORY",         SERVER_ASSETS_DIRECTORY         . "/js" );

// ACF GROUP CLONE COMPONENT GLOBAL SETTINGS
define( "SLUG_CGS", 'component-global-settings');

// ACF GROUP CLONE COLORS
define( "SLUG_COLOR", '_colors__color');

// BACKGROUND COLOR DEFAULT
define( "BG_COLOR_DEFAULT", 'white');

// COLOR DEFAULT
define( "COLOR_DEFAULT", 'black');


define( "AV_USER_ID", 1);
define( "AV_USER_ALEX", 'alex');

// REQUIRES

// TGM config
require_once locate_template('/config/wp-require-plugins.php');

// NEED INSTALLED ACF
if(class_exists('acf')){

	// ADD PAGES OF THEME SETTINGS
	require_once locate_template('/config/av-acf-theme-settings.php');

}

require_once locate_template('/func/cleanup.php');
require_once locate_template('/func/setup.php');
require_once locate_template('/func/enqueues.php');
require_once locate_template('/func/register.php');
require_once locate_template('/func/customizer.php');

// BUDGET
// TODO
// require_once locate_template('/func/budget.php');


// FUNCS

// https://css-tricks.com/snippets/wordpress/allow-svg-through-wordpress-media-uploader/
function cc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

// REMOVE SHOW ADMIN BAR
add_filter('show_admin_bar', '__return_false', 100);

/*
* MOBILE DETECT (needs Mobile_Detect.php)
*/
require_once 'Mobile_Detect.php';
function isPhone(){
	$detect = new Mobile_Detect;
	$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
	return $deviceType == 'phone';
}
function isTablet(){
	$detect = new Mobile_Detect;
	$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
	return $deviceType == 'tablet';
}
function getDeviceType(){
	$detect = new Mobile_Detect;
	$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
	return $deviceType;
}

function isBrowser($browser){
    // Check user browser, eg $browser = 'Firefox'
    $isBrowser = false;
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        // echo '<!-- DUMP '.$agent.'-->';    
        if (strlen(strstr($agent, $browser)) > 0) {
            $isBrowser = true;
        }        
    }
    return $isBrowser;
}

// EXAMPLE USAGE: $page_id = av_get_page_id_by_template('templates/r-template-contact.php');
// RETURN FIRST ID PAGE WITH TEMPLATE $slug_template
function av_get_page_id_by_template($slug_template){
    
    // SLUG TEMPLATE EXAMPLE: templates/r-template-contact.php

    // GET PAGES
    $args = [
        'post_type' => 'page',
        'fields' => 'ids',
        'nopaging' => true,
        'meta_key' => '_wp_page_template',
        'meta_value' => $slug_template
    ];
    $pages = get_posts( $args );
    if(is_array($pages) && count($pages)>0) return $pages[0];
    return false;

}

function av_languages_menu($params){

    // This will print the following markup
    // <div class="languages">
    //    <div class="languages__button">EN</div>
    //     <ul class="languages__list">
    //         <li class="languages__lang icl-es"><a href="#">ES</a></li>
    //     </ul>
    // </div>

    $languages = icl_get_languages($params);
    $current_lang_code = ICL_LANGUAGE_NAME;

    if(!empty($languages)){
        ?>
            <div class="b-languages">
                <div class="b-languages__button"><?php echo ICL_LANGUAGE_CODE; ?>
                    <div class="b-languages__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" 
                            viewBox="0 0 185.344 185.344" style="enable-background:new 0 0 185.344 185.344;" xml:space="preserve">
                            <g>
                                <path class="langs-fill" d="M92.672,144.373c-2.752,0-5.493-1.044-7.593-3.138L3.145,59.301c-4.194-4.199-4.194-10.992,0-15.18
                                    c4.194-4.199,10.987-4.199,15.18,0l74.347,74.341l74.347-74.341c4.194-4.199,10.987-4.199,15.18,0
                                    c4.194,4.194,4.194,10.981,0,15.18l-81.939,81.934C98.166,143.329,95.419,144.373,92.672,144.373z"/>
                            </g>
                        </svg>                    
                    </div>
                </div>
                <ul class="b-languages__list">
                    <?php
                        foreach($languages as $l){
                            if($l['active']!=="1"){
                                ?>
                                    <li class="b-languages__lang icl-<?php echo $l['code'];?>">
                                        <?php
                                            echo '<a class="js-link-refresh" href="' . $l['url'] . '">'. $l['code'] . '</a>';
                                        ?>
                                    </li>
                                <?php
                            }
                        }
                    ?>
                </ul>
            </div>
        <?php
    }

}

    
function av_get_id($_id = false){

    if(!!$_id){
        $_id = defined( 'ICL_LANGUAGE_CODE' ) ? icl_object_id($_id, 'page', false ) : $_id;
    }

    return $_id;

}

// ADD CUSTOM IMAGE SIZE
add_action( 'after_setup_theme', 'av_add_image_size' );
function av_add_image_size() {
    add_image_size( 'custom-medium',        300,    9999 );
    add_image_size( 'custom-tablet',        600,    9999 );
    add_image_size( 'custom-large',         1200,   9999 );
    add_image_size( 'custom-large-crop',    1200,   1200, true );
    add_image_size( 'custom-desktop',       1600,   9999 );
    add_image_size( 'custom-full',          2560,   9999 );
}

add_filter( 'image_size_names_choose', 'av_custom_image_size_names' );
function av_custom_image_size_names( $sizes ) {
    return array_merge( $sizes, array(
        'custom-medium'         => __( 'Custom medium', 'avali' ),
        'custom-tablet'         => __( 'Custom tablet', 'avali' ),
        'custom-large'          => __( 'Custom large', 'avali' ),
        'custom-large-crop'     => __( 'Custom large crop', 'avali' ),
        'custom-desktop'        => __( 'Custom desktop', 'avali' ),
        'custom-full'           => __( 'Custom full', 'avali' ),
    ) );
}


function av_hide_editor() {
    
    global $pagenow;
    if( !( 'post.php' == $pagenow ) ) return;

    global $post;

    $template_file = basename( get_page_template() );

    switch ($template_file) {

        // case 'template-press.php':
        
        //     remove_post_type_support('page', 'editor');

        // break;
        
        default:
            # code...
            break;
    }

}
// add_action( 'admin_head', 'av_hide_editor' );

function av_block_users_to_admin() {

    global $pagenow;

    // admin-post.php / admin-ajax.php son usados por formularios del frontend,
    // no son páginas del panel de administración: no deben bloquearse.
    if ( in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) return;

    $roles = wp_get_current_user()->roles;

    if ( is_admin() && in_array('editor', $roles ) && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

        wp_redirect( home_url() );
        exit;

    }

}
add_action( 'init', 'av_block_users_to_admin' );

/**
 * *****************************************************************************
 * Login setup
 * *****************************************************************************
 */

// DISPLAY CORRECTLY TAXS IN BACKEND
function av_checked_not_ontop( $args, $post_id ) {

	// IF NEED SPECIFICATION
    // if ( 'cpt' == get_post_type( $post_id ) && $args['taxonomy'] == 'custom_tax' ) $args['checked_ontop'] = false;

    // DEFAULT
	$args['checked_ontop'] = false;

    return $args;

}
add_filter( 'wp_terms_checklist_args', 'av_checked_not_ontop', 1, 2 );


// DEBUG PHP - DATA TO BROWSER CONSOLE
function debug_to_console($data) {
    if(is_array($data) || is_object($data))
	{
		echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
	} else {
		echo("<script>console.log('PHP: ".$data."');</script>");
	}
}

// DEBUG IN HTML
function dump($var){

	echo "<!-- <pre>" . var_dump($var) . "</pre>  -->";
	
}

function av_custom_pagination_base() {
	global $wp_rewrite;

  	// Translate
	$wp_rewrite->pagination_base = __('pagina', 'avali');
	$wp_rewrite->flush_rules();

}
// add_action( 'init', 'av_custom_pagination_base', 1 );

function av_breaklines($content){
    
    $content = apply_filters( 'the_content', $content );
    $content = str_replace( ']]>', ']]&gt;', $content );

    return $content;

}

// CHANGE MAIL FROM IN SEND WP_MAIL
function av_mail_from( $email ){
    return $email;
}
add_filter( 'wp_mail_from', 'av_mail_from' );

// CHANGE NAME FROM IN SEND WP_MAIL
function av_mail_from_name( $name ){
    return $name;
}
add_filter( 'wp_mail_from_name', 'av_mail_from_name' );

/**
 * Print custom button used in ajax load more action
 * @return html          			Full button html
 */
function av_print_load_more_button( $args ){

    $query = $args['query'];
    $classes = $args['classes'];
    $text = $args['text'];

    if(!$text) $text = __('View more', 'avali');

	$next_page = 2;
    $query_vars = $query->query_vars;

	if( $query->max_num_pages >= $next_page ){
		?>
			<div class="b-button-ajax js-button-ajax-more-posts <?php echo $classes; ?>"
				data-paged='<?php echo $next_page; ?>'
				data-query='<?php echo json_encode( $query_vars, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>'
			>
				<span class="b-button-ajax__text"><?php echo $text; ?></span>
			</div>
		<?php
	}

}

// DEBUG ONLY FOR AUTHOR AV
function av_dump($var){

	$current_user = wp_get_current_user();

	if ( is_av_user() ) {

        // ONLY AUTHOR R
        var_dump($var);
        
	}

}

// DEBUG ONLY ACCES FOR AUTHOR AV
function av_only_access(){

	$current_user = wp_get_current_user();

	if ( is_av_user() ) {

		// IF USER LOGGED IS NOT AUTHOR
        wp_redirect(site_url());
        exit;
        
	}

}


// DISABLE NOTIFICATIONS FOR THEMES, WP CORE AND PLUGINS
function av_remove_core_updates(){

    global $wp_version;
    return(object) array('last_checked'=> time(), 'version_checked'=> $wp_version);

}
    
// GET IF CURRENT USER IS AV USER
function is_av_user(){
    $current_user = wp_get_current_user();
    if(!!$current_user){
        if(
            $current_user->data->user_login == AV_USER_ALEX
        ){
            return true;
        }
    }
    return false;
}

// ONLY DISABLE IF USER IS NOT AV
function av_disable_update_notifications(){

    if ( !is_av_user() ) {

        add_filter('pre_site_transient_update_core', 'av_remove_core_updates');
        add_filter('pre_site_transient_update_plugins', 'av_remove_core_updates');
        add_filter('pre_site_transient_update_themes', 'av_remove_core_updates');

    }

}
add_action('after_setup_theme', 'av_disable_update_notifications');

// DISABLE AUTOMATIC UPDATER WP
add_filter( 'automatic_updater_disabled', '__return_true' );
    

// CHECK IF STRING CONTAINS SUBSTRING
// RETURN FALSE / TRUE
function av_string_contains_substring($str, $substr){
    
    if (strpos($str, $substr) !== false) return true;
    return false;

}
    
// RETURN FIRST SRC IMAGE OF CONTENT
function av_get_first_src_image_of_content($content){

    preg_match( '@src="([^"]+)"@' , $content, $match );
    $src = array_pop($match);

    // example return: /path/image.jpg
    if($src) return $src;
    return "";

}

// RETURN IMAGES OF CONTENT
function av_get_url_images_of_content($content){

    preg_match_all('/<img(.*?)src=("|\'|)(.*?)("|\'| )(.*?)>/s', $content, $match);
    if($match) return $match[3];
    return false;

}

// SECURITY - BLOCK ACCESS TO URL AUTHOR PARAMETER
if (!is_admin()) {
	// default URL format
	if (preg_match('/author=([0-9]*)/i', $_SERVER['QUERY_STRING'])) die();
	add_filter('redirect_canonical', 'av_shape_space_check_enum', 10, 2); } function av_shape_space_check_enum($redirect, $request) {
	// permalink URL format
	if (preg_match('/\?author=([0-9]*)(\/*)/i', $request)) die();
	else return $redirect;
}




/**
 * *****************************************************************************
 * Optimice title page
 * *****************************************************************************
 */
function av_custom_wp_title() {

    // OLD
    // is_front_page() ? bloginfo('name') : wp_title(bloginfo('name').' — ', true, '');

    // DEFAULT
    if( empty( $title ) && is_home() ) {
        return get_bloginfo( 'title' ) . ' | ' . get_bloginfo( 'description' ) ;
    }
    if( is_front_page() ) {
        return get_bloginfo( 'title' ) . ' | ' . get_bloginfo( 'description' ) ;
    }

    if( is_category() ){
        $blog_id = defined( 'ICL_LANGUAGE_CODE' ) ? icl_object_id($page_id, 'page', false ) : $page_id;
        $blog = get_post($blog_id);
        return $blog->post_title . ' | ' . get_bloginfo( 'title' );
    }

    if(is_tax()){
        $queried_object = get_queried_object();
        return $queried_object->name . ' | ' . get_bloginfo( 'title' );
    }

    $title = get_the_title();
    if(!!$title) return $title . ' | ' . get_bloginfo( 'title' );

    return get_bloginfo( 'title' ) . ' | ' . get_bloginfo( 'description' ) ;
    
}
add_filter('pre_get_document_title', 'av_custom_wp_title');


class WPDocs_Walker_Nav_Menu extends Walker_Nav_Menu {
    
    /**
     * Starts the list before the elements are added.
     *
     * Adds classes to the unordered list sub-menus.
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     */
    function start_lvl( &$output, $depth = 0, $args = array() ) {

        // Depth-dependent classes.
        $indent = ( $depth > 0  ? str_repeat( "\t", $depth ) : '' ); // code indent
        $display_depth = ( $depth + 1); // because it counts the first submenu as 0
        $classes = array(
            'sub-menu',
            ( $display_depth % 2  ? 'menu-odd' : 'menu-even' ),
            ( $display_depth >=2 ? 'sub-sub-menu' : '' ),
            'menu-depth-' . $display_depth
        );
        $class_names = implode( ' ', $classes );

        // Build HTML for output.
        $output .= "\n" . $indent . '<ul class="' . $class_names . '">' . "\n";
        
    }

    /**
     * Start the element output.
     *
     * Adds main/sub-classes to the list items and links.
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item   Menu item data object.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     * @param int    $id     Current item ID.
     */
    function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

        global $wp_query;
        global $post;
        global $term;

        $args_obj = (object) $args;

        $indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent

        // Depth-dependent classes.
        $depth_classes = array(
            ( $depth == 0 ? 'main-menu-item' : 'sub-menu-item' ),
            ( $depth >=2 ? 'sub-sub-menu-item' : '' ),
            ( $depth % 2 ? 'menu-item-odd' : 'menu-item-even' ),
            'menu-item-depth-' . $depth
        );
        $depth_class_names = esc_attr( implode( ' ', $depth_classes ) );

        // Passed classes.
        $classes = empty( $item->classes ) ? array() : (array) $item->classes;

        // AV
        $needle_parent     	= "menu-item-has-children";
        $icon = '';

        // ADD DINAMICALLY CLASSES HAS CHILDREN IN ANCESTOR, IF EXIST MENU
        if($item->classes){

            if ( in_array($needle_parent, $item->classes) ){

            }

        }

        $img_src = '';
        $img_title = '';
        $img_alt = '';

        if($item->object == 'page' || $item->object == 'room'){
            $_id = $item->object_id;
            // FEATURED IMAGE
            $img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $_id ), 'custom-full');
            $img_src = $img_src[0];

            $current_image_1 = get_field( "page_image_menu__image_1", $_id );
            $current_image_2 = get_field( "page_image_menu__image_2", $_id );
        }

        $current_title = apply_filters( 'the_title', $item->title, $item->ID );

        $class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

        // var_dump($item);

        // Build HTML.
        $output .= $indent . '<li id="nav-menu-item-'. $item->ID . '" class="' . $depth_class_names . ' ' . $class_names . ' js-the-id-' . $item->object_id . '">';

        $wrapper_image = '';

        // Link attributes.
        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
        $attributes .= ' class="menu-link js-menu-dropdown-img-menu-target ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';
        // $attributes .= ' data-img-src="' . $img_src . '"';
        $attributes .= ' data-img-src-1="' . $current_image_1['url'] . '"';
        $attributes .= ' data-img-src-2="' . $current_image_2['url'] . '"';

        
        $args_obj->link_after = $icon;

        // Build HTML output and pass through the proper filter.
        $item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s%6$s</a>%7$s',
            $args_obj->before,
            $attributes,
            $wrapper_image,
            $args_obj->link_before,
            '<span>' . $current_title . '</span>',
            $args_obj->link_after,
            $args_obj->after
        );
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args_obj );

    }

} 

add_action('init', 'av_start_session', 1);
add_action('wp_logout', 'av_end_session');
add_action('wp_login', 'av_end_session');

function av_start_session() {

    if(!session_id()) {

        session_start( [
            'read_and_close' => true,
        ] );

        if (!isset($_SESSION['products'])) {
 
            $_SESSION['products'] = array();

        }

        // banners

        if (!isset($_SESSION['banners'])) {
 
            $query_args = array(
                'post_type' 		=> 'cpt_banner',
                'post_status' 		=> 'publish',
                'order' 			=> 'DESC',
                'orderby' 			=> 'date',
                'posts_per_page' 	=> '-1',
                'fields'            => 'ids'
            );

            // QUERY ROOMS
            $banners_array_ids = new WP_Query( $query_args );
            $banners_array_ids = $banners_array_ids->posts;
            $_SESSION['banners'] = $banners_array_ids;

        }

    }

}

function av_end_session() {
    session_destroy ();
}

// function to check if device is a mobile.
function is_mobile() {
	return (bool)preg_match('#\b(ip(hone|od|ad)|android|opera m(ob|in)i|windows (phone|ce)|blackberry|tablet'.
			'|s(ymbian|eries60|amsung)|p(laybook|alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]'.
			'|mobile|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', $_SERVER['HTTP_USER_AGENT'] );
}

// function to add classes in body of browser and OS
function browser_body_class($classes) {
	global $is_lynx, $is_gecko, $is_IE, $is_edge, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	if($is_lynx) $classes[]         = 'lynx';
	elseif($is_gecko) $classes[]    = 'gecko';
	elseif($is_opera) $classes[]    = 'opera';
	elseif($is_NS4) $classes[]      = 'ns4';
	elseif($is_safari) $classes[]   = 'safari';
	elseif($is_chrome) $classes[]   = 'chrome';
	elseif($is_IE) $classes[]       = 'ie';
	elseif($is_edge) $classes[]     = 'edge';
	else $classes[]                 = 'unknown';

	if($is_iphone) $classes[]       = 'iphone';


	if ( stristr( $_SERVER['HTTP_USER_AGENT'],"mac") ) {
		$classes[] = 'osx';
	} elseif ( stristr( $_SERVER['HTTP_USER_AGENT'],"linux") ) {
		$classes[] = 'linux';
	} elseif ( stristr( $_SERVER['HTTP_USER_AGENT'],"windows") ) {
		$classes[] = 'windows';
	}

	// IS RENDER WEBKIT
	if( preg_match('/webkit/', strtolower($_SERVER['HTTP_USER_AGENT']) ) ) { 
        $classes[] = 'webkit';
	}

	if(is_mobile()) $classes[] = 'is_mobile';

	return $classes;
}
add_filter('body_class','browser_body_class');

// Control wrong credentials login redirect
add_action( 'wp_login_failed', 'av_front_end_login_fail' );  // hook failed login

function av_front_end_login_fail( $username ) {
    $referrer = $_SERVER['HTTP_REFERER'];  // where did the post submission come from?
    // if there's a valid referrer, and it's not the default log-in screen
    if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
        wp_redirect( add_query_arg( 'login', 'failed', $referrer ) );  // let's append some information (login=failed) to the URL for the theme to use
        exit;
    }
}

// Our filter callback function
//BLOCK BACKEND
if (!function_exists ( 'theme_admin_setup' )) :
	function av_block_to_backend() {

        global $pagenow;

        // admin-post.php / admin-ajax.php son usados por formularios del frontend,
        // no son páginas del panel de administración: no deben bloquearse.
        if ( in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) return;

        // Restrict admin page
        $current_role = restrictly_get_current_user_role();
		if ( in_array( $current_role, array( 'subscriber', 'editor' ) ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			wp_redirect( home_url() );
			exit;
		} else {
			// Add capabilities
		}
	}
endif;
add_action( 'admin_init', 'av_block_to_backend', 1 );

function restrictly_get_current_user_role() {
    if( is_user_logged_in() ) {
        $user = wp_get_current_user();
        $role = ( array ) $user->roles;
        return $role[0];
    } else {
        return false;
    }
}

// changing the logo link from wordpress.org to your site
function av_login_url() {  return home_url(); }
add_filter( 'login_headerurl', 'av_login_url' );

// changing the alt text on the logo to show your site name
function av_login_title() { return get_option( 'blogname' ); }
add_filter( 'login_headertitle', 'av_login_title' );

// ── Custom login page styles ──────────────────────────────────────────────────
function av_login_enqueue_scripts() {
    wp_enqueue_style(
        'av-google-fonts',
        'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'av-login',
        get_template_directory_uri() . '/dist/app.css',
        [],
        filemtime( get_template_directory() . '/dist/app.css' )
    );
}
add_action( 'login_enqueue_scripts', 'av_login_enqueue_scripts' );

// Custom login logo: logo_icb.svg
function av_login_logo_style() {
    $logo_url = get_template_directory_uri() . '/assets/imgs/logo_icb.svg';
    echo '<style>
        body.login h1 a {
            background-image: url("' . esc_url( $logo_url ) . '") !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            background-position: center !important;
            width: 200px !important;
            height: 80px !important;
        }
    </style>';
}
add_action( 'login_head', 'av_login_logo_style' );

// Traducir textos del formulario de login al español
function av_login_translate( $translated, $text, $domain ) {
    if ( ! ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) ) {
        return $translated;
    }
    $map = [
        'Username or Email Address' => 'Usuario o correo electrónico',
        'Password'                  => 'Contraseña',
        'Remember Me'               => 'Recuérdame',
        'Log In'                    => 'Entrar',
        'Lost your password?'       => '¿Olvidaste tu contraseña?',
        'Send'                      => 'Enviar',
        'Enter your username or email address and we will send you a link to reset your password.'
            => 'Introduce tu usuario o correo y te enviaremos un enlace para restablecer tu contraseña.',
    ];
    return $map[ $text ] ?? $translated;
}
add_filter( 'gettext', 'av_login_translate', 20, 3 );


/**
 * Add the wp-editor back into WordPress after it was removed in 4.2.2.
 *
 * @see https://wordpress.org/support/topic/you-are-currently-editing-the-page-that-shows-your-latest-posts?replies=3#post-7130021
 * @param $post
 * @return void
 */
function fix_no_editor_on_posts_page($post) {

    if( $post->ID != get_option( 'page_for_posts' ) ) { return; }

    remove_action( 'edit_form_after_title', '_wp_posts_page_notice' );
    add_post_type_support( 'page', 'editor' );

}

// This is applied in a namespaced file - so amend this if you're not namespacing
add_action( 'edit_form_after_title', __NAMESPACE__ . '\\fix_no_editor_on_posts_page', 0 );



// disable for posts
add_filter('use_block_editor_for_post', '__return_false', 10);
// disable for post types
add_filter('use_block_editor_for_post_type', '__return_false', 10);


/**
* Parse out url query string into an associative array
*
* $qry can be any valid url or just the query string portion.
* Will return false if no valid querystring found
*
* @param $qry String
* @return Array
*/
function queryToArray($qry){
    $result = array();
    //string must contain at least one = and cannot be in first position
    if(strpos($qry,'=')) {
        if(strpos($qry,'?')!==false) {
            $q = parse_url($qry);
            $qry = $q['query'];
        }
    }else {
        return false;
    }
    foreach (explode('&', $qry) as $couple) {
        list ($key, $val) = explode('=', $couple);
        $result[$key] = $val;
    }
    return empty($result) ? false : $result;
}

function av_formatting_video_external($content, $params = false){

    // use preg_match to find content src
    preg_match('/src="(.+?)"/', $content, $matches);
    $src = $matches[1];

    if(!!$src){

        if(!$params){

            // add extra params to content src
            $params = array(
                'controls'      => 0,
                'hd'            => 1,
                'autohide'      => 1,
                'muted'         => 1,
                'autoplay'      => 1,
                'background'    => 1
            );

        }

        $new_src = add_query_arg($params, $src);

        $content = str_replace($src, $new_src, $content);

        // add extra attributes to content html
        $attributes = 'frameborder="0"';

        $content = str_replace('></iframe>', ' ' . $attributes . '></iframe>', $content);

    }

    return $content;

}

function av_o_media( $args ){

    $defaults  = array(
        'media' => null,
        'extra-classes' => '',
        'src-in-phone' => 'av-phone',
        'src-in-tablet' => 'av-tablet',
        'src-in-desktop' => 'av-desktop',
        'size-phone' => '100vw',
        'size-tablet' => '100vw',
        'size-desktop' => '100vw'
    );
    
    /**
     * Parse incoming $args into an array and merge it with $defaults
     */ 
    $args = wp_parse_args( $args, $defaults );

    if(!!$args['media']){

        $media = $args['media'];
        $extra_classes = $args['extra-classes'];

        if(!!$media){

            $src_in_phone = $args['src-in-phone'];
            $src_in_tablet = $args['src-in-tablet'];
            $src_in_desktop = $args['src-in-desktop'];

            ?>

                <img 
                    class="o-media <?php echo $extra_classes; ?>"
                    src="<?php echo $media['sizes']['av-tablet']; ?>"
                    srcset="<?php echo $media['sizes'][$src_in_phone]; ?> 480w,
                            <?php echo $media['sizes'][$src_in_tablet]; ?> 1024w,
                            <?php echo $media['sizes'][$src_in_desktop]; ?> 1400w"
                    sizes=" (max-width: 480px) <?php echo $size_phone; ?>, 
                            (max-width: 1024px) <?php echo $size_tablet; ?>, 
                            <?php echo $size_desktop; ?>"
                    alt="<?php echo $media['alt']; ?>"
                    title="<?php echo $media['title']; ?>"
                >

            <?php

        }

    }
    
}

function av_get_featured_img($current_id){

    $attachment_id = get_post_thumbnail_id( $current_id );

    $medium = wp_get_attachment_image_src( $attachment_id, 'custom-medium');
    $tablet = wp_get_attachment_image_src( $attachment_id, 'custom-tablet');
    $large = wp_get_attachment_image_src( $attachment_id, 'custom-large');
    $large_crop = wp_get_attachment_image_src( $attachment_id, 'custom-large-crop');
    $desktop = wp_get_attachment_image_src( $attachment_id, 'custom-desktop');
    $full = wp_get_attachment_image_src( $attachment_id, 'custom-full');

    $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true);
    $title = get_the_title( $attachment_id );

    $image = array(
        'sizes' => array(
            'custom-medium'     => $medium[0],
            'custom-tablet'     => $tablet[0],
            'custom-large'      => $large[0],
            'custom-large-crop' => $large_crop[0],
            'custom-desktop'    => $desktop[0],
            'custom-full'       => $full[0]
        ),
        'alt' => $alt,
        'title' => $title,
        'ID' => $attachment_id
    );
    
    return $image;

}

// * GET OPTIONS
function av_component_get_options($component_global_settings){

    $options = array(
        'id-attr'           => av_component_get_option__id_attr($component_global_settings),
        'padding-classes'   => av_component_get_option__padding_classes($component_global_settings),
        'color-classes'     => av_component_get_option__color_classes($component_global_settings),
        'special-classes'   => av_component_get_option__special_classes($component_global_settings),
    );

    return $options;

}

// * GET OPTION - ID ATTR
function av_component_get_option__id_attr($component_global_settings){

    $id = $component_global_settings[SLUG_CGS . '__id'];
    $id_attr = '';

    if(!!$id) $id_attr = 'id="' . $id . '"';

    return $id_attr;

}

// * GET OPTION - PADDING CLASSES
function av_component_get_option__padding_classes($component_global_settings){

    $padding_classes = $component_global_settings[SLUG_CGS . '__padding-classes'];

    if(!$padding_classes) $padding_classes = 'o-padding-default';

    return $padding_classes;

}

// * GET OPTION - SPECIAL CLASSES
function av_component_get_option__special_classes($component_global_settings){

    $special_classes = $component_global_settings[SLUG_CGS . '__special-classes'];

    if(!$special_classes) $special_classes = '';

    return $special_classes;

}

// * GET OPTION - COLOR CLASSES
function av_component_get_option__color_classes($component_global_settings){

    $bg_color = $component_global_settings[SLUG_CGS . '__background-color'][SLUG_COLOR];
    $bg_color = (!!$bg_color) ? $bg_color : BG_COLOR_DEFAULT;

    $color = $component_global_settings[SLUG_CGS . '__color'][SLUG_COLOR];
    $color = (!!$color) ? $color : COLOR_DEFAULT;

    $color_classes =    'o-bg-color-' . $bg_color .
                        ' | o-color-' . $color;

    return $color_classes;

}

// * GET OPTION - IS ACTIVE
function av_component_get_option__is_active($component_global_settings){

    return $component_global_settings[SLUG_CGS . '__is-active']=='yes';

}

// * GET OPTION - IS ACTIVE
function av_component_get_option__hide_mobile($component_global_settings){

    if(!isPhone()){

        return false;

    } else {

        return $component_global_settings[SLUG_CGS . '__hide-mobile']=='disabled';

    }

}

// TODO
function av_component_global_settings_styles($component_global_settings){

    $style = "";

    return $style;

}


// * GET GLOBAL SETTINGS DEFAULT
function av_component_get_component_global_settings_default($value = 1){

    // PADDINGS
    $component_global_settings_default = array(
        SLUG_CGS . '__desktop-padding-top'     => "128",
        SLUG_CGS . '__desktop-padding-bottom'  => "128",
        SLUG_CGS . '__tablet-padding-top'      => "64",
        SLUG_CGS . '__tablet-padding-bottom'   => "64",
        SLUG_CGS . '__phone-padding-top'       => "48",
        SLUG_CGS . '__phone-padding-bottom'    => "48"
    );

    if($value==0){

        $component_global_settings_default = array(
            SLUG_CGS . '__desktop-padding-top'     => "0",
            SLUG_CGS . '__desktop-padding-bottom'  => "0",
            SLUG_CGS . '__tablet-padding-top'      => "0",
            SLUG_CGS . '__tablet-padding-bottom'   => "0",
            SLUG_CGS . '__phone-padding-top'       => "0",
            SLUG_CGS . '__phone-padding-bottom'    => "0"
        );

    }

    // COLORS
    $component_global_settings_default[SLUG_CGS . '__background-color'][SLUG_COLOR] = BG_COLOR_DEFAULT;
    $component_global_settings_default[SLUG_CGS . '__color'][SLUG_COLOR] = COLOR_DEFAULT;

    // IS ACTIVE
    $component_global_settings_default[SLUG_CGS . '__is-active'] = true;

    return $component_global_settings_default;

}


function av_get_tag_for_title($tag = 'h2'){

    if(!$GLOBALS['tag-h1-printed']){
        $tag = 'h1';
        $GLOBALS['tag-h1-printed'] = true;
    }

    return $tag;

}


function disable_wp_emojicons() {
    // remove all actions related to emojis
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
}
add_action( 'init', 'disable_wp_emojicons' );




/*
    Check string image title has readable format
*/

function av_get_img_title($str){

    $title = '';

    if( strpos($str, " ") > 0 ) $title=' title="' . $str . '"';

    return $title;

}

function my_acf_admin_head() {
    ?>
        <style type="text/css">

            .acf-postbox .acf-fc-layout-handle{
                background-color: #0085ba!important;
                color: #FFFFFF!important;
            }
            .acf-postbox .acf-accordion-title{
                background-color: #c2e2f1!important;
            }

            .acf-row .acf-field-message {
                background-color: #f4f4f4;
            }

            .acf-fields.-left>.acf-field{
                background-color: #FFFFFF;
            }

            .acf-accordion .acf-accordion-title:hover {
                background: #9acee6;
            }

            .av-acf-labels__wrapper-item {

                position: relative;
                display: inline-block;

            }

            .av-acf-labels__item{

                display: flex;

            }

            .av-acf-labels__thumbnail {

                padding-left: 5px;

            }

            .av-acf-labels__thumbnail + .av-acf-labels__title{

                padding-left: 0px;

            }

        </style>
    <?php
}

add_action('acf/input/admin_head', 'my_acf_admin_head');


// @ http://digwp.com/2012/06/add-google-analytics-wordpress/

function custom_pagination_base() {
	global $wp_rewrite;

      // Translate
    $wp_rewrite->pagination_base = __("pagina", 'avali');
    $wp_rewrite->flush_rules();

}
// add_action( 'init', 'custom_pagination_base', 999, 1);

add_filter('wpml_custom_field_original_data', '__return_empty_array');

function av_print_button($url, $text, $type = 'box', $target = '_self', $classes = '', $attrs = ''){

    ?>
        <a  class="o-button | o-button--<?php echo $type; ?> <?php echo $classes; ?>" 
            href="<?php echo $url; ?>"
            target="<?php echo $target; ?>"
            <?php echo $attrs; ?>
        >
            <span class="o-button__text"><?php echo $text; ?></span>
        </a>
    <?php

}


function av_get_attachment_image_no_srcset($attachment_id, $size = 'thumbnail', $icon = false, $attr = '') {
    // add a filter to return null for srcset
    add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
    // get the srcset-less img html
    $html = wp_get_attachment_image($attachment_id, $size, $icon, $attr);
    // remove the above filter
    remove_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
    return $html;
}

// ACF Populate components content label with subfield
add_filter('acf/fields/flexible_content/layout_title/name=components', 'av_flexible_content_layout_title', 10, 4);
function av_flexible_content_layout_title( $title, $field, $layout, $i ) {

    $title_label = '<div class="av-acf-labels__wrapper-item"><div class="av-acf-labels__item">'.$title;
    $component_slug = get_row_layout();

    // load text sub field
    if( $text = get_sub_field($component_slug . '__title') ) {

        $text = wp_trim_words($text, 7, '...');
        $title_label .= '&nbsp;:&nbsp;<div class="av-acf-labels__title">' . esc_html($text) . '</div>';

    } else if( $description = get_sub_field($component_slug . '__description') ) {

        $description = wp_trim_words($description, 7, '...');
        $title_label .= '&nbsp;:&nbsp;<div class="av-acf-labels__title">' . esc_html($description) . '</div>';

    } else if( $slider = get_sub_field($component_slug . '__slider') ) {
        $slider_text = '';

        if(array_key_exists('title',$slider[0])){

            $slider_text = $slider[0]['title'];

        } else if(array_key_exists('overline',$slider[0])){

            $slider_text = $slider[0]['overline'];

        } else if(array_key_exists('text',$slider[0])){

            $slider_text = $slider[0]['text'];

        }

        if($slider_text!=''){

            $description = wp_trim_words($slider_text, 7, '...');
            $title_label .= '&nbsp;:&nbsp;<div class="av-acf-labels__title">' . esc_html($slider_text) . '</div>';

        }
            
    } else if( $image = get_sub_field($component_slug . '__image') ) {

        $title_label .= ' <div class="av-acf-labels__thumbnail"><img src="' . esc_url($image['sizes']['thumbnail']) . '" height="20px" /></div>';     

    }

    $title_label = $title_label . '</div></div>';

    return $title_label;
}

//Remove Gutenberg Block Library CSS from loading on the frontend
function smartwp_remove_wp_block_library_css(){
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
} 
add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );

// Determine language and redirect accordingly
function av_set_language_once(){

    if(!is_admin()) {

        if(session_id() == '' || !isset($_SESSION)) session_start();

        if (!isset($_SESSION['start_lang'])) {

            global $sitepress;

            // Get current browser language
            $browser_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            // Get current system languages
            $system_languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );

            // Init array for current system languages codes
            $supportedLangs = array();

            if ( !empty( $system_languages ) ) {

                // Push system language codes
                foreach( $system_languages as $lang ) {
                    array_push($supportedLangs,$lang['language_code']);
                }
                
                if(in_array($browser_language, $supportedLangs))
                {
                    // Set the page locale to the first supported language found
                    $sitepress->switch_lang($browser_language, true);
                    $_SESSION['start_lang'] = true;
                    $go_to_ID = apply_filters( 'wpml_object_id', get_the_ID(), 'post' );
                    if(wp_redirect(get_permalink($go_to_ID))) exit;
                }

            }

            // FLAG session variable to true
            $_SESSION['start_lang'] = true;
        
        }

    }

}

// Captura tanto para usuarios logueados como no logueados
add_action('admin_post_nopriv_crear_sat_cpt', 'crear_sat_cpt');
add_action('admin_post_crear_sat_cpt', 'crear_sat_cpt');

// Un SAT creado desde el botón "Garantía" que se finaliza se guarda como "garantia" en vez de "finalizado".
function av_resolve_sat_status( $estado, $is_warranty ) {
    if ( $estado === 'finalizado' && $is_warranty ) {
        return 'garantia';
    }
    return $estado;
}

// Fija fecha de reparación (estado "reparado") y fecha/usuario de entrega (estado "finalizado" o "garantia"),
// una sola vez, sin sobrescribir si ya estaban puestas.
function av_set_sat_status_dates( $sat_id, $estado ) {

    if ( $estado === 'reparado' ) {
        $existing_repair_date = get_post_meta( $sat_id, 'cpt-sat__repair-date', true );
        if ( empty( $existing_repair_date ) ) {
            update_post_meta( $sat_id, 'cpt-sat__repair-date', wp_date('d/m/Y H:i') );
        }
    }

    // "no-reparado" cierra el SAT igual que "finalizado"/"garantia": también deja
    // el equipo entregado al cliente, así que necesita la misma fecha de entrega
    // (entre otras cosas, para poder calcular la caducidad del enlace de seguimiento).
    if ( in_array( $estado, [ 'finalizado', 'no-reparado', 'garantia' ], true ) ) {
        $existing_delivery_date = get_post_meta( $sat_id, 'cpt-sat__delivery-date', true );
        if ( empty( $existing_delivery_date ) ) {
            update_post_meta( $sat_id, 'cpt-sat__delivery-date', wp_date('d/m/Y H:i') );
            update_post_meta( $sat_id, 'cpt-sat__finalized-by', wp_get_current_user()->display_name );
        }
    }

}

// Token público para que el cliente pueda ver el estado de su SAT sin iniciar sesión.
function av_get_or_create_sat_tracking_token( $sat_id ) {
    $token = get_post_meta( $sat_id, 'cpt-sat__tracking-token', true );
    if ( empty( $token ) ) {
        $token = bin2hex( random_bytes( 32 ) );
        update_post_meta( $sat_id, 'cpt-sat__tracking-token', $token );
    }
    return $token;
}

function av_get_sat_tracking_url( $sat_id ) {
    $token = av_get_or_create_sat_tracking_token( $sat_id );

    // Busca la página por la plantilla asignada (no por slug fijo), para que
    // funcione sin importar qué URL le hayan puesto a la página en cada entorno.
    $pages = get_pages([
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'templates/template-seguimiento-sat.php',
        'number'     => 1,
    ]);

    if ( empty( $pages ) ) return '';

    return add_query_arg( 'token', $token, get_permalink( $pages[0]->ID ) );
}

// El enlace de seguimiento que ve el cliente deja de funcionar pasados N días
// desde que el SAT se dio por finalizado (finalizado/no reparado/garantía).
// Mientras el SAT sigue en curso el enlace no caduca.
define( 'AV_SAT_TRACKING_EXPIRY_DAYS', 5 );

function av_is_sat_tracking_expired( $sat_id ) {
    $estado = get_post_meta( $sat_id, 'cpt-sat__status', true );
    if ( ! in_array( $estado, [ 'finalizado', 'no-reparado', 'garantia' ], true ) ) {
        return false;
    }

    $delivery_date = get_post_meta( $sat_id, 'cpt-sat__delivery-date', true );
    if ( empty( $delivery_date ) ) {
        return false;
    }

    $expires_at = DateTime::createFromFormat( 'd/m/Y H:i', $delivery_date, wp_timezone() );
    if ( ! $expires_at ) {
        return false;
    }
    $expires_at->modify( '+' . AV_SAT_TRACKING_EXPIRY_DAYS . ' days' );

    return $expires_at < new DateTime( 'now', wp_timezone() );
}

// Captura de firma del cliente: desactivada de momento. Poner a true para volver
// a mostrar el pad de firma en el formulario del SAT (las firmas ya guardadas se
// siguen viendo en cualquier caso).
function av_sat_signature_enabled() {
    return false;
}

// Periodos de garantía disponibles. Mismos valores que el campo ACF
// "cpt-sat__warranty-period": sirve para pintar el select y para validar lo que llega.
function av_sat_warranty_period_choices() {
    return [
        '2-meses' => '2 meses',
        '4-meses' => '4 meses',
        '6-meses' => '6 meses',
        '1-ano'   => '1 año',
    ];
}

function av_sat_warranty_period_label( $period ) {
    $choices = av_sat_warranty_period_choices();
    return $choices[ $period ] ?? '';
}

function av_sat_status_label( $status ) {
    $labels = [
        'diagnosticar'   => 'Por diagnosticar',
        'cliente-espera' => 'En espera cliente',
        'pieza'          => 'Esperando pieza',
        'otro-sat'       => 'Enviado otro SAT',
        'reparar'        => 'Por reparar',
        'reparado'       => 'Reparado',
        'no-reparado'    => 'No reparado',
        'garantia'       => 'En garantía',
        'finalizado'     => 'Finalizado',
    ];
    return $labels[ $status ] ?? ucfirst( str_replace( '-', ' ', $status ) );
}

function av_sat_add_history( $sat_id, $label, $type = 'status', $status_slug = '', $text = '' ) {
    $raw     = get_post_meta( $sat_id, 'cpt-sat__history', true );
    $history = ( $raw && is_string( $raw ) ) ? json_decode( $raw, true ) : [];
    if ( ! is_array( $history ) ) $history = [];

    $user  = wp_get_current_user();
    $entry = [
        'ts'    => date_i18n( 'd/m/Y H:i' ),
        'label' => $label,
        'type'  => $type,
        'user'  => $user->display_name ?: $user->user_login,
    ];
    if ( $status_slug ) $entry['status'] = $status_slug;
    if ( $text !== '' )  $entry['text']   = $text;

    $history[] = $entry;
    update_post_meta( $sat_id, 'cpt-sat__history', wp_json_encode( $history, JSON_UNESCAPED_UNICODE ) );
}

// Fotos que se pueden subir desde el formulario del SAT.
//   input del formulario => meta donde se guarda + nº máximo de fotos
function av_sat_photo_fields() {
    return [
        'physical-condition-photo' => [ 'meta' => 'cpt-sat__physical-condition-photo', 'max' => 5 ],
        'warranty-seal-photo'      => [ 'meta' => 'cpt-sat__warranty-seal-photo',      'max' => 1 ],
    ];
}

// Los campos de varias fotos se guardan como JSON; los de una sola, como URL
// suelta (formato antiguo). Esta función devuelve siempre un array de URLs.
function av_sat_photo_urls( $value ) {

    if ( empty( $value ) ) return [];

    if ( is_array( $value ) ) return array_values( array_filter( $value ) );

    $decoded = json_decode( $value, true );
    if ( is_array( $decoded ) ) return array_values( array_filter( $decoded ) );

    return [ $value ];
}

// Sube las fotos que lleguen en un campo y devuelve sus URLs. Acepta tanto un
// input simple como uno múltiple (name="campo[]").
function av_sat_upload_photos( $sat_id, $input_name, $limit ) {

    if ( empty( $_FILES[ $input_name ]['name'] ) ) return [];
    if ( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) return [];
    if ( $limit < 1 ) return [];

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Normaliza el input simple al mismo formato que el múltiple
    $files = $_FILES[ $input_name ];
    if ( ! is_array( $files['name'] ) ) {
        foreach ( [ 'name', 'type', 'tmp_name', 'error', 'size' ] as $key ) {
            $files[ $key ] = [ $files[ $key ] ];
        }
    }

    $urls = [];

    foreach ( $files['name'] as $i => $name ) {

        if ( count( $urls ) >= $limit ) break;
        if ( empty( $name ) || ! empty( $files['error'][ $i ] ) ) continue;

        // Solo imágenes: se comprueba el contenido real del archivo, no la extensión.
        $check = wp_check_filetype_and_ext( $files['tmp_name'][ $i ], $name );
        if ( empty( $check['type'] ) || strpos( $check['type'], 'image/' ) !== 0 ) continue;

        // media_handle_upload espera una entrada de $_FILES por archivo
        $_FILES['av_sat_photo_tmp'] = [
            'name'     => $name,
            'type'     => $files['type'][ $i ],
            'tmp_name' => $files['tmp_name'][ $i ],
            'error'    => $files['error'][ $i ],
            'size'     => $files['size'][ $i ],
        ];

        $attach_id = media_handle_upload( 'av_sat_photo_tmp', $sat_id );
        unset( $_FILES['av_sat_photo_tmp'] );

        if ( is_wp_error( $attach_id ) ) {
            error_log( '[SAT foto] Error al subir ' . $input_name . ': ' . $attach_id->get_error_message() );
            continue;
        }

        $urls[] = wp_get_attachment_url( $attach_id );
    }

    return $urls;
}

// Fotos marcadas para eliminar en el formulario (campo oculto "<campo>-remove")
function av_sat_photos_to_remove( $input_name ) {

    $raw = $_POST[ $input_name . '-remove' ] ?? '';
    if ( empty( $raw ) ) return [];

    $urls = json_decode( wp_unslash( $raw ), true );
    if ( ! is_array( $urls ) ) return [];

    return array_values( array_filter( array_map( 'esc_url_raw', $urls ) ) );
}

// Borra el archivo de la biblioteca de medios, pero solo si ese adjunto es de
// este SAT (así no se toca una imagen que se esté usando en otro sitio).
function av_sat_delete_photo_attachment( $sat_id, $url ) {

    $attach_id = attachment_url_to_postid( $url );
    if ( ! $attach_id ) return;

    $attachment = get_post( $attach_id );
    if ( $attachment && intval( $attachment->post_parent ) === intval( $sat_id ) ) {
        wp_delete_attachment( $attach_id, true );
    }
}

// Aplica los cambios de fotos del envío: primero quita las marcadas para
// eliminar y después añade las nuevas (hasta el máximo del campo). Si no hay
// ni bajas ni altas, no se toca nada.
function av_sat_save_uploaded_photos( $sat_id ) {

    foreach ( av_sat_photo_fields() as $input_name => $config ) {

        $meta_key = $config['meta'];
        $max      = $config['max'];

        $current = av_sat_photo_urls( get_post_meta( $sat_id, $meta_key, true ) );
        $removed = false;

        // 1. Bajas
        $to_remove = av_sat_photos_to_remove( $input_name );
        if ( ! empty( $to_remove ) ) {
            $keep = array_values( array_diff( $current, $to_remove ) );
            if ( count( $keep ) !== count( $current ) ) {
                foreach ( array_diff( $current, $keep ) as $url ) {
                    av_sat_delete_photo_attachment( $sat_id, $url );
                }
                $current = $keep;
                $removed = true;
            }
        }

        // 2. Altas: en los campos de varias fotos se suman hasta el máximo;
        //    en los de una sola, la nueva sustituye a la anterior.
        $free   = $max > 1 ? max( 0, $max - count( $current ) ) : 1;
        $nuevas = av_sat_upload_photos( $sat_id, $input_name, $free );

        if ( empty( $nuevas ) && ! $removed ) continue;

        if ( $max > 1 ) {
            $todas = array_slice( array_merge( $current, $nuevas ), 0, $max );
            update_post_meta( $sat_id, $meta_key, wp_json_encode( array_values( $todas ), JSON_UNESCAPED_UNICODE ) );
        } else {
            update_post_meta( $sat_id, $meta_key, ! empty( $nuevas ) ? $nuevas[0] : '' );
        }
    }
}

function crear_sat_cpt() {

    // Sanitizar datos
    $attended   = sanitize_text_field($_POST['attended'] ?? '');
    $client_id = sanitize_text_field($_POST['client-id'] ?? '');
    $type_equipment = sanitize_text_field($_POST['type-equipment'] ?? ''); 
    $name_other = sanitize_text_field($_POST['name-other'] ?? ''); 
    $model = sanitize_text_field($_POST['model'] ?? ''); 
    $serial = sanitize_text_field($_POST['serial'] ?? ''); 
    $password = sanitize_text_field($_POST['password'] ?? ''); 
    $sim = sanitize_text_field($_POST['sim'] ?? ''); 
    $accesories = array_map('sanitize_text_field', $_POST['accesories'] ?? ['']); 
    $other_accesories = sanitize_text_field($_POST['other-accesories'] ?? ''); 
    $status = sanitize_text_field($_POST['physical-condition'] ?? '');
    $incident = sanitize_text_field($_POST['incident'] ?? '');
    $diagnostic = sanitize_text_field($_POST['diagnostic'] ?? '');
    // El campo "Garantía" solo se pinta en SATs de garantía: si no llega en el POST
    // no significa que esté vacío, sino que el formulario no lo muestra.
    $warranty_note_posted = isset($_POST['warranty-note']) ? sanitize_text_field($_POST['warranty-note']) : null;
    $warranty_note = $warranty_note_posted ?? '';

    // Garantía que se da al cliente por la reparación. Es opcional: vacío = sin garantía.
    $warranty_period_posted = isset($_POST['warranty-period']) ? sanitize_text_field($_POST['warranty-period']) : null;
    if ( $warranty_period_posted !== null && ! array_key_exists( $warranty_period_posted, av_sat_warranty_period_choices() ) ) {
        $warranty_period_posted = '';
    }
    $warranty_period = $warranty_period_posted ?? '';

    // Precinto de garantía: dónde se ha pegado la pegatina. La foto se sube aparte.
    $warranty_seal = sanitize_text_field($_POST['warranty-seal'] ?? '');
    $repair_raw  = wp_unslash( $_POST['repair'] ?? '' );
    $repair_json = json_decode( $repair_raw, true );
    if ( is_array( $repair_json ) ) {
        foreach ( $repair_json as &$_ri ) {
            $_ri['text']  = sanitize_text_field( $_ri['text']  ?? '' );
            $_ri['price'] = sanitize_text_field( $_ri['price'] ?? '' );
        }
        unset( $_ri );
        $repair = wp_json_encode( $repair_json, JSON_UNESCAPED_UNICODE );
    } else {
        $repair = sanitize_textarea_field( $repair_raw );
    }
    $ordered_raw  = wp_unslash( $_POST['ordered-parts'] ?? '' );
    $ordered_json = json_decode( $ordered_raw, true );
    if ( is_array( $ordered_json ) ) {
        foreach ( $ordered_json as &$_oi ) {
            $_oi['text']  = sanitize_text_field( $_oi['text']  ?? '' );
            $_oi['price'] = sanitize_text_field( $_oi['price'] ?? '' );
        }
        unset( $_oi );
        $ordered_parts = wp_json_encode( $ordered_json, JSON_UNESCAPED_UNICODE );
    } else {
        $ordered_parts = sanitize_textarea_field( $ordered_raw );
    }
    $price = sanitize_text_field($_POST['price'] ?? '');
    $repair_date = sanitize_text_field($_POST['repair-date'] ?? '');
    $budget = sanitize_text_field($_POST['budget'] ?? '');
    $estado = sanitize_text_field($_POST['estado'] ?? '');
    $priority = sanitize_text_field($_POST['prioridad'] ?? '');
    $price_description = sanitize_text_field($_POST['price-description'] ?? '');
    $anticipo = sanitize_text_field($_POST['anticipo'] ?? '');
    $anticipo_payment = sanitize_text_field($_POST['anticipo-payment'] ?? '');
    // El anticipo solo tiene sentido si hay una cantidad indicada; sin cantidad
    // no se guarda forma de pago suelta.
    if ( $anticipo === '' || floatval( str_replace( ',', '.', $anticipo ) ) <= 0 ) {
        $anticipo = '';
        $anticipo_payment = '';
    }
    $other_equipment = sanitize_text_field($_POST['other-equipment'] ?? '');
    $is_warranty_flag = ! empty( $_POST['is-warranty'] );

    // Un SAT de garantía que se finaliza se guarda como estado "garantia".
    $estado_to_save = av_resolve_sat_status( $estado, $is_warranty_flag );

    $posts = get_posts([
        'post_type'      => 'cpt-sats',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);

    $ultimo_id = $posts ? $posts[0]->ID : 0;
    $sat_id = get_field('cpt-sat__sat-id', $ultimo_id);
    
    if (empty($_POST['id'])) {
        // Crear nuevo post del tipo personalizado
        $nuevo_id = wp_insert_post([
            'post_type'   => 'cpt-sats',   // <-- Aquí el nombre de tu CPT
            'post_title'  => get_field('cpt-client__name', $client_id) . ' - ' . ($sat_id + 1),
            'post_status' => 'publish',
            'meta_input'  => [
                'cpt-sat__attended' => $attended,
                'cpt-sat__client-id' => $client_id,
                'cpt-sat__sat-id' => $sat_id + 1,
                'cpt-sat__entry-date' => wp_date('d/m/Y H:i'),
                'cpt-sat__type-equipment' => $type_equipment,
                'cpt-sat__name-other' => $name_other,
                'cpt-sat__model' => $model,
                'cpt-sat__model-imei' => $serial,
                'cpt-sat__password' => $password,
                'cpt-sat__pin-sim' => $sim,
                'cpt-sat__accesories' => $accesories,
                'cpt-sat__other-accesories' => $other_accesories,
                'cpt-sat__physical-condition' => $status,
                'cpt-sat__incident' => $incident,
                'cpt-sat__diagnostic' => $diagnostic,
                'cpt-sat__warranty-note' => $warranty_note,
                'cpt-sat__warranty-period' => $warranty_period,
                'cpt-sat__warranty-seal' => $warranty_seal,
                'cpt-sat__budget' => $budget,
                'cpt-sat__repair' => $repair,
                'cpt-sat__ordered-parts' => $ordered_parts,
                'cpt-sat__price' => $price,
                'cpt-sat__repair-date' => $repair_date,
                'cpt-sat__status' => $estado_to_save,
                'cpt-sat__priority' => $priority,
                'cpt-sat__price-description' => $price_description,
                'cpt-sat__anticipo' => $anticipo,
                'cpt-sat__anticipo-payment' => $anticipo_payment,
                'cpt-sat__other-equipment' => $other_equipment,
                'cpt-sat__is-warranty' => $is_warranty_flag ? '1' : '',
                'cpt-sat__tracking-token' => bin2hex( random_bytes( 32 ) ),

            ],
        ]);

        // Las fotos necesitan el post ya creado para poder adjuntarse a él.
        av_sat_save_uploaded_photos( $nuevo_id );

        av_set_sat_status_dates( $nuevo_id, $estado_to_save );
        av_sat_add_history( $nuevo_id, 'Equipo recibido en taller', 'entrada' );
        if ( $estado_to_save ) {
            av_sat_add_history( $nuevo_id, av_sat_status_label( $estado_to_save ), 'status', $estado_to_save );
        }
        if ( ! empty( $diagnostic ) ) {
            av_sat_add_history( $nuevo_id, 'Diagnóstico realizado', 'diagnostic', '', $diagnostic );
        }
    }else{
        $sat_id = $_POST['id'];

        // "Atendido por" solo lo puede cambiar un administrador; si el campo llega
        // deshabilitado (no está en el POST) se conserva el valor ya guardado.
        if ( empty( $_POST['attended'] ) ) {
            $attended = get_post_meta( $sat_id, 'cpt-sat__attended', true );
        }

        // La nota de garantía solo se pinta en SATs de garantía: si no llega en el POST
        // no es que esté vacía, es que el formulario no la muestra. Igual con el periodo,
        // por si algún formulario deja de pintarlo.
        if ( $warranty_note_posted === null ) {
            $warranty_note = get_post_meta( $sat_id, 'cpt-sat__warranty-note', true );
        }
        if ( $warranty_period_posted === null ) {
            $warranty_period = get_post_meta( $sat_id, 'cpt-sat__warranty-period', true );
        }

        // Un SAT ya finalizado, no reparado o en garantía solo puede modificarlo un administrador.
        $current_status     = get_post_meta( $sat_id, 'cpt-sat__status',     true );
        $current_diagnostic = get_post_meta( $sat_id, 'cpt-sat__diagnostic', true );
        $current_price      = get_post_meta( $sat_id, 'cpt-sat__price',      true );
        if ( in_array( $current_status, [ 'finalizado', 'no-reparado', 'garantia' ], true ) && ! current_user_can( 'manage_options' ) ) {
            wp_redirect( get_permalink( $sat_id ) );
            exit;
        }

        wp_update_post([
            'ID'          => $sat_id,
            'post_status' => 'publish',
            'meta_input'  => [
                'cpt-sat__attended' => $attended,
                'cpt-sat__type-equipment' => $type_equipment,
                'cpt-sat__name-other' => $name_other,
                'cpt-sat__model' => $model,
                'cpt-sat__model-imei' => $serial,
                'cpt-sat__password' => $password,
                'cpt-sat__pin-sim' => $sim,
                'cpt-sat__accesories' => $accesories,
                'cpt-sat__other-accesories' => $other_accesories,
                'cpt-sat__physical-condition' => $status,
                'cpt-sat__incident' => $incident,
                'cpt-sat__diagnostic' => $diagnostic,
                'cpt-sat__warranty-note' => $warranty_note,
                'cpt-sat__warranty-period' => $warranty_period,
                'cpt-sat__warranty-seal' => $warranty_seal,
                'cpt-sat__budget' => $budget,
                'cpt-sat__repair' => $repair,
                'cpt-sat__ordered-parts' => $ordered_parts,
                'cpt-sat__price' => $price,
                'cpt-sat__status' => $estado_to_save,
                'cpt-sat__priority' => $priority,
                'cpt-sat__price-description' => $price_description,
                'cpt-sat__anticipo' => $anticipo,
                'cpt-sat__anticipo-payment' => $anticipo_payment,
                'cpt-sat__other-equipment' => $other_equipment,
                'cpt-sat__is-warranty' => $is_warranty_flag ? '1' : '',

            ],
        ]);

        // Si no se sube foto nueva se conserva la que ya tenía el SAT.
        av_sat_save_uploaded_photos( $sat_id );

        av_set_sat_status_dates( $sat_id, $estado_to_save );

        // Normaliza texto para comparar: colapsa espacios/saltos de línea y strip HTML
        $av_norm = function( $s ) {
            return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $s ) ) );
        };

        // 1. Diagnóstico primero (si el texto cambió)
        if ( ! empty( $diagnostic ) && $av_norm( $diagnostic ) !== $av_norm( $current_diagnostic ) ) {
            $diag_label = empty( $current_diagnostic ) ? 'Diagnóstico realizado' : 'Diagnóstico actualizado';
            $diag_text  = $diagnostic;
            $norm_prev  = $av_norm( $current_diagnostic );
            $norm_new   = $av_norm( $diagnostic );
            if ( ! empty( $norm_prev ) && strpos( $norm_new, $norm_prev ) === 0 ) {
                $diag_text = ltrim( substr( $norm_new, strlen( $norm_prev ) ), " \t\n\r.," );
            }
            av_sat_add_history( $sat_id, $diag_label, 'diagnostic', '', $diag_text );
        }

        // 2. Estado + precio (si reparado: precio primero; si otro estado: estado primero)
        $av_norm_price = function( $p ) { return trim( str_replace( ',', '.', wp_strip_all_tags( $p ) ) ); };
        $price_changed = ! empty( $price ) && $av_norm_price( $price ) !== $av_norm_price( $current_price );
        $precio_fmt    = $price_changed ? number_format( floatval( str_replace( ',', '.', $price ) ), 2, ',', '.' ) . ' €' : '';

        if ( $estado_to_save && $current_status !== $estado_to_save ) {
            if ( $estado_to_save === 'reparado' ) {
                if ( $price_changed ) {
                    av_sat_add_history( $sat_id, 'SAT presupuestado', 'price', '', $precio_fmt );
                }
                $r_items = json_decode( $repair, true );
                if ( is_array( $r_items ) ) {
                    $lines = array();
                    foreach ( $r_items as $ri ) {
                        $line = '• ' . ( $ri['text'] ?? '' );
                        if ( ! empty( $ri['price'] ) ) {
                            $line .= ' — ' . number_format( floatval( str_replace( ',', '.', $ri['price'] ) ), 2, ',', '.' ) . ' €';
                        }
                        $lines[] = $line;
                    }
                    $status_text = implode( "\n", $lines );
                } else {
                    $status_text = ! empty( $repair ) ? $repair : '';
                }
                av_sat_add_history( $sat_id, av_sat_status_label( $estado_to_save ), 'status', $estado_to_save, $status_text );
            } else {
                av_sat_add_history( $sat_id, av_sat_status_label( $estado_to_save ), 'status', $estado_to_save );
                if ( $price_changed ) {
                    av_sat_add_history( $sat_id, 'SAT presupuestado', 'price', '', $precio_fmt );
                }
            }
        } elseif ( $price_changed ) {
            av_sat_add_history( $sat_id, 'SAT presupuestado', 'price', '', $precio_fmt );
        }
    }


    // Redirigir a la página del SAT con ?pdf=1 para que JS genere y guarde el PDF
    $post_id  = isset($nuevo_id) ? $nuevo_id : (int) ($_POST['id'] ?? 0);
    $sat_link = $post_id ? get_permalink( $post_id ) : '';
    if ( ! $sat_link ) $sat_link = home_url('/listado-sats/');
    error_log('[SAT PDF] redirect a: ' . $sat_link . ' post_id=' . $post_id);
    wp_redirect( add_query_arg( 'pdf', '1', $sat_link ) );
    exit;
}

// ── Quitar la garantía de un SAT ───────────────────────────────────────────
// Un SAT creado con el botón "Garantía" puede resultar, al revisarlo, no cubierto
// por garantía: este botón lo devuelve a SAT normal (facturable) y desbloquea los
// campos que estaban fijados por venir de una garantía.
add_action( 'admin_post_av_sat_remove_warranty', 'av_sat_remove_warranty' );

function av_sat_remove_warranty() {

    $sat_id = intval( $_GET['sat_id'] ?? 0 );

    if ( ! $sat_id || get_post_type( $sat_id ) !== 'cpt-sats' ) {
        wp_redirect( home_url('/listado-sats/') );
        exit;
    }

    check_admin_referer( 'av_sat_remove_warranty_' . $sat_id );

    $sat_link = get_permalink( $sat_id );

    // Un SAT ya finalizado, no reparado o en garantía solo puede modificarlo un administrador.
    $current_status = get_post_meta( $sat_id, 'cpt-sat__status', true );
    $status_locked  = in_array( $current_status, [ 'finalizado', 'no-reparado', 'garantia' ], true );

    if ( ! current_user_can( 'edit_posts' ) || ( $status_locked && ! current_user_can( 'manage_options' ) ) ) {
        wp_redirect( $sat_link );
        exit;
    }

    // Si ya no era de garantía no hay nada que hacer (doble clic, recarga…).
    if ( get_post_meta( $sat_id, 'cpt-sat__is-warranty', true ) !== '1' ) {
        wp_redirect( $sat_link );
        exit;
    }

    update_post_meta( $sat_id, 'cpt-sat__is-warranty', '' );

    // Incidencia, diagnóstico y reparación vienen copiados del SAT original y están
    // bloqueados mientras es garantía: se vacían para registrar la intervención real.
    update_post_meta( $sat_id, 'cpt-sat__incident', '' );
    update_post_meta( $sat_id, 'cpt-sat__diagnostic', '' );
    update_post_meta( $sat_id, 'cpt-sat__repair', '' );

    // El precio del SAT de garantía es una copia de referencia del SAT original y no
    // se puede editar mientras es garantía: al pasar a facturable hay que indicar el real.
    update_post_meta( $sat_id, 'cpt-sat__price', '' );
    update_post_meta( $sat_id, 'cpt-sat__price-description', '' );
    update_post_meta( $sat_id, 'cpt-sat__anticipo', '' );
    update_post_meta( $sat_id, 'cpt-sat__anticipo-payment', '' );

    // "En garantía" es el equivalente a "Finalizado" en un SAT de garantía.
    $new_status = $current_status;
    if ( $current_status === 'garantia' ) {
        $new_status = 'finalizado';
        update_post_meta( $sat_id, 'cpt-sat__status', $new_status );
    }

    av_sat_add_history( $sat_id, 'Garantía anulada', 'status', $new_status, 'El SAT deja de estar cubierto por garantía y pasa a ser facturable. Se han vaciado incidencia, diagnóstico, reparación y precio.' );

    wp_redirect( $sat_link );
    exit;
}

// ── Marcar como garantía un SAT que todavía está en curso ───────────────────
// El botón "Garantía" solo duplica el SAT (para una reclamación posterior)
// cuando el original ya está finalizado/no reparado/en garantía. Si todavía
// está en curso, no hay nada que duplicar: directamente se finaliza EL MISMO
// SAT sin coste y queda marcado como garantía (estado "garantia").
add_action( 'admin_post_av_sat_finalizar_como_garantia', 'av_sat_finalizar_como_garantia' );
function av_sat_finalizar_como_garantia() {

    $sat_id = intval( $_GET['sat_id'] ?? 0 );

    if ( ! $sat_id || get_post_type( $sat_id ) !== 'cpt-sats' ) {
        wp_redirect( home_url('/listado-sats/') );
        exit;
    }

    check_admin_referer( 'av_sat_finalizar_garantia_' . $sat_id );

    $sat_link = get_permalink( $sat_id );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_redirect( $sat_link );
        exit;
    }

    $current_status = get_post_meta( $sat_id, 'cpt-sat__status', true );

    // Ya está finalizado/no reparado/en garantía: para eso el botón duplica
    // en vez de tocar este SAT (nada que hacer aquí).
    if ( in_array( $current_status, [ 'finalizado', 'no-reparado', 'garantia' ], true ) ) {
        wp_redirect( $sat_link );
        exit;
    }

    update_post_meta( $sat_id, 'cpt-sat__is-warranty', '1' );

    // Cubierto por garantía: sin coste, igual que cualquier otro SAT de garantía.
    update_post_meta( $sat_id, 'cpt-sat__price', '' );
    update_post_meta( $sat_id, 'cpt-sat__price-description', '' );
    update_post_meta( $sat_id, 'cpt-sat__anticipo', '' );
    update_post_meta( $sat_id, 'cpt-sat__anticipo-payment', '' );

    // "finalizado" + is-warranty se resuelve automáticamente a "garantia"
    // (av_resolve_sat_status), igual que al finalizar un SAT duplicado de garantía.
    $estado_final = av_resolve_sat_status( 'finalizado', true );
    update_post_meta( $sat_id, 'cpt-sat__status', $estado_final );
    av_set_sat_status_dates( $sat_id, $estado_final );

    av_sat_add_history( $sat_id, 'Finalizado y marcado como garantía', 'status', $estado_final, 'Se ha marcado esta reparación como cubierta por garantía, sin coste.' );

    wp_redirect( $sat_link );
    exit;
}

// ── AJAX: recibe PDF base64 desde JS y lo guarda en uploads/sats/ ──────────
add_action( 'wp_ajax_sat_guardar_pdf',        'sat_guardar_pdf_ajax' );
add_action( 'wp_ajax_nopriv_sat_guardar_pdf', 'sat_guardar_pdf_ajax' );

function sat_guardar_pdf_ajax() {

    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sat_guardar_pdf' ) ) {
        wp_send_json_error( 'Nonce inválido' );
    }

    $post_id = intval( $_POST['sat_id'] ?? 0 );
    if ( ! $post_id ) {
        wp_send_json_error( 'SAT ID inválido' );
    }

    $data_uri = $_POST['pdf_base64'] ?? '';
    if ( strpos( $data_uri, ',' ) !== false ) {
        $data_uri = explode( ',', $data_uri, 2 )[1];
    }
    $pdf_content = base64_decode( $data_uri );
    if ( ! $pdf_content ) {
        wp_send_json_error( 'PDF inválido' );
    }

    // Crear carpeta uploads/sats/ si no existe
    $upload_dir = wp_upload_dir();
    $sats_dir   = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'sats';
    $sats_url   = $upload_dir['baseurl'] . '/sats';
    if ( ! is_dir( $sats_dir ) ) {
        mkdir( $sats_dir, 0755, true );
        file_put_contents( $sats_dir . DIRECTORY_SEPARATOR . '.htaccess', 'Options -Indexes' );
    }

    $sat_num  = get_post_meta( $post_id, 'cpt-sat__sat-id', true );
    // Token único e imposible de adivinar, derivado del post_id + clave secreta WP
    $token    = substr( hash( 'sha256', $post_id . AUTH_KEY ), 0, 12 );
    $filename = 'SAT_' . ( $sat_num ?: $post_id ) . '_' . $token . '.pdf';
    $filepath = $sats_dir . DIRECTORY_SEPARATOR . $filename;
    $fileurl  = $sats_url . '/' . $filename;

    if ( file_put_contents( $filepath, $pdf_content ) === false ) {
        wp_send_json_error( 'Error al guardar el archivo' );
    }

    update_post_meta( $post_id, 'cpt-sat__pdf-file', $fileurl );

    wp_send_json_success( array( 'url' => $fileurl ) );
}

// Captura tanto para usuarios logueados como no logueados
add_action('admin_post_nopriv_save_contact', 'save_contact');
add_action('admin_post_save_contact', 'save_contact');

function save_contact() {
    // Asegúrate de que los campos existen
    if (!isset($_POST['nombre']) || !isset($_POST['telefono-ext']) || !isset($_POST['telefono']) || !isset($_POST['type-client'])) {
        wp_redirect(home_url('/error/'));
        exit;
    }

    // Sanitizar datos
    $nombre   = sanitize_text_field($_POST['nombre']);
    $telefono = sanitize_text_field($_POST['telefono']); 
    $telefono_ext = sanitize_text_field($_POST['telefono-ext']); 
    $email = sanitize_text_field($_POST['email']); 
    $dni = sanitize_text_field($_POST['dni']); 
    $type_client = sanitize_text_field($_POST['type-client']);
    $has_custom_redirect = ! empty( $_POST['redirect_to'] );
    $redirect_to = $has_custom_redirect ? esc_url_raw( $_POST['redirect_to'] ) : home_url('/listado-clientes/');

    if (!isset($_POST['id'])) {
        // Crear nuevo post del tipo personalizado
        $nuevo_id = wp_insert_post([
            'post_type'   => 'cpt-clients',  
            'post_title'  => $nombre,
            'post_status' => 'publish',
            'meta_input'  => [
                'cpt-client__extension' => $telefono_ext,
                'cpt-client__phone' => $telefono,
                'cpt-client__dni' => $dni,
                'cpt-client__name' => $nombre,
                'cpt-client__email' => $email,
                'cpt-client__type-client' => $type_client,
            ],
        ]);

        if ( ! is_wp_error( $nuevo_id ) && $nuevo_id ) {

            $year = date('Y');

            wp_update_post([
                'ID'         => $nuevo_id,
                'post_title' => 'CLIENT-' . $year . '-' . $nuevo_id,
                'post_name'  => 'CLIENT-' . $year . '-' . $nuevo_id,
            ]);

            if ( $has_custom_redirect ) {
                $redirect_to = add_query_arg( 'id', $nuevo_id, $redirect_to );
            }

        }
    }else{
        $user_id = $_POST['id'];
        wp_update_post([
            'ID'          => $user_id,           
            'meta_input'  => [
                'cpt-client__extension' => $telefono_ext,
                'cpt-client__phone' => $telefono,
                'cpt-client__dni' => $dni,
                'cpt-client__name' => $nombre,
                'cpt-client__email' => $email,
                'cpt-client__type-client' => $type_client,
            ], 
        ]);
    }


    // Redirigir tras guardar
    wp_redirect( $redirect_to );
    exit;
}

add_action('wp_ajax_av_ajax_save_sat_status', 'av_ajax_save_sat_status');
add_action( 'wp_ajax_nopriv_av_ajax_save_sat_status', 'av_ajax_save_sat_status' );
function av_ajax_save_sat_status(){

    if ( ! isset($_POST['sat-id'], $_POST['status']) ) {
        wp_send_json_error('Datos incompletos', 400);
    }

    $sat_id = intval($_POST['sat-id']);
    $status = sanitize_text_field($_POST['status']);
    $price = $_POST['precio-final'] ?? null;
    $payment = sanitize_text_field($_POST['tipo-pago'] ?? '');
    if ( ! in_array( $payment, [ 'tarjeta', 'efectivo' ], true ) ) {
        $payment = null;
    }

    // Reparación indicada desde el listado al marcar el SAT como reparado.
    $repair = sanitize_text_field( wp_unslash( $_POST['reparacion'] ?? '' ) );

    // Un SAT ya finalizado, no reparado o en garantía solo puede modificarlo un administrador.
    $current_status = get_post_meta( $sat_id, 'cpt-sat__status', true );
    if ( in_array( $current_status, [ 'finalizado', 'no-reparado', 'garantia' ], true ) && ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Este SAT no se puede modificar', 403 );
    }

    $is_warranty = get_post_meta( $sat_id, 'cpt-sat__is-warranty', true ) === '1';
    $status = av_resolve_sat_status( $status, $is_warranty );

    $meta_fields = [
        'cpt-sat__status' => $status,
    ];

    if($price !== null){
        $meta_fields['cpt-sat__price'] = $price;
    }

    if($payment !== null){
        $meta_fields['cpt-sat__price-description'] = $payment;
    }

    // Se guarda con el mismo formato que el widget de reparación del detalle del SAT
    // (lista de líneas con texto y precio) para que se pinte igual en las dos pantallas.
    if($repair !== ''){
        $meta_fields['cpt-sat__repair'] = wp_json_encode( [ [ 'text' => $repair, 'price' => '' ] ], JSON_UNESCAPED_UNICODE );
    }

    foreach ($meta_fields as $key => $value) {
        update_post_meta($sat_id, $key, $value);
    }

    av_set_sat_status_dates( $sat_id, $status );

    wp_send_json_success([
        'updated' => true,
        'price' => $price,
        'payment' => $payment,
    ]);

}

add_action('wp_ajax_av_ajax_check_user', 'av_ajax_check_user');
add_action( 'wp_ajax_nopriv_av_ajax_check_user', 'av_ajax_check_user' );
function av_ajax_check_user(){

    if ( ! isset($_POST['value'], $_POST['type']) ) {
        wp_send_json_error('Falta inseertar DNI', 400);
    }

    $value = sanitize_text_field($_POST['value']);
    $type = sanitize_text_field($_POST['type']);
    $extension = sanitize_text_field($_POST['extension']);
    $phone = sanitize_text_field($_POST['phone']);
    $args = array(
        'post_type'         => 'cpt-clients',
        'post_status'       => 'publish',
        'posts_per_page'    => '-1',
        'orderby'           => 'date',
        'order'             => 'DESC',
    );

    $posts = new WP_Query( $args );

    $clients = $posts->posts;

    foreach ($clients as $key => $client) {
        $result = true;
        $campo_dni = get_field('cpt-client__dni', $client->ID);
        $campo_tel_ext = get_field('cpt-client__extension', $client->ID);
        $campo_tel = get_field('cpt-client__phone', $client->ID);
        $campo_name = get_field('cpt-client__name', $client->ID);
        $detail = get_permalink($client->ID);

        switch ($type) {
            case 'name':
                if(strtolower($campo_name) == strtolower($value)){
                    $result = false;                   
                }
                break;

            case 'dni':
                if(strtolower($campo_dni) == strtolower($value)){
                    $result = false;                   
                }
                break;             

            case 'phone-ext':
                if($campo_tel_ext . $campo_tel == $extension . $phone){
                    $result = false;                   
                }
            break; 

            case 'phone':
                if($campo_tel_ext . $campo_tel == $extension . $value){
                    $result = false;                   
                }
                break;           
        }

        if(!$result){
            break;   
        }
        
    }

    wp_send_json_success([
        'result' => $result,
        'type' => $type,
        'current' => [
            'ext' => $extension,
            'phone' => $phone,
        ],
        'client' => [
            'id'    => $client->ID,
            'name'  => $campo_name,
            'telExt' => $campo_tel_ext,
            'tel' => $campo_tel,
            'dni'  => $campo_dni,
            'detail' => $detail,
            'createSatUrl' => get_permalink( get_page_by_path( 'crear-sat' ) ) . '?id=' . $client->ID,
        ],
    ]);

}

// ── AJAX: buscar clientes para el selector de "Crear SAT" ──────────────────
add_action('wp_ajax_av_ajax_search_clients_picker', 'av_ajax_search_clients_picker');
add_action('wp_ajax_nopriv_av_ajax_search_clients_picker', 'av_ajax_search_clients_picker');
function av_ajax_search_clients_picker() {

    $term = sanitize_text_field( $_POST['term'] ?? '' );

    if ( mb_strlen( $term ) < 2 ) {
        wp_send_json_success( [ 'clients' => [] ] );
    }

    $posts = get_posts([
        'post_type'      => 'cpt-clients',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    $term_norm = mb_strtolower( $term );
    $matches   = [];

    foreach ( $posts as $client_id ) {
        $name  = get_field( 'cpt-client__name', $client_id );
        $dni   = get_field( 'cpt-client__dni', $client_id );
        $ext   = get_field( 'cpt-client__extension', $client_id );
        $phone = get_field( 'cpt-client__phone', $client_id );

        $haystack = mb_strtolower( $name . ' ' . $dni . ' ' . $ext . $phone );

        if ( mb_strpos( $haystack, $term_norm ) !== false ) {
            $matches[] = [
                'id'    => $client_id,
                'name'  => $name,
                'dni'   => $dni,
                'phone' => trim( $ext . ' ' . $phone ),
                'url'   => add_query_arg( 'id', $client_id, get_permalink( get_page_by_path( 'crear-sat' ) ) ),
            ];
        }

        if ( count( $matches ) >= 8 ) {
            break;
        }
    }

    wp_send_json_success( [ 'clients' => $matches ] );
}

function solo_usuarios_logueados() {

    // Página pública de seguimiento para el cliente: no requiere login.
    if ( is_page_template( 'templates/template-seguimiento-sat.php' ) ) {
        return;
    }

    if ( !is_user_logged_in() && !is_admin() ) {

        $redirect_url = esc_url( $_SERVER['REQUEST_URI'] );

        wp_redirect( wp_login_url( $redirect_url ) );
        exit;
    }
}
add_action('template_redirect', 'solo_usuarios_logueados');

add_action('wp_ajax_av_ajax_save_signature', 'av_ajax_save_signature');
add_action('wp_ajax_nopriv_av_ajax_save_signature', 'av_ajax_save_signature');

function av_ajax_save_signature() {    

    if (!isset($_POST['image'], $_POST['sat-id'])) {
        wp_send_json_error('Datos incompletos');
    }

    $sat_id = intval($_POST['sat-id']);
    // Limpiar base64
    $image = $_POST['image'];
    // $image = str_replace('data:image/png;base64,', '', $image);
    // $image = str_replace(' ', '+', $image);
    $image_data = base64_decode($image);

    // Crear archivo temporal
    $filename = 'firma_' . time() . '.png';
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['path'] . '/' . $filename;

    file_put_contents($file_path, $image_data);

    // Registrar en la librería de medios
    $filetype = wp_check_filetype($filename, null);

    $attachment = array(
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name($filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $file_path, $sat_id);

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    update_field('cpt-sat__signature-image', $image, $sat_id);

    wp_send_json_success([
        'attachment_id' => $attach_id
    ]);
}

use Dompdf\Dompdf;

add_action('admin_post_generar_parte_sat', 'app_sat_generar_pdf');
add_action('admin_post_nopriv_generar_parte_sat', 'app_sat_generar_pdf');

function app_sat_generar_pdf() {

    $cliente = sanitize_text_field($_POST['cliente']);
    $email   = sanitize_email($_POST['email']);
    $firma   = $_POST['firma_base64'];

    // ===== GUARDAR FIRMA =====
    $firma = str_replace('data:image/png;base64,', '', $firma);
    $firma = str_replace(' ', '+', $firma);
    $firma_data = base64_decode($firma);

    $upload_dir = wp_upload_dir();
    $firma_path = $upload_dir['path'] . '/firma_'.time().'.png';
    file_put_contents($firma_path, $firma_data);

    // ===== GENERAR PDF =====
    $dompdf = new Dompdf();

    $html = "
        <h1>Parte de Reparación</h1>
        <p><strong>Cliente:</strong> $cliente</p>
        <p><strong>Email:</strong> $email</p>
        <br>
        <p><strong>Firma del cliente:</strong></p>
        <img src='$firma_path' width='200'>
    ";

    $dompdf->loadHtml($html);
    $dompdf->render();

    $pdf_output = $dompdf->output();
    $pdf_path = $upload_dir['path'].'/parte_'.time().'.pdf';
    file_put_contents($pdf_path, $pdf_output);

    // ===== ENVIAR EMAIL =====
    wp_mail(
        $email,
        'Copia de su reparación - App Informática',
        'Adjuntamos su parte en PDF.',
        [],
        [$pdf_path]
    );

    wp_redirect(home_url('/gracias'));
    exit;
}


function add_svg_favicon() {
    echo '<link rel="icon" type="image/svg+xml" href="' . get_stylesheet_directory_uri() . '/assets/imgs/logo_icb.svg">';
}
add_action('wp_head', 'add_svg_favicon', 99);

// ─── CPT Facturas ────────────────────────────────────────────────────────────
add_action( 'init', 'av_register_cpt_facturas' );
function av_register_cpt_facturas() {
    register_post_type( 'cpt-facturas', [
        'labels' => [
            'name'               => 'Facturas',
            'singular_name'      => 'Factura',
            'menu_name'          => 'Facturas',
            'all_items'          => 'Todas las facturas',
            'edit_item'          => 'Ver factura',
            'view_item'          => 'Ver factura',
            'search_items'       => 'Buscar facturas',
            'not_found'          => 'No se encontraron facturas',
            'not_found_in_trash' => 'No hay facturas en la papelera',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'menu_position'     => 25,
        'menu_icon'         => 'dashicons-media-text',
        'supports'          => [ 'title' ],
        'capabilities'      => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'      => true,
    ] );
}

function av_sat_factura_upsert( $sat_id, array $data ) {
    $existing = get_posts( [
        'post_type'      => 'cpt-facturas',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => '_factura_sat_id', 'value' => $sat_id, 'compare' => '=' ] ],
    ] );

    if ( $existing ) {
        $factura_id = $existing[0];
        // Solo actualizamos datos, nunca el número de factura
        wp_update_post( [ 'ID' => $factura_id, 'post_title' => get_post_meta( $factura_id, '_factura_numero', true ) ] );
    } else {
        // Generar número secuencial por año, irrevocable
        $year    = intval( wp_date( 'Y' ) );
        $opt_key = 'av_factura_seq_' . $year;
        $seq     = intval( get_option( $opt_key, 0 ) ) + 1;
        update_option( $opt_key, $seq, false );
        $numero = $year . '-' . str_pad( $seq, 6, '0', STR_PAD_LEFT );

        $factura_id = wp_insert_post( [
            'post_type'   => 'cpt-facturas',
            'post_title'  => $numero,
            'post_status' => 'publish',
        ] );
        if ( ! $factura_id || is_wp_error( $factura_id ) ) return;
        // Se asigna una sola vez y nunca se vuelve a tocar
        update_post_meta( $factura_id, '_factura_numero', $numero );
    }

    update_post_meta( $factura_id, '_factura_sat_id',     $sat_id );
    update_post_meta( $factura_id, '_factura_sat_num',    $data['sat_num'] );
    update_post_meta( $factura_id, '_factura_fecha',      wp_date( 'd/m/Y H:i' ) );
    update_post_meta( $factura_id, '_factura_cliente',    $data['client_name'] );
    update_post_meta( $factura_id, '_factura_telefono',   $data['client_phone'] );
    update_post_meta( $factura_id, '_factura_tipo',       $data['type_label'] );
    update_post_meta( $factura_id, '_factura_modelo',     $data['model'] );
    update_post_meta( $factura_id, '_factura_serial',     $data['serial'] );
    update_post_meta( $factura_id, '_factura_reparacion', wp_json_encode( $data['repair_items'], JSON_UNESCAPED_UNICODE ) );
    update_post_meta( $factura_id, '_factura_piezas',     wp_json_encode( $data['parts_items'],  JSON_UNESCAPED_UNICODE ) );
    update_post_meta( $factura_id, '_factura_base',       $data['base'] );
    update_post_meta( $factura_id, '_factura_iva',        $data['iva'] );
    update_post_meta( $factura_id, '_factura_total',      $data['total'] );
    update_post_meta( $factura_id, '_factura_anticipo',            $data['anticipo'] ?? 0 );
    update_post_meta( $factura_id, '_factura_anticipo_forma_pago', $data['anticipo_forma_pago'] ?? '' );
    update_post_meta( $factura_id, '_factura_total_pagar',         $data['total_pagar'] ?? $data['total'] );
    update_post_meta( $factura_id, '_factura_forma_pago', $data['forma_pago'] );
    update_post_meta( $factura_id, '_factura_tecnico',    $data['tecnico'] );
    update_post_meta( $factura_id, '_factura_garantia',   $data['garantia'] ?? '' );
}

// ─── Configuración general ───────────────────────────────────────────────────

/**
 * La antigua pantalla "Config. Factura" pasa a ser una pestaña dentro de
 * "Configuración general". Repunta la página existente al nuevo template para
 * no perder el enlace del menú al actualizar. Se ejecuta una sola vez.
 */
add_action( 'init', 'av_migrate_config_factura_template' );
function av_migrate_config_factura_template() {

    if ( get_option( 'av_config_general_migrated' ) ) return;

    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'templates/template-config-factura.php',
    ]);

    foreach ( $pages as $page_id ) {
        update_post_meta( $page_id, '_wp_page_template', 'templates/template-config-general.php' );
    }

    update_option( 'av_config_general_migrated', 1 );
}

// ─── Configuración de factura ────────────────────────────────────────────────
function av_get_invoice_config() {
    $defaults = [
        'logo_url'           => get_stylesheet_directory_uri() . '/assets/imgs/logo_icorebyte.png',
        'logo_attachment_id' => 0,
        'razon_social'       => '',
        'nif_cif'      => '',
        'web'          => 'icorebyte.com',
        'direccion'    => '',
        'pais'         => 'España',
        'moneda'       => 'EUR',
        'iva_pct'      => '21',
        'iva_auto'     => '1',
        'cliente'      => [ 'mostrar' => '1', 'nombre' => '1', 'telefono' => '1', 'dni' => '0', 'email' => '0' ],
        'equipo'       => [ 'mostrar' => '1', 'sat_num' => '1', 'tipo' => '1', 'modelo' => '1', 'serial' => '1', 'incidencia' => '1' ],
    ];
    $saved = get_option( 'av_invoice_config', [] );
    foreach ( $defaults as $k => $v ) {
        if ( is_array( $v ) ) {
            $saved[$k] = array_merge( $v, (array) ( $saved[$k] ?? [] ) );
        } elseif ( ! isset( $saved[$k] ) || $saved[$k] === '' ) {
            $saved[$k] = $v;
        }
    }
    return $saved;
}

function av_process_invoice_config_save( $permalink ) {
    if ( empty( $_POST['av_invoice_config_save'] ) ) return false;
    if ( ! is_user_logged_in() ) wp_die( 'Acceso denegado.' );
    if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'av_invoice_config' ) ) wp_die( 'Nonce inválido.' );

    $cfg = [
        'logo_attachment_id' => intval( $_POST['logo_attachment_id'] ?? 0 ),
        'logo_url'           => sanitize_text_field( wp_unslash( $_POST['logo_url'] ?? '' ) ),
        'razon_social'       => sanitize_text_field( wp_unslash( $_POST['razon_social'] ?? '' ) ),
        'nif_cif'      => sanitize_text_field( wp_unslash( $_POST['nif_cif']      ?? '' ) ),
        'web'          => sanitize_text_field( wp_unslash( $_POST['web']          ?? '' ) ),
        'direccion'    => sanitize_text_field( wp_unslash( $_POST['direccion']    ?? '' ) ),
        'pais'         => sanitize_text_field( wp_unslash( $_POST['pais']     ?? 'España' ) ),
        'moneda'       => strtoupper( sanitize_text_field( wp_unslash( $_POST['moneda'] ?? 'EUR' ) ) ),
        'iva_pct'      => sanitize_text_field( wp_unslash( $_POST['iva_pct']  ?? '21' ) ),
        'iva_auto'     => isset( $_POST['iva_auto'] ) ? '1' : '0',
        'cliente'   => [
            'mostrar'  => isset( $_POST['cliente_mostrar'] )  ? '1' : '0',
            'nombre'   => isset( $_POST['cliente_nombre'] )   ? '1' : '0',
            'telefono' => isset( $_POST['cliente_telefono'] ) ? '1' : '0',
            'dni'      => isset( $_POST['cliente_dni'] )      ? '1' : '0',
            'email'    => isset( $_POST['cliente_email'] )    ? '1' : '0',
        ],
        'equipo'    => [
            'mostrar'    => isset( $_POST['equipo_mostrar'] )    ? '1' : '0',
            'sat_num'    => isset( $_POST['equipo_sat_num'] )    ? '1' : '0',
            'tipo'       => isset( $_POST['equipo_tipo'] )       ? '1' : '0',
            'modelo'     => isset( $_POST['equipo_modelo'] )     ? '1' : '0',
            'serial'     => isset( $_POST['equipo_serial'] )     ? '1' : '0',
            'incidencia' => isset( $_POST['equipo_incidencia'] ) ? '1' : '0',
        ],
    ];
    update_option( 'av_invoice_config', $cfg );

    // Vuelve a la misma pestaña de la Configuración general desde la que se guardó
    $redirect_args = [ 'saved' => '1' ];
    if ( ! empty( $_POST['av_cfg_tab'] ) ) {
        $redirect_args['tab'] = sanitize_key( $_POST['av_cfg_tab'] );
    }

    wp_safe_redirect( add_query_arg( $redirect_args, $permalink ) );
    exit;
}

function av_currency_symbol( $code ) {
    $map = [
        'EUR' => '€',  'GBP' => '£',   'USD' => '$',  'CHF' => 'CHF',
        'SEK' => 'kr', 'NOK' => 'kr',  'DKK' => 'kr', 'PLN' => 'zł',
        'CZK' => 'Kč', 'HUF' => 'Ft', 'RON' => 'lei','MXN' => '$',
        'ARS' => '$',  'COP' => '$',   'CLP' => '$',  'BRL' => 'R$',
    ];
    return $map[ strtoupper( $code ) ] ?? $code;
}

function av_invoice_logo_url( $cfg ) {
    if ( ! empty( $cfg['logo_attachment_id'] ) ) {
        $url = wp_get_attachment_url( intval( $cfg['logo_attachment_id'] ) );
        if ( $url ) return $url;
    }
    return $cfg['logo_url'] ?? '';
}

// Encolar media uploader solo en la página de configuración general (selector de logo)
add_action( 'wp_enqueue_scripts', 'av_enqueue_invoice_config_media' );
function av_enqueue_invoice_config_media() {
    if ( is_page_template( 'templates/template-config-general.php' ) ) {
        wp_enqueue_media();
    }
}

// ─── SAT Invoice PDF ─────────────────────────────────────────────────────────
add_action( 'template_redirect', 'av_sat_invoice_page' );
function av_sat_invoice_page() {
    if ( ! isset( $_GET['sat_invoice'] ) ) return;

    $sat_id = intval( $_GET['sat_id'] ?? 0 );
    if ( ! $sat_id ) wp_die( 'SAT no válido.' );
    if ( ! is_user_logged_in() ) wp_die( 'Acceso denegado.' );
    if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'sat_invoice_' . $sat_id ) ) wp_die( 'Nonce inválido.' );

    $post = get_post( $sat_id );
    if ( ! $post || $post->post_type !== 'cpt-sats' ) wp_die( 'SAT no encontrado.' );

    $sat_id_visible = get_field( 'cpt-sat__sat-id',        $sat_id );
    $type_equipment = get_field( 'cpt-sat__type-equipment', $sat_id );
    $client_id      = get_field( 'cpt-sat__client-id',     $sat_id );
    $entry_date     = get_field( 'cpt-sat__entry-date',    $sat_id );
    $model          = get_field( 'cpt-sat__model',         $sat_id );
    $serial         = get_field( 'cpt-sat__model-imei',    $sat_id );
    $incident       = get_field( 'cpt-sat__incident',      $sat_id );
    $physical_condition = get_field( 'cpt-sat__physical-condition', $sat_id );
    $repair_raw     = get_field( 'cpt-sat__repair',        $sat_id );
    $ordered_raw    = get_field( 'cpt-sat__ordered-parts', $sat_id );
    $price          = get_field( 'cpt-sat__price',         $sat_id );
    $price_desc     = get_field( 'cpt-sat__price-description', $sat_id );
    $anticipo         = get_field( 'cpt-sat__anticipo',         $sat_id );
    $anticipo_payment = get_field( 'cpt-sat__anticipo-payment', $sat_id );
    $repair_date    = get_field( 'cpt-sat__repair-date',   $sat_id );
    $attended       = get_field( 'cpt-sat__attended',      $sat_id );
    $warranty_period = get_field( 'cpt-sat__warranty-period', $sat_id );

    $client_name  = '';
    $client_dni   = '';
    $client_phone = '';
    if ( $client_id ) {
        $client_name  = get_field( 'cpt-client__name',      $client_id );
        $client_dni   = get_field( 'cpt-client__dni',       $client_id );
        $ext          = get_field( 'cpt-client__extension', $client_id );
        $phone        = get_field( 'cpt-client__phone',     $client_id );
        $client_phone = trim( $ext . ' ' . $phone );
    }

    $parse_items = function( $raw ) {
        $items = json_decode( $raw, true );
        if ( is_array( $items ) ) return $items;
        if ( empty( $raw ) ) return [];
        return array_values( array_filter( array_map(
            fn( $l ) => trim( $l ) ? [ 'text' => trim( $l ), 'price' => '' ] : null,
            explode( "\n", $raw )
        ) ) );
    };

    $repair_items = $parse_items( $repair_raw );
    $parts_items  = $parse_items( $ordered_raw );

    $equipment_labels = [
        'pc' => 'PC Torre', 'portatil' => 'Portátil', 'tablet' => 'Tablet',
        'movil' => 'Móvil', 'impresora' => 'Impresora', 'tv' => 'TV',
        'consola' => 'Consola', 'mando' => 'Mando', 'otro' => 'Otro',
    ];
    $type_label = $equipment_labels[ $type_equipment ] ?? $type_equipment;

    $fmt_price = fn( $p ) => ! empty( $p )
        ? number_format( floatval( str_replace( ',', '.', $p ) ), 2, ',', '.' ) . ' €'
        : '';

    $inv_cfg  = av_get_invoice_config();
    $iva_rate = 1 + ( floatval( $inv_cfg['iva_pct'] ?? 21 ) / 100 );

    // Calcular totales para guardar en BD
    $inv_sum = 0;
    foreach ( array_merge( $repair_items, $parts_items ) as $it ) {
        $p = floatval( str_replace( ',', '.', $it['price'] ?? '' ) );
        if ( $p > 0 ) $inv_sum += $p;
    }
    $inv_total = $inv_sum;
    $inv_base  = $inv_total / $iva_rate;
    $inv_iva   = $inv_total - $inv_base;

    // Anticipo (paga y señal): se descuenta del total a pagar, pero no afecta
    // a la base imponible/IVA, que se calculan sobre el importe total del servicio.
    $inv_anticipo    = ! empty( $anticipo ) ? floatval( str_replace( ',', '.', $anticipo ) ) : 0;
    $inv_total_pagar = max( 0, $inv_total - $inv_anticipo );

    // Guardar/actualizar registro de factura y recuperar su número
    av_sat_factura_upsert( $sat_id, [
        'sat_num'      => $sat_id_visible,
        'client_name'  => $client_name,
        'client_phone' => $client_phone,
        'type_label'   => $type_label,
        'model'        => $model,
        'serial'       => $serial,
        'repair_items' => $repair_items,
        'parts_items'  => $parts_items,
        'base'         => round( $inv_base, 2 ),
        'iva'          => round( $inv_iva,  2 ),
        'total'        => round( $inv_total, 2 ),
        'anticipo'            => round( $inv_anticipo, 2 ),
        'anticipo_forma_pago' => $anticipo_payment,
        'total_pagar'         => round( $inv_total_pagar, 2 ),
        'forma_pago'   => $price_desc,
        'tecnico'      => $attended,
        'garantia'     => $warranty_period,
    ] );

    $fac_posts      = get_posts( [
        'post_type'      => 'cpt-facturas',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => [ [ 'key' => '_factura_sat_id', 'value' => $sat_id, 'compare' => '=' ] ],
    ] );
    $factura_numero = $fac_posts ? get_post_meta( $fac_posts[0], '_factura_numero', true ) : '';

    include locate_template( 'components/c-invoice.php' );
    exit;
}

// El estado de cobro no se marca a mano: una factura queda pagada cuando el SAT
// se finaliza con metodo de pago (rellena _factura_forma_pago en el upsert).

// ─── Factura Preview (datos de prueba) ───────────────────────────────────────
add_action( 'template_redirect', 'av_factura_preview_page' );
function av_factura_preview_page() {
    if ( ! isset( $_GET['factura_preview'] ) ) return;
    if ( ! is_user_logged_in() ) wp_die( 'Acceso denegado.' );
    if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'av_factura_preview' ) ) wp_die( 'Nonce inválido.' );
    if ( ! current_user_can( 'administrator' ) ) wp_die( 'Acceso denegado.' );

    $current_user   = wp_get_current_user();
    $sat_id_visible = '999';
    $client_id      = null;
    $client_name    = 'Cliente Demo';
    $client_phone   = '600 000 000';
    $type_label     = 'Móvil';
    $model          = 'iPhone 16 Pro';
    $serial         = 'A1B2C3D4E5F6';
    $incident       = 'No enciende tras caída al agua';
    $physical_condition = 'Marcas de uso en los bordes y arañazo en la esquina inferior derecha.';
    $entry_date     = wp_date( 'd/m/Y' );
    $repair_date    = wp_date( 'd/m/Y' );
    $attended       = $current_user->display_name ?: $current_user->user_login;
    $price_desc     = 'Tarjeta';
    $anticipo         = '30,00';
    $anticipo_payment = 'efectivo';
    $warranty_period = '6-meses';
    $repair_items   = [
        [ 'text' => 'Diagnóstico y reparación de placa base', 'price' => '80,00' ],
    ];
    $parts_items    = [
        [ 'text' => 'Pantalla iPhone 16 Pro (OLED)', 'price' => '150,00' ],
    ];
    $factura_numero = 'PREVIEW';
    $inv_cfg        = av_get_invoice_config();
    $iva_rate       = 1 + ( floatval( $inv_cfg['iva_pct'] ?? 21 ) / 100 );

    include locate_template( 'components/c-invoice.php' );
    exit;
}

// ─── Construccion de los argumentos de WP_Query para el listado de SATs ─────
// Compartido entre la carga normal de la pagina (template-sats.php) y el
// buscador asincrono (av_ajax_filter_sats), para no duplicar la logica.
function av_build_sats_query_args( array $params, $paged = 1 ) {

    $meta_query = array( 'relation' => 'AND' );

    $filter = isset( $params['filter'] ) ? $params['filter'] : '';

    // El buscador siempre busca en TODOS los SATs: en cuanto se rellena cualquier
    // campo del filtro se ignora la pestaña activa (en curso / finalizados), que
    // si no dejaría fuera, por ejemplo, un SAT ya finalizado buscado por su número.
    $has_search = ! empty( $params['numero-sat'] )
        || ! empty( $params['nombre-cliente'] )
        || ! empty( $params['dni-cliente'] )
        || ! empty( $params['fecha'] )
        || ! empty( $params['estado'] )
        || ( isset( $params['importe'] ) && $params['importe'] !== '' );

    if ( $has_search ) {
        $filter = 'todos';
    }

    if ( ! empty( $params['estado'] ) ) {
        // Filtro de estado específico (viene del Dashboard o del buscador)
        $meta_query[] = array(
            'key'     => 'cpt-sat__status',
            'value'   => sanitize_text_field( $params['estado'] ),
            'compare' => '=',
        );
        $posts_per_page = 20;
    } elseif ( $filter === 'en-curso' || empty( $filter ) ) {
        $meta_query[] = array(
            'key'     => 'cpt-sat__status',
            'value'   => array('finalizado', 'no-reparado', 'garantia'),
            'compare' => 'NOT IN'
        );
        $posts_per_page = -1;
    } elseif ( $filter === 'finalizados' ) {
        $meta_query[] = array(
            'key'     => 'cpt-sat__status',
            'value'   => array('finalizado', 'no-reparado', 'garantia'),
            'compare' => 'IN'
        );
        $posts_per_page = 20;
    } else {
        $posts_per_page = 20;
    }

    // Numero de SAT (coincidencia exacta)
    if ( ! empty( $params['numero-sat'] ) ) {
        $meta_query[] = array(
            'key'     => 'cpt-sat__sat-id',
            'value'   => sanitize_text_field( $params['numero-sat'] ),
            'compare' => '=',
        );
    }

    // Importe
    if ( isset( $params['importe'] ) && $params['importe'] !== '' ) {
        $meta_query[] = array(
            'key'     => 'cpt-sat__price',
            'value'   => sanitize_text_field( $params['importe'] ),
            'compare' => 'LIKE',
        );
    }

    // Fecha (el input HTML envia Y-m-d; el campo se guarda como texto d/m/Y H:i)
    if ( ! empty( $params['fecha'] ) ) {
        $fecha_dt = DateTime::createFromFormat( 'Y-m-d', sanitize_text_field( $params['fecha'] ) );
        if ( $fecha_dt ) {
            $meta_query[] = array(
                'key'     => 'cpt-sat__entry-date',
                'value'   => $fecha_dt->format( 'd/m/Y' ),
                'compare' => 'LIKE',
            );
        }
    }

    // Nombre cliente y/o DNI cliente: se buscan primero los clientes que coincidan
    $nombre_cliente = ! empty( $params['nombre-cliente'] ) ? sanitize_text_field( $params['nombre-cliente'] ) : '';
    $dni_cliente    = ! empty( $params['dni-cliente'] )    ? sanitize_text_field( $params['dni-cliente'] )    : '';

    if ( $nombre_cliente !== '' || $dni_cliente !== '' ) {

        $client_meta_query = array( 'relation' => 'AND' );

        if ( $nombre_cliente !== '' ) {
            $client_meta_query[] = array(
                'key'     => 'cpt-client__name',
                'value'   => $nombre_cliente,
                'compare' => 'LIKE',
            );
        }

        if ( $dni_cliente !== '' ) {
            $client_meta_query[] = array(
                'key'     => 'cpt-client__dni',
                'value'   => $dni_cliente,
                'compare' => 'LIKE',
            );
        }

        $client_ids = get_posts(array(
            'post_type'   => 'cpt-clients',
            'numberposts' => -1,
            'fields'      => 'ids',
            'meta_query'  => $client_meta_query,
        ));

        // Si no hay clientes que coincidan, forzamos que la búsqueda no devuelva nada
        $meta_query[] = array(
            'key'     => 'cpt-sat__client-id',
            'value'   => ! empty( $client_ids ) ? $client_ids : array( 0 ),
            'compare' => 'IN',
        );
    }

    $args = array(
        'post_type'      => 'cpt-sats',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'paged'          => $paged,
    );

    if ( count( $meta_query ) > 1 ) {
        $args['meta_query'] = $meta_query;
    }

    return $args;
}

// ─── Buscador de SATs asincrono (AJAX) ───────────────────────────────────────
add_action( 'wp_ajax_av_ajax_filter_sats', 'av_ajax_filter_sats' );
function av_ajax_filter_sats() {

    check_ajax_referer( 'av_sats_filter_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
    }

    $paged = isset( $_REQUEST['paged'] ) ? max( 1, intval( $_REQUEST['paged'] ) ) : 1;
    $args  = av_build_sats_query_args( $_REQUEST, $paged );

    $posts = new WP_Query( $args );
    $sats  = $posts->posts;

    // Base correcta para los enlaces de paginacion (get_pagenum_link() no es
    // fiable dentro de admin-ajax.php, aqui no hay un "post actual").
    $sats_page_posts = get_posts(array(
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-sats.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
    $sats_page_url = ! empty( $sats_page_posts ) ? get_permalink( $sats_page_posts[0] ) : home_url( '/' );
    $public_params = $_REQUEST;
    unset( $public_params['action'], $public_params['nonce'], $public_params['paged'] );
    $sats_page_url = add_query_arg( $public_params, $sats_page_url );

    ob_start();
    include locate_template( 'components/c-list-cpt-sats-count.php' );
    $count_html = ob_get_clean();

    ob_start();
    include locate_template( 'components/c-list-cpt-sats-list.php' );
    $list_html = ob_get_clean();

    wp_send_json_success( array(
        'count' => $count_html,
        'list'  => $list_html,
    ) );
}

// ─── Construccion de los argumentos de WP_Query para el listado de clientes ──
// Compartido entre la carga normal de la pagina (template-clients.php) y el
// buscador asincrono (av_ajax_filter_clients).
function av_build_clients_query_args( array $params, $paged = 1 ) {

    $meta_query = array( 'relation' => 'AND' );

    if ( ! empty( $params['nombre-cliente'] ) ) {
        $meta_query[] = array(
            'key'     => 'cpt-client__name',
            'value'   => sanitize_text_field( $params['nombre-cliente'] ),
            'compare' => 'LIKE',
        );
    }

    if ( ! empty( $params['dni-cliente'] ) ) {
        $meta_query[] = array(
            'key'     => 'cpt-client__dni',
            'value'   => sanitize_text_field( $params['dni-cliente'] ),
            'compare' => 'LIKE',
        );
    }

    if ( ! empty( $params['telefono'] ) ) {
        $meta_query[] = array(
            'key'     => 'cpt-client__phone',
            'value'   => sanitize_text_field( $params['telefono'] ),
            'compare' => 'LIKE',
        );
    }

    if ( ! empty( $params['email'] ) ) {
        $meta_query[] = array(
            'key'     => 'cpt-client__email',
            'value'   => sanitize_text_field( $params['email'] ),
            'compare' => 'LIKE',
        );
    }

    if ( ! empty( $params['tipo-cliente'] ) ) {
        $meta_query[] = array(
            'key'     => 'cpt-client__type-client',
            'value'   => sanitize_text_field( $params['tipo-cliente'] ),
            'compare' => '=',
        );
    }

    $args = array(
        'post_type'      => 'cpt-clients',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'paged'          => $paged,
    );

    if ( count( $meta_query ) > 1 ) {
        $args['meta_query'] = $meta_query;
    }

    return $args;
}

// ─── Buscador de clientes asincrono (AJAX) ───────────────────────────────────
add_action( 'wp_ajax_av_ajax_filter_clients', 'av_ajax_filter_clients' );
function av_ajax_filter_clients() {

    check_ajax_referer( 'av_clients_filter_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
    }

    $paged   = isset( $_REQUEST['paged'] ) ? max( 1, intval( $_REQUEST['paged'] ) ) : 1;
    $args    = av_build_clients_query_args( $_REQUEST, $paged );

    $posts   = new WP_Query( $args );
    $clients = $posts->posts;

    // Base correcta para los enlaces de paginacion (get_pagenum_link() no es
    // fiable dentro de admin-ajax.php, aqui no hay un "post actual").
    $clients_page_posts = get_posts(array(
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-clients.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
    $clients_page_url = ! empty( $clients_page_posts ) ? get_permalink( $clients_page_posts[0] ) : home_url( '/' );
    $public_params = $_REQUEST;
    unset( $public_params['action'], $public_params['nonce'], $public_params['paged'] );
    $clients_page_url = add_query_arg( $public_params, $clients_page_url );

    ob_start();
    include locate_template( 'components/c-list-cpt-clients-count.php' );
    $count_html = ob_get_clean();

    ob_start();
    include locate_template( 'components/c-list-cpt-clients-list.php' );
    $list_html = ob_get_clean();

    wp_send_json_success( array(
        'count' => $count_html,
        'list'  => $list_html,
    ) );
}

// ─── Filtro de facturas ──────────────────────────────────────────────────────
// Compartido por la carga normal de la pagina (template-facturas.php) y el
// buscador asincrono (av_ajax_filter_facturas).
function av_build_facturas_query_args( array $params, $paged = 1 ) {

    $meta_query = array( 'relation' => 'AND' );

    if ( ! empty( $params['numero-factura'] ) ) {
        $meta_query[] = array(
            'key'     => '_factura_numero',
            'value'   => sanitize_text_field( $params['numero-factura'] ),
            'compare' => 'LIKE',
        );
    }

    if ( ! empty( $params['nombre-cliente'] ) ) {
        $meta_query[] = array(
            'key'     => '_factura_cliente',
            'value'   => sanitize_text_field( $params['nombre-cliente'] ),
            'compare' => 'LIKE',
        );
    }

    if ( ! empty( $params['numero-sat'] ) ) {
        $meta_query[] = array(
            'key'     => '_factura_sat_num',
            'value'   => sanitize_text_field( $params['numero-sat'] ),
            'compare' => 'LIKE',
        );
    }

    // Pagada = tiene metodo de pago registrado, que es lo que rellena el SAT al
    // finalizarse. Misma regla con la que se pinta el badge del listado.
    $estado_pago = sanitize_text_field( $params['estado-pago'] ?? '' );

    if ( $estado_pago === 'pagadas' ) {
        $meta_query[] = array(
            'key'     => '_factura_forma_pago',
            'value'   => '',
            'compare' => '!=',
        );
    } elseif ( $estado_pago === 'pendientes' ) {
        $meta_query[] = array(
            'relation' => 'OR',
            array( 'key' => '_factura_forma_pago', 'compare' => 'NOT EXISTS' ),
            array( 'key' => '_factura_forma_pago', 'value' => '', 'compare' => '=' ),
        );
    }

    $args = array(
        'post_type'      => 'cpt-facturas',
        'post_status'    => 'publish',
        'posts_per_page' => 25,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'paged'          => $paged,
    );

    if ( count( $meta_query ) > 1 ) {
        $args['meta_query'] = $meta_query;
    }

    // El rango se filtra por la fecha de creacion del post: _factura_fecha se
    // guarda como texto (d/m/Y H:i) y no permite comparar rangos de forma fiable.
    $date_query = array();

    if ( ! empty( $params['fecha-desde'] ) ) {
        $date_query[] = array( 'after' => sanitize_text_field( $params['fecha-desde'] ), 'inclusive' => true );
    }

    if ( ! empty( $params['fecha-hasta'] ) ) {
        $date_query[] = array( 'before' => sanitize_text_field( $params['fecha-hasta'] ) . ' 23:59:59', 'inclusive' => true );
    }

    if ( ! empty( $date_query ) ) {
        $args['date_query'] = $date_query;
    }

    return $args;
}

add_action( 'wp_ajax_av_ajax_filter_facturas', 'av_ajax_filter_facturas' );
function av_ajax_filter_facturas() {

    check_ajax_referer( 'av_facturas_filter_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error();
    }

    $paged    = isset( $_REQUEST['paged'] ) ? max( 1, intval( $_REQUEST['paged'] ) ) : 1;
    $args     = av_build_facturas_query_args( $_REQUEST, $paged );

    $query    = new WP_Query( $args );
    $facturas = $query->posts;

    // Base correcta para los enlaces de paginacion (get_pagenum_link() no es
    // fiable dentro de admin-ajax.php, aqui no hay un "post actual").
    $facturas_page_posts = get_posts(array(
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-facturas.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
    $facturas_page_url = ! empty( $facturas_page_posts ) ? get_permalink( $facturas_page_posts[0] ) : home_url( '/' );
    $public_params = $_REQUEST;
    unset( $public_params['action'], $public_params['nonce'], $public_params['paged'] );
    $facturas_page_url = add_query_arg( $public_params, $facturas_page_url );

    ob_start();
    include locate_template( 'components/c-list-facturas-count.php' );
    $count_html = ob_get_clean();

    ob_start();
    include locate_template( 'components/c-list-facturas-list.php' );
    $list_html = ob_get_clean();

    wp_send_json_success( array(
        'count' => $count_html,
        'list'  => $list_html,
    ) );
}

// ─── Gestión de técnicos (usuarios de WordPress) ────────────────────────────

function av_usuarios_page_url() {
    $usuarios_page = get_posts(array(
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-usuarios.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
    return ! empty( $usuarios_page ) ? get_permalink( $usuarios_page[0] ) : home_url( '/' );
}

// Un técnico deshabilitado no puede iniciar sesión, pero se conserva su
// usuario y todo lo que ya tiene asociado (SATs atendidos, facturas...).
function av_user_is_disabled( $user_id ) {
    return get_user_meta( $user_id, 'av_user_disabled', true ) === '1';
}

add_filter( 'wp_authenticate_user', 'av_block_disabled_user_login', 10, 2 );
function av_block_disabled_user_login( $user, $password ) {
    if ( is_wp_error( $user ) ) return $user;
    if ( av_user_is_disabled( $user->ID ) ) {
        return new WP_Error( 'av_user_disabled', 'Este usuario ha sido deshabilitado. Contacta con un administrador.' );
    }
    return $user;
}

add_action( 'admin_post_guardar_usuario', 'av_guardar_usuario' );
function av_guardar_usuario() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    check_admin_referer( 'av_crear_usuario_nonce', 'nonce' );

    $redirect_to = av_usuarios_page_url();

    $editing_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

    $nombre    = isset( $_POST['nombre'] )  ? sanitize_text_field( $_POST['nombre'] ) : '';
    $login     = isset( $_POST['usuario'] ) ? sanitize_user( $_POST['usuario'] )      : '';
    $email_raw = isset( $_POST['email'] )   ? trim( (string) $_POST['email'] )        : '';
    $pass      = isset( $_POST['password'] ) ? (string) $_POST['password']           : '';
    $rol       = isset( $_POST['rol'] ) && $_POST['rol'] === 'administrator' ? 'administrator' : 'editor';

    if ( $nombre === '' || ( ! $editing_id && $login === '' ) ) {
        wp_redirect( add_query_arg( 'error', 'missing', $redirect_to ) );
        exit;
    }

    // Validar el email tal cual se ha escrito: sanitize_email() vacia
    // cualquier cadena sin "@", lo que ocultaria un email mal escrito en
    // vez de avisar al usuario.
    if ( $email_raw !== '' && ! is_email( $email_raw ) ) {
        wp_redirect( add_query_arg( 'error', 'bad_email', $redirect_to ) );
        exit;
    }

    $email = $email_raw !== '' ? sanitize_email( $email_raw ) : '';

    if ( $editing_id ) {

        $existing = get_userdata( $editing_id );
        if ( ! $existing ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        $email_owner_id = $email !== '' ? email_exists( $email ) : false;
        if ( $email_owner_id && (int) $email_owner_id !== $editing_id ) {
            wp_redirect( add_query_arg( 'error', 'email_exists', $redirect_to ) );
            exit;
        }

        $update_data = array(
            'ID'           => $editing_id,
            'display_name' => $nombre,
        );

        // Igual que la contraseña: si se deja en blanco, no se toca el email
        // actual (evita borrarlo por accidente al editar).
        if ( $email !== '' ) {
            $update_data['user_email'] = $email;
        }

        // No permitir que un administrador se quite a si mismo el rol de
        // administrador desde este formulario (evita quedarse sin acceso a
        // esta misma pantalla por error).
        if ( $editing_id !== get_current_user_id() ) {
            $update_data['role'] = $rol;
        }

        if ( $pass !== '' ) {
            $update_data['user_pass'] = $pass;
        }

        $user_id = wp_update_user( $update_data );

        if ( is_wp_error( $user_id ) ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        wp_redirect( add_query_arg( 'actualizado', $user_id, $redirect_to ) );
        exit;
    }

    if ( username_exists( $login ) ) {
        wp_redirect( add_query_arg( 'error', 'user_exists', $redirect_to ) );
        exit;
    }

    if ( $email !== '' && email_exists( $email ) ) {
        wp_redirect( add_query_arg( 'error', 'email_exists', $redirect_to ) );
        exit;
    }

    $auto_generated = false;
    if ( $pass === '' ) {
        $pass = wp_generate_password( 12 );
        $auto_generated = true;
    }

    $user_id = wp_insert_user( array(
        'user_login'   => $login,
        'user_pass'    => $pass,
        'user_email'   => $email,
        'display_name' => $nombre,
        'role'         => $rol,
    ) );

    if ( is_wp_error( $user_id ) ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    if ( $auto_generated ) {
        set_transient( 'av_new_user_pass_' . get_current_user_id(), array(
            'login' => $login,
            'pass'  => $pass,
        ), 60 );
    }

    wp_redirect( add_query_arg( 'creado', $user_id, $redirect_to ) );
    exit;
}

// Deshabilitar / volver a habilitar un técnico. No se puede tocar la propia
// cuenta (te dejaría sin acceso a esta misma pantalla).
add_action( 'admin_post_av_toggle_usuario_estado', 'av_toggle_usuario_estado' );
function av_toggle_usuario_estado() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $user_id     = intval( $_GET['id'] ?? 0 );
    $redirect_to = av_usuarios_page_url();

    check_admin_referer( 'av_toggle_usuario_' . $user_id );

    $target = get_userdata( $user_id );
    if ( ! $target || ! array_intersect( [ 'administrator', 'editor' ], $target->roles ) ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    if ( $user_id === get_current_user_id() ) {
        wp_redirect( add_query_arg( 'error', 'self_action', $redirect_to ) );
        exit;
    }

    $disabled = av_user_is_disabled( $user_id );

    if ( $disabled ) {
        delete_user_meta( $user_id, 'av_user_disabled' );
    } else {
        update_user_meta( $user_id, 'av_user_disabled', '1' );
        // Cierra cualquier sesión activa de ese usuario al deshabilitarlo.
        $sessions = WP_Session_Tokens::get_instance( $user_id );
        $sessions->destroy_all();
    }

    wp_redirect( add_query_arg( $disabled ? 'habilitado' : 'deshabilitado', $user_id, $redirect_to ) );
    exit;
}

// Eliminar un técnico. No borra lo que ya haya generado (SATs, facturas...),
// solo la cuenta de usuario: "Atendido por" queda con el nombre guardado en
// su momento, que es un texto suelto, no una relación con el usuario.
add_action( 'admin_post_av_eliminar_usuario', 'av_eliminar_usuario' );
function av_eliminar_usuario() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $user_id     = intval( $_GET['id'] ?? 0 );
    $redirect_to = av_usuarios_page_url();

    check_admin_referer( 'av_eliminar_usuario_' . $user_id );

    $target = get_userdata( $user_id );
    if ( ! $target || ! array_intersect( [ 'administrator', 'editor' ], $target->roles ) ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    if ( $user_id === get_current_user_id() ) {
        wp_redirect( add_query_arg( 'error', 'self_action', $redirect_to ) );
        exit;
    }

    // Nunca te quedes sin ningún administrador.
    if ( in_array( 'administrator', $target->roles, true ) ) {
        $admins = get_users( [ 'role' => 'administrator', 'fields' => 'ID' ] );
        if ( count( $admins ) <= 1 ) {
            wp_redirect( add_query_arg( 'error', 'last_admin', $redirect_to ) );
            exit;
        }
    }

    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user( $user_id );

    wp_redirect( add_query_arg( 'eliminado', '1', $redirect_to ) );
    exit;
}

// ─── CPT Servicios (mano de obra con su precio) ─────────────────────────────
add_action( 'init', 'av_register_cpt_servicios' );
function av_register_cpt_servicios() {
    register_post_type( 'cpt-servicios', [
        'labels' => [
            'name'               => 'Servicios',
            'singular_name'      => 'Servicio',
            'menu_name'          => 'Servicios',
            'all_items'          => 'Todos los servicios',
            'edit_item'          => 'Editar servicio',
            'view_item'          => 'Ver servicio',
            'search_items'       => 'Buscar servicios',
            'not_found'          => 'No se encontraron servicios',
            'not_found_in_trash' => 'No hay servicios en la papelera',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'menu_position'     => 26,
        'menu_icon'         => 'dashicons-hammer',
        'supports'          => [ 'title' ],
        'capabilities'      => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'      => true,
    ] );
}

function av_servicios_page_url() {
    $servicios_page = get_posts(array(
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-servicios.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
    return ! empty( $servicios_page ) ? get_permalink( $servicios_page[0] ) : home_url( '/' );
}

// Alta / edición de un servicio. Mismo action para los dos casos: si llega
// "id" se actualiza, si no se crea.
add_action( 'admin_post_av_guardar_servicio', 'av_guardar_servicio' );
function av_guardar_servicio() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    check_admin_referer( 'av_servicio_nonce', 'nonce' );

    $redirect_to = av_servicios_page_url();

    $editing_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    $titulo     = isset( $_POST['titulo'] ) ? sanitize_text_field( $_POST['titulo'] ) : '';
    $precio_raw = isset( $_POST['precio'] ) ? trim( (string) $_POST['precio'] ) : '';
    $precio_raw = str_replace( ',', '.', $precio_raw );

    if ( $titulo === '' || $precio_raw === '' || ! is_numeric( $precio_raw ) || floatval( $precio_raw ) < 0 ) {
        wp_redirect( add_query_arg( 'error', 'missing', $redirect_to ) );
        exit;
    }

    $precio = round( floatval( $precio_raw ), 2 );

    if ( $editing_id ) {

        if ( get_post_type( $editing_id ) !== 'cpt-servicios' ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        wp_update_post( [
            'ID'         => $editing_id,
            'post_title' => $titulo,
        ] );
        update_post_meta( $editing_id, '_servicio_precio', $precio );

        wp_redirect( add_query_arg( 'actualizado', $editing_id, $redirect_to ) );
        exit;
    }

    $nuevo_id = wp_insert_post( [
        'post_type'   => 'cpt-servicios',
        'post_title'  => $titulo,
        'post_status' => 'publish',
    ] );

    if ( is_wp_error( $nuevo_id ) || ! $nuevo_id ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    update_post_meta( $nuevo_id, '_servicio_precio', $precio );

    wp_redirect( add_query_arg( 'creado', $nuevo_id, $redirect_to ) );
    exit;
}

add_action( 'admin_post_av_eliminar_servicio', 'av_eliminar_servicio' );
function av_eliminar_servicio() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $servicio_id = intval( $_GET['id'] ?? 0 );
    $redirect_to = av_servicios_page_url();

    check_admin_referer( 'av_eliminar_servicio_' . $servicio_id );

    if ( get_post_type( $servicio_id ) !== 'cpt-servicios' ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    wp_delete_post( $servicio_id, true );

    wp_redirect( add_query_arg( 'eliminado', '1', $redirect_to ) );
    exit;
}

// ─── CPT Dispositivos + taxonomía "Tipo de dispositivo" ─────────────────────
// Catálogo de modelos de equipo (no confundir con los SATs, que son las
// reparaciones): sirve como ficha de referencia con sus datos técnicos.

add_action( 'init', 'av_register_cpt_device' );
function av_register_cpt_device() {
    register_post_type( 'cpt-device', [
        'labels' => [
            'name'               => 'Dispositivos',
            'singular_name'      => 'Dispositivo',
            'menu_name'          => 'Dispositivos',
            'all_items'          => 'Todos los dispositivos',
            'edit_item'          => 'Editar dispositivo',
            'view_item'          => 'Ver dispositivo',
            'search_items'       => 'Buscar dispositivos',
            'not_found'          => 'No se encontraron dispositivos',
            'not_found_in_trash' => 'No hay dispositivos en la papelera',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'menu_position'     => 27,
        'menu_icon'         => 'dashicons-tablet',
        'supports'          => [ 'title' ],
        'taxonomies'        => [ 'cpt-device-category' ],
        'capabilities'      => [
            // El alta se hace siempre desde la app (con sus validaciones y
            // categoría dinámica), nunca desde el "Añadir nuevo" de wp-admin.
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'      => true,
    ] );
}

add_action( 'init', 'av_register_device_category_taxonomy' );
function av_register_device_category_taxonomy() {
    register_taxonomy( 'cpt-device-category', [ 'cpt-device' ], [
        'labels' => [
            'name'          => 'Tipos de dispositivo',
            'singular_name' => 'Tipo de dispositivo',
            'menu_name'     => 'Tipos de dispositivo',
            'search_items'  => 'Buscar tipos',
            'all_items'     => 'Todos los tipos',
            'edit_item'     => 'Editar tipo de dispositivo',
            'update_item'   => 'Actualizar tipo',
            'add_new_item'  => 'Añadir tipo de dispositivo',
            'new_item_name' => 'Nombre del tipo nuevo',
        ],
        'hierarchical'      => true,
        'public'            => false,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => false,
        'show_in_rest'      => false,
        'show_admin_column' => true,
    ] );
}

// Nombres de las categorías de catálogo (sin emoji: el icono ya lo pone el
// menú lateral con SVG, no hace falta repetirlo aquí). Slug => nombre.
function av_device_category_seed_names() {
    return [
        'movil'      => 'Móvil',
        'tablet'     => 'Tablet',
        'portatil'   => 'Portátil',
        'sobremesa'  => 'Sobremesa',
        'consola'    => 'Consola',
        'impresora'  => 'Impresora',
        'smartwatch' => 'Smartwatch',
        'monitor'    => 'Monitor',
        'redes-nas'  => 'Redes / NAS',
        'otros'      => 'Otros',
    ];
}

// Categorías con las que arranca el catálogo. Se crean una sola vez (si el
// administrador borra alguna después, no vuelve a aparecer sola).
add_action( 'init', 'av_seed_device_categories', 20 );
function av_seed_device_categories() {

    if ( get_option( 'av_device_categories_seeded' ) ) return;

    $order = 0;
    foreach ( av_device_category_seed_names() as $slug => $nombre ) {
        $order++;
        if ( ! term_exists( $slug, 'cpt-device-category' ) ) {
            $result = wp_insert_term( $nombre, 'cpt-device-category', [ 'slug' => $slug ] );
            if ( ! is_wp_error( $result ) ) {
                update_term_meta( $result['term_id'], 'av_order', $order );
            }
        }
    }

    update_option( 'av_device_categories_seeded', '1', false );
}

// Orden manual de las categorías de dispositivo (meta "av_order"). Se usa
// tanto en su propio listado como en el <select> del formulario de
// dispositivo, para que un cambio de orden se refleje en los dos sitios.
function av_get_device_categories() {
    $categorias = get_terms( [
        'taxonomy'   => 'cpt-device-category',
        'hide_empty' => false,
        'meta_key'   => 'av_order',
        'orderby'    => 'meta_value_num',
        'order'      => 'ASC',
    ] );
    return is_wp_error( $categorias ) ? [] : $categorias;
}

// Siguiente hueco libre al final del orden, para las categorías que se crean
// nuevas (tanto desde su página como desde el alta rápida en el dispositivo).
function av_device_category_next_order() {
    global $wpdb;
    $max = $wpdb->get_var( "SELECT MAX(CAST(meta_value AS SIGNED)) FROM {$wpdb->termmeta} WHERE meta_key = 'av_order'" );
    return $max !== null ? intval( $max ) + 1 : 1;
}

// Relleno de "av_order" para categorías que ya existían antes de añadir el
// orden manual (p.ej. las sembradas antes de este cambio): sin esto,
// get_terms() con meta_key las excluiría directamente de las listas.
add_action( 'init', 'av_backfill_device_category_order', 21 );
function av_backfill_device_category_order() {

    if ( get_option( 'av_device_categories_order_backfilled' ) ) return;

    $sin_orden = get_terms( [
        'taxonomy'   => 'cpt-device-category',
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
        'meta_query' => [
            [ 'key' => 'av_order', 'compare' => 'NOT EXISTS' ],
        ],
    ] );

    if ( ! is_wp_error( $sin_orden ) ) {
        $order = av_device_category_next_order() - 1;
        foreach ( $sin_orden as $term ) {
            $order++;
            update_term_meta( $term->term_id, 'av_order', $order );
        }
    }

    update_option( 'av_device_categories_order_backfilled', '1', false );
}

// Quita el emoji con el que se sembraron las categorías al principio (el
// icono ya lo pone el menú lateral, no hace falta repetirlo en el nombre).
// Solo renombra si el nombre sigue siendo exactamente el sembrado original:
// si el administrador ya la había renombrado a mano, no se toca.
add_action( 'init', 'av_migrate_device_category_names', 22 );
function av_migrate_device_category_names() {

    if ( get_option( 'av_device_categories_names_migrated' ) ) return;

    // Se identifican por slug (estable, sin acentos ni emoji) en vez de por el
    // nombre viejo exacto: el nombre es justo lo que se está corrigiendo, así
    // que compararlo primero sería frágil.
    foreach ( av_device_category_seed_names() as $slug => $nombre_limpio ) {
        $term = get_term_by( 'slug', $slug, 'cpt-device-category' );
        if ( ! $term ) continue;
        if ( $term->name !== $nombre_limpio ) {
            wp_update_term( $term->term_id, 'cpt-device-category', [ 'name' => $nombre_limpio ] );
        }
    }

    update_option( 'av_device_categories_names_migrated', '1', false );
}

function av_devices_page_url() {
    $devices_page = get_posts(array(
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-devices.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
    return ! empty( $devices_page ) ? get_permalink( $devices_page[0] ) : home_url( '/' );
}

// Campos técnicos según la categoría (por slug). Todo lo que no tenga un
// grupo específico cae en el genérico de "Notas técnicas" en texto libre.
function av_device_field_groups() {
    return [
        'movil' => [
            [ 'name' => 'screen-size',       'label' => 'Tamaño de pantalla',    'placeholder' => 'Ej. 6,7"' ],
            [ 'name' => 'screen-type',       'label' => 'Tipo de pantalla',      'placeholder' => 'Ej. AMOLED' ],
            [ 'name' => 'battery-capacity',  'label' => 'Capacidad de batería',  'placeholder' => 'Ej. 4422 mAh' ],
            [ 'name' => 'connector',         'label' => 'Conector',              'placeholder' => 'Ej. USB-C' ],
            [ 'name' => 'storage',           'label' => 'Almacenamiento',        'placeholder' => 'Ej. 256 GB' ],
            [ 'name' => 'ram',               'label' => 'RAM',                   'placeholder' => 'Ej. 8 GB' ],
            [ 'name' => '5g',                'label' => '5G',                    'type' => 'checkbox' ],
            [ 'name' => 'dual-sim',          'label' => 'Dual SIM',              'type' => 'checkbox' ],
        ],
        'portatil' => [
            [ 'name' => 'cpu',               'label' => 'Procesador',            'placeholder' => 'Ej. Intel Core i7-1355U' ],
            [ 'name' => 'ram',               'label' => 'RAM',                   'placeholder' => 'Ej. 16 GB' ],
            [ 'name' => 'storage',           'label' => 'Almacenamiento',        'placeholder' => 'Ej. 512 GB' ],
            [ 'name' => 'storage-type',      'label' => 'Tipo de almacenamiento','placeholder' => 'Ej. SSD NVMe' ],
            [ 'name' => 'screen-size',       'label' => 'Tamaño de pantalla',    'placeholder' => 'Ej. 15,6"' ],
            [ 'name' => 'resolution',        'label' => 'Resolución',            'placeholder' => 'Ej. 1920x1080' ],
            [ 'name' => 'battery',           'label' => 'Batería',               'placeholder' => 'Ej. 57 Wh' ],
            [ 'name' => 'os',                'label' => 'Sistema operativo',     'placeholder' => 'Ej. Windows 11' ],
        ],
    ];
}

// Grupo que le corresponde a una categoría por su slug; '' si no tiene uno
// específico (usará el genérico de notas libres).
function av_device_field_group_for( $category_slug ) {
    $groups = av_device_field_groups();
    return $groups[ $category_slug ] ?? [];
}

// Alta / edición de un dispositivo. Mismo action para los dos casos: si llega
// "id" se actualiza, si no se crea.
add_action( 'admin_post_av_guardar_device', 'av_guardar_device' );
function av_guardar_device() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    check_admin_referer( 'av_device_nonce', 'nonce' );

    $redirect_to = av_devices_page_url();

    $editing_id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    $nombre       = isset( $_POST['nombre'] )       ? sanitize_text_field( $_POST['nombre'] )       : '';
    $categoria_id = isset( $_POST['categoria'] )    ? intval( $_POST['categoria'] )                 : 0;
    $marca        = isset( $_POST['marca'] )        ? sanitize_text_field( $_POST['marca'] )        : '';
    $modelo       = isset( $_POST['modelo'] )       ? sanitize_text_field( $_POST['modelo'] )       : '';
    $referencia   = isset( $_POST['referencia'] )   ? sanitize_text_field( $_POST['referencia'] )   : '';
    $fabricante   = isset( $_POST['fabricante'] )   ? sanitize_text_field( $_POST['fabricante'] )   : '';

    $categoria_term = $categoria_id ? get_term( $categoria_id, 'cpt-device-category' ) : null;

    if ( $nombre === '' || ! $categoria_term || is_wp_error( $categoria_term ) ) {
        wp_redirect( add_query_arg( 'error', 'missing', $redirect_to ) );
        exit;
    }

    // Campos técnicos: solo se guardan los que pertenecen al grupo de la
    // categoría elegida (si llegara basura de otro grupo por manipular el
    // formulario, se ignora).
    $field_group = av_device_field_group_for( $categoria_term->slug );
    $specs = [];
    if ( ! empty( $field_group ) ) {
        foreach ( $field_group as $field ) {
            $key = $field['name'];
            if ( ( $field['type'] ?? 'text' ) === 'checkbox' ) {
                $specs[ $key ] = ! empty( $_POST['spec'][ $key ] ) ? '1' : '';
            } else {
                $specs[ $key ] = isset( $_POST['spec'][ $key ] ) ? sanitize_text_field( $_POST['spec'][ $key ] ) : '';
            }
        }
    } else {
        $specs['notes'] = isset( $_POST['spec']['notes'] ) ? sanitize_textarea_field( $_POST['spec']['notes'] ) : '';
    }

    $meta_input = [
        'cpt-device__brand'       => $marca,
        'cpt-device__model'       => $modelo,
        'cpt-device__reference'   => $referencia,
        'cpt-device__manufacturer'=> $fabricante,
        'cpt-device__specs'       => wp_json_encode( $specs, JSON_UNESCAPED_UNICODE ),
    ];

    if ( $editing_id ) {

        if ( get_post_type( $editing_id ) !== 'cpt-device' ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        wp_update_post( [
            'ID'         => $editing_id,
            'post_title' => $nombre,
            'meta_input' => $meta_input,
        ] );
        wp_set_object_terms( $editing_id, [ $categoria_term->term_id ], 'cpt-device-category', false );

        wp_redirect( add_query_arg( 'actualizado', $editing_id, $redirect_to ) );
        exit;
    }

    $nuevo_id = wp_insert_post( [
        'post_type'   => 'cpt-device',
        'post_title'  => $nombre,
        'post_status' => 'publish',
        'meta_input'  => $meta_input,
    ] );

    if ( is_wp_error( $nuevo_id ) || ! $nuevo_id ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    wp_set_object_terms( $nuevo_id, [ $categoria_term->term_id ], 'cpt-device-category', false );

    wp_redirect( add_query_arg( 'creado', $nuevo_id, $redirect_to ) );
    exit;
}

add_action( 'admin_post_av_eliminar_device', 'av_eliminar_device' );
function av_eliminar_device() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $device_id   = intval( $_GET['id'] ?? 0 );
    $redirect_to = av_devices_page_url();

    check_admin_referer( 'av_eliminar_device_' . $device_id );

    if ( get_post_type( $device_id ) !== 'cpt-device' ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    wp_delete_post( $device_id, true );

    wp_redirect( add_query_arg( 'eliminado', '1', $redirect_to ) );
    exit;
}

// Alta rápida de una categoría de dispositivo desde el propio formulario
// (botón "+" junto al selector de categoría), sin salir de la modal.
add_action( 'wp_ajax_av_ajax_crear_categoria_device', 'av_ajax_crear_categoria_device' );
function av_ajax_crear_categoria_device() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_send_json_error( 'Acceso denegado', 403 );
    }

    check_ajax_referer( 'av_device_nonce', 'nonce' );

    $nombre = isset( $_POST['nombre'] ) ? sanitize_text_field( $_POST['nombre'] ) : '';
    if ( $nombre === '' ) {
        wp_send_json_error( 'Indica un nombre para la categoría.', 400 );
    }

    if ( term_exists( $nombre, 'cpt-device-category' ) ) {
        wp_send_json_error( 'Ya existe una categoría con ese nombre.', 400 );
    }

    $result = wp_insert_term( $nombre, 'cpt-device-category' );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message(), 400 );
    }

    // Se añade al final del orden actual, como el resto de altas
    update_term_meta( $result['term_id'], 'av_order', av_device_category_next_order() );

    wp_send_json_success( [
        'id'   => $result['term_id'],
        'name' => $nombre,
    ] );
}

// ─── Gestión de categorías de dispositivo (página propia) ──────────────────
// El alta rápida de arriba sirve para no salir del formulario de un
// dispositivo; esta pantalla es para poder también renombrarlas y borrarlas.

function av_device_categories_page_url() {
    $page = get_posts(array(
        'post_type'   => 'page',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'templates/template-device-categories.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
    return ! empty( $page ) ? get_permalink( $page[0] ) : home_url( '/' );
}

add_action( 'admin_post_av_guardar_device_category', 'av_guardar_device_category' );
function av_guardar_device_category() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    check_admin_referer( 'av_device_category_nonce', 'nonce' );

    $redirect_to = av_device_categories_page_url();

    $editing_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    $nombre     = isset( $_POST['nombre'] ) ? sanitize_text_field( $_POST['nombre'] ) : '';

    // Si falla la validación de una edición, el error tiene que volver a
    // abrir la modal sobre ESA categoría (no una "Nueva categoría" en blanco).
    if ( $editing_id ) {
        $redirect_to = add_query_arg( 'edit', $editing_id, $redirect_to );
    }

    if ( $nombre === '' ) {
        wp_redirect( add_query_arg( 'error', 'missing', $redirect_to ) );
        exit;
    }

    if ( $editing_id ) {

        $term = get_term( $editing_id, 'cpt-device-category' );
        if ( ! $term || is_wp_error( $term ) ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        // Por id, no por texto: MySQL trata acentos/mayúsculas como
        // equivalentes ("Portátil" = "portatil"), así que comparar el texto
        // tal cual llevaría a que corregir un acento o una mayúscula la
        // marcase como "ya existe" (chocando consigo misma).
        $existing = term_exists( $nombre, 'cpt-device-category' );
        $existing_id = is_array( $existing ) ? intval( $existing['term_id'] ) : 0;
        if ( $existing_id && $existing_id !== $editing_id ) {
            wp_redirect( add_query_arg( 'error', 'exists', $redirect_to ) );
            exit;
        }

        $result = wp_update_term( $editing_id, 'cpt-device-category', [ 'name' => $nombre ] );
        if ( is_wp_error( $result ) ) {
            wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
            exit;
        }

        wp_redirect( add_query_arg( 'actualizado', $editing_id, $redirect_to ) );
        exit;
    }

    if ( term_exists( $nombre, 'cpt-device-category' ) ) {
        wp_redirect( add_query_arg( 'error', 'exists', $redirect_to ) );
        exit;
    }

    $result = wp_insert_term( $nombre, 'cpt-device-category' );
    if ( is_wp_error( $result ) ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    update_term_meta( $result['term_id'], 'av_order', av_device_category_next_order() );

    wp_redirect( add_query_arg( 'creado', $result['term_id'], $redirect_to ) );
    exit;
}

// Reordenar categorías arrastrando y soltando: recibe el listado completo de
// term_id en el orden final (tal como queda la tabla tras soltar) y reasigna
// "av_order" 1..N en ese mismo orden. Es AJAX para que el arrastre no
// necesite recargar la página cada vez.
add_action( 'wp_ajax_av_ajax_reordenar_device_categories', 'av_ajax_reordenar_device_categories' );
function av_ajax_reordenar_device_categories() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_send_json_error( 'Acceso denegado', 403 );
    }

    check_ajax_referer( 'av_device_category_nonce', 'nonce' );

    $enviado = json_decode( wp_unslash( $_POST['order'] ?? '' ), true );
    if ( ! is_array( $enviado ) || empty( $enviado ) ) {
        wp_send_json_error( 'Orden no válido.', 400 );
    }
    $enviado = array_map( 'intval', $enviado );

    // Solo se aceptan los ids que realmente son categorías de dispositivo
    // ahora mismo, sin duplicados, y tiene que venir la lista completa (si
    // faltase alguna, dejaría huecos en el orden).
    $actuales = wp_list_pluck( av_get_device_categories(), 'term_id' );
    $validos  = array_values( array_unique( array_intersect( $enviado, $actuales ) ) );

    if ( count( $validos ) !== count( $actuales ) ) {
        wp_send_json_error( 'El orden recibido no coincide con las categorías actuales.', 400 );
    }

    foreach ( $validos as $posicion => $term_id ) {
        update_term_meta( $term_id, 'av_order', $posicion + 1 );
    }

    wp_send_json_success( [ 'order' => $validos ] );
}

add_action( 'admin_post_av_eliminar_device_category', 'av_eliminar_device_category' );
function av_eliminar_device_category() {

    if ( ! current_user_can( 'administrator' ) ) {
        wp_die( 'Acceso denegado.' );
    }

    $term_id     = intval( $_GET['id'] ?? 0 );
    $redirect_to = av_device_categories_page_url();

    check_admin_referer( 'av_eliminar_device_category_' . $term_id );

    $term = get_term( $term_id, 'cpt-device-category' );
    if ( ! $term || is_wp_error( $term ) ) {
        wp_redirect( add_query_arg( 'error', 'unknown', $redirect_to ) );
        exit;
    }

    // Los dispositivos que la tuvieran asignada se quedan sin categoría (no
    // se borran): al editarlos habrá que elegirles una nueva.
    wp_delete_term( $term_id, 'cpt-device-category' );

    wp_redirect( add_query_arg( 'eliminado', '1', $redirect_to ) );
    exit;
}

