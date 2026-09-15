# Meta Pixel + Conversions API (Modelarc)

## Arquitectura

| Evento | Browser Pixel | Server CAPI |
|--------|---------------|-------------|
| **PageView** | Sí (SPA router) | No (fase actual) |
| **Contact** | Sí (WhatsApp / `tel:`) | No |
| **Lead** | Sí (solo tras HTTP 201) | Sí (queue job) |

Deduplicación Lead: mismo `event_id` (browser `eventID` ↔ CAPI `event_id`).

## Consentimiento

Categoría **marketing** (ver `docs/architecture/ANALYTICS_PRIVACY_PLAN.md`).

Defense in depth:

| Canal | Gate |
|-------|------|
| Browser Pixel | Solo si `consentStore.marketing === true` |
| Browser Lead | Solo tras HTTP 201 **y** marketing consent |
| Server CAPI | Solo si `META_CONVERSIONS_API_ENABLED` **y** `marketing_consent=true` **y** `meta_event_id` válido |

Sin consentimiento marketing:

- El formulario de contacto **sigue funcionando** (Lead CRM normal).
- **No** se carga Pixel / no PageView / Contact / Lead browser.
- **No** se envían `meta_event_id`, `_fbp`, `_fbc` al API.
- **No** se despacha `SendMetaConversionJob` (aunque un cliente manipule el request: el backend exige `marketing_consent=true`).

`marketing_consent` es **transitorio** en el request HTTP. **No** se persiste en la tabla `leads`.

## ENV

### API (`apps/api/.env`)

```
META_PIXEL_ID=
META_PIXEL_ENABLED=false
META_CONVERSIONS_API_TOKEN=
META_CONVERSIONS_API_ENABLED=false
META_TEST_EVENT_CODE=
# META_GRAPH_API_VERSION=v21.0
```

`META_CONVERSIONS_API_TOKEN` es **secreto de servidor**. Nunca en Vue, Quasar, `site_settings`, docs con valor real, ni Git.

### Web (`apps/web` build env)

```
VITE_META_PIXEL_ID=
VITE_META_PIXEL_ENABLED=true
```

El Pixel ID **no** es secreto. El access token **nunca** tiene prefijo `VITE_`.

## Eventos

### PageView

Tras consentimiento + init; en navegación inicial y `router.afterEach` de rutas públicas del website. Evita path duplicado consecutivo.

### Contact

`fbq('track', 'Contact', { contact_method, content_name, page_path })`

- `contact_method`: `whatsapp` | `phone`
- `content_name` / placement: `floating` | `footer` | `contact_page` | `contact_social` | …

Placements actuales: botón flotante, footer (texto + icono social), página Contacto (aside + social). No hay enlaces `tel:` hoy; el helper ya soporta `phone` si se añaden.

### Lead

1. Si marketing consent: frontend genera UUID `meta_event_id` y envía `marketing_consent=true` (+ opcional `meta_fbp` / `meta_fbc`).
2. Si marketing rechazado: POST con `marketing_consent=false` y **sin** campos Meta.
3. Backend crea Lead → responde 201.
4. Backend encola CAPI **solo** si CAPI enabled + `marketing_consent=true` + `meta_event_id`.
5. Frontend, solo si éxito **y** marketing consent: `fbq('track', 'Lead', params, { eventID })`.
6. 422 / 500 / error de red → **no** Lead browser.

Custom params browser (no PII): `lead_type`, `service`, `budget_range`, `country`.

## Backend

- Config: `config/services.php` → `services.meta.*`
- Service: `App\Services\MetaConversionsService`
- Job: `App\Jobs\SendMetaConversionJob` (`tries=3`, backoff `30,120,300`)
- Hasher: `App\Support\MetaUserDataHasher`
- TrustProxies: `*` en `bootstrap/app.php` para IP correcta detrás de nginx

CAPI endpoint: `POST https://graph.facebook.com/{version}/{pixel_id}/events`

User data hasheada (cuando existe): `em`, `ph`, `fn`, `ln`, `ct`, `st`, `country` + IP/UA efímeros.

Fallos de Meta **no** revierten ni bloquean la creación del Lead.

## QA

1. Sin consentimiento: Network no carga `fbevents.js`.
2. Aceptar marketing: carga Pixel + PageView.
3. Navegar entre páginas: un PageView por ruta.
4. Click WhatsApp: evento Contact.
5. Enviar formulario OK: Lead browser + job CAPI con mismo event_id.
6. Forzar 422: sin Lead.

## Meta Test Events

1. En Events Manager → Test Events, copiar código de prueba.
2. En API `.env`: `META_TEST_EVENT_CODE=...` y `META_CONVERSIONS_API_ENABLED=true`.
3. `php artisan config:cache`
4. Enviar un lead de prueba desde el website (con marketing aceptado).
5. Verificar en Test Events el Lead (browser + server deduplicados).
6. Quitar `META_TEST_EVENT_CODE` en producción real.

## Producción (post-deploy, manual)

```
META_PIXEL_ID=1053858764178204
META_PIXEL_ENABLED=true
META_CONVERSIONS_API_TOKEN=<configurar solo en servidor>
META_CONVERSIONS_API_ENABLED=true
META_TEST_EVENT_CODE=
```

Rebuild website con `VITE_META_PIXEL_ID` / `VITE_META_PIXEL_ENABLED`. Reiniciar queue tras cambiar env.

## Troubleshooting

| Síntoma | Revisar |
|---------|---------|
| Sin eventos browser | Consentimiento marketing; `VITE_META_PIXEL_*` en build |
| CAPI no llega | `META_CONVERSIONS_API_*`, `config:cache`, logs queue, job failed |
| Duplicados Lead | `event_id` distinto browser/server; nombres `Lead` vs `lead` |
| Lead OK pero sin Pixel | Usuario rechazó marketing (CAPI igual puede enviarse server-side) |

Nota: tanto Pixel como CAPI requieren consentimiento **marketing**. Sin él, el Lead CRM se crea igual y no se transmite nada a Meta.
