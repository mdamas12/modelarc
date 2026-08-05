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
const booting = ref(false);
const switching = ref(false);
const activeSceneId = ref<string | null>(null);

let viewer: Viewer | null = null;
let observer: IntersectionObserver | null = null;
let switchToken = 0;
let preloadGen = 0;
let markersPlugin: MarkersPlugin | null = null;
const preloadedUrls = new Set<string>();
const httpWarmed = new Set<string>();

const activeScene = computed(() => {
  if (!activeSceneId.value) return props.scenes[0] ?? null;
  return (
    props.scenes.find((s) => String(s.id) === String(activeSceneId.value)) ??
    props.scenes[0] ??
    null
  );
});

function resolveInitialSceneId() {
  if (props.initialSceneId) {
    const match = props.scenes.find((s) => String(s.id) === String(props.initialSceneId));
    if (match) return String(match.id);
  }
  return props.scenes[0] ? String(props.scenes[0].id) : null;
}

function findScene(sceneId: string | number | undefined | null): TourScene | null {
  if (sceneId == null || sceneId === '') return null;
  return props.scenes.find((s) => String(s.id) === String(sceneId)) ?? null;
}

function buildMarkers(scene: TourScene) {
  return (scene.hotspots ?? [])
    .filter((h) => h.targetSceneId != null && findScene(h.targetSceneId))
    .map((h) => ({
      id: `hotspot-${h.id}`,
      position: { yaw: `${h.yaw}deg`, pitch: `${h.pitch}deg` },
      html: `<div class="ma-hotspot" data-target-scene="${String(h.targetSceneId)}" role="button">${escapeHtml(h.label)}</div>`,
      anchor: 'bottom center' as const,
      tooltip: h.label,
      data: {
        targetSceneId: String(h.targetSceneId),
      },
    }));
}

function escapeHtml(value: string) {
  return value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');
}

function abortPendingLoads() {
  preloadGen += 1;
  try {
    viewer?.textureLoader?.abortLoading?.();
  } catch {
    // ignore
  }
}

function warmHttpCache(url: string) {
  if (!url || httpWarmed.has(url)) return Promise.resolve();
  httpWarmed.add(url);
  return new Promise<void>((resolve) => {
    const img = new Image();
    img.decoding = 'async';
    img.onload = () => resolve();
    img.onerror = () => resolve();
    img.src = url;
  });
}

async function preloadUrl(url: string, gen: number) {
  if (!url || preloadedUrls.has(url)) return;
  if (gen !== preloadGen) return;
  // Nunca precargar en paralelo a un cambio de escena: PSV puede mostrar la textura equivocada.
  if (switching.value || booting.value) return;
  await warmHttpCache(url);
  if (!viewer || gen !== preloadGen || switching.value) return;
  try {
    await viewer.textureLoader.preloadPanorama(url);
    if (gen === preloadGen) preloadedUrls.add(url);
  } catch {
    // La carga bajo demanda sigue disponible.
  }
}

function preloadRelated(scene: TourScene) {
  const gen = preloadGen;
  const urls: string[] = [];
  // Solo destinos de hotspots (evita cargar "la segunda escena" y saturar memoria móvil).
  for (const hotspot of scene.hotspots ?? []) {
    const target = findScene(hotspot.targetSceneId);
    if (target?.panoramaUrl) urls.push(target.panoramaUrl);
  }
  const unique = [...new Set(urls)].filter((u) => u && u !== scene.panoramaUrl);
  void unique.reduce(async (prev, url) => {
    await prev;
    if (gen !== preloadGen) return;
    await preloadUrl(url, gen);
  }, Promise.resolve());
}

function bindMarkerClicks() {
  if (!markersPlugin) return;
  markersPlugin.addEventListener('select-marker', (event) => {
    const marker = event.marker;
    const fromData = marker?.data?.targetSceneId;
    const el = marker?.domElement as HTMLElement | undefined;
    const fromDom =
      el?.getAttribute?.('data-target-scene') ||
      el?.querySelector?.('[data-target-scene]')?.getAttribute('data-target-scene');
    const target = String(fromData ?? fromDom ?? '');
    if (!target) return;
    const scrollY = window.scrollY;
    void switchScene(target).finally(() => {
      if (Math.abs(window.scrollY - scrollY) > 2) {
        window.scrollTo(0, scrollY);
      }
    });
  });
}

