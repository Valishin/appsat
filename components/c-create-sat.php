<?php
$client_id = ! empty( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

// Solo se considera "elegido" un cliente que realmente existe.
if ( $client_id && get_post_type( $client_id ) !== 'cpt-clients' ) {
    $client_id = 0;
}

if ( ! $client_id ) {
    // Sin cliente todavía: obliga a elegir uno (o crearlo) antes de ver el formulario del SAT.
    include( locate_template('components/c-sat-client-picker.php') );
    return;
}

$disabled_all = '';
$client_name  = get_field( 'cpt-client__name',      $client_id );
$client_dni   = get_field( 'cpt-client__dni',       $client_id );
$client_phone = get_field( 'cpt-client__extension', $client_id ) . ' ' . get_field( 'cpt-client__phone', $client_id );

// Variables SAT vacías para formulario de creación
$sat_id            = '';
$sat_id_visible    = '';
$attended          = '';
$type_equipment    = '';
$name_other        = '';
$model             = '';
$serial            = '';
$password          = '';
$sim               = '';
$accesories        = [];
$other_accesories  = '';
$physical_condition = '';
$other_equipment   = '';
$incident          = '';
$diagnostic        = '';
$budget            = '';
$repair            = '';
$ordered_parts     = '';
$price             = '';
$price_description = '';
$anticipo          = '';
$anticipo_payment  = '';
$repair_date       = '';
$delivery_date     = '';
$finalized_by      = '';
$entry_date        = date('d/m/Y H:i');
$estado            = 'diagnosticar';
$prioridad         = '';
$firma             = '';
$is_locked         = false;
$is_warranty       = false;
$warranty_note     = '';
$warranty_period   = '';
$warranty_seal     = '';
$warranty_seal_photo = '';
$physical_condition_photo = '';

// Duplicar SAT: copia los datos del equipo/cliente, pero deja en blanco lo propio de la incidencia.
$duplicate_id = ! empty( $_GET['duplicate'] ) ? intval( $_GET['duplicate'] ) : 0;
if ( $duplicate_id && get_post_type( $duplicate_id ) === 'cpt-sats' ) {
    // El botón "Garantía" duplica igual que "Duplicar SAT" pero sin precio ni tipo de pago.
    $is_warranty = ! empty( $_GET['warranty'] );
    $attended           = get_field( 'cpt-sat__attended',           $duplicate_id );
    $type_equipment     = get_field( 'cpt-sat__type-equipment',     $duplicate_id );
    $name_other         = get_field( 'cpt-sat__name-other',         $duplicate_id );
    $model              = get_field( 'cpt-sat__model',              $duplicate_id );
    $serial             = get_field( 'cpt-sat__model-imei',         $duplicate_id );
    $password           = get_field( 'cpt-sat__password',           $duplicate_id );
    $sim                = get_field( 'cpt-sat__pin-sim',            $duplicate_id );
    $accesories         = get_field( 'cpt-sat__accesories',         $duplicate_id ) ?: [];
    $other_accesories   = get_field( 'cpt-sat__other-accesories',   $duplicate_id );
    $physical_condition = get_field( 'cpt-sat__physical-condition', $duplicate_id );
    $other_equipment    = get_field( 'cpt-sat__other-equipment',    $duplicate_id );
    $repair             = get_field( 'cpt-sat__repair',             $duplicate_id );
    $ordered_parts      = get_field( 'cpt-sat__ordered-parts',      $duplicate_id );

    // Al duplicar para garantía se copian TODOS los campos, incluida incidencia/diagnóstico/prioridad
    // (luego quedan bloqueados en el formulario junto con tipo de equipo, marca e IMEI).
    // El precio también se muestra (de referencia, el de la reparación original) aunque no se pueda modificar.
    if ( $is_warranty ) {
        $incident   = get_field( 'cpt-sat__incident',   $duplicate_id );
        $diagnostic = get_field( 'cpt-sat__diagnostic', $duplicate_id );
        $prioridad  = get_field( 'cpt-sat__priority',   $duplicate_id );
        $price      = get_field( 'cpt-sat__price',      $duplicate_id );
    }
}
?>
<div class="c-create-sat <?php echo esc_attr( $disabled_all ); ?>">
    <div class="c-create-sat__inner">
        <div class="c-create-sat__container o-container">
            <div class="c-create-sat__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-create-sat__wrapper-form">
                    <?php
                        include( locate_template('components/c-sat-form.php') );
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
