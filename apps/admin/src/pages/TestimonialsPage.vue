<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Testimonios</h1>
        <p class="page-subtitle">Registro manual o invitación por email al cliente</p>
      </div>
      <div class="row q-gutter-sm">
        <q-btn
          outline
          color="primary"
          no-caps
          icon="mail"
          label="Invitar cliente"
          @click="openInvite"
        />
        <q-btn color="primary" unelevated no-caps icon="add" label="Nuevo" @click="openCreate" />
      </div>
    </div>

    <div v-if="invitations.length" class="admin-card q-pa-md q-mb-lg">
      <div class="text-subtitle2 q-mb-sm">Invitaciones recientes</div>
      <q-list bordered separator class="rounded-borders">
        <q-item v-for="inv in invitations" :key="inv.id">
          <q-item-section>
            <q-item-label>{{ inv.client_name }} · {{ inv.client_email }}</q-item-label>
            <q-item-label caption>
              {{ inv.project_label || inv.project?.title || 'Sin proyecto' }}
              · {{ labelInviteStatus(inv.status) }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <div class="row q-gutter-xs">
              <q-btn
                v-if="inv.status === 'pending' && inv.public_url"
                flat
                dense
                no-caps
                color="primary"
                icon="content_copy"
                @click="copyUrl(inv.public_url!)"
              >
                <q-tooltip>Copiar link</q-tooltip>
              </q-btn>
              <q-btn
                v-if="inv.status === 'pending'"
                flat
                dense
                no-caps
                color="primary"
                icon="send"
                :loading="resendingId === inv.id"
                @click="resend(inv.id)"
              >
                <q-tooltip>Reenviar email</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                no-caps
                color="negative"
                icon="delete"
                @click="removeInvitation(inv.id)"
              >
                <q-tooltip>Eliminar invitación</q-tooltip>
              </q-btn>
            </div>
          </q-item-section>
        </q-item>
      </q-list>
    </div>

    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="42px" />
    </div>

    <div v-else-if="!rows.length" class="admin-card q-pa-xl text-center text-grey-7">
      No hay testimonios todavía.
    </div>

    <div v-else class="testimonial-grid">
      <article v-for="item in rows" :key="item.id" class="testimonial-card">
        <div class="testimonial-card__body">
          <div class="testimonial-card__field">
            <span class="testimonial-card__label">Cliente</span>
            <div class="testimonial-card__value testimonial-card__value--strong">
              {{ item.client_name }}
            </div>
          </div>

          <div class="testimonial-card__field">
            <span class="testimonial-card__label">Estado</span>
            <div
              class="testimonial-card__value"
              :class="
                item.status === 'active'
                  ? 'testimonial-card__value--active'
                  : 'testimonial-card__value--muted'
              "
            >
              {{ labelStatus(item.status) }}
            </div>
          </div>

          <div class="testimonial-card__field">
            <span class="testimonial-card__label">Valoración</span>
            <div class="testimonial-card__value">
              <q-rating :model-value="item.rating || 0" readonly size="20px" color="primary" />
            </div>
          </div>

          <div class="testimonial-card__field">
            <span class="testimonial-card__label">Orden</span>
            <div class="testimonial-card__value testimonial-card__value--accent">
              {{ item.sort_order ?? 0 }}
            </div>
          </div>

          <div class="testimonial-card__field testimonial-card__field--full">
            <span class="testimonial-card__label">Proyecto</span>
            <div class="testimonial-card__value">
              {{
                item.project?.title ||
                item.project_label ||
                (item.project_id ? `#${item.project_id}` : '—')
              }}
            </div>
          </div>

          <div class="testimonial-card__field testimonial-card__field--full">
            <span class="testimonial-card__label">Cita</span>
            <div class="testimonial-card__value testimonial-card__quote">
              {{ item.quote }}
            </div>
          </div>
        </div>

        <div class="testimonial-card__actions">
          <q-btn
            unelevated
            no-caps
            class="testimonial-card__btn"
            icon="edit"
            label="Editar"
            @click="openEdit(item)"
          />
          <q-btn
            unelevated
            no-caps
            class="testimonial-card__btn testimonial-card__btn--danger"
            icon="delete"
            label="Eliminar"
            @click="remove(item.id)"
          />
        </div>
      </article>
    </div>

    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 440px">
        <q-card-section>
          <div class="text-h6">{{ editingId ? 'Editar testimonio' : 'Nuevo testimonio' }}</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="form.client_name" outlined label="Cliente *" />
          <q-input v-model="form.quote" outlined type="textarea" label="Cita *" autogrow />
          <div>
            <div class="text-caption q-mb-xs">Valoración</div>
            <q-rating v-model="form.rating" size="28px" color="primary" />
          </div>
          <q-input
            v-model="form.project_label"
            outlined
            label="Proyecto *"
            hint="Escribe el nombre del proyecto o referencia, ej. 'Reforma integral · Providencia'"
          />
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

    <q-dialog v-model="inviteDialog" persistent>
      <q-card style="min-width: 440px">
        <q-card-section>
          <div class="text-h6">Invitar cliente a valorar</div>
          <div class="text-caption text-grey-7">
            Se enviará un email elegante con un enlace único para dejar su testimonio.
          </div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input
            v-model="inviteForm.project_label"
            outlined
            label="Proyecto *"
            hint="Escribe el nombre del proyecto"
          />
          <q-input v-model="inviteForm.client_name" outlined label="Nombre del cliente *" />
          <q-input v-model="inviteForm.client_email" outlined type="email" label="Email del cliente *" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn
            color="primary"
            unelevated
            no-caps
            icon="send"
            label="Enviar invitación"
            :loading="inviting"
            @click="sendInvite"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { Testimonial } from '@/types'

type InvitationRow = {
  id: number
  token?: string
  client_name: string
  client_email?: string
  status: string
  project_label?: string | null
  project?: { id: number; title: string } | null
  public_url?: string
}

const $q = useQuasar()
const loading = ref(false)
const saving = ref(false)
const inviting = ref(false)
const dialog = ref(false)
const inviteDialog = ref(false)
const editingId = ref<number | null>(null)
const resendingId = ref<number | null>(null)
const rows = ref<Testimonial[]>([])
const invitations = ref<InvitationRow[]>([])

const form = reactive({
  client_name: '',
  quote: '',
  rating: 5,
  project_id: null as number | null,
  project_label: '',
  sort_order: 0,
  status: 'active',
})

const inviteForm = reactive({
  project_label: '',
  client_name: '',
  client_email: '',
})

const statusOptions = [
  { label: 'Activo', value: 'active' },
  { label: 'Inactivo', value: 'inactive' },
]

function labelStatus(value?: string) {
  return statusOptions.find((o) => o.value === value)?.label || value || '—'
}

function labelInviteStatus(value?: string) {
  if (value === 'pending') return 'Pendiente'
  if (value === 'completed') return 'Completada'
  if (value === 'cancelled') return 'Cancelada'
  return value || '—'
}

function resetForm() {
  Object.assign(form, {
    client_name: '',
    quote: '',
    rating: 5,
    project_id: null,
    project_label: '',
    sort_order: 0,
    status: 'active',
  })
}

function resetInviteForm() {
  Object.assign(inviteForm, {
    project_label: '',
    client_name: '',
    client_email: '',
  })
}

function openCreate() {
  editingId.value = null
  resetForm()
  dialog.value = true
}

function openInvite() {
  resetInviteForm()
  inviteDialog.value = true
}

function openEdit(row: Testimonial) {
  editingId.value = row.id
  Object.assign(form, {
    client_name: row.client_name,
    quote: row.quote,
    rating: row.rating ?? 5,
    project_id: null,
    project_label: row.project_label || row.project?.title || '',
    sort_order: row.sort_order ?? 0,
    status: row.status || 'active',
  })
  dialog.value = true
}

async function loadInvitations() {
  try {
    const res = await adminApi.testimonialInvitations({ per_page: 10 })
    invitations.value = res.data || []
  } catch {
    invitations.value = []
  }
}

async function load() {
  loading.value = true
  try {
    rows.value = await adminApi.testimonials()
    await loadInvitations()
  } catch {
    rows.value = []
    $q.notify({ type: 'negative', message: 'No se pudieron cargar los testimonios' })
  } finally {
    loading.value = false
  }
}

async function sendInvite() {
  if (!inviteForm.project_label.trim() || !inviteForm.client_name.trim() || !inviteForm.client_email.trim()) {
    $q.notify({ type: 'warning', message: 'Proyecto, nombre y email son requeridos' })
    return
  }
  inviting.value = true
  try {
    const res = await adminApi.createTestimonialInvitation({
      project_label: inviteForm.project_label.trim(),
      client_name: inviteForm.client_name.trim(),
      client_email: inviteForm.client_email.trim(),
    })
    inviteDialog.value = false
    const url = res.meta?.public_url || res.data?.public_url
    if (res.meta?.mail_sent) {
      $q.notify({
        type: 'positive',
        message: 'La invitación ha sido enviada',
      })
    } else {
      $q.notify({
        type: 'warning',
        message: 'Invitación creada, pero el email no se pudo enviar (SMTP)',
        caption: url
          ? 'Usa el botón de copiar link en Invitaciones recientes'
          : 'Revisa usuario/contraseña de Titan en el .env',
        timeout: 8000,
      })
    }
    await loadInvitations()
  } catch {
    $q.notify({
      type: 'negative',
      message: 'No se pudo crear la invitación.',
    })
  } finally {
    inviting.value = false
  }
}

async function resend(id: number) {
  resendingId.value = id
  try {
    const res = await adminApi.resendTestimonialInvitation(id)
    if (res.meta?.mail_sent) {
      $q.notify({ type: 'positive', message: 'Email reenviado' })
    } else {
      $q.notify({
        type: 'warning',
        message: 'No se pudo reenviar el email',
        caption: res.meta?.public_url || undefined,
        timeout: 6000,
      })
    }
    await loadInvitations()
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo reenviar' })
  } finally {
    resendingId.value = null
  }
}

async function copyUrl(url: string) {
  try {
    await navigator.clipboard.writeText(url)
    $q.notify({ type: 'positive', message: 'Link copiado' })
  } catch {
    $q.notify({ type: 'warning', message: url })
  }
}

async function removeInvitation(id: number) {
  $q.dialog({
    title: 'Eliminar invitación',
    message: '¿Eliminar esta invitación? El link dejará de funcionar.',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteTestimonialInvitation(id)
      $q.notify({ type: 'positive', message: 'Invitación eliminada' })
      await loadInvitations()
    } catch {
      $q.notify({ type: 'negative', message: 'No se pudo eliminar la invitación' })
    }
  })
}

