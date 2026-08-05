<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Galería de medios</h1>
        <p class="page-subtitle">
          Biblioteca multimedia. Marca como publicada las que deben verse en la web.
        </p>
      </div>
      <q-btn
        color="primary"
        unelevated
        no-caps
        icon="cloud_upload"
        label="Subir"
        :loading="uploading"
        @click="pickFile"
      />
      <input ref="fileInput" type="file" accept="image/*,video/*" multiple hidden @change="onFiles" />
    </div>

    <div class="admin-card q-pa-md">
      <div class="row q-col-gutter-md q-mb-md items-end">
        <div class="col-12 col-md-2">
          <q-select
            v-model="filters.type"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Tipo"
            :options="typeOptions"
            @update:model-value="reload"
          />
        </div>
        <div class="col-12 col-md-2">
          <q-select
            v-model="filters.category"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Categoría"
            :options="categoryOptions"
            @update:model-value="onCategoryFilter"
          />
        </div>
        <div class="col-12 col-md-3">
          <q-select
            v-model="filters.subcategory"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Subcategoría"
            :options="filterSubcategoryOptions"
            :disable="!filters.category"
            @update:model-value="reload"
          />
        </div>
        <div class="col-12 col-md-2">
          <q-select
            v-model="filters.is_published"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Publicación"
            :options="publishOptions"
            @update:model-value="reload"
          />
        </div>
        <div class="col-12 col-md-3">
          <div class="text-caption text-grey-7 q-mb-xs">Valores por defecto al subir</div>
          <div class="row q-col-gutter-sm">
            <div class="col-6">
              <q-select
                v-model="uploadDefaults.category"
                outlined
                dense
                clearable
                emit-value
                map-options
                label="Categoría"
                :options="categoryOptions"
                @update:model-value="uploadDefaults.subcategory = null"
              />
            </div>
            <div class="col-6">
              <q-select
                v-model="uploadDefaults.subcategory"
                outlined
                dense
                clearable
                emit-value
                map-options
                label="Subcategoría"
                :options="uploadSubcategoryOptions"
                :disable="!uploadDefaults.category"
              />
            </div>
          </div>
        </div>
      </div>

      <div v-if="loading" class="flex flex-center q-pa-xl">
        <q-spinner color="primary" size="40px" />
      </div>

      <div v-else class="media-grid">
        <div
          v-for="(item, index) in items"
          :key="item.id"
          class="media-tile"
          :class="{ 'media-tile--unpublished': item.is_published === false }"
        >
          <img :src="item.url || placeholder" :alt="item.original_name || 'media'" />
          <div class="tile-badges">
            <q-badge v-if="item.category" color="primary" :label="labelCategory(item.category)" />
            <q-badge
              v-if="item.subcategory"
              color="grey-8"
              :label="labelSubcategory(item.category, item.subcategory)"
            />
            <q-badge
              :color="item.is_published === false ? 'warning' : 'positive'"
              :label="item.is_published === false ? 'Oculta' : 'Publicada'"
            />
          </div>
          <div class="tile-bar">
            <span class="ellipsis">{{ item.original_name || `#${item.id}` }}</span>
            <div class="row no-wrap q-gutter-xs">
              <q-btn
                dense
                flat
                round
                :icon="item.is_published === false ? 'visibility_off' : 'visibility'"
                :color="item.is_published === false ? 'warning' : 'positive'"
                size="sm"
                :loading="publishingId === item.id"
                @click="togglePublished(item)"
              >
                <q-tooltip>
                  {{ item.is_published === false ? 'Mostrar en la web' : 'Ocultar de la web' }}
                </q-tooltip>
              </q-btn>
              <q-btn
                dense
                flat
                round
                icon="arrow_upward"
                color="white"
                size="sm"
                :disable="index === 0"
                @click="moveItem(index, -1)"
              />
              <q-btn
                dense
                flat
                round
                icon="arrow_downward"
                color="white"
                size="sm"
                :disable="index === items.length - 1"
                @click="moveItem(index, 1)"
              />
              <q-btn dense flat round icon="edit" color="white" size="sm" @click="openEdit(item)" />
              <q-btn
                dense
                flat
                round
                icon="delete"
                color="negative"
                size="sm"
                @click="remove(item.id)"
              />
            </div>
          </div>
        </div>
      </div>

      <div v-if="!items.length && !loading" class="text-grey-6 q-pa-lg text-center">
        No hay archivos. Sube el primero.
      </div>

      <div v-if="meta.last_page > 1" class="row justify-center q-mt-md">
        <q-pagination
          v-model="page"
          :max="meta.last_page"
          color="primary"
          @update:model-value="load"
        />
      </div>
    </div>

    <q-dialog v-model="showEdit" persistent>
      <q-card style="min-width: 380px; max-width: 480px">
        <q-card-section>
          <div class="text-h6">Editar medio</div>
          <div class="text-caption text-grey-7 ellipsis">{{ editForm.original_name }}</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-img
            v-if="editForm.url"
            :src="editForm.url"
            ratio="16/9"
            style="border-radius: 8px"
          />
          <q-select
            v-model="editForm.category"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Categoría"
            :options="categoryOptions"
            @update:model-value="editForm.subcategory = null"
          />
          <q-select
            v-model="editForm.subcategory"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Subcategoría"
            :options="editSubcategoryOptions"
            :disable="!editForm.category"
          />
          <q-toggle v-model="editForm.is_published" label="Publicada en la web" color="primary" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn color="primary" unelevated no-caps label="Guardar" :loading="saving" @click="saveEdit" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import {
  PROJECT_CATEGORIES,
  labelCategory,
  labelSubcategory,
  subcategoriesFor,
} from '@/constants/mediaTaxonomy'
import type { MediaItem } from '@/types'

