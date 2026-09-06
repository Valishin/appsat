# Módulo WhatsApp (WhatsApp Business Cloud API)

Integración con la **API oficial de WhatsApp Business Platform (Cloud API)** de Meta, directamente dentro de la app SAT. No usa `wa.me`, WhatsApp Web ni APIs no oficiales.

Este documento se actualiza según avanzan las fases. Estado actual: **Fase 4 completada** (webhook: verificación, firma, recepción de mensajes/statuses, idempotencia). Faltan inbox/chat, envío de mensajes, media entrante/saliente y asociación con cliente/SAT.

## 1. Arquitectura

- **Sin frameworks nuevos**: PHP procedural (igual que el resto del tema), JS vanilla (mismo patrón `av_*` + `av_call_fn` de `src/js/app.js`).
- **Tablas propias** (no CPT) para conversaciones/mensajes — es la única parte del tema que usa tablas custom; el resto sigue funcionando con CPTs/ACF sin tocar nada.
- **REST API** (`register_rest_route`), primera vez que se usa en este tema, **exclusiva para este módulo**. El resto de la app sigue con `admin-post.php`/`admin-ajax.php` tal cual.
- **Multiempresa preparado, no implementado**: todas las tablas tienen `tenant_id` (fijo a `1` mientras la app sea de un solo taller). Ver `av_whatsapp_current_tenant_id()` en `inc/whatsapp/whatsapp-config.php`.

### Archivos

```
inc/whatsapp/
  whatsapp-encryption.php   Cifrado/descifrado de credenciales (AES-256-CBC)
  whatsapp-db.php           Esquema de las 3 tablas + versión/migración (dbDelta)
  whatsapp-config.php       Versión de Graph API centralizada, tenant activo, logging
  whatsapp-settings.php     Lectura/escritura de la cuenta (wp_sat_whatsapp_accounts) + validaciones
  whatsapp-api.php          Cliente HTTP hacia Meta (wp_remote_request) + test de conexión
  whatsapp-phone.php        Normalización de teléfono centralizada (única función, la usa todo el módulo)
  whatsapp-webhook.php      Verificación GET, firma, recepción POST, idempotencia, conversaciones/mensajes/statuses
  whatsapp-rest.php         Endpoints REST internos (test-connection, webhook GET/POST)

components/config/c-config-tab-whatsapp.php   Pestaña "WhatsApp" en Configuración general
```

## 2. Tablas

Prefijo `wp_sat_whatsapp_*` (dbDelta, se crean/actualizan automáticamente en cualquier carga de página si `AV_WHATSAPP_DB_VERSION` cambia — no requiere ningún paso manual).

**`wp_sat_whatsapp_accounts`** — credenciales, una fila por número de WhatsApp conectado (hoy siempre 1, `tenant_id = 1`).
`id, tenant_id, label, phone_number_id, whatsapp_business_account_id, display_phone_number, access_token_enc, app_secret_enc, verify_token_enc, status, last_error, connected_at, created_at, updated_at`

**`wp_sat_whatsapp_conversations`** — una por cliente y número de WhatsApp. `UNIQUE(whatsapp_account_id, phone_number)`.
`id, tenant_id, whatsapp_account_id, customer_id, sat_id, phone_number, contact_name, last_message_at, last_message_preview, unread_count, status, created_at, updated_at`

**`wp_sat_whatsapp_messages`** — `UNIQUE(whatsapp_message_id)` para idempotencia real (si Meta reenvía un webhook, el segundo `INSERT` falla por duplicado en vez de crear un mensaje repetido). `NULL` se permite para mensajes salientes que fallan antes de tener ID de Meta.
`id, conversation_id, whatsapp_message_id, direction, message_type, text_body, media_id, media_attachment_id, filename, mime_type, caption, status, error_message, sent_by, wa_timestamp, raw_payload, created_at, updated_at`

## 3. Seguridad de credenciales

- `access_token`, `app_secret` y `verify_token` se cifran con **AES-256-CBC** antes de guardarse (`av_whatsapp_encrypt()`/`av_whatsapp_decrypt()`). La clave se deriva con `hash_hmac('sha256', 'av_whatsapp_credentials', AUTH_KEY.SECURE_AUTH_KEY.SECURE_AUTH_SALT, true)` — reutiliza las claves secretas que ya viven en `wp-config.php` (nunca en base de datos). No se añade ninguna dependencia nueva (OpenSSL es núcleo de PHP).
- **El frontend nunca recibe un secreto.** `av_whatsapp_get_account_public()` solo expone `has_access_token`/`has_app_secret`/`has_verify_token` (booleanos). La pantalla de configuración muestra "Configurado ✓" y un botón "Cambiar" que revela un campo vacío de sustitución — nunca precarga el valor real.
- Los campos secretos del formulario son de **sustitución**: si se dejan en blanco al guardar, se conserva lo que ya había.
- Los logs (`av_whatsapp_log()`) nunca incluyen el token ni el texto libre de error de Meta (Meta a veces devuelve el propio valor recibido dentro de `error.message`, p. ej. en tokens malformados — por eso solo se registran campos estructurados: código HTTP, código Meta, subcódigo y tipo).

