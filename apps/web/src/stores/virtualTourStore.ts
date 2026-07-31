import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchVirtualTourBySlug, fetchVirtualTours } from '@/services/virtualTourApi';
import type { TourScene, VirtualTour } from '@/types/models';

export const useVirtualTourStore = defineStore('virtualTour', () => {
  const tours = ref<VirtualTour[]>([]);
  const current = ref<VirtualTour | null>(null);
  const activeSceneId = ref<string | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const activeScene = computed<TourScene | null>(() => {
    if (!current.value || !activeSceneId.value) return null;
    return current.value.scenes.find((s) => s.id === activeSceneId.value) ?? null;
  });

  async function loadTours() {
    loading.value = true;
    error.value = null;
    try {
      tours.value = await fetchVirtualTours();
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Error al cargar recorridos';
      tours.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function loadTour(slug: string) {
    loading.value = true;
    error.value = null;
    try {
      current.value = await fetchVirtualTourBySlug(slug);
      activeSceneId.value = current.value?.scenes[0]?.id ?? null;
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Error al cargar el recorrido';
      current.value = null;
      activeSceneId.value = null;
    } finally {
      loading.value = false;
    }
  }

  function setScene(sceneId: string) {
    if (current.value?.scenes.some((s) => s.id === sceneId)) {
      activeSceneId.value = sceneId;
    }
  }

  return {
    tours,
    current,
    activeSceneId,
    activeScene,
    loading,
    error,
    loadTours,
    loadTour,
    setScene,
  };
});
