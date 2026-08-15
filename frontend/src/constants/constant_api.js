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
  CHANGE_PASSWORD: { url: '/auth/doi-mat-khau', method: 'put' },
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

/** Admin — Phường xã */
export const API_PHUONG_XA = {
  LIST: { url: '/admin/phuong-xa', method: 'get' },
  CREATE: { url: '/admin/phuong-xa', method: 'post' },
  SHOW: (id) => ({ url: `/admin/phuong-xa/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/phuong-xa/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/phuong-xa/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/phuong-xa/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/phuong-xa/bulk-status', method: 'post' },
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

/** Admin — Môn học */
export const API_MON_HOC = {
  LIST: { url: '/admin/mon-hoc', method: 'get' },
  CREATE: { url: '/admin/mon-hoc', method: 'post' },
  SHOW: (id) => ({ url: `/admin/mon-hoc/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/mon-hoc/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/mon-hoc/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/mon-hoc/bulk-delete', method: 'post' },
}

/** Admin — Phương thức xét tuyển */
export const API_PHUONG_THUC_XET_TUYEN = {
  LIST: { url: '/admin/phuong-thuc-xet-tuyen', method: 'get' },
  CREATE: { url: '/admin/phuong-thuc-xet-tuyen', method: 'post' },
  SHOW: (id) => ({ url: `/admin/phuong-thuc-xet-tuyen/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/phuong-thuc-xet-tuyen/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/phuong-thuc-xet-tuyen/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/phuong-thuc-xet-tuyen/bulk-delete', method: 'post' },
}

/** Admin — Tổ hợp môn học */
export const API_TO_HOP_MON_HOC = {
  LIST: { url: '/admin/to-hop-mon-hoc', method: 'get' },
  CREATE: { url: '/admin/to-hop-mon-hoc', method: 'post' },
  SHOW: (id) => ({ url: `/admin/to-hop-mon-hoc/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/to-hop-mon-hoc/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/to-hop-mon-hoc/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/to-hop-mon-hoc/bulk-delete', method: 'post' },
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

/** Admin — Nhóm ngành */
export const API_NHOM_NGANH = {
  LIST: { url: '/admin/nhom-nganh', method: 'get' },
  CREATE: { url: '/admin/nhom-nganh', method: 'post' },
  SHOW: (id) => ({ url: `/admin/nhom-nganh/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/nhom-nganh/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/nhom-nganh/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/nhom-nganh/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/nhom-nganh/bulk-status', method: 'post' },
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

/** Admin — Tuyển sinh theo năm */
export const API_TRUONG_HOC_TUYEN_SINH_THEO_NAM = {
  LIST: { url: '/admin/truong-hoc-tuyen-sinh-theo-nam', method: 'get' },
  CREATE: { url: '/admin/truong-hoc-tuyen-sinh-theo-nam', method: 'post' },
  SHOW: (id) => ({ url: `/admin/truong-hoc-tuyen-sinh-theo-nam/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/truong-hoc-tuyen-sinh-theo-nam/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/truong-hoc-tuyen-sinh-theo-nam/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/truong-hoc-tuyen-sinh-theo-nam/bulk-delete', method: 'post' },
}

/** Admin — Dân tộc */
export const API_DAN_TOC = {
  LIST: { url: '/admin/dan-toc', method: 'get' },
  CREATE: { url: '/admin/dan-toc', method: 'post' },
  SHOW: (id) => ({ url: `/admin/dan-toc/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/dan-toc/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/dan-toc/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/dan-toc/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/dan-toc/bulk-status', method: 'post' },
}

/** Admin — Tôn giáo */
export const API_TON_GIAO = {
  LIST: { url: '/admin/ton-giao', method: 'get' },
  CREATE: { url: '/admin/ton-giao', method: 'post' },
  SHOW: (id) => ({ url: `/admin/ton-giao/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/ton-giao/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/ton-giao/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/ton-giao/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/ton-giao/bulk-status', method: 'post' },
}

/** Admin — Ngân hàng thanh toán */
export const API_NGAN_HANG_THANH_TOAN = {
  LIST: { url: '/admin/ngan-hang-thanh-toan', method: 'get' },
  CREATE: { url: '/admin/ngan-hang-thanh-toan', method: 'post' },
  SHOW: (id) => ({ url: `/admin/ngan-hang-thanh-toan/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/ngan-hang-thanh-toan/${id}`, method: 'put' }),
  DELETE: (id) => ({ url: `/admin/ngan-hang-thanh-toan/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/ngan-hang-thanh-toan/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/ngan-hang-thanh-toan/bulk-status', method: 'post' },
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

/** Admin — Người dùng (bảng users + thong_tin_nguoi_dung) */
export const API_NGUOI_DUNG = {
  LIST: { url: '/admin/nguoi-dung', method: 'get' },
  SHOW: (id) => ({ url: `/admin/nguoi-dung/${id}`, method: 'get' }),
  UPDATE: (id) => ({ url: `/admin/nguoi-dung/${id}`, method: 'put' }),
  CHANGE_PASSWORD: (id) => ({ url: `/admin/nguoi-dung/${id}/doi-mat-khau`, method: 'put' }),
  CHANGE_PAYMENT_PASSWORD: (id) => ({
    url: `/admin/nguoi-dung/${id}/doi-mat-khau-thanh-toan`,
    method: 'put',
  }),
  NAP_TIEN: (id) => ({ url: `/admin/nguoi-dung/${id}/nap-tien`, method: 'post' }),
  DELETE: (id) => ({ url: `/admin/nguoi-dung/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/nguoi-dung/bulk-delete', method: 'post' },
  BULK_STATUS: { url: '/admin/nguoi-dung/bulk-status', method: 'post' },
}

/** Admin — Lịch sử trắc nghiệm (phien da hoan thanh) */
export const API_LICH_SU_TRAC_NGHIEM = {
  LIST: { url: '/admin/lich-su-trac-nghiem', method: 'get' },
  SHOW: (id) => ({ url: `/admin/lich-su-trac-nghiem/${id}`, method: 'get' }),
  DELETE: (id) => ({ url: `/admin/lich-su-trac-nghiem/${id}`, method: 'delete' }),
  BULK_DELETE: { url: '/admin/lich-su-trac-nghiem/bulk-delete', method: 'post' },
}

/** Admin — Lịch sử thanh toán trắc nghiệm */
export const API_LICH_SU_THANH_TOAN = {
  LIST: { url: '/admin/lich-su-thanh-toan', method: 'get' },
  SHOW: (id) => ({ url: `/admin/lich-su-thanh-toan/${id}`, method: 'get' }),
  DUYET: (id) => ({ url: `/admin/lich-su-thanh-toan/${id}/duyet`, method: 'post' }),
}

/** Admin — Lịch sử nạp Edu Coin */
export const API_LICH_SU_NAP_COIN = {
  LIST: { url: '/admin/lich-su-nap-coin', method: 'get' },
  SHOW: (id) => ({ url: `/admin/lich-su-nap-coin/${id}`, method: 'get' }),
  DUYET: (id) => ({ url: `/admin/lich-su-nap-coin/${id}/duyet`, method: 'post' }),
  HUY_DUYET: (id) => ({ url: `/admin/lich-su-nap-coin/${id}/huy-duyet`, method: 'post' }),
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
  NGANH_HOC: {
    LIST: { url: '/nganh-hoc', method: 'get' },
    TRUONG_TUYEN_SINH: (id) => ({
      url: `/nganh-hoc/${id}/truong-tuyen-sinh`,
      method: 'get',
    }),
  },
  TRAC_NGHIEM_CAU_HOI: {
    LIST: { url: '/trac-nghiem-cau-hoi', method: 'get' },
  },
  TRAC_NGHIEM_LICH_SU_TRA_LOI: {
    START: { url: '/trac-nghiem-lich-su-tra-loi/start', method: 'post' },
    SAVE: { url: '/trac-nghiem-lich-su-tra-loi', method: 'post' },
    SHOW: (ssid) => ({ url: `/trac-nghiem-lich-su-tra-loi/${ssid}`, method: 'get' }),
    TONG_HOP: (ssid) => ({
      url: `/trac-nghiem-lich-su-tra-loi/${ssid}/tong-hop`,
      method: 'get',
    }),
  },
  NGUOI_DUNG: {
    ME: { url: '/nguoi-dung/me', method: 'get' },
    STORE: { url: '/nguoi-dung', method: 'post' },
  },
  NGAN_HANG_THANH_TOAN: {
    LIST: { url: '/ngan-hang-thanh-toan', method: 'get' },
  },
  NAP_EDU_COIN: {
    STORE: { url: '/nap-edu-coin', method: 'post' },
    SHOW: (id) => ({ url: `/nap-edu-coin/${id}`, method: 'get' }),
  },
  LICH_SU_NAP_EDU_COIN: {
    LIST: { url: '/lich-su-nap-edu-coin', method: 'get' },
  },
  XU_HE_THONG: {
    TUAN_DIEM_DANH: { url: '/xu-he-thong/diem-danh', method: 'get' },
    DIEM_DANH: { url: '/xu-he-thong/diem-danh', method: 'post' },
  },
  LICH_SU_NHAN_XU: {
    LIST: { url: '/lich-su-nhan-xu', method: 'get' },
  },
  LICH_SU_TRAC_NGHIEM: {
    LIST: { url: '/lich-su-trac-nghiem', method: 'get' },
    SHOW: (id) => ({ url: `/lich-su-trac-nghiem/${id}`, method: 'get' }),
    THANH_TOAN: (id) => ({ url: `/lich-su-trac-nghiem/${id}/thanh-toan`, method: 'post' }),
    THANH_TOAN_SHOW: (id, thanhToanId) => ({
      url: `/lich-su-trac-nghiem/${id}/thanh-toan/${thanhToanId}`,
      method: 'get',
    }),
  },
}
