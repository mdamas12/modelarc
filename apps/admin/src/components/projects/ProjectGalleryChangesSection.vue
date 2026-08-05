<template>
  <section class="admin-card gc-section">
    <div class="gc-section__head">
      <div>
        <h2 class="gc-section__title">Antes y después</h2>
        <p class="gc-section__lead">
          Comparaciones por ambiente. Solo las destacadas se publican en la web.
        </p>
      </div>
      <div class="gc-section__actions">
        <q-btn
          outline
          no-caps
          color="grey-8"
          icon="fullscreen"
          label="Pantalla completa"
          :disable="!items.length"
          @click="openFullscreen(0)"
        />
        <q-btn
          unelevated
          no-caps
          color="primary"
          icon="add"
          label="Nueva comparación"
          @click="openCreate"
        />
      </div>
    </div>

    <div v-if="loading" class="flex flex-center q-pa-lg">
      <q-spinner color="primary" size="36px" />
    </div>

    <div v-else-if="!items.length" class="gc-section__empty">
      Aún no hay comparaciones. Agrega un antes + diseño y/o después.
    </div>

    <div v-else class="gc-list">
      <article v-for="(item, index) in items" :key="item.id" class="gc-card">
        <button type="button" class="gc-card__preview" @click="openFullscreen(index)">
          <div class="gc-card__half">
            <img :src="beforeUrl(item)" alt="Antes" loading="lazy" />
            <span class="gc-card__tag">Antes</span>
          </div>
          <div class="gc-card__half">
            <img :src="compareUrl(item)" :alt="item.compare_label || 'Después'" loading="lazy" />
            <span class="gc-card__tag">{{ item.compare_label || 'Después' }}</span>
          </div>
          <span class="gc-card__divider" aria-hidden="true" />
        </button>

        <div class="gc-card__body">
          <div class="gc-card__meta">
            <h3 class="gc-card__title">
              {{ item.title || labelSubcategory(category, item.subcategory) || 'Sin título' }}
            </h3>
            <p class="gc-card__sub">
              {{ labelSubcategory(category, item.subcategory) }}
              · Compara con {{ item.compare_label || item.compare_with }}
            </p>
            <p v-if="item.description" class="gc-card__desc">{{ item.description }}</p>
            <div class="gc-card__badges">
              <q-badge v-if="item.is_featured" color="primary">Destacada (web)</q-badge>
              <q-badge v-else color="grey-6">Solo admin</q-badge>
              <q-badge v-if="item.design_media_id || item.design_media" outline color="grey-7">
                Diseño
              </q-badge>
              <q-badge v-if="item.after_media_id || item.after_media" outline color="grey-7">
                Después
              </q-badge>
            </div>
          </div>

          <div class="gc-card__controls">
            <q-btn dense flat round icon="fullscreen" @click="openFullscreen(index)">
              <q-tooltip>Vista completa</q-tooltip>
            </q-btn>
            <q-btn dense flat round icon="arrow_upward" :disable="index === 0" @click="move(index, -1)">
              <q-tooltip>Subir</q-tooltip>
            </q-btn>
            <q-btn
              dense
              flat
              round
              icon="arrow_downward"
              :disable="index === items.length - 1"
              @click="move(index, 1)"
            >
              <q-tooltip>Bajar</q-tooltip>
            </q-btn>
            <q-btn dense flat round icon="edit" color="primary" @click="openEdit(item)" />
            <q-btn dense flat round icon="delete" color="negative" @click="confirmDelete(item)" />
          </div>
        </div>
      </article>
    </div>

    <!-- Create / Edit dialog -->
    <q-dialog v-model="formOpen" persistent>
      <q-card class="gc-form-dialog">
        <header class="gc-form-dialog__header">
          <div>
            <h3 class="gc-form-dialog__title">
              {{ editingId ? 'Editar comparación' : 'Nueva comparación' }}
            </h3>
            <p class="gc-form-dialog__lead">
              Sube Antes y al menos Diseño o Después para el slider.
            </p>
          </div>
          <q-btn flat dense round icon="close" :disable="uploading" v-close-popup />
        </header>

        <div class="gc-form-dialog__body">
          <div class="gc-uploads">
            <div
              v-for="slot in uploadSlots"
              :key="slot.key"
              class="gc-upload"
            >
              <div class="gc-upload__label">
                {{ slot.label }}<span v-if="slot.required"> *</span>
              </div>
              <button
                type="button"
                class="gc-upload__box"
                :class="{
                  'gc-upload__box--filled': !!slotPreview(slot.key),
                  'gc-upload__box--busy': uploadingSlot === slot.key,
                }"
                :disabled="uploading"
                @click="pickSlot(slot.key)"
              >
                <img
                  v-if="slotPreview(slot.key)"
                  :src="slotPreview(slot.key)"
                  :alt="slot.label"
                />
                <template v-else>
                  <q-icon :name="slot.icon" size="28px" color="grey-6" />
                  <span>Subir</span>
                </template>

                <div v-if="uploadingSlot === slot.key" class="gc-upload__overlay">
                  <q-spinner color="white" size="28px" />
                  <span>Subiendo…</span>
                </div>

                <q-btn
                  v-if="slotPreview(slot.key) && !slot.required && uploadingSlot !== slot.key"
                  dense
                  flat
                  round
                  size="sm"
                  icon="close"
                  class="gc-upload__clear"
                  :disable="uploading"
                  @click.stop="clearSlot(slot.key as 'design' | 'after')"
                />
              </button>
            </div>
          </div>
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            hidden
            @change="onFilePicked"
          />

          <div class="gc-form-dialog__compare">
            <q-option-group
              v-model="form.compare_with"
              :options="compareOptions"
              color="primary"
              type="radio"
              inline
              :disable="compareOptions.length < 2 || uploading"
            />
            <p class="gc-form-dialog__hint">
              El lado izquierdo del slider siempre es Antes. Elige qué imagen va a la derecha.
            </p>
          </div>

          <div class="gc-form-dialog__fields">
            <q-select
              v-model="form.subcategory"
              outlined
              dense
              emit-value
              map-options
              clearable
              label="Subcategoría"
              :options="subcategoryOptions"
              :disable="uploading"
            />
            <q-input
              v-model="form.title"
              outlined
              dense
              label="Título (opcional)"
              :disable="uploading"
            />
            <q-input
              v-model="form.description"
              class="gc-form-dialog__full"
              outlined
              dense
              type="textarea"
              autogrow
              label="Descripción"
              :disable="uploading"
            />
            <q-toggle
              v-model="form.is_featured"
              class="gc-form-dialog__full"
              color="primary"
              label="Destacada en la web"
              :disable="uploading"
            />
          </div>
        </div>

        <footer class="gc-form-dialog__footer">
          <q-btn flat no-caps label="Cancelar" :disable="uploading" v-close-popup />
          <q-btn
            unelevated
            no-caps
            color="primary"
            label="Guardar"
            :loading="saving"
            :disable="!canSave || uploading"
            @click="saveForm"
          />
        </footer>
      </q-card>
    </q-dialog>

    <!-- Fullscreen preview -->
    <q-dialog v-model="fullscreenOpen" maximized transition-show="fade" transition-hide="fade">
      <q-card class="gc-fullscreen">
        <div class="gc-fullscreen__bar">
          <div>
            <div class="gc-fullscreen__title">
              {{ activeItem?.title || labelSubcategory(category, activeItem?.subcategory) || 'Comparación' }}
            </div>
            <div class="gc-fullscreen__sub">
              {{ activeIndex + 1 }} / {{ items.length }}
              · {{ activeItem?.compare_label || 'Después' }}
            </div>
          </div>
          <div class="row items-center q-gutter-sm">
            <q-btn
              flat
              round
              color="white"
              icon="chevron_left"
              :disable="activeIndex <= 0"
              @click="activeIndex--"
            />
            <q-btn
              flat
              round
              color="white"
              icon="chevron_right"
              :disable="activeIndex >= items.length - 1"
              @click="activeIndex++"
            />
            <q-btn flat round color="white" icon="close" v-close-popup />
          </div>
        </div>

        <div v-if="activeItem" class="gc-fullscreen__stage">
          <AdminBeforeAfterSlider
            :key="activeItem.id"
            :before-image="beforeUrl(activeItem)"
            :after-image="compareUrl(activeItem)"
            :after-label="activeItem.compare_label || 'Después'"
          />
        </div>
      </q-card>
    </q-dialog>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useQuasar } from 'quasar'
