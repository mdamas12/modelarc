<script setup lang="ts">
import type { Project } from '@/types/models';

defineProps<{
  project: Project;
  dark?: boolean;
}>();
</script>

<template>
  <router-link
    :to="`/proyectos/${project.slug}`"
    class="project-card"
    :class="{ 'project-card--dark': dark }"
  >
    <div
      class="project-card__media"
      :style="{ backgroundImage: `url(${project.coverImage})` }"
    >
      <span v-if="project.hasVirtualTour" class="ma-badge project-card__badge">
        Recorrido 360°
      </span>
    </div>
    <div class="project-card__meta">
      <p class="project-card__cat">{{ project.category }} · {{ project.year }}</p>
      <h3>{{ project.title }}</h3>
      <p class="project-card__loc">{{ project.location }}</p>
    </div>
  </router-link>
</template>

<style scoped lang="scss">
.project-card {
  display: block;
  color: inherit;

  &__media {
    position: relative;
    aspect-ratio: 4 / 3;
    background-size: cover;
    background-position: center;
    margin-bottom: 1rem;
    overflow: hidden;

    &::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(transparent 60%, rgba(17, 17, 17, 0.35));
      opacity: 0;
      transition: opacity 0.3s ease;
    }
  }

  &:hover &__media::after {
    opacity: 1;
  }

  &__badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 1;
  }

  &__cat {
    margin: 0 0 0.35rem;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--ma-gold);
  }

  h3 {
    font-size: clamp(1.15rem, 1.5vw, 1.45rem);
    margin: 0 0 0.25rem;
  }

  &__loc {
    margin: 0;
    font-size: 0.9rem;
    color: var(--ma-muted);
  }

  &--dark &__loc {
    color: rgba(247, 244, 240, 0.55);
  }
}
</style>
