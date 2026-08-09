<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Quiénes somos</h1>
        <p class="page-subtitle">Contenido de la sección Nosotros y fotos del equipo</p>
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
            <q-input v-model="form.title" outlined label="Título *" />
          </div>
          <div class="col-12">
            <div class="editor-field">
              <label class="editor-field__label">Descripción</label>
              <q-editor
                v-model="form.description"
                class="html-editor"
                min-height="160px"
                :toolbar="htmlToolbar"
              />
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="editor-field">
              <label class="editor-field__label">Visión</label>
              <q-editor
                v-model="form.vision"
                class="html-editor"
                min-height="180px"
                :toolbar="htmlToolbar"
              />
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="editor-field">
              <label class="editor-field__label">Misión</label>
              <q-editor
                v-model="form.mission"
                class="html-editor"
                min-height="180px"
                :toolbar="htmlToolbar"
              />
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="editor-field">
              <label class="editor-field__label">Valores</label>
              <q-editor
                v-model="form.values"
                class="html-editor"
                min-height="180px"
                :toolbar="htmlToolbar"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="admin-card q-pa-md">
        <div class="team-header">
          <h2 class="section-title">Imágenes sobre Nosotros</h2>
          <div v-if="uploadingCount" class="text-caption text-grey-6">
            Subiendo {{ uploadingCount }}…
          </div>
        </div>

        <div class="team-strip" @dragover.prevent>
          <article
            v-for="(item, index) in teams"
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
              :alt="item.title || `Imagen ${index + 1}`"
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
                @click.stop="removeTeam(item.id)"
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
                :disabled="index === teams.length - 1 || reordering"
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
            @click="teamImageInput?.click()"
          >
            <q-spinner v-if="uploadingCount" color="primary" size="28px" />
            <template v-else>
              <q-icon name="add_photo_alternate" size="32px" color="primary" />
              <span>Agregar</span>
            </template>
          </button>

          <input
            ref="teamImageInput"
            type="file"
            accept="image/*"
            multiple
            hidden
            @change="onTeamFilesSelected"
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
import type { WeAreTeam } from '@/types'

const $q = useQuasar()
const loading = ref(false)
const savingInfo = ref(false)
const uploadingCount = ref(0)
const reordering = ref(false)
const busyId = ref<number | null>(null)
const teams = ref<WeAreTeam[]>([])
const teamImageInput = ref<HTMLInputElement | null>(null)
const dragFrom = ref<number | null>(null)
const dragOver = ref<number | null>(null)

const form = reactive({
  title: '',
  description: '',
  vision: '',
  mission: '',
  values: '',
})

const htmlToolbar = [
  ['left', 'center', 'right', 'justify'],
  ['bold', 'italic', 'underline', 'strike'],
  ['unordered', 'ordered'],
  ['outdent', 'indent'],
  ['undo', 'redo'],
  ['removeFormat'],
]

function normalizeHtml(value: string): string | null {
  const text = value
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, ' ')
    .trim()
  return text ? value : null
}

async function load() {
  loading.value = true
  try {
    const payload = await adminApi.weAre()
    form.title = payload.we_are.title || ''
    form.description = payload.we_are.description || ''
    form.vision = payload.we_are.vision || ''
    form.mission = payload.we_are.mission || ''
    form.values = payload.we_are.values || ''
    teams.value = [...(payload.teams || [])].sort((a, b) => a.order - b.order)
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cargar Quiénes somos' })
  } finally {
    loading.value = false
  }
}

async function saveInfo() {
  if (!form.title.trim()) {
    $q.notify({ type: 'warning', message: 'El título es requerido' })
    return
  }
  savingInfo.value = true
  try {
    await adminApi.updateWeAre({
      title: form.title.trim(),
      description: normalizeHtml(form.description),
      vision: normalizeHtml(form.vision),
      mission: normalizeHtml(form.mission),
      values: normalizeHtml(form.values),
    })
    $q.notify({ type: 'positive', message: 'Información guardada' })
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar la información' })
  } finally {
    savingInfo.value = false
  }
}

async function onTeamFilesSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files || []).filter((f) => f.type.startsWith('image/'))
  input.value = ''
  if (!files.length) {
    $q.notify({ type: 'warning', message: 'Selecciona una imagen válida (JPG, PNG o WEBP)' })
    return
  }

  uploadingCount.value = files.length
  let ok = 0
  let lastError = ''
  try {
    for (const file of files) {
      const data = new FormData()
      data.append('image', file)
      data.append('published', '1')
      data.append('order', String(teams.value.length + 1))
      try {
        const created = await adminApi.createWeAreTeam(data)
        teams.value = [...teams.value, created]
        ok += 1
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
    if (lastError) {
      $q.notify({ type: 'negative', message: lastError })
      if (!ok) await load()
    }
  } finally {
    uploadingCount.value = 0
  }
}

async function persistOrder(next: WeAreTeam[]) {
  teams.value = next
  reordering.value = true
  try {
    await adminApi.reorderWeAreTeam(next.map((t) => t.id))
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo reordenar' })
    await load()
  } finally {
    reordering.value = false
  }
}

async function moveItem(index: number, dir: number) {
  const target = index + dir
  if (target < 0 || target >= teams.value.length) return
  const copy = [...teams.value]
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
  const copy = [...teams.value]
  const [row] = copy.splice(from, 1)
  if (!row) return
  copy.splice(index, 0, row)
  await persistOrder(copy)
}

function onDragEnd() {
  dragFrom.value = null
  dragOver.value = null
}

async function togglePublished(item: WeAreTeam) {
  busyId.value = item.id
  try {
    const data = new FormData()
    data.append('published', item.published ? '0' : '1')
    data.append('order', String(item.order))
    if (item.title) data.append('title', item.title)
    const updated = await adminApi.updateWeAreTeam(item.id, data)
    teams.value = teams.value.map((row) => (row.id === item.id ? updated : row))
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo actualizar el estado' })
  } finally {
    busyId.value = null
  }
}

async function removeTeam(id: number) {
  $q.dialog({
    title: 'Eliminar',
    message: '¿Eliminar esta imagen?',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    busyId.value = id
    try {
      await adminApi.deleteWeAreTeam(id)
      teams.value = teams.value.filter((t) => t.id !== id)
      if (teams.value.length) {
        await adminApi.reorderWeAreTeam(teams.value.map((t) => t.id)).catch(() => undefined)
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

.editor-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.editor-field__label {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  color: #666;
}

.html-editor {
  border: 1px solid var(--ma-border);
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
}

.html-editor :deep(.q-editor__toolbar) {
  background: #f7f4f0;
  border-bottom: 1px solid var(--ma-border);
}

.html-editor :deep(.q-editor__content) {
  font-size: 0.92rem;
  line-height: 1.55;
  color: #222;
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
