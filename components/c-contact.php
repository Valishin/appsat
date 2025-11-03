<?php   

?>

<div class="c-contact">
    <div class="c-contact__inner">
        <div class="c-contact__container o-container">
            <div class="c-contact__col o-col-4@md o-col-4@sm o-col-4@xs">
                <div class="c-contact__box">
                    <div class="c-contact__wrapper-text">
                        <div class="c-contact__wrapper-title">
                            <div class="c-contact__title o-font-display-headline"><?php echo $c_contact__title; ?></div>                            
                        </div>
                        <div class="c-contact__wrapper-description">
                            <div class="c-contact__description"><?php echo $c_contact__description; ?></div>                            
                        </div>
                        <div class="c-contact__wrapper-map">

                        </div>
                    </div>
                </div>
            </div>
            <div class="c-contact__col o-col-8@md o-col-4@sm o-col-4@xs">
                <div class="c-contact__wrapper-form">
                    <?php echo do_shortcode($c_contact__shortcode); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- <div class="c-contact__form">
        <div class="c-contact__wrapper-content">
            <div class="c-contact__wrapper-input c-contact__input--text">
                <p class="c-contact__label">NOMBRE</p> 
                [text* your-name]
            </div>
            <div class="c-contact__wrapper-input c-contact__input--email">
                <p class="c-contact__label">CORREO</p> 
                [email* your-email]
            </div>
            <div class="c-contact__wrapper-input c-contact__input--checkbox">
                <p class="c-contact__label">SERVICIO</p>           
                [checkbox* secciones use_label_element "Diseño web" "Desarrollo" "Identidad de marca" "Hosting" "Otro"]
            </div>
            <div class="c-contact__wrapper-input c-contact__input--text-area">
                <p class="c-contact__label">MENSAJE</p> 
                [textarea your-message placeholder "Message"]
            </div>
            <div class="c-contact__wrapper-button c-contact__input--submit">
                [submit class:c-contact__button "Enviar"]
            </div>
        </div>
    </div> -->
