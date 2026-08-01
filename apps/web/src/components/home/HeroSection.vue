<script setup lang="ts">
import { heroImage } from '@/data/mockData';
</script>

<template>
  <section class="hero" :style="{ backgroundImage: `url(${heroImage})` }">
    <div class="hero__overlay" />
    <div class="hero__content ma-container">
      <p class="ma-eyebrow">Arquitectura · Construcción · Remodelación</p>
      <h1 class="hero__title">
        <span class="hero__title-line">Diseñamos espacios</span>
        <span class="hero__title-line">que transforman tu vida</span>
      </h1>
      <p class="hero__lead">
        Proyectos residenciales y comerciales con identidad, precisión técnica y una experiencia
        inmersiva en cada detalle.
      </p>
      <div class="hero__actions">
        <router-link to="/proyectos" class="ma-btn ma-btn--gold">Ver proyectos</router-link>
        <router-link to="/recorridos-360" class="ma-btn ma-btn--outline">Explorar 360°</router-link>
      </div>
    </div>
    <div class="hero__scroll" aria-hidden="true">
      <span>Scroll</span>
    </div>
  </section>
</template>

<style scoped lang="scss">
.hero {
  position: relative;
  min-height: calc(100vh - 4.5rem);
  min-height: calc(100dvh - 4.5rem);
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: flex-end;
  color: var(--ma-cream);

  &__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
      180deg,
      rgba(17, 17, 17, 0.35) 0%,
      rgba(17, 17, 17, 0.55) 45%,
      rgba(17, 17, 17, 0.88) 100%
    );
  }

  &__content {
    position: relative;
    z-index: 1;
    padding-bottom: clamp(5rem, 12vh, 8rem);
    max-width: 52rem;
  }

  &__title {
    font-family: var(--ma-font-serif);
    font-size: clamp(2.8rem, 7vw, 5.2rem);
    font-weight: 400;
    line-height: 1.05;
    letter-spacing: 0.01em;
    text-transform: uppercase;
    margin: 0 0 1.25rem;
  }

  &__title-line {
    display: block;
  }

  &__lead {
    font-size: 1.05rem;
    line-height: 1.7;
    color: rgba(247, 244, 240, 0.78);
    max-width: 32rem;
    margin: 0 0 2rem;
  }

  &__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
  }

  &__scroll {
    position: absolute;
    right: 2rem;
    bottom: 2.5rem;
    z-index: 1;
    writing-mode: vertical-rl;
    font-size: 0.65rem;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: rgba(247, 244, 240, 0.55);

    span::after {
      content: '';
      display: block;
      width: 1px;
      height: 3rem;
      margin: 0.75rem auto 0;
      background: rgba(196, 164, 124, 0.6);
    }
  }
}

@media (max-width: 700px) {
  .hero__scroll {
    display: none;
  }

  .hero__title {
    font-size: clamp(1.85rem, 8.2vw, 2.55rem);
    line-height: 1.08;
  }

  .hero__actions {
    flex-direction: column;
    align-items: stretch;

    .ma-btn {
      width: 100%;
    }
  }
}
</style>
