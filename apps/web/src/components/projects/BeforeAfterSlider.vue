<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
  defineProps<{
    beforeImage: string;
    afterImage: string;
    beforeLabel?: string;
    afterLabel?: string;
    title?: string;
    description?: string;
    showActions?: boolean;
    allowFullscreen?: boolean;
  }>(),
  {
    showActions: true,
    allowFullscreen: true,
  },
);

const container = ref<HTMLElement | null>(null);
const fsContainer = ref<HTMLElement | null>(null);
const position = ref(50);
const dragging = ref(false);
const fullscreen = ref(false);
const dragMoved = ref(false);
const pointerStartX = ref(0);

const clipStyle = computed(() => ({
  clipPath: `inset(0 ${100 - position.value}% 0 0)`,
}));

function activeContainer() {
  return fullscreen.value ? fsContainer.value : container.value;
}

function setFromClientX(clientX: number) {
  const el = activeContainer();
  if (!el) return;
  const rect = el.getBoundingClientRect();
  const x = Math.min(Math.max(clientX - rect.left, 0), rect.width);
  position.value = (x / rect.width) * 100;
}

function onPointerDown(e: PointerEvent) {
  dragging.value = true;
  dragMoved.value = false;
  pointerStartX.value = e.clientX;
  (e.target as HTMLElement).setPointerCapture?.(e.pointerId);
  setFromClientX(e.clientX);
}

function onPointerMove(e: PointerEvent) {
  if (!dragging.value) return;
  if (Math.abs(e.clientX - pointerStartX.value) > 4) dragMoved.value = true;
  setFromClientX(e.clientX);
}

function onPointerUp() {
  dragging.value = false;
}

function onKey(e: KeyboardEvent) {
  if (e.key === 'ArrowLeft') position.value = Math.max(0, position.value - 2);
  if (e.key === 'ArrowRight') position.value = Math.min(100, position.value + 2);
  if (e.key === 'Escape' && fullscreen.value) closeFullscreen();
}

function revealBefore() {
  position.value = 100;
}

function revealAfter() {
  position.value = 0;
}

function openFullscreen() {
  if (!props.allowFullscreen) return;
  fullscreen.value = true;
}

function closeFullscreen() {
  fullscreen.value = false;
}

function onSliderClick() {
  // Avoid opening fullscreen when the user was dragging the handle.
  if (dragMoved.value || dragging.value) return;
  openFullscreen();
}

watch(fullscreen, (open) => {
  document.body.style.overflow = open ? 'hidden' : '';
});

onMounted(() => {
  window.addEventListener('pointerup', onPointerUp);
  window.addEventListener('keydown', onKey);
});

onUnmounted(() => {
  window.removeEventListener('pointerup', onPointerUp);
  window.removeEventListener('keydown', onKey);
  document.body.style.overflow = '';
});
</script>

