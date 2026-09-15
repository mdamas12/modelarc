/**
 * Privacy / consent architecture for future analytics (Phase 2+).
 *
 * Phase 1 intentionally does NOT ship tracking beacons, GA4, Meta Pixel,
 * or cookie banners. This document defines the contract so later work can
 * plug in without redesigning storage or UX.
 */

# Consent categories

| Category | Purpose | Default | Examples |
|----------|---------|---------|----------|
| **essential** | Site operation, auth, security | Always on | Session cookie admin, CSRF, load balancing |
| **analytics** | Audience measurement for Modelarc dashboard | Off until consent | page_view, session duration, scroll depth, CTA clicks |
| **marketing** | Ads / remarketing pixels | Off until consent | Meta Pixel, Google Ads |

# Rules before enabling analytics

1. **No tracking scripts** load until the visitor grants `analytics` (and `marketing` separately).
2. **Do not store full IP addresses.** Prefer country/region from trusted edge headers (e.g. Cloudflare) or a hashed truncated IP if needed for abuse control only — never as a primary analytics key.
3. **Session identifier** must be anonymous (random UUID in first-party storage), rotatable, and not joinable to PII without explicit operational need.
4. **Retention** must be configurable (suggested default: 90 days raw events, longer for aggregates).
5. **Revocation**: withdrawing consent stops new collection; document whether historical anonymous aggregates remain.
6. Dashboard must never invent visit metrics when consent/tracking is absent (Phase 1 already removes mocks).

# Suggested future modules (not implemented yet)

- Frontend: `consentStore` / composable with `essential | analytics | marketing`
- API: `POST /api/public/analytics/events` gated by server-side validation only (no secret keys in frontend beyond public measurement IDs)
- Tables: `analytics_sessions`, `analytics_events` (no `ip` column; optional `ip_hash` with short TTL if ever required)

# Relation to third-party tools

GA4 / GTM / Meta Pixel are optional marketing/analytics vendors and must respect the same consent gates. They are **not** substitutes for Modelarc’s own CRM lead data.
