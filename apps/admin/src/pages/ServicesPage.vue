<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Servicios</h1>
        <p class="page-subtitle">Catálogo de servicios</p>
      </div>
      <q-btn color="primary" unelevated no-caps icon="add" label="Nuevo" @click="openCreate" />
    </div>

    <div class="admin-card q-pa-md">
      <div class="row q-col-gutter-md q-mb-md items-center">
        <div class="col-12 col-md-5">
          <q-input
            v-model="search"
            outlined
            dense
            clearable
            debounce="200"
            placeholder="Buscar por nombre..."
            bg-color="white"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
        <div class="col-6 col-md-3">
          <q-select
            v-model="statusFilter"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Estado"
            bg-color="white"
            :options="statusOptions"
          />
        </div>
      </div>

      <div v-if="loading" class="flex flex-center q-pa-xl">
        <q-spinner color="primary" size="42px" />
      </div>

      <div v-else-if="!filteredRows.length" class="text-grey-7 text-center q-pa-xl">
        No hay servicios con estos filtros.
      </div>

      <div v-else class="service-list">
        <article v-for="service in filteredRows" :key="service.id" class="service-card">
          <div class="service-card__identity">
            <div class="service-card__thumb">
              <img
                v-if="service.image?.url"
                :src="service.image.url"
                :alt="service.name"
              />
              <q-icon v-else :name="displayIcon(service.icon)" size="28px" color="primary" />
            </div>
            <div class="service-card__titles">
              <h3 class="service-card__name">{{ service.name }}</h3>
              <p class="service-card__slug">{{ service.summary || 'Sin resumen' }}</p>
            </div>
          </div>

          <div class="service-card__field">
            <span class="service-card__label">Orden</span>
            <div class="service-card__value">
              <q-icon name="sort" size="16px" />
              <span>{{ service.sort_order ?? 0 }}</span>
            </div>
          </div>

          <div class="service-card__field service-card__field--wide">
            <span class="service-card__label">Descripción</span>
            <div class="service-card__value">
              <q-icon name="notes" size="16px" />
              <span class="ellipsis">{{ service.description || service.summary || '—' }}</span>
            </div>
          </div>

          <div class="service-card__status">
            <span class="service-card__label">Estado</span>
            <div
              class="service-card__badge"
              :class="
                service.status === 'active'
                  ? 'service-card__badge--active'
                  : 'service-card__badge--inactive'
              "
            >
              <q-icon
                :name="service.status === 'active' ? 'check_circle' : 'pause_circle'"
                size="16px"
              />
              <span>{{ labelStatus(service.status) }}</span>
            </div>
          </div>

          <div class="service-card__actions">
            <q-btn
              unelevated
              no-caps
              dense
              color="primary"
              class="service-card__btn"
              icon="edit"
              label="Editar"
              @click="openEdit(service)"
            />
            <q-btn
              outline
              no-caps
              dense
              color="negative"
              class="service-card__btn service-card__btn--danger"
              icon="delete"
              label="Eliminar"
              @click="remove(service.id)"
            />
          </div>
        </article>
      </div>
    </div>

    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 440px; max-width: 520px">
        <q-card-section>
          <div class="text-h6">{{ editingId ? 'Editar servicio' : 'Nuevo servicio' }}</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="form.name" outlined label="Nombre *" />

          <div>
            <div class="text-caption text-grey-7 q-mb-xs">Imagen (web) *</div>
            <div class="service-image-upload">
              <div class="service-image-upload__preview">
                <img v-if="imagePreviewUrl" :src="imagePreviewUrl" alt="Vista previa" />
                <div v-else class="service-image-upload__empty">
                  <q-icon name="image" size="32px" color="grey-5" />
                  <span>Sin imagen</span>
                </div>
              </div>
              <div class="service-image-upload__actions">
                <q-btn
                  outline
                  no-caps
                  color="primary"
                  icon="cloud_upload"
                  label="Subir imagen"
                  :loading="uploadingImage"
                  @click="imageInput?.click()"
                />
                <q-btn
                  v-if="form.image_media_id"
                  flat
                  no-caps
                  dense
                  color="negative"
                  icon="delete"
                  label="Quitar"
                  @click="clearImage"
                />
                <div class="text-caption text-grey-6">
                  Se muestra en la web (home y servicios). Recomendado vertical u horizontal 4:3.
                </div>
              </div>
              <input
                ref="imageInput"
                type="file"
                accept="image/*"
                hidden
                @change="onImageSelected"
              />
            </div>
          </div>

          <div>
            <div class="text-caption text-grey-7 q-mb-xs">Icono</div>
            <div class="icon-picker-trigger" @click="openIconPicker">
              <div class="icon-picker-trigger__preview">
                <q-icon :name="displayIcon(form.icon)" size="32px" color="primary" />
              </div>
              <div class="icon-picker-trigger__meta">
                <div class="text-weight-medium">{{ form.icon ? displayIcon(form.icon) : 'Seleccionar icono' }}</div>
                <div class="text-caption text-grey-6">Haz clic para elegir</div>
              </div>
              <q-icon name="apps" color="grey-6" />
            </div>
          </div>

          <q-input v-model="form.summary" outlined type="textarea" label="Resumen" autogrow />
          <q-input v-model="form.description" outlined type="textarea" label="Descripción" autogrow />
          <q-input v-model.number="form.sort_order" outlined type="number" label="Orden" />
          <q-select
            v-model="form.status"
            outlined
            emit-value
            map-options
            label="Estado"
            :options="statusOptions"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn color="primary" unelevated no-caps label="Guardar" :loading="saving" @click="save" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="iconDialog" persistent>
      <q-card class="icon-picker-dialog">
        <q-card-section class="icon-picker-dialog__header row items-center no-wrap">
          <q-avatar color="primary" text-color="white" size="28px" icon="apps" />
          <div class="text-subtitle1 text-weight-bold q-ml-sm">Selecciona un icono</div>
          <q-space />
          <q-btn flat dense round icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input
            v-model="iconSearch"
            outlined
            dense
            clearable
            debounce="150"
            placeholder="Buscar icono..."
            class="q-mb-md"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
          </q-input>

          <div class="icon-picker-grid">
            <button
              v-for="icon in pagedIcons"
              :key="icon"
              type="button"
              class="icon-picker-cell"
              :class="{ 'icon-picker-cell--selected': pendingIcon === icon }"
              :title="icon"
              @click="pendingIcon = icon"
            >
              <q-icon :name="icon" size="26px" />
            </button>
          </div>

          <div v-if="!pagedIcons.length" class="text-center text-grey-6 q-pa-md">
            No hay iconos con esa búsqueda.
          </div>

          <div v-if="iconPageCount > 1" class="row justify-center q-mt-md">
            <q-pagination
              v-model="iconPage"
              :max="iconPageCount"
              direction-links
              boundary-links
              color="primary"
              size="sm"
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn
            color="primary"
            unelevated
            no-caps
            label="Usar icono"
            :disable="!pendingIcon"
            @click="confirmIcon"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import { SERVICE_ICONS, SERVICE_ICONS_PAGE_SIZE } from '@/constants/serviceIcons'