function resolveHotspotTarget(event: Event): string | null {
  const el = (event.target as HTMLElement | null)?.closest?.('[data-target-scene]') as
    | HTMLElement
    | null;
  if (!el) return null;
  return el.getAttribute('data-target-scene');
}

function onContainerPointer(event: Event) {
  const target = resolveHotspotTarget(event);
  if (!target) return;
  // Evita que el gesto haga scroll/jump al layout o active enlaces padres.
  event.preventDefault();
  event.stopPropagation();
  if (typeof (event as Event & { stopImmediatePropagation?: () => void }).stopImmediatePropagation === 'function') {
    (event as Event & { stopImmediatePropagation: () => void }).stopImmediatePropagation();
  }
  const scrollY = window.scrollY;
  void switchScene(target).finally(() => {
    if (Math.abs(window.scrollY - scrollY) > 2) {
      window.scrollTo(0, scrollY);
    }
  });
}

async function applyScene(scene: TourScene, opts?: { isBoot?: boolean }) {
  if (!viewer) return;

  const token = ++switchToken;
  abortPendingLoads();
  const alreadyReady = preloadedUrls.has(scene.panoramaUrl);
  let subtleTimer: number | undefined;

  if (!opts?.isBoot) {
    switching.value = true;
    if (!alreadyReady) {
      subtleTimer = window.setTimeout(() => {
        if (token === switchToken) switching.value = true;
      }, 40);
    }
  }

  try {
    await viewer.setPanorama(scene.panoramaUrl, {
      position: {
        yaw: `${scene.yaw ?? 0}deg`,
        pitch: `${scene.pitch ?? 0}deg`,
      },
      showLoader: false,
      transition: {
        speed: alreadyReady ? 280 : 420,
        rotation: true,
        effect: 'fade',
      },
    });

    if (token !== switchToken) return;

    preloadedUrls.add(scene.panoramaUrl);
    markersPlugin?.setMarkers(buildMarkers(scene));
    // Precarga solo después de fijar la escena visible.
    preloadRelated(scene);
  } catch {
    // Si falla (p.ej. memoria en iOS), no dejamos el UI en un id distinto a lo mostrado.
    if (token === switchToken) {
      throw new Error('scene-load-failed');
    }
  } finally {
    if (subtleTimer !== undefined) window.clearTimeout(subtleTimer);
    if (token === switchToken) switching.value = false;
  }
}

async function initViewer() {
  if (viewer || !containerEl.value || !props.scenes.length) return;

  const initialId = resolveInitialSceneId();
  activeSceneId.value = initialId;
  const scene = activeScene.value;
  if (!scene) return;

  booting.value = true;
  started.value = true;
  await nextTick();

  // Calienta HTTP cache de la primera escena antes de montar PSV.
  await warmHttpCache(scene.panoramaUrl);

  if (!containerEl.value) return;

  viewer = new Viewer({
    container: containerEl.value,
    panorama: scene.panoramaUrl,
    defaultYaw: `${scene.yaw ?? 0}deg`,
    defaultPitch: `${scene.pitch ?? 0}deg`,
    navbar: false,
    defaultZoomLvl: 50,
    mousewheel: true,
    touchmoveTwoFingers: false,
    loadingTxt: 'Preparando Experiencia',
    lang: {
      loading: 'Preparando Experiencia',
      twoFingers: 'Desliza para navegar',
      loadError: 'No se pudo cargar la escena',
    },
    defaultTransition: {
      speed: 360,
      rotation: true,
      effect: 'fade',
    },
    plugins: [
      [
        MarkersPlugin,
        {
          clickEventOnMarker: true,
          markers: buildMarkers(scene),
        },
      ],
    ],
  });

  // Fuerza el copy por si PSV mezcla defaults en inglés.
  viewer.config.loadingTxt = 'Preparando Experiencia';
  viewer.config.lang.loading = 'Preparando Experiencia';

  markersPlugin = viewer.getPlugin(MarkersPlugin) as MarkersPlugin;
  bindMarkerClicks();

  // pointerdown + click: captura hotspots en móvil sin dejar que el gesto suba al layout.
  containerEl.value.addEventListener('pointerdown', onContainerPointer, { capture: true });
  containerEl.value.addEventListener('click', onContainerPointer, { capture: true });

  viewer.addEventListener(
    'ready',
    () => {
      booting.value = false;
      preloadedUrls.add(scene.panoramaUrl);
      preloadRelated(scene);
    },
    { once: true },
  );

  window.setTimeout(() => {
    booting.value = false;
    if (viewer) {
      preloadedUrls.add(scene.panoramaUrl);
      preloadRelated(scene);
    }
  }, 4000);
}

