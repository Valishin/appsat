
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">  
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-list-cpt-sats__wrapper-title">
                    <div class="c-list-cpt-sats__title o-font-display-2">Listado de SATS</div>
                </div>  
                <div class="c-list-cpt-sats__wrapper-count o-font-display-caption">
                    <?php echo sprintf( _n( 'Total %d SAT', 'Total %d SATS', $posts->found_posts, 'appsat' ), $posts->found_posts ); ?>
                </div>
                <div class="c-list-cpt-sats__wrapper-search">
                    <form class="c-list-cpt-sats__form-search" method="GET" action="<?php echo esc_url( get_permalink() ); ?>">
                        <div class="c-list-cpt-sats__wrapper-box-search">                            
                            <div class="c-list-cpt-sats__wrapper-input-search">
                                <input placeholder="<?php echo isset($search_term) ? $search_term : 'Nombre cliente'; ?>" class="c-list-cpt-sats__search" type="text" name="nombre-cliente" data-id="nombre-cliente" />
                                <?php if(isset($search_term)){ ?>
                                    <div class="c-list-cpt-sats__remove-search">
                                        <svg class="c-list-cpt-sats__remove-search-icon js-remove-search-list-sats" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#f00" d="m12 12.708l-5.246 5.246q-.14.14-.344.15t-.364-.15t-.16-.354t.16-.354L11.292 12L6.046 6.754q-.14-.14-.15-.344t.15-.364t.354-.16t.354.16L12 11.292l5.246-5.246q.14-.14.345-.15q.203-.01.363.15t.16.354t-.16.354L12.708 12l5.246 5.246q.14.14.15.345q.01.203-.15.363t-.354.16t-.354-.16z"/></svg>
                                    </div>
                                <?php } ?>
                                <input type="submit" value="Buscar" class="c-list-cpt-sats__search-button o-button o-button--style-1" />
                            </div>
                        </div>                                                                          
                    </form>                     
                </div>  
                <div class="c-list-cpt-sats__wrapper-menu o-font-display-caption">                    
                    <div class="c-list-cpt-sats__menu-item js-filter-all" data-id="todos">Todos</div>
                    <div class="c-list-cpt-sats__menu-item c-list-cpt-sats__menu-item--active js-filter-all" data-id="en-curso">En curso</div>
                    <div class="c-list-cpt-sats__menu-item js-filter-all" data-id="finalizados">Finalizados</div>                    
                </div>              
                <div class="c-list-cpt-sats__wrapper-list o-font-display-caption">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha Entrada</th>
                                <th>ID SAT</th>
                                <th>Cliente</th>
                                <th>Dispositivo</th>
                                <th>Problema</th>                                
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Precio Final</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $sats as $sat ) :                                
                                $client_id= get_field('cpt-sat__client-id', $sat->ID);
                                $date= get_field('cpt-sat__entry-date', $sat->ID);
                                $equipment= get_field('cpt-sat__type-equipment', $sat->ID);
                                $equipment_model = get_field('cpt-sat__model', $sat->ID);
                                $client_name = get_field( 'cpt-client__name', $client_id ); 
                                $estado = get_field('cpt-sat__status', $sat->ID); 
                                $incident = get_field('cpt-sat__incident', $sat->ID); 
                                $rawPrice = get_field('cpt-sat__price', $sat->ID); 
                                $price_description = get_field('cpt-sat__price-description', $sat->ID);
                                $priority = get_field('cpt-sat__priority', $sat->ID);
                                $sat_id = get_field('cpt-sat__sat-id', $sat->ID);

                                if ($rawPrice === '' || $rawPrice === null) {
                                    $price = ''; // o ''
                                } else {
                                    // Normalizar
                                    $rawPrice = str_replace(',', '.', $rawPrice);

                                    // Asegurar número
                                    if (is_numeric($rawPrice)) {
                                        $price = number_format((float)$rawPrice, 2, ',', '.');
                                    } else {
                                        $price = '0,00'; // fallback
                                    }
                                }
                                
                                $pdf_id = 1; // Reemplaza con el ID de tu plantilla de E2Pdf
                                $pdf_url = add_query_arg( array(
                                    'e2pdf'   => $pdf_id,
                                    'post'    => $sat->ID,
                                    'download'=> 1,
                                ), home_url( '/' ) );                                
                                ?>                                                                                                                        
                                <tr class="c-list-cpt-sats__row js-row-sat">                                        
                                    <td><?php echo esc_html( $date ); ?></td>
                                    <td><?php echo esc_html( $sat_id ); ?></td>
                                    <td title="<?php echo $client_id; ?>"><a href="<?php echo get_permalink($client_id); ?>"><?php echo esc_html($client_name); ?></a></td>
                                    <td title="<?php echo esc_html($equipment_model); ?>"><?php echo esc_html( $equipment ); ?></td>   
                                    <td title="<?php echo esc_html( $incident ); ?>"><?php echo esc_html( wp_trim_words($incident, 4, '...') ); ?></td>                                                                      
                                    <td>
                                        <div class="c-list-cpt-sats__wrapper-select-status js-list-cpt-sats__wrapper-select-status" data-satid="<?php echo esc_attr( $sat->ID ); ?>">
                                            <select class="js-list-cpt-sats__select-status">
                                                <option value="">Seleccione...</option>
                                                <option style="background-color:#FFF176; color:#000;" value="diagnosticar" <?php selected($estado, 'diagnosticar'); ?>>Por diagnosticar</option>
                                                <option style="background-color:#FFB74D; color:#000;" value="cliente-espera" <?php selected($estado, 'cliente-espera'); ?>>En espera cliente</option>
                                                <option style="background-color:#CE93D8; color:#000;" value="pieza" <?php selected($estado, 'pieza'); ?>>Esperando pieza</option>
                                                <option style="background-color:#64B5F6; color:#000;" value="reparar" <?php selected($estado, 'reparar'); ?>>Por reparar</option>
                                                <option style="background-color:#81C784; color:#fff;" value="reparado" <?php selected($estado, 'reparado'); ?>>Reparado</option>
                                                <option style="background-color:#E57373; color:#000;" value="no-reparado" <?php selected($estado, 'no-reparado'); ?>>No reparado</option>
                                                <option style="background-color:#B0BEC5; color:#000;" value="garantia" <?php selected($estado, 'garantia'); ?>>Garantía</option>
                                                <option style="background-color:#388E3C; color:#fff;" value="finalizado" <?php selected($estado, 'finalizado'); ?>>Finalizado</option>
                                            </select>     
                                            <div>
                                                <svg class="c-list-cpt-sats__save-status c-list-cpt-sats__save-status--save js-list-cpt-sats__save-status" xmlns="http://www.w3.org/2000/svg" width="30px" viewBox="0 0 24 24"><path fill="currentColor" d="M6 4h10.59L20 7.41V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7.91L16.09 5H15v5H6zm1 0v4h7V5zm5 7a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2a2 2 0 0 0 2 2a2 2 0 0 0 2-2a2 2 0 0 0-2-2"/></svg>                                                                                                      
                                        </div>
                                    </td>
                                    <td class="c-list-cpt-sats__priority <?php echo esc_attr($priority); ?>"><?php echo esc_html($priority); ?></td>
                                    <td class="c-list-cpt-sats__price" title="<?php echo $price_description; ?>"><?php echo $price; ?><span><?php echo !empty($price) ? ' €' : ''; ?></td>
                                    <td>
                                        <a href="<?php echo get_permalink($sat->ID); ?>" title="ver SAT">
                                            <svg class="c-list-cpt-sats__svg" xmlns="http://www.w3.org/2000/svg" width="30px" viewBox="0 0 24 24"><path fill="currentColor" d="M5 3h6a3 3 0 0 1 3 3v4h-1V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-4h1v4a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m3 9h11.25L16 8.75l.66-.75l4.5 4.5l-4.5 4.5l-.66-.75L19.25 13H8z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                        <?php
                        if ($posts->max_num_pages > 1) {

                            echo '<div class="c-list-cpt-sats__pagination">';

                            echo paginate_links(array(
                                'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                                'format'    => '?paged=%#%',
                                'current'   => max(1, get_query_var('paged')),
                                'total'     => $posts->max_num_pages,
                                'prev_text' => '« Anterior',
                                'next_text' => 'Siguiente »',
                            ));

                            echo '</div>';
                        }
                        ?>
                </div>       
            </div>
        </div>          
    </div>
</div>