<div class="c-create-sat">
    <div class="c-create-sat__inner">
        <div class="c-create-sat__container o-container">
            <div class="c-create-sat__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-create-sat__box">
                    <div class="c-create-sat__wrapper-text">
                        <div class="c-create-sat__wrapper-title">
                            <div class="c-create-sat__title o-font-display-2">Crear Sat</div>                            
                        </div>
                        <div class="c-create-sat__wrapper-description">
                            <div class="c-create-sat__description">Utiliza el siguiente formulario para crear un nuevo sat en el sistema. Completa todos los campos requeridos y haz clic en "Crear Sat" para finalizar el proceso.</div>                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="c-create-sat__col o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">
                <div class="c-create-sat__wrapper-form">
                    <form class="c-create-sat__form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                        <div class="c-create-sat__wrapper-input">
                            <input class="c-create-sat__input" type="hidden" name="action" value="crear_sat_cpt">
                        </div>
                        <div class="c-create-sat__wrapper-input">                    
                            <label for="attended">Atendido por:</label>
                            <select name="attended" id="attended">
                                <option value="laura">Laura</option>
                                <option value="alex" selected>Alex</option>                                
                            </select>
                        </div>
                        <div class="c-create-sat__wrapper-box">                                                
                            <div class="c-create-sat__wrapper-input">
                                <label for="type-equipment">Tipo de equipo</label>
                                <select class="js-create-sat__type-equipment" name="type-equipment" id="type-equipment">
                                    <option value="pc" selected>PC Torre</option>
                                    <option value="portatil">Portátil</option> 
                                    <option value="tablet">Tablet</option> 
                                    <option value="movil">Móvil</option> 
                                    <option value="impresora">Impresora</option>  
                                    <option value="tv">TV</option>
                                    <option value="consola">Consola</option>
                                    <option value="mando">Mando</option>
                                    <option value="varios">Varios</option>                                
                                </select>
                            </div>
                            <div class="c-create-sat__wrapper-input c-create-sat__wrapper-input--other c-create-sat__wrapper-input--hidden is-hidden">                    
                                <label>Nombre del dispositivo</label>
                                <input class="c-create-sat__input" type="text" name="name-other">
                            </div>
                            <div class="c-create-sat__wrapper-input">                    
                                <label>Marca/modelo</label>
                                <input class="c-create-sat__input" type="text" name="model">
                            </div>
                        </div>
                        <div class="c-create-sat__wrapper-input">                    
                            <label>Número de serie o IMEI</label>
                            <input class="c-create-sat__input" type="text" name="serial">
                        </div>
                        <div class="c-create-sat__wrapper-input">                    
                            <label>Contraseña del dispositivo</label>
                            <input class="c-create-sat__input" type="text" name="password">
                        </div>
                        <div class="c-create-sat__wrapper-input c-create-sat__wrapper-input--sim c-create-sat__wrapper-input--hidden is-hidden">                    
                            <label>Pin de la SIM</label>
                            <input class="c-create-sat__input" type="text" name="sim">
                        </div>
                        <div class="c-create-sat__wrapper-input">                    
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
                        <div class="c-create-sat__wrapper-input">                    
                            <label>Estado físico del dispositivo</label>
                            <textarea class="c-create-sat__input" type="text" name="physical-condition" rows="4" cols="50" style="resize: none;"></textarea>
                        </div>
                        <button type="submit">Crear SAT</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>