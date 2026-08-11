<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import { useHomeStore } from '@/stores/homeStore';

const home = useHomeStore();

const pageSize = ref(3);
const page = ref(0);

const testimonials = computed(() => home.testimonials);

const pageCount = computed(() =>
  Math.max(1, Math.ceil(testimonials.value.length / pageSize.value)),
);

const visibleTestimonials = computed(() => {
  const size = pageSize.value;
  const start = page.value * size;
  return testimonials.value.slice(start, start + size);
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
  <section class="ma-section ma-section--charcoal testimonials">
    <div class="ma-container">
      <SectionHeader
        eyebrow="Clientes"
        title="Lo que dicen de nosotros"
        align="center"
        dark
      />

      <div v-if="pageCount > 1" class="testimonials__nav">
        <button
          type="button"
          class="testimonials__arrow"
          aria-label="Ver testimonios anteriores"
          :disabled="!canPrev"
          @click="prevPage"
        >
          ‹
        </button>
        <button
          type="button"
          class="testimonials__arrow"
          aria-label="Ver testimonios siguientes"
          :disabled="!canNext"
          @click="nextPage"
        >
          ›
        </button>
      </div>

      <div v-if="home.loading && !testimonials.length" class="testimonials__status">
        Cargando testimonios…
      </div>
      <div
        v-else
        class="testimonials__grid"
        :class="{ 'testimonials__grid--single': pageSize === 1 }"
        :key="`${pageSize}-${page}`"
      >
        <blockquote
          v-for="item in visibleTestimonials"
          :key="item.id"
          class="testimonials__card"
        >
          <p class="testimonials__quote">“{{ item.quote }}”</p>
          <footer>
            <strong>{{ item.name }}</strong>
            <span>{{ item.role }}</span>
          </footer>
        </blockquote>
      </div>
    </div>
  </section>
</template>

<style scoped lang="scss">
.testimonials {
  &__nav {
    display: flex;
    justify-content: center;
    gap: 0.45rem;
    margin: -1.5rem 0 2rem;
  }

  &__arrow {
    width: 2.35rem;
    height: 2.35rem;
    border: 1px solid rgba(247, 244, 240, 0.25);
    border-radius: 999px;
    background: transparent;
    color: var(--ma-cream);
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
      border-color: rgba(247, 244, 240, 0.45);
    }

    &:disabled {
      opacity: 0.35;
      cursor: default;
    }
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    animation: testimonials-fade 0.28s ease;

    &--single {
      grid-template-columns: minmax(0, 1fr);
    }
  }

  &__card {
    margin: 0;
    padding: 2rem 1.75rem;
    border-top: 1px solid rgba(196, 164, 124, 0.45);
    background: rgba(255, 255, 255, 0.02);
  }

  &__quote {
    font-family: var(--ma-font-serif);
    font-size: 1.35rem;
    line-height: 1.45;
    color: var(--ma-cream);
    margin: 0 0 1.75rem;
    font-style: normal;
    font-weight: 400;
  }

  &__status {
    color: rgba(247, 244, 240, 0.65);
    text-align: center;
    padding: 1rem 0 2rem;
  }

  footer {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;

    strong {
      font-family: var(--ma-font-sans);
      font-size: 0.85rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      color: var(--ma-gold);
    }

    span {
      font-size: 0.8rem;
      color: rgba(247, 244, 240, 0.55);
    }
  }
}

@keyframes testimonials-fade {
  from {
    opacity: 0;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 800px) {
  .testimonials {
    &__nav {
      margin-top: -0.5rem;
    }

    &__arrow {
      width: 2.1rem;
      height: 2.1rem;
      font-size: 1.3rem;
    }
  }
}
</style>
