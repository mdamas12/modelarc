# MODELARC — AUDITORÍA TÉCNICA DE NUEVOS REQUERIMIENTOS

**Fecha:** 2026-09-15
**Alcance:** Monorepo `C:\modelarc` (`apps/api`, `apps/web`, `apps/admin`)
**Modo:** Solo lectura — sin cambios de código, migraciones, commit, push ni deploy
**Método:** Evidencia en código, rutas, modelos, migraciones, formularios y flujos API

---

# 1. Resumen ejecutivo

| Métrica | Valor |
|---------|-------|
| **Total requisitos auditados** | 28 |
| **🟢 Implementados** | 5 |
| **🟡 Parciales** | 9 |
| **🔴 No implementados** | 11 |
| **🔵 Configuración externa** | 3 |
| **⚠️ Riesgos encontrados** | 8 |

**Estado general:** Modelarc es un CMS/API sólido para contenido (proyectos, categorías/subcategorías, servicios, hero, quiénes somos, tours 360°, testimonios, leads básicos y usuarios). El **nuevo alcance** (analytics avanzado, CRM Kanban, Google Business / Reviews, scoring, SEO técnico, límite Home de 4 elementos, correo 100 % centralizado en Gmail) está **mayormente sin desarrollar**.

> **Actualización Fase 1 (2026-09-15):** se completaron fundamentos P0 de correo comercial (`MAIL_TO_ADDRESS` → `modelarcca@gmail.com`), WhatsApp público configurable y visible en mobile, `budget_range` en formulario + filtros/geo en Solicitudes, eliminación de mocks de visitas/storage en Dashboard, e índices/settings WhatsApp. Ver `docs/implementation/MODELARC_PHASE_1.md`. Analytics de audiencia, Kanban y GBP **siguen pendientes**.

**Veredicto anticipado:** el sitio y el admin actuales **no cubren** el paquete completo de nuevos requerimientos. Tras Fase 1, el cumplimiento estimado del nuevo alcance sube a **~35–40 %** (fundamentos); el resto del backlog (analytics/CRM avanzado/SEO) permanece abierto.

---

# 2. Arquitectura encontrada

### Backend
- **Stack:** Laravel **13.20** (declarado `^13.8`), PHP `^8.3`
- **Auth:** Laravel Sanctum (Bearer tokens). Sin Passport/JWT. `statefulApi()` deshabilitado a propósito (`bootstrap/app.php`)
- **Roles:** Spatie Permission — roles seed `superadmin`, `admin`, `editor`. Sin Policies; sin middleware `role:`/`permission:` en rutas
- **Capas:** Models (22), Controllers `Api/Admin` + `Api/Website`, FormRequests (~40), Resources (~20), Services (~13), Jobs (4), Mailables (3)
- **Ausente:** Events/Listeners, Notifications (Laravel), Policies, Middleware custom, Scheduler (`Schedule::`)
- **Rutas:** `routes/api.php` (público `/api/public/*` + admin `/api/admin/*`); `routes/web.php` solo welcome
- **Storage:** disco `public` o DigitalOcean Spaces (`DO_SPACES_*`) vía `MediaService`
- **Media:** Intervention Image → variantes WebP `thumb`/`medium` (`ProcessProjectImage`); panoramas `ProcessPanorama`; **sin** Spatie Media Library
- **Cola/cache:** `QUEUE_CONNECTION=database`, `CACHE_STORE=database`
- **WhatsApp interno (avisos de lead):** CallMeBot (`ContactWhatsAppNotifier` + job)

### Frontend público (`apps/web`)
- **Vue 3.5.x / Quasar 2.21.x**, Vue Router, Pinia, Axios
- **Layout:** `PublicLayout.vue` (header, footer, barra flotante social)
- **Páginas:** Home, Nosotros, Servicios, Proyectos (+ detalle), Recorridos 360 (+ detalle), Contacto, Testimonio por invitación, Blog placeholder, 404
- **Stores:** `homeStore`, `projectStore`, `virtualTourStore`
- **SEO:** title/description estáticos en `index.html` únicamente
- **SSR:** bloque `ssr` en `quasar.config.ts`; **no** se encontró inyección dinámica de meta/OG/JSON-LD en páginas

