<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import SectionHeader from '@/components/common/SectionHeader.vue'
import {
  fetchAbout,
  hasRichText,
  type WeAreContent,
  type WeAreTeamImage,
} from '@/services/aboutApi'

const FALLBACK_HERO = '/about/team.jpg'

const loading = ref(true)
const error = ref<string | null>(null)
const weAre = ref<WeAreContent | null>(null)
const teams = ref<WeAreTeamImage[]>([])
const slide = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

const heroImage = computed(() => {
  const byOrder = teams.value.find((t) => t.order === 1 && t.url)
  return byOrder?.url || teams.value[0]?.url || FALLBACK_HERO
})

const showMission = computed(() => hasRichText(weAre.value?.mission))
const showVision = computed(() => hasRichText(weAre.value?.vision))
const showValues = computed(() => hasRichText(weAre.value?.values))
const showPillars = computed(() => showMission.value || showVision.value || showValues.value)
const sliderImages = computed(() => teams.value.filter((t) => t.url))
const heroTitle = computed(() => weAre.value?.titulo_hero?.trim() || '')
const heroMessage = computed(() => weAre.value?.mensaje_hero?.trim() || '')
const showHeroCopy = computed(() => Boolean(heroTitle.value || heroMessage.value))

function goTo(index: number) {
  const total = sliderImages.value.length
  if (!total) return
  slide.value = ((index % total) + total) % total
  restartAutoplay()
}

function next() {
  goTo(slide.value + 1)
}

function prev() {
  goTo(slide.value - 1)
}

function stopAutoplay() {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

function startAutoplay() {
  stopAutoplay()
  if (sliderImages.value.length < 2) return
  timer = setInterval(() => {
    slide.value = (slide.value + 1) % sliderImages.value.length
  }, 5500)
}

function restartAutoplay() {
  startAutoplay()
}

watch(sliderImages, () => {
  slide.value = 0
  startAutoplay()
})

onMounted(async () => {
  loading.value = true
  error.value = null
  try {
    const payload = await fetchAbout()
    weAre.value = payload.weAre
    teams.value = payload.teams
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'No se pudo cargar la sección'
    weAre.value = null
    teams.value = []
  } finally {
    loading.value = false
    startAutoplay()
  }
})

onUnmounted(stopAutoplay)
</script>

<template>
  <q-page>
    <section class="about-hero">
      <img
        class="about-hero__media"
        :src="heroImage"
        alt="Equipo Modelarc"
        width="2560"
        height="1440"
        decoding="async"
        fetchpriority="high"
      />
      <div class="about-hero__overlay" />
      <div v-if="showHeroCopy" class="ma-container about-hero__content">
        <p v-if="heroMessage" class="ma-eyebrow">{{ heroMessage }}</p>
        <h1 v-if="heroTitle" class="ma-heading ma-heading--lg">{{ heroTitle }}</h1>
      </div>
    </section>

    <section class="ma-section ma-section--light">
      <div class="ma-container about-intro">
        <div v-if="loading" class="about-status">Cargando…</div>
        <div v-else-if="error" class="about-status">{{ error }}</div>
        <template v-else-if="weAre">
          <SectionHeader
            eyebrow="Modelarc"
            :title="weAre.title"
          />
          <div
            v-if="hasRichText(weAre.description)"
            class="about-intro__body"
            v-html="weAre.description"
          />
        </template>
      </div>
    </section>

    <section
      v-if="sliderImages.length"
      class="about-gallery"
      @mouseenter="stopAutoplay"
      @mouseleave="startAutoplay"
    >
      <div class="ma-container about-gallery__header">
        <h2 class="ma-heading about-gallery__title">Nuestro Equipo</h2>
        <div class="ma-divider" />
      </div>

      <div class="about-gallery__viewport">
        <div class="about-gallery__track">
          <figure
            v-for="(image, index) in sliderImages"
            :key="image.id"
            class="about-gallery__slide"
            :class="{ 'about-gallery__slide--active': index === slide }"
            :aria-hidden="index !== slide"
          >
            <img
              :src="image.url"
              :alt="image.title || `Imagen ${index + 1}`"
              class="about-gallery__img"
              :loading="index === 0 ? 'eager' : 'lazy'"
              decoding="async"
            />
          </figure>
        </div>

        <template v-if="sliderImages.length > 1">
          <button
            type="button"
            class="about-gallery__nav about-gallery__nav--prev"
            aria-label="Anterior"
            @click="prev"
          >
            <q-icon name="chevron_left" size="28px" />
          </button>
          <button
            type="button"
            class="about-gallery__nav about-gallery__nav--next"
            aria-label="Siguiente"
            @click="next"
          >
            <q-icon name="chevron_right" size="28px" />
          </button>

          <div class="about-gallery__dots" role="tablist" aria-label="Imágenes del equipo">
            <button
              v-for="(image, index) in sliderImages"
              :key="`dot-${image.id}`"
              type="button"
              class="about-gallery__dot"
              :class="{ 'about-gallery__dot--active': index === slide }"
              :aria-label="`Ir a imagen ${index + 1}`"
              :aria-selected="index === slide"
              @click="goTo(index)"
            />
          </div>
        </template>
      </div>
    </section>

    <section v-if="showPillars" class="ma-section ma-section--dark">
      <div class="ma-container about-pillars">
        <article v-if="showMission" class="about-pillars__item">
          <p class="ma-eyebrow">Compromiso</p>
          <h3>Nuestra Misión</h3>
          <p class="about-pillars__text">{{ weAre?.mission }}</p>
        </article>
        <article v-if="showVision" class="about-pillars__item">
          <p class="ma-eyebrow">Horizonte</p>
          <h3>Nuestra Visión</h3>
          <p class="about-pillars__text">{{ weAre?.vision }}</p>
        </article>
        <article
          v-if="showValues"
          class="about-pillars__item about-pillars__item--wide"
        >
          <p class="ma-eyebrow">Principios</p>
          <h3>Nuestros Valores</h3>
          <div class="about-pillars__html" v-html="weAre?.values" />
        </article>
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
  min-height: 70vh;
  display: flex;
  align-items: flex-end;
  color: var(--ma-cream);
  overflow: hidden;
  background: #151515;

  &__media {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    display: block;
    z-index: 0;
  }

  /* Keep faces clear: light top, dark band only behind the title */
  &__overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    background: linear-gradient(
      180deg,
      rgba(17, 17, 17, 0.22) 0%,
      rgba(17, 17, 17, 0.08) 45%,
      rgba(17, 17, 17, 0.55) 72%,
      rgba(17, 17, 17, 0.9) 100%
    );
  }

  &__content {
    position: relative;
    z-index: 2;
    padding-top: 2rem;
    padding-bottom: clamp(2.25rem, 5vh, 3.5rem);
    max-width: 48rem;

    .ma-eyebrow {
      text-shadow: 0 1px 10px rgba(0, 0, 0, 0.45);
    }

    .ma-heading {
      text-shadow: 0 2px 18px rgba(0, 0, 0, 0.55);
    }
  }
}

