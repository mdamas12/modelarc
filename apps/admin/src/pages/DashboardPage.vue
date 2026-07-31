<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Resumen general de Modelarc</p>
      </div>
      <q-chip v-if="usingMock" dense outline color="warning" icon="info">
        Datos de demostración
      </q-chip>
    </div>

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
      <div class="col-12 col-lg-8">
        <div class="admin-card q-pa-md" style="height: 100%">
          <div class="text-weight-medium q-mb-md">Visitas del sitio</div>
          <apexchart
            type="area"
            height="280"
            :options="visitsChartOptions"
            :series="visitsSeries"
          />
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="admin-card q-pa-md" style="height: 100%">
          <div class="text-weight-medium q-mb-md">Almacenamiento</div>
          <apexchart
            type="donut"
            height="280"
            :options="storageChartOptions"
            :series="storageSeries"
          />
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
          <q-timeline color="primary" dense>
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
        </div>

        <div class="admin-card q-pa-md">
          <div class="text-weight-medium q-mb-md">Top proyectos</div>
          <div v-for="(p, i) in topProjects" :key="p.name" class="q-mb-sm">
            <div class="row items-center justify-between q-mb-xs">
              <span>{{ i + 1 }}. {{ p.name }}</span>
              <span class="text-grey-6 text-caption">{{ p.views }} visitas</span>
            </div>
            <q-linear-progress
              :value="p.views / maxTopViews"
              color="primary"
              track-color="grey-3"
              rounded
              size="6px"
            />
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import type { QTableColumn } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { DashboardData, Project } from '@/types'

const usingMock = ref(false)
const data = ref<DashboardData | null>(null)

const mockDashboard: DashboardData = {
  projects_total: 24,
  projects_published: 18,
  projects_featured: 6,
  projects_with_tour: 12,
  tours_published: 12,
  services_active: 5,
  testimonials_active: 8,
  leads_total: 47,
  leads_new: 9,
  leads_by_status: { new: 9, in_progress: 14, closed: 24 },
  projects_by_publication: { draft: 4, published: 18, archived: 2 },
  recent_leads: [],
  recent_projects: [
    { id: 1, title: 'Residencia Valle Verde', slug: 'valle-verde', category: 'residencial', publication_status: 'published', created_at: '2026-07-10' },
    { id: 2, title: 'Torre Corporativa Norte', slug: 'torre-norte', category: 'corporativo', publication_status: 'published', created_at: '2026-07-08' },
    { id: 3, title: 'Plaza Comercial Sol', slug: 'plaza-sol', category: 'comercial', publication_status: 'draft', created_at: '2026-07-05' },
    { id: 4, title: 'Casa Bosque Azul', slug: 'bosque-azul', category: 'residencial', publication_status: 'published', created_at: '2026-07-01' },
  ],
  visits_total: 12840,
  storage_used_gb: 42.5,
  storage_total_gb: 100,
  storage_breakdown: [
    { label: 'Imágenes', value: 22 },
    { label: 'Panoramas', value: 14 },
    { label: 'Videos', value: 4.5 },
    { label: 'Otros', value: 2 },
  ],
  activity: [
    { id: 1, title: 'Nuevo proyecto', description: 'Se creó Residencia Valle Verde', time: 'Hace 2 h' },
    { id: 2, title: 'Solicitud recibida', description: 'Lead de María López — presupuesto medio', time: 'Hace 4 h' },
    { id: 3, title: 'Tour publicado', description: 'Recorrido 360° de Torre Norte', time: 'Ayer' },
    { id: 4, title: 'Medios subidos', description: '8 imágenes a Plaza Comercial Sol', time: 'Hace 2 días' },
  ],
  chart_visits: {
    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
    data: [420, 510, 480, 620, 710, 390, 450],
  },
  top_projects: [
    { name: 'Residencia Valle Verde', views: 1840 },
    { name: 'Torre Corporativa Norte', views: 1520 },
    { name: 'Casa Bosque Azul', views: 980 },
    { name: 'Plaza Comercial Sol', views: 740 },
  ],
}

const kpis = computed(() => {
  const d = data.value || mockDashboard
  return [
    { label: 'Proyectos', value: d.projects_total, icon: 'apartment' },
    { label: 'Tours 360°', value: d.tours_published || d.projects_with_tour, icon: 'threesixty' },
    { label: 'Solicitudes', value: d.leads_total, icon: 'mail_outline' },
    { label: 'Visitas', value: d.visits_total ?? '—', icon: 'visibility' },
    {
      label: 'Almacenamiento',
      value: d.storage_used_gb != null ? `${d.storage_used_gb} GB` : '—',
      icon: 'cloud',
    },
  ]
})

const recentProjects = computed(() => data.value?.recent_projects || [])
const activity = computed(() => data.value?.activity || mockDashboard.activity || [])
const topProjects = computed(() => data.value?.top_projects || mockDashboard.top_projects || [])
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

const visitsLabels = computed(
  () => data.value?.chart_visits?.labels || mockDashboard.chart_visits!.labels,
)
const visitsSeries = computed(() => [
  {
    name: 'Visitas',
    data: data.value?.chart_visits?.data || mockDashboard.chart_visits!.data,
  },
])

const visitsChartOptions = computed(() => ({
  chart: { toolbar: { show: false }, zoom: { enabled: false } },
  colors: ['#C4A47C'],
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 },
  },
  xaxis: { categories: visitsLabels.value },
  yaxis: { labels: { style: { colors: '#6b6b6b' } } },
  grid: { borderColor: '#eee' },
  tooltip: { theme: 'light' },
}))

const storageSeries = computed(() => {
  const breakdown = data.value?.storage_breakdown || mockDashboard.storage_breakdown || []
  return breakdown.map((b) => b.value)
})

const storageChartOptions = computed(() => {
  const breakdown = data.value?.storage_breakdown || mockDashboard.storage_breakdown || []
  return {
    labels: breakdown.map((b) => b.label),
    colors: ['#C4A47C', '#8B7355', '#D4C4A8', '#A09080'],
    legend: { position: 'bottom' },
    dataLabels: { enabled: false },
    plotOptions: {
      pie: {
        donut: {
          size: '65%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Usado',
              formatter: () => `${data.value?.storage_used_gb ?? mockDashboard.storage_used_gb} GB`,
            },
          },
        },
      },
    },
  }
})

function statusColor(status?: string) {
  if (status === 'published') return 'positive'
  if (status === 'archived') return 'grey'
  return 'warning'
}

function enrichDashboard(apiData: DashboardData): DashboardData {
  const topProjects =
    apiData.top_projects ??
    (apiData.recent_projects || []).slice(0, 4).map((p: Project, i: number) => ({
      name: p.title,
      views: (p.views_count || 800) - i * 120,
    }))

  return {
    ...mockDashboard,
    ...apiData,
    visits_total: apiData.visits_total ?? mockDashboard.visits_total!,
    storage_used_gb: apiData.storage_used_gb ?? mockDashboard.storage_used_gb!,
    storage_total_gb: apiData.storage_total_gb ?? mockDashboard.storage_total_gb!,
    storage_breakdown: apiData.storage_breakdown ?? mockDashboard.storage_breakdown!,
    activity: apiData.activity ?? mockDashboard.activity!,
    chart_visits: apiData.chart_visits ?? mockDashboard.chart_visits!,
    top_projects: topProjects,
  }
}

onMounted(async () => {
  try {
    const result = await adminApi.dashboard()
    data.value = enrichDashboard(result)
    usingMock.value = false
  } catch {
    data.value = mockDashboard
    usingMock.value = true
  }
})
</script>
