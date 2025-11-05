
<div class="c-list-cpt-sats">
    <div class="c-list-cpt-sats__inner">  
        <div class="c-list-cpt-sats__container o-container">
            <div class="c-list-cpt-sats__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-list-cpt-sats__wrapper-title">
                    <div class="c-list-cpt-sats__title o-font-display-2">Listado de clientes</div>
                </div> 
                <div class="c-list-cpt-sats__ctas">
                    <div class="c-list-cpt-sats__wrapper-cta">
                        <div class="c-list-cpt-sats__cta">
                            <a href="<?php echo get_permalink( get_page_by_path( 'crear-sat' ) ); ?>" class="o-button o-button--style-1">Crear sats</a>
                        </div>
                    </div>
                </div> 
                <div class="c-list-cpt-sats__wrapper-list">
                    <table>
                        <thead>
                            <tr>
                                <th>ID SAT</th>
                                <th>Nombre y apellidos</th>
                                <th>Teléfono</th>
                                <th>Detalle Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $sats as $sat ) :
                                $cpt_projects__link = get_field('cpt-client__phone', $sat->ID);                                 
                                ?>
                                <tr>                                        
                                    <td><?php echo esc_html( $sat->ID ); ?></td>
                                    <td><?php echo esc_html( get_the_title( $sat->ID ) ); ?></td>
                                    <td><?php echo esc_html( $cpt_projects__link ); ?></td>                                    
                                    <td><a href="<?php echo get_permalink($sat->ID); ?>">Detalle</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                </div>       
            </div>
        </div>          
    </div>
</div>