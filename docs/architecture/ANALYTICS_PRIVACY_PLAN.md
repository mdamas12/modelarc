/**
 * Privacy / consent architecture for analytics and marketing tags.
 *
 * Phase 1 shipped without trackers. Meta Pixel + Conversions API are now
 * gated by the marketing consent category implemented on the public website.
 */

# Consent categories

| Category | Purpose | Default | Examples |
|----------|---------|---------|----------|
| **essential** | Site operation, auth, security | Always on | Session cookie admin, CSRF, load balancing |
| **analytics** | Audience measurement for Modelarc dashboard | Off until consent | Future first-party page_view / CTA events |
| **marketing** | Ads / remarketing pixels | Off until consent | **Meta Pixel**, Google Ads |

# Rules

1. **No marketing scripts** (including Meta Pixel / `fbevents.js`) load until the visitor grants `marketing`.
2. **Analytics** is a separate toggle; Meta does **not** use the analytics category.
3. **Do not store full IP addresses** in Modelarc databases for analytics. Meta CAPI may receive `client_ip_address` **ephemerally** during the HTTP request for Lead matching only — it is not persisted for Meta purposes.
4. **Session identifier** for future first-party analytics must be anonymous (random UUID), rotatable, and not joinable to PII without operational need.
5. **Retention** (future first-party events): suggested default 90 days raw, longer for aggregates.
6. **Revocation**: withdrawing consent (re-open preferences and disable marketing) stops new Pixel loads/events. Historical Meta-side data follows Meta’s policies; Modelarc does not keep a local copy of Pixel payloads.
7. Dashboard must never invent visit metrics when consent/tracking is absent.

# Meta Pixel + Conversions API

| Channel | Events | Consent |
|---------|--------|---------|
| Browser Pixel | `PageView`, `Contact`, `Lead` | Requires `marketing` |
| Server CAPI | `Lead` only | Server-side; uses data the user submitted in the contact form |

## Deduplication

Browser `Lead` and server `Lead` share the same `event_id` / `eventID` (UUID generated in the browser before submit).

## User data on CAPI

Normalized + SHA-256 fields may include: `em`, `ph`, `fn`, `ln`, `ct`, `st`, `country`, plus request `client_ip_address` / `client_user_agent` and optional `_fbp` / `_fbc`.

**Important:** Hashing is a Meta matching requirement. It does **not** make personal data anonymous under privacy law.

## Local storage

Consent preferences are stored in first-party `localStorage` key `modelarc_consent_v1` (`essential` / `analytics` / `marketing` / `decidedAt`). No fingerprinting.

# Suggested future modules

- API: `POST /api/public/analytics/events` for first-party analytics (separate from Meta)
- Tables: `analytics_sessions`, `analytics_events` (no full `ip` column)

# Relation to third-party tools

GA4 / GTM / Meta Pixel are optional marketing/analytics vendors and must respect the same consent gates. They are **not** substitutes for Modelarc’s own CRM lead data.

See also: `docs/integrations/META_TRACKING.md`
