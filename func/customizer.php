<?php

function av_customize_register( $wp_customize ) {

	// Add postMessage support for site title and description.
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
    $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
    
	av_register_social_control($wp_customize);
	av_register_theme_options_control($wp_customize);

}
add_action( 'customize_register', 'av_customize_register' );


function av_register_social_control($wp_customize){

}

function av_register_theme_options_control($wp_customize){

}
