<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <RouterLink to="/admin" class="brand">CMS Hướng Nghiệp</RouterLink>
      <nav class="nav">
        <RouterLink :to="{ name: 'admin-dashboard' }">Tổng quan</RouterLink>
        <RouterLink :to="{ name: 'admin-careers' }">Nghề nghiệp</RouterLink>
        <RouterLink :to="{ name: 'admin-articles' }">Bài viết</RouterLink>
        <RouterLink :to="{ name: 'admin-assessments' }">Trắc nghiệm</RouterLink>
      </nav>
      <div class="sidebar-footer">
        <RouterLink to="/">← Về trang chủ</RouterLink>
      </div>
    </aside>

    <div class="content">
      <header class="topbar">
        <div>
          <p class="muted role">Vai trò: {{ auth.role }}</p>
          <strong>{{ auth.user?.name }}</strong>
        </div>
        <button class="btn btn-outline" type="button" @click="handleLogout">Đăng xuất</button>
      </header>
      <main class="main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 240px 1fr;
}

.sidebar {
  background: #123527;
  color: #e8f5ee;
  padding: 1.25rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.brand {
  font-weight: 700;
  letter-spacing: -0.02em;
}

.nav {
  display: grid;
  gap: 0.35rem;
}

.nav a {
  padding: 0.65rem 0.75rem;
  border-radius: 10px;
  color: #c6ddd1;
}

.nav a.router-link-active {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
  font-weight: 600;
}

.sidebar-footer {
  margin-top: auto;
  font-size: 0.9rem;
  color: #9fbfb0;
}

.content {
  display: grid;
  grid-template-rows: auto 1fr;
  min-width: 0;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.8);
}

.role {
  margin: 0;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.main {
  padding: 1.5rem;
}

@media (max-width: 860px) {
  .admin-layout {
    grid-template-columns: 1fr;
  }

  .sidebar {
    min-height: auto;
  }
}
</style>