### Dashboard (`apps/admin`)
- Mismo stack Vue/Quasar
- CRUD: proyectos, categorías/subcategorías, medios, hero, servicios, quiénes somos, testimonios (+ invitaciones), tours editor, solicitudes (lista), usuarios, settings key/value, manuales
- **Sin** módulo Analytics dedicado, **sin** Kanban, **sin** WhatsApp UI

### Database (tablas relevantes)
`users`, `personal_access_tokens`, Spatie permission tables, `project_types`, `media`, `projects`, `project_media`, `gallery_changes`, `virtual_tours`, `tour_scenes`, `tour_hotspots`, `services`, `testimonials`, `testimonial_invitations`, `leads` (+ `country`/`state`/`city`), `activity_logs`, `site_settings`, `we_are`, `we_are_teams`, `heroes`, `hero_galleries`, `categories`, `subcategories`, `jobs`/`cache`/`sessions`

**NO ENCONTRADO EN EL CÓDIGO AUDITADO:**
`analytics_events`, `visitor_sessions`, `page_views`, `lead_notes`, `lead_activities`, `lead_statuses` (tabla), heatmaps, geo-ip tables.

### Infraestructura / integraciones
- Deploy documentado hacia DigitalOcean / dominios `modelarcve.com`, `admin.*`, `api.*`
- Mail SMTP (ejemplo Titan) + `MAIL_TO_ADDRESS` multi-destinatario
- CallMeBot WhatsApp
- Spaces opcional
- **NO ENCONTRADO:** GA4, GTM, Meta Pixel, Google Places/Reviews API, GeoIP/MaxMind, Google Business API

---

# 3. Matriz de cumplimiento