async function switchScene(sceneId: string) {
  const scene = findScene(sceneId);
  if (!scene) return;
  if (String(scene.id) === String(activeSceneId.value)) return;
  if (switching.value) return;

  const previousId = activeSceneId.value;
  // Mantener el usuario en el visor: solo cambia escena.
  activeSceneId.value = String(scene.id);
  emit('scene-change', String(scene.id));

  if (!viewer) {
    await initViewer();
    return;
  }

  try {
    await applyScene(scene);
  } catch {
    if (previousId) {
      activeSceneId.value = previousId;
      emit('scene-change', previousId);
    }
  }
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
  switchToken += 1;
  abortPendingLoads();
  preloadedUrls.clear();
  markersPlugin = null;
  if (containerEl.value) {
    containerEl.value.removeEventListener('pointerdown', onContainerPointer, true);
    containerEl.value.removeEventListener('click', onContainerPointer, true);
  }
  if (viewer) {
    viewer.destroy();
    viewer = null;
  }
}

// Solo reacciona a cambios externos de escena inicial (p.ej. thumbs), no remonta el visor.
watch(
  () => props.initialSceneId,
  (id) => {
    if (!id || !started.value || !viewer) return;
    if (String(id) === String(activeSceneId.value)) return;
    void switchScene(String(id));
  },
);

watch(
  () => props.autoplay,
  (val) => {
    if (val && !started.value) void start();
  },
);

onMounted(() => {
  if (!rootEl.value) return;
  observer = new IntersectionObserver(
    (entries) => {
      if (props.autoplay && entries.some((e) => e.isIntersecting) && !started.value) {
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

defineExpose({ switchScene, start });
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
      <button type="button" class="tour-viewer__play" @click.stop.prevent="start">
        <span class="tour-viewer__play-icon">▶</span>
        <span>Iniciar recorrido 360°</span>
      </button>
    </div>

    <TourLoadingScreen
      :visible="started && booting"
      message="Preparando Experiencia"
    />
    <TourLoadingScreen :visible="started && switching && !booting" subtle />

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
  max-width: 100%;
  box-sizing: border-box;
  background: var(--ma-charcoal-deep);
  overflow: hidden;
  border: 1px solid rgba(196, 164, 124, 0.28);
  display: flex;
  flex-direction: column;
  touch-action: none;
  isolation: isolate;

  &__canvas {
    flex: 1;
    min-height: 0;
    width: 100%;
    max-width: 100%;
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
    display: inline-block;
    border: 0;
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
    border-radius: 2px;
    pointer-events: auto;
    user-select: none;
    -webkit-user-select: none;
  }

  :deep(.psv-container) {
    width: 100%;
    height: 100%;
  }

  :deep(.psv-marker) {
    cursor: pointer;
  }

  /* Oculta el loader nativo de PSV (z-index 80 + "Loading..."); usamos TourLoadingScreen. */
  :deep(.psv-loader-container) {
    display: none !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }

  :deep(.psv-loader-text),
  :deep(.psv-loader .psv-loader-text) {
    font-size: 0 !important;
    color: transparent !important;
  }

  :deep(.psv-loader-text::after) {
    content: 'Preparando Experiencia';
    font-size: 0.75rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ma-cream);
  }
}
</style>
