
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
                        <div class="c-list-cpt-sats__wrapper-filter">
                            <div class="c-list-cpt-sats__wrapper-box-search">                            
                                <div class="c-list-cpt-sats__wrapper-input-search">
                                    <input placeholder="<?php echo !empty($_GET['nombre-cliente']) ? $search_term : 'Nombre cliente'; ?>" class="c-list-cpt-sats__search" type="text" name="nombre-cliente" data-id="nombre-cliente" />
                                    <?php if(!empty($_GET['nombre-cliente'])){ ?>
                                        <div class="c-list-cpt-sats__remove-search">
                                            <svg class="c-list-cpt-sats__remove-search-icon js-remove-search-list-sats" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#f00" d="m12 12.708l-5.246 5.246q-.14.14-.344.15t-.364-.15t-.16-.354t.16-.354L11.292 12L6.046 6.754q-.14-.14-.15-.344t.15-.364t.354-.16t.354.16L12 11.292l5.246-5.246q.14-.14.345-.15q.203-.01.363.15t.16.354t-.16.354L12.708 12l5.246 5.246q.14.14.15.345q.01.203-.15.363t-.354.16t-.354-.16z"/></svg>
                                        </div>
                                    <?php } ?>                                
                                </div>
                            </div> 
                            <div class="c-list-cpt-sats__wrapper-box-search">                            
                                <div class="c-list-cpt-sats__wrapper-input-search">                               
                                    <input placeholder="<?php echo !empty($_GET['numero-sat']) ? $_GET['numero-sat'] : 'Número SAT'; ?>" class="c-list-cpt-sats__search" type="text" name="numero-sat" data-id="numero-sat" />
                                    <?php if(!empty($_GET['numero-sat'])){ ?>
                                        <div class="c-list-cpt-sats__remove-search">
                                            <svg class="c-list-cpt-sats__remove-search-icon js-remove-search-list-sats" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#f00" d="m12 12.708l-5.246 5.246q-.14.14-.344.15t-.364-.15t-.16-.354t.16-.354L11.292 12L6.046 6.754q-.14-.14-.15-.344t.15-.364t.354-.16t.354.16L12 11.292l5.246-5.246q.14-.14.345-.15q.203-.01.363.15t.16.354t-.16.354L12.708 12l5.246 5.246q.14.14.15.345q.01.203-.15.363t-.354.16t-.354-.16z"/></svg>
                                        </div>
                                    <?php } ?>                                
                                </div>
                            </div> 
                            <div class="c-list-cpt-sats__wrapper-search-button">
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
                                $client_phone_ext = get_field('cpt-client__extension', $client_id);
                                $client_phone = get_field('cpt-client__phone', $client_id);

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
                                    <td title="<?php echo $client_id; ?>">
                                        <div class="c-list-cpt-sats__wrapper-client">
                                            <a href="<?php echo get_permalink($client_id); ?>"><?php echo esc_html($client_name); ?></a>                        
                                            <a href="https://wa.me/<?php echo $client_phone_ext . $client_phone; ?>" target="_blank" title="<?php echo '+' . $client_phone_ext . ' ' . $client_phone; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 258"><defs><linearGradient id="SVGBRLHCcSy" x1="50%" x2="50%" y1="100%" y2="0%"><stop offset="0%" stop-color="#1faf38"/><stop offset="100%" stop-color="#60d669"/></linearGradient><linearGradient id="SVGHW6lecxh" x1="50%" x2="50%" y1="100%" y2="0%"><stop offset="0%" stop-color="#f9f9f9"/><stop offset="100%" stop-color="#fff"/></linearGradient></defs><path fill="url(#SVGBRLHCcSy)" d="M5.463 127.456c-.006 21.677 5.658 42.843 16.428 61.499L4.433 252.697l65.232-17.104a123 123 0 0 0 58.8 14.97h.054c67.815 0 123.018-55.183 123.047-123.01c.013-32.867-12.775-63.773-36.009-87.025c-23.23-23.25-54.125-36.061-87.043-36.076c-67.823 0-123.022 55.18-123.05 123.004"/><path fill="url(#SVGHW6lecxh)" d="M1.07 127.416c-.007 22.457 5.86 44.38 17.014 63.704L0 257.147l67.571-17.717c18.618 10.151 39.58 15.503 60.91 15.511h.055c70.248 0 127.434-57.168 127.464-127.423c.012-34.048-13.236-66.065-37.3-90.15C194.633 13.286 162.633.014 128.536 0C58.276 0 1.099 57.16 1.071 127.416m40.24 60.376l-2.523-4.005c-10.606-16.864-16.204-36.352-16.196-56.363C22.614 69.029 70.138 21.52 128.576 21.52c28.3.012 54.896 11.044 74.9 31.06c20.003 20.018 31.01 46.628 31.003 74.93c-.026 58.395-47.551 105.91-105.943 105.91h-.042c-19.013-.01-37.66-5.116-53.922-14.765l-3.87-2.295l-40.098 10.513z"/><path fill="#fff" d="M96.678 74.148c-2.386-5.303-4.897-5.41-7.166-5.503c-1.858-.08-3.982-.074-6.104-.074c-2.124 0-5.575.799-8.492 3.984c-2.92 3.188-11.148 10.892-11.148 26.561s11.413 30.813 13.004 32.94c1.593 2.123 22.033 35.307 54.405 48.073c26.904 10.609 32.379 8.499 38.218 7.967c5.84-.53 18.844-7.702 21.497-15.139c2.655-7.436 2.655-13.81 1.859-15.142c-.796-1.327-2.92-2.124-6.105-3.716s-18.844-9.298-21.763-10.361c-2.92-1.062-5.043-1.592-7.167 1.597c-2.124 3.184-8.223 10.356-10.082 12.48c-1.857 2.129-3.716 2.394-6.9.801c-3.187-1.598-13.444-4.957-25.613-15.806c-9.468-8.442-15.86-18.867-17.718-22.056c-1.858-3.184-.199-4.91 1.398-6.497c1.431-1.427 3.186-3.719 4.78-5.578c1.588-1.86 2.118-3.187 3.18-5.311c1.063-2.126.531-3.986-.264-5.579c-.798-1.593-6.987-17.343-9.819-23.64"/></svg>
                                            </a>
                                        </div>
                                    </td>
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