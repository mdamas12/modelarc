import { defineStore } from 'pinia';
import { ref } from 'vue';
import { fetchHome, type HeroContent, type HeroGalleryImage, type HomePayload } from '@/services/homeApi';
import type { Project, ServiceItem, Testimonial, VirtualTour } from '@/types/models';

const EMPTY_HERO: HeroContent = { text1: '', text2: '', text3: '' };

export const useHomeStore = defineStore('home', () => {
  const featuredProjects = ref<Project[]>([]);
  const services = ref<ServiceItem[]>([]);
  const testimonials = ref<Testimonial[]>([]);
  const featuredTour = ref<VirtualTour | null>(null);
  const settings = ref<Record<string, string>>({});
  const hero = ref<HeroContent>({ ...EMPTY_HERO });
  const heroGalleries = ref<HeroGalleryImage[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const loaded = ref(false);

  async function loadHome() {
    if (loading.value) return;
    loading.value = true;
    error.value = null;
    try {
      const payload: HomePayload = await fetchHome();
      featuredProjects.value = payload.featuredProjects;
      services.value = payload.services;
      testimonials.value = payload.testimonials;
      featuredTour.value = payload.featuredTour;
      settings.value = payload.settings;
      hero.value = payload.hero;
      heroGalleries.value = payload.heroGalleries;
      loaded.value = true;
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Error al cargar el inicio';
      featuredProjects.value = [];
      services.value = [];
      testimonials.value = [];
      featuredTour.value = null;
      hero.value = { ...EMPTY_HERO };
      heroGalleries.value = [];
    } finally {
      loading.value = false;
    }
  }

  return {
    featuredProjects,
    services,
    testimonials,
    featuredTour,
    settings,
    hero,
    heroGalleries,
    loading,
    error,
    loaded,
    loadHome,
  };
});
