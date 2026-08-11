<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import BeforeAfterSlider from '@/components/projects/BeforeAfterSlider.vue';
import ProjectCard from '@/components/projects/ProjectCard.vue';
import VirtualTourViewer from '@/components/virtual-tour/VirtualTourViewer.vue';
import { useProjectStore } from '@/stores/projectStore';

const route = useRoute();
const store = useProjectStore();

const slug = computed(() => String(route.params.slug || ''));
const tour = computed(() => store.currentTour);
const gallery = computed(() => store.current?.images ?? []);

const galleryPageSize = ref(3);
const galleryPage = ref(0);
const lightboxOpen = ref(false);
const lightboxIndex = ref(0);
const activeImage = computed(() => gallery.value[lightboxIndex.value] || null);

const galleryPageCount = computed(() =>
  Math.max(1, Math.ceil(gallery.value.length / galleryPageSize.value)),
);

const visibleGallery = computed(() => {
  const size = galleryPageSize.value;
  const start = galleryPage.value * size;
  return gallery.value.slice(start, start + size).map((url, offset) => ({
    url,
    index: start + offset,
  }));
});

const canPrevGallery = computed(() => galleryPage.value > 0);
const canNextGallery = computed(() => galleryPage.value < galleryPageCount.value - 1);

const related = computed(() =>
  store.projects
    .filter((p) => p.slug !== store.current?.slug && p.category === store.current?.category)
    .slice(0, 2),
);

function syncGalleryPageSize() {
  const nextSize = window.matchMedia('(max-width: 800px)').matches ? 1 : 3;
  if (galleryPageSize.value === nextSize) return;
  const firstIndex = galleryPage.value * galleryPageSize.value;
  galleryPageSize.value = nextSize;
  galleryPage.value = Math.floor(firstIndex / nextSize);
}

function clampGalleryPage() {
  const maxPage = Math.max(0, galleryPageCount.value - 1);
  if (galleryPage.value > maxPage) galleryPage.value = maxPage;
}

async function load() {
  lightboxOpen.value = false;
  lightboxIndex.value = 0;
  galleryPage.value = 0;
  await store.loadProject(slug.value);
  clampGalleryPage();
}

function prevGalleryPage() {
  if (!canPrevGallery.value) return;
  galleryPage.value -= 1;
}

function nextGalleryPage() {
  if (!canNextGallery.value) return;
  galleryPage.value += 1;
}

function openLightbox(index: number) {
  lightboxIndex.value = index;
  lightboxOpen.value = true;
}

function prevImage() {
  if (!gallery.value.length) return;
  lightboxIndex.value = (lightboxIndex.value - 1 + gallery.value.length) % gallery.value.length;
}

function nextImage() {
  if (!gallery.value.length) return;
  lightboxIndex.value = (lightboxIndex.value + 1) % gallery.value.length;
}

function onKeydown(e: KeyboardEvent) {
  if (!lightboxOpen.value) return;
  if (e.key === 'Escape') lightboxOpen.value = false;
  if (e.key === 'ArrowLeft') prevImage();
  if (e.key === 'ArrowRight') nextImage();
}

onMounted(() => {
  syncGalleryPageSize();
  void load();
  window.addEventListener('keydown', onKeydown);
  window.addEventListener('resize', syncGalleryPageSize);
});

onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown);
  window.removeEventListener('resize', syncGalleryPageSize);
});

watch(slug, load);
watch(galleryPageCount, clampGalleryPage);
</script>

