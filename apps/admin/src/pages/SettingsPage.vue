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
      <div v-if="loading" class="flex flex-center q-pa-xl">
        <q-spinner color="primary" size="42px" />
      </div>

      <div v-else-if="!rows.length" class="text-grey-7 text-center q-pa-xl">
        No hay ajustes configurados.
      </div>

      <div v-else class="setting-list">
        <article v-for="row in rows" :key="row.id" class="setting-card">
          <div class="setting-card__identity">
            <div class="setting-card__thumb">
              <q-icon :name="iconForKey(row.key)" size="26px" color="primary" />
            </div>
            <div class="setting-card__titles">
              <h3 class="setting-card__name">{{ row.key }}</h3>
              <p class="setting-card__slug">Clave de configuración</p>
            </div>
          </div>

          <div class="setting-card__field setting-card__field--wide">
            <span class="setting-card__label">Valor</span>
            <div class="setting-card__value">
              <q-icon name="notes" size="16px" />
              <span class="ellipsis">{{ formatValue(row.value) }}</span>
            </div>
          </div>

          <div class="setting-card__field">
            <span class="setting-card__label">Actualizado</span>
            <div class="setting-card__value">
              <q-icon name="event" size="16px" />
              <span>{{ formatDate(row.updated_at) }}</span>
            </div>
          </div>

          <div class="setting-card__actions">
            <q-btn
              unelevated
              no-caps
              dense
              color="primary"
              class="setting-card__btn"
              icon="edit"
              label="Editar"
              @click="openEdit(row)"
            />
            <q-btn
              outline
              no-caps
              dense
              color="negative"
              class="setting-card__btn setting-card__btn--danger"
              icon="delete"
              label="Eliminar"
              @click="remove(row.id)"
            />
          </div>
        </article>
      </div>
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
import { useQuasar } from 'quasar'
import { adminApi } from '@/services/adminApi'
import type { SiteSetting } from '@/types'

const $q = useQuasar()
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const editing = ref(false)
const rows = ref<SiteSetting[]>([])

const form = reactive({ key: '', value: '' })

function formatValue(value: unknown) {
  if (value == null) return '—'
  if (typeof value === 'string') return value
  try {
    return JSON.stringify(value)
  } catch {
    return String(value)
  }
}

function formatDate(value?: string | null) {
  if (!value) return '—'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return '—'
  const dd = String(d.getDate()).padStart(2, '0')
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const yyyy = d.getFullYear()
  return `${dd}/${mm}/${yyyy}`
}

function iconForKey(key: string) {
  if (key.includes('email')) return 'email'
  if (key.includes('phone')) return 'phone'
  if (key.includes('name')) return 'badge'
  if (key.includes('tagline')) return 'short_text'
  return 'tune'
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

<style scoped lang="scss">
.setting-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.setting-card {
  display: grid;
  grid-template-columns: minmax(180px, 1.1fr) minmax(220px, 1.6fr) minmax(140px, 0.8fr) auto;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1rem;
  background: #fff;
  border: 1px solid var(--ma-border);
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.setting-card__identity {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.setting-card__thumb {
  width: 56px;
  height: 56px;
  flex-shrink: 0;
  border-radius: 10px;
  background: #f5f2ed;
  border: 1px solid var(--ma-border);
  display: grid;
  place-items: center;
}

.setting-card__titles {
  min-width: 0;
}

.setting-card__name {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a1a;
  word-break: break-all;
}

.setting-card__slug {
  margin: 0.15rem 0 0;
  font-size: 0.75rem;
  color: #777;
}

.setting-card__field {
  min-width: 0;
}

.setting-card__label {
  display: block;
  margin-bottom: 0.3rem;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #888;
}

.setting-card__value {
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

.setting-card__actions {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  min-width: 118px;
}

.setting-card__btn {
  min-height: 30px;
  font-size: 0.72rem;
  font-weight: 600;
}

.setting-card__btn--danger {
  border-color: rgba(193, 0, 21, 0.35);
}

.ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 1100px) {
  .setting-card {
    grid-template-columns: 1fr 1fr;
    align-items: start;
  }

  .setting-card__identity,
  .setting-card__field--wide,
  .setting-card__actions {
    grid-column: 1 / -1;
  }

  .setting-card__actions {
    flex-direction: row;
  }

  .setting-card__btn {
    flex: 1;
  }
}

@media (max-width: 600px) {
  .setting-card {
    grid-template-columns: 1fr;
  }
}
</style>
