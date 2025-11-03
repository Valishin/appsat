<?php

    if (! function_exists ( 'av_theme_setup' )) :
        function av_theme_setup() {
            
            // Load text domain
            load_theme_textdomain ( 'avali', get_template_directory () . '/languages' );
            // Add theme supports
            add_theme_support( 'automatic-feed-links' );
            add_theme_support( 'title-tag' );
            add_theme_support('post-thumbnails');
            
            /*
            * Switch default core markup for search form, comment form, and comments
            * to output valid HTML5.
            */
            add_theme_support( 'html5', array(
                    'search-form',
                    'comment-form',
                    'comment-list',
                    'gallery',
                    'caption',
            ) );
            
            /*
            * Enable support for Post Formats.
            * See https://developer.wordpress.org/themes/functionality/post-formats/
            */
            add_theme_support( 'post-formats', array(
                    'aside',
                    'image',
                    'video',
                    'quote',
                    'link',
            ) );
            
            add_theme_support('woocommerce');
            
            add_theme_support( 'wc-product-gallery-zoom' );
            add_theme_support( 'wc-product-gallery-lightbox' );
            add_theme_support( 'wc-product-gallery-slider' );
            
        }
    endif;
    add_action ( 'after_setup_theme', 'av_theme_setup' );

?>