## 4. Versión de Graph API

Centralizada en `inc/whatsapp/whatsapp-config.php`:
```php
define( 'AV_WHATSAPP_GRAPH_API_VERSION', 'v25.0' );
```
Para actualizarla cuando Meta publique una versión nueva, solo hay que cambiar esta constante — ningún otro archivo tiene la versión escrita a mano.

## 5. Configuración de Meta (necesaria antes de poder usar el módulo)

Pasos según la documentación oficial vigente de Meta (developers.facebook.com):

1. Crear/tener una cuenta de **Meta Business Manager**.
2. Crear una **WhatsApp Business Account (WABA)** dentro de ese Business Manager.
3. Añadir un **número de teléfono** dedicado y verificarlo (no puede ser un número que ya use WhatsApp normal o WhatsApp Business app — hay que migrarlo o usar uno nuevo).
4. Crear una **App en Meta for Developers** (developers.facebook.com) y añadirle el producto "WhatsApp".
5. Generar un **Access Token permanente** vía **System User** en el Business Manager (Configuración del negocio → Usuarios del sistema → crear uno nuevo → asignarle la App con "Administrar app" y la WABA con "Administrar cuentas de WhatsApp Business" → Generar token). Los tokens personales caducan; el token de producción debe ser el de un System User.
6. Copiar el **App Secret** desde la configuración de la App (Configuración básica).
7. Inventar un **Verify Token**: una cadena secreta cualquiera que se configura tanto en Meta como en esta app (se usa solo en el handshake de verificación del webhook, fase 4).
8. Copiar el **Phone Number ID** y el **WhatsApp Business Account ID** (identificadores internos de Meta, no son el número de teléfono).

Todo esto se introduce en **Configuración → WhatsApp** dentro de la app.

## 5.1 Webhook: URL a configurar en Meta

**URL del webhook (entorno local actual, no accesible desde Meta tal cual):**
```
http://localhost:8888/appsat/wp-json/sat/v1/whatsapp/webhook
```
Meta exige HTTPS público, así que localhost no sirve directamente — ver "Desarrollo local" más abajo. En producción, la URL será la equivalente sobre el dominio real:
```
https://TU-DOMINIO/wp-json/sat/v1/whatsapp/webhook
```

En el panel de Meta (App → WhatsApp → Configuration → Webhook):
1. Pegar esa URL en **Callback URL**.
2. Pegar el **Verify Token** que se haya guardado en Configuración → WhatsApp (ver §3, es un valor propio, no lo genera Meta).
3. Pulsar "Verify and save" — Meta hace un `GET` a la URL con `hub.mode=subscribe`, y esta app responde con el `hub.challenge` recibido si el `verify_token` coincide (ver §5.3).
4. Suscribirse al campo **`messages`** (trae tanto mensajes entrantes como statuses de mensajes salientes — no hace falta suscribirse a nada más para esta fase).

## 5.2 Cómo funciona la verificación GET

`GET /wp-json/sat/v1/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=...&hub.challenge=...`

- PHP convierte automáticamente los puntos de `hub.mode`/`hub.verify_token`/`hub.challenge` en guiones bajos al poblar `$_GET` (comportamiento nativo de PHP con cualquier query string, no específico de WordPress) — el código lee `$_GET['hub_mode']`, etc.
- Si `hub.mode !== 'subscribe'` o falta `hub.verify_token` → **403**, sin cuerpo.
- Se compara `hub.verify_token` (con `hash_equals()`, comparación segura frente a timing attacks) contra el `verify_token` descifrado de **cada** cuenta configurada — no hay un token global hardcodeado, cada cuenta tiene el suyo en `wp_sat_whatsapp_accounts`.
- Si coincide con alguna → **200**, `Content-Type: text/plain`, cuerpo = el propio `hub.challenge` (como entero). Si no coincide con ninguna → **403**.

## 5.3 Cómo funciona la recepción POST y la firma

