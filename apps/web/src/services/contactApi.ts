import { api } from '@/boot/axios';
import type { ContactPayload } from '@/types/models';
import { createMetaEventId, readMetaBrowserIds } from '@/services/metaPixel';

export interface SubmitContactResult {
  ok: boolean;
  message: string;
  metaEventId: string;
}

export async function submitContact(payload: ContactPayload): Promise<SubmitContactResult> {
  const metaEventId = createMetaEventId();
  const browserIds = readMetaBrowserIds();

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
    meta_event_id: metaEventId,
    event_source_url: typeof window !== 'undefined' ? window.location.href : null,
    meta_fbp: browserIds.fbp || null,
    meta_fbc: browserIds.fbc || null,
  });

  return {
    ok: true,
    message: 'Mensaje enviado correctamente. Te contactaremos pronto.',
    metaEventId,
  };
}
