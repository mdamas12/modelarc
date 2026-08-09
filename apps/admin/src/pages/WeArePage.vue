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
          <div>
            <h2 class="section-title">Equipo</h2>
            <p class="section-hint">Imágenes que se muestran en la página Nosotros</p>
          </div>
          <q-btn
            color="primary"
            unelevated
            no-caps
            icon="add"
            label="Agregar imagen"
            @click="openCreateTeam"
          />
        </div>

        <div v-if="!teams.length" class="text-grey-7 text-center q-pa-xl">
          Aún no hay imágenes del equipo.
        </div>

        <div v-else class="team-list">
          <article v-for="item in teams" :key="item.id" class="team-card">
            <div class="team-card__identity">
              <div class="team-card__thumb">
                <img v-if="item.url" :src="item.url" :alt="item.title || 'Equipo'" />
                <q-icon v-else name="groups" size="28px" color="primary" />
              </div>
              <div class="team-card__titles">
                <h3 class="team-card__name">{{ item.title || 'Sin título' }}</h3>
                <p class="team-card__slug">Orden {{ item.order }}</p>
              </div>
            </div>

            <div class="team-card__field">
              <span class="team-card__label">Orden</span>
              <div class="team-card__value">
                <q-icon name="sort" size="16px" />
                <span>{{ item.order }}</span>
              </div>
            </div>

            <div class="team-card__status">
              <span class="team-card__label">Estado</span>
              <div
                class="team-card__badge"
                :class="
                  item.published
                    ? 'team-card__badge--active'
                    : 'team-card__badge--inactive'
                "
              >
                <q-icon
                  :name="item.published ? 'visibility' : 'visibility_off'"
                  size="16px"
                />
                <span>{{ item.published ? 'Publicada' : 'Oculta' }}</span>
              </div>
            </div>

            <div class="team-card__actions">
              <q-btn
                unelevated
                no-caps
                dense
                color="primary"
                class="team-card__btn"
                icon="edit"
                label="Editar"
                @click="openEditTeam(item)"
              />
              <q-btn
                outline
                no-caps
                dense
                :color="item.published ? 'grey-8' : 'positive'"
                class="team-card__btn"
                :icon="item.published ? 'visibility_off' : 'visibility'"
                :label="item.published ? 'Ocultar' : 'Publicar'"
                :loading="togglingId === item.id"
                @click="togglePublished(item)"
              />
              <q-btn
                outline
                no-caps
                dense
                color="negative"
                class="team-card__btn team-card__btn--danger"
                icon="delete"
                label="Eliminar"
                @click="removeTeam(item.id)"
              />
            </div>
          </article>
        </div>
      </div>
    </template>

    <q-dialog v-model="teamDialog" persistent>
      <q-card style="min-width: 420px; max-width: 520px">
        <q-card-section>
          <div class="text-h6">{{ editingTeamId ? 'Editar imagen' : 'Nueva imagen' }}</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <div>
            <div class="text-caption text-grey-7 q-mb-xs">
              Imagen {{ editingTeamId ? '' : '*' }}
            </div>
            <div class="team-image-upload">
              <div class="team-image-upload__preview">
                <img v-if="teamPreviewUrl" :src="teamPreviewUrl" alt="Vista previa" />
                <div v-else class="team-image-upload__empty">
                  <q-icon name="image" size="32px" color="grey-5" />
                  <span>Sin imagen</span>
                </div>
              </div>
              <div class="team-image-upload__actions">
                <q-btn
                  outline
                  no-caps
                  color="primary"
                  icon="cloud_upload"
                  :label="editingTeamId ? 'Cambiar imagen' : 'Subir imagen'"
                  @click="teamImageInput?.click()"
                />
                <div class="text-caption text-grey-6">
                  JPG, PNG o WEBP. Máx. 10 MB.
                </div>
              </div>
              <input
                ref="teamImageInput"
                type="file"
                accept="image/*"
                hidden
                @change="onTeamImageSelected"
              />
            </div>
          </div>

          <q-input v-model="teamForm.title" outlined label="Título descriptivo" />
          <q-input v-model.number="teamForm.order" outlined type="number" label="Orden" />
          <q-toggle v-model="teamForm.published" label="Publicada en la web" color="primary" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn
            color="primary"
            unelevated
            no-caps
            label="Guardar"
            :loading="savingTeam"
            @click="saveTeam"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
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
const savingTeam = ref(false)
const togglingId = ref<number | null>(null)
const teamDialog = ref(false)
const editingTeamId = ref<number | null>(null)
const teams = ref<WeAreTeam[]>([])
const teamImageInput = ref<HTMLInputElement | null>(null)
const teamPreviewUrl = ref<string | null>(null)
const pendingTeamFile = ref<File | null>(null)

const form = reactive({
  title: '',
  description: '',
  vision: '',
  mission: '',
  values: '',
})