`POST /wp-json/sat/v1/whatsapp/webhook` — público, sin login ni nonce de WordPress (Meta no los manda). La seguridad es el mecanismo propio de Meta:

1. Se lee el **cuerpo crudo** de la petición (`WP_REST_Request::get_body()` — imprescindible que sea el texto exacto tal cual llegó, no una versión re-serializada, porque la firma es un HMAC sobre esos bytes exactos).
2. Se calcula `hash_hmac('sha256', $cuerpo_crudo, $app_secret)` y se compara (con `hash_equals()`) contra la cabecera `X-Hub-Signature-256: sha256=...`.
3. **No hay una cuenta identificada todavía en este punto** (el payload aún no se ha leído), así que se prueba la firma contra el `app_secret` de **cada** cuenta configurada. Con una sola cuenta (caso actual) es una comparación; si en el futuro hay varias, sigue siendo correcto porque normalmente todas comparten la misma App de Meta (un único Callback URL/App Secret puede recibir eventos de varios números/WABAs).
4. Si ninguna coincide → **403**, se descarta sin procesar nada.
5. Si coincide → se decodifica el JSON y se recorre `entry[].changes[].value`. Para cada "change" se lee `value.metadata.phone_number_id` y **ahí sí** se identifica la cuenta exacta a la que pertenece ese mensaje/status concreto (`wp_sat_whatsapp_accounts.phone_number_id`) — nunca se asume "la cuenta 1".
6. Si ese `phone_number_id` no corresponde a ninguna cuenta conocida → se registra (`WEBHOOK_ACCOUNT_NOT_FOUND`) y se ignora, sin crear ningún dato. Se responde **200** igualmente (evita que Meta reintente algo que nunca vamos a poder procesar).
7. La respuesta a Meta es siempre rápida: todo el procesamiento son escrituras ligeras en las tablas propias, sin llamadas HTTP salientes ni descarga de media en esta fase — no hace falta cola/worker en background.

## 5.4 Idempotencia

`whatsapp_message_id` es `UNIQUE` en `wp_sat_whatsapp_messages`. Antes de insertar se comprueba si ya existe (evita trabajo innecesario: normalizar teléfono, resolver conversación...), pero la protección **real** es la propia restricción de la base de datos — si dos entregas del mismo evento llegan casi a la vez y ambas pasan la comprobación previa, el `INSERT` de la segunda falla por clave duplicada y se descarta sin crear un mensaje repetido. Nunca se garantiza "cero duplicados" solo con un `SELECT` previo (condición de carrera), por eso el índice único es la garantía definitiva.

## 5.5 Statuses y mensajes desordenados

Se procesan `sent`/`delivered`/`read`/`failed`, buscando el mensaje local por `whatsapp_message_id`.

- **Si el mensaje local no existe todavía** cuando llega su status: no se crea una fila "hueca" (no habría `conversation_id`/`direction`/`message_type` reales — sería un dato corrupto). Se registra en el log y se descarta. Esto no debería ocurrir en la práctica para mensajes que esta app envíe (Fase 6): el `id` de Meta se recibe en la misma respuesta HTTP de la llamada de envío, así que el mensaje local ya existe antes de que pueda llegar cualquier webhook de status sobre él. El caso solo se daría con mensajes ajenos a esta app (im probable, dado que solo se reciben eventos de números configurados aquí).
- **Statuses que llegan desordenados** (p. ej. un `sent` que llega después de que ya se haya registrado `read`, por reintentos/reordenación de red): se ignoran si representan un estado "anterior" al ya guardado (`pending < sent < delivered < read`, con `failed` como terminal). Así el estado mostrado nunca retrocede.

## 5.6 Conversación y teléfono

Al llegar un mensaje se busca la conversación por `(whatsapp_account_id, phone_number)`; si no existe, se crea con `customer_id`/`sat_id` en `NULL` (los asociará la Fase 8). Se actualizan `last_message_at`, `last_message_preview`, `unread_count` (+1) y `updated_at`. El nombre de contacto que manda Meta se guarda solo si no está vacío y es distinto del ya guardado — nunca se sobrescribe un nombre existente con uno vacío.

El número se normaliza con la única función centralizada `av_whatsapp_normalize_phone()` (`inc/whatsapp/whatsapp-phone.php`) — a dígitos, sin `+`, aceptando `+34 600000000` / `0034 600000000` / `600000000` (esta última solo si se le pasa un prefijo por defecto, no aplica al webhook porque Meta ya manda el número completo). Si no se puede normalizar con confianza, devuelve `''` y el mensaje se descarta con log, sin inventar ningún país.

