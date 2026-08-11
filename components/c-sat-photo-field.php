<?php
// Campo de foto reutilizable del formulario del SAT (estado del dispositivo,
// precinto de garantía…). Espera en el scope:
//   $photo_field = [
//       'name'    => 'nombre-del-input',
//       'label'   => 'Etiqueta',
//       'value'   => url guardada (o JSON con varias),
//       'display' => 'thumb' (miniaturas visibles) | 'link' (solo enlace de texto),
//       'max'     => nº máximo de fotos (1 = campo simple)
//   ]
// Las fotos se ven en grande en una modal (con slider si hay varias).
$photo_name    = $photo_field['name'];
$photo_label   = $photo_field['label'] ?? 'Foto';
$photo_display = $photo_field['display'] ?? 'thumb';
$photo_max     = max( 1, intval( $photo_field['max'] ?? 1 ) );
$photo_values  = av_sat_photo_urls( $photo_field['value'] ?? '' );
$photo_values  = array_slice( $photo_values, 0, $photo_max );
$photo_id      = 'photo-' . $photo_name;
$photo_multi   = $photo_max > 1;
$photo_left    = $photo_max - count( $photo_values );
$photo_eye     = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
?>
<div class="c-sat-form__wrapper-input js-photo-field" data-display="<?php echo esc_attr( $photo_display ); ?>">
    <label for="<?php echo esc_attr( $photo_id ); ?>"><?php echo esc_html( $photo_label ); ?></label>
    <button type="button" class="c-sat-form__photo-camera js-photo-camera">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
        <span>Tomar foto</span>
    </button>
    <input class="c-sat-form__input c-sat-form__input--file js-photo-input"
        type="file"
        name="<?php echo esc_attr( $photo_name . ( $photo_multi ? '[]' : '' ) ); ?>"
        id="<?php echo esc_attr( $photo_id ); ?>"
        accept="image/*"
        <?php echo $photo_multi ? 'multiple' : ''; ?>
        data-max="<?php echo esc_attr( $photo_max ); ?>"
        data-left="<?php echo esc_attr( $photo_left ); ?>"
        <?php echo $photo_multi && $photo_left < 1 ? 'disabled' : ''; ?>>

    <?php if ( $photo_multi ) : ?>
    <small class="c-sat-form__photo-hint js-photo-hint-left"><?php
        echo $photo_left > 0
            ? 'Puedes añadir hasta ' . esc_html( $photo_max ) . ' fotos (' . esc_html( $photo_left ) . ' disponibles).'
            : 'Has llegado al máximo de ' . esc_html( $photo_max ) . ' fotos.';
    ?></small>
    <?php endif; ?>

    <?php // Fotos recién elegidas, todavía sin guardar ?>
    <div class="c-sat-form__photo-preview js-photo-preview is-hidden">
        <?php if ( $photo_display === 'thumb' ) : ?>
        <div class="c-sat-form__photo-thumbs js-photo-gallery js-photo-preview-list"></div>
        <?php else : ?>
        <a class="c-sat-form__photo-link js-photo-zoom js-photo-preview-link" href="#" title="Ver la foto en grande">
            <?php echo $photo_eye; ?>
            <span>Ver la foto seleccionada</span>
        </a>
        <?php endif; ?>
        <small class="c-sat-form__photo-hint">Se <?php echo $photo_multi ? 'guardarán' : 'guardará'; ?> al guardar el SAT</small>
    </div>

    <?php
    $photo_trash = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>';
    $photo_undo  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>';
    ?>
    <?php if ( ! empty( $photo_values ) ) : ?>
    <div class="c-sat-form__photo-saved">
        <?php if ( $photo_display === 'thumb' ) : ?>
        <div class="c-sat-form__photo-thumbs js-photo-gallery">
            <?php foreach ( $photo_values as $i => $photo_url ) : ?>
            <div class="c-sat-form__photo-item js-photo-item" data-url="<?php echo esc_attr( $photo_url ); ?>">
                <a class="js-photo-zoom" href="<?php echo esc_url( $photo_url ); ?>" title="Ver la foto en grande">
                    <img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $photo_label . ' ' . ( $i + 1 ) ); ?>" loading="lazy">
                </a>
                <button type="button" class="c-sat-form__photo-remove js-photo-remove" title="Eliminar esta foto">
                    <span class="c-sat-form__photo-remove-icon"><?php echo $photo_trash; ?></span>
                    <span class="c-sat-form__photo-remove-icon c-sat-form__photo-remove-icon--undo"><?php echo $photo_undo; ?></span>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div class="c-sat-form__photo-item c-sat-form__photo-item--inline js-photo-item" data-url="<?php echo esc_attr( $photo_values[0] ); ?>">
            <a class="c-sat-form__photo-link js-photo-zoom" href="<?php echo esc_url( $photo_values[0] ); ?>" title="Ver la foto en grande">
                <?php echo $photo_eye; ?>
                <span>Ver <?php echo esc_html( mb_strtolower( $photo_label ) ); ?></span>
            </a>
            <button type="button" class="c-sat-form__photo-remove c-sat-form__photo-remove--inline js-photo-remove" title="Eliminar esta foto">
                <span class="c-sat-form__photo-remove-icon"><?php echo $photo_trash; ?></span>
                <span class="c-sat-form__photo-remove-icon c-sat-form__photo-remove-icon--undo"><?php echo $photo_undo; ?></span>
            </button>
        </div>
        <?php endif; ?>
        <small class="c-sat-form__photo-hint">
            <?php echo $photo_multi ? 'Las fotos que marques se eliminarán al guardar el SAT.' : 'Si subes otra foto se sustituirá esta.'; ?>
        </small>
    </div>
    <?php endif; ?>
    <input type="hidden" name="<?php echo esc_attr( $photo_name ); ?>-remove" class="js-photo-remove-input" value="">
</div>
