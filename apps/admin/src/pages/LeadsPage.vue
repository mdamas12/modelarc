<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Solicitudes</h1>
        <p class="page-subtitle">Leads y contactos del sitio</p>
      </div>
    </div>

    <div class="admin-card q-pa-md">
      <div class="row q-col-gutter-md q-mb-md items-center">
        <div class="col-12 col-md-5">
          <q-input
            v-model="filters.search"
            outlined
            dense
            clearable
            debounce="300"
            placeholder="Buscar..."
            bg-color="white"
            @update:model-value="reloadFirstPage"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
        <div class="col-6 col-md-3">
          <q-select
            v-model="filters.status"
            outlined
            dense
            clearable
            emit-value
            map-options
            label="Estado"
            bg-color="white"
            :options="statusOptions"
            @update:model-value="reloadFirstPage"
          />
        </div>
      </div>

      <div v-if="loading" class="flex flex-center q-pa-xl">
        <q-spinner color="primary" size="42px" />
      </div>

      <div v-else-if="!rows.length" class="text-grey-7 text-center q-pa-xl">
        No hay solicitudes con estos filtros.
      </div>

      <div v-else class="lead-list">
        <article v-for="lead in rows" :key="lead.id" class="lead-card">
          <div class="lead-card__identity">
            <div class="lead-card__thumb">
              <q-icon name="person" size="26px" color="primary" />
            </div>
            <div class="lead-card__titles">
              <h3 class="lead-card__name">{{ lead.name }}</h3>
              <p class="lead-card__slug">{{ lead.email }}</p>
            </div>
          </div>

          <div class="lead-card__field">
            <span class="lead-card__label">Teléfono</span>
            <div class="lead-card__value">
              <q-icon name="phone" size="16px" />
              <span>{{ lead.phone || '—' }}</span>
            </div>
          </div>

          <div class="lead-card__field">
            <span class="lead-card__label">Tipo</span>
            <div class="lead-card__value">
              <q-icon name="category" size="16px" />
              <span class="ellipsis">{{ lead.project_type || '—' }}</span>
            </div>
          </div>

          <div class="lead-card__field">
            <span class="lead-card__label">Fecha</span>
            <div class="lead-card__value">
              <q-icon name="event" size="16px" />
              <span>{{ formatDate(lead.created_at) }}</span>
            </div>
          </div>

          <div class="lead-card__status">
            <span class="lead-card__label">Estado</span>
            <q-select
              :model-value="lead.status"
              dense
              outlined
              emit-value
              map-options
              bg-color="white"
              :options="statusOptions"
              class="lead-card__select"
              @update:model-value="(v: string) => updateStatus(lead.id, v)"
            />
          </div>

          <div class="lead-card__actions">
            <q-btn
              unelevated
              no-caps
              dense
              color="primary"
              class="lead-card__btn"
              icon="visibility"
              label="Ver"
              @click="showLead(lead)"
            />
            <q-btn
              outline
              no-caps
              dense
              color="negative"
              class="lead-card__btn lead-card__btn--danger"
              icon="delete"
              label="Eliminar"
              @click="remove(lead.id)"
            />
          </div>
        </article>
      </div>

      <div v-if="pagination.rowsNumber > pagination.rowsPerPage" class="row justify-end q-mt-md">
        <q-pagination
          v-model="pagination.page"
          :max="pageCount"
          direction-links
          boundary-links
          color="primary"
          @update:model-value="load"
        />
      </div>
    </div>

    <q-dialog v-model="detailOpen">
      <q-card v-if="selected" class="lead-detail">
        <header class="lead-detail__header">
          <div class="lead-detail__avatar" aria-hidden="true">
            <q-icon name="person" size="28px" />
          </div>
          <div class="lead-detail__intro">
            <p class="lead-detail__eyebrow">Solicitud de contacto</p>
            <h2 class="lead-detail__title">{{ selected.name }}</h2>
            <div class="lead-detail__contacts">
              <a :href="`mailto:${selected.email}`" class="lead-detail__chip">
                <q-icon name="email" size="15px" />
                <span>{{ selected.email }}</span>
              </a>
              <a
                v-if="selected.phone"
                :href="`tel:${selected.phone}`"
                class="lead-detail__chip"
              >
                <q-icon name="phone" size="15px" />
                <span>{{ selected.phone }}</span>
              </a>
            </div>
          </div>
          <q-btn
            flat
            dense
            round
            icon="close"
            class="lead-detail__close"
            aria-label="Cerrar"
            v-close-popup
          />
        </header>

        <section class="lead-detail__body">
          <div class="lead-detail__meta">
            <div class="lead-detail__meta-item">
              <span class="lead-detail__meta-label">Tipo</span>
              <span class="lead-detail__meta-value">
                <q-icon name="category" size="16px" />
                {{ selected.project_type || '—' }}
              </span>
            </div>
            <div class="lead-detail__meta-item">
              <span class="lead-detail__meta-label">Presupuesto</span>
              <span class="lead-detail__meta-value">
                <q-icon name="payments" size="16px" />
                {{ selected.budget_range || '—' }}
              </span>
            </div>
            <div class="lead-detail__meta-item">
              <span class="lead-detail__meta-label">Estado</span>
              <span
                class="lead-detail__badge"
                :class="`lead-detail__badge--${selected.status || 'new'}`"
              >
                {{ labelStatus(selected.status) }}
              </span>
            </div>
            <div class="lead-detail__meta-item">
              <span class="lead-detail__meta-label">Fecha</span>
              <span class="lead-detail__meta-value">
                <q-icon name="event" size="16px" />
                {{ formatDate(selected.created_at) }}
              </span>
            </div>
          </div>

          <div class="lead-detail__message">
            <div class="lead-detail__message-head">
              <q-icon name="chat_bubble_outline" size="18px" />
              <span>Mensaje</span>
            </div>
            <p>{{ selected.message || 'Sin mensaje.' }}</p>
          </div>
        </section>

        <footer class="lead-detail__footer">
          <q-btn flat no-caps label="Cerrar" color="grey-8" v-close-popup />
          <q-btn
            unelevated
            no-caps
            color="primary"
            icon="mail"
            label="Responder"
            :href="`mailto:${selected.email}`"
            type="a"
          />
        </footer>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useQuasar } from 'quasar'
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

