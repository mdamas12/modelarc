<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Usuarios</h1>
        <p class="page-subtitle">Invita colaboradores y gestiona el acceso al panel</p>
      </div>
      <q-btn
        color="primary"
        unelevated
        no-caps
        icon="person_add"
        label="Invitar usuario"
        @click="openCreate"
      />
    </div>

    <div class="admin-card q-pa-md">
      <div class="row q-col-gutter-md q-mb-md">
        <div class="col-12 col-md-6">
          <q-input
            v-model="search"
            outlined
            dense
            clearable
            debounce="300"
            label="Buscar por nombre o email"
            @update:model-value="load"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
        <div class="col-12 col-md-4">
          <q-select
            v-model="statusFilter"
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
        hide-pagination
        :pagination="{ rowsPerPage: 0 }"
      >
        <template #body-cell-roles="props">
          <q-td :props="props">
            <q-chip
              v-for="role in props.row.roles || []"
              :key="role"
              dense
              size="sm"
              color="primary"
              text-color="white"
              :label="role"
            />
            <span v-if="!(props.row.roles || []).length">—</span>
          </q-td>
        </template>
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-badge :color="statusColor(props.row.status)" :label="statusLabel(props.row.status)" />
          </q-td>
        </template>
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row q-gutter-sm no-wrap">
              <q-btn
                outline
                dense
                no-caps
                color="grey-8"
                label="Editar"
                @click="openEdit(props.row)"
              />
              <q-btn
                v-if="props.row.status === 'pending'"
                outline
                dense
                no-caps
                color="primary"
                label="Reenviar"
                :loading="busyId === props.row.id"
                @click="resendActivation(props.row)"
              />
              <q-btn
                v-else-if="props.row.status === 'blocked'"
                outline
                dense
                no-caps
                color="positive"
                label="Activar"
                :loading="busyId === props.row.id"
                @click="unblockUser(props.row)"
              />
              <q-btn
                v-else
                outline
                dense
                no-caps
                color="negative"
                label="Bloquear"
                :loading="busyId === props.row.id"
                @click="openBlock(props.row)"
              />
            </div>
          </q-td>
        </template>
      </q-table>
    </div>

    <q-dialog v-model="formDialog" persistent>
      <q-card style="min-width: 420px; max-width: 520px">
        <q-card-section>
          <div class="text-h6">{{ editing ? 'Editar usuario' : 'Invitar usuario' }}</div>
          <p v-if="!editing" class="text-grey-7 q-mb-none q-mt-sm" style="font-size: 0.9rem">
            Se enviará un email de bienvenida para que active su cuenta y cree su contraseña.
          </p>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="form.name" outlined label="Nombre *" />
          <q-input v-model="form.email" outlined type="email" label="Email *" />
          <q-select
            v-model="form.role"
            outlined
            :options="roleOptions"
            label="Rol *"
            emit-value
            map-options
          />
        </q-card-section>
        <q-card-section v-if="editing" class="q-pt-none">
          <q-btn
            outline
            no-caps
            color="primary"
            icon="lock_reset"
            label="Restablecer contraseña"
            class="full-width"
            :disable="editTarget?.status !== 'active'"
            :loading="resetting"
            @click="sendReset"
          />
          <p
            v-if="editTarget?.status !== 'active'"
            class="text-caption text-grey-6 q-mb-none q-mt-sm"
          >
            Solo cuentas activas pueden recibir un restablecimiento de contraseña.
          </p>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn
            color="primary"
            unelevated
            no-caps
            :label="editing ? 'Guardar' : 'Enviar invitación'"
            :loading="saving"
            @click="saveUser"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="blockDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Bloquear usuario</div>
        </q-card-section>
        <q-card-section>
          <p class="q-mb-none text-grey-7">
            ¿Bloquear a <strong>{{ blockTarget?.name }}</strong>?<br />
            No podrá iniciar sesión hasta que lo reactives.
          </p>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn
            color="negative"
            outline
            no-caps
            label="Bloquear"
            :loading="busyId === blockTarget?.id"
            @click="confirmBlock"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useQuasar, type QTableColumn } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { User } from '@/types'

const $q = useQuasar()
const loading = ref(false)
const saving = ref(false)
const resetting = ref(false)
const busyId = ref<number | null>(null)
const rows = ref<User[]>([])
const search = ref('')
const statusFilter = ref<string | null>(null)
const formDialog = ref(false)
const blockDialog = ref(false)
const editing = ref(false)
const editTarget = ref<User | null>(null)
const blockTarget = ref<User | null>(null)

const form = reactive({
  name: '',
  email: '',
  role: 'editor',
})

const roleOptions = [
  { label: 'admin', value: 'admin' },
  { label: 'editor', value: 'editor' },
]

const statusOptions = [
  { label: 'Pendiente', value: 'pending' },
  { label: 'Activo', value: 'active' },
  { label: 'Bloqueado', value: 'blocked' },
]

