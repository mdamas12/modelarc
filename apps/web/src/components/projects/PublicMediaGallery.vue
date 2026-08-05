<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import {
  PROJECT_CATEGORIES,
  allSubcategories,
  labelSubcategory,
  subcategoriesFor,
} from '@/constants/mediaTaxonomy';
import { fetchPublicGallery, type GalleryImage } from '@/services/galleryApi';

const INITIAL_VISIBLE = 4;

const loading = ref(false);
const items = ref<GalleryImage[]>([]);
const category = ref('');
const subcategory = ref('');
const expanded = ref(false);
const lightboxOpen = ref(false);
const lightboxIndex = ref(0);

const categoryOptions = [
  { label: 'Todas las categorías', value: '' },
  ...PROJECT_CATEGORIES.map((c) => ({ label: c.label, value: c.value })),
];

const subcategoryOptions = computed(() => {
  const list = category.value ? subcategoriesFor(category.value) : allSubcategories();
  return [{ label: 'Todas las subcategorías', value: '' }, ...list];
});

const filteredItems = computed(() =>
  items.value.filter((item) => {
    if (category.value && item.category !== category.value) return false;
    if (subcategory.value && item.subcategory !== subcategory.value) return false;
    return true;
  }),
);

const visibleItems = computed(() =>
  expanded.value ? filteredItems.value : filteredItems.value.slice(0, INITIAL_VISIBLE),
);

const hasMore = computed(() => filteredItems.value.length > INITIAL_VISIBLE);

const activeImage = computed(() => filteredItems.value[lightboxIndex.value] || null);

async function load() {
  loading.value = true;
  try {
    items.value = await fetchPublicGallery({ perPage: 120 });
  } catch {
    items.value = [];
  } finally {
    loading.value = false;
  }
}

function onCategoryChange() {
  subcategory.value = '';
}

function openLightbox(index: number) {
  lightboxIndex.value = index;
  lightboxOpen.value = true;
}

function prevImage() {
  if (!filteredItems.value.length) return;
  lightboxIndex.value =
    (lightboxIndex.value - 1 + filteredItems.value.length) % filteredItems.value.length;
}

function nextImage() {
  if (!filteredItems.value.length) return;
  lightboxIndex.value = (lightboxIndex.value + 1) % filteredItems.value.length;
}

watch([category, subcategory], () => {
  expanded.value = false;
  lightboxIndex.value = 0;
});

onMounted(load);
</script>

<template>
  <section class="media-gallery">
    <div class="media-gallery__head">
      <div>
        <p class="media-gallery__eyebrow">Galería</p>
        <h2 class="media-gallery__title">Galería de medios</h2>
        <p class="media-gallery__lead">
          Explora ambientes y detalles de nuestros proyectos.
        </p>
      </div>

      <div class="media-gallery__filters">
        <label class="media-gallery__field">
          <span>Categoría</span>
          <select v-model="category" @change="onCategoryChange">
            <option
              v-for="opt in categoryOptions"
              :key="opt.value || 'all'"
              :value="opt.value"
            >
              {{ opt.label }}
            </option>
          </select>
        </label>
        <label class="media-gallery__field">
          <span>Subcategoría</span>
          <select v-model="subcategory">
            <option
              v-for="opt in subcategoryOptions"
              :key="opt.value || 'all-sub'"
              :value="opt.value"
            >
              {{ opt.label }}
            </option>
          </select>
        </label>
      </div>
    </div>

    <div v-if="loading" class="media-gallery__status">Cargando galería…</div>
    <div v-else-if="!filteredItems.length" class="media-gallery__status">
      No hay imágenes con este filtro.
    </div>
    <template v-else>
      <div class="media-gallery__grid">
        <button
          v-for="(item, i) in visibleItems"
          :key="item.id"
          type="button"
          class="media-gallery__item"
          :style="{ backgroundImage: `url(${item.thumbUrl})` }"
          :aria-label="item.alt"
          @click="openLightbox(i)"
        >
          <span v-if="item.subcategory" class="media-gallery__tag">
            {{ labelSubcategory(item.category, item.subcategory) }}
          </span>
        </button>
      </div>
      <div v-if="hasMore && !expanded" class="media-gallery__more">
        <button type="button" class="media-gallery__more-btn" @click="expanded = true">
          Ver más
        </button>
      </div>
    </template>

    <Teleport to="body">
      <div
        v-if="lightboxOpen && activeImage"
        class="media-lightbox"
        role="dialog"
        aria-modal="true"
        @click.self="lightboxOpen = false"
      >
        <button
          type="button"
          class="media-lightbox__close"
          aria-label="Cerrar"
          @click="lightboxOpen = false"
        >
          ×
        </button>
        <button
          type="button"
          class="media-lightbox__nav media-lightbox__nav--prev"
          aria-label="Anterior"
          @click="prevImage"
        >
          ‹
        </button>
        <img :src="activeImage.url" :alt="activeImage.alt" class="media-lightbox__img" />
        <button
          type="button"
          class="media-lightbox__nav media-lightbox__nav--next"
          aria-label="Siguiente"
          @click="nextImage"
        >
          ›
        </button>
        <p class="media-lightbox__count">
          {{ lightboxIndex + 1 }} / {{ filteredItems.length }}
        </p>
      </div>
    </Teleport>
  </section>
