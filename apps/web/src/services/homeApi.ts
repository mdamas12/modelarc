import { api } from '@/boot/axios';
import {
  extractResource,
  mapProject,
  mapService,
  mapTestimonial,
  mapTour,
} from '@/services/mappers';
import type { Project, ServiceItem, Testimonial, VirtualTour } from '@/types/models';

export interface HeroContent {
  text1: string;
  text2: string;
  text3: string;
}

export interface HeroGalleryImage {
  id: number;
  url: string;
  order: number;
  published: boolean;
}

export interface HomePayload {
  featuredProjects: Project[];
  services: ServiceItem[];
  testimonials: Testimonial[];
  settings: Record<string, string>;
  featuredTour: VirtualTour | null;
  hero: HeroContent;
  heroGalleries: HeroGalleryImage[];
}

function mapHeroGallery(item: Record<string, unknown>): HeroGalleryImage | null {
  const url = typeof item.url === 'string' && item.url.length > 0 ? item.url : undefined;
  if (!url) return null;
  return {
    id: Number(item.id ?? 0),
    url,
    order: Number(item.order ?? 0),
    published: Boolean(item.published),
  };
}

export async function fetchHome(): Promise<HomePayload> {
  const { data } = await api.get('/public/home');
  const payload = extractResource<Record<string, unknown>>(data);

  const featuredProjects = Array.isArray(payload.featured_projects)
    ? payload.featured_projects.map((item) => mapProject(item as Record<string, unknown>))
    : [];

  const services = Array.isArray(payload.services)
    ? payload.services.map((item, index) => mapService(item as Record<string, unknown>, index))
    : [];

  const testimonials = Array.isArray(payload.testimonials)
    ? payload.testimonials.map((item) => mapTestimonial(item as Record<string, unknown>))
    : [];

  const settings =
    payload.settings && typeof payload.settings === 'object'
      ? Object.fromEntries(
          Object.entries(payload.settings as Record<string, unknown>).map(([key, value]) => [
            key,
            String(value ?? ''),
          ]),
        )
      : {};

  const featuredWithTour = featuredProjects.find((project) => project.hasVirtualTour);
  let featuredTour: VirtualTour | null = null;

  if (featuredWithTour) {
    try {
      const tourResponse = await api.get(`/public/projects/${featuredWithTour.slug}/tour`);
      featuredTour = mapTour(
        extractResource<Record<string, unknown>>(tourResponse.data),
        featuredWithTour.coverImage,
        featuredWithTour.slug,
      );
    } catch {
      featuredTour = null;
    }
  }

  const rawHero = (payload.hero as Record<string, unknown> | null | undefined) ?? {};
  const hero: HeroContent = {
    text1: String(rawHero.text_1 ?? ''),
    text2: String(rawHero.text_2 ?? ''),
    text3: String(rawHero.text_3 ?? ''),
  };

  const heroGalleries = Array.isArray(payload.hero_galleries)
    ? payload.hero_galleries
        .map((item) => mapHeroGallery(item as Record<string, unknown>))
        .filter((item): item is HeroGalleryImage => Boolean(item))
        .filter((item) => item.published)
        .sort((a, b) => a.order - b.order)
    : [];

  return {
    featuredProjects,
    services,
    testimonials,
    settings,
    featuredTour,
    hero,
    heroGalleries,
  };
}
