<template>
  <div class="layout">
    <header class="header">
      <div class="container header-inner">
        <RouterLink to="/" class="brand" @click="closeMenu" aria-label="Hướng Nghiệp">
          <img
            class="brand__logo brand__logo--full"
            src="/images/logos/logo-mark.svg"
            alt=""
            width="32"
            height="32"
          />
          <span class="brand__text">Hướng Nghiệp</span>
          <img
            class="brand__logo brand__logo--mark"
            src="/images/logos/logo-mark-wide.svg"
            alt="Hướng Nghiệp"
            width="108"
            height="40"
          />
        </RouterLink>

        <nav class="nav nav--desktop" aria-label="Menu chính">
          <RouterLink
            v-for="item in visibleMenu"
            :key="item.to"
            :to="item.to"
          >
            {{ item.label }}
          </RouterLink>
        </nav>

        <div class="header-right">
          <div class="actions">
            <template v-if="auth.isAuthenticated">
              <span class="muted user-name">{{ auth.user?.name }}</span>
              <button class="btn btn-outline btn-sm" type="button" @click="handleLogout">
                Đăng xuất
              </button>
            </template>
            <template v-else>
              <RouterLink class="btn btn-outline btn-sm" to="/login">Đăng nhập</RouterLink>
              <RouterLink class="btn btn-sm actions__register" to="/register">Đăng ký</RouterLink>
            </template>
          </div>

          <button
            type="button"
            class="menu-toggle"
            :aria-expanded="menuOpen"
            aria-controls="mobile-nav"
            :aria-label="menuOpen ? 'Đóng menu' : 'Mở menu'"
            @click="toggleMenu"
          >
            <el-icon :size="22">
              <Close v-if="menuOpen" />
              <Menu v-else />
            </el-icon>
          </button>
        </div>
      </div>

      <nav
        id="mobile-nav"
        class="nav nav--mobile"
        :class="{ 'is-open': menuOpen }"
        aria-label="Menu chính"
      >
        <div class="container nav-mobile-inner">
          <RouterLink
            v-for="item in visibleMenu"
            :key="item.to"
            :to="item.to"
            class="nav-mobile-link"
            @click="closeMenu"
          >
            {{ item.label }}
          </RouterLink>

          <div class="nav-mobile-actions">
            <template v-if="auth.isAuthenticated">
              <p class="muted user-name user-name--mobile">{{ auth.user?.name }}</p>
              <button class="btn btn-outline" type="button" @click="handleLogoutMobile">
                Đăng xuất
              </button>
            </template>
            <template v-else>
              <RouterLink class="btn btn-outline" to="/login" @click="closeMenu">
                Đăng nhập
              </RouterLink>
              <RouterLink class="btn" to="/register" @click="closeMenu">
                Đăng ký
              </RouterLink>
            </template>
          </div>
        </div>
      </nav>
    </header>

    <div
      v-if="menuOpen"
      class="nav-backdrop"
      aria-hidden="true"
      @click="closeMenu"
    />

    <main class="main" :class="{ 'main--flush': isHome }">
      <div :class="{ container: !isHome }">
        <RouterView />
      </div>
    </main>

    <footer class="footer">
      <div class="container footer-inner">
        <div class="footer-brand">
          <p class="footer-title">Hướng Nghiệp</p>
          <p class="muted">
            Nền tảng hỗ trợ khám phá bản thân, chọn ngành học và định hướng nghề nghiệp.
          </p>
        </div>
        <div class="footer-links">
          <RouterLink
            v-for="item in publicMenu"
            :key="item.to"
            :to="item.to"
          >
            {{ item.label }}
          </RouterLink>
        </div>
        <p class="muted footer-copy">© {{ year }} Hướng Nghiệp. Tất cả quyền được bảo lưu.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { Close, Menu } from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/auth'
import userMenu from '@/data/user-menu.json'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const year = new Date().getFullYear()
const isHome = computed(() => route.name === 'home')
const menuOpen = ref(false)

const publicMenu = computed(() =>
  userMenu.filter((item) => !item.auth && !item.admin),
)

const visibleMenu = computed(() =>
  userMenu.filter((item) => {
    if (item.admin) return auth.isAdmin
    if (item.auth) return auth.isAuthenticated
    return true
  }),
)

function toggleMenu() {
  menuOpen.value = !menuOpen.value
}

function closeMenu() {
  menuOpen.value = false
}

function onKeydown(event) {
  if (event.key === 'Escape') closeMenu()
}

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}

async function handleLogoutMobile() {
  closeMenu()
  await handleLogout()
}

watch(
  () => route.fullPath,
  () => closeMenu(),
)

watch(menuOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
  if (open) {
    window.addEventListener('keydown', onKeydown)
  } else {
    window.removeEventListener('keydown', onKeydown)
  }
})

function onResize() {
  if (window.innerWidth >= 1100) closeMenu()
}

onMounted(() => {
  window.addEventListener('resize', onResize)
})