import AdminBeforeAfterSlider from '@/components/projects/AdminBeforeAfterSlider.vue'
import { adminApi } from '@/services/adminApi'
import { labelSubcategory, subcategoriesFor } from '@/constants/mediaTaxonomy'
import type { GalleryChange } from '@/types'

const props = defineProps<{
  projectId: number | string
  category: string
}>()

const $q = useQuasar()
const placeholder = 'https://placehold.co/800x500/1a1a1a/c4a47c?text=Imagen'

type UploadSlotKey = 'before' | 'design' | 'after'

const loading = ref(false)
const saving = ref(false)
const uploadingSlot = ref<UploadSlotKey | null>(null)
const uploading = computed(() => uploadingSlot.value !== null)
const items = ref<GalleryChange[]>([])
const formOpen = ref(false)
const fullscreenOpen = ref(false)
const activeIndex = ref(0)
const editingId = ref<number | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const uploadSlot = ref<UploadSlotKey>('before')
const localPreviewUrls = ref<string[]>([])

const uploadSlots: { key: UploadSlotKey; label: string; icon: string; required?: boolean }[] = [
  { key: 'before', label: 'Antes', icon: 'image', required: true },
  { key: 'design', label: 'Diseño', icon: 'architecture' },
  { key: 'after', label: 'Después', icon: 'check_circle' },
]

