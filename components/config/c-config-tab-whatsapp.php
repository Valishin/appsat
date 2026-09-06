<?php
/**
 * Pestaña "WhatsApp" de la Configuración general.
 * Autocontenida: obtiene sus propios datos (no depende de $cfg, que es
 * específico de la pestaña de Facturación).
 */

$wa_account = av_whatsapp_get_account_public();
$wa_uid     = get_current_user_id();
$wa_errors  = get_transient( 'av_whatsapp_config_errors_' . $wa_uid );
if ( $wa_errors ) delete_transient( 'av_whatsapp_config_errors_' . $wa_uid );

$wa_status = $wa_account['status'];
$wa_status_label = [
    'connected'    => '🟢 Conectado',
    'error'        => '🔴 Error de conexión',
    'disconnected' => '⚪ Sin probar',
][ $wa_status ] ?? '⚪ Sin probar';
?>

<?php if ( $wa_errors ) : ?>
<div class="c-cfg__notice c-cfg__notice--error">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
        <?php foreach ( $wa_errors as $err ) : ?>
        <div><?php echo esc_html( $err ); ?></div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="c-cfg__section c-cfg__wa-status-section">
    <div class="c-cfg__section-title">Estado de conexión</div>

    <div class="c-cfg__wa-status-row">
        <span class="js-whatsapp-status c-cfg__wa-status c-cfg__wa-status--<?php echo esc_attr( $wa_status ); ?>">
            <?php echo esc_html( $wa_status_label ); ?>
        </span>
        <button type="button"
                class="c-sat-form__cta c-sat-form__cta--secondary js-whatsapp-test-connection"
                <?php echo empty( $wa_account['has_access_token'] ) || empty( $wa_account['phone_number_id'] ) ? 'disabled title="Guarda primero el Access Token y el Phone Number ID"' : ''; ?>>
            Probar conexión
        </button>
    </div>

    <?php if ( ! empty( $wa_account['display_phone_number'] ) ) : ?>
    <div class="c-cfg__wa-status-detail">Número: <strong>+<?php echo esc_html( $wa_account['display_phone_number'] ); ?></strong></div>
    <?php endif; ?>

    <div class="js-whatsapp-status-message c-cfg__wa-status-message"><?php echo esc_html( $wa_account['last_error'] ?: '' ); ?></div>
</div>

<form method="POST" action="<?php echo esc_url( get_permalink() ); ?>" class="c-cfg__form">
    <?php wp_nonce_field( 'av_whatsapp_config' ); ?>
    <input type="hidden" name="av_whatsapp_config_save" value="1">
    <input type="hidden" name="av_cfg_tab" value="whatsapp">

    <div class="c-cfg__section">
        <div class="c-cfg__section-title">Cuenta de WhatsApp Business</div>

        <div class="c-cfg__field">
            <label>Nombre / etiqueta</label>
            <input type="text" name="wa_label" class="c-cfg__input"
                   value="<?php echo esc_attr( $wa_account['label'] ); ?>" placeholder="Taller Central">
        </div>

        <div class="c-cfg__field">
            <label>Phone Number ID</label>
            <input type="text" name="wa_phone_number_id" class="c-cfg__input"
                   value="<?php echo esc_attr( $wa_account['phone_number_id'] ); ?>" placeholder="Solo dígitos, p.ej. 109876543210987">
        </div>

        <div class="c-cfg__field">
            <label>WhatsApp Business Account ID</label>
            <input type="text" name="wa_waba_id" class="c-cfg__input"
                   value="<?php echo esc_attr( $wa_account['whatsapp_business_account_id'] ); ?>" placeholder="Solo dígitos">
        </div>

        <div class="c-cfg__field">
            <label>Número visible</label>
            <input type="text" name="wa_display_phone" class="c-cfg__input"
                   value="<?php echo esc_attr( $wa_account['display_phone_number'] ); ?>" placeholder="+34 600 000 000">
        </div>
    </div>

    <div class="c-cfg__section">
        <div class="c-cfg__section-title">Credenciales (Meta)</div>

        <?php
        $wa_secret_fields = [
            'wa_access_token' => [ 'label' => 'Access Token', 'has' => $wa_account['has_access_token'], 'placeholder' => 'Token permanente (System User)' ],
            'wa_app_secret'   => [ 'label' => 'App Secret',   'has' => $wa_account['has_app_secret'],   'placeholder' => 'App Secret de la App de Meta' ],
            'wa_verify_token' => [ 'label' => 'Verify Token', 'has' => $wa_account['has_verify_token'], 'placeholder' => 'Cadena secreta que tú inventas' ],
        ];
        foreach ( $wa_secret_fields as $field_name => $field ) :
        ?>
        <div class="c-cfg__field">
            <label><?php echo esc_html( $field['label'] ); ?></label>
            <?php if ( $field['has'] ) : ?>
                <div class="c-cfg__wa-secret-set js-wa-secret-set">
                    <span class="c-cfg__wa-secret-badge">Configurado ✓</span>
                    <button type="button" class="c-cfg__wa-secret-change js-wa-secret-change">Cambiar</button>
                </div>
                <input type="password" name="<?php echo esc_attr( $field_name ); ?>"
                       class="c-cfg__input js-wa-secret-input c-cfg__wa-hidden"
                       placeholder="Nuevo valor" autocomplete="new-password">
            <?php else : ?>
                <input type="password" name="<?php echo esc_attr( $field_name ); ?>"
                       class="c-cfg__input" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" autocomplete="new-password">
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <p class="c-cfg__wa-hint">Los secretos ya guardados nunca se muestran ni se envían al navegador. Deja los campos en blanco para conservarlos.</p>
    </div>

    <div class="c-cfg__actions">
        <button type="submit" class="c-sat-form__cta c-sat-form__cta--primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Guardar configuración
        </button>
    </div>
</form>
