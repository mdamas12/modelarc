<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Solicitudes</h1>
        <p class="page-subtitle">Leads y contactos del sitio</p>
      </div>
    </div>

    <div class="admin-card q-pa-md">
      <div class="row q-col-gutter-md q-mb-md">
        <div class="col-12 col-md-4">
          <q-input
            v-model="filters.search"
            outlined
            dense
            clearable
            debounce="300"
            placeholder="Buscar..."
            @update:model-value="load"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
        <div class="col-12 col-md-3">
          <q-select
            v-model="filters.status"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Estado"
            :options="statusOptions"
            @update:model-value="load"
          />
        </div>
      </div>

      <q-table
        flat
        :rows="rows"
        :columns="columns"
        row-key="id"
        :loading="loading"
        v-model:pagination="pagination"
        @request="onRequest"
      >
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-select
              :model-value="props.row.status"
              dense
              borderless
              emit-value
              map-options
              :options="statusOptions"
              style="min-width: 140px"
              @update:model-value="(v: string) => updateStatus(props.row.id, v)"
            />
          </q-td>
        </template>
        <template #body-cell-actions="props">
          <q-td :props="props">
            <q-btn flat dense round icon="visibility" color="primary" @click="showLead(props.row)" />
            <q-btn flat dense round icon="delete" color="negative" @click="remove(props.row.id)" />
          </q-td>
        </template>
      </q-table>
    </div>

    <q-dialog v-model="detailOpen">
      <q-card style="min-width: 420px; max-width: 560px">
        <q-card-section>
          <div class="text-h6">{{ selected?.name }}</div>
          <div class="text-caption text-grey-6">{{ selected?.email }} · {{ selected?.phone || 'Sin teléfono' }}</div>
        </q-card-section>
        <q-card-section>
          <div class="q-mb-sm"><strong>Tipo:</strong> {{ selected?.project_type || '—' }}</div>
          <div class="q-mb-sm"><strong>Presupuesto:</strong> {{ selected?.budget_range || '—' }}</div>
          <div class="q-mb-sm"><strong>Estado:</strong> {{ selected?.status }}</div>
          <div class="q-mb-sm"><strong>Mensaje:</strong></div>
          <div class="text-body2" style="white-space: pre-wrap">{{ selected?.message || '—' }}</div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cerrar" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useQuasar, type QTableColumn, type QTableProps } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { Lead } from '@/types'

const $q = useQuasar()
const loading = ref(false)
const rows = ref<Lead[]>([])
const detailOpen = ref(false)
const selected = ref<Lead | null>(null)

const filters = reactive({
  search: '',
  status: null as string | null,
})

const pagination = ref({ page: 1, rowsPerPage: 20, rowsNumber: 0 })

const statusOptions = [
  { label: 'Nuevo', value: 'new' },
  { label: 'En progreso', value: 'in_progress' },
  { label: 'Cerrado', value: 'closed' },
]

const columns: QTableColumn[] = [
  { name: 'name', label: 'Nombre', field: 'name', align: 'left' },
  { name: 'email', label: 'Email', field: 'email', align: 'left' },
  { name: 'phone', label: 'Teléfono', field: 'phone', align: 'left' },
  { name: 'project_type', label: 'Tipo', field: 'project_type', align: 'left' },
  { name: 'status', label: 'Estado', field: 'status', align: 'left' },
  {
    name: 'created_at',
    label: 'Fecha',
    field: 'created_at',
    align: 'left',
    format: (v: string) => (v ? String(v).slice(0, 10) : '—'),
  },
  { name: 'actions', label: '', field: 'actions', align: 'right' },
]

async function load() {
  loading.value = true
  try {
    const res = await adminApi.leads({
      search: filters.search || undefined,
      status: filters.status || undefined,
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage,
    })
    rows.value = res.data || []
    pagination.value.rowsNumber = res.meta?.total ?? rows.value.length
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudieron cargar las solicitudes' })
  } finally {
    loading.value = false
  }
}

function onRequest(props: Parameters<NonNullable<QTableProps['onRequest']>>[0]) {
  pagination.value.page = props.pagination.page
  pagination.value.rowsPerPage = props.pagination.rowsPerPage
  void load()
}

async function updateStatus(id: number, status: string) {
  try {
    await adminApi.updateLead(id, { status })
    $q.notify({ type: 'positive', message: 'Estado actualizado' })
    await load()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al actualizar estado' })
  }
}

function showLead(lead: Lead) {
  selected.value = lead
  detailOpen.value = true
}

async function remove(id: number) {
  $q.dialog({ title: 'Eliminar', message: '¿Eliminar esta solicitud?', cancel: true }).onOk(async () => {
    try {
      await adminApi.deleteLead(id)
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar' })
    }
  })
}

onMounted(load)
</script>