| ID | Requisito | Estado | Evidencia | Qué existe | Qué falta | Prioridad |
|----|-----------|--------|-----------|------------|-----------|-----------|
| A1 | Correo centralizado `Modelarcca@gmail.com` | 🟢 (TO) / 🔵 (FROM SMTP) | `CommercialMail.php`, `ContactMailNotifier.php`, `.env.example` | `MAIL_TO_ADDRESS` central; sin fallbacks comerciales legacy | FROM Gmail solo si se configura SMTP Gmail (no requerido Fase 1) | P0 done |
| A2 | Google Business Profile + oficina PO | 🔴 | NAP hardcodeado en `SiteFooter.vue`, `ContactPage.vue`, `PublicLayout.vue` | Texto “Puerto Ordaz, estado Bolívar, Venezuela” + teléfono | Enlace GBP, Maps embed, Schema LocalBusiness, sync API | P1 |
| A3 | WhatsApp flotante (web; valorar panel) | 🟢 | `PublicLayout.vue`, `whatsapp.ts`, settings `whatsapp_*` | Visible mobile; configurable CMS; footer/contacto | Tracking click (Fase analytics) | P0 done |
| B1 | Home máx. 4 elementos alto impacto | 🔴 | `HomePage.vue` | Hero, servicios, proyectos, tours, proceso (6 pasos mock), testimonios, CTA | Límite 4 (CMS/backend); before/after en home; enforcement | P1 |
| B2 | Optimización imágenes Quiénes somos | 🟡 | `WeAreTeamImageService.php`, `AboutPage.vue`, job/cmd optimize | `display_path` redimensionado; `loading="lazy"` en galería | AVIF, srcset/picture, WebP sistemático en Quiénes somos, LCP controlado | P2 |
| B3 | Presupuesto: País + Estado | 🟢 | `ContactPage.vue`, `StoreLeadRequest.php`, `config/leads.php` | Geo + `budget_range` obligatorio (low/medium/high/special) | Montos monetarios (si negocio los define) | P0 done |
| B4 | Proyectos + subcategorías jerárquicas | 🟢 | `Category.php`, `Subcategory.php`, `Project.php`, `CategoriesPage.vue`, rutas admin/public | 1 categoría + 1 subcategoría por proyecto; CRUD admin; filtros públicos por categoría | M2M multi-categoría; unificar taxonomía media hardcodeada (`mediaTaxonomy.ts`) | P2 |
| B5 | Recorridos 360° (sistema existente) | 🟢 | `VirtualTour*`, `TourEditorPage.vue`, `VirtualTourViewer.vue`, jobs panorama | Visor PSV, escenas, hotspots, fullscreen, publish draft/published, relación proyecto | — (no rediseñar) | — |
| B5b | Indicador/botón estado del recorrido | 🟡 | `ProjectCard.vue`, `ImmersiveTourSection.vue`, `has_virtual_tour`, status tour | Badge “Recorrido 360°” / “360°”; flag proyecto; draft/published admin | Estados richer (processing/unavailable); CTA “Explorar” unificado; indicador en detalle más claro | P2 |
| B6a | Testimonios propios | 🟢 | `Testimonial*`, invitaciones, `TestimonialsSection.vue`, admin | CRUD, rating, invitación por email, home | — | — |
| B6b | Google Reviews | 🔴 | Búsqueda web/admin/api | — | API Places / sync / widget / enlace oficial | P2 |
| C1 | Sesiones y retención | 🔴 | DashboardService + DashboardPage mocks | KPIs de negocio (conteos) | Captura sesiones, duración, tiempo/página, evolución | P1 |
| C2 | Scroll depth 25/50/75/100 | 🔴 | Sin listeners en `apps/web` | — | Frontend + API + persistencia + dashboard | P1 |
| C3 | Drop-off / exit pages | 🔴 | NO ENCONTRADO | — | Modelo de session path + last page | P2 |
| C4 | Top CTAs | 🔴 | Sin event tracking en web | Enlaces CTA estáticos | event_name/type/page/session | P1 |
| C5 | Geolocalización visitantes | 🔴 | Solo geo de **formulario** lead | country/state/city en leads | GeoIP headers/servicio; privacidad/anonimización | P2 |
| C6 | Mapa de calor geográfico | 🔴 | NO ENCONTRADO (libs mapa admin) | — | Datos agregados + UI mapa | P3 |
| C7 | Ranking contenido / visitas únicas | 🟡 | `DashboardService` top por `views_count`; sin mocks | Top proyectos con contador real de ficha | Visitas únicas de audiencia / chart sitio | P1 |
| D1 | Lead asociado a solicitud | 🟢 | `Lead.php`, `LeadsPage.vue`, filtros API | Persistencia + geo/budget visibles + filtros | Historial CRM | P1 |
| D2 | Pipeline Kanban (5+ estados) | 🔴 | `UpdateLeadRequest`: `new\|in_progress\|closed` | Lista + select de 3 estados | Estados de negocio, drag&drop, persistencia, historial | P1 |
| D3 | Densidad presupuesto Bajo/Medio/Alto/Especial | 🟢 (semántico) | `config/leads.php`, form + admin | Categorías semánticas sin montos | Rangos monetarios configurables | P1 |
| D4 | Área/servicio (catálogo) | 🟡 | `services` + select string en contacto (`project_type`) | Servicios administrables; lead guarda texto | Vincular FK a `services.id` | P2 |
| D5 | Filtro geográfico CRM | 🟢 | `LeadService`, `LeadsPage.vue`, índices | Filtros país/estado (+ budget/tipo) combinados | — | P0 done |
| D6 | Lead scoring numérico | 🔴 | NO ENCONTRADO | Etiquetas de status no son score | Matriz scores + total + reglas | P3 |
| SEO | SEO técnico / “Meta” | 🟡 | `apps/web/index.html` | Title + meta description globales | OG/Twitter, canonical, sitemap, robots, JSON-LD, meta por ruta; **no** confundir con Meta Pixel | P1 |
| PERF | Performance imágenes/360 | 🟡 | `ProcessProjectImage`, panoramas, lazy parcial | WebP variants proyectos; queues | srcset consumo consistente; AVIF; CDN; cuidado LCP/memoria 360 | P2 |
| SEC | Privacidad tracking | 🔵 / ⚠️ | Sin consent banner; sin analytics propio aún | — | Antes de analytics: consentimiento, retención, no IP completa | P0 (si se implementa C*) |
| EXT | CallMeBot / Spaces / SMTP | 🔵 | `config/services.php`, `.env.example` | Código listo; depende credenciales | Validar prod; Gmail si se cambia FROM | P0 |

---

# 4. Funcionalidades ya completadas

Verificadas end-to-end (código + rutas + persistencia):

