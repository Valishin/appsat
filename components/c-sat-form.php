<form class="c-sat-form__form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
    <div class="c-sat-form__accordion">   
        <div class="c-sat-form__accordion-title">Servicio Informático</div>                                                 
        <div class="c-sat-form__wrapper-input">
            <input class="c-sat-form__input" type="hidden" name="action" value="crear_sat_cpt">
            <input type="hidden" name="id" value="<?php echo $sat_id; ?>">
        </div>
        <div class="c-sat-form__wrapper-box"> 
            <div class="c-sat-form__wrapper-input">                    
                <label for="attended">Atendido por:</label>
                <select name="attended" id="attended">
                    <option value="laura">Laura</option>
                    <option value="alex" selected>Alex</option>                                
                </select>
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label for="client">Cliente</label>
                <input class="c-sat-form__input" type="hidden" name="client-id" value="<?php echo $client_id; ?>">
                <input class="c-sat-form__input" type="text" name="client-name" id="client" disabled value="<?php echo $client_name; ?>">
            </div>
        </div>
        <input class="js-sat-form__id-client" type="hidden" value="">
        <div class="c-sat-form__wrapper-box">                                                
            <div class="c-sat-form__wrapper-input">
                <label for="type-equipment">Tipo de equipo</label>
                <select class="js-sat-form__type-equipment" name="type-equipment" id="type-equipment">
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
            </div>
            <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--other c-sat-form__wrapper-input--hidden is-hidden">                    
                <label>Nombre del dispositivo</label>
                <input class="c-sat-form__input" type="text" name="name-other" value="<?php echo esc_html( $name_other ); ?>">
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label>Marca/modelo</label>
                <input class="c-sat-form__input" type="text" name="model" value="<?php echo esc_html( $model ); ?>">
            </div>
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input">                    
                <label>Número de serie o IMEI</label>
                <input class="c-sat-form__input" type="text" name="serial" value="<?php echo esc_html( $serial ); ?>">
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label>Contraseña del dispositivo</label>
                <input class="c-sat-form__input" type="text" name="password" value="<?php echo esc_html( $password ); ?>">
            </div>
            <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--sim c-sat-form__wrapper-input--hidden is-hidden">                    
                <label>Pin de la SIM</label>
                <input class="c-sat-form__input" type="text" name="sim" value="<?php echo esc_html( $sim ); ?>">
            </div>
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input">                    
                <label>Accesorios entregados</label>
                <select name="accesories[]" id="accesories" multiple>
                    <option value="funda-laptop">Funda portátil</option>
                    <option value="funda-movil">Funda móvil</option>
                    <option value="cargador">Cargador</option>
                    <option value="cables">Cables</option>
                    <option value="pendrive">Pendrive</option>
                    <option value="bolsa">Bolsa</option>
                </select>
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label>Otro accesorio</label>
                <input class="c-sat-form__input" type="text" name="other-accesories" value="<?php echo esc_html( $other_accesories ); ?>">
            </div>
        </div>
        <div class="c-sat-form__wrapper-input">                    
            <label>Estado físico del dispositivo</label>
            <textarea class="c-sat-form__input" type="text" name="physical-condition" rows="4" cols="50" style="resize: none;" ><?php echo esc_html( $physical_condition ); ?></textarea>
        </div>
    </div>
    <div class="c-sat-form__accordion">   
        <div class="c-sat-form__accordion-title">Detalle del problema</div>  
            <div class="c-sat-form__wrapper-input">                    
                <label>Incidencia</label>
                <textarea class="c-sat-form__input" type="text" name="incident" rows="4" cols="50" style="resize: none;" ><?php echo esc_html( $incident ); ?></textarea>
            </div>
            <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--checkbox">                 
                <input type="checkbox" id="budget" name="budget" value="yes" <?php checked( $budget, 'yes' ); ?>>
                <label for="budget">Pasar presupuesto al cliente por teléfono, whatsapp o email.</label>
            </div>
            <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--select">                 
                <label for="budget">Estado del dispositivo:</label>
                <select class="c-sat-form__select" name="estado">
                    <option value="">Seleccione...</option>
                    <option value="diagnosticar" <?php selected($estado, 'diagnosticar'); ?>>Por diagnosticar</option>
                    <option value="reparar" <?php selected($estado, 'reparar'); ?>>Por reparar</option>
                    <option value="reparado" <?php selected($estado, 'reparado'); ?>>Reparado</option>
                    <option value="no-reparado" <?php selected($estado, 'no-reparado'); ?>>No reparado</option>
                    <option value="garantia" <?php selected($estado, 'garantia'); ?>>Garantía</option>
                </select>
            </div>
        <div class="c-sat-form__wrapper-box">                                                                                
            <div class="c-sat-form__wrapper-input">                    
                <label>Reparación</label>
                <textarea class="c-sat-form__input" type="text" name="repair" rows="4" cols="50" style="resize: none;" ><?php echo esc_html( $repair ); ?></textarea>
            </div>
        </div>
        <div class="c-sat-form__wrapper-box">
            <div class="c-sat-form__wrapper-input">                    
                <label>Coste Final</label>
                <input class="c-sat-form__input" type="text" name="price" value="<?php echo esc_html( $price ); ?>">
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label>Fecha reparación</label>
                <input class="c-sat-form__input" type="text" name="repair-date" value="<?php echo esc_html( $repair_date ); ?>">
            </div>
        </div>
        <button type="submit" class="c-sat-form__button"><?= isset($sat_id) ? 'Guardar' : 'Crear'; ?> SAT</button>
        <button onclick="window.print()" class="c-sat-form__button">Imprimir</button>
    </div>
</form>