## 5.7 Tipos de mensaje

`text` se procesa completo (`text_body`). Para `image`/`video`/`audio`/`sticker`/`document` se guarda el `media_id`/`mime_type`/`caption`/`filename` que YA viene en el payload, sin descargar nada todavía (Fase 7). `location` guarda `"lat,lng"` como texto. Cualquier tipo no reconocido (incluido uno nuevo que Meta añada en el futuro) no rompe el webhook: se guarda `message_type` tal cual y el `raw_payload` íntegro del mensaje, sin contenido estructurado adicional.

## 5.8 Qué se guarda en `raw_payload`

Solo el objeto del mensaje o del status **individual** (no el payload completo del webhook, que puede traer varios mensajes/contactos a la vez) — tamaño acotado, JSON válido. Nunca contiene tokens ni secretos: Meta no los incluye en el contenido de un mensaje/status bajo ninguna circunstancia.

## 6. Test de conexión

El botón "Probar conexión" hace una llamada **real** a Meta (`GET /{version}/{phone_number_id}?fields=verified_name,display_phone_number,quality_rating,code_verification_status`, y si hay WABA ID también `GET /{version}/{waba_id}?fields=id,name`), no solo comprueba que los campos estén rellenados. Actualiza `status`/`last_error`/`connected_at` en la tabla de cuentas y nunca expone el payload crudo de Meta, solo un mensaje traducido.

## 7. Desarrollo local

Este entorno usa MAMP sin HTTPS público (`http://localhost:8888/appsat`). Meta exige una URL `https://` accesible desde internet para el webhook — hará falta un túnel (p. ej. `ngrok http 8888` o Cloudflare Tunnel) apuntando al sitio local mientras se configura el webhook en el panel de Meta, y usar la URL pública que dé el túnel (terminada en `/wp-json/sat/v1/whatsapp/webhook`) en el paso de configuración de Meta. El test de conexión (fase 3) no lo necesita: es una llamada saliente de nuestro servidor hacia Meta, no una entrante. Las pruebas de la Fase 4 en este entorno se han hecho con `curl` directo al endpoint local (ver §11.1), simulando exactamente lo que Meta enviaría (misma firma HMAC, mismo formato de payload) — funcionalmente equivalente a una llamada real de Meta, solo falta la conectividad pública para que Meta pueda alcanzar la URL por sí sola.

## 8. Producción

- Configurar el dominio real con HTTPS válido.
- Configurar la URL del webhook en el panel de Meta apuntando a `https://tu-dominio/wp-json/sat/v1/whatsapp/webhook`.
- Revisar que `AUTH_KEY`/`SECURE_AUTH_KEY`/`SECURE_AUTH_SALT` en `wp-config.php` sean los definitivos del entorno de producción **antes** de configurar credenciales de WhatsApp: son la base del cifrado, y si cambian después, las credenciales ya guardadas dejan de poder descifrarse (habría que volver a introducirlas).

## 9. Ventana de 24 horas y plantillas

WhatsApp solo permite texto libre durante las 24h siguientes al último mensaje del cliente. Fuera de esa ventana, es obligatorio usar una **plantilla aprobada por Meta**. Esto afecta directamente a "iniciar conversación" desde la ficha de un cliente que nunca ha escrito: no habrá ventana abierta, así que solo se podrá usar una plantilla. La arquitectura (tablas, servicio API) está lista para esto, pero el envío de plantillas todavía no está implementado (fases posteriores) — se dejará claramente indicado en la UI cuándo hace falta una plantilla en vez del composer normal.

## 10. Multiempresa

Todas las tablas tienen `tenant_id`. Hoy `av_whatsapp_current_tenant_id()` devuelve siempre `1`. Para dar de alta un segundo taller en el futuro: sustituir esa función por la resolución real del tenant (usuario logueado, dominio, etc.) — el resto del módulo ya filtra y guarda por `tenant_id`, no haría falta rehacer nada.

## 11. Troubleshooting

