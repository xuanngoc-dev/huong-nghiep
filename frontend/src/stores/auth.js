import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => Boolean(token.value))
  const role = computed(() => user.value?.role || 'user')
  const isAdmin = computed(() => role.value === 'admin')

  function setSession(nextUser, nextToken) {
    user.value = nextUser
    token.value = nextToken
    localStorage.setItem('auth_user', JSON.stringify(nextUser))
    localStorage.setItem('auth_token', nextToken)
  }

  function clearSession() {
    user.value = null
    token.value = null
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_token')
  }

  async function login(credentials) {
    loading.value = true
    error.value = null
    try {
      const { data } = await authApi.login(credentials)
      setSession(data.user, data.token)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Đăng nhập thất bại.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function register(payload) {
    loading.value = true
    error.value = null
    try {
      const { data } = await authApi.register(payload)
      setSession(data.user, data.token)
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Đăng ký thất bại.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await authApi.logout()
      }
    } finally {
      clearSession()
    }
  }

  async function fetchMe() {
    if (!token.value) return null
    const { data } = await authApi.me()
    user.value = data.user
    localStorage.setItem('auth_user', JSON.stringify(data.user))
    return data.user
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    role,
    isAdmin,
    login,
    register,
    logout,
    fetchMe,
    clearSession,
  }
})
