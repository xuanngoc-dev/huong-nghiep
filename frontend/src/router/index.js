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
          component: () => import('@/layouts/user/TrangCaNhanLayout.vue'),
          meta: { requiresAuth: true },
          children: [
            {
              path: '',
              name: 'profile',
              component: placeholder,
              meta: { title: 'Tổng quan', icon: 'User' },
            },
            {
              path: 'thong-tin',
              name: 'profile-thong-tin',
              component: placeholder,
              meta: { title: 'Thông tin cá nhân', icon: 'UserFilled' },
            },
            {
              path: 'bao-mat',
              name: 'profile-bao-mat',
              component: placeholder,
              meta: { title: 'Bảo mật tài khoản', icon: 'Lock' },
            },
            {
              path: 'doi-mat-khau',
              redirect: { name: 'profile-bao-mat' },
            },
            {
              path: 'tai-san',
              name: 'profile-tai-san',
              component: placeholder,
              meta: { title: 'Tài sản', icon: 'Wallet' },
            },
            {
              path: 'lich-su-trac-nghiem',
              name: 'profile-lich-su-trac-nghiem',
              component: placeholder,
              meta: { title: 'Lịch sử trắc nghiệm', icon: 'Document' },
            },
            {
              path: 'ho-tro',
              name: 'profile-ho-tro',
              component: placeholder,
              meta: { title: 'Hỗ trợ', icon: 'Headset' },
            },
          ],
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
          path: 'danh-muc/phuong-xa',
          name: 'admin-phuong-xa',
          component: () => import('@/views/admin/PhuongXaView.vue'),
          meta: { title: 'Phường xã', icon: 'Place' },
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
          path: 'danh-muc/nhom-nganh',
          name: 'admin-nhom-nganh',
          component: () => import('@/views/admin/NhomNganhView.vue'),
          meta: { title: 'Nhóm ngành', icon: 'Collection' },
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
        {
          path: 'danh-muc/dan-toc',
          name: 'admin-dan-toc',
          component: () => import('@/views/admin/DanTocView.vue'),
          meta: { title: 'Dân tộc', icon: 'Flag' },
        },
        {
          path: 'danh-muc/ton-giao',
          name: 'admin-ton-giao',
          component: () => import('@/views/admin/TonGiaoView.vue'),
          meta: { title: 'Tôn giáo', icon: 'Guide' },
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

        // Người dùng
        {
          path: 'nguoi-dung',
          name: 'admin-nguoi-dung',
          component: () => import('@/views/admin/NguoiDungView.vue'),
          meta: { title: 'Danh sách người dùng', icon: 'User' },
        },
        {
          path: 'nguoi-dung/lich-su-thanh-toan',
          name: 'admin-lich-su-thanh-toan',
          component: placeholder,
          meta: { title: 'Lịch sử thanh toán', icon: 'Wallet' },
        },
        {
          path: 'nguoi-dung/lich-su-trac-nghiem',
          name: 'admin-lich-su-trac-nghiem',
          component: () => import('@/views/admin/LichSuTracNghiemView.vue'),
          meta: { title: 'Lịch sử trắc nghiệm', icon: 'Document' },
        },

        // Cấu hình
        {
          path: 'cau-hinh/chu-so-huu',
          name: 'admin-chu-so-huu',
          component: placeholder,
          meta: { title: 'Chủ sở hữu', icon: 'Avatar' },
        },
        {
          path: 'cau-hinh/ngan-hang-thanh-toan',
          name: 'admin-ngan-hang-thanh-toan',
          component: () => import('@/views/admin/NganHangThanhToanView.vue'),
          meta: { title: 'Ngân hàng thanh toán', icon: 'CreditCard' },
        },

        // Blog
        {
          path: 'blog/tin-tuc',
          name: 'admin-tin-tuc',
          component: placeholder,
          meta: { title: 'Tin tức', icon: 'Newspaper' },
        },
        {
          path: 'blog/tuyen-dung',
          name: 'admin-tuyen-dung',
          component: placeholder,
          meta: { title: 'Tuyển dụng', icon: 'Briefcase' },
        },

        // Hỗ trợ
        {
          path: 'ho-tro/kenh-ho-tro',
          name: 'admin-kenh-ho-tro',
          component: placeholder,
          meta: { title: 'Kênh hỗ trợ', icon: 'Headset' },
        },
        {
          path: 'ho-tro/chat-ho-tro',
          name: 'admin-chat-ho-tro',
          component: placeholder,
          meta: { title: 'Chat hỗ trợ', icon: 'ChatDotRound' },
        },

        // 404 trong khu vực admin
        {
          path: ':pathMatch(.*)*',
          name: 'admin-not-found',
          component: () => import('@/views/admin/KhongTimThayView.vue'),
          meta: { title: 'Không tìm thấy' },
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
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/user/KhongTimThayView.vue'),
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
