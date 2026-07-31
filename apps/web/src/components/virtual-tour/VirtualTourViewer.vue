<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Viewer } from '@photo-sphere-viewer/core';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';
import '@photo-sphere-viewer/core/index.css';
import '@photo-sphere-viewer/markers-plugin/index.css';
import type { TourScene } from '@/types/models';
import TourControls from './TourControls.vue';
import TourLoadingScreen from './TourLoadingScreen.vue';
import TourSceneSelector from './TourSceneSelector.vue';

const props = withDefaults(
  defineProps<{
    scenes: TourScene[];
    initialSceneId?: string;
    autoplay?: boolean;
    coverImage?: string;
    height?: string;
    showSelector?: boolean;
  }>(),
  {
    autoplay: false,
    height: '560px',
    showSelector: true,
  },
);

const emit = defineEmits<{
  'scene-change': [sceneId: string];
}>();

const rootEl = ref<HTMLElement | null>(null);
const containerEl = ref<HTMLElement | null>(null);
const started = ref(false);
const loading = ref(false);
const activeSceneId = ref<string | null>(null);
const visibleEnough = ref(false);

let viewer: Viewer | null = null;
let observer: IntersectionObserver | null = null;

const activeScene = computed(
  () => props.scenes.find((s) => s.id === activeSceneId.value) ?? props.scenes[0] ?? null,
);

function resolveInitialSceneId() {
  if (props.initialSceneId && props.scenes.some((s) => s.id === props.initialSceneId)) {
    return props.initialSceneId;
  }
  return props.scenes[0]?.id ?? null;
}

function buildMarkers(scene: TourScene) {
  return (scene.hotspots ?? []).map((h) => ({
    id: h.id,
    position: { yaw: `${h.yaw}deg`, pitch: `${h.pitch}deg` },
    html: `<div class="ma-hotspot">${h.label}</div>`,
    anchor: 'bottom center',
    tooltip: h.label,
    data: { targetSceneId: h.targetSceneId },
  }));
}

async function applyScene(scene: TourScene) {
  if (!viewer) return;
  loading.value = true;
  try {
    await viewer.setPanorama(scene.panoramaUrl, {
      position: {
        yaw: `${scene.yaw ?? 0}deg`,
        pitch: `${scene.pitch ?? 0}deg`,
      },
      transition: true,
    });
    const markers = viewer.getPlugin(MarkersPlugin) as MarkersPlugin | undefined;
    markers?.setMarkers(buildMarkers(scene));
  } finally {
    loading.value = false;
  }
}

async function initViewer() {
  if (viewer || !containerEl.value || !props.scenes.length) return;

  activeSceneId.value = resolveInitialSceneId();
  const scene = activeScene.value;
  if (!scene) return;

  loading.value = true;
  started.value = true;
  await nextTick();

  viewer = new Viewer({
    container: containerEl.value,
    panorama: scene.panoramaUrl,
    defaultYaw: `${scene.yaw ?? 0}deg`,
    defaultPitch: `${scene.pitch ?? 0}deg`,
    navbar: false,
    defaultZoomLvl: 50,
    mousewheel: true,
    touchmoveTwoFingers: true,
    plugins: [
      [
        MarkersPlugin,
        {
          markers: buildMarkers(scene),
        },
      ],
    ],
  });

  const markers = viewer.getPlugin(MarkersPlugin) as MarkersPlugin | undefined;
  markers?.addEventListener('select-marker', ({ marker }) => {
    const target = marker.data?.targetSceneId as string | undefined;
    if (target) void switchScene(target);
  });

  viewer.addEventListener('ready', () => {
    loading.value = false;
  });

  // Fallback if ready already fired
  window.setTimeout(() => {
    loading.value = false;
  }, 2500);
}

async function switchScene(sceneId: string) {
  if (sceneId === activeSceneId.value) return;
  const scene = props.scenes.find((s) => s.id === sceneId);
  if (!scene) return;
  activeSceneId.value = sceneId;
  emit('scene-change', sceneId);
  if (!viewer) {
    await initViewer();
    return;
  }
  await applyScene(scene);
}