@media (max-width: 800px) {
  .about-hero {
    min-height: 58vh;
  }
}

.about-status {
  color: var(--ma-muted);
  padding: 1rem 0 2rem;
}

.about-intro {
  :deep(.section-header) {
    max-width: 44rem;
  }

  &__body {
    max-width: 52rem;
    color: var(--ma-muted);
    line-height: 1.85;
    font-size: 1.05rem;
    text-align: justify;
    text-justify: inter-word;

    :deep(p) {
      margin: 0 0 1.1rem;
    }

    :deep(p:last-child) {
      margin-bottom: 0;
    }

    :deep(ul),
    :deep(ol) {
      margin: 0 0 1.1rem;
      padding-left: 1.25rem;
    }

    :deep(strong) {
      color: var(--ma-charcoal);
      font-weight: 600;
    }
  }
}

.about-pillars {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 2rem 2.5rem;

  &__item {
    border-top: 1px solid rgba(196, 164, 124, 0.4);
    padding-top: 1.5rem;

    h3 {
      font-family: var(--ma-font-serif);
      font-size: clamp(1.6rem, 2.4vw, 2.1rem);
      color: var(--ma-gold);
      margin: 0.35rem 0 1rem;
      font-weight: 500;
    }
  }

  &__item--wide {
    grid-column: 1 / -1;
  }

  &__text {
    margin: 0;
    color: rgba(247, 244, 240, 0.78);
    line-height: 1.75;
    white-space: pre-line;
  }

  &__html {
    color: rgba(247, 244, 240, 0.78);
    line-height: 1.75;

    :deep(p) {
      margin: 0 0 0.85rem;
    }

    :deep(ul),
    :deep(ol) {
      margin: 0;
      padding-left: 1.2rem;
    }

    :deep(li + li) {
      margin-top: 0.35rem;
    }

    :deep(strong) {
      color: var(--ma-cream);
    }
  }
}

.about-gallery {
  background: var(--ma-cream);
  padding-block: var(--ma-section-pad);

  &__header {
    margin-bottom: 2.25rem;
  }

  &__title {
    margin: 0;
  }

  &__viewport {
    position: relative;
    width: min(1180px, calc(100% - 2.5rem));
    margin-inline: auto;
  }

  &__track {
    position: relative;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #111;
  }

  &__slide {
    position: absolute;
    inset: 0;
    margin: 0;
    opacity: 0;
    transition: opacity 0.85s ease;
    pointer-events: none;
  }

  &__slide--active {
    opacity: 1;
    pointer-events: auto;
    z-index: 1;
  }

  &__img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center center;
    display: block;
    image-rendering: auto;
  }

  &__nav {
    position: absolute;
    top: 50%;
    z-index: 3;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    border: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.92);
    color: var(--ma-charcoal);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.16);
    transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
  }

  &__nav:hover {
    background: var(--ma-gold);
    color: var(--ma-charcoal-deep);
  }

  &__nav--prev {
    left: 1rem;
  }

  &__nav--next {
    right: 1rem;
  }

  &__dots {
    position: absolute;
    left: 50%;
    bottom: 1.1rem;
    z-index: 3;
    transform: translateX(-50%);
    display: flex;
    gap: 0.45rem;
    padding: 0.35rem 0.55rem;
    border-radius: 999px;
    background: rgba(17, 17, 17, 0.28);
    backdrop-filter: blur(6px);
  }

  &__dot {
    width: 7px;
    height: 7px;
    border: 0;
    border-radius: 999px;
    padding: 0;
    background: rgba(255, 255, 255, 0.55);
    cursor: pointer;
    transition: width 0.2s ease, background 0.2s ease;
  }

  &__dot--active {
    width: 22px;
    background: var(--ma-gold);
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
  .about-pillars {
    grid-template-columns: 1fr;
  }

  .about-gallery {
    &__viewport {
      width: 100%;
    }

    &__track {
      aspect-ratio: 4 / 5;
    }

    &__img {
      object-position: center center;
    }

    &__nav {
      width: 38px;
      height: 38px;
    }

    &__nav--prev {
      left: 0.65rem;
    }

    &__nav--next {
      right: 0.65rem;
    }
  }
}
</style>
