<?php

    $slug_flexible_content = 'components';
    
    if(!!$custom_id){
        $custom_id_end = $custom_id;
    }else{
        $custom_id_end = get_the_ID();
    }

    // $total_components = count(get_field($slug_flexible_content));

    // check if the flexible content field has rows of data
    if( have_rows($slug_flexible_content, $custom_id_end) ){

        $i = 1;
        $home_slug = 'home';
        $home_id = get_option('page_on_front');
        
        $GLOBALS['tag-h1-printed'] = false;

        // loop through the rows of data
        while ( have_rows($slug_flexible_content, $custom_id_end) ){

            the_row();

            $component_slug = get_row_layout();

            $component_global_settings = get_sub_field(SLUG_CGS);

            switch ($component_slug) {

                case 'c-hero':

                    $c_hero__title              = get_sub_field($component_slug . '__title');
                    $c_hero__description        = get_sub_field($component_slug . '__description');
                    $c_hero__text               = get_sub_field($component_slug . '__text');
                    $c_hero__cta                = get_sub_field($component_slug . '__cta');      
                    $c_hero__image              = get_sub_field($component_slug . '__image');     

                break;                             

                case 'c-image-text':

                    $c_image_text__position               = get_sub_field($component_slug . '__position');
                    $c_image_text__image                  = get_sub_field($component_slug . '__image');
                    $c_image_text__title                  = get_sub_field($component_slug . '__title');
                    $c_image_text__description            = get_sub_field($component_slug . '__description');
                    $c_image_text__cta                      = get_sub_field($component_slug . '__cta-1');
                    $c_image_text__cta_style                = get_sub_field($component_slug . '__cta-1-style');

                break;              

                case 'c-text':

                    $c_text__title                      = get_sub_field($component_slug . '__title');
                    $c_text__text                       = get_sub_field($component_slug . '__text');
                    $c_text__cta                        = get_sub_field($component_slug . '__cta-1');
                    $c_text__cta_style                  = get_sub_field($component_slug . '__cta-1-style');
                    $c_text__border                     = get_sub_field($component_slug . '__border');
                    $c_text__text_align                 = get_sub_field($component_slug . '__text-align');

                break;                                             

                case 'c-gallery':

                    $c_gallery__gallery               = get_sub_field($component_slug . '__gallery');
                    $c_gallery__title                 = get_sub_field($component_slug . '__title');

                break;

                case 'c-slider':

                    $c_slider__overline             = get_sub_field($component_slug . '__overline');
                    $c_slider__title                = get_sub_field($component_slug . '__title');
                    $c_slider__slider               = get_sub_field($component_slug . '__slider');

                break;

                case 'c-service':
                
                    $c_service__services            = get_sub_field($component_slug . '__services');

                break;

                case 'c-menu':

                    $c_menu__title             = get_sub_field($component_slug . '__title');
                    $c_menu__menu             = get_sub_field($component_slug . '__menu');

                break;

                case 'c-video':
                   
                    $c_video__video             = get_sub_field($component_slug . '__video');

                break;

                case 'c-image':
                   
                    $c_image__image             = get_sub_field($component_slug . '__image');

                break;

                case 'c-contact':
                   
                    $c_contact__shortcode           = get_sub_field($component_slug . '__shortcode');
                    $c_contact__title               = get_sub_field($component_slug . '__title');
                    $c_contact__description         = get_sub_field($component_slug . '__description');

                break;

                case 'c-two-image-text':

                    $c_two_image_text__overline     = get_sub_field($component_slug . '__overline');
                    $c_two_image_text__title        = get_sub_field($component_slug . '__title');
                    $c_two_image_text__image_left   = get_sub_field($component_slug . '__image-left');
                    $c_two_image_text__title_left   = get_sub_field($component_slug . '__title-left');
                    $c_two_image_text__text_left    = get_sub_field($component_slug . '__text-left');
                    $c_two_image_text__cta_left     = get_sub_field($component_slug . '__cta-left');
                    $c_two_image_text__image_right  = get_sub_field($component_slug . '__image-right');
                    $c_two_image_text__title_right  = get_sub_field($component_slug . '__title-right');
                    $c_two_image_text__text_right   = get_sub_field($component_slug . '__text-right');
                    $c_two_image_text__cta_right    = get_sub_field($component_slug . '__cta-right');

                default:
                    // DEFAULT
                break;

                
            } // END SWITCH

            include( locate_template('components/' . $component_slug . '.php') );

            $i++;
        
        }

    }

?>
