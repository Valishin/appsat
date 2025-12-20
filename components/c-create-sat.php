<?php
$disabled_all = '';
if ( isset( $_GET['id'] ) ) {
    $client_id = intval( $_GET['id'] ); // sanitiza por seguridad
    $titulo = get_the_title( $client_id );
}else{
    $disabled_all = 'disabled-all';
    $client_id = '';
}

?>
<div class="c-create-sat <?php echo esc_attr( $disabled_all ); ?>">
    <div class="c-create-sat__inner">
        <div class="c-create-sat__container o-container">            
            <div class="c-create-sat__col o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">
                <div class="c-create-sat__wrapper-form">
                    <?php        
                        include( locate_template('components/c-sat-form.php') );
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>