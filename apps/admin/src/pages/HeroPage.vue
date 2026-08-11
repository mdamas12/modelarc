<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Hero</h1>
        <p class="page-subtitle">Textos y galería de imágenes del hero principal</p>
      </div>
      <q-btn
        color="primary"
        unelevated
        no-caps
        icon="save"
        label="Guardar información"
        :loading="savingInfo"
        :disable="loading"
        @click="saveInfo"
      />
    </div>

    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="42px" />
    </div>

    <template v-else>
      <div class="admin-card q-pa-md q-mb-md">
        <h2 class="section-title">Información</h2>
        <div class="row q-col-gutter-md">
          <div class="col-12">
            <q-input v-model="form.text_1" outlined label="Texto 1" />
          </div>
          <div class="col-12">
            <q-input
              v-model="form.text_2"
              outlined
              type="textarea"
              label="Texto 2"
              autogrow
              :input-style="{ minHeight: '100px' }"
            />
          </div>
          <div class="col-12">
            <q-input
              v-model="form.text_3"
              outlined
              type="textarea"
              label="Texto 3"
              autogrow
              :input-style="{ minHeight: '100px' }"
            />
          </div>
        </div>
      </div>

      <div class="admin-card q-pa-md">
        <div class="team-header">
          <div>
            <h2 class="section-title">Imágenes del Hero</h2>
            <p class="section-hint">Máximo 4 imágenes publicadas</p>
          </div>
          <div v-if="uploadingCount" class="text-caption text-grey-6">
            Subiendo {{ uploadingCount }}…
          </div>
        </div>

        <div class="team-strip" @dragover.prevent>
          <article
            v-for="(item, index) in galleries"
            :key="item.id"
            class="team-thumb"
            :class="{
              'team-thumb--dragging': dragFrom === index,
              'team-thumb--over': dragOver === index && dragFrom !== index,
              'team-thumb--unpublished': !item.published,
            }"
            draggable="true"
            @dragstart="onDragStart(index, $event)"
            @dragenter.prevent="onDragEnter(index)"
            @dragend="onDragEnd"
            @drop.prevent="onDrop(index)"
          >
            <img
              v-if="item.url"
              :src="item.url"
              :alt="`Imagen ${index + 1}`"
              class="team-thumb__img"
              draggable="false"
            />
            <div v-else class="team-thumb__empty">
              <q-icon name="image" size="28px" />
            </div>

            <div class="team-thumb__order">{{ index + 1 }}</div>

            <div class="team-thumb__actions">
              <button
                type="button"
                class="team-thumb__btn"
                :title="item.published ? 'Ocultar en la web' : 'Publicar en la web'"
                :disabled="busyId === item.id"
                @click.stop="togglePublished(item)"
              >
                <q-icon :name="item.published ? 'visibility' : 'visibility_off'" size="16px" />
              </button>
              <button
                type="button"
                class="team-thumb__btn team-thumb__btn--danger"
                title="Eliminar"
                :disabled="busyId === item.id"
                @click.stop="removeGallery(item.id)"
              >
                <q-icon name="close" size="16px" />
              </button>
            </div>

            <div class="team-thumb__move">
              <button
                type="button"
                class="team-thumb__btn"
                title="Mover a la izquierda"
                :disabled="index === 0 || reordering"
                @click.stop="moveItem(index, -1)"
              >
                <q-icon name="chevron_left" size="18px" />
              </button>
              <button
                type="button"
                class="team-thumb__btn"
                title="Mover a la derecha"
                :disabled="index === galleries.length - 1 || reordering"
                @click.stop="moveItem(index, 1)"
              >
                <q-icon name="chevron_right" size="18px" />
              </button>
            </div>
          </article>

          <button
            type="button"
            class="team-thumb team-thumb--add"
            :disabled="uploadingCount > 0"
            @click="galleryImageInput?.click()"
          >
            <q-spinner v-if="uploadingCount" color="primary" size="28px" />
            <template v-else>
              <q-icon name="add_photo_alternate" size="32px" color="primary" />
              <span>Agregar</span>
            </template>
          </button>

          <input
            ref="galleryImageInput"
            type="file"
            accept="image/*"
            multiple
            hidden
            @change="onGalleryFilesSelected"
          />
        </div>
      </div>
    </template>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { HeroGallery } from '@/types'

const MAX_PUBLISHED = 4

