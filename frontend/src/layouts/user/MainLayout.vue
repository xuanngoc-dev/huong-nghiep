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
          <div v-if="auth.isAuthenticated" class="user-wallet" aria-label="Số dư tài khoản">
            <el-tooltip content="Edu Coin" placement="bottom" :show-after="150">
              <span class="user-wallet__item">
                <el-icon :size="16" aria-hidden="true"><Coin /></el-icon>
                <span>{{ formatBalance(eduCoin) }}</span>
              </span>
            </el-tooltip>
            <el-tooltip content="Xu hệ thống" placement="bottom" :show-after="150">
              <span class="user-wallet__item">
                <el-icon :size="16" aria-hidden="true"><Medal /></el-icon>
                <span>{{ formatBalance(xuHeThong) }}</span>
              </span>
            </el-tooltip>
          </div>

          <div class="actions">
            <template v-if="auth.isAuthenticated">
              <el-dropdown
                trigger="hover"
                placement="bottom-end"
                popper-class="user-menu-popper"
                @command="onUserCommand"
              >
                <button type="button" class="user-menu-trigger">
                  <span class="user-name">{{ auth.user?.name }}</span>
                  <el-icon class="user-menu-caret" :size="14">
                    <ArrowDown />
                  </el-icon>
                </button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item command="profile" :icon="User">
                      Trang cá nhân
                    </el-dropdown-item>
                    <el-dropdown-item divided command="logout" :icon="SwitchButton">
                      Đăng xuất
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
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
              <div class="user-wallet user-wallet--mobile" aria-label="Số dư tài khoản">
                <span class="user-wallet__item" title="Edu Coin">
                  <el-icon :size="16" aria-hidden="true"><Coin /></el-icon>
                  <span>{{ formatBalance(eduCoin) }}</span>
                </span>
                <span class="user-wallet__item" title="Xu hệ thống">
                  <el-icon :size="16" aria-hidden="true"><Medal /></el-icon>
                  <span>{{ formatBalance(xuHeThong) }}</span>
                </span>
              </div>
              <p class="muted user-name user-name--mobile">{{ auth.user?.name }}</p>
              <RouterLink
                class="btn btn-outline"
                :to="{ name: 'profile' }"
                @click="closeMenu"
              >
                Trang cá nhân
              </RouterLink>
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

    <footer class="footer" data-aos="fade-up" data-aos-duration="700">
      <div class="container footer-inner">
        <div class="footer-brand wow animate__fadeInUp" data-wow-duration="0.8s">
          <p class="footer-title">Hướng Nghiệp</p>
          <p class="muted">
            Nền tảng hỗ trợ khám phá bản thân, chọn ngành học và định hướng nghề nghiệp.
          </p>
        </div>
        <div
          class="footer-links wow animate__fadeInUp"
          data-wow-delay="0.12s"
          data-wow-duration="0.8s"
        >
          <RouterLink
            v-for="item in publicMenu"
            :key="item.to"
            :to="item.to"
          >
            {{ item.label }}
          </RouterLink>
        </div>
        <p
          class="muted footer-copy wow animate__fadeInUp"
          data-wow-delay="0.2s"
          data-wow-duration="0.7s"
        >
          © {{ year }} Hướng Nghiệp. Tất cả quyền được bảo lưu.
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { ArrowDown, Close, Coin, Medal, Menu, SwitchButton, User } from '@element-plus/icons-vue'
import { useScrollReveal } from '@/composables/useScrollReveal'
import { useAuthStore } from '@/stores/auth'
import userMenu from '@/data/user-menu.json'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const year = new Date().getFullYear()
const isHome = computed(() => route.name === 'home')
const menuOpen = ref(false)
const eduCoin = computed(() => Number(auth.user?.edu_coin) || 0)
const xuHeThong = computed(() => Number(auth.user?.xu_he_thong) || 0)

function formatBalance(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}

useScrollReveal()

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

function onUserCommand(command) {
  if (command === 'profile') {
    router.push({ name: 'profile' })
    return
  }
  if (command === 'logout') {
    handleLogout()
  }
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
  document.documentElement.classList.add('is-user-layout')
  window.addEventListener('resize', onResize)
  if (auth.isAuthenticated) {
    auth.fetchMe().catch(() => {})
  }
})

onBeforeUnmount(() => {
  document.documentElement.classList.remove('is-user-layout')
  document.body.style.overflow = ''
  window.removeEventListener('keydown', onKeydown)
  window.removeEventListener('resize', onResize)
})
</script>

