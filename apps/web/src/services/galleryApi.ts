import { api } from '@/boot/axios';
import { extractList } from '@/services/mappers';

export interface GalleryImage {
  id: number;
  url: string;
  thumbUrl: string;
  alt: string;
  category?: string | null;
  subcategory?: string | null;
}

function mediaUrl(item: Record<string, unknown>, key: 'url' | 'thumbnail_url'): string | undefined {
  const value = item[key];
  return typeof value === 'string' && value.length > 0 ? value : undefined;
}

function mapGalleryItem(item: Record<string, unknown>): GalleryImage | null {
  const url = mediaUrl(item, 'url');
  if (!url) return null;

  const thumbUrl = mediaUrl(item, 'thumbnail_url') || url;
  const name =
    (typeof item.original_name === 'string' && item.original_name) ||
    (typeof item.subcategory === 'string' && item.subcategory) ||
    'Galería Modelarc';

  return {
    id: Number(item.id ?? 0),
    url,
    thumbUrl,
    alt: name,
    category: typeof item.category === 'string' ? item.category : null,
    subcategory: typeof item.subcategory === 'string' ? item.subcategory : null,
  };
}

export async function fetchPublicGallery(options?: {
  subcategory?: string | null;
  perPage?: number;
}): Promise<GalleryImage[]> {
  const { data } = await api.get('/public/gallery', {
    params: {
      per_page: options?.perPage ?? 100,
      subcategory: options?.subcategory || undefined,
    },
  });

  return extractList<Record<string, unknown>>(data)
    .map(mapGalleryItem)
    .filter((item): item is GalleryImage => Boolean(item));
}

export async function fetchFooterGallery(limit = 6): Promise<GalleryImage[]> {
  const items = await fetchPublicGallery({ perPage: limit });
  return items.slice(0, limit);
}