1. **CMS Proyectos** — CRUD, publish/archive, media, gallery before/after, featured, SEO fields en modelo, `views_count`.
2. **Categorías y subcategorías** — jerarquía DB + admin + asignación 1:1 en proyecto + listado público filtrable por categoría.
3. **Servicios** — CRUD admin + página pública.
4. **Hero + Quiénes somos** — textos/galerías administrables; `titulo_hero` / `mensaje_hero` opcionales (vacío = no se muestra).
5. **Recorridos 360°** — escenas, hotspots, visor Photo Sphere, publicación, vínculo a proyecto, listado público.
6. **Testimonios** — CRUD, rating, invitaciones por email/token, render en home.
7. **Lead básico** — `POST /api/public/contact` (y `/leads`), persistencia `leads`, notificación email + WhatsApp CallMeBot, listado/edición status admin.
8. **Usuarios admin** — invite, roles Spatie, block, activation/reset mail.
9. **Contacto web con País/Estado/Ciudad** en cascada (`country-state-city`).
10. **Pipeline de imágenes de proyecto** — Intervention → WebP thumb/medium (job en cola).

---

# 5. Funcionalidades parcialmente desarrolladas

### A1 — Correo
- **QUÉ EXISTE:** Mailables `ContactLeadMail`, `UserAccountMail`, `TestimonialInvitationMail`; `MAIL_TO_ADDRESS` multi-destino; cola `SendContactLeadMailJob`.
- **QUÉ FALTA:** Centralizar en `modelarcca@gmail.com` como FROM y/o único TO; eliminar inconsistencias con `info@modelarcve.com` y `marcosdamas12@gmail.com`; alinear prod vs `.env.example`.
- **ARCHIVOS:** `apps/api/config/mail.php`, `apps/api/app/Services/ContactMailNotifier.php`, `apps/api/.env.example`, vistas `resources/views/emails/*`.
- **RIESGO:** Destinatarios/remitentes mixtos; operador cree que “todo va a Gmail” cuando FROM sigue siendo Titan/`info@`.

### A3 — WhatsApp flotante
- **QUÉ EXISTE:** Barra flotante IG/FB/WA en `PublicLayout.vue`; enlaces en footer/contacto; CallMeBot para avisos internos.
- **QUÉ FALTA:** Número/mensaje desde `site_settings`; click tracking; visibilidad mobile (hoy `display:none` ≤700px).
- **ARCHIVOS:** `PublicLayout.vue`, `SiteFooter.vue`, `ContactPage.vue`, `ContactWhatsAppNotifier.php`.
- **RIESGO UX:** En móvil —donde WhatsApp más se usa— el flotante **desaparece**. Panel admin: no hay botón WA comercial (razonable; el canal comercial es el sitio).

### B2 — Imágenes Quiénes somos
- **QUÉ EXISTE:** `display_path` con ancho máx. 2560; comando `we-are:optimize-team-images`; lazy en galería.
- **QUÉ FALTA:** AVIF/srcset/picture; WebP dedicado; no cargar originales pesados en LCP.
- **ARCHIVOS:** `WeAreTeamImageService.php`, `AboutPage.vue`, `OptimizeWeAreTeamImages`.

### B3 — Presupuesto geo + presupuesto
- **QUÉ EXISTE:** País/Estado/Ciudad requeridos en web + API; guardados en `leads`.
- **QUÉ FALTA:** UI `budget_range`; mostrar geo en admin; filtros.
- **ARCHIVOS:** `ContactPage.vue`, `StoreLeadRequest.php`, `LeadsPage.vue`, `LeadResource.php`.

### B5b — Indicador estado recorrido
- **QUÉ EXISTE:** Badges y `has_virtual_tour`; status draft/published.
- **QUÉ FALTA:** Semántica de “estado del recorrido” más rica y CTA consistente “Explorar recorrido”.

### B6 — Testimonios vs Google
- **QUÉ EXISTE:** Testimonios propios completos.
- **QUÉ FALTA:** Google Reviews (manual, API o deep-link).

### C7 / Dashboard analytics
- **QUÉ EXISTE:** `DashboardService` KPIs reales (conteos proyectos/leads/tours); `views_count` por proyecto.
- **QUÉ FALTA:** Visitas de sitio reales; charts sin mock; visitas únicas.
- **ARCHIVOS:** `DashboardService.php`, `DashboardPage.vue` (`mockDashboard`, `enrichDashboard`).
- **RIESGO:** **Alto** — UI parece analytics real.

