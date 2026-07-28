/**
 * Khai báo endpoint API: { url, method }.
 * Dùng với request({ url, params, body }).
 *
 * method: 'get' | 'post' | 'put' | 'patch' | 'delete'
 */

/** Auth */
export const API_AUTH = {
  REGISTER: { url: '/auth/register', method: 'post' },
  LOGIN: { url: '/auth/login', method: 'post' },
  LOGOUT: { url: '/auth/logout', method: 'post' },
  ME: { url: '/auth/me', method: 'get' },
}

/** Admin — Ngành học */
export const API_NGANH_HOC = {
  LIST: { url: '/admin/nganh-hoc', method: 'get' },
  CREATE: { url: '/admin/nganh-hoc', method: 'post' },
  SHOW: (id) => ({ url: `/admin/nganh-hoc/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/nganh-hoc/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/nganh-hoc/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/nganh-hoc/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/nganh-hoc/bulk-status', method: 'post' },
}

/** Admin — Khu vực */
export const API_KHU_VUC = {
  LIST: { url: '/admin/khu-vuc', method: 'get' },
  CREATE: { url: '/admin/khu-vuc', method: 'post' },
  SHOW: (id) => ({ url: `/admin/khu-vuc/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/khu-vuc/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/khu-vuc/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/khu-vuc/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/khu-vuc/bulk-status', method: 'post' },
}

/** Admin — Chuyên ngành */
export const API_CHUYEN_NGANH = {
  LIST: { url: '/admin/chuyen-nganh', method: 'get' },
  CREATE: { url: '/admin/chuyen-nganh', method: 'post' },
  SHOW: (id) => ({ url: `/admin/chuyen-nganh/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/chuyen-nganh/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/chuyen-nganh/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/chuyen-nganh/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/chuyen-nganh/bulk-status', method: 'post' },
}

/** Admin — khác (placeholder / dùng dần) */
export const API_ADMIN = {
  DASHBOARD: { url: '/admin/dashboard', method: 'get' },
  CAREERS: {
    LIST: { url: '/admin/careers', method: 'get' },
    CREATE: { url: '/admin/careers', method: 'post' },
    SHOW: (id) => ({ url: `/admin/careers/${id}`, method: 'get' }),
    UPDATE: (id) => ({ url: `/admin/careers/${id}`, method: 'put' }),
    DELETE: (id) => ({ url: `/admin/careers/${id}`, method: 'delete' }),
  },
  ARTICLES: {
    LIST: { url: '/admin/articles', method: 'get' },
    CREATE: { url: '/admin/articles', method: 'post' },
    UPDATE: (id) => ({ url: `/admin/articles/${id}`, method: 'put' }),
    DELETE: (id) => ({ url: `/admin/articles/${id}`, method: 'delete' }),
  },
  ASSESSMENTS: {
    LIST: { url: '/admin/assessments', method: 'get' },
    CREATE: { url: '/admin/assessments', method: 'post' },
    UPDATE: (id) => ({ url: `/admin/assessments/${id}`, method: 'put' }),
    DELETE: (id) => ({ url: `/admin/assessments/${id}`, method: 'delete' }),
  },
}

/** Public site */
export const API_PUBLIC = {
  HEALTH: { url: '/health', method: 'get' },
  CAREERS: {
    LIST: { url: '/careers', method: 'get' },
    SHOW: (id) => ({ url: `/careers/${id}`, method: 'get' }),
  },
  ARTICLES: {
    LIST: { url: '/articles', method: 'get' },
    SHOW: (id) => ({ url: `/articles/${id}`, method: 'get' }),
  },
  ASSESSMENTS: {
    LIST: { url: '/assessments', method: 'get' },
    SHOW: (id) => ({ url: `/assessments/${id}`, method: 'get' }),
    SUBMIT: (id) => ({ url: `/assessments/${id}/submit`, method: 'post' }),
  },
}
