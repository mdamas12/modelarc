import type {
  BeforeAfterItem,
  Project,
  ServiceItem,
  Testimonial,
  TourHotspot,
  TourScene,
  VirtualTour,
} from '@/types/models';

const SERVICE_FALLBACK_IMAGES = [
  'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=1000&q=80',
];

const PLACEHOLDER_COVER =
  'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1600&q=80';

function mediaUrl(media: unknown): string | undefined {
  if (!media || typeof media !== 'object') return undefined;
  const url = (media as { url?: unknown }).url;
  return typeof url === 'string' && url.length > 0 ? url : undefined;
}

function mediaThumbUrl(media: unknown): string | undefined {
  if (!media || typeof media !== 'object') return undefined;
  const thumb = (media as { thumbnail_url?: unknown }).thumbnail_url;
  if (typeof thumb === 'string' && thumb.length > 0) return thumb;
  return mediaUrl(media);
}

function titleCase(value: string | null | undefined): string {
  if (!value) return '';
  return value
    .replaceAll('_', ' ')
    .split(' ')
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function unwrapData<T>(payload: unknown): T {
  if (payload && typeof payload === 'object' && 'data' in payload) {
    return (payload as { data: T }).data;
  }
  return payload as T;
}

function mapGalleryChange(raw: Record<string, unknown>): BeforeAfterItem | null {
  const before =
    (typeof raw.before_image_url === 'string' && raw.before_image_url) ||
    mediaUrl(raw.before_media);
  const after =
    (typeof raw.comparison_image_url === 'string' && raw.comparison_image_url) ||
    mediaUrl(raw.comparison_media) ||
    (raw.compare_with === 'design' ? mediaUrl(raw.design_media) : mediaUrl(raw.after_media)) ||
    mediaUrl(raw.after_media) ||
    mediaUrl(raw.design_media);

  if (!before || !after) return null;

  const compareLabel =
    typeof raw.compare_label === 'string' && raw.compare_label
      ? raw.compare_label
      : raw.compare_with === 'design'
        ? 'Diseño'
        : 'Después';

  const subcategory = raw.subcategory ? titleCase(String(raw.subcategory)) : undefined;
  const title =
    (typeof raw.title === 'string' && raw.title.trim()) || subcategory || undefined;

  return {
    id: String(raw.id),
    beforeImage: before,
    afterImage: after,
    beforeLabel: 'Antes',
    afterLabel: compareLabel,
    title,
    description: typeof raw.description === 'string' ? raw.description : undefined,
    subcategory,
  };
}

export function mapProject(raw: Record<string, unknown>): Project {
  const cover = mediaUrl(raw.cover_media) ?? PLACEHOLDER_COVER;
  const projectMedia = Array.isArray(raw.project_media) ? raw.project_media : [];
  const gallery = projectMedia
    .map((item) => {
      if (!item || typeof item !== 'object') return undefined;
      const row = item as { type?: string; media?: unknown };
      // Keep legacy before/after pivot rows out of the main gallery grid.
      if (row.type === 'before' || row.type === 'after') return undefined;
      return mediaUrl(row.media);
    })
    .filter((url): url is string => Boolean(url));

  const galleryChanges = Array.isArray(raw.gallery_changes) ? raw.gallery_changes : [];
  const beforeAfterItems = galleryChanges
    .map((item) =>
      item && typeof item === 'object'
        ? mapGalleryChange(item as Record<string, unknown>)
        : null,
    )
    .filter((item): item is BeforeAfterItem => Boolean(item));

  const virtualTour = raw.virtual_tour as Record<string, unknown> | null | undefined;
  const projectType = raw.project_type as { name?: string } | null | undefined;
  const categoryRef = raw.category_ref as { name?: string } | null | undefined;

  const project: Project = {
    id: String(raw.id),
    slug: String(raw.slug ?? ''),
    title: String(raw.title ?? ''),
    category: titleCase(categoryRef?.name ?? projectType?.name ?? String(raw.category ?? '')),
    location: String(raw.location ?? ''),
    year: Number(raw.year ?? new Date().getFullYear()),
    coverImage: cover,
    images: gallery.length ? gallery : [cover],
    description: String(raw.summary ?? raw.description ?? ''),
    longDescription: String(raw.description ?? raw.summary ?? ''),
    hasVirtualTour: Boolean(raw.has_virtual_tour),
    isFeatured: Boolean(raw.is_featured),
    beforeAfterItems,
  };

  if (beforeAfterItems[0]) {
    project.beforeImage = beforeAfterItems[0].beforeImage;
    project.afterImage = beforeAfterItems[0].afterImage;
  }

  if (virtualTour?.slug) project.tourSlug = String(virtualTour.slug);
  if (raw.area) project.area = String(raw.area);
  if (raw.status) project.status = titleCase(String(raw.status));

  return project;
}

export function mapHotspot(raw: Record<string, unknown>): TourHotspot {
  const hotspot: TourHotspot = {
    id: String(raw.id),
    yaw: Number(raw.yaw ?? 0),
    pitch: Number(raw.pitch ?? 0),
    label: String(raw.title ?? raw.description ?? 'Punto interactivo'),
  };
  if (raw.target_scene_id != null) hotspot.targetSceneId = String(raw.target_scene_id);
  return hotspot;
}

export function mapScene(raw: Record<string, unknown>): TourScene & { sortOrder?: number } {
  const panoramaUrl = mediaUrl(raw.panorama_media) ?? '';
  // Nunca usar el panorama completo (30–40 MB) como miniatura: tumba móvil/Safari.
  const thumbnailUrl =
    mediaUrl(raw.thumbnail_media) ??
    mediaThumbUrl(raw.panorama_media) ??
    PLACEHOLDER_COVER;
  const hotspots = Array.isArray(raw.hotspots)
    ? raw.hotspots.map((item) => mapHotspot(item as Record<string, unknown>))
    : [];

  return {
    id: String(raw.id),
    name: String(raw.name ?? 'Escena'),
    panoramaUrl,
    thumbnailUrl,
    yaw: Number(raw.initial_yaw ?? 0),
    pitch: Number(raw.initial_pitch ?? 0),
    hotspots,
    sortOrder: Number(raw.sort_order ?? 0),
  };
}

export function mapTour(
  raw: Record<string, unknown>,
  fallbackCover?: string,
  projectSlug?: string,
): VirtualTour {
  const scenes = Array.isArray(raw.scenes)
    ? [...raw.scenes]
        .map((item) => mapScene(item as Record<string, unknown>))
        .filter((scene) => Boolean(scene.panoramaUrl))
        .sort((a, b) => (a.sortOrder ?? 0) - (b.sortOrder ?? 0))
        .map(({ sortOrder: _sortOrder, ...scene }) => scene)
    : [];

  const project = raw.project as
    | {
        slug?: string;
        cover_media?: unknown;
        location?: string;
        year?: number;
        category?: string;
        project_type?: { name?: string };
        category_ref?: { name?: string };
      }
    | null
    | undefined;
  const cover =
    fallbackCover ??
    mediaUrl(project?.cover_media) ??
    scenes[0]?.thumbnailUrl ??
    PLACEHOLDER_COVER;

  const tour: VirtualTour = {
    id: String(raw.id),
    slug: String(raw.slug ?? ''),
    title: String(raw.name ?? 'Recorrido 360°'),
    description: String(raw.description ?? ''),
    coverImage: cover,
    scenes,
  };

  const resolvedProjectSlug = projectSlug ?? (project?.slug ? String(project.slug) : undefined);
  if (resolvedProjectSlug) tour.projectSlug = resolvedProjectSlug;

  const categoryName = titleCase(
    project?.category_ref?.name ?? project?.project_type?.name ?? String(project?.category ?? ''),
  );
  if (categoryName) tour.category = categoryName;
  if (project?.location) tour.location = String(project.location);
  if (project?.year != null) tour.year = Number(project.year);

  return tour;
}

export function mapService(raw: Record<string, unknown>, index = 0): ServiceItem {
  const summary = String(raw.summary ?? '');
  const description = String(raw.description ?? '');
  const service: ServiceItem = {
    id: String(raw.id ?? raw.slug ?? index),
    title: String(raw.name ?? ''),
    summary: summary || description,
    description: description || summary,
    image: mediaUrl(raw.image) ?? SERVICE_FALLBACK_IMAGES[index % SERVICE_FALLBACK_IMAGES.length]!,
  };
  if (raw.icon) service.icon = String(raw.icon);
  return service;
}

export function mapTestimonial(raw: Record<string, unknown>): Testimonial {
  const project = raw.project as { title?: string } | null | undefined;
  const projectLabel =
    (typeof raw.project_label === 'string' && raw.project_label.trim()) || undefined;
  const testimonial: Testimonial = {
    id: String(raw.id),
    name: String(raw.client_name ?? ''),
    role: projectLabel || project?.title || 'Cliente Modelarc',
    quote: String(raw.quote ?? ''),
  };
  const rating = Number(raw.rating);
  if (Number.isFinite(rating) && rating > 0) {
    testimonial.rating = Math.min(5, Math.max(1, Math.round(rating)));
  }
  const avatar = mediaUrl(raw.client_photo);
  if (avatar) testimonial.avatar = avatar;
  return testimonial;
}

export function extractList<T>(payload: unknown): T[] {
  const data = unwrapData<unknown>(payload);
  if (Array.isArray(data)) return data as T[];
  if (data && typeof data === 'object' && Array.isArray((data as { data?: unknown }).data)) {
    return (data as { data: T[] }).data;
  }
  return [];
}

export function extractResource<T>(payload: unknown): T {
  return unwrapData<T>(payload);
}
