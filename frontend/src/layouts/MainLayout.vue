<template>
  <div class="layout">
    <header class="header">
      <div class="container header-inner">
        <RouterLink to="/" class="brand">Hướng Nghiệp</RouterLink>
        <nav class="nav">
          <RouterLink to="/careers">Nghề nghiệp</RouterLink>
          <RouterLink to="/assessments">Trắc nghiệm</RouterLink>
          <RouterLink to="/articles">Bài viết</RouterLink>
          <RouterLink v-if="auth.isAuthenticated" to="/profile">Hồ sơ</RouterLink>
          <RouterLink v-if="auth.isAdmin" to="/admin">CMS</RouterLink>
        </nav>
        <div class="actions">
          <template v-if="auth.isAuthenticated">
            <span class="muted user-name">{{ auth.user?.name }}</span>
            <button class="btn btn-outline" type="button" @click="handleLogout">
              Đăng xuất
            </button>
          </template>
          <template v-else>
            <RouterLink class="btn btn-outline" to="/login">Đăng nhập</RouterLink>
            <RouterLink class="btn" to="/register">Đăng ký</RouterLink>
          </template>
        </div>
      </div>
    </header>

    <main class="main">
      <div class="container">
        <RouterView />
      </div>
    </main>

    <footer class="footer">
      <div class="container muted">
        © {{ year }} Hệ thống hướng nghiệp — Laravel API + Vue 3
      </div>
    </footer>
  </div>
</template>

<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const year = new Date().getFullYear()

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<style scoped>
.layout {
  min-height: 100vh;
  display: grid;
  grid-template-rows: auto 1fr auto;
}

.header {
  position: sticky;
  top: 0;
  z-index: 10;
  backdrop-filter: blur(10px);
  background: rgba(247, 250, 248, 0.9);
  border-bottom: 1px solid var(--border);
}

.header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 0;
}

.brand {
  font-weight: 700;
  font-size: 1.15rem;
  letter-spacing: -0.02em;
}

.nav {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.nav a {
  color: var(--muted);
}

.nav a.router-link-active {
  color: var(--accent);
  font-weight: 600;
}

.actions {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.user-name {
  font-size: 0.9rem;
}

.main {
  padding: 2rem 0 3rem;
}

.footer {
  padding: 1.25rem 0 2rem;
  border-top: 1px solid var(--border);
}

@media (max-width: 800px) {
  .header-inner {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