const $q = useQuasar()
const loading = ref(false)
const uploading = ref(false)
const saving = ref(false)
const publishingId = ref<number | null>(null)
const showEdit = ref(false)
const items = ref<MediaItem[]>([])
const page = ref(1)
const fileInput = ref<HTMLInputElement | null>(null)
const placeholder = 'https://placehold.co/400x400/1a1a1a/c4a47c?text=Media'
const meta = reactive({ last_page: 1, total: 0 })

const filters = reactive({
  type: null as string | null,
  category: null as string | null,
  subcategory: null as string | null,
  is_published: null as boolean | null,
})

const uploadDefaults = reactive({
  category: null as string | null,
  subcategory: null as string | null,
})

const editForm = reactive({
  id: 0,
  url: '',
  original_name: '',
  category: null as string | null,
  subcategory: null as string | null,
  is_published: true,
})

const typeOptions = [
  { label: 'Imagen', value: 'image' },
  { label: 'Panorama', value: 'panorama' },
  { label: 'Video', value: 'video' },
]

const categoryOptions = [...PROJECT_CATEGORIES]

const publishOptions = [
  { label: 'Publicada', value: true },
  { label: 'Oculta', value: false },
]

const filterSubcategoryOptions = computed(() => subcategoriesFor(filters.category))
const uploadSubcategoryOptions = computed(() => subcategoriesFor(uploadDefaults.category))
const editSubcategoryOptions = computed(() => subcategoriesFor(editForm.category))

function pickFile() {
  fileInput.value?.click()
}

function reload() {
  page.value = 1
  void load()
}

function onCategoryFilter() {
  filters.subcategory = null
  reload()
}

async function load() {
  loading.value = true
  try {
    const res = await adminApi.media({
      page: page.value,
      per_page: 24,
      type: filters.type || undefined,
      category: filters.category || undefined,
      subcategory: filters.subcategory || undefined,
      is_published: filters.is_published == null ? undefined : filters.is_published,
    })
    items.value = res.data || []
    meta.last_page = res.meta?.last_page ?? 1
    meta.total = res.meta?.total ?? items.value.length
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cargar la galería' })
  } finally {
    loading.value = false
  }
}

async function onFiles(event: Event) {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files || [])
  if (!files.length) return
  uploading.value = true
  try {
    for (const file of files) {
      await adminApi.uploadMedia(file, filters.type || 'image', {
        category: uploadDefaults.category,
        subcategory: uploadDefaults.subcategory,
        is_published: true,
      })
    }
    $q.notify({ type: 'positive', message: 'Archivos subidos' })
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al subir' })
  } finally {
    uploading.value = false
    input.value = ''
  }
}

function openEdit(item: MediaItem) {
  editForm.id = item.id
  editForm.url = item.url
  editForm.original_name = item.original_name || `#${item.id}`
  editForm.category = item.category || null
  editForm.subcategory = item.subcategory || null
  editForm.is_published = item.is_published !== false
  showEdit.value = true
}

async function togglePublished(item: MediaItem) {
  const next = item.is_published === false
  publishingId.value = item.id
  try {
    const updated = await adminApi.updateMedia(item.id, { is_published: next })
    items.value = items.value.map((row) =>
      row.id === item.id ? { ...row, is_published: updated.is_published !== false } : row,
    )
    $q.notify({
      type: 'positive',
      message: next ? 'Visible en la web' : 'Oculta en la web',
    })
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo actualizar la publicación' })
  } finally {
    publishingId.value = null
  }
}

async function saveEdit() {
  saving.value = true
  try {
    await adminApi.updateMedia(editForm.id, {
      category: editForm.category,
      subcategory: editForm.subcategory,
      is_published: editForm.is_published,
    })
    showEdit.value = false
    $q.notify({ type: 'positive', message: 'Medio actualizado' })
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo guardar' })
  } finally {
    saving.value = false
  }
}

async function moveItem(index: number, dir: number) {
  const target = index + dir
  if (target < 0 || target >= items.value.length) return
  const copy = [...items.value]
  const [row] = copy.splice(index, 1)
  if (!row) return
  copy.splice(target, 0, row)
  items.value = copy
  try {
    await adminApi.reorderMedia(copy.map((m) => m.id))
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo reordenar' })
    await load()
  }
}

async function remove(id: number) {
  $q.dialog({
    title: 'Eliminar archivo',
    message: '¿Eliminar este medio?',
    cancel: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteMedia(id)
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar' })
    }
  })
}

onMounted(load)
</script>

<style scoped>
.media-tile {
  position: relative;
  overflow: hidden;
}

.media-tile--unpublished {
  opacity: 0.72;
}

.tile-badges {
  position: absolute;
  top: 8px;
  left: 8px;
  right: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.tile-bar {
  position: absolute;
  inset: auto 0 0 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 4px;
  padding: 6px 8px;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
  color: #fff;
  font-size: 0.7rem;
}
</style>
