<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Usuarios</h1>
        <p class="page-subtitle">Administradores del panel</p>
      </div>
      <q-btn
        color="primary"
        unelevated
        no-caps
        icon="person_add"
        label="Nuevo usuario"
        @click="openCreate"
      />
    </div>

    <div class="admin-card q-pa-md">
      <q-banner class="bg-grey-2 q-mb-md" rounded dense>
        La API aún no persiste altas/ediciones. La interfaz ya está lista para el flujo del manual;
        los cambios de esta sesión son solo locales.
      </q-banner>

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
              v-for="role in props.row.roles || ['admin']"
              :key="role"
              dense
              size="sm"
              color="primary"
              text-color="white"
              :label="role"
            />
          </q-td>
        </template>
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-badge
              :color="props.row.status === 'active' ? 'positive' : 'grey'"
              :label="props.row.status || 'active'"
            />
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
                outline
                dense
                no-caps
                color="negative"
                :label="props.row.status === 'blocked' ? 'Activar' : 'Bloquear'"
                @click="openBlock(props.row)"
              />
            </div>
          </q-td>
        </template>
      </q-table>
    </div>

    <q-dialog v-model="formDialog" persistent>
      <q-card style="min-width: 420px">
        <q-card-section>
          <div class="text-h6">{{ editing ? 'Editar usuario' : 'Nuevo usuario' }}</div>
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input v-model="form.name" outlined label="Nombre *" />
          <q-input v-model="form.email" outlined type="email" label="Email *" />
          <q-input
            v-if="!editing"
            v-model="form.password"
            outlined
            type="password"
            label="Contraseña *"
          />
          <q-select
            v-model="form.role"
            outlined
            :options="roleOptions"
            label="Rol"
            emit-value
            map-options
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn color="primary" unelevated no-caps label="Guardar" @click="saveUser" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="blockDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">
            {{ blockTarget?.status === 'blocked' ? 'Activar usuario' : 'Bloquear usuario' }}
          </div>
        </q-card-section>
        <q-card-section>
          <p class="q-mb-none text-grey-7">
            <template v-if="blockTarget?.status === 'blocked'">
              ¿Activar a <strong>{{ blockTarget?.name }}</strong>? Podrá iniciar sesión de nuevo.
            </template>
            <template v-else>
              ¿Bloquear a <strong>{{ blockTarget?.name }}</strong>?<br />
              No podrá iniciar sesión hasta que lo reactives.
            </template>
          </p>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancelar" v-close-popup />
          <q-btn
            :color="blockTarget?.status === 'blocked' ? 'primary' : 'negative'"
            outline
            no-caps
            :label="blockTarget?.status === 'blocked' ? 'Activar' : 'Bloquear'"
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
import { useAuthStore } from '@/stores/authStore'
import type { User } from '@/types'

type UserRow = User & { roles?: string[]; status?: string }

const $q = useQuasar()
const auth = useAuthStore()
const loading = ref(false)
const rows = ref<UserRow[]>([])
const formDialog = ref(false)
const blockDialog = ref(false)
const editing = ref(false)
const blockTarget = ref<UserRow | null>(null)
let localId = 1000

const form = reactive({
  id: null as number | string | null,
  name: '',
  email: '',
  password: '',
  role: 'editor',
})

const roleOptions = [
  { label: 'admin', value: 'admin' },
  { label: 'editor', value: 'editor' },
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

onMounted(async () => {
  loading.value = true
  try {
    const me = auth.user || (await auth.fetchMe())
    rows.value = me
      ? [
          {
            ...me,
            roles: me.roles?.length ? me.roles : ['admin'],
            status: me.status || 'active',
          },
        ]
      : []
  } finally {
    loading.value = false
  }
})

function openCreate() {
  editing.value = false
  form.id = null
  form.name = ''
  form.email = ''
  form.password = ''
  form.role = 'editor'
  formDialog.value = true
}

function openEdit(row: UserRow) {
  editing.value = true
  form.id = row.id
  form.name = row.name
  form.email = row.email
  form.password = ''
  form.role = row.roles?.[0] || 'admin'
  formDialog.value = true
}

function openBlock(row: UserRow) {
  blockTarget.value = row
  blockDialog.value = true
}

function saveUser() {
  if (!form.name.trim() || !form.email.trim() || (!editing.value && !form.password)) {
    $q.notify({ type: 'warning', message: 'Completa los campos requeridos.' })
    return
  }

  if (editing.value && form.id != null) {
    rows.value = rows.value.map((row) =>
      row.id === form.id
        ? { ...row, name: form.name.trim(), email: form.email.trim(), roles: [form.role] }
        : row,
    )
    $q.notify({ type: 'positive', message: 'Cambios guardados' })
  } else {
    rows.value = [
      ...rows.value,
      {
        id: ++localId,
        name: form.name.trim(),
        email: form.email.trim(),
        roles: [form.role],
        status: 'active',
        last_login_at: null,
      } as UserRow,
    ]
    $q.notify({ type: 'positive', message: 'Usuario creado correctamente' })
  }

  formDialog.value = false
}

function confirmBlock() {
  const target = blockTarget.value
  if (!target) return
  const next = target.status === 'blocked' ? 'active' : 'blocked'
  rows.value = rows.value.map((row) => (row.id === target.id ? { ...row, status: next } : row))
  $q.notify({
    type: 'positive',
    message: next === 'blocked' ? 'Usuario bloqueado' : 'Usuario activado',
  })
  blockDialog.value = false
}
</script>