### D4 — Servicio en lead
- **QUÉ EXISTE:** Select de servicios/tipos en contacto → `project_type` string.
- **QUÉ FALTA:** FK a `services.id`.

### SEO
- **QUÉ EXISTE:** Title + description globales.
- **QUÉ FALTA:** Meta por página, OG, sitemap, Schema, indexabilidad SPA/SSR.

### Performance
- **QUÉ EXISTE:** WebP variants backend para media de proyectos; colas; lazy parcial.
- **QUÉ FALTA:** Consumo sistemático srcset en web; optimización panoramas; AVIF.

---

# 6. Funcionalidades no desarrolladas

### Infraestructura
- Vinculación oficial Google Business Profile / sync API.
- Correo 100 % centralizado solo en Gmail (remitente + política única).
- Meta Pixel / GA4 / GTM (si se consideran parte del alcance de medición).

### Frontend
- Límite Home a máximo 4 elementos de alto impacto (CMS + UI).
- Before/After / hotspots como piezas Home limitadas y administrables bajo ese cupo.
- Indicador/CTA unificado de estado de recorrido más allá del badge actual.
- Google Reviews en UI.

### Analytics
- Sesiones, duración, tiempo por página, retención.
- Scroll depth 25/50/75/100.
- Exit pages / drop-off.
- Top CTAs (WhatsApp, Presupuesto, Tours, Contacto).
- GeoIP de visitante + mapa de calor.
- Ranking con visitas únicas reales (no `COUNT`/`++` ingenuo).

### CRM
- Kanban con pipeline: Nuevo → En calificación → Presupuesto enviado → Negociación → Ganado/Cerrado.
- Densidad de presupuesto Bajo/Medio/Alto/Especial.
- Filtros País/Estado en solicitudes.
- Lead scoring numérico / matriz.
- Notas, actividades, historial de cambios de estado.

### SEO
- Open Graph, Twitter Cards, canonical, robots.txt/sitemap.xml dinámicos, JSON-LD Organization/LocalBusiness/BreadcrumbList, meta por proyecto.

### Performance / Privacy
- Pipeline AVIF; consentimiento cookies/analytics; retención y anonimización IP.

---

# 7. Integraciones externas necesarias

| Integración | ¿Credenciales? | ¿Cuenta? | ¿Costo? | ¿API? | ¿Sin servicio externo? |
|-------------|----------------|----------|---------|-------|-------------------------|
| **Email (Gmail o Titan)** | Sí | Sí | Gmail gratis con límites; Workspace de pago | SMTP/API | No para envío real |
| **CallMeBot WhatsApp** | Sí (phone + apikey) | Sí | Modelo freemium típico | HTTP API | Parcial: `wa.me` link no necesita API |
| **DigitalOcean Spaces** | Sí | Sí | Sí (storage) | S3 API | Sí (disco `public` local) |
| **Google Business Profile** | Cuenta Google Business | Sí | Gratis claim; APIs pueden requerir Cloud | Opcional | Sí: enlace + NAP + Schema sin API |
| **Google Maps embed** | API key si JS Maps | Sí | Cuota free limitada | Opcional | Sí: iframe share link |
| **Google Reviews** | Place ID; API key si sync | Sí | Cuota | Places API (read) | Sí: deep-link a reviews; **no** se pueden *publicar* reviews vía API |
| **GA4 / GTM** | Measurement ID | Sí | Gratis | gtag | Sí para básico |
| **Meta Pixel** | Pixel ID | Meta Business | Gratis | Pixel | No |
| **GeoIP** | Según proveedor | Sí | MaxMind/ip-api varían | Sí | Parcial: Cloudflare headers si el edge los da |
| **Mapas dashboard (Leaflet/Mapbox)** | Mapbox token si aplica | Opcional | Mapbox de pago tras free | Opcional | Leaflet + tiles OSM posible |

---

# 8. Auditoría de Analytics

### Qué se mide actualmente
- Conteos de negocio en admin dashboard: proyectos, tours, leads, servicios, testimonios (`DashboardService::kpis`).
- `projects.views_count` incrementado en cada show de proyecto (`ProjectService`) — **no** es visita única.
- `activity_logs` para algunas acciones de admin (no tráfico web).

