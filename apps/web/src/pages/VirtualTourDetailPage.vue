<script setup lang="ts">
import { computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import VirtualTourViewer from '@/components/virtual-tour/VirtualTourViewer.vue';
import { useVirtualTourStore } from '@/stores/virtualTourStore';

const route = useRoute();
const store = useVirtualTourStore();
const slug = computed(() => String(route.params.slug || ''));

async function load() {
  await store.loadTour(slug.value);
}

onMounted(load);
watch(slug, load);
</script>

<template>
  <q-page class="tour-detail">
    <div v-if="store.loading" class="tour-detail__status ma-container">Preparando tu experiencia…</div>
    <div v-else-if="!store.current" class="tour-detail__status ma-container">
      Recorrido no encontrado.
      <router-link to="/recorridos-360" class="ma-btn ma-btn--outline-dark" style="margin-top: 1rem">
        Ver todos
      </router-link>
    </div>
    <template v-else>
      <section class="ma-section ma-section--dark tour-detail__head">
        <div class="ma-container">
          <p class="ma-eyebrow">Recorrido 360°</p>
          <h1 class="ma-heading">{{ store.current.title }}</h1>
          <p class="tour-detail__desc">{{ store.current.description }}</p>
          <router-link
            v-if="store.current.projectSlug"
            :to="`/proyectos/${store.current.projectSlug}`"
            class="tour-detail__project-link"
          >
            Ver proyecto →
          </router-link>
        </div>
      </section>
      <section class="ma-section ma-section--charcoal" style="padding-top: 0">
        <div class="ma-container">
          <VirtualTourViewer
            :scenes="store.current.scenes"
            :cover-image="store.current.coverImage"
            autoplay
            height="70vh"
          />
        </div>
      </section>
    </template>
  </q-page>
</template>

<style scoped lang="scss">
.tour-detail {
  &__status {
    padding: 6rem 0;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  &__head {
    padding-top: 5rem;
    padding-bottom: 2rem;
  }

  &__desc {
    color: rgba(247, 244, 240, 0.7);
    max-width: 36rem;
  }

  &__project-link {
    display: inline-block;
    margin-top: 1rem;
    font-size: 0.75rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ma-gold);
  }
}
</style>
