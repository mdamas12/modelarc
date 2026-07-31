<template>
  <q-page class="page-wrap" style="max-width: none">
    <div class="page-header">
      <div>
        <h1 class="page-title">Editor de recorrido</h1>
        <p class="page-subtitle">{{ tour?.name || 'Cargando...' }}</p>
      </div>
      <div class="row q-gutter-sm">
        <q-btn flat no-caps icon="arrow_back" label="Volver" to="/recorridos" />
        <q-btn
          color="primary"
          unelevated
          no-caps
          icon="add_location_alt"
          label="Agregar punto interactivo"
          :disable="!currentScene"
          @click="captureHotspot"
        />
        <q-btn outline color="primary" no-caps icon="add" label="Escena" @click="showAddScene = true" />
      </div>
    </div>

    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="48px" />
    </div>

    <div v-else class="tour-editor">
      <!-- Left: scenes -->
      <div class="tour-panel">
        <div class="tour-panel-header">Escenas</div>
        <q-list separator class="col">
          <q-item
            v-for="scene in scenes"
            :key="scene.id"
            clickable
            v-ripple
            :active="currentScene?.id === scene.id"
            active-class="bg-primary text-white"
            @click="selectScene(scene)"
          >
            <q-item-section>
              <q-item-label>{{ scene.name }}</q-item-label>
              <q-item-label caption :class="currentScene?.id === scene.id ? 'text-white' : ''">
                {{ scene.hotspots?.length || 0 }}
                {{ (scene.hotspots?.length || 0) === 1 ? 'punto interactivo' : 'puntos interactivos' }}
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-btn
                flat
                dense
                round
                size="sm"
                :icon="tour?.initial_scene_id === scene.id ? 'star' : 'star_border'"
                :color="
                  currentScene?.id === scene.id
                    ? 'white'
                    : tour?.initial_scene_id === scene.id
                      ? 'primary'
                      : 'grey-5'
                "
                :aria-label="'Marcar como escena inicial'"
                @click.stop="setInitialScene(scene)"
              >
                <q-tooltip>
                  {{
                    tour?.initial_scene_id === scene.id
                      ? 'Escena inicial'
                      : 'Usar como escena inicial'
                  }}
                </q-tooltip>
              </q-btn>
            </q-item-section>
          </q-item>
          <q-item v-if="!scenes.length">
            <q-item-section class="text-grey-6">Sin escenas. Agrega una para comenzar.</q-item-section>
          </q-item>
        </q-list>
      </div>

      <!-- Center: PSV -->
      <div class="tour-panel">
        <div class="tour-panel-header row items-center justify-between">
          <span>{{ currentScene?.name || 'Visor 360°' }}</span>
          <span v-if="currentPose" class="text-caption text-grey-6">
            yaw {{ currentPose.yaw.toFixed(2) }} · pitch {{ currentPose.pitch.toFixed(2) }}
          </span>
        </div>
        <div ref="viewerEl" class="psv-container" />
      </div>

      <!-- Right: hotspot form -->
      <div class="tour-panel">
        <div class="tour-panel-header">
          {{ editingHotspotId ? 'Editar punto interactivo' : 'Configurar punto interactivo' }}
        </div>
        <div class="q-pa-md col overflow-auto">
          <q-banner v-if="!draft.active" dense rounded class="bg-grey-2 q-mb-md text-caption">
            Usa «Agregar punto interactivo» para capturar la posición actual del visor.
          </q-banner>

          <q-form class="q-gutter-sm" @submit.prevent="saveHotspot">
            <q-input v-model="draft.title" outlined dense label="Título" :disable="!draft.active" />
            <q-select
              v-model="draft.type"
              outlined
              dense
              emit-value
              map-options
              label="Tipo"
              :options="hotspotTypes"
              :disable="!draft.active"
            />
            <q-input
              v-model.number="draft.yaw"
              outlined
              dense
              type="number"
              step="0.01"
              label="Yaw"
              :disable="!draft.active"
            />
            <q-input
              v-model.number="draft.pitch"
              outlined
              dense
              type="number"
              step="0.01"
              label="Pitch"
              :disable="!draft.active"
            />
            <q-select
              v-if="draft.type === 'scene'"
              v-model="draft.target_scene_id"
              outlined
              dense
              emit-value
              map-options
              clearable
              label="Escena destino"
              :options="sceneOptions"
              :disable="!draft.active"
            />
            <q-input
              v-if="draft.type === 'link'"
              v-model="draft.external_url"
              outlined
              dense
              label="URL externa"
              :disable="!draft.active"
            />
            <q-input
              v-model="draft.description"
              outlined
              dense
              type="textarea"
              label="Descripción"
              autogrow
              :disable="!draft.active"
            />
            <q-input
              v-if="showHotspotIconField"
              v-model="draft.icon"
              outlined
              dense
              label="Icono"
              :disable="!draft.active"
            />

            <div class="row q-gutter-sm q-mt-sm">
              <q-btn
                type="submit"
                color="primary"
                unelevated
                no-caps
                dense
                label="Guardar punto interactivo"
                :loading="saving"
                :disable="!draft.active || !currentScene"
              />
              <q-btn
                v-if="editingHotspotId"
                flat
                dense
                no-caps
                color="negative"
                label="Eliminar"
                @click="deleteHotspot"
              />
              <q-btn flat dense no-caps label="Limpiar" :disable="!draft.active" @click="resetDraft" />
            </div>
          </q-form>

          <q-separator class="q-my-md" />
          <div class="text-caption text-weight-medium q-mb-sm">Puntos interactivos de la escena</div>
          <q-list dense bordered separator class="rounded-borders">
            <q-item
              v-for="hs in currentScene?.hotspots || []"
              :key="hs.id"
              clickable
              v-ripple
              @click="editHotspot(hs)"
            >
              <q-item-section>
                <q-item-label>{{ hs.title || `Punto #${hs.id}` }}</q-item-label>
                <q-item-label caption>{{ hs.type }} · {{ hs.yaw?.toFixed?.(1) }}, {{ hs.pitch?.toFixed?.(1) }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="!(currentScene?.hotspots || []).length">
              <q-item-section class="text-grey-6">Ninguno aún</q-item-section>
            </q-item>
          </q-list>
        </div>
      </div>
    </div>

    <q-dialog v-model="showAddScene" persistent @show="onAddSceneShow">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Nueva escena</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="sceneForm.name" outlined label="Nombre *" />

          <div>
            <div class="text-subtitle2 q-mb-xs">Imagen panorámica</div>
            <div
              v-if="panoramaPreviewUrl"
              class="panorama-preview q-mb-sm"
            >
              <img :src="panoramaPreviewUrl" alt="Vista previa panorama" />
              <q-btn
                class="panorama-preview__clear"
                dense
                flat
                round
                icon="close"
                color="white"
                size="sm"
                :disable="uploadingPanorama || creatingScene"
                @click="clearPanorama"
              />
            </div>
            <div class="row items-center q-gutter-sm">
              <q-btn
                outline
                no-caps
                color="primary"
                icon="cloud_upload"
                :label="sceneForm.panorama_media_id ? 'Cambiar imagen' : 'Subir panorama'"
                :loading="uploadingPanorama"
                :disable="creatingScene"
                @click="pickPanorama"
              />
              <span v-if="panoramaFileName" class="text-body2 text-grey-7 ellipsis" style="max-width: 180px">
                {{ panoramaFileName }}
              </span>
            </div>
            <div class="text-caption text-grey-6 q-mt-xs">
              Sube una imagen equirectangular 360°. Si no hay panorama, se usa una imagen de demostración.
            </div>
            <input
              ref="panoramaInput"
              type="file"
              accept="image/*"
              hidden
              @change="onPanoramaFile"
            />
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" :disable="uploadingPanorama || creatingScene" v-close-popup />
          <q-btn
            color="primary"
            unelevated
            no-caps
            label="Crear"
            :loading="creatingScene"
            :disable="uploadingPanorama"
            @click="addScene"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { Viewer } from '@photo-sphere-viewer/core'
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin'
import '@photo-sphere-viewer/core/index.css'
import '@photo-sphere-viewer/markers-plugin/index.css'
import { adminApi } from '@/services/adminApi'
import type { TourHotspot, TourScene, VirtualTour } from '@/types'

const PLACEHOLDER_PANORAMA =
  'https://photo-sphere-viewer-data.netlify.app/assets/sphere.jpg'

const route = useRoute()
const $q = useQuasar()

const loading = ref(true)
const saving = ref(false)
const creatingScene = ref(false)
const uploadingPanorama = ref(false)
const showAddScene = ref(false)
const tour = ref<VirtualTour | null>(null)
const scenes = ref<TourScene[]>([])
const currentScene = ref<TourScene | null>(null)
const editingHotspotId = ref<number | null>(null)
const viewerEl = ref<HTMLElement | null>(null)
const currentPose = ref<{ yaw: number; pitch: number } | null>(null)
const panoramaInput = ref<HTMLInputElement | null>(null)
const panoramaPreviewUrl = ref<string | null>(null)
const panoramaFileName = ref('')

let viewer: Viewer | null = null
let markersPlugin: MarkersPlugin | null = null
let poseInterval: ReturnType<typeof setInterval> | null = null

const hotspotTypes = [
  { label: 'Escena', value: 'scene' },
  { label: 'Info', value: 'info' },
  { label: 'Media', value: 'media' },
  { label: 'Enlace', value: 'link' },
]

const showHotspotIconField = false

const draft = reactive({
  active: false,
  title: '',
  type: 'info' as string,
  yaw: 0,
  pitch: 0,
  description: '',
  icon: 'info',
  target_scene_id: null as number | null,
  external_url: '',
})

const sceneForm = reactive({
  name: '',
  panorama_media_id: null as number | null,
})

const sceneOptions = computed(() =>
  scenes.value
    .filter((s) => s.id !== currentScene.value?.id)
    .map((s) => ({ label: s.name, value: s.id })),
)

function panoramaUrl(scene: TourScene | null) {
  return scene?.panorama_media?.url || PLACEHOLDER_PANORAMA
}

function resetSceneForm() {
  sceneForm.name = ''
  sceneForm.panorama_media_id = null
  panoramaFileName.value = ''
  if (panoramaPreviewUrl.value?.startsWith('blob:')) {
    URL.revokeObjectURL(panoramaPreviewUrl.value)
  }
  panoramaPreviewUrl.value = null
  if (panoramaInput.value) panoramaInput.value.value = ''
}

function onAddSceneShow() {
  resetSceneForm()
}

function pickPanorama() {
  panoramaInput.value?.click()
}

function clearPanorama() {
  sceneForm.panorama_media_id = null
  panoramaFileName.value = ''
  if (panoramaPreviewUrl.value?.startsWith('blob:')) {
    URL.revokeObjectURL(panoramaPreviewUrl.value)
  }
  panoramaPreviewUrl.value = null
  if (panoramaInput.value) panoramaInput.value.value = ''
}

async function onPanoramaFile(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  if (panoramaPreviewUrl.value?.startsWith('blob:')) {
    URL.revokeObjectURL(panoramaPreviewUrl.value)
  }
  panoramaPreviewUrl.value = URL.createObjectURL(file)
  panoramaFileName.value = file.name

  uploadingPanorama.value = true
  try {
    const media = await adminApi.uploadMedia(file, 'panorama')
    sceneForm.panorama_media_id = media.id
    if (media.url) {
      if (panoramaPreviewUrl.value?.startsWith('blob:')) {
        URL.revokeObjectURL(panoramaPreviewUrl.value)
      }
      panoramaPreviewUrl.value = media.url
    }
    panoramaFileName.value = media.original_name || file.name
    $q.notify({ type: 'positive', message: 'Panorama subido' })
  } catch {
    clearPanorama()
    $q.notify({ type: 'negative', message: 'Error al subir el panorama' })
  } finally {
    uploadingPanorama.value = false
    input.value = ''
  }
}

function destroyViewer() {
  if (poseInterval) {
    clearInterval(poseInterval)
    poseInterval = null
  }
  if (viewer) {
    viewer.destroy()
    viewer = null
    markersPlugin = null
  }
}

function syncMarkers() {
  if (!markersPlugin || !currentScene.value) return
  markersPlugin.clearMarkers()
  for (const hs of currentScene.value.hotspots || []) {
    const label = hs.title || `Punto #${hs.id}`
    markersPlugin.addMarker({
      id: `hs-${hs.id}`,
      position: { yaw: `${hs.yaw}deg`, pitch: `${hs.pitch}deg` },
      html: `<div class="ma-editor-hotspot"><span>${label}</span><i></i></div>`,
      anchor: 'bottom center',
      tooltip: hs.type === 'scene' ? `Ir a: ${label}` : label,
      data: hs,
    })
  }
}

async function initViewer(scene: TourScene) {
  await nextTick()
  if (!viewerEl.value) return
  destroyViewer()

  viewer = new Viewer({
    container: viewerEl.value,
    panorama: panoramaUrl(scene),
    defaultYaw: `${scene.initial_yaw ?? 0}deg`,
    defaultPitch: `${scene.initial_pitch ?? 0}deg`,
    navbar: ['zoom', 'move', 'fullscreen'],
    plugins: [[MarkersPlugin, {}]],
  })

  markersPlugin = viewer.getPlugin(MarkersPlugin) as MarkersPlugin

  markersPlugin.addEventListener('select-marker', ({ marker }) => {
    const hs = marker.data as TourHotspot | undefined
    if (!hs) return

    // Puntos de tipo escena: navegar al destino (como en el sitio público).
    // Para editarlos, usa la lista "Puntos interactivos de la escena".
    if (hs.type === 'scene' && hs.target_scene_id != null) {
      const target = scenes.value.find((s) => s.id === hs.target_scene_id)
      if (target) {
        void selectScene(target)
        return
      }
      $q.notify({ type: 'warning', message: 'La escena destino no está disponible' })
    }

    editHotspot(hs)
  })

  poseInterval = setInterval(() => {
    if (!viewer) return
    const pos = viewer.getPosition()
    currentPose.value = {
      yaw: (pos.yaw * 180) / Math.PI,
      pitch: (pos.pitch * 180) / Math.PI,
    }
  }, 200)

  viewer.addEventListener('ready', () => {
    syncMarkers()
  }, { once: true })
}

async function selectScene(scene: TourScene) {
  currentScene.value = scene
  resetDraft()
  await initViewer(scene)
}

function captureHotspot() {
  if (!viewer || !currentScene.value) return
  const pos = viewer.getPosition()
  draft.active = true
  draft.yaw = Number(((pos.yaw * 180) / Math.PI).toFixed(4))
  draft.pitch = Number(((pos.pitch * 180) / Math.PI).toFixed(4))
  editingHotspotId.value = null
  draft.title = ''
  draft.type = 'info'
  draft.description = ''
  draft.icon = 'info'
  draft.target_scene_id = null
  draft.external_url = ''
  $q.notify({ type: 'info', message: 'Posición capturada. Completa y guarda el punto interactivo.' })
}

function editHotspot(hs: TourHotspot) {
  editingHotspotId.value = hs.id
  draft.active = true
  draft.title = hs.title || ''
  draft.type = hs.type
  draft.yaw = Number(hs.yaw)
  draft.pitch = Number(hs.pitch)
  draft.description = hs.description || ''
  draft.icon = hs.icon || 'info'
  draft.target_scene_id = hs.target_scene_id ?? null
  draft.external_url = hs.external_url || ''
}

function resetDraft() {
  editingHotspotId.value = null
  draft.active = false
  draft.title = ''
  draft.type = 'info'
  draft.yaw = 0
  draft.pitch = 0
  draft.description = ''
  draft.icon = 'info'
  draft.target_scene_id = null
  draft.external_url = ''
}

async function saveHotspot() {
  if (!currentScene.value) return
  saving.value = true
  try {
    const payload = {
      type: draft.type,
      title: draft.title || null,
      description: draft.description || null,
      yaw: draft.yaw,
      pitch: draft.pitch,
      icon: draft.icon || null,
      target_scene_id: draft.type === 'scene' ? draft.target_scene_id : null,
      external_url: draft.type === 'link' ? draft.external_url || null : null,
    }

    if (editingHotspotId.value) {
      await adminApi.updateHotspot(editingHotspotId.value, payload)
      $q.notify({ type: 'positive', message: 'Punto interactivo actualizado' })
    } else {
      await adminApi.createHotspot(currentScene.value.id, payload)
      $q.notify({ type: 'positive', message: 'Punto interactivo creado' })
    }

    await reloadTour(currentScene.value.id)
    resetDraft()
  } catch {
    $q.notify({
      type: 'negative',
      message: 'No se pudo guardar el punto interactivo. La posición quedó capturada en el formulario.',
    })
  } finally {
    saving.value = false
  }
}

async function deleteHotspot() {
  if (!editingHotspotId.value || !currentScene.value) return
  try {
    await adminApi.deleteHotspot(editingHotspotId.value)
    $q.notify({ type: 'positive', message: 'Punto interactivo eliminado' })
    await reloadTour(currentScene.value.id)
    resetDraft()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al eliminar el punto interactivo' })
  }
}

async function addScene() {
  if (!tour.value || !sceneForm.name) {
    $q.notify({ type: 'warning', message: 'Nombre requerido' })
    return
  }
  if (uploadingPanorama.value) {
    $q.notify({ type: 'warning', message: 'Espera a que termine la subida del panorama' })
    return
  }
  creatingScene.value = true
  try {
    const payload: Record<string, unknown> = {
      name: sceneForm.name,
      sort_order: scenes.value.length,
    }
    if (sceneForm.panorama_media_id) {
      payload.panorama_media_id = sceneForm.panorama_media_id
    }
    const scene = await adminApi.createScene(tour.value.id, payload)
    showAddScene.value = false
    resetSceneForm()
    await reloadTour(scene.id)
  } catch {
    // Allow local demo scene if API fails
    const localScene: TourScene = {
      id: Date.now(),
      virtual_tour_id: tour.value!.id,
      name: sceneForm.name,
      hotspots: [],
      panorama_media: null,
    }
    scenes.value.push(localScene)
    showAddScene.value = false
    resetSceneForm()
    await selectScene(localScene)
    $q.notify({
      type: 'warning',
      message: 'Escena local de demo (API no disponible). El visor funciona con panorama placeholder.',
    })
  } finally {
    creatingScene.value = false
  }
}

async function setInitialScene(scene: TourScene) {
  if (!tour.value) return
  if (tour.value.initial_scene_id === scene.id) return
  try {
    const updated = await adminApi.updateTour(tour.value.id, {
      initial_scene_id: scene.id,
    })
    tour.value = { ...tour.value, ...updated, scenes: scenes.value }
    $q.notify({ type: 'positive', message: `"${scene.name}" es ahora la escena inicial` })
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo actualizar la escena inicial' })
  }
}

function pickScene(selectId?: number) {
  return (
    scenes.value.find((s) => s.id === selectId) ||
    scenes.value.find((s) => s.id === tour.value?.initial_scene_id) ||
    scenes.value[0] ||
    null
  )
}

async function reloadTour(selectId?: number) {
  const id = route.params.id as string
  tour.value = await adminApi.tour(id)
  scenes.value = tour.value.scenes || []

  // El visor necesita el contenedor en el DOM; no inicializar mientras loading=true.
  if (loading.value) {
    loading.value = false
    await nextTick()
  }

  const next = pickScene(selectId)
  if (next) {
    await selectScene(next)
  } else {
    currentScene.value = null
    destroyViewer()
  }
}

async function load() {
  loading.value = true
  try {
    await reloadTour()
  } catch {
    tour.value = {
      id: Number(route.params.id) || 1,
      project_id: 1,
      name: 'Tour de demostración',
      slug: 'demo',
      status: 'draft',
      initial_scene_id: 1,
      scenes: [],
    }
    scenes.value = [
      {
        id: 1,
        virtual_tour_id: tour.value.id,
        name: 'Sala principal',
        hotspots: [],
        panorama_media: null,
      },
      {
        id: 2,
        virtual_tour_id: tour.value.id,
        name: 'Terraza',
        hotspots: [],
        panorama_media: null,
      },
    ]
    loading.value = false
    await nextTick()
    await selectScene(scenes.value[0]!)
    $q.notify({
      type: 'info',
      message: 'Modo demo: panorama placeholder. Conecta la API para guardar.',
    })
  } finally {
    loading.value = false
  }
}

watch(
  () => currentScene.value?.hotspots,
  () => {
    if (viewer) syncMarkers()
  },
  { deep: true },
)

onMounted(load)
onBeforeUnmount(() => {
  destroyViewer()
  if (panoramaPreviewUrl.value?.startsWith('blob:')) {
    URL.revokeObjectURL(panoramaPreviewUrl.value)
  }
})
</script>

<style scoped>
.panorama-preview {
  position: relative;
  overflow: hidden;
  border-radius: 8px;
  background: #1a1a1a;
  aspect-ratio: 2 / 1;
}

.panorama-preview img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.panorama-preview__clear {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(0, 0, 0, 0.45);
}

:deep(.ma-editor-hotspot) {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  user-select: none;
}

:deep(.ma-editor-hotspot span) {
  padding: 2px 8px;
  border-radius: 4px;
  background: rgba(26, 26, 26, 0.78);
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.02em;
  white-space: nowrap;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
}

:deep(.ma-editor-hotspot i) {
  display: block;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #c4a47c;
  border: 2px solid #fff;
  box-shadow: 0 0 6px rgba(0, 0, 0, 0.5);
}
</style>
