
<div class="c-list-cpt-clients">
    <div class="c-list-cpt-clients__inner">  
        <div class="c-list-cpt-clients__container o-container">
            <div class="c-list-cpt-clients__col o-col-12@md o-col-8@sm o-col-4@xs">
                <div class="c-list-cpt-clients__wrapper-title">
                    <div class="c-list-cpt-clients__title o-font-display-2">Listado de clientes</div>
                </div> 
                <div class="c-list-cpt-clients__ctas">
                    <div class="c-list-cpt-clients__wrapper-cta">
                        <div class="c-list-cpt-clients__cta">
                            <a href="<?php echo get_permalink( get_page_by_path( 'crear-cliente' ) ); ?>" class="o-button o-button--style-1">Crear cliente</a>
                        </div>
                    </div>
                </div> 
                <div class="c-list-cpt-clients__wrapper-list">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Cliente</th>
                                <th>Nombre y apellidos</th>
                                <th>Teléfono</th>
                                <th>Detalle Cliente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $clients as $client ) :
                                $cpt_projects__link = get_field('cpt-client__phone', $client->ID);                                 
                                ?>
                                <tr>                                        
                                    <td><?php echo esc_html( $client->ID ); ?></td>
                                    <td><?php echo esc_html( get_the_title( $client->ID ) ); ?></td>
                                    <td><?php echo esc_html( $cpt_projects__link ); ?></td>                                    
                                    <td><a href="<?php echo get_permalink($client->ID); ?>">Detalle</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                </div>       
            </div>
        </div>          
    </div>
</div>