### Qué NO se mide
- Sesiones de visitante, duración, tiempo por página, scroll depth, exit page, CTA clicks, geo de visitante, heatmaps, funnels.

### De dónde vienen los datos del dashboard “Visitas / Storage”
- `DashboardService` **no** retorna `visits_total`, `chart_visits`, `storage_*`.
- `DashboardPage.vue` rellena con **`mockDashboard` / `enrichDashboard`** cuando la API no trae esos campos.
- Conclusión: los gráficos de visitas/almacenamiento son **placeholders**, no analytics de producción.

### Arquitectura necesaria (propuesta, no implementada)
1. Frontend beacon ligero (page_view, heartbeat, scroll milestones, cta_click) con `session_id` anónimo.
2. Tablas `analytics_sessions`, `analytics_events` (sin IP cruda; hash opcional; TTL retención).
3. Geo vía CF-IPCountry / servicio (solo país/región).
4. Jobs de agregación diaria.
5. Endpoints admin + charts reales; eliminar mocks o etiquetar explícitamente “demo”.
6. Consentimiento antes de cookies no esenciales.

**GA4 ≠ analytics propio Modelarc.** Hoy **ninguno** de los dos está cableado en el frontend auditado.

---

# 9. Auditoría CRM

| Aspecto | Realidad |
|---------|----------|
| **Gestor actual** | Página **Solicitudes** (`LeadsPage.vue`) — lista + diálogo detalle |
| **Modelo** | `leads`: name, email, phone, country, state, city, project_type, message, budget_range, preferred_contact_method, status, source, project_id |
| **Pipeline** | Solo `new` / `in_progress` / `closed` — **no** Kanban |
| **Presupuesto** | Columna `budget_range` existe; **el formulario público no la envía** |
| **Geografía** | Capturada en web; **API Resource la expone**; **admin no la muestra ni filtra** |
| **Servicios** | Texto libre `project_type` (opciones del select web), no FK |
| **Scoring** | **NO ENCONTRADO** — no confundir status o budget label con score numérico |
| **Historial** | **NO ENCONTRADO** notes/activities/status history |
| **Notificaciones** | Email a `MAIL_TO_*` + WhatsApp CallMeBot al crear lead |

**Diferencia Lead Scoring vs etiquetas:**
Etiquetas “Bajo/Medio/Alto” o estados de pipeline **no** constituyen un Lead Scoring System. Scoring implica pesos numéricos (`budget_score`, `service_score`, …) y `total_score` calculado. Eso **no existe**.

---

# 10. Riesgos técnicos

| Severidad | Riesgo |
|-----------|--------|
| **CRÍTICO** | Dashboard presenta visitas/storage **mock** como si fueran datos reales → decisiones de negocio erróneas. |
| **ALTO** | WhatsApp flotante **oculto en mobile** (`PublicLayout` ≤700px). |
| **ALTO** | Correo **no centralizado**: FROM `info@…`, TO múltiples; fallbacks hardcodeados. |
| **ALTO** | SPA sin meta por ruta / sin Schema → SEO local y de proyectos débil frente al requerimiento GBP/SEO. |
| **MEDIO** | Doble taxonomía: categorías DB vs `mediaTaxonomy.ts` hardcodeado en galería pública. |
| **MEDIO** | Roles Spatie sin Policies/middleware de autorización fina. |
| **MEDIO** | Panoramas 360 alta resolución: riesgo LCP/memoria móvil/ancho de banda sin estrategia agresiva de previews. |
| **BAJO** | Blog placeholder (`BlogPage.vue`) con Unsplash; badge notificaciones admin hardcodeado “3”. |
| **BAJO→ALTO (futuro)** | Implementar analytics sin consentimiento/retención/IP hashing. |

---

# 11. Backlog recomendado

