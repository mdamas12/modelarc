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
      style="background-image: url('https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=2000&q=80')"
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
          lead="Somos un estudio-constructora enfocado en proyectos residenciales y comerciales de alto nivel. Unimos visión creativa, rigor técnico y una experiencia de cliente excepcional."
        />
        <div class="about-intro__grid">
          <p>
            Creemos que los mejores espacios nacen del diálogo entre arquitectura, materialidad y
            estilo de vida. Cada proyecto se desarrolla a medida: desde el primer croquis hasta la
            entrega final y el recorrido virtual 360°.
          </p>
          <p>
            Nuestro equipo integra arquitectos, ingenieros y gestores de obra bajo una misma
            dirección creativa, garantizando coherencia estética y control de calidad en cada etapa.
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
