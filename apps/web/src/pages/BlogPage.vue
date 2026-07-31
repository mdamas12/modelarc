<script setup lang="ts">
import SectionHeader from '@/components/common/SectionHeader.vue';

const posts = [
  {
    title: 'Materiales nobles para residencias contemporáneas',
    date: '12 Mar 2026',
    excerpt: 'Piedra, madera y bronce: cómo combinarlos sin saturar el espacio.',
    image: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1000&q=80',
  },
  {
    title: 'Recorridos 360° en el proceso de diseño',
    date: '28 Feb 2026',
    excerpt: 'Por qué la inmersión virtual acelera decisiones y reduce cambios en obra.',
    image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
  },
  {
    title: 'Luz natural: el lujo silencioso',
    date: '10 Feb 2026',
    excerpt: 'Estrategias de orientación y aperturas para climas tropicales.',
    image: 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1000&q=80',
  },
];
</script>

<template>
  <q-page>
    <section class="page-hero ma-section ma-section--dark">
      <div class="ma-container">
        <SectionHeader
          eyebrow="Blog"
          title="Ideas y proceso"
          lead="Artículos sobre arquitectura, construcción y la cultura del detalle. (Placeholder)"
          dark
        />
      </div>
    </section>

    <section class="ma-section ma-section--light">
      <div class="ma-container blog-grid">
        <article v-for="post in posts" :key="post.title" class="blog-card">
          <div class="blog-card__media" :style="{ backgroundImage: `url(${post.image})` }" />
          <p class="blog-card__date">{{ post.date }}</p>
          <h3>{{ post.title }}</h3>
          <p>{{ post.excerpt }}</p>
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

.blog-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.blog-card {
  &__media {
    aspect-ratio: 16 / 10;
    background-size: cover;
    background-position: center;
    margin-bottom: 1rem;
  }

  &__date {
    margin: 0 0 0.4rem;
    font-size: 0.68rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--ma-gold);
  }

  h3 {
    font-size: 1.45rem;
    margin-bottom: 0.5rem;
  }

  p {
    margin: 0;
    color: var(--ma-muted);
    line-height: 1.65;
  }
}

@media (max-width: 900px) {
  .blog-grid {
    grid-template-columns: 1fr;
  }
}
</style>
