<div class="c-client-choice <?= isset($post_id) ? 'modificar' : 'crear'; ?>">
    <div class="c-client-choice__inner">
        <div class="c-client-choice__container o-container">
            <div class="c-client-choice__col-form o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">
                <div class="c-client-choice__box-form">
                    <div class="c-client-choice__wrapper-form-text">
                        <div class="c-client-choice__wrapper-form-title">
                            <div class="c-client-choice__title-form o-font-display-headline"><?php echo $title; ?></div>                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="c-client-choice__col o-col-6@md o-col-push-3@md o-col-8@sm o-col-4@xs">
                <div class="c-client-choice__wrapper-type-client">                    
                    <div class="c-client-choice__type-client js-client-choice__type-client <?php echo $type_client === 'particular' || !isset($post_id) || !isset($type_client) ? 'is-active' : '' ?>" data-id="particular">
                        <p>Cliente Particular</p>
                    </div>
                    <div class="c-client-choice__type-client js-client-choice__type-client <?php echo $type_client === 'profesional' ? 'is-active' : '' ?>" data-id="profesional">
                        <p>Cliente profesional</p>
                    </div>
                </div>
            </div>
            <div class="c-client-choice__col o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">
                <?php        
                    include( locate_template('components/c-client-form.php') );
                ?>
            </div>
            <?php if(isset($post_id)){ ?>
            <div class="c-client-choice__col o-col-12@md o-col-8@sm o-col-4@xs">
                    <div class="c-client-choice__sat-title o-font-display-headline">Sats de <?php echo $name; ?></div>
                    <?php        
                        include( locate_template('components/c-list-cpt-sats.php') );
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>