onBeforeUnmount(() => {
  document.body.style.overflow = ''
  window.removeEventListener('keydown', onKeydown)
  window.removeEventListener('resize', onResize)
})
</script>

<style scoped>
.layout {
  min-height: 100vh;
  display: grid;
  grid-template-rows: auto 1fr auto;
  font-family: var(--font);
  font-weight: 400;
  -webkit-font-smoothing: antialiased;
}

.header {
  position: sticky;
  top: 0;
  z-index: 100;
  backdrop-filter: blur(10px);
  background: rgba(247, 250, 248, 0.95);
  border-bottom: 1px solid var(--border);
}

.header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding-top: 0.55rem;
  padding-bottom: 0.55rem;
  min-height: 3.25rem;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.brand {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  line-height: 0;
  color: var(--text);
}

.brand__logo {
  display: block;
  height: 2rem;
  width: auto;
}

.brand__logo--full {
  display: none;
  width: 2rem;
  height: 2rem;
}

.brand__logo--mark {
  display: block;
  height: 2.15rem;
  width: auto;
}

.brand__text {
  display: none;
  font-weight: 500;
  font-size: 1.05rem;
  letter-spacing: -0.02em;
  line-height: 1.2;
  white-space: nowrap;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
  margin-left: auto;
}

.nav--desktop {
  display: none;
  flex: 1;
  justify-content: center;
  flex-wrap: wrap;
  gap: 0.35rem 1rem;
  min-width: 0;
}

.nav--desktop a {
  color: var(--muted);
  font-size: 0.95rem;
  white-space: nowrap;
}

.nav--desktop a.router-link-active {
  color: var(--accent);
  font-weight: 500;
}

.actions {
  display: none;
  align-items: center;
  gap: var(--space-btn);
}

.user-name {
  max-width: 8rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.9rem;
}

.btn-sm {
  padding: 0.4rem 0.85rem;
  font-size: 0.9rem;
}

.menu-toggle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  padding: 0;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: #fff;
  color: var(--text);
  cursor: pointer;
}

.menu-toggle:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.nav-backdrop {
  position: fixed;
  inset: 0;
  z-index: 90;
  background: rgba(26, 46, 36, 0.28);
}

.nav--mobile {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 101;
  display: block;
  max-height: min(75vh, 28rem);
  overflow-x: hidden;
  overflow-y: auto;
  background: #fff;
  border-bottom: 1px solid var(--border);
  box-shadow: 0 16px 32px rgba(26, 46, 36, 0.12);
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transform: translateY(-0.35rem);
  transition:
    opacity 0.22s ease,
    transform 0.22s ease,
    visibility 0.22s ease;
}

.nav--mobile.is-open {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
  transform: translateY(0);
}

.nav-mobile-inner {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding-top: 0.75rem;
  padding-bottom: 1rem;
}

.nav-mobile-link {
  display: block;
  padding: 0.75rem 0.25rem;
  color: var(--text);
  font-size: 1rem;
  font-weight: 400;
  border-bottom: 1px solid var(--border);
}

.nav-mobile-link.router-link-active {
  color: var(--accent);
  font-weight: 500;
}

.nav-mobile-actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-btn);
  padding-top: 1rem;
}

.user-name--mobile {
  width: 100%;
  margin: 0;
  max-width: none;
}

.main {
  padding: var(--space-section) 0 calc(var(--space-section) * 2);
  min-width: 0;
}

.main--flush {
  padding: 0 0 var(--space-section);
}

.footer {
  padding: var(--space-section) 0;
  border-top: 1px solid var(--border);
  background: #fff;
}

.footer-inner {
  display: grid;
  gap: 1rem;
}

.footer-title {
  margin: 0 0 0.4rem;
  font-weight: 500;
  font-size: 1.05rem;
}

.footer-brand .muted {
  margin: 0;
  max-width: 28rem;
  line-height: 1.5;
  font-size: 0.92rem;
}

.footer-links {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1rem;
}

.footer-links a {
  color: var(--muted);
  font-size: 0.92rem;
}

.footer-links a:hover {
  color: var(--accent);
}

.footer-copy {
  margin: 0;
  font-size: 0.85rem;
}

/* Tablet: hiện actions, vẫn dùng menu toggle vì nhiều mục */
@media (min-width: 640px) {
  .brand__logo--full,
  .brand__text {
    display: block;
  }

  .brand__logo--mark {
    display: none;
  }

  .actions {
    display: flex;
  }

  .nav-mobile-actions {
    display: none;
  }
}

/* Desktop lớn: nav ngang, ẩn hamburger */
@media (min-width: 1100px) {
  .nav--desktop {
    display: flex;
  }

  .menu-toggle,
  .nav--mobile,
  .nav-backdrop {
    display: none !important;
  }

  .header-right {
    margin-left: 0;
  }
}

@media (max-width: 639px) {
  .actions__register {
    display: none;
  }

  .header-inner {
    gap: 0.75rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .nav--mobile {
    transition: none;
  }
}
</style>