</template>

<style scoped lang="scss">
.media-gallery {
  &__head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.25rem;
    margin-bottom: 1.75rem;
  }

  &__eyebrow {
    margin: 0 0 0.35rem;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--ma-gold);
  }

  &__title {
    margin: 0;
    font-family: var(--ma-font-display, Georgia, serif);
    font-size: clamp(1.6rem, 2.5vw, 2.1rem);
    font-weight: 500;
    color: var(--ma-charcoal);
  }

  &__lead {
    margin: 0.45rem 0 0;
    color: var(--ma-muted);
    max-width: 36rem;
  }

  &__filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  &__field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    min-width: 12rem;

    span {
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #8a8580;
    }

    select {
      appearance: none;
      min-height: 2.65rem;
      padding: 0.65rem 2.2rem 0.65rem 0.9rem;
      border: 1px solid rgba(26, 26, 26, 0.18);
      background:
        linear-gradient(45deg, transparent 50%, #666 50%) calc(100% - 16px) calc(1rem + 2px) / 5px 5px no-repeat,
        linear-gradient(135deg, #666 50%, transparent 50%) calc(100% - 11px) calc(1rem + 2px) / 5px 5px no-repeat,
        var(--ma-white);
      font-family: var(--ma-font-sans);
      font-size: 0.9rem;
      color: var(--ma-charcoal);
      outline: none;
      cursor: pointer;

      &:focus {
        border-color: var(--ma-gold);
      }
    }
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
  }

  &__item {
    position: relative;
    aspect-ratio: 1;
    border: 0;
    padding: 0;
    background-size: cover;
    background-position: center;
    background-color: #1a1a1a;
    cursor: zoom-in;
  }

  &__tag {
    position: absolute;
    left: 0.55rem;
    bottom: 0.55rem;
    padding: 0.2rem 0.45rem;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: rgba(17, 17, 17, 0.72);
    color: #f7f4f0;
  }

  &__status {
    text-align: center;
    color: var(--ma-muted);
    padding: 2.5rem 0;
  }

  &__more {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
  }

  &__more-btn {
    min-width: 10rem;
    padding: 0.75rem 1.5rem;
    border: 1px solid rgba(26, 26, 26, 0.22);
    background: transparent;
    font-family: var(--ma-font-sans);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--ma-charcoal);
    cursor: pointer;
    transition:
      background 0.2s ease,
      border-color 0.2s ease,
      color 0.2s ease;

    &:hover {
      border-color: var(--ma-gold);
      color: var(--ma-gold);
    }
  }
}

.media-lightbox {
  position: fixed;
  inset: 0;
  z-index: 9000;
  background: rgba(10, 10, 10, 0.94);
  display: grid;
  place-items: center;
}

.media-lightbox__img {
  max-width: min(1100px, 92vw);
  max-height: 82vh;
  object-fit: contain;
}

.media-lightbox__close,
.media-lightbox__nav {
  position: absolute;
  border: 0;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  cursor: pointer;
  display: grid;
  place-items: center;
}

.media-lightbox__close {
  top: 1rem;
  right: 1rem;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 999px;
  font-size: 1.5rem;
}

.media-lightbox__nav {
  top: 50%;
  transform: translateY(-50%);
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 999px;
  font-size: 1.8rem;
  line-height: 1;

  &--prev {
    left: 1rem;
  }

  &--next {
    right: 1rem;
  }
}

.media-lightbox__count {
  position: absolute;
  bottom: 1.25rem;
  left: 50%;
  transform: translateX(-50%);
  margin: 0;
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.85rem;
}

@media (max-width: 1100px) {
  .media-gallery__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 700px) {
  .media-gallery__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .media-gallery__field {
    min-width: 100%;
  }

  .media-lightbox__nav {
    display: none;
  }
}
</style>
