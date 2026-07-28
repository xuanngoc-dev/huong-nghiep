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
        {
          path: 'chuyen-nganh',
          name: 'admin-chuyen-nganh',
          component: placeholder,
          meta: { title: 'Chuyên ngành', icon: 'Notebook' },
        },

        // Danh mục
        {
          path: 'danh-muc/tinh-thanh',
          name: 'admin-tinh-thanh',
          component: placeholder,
          meta: { title: 'Tỉnh thành', icon: 'Location' },
        },
        {
          path: 'danh-muc/khu-vuc',
          name: 'admin-khu-vuc',
          component: placeholder,
          meta: { title: 'Khu vực', icon: 'MapLocation' },
        },
        {
          path: 'danh-muc/truong-hoc',
          name: 'admin-truong-hoc',
          component: placeholder,
          meta: { title: 'Trường học', icon: 'School' },
        },
        {
          path: 'danh-muc/nganh-hoc',
          name: 'admin-nganh-hoc',
          component: placeholder,
          meta: { title: 'Ngành học', icon: 'Reading' },
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