const form = reactive({
  before_media_id: null as number | null,
  design_media_id: null as number | null,
  after_media_id: null as number | null,
  beforePreview: '' as string,
  designPreview: '' as string,
  afterPreview: '' as string,
  compare_with: 'after' as 'design' | 'after',
  subcategory: null as string | null,
  title: '',
  description: '',
  is_featured: false,
})

const subcategoryOptions = computed(() => subcategoriesFor(props.category))

const compareOptions = computed(() => {
  const opts: { label: string; value: 'design' | 'after' }[] = []
  if (form.design_media_id) opts.push({ label: 'Comparar con Diseño', value: 'design' })
  if (form.after_media_id) opts.push({ label: 'Comparar con Después', value: 'after' })
  return opts
})

const canSave = computed(
  () =>
    Boolean(form.before_media_id) &&
    Boolean(form.design_media_id || form.after_media_id) &&
    compareOptions.value.some((o) => o.value === form.compare_with),
)

const activeItem = computed(() => items.value[activeIndex.value] || null)

function slotPreview(slot: UploadSlotKey) {
  if (slot === 'before') return form.beforePreview
  if (slot === 'design') return form.designPreview
  return form.afterPreview
}

function beforeUrl(item: GalleryChange) {
  return (
    item.before_image_url ||
    item.before_media?.url ||
    placeholder
  )
}

function compareUrl(item: GalleryChange) {
  return (
    item.comparison_image_url ||
    item.comparison_media?.url ||
    (item.compare_with === 'design'
      ? item.design_media?.url
      : item.after_media?.url) ||
    item.after_media?.url ||
    item.design_media?.url ||
    placeholder
  )
}

function revokeLocalPreviews() {
  for (const url of localPreviewUrls.value) URL.revokeObjectURL(url)
  localPreviewUrls.value = []
}

function apiErrorMessage(error: unknown, fallback: string) {
  const data = (error as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })
    ?.response?.data
  if (data?.errors) {
    const first = Object.values(data.errors)[0]?.[0]
    if (first) return first
  }
  if (data?.message) return data.message
  return fallback
}

