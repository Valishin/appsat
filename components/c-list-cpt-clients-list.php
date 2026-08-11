<?php
// Espera $posts (WP_Query) y $clients (array de WP_Post) en el scope. Usado
// tanto en la carga normal de la pagina como en la respuesta del buscador
// asincrono (AJAX).
$crear_sat_url = get_permalink( get_page_by_path( 'crear-sat' ) );
?>
<table>
    <thead>
        <tr>
            <th>ID Cliente</th>
            <th>Tipo Cliente</th>
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
            $type_client = get_field('cpt-client__type-client', $client->ID);
            ?>
            <tr>
                <td><?php echo esc_html( get_the_title( $client->ID ) ); ?></td>
                <td><?php echo esc_html( $type_client ); ?></td>
                <td><?php echo esc_html( $dni ); ?></td>
                <td><?php echo esc_html( $name ); ?></td>
                <td><?php echo esc_html( $phone ); ?></td>
                <td><a href="<?php echo esc_url( $crear_sat_url . '?id=' . $client->ID ); ?>">Crear SAT</a></td>
                <td>
                    <a class="c-list-cpt-clients__wrapper-view-client" href="<?php echo get_permalink($client->ID); ?>" title="ver Cliente">
                        <svg class="c-list-cpt-clients__view-client" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
if ($posts->max_num_pages > 1) {

    // $clients_page_url llega solo desde el buscador asincrono (AJAX); en la
    // carga normal de la pagina get_pagenum_link() ya funciona correctamente.
    $pagination_base = ! empty( $clients_page_url )
        ? add_query_arg( 'paged', 999999999, $clients_page_url )
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
