<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Categorías</h1>
        <p class="page-subtitle">
          Organiza las categorías y subcategorías usadas en proyectos y medios.
        </p>
      </div>
      <q-btn
        color="primary"
        unelevated
        no-caps
        icon="add"
        label="Nueva categoría"
        @click="openCreateCategory"
      />
    </div>

    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="42px" />
    </div>

    <div v-else-if="!categories.length" class="admin-card q-pa-xl text-center text-grey-7">
      Aún no hay categorías. Crea la primera para empezar.
    </div>

    <div v-else class="category-list">
      <article v-for="(category, index) in categories" :key="category.id" class="category-card">
        <header class="category-card__head" @click="toggle(category.id)">
          <div class="category-card__order-btns" @click.stop>
            <q-btn
              flat
              dense
              round
              size="sm"
              icon="arrow_upward"
              :disable="index === 0"
              :loading="movingCategoryId === category.id"
              @click="moveCategory(index, -1)"
            />
            <q-btn
              flat
              dense
              round
              size="sm"
              icon="arrow_downward"
              :disable="index === categories.length - 1"
              :loading="movingCategoryId === category.id"
              @click="moveCategory(index, 1)"
            />
          </div>

          <q-icon
            :name="expanded.has(category.id) ? 'expand_less' : 'expand_more'"
            size="22px"
            color="grey-7"
          />

          <div class="category-card__title">
            <h3>{{ category.name }}</h3>
            <span class="category-card__slug">/{{ category.slug }}</span>
          </div>

          <q-badge
            :color="category.published ? 'positive' : 'grey-6'"
            :label="category.published ? 'Publicada' : 'Oculta'"
          />
          <span class="category-card__count">
            {{ (category.subcategories || []).length }} subcategoría(s)
          </span>

          <q-space />

          <div class="category-card__actions" @click.stop>
            <q-btn
              flat
              dense
              no-caps
              color="primary"
              icon="add"
              label="Subcategoría"
              @click="openCreateSubcategory(category)"
            />
            <q-btn
              flat
              dense
              round
              icon="edit"
              color="primary"
              @click="openEditCategory(category)"
            >
              <q-tooltip>Editar</q-tooltip>
            </q-btn>
            <q-btn
              flat
              dense
              round
              icon="delete"
              color="negative"
              @click="removeCategory(category)"
            >
              <q-tooltip>Eliminar</q-tooltip>
            </q-btn>
          </div>
        </header>

        <div v-if="expanded.has(category.id)" class="category-card__body">
          <div v-if="!(category.subcategories || []).length" class="subcategory-empty">
            Sin subcategorías todavía.
          </div>

          <div v-else class="subcategory-list">
            <div
              v-for="(sub, sIndex) in category.subcategories"
              :key="sub.id"
              class="subcategory-row"
            >
              <div class="subcategory-row__order">
                <q-btn
                  flat
                  dense
                  round
                  size="sm"
                  icon="arrow_upward"
                  :disable="sIndex === 0"
                  :loading="movingSubcategoryId === sub.id"
                  @click="moveSubcategory(category, sIndex, -1)"
                />
                <q-btn
                  flat
                  dense
                  round
                  size="sm"
                  icon="arrow_downward"
                  :disable="sIndex === (category.subcategories?.length ?? 0) - 1"
                  :loading="movingSubcategoryId === sub.id"
                  @click="moveSubcategory(category, sIndex, 1)"
                />
              </div>
              <span class="subcategory-row__name">{{ sub.name }}</span>
              <span class="subcategory-row__slug">/{{ sub.slug }}</span>
              <q-badge
                :color="sub.published ? 'positive' : 'grey-6'"
                :label="sub.published ? 'Publicada' : 'Oculta'"
              />
              <q-space />
              <div class="subcategory-row__actions">
                <q-btn
                  flat
                  dense
                  round
                  size="sm"
                  icon="edit"
                  color="primary"
                  @click="openEditSubcategory(category, sub)"
                />
                <q-btn
                  flat
                  dense
                  round
                  size="sm"
                  icon="delete"
                  color="negative"
                  @click="removeSubcategory(sub)"
                />
              </div>
            </div>
          </div>

          <q-btn
            outline
            no-caps
            dense
            color="primary"
            icon="add"
            label="Nueva subcategoría"
            class="q-mt-md"
            @click="openCreateSubcategory(category)"
          />
        </div>
      </article>
    </div>

    <!-- Category dialog -->
    <q-dialog v-model="categoryDialog" persistent>
      <q-card style="min-width: 380px; max-width: 480px">
        <q-card-section>
          <div class="text-h6">{{ editingCategoryId ? 'Editar categoría' : 'Nueva categoría' }}</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="categoryForm.name" outlined label="Nombre *" autofocus />
          <q-input v-model.number="categoryForm.order" outlined type="number" label="Orden" />
          <q-toggle v-model="categoryForm.published" label="Publicada" color="primary" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn
            color="primary"
            unelevated
            no-caps
            label="Guardar"
            :loading="savingCategory"
            @click="saveCategory"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Subcategory dialog -->
    <q-dialog v-model="subcategoryDialog" persistent>
      <q-card style="min-width: 380px; max-width: 480px">
        <q-card-section>
          <div class="text-h6">
            {{ editingSubcategoryId ? 'Editar subcategoría' : 'Nueva subcategoría' }}
          </div>
          <div class="text-caption text-grey-7 q-mt-xs">
            Categoría: {{ activeCategory?.name }}
          </div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="subcategoryForm.name" outlined label="Nombre *" autofocus />
          <q-input v-model.number="subcategoryForm.order" outlined type="number" label="Orden" />
          <q-toggle v-model="subcategoryForm.published" label="Publicada" color="primary" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn
            color="primary"
            unelevated
            no-caps
            label="Guardar"
            :loading="savingSubcategory"
            @click="saveSubcategory"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { Category, Subcategory } from '@/types'

