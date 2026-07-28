/**
 * API layer — request helper + endpoint wrappers (legacy).
 *
 * Ưu tiên dùng:
 *   import { request } from '@/api'
 *   import { API_NGANH_HOC } from '@/constants/constant_api'
 *
 *   const url = API_NGANH_HOC.LIST
 *   const params = { q, start, limit }
 *   const res = await request({ url, params })
 *
 *   // Tắt loading / toast khi cần:
 *   await request({ url, params, loading: false, silent: true })
 */
import api from './axios'

export { default as api } from './axios'
export { request, http, notifyApi, apiErrorMessage, isRequestLoading } from './request'
export {
  API_AUTH,
  API_NGANH_HOC,
  API_ADMIN,
  API_PUBLIC,
} from '@/constants/constant_api'

export const authApi = {
  register: (payload) => api.post('/auth/register', payload),
  login: (payload) => api.post('/auth/login', payload),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
}

export const careerApi = {
  list: () => api.get('/careers'),
  show: (id) => api.get(`/careers/${id}`),
}

export const articleApi = {
  list: () => api.get('/articles'),
  show: (id) => api.get(`/articles/${id}`),
}

export const assessmentApi = {
  list: () => api.get('/assessments'),
  show: (id) => api.get(`/assessments/${id}`),
  submit: (id, answers) => api.post(`/assessments/${id}/submit`, { answers }),
}

export const healthApi = {
  check: () => api.get('/health'),
}

export const adminApi = {
  dashboard: () => api.get('/admin/dashboard'),
  careers: {
    list: () => api.get('/admin/careers'),
    show: (id) => api.get(`/admin/careers/${id}`),
    create: (payload) => api.post('/admin/careers', payload),
    update: (id, payload) => api.put(`/admin/careers/${id}`, payload),
    remove: (id) => api.delete(`/admin/careers/${id}`),
  },
  articles: {
    list: () => api.get('/admin/articles'),
    create: (payload) => api.post('/admin/articles', payload),
    update: (id, payload) => api.put(`/admin/articles/${id}`, payload),
    remove: (id) => api.delete(`/admin/articles/${id}`),
  },
  assessments: {
    list: () => api.get('/admin/assessments'),
    create: (payload) => api.post('/admin/assessments', payload),
    update: (id, payload) => api.put(`/admin/assessments/${id}`, payload),
    remove: (id) => api.delete(`/admin/assessments/${id}`),
  },
}
