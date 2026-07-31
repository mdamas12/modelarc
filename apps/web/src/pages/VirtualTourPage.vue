<script setup lang="ts">
import { onMounted } from 'vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import { useVirtualTourStore } from '@/stores/virtualTourStore';

const store = useVirtualTourStore();

onMounted(() => {
  void store.loadTours();
});
</script>

<template>
  <q-page>
    <section class="page-hero ma-section ma-section--dark">
      <div class="ma-container">
        <SectionHeader
          eyebrow="Inmersivo"
          title="Recorridos virtuales 360°"
          lead="Explora proyectos antes de construirlos. Escenas conectadas, puntos interactivos y una experiencia de alto impacto."
          dark
        />
      </div>
    </section>

    <section class="ma-section ma-section--light">
      <div class="ma-container">
        <div v-if="store.loading" class="tours-status">Cargando recorridos…</div>
        <div v-else class="tours-grid">
          <router-link
            v-for="tour in store.tours"
            :key="tour.id"
            :to="`/recorridos-360/${tour.slug}`"
            class="tour-card"
          >
            <div
              class="tour-card__media"
              :style="{ backgroundImage: `url(${tour.coverImage})` }"
            >
              <span class="ma-badge">360°</span>
            </div>
            <h3>{{ tour.title }}</h3>
            <p>{{ tour.description }}</p>
          </router-link>
        </div>
      </div>
    </section>
  </q-page>
</template>

<style scoped lang="scss">
.page-hero {
  padding-top: 5rem;
  padding-bottom: 3rem;

  :deep(.section-header) {
    margin-bottom: 0;
  }
}

.tours-status {
  text-align: center;
  color: var(--ma-muted);
  padding: 3rem 0;
}

.tours-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.75rem;
}

.tour-card {
  display: block;
  color: inherit;

  &__media {
    position: relative;
    aspect-ratio: 4 / 3;
    background-size: cover;
    background-position: center;
    margin-bottom: 1rem;

    .ma-badge {
      position: absolute;
      top: 1rem;
      left: 1rem;
    }
  }

  h3 {
    font-size: 1.4rem;
    margin-bottom: 0.4rem;
  }

  p {
    margin: 0;
    color: var(--ma-muted);
    font-size: 0.92rem;
    line-height: 1.6;
  }
}

@media (max-width: 900px) {
  .tours-grid {
    grid-template-columns: 1fr;
  }
}
</style>
