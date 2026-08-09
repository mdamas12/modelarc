<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
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

const heroImage = computed(() => {
  const byOrder = teams.value.find((t) => t.order === 1 && t.url)
  return byOrder?.url || teams.value[0]?.url || FALLBACK_HERO
})

const showMission = computed(() => hasRichText(weAre.value?.mission))
const showVision = computed(() => hasRichText(weAre.value?.vision))
const showValues = computed(() => hasRichText(weAre.value?.values))
const showPillars = computed(() => showMission.value || showVision.value || showValues.value)
const sliderImages = computed(() => teams.value.filter((t) => t.url))

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
  }
})
</script>

<template>
  <q-page>
    <section
      class="about-hero"
      :style="{ backgroundImage: `url('${heroImage}')` }"
    >
      <div class="about-hero__overlay" />
      <div class="ma-container about-hero__content">
        <p class="ma-eyebrow">Nosotros</p>
        <h1 class="ma-heading ma-heading--lg">Arquitectura con propósito</h1>
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

    <section v-if="sliderImages.length" class="about-gallery">
      <div class="ma-container about-gallery__header">
        <p class="ma-eyebrow">Equipo</p>
        <h2 class="ma-heading">Imágenes sobre Nosotros</h2>
        <div class="ma-divider" />
      </div>

      <div class="about-gallery__stage">
        <q-carousel
          v-model="slide"
          animated
          infinite
          swipeable
          navigation
          arrows
          transition-prev="fade"
          transition-next="fade"
          :autoplay="5000"
          control-color="primary"
          class="about-carousel"
          height="min(72vh, 620px)"
        >
          <q-carousel-slide
            v-for="(image, index) in sliderImages"
            :key="image.id"
            :name="index"
            class="about-carousel__slide"
          >
            <div
              class="about-carousel__image"
              :style="{ backgroundImage: `url('${image.url}')` }"
              :aria-label="image.title || `Imagen ${index + 1}`"
              role="img"
            />
            <div v-if="image.title" class="about-carousel__caption">
              {{ image.title }}
            </div>
          </q-carousel-slide>
        </q-carousel>
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
  background-position: center 18%;
  background-repeat: no-repeat;
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

@media (max-width: 800px) {
  .about-hero {
    min-height: 52vh;
    background-position: center 12%;
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
  padding-block: var(--ma-section-pad) 0;

  &__header {
    margin-bottom: 2.5rem;
    max-width: 36rem;
  }

  &__stage {
    width: 100%;
  }
}

.about-carousel {
  background: transparent;

  :deep(.q-carousel__control) {
    color: var(--ma-cream);
    text-shadow: 0 1px 8px rgba(0, 0, 0, 0.45);
  }

  :deep(.q-carousel__navigation .q-btn) {
    opacity: 0.55;
  }

  :deep(.q-carousel__navigation .q-btn--active),
  :deep(.q-carousel__navigation .q-btn:hover) {
    opacity: 1;
    color: var(--ma-gold);
  }

  :deep(.q-carousel__prev-arrow),
  :deep(.q-carousel__next-arrow) {
    background: rgba(17, 17, 17, 0.35);
    backdrop-filter: blur(4px);
    border-radius: 999px;
    margin: 0 1rem;
  }

  &__slide {
    padding: 0;
    overflow: hidden;
  }

  &__image {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    transform: scale(1.02);
    animation: about-kenburns 12s ease-in-out infinite alternate;
  }

  &__caption {
    position: absolute;
    left: 1.5rem;
    bottom: 1.5rem;
    padding: 0.55rem 0.9rem;
    background: rgba(17, 17, 17, 0.55);
    color: var(--ma-cream);
    font-size: 0.85rem;
    letter-spacing: 0.04em;
    backdrop-filter: blur(6px);
  }
}

@keyframes about-kenburns {
  from {
    transform: scale(1.02);
  }
  to {
    transform: scale(1.08);
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

  .about-carousel {
    height: 48vh !important;
  }
}
</style>
