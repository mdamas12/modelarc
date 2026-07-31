<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Usuarios</h1>
        <p class="page-subtitle">Administradores del panel</p>
      </div>
    </div>

    <div class="admin-card q-pa-md">
      <q-banner class="bg-grey-2 q-mb-md" rounded dense>
        La API actual no expone un endpoint CRUD de usuarios. Se muestra la sesión activa.
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
      </q-table>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { QTableColumn } from 'quasar'
import { useAuthStore } from '@/stores/authStore'
import type { User } from '@/types'

const auth = useAuthStore()
const loading = ref(false)
const rows = ref<User[]>([])

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
]

onMounted(async () => {
  loading.value = true
  try {
    const me = auth.user || (await auth.fetchMe())
    rows.value = me ? [me] : []
  } finally {
    loading.value = false
  }
})
</script>
