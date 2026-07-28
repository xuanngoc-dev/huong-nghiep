import api from './axios'

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
