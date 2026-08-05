<script setup lang="ts">
import { onMounted } from 'vue';
import ProjectCard from '@/components/projects/ProjectCard.vue';
import ProjectFilters from '@/components/projects/ProjectFilters.vue';
import PublicMediaGallery from '@/components/projects/PublicMediaGallery.vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import { useProjectStore } from '@/stores/projectStore';

const store = useProjectStore();

onMounted(() => {
  void store.loadProjects();
});
</script>

<template>
  <q-page class="projects-page">
    <section class="page-hero ma-section ma-section--dark">
      <div class="ma-container">
        <SectionHeader
          eyebrow="Portafolio"
          title="Nuestros proyectos"
          lead="Residencias, espacios comerciales y remodelaciones con una mirada contemporánea."
          dark
        />
      </div>
    </section>

    <section class="ma-section ma-section--light">
      <div class="ma-container">
        <ProjectFilters
          :categories="store.categories"
          :model-value="store.categoryFilter"
          :search="store.searchQuery"
          @update:model-value="store.setCategory"
          @update:search="store.setSearch"
        />

        <div v-if="store.loading" class="projects-page__status">Cargando proyectos…</div>
        <div v-else-if="!store.filteredProjects.length" class="projects-page__status">
          No hay proyectos con estos filtros.
        </div>
        <div v-else class="projects-page__grid">
          <ProjectCard
            v-for="project in store.filteredProjects"
            :key="project.id"
            :project="project"
          />
        </div>
      </div>
    </section>

    <section class="ma-section ma-section--cream">
      <div class="ma-container">
        <PublicMediaGallery />
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

.projects-page {
  &__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
  }

  &__status {
    text-align: center;
    color: var(--ma-muted);
    padding: 3rem 0;
  }
}

@media (max-width: 1100px) {
  .projects-page__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 700px) {
  .projects-page__grid {
    grid-template-columns: 1fr;
  }
}
</style>
