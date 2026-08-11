<?php
$logo_url = get_template_directory_uri() . '/assets/imgs/logo_icb.png';
?>
<div class="c-sat-tracking">
    <div class="c-sat-tracking__inner">
        <div class="c-sat-tracking__container o-container">
            <div class="c-sat-tracking__col o-col-8@md o-col-push-2@md o-col-8@sm o-col-4@xs">

                <div class="c-sat-tracking__brand">
                    <a href="https://icorebyte.com" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="iCB SAT">
                    </a>
                </div>

                <?php if ( ! $sat ) : ?>

                    <div class="c-sat-tracking__card c-sat-tracking__card--empty">
                        <h1 class="c-sat-tracking__title">No hemos encontrado esta reparación</h1>
                        <p class="c-sat-tracking__text">El enlace no es válido o ha caducado. Ponte en contacto con nosotros si necesitas ayuda.</p>
                    </div>

                <?php else :

                    $sat_id             = $sat->ID;
                    $sat_id_visible     = get_field( 'cpt-sat__sat-id',       $sat_id );
                    $client_id          = get_field( 'cpt-sat__client-id',    $sat_id );
                    $client_name        = get_field( 'cpt-client__name',     $client_id );
                    $type_equipment     = get_field( 'cpt-sat__type-equipment', $sat_id );
                    $name_other         = get_field( 'cpt-sat__name-other',   $sat_id );
                    $model              = get_field( 'cpt-sat__model',       $sat_id );
                    $serial             = get_field( 'cpt-sat__model-imei',  $sat_id );
                    $entry_date         = get_field( 'cpt-sat__entry-date', $sat_id );
                    $delivery_date      = get_field( 'cpt-sat__delivery-date', $sat_id );
                    $estado             = get_field( 'cpt-sat__status',    $sat_id );
                    $is_warranty        = get_field( 'cpt-sat__is-warranty', $sat_id ) === '1';
                    $incident           = get_field( 'cpt-sat__incident',   $sat_id );
                    $physical_condition = get_field( 'cpt-sat__physical-condition', $sat_id );
                    $physical_condition_photos = av_sat_photo_urls( get_field( 'cpt-sat__physical-condition-photo', $sat_id ) );
                    $price              = get_field( 'cpt-sat__price',      $sat_id );
                    $price_label        = in_array( $estado, [ 'finalizado', 'garantia' ], true ) ? 'Coste final' : 'Presupuesto';

                    $equipment_types = [
                        'pc'        => 'PC Torre',
                        'portatil'  => 'Portátil',
                        'tablet'    => 'Tablet',
                        'movil'     => 'Móvil',
                        'impresora' => 'Impresora',
                        'tv'        => 'TV',
                        'consola'   => 'Consola',
                        'mando'     => 'Mando',
                        'otro'      => $name_other ?: 'Otro',
                    ];
                    $equipment_label = $equipment_types[ $type_equipment ] ?? $type_equipment;

                    $raw_history     = get_post_meta( $sat_id, 'cpt-sat__history', true );
                    $history_entries = ( $raw_history && is_string( $raw_history ) ) ? json_decode( $raw_history, true ) : [];
                    if ( ! is_array( $history_entries ) ) $history_entries = [];
                    $history_reversed = array_reverse( $history_entries );
                ?>

                <div class="c-sat-tracking__card">

                    <div class="c-sat-tracking__head">
                        <span class="c-sat-tracking__sat-number">SAT #<?php echo esc_html( $sat_id_visible ); ?></span>
                        <?php if ( $is_warranty ) : ?>
                        <span class="c-sat-tracking__warranty-badge">Garantía</span>
                        <?php endif; ?>
                    </div>

                    <h1 class="c-sat-tracking__title">Hola<?php echo $client_name ? ', ' . esc_html( $client_name ) : ''; ?></h1>
                    <p class="c-sat-tracking__text">Este es el estado actual de tu reparación.</p>

                    <div class="c-sat-tracking__status">
                        <span class="c-sat-tracking__status-badge c-sat-tracking__status-badge--<?php echo esc_attr( $estado ); ?>">
                            <?php echo esc_html( av_sat_status_label( $estado ) ); ?>
                        </span>
                    </div>

                    <?php if ( $incident ) : ?>
                    <div class="c-sat-tracking__incident">
                        <h2 class="c-sat-tracking__section-title">Incidencia reportada</h2>
                        <p class="c-sat-tracking__text c-sat-tracking__text--incident"><?php echo esc_html( $incident ); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="c-sat-tracking__equipment">
                        <h2 class="c-sat-tracking__section-title">Datos del equipo</h2>
                        <div class="c-sat-tracking__equipment-grid">
                            <div class="c-sat-tracking__field">
                                <span class="c-sat-tracking__field-label">Tipo de equipo</span>
                                <span class="c-sat-tracking__field-value"><?php echo esc_html( $equipment_label ); ?></span>
                            </div>
                            <?php if ( $model ) : ?>
                            <div class="c-sat-tracking__field">
                                <span class="c-sat-tracking__field-label">Marca / modelo</span>
                                <span class="c-sat-tracking__field-value"><?php echo esc_html( $model ); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ( $serial ) : ?>
                            <div class="c-sat-tracking__field">
                                <span class="c-sat-tracking__field-label">Nº de serie / IMEI</span>
                                <span class="c-sat-tracking__field-value"><?php echo esc_html( $serial ); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ( $physical_condition ) : ?>
                            <div class="c-sat-tracking__field">
                                <span class="c-sat-tracking__field-label">Estado físico</span>
                                <span class="c-sat-tracking__field-value"><?php echo esc_html( $physical_condition ); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="c-sat-tracking__field">
                                <span class="c-sat-tracking__field-label">Fecha de entrada</span>
                                <span class="c-sat-tracking__field-value"><?php echo esc_html( $entry_date ); ?></span>
                            </div>
                            <?php if ( $delivery_date ) : ?>
                            <div class="c-sat-tracking__field">
                                <span class="c-sat-tracking__field-label">Fecha de entrega</span>
                                <span class="c-sat-tracking__field-value"><?php echo esc_html( $delivery_date ); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ( $price && ! $is_warranty ) : ?>
                            <div class="c-sat-tracking__field">
                                <span class="c-sat-tracking__field-label"><?php echo esc_html( $price_label ); ?></span>
                                <span class="c-sat-tracking__field-value"><?php echo esc_html( number_format( floatval( str_replace( ',', '.', $price ) ), 2, ',', '.' ) ); ?> €</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ( ! empty( $physical_condition_photos ) ) : ?>
                        <div class="c-sat-tracking__photos">
                            <span class="c-sat-tracking__field-label"><?php echo count( $physical_condition_photos ) > 1 ? 'Fotos del equipo a la entrada' : 'Foto del equipo a la entrada'; ?></span>
                            <div class="c-sat-tracking__photos-list js-photo-gallery">
                                <?php foreach ( $physical_condition_photos as $i => $photo_url ) : ?>
                                <a class="js-photo-zoom" href="<?php echo esc_url( $photo_url ); ?>" title="Ver la foto en grande">
                                    <img src="<?php echo esc_url( $photo_url ); ?>" alt="Estado del equipo a la entrada <?php echo esc_attr( $i + 1 ); ?>" loading="lazy">
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ( ! empty( $history_reversed ) ) : ?>
                    <div class="c-sat-tracking__history">
                        <h2 class="c-sat-tracking__section-title">Historial</h2>
                        <div class="c-sat-tracking__timeline">
                            <?php foreach ( $history_reversed as $entry ) :
                                $type = $entry['type'] ?? 'status';
                                $slug = $entry['status'] ?? '';
                                $classes = 'c-sat-tracking__timeline-item c-sat-tracking__timeline-item--' . esc_attr( $type );
                                if ( $slug ) $classes .= ' c-sat-tracking__timeline-item--' . esc_attr( $slug );
                            ?>
                            <div class="<?php echo $classes; ?>">
                                <div class="c-sat-tracking__timeline-dot"></div>
                                <div class="c-sat-tracking__timeline-content">
                                    <span class="c-sat-tracking__timeline-label"><?php echo esc_html( $entry['label'] ); ?></span>
                                    <?php if ( ! empty( $entry['text'] ) ) : ?>
                                    <span class="c-sat-tracking__timeline-text"><?php echo esc_html( $entry['text'] ); ?></span>
                                    <?php endif; ?>
                                    <span class="c-sat-tracking__timeline-meta"><?php echo esc_html( $entry['ts'] ); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

                <?php endif; ?>

                <div class="c-sat-tracking__footer">
                    <a href="https://icorebyte.com" target="_blank" rel="noopener" class="c-sat-tracking__footer-link">Visita icorebyte.com</a>
                </div>

            </div>
        </div>
    </div>
</div>