async function save() {
  if (!form.client_name || !form.quote || !form.project_label.trim()) {
    $q.notify({ type: 'warning', message: 'Cliente, proyecto y cita son requeridos' })
    return
  }
  saving.value = true
  try {
    const payload = {
      client_name: form.client_name,
      quote: form.quote,
      rating: form.rating,
      project_id: null,
      project_label: form.project_label.trim(),
      sort_order: form.sort_order,
      status: form.status,
    }
    if (editingId.value) {
      await adminApi.updateTestimonial(editingId.value, payload)
    } else {
      await adminApi.createTestimonial(payload)
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
    message: '¿Eliminar este testimonio?',
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await adminApi.deleteTestimonial(id)
      $q.notify({ type: 'positive', message: 'Testimonio eliminado' })
      await load()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar' })
    }
  })
}

onMounted(load)
</script>

<style scoped lang="scss">
.testimonial-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.testimonial-card {
  background: #fff;
  border: 1px solid var(--ma-border);
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.testimonial-card__body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem 1.25rem;
  padding: 1.15rem 1.2rem 0.85rem;
}

.testimonial-card__field--full {
  grid-column: 1 / -1;
}

.testimonial-card__label {
  display: block;
  margin-bottom: 0.35rem;
  font-size: 0.72rem;
  font-weight: 500;
  color: #9a9a9a;
}

.testimonial-card__value {
  font-size: 0.95rem;
  color: #333;
  line-height: 1.35;
  word-break: break-word;
}

.testimonial-card__value--strong {
  font-weight: 700;
  color: #1a1a1a;
}

.testimonial-card__value--accent {
  font-weight: 700;
  color: var(--ma-gold, #c4a47c);
}

.testimonial-card__value--active {
  font-weight: 700;
  color: #2e7d32;
}

.testimonial-card__value--muted {
  font-weight: 600;
  color: #757575;
}

.testimonial-card__quote {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
  color: #555;
  font-size: 0.9rem;
}

.testimonial-card__actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
  padding: 0.85rem 1.2rem 1.1rem;
  border-top: 1px solid #f0f0f0;
  margin-top: auto;
}

.testimonial-card__btn {
  min-height: 38px;
  border-radius: 8px;
  background: #f3f3f3;
  color: #444;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.testimonial-card__btn--danger {
  color: #c62828;
}

.testimonial-card__btn :deep(.q-icon) {
  font-size: 16px;
}

@media (max-width: 900px) {
  .testimonial-grid {
    grid-template-columns: 1fr;
  }
}
</style>
