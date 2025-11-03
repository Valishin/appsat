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

    $roles = wp_get_current_user()->roles;

    if ( is_admin() && in_array('custom_role', $roles ) && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

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
        // Restrict admin page
        $current_role = restrictly_get_current_user_role();
		if ( ($current_role=='subscriber') && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
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
