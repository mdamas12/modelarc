import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { api, TOKEN_KEY } from '@/boot/axios'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string>(localStorage.getItem(TOKEN_KEY) || '')
  const user = ref<User | null>(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => Boolean(token.value))
  const displayName = computed(() => user.value?.name || 'Administrador')

  function setSession(newToken: string, newUser: User) {
    token.value = newToken
    user.value = newUser
    localStorage.setItem(TOKEN_KEY, newToken)
  }

  function clearSession() {
    token.value = ''
    user.value = null
    localStorage.removeItem(TOKEN_KEY)
  }

  async function login(email: string, password: string) {
    loading.value = true
    try {
      const { data } = await api.post('/admin/login', {
        email,
        password,
        device_name: 'admin-web',
      })
      const payload = data.data
      setSession(payload.token, payload.user)
      return payload.user as User
    } finally {
      loading.value = false
    }
  }

  async function fetchMe() {
    if (!token.value) return null
    try {
      const { data } = await api.get('/admin/me')
      user.value = data.data ?? data
      return user.value
    } catch {
      clearSession()
      return null
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await api.post('/admin/logout')
      }
    } catch {
      // ignore logout errors
    } finally {
      clearSession()
    }
  }

  return {
    token,
    user,
    loading,
    isAuthenticated,
    displayName,
    login,
    logout,
    fetchMe,
    setSession,
    clearSession,
  }
})
