/**
 * Build a wa.me URL from international phone digits and optional prefilled message.
 * Returns null when the phone cannot be normalized (caller must hide the link).
 */
export function normalizeWhatsAppPhone(raw: unknown): string | null {
  if (raw == null) return null;
  const digits = String(raw).replace(/\D+/g, '');
  if (digits.length < 8) return null;
  return digits;
}

export function buildWhatsAppUrl(phone: unknown, message?: unknown): string | null {
  const normalized = normalizeWhatsAppPhone(phone);
  if (!normalized) return null;

  const text = message == null ? '' : String(message).trim();
  if (!text) return `https://wa.me/${normalized}`;

  return `https://wa.me/${normalized}?text=${encodeURIComponent(text)}`;
}

export function formatWhatsAppDisplay(phone: unknown): string | null {
  const normalized = normalizeWhatsAppPhone(phone);
  if (!normalized) return null;
  if (normalized.startsWith('58') && normalized.length >= 12) {
    const local = normalized.slice(2);
    return `(+58)-${local}`;
  }
  return `+${normalized}`;
}
