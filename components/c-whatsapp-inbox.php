<?php
/**
 * Inbox de WhatsApp — Fase 5 (solo lectura). Todo el contenido dinámico
 * (listado, mensajes, badges) lo pinta JS vía REST; este componente solo
 * monta el esqueleto estático y los contenedores vacíos.
 *
 * ?conversation=ID abre esa conversación directamente al cargar (usado por
 * el botón de la ficha de cliente y la sección del SAT).
 */
$wa_preopen = isset( $_GET['conversation'] ) ? intval( $_GET['conversation'] ) : 0;
?>
<div class="c-whatsapp-inbox js-whatsapp-inbox" data-open-conversation="<?php echo esc_attr( $wa_preopen ?: '' ); ?>">

    <div class="c-whatsapp-inbox__list-pane">
        <div class="c-whatsapp-inbox__list-header">
            <h1 class="c-whatsapp-inbox__title">WhatsApp</h1>
        </div>

        <div class="c-whatsapp-inbox__search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="c-whatsapp-inbox__search js-whatsapp-search" placeholder="Buscar conversaciones...">
        </div>

        <div class="c-whatsapp-inbox__filters">
            <button type="button" class="c-whatsapp-inbox__filter js-whatsapp-filter is-active" data-filter="all">Todas</button>
            <button type="button" class="c-whatsapp-inbox__filter js-whatsapp-filter" data-filter="unread">No leídas</button>
        </div>

        <div class="c-whatsapp-inbox__list js-whatsapp-list"></div>
        <div class="c-whatsapp-inbox__list-loading js-whatsapp-list-loading">Cargando conversaciones...</div>
        <div class="c-whatsapp-inbox__list-empty js-whatsapp-list-empty is-hidden">Todavía no hay conversaciones de WhatsApp.</div>
        <button type="button" class="c-whatsapp-inbox__load-more js-whatsapp-load-more-conversations is-hidden">Cargar más</button>
    </div>

    <div class="c-whatsapp-inbox__chat-pane js-whatsapp-chat-pane">

        <div class="c-whatsapp-inbox__chat-empty js-whatsapp-chat-empty">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <p>Selecciona una conversación</p>
        </div>

        <div class="c-whatsapp-inbox__chat js-whatsapp-chat is-hidden">
            <div class="c-whatsapp-inbox__chat-header">
                <button type="button" class="c-whatsapp-inbox__back js-whatsapp-back" aria-label="Volver a conversaciones">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="c-whatsapp-inbox__chat-header-info">
                    <div class="c-whatsapp-inbox__chat-name js-whatsapp-chat-name"></div>
                    <div class="c-whatsapp-inbox__chat-meta js-whatsapp-chat-meta"></div>
                </div>
            </div>

            <div class="c-whatsapp-inbox__messages js-whatsapp-messages">
                <button type="button" class="c-whatsapp-inbox__load-older js-whatsapp-load-older is-hidden">Cargar mensajes anteriores</button>
                <div class="js-whatsapp-messages-list"></div>
            </div>

            <div class="c-whatsapp-inbox__composer" title="El envío estará disponible próximamente">
                <input type="text" class="c-whatsapp-inbox__composer-input" placeholder="El envío estará disponible próximamente..." disabled>
                <button type="button" class="c-whatsapp-inbox__composer-send" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
        </div>

    </div>

</div>
