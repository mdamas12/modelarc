<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ isEdit ? 'Editar proyecto' : 'Nuevo proyecto' }}</h1>
        <p class="page-subtitle">
          {{ isEdit ? 'Actualiza información, galería e imagen destacada' : 'Completa la información del proyecto' }}
        </p>
      </div>
      <q-btn flat no-caps icon="arrow_back" label="Volver" to="/proyectos" />
    </div>

    <div class="project-editor" :class="{ 'project-editor--create': !isEdit }">
      <!-- Left: media (edit only) -->
      <section v-if="isEdit" class="admin-card project-editor__media">
        <div class="project-editor__media-head">
          <div>
            <h2 class="project-editor__section-title">Fotografías del proyecto</h2>
            <p class="project-editor__section-lead">
              Arrastra, organiza y marca la imagen destacada
            </p>
          </div>
          <q-btn
            color="primary"
            unelevated
            no-caps
            icon="cloud_upload"
            label="Subir fotos"
            :loading="uploading"
            @click="pickFile"
          />
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            multiple
            hidden
            @change="onFiles"
          />
        </div>

        <div
          class="upload-drop"
          :class="{ 'upload-drop--active': dragOver }"
          @dragover.prevent="dragOver = true"
          @dragleave.prevent="dragOver = false"
          @drop.prevent="onDrop"
          @click="pickFile"
        >
          <q-icon name="add_photo_alternate" size="36px" color="grey-6" />
          <div class="text-weight-medium q-mt-sm">Arrastra imágenes aquí</div>
          <div class="text-caption text-grey-6">o haz clic para seleccionar</div>
        </div>

        <div class="project-editor__media-scroll">
          <div v-if="mediaLoading" class="flex flex-center q-pa-lg">
            <q-spinner color="primary" size="36px" />
          </div>

          <div v-else-if="!mediaItems.length" class="text-grey-6 q-mt-md text-center">
            Aún no hay fotografías en este proyecto.
          </div>

          <div v-else class="media-grid">
            <div
              v-for="(item, index) in mediaItems"
              :key="`${item.media_id}-${index}`"
              class="media-tile media-tile--project"
              :class="{
                'media-tile--cover': item.is_cover,
                'media-tile--unpublished': item.is_published === false,
              }"
            >
              <div class="media-tile__preview">
                <img :src="item.media?.url || placeholder" :alt="item.title || 'foto'" />
                <q-badge v-if="item.is_cover" floating color="primary">Principal</q-badge>
                <q-badge
                  v-if="item.is_published === false"
                  floating
                  color="warning"
                  class="media-tile__hidden-badge"
                >
                  Oculta
                </q-badge>
                <div class="tile-actions">
                  <q-btn
                    dense
                    flat
                    round
                    icon="star"
                    color="white"
                    :disable="item.is_cover"
                    @click.stop="setCover(index)"
                  >
                    <q-tooltip>Marcar como destacada</q-tooltip>
                  </q-btn>
                  <q-btn
                    dense
                    flat
                    round
                    icon="arrow_upward"
                    color="white"
                    :disable="index === 0"
                    @click.stop="move(index, -1)"
                  />
                  <q-btn
                    dense
                    flat
                    round
                    icon="arrow_downward"
                    color="white"
                    :disable="index === mediaItems.length - 1"
                    @click.stop="move(index, 1)"
                  />
                  <q-btn
                    dense
                    flat
                    round
                    icon="delete"
                    color="negative"
                    @click.stop="removeMedia(index)"
                  />
                </div>
              </div>
              <div class="tile-meta" @click.stop>
                <q-select
                  v-model="item.subcategory"
                  dense
                  outlined
                  emit-value
                  map-options
                  clearable
                  bg-color="white"
                  label="Subcategoría"
                  :options="projectSubcategoryOptions"
                  @update:model-value="() => saveMedia(false)"
                />
                <q-toggle
                  v-model="item.is_published"
                  dense
                  color="primary"
                  label="Web"
                  @update:model-value="() => saveMedia(false)"
                />
              </div>
            </div>
          </div>
        </div>

        <div v-if="mediaItems.length" class="project-editor__media-footer">
          <q-btn
            outline
            no-caps
            color="primary"
            label="Guardar galería"
            :loading="savingMedia"
            @click="() => saveMedia()"
          />
        </div>
      </section>

      <!-- Right / full: form -->
      <section class="admin-card project-editor__form">
        <h2 class="project-editor__section-title">
          {{ isEdit ? 'Información del proyecto' : 'Crear proyecto' }}
        </h2>

        <q-banner v-if="!isEdit" dense rounded class="bg-grey-2 q-mb-md">
          Primero guarda el proyecto. Luego podrás cargar fotos e imagen destacada.
        </q-banner>

        <q-form class="q-gutter-md" @submit.prevent="save">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-8">
              <q-input
                v-model="form.title"
                outlined
                label="Título *"
                :rules="[(v) => !!v || 'Requerido']"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.slug" outlined label="Slug" hint="Opcional" />
            </div>
            <div class="col-12">
              <q-input v-model="form.summary" outlined label="Resumen" type="textarea" autogrow />
            </div>
            <div class="col-12">
              <q-input
                v-model="form.description"
                outlined
                label="Descripción"
                type="textarea"
                autogrow
              />
            </div>
            <div class="col-12 col-md-6">
              <q-select
                v-model="form.category"
                outlined
                emit-value
                map-options
                label="Categoría *"
                :options="categoryOptions"
                :rules="[(v) => !!v || 'Requerido']"
              />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.location" outlined label="Ubicación" />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model.number="form.year" outlined type="number" label="Año" />
            </div>
            <div class="col-12 col-md-4">
              <q-select
                v-model="form.status"
                outlined
                emit-value
                map-options
                clearable
                label="Estado de obra"
                :options="workStatusOptions"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-select
                v-model="form.publication_status"
                outlined
                emit-value
                map-options
                label="Publicación"
                :options="pubOptions"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.area" outlined label="Área" />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.duration" outlined label="Duración" />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.client_name" outlined label="Cliente" />
            </div>
            <div class="col-12">
              <q-toggle v-model="form.is_featured" label="Proyecto destacado" color="primary" />
              <q-toggle
                v-model="form.has_virtual_tour"
                label="Tiene tour virtual"
                color="primary"
                class="q-ml-md"
              />
            </div>
            <div v-show="showSeoFields" class="col-12">
              <q-input v-model="form.seo_title" outlined label="SEO título" />
            </div>
            <div v-show="showSeoFields" class="col-12">
              <q-input
                v-model="form.seo_description"
                outlined
                label="SEO descripción"
                type="textarea"
                autogrow
              />
            </div>
          </div>

          <div class="row q-gutter-sm q-mt-md">
            <q-btn
              type="submit"
              color="primary"
              unelevated
              no-caps
              :loading="saving"
              :label="isEdit ? 'Guardar cambios' : 'Crear proyecto'"
            />
            <q-btn flat no-caps label="Cancelar" to="/proyectos" />
          </div>
        </q-form>
      </section>
    </div>

    <ProjectGalleryChangesSection
      v-if="isEdit"
      class="q-mt-md"
      :project-id="route.params.id as string"
      :category="form.category"
    />
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import ProjectGalleryChangesSection from '@/components/projects/ProjectGalleryChangesSection.vue'
import { adminApi } from '@/services/adminApi'
import { PROJECT_CATEGORIES, subcategoriesFor } from '@/constants/mediaTaxonomy'
import type { ProjectMedia } from '@/types'

