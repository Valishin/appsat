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
                <div class="c-sat-form__wrapper-client-phone">
                    <input class="c-sat-form__input" type="text" name="client-name" id="client" disabled value="<?php echo get_field('cpt-client__name', $client_id); ?>" title="<?php echo get_field('cpt-client__name', $client_id); ?>">
                    <input type="text" name="client-phone" disabled value="<?php echo '+' . get_field('cpt-client__extension', $client_id) . ' ' . get_field('cpt-client__phone', $client_id) ?>" title="<?php echo '+' . get_field('cpt-client__extension', $client_id) . ' ' . get_field('cpt-client__phone', $client_id) ?>">
                </div>
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label for="client">SAT ID</label>                
                <input class="c-sat-form__input js-sat-id" type="text" name="sat-id" id="sat-id" disabled value="<?php echo $sat_id; ?>">
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
                <input class="c-sat-form__input c-sat-form__input--other" type="text" name="name-other" value="<?php echo esc_html( $name_other ); ?>">
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
        <?php if(!isset($firma) || empty($firma)) { ?>
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
            <div class="c-sat-form__wrapper-input">                    
                <label>Incidencia</label>
                <textarea class="c-sat-form__input" type="text" name="incident" rows="4" cols="50" style="resize: none;" ><?php echo esc_html( $incident ); ?></textarea>
            </div>
            <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--checkbox">                 
                <input type="checkbox" id="budget" name="budget" value="yes" <?php checked( $budget, 'yes' ); ?>>
                <label for="budget">Pasar presupuesto al cliente por teléfono, whatsapp o email.</label>
            </div>
            <div class="c-sat-form__wrapper-box">
                <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--select">                 
                    <label for="budget">Estado del dispositivo:</label>
                    <select class="c-sat-form__select" name="estado">
                        <option value="">Seleccione...</option>
                        <option style="" value="diagnosticar" <?php selected($estado, 'diagnosticar'); ?>>Por diagnosticar</option>
                        <option style="" value="cliente-espera" <?php selected($estado, 'cliente-espera'); ?>>En espera cliente</option>
                        <option style="" value="pieza" <?php selected($estado, 'pieza'); ?>>Esperando pieza</option>
                        <option style="" value="reparar" <?php selected($estado, 'reparar'); ?>>Por reparar</option>
                        <option style="" value="reparado" <?php selected($estado, 'reparado'); ?>>Reparado</option>
                        <option style="" value="no-reparado" <?php selected($estado, 'no-reparado'); ?>>No reparado</option>
                        <option style="" value="garantia" <?php selected($estado, 'garantia'); ?>>Garantía</option>
                        <option style="" value="finalizado" <?php selected($estado, 'finalizado'); ?>>Finalizado</option>
                    </select>
                </div>
                <div class="c-sat-form__wrapper-input c-sat-form__wrapper-input--select">                 
                    <label for="budget">Prioridad:</label>
                    <select class="c-sat-form__select" name="prioridad">
                        <option value="">Seleccione...</option>
                        <option value="rapida" <?php selected($prioridad, 'rapida'); ?>>Rápida</option>
                        <option value="media" <?php selected($prioridad, 'media'); ?>>Media</option>
                        <option value="compleja" <?php selected($prioridad, 'compleja'); ?>>Compleja</option>                       
                    </select>
                </div>
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
                <div class="c-sat-form__wrapper-price-input">
                    <input class="c-sat-form__input" type="number" step="any" name="price" value="<?php echo esc_html( $price ); ?>"><span>€</span>
                </div>
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label>Descripción Coste</label>
                <input class="c-sat-form__input" placeholder="Tipo de pago u otros" type="text" name="price-description" value="<?php echo esc_html( $price_description ); ?>">
            </div>
            <div class="c-sat-form__wrapper-input">                    
                <label>Fecha reparación</label>
                <input class="c-sat-form__input" type="text" name="repair-date" value="<?php echo esc_html( $repair_date ); ?>" disabled>
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
            <?php if(!!$sat_id){ ?>                 
            <div class="c-sat-form__wrapper-send-signature">
                <small class="c-sat-form__wrapper-send-signature-label">Enviar para firmar a:</small>
                <div class="c-sat-form__wrapper-send-signature__buttons">
                    <a href="https://wa.me/34678758210?text=<?php echo $encoded_message; ?>"
                        target="_blank"
                        class="c-sat-form__wrapper-send-signature__button o-button o-button--style-1">
                        Alex
                    </a>  
                    <a href="https://wa.me/34602895522?text=<?php echo $encoded_message; ?>"
                        target="_blank"
                        class="c-sat-form__wrapper-send-signature__button o-button o-button--style-1">
                        Laura
                    </a>                  
                </div>
            </div> 
            <?php } ?>
        <?php } ?>    
        <div class="c-sat-form__wrapper-ctas">            
            <button type="submit" class="o-button o-button--style-1 o-font-display-body"><?= isset($sat_id) ? 'Guardar' : 'Crear'; ?> SAT</button>
            <button onclick="window.print()" class="o-button o-button--style-1 o-font-display-body">Imprimir</button>
            <a href="https://wa.me/<?php echo $client_phone_ext . $client_phone; ?>" target="_blank" title="<?php echo '+' . $client_phone_ext . ' ' . $client_phone; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 256 258"><defs><linearGradient id="SVGBRLHCcSy" x1="50%" x2="50%" y1="100%" y2="0%"><stop offset="0%" stop-color="#1faf38"/><stop offset="100%" stop-color="#60d669"/></linearGradient><linearGradient id="SVGHW6lecxh" x1="50%" x2="50%" y1="100%" y2="0%"><stop offset="0%" stop-color="#f9f9f9"/><stop offset="100%" stop-color="#fff"/></linearGradient></defs><path fill="url(#SVGBRLHCcSy)" d="M5.463 127.456c-.006 21.677 5.658 42.843 16.428 61.499L4.433 252.697l65.232-17.104a123 123 0 0 0 58.8 14.97h.054c67.815 0 123.018-55.183 123.047-123.01c.013-32.867-12.775-63.773-36.009-87.025c-23.23-23.25-54.125-36.061-87.043-36.076c-67.823 0-123.022 55.18-123.05 123.004"/><path fill="url(#SVGHW6lecxh)" d="M1.07 127.416c-.007 22.457 5.86 44.38 17.014 63.704L0 257.147l67.571-17.717c18.618 10.151 39.58 15.503 60.91 15.511h.055c70.248 0 127.434-57.168 127.464-127.423c.012-34.048-13.236-66.065-37.3-90.15C194.633 13.286 162.633.014 128.536 0C58.276 0 1.099 57.16 1.071 127.416m40.24 60.376l-2.523-4.005c-10.606-16.864-16.204-36.352-16.196-56.363C22.614 69.029 70.138 21.52 128.576 21.52c28.3.012 54.896 11.044 74.9 31.06c20.003 20.018 31.01 46.628 31.003 74.93c-.026 58.395-47.551 105.91-105.943 105.91h-.042c-19.013-.01-37.66-5.116-53.922-14.765l-3.87-2.295l-40.098 10.513z"/><path fill="#fff" d="M96.678 74.148c-2.386-5.303-4.897-5.41-7.166-5.503c-1.858-.08-3.982-.074-6.104-.074c-2.124 0-5.575.799-8.492 3.984c-2.92 3.188-11.148 10.892-11.148 26.561s11.413 30.813 13.004 32.94c1.593 2.123 22.033 35.307 54.405 48.073c26.904 10.609 32.379 8.499 38.218 7.967c5.84-.53 18.844-7.702 21.497-15.139c2.655-7.436 2.655-13.81 1.859-15.142c-.796-1.327-2.92-2.124-6.105-3.716s-18.844-9.298-21.763-10.361c-2.92-1.062-5.043-1.592-7.167 1.597c-2.124 3.184-8.223 10.356-10.082 12.48c-1.857 2.129-3.716 2.394-6.9.801c-3.187-1.598-13.444-4.957-25.613-15.806c-9.468-8.442-15.86-18.867-17.718-22.056c-1.858-3.184-.199-4.91 1.398-6.497c1.431-1.427 3.186-3.719 4.78-5.578c1.588-1.86 2.118-3.187 3.18-5.311c1.063-2.126.531-3.986-.264-5.579c-.798-1.593-6.987-17.343-9.819-23.64"/></svg>
            </a>
        </div>
    </div>
</form>