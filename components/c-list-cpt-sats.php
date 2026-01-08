
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">  
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-list-cpt-sats__wrapper-title">
                    <div class="c-list-cpt-sats__title o-font-display-2">Listado de SATS</div>
                </div>  
                <div class="c-list-cpt-sats__wrapper-search">
                    <form method="GET" action="">   
                        Buscar por:
                        <select name="" id="">
                            <option value="">Selecciona...</option>
                            <option value="fecha">Fecha</option>
                            <option value="nombre-cliente">Nombre cliente</option>
                            <option value="id-cliente">ID Cliente</option>
                            <option value="precio">Precio</option>
                            <option value="estado">Estado</option>
                        </select>   
                        <input class="c-list-cpt-sats__search-date" data-id="fecha" type="date" hidden/>                      
                        <input class="c-list-cpt-sats__search" type="text" id="nombre-cliente" hidden/>
                        <input class="c-list-cpt-sats__search" type="text" id="id-cliente" hidden/>
                        <input class="c-list-cpt-sats__search" type="text" id="precio" hidden/>
                        <select data-id="estado" hidden>
                            <option value="">Seleccione...</option>
                            <option value="diagnosticar">Por diagnosticar</option>
                            <option value="reparar">Por reparar</option>
                            <option value="reparado">Reparado</option>
                            <option value="no-reparado">No reparado</option>
                        </select>
                        <input type="submit" value="Buscar" class="o-button o-button--style-1" />
                    </form>                     
                </div>
                <div class="c-list-cpt-sats__wrapper-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha Entrada</th>
                                <th>ID SAT</th>
                                <th>Cliente</th>
                                <th>Dispositivo</th>
                                <th>Problema</th>                                
                                <th>Estado</th>
                                <th>Precio Final</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $sats as $sat ) :                                
                                $client_id= get_field('cpt-sat__client-id', $sat->ID);
                                $date= get_field('cpt-sat__entry-date', $sat->ID);
                                $equipment= get_field('cpt-sat__type-equipment', $sat->ID);
                                $client_name = get_field( 'cpt-client__name', $client_id ); 
                                $estado = get_field('cpt-sat__status', $sat->ID); 
                                $incident = get_field('cpt-sat__incident', $sat->ID); 
                                $price = get_field('cpt-sat__price', $sat->ID); 
                                
                                $pdf_id = 1; // Reemplaza con el ID de tu plantilla de E2Pdf
                                $pdf_url = add_query_arg( array(
                                    'e2pdf'   => $pdf_id,
                                    'post'    => $sat->ID,
                                    'download'=> 1,
                                ), home_url( '/' ) );
                                ?>                                                                                                                        
                                <tr>                                        
                                    <td><?php echo esc_html( $date ); ?></td>
                                    <td><?php echo esc_html( get_the_title( $sat->ID ) ); ?></td>
                                    <td><?php echo esc_html($client_name) . ' (ID: ' . $client_id . ')'; ?></td>
                                    <td><?php echo esc_html( $equipment ); ?></td>   
                                    <td title="<?php echo esc_html( $incident ); ?>"><?php echo esc_html( wp_trim_words($incident, 4, '...') ); ?></td>                                                                      
                                    <td>
                                        <div class="c-list-cpt-sats__wrapper-select-status js-list-cpt-sats__wrapper-select-status" data-satid="<?php echo esc_attr( $sat->ID ); ?>">
                                            <select class="js-list-cpt-sats__select-status">
                                                <option value="">Seleccione...</option>
                                                <option value="diagnosticar" <?php selected($estado, 'diagnosticar'); ?>>Por diagnosticar</option>
                                                <option value="reparar" <?php selected($estado, 'reparar'); ?>>Por reparar</option>
                                                <option value="reparado" <?php selected($estado, 'reparado'); ?>>Reparado</option>
                                                <option value="no-reparado" <?php selected($estado, 'no-reparado'); ?>>No reparado</option>
                                            </select>     
                                            <div>
                                                <svg class="c-list-cpt-sats__save-status c-list-cpt-sats__save-status--save js-list-cpt-sats__save-status" xmlns="http://www.w3.org/2000/svg" width="30px" viewBox="0 0 24 24"><path fill="currentColor" d="M6 4h10.59L20 7.41V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7.91L16.09 5H15v5H6zm1 0v4h7V5zm5 7a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2a2 2 0 0 0 2 2a2 2 0 0 0 2-2a2 2 0 0 0-2-2"/></svg>                                                                                                      
                                        </div>
                                    </td>
                                    <td><?php echo $price; ?></td>
                                    <td>
                                        <a href="<?php echo get_permalink($sat->ID); ?>" title="ver SAT">
                                            <svg class="c-list-cpt-sats__svg" xmlns="http://www.w3.org/2000/svg" width="30px" viewBox="0 0 24 24"><path fill="currentColor" d="M5 3h6a3 3 0 0 1 3 3v4h-1V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-4h1v4a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m3 9h11.25L16 8.75l.66-.75l4.5 4.5l-4.5 4.5l-.66-.75L19.25 13H8z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                </div>       
            </div>
        </div>          
    </div>
</div>