### P0
| ID | Tarea | Dependencias | Backend | Frontend | DB | Integración | Complejidad |
|----|-------|--------------|---------|----------|-----|-------------|-------------|
| P0-1 | Política correo: centralizar TO/FROM hacia `modelarcca@gmail.com`; quitar inconsistencias | Acceso mailbox | Sí | Display emails web | No | SMTP/Gmail | Media |
| P0-2 | WhatsApp flotante visible en mobile + número configurable (`site_settings`) | — | Settings | Layout web | No/settings | — | Baja |
| P0-3 | Admin leads: mostrar country/state/city; formulario presupuesto `budget_range` | — | Validación | Web + Admin | Ya existe columna | — | Baja |
| P0-4 | Eliminar o etiquetar mocks del Dashboard (visitas/storage) | — | Extender o no KPIs | DashboardPage | — | — | Baja |
| P0-5 | Definir consentimiento/privacidad **antes** de analytics | Legal/negocio | — | Banner | — | — | Media |

### P1
| ID | Tarea | Dependencias | Backend | Frontend | DB | Integración | Complejidad |
|----|-------|--------------|---------|----------|-----|-------------|-------------|
| P1-1 | CRM pipeline + Kanban (estados requeridos + persistencia) | P0-3 | Sí | Admin Kanban | Enum/status + history | — | Alta |
| P1-2 | Filtros geo CRM + índices | P0-3 | Sí | Admin | Índices | — | Media |
| P1-3 | Densidad presupuesto (catálogo rangos) | P0-3 | Sí | Web+Admin | Config/settings | — | Media |
| P1-4 | Tracking base: session + page_view + CTA | P0-5 | API events | Beacon web | Nuevas tablas | — | Alta |
| P1-5 | Scroll depth | P1-4 | Agregación | Listeners | Events | — | Media |
| P1-6 | SEO base: meta por ruta, OG, sitemap, JSON-LD LocalBusiness | NAP final | Opcional API | Web/SSR | — | — | Media |
| P1-7 | Home: definir y aplicar cupo máx. 4 bloques alto impacto | Producto | Reglas CMS | Home | Config | — | Media |
| P1-8 | Enlace oficial GBP + Maps (sin sync API) | Cuenta Google | — | Footer/Contacto | — | GBP | Baja |

### P2
| ID | Tarea | Dependencias | Backend | Frontend | DB | Integración | Complejidad |
|----|-------|--------------|---------|----------|-----|-------------|-------------|
| P2-1 | Drop-off / exit pages | P1-4 | Agregación | — | Sessions | — | Media |
| P2-2 | Ranking visitas únicas reales | P1-4 | Sí | Dashboard | — | — | Media |
| P2-3 | Geo visitante (CF/GeoIP) + privacidad | P0-5, P1-4 | Sí | — | country/region | GeoIP/CF | Media |
| P2-4 | Imágenes About: WebP/srcset/LCP | — | Pipeline | AboutPage | — | — | Media |
| P2-5 | CTA/estado recorrido unificado | — | Opcional status | Cards/detalle | — | — | Baja |
| P2-6 | Lead ↔ Service FK | — | Sí | Contact+Admin | FK | — | Baja |
| P2-7 | Google Reviews (link o sync lectura) | Place ID | Opcional | Home/Nosotros | Cache opcional | Places | Media |

### P3
| ID | Tarea | Dependencias | Backend | Frontend | DB | Integración | Complejidad |
|----|-------|--------------|---------|----------|-----|-------------|-------------|
| P3-1 | Mapa calor geográfico dashboard | P2-3 | Agregados | Mapa UI | — | Tiles/Mapbox | Alta |
| P3-2 | Lead scoring matriz numérica | P1-1..P1-3 | Motor score | UI score | Campos score | — | Alta |
| P3-3 | Sync API Google Business (si se exige “oficial”) | Cuenta Cloud | Jobs | — | — | GBP API | Alta |
| P3-4 | Meta Pixel / GA4 (si marketing lo pide además del propio) | P0-5 | — | Tags | — | Meta/Google | Baja |

---

# 12. Orden recomendado de implementación

**FASE 1 — Fundamentos y datos limpios**
Correo centralizado · WhatsApp mobile/configurable · Leads geo+budget en UI · Quitar mocks dashboard · Consentimiento.

**FASE 2 — UX y contenido**
Home cupo 4 · SEO local (NAP + Schema + GBP link/Maps) · Indicador recorrido · Optimización imágenes About · Unificar taxonomías media.

**FASE 3 — CRM**
Pipeline estados · Kanban persistente · Filtros geo · Densidad presupuesto · FK servicio · (histórico de estados).

