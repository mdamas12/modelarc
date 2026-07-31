import { api } from '@/boot/axios';
import { extractResource, mapTour } from '@/services/mappers';
import { fetchProjects } from '@/services/projectApi';
import type { VirtualTour } from '@/types/models';

export async function fetchProjectTour(projectSlug: string): Promise<VirtualTour | null> {
  const { data } = await api.get(`/public/projects/${projectSlug}/tour`);
  return mapTour(extractResource<Record<string, unknown>>(data), undefined, projectSlug);
}

export async function fetchVirtualTourBySlug(slug: string): Promise<VirtualTour | null> {
  const { data } = await api.get(`/public/tours/${slug}`);
  return mapTour(extractResource<Record<string, unknown>>(data));
}

export async function fetchVirtualTours(): Promise<VirtualTour[]> {
  const projects = await fetchProjects({ per_page: 50 });
  const withTour = projects.filter((project) => project.hasVirtualTour);

  const tours = await Promise.all(
    withTour.map(async (project) => {
      try {
        const tour = await fetchProjectTour(project.slug);
        if (!tour) return null;
        const mapped: VirtualTour = {
          ...tour,
          coverImage: tour.coverImage || project.coverImage,
        };
        mapped.projectSlug = project.slug;
        return mapped;
      } catch {
        return null;
      }
    }),
  );

  return tours.filter((tour): tour is VirtualTour => tour != null);
}

export async function fetchFeaturedTour(): Promise<VirtualTour | null> {
  const projects = await fetchProjects({ is_featured: true, per_page: 12 });
  const candidate =
    projects.find((project) => project.hasVirtualTour) ??
    (await fetchProjects({ per_page: 20 })).find((project) => project.hasVirtualTour);

  if (!candidate) return null;

  try {
    const tour = await fetchProjectTour(candidate.slug);
    if (!tour) return null;
    const mapped: VirtualTour = {
      ...tour,
      coverImage: tour.coverImage || candidate.coverImage,
    };
    mapped.projectSlug = candidate.slug;
    return mapped;
  } catch {
    return null;
  }
}