const $q = useQuasar()

const loading = ref(false)
const categories = ref<Category[]>([])
const expanded = ref(new Set<number>())

const movingCategoryId = ref<number | null>(null)
const movingSubcategoryId = ref<number | null>(null)

const categoryDialog = ref(false)
const savingCategory = ref(false)
const editingCategoryId = ref<number | null>(null)
const categoryForm = reactive({
  name: '',
  order: 0,
  published: true,
})

const subcategoryDialog = ref(false)
const savingSubcategory = ref(false)
const editingSubcategoryId = ref<number | null>(null)
const activeCategory = ref<Category | null>(null)
const subcategoryForm = reactive({
  name: '',
  order: 0,
  published: true,
})

function toggle(id: number) {
  if (expanded.value.has(id)) {
    expanded.value.delete(id)
  } else {
    expanded.value.add(id)
  }
  expanded.value = new Set(expanded.value)
}

async function load() {
  loading.value = true
  try {
    const rows = await adminApi.categories()
    categories.value = [...rows].sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
  } catch {
    categories.value = []
    $q.notify({ type: 'negative', message: 'No se pudieron cargar las categorías' })
  } finally {
    loading.value = false
  }
}

function openCreateCategory() {
  editingCategoryId.value = null
  Object.assign(categoryForm, {
    name: '',
    order: categories.value.length,
    published: true,
  })
  categoryDialog.value = true
}

function openEditCategory(category: Category) {
  editingCategoryId.value = category.id
  Object.assign(categoryForm, {
    name: category.name,
    order: category.order ?? 0,
    published: category.published,
  })
  categoryDialog.value = true
}

async function saveCategory() {
  if (!categoryForm.name.trim()) {
    $q.notify({ type: 'warning', message: 'El nombre es requerido' })
    return
  }
  savingCategory.value = true
  try {
    const payload = {
      name: categoryForm.name,
      order: categoryForm.order ?? 0,
      published: categoryForm.published,
    }
    if (editingCategoryId.value) {
      await adminApi.updateCategory(editingCategoryId.value, payload)
    } else {
      await adminApi.createCategory(payload)
    }
    categoryDialog.value = false
    $q.notify({ type: 'positive', message: 'Categoría guardada' })
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar la categoría' })
  } finally {
    savingCategory.value = false
  }
}

function removeCategory(category: Category) {
  $q.dialog({
    title: 'Eliminar categoría',
    message: `¿Eliminar "${category.name}"? Sus subcategorías también se eliminarán y los proyectos asociados quedarán sin categoría.`,
    cancel: { flat: true, noCaps: true, label: 'Cancelar' },
    ok: { unelevated: true, color: 'negative', noCaps: true, label: 'Eliminar' },
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteCategory(category.id)
      $q.notify({ type: 'positive', message: 'Categoría eliminada' })
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'No se pudo eliminar la categoría' })
    }
  })
}

