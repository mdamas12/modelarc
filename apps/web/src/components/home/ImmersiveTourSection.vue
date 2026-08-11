<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import { useHomeStore } from '@/stores/homeStore';

const home = useHomeStore();

const pageSize = ref(3);
const startIndex = ref(0);
const isMobile = ref(false);

const tours = computed(() => home.featuredTours);

const resolvedPageSize = computed(() => {
  const n = tours.value.length;
  if (!n) return 1;
  if (isMobile.value) return 1;
  // With exactly 3 tours, show 2 so the arrows can move (same UX as projects slider).
  if (n === 3) return 2;
  return Math.min(3, n);
});

const maxStart = computed(() => Math.max(0, tours.value.length - resolvedPageSize.value));

const visibleTours = computed(() => {
  const list = tours.value;
  if (!list.length) return [];
  const size = resolvedPageSize.value;
  return list.slice(startIndex.value, startIndex.value + size);
});

const canPrev = computed(() => startIndex.value > 0);
const canNext = computed(() => startIndex.value < maxStart.value);
const showNav = computed(() => tours.value.length > resolvedPageSize.value);

function syncPageSize() {
  isMobile.value = window.matchMedia('(max-width: 800px)').matches;
  pageSize.value = resolvedPageSize.value;
  if (startIndex.value > maxStart.value) startIndex.value = maxStart.value;
}

function prevPage() {
  if (!canPrev.value) return;
  startIndex.value -= 1;
}

function nextPage() {
  if (!canNext.value) return;
  startIndex.value += 1;
}

onMounted(() => {
  syncPageSize();
  window.addEventListener('resize', syncPageSize);
  if (!home.loaded) void home.loadHome();
});

onUnmounted(() => {
  window.removeEventListener('resize', syncPageSize);
});

watch([() => tours.value.length, resolvedPageSize], () => {
  syncPageSize();
});
</script>

<template>
  <section class="ma-section ma-section--dark immersive">
    <div class="ma-container">
      <div class="immersive__head">
        <SectionHeader
          eyebrow="Experiencia inmersiva"
          title="Recorridos virtuales 360°"
          lead="Explora cada ambiente antes de construir. Orientación, puntos interactivos y escenas conectadas."
          dark
        />
        <div class="immersive__head-actions">
          <div v-if="showNav" class="immersive__nav">
            <button
              type="button"
              class="immersive__arrow"
              aria-label="Ver recorridos anteriores"
              :disabled="!canPrev"
              @click="prevPage"
            >
              ‹
            </button>
            <button
              type="button"
              class="immersive__arrow"
              aria-label="Ver recorridos siguientes"
              :disabled="!canNext"
              @click="nextPage"
            >
              ›
            </button>
          </div>
          <router-link to="/recorridos-360" class="ma-btn ma-btn--outline immersive__all">
            Ver todos
          </router-link>
        </div>
      </div>

      <div v-if="home.loading && !tours.length" class="immersive__status">
        Cargando recorridos…
      </div>
      <div v-else-if="!tours.length" class="immersive__status">
        Aún no hay recorridos publicados para mostrar.
      </div>
      <div
        v-else
        class="immersive__grid"
        :class="{
          'immersive__grid--single': resolvedPageSize === 1,
          'immersive__grid--two': resolvedPageSize === 2,
        }"
        :key="`${resolvedPageSize}-${startIndex}`"
      >
        <router-link
          v-for="tour in visibleTours"
          :key="tour.id"
          :to="`/recorridos-360/${tour.slug}`"
          class="tour-card"
        >
          <div class="tour-card__media" :style="{ backgroundImage: `url(${tour.coverImage})` }">
            <span class="ma-badge tour-card__badge">Recorrido 360°</span>
          </div>
          <div class="tour-card__meta">
            <p v-if="tour.category || tour.year" class="tour-card__cat">
              <template v-if="tour.category">{{ tour.category }}</template>
              <template v-if="tour.category && tour.year"> · </template>
              <template v-if="tour.year">{{ tour.year }}</template>
            </p>
            <h3>{{ tour.title }}</h3>
            <p v-if="tour.location" class="tour-card__loc">{{ tour.location }}</p>
          </div>
        </router-link>
      </div>
    </div>
  </section>
</template>

<style scoped lang="scss">
.immersive {
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
    animation: immersive-fade 0.28s ease;

    &--two {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    &--single {
      grid-template-columns: minmax(0, 1fr);
    }
  }

  &__status {
    color: rgba(247, 244, 240, 0.65);
    padding: 2rem 0;
  }
}

.tour-card {
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
    color: rgba(247, 244, 240, 0.55);
  }
}

@keyframes immersive-fade {
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
  .immersive {
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