**FASE 4 — Analytics tracking**
Session/page_view/CTA/scroll · Tablas + API · Sin IP cruda.

**FASE 5 — Dashboard Analytics**
Charts reales · Top contenido con uniques · Drop-off · (luego geo map).

**FASE 6 — Integraciones externas**
Google Reviews (link o Places read) · GeoIP/CF · GA4/Pixel opcionales · GBP API solo si negocio lo exige.

**FASE 7 — Scoring / avanzado**
Lead scoring · Heatmap geo · Performance 360/CDN.

**FASE 8 — QA / Performance / SEO / Producción**
Lighthouse, indexación, colas en prod, verificación mail/WA, eliminación de datos demo.

---

# 13. Veredicto final

### ¿Está todo lo solicitado desarrollado?
**NO**

### ¿Qué porcentaje aproximado del nuevo alcance está realmente implementado?
**~25 %**

(Base CMS/tours/testimonios/leads básicos ya existía; el bloque nuevo —analytics, CRM Kanban, GBP/Reviews, scoring, SEO técnico, Home≤4, correo único— está mayormente pendiente.)

### ¿Cuáles son los 5 faltantes más importantes?
1. **Analytics real** (sesiones, scroll, CTAs, uniques) — hoy hay mocks en dashboard.
2. **CRM Kanban + pipeline de estados de negocio.**
3. **Correo/WhatsApp comercial consistentes** (Gmail central + WA visible en mobile y configurable).
4. **Presupuesto completo** (`budget_range` + densidad + geo visible/filtrable en admin).
5. **SEO local / Google Business** (Schema + enlace oficial; Reviews).

### ¿Qué cosas parecen implementadas visualmente pero NO funcionan realmente end-to-end?
1. **Gráficos de Visitas y Storage del Dashboard** — datos mock/fallback (`DashboardPage.vue`), no tracking.
2. **“Analytics” implícito del panel** — son KPIs de conteo CMS, no medición de audiencia.
3. **Google Reviews** — no hay integración; solo testimonios propios.
4. **Google Business / Maps / SEO local avanzado** — solo texto NAP hardcodeado.
5. **WhatsApp flotante “siempre disponible”** — no en mobile (oculto).
6. **Presupuesto con rangos / densidad** — columna DB sin captura en formulario.
7. **Settings del home API** — se cargan en store pero **no** impulsan NAP/WhatsApp en UI.
8. **Blog** — ruta placeholder, no CMS.
9. **Badge notificaciones admin “3”** — hardcodeado.
10. **`views_count` como “más vistos”** — no es visita única.

---

## Anexo A — Clasificación correo (sin secretos)

| Ítem | Estado auditado (local `.env`) |
|------|--------------------------------|
| `MAIL_MAILER` | CONFIGURADO (`smtp`) |
| `MAIL_HOST` / `PORT` / `SCHEME` | CONFIGURADO (Titan) |
| `MAIL_USERNAME` | CONFIGURADO (`info@modelarcve.com`) |
| `MAIL_FROM_ADDRESS` | CONFIGURADO (`info@modelarcve.com`) — **no** Gmail |
| `MAIL_TO_ADDRESS` | CONFIGURADO multi-destino **incluye** `modelarcca@gmail.com` + otros |
| Centralización exclusiva en Gmail | **NO** — INCONSISTENTE |

Web pública muestra `modelarcca@gmail.com` en footer/contacto (hardcode), alineado parcialmente con el requerimiento de presencia, pero el **pipeline SMTP FROM** no usa esa cuenta.

## Anexo B — Aclaración “Análisis SEO con Meta”
En este codebase:
- **META HTML** = title/description en `index.html` (existe, básico).
- **Meta Platforms / Facebook** = enlace social hardcodeado (no Pixel).
- **Meta Pixel** = **NO ENCONTRADO EN EL CÓDIGO AUDITADO**.

No son equivalentes.

## Anexo C — WhatsApp en panel admin
**Recomendación de producto (sin implementar):** limitar el botón flotante comercial al **website público**. En admin, si se necesita, usar solo notificación operativa (CallMeBot ya avisa leads) o un enlace de soporte interno — no un CTA de ventas sobre el backoffice.

---

*Fin del informe. Generado únicamente a partir de evidencia del repositorio Modelarc. Sin modificaciones de código en esta fase.*
