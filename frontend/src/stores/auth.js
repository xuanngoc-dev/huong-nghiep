import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api'
import { AUTH_TOKEN_KEY, AUTH_USER_KEY } from '@/api/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem(AUTH_USER_KEY) || 'null'))
  const token = ref(localStorage.getItem(AUTH_TOKEN_KEY) || null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => Boolean(token.value))
  const role = computed(() => user.value?.role || 'user')
  const isAdmin = computed(() => role.value === 'admin')

  function setSession(nextUser, nextToken) {
    if (!nextToken) {
      throw new Error('Thiếu token từ phản hồi đăng nhập.')
    }
    user.value = nextUser
    token.value = nextToken
    localStorage.setItem(AUTH_USER_KEY, JSON.stringify(nextUser))
    localStorage.setItem(AUTH_TOKEN_KEY, nextToken)
  }

  function clearSession() {
    user.value = null
    token.value = null
    localStorage.removeItem(AUTH_USER_KEY)
    localStorage.removeItem(AUTH_TOKEN_KEY)
  }

  async function login(credentials) {
    loading.value = true
    error.value = null
    try {
      const { data } = await authApi.login(credentials)

      // ApiResponse lỗi dùng HTTP 200 + status: false — không vào axios catch
      if (data?.status === false) {
        const fieldError =
          data?.errors?.tai_khoan?.[0] ||
          data?.errors?.email?.[0] ||
          data?.errors?.password?.[0]
        error.value = fieldError || data?.message || 'Đăng nhập thất bại.'
        throw new Error(error.value)
      }

      setSession(data.user, data.token)
      return data
    } catch (err) {
      if (!error.value) {
        const data = err.response?.data
        const fieldError =
          data?.errors?.tai_khoan?.[0] ||
          data?.errors?.email?.[0] ||
          data?.errors?.password?.[0]
        error.value =
          fieldError || data?.message || err.message || 'Đăng nhập thất bại.'
      }
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

      if (data?.status === false) {
        error.value = data?.message || 'Đăng ký thất bại.'
        throw new Error(error.value)
      }

      setSession(data.user, data.token)
      return data
    } catch (err) {
      if (!error.value) {
        const data = err.response?.data
        error.value = data?.message || err.message || 'Đăng ký thất bại.'
      }
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
    localStorage.setItem(AUTH_USER_KEY, JSON.stringify(data.user))
    return data.user
  }

  function updateUser(partial) {
    if (!user.value || !partial || typeof partial !== 'object') return
    user.value = { ...user.value, ...partial }
    localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user.value))
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
    updateUser,
    clearSession,
  }
})