const pageCount = computed(() =>
  Math.max(1, Math.ceil(pagination.value.rowsNumber / pagination.value.rowsPerPage)),
)

function formatDate(value?: string | null) {
  if (!value) return '—'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return '—'
  const dd = String(d.getDate()).padStart(2, '0')
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const yyyy = d.getFullYear()
  return `${dd}/${mm}/${yyyy}`
}

function labelStatus(value?: string | null) {
  return statusOptions.find((o) => o.value === value)?.label || value || '—'
}

function reloadFirstPage() {
  pagination.value.page = 1
  void load()
}

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

<style scoped lang="scss">
.lead-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.lead-card {
  display: grid;
  grid-template-columns:
    minmax(180px, 1.3fr)
    minmax(120px, 0.8fr)
    minmax(120px, 0.9fr)
    minmax(110px, 0.7fr)
    minmax(140px, 0.9fr)
    auto;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1rem;
  background: #fff;
  border: 1px solid var(--ma-border);
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.lead-card__identity {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.lead-card__thumb {
  width: 56px;
  height: 56px;
  flex-shrink: 0;
  border-radius: 10px;
  background: #f5f2ed;
  border: 1px solid var(--ma-border);
  display: grid;
  place-items: center;
}

.lead-card__titles {
  min-width: 0;
}

.lead-card__name {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a1a;
}

.lead-card__slug {
  margin: 0.15rem 0 0;
  font-size: 0.75rem;
  color: #777;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.lead-card__field,
.lead-card__status {
  min-width: 0;
}

.lead-card__label {
  display: block;
  margin-bottom: 0.3rem;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #888;
}

.lead-card__value {
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

.lead-card__select {
  width: 100%;
}

.lead-card__actions {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  min-width: 118px;
}

.lead-card__btn {
  min-height: 30px;
  font-size: 0.72rem;
  font-weight: 600;
}

.lead-card__btn--danger {
  border-color: rgba(193, 0, 21, 0.35);
}

.ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 1200px) {
  .lead-card {
    grid-template-columns: 1fr 1fr;
    align-items: start;
  }

  .lead-card__identity,
  .lead-card__actions {
    grid-column: 1 / -1;
  }

  .lead-card__actions {
    flex-direction: row;
  }

  .lead-card__btn {
    flex: 1;
  }
}

@media (max-width: 600px) {
  .lead-card {
    grid-template-columns: 1fr;
  }
}

.lead-detail {
  width: min(560px, 94vw);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
}

.lead-detail__header {
  position: relative;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  padding: 1.35rem 1.35rem 1.2rem;
  background:
    linear-gradient(135deg, rgba(196, 164, 124, 0.18), transparent 55%),
    #1a1a1a;
  color: #f7f4f0;
}

.lead-detail__avatar {
  width: 56px;
  height: 56px;
  flex-shrink: 0;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: rgba(196, 164, 124, 0.18);
  border: 1px solid rgba(196, 164, 124, 0.4);
  color: #c4a47c;
}

.lead-detail__intro {
  min-width: 0;
  flex: 1;
  padding-right: 1.75rem;
}

.lead-detail__eyebrow {
  margin: 0 0 0.35rem;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #c4a47c;
}

.lead-detail__title {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
  line-height: 1.25;
  color: #fff;
}

.lead-detail__contacts {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
  margin-top: 0.75rem;
}

.lead-detail__chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  max-width: 100%;
  padding: 0.3rem 0.65rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(247, 244, 240, 0.9);
  font-size: 0.78rem;
  text-decoration: none;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.lead-detail__chip span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.lead-detail__chip:hover {
  background: rgba(196, 164, 124, 0.2);
  border-color: rgba(196, 164, 124, 0.45);
  color: #fff;
}

.lead-detail__close {
  position: absolute;
  top: 0.65rem;
  right: 0.65rem;
  color: rgba(247, 244, 240, 0.75);
}

.lead-detail__body {
  padding: 1.25rem 1.35rem 0.35rem;
  background: #faf8f5;
}

.lead-detail__meta {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.lead-detail__meta-item {
  padding: 0.75rem 0.85rem;
  border-radius: 10px;
  background: #fff;
  border: 1px solid rgba(26, 26, 26, 0.08);
}

.lead-detail__meta-label {
  display: block;
  margin-bottom: 0.35rem;
  font-size: 0.66rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #8a8580;
}

.lead-detail__meta-value {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1a1a1a;
}

.lead-detail__badge {
  display: inline-flex;
  align-items: center;
  min-height: 28px;
  padding: 0.2rem 0.7rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
}

.lead-detail__badge--new {
  background: rgba(196, 164, 124, 0.18);
  color: #8a6a3d;
}

.lead-detail__badge--in_progress {
  background: rgba(25, 118, 210, 0.12);
  color: #1565c0;
}

.lead-detail__badge--closed {
  background: rgba(46, 125, 50, 0.12);
  color: #2e7d32;
}

.lead-detail__message {
  margin-top: 0.85rem;
  padding: 1rem 1.05rem;
  border-radius: 12px;
  background: #fff;
  border: 1px solid rgba(26, 26, 26, 0.08);
}

.lead-detail__message-head {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 0.55rem;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #8a8580;
}

.lead-detail__message p {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.65;
  color: #333;
  white-space: pre-wrap;
}

.lead-detail__footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.5rem;
  padding: 0.9rem 1.35rem 1.15rem;
  background: #faf8f5;
}

@media (max-width: 520px) {
  .lead-detail__meta {
    grid-template-columns: 1fr;
  }

  .lead-detail__footer {
    flex-direction: column-reverse;
    align-items: stretch;
  }
}
</style>