- **"Faltan credenciales por configurar"** al probar conexión → falta Access Token o Phone Number ID.
- **"El Access Token no es válido o ha caducado"** → token incorrecto, caducado, o no es un token de System User (código Meta 190).
- **"Los datos de conexión (...) no son correctos"** → Phone Number ID o WABA ID no existen o no pertenecen a ese token.
- **"El Phone Number ID es correcto, pero el WhatsApp Business Account ID no."** → el número sí es válido pero el WABA ID no le corresponde (posible copia de otra cuenta).
- **La verificación del webhook en Meta falla ("The callback URL or verify token couldn't be validated")** → comprobar que el Verify Token pegado en Meta es EXACTAMENTE el mismo que se guardó en Configuración → WhatsApp (sensible a mayúsculas/espacios), y que la URL apunta a `/wp-json/sat/v1/whatsapp/webhook` (no a la raíz del sitio).
- **Meta reintenta el mismo evento muchas veces** → el endpoint no está devolviendo 200 a tiempo, o está devolviendo 403/400 por algo que debería aceptarse (revisar `WEBHOOK_SIGNATURE_INVALID` en el log: normalmente indica que el App Secret guardado no es el de la App que realmente está enviando el webhook).
- **Un mensaje llega pero no aparece ninguna conversación nueva** → revisar `WEBHOOK_ACCOUNT_NOT_FOUND` en el log: el `phone_number_id` del evento no coincide con el guardado en Configuración → WhatsApp (puede pasar si se prueba el webhook contra un número de prueba de Meta distinto al configurado).
- Los errores técnicos completos (con código de Meta) se registran vía `error_log()`, nunca con el token, el secret, ni el texto libre de error de Meta (que en algunos casos incluye el propio valor recibido).

## 11.1 Cómo probar el webhook manualmente (sin esperar a Meta)

Verificación GET:
```bash
curl "http://localhost:8888/appsat/wp-json/sat/v1/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=TU_VERIFY_TOKEN&hub.challenge=12345"
```
Debe responder `12345` con HTTP 200 si el token coincide con el guardado, o 403 si no.

Mensaje entrante (con firma real):
```bash
BODY='{"object":"whatsapp_business_account","entry":[{"id":"<WABA_ID>","changes":[{"value":{"metadata":{"phone_number_id":"<PHONE_NUMBER_ID>"},"contacts":[{"profile":{"name":"Prueba"},"wa_id":"34600000000"}],"messages":[{"from":"34600000000","id":"wamid.PRUEBA123","timestamp":"1700000000","type":"text","text":{"body":"Hola"}}]},"field":"messages"}]}]}'
SIG=$(printf '%s' "$BODY" | openssl dgst -sha256 -hmac "TU_APP_SECRET" | sed 's/^.* //')
curl -X POST "http://localhost:8888/appsat/wp-json/sat/v1/whatsapp/webhook" \
  -H "Content-Type: application/json" \
  -H "X-Hub-Signature-256: sha256=$SIG" \
  -d "$BODY"
```
`<PHONE_NUMBER_ID>` debe coincidir con el guardado en Configuración → WhatsApp para que se procese (si no, se ignora de forma segura con `WEBHOOK_ACCOUNT_NOT_FOUND`).

## 12. Eventos soportados

- **Mensajes entrantes**: `text` (completo), `image`/`video`/`audio`/`sticker`/`document` (metadatos disponibles en el payload — `media_id`/`mime_type`/`caption`/`filename` — sin descargar el binario todavía), `location` (coordenadas como texto), cualquier otro tipo (se guarda `message_type` + `raw_payload`, sin romper el webhook).
- **Statuses**: `sent`, `delivered`, `read`, `failed` (con el motivo del error cuando Meta lo proporciona). Cualquier otro valor de status se ignora de forma segura.
- **Eventos no reconocidos** (entradas sin `messages` ni `statuses`, o sin `metadata.phone_number_id`): se registran como `WEBHOOK_UNKNOWN_EVENT` y se ignoran, siempre respondiendo 200.

## 13. Comportamiento ante duplicados

Un mismo evento puede llegar más de una vez (reintentos de Meta, entregas casi simultáneas). `whatsapp_message_id` es `UNIQUE` en la tabla de mensajes: la segunda vez, el `INSERT` falla por clave duplicada y no se crea ningún mensaje repetido — se considera procesado correctamente (log `WEBHOOK_MESSAGE_DUPLICATE`, respuesta 200). Ver §5.4 para el detalle completo.

## 14. Limitaciones actuales

- No hay inbox/chat: los mensajes ya se guardan en BD, pero no hay interfaz todavía (Fase 5).
- No hay envío de mensajes desde la app (Fase 6).
- No hay descarga/subida de media — solo se guardan los metadatos que Meta incluye en el propio evento (Fase 7).
- No hay asociación automática con cliente/SAT — toda conversación entrante queda con `customer_id`/`sat_id` en `NULL` (Fase 8).
- Solo se ha probado contra un entorno local sin HTTPS público, simulando peticiones de Meta con `curl` (firma real incluida) — la conexión real con el panel de Meta necesitará un túnel (§7) o el dominio de producción.
