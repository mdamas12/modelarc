<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Resumen general de Modelarc</p>
      </div>
      <q-chip v-if="loadError" dense outline color="negative" icon="error_outline">
        No se pudo cargar el dashboard
      </q-chip>
    </div>

    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="42px" />
    </div>

    <template v-else>
      <div class="row q-col-gutter-md q-mb-md">
        <div v-for="kpi in kpis" :key="kpi.label" class="col-12 col-sm-6 col-md-4 col-lg">
          <div class="admin-card kpi-card">
            <div class="row items-start justify-between">
              <div>
                <div class="kpi-label">{{ kpi.label }}</div>
                <div class="kpi-value">{{ kpi.value }}</div>
              </div>
              <div class="kpi-icon">
                <q-icon :name="kpi.icon" size="22px" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row q-col-gutter-md q-mb-md">
        <div class="col-12">
          <div class="admin-card q-pa-md analytics-pending">
            <div class="text-weight-medium q-mb-xs">Analítica de visitas</div>
            <p class="text-grey-7 q-mb-none">
              Analytics pendiente de configuración. Las métricas de visitas y almacenamiento
              se habilitarán cuando exista tracking real (Fase posterior).
            </p>
          </div>
        </div>
      </div>

      <div class="row q-col-gutter-md">
        <div class="col-12 col-lg-7">
          <div class="admin-card q-pa-md">
            <div class="row items-center justify-between q-mb-md">
              <div class="text-weight-medium">Proyectos recientes</div>
              <q-btn flat dense no-caps color="primary" label="Ver todos" to="/proyectos" />
            </div>
            <q-table
              flat
              :rows="recentProjects"
              :columns="projectColumns"
              row-key="id"
              hide-pagination
              :pagination="{ rowsPerPage: 0 }"
              no-data-label="Sin proyectos recientes"
            >
              <template #body-cell-publication_status="props">
                <q-td :props="props">
                  <q-badge
                    :color="statusColor(props.row.publication_status)"
                    :label="props.row.publication_status || '—'"
                    class="status-chip"
                  />
                </q-td>
              </template>
            </q-table>
          </div>
        </div>

        <div class="col-12 col-lg-5">
          <div class="admin-card q-pa-md q-mb-md">
            <div class="text-weight-medium q-mb-md">Actividad reciente</div>
            <q-timeline v-if="activity.length" color="primary" dense>
              <q-timeline-entry
                v-for="item in activity"
                :key="item.id"
                :title="item.title"
                :subtitle="item.time"
                icon="circle"
              >
                {{ item.description }}
              </q-timeline-entry>
            </q-timeline>
            <p v-else class="text-grey-6 q-mb-none">Sin actividad registrada.</p>
          </div>

          <div class="admin-card q-pa-md">
            <div class="text-weight-medium q-mb-md">Proyectos más vistos</div>
            <template v-if="topProjects.length">
              <div v-for="(p, i) in topProjects" :key="p.name" class="q-mb-sm">
                <div class="row items-center justify-between q-mb-xs">
                  <span>{{ i + 1 }}. {{ p.name }}</span>
                  <span class="text-grey-6 text-caption">{{ p.views }} vistas</span>
                </div>
                <q-linear-progress
                  :value="p.views / maxTopViews"
                  color="primary"
                  track-color="grey-3"
                  rounded
                  size="6px"
                />
              </div>
              <p class="text-caption text-grey-6 q-mb-none q-mt-sm">
                Contador de vistas del proyecto (no visita única).
              </p>
            </template>
            <p v-else class="text-grey-6 q-mb-none">Aún no hay vistas registradas.</p>
          </div>
        </div>
      </div>
    </template>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import type { QTableColumn } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { DashboardData } from '@/types'

const loading = ref(true)
const loadError = ref(false)
const data = ref<DashboardData | null>(null)

const kpis = computed(() => {
  const d = data.value
  return [
    { label: 'Proyectos', value: d?.projects_total ?? '—', icon: 'apartment' },
    {
      label: 'Tours 360°',
      value: d?.tours_published ?? d?.projects_with_tour ?? '—',
      icon: 'threesixty',
    },
    { label: 'Solicitudes', value: d?.leads_total ?? '—', icon: 'mail_outline' },
    { label: 'Servicios', value: d?.services_active ?? '—', icon: 'handyman' },
    { label: 'Testimonios', value: d?.testimonials_active ?? '—', icon: 'format_quote' },
  ]
})

const recentProjects = computed(() => data.value?.recent_projects || [])
const activity = computed(() => data.value?.activity || [])
const topProjects = computed(() => data.value?.top_projects || [])
const maxTopViews = computed(() => Math.max(...topProjects.value.map((p) => p.views), 1))

const projectColumns: QTableColumn[] = [
  { name: 'title', label: 'Proyecto', field: 'title', align: 'left' },
  { name: 'category', label: 'Categoría', field: 'category', align: 'left' },
  { name: 'publication_status', label: 'Estado', field: 'publication_status', align: 'left' },
  {
    name: 'created_at',
    label: 'Creado',
    field: 'created_at',
    align: 'left',
    format: (v: string) => (v ? String(v).slice(0, 10) : '—'),
  },
]

function statusColor(status?: string) {
  if (status === 'published') return 'positive'
  if (status === 'archived') return 'grey'
  return 'warning'
}

onMounted(async () => {
  loading.value = true
  loadError.value = false
  try {
    data.value = await adminApi.dashboard()
  } catch {
    data.value = null
    loadError.value = true
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.analytics-pending {
  border: 1px dashed rgba(0, 0, 0, 0.12);
  background: rgba(196, 164, 124, 0.06);
}
</style>
