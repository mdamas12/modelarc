<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Configuración</h1>
        <p class="page-subtitle">Ajustes del sitio (clave / valor)</p>
      </div>
      <q-btn color="primary" unelevated no-caps icon="add" label="Nueva clave" @click="openCreate" />
    </div>

    <div class="admin-card q-pa-md">
      <q-table flat :rows="rows" :columns="columns" row-key="id" :loading="loading" hide-pagination :pagination="{ rowsPerPage: 0 }">
        <template #body-cell-value="props">
          <q-td :props="props">
            <code class="text-caption">{{ formatValue(props.row.value) }}</code>
          </q-td>
        </template>
        <template #body-cell-actions="props">
          <q-td :props="props" class="q-gutter-xs">
            <q-btn flat dense round icon="edit" color="primary" @click="openEdit(props.row)" />
            <q-btn flat dense round icon="delete" color="negative" @click="remove(props.row.id)" />
          </q-td>
        </template>
      </q-table>
    </div>

    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 420px">
        <q-card-section>
          <div class="text-h6">{{ editing ? 'Editar ajuste' : 'Nuevo ajuste' }}</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="form.key" outlined label="Clave *" :disable="editing" />
          <q-input v-model="form.value" outlined type="textarea" label="Valor" autogrow />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn color="primary" unelevated no-caps label="Guardar" :loading="saving" @click="save" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useQuasar, type QTableColumn } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { SiteSetting } from '@/types'

const $q = useQuasar()
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const editing = ref(false)
const rows = ref<SiteSetting[]>([])

const form = reactive({ key: '', value: '' })

const columns: QTableColumn[] = [
  { name: 'key', label: 'Clave', field: 'key', align: 'left' },
  { name: 'value', label: 'Valor', field: 'value', align: 'left' },
  {
    name: 'updated_at',
    label: 'Actualizado',
    field: 'updated_at',
    align: 'left',
    format: (v: string) => (v ? String(v).slice(0, 16).replace('T', ' ') : '—'),
  },
  { name: 'actions', label: '', field: 'actions', align: 'right' },
]

function formatValue(value: unknown) {
  if (value == null) return '—'
  if (typeof value === 'string') return value
  try {
    return JSON.stringify(value)
  } catch {
    return String(value)
  }
}

function openCreate() {
  editing.value = false
  form.key = ''
  form.value = ''
  dialog.value = true
}

function openEdit(row: SiteSetting) {
  editing.value = true
  form.key = row.key
  form.value = typeof row.value === 'string' ? row.value : JSON.stringify(row.value ?? '')
  dialog.value = true
}

async function load() {
  loading.value = true
  try {
    rows.value = await adminApi.settings()
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cargar la configuración' })
  } finally {
    loading.value = false
  }
}

async function save() {
  if (!form.key) {
    $q.notify({ type: 'warning', message: 'Clave requerida' })
    return
  }
  saving.value = true
  try {
    let value: unknown = form.value
    try {
      value = JSON.parse(form.value)
    } catch {
      // keep as string
    }
    await adminApi.upsertSetting(form.key, value)
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
  $q.dialog({ title: 'Eliminar', message: '¿Eliminar este ajuste?', cancel: true }).onOk(async () => {
    try {
      await adminApi.deleteSetting(id)
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar' })
    }
  })
}

onMounted(load)
</script>
