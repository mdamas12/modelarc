<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Proyectos</h1>
        <p class="page-subtitle">Gestiona el portafolio de proyectos</p>
      </div>
      <q-btn color="primary" unelevated no-caps icon="add" label="Nuevo proyecto" to="/proyectos/nuevo" />
    </div>

    <div class="filters-bar row q-col-gutter-md q-mb-lg items-center">
      <div class="col-12 col-md-4">
        <q-input
          v-model="filters.search"
          outlined
          dense
          clearable
          placeholder="Buscar por nombre..."
          debounce="300"
          bg-color="white"
          @update:model-value="load"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-6 col-md-3">
        <q-select
          v-model="filters.publication_status"
          outlined
          dense
          clearable
          emit-value
          map-options
          label="Estado"
          bg-color="white"
          :options="statusOptions"
          @update:model-value="load"
        />
      </div>
      <div class="col-6 col-md-3">
        <q-select
          v-model="filters.category"
          outlined
          dense
          clearable
          emit-value
          map-options
          label="Categoría"
          bg-color="white"
          :options="categoryOptions"
          @update:model-value="load"
        />
      </div>
    </div>

    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="42px" />
    </div>

    <div v-else-if="!rows.length" class="admin-card q-pa-xl text-center text-grey-7">
      No hay proyectos con estos filtros.
    </div>

    <div v-else class="project-cards">
      <article v-for="project in rows" :key="project.id" class="project-card">
        <div
          class="project-card__media"
          :style="{ backgroundImage: `url(${coverOf(project)})` }"
        >
          <div class="project-card__overlay">
            <h3 class="project-card__title">{{ project.title }}</h3>
            <p class="project-card__meta">
              {{ labelCategory(project.category) }}
              <span v-if="project.location"> · {{ project.location }}</span>
            </p>
            <p class="project-card__meta">
              {{ labelStatus(project.publication_status) }}
              <span v-if="project.year"> · {{ project.year }}</span>
            </p>
          </div>
          <q-badge
            v-if="project.is_featured"
            class="project-card__badge"
            color="primary"
            label="Destacado"
          />
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
            :to="`/proyectos/${project.id}`"
          />
          <q-btn
            outline
            no-caps
            dense
            color="negative"
            class="project-card__btn project-card__btn--danger"
            icon="delete"
            label="Eliminar"
            @click="remove(project.id)"
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
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { Project } from '@/types'

const $q = useQuasar()
const loading = ref(false)
const rows = ref<Project[]>([])
const placeholder =
  'https://placehold.co/800x600/1a1a1a/c4a47c?text=Sin+imagen'

const filters = reactive({
  search: '',
  publication_status: null as string | null,
  category: null as string | null,
})

const pagination = ref({
  page: 1,
  rowsPerPage: 12,
  rowsNumber: 0,
})

const statusOptions = [
  { label: 'Borrador', value: 'draft' },
  { label: 'Publicado', value: 'published' },
  { label: 'Archivado', value: 'archived' },
]

const categoryOptions = [
  { label: 'Residencial', value: 'residencial' },
  { label: 'Comercial', value: 'comercial' },
  { label: 'Corporativo', value: 'corporativo' },
]

function coverOf(project: Project) {
  return project.cover_media?.url || placeholder
}

function labelCategory(value?: string) {
  return categoryOptions.find((o) => o.value === value)?.label || value || '—'
}

function labelStatus(value?: string) {
  return statusOptions.find((o) => o.value === value)?.label || value || '—'
}

async function load() {
  loading.value = true
  try {
    const res = await adminApi.projects({
      search: filters.search || undefined,
      publication_status: filters.publication_status || undefined,
      category: filters.category || undefined,
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage,
    })
    rows.value = res.data || []
    pagination.value.rowsNumber = res.meta?.total ?? rows.value.length
  } catch {
    rows.value = []
    $q.notify({ type: 'negative', message: 'No se pudieron cargar los proyectos' })
  } finally {
    loading.value = false
  }
}

async function remove(id: number) {
  $q.dialog({
    title: 'Eliminar proyecto',
    message: '¿Seguro que deseas eliminar este proyecto?',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteProject(id)
      $q.notify({ type: 'positive', message: 'Proyecto eliminado' })
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

.project-card__badge {
  position: absolute;
  top: 10px;
  left: 10px;
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
