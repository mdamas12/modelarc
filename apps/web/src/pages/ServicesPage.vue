<script setup lang="ts">
import { onMounted, ref } from 'vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import { api } from '@/boot/axios';
import { mapService } from '@/services/mappers';
import type { ServiceItem } from '@/types/models';

const services = ref<ServiceItem[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

onMounted(async () => {
  loading.value = true;
  error.value = null;
  try {
    const { data } = await api.get('/public/services');
    const list = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
    services.value = list.map((item: Record<string, unknown>, index: number) =>
      mapService(item, index),
    );
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'No se pudieron cargar los servicios';
    services.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <q-page>
    <section class="page-hero ma-section ma-section--dark">
      <div class="ma-container">
        <SectionHeader
          eyebrow="Expertos"
          title="Nuestros servicios"
          lead="Diseño, construcción y remodelación con un estándar de lujo contemporáneo."
          dark
        />
      </div>
    </section>

    <section class="ma-section ma-section--light">
      <div class="ma-container services-list">
        <div v-if="loading" class="services-list__status">Cargando servicios…</div>
        <div v-else-if="error" class="services-list__status">{{ error }}</div>
        <article
          v-for="(service, i) in services"
          :key="service.id"
          class="services-list__item"
        >
          <div
            class="services-list__media"
            :style="{ backgroundImage: `url(${service.image})` }"
            :class="{ 'services-list__media--right': i % 2 === 1 }"
          />
          <div class="services-list__copy">
            <p class="ma-eyebrow">0{{ i + 1 }}</p>
            <h2 class="ma-heading" style="font-size: 2.4rem">{{ service.title }}</h2>
            <p v-if="service.summary" class="services-list__summary">{{ service.summary }}</p>
            <div class="ma-divider" />
            <p v-if="service.description" class="services-list__description">
              {{ service.description }}
            </p>
            <router-link to="/contacto" class="ma-btn ma-btn--outline-dark">
              Solicitar Presupuesto
            </router-link>
          </div>
        </article>
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

.services-list {
  display: flex;
  flex-direction: column;
  gap: 5rem;

  &__status {
    text-align: center;
    color: var(--ma-muted);
    padding: 2rem 0;
  }

  &__item {
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
    gap: 3rem;
    align-items: center;

    &:nth-child(even) {
      .services-list__media {
        order: 2;
      }
    }
  }

  &__media {
    aspect-ratio: 4 / 5;
    background-size: cover;
    background-position: center;
  }

  &__copy {
    p {
      color: var(--ma-muted);
      line-height: 1.75;
    }
  }

  &__summary {
    margin: 0.85rem 0 0;
    font-size: 1.05rem;
    color: #8a7a68 !important;
    line-height: 1.55;
  }

  &__description {
    margin: 0 0 1.75rem;
  }

  .ma-divider {
    margin: 1.1rem 0 1.25rem;
  }
}

@media (max-width: 900px) {
  .services-list__item,
  .services-list__item:nth-child(even) {
    grid-template-columns: 1fr;

    .services-list__media {
      order: 0;
      aspect-ratio: 16 / 10;
    }
  }
}
</style>
