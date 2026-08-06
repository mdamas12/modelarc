<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-brand">
        <img src="/brand/logo-dark.svg?v=2" alt="Modelarc" class="login-brand__logo" />
      </div>
      <p class="text-center text-grey-6 q-mb-lg">{{ title }}</p>

      <div v-if="loading" class="flex flex-center q-py-xl">
        <q-spinner color="primary" size="40px" />
      </div>

      <q-banner v-else-if="error" dense rounded class="bg-red-1 text-negative q-mb-md">
        {{ error }}
      </q-banner>

      <template v-else-if="done">
        <q-banner dense rounded class="bg-green-1 text-positive q-mb-md">
          {{ doneMessage }}
        </q-banner>
        <q-btn
          color="primary"
          class="full-width"
          unelevated
          no-caps
          label="Ir al login"
          :to="'/login'"
        />
      </template>

      <q-form v-else class="q-gutter-md" @submit.prevent="onSubmit">
        <q-input outlined :model-value="info?.name || ''" label="Nombre" readonly />
        <q-input outlined :model-value="info?.email || ''" label="Email" readonly />
        <q-input
          v-model="password"
          outlined
          :type="showPassword ? 'text' : 'password'"
          label="Contraseña *"
          autocomplete="new-password"
          :rules="[
            (v) => !!v || 'Requerido',
            (v) => (v && v.length >= 8) || 'Mínimo 8 caracteres',
          ]"
        >
          <template #append>
            <q-icon
              :name="showPassword ? 'visibility_off' : 'visibility'"
              class="cursor-pointer"
              @click="showPassword = !showPassword"
            />
          </template>
        </q-input>
        <q-input
          v-model="passwordConfirm"
          outlined
          :type="showPassword ? 'text' : 'password'"
          label="Confirmar contraseña *"
          autocomplete="new-password"
          :rules="[
            (v) => !!v || 'Requerido',
            (v) => v === password || 'Las contraseñas no coinciden',
          ]"
        />

        <q-banner v-if="submitError" dense rounded class="bg-red-1 text-negative">
          {{ submitError }}
        </q-banner>

        <q-btn
          type="submit"
          color="primary"
          class="full-width"
          size="md"
          no-caps
          :label="submitLabel"
          :loading="submitting"
          unelevated
        />
      </q-form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminApi } from '@/services/adminApi'

const props = defineProps<{
  mode: 'activation' | 'password_reset'
}>()

const route = useRoute()
const router = useRouter()
const token = computed(() => String(route.params.token || ''))

const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const submitError = ref('')
const done = ref(false)
const doneMessage = ref('')
const showPassword = ref(false)
const password = ref('')
const passwordConfirm = ref('')
const info = ref<{ name: string; email: string } | null>(null)

const title = computed(() =>
  props.mode === 'activation' ? 'Activar cuenta' : 'Restablecer contraseña',
)
const submitLabel = computed(() =>
  props.mode === 'activation' ? 'Activar cuenta' : 'Actualizar contraseña',
)

onMounted(async () => {
  loading.value = true
  error.value = ''
  try {
    info.value =
      props.mode === 'activation'
        ? await adminApi.getActivation(token.value)
        : await adminApi.getPasswordReset(token.value)
  } catch {
    error.value = 'Este enlace no es válido, expiró o ya fue utilizado.'
  } finally {
    loading.value = false
  }
})

async function onSubmit() {
  submitError.value = ''
  submitting.value = true
  try {
    const res =
      props.mode === 'activation'
        ? await adminApi.activateAccount(token.value, {
            password: password.value,
            password_confirmation: passwordConfirm.value,
          })
        : await adminApi.completePasswordReset(token.value, {
            password: password.value,
            password_confirmation: passwordConfirm.value,
          })

    done.value = true
    doneMessage.value = res.message || 'Listo.'
    setTimeout(() => {
      void router.replace('/login')
    }, 1800)
  } catch (e: unknown) {
    submitError.value =
      (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })
        ?.response?.data?.errors?.password?.[0] ||
      (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } })
        ?.response?.data?.errors?.token?.[0] ||
      (e as { response?: { data?: { message?: string } } })?.response?.data?.message ||
      'No se pudo completar la operación.'
  } finally {
    submitting.value = false
  }
}
</script>
