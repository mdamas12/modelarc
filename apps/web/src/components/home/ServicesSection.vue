<script setup lang="ts">
import { onMounted } from 'vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import { useHomeStore } from '@/stores/homeStore';

const home = useHomeStore();

onMounted(() => {
  if (!home.loaded) void home.loadHome();
});
</script>

<template>
  <section class="ma-section ma-section--light services">
    <div class="ma-container">
      <SectionHeader
        eyebrow="Lo que hacemos"
        title="Servicios de excelencia"
        lead="Acompañamos cada etapa del proyecto con un equipo multidisciplinario y un estándar de calidad impecable."
      />

      <div v-if="home.loading && !home.services.length" class="services__status">
        Cargando servicios…
      </div>
      <div v-else class="services__grid">
        <article v-for="service in home.services" :key="service.id" class="services__item">
          <div
            class="services__image"
            :style="{ backgroundImage: `url(${service.image})` }"
          />
          <div class="services__body">
            <h3>{{ service.title }}</h3>
            <p>{{ service.description }}</p>
            <router-link to="/servicios" class="services__link">Descubrir →</router-link>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<style scoped lang="scss">
.services {
  &__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
  }

  &__item {
    display: flex;
    flex-direction: column;
  }

  &__image {
    aspect-ratio: 4 / 5;
    background-size: cover;
    background-position: center;
    margin-bottom: 1.35rem;
  }

  &__body {
    h3 {
      font-size: 1.65rem;
      margin-bottom: 0.65rem;
    }

    p {
      color: var(--ma-muted);
      font-size: 0.95rem;
      margin: 0 0 1rem;
      line-height: 1.7;
    }
  }

  &__link {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--ma-gold-dark);

    &:hover {
      color: var(--ma-charcoal);
    }
  }

  &__status {
    color: var(--ma-muted);
    padding: 1rem 0 2rem;
  }
}

@media (max-width: 900px) {
  .services__grid {
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }

  .services__image {
    aspect-ratio: 16 / 10;
  }
}
</style>
