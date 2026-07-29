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

/** Admin — Tỉnh thành */
export const API_TINH_THANH = {
  LIST: { url: '/admin/tinh-thanh', method: 'get' },
  CREATE: { url: '/admin/tinh-thanh', method: 'post' },
  SHOW: (id) => ({ url: `/admin/tinh-thanh/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/tinh-thanh/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/tinh-thanh/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/tinh-thanh/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/tinh-thanh/bulk-status', method: 'post' },
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

/** Admin — Loại trường */
export const API_LOAI_TRUONG = {
  LIST: { url: '/admin/loai-truong', method: 'get' },
  CREATE: { url: '/admin/loai-truong', method: 'post' },
  SHOW: (id) => ({ url: `/admin/loai-truong/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/loai-truong/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/loai-truong/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/loai-truong/bulk-delete', method: 'post' },
}

/** Admin — Hệ đào tạo */
export const API_HE_DAO_TAO = {
  LIST: { url: '/admin/he-dao-tao', method: 'get' },
  CREATE: { url: '/admin/he-dao-tao', method: 'post' },
  SHOW: (id) => ({ url: `/admin/he-dao-tao/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/he-dao-tao/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/he-dao-tao/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/he-dao-tao/bulk-delete', method: 'post' },
}

/** Admin — Trường học */
export const API_TRUONG_HOC = {
  LIST: { url: '/admin/truong-hoc', method: 'get' },
  CREATE: { url: '/admin/truong-hoc', method: 'post' },
  SHOW: (id) => ({ url: `/admin/truong-hoc/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/truong-hoc/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/truong-hoc/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/truong-hoc/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/truong-hoc/bulk-status', method: 'post' },
}

/** Admin — Loại câu hỏi */
export const API_LOAI_CAU_HOI = {
  LIST: { url: '/admin/loai-cau-hoi', method: 'get' },
  CREATE: { url: '/admin/loai-cau-hoi', method: 'post' },
  SHOW: (id) => ({ url: `/admin/loai-cau-hoi/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/loai-cau-hoi/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/loai-cau-hoi/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/loai-cau-hoi/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/loai-cau-hoi/bulk-status', method: 'post' },
}

/** Admin — Trắc nghiệm câu hỏi */
export const API_TRAC_NGHIEM_CAU_HOI = {
  LIST: { url: '/admin/trac-nghiem-cau-hoi', method: 'get' },
  CREATE: { url: '/admin/trac-nghiem-cau-hoi', method: 'post' },
  SHOW: (id) => ({ url: `/admin/trac-nghiem-cau-hoi/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/trac-nghiem-cau-hoi/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/trac-nghiem-cau-hoi/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/trac-nghiem-cau-hoi/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/trac-nghiem-cau-hoi/bulk-status', method: 'post' },
}

/** Admin — Trắc nghiệm câu trả lời */
export const API_TRAC_NGHIEM_CAU_TRA_LOI = {
  LIST: { url: '/admin/trac-nghiem-cau-tra-loi', method: 'get' },
  CREATE: { url: '/admin/trac-nghiem-cau-tra-loi', method: 'post' },
  SHOW: (id) => ({ url: `/admin/trac-nghiem-cau-tra-loi/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/trac-nghiem-cau-tra-loi/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/trac-nghiem-cau-tra-loi/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/trac-nghiem-cau-tra-loi/bulk-delete', method: 'post' },
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
  LOAI_CAU_HOI: {
    LIST: { url: '/loai-cau-hoi', method: 'get' },
  },
  TRAC_NGHIEM_CAU_HOI: {
    LIST: { url: '/trac-nghiem-cau-hoi', method: 'get' },
  },
}