async function start() {
  await initViewer();
}

function onFullscreen() {
  const el = rootEl.value;
  if (!el) return;
  if (document.fullscreenElement) {
    void document.exitFullscreen();
  } else {
    void el.requestFullscreen?.();
  }
}

function onReset() {
  if (!viewer || !activeScene.value) return;
  void viewer.animate({
    yaw: `${activeScene.value.yaw ?? 0}deg`,
    pitch: `${activeScene.value.pitch ?? 0}deg`,
    speed: '3rpm',
  });
}

function destroyViewer() {
  if (viewer) {
    viewer.destroy();
    viewer = null;
  }
}

watch(
  () => props.initialSceneId,
  (id) => {
    if (id && started.value) void switchScene(id);
  },
);

watch(
  () => props.autoplay,
  (val) => {
    if (val && !started.value) void start();
  },
);

watch(
  () => props.scenes,
  () => {
    if (started.value && activeScene.value) {
      void applyScene(activeScene.value);
    }
  },
  { deep: true },
);

onMounted(() => {
  if (!rootEl.value) return;
  observer = new IntersectionObserver(
    (entries) => {
      visibleEnough.value = entries.some((e) => e.isIntersecting);
      if (props.autoplay && visibleEnough.value && !started.value) {
        void start();
      }
    },
    { threshold: 0.35 },
  );
  observer.observe(rootEl.value);

  if (props.autoplay) void start();
});

onBeforeUnmount(() => {
  observer?.disconnect();
  destroyViewer();
});
</script>

<template>
  <div ref="rootEl" class="tour-viewer" :style="{ height }">
    <div v-show="started" ref="containerEl" class="tour-viewer__canvas" />

    <div
      v-if="!started"
      class="tour-viewer__cover"
      :style="coverImage ? { backgroundImage: `url(${coverImage})` } : undefined"
    >
      <div class="tour-viewer__cover-overlay" />
      <button type="button" class="tour-viewer__play" @click="start">
        <span class="tour-viewer__play-icon">▶</span>
        <span>Iniciar recorrido 360°</span>
      </button>
    </div>

    <TourLoadingScreen :visible="started && loading" />
    <TourControls v-if="started" @fullscreen="onFullscreen" @reset="onReset" />

    <TourSceneSelector
      v-if="started && showSelector && scenes.length > 1"
      :scenes="scenes"
      :active-id="activeSceneId"
      @select="switchScene"
    />
  </div>
</template>

<style scoped lang="scss">
.tour-viewer {
  position: relative;
  width: 100%;
  background: var(--ma-charcoal-deep);
  overflow: hidden;
  border: 1px solid rgba(196, 164, 124, 0.28);
  display: flex;
  flex-direction: column;

  &__canvas {
    flex: 1;
    min-height: 0;
    width: 100%;
  }

  &__cover {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    display: grid;
    place-items: center;
    z-index: 2;
  }

  &__cover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(17, 17, 17, 0.55);
  }

  &__play {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border: 1px solid var(--ma-gold);
    background: rgba(17, 17, 17, 0.7);
    color: var(--ma-cream);
    font-family: var(--ma-font-sans);
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;

    &:hover {
      background: var(--ma-gold);
      color: var(--ma-charcoal);
    }
  }

  &__play-icon {
    display: grid;
    place-items: center;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 1px solid currentColor;
    font-size: 0.7rem;
    padding-left: 2px;
  }

  :deep(.ma-hotspot) {
    padding: 0.4rem 0.7rem;
    background: var(--ma-gold);
    color: var(--ma-charcoal-deep);
    font-family: var(--ma-font-sans);
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    white-space: nowrap;
    cursor: pointer;
  }

  :deep(.psv-container) {
    width: 100%;
    height: 100%;
  }
}
</style>