<template>
  <q-page class="project-detail">
    <div v-if="store.loading" class="project-detail__status ma-container">Cargando…</div>
    <div v-else-if="!store.current" class="project-detail__status ma-container">
      Proyecto no encontrado.
      <router-link to="/proyectos" class="ma-btn ma-btn--outline-dark" style="margin-top: 1rem">
        Volver a proyectos
      </router-link>
    </div>

    <template v-else>
      <header
        class="project-detail__hero"
        :style="{ backgroundImage: `url(${store.current.coverImage})` }"
      >
        <div class="project-detail__hero-overlay" />
        <div class="ma-container project-detail__hero-content">
          <p class="ma-eyebrow">{{ store.current.category }} · {{ store.current.year }}</p>
          <h1 class="ma-heading ma-heading--lg">{{ store.current.title }}</h1>
          <p class="project-detail__meta">
            {{ store.current.location }}
            <span v-if="store.current.area"> · {{ store.current.area }}</span>
            <span v-if="store.current.status"> · {{ store.current.status }}</span>
          </p>
        </div>
      </header>

      <section class="project-detail__about ma-section--light">
        <div class="ma-container">
          <h2 class="project-detail__about-title">Sobre este proyecto</h2>
          <div class="ma-divider" />
          <p class="project-detail__about-text">
            {{ store.current.longDescription || store.current.description }}
          </p>
        </div>
      </section>

      <section v-if="gallery.length" class="project-detail__gallery-section ma-section--cream">
        <div class="ma-container">
          <div class="project-detail__gallery-head">
            <h2 class="project-detail__section-title">Galería</h2>
            <div v-if="galleryPageCount > 1" class="project-detail__gallery-nav">
              <button
                type="button"
                class="project-detail__gallery-arrow"
                aria-label="Ver imágenes anteriores"
                :disabled="!canPrevGallery"
                @click="prevGalleryPage"
              >
                ‹
              </button>
              <button
                type="button"
                class="project-detail__gallery-arrow"
                aria-label="Ver imágenes siguientes"
                :disabled="!canNextGallery"
                @click="nextGalleryPage"
              >
                ›
              </button>
            </div>
          </div>
          <div
            class="project-detail__gallery"
            :class="{ 'project-detail__gallery--single': galleryPageSize === 1 }"
            :key="`${galleryPageSize}-${galleryPage}`"
          >
            <button
              v-for="item in visibleGallery"
              :key="item.index"
              type="button"
              class="project-detail__gallery-item"
              :style="{ backgroundImage: `url(${item.url})` }"
              :aria-label="`Ver imagen ${item.index + 1}`"
              @click="openLightbox(item.index)"
            />
          </div>
        </div>
      </section>

      <section
        v-if="store.current.beforeAfterItems?.length"
        class="ma-section ma-section--light"
      >
        <div class="ma-container">
          <h2 class="ma-heading" style="font-size: 2rem; margin-bottom: 1.5rem">
            Antes / Después
          </h2>
          <div class="project-detail__ba-grid">
            <BeforeAfterSlider
              v-for="item in store.current.beforeAfterItems"
              :key="item.id"
              :before-image="item.beforeImage"
              :after-image="item.afterImage"
              :before-label="item.beforeLabel"
              :after-label="item.afterLabel"
              :title="item.title"
              :description="item.description"
            />
          </div>
        </div>
      </section>

      <section v-if="tour?.scenes?.length" class="ma-section ma-section--dark">
        <div class="ma-container">
          <h2 class="ma-heading" style="font-size: 2rem; margin-bottom: 1.5rem">Recorrido 360°</h2>
          <VirtualTourViewer
            :scenes="tour.scenes"
            :cover-image="store.current.coverImage"
            height="600px"
          />
        </div>
      </section>

      <section v-if="related.length" class="ma-section ma-section--light">
        <div class="ma-container">
          <h2 class="ma-heading" style="font-size: 2rem; margin-bottom: 1.5rem">Proyectos relacionados</h2>
          <div class="project-detail__related">
            <ProjectCard v-for="p in related" :key="p.id" :project="p" />
          </div>
        </div>
      </section>

      <Teleport to="body">
        <div
          v-if="lightboxOpen && activeImage"
          class="project-lightbox"
          role="dialog"
          aria-modal="true"
          @click.self="lightboxOpen = false"
        >
          <button
            type="button"
            class="project-lightbox__close"
            aria-label="Cerrar"
            @click="lightboxOpen = false"
          >
            ×
          </button>
          <button
            type="button"
            class="project-lightbox__nav project-lightbox__nav--prev"
            aria-label="Anterior"
            @click="prevImage"
          >
            ‹
          </button>
          <img :src="activeImage" :alt="store.current.title" class="project-lightbox__img" />
          <button
            type="button"
            class="project-lightbox__nav project-lightbox__nav--next"
            aria-label="Siguiente"
            @click="nextImage"
          >
            ›
          </button>
          <p class="project-lightbox__count">
            {{ lightboxIndex + 1 }} / {{ gallery.length }}
          </p>
        </div>
      </Teleport>
    </template>
  </q-page>
</template>