import type { Service } from '@/types'

const ICON_ALIASES: Record<string, string> = {
  'pen-tool': 'design_services',
  'hard-hat': 'construction',
  hammer: 'hardware',
  arrow: 'arrow_forward',
}

const $q = useQuasar()
const loading = ref(false)
const saving = ref(false)
const uploadingImage = ref(false)
const dialog = ref(false)
const iconDialog = ref(false)
const editingId = ref<number | null>(null)
const rows = ref<Service[]>([])
const search = ref('')
const statusFilter = ref<string | null>(null)
const iconSearch = ref('')
const iconPage = ref(1)
const pendingIcon = ref('')
const imageInput = ref<HTMLInputElement | null>(null)
const imagePreviewUrl = ref<string | null>(null)

const form = reactive({
  name: '',
  icon: '',
  summary: '',
  description: '',
  sort_order: 0,
  status: 'active',
  image_media_id: null as number | null,
})

const statusOptions = [
  { label: 'Activo', value: 'active' },
  { label: 'Inactivo', value: 'inactive' },
]

const filteredRows = computed(() => {
  const needle = search.value.trim().toLowerCase()
  return rows.value
    .filter((s) => !statusFilter.value || s.status === statusFilter.value)
    .filter((s) => {
      if (!needle) return true
      return (
        s.name.toLowerCase().includes(needle) ||
        (s.summary || '').toLowerCase().includes(needle) ||
        (s.description || '').toLowerCase().includes(needle)
      )
    })
    .slice()
    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
})

