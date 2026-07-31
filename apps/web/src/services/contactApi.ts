import { api } from '@/boot/axios';
import type { ContactPayload } from '@/types/models';

export async function submitContact(payload: ContactPayload): Promise<{ ok: boolean; message: string }> {
  await api.post('/public/contact', {
    name: payload.name,
    email: payload.email,
    phone: payload.phone || null,
    project_type: payload.service || null,
    message: payload.message,
    source: 'website',
  });

  return { ok: true, message: 'Mensaje enviado correctamente. Te contactaremos pronto.' };
}