watch(compareOptions, (opts) => {
  if (!opts.length) return
  if (!opts.some((o) => o.value === form.compare_with)) {
    form.compare_with = opts[0]!.value
  }
})

async function load() {
  loading.value = true
  try {
    items.value = await adminApi.galleryChanges(props.projectId)
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudieron cargar las comparaciones' })
  } finally {
    loading.value = false
  }
}

function resetForm() {
  revokeLocalPreviews()
  editingId.value = null
  uploadingSlot.value = null
  form.before_media_id = null
  form.design_media_id = null
  form.after_media_id = null
  form.beforePreview = ''
  form.designPreview = ''
  form.afterPreview = ''
  form.compare_with = 'after'
  form.subcategory = null
  form.title = ''
  form.description = ''
  form.is_featured = false
}

function openCreate() {
  resetForm()
  formOpen.value = true
}

function openEdit(item: GalleryChange) {
  editingId.value = item.id
  form.before_media_id = item.before_media_id
  form.design_media_id = item.design_media_id ?? null
  form.after_media_id = item.after_media_id ?? null
  form.beforePreview = item.before_media?.url || ''
  form.designPreview = item.design_media?.url || ''
  form.afterPreview = item.after_media?.url || ''
  form.compare_with = item.compare_with
  form.subcategory = item.subcategory ?? null
  form.title = item.title || ''
  form.description = item.description || ''
  form.is_featured = Boolean(item.is_featured)
  formOpen.value = true
}

function pickSlot(slot: UploadSlotKey) {
  if (uploading.value) return
  uploadSlot.value = slot
  fileInput.value?.click()
}

function clearSlot(slot: 'design' | 'after') {
  if (uploading.value) return
  if (slot === 'design') {
    form.design_media_id = null
    form.designPreview = ''
  } else {
    form.after_media_id = null
    form.afterPreview = ''
  }
}

function setSlotPreview(slot: UploadSlotKey, url: string) {
  if (slot === 'before') form.beforePreview = url
  else if (slot === 'design') form.designPreview = url
  else form.afterPreview = url
}

function setSlotMedia(slot: UploadSlotKey, id: number, url: string) {
  if (slot === 'before') {
    form.before_media_id = id
    form.beforePreview = url
  } else if (slot === 'design') {
    form.design_media_id = id
    form.designPreview = url
  } else {
    form.after_media_id = id
    form.afterPreview = url
  }
}

async function onFilePicked(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file) return

  if (!file.type.startsWith('image/')) {
    $q.notify({ type: 'negative', message: 'Selecciona un archivo de imagen válido' })
    return
  }

  const slot = uploadSlot.value
  const previousPreview = slotPreview(slot)
  const previousId =
    slot === 'before'
      ? form.before_media_id
      : slot === 'design'
        ? form.design_media_id
        : form.after_media_id

  const localUrl = URL.createObjectURL(file)
  localPreviewUrls.value.push(localUrl)
  setSlotPreview(slot, localUrl)
  uploadingSlot.value = slot

  try {
    const media = await adminApi.uploadMedia(file, 'image', {
      category: props.category,
      subcategory: form.subcategory,
      is_published: true,
    })
    setSlotMedia(slot, media.id, media.url)
    $q.notify({ type: 'positive', message: 'Imagen subida' })
  } catch (error) {
    if (slot === 'before') {
      form.before_media_id = previousId
      form.beforePreview = previousPreview
    } else if (slot === 'design') {
      form.design_media_id = previousId
      form.designPreview = previousPreview
    } else {
      form.after_media_id = previousId
      form.afterPreview = previousPreview
    }
    $q.notify({
      type: 'negative',
      message: apiErrorMessage(error, 'Error al subir la imagen. Intenta de nuevo.'),
      timeout: 4500,
    })
  } finally {
    uploadingSlot.value = null
  }
}

