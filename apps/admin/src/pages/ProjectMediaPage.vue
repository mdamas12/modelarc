<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Medios del proyecto</h1>
        <p class="page-subtitle">{{ project?.title || 'Cargando...' }}</p>
      </div>
      <div class="row q-gutter-sm">
        <q-btn flat no-caps icon="arrow_back" label="Volver" :to="`/proyectos/${projectId}`" />
        <q-btn color="primary" unelevated no-caps icon="cloud_upload" label="Subir" :loading="uploading" @click="pickFile" />
        <input ref="fileInput" type="file" accept="image/*,video/*" multiple hidden @change="onFiles" />
      </div>
    </div>

    <div class="admin-card q-pa-md">
      <q-banner v-if="!items.length && !loading" class="bg-grey-2 q-mb-md" rounded>
        No hay medios. Sube imágenes para la galería del proyecto.
      </q-banner>

      <div v-if="loading" class="flex flex-center q-pa-xl">
        <q-spinner color="primary" size="40px" />
      </div>

      <div v-else class="media-grid">
        <div v-for="(item, index) in items" :key="item.media_id" class="media-tile">
          <img :src="item.media?.url || placeholder" :alt="item.title || 'media'" />
          <div class="tile-actions">
            <q-btn dense flat round icon="arrow_upward" color="white" :disable="index === 0" @click="move(index, -1)" />
            <q-btn dense flat round icon="arrow_downward" color="white" :disable="index === items.length - 1" @click="move(index, 1)" />
            <q-btn dense flat round icon="delete" color="negative" @click="remove(index)" />
          </div>
          <q-badge v-if="item.is_cover" floating color="primary">Portada</q-badge>
        </div>
      </div>

      <div v-if="items.length" class="q-mt-md">
        <q-btn color="primary" unelevated no-caps label="Guardar orden" :loading="saving" @click="saveOrder" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { Project, ProjectMedia } from '@/types'

const route = useRoute()
const $q = useQuasar()
const projectId = computed(() => route.params.id as string)
const project = ref<Project | null>(null)
const items = ref<ProjectMedia[]>([])
const loading = ref(false)
const uploading = ref(false)
const saving = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const placeholder = 'https://placehold.co/400x400/1a1a1a/c4a47c?text=Media'

function pickFile() {
  fileInput.value?.click()
}

async function load() {
  loading.value = true
  try {
    project.value = await adminApi.project(projectId.value)
    items.value = (project.value.project_media || []).map((m, i) => ({
      ...m,
      media_id: m.media_id || m.media?.id || 0,
      sort_order: m.sort_order ?? i,
    }))
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cargar el proyecto' })
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
      const media = await adminApi.uploadMedia(file, 'image')
      items.value.push({
        media_id: media.id,
        type: 'gallery',
        title: media.original_name || file.name,
        sort_order: items.value.length,
        is_cover: items.value.length === 0,
        media,
      })
    }
    await saveOrder()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al subir archivos' })
  } finally {
    uploading.value = false
    input.value = ''
  }
}

function move(index: number, dir: number) {
  const target = index + dir
  if (target < 0 || target >= items.value.length) return
  const copy = [...items.value]
  const [row] = copy.splice(index, 1)
  copy.splice(target, 0, row!)
  items.value = copy.map((m, i) => ({ ...m, sort_order: i, is_cover: i === 0 }))
}

function remove(index: number) {
  items.value = items.value
    .filter((_, i) => i !== index)
    .map((m, i) => ({ ...m, sort_order: i, is_cover: i === 0 }))
}

async function saveOrder() {
  saving.value = true
  try {
    await adminApi.updateProject(projectId.value, {
      media: items.value.map((m, i) => ({
        media_id: m.media_id,
        type: m.type || 'gallery',
        title: m.title,
        sort_order: i,
        is_cover: i === 0,
      })),
      cover_media_id: items.value[0]?.media_id || null,
    })
    $q.notify({ type: 'positive', message: 'Galería actualizada' })
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar galería' })
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.tile-actions {
  position: absolute;
  inset: auto 0 0 0;
  display: flex;
  justify-content: center;
  gap: 4px;
  padding: 6px;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.65));
}
</style>
