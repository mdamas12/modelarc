<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Recorridos 360°</h1>
        <p class="page-subtitle">Tours virtuales y escenas panorámicas</p>
      </div>
      <q-btn color="primary" unelevated no-caps icon="add" label="Nuevo tour" @click="showCreate = true" />
    </div>

    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="42px" />
    </div>

    <div v-else-if="!rows.length" class="admin-card q-pa-xl text-center text-grey-7">
      No hay recorridos 360° todavía.
    </div>

    <div v-else class="project-cards">
      <article v-for="tour in rows" :key="tour.id" class="project-card">
        <div
          class="project-card__media"
          :style="{ backgroundImage: `url(${coverOf(tour)})` }"
        >
          <div class="project-card__overlay">
            <h3 class="project-card__title">{{ tour.name }}</h3>
            <p class="project-card__meta">
              {{ tour.project?.title || 'Sin proyecto' }}
              · {{ sceneCount(tour) }} escena{{ sceneCount(tour) === 1 ? '' : 's' }}
            </p>
            <p class="project-card__meta">{{ labelStatus(tour.status) }}</p>
          </div>
        </div>

        <div class="project-card__actions">
          <q-btn
            unelevated
            no-caps
            dense
            color="primary"
            class="project-card__btn"
            icon="visibility"
            label="Ver detalle"
            :to="`/recorridos/${tour.id}/editor`"
          />
          <q-btn
            outline
            no-caps
            dense
            color="negative"
            class="project-card__btn project-card__btn--danger"
            icon="delete"
            label="Eliminar"
            @click="remove(tour.id)"
          />
        </div>
      </article>
    </div>

    <div v-if="pagination.rowsNumber > pagination.rowsPerPage" class="row justify-center q-mt-lg">
      <q-pagination
        v-model="pagination.page"
        :max="Math.ceil(pagination.rowsNumber / pagination.rowsPerPage)"
        direction-links
        boundary-links
        color="primary"
        @update:model-value="load"
      />
    </div>

    <q-dialog v-model="showCreate" persistent @show="onCreateDialogShow">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Nuevo recorrido</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="createForm.name" outlined label="Nombre *" />
          <q-select
            v-model="createForm.project_id"
            outlined
            emit-value
            map-options
            use-input
            fill-input
            hide-selected
            input-debounce="0"
            clearable
            label="Proyecto *"
            :options="filteredProjectOptions"
            :loading="loadingProjects"
            :disable="loadingProjects"
            @filter="filterProjects"
          >
            <template #no-option>
              <q-item>
                <q-item-section class="text-grey">
                  {{ loadingProjects ? 'Cargando proyectos…' : 'No hay proyectos activos' }}
                </q-item-section>
              </q-item>
            </template>
          </q-select>
          <q-input v-model="createForm.description" outlined type="textarea" label="Descripción" autogrow />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn color="primary" unelevated no-caps label="Crear" :loading="creating" @click="create" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { TourScene, VirtualTour } from '@/types'

type ProjectOption = { label: string; value: number }

const $q = useQuasar()
const router = useRouter()
const loading = ref(false)
const creating = ref(false)
const loadingProjects = ref(false)
const showCreate = ref(false)
const rows = ref<VirtualTour[]>([])
const projectOptions = ref<ProjectOption[]>([])
const filteredProjectOptions = ref<ProjectOption[]>([])
const placeholder =
  'https://placehold.co/800x600/1a1a1a/c4a47c?text=Sin+imagen'

const pagination = ref({
  page: 1,
  rowsPerPage: 12,
  rowsNumber: 0,
})

const createForm = reactive({ name: '', project_id: null as number | null, description: '' })

const statusOptions = [
  { label: 'Borrador', value: 'draft' },
  { label: 'Publicado', value: 'published' },
  { label: 'Archivado', value: 'archived' },
]

function sceneMediaUrl(scene?: TourScene | null) {
  return scene?.thumbnail_media?.url || scene?.panorama_media?.url || null
}