async function saveForm() {
  if (!canSave.value) return
  saving.value = true
  try {
    const payload = {
      before_media_id: form.before_media_id,
      design_media_id: form.design_media_id,
      after_media_id: form.after_media_id,
      compare_with: form.compare_with,
      subcategory: form.subcategory,
      title: form.title || null,
      description: form.description || null,
      is_featured: form.is_featured,
    }
    if (editingId.value) {
      await adminApi.updateGalleryChange(props.projectId, editingId.value, payload)
      $q.notify({ type: 'positive', message: 'Comparación actualizada' })
    } else {
      await adminApi.createGalleryChange(props.projectId, payload)
      $q.notify({ type: 'positive', message: 'Comparación creada' })
    }
    formOpen.value = false
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo guardar la comparación' })
  } finally {
    saving.value = false
  }
}

function confirmDelete(item: GalleryChange) {
  $q.dialog({
    title: 'Eliminar comparación',
    message: '¿Seguro que deseas eliminar esta comparación?',
    cancel: { flat: true, noCaps: true, label: 'Cancelar' },
    ok: { unelevated: true, color: 'negative', noCaps: true, label: 'Eliminar' },
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteGalleryChange(props.projectId, item.id)
      $q.notify({ type: 'positive', message: 'Comparación eliminada' })
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'No se pudo eliminar' })
    }
  })
}

async function move(index: number, dir: number) {
  const target = index + dir
  if (target < 0 || target >= items.value.length) return
  const copy = [...items.value]
  const [row] = copy.splice(index, 1)
  copy.splice(target, 0, row!)
  items.value = copy
  try {
    await adminApi.reorderGalleryChanges(
      props.projectId,
      copy.map((i) => i.id),
    )
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo reordenar' })
    await load()
  }
}

function openFullscreen(index: number) {
  if (!items.value.length) return
  activeIndex.value = Math.min(Math.max(index, 0), items.value.length - 1)
  fullscreenOpen.value = true
}

watch(
  () => props.projectId,
  () => {
    void load()
  },
)

watch(formOpen, (open) => {
  if (!open) {
    uploadingSlot.value = null
    revokeLocalPreviews()
  }
})

onMounted(load)

defineExpose({ reload: load })
</script>

<style scoped lang="scss">
.gc-section {
  padding: 1.35rem 1.5rem 1.5rem;
}

.gc-section__head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}

.gc-section__title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
}

.gc-section__lead {
  margin: 0.25rem 0 0;
  color: #6b6560;
  font-size: 0.9rem;
}

.gc-section__actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.gc-section__empty {
  padding: 2.25rem 1.25rem;
  text-align: center;
  color: #8a8580;
  border: 1px dashed rgba(26, 26, 26, 0.14);
  border-radius: 12px;
  background: #faf8f5;
}

.gc-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.gc-card {
  display: grid;
  grid-template-columns: minmax(240px, 280px) 1fr;
  gap: 1.15rem;
  padding: 1rem;
  border: 1px solid rgba(26, 26, 26, 0.08);
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.gc-card__preview {
  position: relative;
  display: grid;
  grid-template-columns: 1fr 1fr;
  height: 148px;
  border: 0;
  padding: 0;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  background: #1a1a1a;
}

.gc-card__half {
  position: relative;
  min-width: 0;
  overflow: hidden;
}

.gc-card__half img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.gc-card__tag {
  position: absolute;
  bottom: 0.5rem;
  left: 0.5rem;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  background: rgba(0, 0, 0, 0.68);
  color: #fff;
}

.gc-card__half:last-of-type .gc-card__tag {
  left: auto;
  right: 0.5rem;
}

.gc-card__divider {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 50%;
  width: 2px;
  transform: translateX(-50%);
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.15);
  pointer-events: none;
}

.gc-card__body {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  min-width: 0;
  padding: 0.15rem 0.15rem 0.15rem 0;
}

.gc-card__meta {
  min-width: 0;
  flex: 1;
}

.gc-card__title {
  margin: 0;
  font-size: 1.08rem;
  font-weight: 700;
  line-height: 1.3;
}

