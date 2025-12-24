<?php 
    $sat_id = get_the_ID();
    $type_equipment = get_field('cpt-sat__type-equipment', $sat_id);  
    $client_id = get_field('cpt-sat__client-id', $sat_id); 
    $client_name = get_the_title( $client_id );
    $attended = get_field('cpt-sat__attended', $sat_id); 
    $entry_date = get_field('cpt-sat__entry-date', $sat_id); 
    $name_other = get_field('cpt-sat__name-other', $sat_id); 
    $model = get_field('cpt-sat__model', $sat_id); 
    $serial = get_field('cpt-sat__model-imei', $sat_id); 
    $password = get_field('cpt-sat__password', $sat_id);
    $sim = get_field('cpt-sat__sim', $sat_id);
    $accesories = get_field('cpt-sat__accesories', $sat_id);
    $other_accesories = get_field('cpt-sat__other-accesories', $sat_id);
    $status = get_field('cpt-sat__physical-condition', $sat_id);
    $incident = get_field('cpt-sat__incident', $sat_id);
    $budget = get_field('cpt-sat__budget', $sat_id);
    $repair = get_field('cpt-sat__repair', $sat_id);
    $price = get_field('cpt-sat__price', $sat_id);
    $repair_date = get_field('cpt-sat__repair-date', $sat_id);

?>
<div class="c-single-cpt-themes">
    <div class="c-single-cpt-themes__inner">
        <div class="c-single-cpt-themes__container o-container">
            <div class="c-single-cpt-themes__col o-col-10@md o-col-push-1@md o-col-8@sm o-col-4@xs">
                <?php        
                    include( locate_template('components/c-sat-form.php') );
                ?>
            </div>   
        </div>
    </div>
</div>