function coverOf(tour: VirtualTour) {
  const initialId = tour.initial_scene_id
  const initial =
    tour.scenes?.find((s) => s.id === initialId) || tour.initial_scene
  const first = [...(tour.scenes || [])].sort(
    (a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0),
  )[0]

  return (
    sceneMediaUrl(initial) ||
    sceneMediaUrl(first) ||
    tour.project?.cover_media?.url ||
    placeholder
  )
}

function sceneCount(tour: VirtualTour) {
  return tour.scenes?.length ?? 0
}

function labelStatus(value?: string) {
  return statusOptions.find((o) => o.value === value)?.label || value || '—'
}

async function load() {
  loading.value = true
  try {
    const res = await adminApi.tours({
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage,
    })
    rows.value = res.data || []
    pagination.value.rowsNumber = res.meta?.total ?? rows.value.length
  } catch {
    rows.value = []
    $q.notify({ type: 'negative', message: 'No se pudieron cargar los tours' })
  } finally {
    loading.value = false
  }
}

async function loadProjects() {
  loadingProjects.value = true
  try {
    const res = await adminApi.projects({ per_page: 100 })
    const active = (res.data || []).filter((p) => p.publication_status !== 'archived')
    projectOptions.value = active.map((p) => ({ label: p.title, value: p.id }))
    filteredProjectOptions.value = projectOptions.value
  } catch {
    projectOptions.value = []
    filteredProjectOptions.value = []
    $q.notify({ type: 'negative', message: 'No se pudieron cargar los proyectos' })
  } finally {
    loadingProjects.value = false
  }
}

function filterProjects(val: string, update: (fn: () => void) => void) {
  update(() => {
    const needle = val.trim().toLowerCase()
    filteredProjectOptions.value = needle
      ? projectOptions.value.filter((p) => p.label.toLowerCase().includes(needle))
      : projectOptions.value
  })
}

function resetCreateForm() {
  createForm.name = ''
  createForm.project_id = null
  createForm.description = ''
}

async function onCreateDialogShow() {
  resetCreateForm()
  await loadProjects()
}

async function create() {
  if (!createForm.name || !createForm.project_id) {
    $q.notify({ type: 'warning', message: 'Nombre y proyecto son requeridos' })
    return
  }
  creating.value = true
  try {
    const tour = await adminApi.createTour({ ...createForm })
    showCreate.value = false
    await router.push(`/recorridos/${tour.id}/editor`)
  } catch {
    $q.notify({ type: 'negative', message: 'Error al crear el tour' })
  } finally {
    creating.value = false
  }
}

async function remove(id: number) {
  $q.dialog({
    title: 'Eliminar tour',
    message: '¿Eliminar este recorrido virtual?',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteTour(id)
      $q.notify({ type: 'positive', message: 'Tour eliminado' })
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar' })
    }
  })
}

onMounted(load)
</script>

<style scoped lang="scss">
.project-cards {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.25rem;
}

.project-card {
  background: #fff;
  border: 1px solid var(--ma-border);
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.project-card__media {
  position: relative;
  aspect-ratio: 4 / 3;
  background-size: cover;
  background-position: center;
  background-color: #1a1a1a;
}

.project-card__overlay {
  position: absolute;
  inset: auto 0 0 0;
  padding: 1.25rem 1rem 1rem;
  text-align: center;
  color: #1a1a1a;
  background: linear-gradient(transparent, rgba(255, 255, 255, 0.92) 28%, rgba(255, 255, 255, 0.96));
}

.project-card__title {
  margin: 0 0 0.35rem;
  font-size: 1.05rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  line-height: 1.25;
}

.project-card__meta {
  margin: 0;
  font-size: 0.78rem;
  color: #666;
  line-height: 1.4;
}

.project-card__actions {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 0.85rem 0.9rem;
}

.project-card__btn {
  flex: 1;
  min-height: 32px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.project-card__btn--danger {
  border-color: rgba(193, 0, 21, 0.35);
}

.project-card__btn :deep(.q-icon) {
  font-size: 16px;
}

@media (max-width: 1100px) {
  .project-cards {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 700px) {
  .project-cards {
    grid-template-columns: 1fr;
  }
}
</style>