async function moveCategory(index: number, dir: number) {
  const target = index + dir
  if (target < 0 || target >= categories.value.length) return
  const current = categories.value[index]!
  const swapWith = categories.value[target]!
  movingCategoryId.value = current.id
  try {
    const currentOrder = current.order ?? 0
    const swapOrder = swapWith.order ?? 0
    await Promise.all([
      adminApi.updateCategory(current.id, { order: swapOrder }),
      adminApi.updateCategory(swapWith.id, { order: currentOrder }),
    ])
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cambiar el orden' })
  } finally {
    movingCategoryId.value = null
  }
}

function openCreateSubcategory(category: Category) {
  activeCategory.value = category
  expanded.value = new Set([...expanded.value, category.id])
  editingSubcategoryId.value = null
  Object.assign(subcategoryForm, {
    name: '',
    order: (category.subcategories || []).length,
    published: true,
  })
  subcategoryDialog.value = true
}

function openEditSubcategory(category: Category, subcategory: Subcategory) {
  activeCategory.value = category
  editingSubcategoryId.value = subcategory.id
  Object.assign(subcategoryForm, {
    name: subcategory.name,
    order: subcategory.order ?? 0,
    published: subcategory.published,
  })
  subcategoryDialog.value = true
}

async function saveSubcategory() {
  if (!subcategoryForm.name.trim() || !activeCategory.value) {
    $q.notify({ type: 'warning', message: 'El nombre es requerido' })
    return
  }
  savingSubcategory.value = true
  try {
    const payload = {
      name: subcategoryForm.name,
      order: subcategoryForm.order ?? 0,
      published: subcategoryForm.published,
    }
    if (editingSubcategoryId.value) {
      await adminApi.updateSubcategory(editingSubcategoryId.value, payload)
    } else {
      await adminApi.createSubcategory(activeCategory.value.id, payload)
    }
    subcategoryDialog.value = false
    $q.notify({ type: 'positive', message: 'Subcategoría guardada' })
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar la subcategoría' })
  } finally {
    savingSubcategory.value = false
  }
}

function removeSubcategory(subcategory: Subcategory) {
  $q.dialog({
    title: 'Eliminar subcategoría',
    message: `¿Eliminar "${subcategory.name}"?`,
    cancel: { flat: true, noCaps: true, label: 'Cancelar' },
    ok: { unelevated: true, color: 'negative', noCaps: true, label: 'Eliminar' },
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteSubcategory(subcategory.id)
      $q.notify({ type: 'positive', message: 'Subcategoría eliminada' })
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'No se pudo eliminar la subcategoría' })
    }
  })
}

async function moveSubcategory(category: Category, index: number, dir: number) {
  const list = category.subcategories || []
  const target = index + dir
  if (target < 0 || target >= list.length) return
  const current = list[index]!
  const swapWith = list[target]!
  movingSubcategoryId.value = current.id
  try {
    const currentOrder = current.order ?? 0
    const swapOrder = swapWith.order ?? 0
    await Promise.all([
      adminApi.updateSubcategory(current.id, { order: swapOrder }),
      adminApi.updateSubcategory(swapWith.id, { order: currentOrder }),
    ])
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cambiar el orden' })
  } finally {
    movingSubcategoryId.value = null
  }
}

void load()
</script>

<style scoped lang="scss">
.category-list {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.category-card {
  background: #fff;
  border: 1px solid var(--ma-border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.category-card__head {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  cursor: pointer;
}

.category-card__order-btns {
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}

.category-card__title {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  min-width: 0;

  h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a1a;
  }
}

.category-card__slug {
  font-size: 0.78rem;
  color: #999;
}

.category-card__count {
  font-size: 0.78rem;
  color: #777;
  white-space: nowrap;
}

.category-card__actions {
  display: flex;
  gap: 0.15rem;
  flex-shrink: 0;
}

.category-card__body {
  padding: 0 1rem 1.1rem 3.5rem;
  border-top: 1px solid var(--ma-border);
  padding-top: 0.85rem;
}

.subcategory-empty {
  color: #999;
  font-size: 0.85rem;
  padding: 0.5rem 0;
}

.subcategory-list {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.subcategory-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.5rem 0.65rem;
  background: #faf9f7;
  border: 1px solid var(--ma-border);
  border-radius: 8px;
}

.subcategory-row__order {
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}

.subcategory-row__name {
  font-weight: 600;
  font-size: 0.88rem;
  color: #1a1a1a;
}

.subcategory-row__slug {
  font-size: 0.75rem;
  color: #999;
}

.subcategory-row__actions {
  display: flex;
  gap: 0.1rem;
  flex-shrink: 0;
}
</style>
