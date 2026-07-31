<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
  beforeImage: string;
  afterImage: string;
  beforeLabel?: string;
  afterLabel?: string;
}>();

const container = ref<HTMLElement | null>(null);
const position = ref(50);
const dragging = ref(false);

const clipStyle = computed(() => ({
  clipPath: `inset(0 ${100 - position.value}% 0 0)`,
}));

function setFromClientX(clientX: number) {
  if (!container.value) return;
  const rect = container.value.getBoundingClientRect();
  const x = Math.min(Math.max(clientX - rect.left, 0), rect.width);
  position.value = (x / rect.width) * 100;
}

function onPointerDown(e: PointerEvent) {
  dragging.value = true;
  (e.target as HTMLElement).setPointerCapture?.(e.pointerId);
  setFromClientX(e.clientX);
}

function onPointerMove(e: PointerEvent) {
  if (!dragging.value) return;
  setFromClientX(e.clientX);
}

function onPointerUp() {
  dragging.value = false;
}

function onKey(e: KeyboardEvent) {
  if (e.key === 'ArrowLeft') position.value = Math.max(0, position.value - 2);
  if (e.key === 'ArrowRight') position.value = Math.min(100, position.value + 2);
}

onMounted(() => {
  window.addEventListener('pointerup', onPointerUp);
});

onUnmounted(() => {
  window.removeEventListener('pointerup', onPointerUp);
});
</script>

<template>
  <div
    ref="container"
    class="ba-slider"
    @pointermove="onPointerMove"
  >
    <div
      class="ba-slider__layer ba-slider__after"
      :style="{ backgroundImage: `url(${afterImage})` }"
    >
      <span class="ba-slider__label">{{ afterLabel || 'Después' }}</span>
    </div>
    <div
      class="ba-slider__layer ba-slider__before"
      :style="[{ backgroundImage: `url(${beforeImage})` }, clipStyle]"
    >
      <span class="ba-slider__label">{{ beforeLabel || 'Antes' }}</span>
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
      <span class="ba-slider__knob" />
    </div>
  </div>
</template>

<style scoped lang="scss">
.ba-slider {
  position: relative;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  user-select: none;
  touch-action: none;
  background: var(--ma-charcoal);

  &__layer {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
  }

  &__label {
    position: absolute;
    top: 1rem;
    padding: 0.35rem 0.7rem;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    background: rgba(17, 17, 17, 0.7);
    color: var(--ma-cream);
  }

  &__before .ba-slider__label {
    left: 1rem;
  }

  &__after .ba-slider__label {
    right: 1rem;
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
    background: var(--ma-gold);
  }

  &__knob {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    border: 2px solid var(--ma-gold);
    background: var(--ma-charcoal);
    transform: translate(-50%, -50%);
  }
}
</style>
