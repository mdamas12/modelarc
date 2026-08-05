<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps<{
  beforeImage: string
  afterImage: string
  beforeLabel?: string
  afterLabel?: string
}>()

const container = ref<HTMLElement | null>(null)
const position = ref(50)
const dragging = ref(false)

const clipStyle = computed(() => ({
  clipPath: `inset(0 ${100 - position.value}% 0 0)`,
}))

function setFromClientX(clientX: number) {
  if (!container.value) return
  const rect = container.value.getBoundingClientRect()
  const x = Math.min(Math.max(clientX - rect.left, 0), rect.width)
  position.value = (x / rect.width) * 100
}

function onPointerDown(e: PointerEvent) {
  dragging.value = true
  ;(e.target as HTMLElement).setPointerCapture?.(e.pointerId)
  setFromClientX(e.clientX)
}

function onPointerMove(e: PointerEvent) {
  if (!dragging.value) return
  setFromClientX(e.clientX)
}

function onPointerUp() {
  dragging.value = false
}

function revealBefore() {
  position.value = 100
}

function revealAfter() {
  position.value = 0
}

onMounted(() => {
  window.addEventListener('pointerup', onPointerUp)
})

onUnmounted(() => {
  window.removeEventListener('pointerup', onPointerUp)
})

defineExpose({ revealBefore, revealAfter, position })
</script>

<template>
  <div class="admin-ba">
    <div ref="container" class="admin-ba__slider" @pointermove="onPointerMove">
      <div
        class="admin-ba__layer admin-ba__after"
        :style="{ backgroundImage: `url(${afterImage})` }"
      >
        <span class="admin-ba__label">{{ afterLabel || 'Después' }}</span>
      </div>
      <div
        class="admin-ba__layer admin-ba__before"
        :style="[{ backgroundImage: `url(${beforeImage})` }, clipStyle]"
      >
        <span class="admin-ba__label">{{ beforeLabel || 'Antes' }}</span>
      </div>
      <div
        class="admin-ba__handle"
        :style="{ left: `${position}%` }"
        role="slider"
        tabindex="0"
        :aria-valuenow="Math.round(position)"
        aria-valuemin="0"
        aria-valuemax="100"
        @pointerdown="onPointerDown"
      >
        <span class="admin-ba__line" />
        <span class="admin-ba__knob">
          <q-icon name="drag_indicator" size="16px" />
        </span>
      </div>
    </div>
    <div class="admin-ba__buttons">
      <q-btn outline no-caps color="white" icon="search" label="Ver antes" @click="revealBefore" />
      <q-btn outline no-caps color="white" icon="search" label="Ver después" @click="revealAfter" />
    </div>
  </div>
</template>

<style scoped lang="scss">
.admin-ba {
  width: min(1100px, 100%);
}

.admin-ba__slider {
  position: relative;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  border-radius: 12px;
  user-select: none;
  touch-action: none;
  background: #1a1a1a;
}

.admin-ba__layer {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
}

.admin-ba__label {
  position: absolute;
  bottom: 1rem;
  padding: 0.35rem 0.7rem;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  background: rgba(17, 17, 17, 0.75);
  color: #f7f4f0;
}

.admin-ba__before .admin-ba__label {
  left: 1rem;
}

.admin-ba__after .admin-ba__label {
  right: 1rem;
}

.admin-ba__handle {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2px;
  transform: translateX(-50%);
  cursor: ew-resize;
  z-index: 2;
}

.admin-ba__line {
  position: absolute;
  inset: 0;
  background: #c4a47c;
}

.admin-ba__knob {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 2.4rem;
  height: 2.4rem;
  border-radius: 50%;
  border: 2px solid #c4a47c;
  background: #1a1a1a;
  color: #c4a47c;
  display: grid;
  place-items: center;
  transform: translate(-50%, -50%);
}

.admin-ba__buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-top: 1rem;
}
</style>