.gc-card__sub {
  margin: 0.35rem 0 0;
  font-size: 0.84rem;
  color: #6b6560;
}

.gc-card__desc {
  margin: 0.55rem 0 0;
  font-size: 0.9rem;
  color: #333;
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.gc-card__badges {
  margin-top: 0.75rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.gc-card__controls {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  flex-shrink: 0;
  padding-left: 0.25rem;
}

.gc-form-dialog {
  width: min(720px, 94vw);
  max-width: 94vw;
  border-radius: 14px;
  overflow: hidden;
}

.gc-form-dialog__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.35rem 1.5rem 0.85rem;
  border-bottom: 1px solid rgba(26, 26, 26, 0.06);
}

.gc-form-dialog__title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  line-height: 1.3;
}

.gc-form-dialog__lead {
  margin: 0.3rem 0 0;
  font-size: 0.86rem;
  color: #6b6560;
}

.gc-form-dialog__body {
  padding: 1.25rem 1.5rem 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.gc-form-dialog__compare {
  padding: 0.85rem 1rem;
  border-radius: 10px;
  background: #f7f5f2;
  border: 1px solid rgba(26, 26, 26, 0.06);
}

.gc-form-dialog__hint {
  margin: 0.45rem 0 0;
  font-size: 0.78rem;
  color: #6b6560;
  line-height: 1.45;
}

.gc-form-dialog__fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem 1rem;
}

.gc-form-dialog__full {
  grid-column: 1 / -1;
}

.gc-form-dialog__footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem 1.25rem;
  border-top: 1px solid rgba(26, 26, 26, 0.06);
  background: #faf8f5;
}

.gc-uploads {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.85rem;
}

.gc-upload__label {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #8a8580;
  margin-bottom: 0.4rem;
}

.gc-upload__box {
  position: relative;
  width: 100%;
  height: 128px;
  border: 1.5px dashed rgba(26, 26, 26, 0.22);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  cursor: pointer;
  background: #fff;
  color: #8a8580;
  font-size: 0.8rem;
  overflow: hidden;
  padding: 0;
  transition: border-color 0.15s ease, background 0.15s ease;
}

.gc-upload__box:hover:not(:disabled) {
  border-color: rgba(196, 164, 124, 0.75);
  background: rgba(196, 164, 124, 0.06);
}

.gc-upload__box:disabled {
  cursor: wait;
}

.gc-upload__box--filled {
  border-style: solid;
  border-color: rgba(26, 26, 26, 0.1);
}

.gc-upload__box--busy {
  border-color: rgba(196, 164, 124, 0.8);
}

.gc-upload__box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.gc-upload__overlay {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  background: rgba(17, 17, 17, 0.62);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.gc-upload__clear {
  position: absolute;
  top: 0.35rem;
  right: 0.35rem;
  background: rgba(255, 255, 255, 0.92);
  z-index: 1;
}

.gc-fullscreen {
  background: #0e0e0e;
  color: #fff;
  display: flex;
  flex-direction: column;
}

.gc-fullscreen__bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.gc-fullscreen__title {
  font-size: 1.1rem;
  font-weight: 700;
}

.gc-fullscreen__sub {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.65);
  margin-top: 0.15rem;
}

.gc-fullscreen__stage {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  min-height: 0;
}

@media (max-width: 700px) {
  .gc-section {
    padding: 1.15rem;
  }

  .gc-card {
    grid-template-columns: 1fr;
    padding: 0.9rem;
  }

  .gc-card__preview {
    height: 160px;
  }

  .gc-card__body {
    padding: 0;
  }

  .gc-card__controls {
    flex-direction: row;
    justify-content: flex-end;
    padding-left: 0;
  }

  .gc-uploads,
  .gc-form-dialog__fields {
    grid-template-columns: 1fr;
  }

  .gc-form-dialog__header,
  .gc-form-dialog__body,
  .gc-form-dialog__footer {
    padding-left: 1.15rem;
    padding-right: 1.15rem;
  }
}
</style>