const $q = useQuasar()
const loading = ref(false)
const savingInfo = ref(false)
const uploadingCount = ref(0)
const reordering = ref(false)
const busyId = ref<number | null>(null)
const galleries = ref<HeroGallery[]>([])
const galleryImageInput = ref<HTMLInputElement | null>(null)
const dragFrom = ref<number | null>(null)
const dragOver = ref<number | null>(null)

const form = reactive({
  text_1: '',
  text_2: '',
  text_3: '',
})

function normalizePlain(value: string): string | null {
  const text = value.trim()
  return text || null
}

function publishedCount(): number {
  return galleries.value.filter((g) => g.published).length
}

async function load() {
  loading.value = true
  try {
    const payload = await adminApi.hero()
    form.text_1 = payload.hero.text_1 || ''
    form.text_2 = payload.hero.text_2 || ''
    form.text_3 = payload.hero.text_3 || ''
    galleries.value = [...(payload.galleries || [])].sort((a, b) => a.order - b.order)
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cargar el Hero' })
  } finally {
    loading.value = false
  }
}

async function saveInfo() {
  savingInfo.value = true
  try {
    await adminApi.updateHero({
      text_1: normalizePlain(form.text_1),
      text_2: normalizePlain(form.text_2),
      text_3: normalizePlain(form.text_3),
    })
    $q.notify({ type: 'positive', message: 'Información guardada' })
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar la información' })
  } finally {
    savingInfo.value = false
  }
}

async function onGalleryFilesSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files || []).filter((f) => f.type.startsWith('image/'))
  input.value = ''
  if (!files.length) {
    $q.notify({ type: 'warning', message: 'Selecciona una imagen válida (JPG, PNG o WEBP)' })
    return
  }

  uploadingCount.value = files.length
  let ok = 0
  let skippedLimit = 0
  let lastError = ''
  try {
    for (const file of files) {
      const canPublish = publishedCount() < MAX_PUBLISHED
      const data = new FormData()
      data.append('image', file)
      data.append('published', canPublish ? '1' : '0')
      data.append('order', String(galleries.value.length + 1))
      try {
        const created = await adminApi.createHeroGallery(data)
        galleries.value = [...galleries.value, created]
        ok += 1
        if (!canPublish) skippedLimit += 1
      } catch (err: unknown) {
        const axiosErr = err as {
          response?: { data?: { message?: string; errors?: Record<string, string[]> } }
        }
        const errors = axiosErr.response?.data?.errors
        lastError =
          (errors && Object.values(errors).flat()[0]) ||
          axiosErr.response?.data?.message ||
          `No se pudo subir ${file.name}`
      }
      uploadingCount.value = files.length - ok - (lastError ? 1 : 0)
    }
    if (ok) {
      $q.notify({
        type: 'positive',
        message: ok === 1 ? 'Imagen agregada' : `${ok} imágenes agregadas`,
      })
    }
    if (skippedLimit) {
      $q.notify({
        type: 'warning',
        message: `Ya hay ${MAX_PUBLISHED} imágenes publicadas. ${skippedLimit} imagen(es) se agregaron sin publicar.`,
      })
    }
    if (lastError) {
      $q.notify({ type: 'negative', message: lastError })
      if (!ok) await load()
    }
  } finally {
    uploadingCount.value = 0
  }
}

async function persistOrder(next: HeroGallery[]) {
  galleries.value = next
  reordering.value = true
  try {
    await adminApi.reorderHeroGallery(next.map((t) => t.id))
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo reordenar' })
    await load()
  } finally {
    reordering.value = false
  }
}

async function moveItem(index: number, dir: number) {
  const target = index + dir
  if (target < 0 || target >= galleries.value.length) return
  const copy = [...galleries.value]
  const [row] = copy.splice(index, 1)
  if (!row) return
  copy.splice(target, 0, row)
  await persistOrder(copy)
}

function onDragStart(index: number, event: DragEvent) {
  dragFrom.value = index
  dragOver.value = index
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/plain', String(index))
  }
}

function onDragEnter(index: number) {
  if (dragFrom.value === null) return
  dragOver.value = index
}

async function onDrop(index: number) {
  const from = dragFrom.value
  dragFrom.value = null
  dragOver.value = null
  if (from === null || from === index) return
  const copy = [...galleries.value]
  const [row] = copy.splice(from, 1)
  if (!row) return
  copy.splice(index, 0, row)
  await persistOrder(copy)
}

function onDragEnd() {
  dragFrom.value = null
  dragOver.value = null
}