<template>
  <article class="ba-card">
    <div
      ref="container"
      class="ba-slider"
      :class="{ 'ba-slider--clickable': allowFullscreen }"
      @pointermove="onPointerMove"
      @click="onSliderClick"
    >
      <div
        class="ba-slider__layer ba-slider__after"
        :style="{ backgroundImage: `url(${afterImage})` }"
      >
        <span class="ba-slider__label ba-slider__label--right">
          {{ afterLabel || 'Después' }}
        </span>
      </div>
      <div
        class="ba-slider__layer ba-slider__before"
        :style="[{ backgroundImage: `url(${beforeImage})` }, clipStyle]"
      >
        <span class="ba-slider__label ba-slider__label--left">
          {{ beforeLabel || 'Antes' }}
        </span>
      </div>
      <div
        class="ba-slider__handle"
        :style="{ left: `${position}%` }"
        role="slider"
        tabindex="0"
        :aria-valuenow="Math.round(position)"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-label="Comparar antes y después"
        @pointerdown.stop="onPointerDown"
        @click.stop
        @keydown="onKey"
      >
        <span class="ba-slider__line" />
        <span class="ba-slider__knob" aria-hidden="true">
          <span class="ba-slider__dots" />
        </span>
      </div>

      <button
        v-if="allowFullscreen"
        type="button"
        class="ba-slider__fs-btn"
        aria-label="Ver en pantalla completa"
        @click.stop="openFullscreen"
      >
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
          <path
            fill="currentColor"
            d="M7 14H5v5h5v-2H7v-3zm0-4h2V7h3V5H5v5h2zm10 7h-3v2h5v-5h-2v3zm0-12h-3v2h3v3h2V5h-2z"
          />
        </svg>
      </button>
    </div>

    <div v-if="showActions" class="ba-card__actions">
      <button type="button" class="ba-card__btn" @click="revealBefore">
        Ver antes
      </button>
      <button type="button" class="ba-card__btn" @click="revealAfter">
        Ver {{ (afterLabel || 'después').toLowerCase() }}
      </button>
    </div>

    <div v-if="title || description" class="ba-card__meta">
      <div class="ba-card__meta-row">
        <h3 v-if="title" class="ba-card__title">{{ title }}</h3>
        <button
          v-if="allowFullscreen"
          type="button"
          class="ba-card__expand"
          @click="openFullscreen"
        >
          Pantalla completa
        </button>
      </div>
      <p v-if="description" class="ba-card__desc">{{ description }}</p>
    </div>

    <Teleport to="body">
      <div
        v-if="fullscreen"
        class="ba-fs"
        role="dialog"
        aria-modal="true"
        :aria-label="title || 'Antes y después'"
        @click.self="closeFullscreen"
      >
        <div class="ba-fs__bar">
          <div class="ba-fs__info">
            <p class="ba-fs__title">{{ title || 'Antes / Después' }}</p>
            <p v-if="description" class="ba-fs__desc">{{ description }}</p>
          </div>
          <button type="button" class="ba-fs__close" aria-label="Cerrar" @click="closeFullscreen">
            <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
              <path
                fill="currentColor"
                d="M18.3 5.71 12 12.01l-6.3-6.3-1.4 1.42 6.29 6.29-6.3 6.3 1.42 1.4 6.29-6.29 6.3 6.3 1.4-1.42-6.29-6.29 6.3-6.3z"
              />
            </svg>
          </button>
        </div>

        <div class="ba-fs__stage">
          <div
            ref="fsContainer"
            class="ba-slider ba-slider--fullscreen"
            @pointermove="onPointerMove"
          >
            <div
              class="ba-slider__layer ba-slider__after"
              :style="{ backgroundImage: `url(${afterImage})` }"
            >
              <span class="ba-slider__label ba-slider__label--right">
                {{ afterLabel || 'Después' }}
              </span>
            </div>
            <div
              class="ba-slider__layer ba-slider__before"
              :style="[{ backgroundImage: `url(${beforeImage})` }, clipStyle]"
            >
              <span class="ba-slider__label ba-slider__label--left">
                {{ beforeLabel || 'Antes' }}
              </span>
            </div>
            <div
              class="ba-slider__handle"
              :style="{ left: `${position}%` }"
              role="slider"
              tabindex="0"
              :aria-valuenow="Math.round(position)"
              aria-valuemin="0"
              aria-valuemax="100"
              aria-label="Comparar antes y después"
              @pointerdown="onPointerDown"
              @keydown="onKey"
            >
              <span class="ba-slider__line" />
              <span class="ba-slider__knob" aria-hidden="true">
                <span class="ba-slider__dots" />
              </span>
            </div>
          </div>

          <div class="ba-fs__actions">
            <button type="button" class="ba-card__btn" @click="revealBefore">Ver antes</button>
            <button type="button" class="ba-card__btn" @click="revealAfter">
              Ver {{ (afterLabel || 'después').toLowerCase() }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </article>
</template>

<style scoped lang="scss">
.ba-card {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.ba-slider {
  position: relative;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  border-radius: 10px;
  user-select: none;
  touch-action: none;
  background: var(--ma-charcoal);

  &--clickable {
    cursor: zoom-in;
  }

  &--fullscreen {
    width: min(1200px, 100%);
    aspect-ratio: 16 / 10;
    max-height: min(72vh, 720px);
    border-radius: 12px;
    cursor: ew-resize;
  }

  &__layer {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
  }

  &__label {
    position: absolute;
    bottom: 0.9rem;
    padding: 0.35rem 0.7rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    background: rgba(17, 17, 17, 0.72);
    color: var(--ma-cream);
  }

  &__label--left {
    left: 0.9rem;
  }

  &__label--right {
    right: 0.9rem;
  }

  &__handle {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 2px;
    transform: translateX(-50%);
    cursor: ew-resize;
    z-index: 2;
  }

  &__line {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.9);
  }

  &__knob {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.35);
    background: rgba(20, 20, 20, 0.92);
    transform: translate(-50%, -50%);
    display: grid;
    place-items: center;
  }

  &__dots {
    width: 0.7rem;
    height: 0.9rem;
    background-image: radial-gradient(circle, #fff 1.1px, transparent 1.2px);
    background-size: 0.35rem 0.35rem;
    opacity: 0.85;
  }

  &__fs-btn {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    z-index: 3;
    width: 2.25rem;
    height: 2.25rem;
    border: 0;
    border-radius: 8px;
    display: grid;
    place-items: center;
    background: rgba(17, 17, 17, 0.72);
    color: #f7f4f0;
    cursor: pointer;
    transition: background 0.15s ease;

    &:hover {
      background: rgba(17, 17, 17, 0.9);
    }
  }
}

.ba-card__actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}

