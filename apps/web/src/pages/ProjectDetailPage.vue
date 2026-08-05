<script setup lang="ts">
import { computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import BeforeAfterSlider from '@/components/projects/BeforeAfterSlider.vue';
import ProjectCard from '@/components/projects/ProjectCard.vue';
import VirtualTourViewer from '@/components/virtual-tour/VirtualTourViewer.vue';
import { useProjectStore } from '@/stores/projectStore';

const route = useRoute();
const store = useProjectStore();

const slug = computed(() => String(route.params.slug || ''));

const tour = computed(() => store.currentTour);

const related = computed(() =>
  store.projects
    .filter((p) => p.slug !== store.current?.slug && p.category === store.current?.category)
    .slice(0, 2),
);

async function load() {
  await store.loadProject(slug.value);
}

onMounted(load);
watch(slug, load);
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

      <section class="ma-section ma-section--light">
        <div class="ma-container project-detail__intro">
          <div>
            <h2 class="ma-heading" style="font-size: 2rem">Sobre el proyecto</h2>
            <div class="ma-divider" />
            <p>{{ store.current.longDescription || store.current.description }}</p>
          </div>
          <aside class="project-detail__facts">
            <div><span>Ubicación</span><strong>{{ store.current.location }}</strong></div>
            <div><span>Año</span><strong>{{ store.current.year }}</strong></div>
            <div v-if="store.current.area"><span>Área</span><strong>{{ store.current.area }}</strong></div>
            <div v-if="store.current.status"><span>Estado</span><strong>{{ store.current.status }}</strong></div>
          </aside>
        </div>
      </section>

      <section class="ma-section ma-section--cream">
        <div class="ma-container">
          <h2 class="ma-heading" style="font-size: 2rem; margin-bottom: 1.5rem">Galería</h2>
          <div class="project-detail__gallery">
            <div
              v-for="(img, i) in store.current.images"
              :key="i"
              class="project-detail__gallery-item"
              :style="{ backgroundImage: `url(${img})` }"
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

  &__intro {
    display: grid;
    grid-template-columns: 1.4fr 0.8fr;
    gap: 3rem;

    p {
      color: var(--ma-muted);
      line-height: 1.8;
      font-size: 1.05rem;
    }
  }

  &__facts {
    display: grid;
    gap: 1.25rem;
    align-content: start;
    padding: 1.75rem;
    border-top: 1px solid var(--ma-gold);

    div {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;

      span {
        font-size: 0.68rem;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--ma-gold);
      }

      strong {
        font-weight: 500;
        font-size: 1.05rem;
      }
    }
  }

  &__gallery {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }

  &__gallery-item {
    aspect-ratio: 4 / 3;
    background-size: cover;
    background-position: center;

    &:first-child {
      grid-column: span 2;
      aspect-ratio: 21 / 9;
    }
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

@media (max-width: 1100px) {
  .project-detail__related,
  .project-detail__ba-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 800px) {
  .project-detail {
    &__intro,
    &__related,
    &__gallery,
    &__ba-grid {
      grid-template-columns: 1fr;
    }

    &__gallery-item:first-child {
      grid-column: auto;
      aspect-ratio: 16 / 10;
    }
  }
}
</style>
