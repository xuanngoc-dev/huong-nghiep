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
          component: () => import('@/views/user/HomeView.vue'),
        },
        {
          path: 'careers',
          name: 'careers',
          component: () => import('@/views/user/CareersView.vue'),
        },
        {
          path: 'careers/:id',
          name: 'career-detail',
          component: () => import('@/views/user/CareerDetailView.vue'),
        },
        {
          path: 'articles',
          name: 'articles',
          component: () => import('@/views/user/ArticlesView.vue'),
        },
        {
          path: 'assessments',
          name: 'assessments',
          component: () => import('@/views/user/AssessmentsView.vue'),
        },
        {
          path: 'profile',
          name: 'profile',
          component: () => import('@/views/user/ProfileView.vue'),
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
          component: () => import('@/views/user/quiz/QuizStartView.vue'),
          meta: {
            quizTitle: 'Bắt đầu làm bài',
            quizStep: 1,
            exitTo: { name: 'assessments' },
          },
        },
        {
          path: 'so-thich-dam-me',
          name: 'quiz-interests',
          component: () => import('@/views/user/quiz/QuizTakeView.vue'),
          meta: {
            quizTitle: 'Sở thích & đam mê',
            quizStep: 2,
            quizSection: 'interests',
            exitTo: { name: 'quiz-start' },
          },
        },
        {
          path: 'ky-nang-kha-nang',
          name: 'quiz-skills',
          component: () => import('@/views/user/quiz/QuizTakeView.vue'),
          meta: {
            quizTitle: 'Kỹ năng & khả năng',
            quizStep: 3,
            quizSection: 'skills',
            exitTo: { name: 'quiz-start' },
          },
        },
        {
          path: 'moi-truong-lam-viec',
          name: 'quiz-environment',
          component: () => import('@/views/user/quiz/QuizTakeView.vue'),
          meta: {
            quizTitle: 'Môi trường làm việc',
            quizStep: 4,
            quizSection: 'environment',
            exitTo: { name: 'quiz-start' },
          },
        },
        {
          path: 'phong-cach-lam-viec',
          name: 'quiz-style',
          component: () => import('@/views/user/quiz/QuizTakeView.vue'),
          meta: {
            quizTitle: 'Phong cách làm việc',
            quizStep: 5,
            quizSection: 'style',
            exitTo: { name: 'quiz-start' },
          },
        },
        {
          path: 'nganh-phu-hop',
          name: 'quiz-fields',
          component: () => import('@/views/user/quiz/QuizTakeView.vue'),
          meta: {
            quizTitle: 'Ngành phù hợp',
            quizStep: 6,
            quizSection: 'fields',
            exitTo: { name: 'quiz-start' },
          },
        },
        {
          path: 'ket-qua',
          name: 'quiz-result',
          component: () => import('@/views/user/quiz/QuizResultView.vue'),
          meta: {
            quizTitle: 'Kết quả trắc nghiệm',
            quizStep: 7,
            exitTo: { name: 'assessments' },
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
          component: () => import('@/views/admin/DashboardView.vue'),
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
      component: () => import('@/views/user/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/user/RegisterView.vue'),
      meta: { guest: true },
    },
    {
      path: '/forbidden',
      name: 'forbidden',
      component: () => import('@/views/user/ForbiddenView.vue'),
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
