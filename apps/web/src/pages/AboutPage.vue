<script setup lang="ts">
import SectionHeader from '@/components/common/SectionHeader.vue';

const values = [
  {
    title: 'Precisión',
    text: 'Detalles técnicos y acabados que resisten el paso del tiempo.',
  },
  {
    title: 'Sensibilidad',
    text: 'Espacios que se sienten, no solo se ven. Luz, proporción y material.',
  },
  {
    title: 'Transparencia',
    text: 'Procesos claros, comunicación constante y compromisos reales.',
  },
];
</script>

<template>
  <q-page>
    <section
      class="about-hero"
      style="background-image: url('/about/team.jpg')"
    >
      <div class="about-hero__overlay" />
      <div class="ma-container about-hero__content">
        <p class="ma-eyebrow">Nosotros</p>
        <h1 class="ma-heading ma-heading--lg">Arquitectura con propósito</h1>
      </div>
    </section>

    <section class="ma-section ma-section--light">
      <div class="ma-container about-intro">
        <SectionHeader
          eyebrow="Modelarc"
          title="Diseño contemporáneo, ejecución impecable"
          lead="Somos un estudio de arquitectura y construcción enfocado en proyectos residenciales y comerciales de alto nivel. Combinamos visión creativa, precisión y un cuidado excepcional por cada detalle de tu experiencia."
        />
        <div class="about-intro__grid">
          <p>
            Creemos que los mejores espacios nacen de la combinación entre arquitectura, materiales y
            tu estilo de vida. Hacemos cada proyecto a medida, desde la primera idea y la experiencia
            virtual en 360°, hasta la entrega final del proyecto.
          </p>
          <p>
            Para materializar espacios con carácter, integramos el talento de arquitectos, ingenieros y
            especialistas en obra bajo un solo criterio. Al integrar todas las disciplinas, garantizamos
            que la estética del diseño se respete al máximo y que la ejecución sea impecable de
            principio a fin.
          </p>
        </div>
      </div>
    </section>

    <section class="ma-section ma-section--dark">
      <div class="ma-container about-values">
        <div v-for="item in values" :key="item.title" class="about-values__item">
          <h3>{{ item.title }}</h3>
          <p>{{ item.text }}</p>
        </div>
      </div>
    </section>

    <section class="ma-section ma-section--cream">
      <div class="ma-container about-cta">
        <h2 class="ma-heading">Construyamos algo extraordinario juntos</h2>
        <router-link to="/contacto" class="ma-btn ma-btn--gold">Hablar con el equipo</router-link>
      </div>
    </section>
  </q-page>
</template>



<style scoped lang="scss">
.about-hero {
  position: relative;
  min-height: 65vh;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: flex-end;
  color: var(--ma-cream);

  &__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(transparent 30%, rgba(17, 17, 17, 0.85));
  }

  &__content {
    position: relative;
    z-index: 1;
    padding-bottom: 4rem;
  }
}

.about-intro__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;

  p {
    margin: 0;
    color: var(--ma-muted);
    line-height: 1.8;
  }
}

.about-values {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;

  &__item {
    border-top: 1px solid rgba(196, 164, 124, 0.4);
    padding-top: 1.5rem;

    h3 {
      font-size: 1.6rem;
      color: var(--ma-gold);
      margin-bottom: 0.75rem;
    }

    p {
      margin: 0;
      color: rgba(247, 244, 240, 0.7);
      line-height: 1.7;
    }
  }
}

.about-cta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: wrap;
}

@media (max-width: 800px) {
  .about-intro__grid,
  .about-values {
    grid-template-columns: 1fr;
  }
}
</style>
