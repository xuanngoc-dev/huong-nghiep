import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const placeholder = () => import('@/components/admin/PagePlaceholder.vue')

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: () => import('@/layouts/user/MainLayout.vue'),
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('@/views/user/TrangChuView.vue'),
        },
        {
          path: 'careers',
          name: 'careers',
          component: () => import('@/views/user/NgheNghiepView.vue'),
        },
        {
          path: 'careers/:id',
          name: 'career-detail',
          component: () => import('@/views/user/ChiTietNgheNghiepView.vue'),
        },
        {
          path: 'articles',
          name: 'articles',
          component: () => import('@/views/user/BaiVietView.vue'),
        },
        {
          path: 'assessments',
          name: 'assessments',
          component: () => import('@/views/user/KhaoSatView.vue'),
        },
        {
          path: 'profile',
          name: 'profile',
          component: () => import('@/views/user/HoSoView.vue'),
          meta: { requiresAuth: true },
        },
      ],
    },
    {
      path: '/trac-nghiem',
      component: () => import('@/layouts/user/QuizLayout.vue'),
      children: [
        {
          path: '',
          redirect: { name: 'quiz-start' },
        },
        {
          path: 'bat-dau',
          name: 'quiz-start',
          component: () => import('@/views/user/quiz/TracNghiemBatDauView.vue'),
          meta: {
            quizTitle: 'Bắt đầu làm bài',
            exitTo: { name: 'assessments' },
          },
        },
        {
          path: ':ssid/nganh-phu-hop',
          name: 'quiz-fields',
          component: () => import('@/views/user/quiz/TracNghiemNganhPhuHopView.vue'),
          meta: {
            quizTitle: 'Ngành phù hợp',
            quizSection: 'fields',
            requiresQuizSession: true,
            exitTo: { name: 'quiz-start' },
          },
        },
        {
          path: ':ssid/ket-qua',
          name: 'quiz-result',
          component: () => import('@/views/user/quiz/TracNghiemKetQuaView.vue'),
          meta: {
            quizTitle: 'Kết quả trắc nghiệm',
            requiresQuizSession: true,
            exitTo: { name: 'assessments' },
          },
        },
        {
          path: ':ssid/loai/:maLoaiCauHoi',
          name: 'quiz-loai',
          component: () => import('@/views/user/quiz/TracNghiemLamBaiView.vue'),
          meta: {
            requiresQuizSession: true,
            exitTo: { name: 'quiz-start' },
          },
        },
      ],
    },
    {
      path: '/admin',
      component: () => import('@/layouts/admin/AdminLayout.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: () => import('@/views/admin/TongQuanView.vue'),
          meta: { title: 'Tổng quan' },
        },

        // Ngành
        {
          path: 'nganh',
          name: 'admin-nganh',
          component: placeholder,
          meta: { title: 'Ngành', icon: 'Collection' },
        },

        // Danh mục
        {
          path: 'danh-muc/tinh-thanh',
          name: 'admin-tinh-thanh',
          component: () => import('@/views/admin/TinhThanhView.vue'),
          meta: { title: 'Tỉnh thành', icon: 'Location' },
        },
        {
          path: 'danh-muc/khu-vuc',
          name: 'admin-khu-vuc',
          component: () => import('@/views/admin/KhuVucView.vue'),
          meta: { title: 'Khu vực', icon: 'MapLocation' },
        },
        {
          path: 'danh-muc/truong-hoc',
          name: 'admin-truong-hoc',
          component: () => import('@/views/admin/TruongHocView.vue'),
          meta: { title: 'Trường học', icon: 'School' },
        },
        {
          path: 'danh-muc/tuyen-sinh-theo-nam',
          name: 'admin-tuyen-sinh-theo-nam',
          component: () => import('@/views/admin/TuyenSinhTheoNamView.vue'),
          meta: { title: 'Tuyển sinh theo năm', icon: 'Calendar' },
        },
        {
          path: 'danh-muc/loai-truong',
          name: 'admin-loai-truong',
          component: () => import('@/views/admin/LoaiTruongView.vue'),
          meta: { title: 'Loại trường', icon: 'OfficeBuilding' },
        },
        {
          path: 'danh-muc/he-dao-tao',
          name: 'admin-he-dao-tao',
          component: () => import('@/views/admin/HeDaoTaoView.vue'),
          meta: { title: 'Hệ đào tạo', icon: 'Medal' },
        },
        {
          path: 'danh-muc/nganh-hoc',
          name: 'admin-nganh-hoc',
          component: () => import('@/views/admin/NganhHocView.vue'),
          meta: { title: 'Ngành học', icon: 'Reading' },
        },
        {
          path: 'danh-muc/chuyen-nganh',
          name: 'admin-chuyen-nganh',
          component: () => import('@/views/admin/ChuyenNganhView.vue'),
          meta: { title: 'Chuyên ngành', icon: 'Notebook' },
        },
        {
          path: 'danh-muc/loai-cau-hoi',
          name: 'admin-loai-cau-hoi',
          component: () => import('@/views/admin/LoaiCauHoiView.vue'),
          meta: { title: 'Loại câu hỏi', icon: 'Document' },
        },
        {
          path: 'danh-muc/mon-hoc',
          name: 'admin-mon-hoc',
          component: () => import('@/views/admin/MonHocView.vue'),
          meta: { title: 'Môn học', icon: 'Notebook' },
        },
        {
          path: 'danh-muc/phuong-thuc-xet-tuyen',
          name: 'admin-phuong-thuc-xet-tuyen',
          component: () => import('@/views/admin/PhuongThucXetTuyenView.vue'),
          meta: { title: 'Phương thức xét tuyển', icon: 'Checked' },
        },
        {
          path: 'danh-muc/to-hop-mon-hoc',
          name: 'admin-to-hop-mon-hoc',
          component: () => import('@/views/admin/ToHopMonHocView.vue'),
          meta: { title: 'Tổ hợp môn học', icon: 'Files' },
        },

        // Trắc nghiệm
        {
          path: 'cau-hoi-dap-an',
          name: 'admin-cau-hoi-dap-an',
          component: () => import('@/views/admin/CauHoiDapAnView.vue'),
          meta: { title: 'Câu hỏi đáp án', icon: 'Collection' },
        },
        {
          path: 'trac-nghiem/lich-su-khao-sat',
          name: 'admin-lich-su-khao-sat',
          component: placeholder,
          meta: { title: 'Lịch sử khảo sát', icon: 'Calendar' },
        },
      ],
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/user/DangNhapView.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/user/DangKyView.vue'),
      meta: { guest: true },
    },
    {
      path: '/forbidden',
      name: 'forbidden',
      component: () => import('@/views/user/CamTruyCapView.vue'),
    },
  ],
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: 'forbidden' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return auth.isAdmin ? { name: 'admin-dashboard' } : { name: 'home' }
  }
})

export default router
