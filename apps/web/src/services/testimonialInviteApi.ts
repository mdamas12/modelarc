import { api } from '@/boot/axios';

export interface TestimonialInvitePayload {
  client_name: string;
  status: string;
  project_label?: string | null;
  project_display_name?: string | null;
  project: {
    id: number;
    title: string;
    slug?: string;
    category?: string | null;
    location?: string | null;
  } | null;
  message?: string;
}

function unwrapData<T>(payload: { data: T } | T): T {
  if (payload && typeof payload === 'object' && 'data' in payload) {
    return (payload as { data: T }).data;
  }
  return payload as T;
}

export async function fetchTestimonialInvite(token: string) {
  const { data, status } = await api.get(`/public/testimonial-invitations/${token}`, {
    validateStatus: (s) => (s >= 200 && s < 300) || s === 410,
  });
  return {
    status,
    data: unwrapData<TestimonialInvitePayload>(data),
  };
}

export async function submitTestimonialInvite(
  token: string,
  payload: {
    rating: number;
    quote: string;
    allow_publish: boolean;
    client_name?: string;
  },
) {
  const { data } = await api.post(`/public/testimonial-invitations/${token}`, payload);
  return unwrapData(data);
}
