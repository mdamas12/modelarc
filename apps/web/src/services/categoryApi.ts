import { api } from '@/boot/axios';
import { extractList } from '@/services/mappers';

export interface PublicCategory {
  id: number;
  name: string;
  slug: string;
}

export async function fetchPublicCategories(): Promise<PublicCategory[]> {
  const { data } = await api.get('/public/categories');
  return extractList<Record<string, unknown>>(data).map((raw) => ({
    id: Number(raw.id),
    name: String(raw.name ?? ''),
    slug: String(raw.slug ?? ''),
  }));
}