const route = useRoute()
const router = useRouter()
const $q = useQuasar()

const isEdit = computed(
  () => route.params.id !== undefined && route.path !== '/proyectos/nuevo',
)
const saving = ref(false)
const savingMedia = ref(false)
const uploading = ref(false)
const mediaLoading = ref(false)
const dragOver = ref(false)
const showSeoFields = false
const fileInput = ref<HTMLInputElement | null>(null)
const mediaItems = ref<ProjectMedia[]>([])
const placeholder = 'https://placehold.co/400x400/1a1a1a/c4a47c?text=Media'

const form = reactive({
  title: '',
  slug: '',
  summary: '',
  description: '',
  category: 'residencial',
  location: '',
  year: null as number | null,
  status: null as string | null,
  area: '',
  duration: '',
  client_name: '',
  publication_status: 'draft',
  is_featured: false,
  has_virtual_tour: false,
  seo_title: '',
  seo_description: '',
})

const categoryOptions = [...PROJECT_CATEGORIES]

const projectSubcategoryOptions = computed(() => subcategoriesFor(form.category))

const workStatusOptions = [
  { label: 'En ejecución', value: 'en_ejecucion' },
  { label: 'Finalizado', value: 'finalizado' },
]

const pubOptions = [
  { label: 'Borrador', value: 'draft' },
  { label: 'Publicado', value: 'published' },
  { label: 'Archivado', value: 'archived' },
]

