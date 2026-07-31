<script setup lang="ts">
import SectionHeader from '@/components/common/SectionHeader.vue';
import { mockProcess } from '@/data/mockData';
</script>

<template>
  <section class="ma-section ma-section--light process">
    <div class="ma-container">
      <SectionHeader
        eyebrow="Cómo trabajamos"
        title="Un proceso claro, de principio a fin"
        lead="Seis etapas pensadas para dar certeza, calidad y transparencia en cada decisión."
        align="center"
      />

      <ol class="process__steps">
        <li v-for="step in mockProcess" :key="step.number" class="process__step">
          <span class="process__num">{{ step.number }}</span>
          <h3>{{ step.title }}</h3>
          <p>{{ step.description }}</p>
        </li>
      </ol>
    </div>
  </section>
</template>

<style scoped lang="scss">
.process {
  &__steps {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1.25rem;
    counter-reset: none;
  }

  &__step {
    position: relative;
    padding-top: 0.5rem;

    &::after {
      content: '';
      position: absolute;
      top: 1.1rem;
      left: 3.2rem;
      right: -0.75rem;
      height: 1px;
      background: rgba(196, 164, 124, 0.35);
    }

    &:last-child::after {
      display: none;
    }

    h3 {
      font-size: 1.25rem;
      margin: 0.85rem 0 0.45rem;
    }

    p {
      margin: 0;
      font-size: 0.88rem;
      color: var(--ma-muted);
      line-height: 1.6;
    }
  }

  &__num {
    font-family: var(--ma-font-serif);
    font-size: 1.5rem;
    color: var(--ma-gold);
    font-weight: 400;
  }
}

@media (max-width: 1100px) {
  .process__steps {
    grid-template-columns: repeat(3, 1fr);
  }

  .process__step:nth-child(3n)::after {
    display: none;
  }
}

@media (max-width: 700px) {
  .process__steps {
    grid-template-columns: 1fr 1fr;
  }

  .process__step::after {
    display: none;
  }
}
</style>
