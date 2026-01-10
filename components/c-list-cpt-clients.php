
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
                    <div class="c-list-cpt-clients__wrapper-search">
                        <form method="GET" action="">                            
                            <input class="c-list-cpt-clients__search" type="text" name="buscar" id="buscar" value="<?php echo isset($_GET['buscar']) ? esc_attr($_GET['buscar']) : ''; ?>" />
                            <input type="submit" value="Buscar" class="o-button o-button--style-1" />
                        </form>                     
                    </div>
                </div> 
                <div class="c-list-cpt-clients__wrapper-list">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Cliente</th>
                                <th>DNI</th>
                                <th>Nombre y apellidos</th>
                                <th>Teléfono</th>                                
                                <th>Crear SAT</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="resultado-clientes">
                            <?php foreach ( $clients as $client ) :
                                $phone = '+' . get_field('cpt-client__extension', $client->ID) . ' ' . get_field('cpt-client__phone', $client->ID);  
                                $dni = get_field('cpt-client__dni', $client->ID);    
                                $name = get_field('cpt-client__name', $client->ID);                             
                                ?>
                                <tr>                                        
                                    <td><?php echo esc_html( get_the_title( $client->ID ) ); ?></td>
                                    <td><?php echo esc_html( $dni ); ?></td>
                                    <td><?php echo esc_html( $name ); ?></td>
                                    <td><?php echo esc_html( $phone ); ?></td>                                    
                                    <td><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'crear-sat' ) ) . '?id=' . $client->ID ); ?>">Crear SAT</td>
                                    <td>
                                        <a href="<?php echo get_permalink($client->ID); ?>" title="ver Cliente">
                                            <svg class="c-list-cpt-clients__svg" xmlns="http://www.w3.org/2000/svg" width="30px" viewBox="0 0 24 24"><path fill="currentColor" d="M5 3h6a3 3 0 0 1 3 3v4h-1V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-4h1v4a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m3 9h11.25L16 8.75l.66-.75l4.5 4.5l-4.5 4.5l-.66-.75L19.25 13H8z"/></svg>
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