.ba-card__btn {
  appearance: none;
  border: 1px solid rgba(26, 26, 26, 0.18);
  background: #1a1a1a;
  color: #f7f4f0;
  border-radius: 6px;
  min-height: 2.6rem;
  padding: 0.55rem 0.85rem;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease;

  &:hover {
    background: #2a2a2a;
    border-color: rgba(196, 164, 124, 0.55);
  }
}

.ba-card__meta {
  padding: 0.15rem 0.1rem 0;
}

.ba-card__meta-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.75rem;
}

.ba-card__title {
  margin: 0;
  font-family: var(--ma-font-display, Georgia, 'Times New Roman', serif);
  font-size: 1.35rem;
  font-weight: 500;
  color: var(--ma-charcoal, #1a1a1a);
}

.ba-card__expand {
  appearance: none;
  border: 0;
  background: transparent;
  color: #8a6a3d;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  cursor: pointer;
  white-space: nowrap;
  padding: 0;

  &:hover {
    color: #1a1a1a;
  }
}

.ba-card__desc {
  margin: 0.35rem 0 0;
  font-size: 0.92rem;
  line-height: 1.55;
  color: #5c5752;
}

.ba-fs {
  position: fixed;
  inset: 0;
  z-index: 9000;
  background: rgba(10, 10, 10, 0.94);
  display: flex;
  flex-direction: column;
  color: #f7f4f0;
}

.ba-fs__bar {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.ba-fs__title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 600;
}

.ba-fs__desc {
  margin: 0.3rem 0 0;
  font-size: 0.88rem;
  color: rgba(247, 244, 240, 0.7);
}

.ba-fs__close {
  appearance: none;
  border: 0;
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 999px;
  display: grid;
  place-items: center;
  cursor: pointer;
  flex-shrink: 0;

  &:hover {
    background: rgba(255, 255, 255, 0.16);
  }
}

.ba-fs__stage {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1.25rem;
}

.ba-fs__actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
  width: min(1200px, 100%);
}

.ba-fs__actions .ba-card__btn {
  border-color: rgba(255, 255, 255, 0.18);
  background: rgba(255, 255, 255, 0.08);

  &:hover {
    background: rgba(255, 255, 255, 0.14);
    border-color: rgba(196, 164, 124, 0.55);
  }
}

@media (max-width: 700px) {
  .ba-slider--fullscreen {
    max-height: 58vh;
  }

  .ba-card__meta-row {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
