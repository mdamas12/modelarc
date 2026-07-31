import type {
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

export function mapProject(raw: Record<string, unknown>): Project {
  const cover = mediaUrl(raw.cover_media) ?? PLACEHOLDER_COVER;
  const projectMedia = Array.isArray(raw.project_media) ? raw.project_media : [];
  const gallery = projectMedia
    .map((item) => {
      if (!item || typeof item !== 'object') return undefined;
      return mediaUrl((item as { media?: unknown }).media);
    })
    .filter((url): url is string => Boolean(url));

  const virtualTour = raw.virtual_tour as Record<string, unknown> | null | undefined;
  const projectType = raw.project_type as { name?: string } | null | undefined;

  const project: Project = {
    id: String(raw.id),
    slug: String(raw.slug ?? ''),
    title: String(raw.title ?? ''),
    category: titleCase(projectType?.name ?? String(raw.category ?? '')),
    location: String(raw.location ?? ''),
    year: Number(raw.year ?? new Date().getFullYear()),
    coverImage: cover,
    images: gallery.length ? gallery : [cover],
    description: String(raw.summary ?? raw.description ?? ''),
    longDescription: String(raw.description ?? raw.summary ?? ''),
    hasVirtualTour: Boolean(raw.has_virtual_tour),
    isFeatured: Boolean(raw.is_featured),
  };

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
  const thumbnailUrl =
    mediaUrl(raw.thumbnail_media) ?? (panoramaUrl || PLACEHOLDER_COVER);
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

  const project = raw.project as { slug?: string; cover_media?: unknown } | null | undefined;
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

  return tour;
}

export function mapService(raw: Record<string, unknown>, index = 0): ServiceItem {
  const service: ServiceItem = {
    id: String(raw.id ?? raw.slug ?? index),
    title: String(raw.name ?? ''),
    description: String(raw.summary ?? raw.description ?? ''),
    image: mediaUrl(raw.image) ?? SERVICE_FALLBACK_IMAGES[index % SERVICE_FALLBACK_IMAGES.length]!,
  };
  if (raw.icon) service.icon = String(raw.icon);
  return service;
}

export function mapTestimonial(raw: Record<string, unknown>): Testimonial {
  const project = raw.project as { title?: string } | null | undefined;
  const testimonial: Testimonial = {
    id: String(raw.id),
    name: String(raw.client_name ?? ''),
    role: project?.title ? `Cliente · ${project.title}` : 'Cliente Modelarc',
    quote: String(raw.quote ?? ''),
  };
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
