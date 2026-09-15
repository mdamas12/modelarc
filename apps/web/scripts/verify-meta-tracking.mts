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

console.log('verify-meta-tracking: OK');