async function togglePublished(item: HeroGallery) {
  if (!item.published && publishedCount() >= MAX_PUBLISHED) {
    $q.notify({
      type: 'warning',
      message: `Ya hay ${MAX_PUBLISHED} imágenes publicadas. Oculta alguna antes de publicar otra.`,
    })
    return
  }
  busyId.value = item.id
  try {
    const data = new FormData()
    data.append('published', item.published ? '0' : '1')
    data.append('order', String(item.order))
    const updated = await adminApi.updateHeroGallery(item.id, data)
    galleries.value = galleries.value.map((row) => (row.id === item.id ? updated : row))
  } catch (err: unknown) {
    const axiosErr = err as {
      response?: { data?: { message?: string; errors?: Record<string, string[]> } }
    }
    const errors = axiosErr.response?.data?.errors
    const message =
      (errors && Object.values(errors).flat()[0]) ||
      axiosErr.response?.data?.message ||
      'No se pudo actualizar el estado'
    $q.notify({ type: 'negative', message })
  } finally {
    busyId.value = null
  }
}

async function removeGallery(id: number) {
  $q.dialog({
    title: 'Eliminar',
    message: '¿Eliminar esta imagen?',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    busyId.value = id
    try {
      await adminApi.deleteHeroGallery(id)
      galleries.value = galleries.value.filter((t) => t.id !== id)
      if (galleries.value.length) {
        await adminApi.reorderHeroGallery(galleries.value.map((t) => t.id)).catch(() => undefined)
      }
      $q.notify({ type: 'positive', message: 'Imagen eliminada' })
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar' })
    } finally {
      busyId.value = null
    }
  })
}

onMounted(load)
</script>

<style scoped lang="scss">
.section-title {
  margin: 0 0 0.35rem;
  font-size: 1.05rem;
  font-weight: 700;
  color: #1a1a1a;
}

.section-hint {
  margin: 0;
  font-size: 0.8rem;
  color: #777;
}

.team-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.team-strip {
  display: flex;
  align-items: stretch;
  gap: 0.75rem;
  overflow-x: auto;
  padding: 0.25rem 0.15rem 0.5rem;
  scrollbar-width: thin;
}

.team-thumb {
  position: relative;
  flex: 0 0 140px;
  width: 140px;
  height: 160px;
  border-radius: 12px;
  border: 1px solid var(--ma-border);
  background: #f7f4f0;
  overflow: hidden;
  cursor: grab;
  user-select: none;
  transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
}

.team-thumb:active {
  cursor: grabbing;
}

.team-thumb--dragging {
  opacity: 0.45;
}

.team-thumb--over {
  border-color: var(--q-primary);
  box-shadow: 0 0 0 2px rgba(166, 137, 102, 0.25);
}

.team-thumb--unpublished {
  opacity: 0.72;
}

.team-thumb__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  pointer-events: none;
}

.team-thumb__empty {
  height: 100%;
  display: grid;
  place-items: center;
  color: #aaa;
}

.team-thumb__order {
  position: absolute;
  top: 8px;
  left: 8px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: rgba(26, 26, 26, 0.72);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  display: grid;
  place-items: center;
}

.team-thumb__actions {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.12s ease;
}

.team-thumb:hover .team-thumb__actions,
.team-thumb:focus-within .team-thumb__actions {
  opacity: 1;
}

.team-thumb__move {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  justify-content: space-between;
  padding: 6px;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.45));
  opacity: 0;
  transition: opacity 0.12s ease;
}

.team-thumb:hover .team-thumb__move,
.team-thumb:focus-within .team-thumb__move {
  opacity: 1;
}

.team-thumb__btn {
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.92);
  color: #333;
  display: grid;
  place-items: center;
  cursor: pointer;
}

.team-thumb__btn:disabled {
  opacity: 0.45;
  cursor: default;
}

.team-thumb__btn--danger {
  color: #c10015;
}

.team-thumb--add {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  cursor: pointer;
  border-style: dashed;
  border-color: #d2c4b2;
  background: #faf8f5;
  color: #666;
  font-size: 0.78rem;
  font-weight: 600;
}

.team-thumb--add:hover:not(:disabled) {
  border-color: var(--q-primary);
  background: rgba(166, 137, 102, 0.08);
}

.team-thumb--add:disabled {
  cursor: wait;
  opacity: 0.75;
}

@media (max-width: 600px) {
  .team-header {
    flex-direction: column;
  }

  .team-thumb,
  .team-thumb--add {
    flex-basis: 120px;
    width: 120px;
    height: 140px;
  }
}
</style>
