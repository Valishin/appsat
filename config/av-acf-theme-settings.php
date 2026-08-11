<?php

// CUSTOM OPTIONS THEME
if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Theme Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> true
	));

	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'General',
		'parent_slug'	=> 'theme-settings',
	));		

	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme Footer Settings',
		'menu_title'	=> 'Footer',
		'parent_slug'	=> 'theme-settings',
	));

	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme Social Settings',
		'menu_title'	=> 'Social',
		'parent_slug'	=> 'theme-settings',
	));
	
}
