import { createMetaEventId, readMetaBrowserIds } from '../services/metaPixel.ts';

/**
 * Meta-related fields for the contact POST.
 * Pure helper so consent gating can be verified without Vitest.
 */
export function buildContactMetaFields(marketingConsent: boolean): {
  marketing_consent: boolean;
  meta_event_id?: string;
  meta_fbp?: string | null;
  meta_fbc?: string | null;
  event_source_url?: string | null;
} {
  if (!marketingConsent) {
    return { marketing_consent: false };
  }

  const browserIds = readMetaBrowserIds();

  return {
    marketing_consent: true,
    meta_event_id: createMetaEventId(),
    meta_fbp: browserIds.fbp || null,
    meta_fbc: browserIds.fbc || null,
    event_source_url: typeof window !== 'undefined' ? window.location.href : null,
  };
}