const columns: QTableColumn[] = [
  { name: 'name', label: 'Nombre', field: 'name', align: 'left' },
  { name: 'email', label: 'Email', field: 'email', align: 'left' },
  { name: 'roles', label: 'Roles', field: 'roles', align: 'left' },
  { name: 'status', label: 'Estado', field: 'status', align: 'left' },
  {
    name: 'last_login_at',
    label: 'Último acceso',
    field: 'last_login_at',
    align: 'left',
    format: (v: string | null) => (v ? String(v).slice(0, 16).replace('T', ' ') : '—'),
  },
  { name: 'actions', label: 'Acciones', field: 'id', align: 'left' },
]

onMounted(load)

async function load() {
  loading.value = true
  try {
    const res = await adminApi.users({
      search: search.value || undefined,
      status: statusFilter.value || undefined,
      per_page: 100,
    })
    rows.value = res.data || []
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cargar usuarios.' })
  } finally {
    loading.value = false
  }
}

function statusColor(status?: string) {
  if (status === 'active') return 'positive'
  if (status === 'pending') return 'warning'
  return 'grey'
}

function statusLabel(status?: string) {
  if (status === 'active') return 'active'
  if (status === 'pending') return 'pending'
  if (status === 'blocked') return 'blocked'
  return status || '—'
}

function openCreate() {
  editing.value = false
  editTarget.value = null
  form.name = ''
  form.email = ''
  form.role = 'editor'
  formDialog.value = true
}

function openEdit(row: User) {
  editing.value = true
  editTarget.value = row
  form.name = row.name
  form.email = row.email
  form.role = row.roles?.[0] || 'admin'
  formDialog.value = true
}

function openBlock(row: User) {
  blockTarget.value = row
  blockDialog.value = true
}

async function saveUser() {
  if (!form.name.trim() || !form.email.trim()) {
    $q.notify({ type: 'warning', message: 'Completa nombre y email.' })
    return
  }

  saving.value = true
  try {
    if (editing.value && editTarget.value) {
      await adminApi.updateUser(editTarget.value.id, {
        name: form.name.trim(),
        email: form.email.trim(),
        role: form.role,
      })
      $q.notify({ type: 'positive', message: 'Usuario actualizado.' })
    } else {
      const res = await adminApi.inviteUser({
        name: form.name.trim(),
        email: form.email.trim(),
        role: form.role,
      })
      $q.notify({
        type: res.meta?.mail_sent ? 'positive' : 'warning',
        message: res.meta?.message || 'Invitación procesada.',
      })
    }
    formDialog.value = false
    await load()
  } catch (e: unknown) {
    const msg =
      (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })
        ?.response?.data?.errors?.email?.[0] ||
      (e as { response?: { data?: { message?: string } } })?.response?.data?.message ||
      'No se pudo guardar el usuario.'
    $q.notify({ type: 'negative', message: msg })
  } finally {
    saving.value = false
  }
}

async function sendReset() {
  if (!editTarget.value) return
  resetting.value = true
  try {
    const res = await adminApi.resetUserPassword(editTarget.value.id)
    $q.notify({
      type: res.meta?.mail_sent ? 'positive' : 'warning',
      message: res.meta?.message || 'Solicitud enviada.',
    })
  } catch (e: unknown) {
    const msg =
      (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })
        ?.response?.data?.errors?.user?.[0] ||
      (e as { response?: { data?: { message?: string } } })?.response?.data?.message ||
      'No se pudo enviar el restablecimiento.'
    $q.notify({ type: 'negative', message: msg })
  } finally {
    resetting.value = false
  }
}

async function resendActivation(row: User) {
  busyId.value = row.id
  try {
    const res = await adminApi.resendUserActivation(row.id)
    $q.notify({
      type: res.meta?.mail_sent ? 'positive' : 'warning',
      message: res.meta?.message || 'Reenvío procesado.',
    })
  } catch (e: unknown) {
    const msg =
      (e as { response?: { data?: { message?: string } } })?.response?.data?.message ||
      'No se pudo reenviar la activación.'
    $q.notify({ type: 'negative', message: msg })
  } finally {
    busyId.value = null
  }
}

async function confirmBlock() {
  if (!blockTarget.value) return
  busyId.value = blockTarget.value.id
  try {
    await adminApi.blockUser(blockTarget.value.id)
    $q.notify({ type: 'positive', message: 'Usuario bloqueado.' })
    blockDialog.value = false
    await load()
  } catch (e: unknown) {
    const msg =
      (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })
        ?.response?.data?.errors?.user?.[0] ||
      (e as { response?: { data?: { message?: string } } })?.response?.data?.message ||
      'No se pudo bloquear.'
    $q.notify({ type: 'negative', message: msg })
  } finally {
    busyId.value = null
  }
}

async function unblockUser(row: User) {
  busyId.value = row.id
  try {
    await adminApi.unblockUser(row.id)
    $q.notify({ type: 'positive', message: 'Usuario reactivado.' })
    await load()
  } catch (e: unknown) {
    const msg =
      (e as { response?: { data?: { message?: string } } })?.response?.data?.message ||
      'No se pudo reactivar.'
    $q.notify({ type: 'negative', message: msg })
  } finally {
    busyId.value = null
  }
}
</script>
