import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchPublicCategories } from '@/services/categoryApi';
import { fetchProjectBySlug, fetchProjects } from '@/services/projectApi';
import { fetchProjectTour } from '@/services/virtualTourApi';
import type { Project, VirtualTour } from '@/types/models';

function titleCase(value: string): string {
  return value
    .replaceAll('_', ' ')
    .split(' ')
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

export const useProjectStore = defineStore('project', () => {
  const projects = ref<Project[]>([]);
  const current = ref<Project | null>(null);
  const currentTour = ref<VirtualTour | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const categoryFilter = ref('Todos');
  const searchQuery = ref('');
  const apiCategoryNames = ref<string[]>([]);

  const categories = computed(() => {
    // Prefer the admin-managed category list (keeps filters in sync even for
    // categories with no published projects yet); fall back to deriving from
    // loaded projects if the categories endpoint is unavailable.
    const names = apiCategoryNames.value.length
      ? apiCategoryNames.value
      : Array.from(new Set(projects.value.map((p) => p.category).filter(Boolean)));

    return ['Todos', ...names];
  });

  const filteredProjects = computed(() => {
    return projects.value.filter((p) => {
      const catOk = categoryFilter.value === 'Todos' || p.category === categoryFilter.value;
      const q = searchQuery.value.trim().toLowerCase();
      const searchOk =
        !q ||
        p.title.toLowerCase().includes(q) ||
        p.location.toLowerCase().includes(q) ||
        p.category.toLowerCase().includes(q);
      return catOk && searchOk;
    });
  });

  const featured = computed(() => {
    const marked = projects.value.filter((p) => p.isFeatured);
    return (marked.length ? marked : projects.value).slice(0, 4);
  });

  async function loadProjects() {
    loading.value = true;
    error.value = null;
    try {
      projects.value = await fetchProjects();
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Error al cargar proyectos';
      projects.value = [];
    } finally {
      loading.value = false;
    }

    try {
      const apiCategories = await fetchPublicCategories();
      apiCategoryNames.value = apiCategories.map((c) => titleCase(c.name));
    } catch {
      apiCategoryNames.value = [];
    }
  }

  async function loadProject(slug: string) {
    loading.value = true;
    error.value = null;
    currentTour.value = null;
    try {
      current.value = await fetchProjectBySlug(slug);
      if (!projects.value.length) {
        projects.value = await fetchProjects();
      }
      if (current.value?.hasVirtualTour) {
        try {
          currentTour.value = await fetchProjectTour(slug);
          if (currentTour.value && !current.value.tourSlug) {
            current.value = {
              ...current.value,
              tourSlug: currentTour.value.slug,
            };
          }
        } catch {
          currentTour.value = null;
        }
      }
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Error al cargar el proyecto';
      current.value = null;
      currentTour.value = null;
    } finally {
      loading.value = false;
    }
  }

  function setCategory(category: string) {
    categoryFilter.value = category;
  }

  function setSearch(query: string) {
    searchQuery.value = query;
  }

  return {
    projects,
    current,
    currentTour,
    loading,
    error,
    categoryFilter,
    searchQuery,
    categories,
    filteredProjects,
    featured,
    loadProjects,
    loadProject,
    setCategory,
    setSearch,
  };
});
