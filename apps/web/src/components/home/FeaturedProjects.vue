<script setup lang="ts">
import { onMounted } from 'vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import ProjectCard from '@/components/projects/ProjectCard.vue';
import { useHomeStore } from '@/stores/homeStore';

const home = useHomeStore();

onMounted(() => {
  if (!home.loaded) void home.loadHome();
});
</script>

<template>
  <section class="ma-section ma-section--dark featured">
    <div class="ma-container">
      <div class="featured__head">
        <SectionHeader
          eyebrow="Portafolio"
          title="Proyectos destacados"
          lead="Obras que combinan arquitectura contemporánea, materialidad noble y experiencias espaciales memorables."
          dark
        />
        <router-link to="/proyectos" class="ma-btn ma-btn--outline featured__all">
          Ver todos
        </router-link>
      </div>

      <div v-if="home.loading && !home.featuredProjects.length" class="featured__status">
        Cargando proyectos…
      </div>
      <div v-else-if="home.error && !home.featuredProjects.length" class="featured__status">
        {{ home.error }}
      </div>
      <div v-else class="featured__grid">
        <ProjectCard
          v-for="project in home.featuredProjects.slice(0, 6)"
          :key="project.id"
          :project="project"
          dark
        />
      </div>
    </div>
  </section>
</template>

<style scoped lang="scss">
.featured {
  &__head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 2rem;
    margin-bottom: 0.5rem;

    :deep(.section-header) {
      margin-bottom: 2rem;
    }
  }

  &__all {
    flex-shrink: 0;
    margin-bottom: 2.5rem;
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }

  &__status {
    color: rgba(247, 244, 240, 0.65);
    padding: 2rem 0;
  }
}

@media (max-width: 1100px) {
  .featured__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 700px) {
  .featured {
    &__head {
      flex-direction: column;
      align-items: flex-start;
    }

    &__all {
      margin-bottom: 0;
    }

    &__grid {
      grid-template-columns: 1fr;
    }
  }
}
</style>
