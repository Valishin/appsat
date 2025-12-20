
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">  
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-list-cpt-sats__wrapper-title">
                    <div class="c-list-cpt-sats__title o-font-display-2">Listado de SATS</div>
                </div>  
                <div class="c-list-cpt-sats__wrapper-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>ID SAT</th>
                                <th>Cliente</th>
                                <th>Dispositivo</th>
                                <th>Detalle Sat</th>
                                <th>Ver en PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $sats as $sat ) :                                
                                $client_id= get_field('cpt-sat__client-id', $sat->ID);
                                $date= get_field('cpt-sat__entry-date', $sat->ID);
                                $equipment= get_field('cpt-sat__type-equipment', $sat->ID);
                                $client_name = get_the_title( $client_id );  
                                
                                $pdf_id = 1; // Reemplaza con el ID de tu plantilla de E2Pdf
                                $pdf_url = add_query_arg( array(
                                    'e2pdf'   => $pdf_id,
                                    'post'    => $sat->ID,
                                    'download'=> 1,
                                ), home_url( '/' ) );
                                ?>                                                                                                                        
                                <tr>                                        
                                    <td><?php echo esc_html( $date ); ?></td>
                                    <td><?php echo esc_html( $sat->ID ); ?></td>
                                    <td><?php echo $client_name?></td>
                                    <td><?php echo esc_html( $equipment ); ?></td>                                    
                                    <td><a href="<?php echo get_permalink($sat->ID); ?>">Detalle</a></td>
                                    <td>
                                        <?php                                            
                                            $post_id = get_the_ID();
                                            echo do_shortcode('[e2pdf-download id="1" dataset="'.$sat->ID.'"]'); 
                                        ?>                                    
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