function pickFile() {
  fileInput.value?.click()
}

function normalizeMedia(list: ProjectMedia[] = []) {
  return list.map((m, i) => ({
    ...m,
    media_id: m.media_id || m.media?.id || 0,
    sort_order: m.sort_order ?? i,
    is_cover: Boolean(m.is_cover) || i === 0,
    subcategory: m.subcategory ?? null,
    is_published: m.is_published !== false,
  }))
}

async function loadProject() {
  if (!isEdit.value) {
    mediaItems.value = []
    return
  }
  mediaLoading.value = true
  try {
    const project = await adminApi.project(route.params.id as string)
    Object.assign(form, {
      title: project.title || '',
      slug: project.slug || '',
      summary: project.summary || '',
      description: project.description || '',
      category: project.category || 'residencial',
      location: project.location || '',
      year: project.year ?? null,
      status: project.status ?? null,
      area: project.area || '',
      duration: project.duration || '',
      client_name: project.client_name || '',
      publication_status: project.publication_status || 'draft',
      is_featured: Boolean(project.is_featured),
      has_virtual_tour: Boolean(project.has_virtual_tour),
      seo_title: project.seo_title || '',
      seo_description: project.seo_description || '',
    })
    mediaItems.value = normalizeMedia(project.project_media || [])
    if (project.cover_media?.id) {
      const coverId = project.cover_media.id
      const hasCover = mediaItems.value.some((m) => m.media_id === coverId)
      mediaItems.value = mediaItems.value.map((m, i) => ({
        ...m,
        is_cover: hasCover ? m.media_id === coverId : i === 0,
      }))
    }
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cargar el proyecto' })
  } finally {
    mediaLoading.value = false
  }
}

async function save() {
  saving.value = true
  try {
    const payload = { ...form }
    if (isEdit.value) {
      await adminApi.updateProject(route.params.id as string, payload)
      $q.notify({ type: 'positive', message: 'Proyecto actualizado' })
    } else {
      const created = await adminApi.createProject(payload)
      $q.notify({ type: 'positive', message: 'Proyecto creado. Ahora puedes subir fotos.' })
      await router.replace(`/proyectos/${created.id}`)
    }
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar' })
  } finally {
    saving.value = false
  }
}

async function addFiles(files: File[]) {
  if (!files.length || !isEdit.value) return
  uploading.value = true
  try {
    for (const file of files) {
      const media = await adminApi.uploadMedia(file, 'image', {
        category: form.category,
        is_published: true,
      })
      mediaItems.value.push({
        media_id: media.id,
        type: 'gallery',
        title: media.original_name || file.name,
        sort_order: mediaItems.value.length,
        is_cover: mediaItems.value.length === 0,
        subcategory: null,
        is_published: true,
        media,
      })
    }
    await saveMedia(false)
  } catch {
    $q.notify({ type: 'negative', message: 'Error al subir archivos' })
  } finally {
    uploading.value = false
  }
}

async function onFiles(event: Event) {
  const input = event.target as HTMLInputElement
  await addFiles(Array.from(input.files || []))
  input.value = ''
}

async function onDrop(event: DragEvent) {
  dragOver.value = false
  const files = Array.from(event.dataTransfer?.files || []).filter((f) =>
    f.type.startsWith('image/'),
  )
  await addFiles(files)
}

function move(index: number, dir: number) {
  const target = index + dir
  if (target < 0 || target >= mediaItems.value.length) return
  const copy = [...mediaItems.value]
  const [row] = copy.splice(index, 1)
  copy.splice(target, 0, row!)
  const coverId = copy.find((m) => m.is_cover)?.media_id
  mediaItems.value = copy.map((m, i) => ({
    ...m,
    sort_order: i,
    is_cover: coverId ? m.media_id === coverId : i === 0,
  }))
}

