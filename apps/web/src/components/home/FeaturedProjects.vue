<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import ProjectCard from '@/components/projects/ProjectCard.vue';
import { useHomeStore } from '@/stores/homeStore';

const home = useHomeStore();

const pageSize = ref(3);
const page = ref(0);

const projects = computed(() => home.featuredProjects);

const pageCount = computed(() => Math.max(1, Math.ceil(projects.value.length / pageSize.value)));

const visibleProjects = computed(() => {
  const size = pageSize.value;
  const start = page.value * size;
  return projects.value.slice(start, start + size);
});

const canPrev = computed(() => page.value > 0);
const canNext = computed(() => page.value < pageCount.value - 1);

function syncPageSize() {
  const nextSize = window.matchMedia('(max-width: 800px)').matches ? 1 : 3;
  if (pageSize.value === nextSize) return;
  const firstIndex = page.value * pageSize.value;
  pageSize.value = nextSize;
  page.value = Math.floor(firstIndex / nextSize);
}

function clampPage() {
  const maxPage = Math.max(0, pageCount.value - 1);
  if (page.value > maxPage) page.value = maxPage;
}

function prevPage() {
  if (!canPrev.value) return;
  page.value -= 1;
}

function nextPage() {
  if (!canNext.value) return;
  page.value += 1;
}

onMounted(() => {
  syncPageSize();
  window.addEventListener('resize', syncPageSize);
  if (!home.loaded) void home.loadHome();
});

onUnmounted(() => {
  window.removeEventListener('resize', syncPageSize);
});

watch(pageCount, clampPage);
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
        <div class="featured__head-actions">
          <div v-if="pageCount > 1" class="featured__nav">
            <button
              type="button"
              class="featured__arrow"
              aria-label="Ver proyectos anteriores"
              :disabled="!canPrev"
              @click="prevPage"
            >
              ‹
            </button>
            <button
              type="button"
              class="featured__arrow"
              aria-label="Ver proyectos siguientes"
              :disabled="!canNext"
              @click="nextPage"
            >
              ›
            </button>
          </div>
          <router-link to="/proyectos" class="ma-btn ma-btn--outline featured__all">
            Ver todos
          </router-link>
        </div>
      </div>

      <div v-if="home.loading && !projects.length" class="featured__status">
        Cargando proyectos…
      </div>
      <div v-else-if="home.error && !projects.length" class="featured__status">
        {{ home.error }}
      </div>
      <div
        v-else
        class="featured__grid"
        :class="{ 'featured__grid--single': pageSize === 1 }"
        :key="`${pageSize}-${page}`"
      >
        <ProjectCard
          v-for="project in visibleProjects"
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

  &__head-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
    margin-bottom: 2.5rem;
  }

  &__nav {
    display: flex;
    gap: 0.45rem;
  }

  &__arrow {
    width: 2.35rem;
    height: 2.35rem;
    border: 1px solid rgba(247, 244, 240, 0.28);
    border-radius: 999px;
    background: transparent;
    color: #f7f4f0;
    font-size: 1.45rem;
    line-height: 1;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition:
      background 0.2s ease,
      border-color 0.2s ease,
      opacity 0.2s ease;

    &:hover:not(:disabled) {
      background: rgba(247, 244, 240, 0.08);
      border-color: rgba(247, 244, 240, 0.5);
    }

    &:disabled {
      opacity: 0.35;
      cursor: default;
    }
  }

  &__all {
    flex-shrink: 0;
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
    animation: featured-fade 0.28s ease;

    &--single {
      grid-template-columns: minmax(0, 1fr);
    }
  }

  &__status {
    color: rgba(247, 244, 240, 0.65);
    padding: 2rem 0;
  }
}

@keyframes featured-fade {
  from {
    opacity: 0.35;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 800px) {
  .featured {
    &__head {
      flex-direction: column;
      align-items: flex-start;
    }

    &__head-actions {
      margin-bottom: 0;
      width: 100%;
      justify-content: space-between;
    }
  }
}
</style>