<style scoped>
.layout {
  --text: #0f241a;
  --muted: #3d564b;
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  --el-font-family: var(--font);
  min-height: 100vh;
  display: grid;
  grid-template-rows: auto 1fr auto;
  color: var(--text);
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.layout :deep(h1),
.layout :deep(h2),
.layout :deep(h3),
.layout :deep(h4) {
  color: var(--text);
  font-family: var(--font);
  font-weight: 400;
  letter-spacing: -0.025em;
  text-wrap: balance;
}

.layout :deep(p),
.layout :deep(li),
.layout :deep(label),
.layout :deep(span),
.layout :deep(a),
.layout :deep(button),
.layout :deep(input),
.layout :deep(textarea) {
  font-family: var(--font);
  font-weight: 300;
  letter-spacing: -0.02em;
  text-rendering: optimizeLegibility;
}

.layout :deep(.muted) {
  color: var(--muted);
}

.header {
  position: sticky;
  top: 0;
  z-index: 100;
  backdrop-filter: blur(10px);
  background: rgba(247, 250, 248, 0.97);
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
  font-weight: 400;
  font-size: 16px;
  letter-spacing: -0.03em;
  line-height: 1.2;
  white-space: nowrap;
  color: var(--text);
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
  font-size: 16px;
  font-weight: 300;
  white-space: nowrap;
}

.nav--desktop a.router-link-active {
  color: var(--accent);
  font-weight: 400;
}

.user-wallet {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  flex-shrink: 0;
}

.user-wallet__item {
  display: inline-flex;
  align-items: center;
  gap: 0.28rem;
  padding: 0.28rem 0.55rem;
  border-radius: 999px;
  background: var(--accent-soft);
  color: var(--accent);
  font-size: 16px;
  font-weight: 300;
  line-height: 1;
  white-space: nowrap;
  cursor: default;
}

.user-wallet--mobile {
  width: 100%;
  margin-bottom: 0.15rem;
}

.user-menu-trigger {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  max-width: 14rem;
  padding: 0.35rem 0.65rem;
  border: 0;
  border-radius: 999px;
  background: transparent;
  color: var(--text);
  cursor: pointer;
  outline: none;
}

.user-menu-trigger:hover,
.user-menu-trigger:focus-visible,
.user-menu-trigger[aria-expanded="true"] {
  background: var(--accent-soft);
  color: var(--accent);
}

.user-menu-caret {
  flex-shrink: 0;
  transition: transform 0.18s ease;
}

.user-menu-trigger[aria-expanded="true"] .user-menu-caret {
  transform: rotate(180deg);
}

.user-name {
  max-width: 9.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 16px;
  font-weight: 300;
}

.btn-sm {
  padding: 0.4rem 0.85rem;
  font-size: 16px;
  font-weight: 300;
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
  font-size: 16px;
  font-weight: 300;
  border-bottom: 1px solid var(--border);
}

.nav-mobile-link.router-link-active {
  color: var(--accent);
  font-weight: 400;
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
  font-weight: 400;
  font-size: 16px;
  letter-spacing: -0.03em;
}

.footer-brand .muted {
  margin: 0;
  max-width: 28rem;
  line-height: 1.55;
  font-size: 16px;
  font-weight: 300;
}

.footer-links {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1rem;
}

.footer-links a {
  color: var(--muted);
  font-size: 16px;
  font-weight: 300;
}

.footer-links a:hover {
  color: var(--accent);
}

.footer-copy {
  margin: 0;
  font-size: 16px;
  font-weight: 300;
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

  .user-wallet {
    gap: 0.35rem;
  }

  .user-wallet__item {
    padding: 0.22rem 0.45rem;
    font-size: 14px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .nav--mobile,
  .user-menu-caret {
    transition: none;
  }

  .layout :deep(.wow),
  .layout :deep([data-aos]) {
    animation: none !important;
    transition: none !important;
    opacity: 1 !important;
    transform: none !important;
    visibility: visible !important;
  }
}
</style>

<style>
html.is-user-layout {
  --el-font-family: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
}

html.is-user-layout .el-select-dropdown,
html.is-user-layout .el-picker-panel,
html.is-user-layout .el-popper,
html.is-user-layout .el-dropdown-menu,
html.is-user-layout .el-select-dropdown__item,
html.is-user-layout .el-picker-panel__content {
  font-family: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.user-menu-popper.el-popper {
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 12px 28px rgba(26, 46, 36, 0.12);
  font-family: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  font-size: 16px;
  font-weight: 300;
}

.user-menu-popper .el-dropdown-menu {
  padding: 0.35rem;
  border: 0;
}

.user-menu-popper .el-dropdown-menu__item {
  border-radius: 8px;
  font-family: inherit;
  font-size: 16px;
  font-weight: 300;
  color: var(--text);
}

.user-menu-popper .el-dropdown-menu__item:hover,
.user-menu-popper .el-dropdown-menu__item:focus {
  background: var(--accent-soft);
  color: var(--accent);
}

.user-menu-popper .el-dropdown-menu__item .el-icon {
  color: inherit;
}
</style>
