/**
 * Meta Pixel SPA helper (browser only).
 * Never import or expose META_CONVERSIONS_API_TOKEN here.
 */

export type ContactMethod = 'whatsapp' | 'phone';
export type ContactPlacement =
  | 'floating'
  | 'footer'
  | 'contact_page'
  | 'contact_social'
  | 'other';

export interface MetaPixelConfig {
  pixelId: string;
  enabled: boolean;
}

declare global {
  interface Window {
    fbq?: (...args: unknown[]) => void;
    _fbq?: (...args: unknown[]) => void;
  }
}

let initialized = false;
let lastPageViewPath: string | null = null;
let marketingConsentGranted = false;

export function getMetaPixelConfig(): MetaPixelConfig {
  const pixelId = String(import.meta.env.VITE_META_PIXEL_ID || '').trim();
  const enabledFlag = String(import.meta.env.VITE_META_PIXEL_ENABLED || '').toLowerCase();
  const enabled =
    (enabledFlag === 'true' || enabledFlag === '1') && pixelId !== '';

  return { pixelId, enabled };
}

export function isMetaPixelReady(): boolean {
  return initialized && marketingConsentGranted && typeof window !== 'undefined' && typeof window.fbq === 'function';
}

export function setMarketingConsent(granted: boolean): void {
  marketingConsentGranted = granted;
  if (granted) {
    void initializeMetaPixel();
  }
}

export async function initializeMetaPixel(): Promise<boolean> {
  if (typeof window === 'undefined') return false;
  if (!marketingConsentGranted) return false;

  const { pixelId, enabled } = getMetaPixelConfig();
  if (!enabled || !pixelId) return false;
  if (initialized && typeof window.fbq === 'function') return true;

  await loadFbqStub(pixelId);
  initialized = true;
  return true;
}

function loadFbqStub(pixelId: string): Promise<void> {
  return new Promise((resolve, reject) => {
    if (typeof window.fbq === 'function') {
      window.fbq('init', pixelId);
      resolve();
      return;
    }

    const n = function (...args: unknown[]) {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const fn = n as any;
      (fn.queue = fn.queue || []).push(args);
    };
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (n as any).queue = [];
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (n as any).loaded = true;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (n as any).version = '2.0';
    window.fbq = n as typeof window.fbq;
    if (!window._fbq) window._fbq = window.fbq;

    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://connect.facebook.net/en_US/fbevents.js';
    script.onload = () => {
      window.fbq?.('init', pixelId);
      resolve();
    };
    script.onerror = () => reject(new Error('Failed to load Meta Pixel'));
    document.head.appendChild(script);
  });
}

export function trackPageView(path?: string): void {
  if (!isMetaPixelReady()) return;

  const currentPath = path ?? (typeof window !== 'undefined' ? window.location.pathname : '');
  if (currentPath && currentPath === lastPageViewPath) return;

  window.fbq?.('track', 'PageView');
  lastPageViewPath = currentPath;
}

export function track(
  eventName: string,
  params?: Record<string, unknown>,
  eventData?: { eventID?: string },
): void {
  if (!isMetaPixelReady()) return;

  if (eventData?.eventID) {
    window.fbq?.('track', eventName, params ?? {}, { eventID: eventData.eventID });
    return;
  }

  window.fbq?.('track', eventName, params ?? {});
}

export function trackCustom(
  eventName: string,
  params?: Record<string, unknown>,
  eventData?: { eventID?: string },
): void {
  if (!isMetaPixelReady()) return;

  if (eventData?.eventID) {
    window.fbq?.('trackCustom', eventName, params ?? {}, { eventID: eventData.eventID });
    return;
  }

  window.fbq?.('trackCustom', eventName, params ?? {});
}

export function trackContact(input: {
  method: ContactMethod;
  placement: ContactPlacement;
  pagePath?: string;
}): void {
  track('Contact', {
    contact_method: input.method,
    content_name: input.placement,
    page_path: input.pagePath ?? (typeof window !== 'undefined' ? window.location.pathname : ''),
  });
}

export function trackLead(input: {
  eventId: string;
  leadType?: string;
  service?: string;
  budgetRange?: string;
  country?: string;
}): void {
  if (!input.eventId) return;

  const params: Record<string, unknown> = {
    content_name: 'contact_form',
    lead_type: input.leadType ?? 'website_contact',
  };
  if (input.service) params.service = input.service;
  if (input.budgetRange) params.budget_range = input.budgetRange;
  if (input.country) params.country = input.country;

  track('Lead', params, { eventID: input.eventId });
}

export function createMetaEventId(): string {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID();
  }

  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0;
    const v = c === 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

export function readMetaBrowserIds(): { fbp?: string; fbc?: string } {
  if (typeof document === 'undefined') return {};

  const cookies = document.cookie.split(';').map((c) => c.trim());
  const find = (name: string) => {
    const row = cookies.find((c) => c.startsWith(`${name}=`));
    return row ? decodeURIComponent(row.slice(name.length + 1)) : undefined;
  };

  return {
    fbp: find('_fbp'),
    fbc: find('_fbc'),
  };
}

/** Test helpers */
export function __resetMetaPixelForTests(): void {
  initialized = false;
  lastPageViewPath = null;
  marketingConsentGranted = false;
}
