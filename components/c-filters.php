<?php
// Filtro reutilizable de los listados (SATs, clientes, facturas).
// Los campos marcados con 'quick' => true se ven siempre; el resto se despliega
// con el botón "Filtros".
// Espera en el scope $filters con esta forma:
//   $filters = [
//       'form_class'   => 'js-sats-filter-form',   // clase que engancha el buscador AJAX
//       'submit_class' => 'js-sats-filter-submit',
//       'clear_id'     => 'sats-clear-filters',
//       'nonce'        => 'av_sats_filter_nonce',
//       'fields'       => [
//           [ 'name' => 'nombre-cliente', 'label' => 'Cliente', 'placeholder' => 'Nombre', 'quick' => true ],
//           [ 'name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'options' => [ '' => 'Todos', ... ] ],
//           [ 'name' => 'importe', 'label' => 'Importe', 'type' => 'number', 'suffix' => '€' ],
//           [ 'name' => 'fecha',   'label' => 'Fecha',   'type' => 'date' ],
//       ],
//   ]
$filters_fields = $filters['fields'] ?? [];

$filters_quick = [];
$filters_more  = [];
foreach ( $filters_fields as $filters_field ) {
    if ( ! empty( $filters_field['quick'] ) ) {
        $filters_quick[] = $filters_field;
    } else {
        $filters_more[] = $filters_field;
    }
}

// Sin campos rápidos definidos se comporta como antes: todo a la vista
if ( empty( $filters_quick ) ) {
    $filters_quick = $filters_more;
    $filters_more  = [];
}

$filters_has_value = function( $field ) {
    return isset( $_GET[ $field['name'] ] ) && $_GET[ $field['name'] ] !== '';
};

// Nº de filtros aplicados: se muestra en el botón y decide si se ve "Limpiar"
$filters_active = count( array_filter( $filters_fields, $filters_has_value ) );

// Si hay algún filtro avanzado aplicado, el panel se abre solo
$filters_open = (bool) array_filter( $filters_more, $filters_has_value );

// Pinta un campo del filtro
$filters_render_field = function( $field ) {

    $name        = $field['name'];
    $type        = $field['type'] ?? 'text';
    $label       = $field['label'] ?? '';
    $placeholder = $field['placeholder'] ?? '';
    $suffix      = $field['suffix'] ?? '';
    $value       = $_GET[ $name ] ?? '';
    $id          = 'f-' . $name;
    $clearable   = $type !== 'select';
    ?>
    <div class="c-filters__field">
        <label class="c-filters__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <div class="c-filters__control js-filter-control<?php echo $suffix ? ' c-filters__control--suffix' : ''; ?>">

            <?php if ( $type === 'select' ) : ?>
                <select id="<?php echo esc_attr( $id ); ?>" class="c-filters__input js-filter-field" name="<?php echo esc_attr( $name ); ?>">
                    <?php foreach ( ( $field['options'] ?? [] ) as $opt_value => $opt_label ) : ?>
                    <option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value, $opt_value ); ?>><?php echo esc_html( $opt_label ); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <input id="<?php echo esc_attr( $id ); ?>"
                    class="c-filters__input js-filter-field"
                    type="<?php echo esc_attr( $type ); ?>"
                    name="<?php echo esc_attr( $name ); ?>"
                    value="<?php echo esc_attr( $value ); ?>"
                    <?php echo $placeholder ? 'placeholder="' . esc_attr( $placeholder ) . '"' : ''; ?>
                    <?php echo isset( $field['step'] ) ? 'step="' . esc_attr( $field['step'] ) . '"' : ''; ?>
                    <?php echo isset( $field['min'] )  ? 'min="'  . esc_attr( $field['min'] )  . '"' : ''; ?>>
            <?php endif; ?>

            <?php if ( $suffix ) : ?>
            <span class="c-filters__suffix"><?php echo esc_html( $suffix ); ?></span>
            <?php endif; ?>

            <?php if ( $clearable ) : ?>
            <button type="button" class="c-filters__clear-field js-field-clear" aria-label="Borrar <?php echo esc_attr( mb_strtolower( $label ) ); ?>" tabindex="-1">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <?php endif; ?>

        </div>
    </div>
    <?php
};
?>
<div class="c-filters">
    <form class="c-filters__form <?php echo esc_attr( $filters['form_class'] ); ?>" method="GET" action="<?php echo esc_url( get_permalink() ); ?>">
        <?php wp_nonce_field( $filters['nonce'], 'nonce', false ); ?>

        <div class="c-filters__bar">

            <div class="c-filters__grid">
                <?php foreach ( $filters_quick as $field ) $filters_render_field( $field ); ?>
            </div>

            <div class="c-filters__bar-actions">
                <a href="<?php echo esc_url( get_permalink() ); ?>"
                   id="<?php echo esc_attr( $filters['clear_id'] ); ?>"
                   class="c-filters__clear<?php echo $filters_active ? '' : ' is-hidden'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    <span>Limpiar</span>
                </a>

                <?php if ( ! empty( $filters_more ) ) : ?>
                <button type="button" class="c-filters__toggle js-filters-toggle<?php echo $filters_open ? ' is-open' : ''; ?>" aria-expanded="<?php echo $filters_open ? 'true' : 'false'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                    <span>Filtros</span>
                    <span class="c-filters__count js-filter-count<?php echo $filters_active ? '' : ' is-hidden'; ?>"><?php echo esc_html( $filters_active ); ?></span>
                    <svg class="c-filters__toggle-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <?php endif; ?>

                <button type="submit" class="c-filters__submit <?php echo esc_attr( $filters['submit_class'] ); ?>">
                    <span class="c-filters__submit-spinner"></span>
                    <span class="c-filters__submit-label">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <span>Buscar</span>
                    </span>
                </button>
            </div>

        </div>

        <?php if ( ! empty( $filters_more ) ) : ?>
        <div class="c-filters__more js-filters-more<?php echo $filters_open ? ' is-open' : ''; ?>">
            <div class="c-filters__grid">
                <?php foreach ( $filters_more as $field ) $filters_render_field( $field ); ?>
            </div>
        </div>
        <?php endif; ?>

    </form>
</div>
