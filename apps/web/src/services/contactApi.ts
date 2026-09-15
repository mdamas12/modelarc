import { api } from '@/boot/axios';
import type { ContactPayload } from '@/types/models';
import { useConsentStore } from '@/stores/consentStore';
import { buildContactMetaFields } from '@/tracking/contactMeta';

export interface SubmitContactResult {
  ok: boolean;
  message: string;
  metaEventId: string;
  marketingConsent: boolean;
}

export { buildContactMetaFields } from '@/tracking/contactMeta';

export async function submitContact(payload: ContactPayload): Promise<SubmitContactResult> {
  const consent = useConsentStore();
  const marketingConsent = consent.marketingAllowed === true;
  const metaFields = buildContactMetaFields(marketingConsent);

  await api.post('/public/contact', {
    name: payload.name,
    email: payload.email,
    phone: payload.phone || null,
    country: payload.country || null,
    state: payload.state || null,
    city: payload.city || null,
    project_type: payload.service || null,
    budget_range: payload.budget_range || null,
    message: payload.message,
    source: 'website',
    ...metaFields,
  });

  return {
    ok: true,
    message: 'Mensaje enviado correctamente. Te contactaremos pronto.',
    metaEventId: metaFields.meta_event_id ?? '',
    marketingConsent,
  };
}
