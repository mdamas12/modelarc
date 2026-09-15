# MODELARC — Fase 1 — Implementación

**Fecha:** 2026-09-15
**Alcance:** Fundamentos, comunicación y preparación del CRM
**Sin deploy** en esta fase.

---

## Funcionalidades implementadas

1. **Correo comercial centralizado** vía `MAIL_TO_ADDRESS` (default/ejemplo: `modelarcca@gmail.com`). Se eliminaron fallbacks comerciales a `info@modelarcve.com` / `marcosdamas12@gmail.com` en el notifier. `MAIL_FROM_*` sigue siendo el remitente SMTP técnico (p. ej. Titan).
2. **WhatsApp flotante** visible en desktop/tablet/mobile; número y mensaje desde `site_settings` (`whatsapp_phone`, `whatsapp_message`); sin enlace roto si falta config; reutilizado en layout, footer y contacto. **No** se añadió al Admin.
3. **Rango de presupuesto** obligatorio en formulario de contacto (`low|medium|high|special`); config central `config/leads.php`; mail y CallMeBot incluyen presupuesto/ubicación.
4. **Admin Solicitudes:** muestra país/estado/ciudad/presupuesto/origen; filtros combinables status/country/state/budget_range/project_type + búsqueda.
5. **Dashboard:** eliminados mocks de visitas/storage; KPIs reales; placeholder “Analytics pendiente”; top proyectos por `views_count` real; actividad desde `activity_logs`.
6. **Privacidad:** plan documentado en `docs/architecture/ANALYTICS_PRIVACY_PLAN.md` (sin tracking aún).

---

## Archivos modificados (principales)

### API
- `config/mail.php`, `config/leads.php`, `.env.example`, `phpunit.xml`
- `app/Support/BudgetRange.php`, `CommercialMail.php`
- `app/Services/ContactMailNotifier.php`, `LeadService.php`, `ContactWhatsAppNotifier.php`, `DashboardService.php`
- `app/Http/Requests/Contact/StoreLeadRequest.php`, `Admin/UpdateLeadRequest.php`
- `app/Http/Resources/LeadResource.php`
- `app/Http/Controllers/Api/Website/HomeController.php`, `Admin/LeadController.php`
- `resources/views/emails/contact-lead.blade.php`
- `routes/api.php`
- `database/seeders/DatabaseSeeder.php`
- `database/migrations/2026_09_15_110000_add_lead_filter_indexes_to_leads_table.php`
- `database/migrations/2026_09_15_110100_seed_whatsapp_site_settings.php`
- `database/migrations/2026_08_11_003000_add_project_label_to_testimonial_invitations_table.php` (portable SQLite para tests)
- `tests/Feature/LeadPhaseOneTest.php`

### Web
- `layouts/PublicLayout.vue`, `components/common/SiteFooter.vue`, `pages/ContactPage.vue`
- `utils/whatsapp.ts`, `constants/budgetRanges.ts`
- `stores/homeStore.ts`, `services/homeApi.ts`, `services/contactApi.ts`, `types/models.ts`

### Admin
- `pages/DashboardPage.vue`, `pages/LeadsPage.vue`
- `constants/budgetRanges.ts`, `services/adminApi.ts`, `types/index.ts`

### Docs
- `docs/architecture/ANALYTICS_PRIVACY_PLAN.md`
- `docs/implementation/MODELARC_PHASE_1.md`
- Actualización puntual de `docs/audits/MODELARC_NEW_REQUIREMENTS_AUDIT.md`

---

## Migraciones

| Migración | Efecto |
|-----------|--------|
| `2026_09_15_110000_add_lead_filter_indexes_to_leads_table` | Índices `country`, `state`, `budget_range`, `project_type` |
| `2026_09_15_110100_seed_whatsapp_site_settings` | Seed `whatsapp_phone`, `whatsapp_message`, `contact_email` |

---

## Configuración ENV requerida

```env
# Remitente SMTP técnico (no necesariamente Gmail)
MAIL_FROM_ADDRESS="info@modelarcve.com"
MAIL_FROM_NAME="Modelarc"

# Destinatario comercial central de solicitudes
MAIL_TO_ADDRESS="modelarcca@gmail.com"
```

Settings CMS (Admin → Configuración):

| key | ejemplo |
|-----|---------|
| `whatsapp_phone` | `584249171058` |
| `whatsapp_message` | texto prefijado |
| `contact_email` | `modelarcca@gmail.com` |

---

## Tests ejecutados

```
php vendor/bin/phpunit --filter=LeadPhaseOneTest
OK (6 tests, 20 assertions)

php vendor/bin/phpunit
OK (8 tests, 22 assertions)
```

---

## Builds

| App | Lint | Build |
|-----|------|-------|
| web | No hay script `lint` | `quasar build` OK |
| admin | No hay script `lint` | `quasar build` OK |

---

## QA manual (checklist)

- [ ] WhatsApp flotante visible en mobile/tablet/desktop
- [ ] wa.me abre número + mensaje desde settings
- [ ] Formulario envía country/state/city/budget_range
- [ ] Admin Solicitudes muestra geo + presupuesto y filtros combinados
- [ ] Dashboard sin cifras inventadas de visitas/storage

---

## Pendientes para Fase 2

- Tracking analytics real + consentimiento UI
- CRM Kanban / pipeline expandido
- Google Business / Reviews / SEO Schema
- Home máx. 4 elementos alto impacto
- Densidad monetaria de presupuesto (si negocio define montos)
- Lead scoring

---

## Riesgos / decisiones técnicas

1. **FROM vs TO:** se mantiene Titan/`info@` como FROM SMTP; solo se centraliza TO comercial en Gmail.
2. **Statuses CRM:** se conservan `new|in_progress|closed` para no romper datos; evolución futura documentada en `UpdateLeadRequest`.
3. **Budget labels:** keys semánticas sin montos inventados; constants TS espejo de `config/leads.php` (mantener sync manual o unificar paquete más adelante).
4. **`views_count` en dashboard:** es contador real de vistas de ficha, **no** visita única de audiencia.
5. Migración MySQL `MODIFY` de invitaciones hecha portable para SQLite (tests).
