<?php

the_post();
get_header();

$page_id = get_option( 'page_on_front' );
$home_id = defined( 'ICL_LANGUAGE_CODE' ) ? icl_object_id($page_id, 'page', false ) : $page_id;

$image_big = get_field('theme-settings-general__404-image-big', 'option');
$image_small = get_field('theme-settings-general__404-image-small', 'option');
$caption = get_field('theme-settings-general__404-caption', 'option');
$title = get_field('theme-settings-general__404-title', 'option');
$button_text = get_field('theme-settings-general__404-text-button', 'option');

// GLOBAL OPTIONS
$component_global_settings = av_component_get_component_global_settings_default();

// $component_global_settings = ["component-global-settings__color" => ["_colors__color" => 'black']];

// COMPONENT OPTIONS
$c_hero__overline  = $caption;
$c_hero__title     = $title;
$c_hero__cta       = array('url' => get_permalink($home_id), 'title' => $button_text, 'target' => '_self');
$c_hero__cta_icon  = null;
$c_hero__cta_style  = 'style-1';
$c_hero__big_image     = $image_big;
$c_hero__small_image     = $image_small;

$title_tag = 'h1';


?>

    <section class="o-main s-template-404">

        <?php include( locate_template('components/c-hero.php') ); ?>

    </section>

<?php

get_footer(); 

?>