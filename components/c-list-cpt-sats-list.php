<?php
// Espera $posts (WP_Query) y $sats (array de WP_Post) en el scope. Usado
// tanto en la carga normal de la pagina como en la respuesta del buscador
// asincrono (AJAX).
?>
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
            $is_warranty = get_field('cpt-sat__is-warranty', $sat->ID) === '1';
            $priority = get_field('cpt-sat__priority', $sat->ID);
            $sat_id = get_field('cpt-sat__sat-id', $sat->ID);
            $client_phone_ext = get_field('cpt-client__extension', $client_id);
            $client_phone = get_field('cpt-client__phone', $client_id);

            // La reparación se guarda como JSON de líneas ({text, price}) desde el detalle
            // del SAT, pero puede ser texto plano en SATs antiguos.
            $repair_raw   = get_field('cpt-sat__repair', $sat->ID);
            $repair_items = json_decode( (string) $repair_raw, true );
            $has_repair   = is_array( $repair_items ) ? ! empty( $repair_items ) : trim( (string) $repair_raw ) !== '';

            // La fecha se guarda como "d/m/Y H:i": en el listado solo se muestra el día.
            $entry_parts = explode( ' ', trim( (string) $date ), 2 );
            $entry_day   = $entry_parts[0] ?? '';

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
                <td><?php echo esc_html( $entry_day ); ?></td>
                <td><?php echo esc_html( $sat_id ); ?></td>
                <td title="<?php echo $client_id; ?>">
                    <div class="c-list-cpt-sats__wrapper-client">
                        <a href="<?php echo get_permalink($client_id); ?>"><?php echo esc_html($client_name); ?></a>
                        <a href="https://wa.me/<?php echo $client_phone_ext . $client_phone; ?>" target="_blank" title="<?php echo '+' . $client_phone_ext . ' ' . $client_phone; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </a>
                    </div>
                </td>
                <td title="<?php echo esc_html($equipment_model); ?>"><?php echo esc_html( $equipment ); ?></td>
                <td title="<?php echo esc_html( $incident ); ?>"><?php echo esc_html( wp_trim_words($incident, 4, '...') ); ?></td>
                <td>
                    <div class="c-list-cpt-sats__wrapper-select-status js-list-cpt-sats__wrapper-select-status" data-satid="<?php echo esc_attr( $sat->ID ); ?>" data-payment="<?php echo esc_attr( $price_description ); ?>" data-saved-status="<?php echo esc_attr( $estado ); ?>" data-warranty="<?php echo $is_warranty ? '1' : '0'; ?>" data-repair="<?php echo $has_repair ? '1' : '0'; ?>">
                        <select class="js-list-cpt-sats__select-status">
                            <option value="">Seleccione...</option>
                            <option style="background-color:#FFF176; color:#000;" value="diagnosticar" <?php selected($estado, 'diagnosticar'); ?>>Por diagnosticar</option>
                            <option style="background-color:#FFB74D; color:#000;" value="cliente-espera" <?php selected($estado, 'cliente-espera'); ?>>En espera cliente</option>
                            <option style="background-color:#CE93D8; color:#000;" value="pieza" <?php selected($estado, 'pieza'); ?>>Esperando pieza</option>
                            <option style="background-color:#731087; color:#fff;" value="otro-sat" <?php selected($estado, 'otro-sat'); ?>>Enviado otro SAT</option>
                            <option style="background-color:#64B5F6; color:#000;" value="reparar" <?php selected($estado, 'reparar'); ?>>Por reparar</option>
                            <option style="background-color:#81C784; color:#fff;" value="reparado" <?php selected($estado, 'reparado'); ?>>Reparado</option>
                            <option style="background-color:#E57373; color:#000;" value="no-reparado" <?php selected($estado, 'no-reparado'); ?>>No reparado</option>
                            <?php if ( $estado === 'garantia' ) : ?>
                            <option style="background-color:#B0BEC5; color:#000;" value="garantia" selected='selected'>Garantía</option>
                            <?php endif; ?>
                            <option style="background-color:#388E3C; color:#fff;" value="finalizado" <?php selected($estado, 'finalizado'); ?>>Finalizado</option>
                        </select>
                        <div>
                            <svg class="c-list-cpt-sats__save-status c-list-cpt-sats__save-status--save js-list-cpt-sats__save-status" xmlns="http://www.w3.org/2000/svg" width="30px" viewBox="0 0 24 24"><path fill="currentColor" d="M6 4h10.59L20 7.41V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7.91L16.09 5H15v5H6zm1 0v4h7V5zm5 7a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2a2 2 0 0 0 2 2a2 2 0 0 0 2-2a2 2 0 0 0-2-2"/></svg>
                        </div>
                    </div>
                </td>
                <td class="c-list-cpt-sats__priority <?php echo esc_attr($priority); ?>"><?php echo esc_html($priority); ?></td>
                <td class="c-list-cpt-sats__price" title="<?php echo $price_description; ?>">
                    <?php if ( $is_warranty ) : ?>
                    <span class="c-list-cpt-sats__warranty-badge" title="Este SAT es de garantía">Garantía</span>
                    <?php else : ?>
                    <?php echo $price; ?><span><?php echo !empty($price) ? ' €' : ''; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <a class="c-list-cpt-sats__wrapper-view-sat" href="<?php echo get_permalink($sat->ID); ?>" title="ver SAT">
                        <svg class="c-list-cpt-sats__view-sat" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
if ($posts->max_num_pages > 1) {

    // $sats_page_url llega solo desde el buscador asincrono (AJAX); en la carga
    // normal de la pagina get_pagenum_link() ya funciona correctamente.
    $pagination_base = ! empty( $sats_page_url )
        ? add_query_arg( 'paged', 999999999, $sats_page_url )
        : get_pagenum_link( 999999999 );

    echo '<div class="c-list-cpt-sats__pagination">';

    echo paginate_links(array(
        'base'      => str_replace(999999999, '%#%', esc_url($pagination_base)),
        'format'    => '?paged=%#%',
        'current'   => max(1, intval($paged)),
        'total'     => $posts->max_num_pages,
        'prev_text' => '« Anterior',
        'next_text' => 'Siguiente »',
    ));

    echo '</div>';
}
?>
