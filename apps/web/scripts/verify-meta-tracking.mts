/**
 * Lightweight verification for Meta consent + Pixel helpers (no Vitest).
 * Run: node --experimental-strip-types scripts/verify-meta-tracking.mts
 */
import assert from 'node:assert/strict';
import {
  acceptAllConsent,
  buildCustomConsent,
  DEFAULT_CONSENT,
  hasDecided,
  rejectNonEssentialConsent,
} from '../src/tracking/consent.ts';
import { buildContactMetaFields } from '../src/tracking/contactMeta.ts';

assert.equal(DEFAULT_CONSENT.essential, true);
assert.equal(DEFAULT_CONSENT.marketing, false);
assert.equal(hasDecided(DEFAULT_CONSENT), false);

const accepted = acceptAllConsent();
assert.equal(accepted.marketing, true);
assert.equal(accepted.analytics, true);
assert.equal(hasDecided(accepted), true);

const rejected = rejectNonEssentialConsent();
assert.equal(rejected.marketing, false);
assert.equal(rejected.analytics, false);
assert.equal(hasDecided(rejected), true);

const custom = buildCustomConsent({ analytics: true, marketing: false });
assert.equal(custom.analytics, true);
assert.equal(custom.marketing, false);
assert.equal(custom.essential, true);

// Marketing false → no Meta identifiers for backend / browser Lead.
const denied = buildContactMetaFields(false);
assert.equal(denied.marketing_consent, false);
assert.equal('meta_event_id' in denied, false);
assert.equal('meta_fbp' in denied, false);
assert.equal('meta_fbc' in denied, false);
assert.equal('event_source_url' in denied, false);

// Marketing true → event_id generated; browser ids optional.
const allowed = buildContactMetaFields(true);
assert.equal(allowed.marketing_consent, true);
assert.equal(typeof allowed.meta_event_id, 'string');
assert.match(allowed.meta_event_id ?? '', /^[0-9a-f-]{36}$/i);

console.log('verify-meta-tracking: OK');