<style scoped lang="scss">
.project-detail {
  &__status {
    padding: 6rem 0;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  &__hero {
    position: relative;
    min-height: 58vh;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: flex-end;
    color: var(--ma-cream);
  }

  &__hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(transparent 20%, rgba(17, 17, 17, 0.85));
  }

  &__hero-content {
    position: relative;
    z-index: 1;
    padding-bottom: 3.5rem;
  }

  &__meta {
    color: rgba(247, 244, 240, 0.7);
    margin: 0;
  }

  &__about {
    padding: 1.15rem 0 0.85rem;
  }

  &__about-title,
  &__section-title {
    margin: 0;
    font-family: var(--ma-font-sans);
    font-size: clamp(1.25rem, 2vw, 1.55rem);
    font-weight: 600;
    letter-spacing: 0.01em;
    line-height: 1.25;
    text-align: left;
    color: var(--ma-charcoal);
  }

  &__section-title {
    margin-bottom: 0;
  }

  &__about-text {
    max-width: 42rem;
    margin: 0;
    color: var(--ma-muted);
    line-height: 1.7;
    font-size: 1rem;
    text-align: left;
  }

  &__about .ma-divider {
    margin: 0.55rem 0 0.75rem;
  }

  &__gallery-section {
    padding: 1.35rem 0 2.5rem;
  }

  &__gallery-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  &__gallery-nav {
    display: flex;
    gap: 0.45rem;
  }

  &__gallery-arrow {
    width: 2.35rem;
    height: 2.35rem;
    border: 1px solid rgba(26, 26, 26, 0.18);
    border-radius: 999px;
    background: transparent;
    color: var(--ma-charcoal);
    font-size: 1.45rem;
    line-height: 1;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition:
      background 0.2s ease,
      border-color 0.2s ease,
      opacity 0.2s ease;

    &:hover:not(:disabled) {
      background: rgba(26, 26, 26, 0.06);
      border-color: rgba(26, 26, 26, 0.35);
    }

    &:disabled {
      opacity: 0.35;
      cursor: default;
    }
  }

  &__gallery {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
    width: 100%;
    animation: gallery-fade 0.28s ease;

    &--single {
      grid-template-columns: minmax(0, 1fr);
    }
  }

  &__gallery-item {
    aspect-ratio: 1;
    width: 100%;
    border: 0;
    padding: 0;
    background-size: cover;
    background-position: center;
    background-color: #1a1a1a;
    cursor: zoom-in;
  }

  &__related {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }

  &__ba-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 2rem 1.5rem;
  }
}

.project-lightbox {
  position: fixed;
  inset: 0;
  z-index: 9000;
  background: rgba(10, 10, 10, 0.94);
  display: grid;
  place-items: center;
}

.project-lightbox__img {
  max-width: min(1100px, 92vw);
  max-height: 82vh;
  object-fit: contain;
}

.project-lightbox__close,
.project-lightbox__nav {
  position: absolute;
  border: 0;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  cursor: pointer;
  display: grid;
  place-items: center;
}

.project-lightbox__close {
  top: 1rem;
  right: 1rem;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 999px;
  font-size: 1.5rem;
}

.project-lightbox__nav {
  top: 50%;
  transform: translateY(-50%);
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 999px;
  font-size: 1.8rem;
  line-height: 1;

  &--prev {
    left: 1rem;
  }

  &--next {
    right: 1rem;
  }
}

.project-lightbox__count {
  position: absolute;
  bottom: 1.25rem;
  left: 50%;
  transform: translateX(-50%);
  margin: 0;
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.85rem;
}

@keyframes gallery-fade {
  from {
    opacity: 0.35;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 1100px) {
  .project-detail__related,
  .project-detail__ba-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 800px) {
  .project-detail {
    &__related,
    &__ba-grid {
      grid-template-columns: 1fr;
    }

    &__gallery {
      grid-template-columns: minmax(0, 1fr);
      gap: 0;
    }

    &__gallery-item {
      aspect-ratio: 4 / 3;
      max-height: min(68vw, 420px);
    }

    &__gallery-arrow {
      width: 2.1rem;
      height: 2.1rem;
      font-size: 1.3rem;
    }
  }

  .project-lightbox__nav {
    width: 2.4rem;
    height: 2.4rem;
  }
}
</style>
