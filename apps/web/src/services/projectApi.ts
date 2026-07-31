import { api } from '@/boot/axios';
import {
  extractList,
  extractResource,
  mapProject,
} from '@/services/mappers';
import type { Project } from '@/types/models';

export async function fetchProjects(params?: {
  per_page?: number;
  is_featured?: boolean;
  search?: string;
}): Promise<Project[]> {
  const { data } = await api.get('/public/projects', {
    params: {
      per_page: params?.per_page ?? 50,
      ...(params?.is_featured != null ? { is_featured: params.is_featured } : {}),
      ...(params?.search ? { search: params.search } : {}),
    },
  });

  return extractList<Record<string, unknown>>(data).map(mapProject);
}

export async function fetchProjectBySlug(slug: string): Promise<Project | null> {
  const { data } = await api.get(`/public/projects/${slug}`);
  return mapProject(extractResource<Record<string, unknown>>(data));
}

export async function fetchFeaturedProjects(limit = 4): Promise<Project[]> {
  const featured = await fetchProjects({ is_featured: true, per_page: limit });
  if (featured.length) return featured.slice(0, limit);
  const all = await fetchProjects({ per_page: limit });
  return all.slice(0, limit);
}
