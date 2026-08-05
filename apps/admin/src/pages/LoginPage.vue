<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-brand">
        <img src="/brand/logo-dark.svg?v=2" alt="Modelarc" class="login-brand__logo" />
      </div>
      <p class="text-center text-grey-6 q-mb-lg">Panel de administración</p>

      <q-form class="q-gutter-md" @submit.prevent="onSubmit">
        <q-input
          v-model="email"
          outlined
          type="email"
          label="Correo electrónico"
          autocomplete="username"
          :rules="[(v) => !!v || 'Requerido', (v) => /.+@.+\..+/.test(v) || 'Email inválido']"
        >
          <template #prepend>
            <q-icon name="email" color="primary" />
          </template>
        </q-input>

        <q-input
          v-model="password"
          outlined
          :type="showPassword ? 'text' : 'password'"
          label="Contraseña"
          autocomplete="current-password"
          :rules="[(v) => !!v || 'Requerido']"
        >
          <template #prepend>
            <q-icon name="lock" color="primary" />
          </template>
          <template #append>
            <q-icon
              :name="showPassword ? 'visibility_off' : 'visibility'"
              class="cursor-pointer"
              @click="showPassword = !showPassword"
            />
          </template>
        </q-input>

        <q-banner v-if="error" dense rounded class="bg-red-1 text-negative">
          {{ error }}
        </q-banner>

        <q-btn
          type="submit"
          color="primary"
          class="full-width"
          size="md"
          no-caps
          label="Iniciar sesión"
          :loading="auth.loading"
          unelevated
        />
      </q-form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const auth = useAuthStore()
const router = useRouter()

const email = ref('admin@modelarc.com')
const password = ref('password')
const showPassword = ref(false)
const error = ref('')

async function onSubmit() {
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    await router.replace('/')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    const validation = err.response?.data?.errors?.email?.[0]
    error.value = validation || err.response?.data?.message || 'No se pudo iniciar sesión'
  }
}
</script>
