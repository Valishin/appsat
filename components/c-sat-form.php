<?php
    // Forzar array
    if (!is_array($accesories)) {
        $accesories = [];
    }
    $options = [
        'funda-laptop' => 'Funda portátil',
        'funda-movil'  => 'Funda móvil',
        'cargador'     => 'Cargador',
        'cables'       => 'Cables',
        'pendrive'     => 'Pendrive',
        'bolsa'        => 'Bolsa',
    ];

    // Usuarios que pueden atender un SAT (admin/editor), con el usuario logueado por defecto
    $attended_users        = get_users([ 'role__in' => [ 'administrator', 'editor' ], 'orderby' => 'display_name', 'order' => 'ASC' ]);
    $attended_current_login = wp_get_current_user()->user_login;

    // Una vez creado el SAT, "Atendido por" solo lo puede cambiar un administrador.
    $attended_locked = ( ! empty( $sat_id ) && ! current_user_can( 'manage_options' ) );
?>
<form class="c-sat-form__form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" enctype="multipart/form-data">
    <?php if ( ! empty( $sat_id_visible ) || ! empty( $is_warranty ) ) : ?>
    <div class="c-sat-form__id-badge">
        <?php if ( ! empty( $sat_id_visible ) ) : ?>
        <span class="c-sat-form__id-badge-label">SAT</span>
        <span class="c-sat-form__id-badge-number">#<?php echo esc_html( $sat_id_visible ); ?></span>
        <?php endif; ?>
        <?php if ( ! empty( $is_warranty ) ) : ?>
        <span class="c-sat-form__id-badge-warranty">Garantía</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="c-sat-form__entry-date">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span>Fecha de entrada: <strong><?php echo esc_html( $entry_date ); ?></strong></span>
    </div>
    <?php if ( ! empty( $is_locked ) ) : ?>
    <div class="c-sat-form__locked-notice">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span>Este SAT está <?php
            if ( $estado === 'no-reparado' )  { echo 'marcado como no reparado'; }
            elseif ( $estado === 'garantia' ) { echo 'en garantía'; }
            else                              { echo 'finalizado'; }
        ?>. Solo un administrador puede editarlo.</span>
    </div>
    <?php endif; ?>
    <fieldset class="c-sat-form__fieldset" <?php echo ! empty( $is_locked ) ? 'disabled' : ''; ?>>
    <div class="c-sat-form__accordion">
        <div class="c-sat-form__accordion-title">Servicio Informático</div>                                                 
        <div class="c-sat-form__wrapper-input">
            <input class="c-sat-form__input" type="hidden" name="action" value="crear_sat_cpt">
            <input type="hidden" name="id" value="<?php echo $sat_id; ?>">
            <?php if ( ! empty( $is_warranty ) ) : ?>
                <input type="hidden" name="is-warranty" value="1">
            <?php endif; ?>
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input">                    
                <label for="attended">Atendido por:</label>
                <select name="attended" id="attended" <?php echo $attended_locked ? 'disabled title="Solo un administrador puede cambiar quién atiende un SAT ya creado"' : ''; ?>>
                    <?php foreach ( $attended_users as $attended_user ) :
                        // En un SAT existente (o al duplicar uno) respeta lo ya guardado; si no, usa el usuario logueado
                        $is_attended_selected = ( $sat_id || ! empty( $attended ) )
                            ? ( 0 === strcasecmp( $attended, $attended_user->user_login ) )
                            : ( $attended_current_login === $attended_user->user_login );
                    ?>
                    <option value="<?php echo esc_attr( $attended_user->user_login ); ?>" <?php selected( $is_attended_selected, true ); ?>><?php echo esc_html( $attended_user->display_name ); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($client_id) : ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
                <?php endif; ?>
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label for="sat-id-visible">SAT ID</label>                
                <input class="c-sat-form__input" type="text" name="sat-id" id="sat-id-visible" disabled value="<?php echo $sat_id_visible; ?>">
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label for="sat-id">SAT ID Backend</label>                
                <input class="c-sat-form__input js-sat-id" type="text" name="sat-id" id="sat-id" disabled value="<?php echo $sat_id; ?>">
            </div> 
        </div>
        <div class="c-sat-form__wrapper-box"> 
            <div class="c-sat-form__wrapper-input">                    
                <label for="client">Cliente</label>
                <div class="c-sat-form__input-link-wrap">
                    <input class="c-sat-form__input" type="text" name="client-name" id="client" readonly value="<?php echo $client_name; ?>" title="<?php echo $client_name; ?>">
                    <?php if ($client_id) : ?>
                    <a class="c-sat-form__input-link" href="<?php echo esc_url( get_permalink($client_id) ); ?>" title="Ver cliente">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
                <input class="c-sat-form__input" type="hidden" name="client-id" value="<?php echo $client_id; ?>">
            </div>                
            <div class="c-sat-form__wrapper-input">
                <label for="dni">DNI</label>
                <input class="c-sat-form__input" type="text" id="dni" disabled value="<?php echo $client_dni; ?>">
            </div>
            <div class="c-sat-form__wrapper-input">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="client-phone" disabled value="<?php echo '+' . $client_phone; ?>" title="<?php echo $client_phone;  ?>">
            </div>
        </div>                
        <div class="c-sat-form__wrapper-box">                                                                       
            <div class="c-sat-form__wrapper-input">
            <label for="type-equipment">Tipo de equipo</label>
            <select class="js-sat-form__type-equipment" name="type-equipment" id="type-equipment" required <?php echo ! empty( $is_warranty ) ? 'disabled title="No se puede cambiar el tipo de equipo en un SAT de garantía"' : ''; ?>>
                <option value="" <?php selected($type_equipment, ''); ?>>Seleccione...</option>
                <option value="pc" <?php selected($type_equipment, 'pc'); ?>>PC Torre</option>
                <option value="portatil" <?php selected($type_equipment, 'portatil'); ?>>Portátil</option>
                <option value="tablet" <?php selected($type_equipment, 'tablet'); ?>>Tablet</option>
                <option value="movil" <?php selected($type_equipment, 'movil'); ?>>Móvil</option>
                <option value="impresora" <?php selected($type_equipment, 'impresora'); ?>>Impresora</option>
                <option value="tv" <?php selected($type_equipment, 'tv'); ?>>TV</option>
                <option value="consola" <?php selected($type_equipment, 'consola'); ?>>Consola</option>
                <option value="mando" <?php selected($type_equipment, 'mando'); ?>>Mando</option>
                <option value="otro" <?php selected($type_equipment, 'otro'); ?>>Otro</option>
            </select>
            <?php if ( ! empty( $is_warranty ) ) : ?>
                <input type="hidden" name="type-equipment" value="<?php echo esc_attr( $type_equipment ); ?>">
            <?php endif; ?>
        </div>
        <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--other c-sat-form__wrapper-input--hidden is-hidden">                    
            <label>Nombre del dispositivo</label>
            <input class="c-sat-form__input c-sat-form__input--other" type="text" name="name-other" value="<?php echo esc_html( $name_other ); ?>">
        </div>
        <div class="c-sat-form__wrapper-input">                    
            <label>Marca/modelo</label>
            <input class="c-sat-form__input" type="text" name="model" value="<?php echo esc_html( $model ); ?>" required <?php echo ! empty( $is_warranty ) ? 'readonly title="No se puede cambiar la marca/modelo en un SAT de garantía"' : ''; ?>>
        </div>
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input">                    
                <label>Número de serie o IMEI</label>
                <input class="c-sat-form__input" type="text" name="serial" value="<?php echo esc_html( $serial ); ?>" <?php echo ! empty( $is_warranty ) ? 'readonly title="No se puede cambiar el número de serie o IMEI en un SAT de garantía"' : ''; ?>>
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label>Contraseña del dispositivo</label>
                <input class="c-sat-form__input" type="text" name="password" value="<?php echo esc_html( $password ); ?>">
            </div>
            <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--sim c-sat-form__wrapper-input--hidden is-hidden">                    
                <label>Pin de la SIM</label>
                <input class="c-sat-form__input c-sat-form__input--sim" type="text" name="sim" value="<?php echo esc_html( $sim ); ?>">
            </div>
        </div>
        <div class="c-sat-form__wrapper-input">                    
            <label>Otro equipo</label>
            <input class="c-sat-form__input" type="text" name="other-equipment" value="<?php echo esc_html( $other_equipment ); ?>">
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input">                    
                <label>Accesorios entregados</label>
                <select name="accesories[]" id="accesories" multiple>
                    <?php foreach ($options as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"
                            <?php echo in_array($value, $accesories, true) ? 'selected' : ''; ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>            
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label>Otro accesorio</label>
                <input class="c-sat-form__input" type="text" name="other-accesories" value="<?php echo esc_html( $other_accesories ); ?>">
            </div>
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input">
                <label for="physical-condition">Estado físico del dispositivo</label>
                <textarea class="c-sat-form__input" type="text" name="physical-condition" id="physical-condition" rows="4" cols="50" style="resize: none;" ><?php echo esc_html( $physical_condition ); ?></textarea>
            </div>
            <?php
                $photo_field = [
                    'name'    => 'physical-condition-photo',
                    'label'   => 'Fotos del estado',
                    'value'   => $physical_condition_photo,
                    'display' => 'thumb',
                    'max'     => 5,
                ];
                include( locate_template('components/c-sat-photo-field.php') );
            ?>
        </div>
        <?php if( av_sat_signature_enabled() && ( !isset($firma) || empty($firma) ) ) { ?>
        <div class="c-sat-form__wrapper-box c-sat-form__wrapper-box--signature">
            <div class="c-sat-form__wrapper-input">                    
                <label>Firma de cliente</label>
                <div class="c-sat-form__signature-pad js-sat-form__signature-pad" id="signature-pad">
                    <canvas class="c-sat-form__canvas"></canvas>
                    <button type="button" class="c-sat-form__clear-signature o-button o-button--style-1 js-signature-clear">Borrar</button>
                </div>
                <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--checkbox c-sat-form__wrapper-input--checkbox-acceptance">
                    <input type="checkbox" name="signature-confirmed" id="signature-confirmed">
                    <label for="signature-confirmed">Acepto los términos y condiciones</label>                    
                </div>
                <small class="o-font-display-caption">
                    Al marcar esta casilla y firmar, declaro que soy el propietario del dispositivo entregado, autorizo a APP Informática a realizar la reparación solicitada, acepto los términos y condiciones de servicio y entiendo que la tienda no se hace responsable de pérdida de datos, daños previos o imprevistos durante la reparación. Asimismo, confirmo que he recibido el dispositivo en las condiciones descritas en este documento. El diagnóstico se cobrará en caso de no aceptar el presupuesto de reparación que puede ser desde 10€ hasta los 30€ dependiendo del equipo y la incidencia.
                </small>
                <button type="button" class="o-button o-button--style-1 o-font-display-caption js-signature-save">Firmar</button>
            </div>
        </div>
        <?php } ?>
    </div>
    <div class="c-sat-form__accordion">   
        <div class="c-sat-form__accordion-title">Detalle del problema</div>
            <div class="c-sat-form__wrapper-box">
                <div class="c-sat-form__wrapper-input">
                    <label>Incidencia</label>
                    <textarea class="c-sat-form__input" type="text" name="incident" rows="4" cols="50" style="resize: none;" required <?php echo ! empty( $is_warranty ) ? 'readonly title="No se puede cambiar la incidencia original en un SAT de garantía"' : ''; ?>><?php echo esc_html( $incident ); ?></textarea>
                </div>
                <div class="c-sat-form__wrapper-input">
                    <label>Diagnóstico</label>
                    <textarea class="c-sat-form__input" type="text" name="diagnostic" rows="4" cols="50" style="resize: none;" <?php echo ! empty( $is_warranty ) ? 'readonly title="No se puede cambiar el diagnóstico original en un SAT de garantía"' : ''; ?>><?php echo esc_html( $diagnostic ); ?></textarea>
                </div>
            </div>
            <?php if ( ! empty( $is_warranty ) ) : ?>
            <div class="c-sat-form__wrapper-box">
                <div class="c-sat-form__wrapper-input">
                    <label>Garantía</label>
                    <textarea class="c-sat-form__input" type="text" name="warranty-note" rows="4" cols="50" style="resize: none;" ><?php echo esc_html( $warranty_note ); ?></textarea>
                </div>
            </div>
            <?php endif; ?>
            <div class="c-sat-form__wrapper-box">
                <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--select">
                    <label for="budget">Estado del dispositivo:</label>
                    <select class="c-sat-form__select" name="estado">
                        <option value="">Seleccione...</option>
                        <option style="" value="diagnosticar" <?php selected($estado, 'diagnosticar'); ?>>Por diagnosticar</option>
                        <option style="" value="cliente-espera" <?php selected($estado, 'cliente-espera'); ?>>En espera cliente</option>
                        <option style="" value="pieza" <?php selected($estado, 'pieza'); ?>>Esperando pieza</option>
                        <option style="" value="otro-sat" <?php selected($estado, 'otro-sat'); ?>>Enviado otro SAT</option>
                        <option style="" value="reparar" <?php selected($estado, 'reparar'); ?>>Por reparar</option>
                        <option style="" value="reparado" <?php selected($estado, 'reparado'); ?>>Reparado</option>
                        <option style="" value="no-reparado" <?php selected($estado, 'no-reparado'); ?>>No reparado</option>
                        <?php if ( $estado === 'garantia' ) : ?>
                        <option style="" value="garantia" selected='selected'>Garantía</option>
                        <?php endif; ?>
                        <option style="" value="finalizado" <?php selected($estado, 'finalizado'); ?>>Finalizado</option>
                    </select>
                </div>
                <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--select">                 
                    <label for="budget">Prioridad:</label>
                    <select class="c-sat-form__select c-sat-form__select--priority" name="prioridad">
                        <option value="">Seleccione...</option>
                        <option value="rapida" <?php selected($prioridad, 'rapida'); ?>>Rápida</option>
                        <option value="media" <?php selected($prioridad, 'media'); ?>>Media</option>
                        <option value="compleja" <?php selected($prioridad, 'compleja'); ?>>Compleja</option>                       
                    </select>
                </div>
            </div>
        <div class="c-sat-form__wrapper-box c-sat-form__wrapper-box--repair">
            <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--repair">
                <label>Reparación</label>
                <div class="c-sat-form__repair-box js-repair-widget">
                <?php if ( empty( $is_warranty ) ) : ?>
                <div class="c-sat-form__repair-add">
                    <input type="text" class="c-sat-form__input js-repair-input" placeholder="Describe la acción realizada…">
                    <input type="number" step="any" class="c-sat-form__input c-sat-form__repair-price-input js-repair-price" placeholder="Precio €">
                    <button type="button" class="c-sat-form__repair-btn js-repair-add">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Añadir
                    </button>
                </div>
                <ul class="c-sat-form__repair-list js-repair-list"></ul>
                <input type="hidden" name="repair" class="js-repair-hidden" value="<?php echo esc_attr( $repair ); ?>">
                <?php else : ?>
                <?php
                $r_ro = json_decode( $repair, true );
                if ( is_array( $r_ro ) ) {
                    foreach ( $r_ro as $ri ) {
                        echo '<div class="c-sat-form__repair-item c-sat-form__repair-item--ro">';
                        echo '<span class="c-sat-form__repair-item-text">' . esc_html( $ri['text'] ?? '' ) . '</span>';
                        if ( ! empty( $ri['price'] ) ) {
                            echo '<span class="c-sat-form__repair-item-price">' . esc_html( $ri['price'] ) . ' €</span>';
                        }
                        echo '</div>';
                    }
                } else {
                    echo nl2br( esc_html( $repair ) );
                }
                ?>
                <input type="hidden" name="repair" value="<?php echo esc_attr( $repair ); ?>">
                <?php endif; ?>
                </div>
            </div>
            <div class="c-sat-form__wrapper-input">
                <label for="">Piezas pedidas</label>
                <div class="c-sat-form__repair-box js-repair-widget">
                <?php if ( empty( $is_warranty ) ) : ?>
                <div class="c-sat-form__repair-add">
                    <input type="text" class="c-sat-form__input js-repair-input" placeholder="Añade una pieza o material…">
                    <input type="number" step="any" class="c-sat-form__input c-sat-form__repair-price-input js-repair-price" placeholder="Precio €">
                    <button type="button" class="c-sat-form__repair-btn js-repair-add">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Añadir
                    </button>
                </div>
                <ul class="c-sat-form__repair-list js-repair-list"></ul>
                <input type="hidden" name="ordered-parts" class="js-repair-hidden" value="<?php echo esc_attr( $ordered_parts ); ?>">
                <?php else : ?>
                <?php
                $op_ro = json_decode( $ordered_parts, true );
                if ( is_array( $op_ro ) ) {
                    foreach ( $op_ro as $oi ) {
                        echo '<div class="c-sat-form__repair-item c-sat-form__repair-item--ro">';
                        echo '<span class="c-sat-form__repair-item-text">' . esc_html( $oi['text'] ?? '' ) . '</span>';
                        if ( ! empty( $oi['price'] ) ) {
                            echo '<span class="c-sat-form__repair-item-price">' . esc_html( $oi['price'] ) . ' €</span>';
                        }
                        echo '</div>';
                    }
                } else {
                    echo nl2br( esc_html( $ordered_parts ) );
                }
                ?>
                <input type="hidden" name="ordered-parts" value="<?php echo esc_attr( $ordered_parts ); ?>">
                <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="c-sat-form__seal">
            <div class="c-sat-form__seal-head">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                <span>Precinto de garantía</span>
            </div>
            <div class="c-sat-form__wrapper-box">
                <div class="c-sat-form__wrapper-input">
                    <label for="warranty-seal">Ubicación de la pegatina</label>
                    <input class="c-sat-form__input" type="text" name="warranty-seal" id="warranty-seal" value="<?php echo esc_attr( $warranty_seal ); ?>" placeholder="Ej. tornillo inferior derecho de la carcasa">
                </div>
                <?php
                    // Solo enlace: la foto del precinto se ve en la modal, no ocupa sitio en el formulario
                    $photo_field = [
                        'name'    => 'warranty-seal-photo',
                        'label'   => 'Foto del precinto',
                        'value'   => $warranty_seal_photo,
                        'display' => 'link',
                    ];
                    include( locate_template('components/c-sat-photo-field.php') );
                ?>
            </div>
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input">
                <label>Coste Final</label>
                <div class="c-sat-form__wrapper-price-input">
                    <input class="c-sat-form__input" type="number" step="any" name="price" value="<?php echo esc_html( $price ); ?>" <?php echo ! empty( $is_warranty ) ? 'disabled title="El precio no se puede modificar en un SAT de garantía"' : ''; ?>><span>€</span>
                </div>
                <?php if ( ! empty( $is_warranty ) ) : ?>
                    <input type="hidden" name="price" value="<?php echo esc_attr( $price ); ?>">
                <?php endif; ?>
            </div>
            <div class="c-sat-form__wrapper-input<?php echo ! empty( $is_warranty ) ? ' c-sat-form__wrapper-input--warranty-hidden' : ''; ?>">
                <label>Tipo de pago</label>
                <select class="c-sat-form__select" name="price-description" <?php echo ! empty( $is_warranty ) ? 'disabled' : ''; ?>>
                    <option value="">Seleccione...</option>
                    <option value="tarjeta" <?php selected($price_description, 'tarjeta'); ?>>Tarjeta</option>
                    <option value="efectivo" <?php selected($price_description, 'efectivo'); ?>>Efectivo</option>
                </select>
            </div>
            <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--select">
                <label for="warranty-period">Garantía de la reparación</label>
                <select class="c-sat-form__select" name="warranty-period" id="warranty-period" title="Garantía que se da al cliente por esta reparación (opcional)">
                    <option value="">Sin garantía</option>
                    <?php foreach ( av_sat_warranty_period_choices() as $period_value => $period_label ) : ?>
                    <option value="<?php echo esc_attr( $period_value ); ?>" <?php selected( $warranty_period, $period_value ); ?>><?php echo esc_html( $period_label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="c-sat-form__wrapper-input">
                <label>Fecha reparación</label>
                <input class="c-sat-form__input" type="text" name="repair-date" value="<?php echo esc_html( $repair_date ); ?>" disabled>
            </div>
            <div class="c-sat-form__wrapper-input">
                <label>Fecha entrega</label>
                <input class="c-sat-form__input" type="text" name="delivery-date" value="<?php echo esc_html( $delivery_date ); ?>" disabled>
            </div>
            <div class="c-sat-form__wrapper-input">
                <label>Finalizado por</label>
                <input class="c-sat-form__input" type="text" name="finalized-by" value="<?php echo esc_html( $finalized_by ); ?>" disabled>
            </div>
        </div>
        <?php if(!!$firma){ ?>
            <small class="c-sat-form__wrapper-signature-title">Firma del cliente</small>
            <div class="c-sat-form__wrapper-signature">
                <img class="c-sat-form__signature-img" src="<?php echo $firma; ?>" alt="" srcset="">
            </div> 
        <?php } ?>     
        <?php if(!isset($firma) || empty($firma)) {
            $link = set_url_scheme( get_permalink(), is_ssl() ? 'https' : 'http' );
            $message = 'Envío enlace para firmar: ' . $link;
            $encoded_message = urlencode( $message );
        ?>
        <?php } ?>
    </div>
    </fieldset>
    <div class="c-sat-form__wrapper-ctas js-sat-form__wrapper-ctas">
        <div class="c-sat-form__ctas-inner">
            <?php if ( $sat_id ) : ?>
            <?php
                $pdf_url  = get_post_meta( $sat_id, 'cpt-sat__pdf-file', true );
                $sat_num  = get_post_meta( $sat_id, 'cpt-sat__sat-id',   true );
                $wa_phone = str_replace( ' ', '', $client_phone );
                $tracking_url = av_get_sat_tracking_url( $sat_id );
                $wa_msg   = $pdf_url
                    ? urlencode( "Hola, adjunto el parte de reparación SAT #{$sat_num}:\n{$pdf_url}" . ( $tracking_url ? "\n\nPuedes ver el estado de tu reparación aquí:\n{$tracking_url}" : '' ) )
                    : '';
                $wa_href  = $pdf_url
                    ? 'https://wa.me/' . $wa_phone . '?text=' . $wa_msg
                    : '';
                $tracking_msg  = $tracking_url
                    ? urlencode( "Hola, puedes ver el estado de tu reparación en este enlace:\n{$tracking_url}" )
                    : '';
                $tracking_href = ( $client_id && $tracking_url )
                    ? 'https://wa.me/' . $wa_phone . '?text=' . $tracking_msg
                    : '';
            ?>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--secondary">
                <button type="button" class="c-sat-form__cta c-sat-form__cta--secondary js-generate-sat-pdf">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><polyline points="7 10 12 15 17 10"/><path d="M5 19h14"/></svg>
                    <span>Descargar PDF</span>
                </button>
                <?php if ( $tracking_href ) : ?>
                <a class="c-sat-form__cta c-sat-form__cta--whatsapp"
                    href="<?php echo esc_url( $tracking_href ); ?>"
                    target="_blank"
                    title="Envía al cliente el enlace para ver el estado de su reparación">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Enviar seguimiento</span>
                </a>
                <?php endif; ?>
                <?php if ( $client_id ) : ?>
                <a  id="js-wa-send-sat"
                    href="<?php echo $wa_href ? esc_url( $wa_href ) : '#'; ?>"
                    target="<?php echo $wa_href ? '_blank' : '_self'; ?>"
                    data-phone="<?php echo esc_attr( $wa_phone ); ?>"
                    data-sat-num="<?php echo esc_attr( $sat_num ); ?>"
                    class="c-sat-form__cta c-sat-form__cta--whatsapp<?php echo ! $wa_href ? ' is-hidden' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <span>Enviar SAT</span>
                </a>
                <?php endif; ?>
                <?php if ( $client_id && in_array( $estado, [ 'finalizado', 'no-reparado', 'garantia' ], true ) ) : ?>
                <a class="c-sat-form__cta c-sat-form__cta--secondary"
                    href="<?php echo esc_url( add_query_arg( [ 'id' => $client_id, 'duplicate' => $sat_id ], get_permalink( get_page_by_path( 'crear-sat' ) ) ) ); ?>"
                    title="Crea un SAT nuevo copiando los datos del equipo y del cliente">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    <span>Duplicar SAT</span>
                </a>
                <?php endif; ?>
            </div>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary">
                <?php if ( $client_id && in_array( $estado, [ 'finalizado', 'no-reparado', 'garantia' ], true ) ) : ?>
                <a class="c-sat-form__cta c-sat-form__cta--warranty"
                    href="<?php echo esc_url( add_query_arg( [ 'id' => $client_id, 'duplicate' => $sat_id, 'warranty' => 1 ], get_permalink( get_page_by_path( 'crear-sat' ) ) ) ); ?>"
                    title="Crea un SAT nuevo de garantía copiando los datos del equipo y del cliente, sin precio ni tipo de pago">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Garantía</span>
                </a>
                <?php endif; ?>
                <?php if ( ! empty( $is_warranty ) && empty( $is_locked ) ) : ?>
                <?php
                    $remove_warranty_href = wp_nonce_url(
                        add_query_arg(
                            [ 'action' => 'av_sat_remove_warranty', 'sat_id' => $sat_id ],
                            admin_url( 'admin-post.php' )
                        ),
                        'av_sat_remove_warranty_' . $sat_id
                    );
                ?>
                <a class="c-sat-form__cta c-sat-form__cta--warranty-off"
                    href="<?php echo esc_url( $remove_warranty_href ); ?>"
                    title="Marca este SAT como no cubierto por garantía: se desbloquean los campos del equipo y del precio"
                    onclick="return confirm('¿Marcar este SAT como NO garantía?\n\nSe desbloquearán los campos y se vaciarán incidencia, diagnóstico, reparación y precio (venían copiados del SAT original) para que registres la intervención real.\n\nSe perderán los cambios que no hayas guardado.');">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19.69 14a6.9 6.9 0 0 0 .31-2V5l-8-3-3.16 1.18"/><path d="M4.73 4.73 4 5v7c0 6 8 10 8 10a20.29 20.29 0 0 0 5.62-4.38"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    <span>No es garantía</span>
                </a>
                <?php endif; ?>
                <a class="c-sat-form__cta c-sat-form__cta--whatsapp" href="<?php echo esc_url( 'https://wa.me/' . $wa_phone ); ?>" target="_blank" title="<?php echo '+' . $client_phone; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Enviar mensaje</span>
                </a>
                <?php
                $factura_existente = get_posts( [
                    'post_type'      => 'cpt-facturas',
                    'posts_per_page' => 1,
                    'post_status'    => 'publish',
                    'fields'         => 'ids',
                    'meta_query'     => [ [ 'key' => '_factura_sat_id', 'value' => $sat_id, 'compare' => '=' ] ],
                ] );
                $tiene_factura = ! empty( $factura_existente );
                ?>
                <a class="c-sat-form__cta c-sat-form__cta--invoice js-invoice-btn<?php echo $tiene_factura ? ' c-sat-form__cta--invoice-done' : ''; ?>"
                   href="<?php echo esc_url( add_query_arg( [ 'sat_invoice' => '1', 'sat_id' => $sat_id, 'nonce' => wp_create_nonce( 'sat_invoice_' . $sat_id ) ], home_url( '/' ) ) ); ?>"
                   target="_blank"
                   aria-disabled="<?php echo empty( $price ) ? 'true' : 'false'; ?>"
                   title="<?php echo $tiene_factura ? 'Ver factura generada' : 'Generar factura en PDF'; ?>">
                    <?php if ( $tiene_factura ) : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="9 15 11 17 15 13"/></svg>
                        <span>Ver Factura</span>
                    <?php else : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Generar Factura</span>
                    <?php endif; ?>
                </a>
                <button type="button" class="c-sat-form__cta c-sat-form__cta--secondary js-sat-form__cancel-btn is-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    <span>Cancelar</span>
                </button>
                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary js-sat-form__save-btn" disabled data-locked="<?php echo ! empty( $is_locked ) ? '1' : '0'; ?>" <?php echo ! empty( $is_locked ) ? 'title="Solo un administrador puede guardar cambios en este SAT"' : 'title="Modifica algún campo para poder guardar"'; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span>Guardar SAT</span>
                </button>
            </div>
            <?php else : ?>
            <div class="c-sat-form__ctas-group c-sat-form__ctas-group--primary c-sat-form__ctas-group--solo">
                <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span>Crear SAT</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php
if ( $sat_id ) :
    $raw_history     = get_post_meta( $sat_id, 'cpt-sat__history', true );
    $history_entries = ( $raw_history && is_string( $raw_history ) ) ? json_decode( $raw_history, true ) : [];
    if ( is_array( $history_entries ) && ! empty( $history_entries ) ) :
        $history_reversed = array_reverse( $history_entries );
?>
<div class="c-sat-history js-sat-history">
    <div class="c-sat-history__header">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <h3 class="c-sat-history__title">Histórico</h3>
    </div>
    <div class="c-sat-history__timeline-wrap js-sat-history-wrap">
        <div class="c-sat-history__timeline">
            <?php foreach ( $history_reversed as $entry ) :
                $type   = $entry['type']   ?? 'status';
                $slug   = $entry['status'] ?? '';
                $classes = 'c-sat-history__item c-sat-history__item--' . esc_attr( $type );
                if ( $slug ) $classes .= ' c-sat-history__item--' . esc_attr( $slug );
            ?>
            <div class="<?php echo $classes; ?>">
                <div class="c-sat-history__dot"></div>
                <div class="c-sat-history__content">
                    <span class="c-sat-history__label"><?php echo esc_html( $entry['label'] ); ?></span>
                    <?php if ( ! empty( $entry['text'] ) ) : ?>
                    <span class="c-sat-history__text"><?php echo esc_html( $entry['text'] ); ?></span>
                    <?php endif; ?>
                    <span class="c-sat-history__meta">
                        <?php echo esc_html( $entry['ts'] ); ?>
                        <?php if ( ! empty( $entry['user'] ) ) : ?>· <?php echo esc_html( $entry['user'] ); ?><?php endif; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <button type="button" class="c-sat-history__toggle js-sat-history-toggle" aria-expanded="false">
        <span class="js-sat-history-toggle-text">Ver todo el histórico</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
</div>
<script>
(function () {
    var wrap   = document.querySelector('.js-sat-history-wrap');
    var btn    = document.querySelector('.js-sat-history-toggle');
    if (!wrap || !btn) return;
    if (wrap.scrollHeight <= wrap.clientHeight + 4) {
        btn.style.display = 'none';
        return;
    }
    btn.addEventListener('click', function () {
        var expanded = wrap.classList.toggle('is-expanded');
        btn.classList.toggle('is-expanded', expanded);
        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        btn.querySelector('.js-sat-history-toggle-text').textContent = expanded ? 'Ver menos' : 'Ver todo el histórico';
    });
})();
</script>
<?php
    endif;
endif;
?>