function setCover(index: number) {
  mediaItems.value = mediaItems.value.map((m, i) => {
    const next: ProjectMedia = {
      ...m,
      is_cover: i === index,
    }
    next.type = m.type || 'gallery'
    return next
  })
}

function removeMedia(index: number) {
  const wasCover = mediaItems.value[index]?.is_cover
  const next = mediaItems.value.filter((_, i) => i !== index)
  mediaItems.value = next.map((m, i) => ({
    ...m,
    sort_order: i,
    is_cover: wasCover ? i === 0 : Boolean(m.is_cover),
  }))
}

async function saveMedia(notify = true) {
  if (!isEdit.value) return
  savingMedia.value = true
  try {
    const cover = mediaItems.value.find((m) => m.is_cover) || mediaItems.value[0]
    await adminApi.updateProject(route.params.id as string, {
      media: mediaItems.value.map((m, i) => ({
        media_id: m.media_id,
        type: m.type || 'gallery',
        title: m.title,
        subcategory: m.subcategory || null,
        sort_order: i,
        is_cover: cover ? m.media_id === cover.media_id : i === 0,
        is_published: m.is_published !== false,
      })),
      cover_media_id: cover?.media_id || null,
    })
    if (notify) $q.notify({ type: 'positive', message: 'Galería actualizada' })
    await loadProject()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar galería' })
  } finally {
    savingMedia.value = false
  }
}

watch(
  () => route.params.id,
  () => {
    void loadProject()
  },
)

watch(
  () => form.category,
  (category) => {
    const allowed = new Set(subcategoriesFor(category).map((o) => o.value))
    let changed = false
    mediaItems.value = mediaItems.value.map((m) => {
      if (m.subcategory && !allowed.has(m.subcategory)) {
        changed = true
        return { ...m, subcategory: null }
      }
      return m
    })
    if (changed && isEdit.value) void saveMedia(false)
  },
)

onMounted(loadProject)
</script>

<style scoped lang="scss">
.project-editor {
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  gap: 1.25rem;
  align-items: stretch;
  min-height: calc(100vh - 180px);
}

.project-editor--create {
  grid-template-columns: 1fr;
  max-width: 920px;
  min-height: 0;
}

.project-editor__media,
.project-editor__form {
  padding: 1.25rem 1.35rem 1.5rem;
  min-height: 0;
  height: calc(100vh - 180px);
  max-height: calc(100vh - 180px);
  box-sizing: border-box;
}

.project-editor__media {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.project-editor__form {
  overflow: auto;
}

.project-editor__media-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.85rem;
  flex-wrap: wrap;
  flex-shrink: 0;
}

.project-editor__section-title {
  margin: 0 0 0.25rem;
  font-size: 1.15rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.project-editor__section-lead {
  margin: 0;
  color: var(--ma-muted);
  font-size: 0.88rem;
}

.upload-drop {
  border: 2px dashed #d0d0d0;
  border-radius: 12px;
  background: #fafafa;
  min-height: 100px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.upload-drop:hover,
.upload-drop--active {
  border-color: var(--ma-gold);
  background: rgba(196, 164, 124, 0.08);
}

.project-editor__media-scroll {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  margin-top: 0.85rem;
  padding-right: 4px;
}

.project-editor__media-footer {
  flex-shrink: 0;
  margin-top: 0.85rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--ma-border);
}

.tile-actions {
  position: absolute;
  inset: auto 0 0 0;
  display: flex;
  justify-content: center;
  gap: 2px;
  padding: 6px;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
}

.media-tile--project {
  aspect-ratio: auto;
  overflow: visible;
  display: flex;
  flex-direction: column;
  background: #fff;
}

.media-tile__preview {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  border-radius: 8px 8px 0 0;
  background: #eee;
}

.media-tile__preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.media-tile__hidden-badge {
  top: 8px;
  right: 8px;
  left: auto;
}

.tile-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 8px;
  border-top: 1px solid var(--ma-border);
}

.media-tile--cover {
  outline: 2px solid var(--ma-gold);
  outline-offset: -2px;
}

.media-tile--unpublished {
  opacity: 0.85;
}

@media (max-width: 1023px) {
  .project-editor {
    grid-template-columns: 1fr;
  }

  .project-editor__media,
  .project-editor__form {
    max-height: none;
    height: auto;
  }

  .project-editor__media-scroll {
    max-height: 420px;
  }
}
</style>