const teamForm = reactive({
  title: '',
  order: 0,
  published: true,
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

function openCreateTeam() {
  editingTeamId.value = null
  teamForm.title = ''
  teamForm.order = teams.value.length
    ? Math.max(...teams.value.map((t) => t.order)) + 1
    : 1
  teamForm.published = true
  pendingTeamFile.value = null
  teamPreviewUrl.value = null
  teamDialog.value = true
}

function openEditTeam(item: WeAreTeam) {
  editingTeamId.value = item.id
  teamForm.title = item.title || ''
  teamForm.order = item.order
  teamForm.published = item.published
  pendingTeamFile.value = null
  teamPreviewUrl.value = item.url || null
  teamDialog.value = true
}

function onTeamImageSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  pendingTeamFile.value = file
  if (teamPreviewUrl.value?.startsWith('blob:')) {
    URL.revokeObjectURL(teamPreviewUrl.value)
  }
  teamPreviewUrl.value = URL.createObjectURL(file)
  input.value = ''
}

function buildTeamFormData() {
  const data = new FormData()
  if (pendingTeamFile.value) {
    data.append('image', pendingTeamFile.value)
  }
  if (teamForm.title) {
    data.append('title', teamForm.title)
  }
  data.append('order', String(teamForm.order ?? 0))
  data.append('published', teamForm.published ? '1' : '0')
  return data
}

async function saveTeam() {
  if (!editingTeamId.value && !pendingTeamFile.value) {
    $q.notify({ type: 'warning', message: 'Debes subir una imagen' })
    return
  }

  savingTeam.value = true
  try {
    const payload = buildTeamFormData()
    if (editingTeamId.value) {
      await adminApi.updateWeAreTeam(editingTeamId.value, payload)
    } else {
      await adminApi.createWeAreTeam(payload)
    }
    teamDialog.value = false
    $q.notify({ type: 'positive', message: 'Imagen guardada' })
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar la imagen' })
  } finally {
    savingTeam.value = false
  }
}

async function togglePublished(item: WeAreTeam) {
  togglingId.value = item.id
  try {
    const data = new FormData()
    data.append('published', item.published ? '0' : '1')
    data.append('order', String(item.order))
    if (item.title) data.append('title', item.title)
    await adminApi.updateWeAreTeam(item.id, data)
    $q.notify({
      type: 'positive',
      message: item.published ? 'Imagen ocultada' : 'Imagen publicada',
    })
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo actualizar el estado' })
  } finally {
    togglingId.value = null
  }
}

async function removeTeam(id: number) {
  $q.dialog({
    title: 'Eliminar',
    message: '¿Eliminar esta imagen del equipo?',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteWeAreTeam(id)
      $q.notify({ type: 'positive', message: 'Imagen eliminada' })
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar' })
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

.team-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.team-card {
  display: grid;
  grid-template-columns: minmax(200px, 1.5fr) minmax(110px, 0.6fr) auto auto;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1rem;
  background: #fff;
  border: 1px solid var(--ma-border);
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.team-card__identity {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.team-card__thumb {
  width: 72px;
  height: 72px;
  flex-shrink: 0;
  border-radius: 10px;
  background: #f5f2ed;
  border: 1px solid var(--ma-border);
  display: grid;
  place-items: center;
  overflow: hidden;
}

.team-card__thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.team-card__titles {
  min-width: 0;
}

.team-card__name {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a1a;
  line-height: 1.25;
}

.team-card__slug {
  margin: 0.15rem 0 0;
  font-size: 0.75rem;
  color: #777;
}

.team-card__field,
.team-card__status {
  min-width: 0;
}

.team-card__label {
  display: block;
  margin-bottom: 0.3rem;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #888;
}

.team-card__value {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  min-height: 36px;
  padding: 0.35rem 0.65rem;
  border-radius: 8px;
  background: #f4f4f4;
  color: #333;
  font-size: 0.8rem;
}

.team-card__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  min-height: 36px;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
}

.team-card__badge--active {
  background: rgba(46, 125, 50, 0.12);
  color: #2e7d32;
}

.team-card__badge--inactive {
  background: rgba(158, 158, 158, 0.18);
  color: #616161;
}

.team-card__actions {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  min-width: 118px;
}

.team-card__btn {
  min-height: 30px;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.03em;
}

.team-card__btn--danger {
  border-color: rgba(193, 0, 21, 0.35);
}

.team-image-upload {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 0.85rem;
  align-items: start;
}

.team-image-upload__preview {
  width: 120px;
  height: 120px;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid var(--ma-border);
  background: #f7f4f0;
}

.team-image-upload__preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.team-image-upload__empty {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  color: #999;
  font-size: 0.75rem;
}

.team-image-upload__actions {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.45rem;
}

@media (max-width: 1100px) {
  .team-card {
    grid-template-columns: 1fr 1fr;
    align-items: start;
  }

  .team-card__identity,
  .team-card__actions {
    grid-column: 1 / -1;
  }

  .team-card__actions {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .team-card__btn {
    flex: 1;
  }
}

@media (max-width: 600px) {
  .team-header {
    flex-direction: column;
  }

  .team-card {
    grid-template-columns: 1fr;
  }

  .team-image-upload {
    grid-template-columns: 1fr;
  }
}
</style>