const filteredIcons = computed(() => {
  const needle = iconSearch.value.trim().toLowerCase()
  if (!needle) return SERVICE_ICONS
  return SERVICE_ICONS.filter((icon) => icon.includes(needle))
})

const iconPageCount = computed(() =>
  Math.max(1, Math.ceil(filteredIcons.value.length / SERVICE_ICONS_PAGE_SIZE)),
)

const pagedIcons = computed(() => {
  const start = (iconPage.value - 1) * SERVICE_ICONS_PAGE_SIZE
  return filteredIcons.value.slice(start, start + SERVICE_ICONS_PAGE_SIZE)
})

watch(iconSearch, () => {
  iconPage.value = 1
})

function displayIcon(value?: string | null) {
  if (!value) return 'handyman'
  return ICON_ALIASES[value] || value
}

function labelStatus(value?: string) {
  return statusOptions.find((o) => o.value === value)?.label || value || '—'
}

function resetForm() {
  Object.assign(form, {
    name: '',
    icon: 'architecture',
    summary: '',
    description: '',
    sort_order: 0,
    status: 'active',
    image_media_id: null,
  })
  imagePreviewUrl.value = null
}

function openCreate() {
  editingId.value = null
  resetForm()
  dialog.value = true
}

function openEdit(row: Service) {
  editingId.value = row.id
  Object.assign(form, {
    name: row.name,
    icon: displayIcon(row.icon),
    summary: row.summary || '',
    description: row.description || '',
    sort_order: row.sort_order ?? 0,
    status: row.status || 'active',
    image_media_id: row.image?.id ?? null,
  })
  imagePreviewUrl.value = row.image?.url || null
  dialog.value = true
}

function clearImage() {
  form.image_media_id = null
  imagePreviewUrl.value = null
}

async function onImageSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  uploadingImage.value = true
  try {
    const media = await adminApi.uploadMedia(file, 'image')
    form.image_media_id = media.id
    imagePreviewUrl.value = media.url
    $q.notify({ type: 'positive', message: 'Imagen subida' })
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo subir la imagen' })
  } finally {
    uploadingImage.value = false
    input.value = ''
  }
}

function openIconPicker() {
  pendingIcon.value = displayIcon(form.icon)
  iconSearch.value = ''
  iconPage.value = 1
  const idx = SERVICE_ICONS.indexOf(pendingIcon.value)
  if (idx >= 0) {
    iconPage.value = Math.floor(idx / SERVICE_ICONS_PAGE_SIZE) + 1
  }
  iconDialog.value = true
}

function confirmIcon() {
  if (!pendingIcon.value) return
  form.icon = pendingIcon.value
  iconDialog.value = false
}

async function load() {
  loading.value = true
  try {
    rows.value = await adminApi.services()
  } catch {
    rows.value = []
    $q.notify({ type: 'negative', message: 'No se pudieron cargar los servicios' })
  } finally {
    loading.value = false
  }
}

async function save() {
  if (!form.name) {
    $q.notify({ type: 'warning', message: 'Nombre requerido' })
    return
  }
  if (!form.image_media_id) {
    $q.notify({ type: 'warning', message: 'La imagen del servicio es requerida para la web' })
    return
  }
  saving.value = true
  try {
    const payload = {
      name: form.name,
      icon: form.icon || null,
      summary: form.summary || null,
      description: form.description || null,
      sort_order: form.sort_order ?? 0,
      status: form.status,
      image_media_id: form.image_media_id,
    }
    if (editingId.value) {
      await adminApi.updateService(editingId.value, payload)
    } else {
      await adminApi.createService(payload)
    }
    dialog.value = false
    $q.notify({ type: 'positive', message: 'Guardado' })
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar' })
  } finally {
    saving.value = false
  }
}

