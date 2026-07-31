import { api } from '@/boot/axios';
import {
  extractResource,
  mapProject,
  mapService,
  mapTestimonial,
  mapTour,
} from '@/services/mappers';
import type { Project, ServiceItem, Testimonial, VirtualTour } from '@/types/models';

export interface HomePayload {
  featuredProjects: Project[];
  services: ServiceItem[];
  testimonials: Testimonial[];
  settings: Record<string, string>;
  featuredTour: VirtualTour | null;
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

  return {
    featuredProjects,
    services,
    testimonials,
    settings,
    featuredTour,
  };
}
