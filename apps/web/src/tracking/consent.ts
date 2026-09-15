/**
 * First-party marketing consent preferences (localStorage).
 * Categories mirror docs/architecture/ANALYTICS_PRIVACY_PLAN.md
 */

export type ConsentCategory = 'essential' | 'analytics' | 'marketing';

export interface ConsentPreferences {
  essential: true;
  analytics: boolean;
  marketing: boolean;
  decidedAt: string | null;
}

export const CONSENT_STORAGE_KEY = 'modelarc_consent_v1';

export const DEFAULT_CONSENT: ConsentPreferences = {
  essential: true,
  analytics: false,
  marketing: false,
  decidedAt: null,
};

export function hasDecided(prefs: ConsentPreferences): boolean {
  return prefs.decidedAt !== null;
}

export function readConsentFromStorage(
  storage: Pick<Storage, 'getItem'> = localStorage,
): ConsentPreferences {
  try {
    const raw = storage.getItem(CONSENT_STORAGE_KEY);
    if (!raw) return { ...DEFAULT_CONSENT };

    const parsed = JSON.parse(raw) as Partial<ConsentPreferences>;
    return {
      essential: true,
      analytics: Boolean(parsed.analytics),
      marketing: Boolean(parsed.marketing),
      decidedAt: typeof parsed.decidedAt === 'string' ? parsed.decidedAt : null,
    };
  } catch {
    return { ...DEFAULT_CONSENT };
  }
}

export function writeConsentToStorage(
  prefs: ConsentPreferences,
  storage: Pick<Storage, 'setItem'> = localStorage,
): void {
  storage.setItem(CONSENT_STORAGE_KEY, JSON.stringify(prefs));
}

export function acceptAllConsent(): ConsentPreferences {
  return {
    essential: true,
    analytics: true,
    marketing: true,
    decidedAt: new Date().toISOString(),
  };
}

export function rejectNonEssentialConsent(): ConsentPreferences {
  return {
    essential: true,
    analytics: false,
    marketing: false,
    decidedAt: new Date().toISOString(),
  };
}

export function buildCustomConsent(input: {
  analytics: boolean;
  marketing: boolean;
}): ConsentPreferences {
  return {
    essential: true,
    analytics: Boolean(input.analytics),
    marketing: Boolean(input.marketing),
    decidedAt: new Date().toISOString(),
  };
}
