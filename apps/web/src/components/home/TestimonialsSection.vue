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
  <section class="ma-section ma-section--charcoal testimonials">
    <div class="ma-container">
      <SectionHeader
        eyebrow="Clientes"
        title="Lo que dicen de nosotros"
        align="center"
        dark
      />

      <div v-if="home.loading && !home.testimonials.length" class="testimonials__status">
        Cargando testimonios…
      </div>
      <div v-else class="testimonials__grid">
        <blockquote
          v-for="item in home.testimonials"
          :key="item.id"
          class="testimonials__card"
        >
          <p class="testimonials__quote">“{{ item.quote }}”</p>
          <footer>
            <strong>{{ item.name }}</strong>
            <span>{{ item.role }}</span>
          </footer>
        </blockquote>
      </div>
    </div>
  </section>
</template>

<style scoped lang="scss">
.testimonials {
  &__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }

  &__card {
    margin: 0;
    padding: 2rem 1.75rem;
    border-top: 1px solid rgba(196, 164, 124, 0.45);
    background: rgba(255, 255, 255, 0.02);
  }

  &__quote {
    font-family: var(--ma-font-serif);
    font-size: 1.35rem;
    line-height: 1.45;
    color: var(--ma-cream);
    margin: 0 0 1.75rem;
    font-style: normal;
    font-weight: 400;
  }

  &__status {
    color: rgba(247, 244, 240, 0.65);
    text-align: center;
    padding: 1rem 0 2rem;
  }

  footer {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;

    strong {
      font-family: var(--ma-font-sans);
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      color: var(--ma-gold);
    }

    span {
      font-size: 0.8rem;
      color: rgba(247, 244, 240, 0.55);
    }
  }
}

@media (max-width: 900px) {
  .testimonials__grid {
    grid-template-columns: 1fr;
  }
}
</style>