async function remove(id: number) {
  $q.dialog({
    title: 'Eliminar',
    message: '¿Eliminar este servicio?',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteService(id)
      $q.notify({ type: 'positive', message: 'Servicio eliminado' })
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar' })
    }
  })
}

onMounted(load)
</script>

<style scoped lang="scss">
.service-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.service-card {
  display: grid;
  grid-template-columns: minmax(200px, 1.4fr) minmax(110px, 0.6fr) minmax(200px, 1.4fr) auto auto;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1rem;
  background: #fff;
  border: 1px solid var(--ma-border);
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.service-card__identity {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.service-card__thumb {
  width: 72px;
  height: 88px;
  flex-shrink: 0;
  border-radius: 10px;
  background: #f5f2ed;
  border: 1px solid var(--ma-border);
  display: grid;
  place-items: center;
  overflow: hidden;
}

.service-card__thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.service-image-upload {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 0.85rem;
  align-items: start;
}

.service-image-upload__preview {
  width: 120px;
  height: 150px;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid var(--ma-border);
  background: #f7f4f0;
}

.service-image-upload__preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.service-image-upload__empty {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  color: #999;
  font-size: 0.75rem;
}

.service-image-upload__actions {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.45rem;
}

.service-card__titles {
  min-width: 0;
}

.service-card__name {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a1a;
  line-height: 1.25;
}

.service-card__slug {
  margin: 0.15rem 0 0;
  font-size: 0.75rem;
  color: #777;
}

.service-card__field,
.service-card__status {
  min-width: 0;
}

.service-card__label {
  display: block;
  margin-bottom: 0.3rem;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #888;
}

.service-card__value {
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

.service-card__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  min-height: 36px;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
}

.service-card__badge--active {
  background: rgba(46, 125, 50, 0.12);
  color: #2e7d32;
}

.service-card__badge--inactive {
  background: rgba(158, 158, 158, 0.18);
  color: #616161;
}

.service-card__actions {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  min-width: 118px;
}

.service-card__btn {
  min-height: 30px;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.03em;
}

.service-card__btn--danger {
  border-color: rgba(193, 0, 21, 0.35);
}

.service-card__btn :deep(.q-icon) {
  font-size: 15px;
}

@media (max-width: 1100px) {
  .service-card {
    grid-template-columns: 1fr 1fr;
    align-items: start;
  }

  .service-card__identity,
  .service-card__field--wide,
  .service-card__actions {
    grid-column: 1 / -1;
  }

  .service-card__actions {
    flex-direction: row;
  }

  .service-card__btn {
    flex: 1;
  }
}

@media (max-width: 600px) {
  .service-card {
    grid-template-columns: 1fr;
  }
}

.icon-picker-trigger {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0.9rem;
  border: 1px solid var(--ma-border);
  border-radius: 10px;
  background: #fafafa;
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.icon-picker-trigger:hover {
  border-color: var(--ma-gold);
  background: rgba(196, 164, 124, 0.08);
}

.icon-picker-trigger__preview {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  background: #fff;
  border: 1px solid var(--ma-border);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.icon-picker-trigger__meta {
  flex: 1;
  min-width: 0;
}

.icon-picker-dialog {
  width: min(560px, 94vw);
}

.icon-picker-dialog__header {
  background: #2a2a2a;
  color: #fff;
  padding: 0.85rem 1rem;
}

.icon-picker-grid {
  display: grid;
  grid-template-columns: repeat(8, minmax(0, 1fr));
  gap: 0.5rem;
}

.icon-picker-cell {
  aspect-ratio: 1;
  display: grid;
  place-items: center;
  border: 2px solid #e6e6e6;
  border-radius: 10px;
  background: #fff;
  color: #222;
  cursor: pointer;
  transition: border-color 0.12s ease, background 0.12s ease;
}

.icon-picker-cell:hover {
  border-color: var(--ma-gold);
  background: rgba(196, 164, 124, 0.08);
}

.icon-picker-cell--selected {
  border-color: var(--ma-gold);
  background: rgba(196, 164, 124, 0.16);
  box-shadow: inset 0 0 0 1px var(--ma-gold);
}

@media (max-width: 600px) {
  .icon-picker-grid {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }
}
</style>
