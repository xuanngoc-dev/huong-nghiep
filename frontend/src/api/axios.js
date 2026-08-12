import axios from 'axios'

export const AUTH_TOKEN_KEY = 'auth_token'
export const AUTH_USER_KEY = 'auth_user'
export const ANONYMOUS_TOKEN = 'anonymous'

/** Token gửi kèm mọi request: token đăng nhập hoặc `anonymous`. */
export function getRequestToken() {
  return localStorage.getItem(AUTH_TOKEN_KEY) || ANONYMOUS_TOKEN
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

api.interceptors.request.use((config) => {
  config.headers.Authorization = `Bearer ${getRequestToken()}`
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const hadToken = Boolean(localStorage.getItem(AUTH_TOKEN_KEY))
      localStorage.removeItem(AUTH_TOKEN_KEY)
      localStorage.removeItem(AUTH_USER_KEY)

      // Chỉ redirect khi từng có session thật (tránh redirect khi gửi anonymous)
      if (hadToken && window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  },
)

export default api
