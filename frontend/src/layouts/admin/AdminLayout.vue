<template>
  <el-container
    class="main-layout"
    :class="{
      'is-navbar-fixed': navbarFixed,
      'is-sidebar-fixed': sidebarFixed,
      'is-sidebar-overlay': isSidebarOverlay,
    }"
  >
    <div
      :key="asideMountKey"
      class="aside-slot"
      :style="{ width: asideSlotWidth }"
    >
      <el-aside
        :width="asidePanelWidth"
        class="aside"
        :class="{
          'is-fixed': sidebarFixed || isSidebarOverlayExpanded,
          'is-collapsed': collapsed,
          'is-overlay-expanded': isSidebarOverlayExpanded,
          'is-hover-expanded': hoverExpanded,
        }"
        @mouseenter="onAsideEnter"
        @mouseleave="onAsideLeave"
      >
        <div class="brand">
          <el-icon :size="22"><Monitor /></el-icon>
          <span class="brand-text" :class="{ 'is-hidden': collapsed }">Hướng Nghiệp</span>
        </div>

        <div class="aside-menu">
          <SideMenu :collapsed="collapsed" />
        </div>
      </el-aside>
    </div>

    <div
      v-if="showAsideMask"
      class="aside-mask"
      aria-hidden="true"
      @click="pinCollapse"
    />

    <el-container class="content-shell">
      <el-header
        class="header"
        :class="{ 'is-fixed': navbarFixed }"
      >
        <div class="header-left">
          <el-button text @click="togglePinnedCollapse">
            <el-icon :size="20">
              <Fold v-if="!pinnedCollapsed" />
              <Expand v-else />
            </el-icon>
          </el-button>
          <span class="page-title">{{ pageTitle }}</span>
        </div>

        <div class="header-right">
          <div class="header-datetime" :title="nowFullLabel">
            <span class="header-datetime__weekday">{{ nowWeekday }}</span>
            <span class="header-datetime__time">{{ nowTime }}</span>
            <span class="header-datetime__date">{{ nowDate }}</span>
          </div>

          <button
            type="button"
            class="header-search"
            aria-label="Lọc nhanh"
            @click="searchOpen = true"
          >
            <el-icon :size="16"><Search /></el-icon>
            <span class="header-search__placeholder">Lọc nhanh...</span>
            <kbd class="header-search__kbd">{{ searchShortcutLabel }}</kbd>
          </button>

          <el-switch
            v-model="isDark"
            inline-prompt
            active-text="🌙"
            inactive-text="☀️"
            @change="toggleDark"
          />

          <el-tooltip content="Thông báo" placement="bottom">
            <el-badge
              :value="unreadNotifications"
              :hidden="!unreadNotifications"
              class="header-badge"
            >
              <el-button text class="icon-btn" @click="notificationsOpen = true">
                <el-icon :size="20"><Bell /></el-icon>
              </el-button>
            </el-badge>
          </el-tooltip>

          <el-tooltip content="Cài đặt giao diện" placement="bottom">
            <el-button text class="icon-btn" @click="settingsOpen = true">
              <el-icon :size="20"><Setting /></el-icon>
            </el-button>
          </el-tooltip>

          <el-dropdown trigger="click" @command="onCommand">
            <span class="user-trigger">
              <el-avatar :size="32">{{ avatarLetter }}</el-avatar>
              <span class="user-name" :title="userFullName">
                <span class="user-name__full">{{ userFullName }}</span>
                <span class="user-name__short">{{ userShortName }}</span>
              </span>
              <el-icon><ArrowDown /></el-icon>
            </span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item disabled>
                  {{ authStore.user?.email }}
                </el-dropdown-item>
                <el-dropdown-item command="home">
                  Về trang chủ
                </el-dropdown-item>
                <el-dropdown-item divided command="logout">
                  Đăng xuất
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </el-header>

      <el-main class="main">
        <RouterView />
      </el-main>
    </el-container>

    <NotificationDrawer
      v-model="notificationsOpen"
      v-model:unread-count="unreadNotifications"
    />
    <LayoutSettingsDrawer v-model="settingsOpen" v-model:dark="isDark" />
    <QuickSearchModal v-model="searchOpen" />
  </el-container>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { RouterView, useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { ArrowDown, Bell, Expand, Fold, Monitor, Search, Setting } from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/auth'
import { useLayoutStore } from '@/stores/layout'
import SideMenu from '@/components/admin/SideMenu.vue'
import NotificationDrawer from '@/components/admin/NotificationDrawer.vue'
import LayoutSettingsDrawer from '@/components/admin/LayoutSettingsDrawer.vue'
import QuickSearchModal from '@/components/admin/QuickSearchModal.vue'

const COLLAPSE_BREAKPOINT = 992

const WEEKDAYS = [
  'Chủ Nhật',
  'Thứ Hai',
  'Thứ Ba',
  'Thứ Tư',
  'Thứ Năm',
  'Thứ Sáu',
  'Thứ Bảy',
]

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const layoutStore = useLayoutStore()
const { navbarFixed, sidebarPushContent } = storeToRefs(layoutStore)
const sidebarFixed = true

const pinnedCollapsed = ref(false)
const hoverExpanded = ref(false)
const settingsOpen = ref(false)
const notificationsOpen = ref(false)
const searchOpen = ref(false)
const unreadNotifications = ref(0)
const isDark = ref(document.documentElement.classList.contains('dark'))
const now = ref(new Date())
const asideMountKey = ref(0)

const isMac = /Mac|iPhone|iPad|iPod/.test(navigator.platform)
const searchShortcutLabel = isMac ? '⌘K' : 'Ctrl+K'

const collapsed = computed(() => pinnedCollapsed.value && !hoverExpanded.value)
const isSidebarOverlay = computed(() => !sidebarPushContent.value)
const isSidebarOverlayExpanded = computed(() => {
  if (hoverExpanded.value) return true
  return isSidebarOverlay.value && !pinnedCollapsed.value
})
const showAsideMask = computed(
  () => isSidebarOverlay.value && !pinnedCollapsed.value,
)

const asideSlotWidth = computed(() => {
  if (pinnedCollapsed.value) return '64px'
  if (isSidebarOverlay.value) return '64px'
  return '220px'
})
const asidePanelWidth = computed(() => (collapsed.value ? '64px' : '220px'))

let hoverLeaveTimer = null

function clearHoverLeaveTimer() {
  if (hoverLeaveTimer != null) {
    window.clearTimeout(hoverLeaveTimer)
    hoverLeaveTimer = null
  }
}

function onAsideEnter() {
  if (!pinnedCollapsed.value) return
  clearHoverLeaveTimer()
  hoverExpanded.value = true
}

function onAsideLeave() {
  if (!pinnedCollapsed.value) return
  clearHoverLeaveTimer()
  hoverLeaveTimer = window.setTimeout(() => {
    hoverExpanded.value = false
    hoverLeaveTimer = null
  }, 180)
}

function togglePinnedCollapse() {
  clearHoverLeaveTimer()
  hoverExpanded.value = false
  pinnedCollapsed.value = !pinnedCollapsed.value
}

function pinCollapse() {
  clearHoverLeaveTimer()
  hoverExpanded.value = false
  pinnedCollapsed.value = true
}

watch(sidebarPushContent, async () => {
  clearHoverLeaveTimer()
  hoverExpanded.value = false
  asideMountKey.value += 1
  await nextTick()
})

const pageTitle = computed(() => route.meta.title || 'CMS')
const userFullName = computed(() => String(authStore.user?.name || '').trim())
const userShortName = computed(() => {
  const parts = userFullName.value.split(/\s+/).filter(Boolean)
  return parts.length ? parts[parts.length - 1] : 'User'
})
const avatarLetter = computed(() => (userShortName.value || 'U').charAt(0).toUpperCase())

const nowWeekday = computed(() => WEEKDAYS[now.value.getDay()])
const nowTime = computed(() =>
  now.value.toLocaleTimeString('vi-VN', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
  }),
)
const nowDate = computed(() =>
  now.value.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }),
)
const nowFullLabel = computed(
  () => `${nowWeekday.value}, ${nowTime.value} — ${nowDate.value}`,
)

let mediaQuery = null
let clockTimer = null

function syncAdminViewport() {
  const root = document.documentElement
  const scale = Math.max(0.8, Math.min(1.25, Number(layoutStore.uiScale) / 100)) || 1
  // Sau zoom, bề ngang/dọc visual luôn khớp đúng cửa sổ trình duyệt (100%)
  root.style.setProperty('--admin-layout-width', `${window.innerWidth / scale}px`)
  root.style.setProperty('--admin-layout-height', `${window.innerHeight / scale}px`)
}

function syncCollapseByViewport(e) {
  clearHoverLeaveTimer()
  hoverExpanded.value = false
  pinnedCollapsed.value = e.matches
}

function onGlobalKeydown(event) {
  const key = event.key?.toLowerCase()
  const withModifier = event.metaKey || event.ctrlKey
  if (withModifier && key === 'k') {
    event.preventDefault()
    searchOpen.value = true
  }
}

function toggleDark(val) {
  document.documentElement.classList.toggle('dark', val)
  localStorage.setItem('darkMode', val ? '1' : '0')
}

async function onCommand(cmd) {
  if (cmd === 'home') {
    router.push('/')
    return
  }
  if (cmd === 'logout') {
    await authStore.logout()
    ElMessage.success('Đã đăng xuất')
    router.push({ name: 'login' })
  }
}

onMounted(() => {
  document.documentElement.classList.add('is-admin-layout')
  layoutStore.syncAppearance()
  syncAdminViewport()

  const saved = localStorage.getItem('darkMode')
  if (saved === '1') {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }

  mediaQuery = window.matchMedia(`(max-width: ${COLLAPSE_BREAKPOINT - 1}px)`)
  pinnedCollapsed.value = mediaQuery.matches
  mediaQuery.addEventListener('change', syncCollapseByViewport)
  window.addEventListener('keydown', onGlobalKeydown)
  window.addEventListener('resize', syncAdminViewport)

  clockTimer = window.setInterval(() => {
    now.value = new Date()
  }, 1000)
})

watch(() => layoutStore.uiScale, () => {
  nextTick(syncAdminViewport)
})

onUnmounted(() => {
  const root = document.documentElement
  root.classList.remove('is-admin-layout')
  root.style.removeProperty('--admin-font')
  root.style.removeProperty('--admin-font-size')
  root.style.removeProperty('--admin-ui-scale')
  root.style.removeProperty('--admin-layout-width')
  root.style.removeProperty('--admin-layout-height')
  root.style.removeProperty('--el-font-size-extra-small')
  root.style.removeProperty('--el-font-size-small')
  root.style.removeProperty('--el-font-size-base')
  root.style.removeProperty('--el-font-size-medium')
  root.style.removeProperty('--el-font-size-large')
  root.style.removeProperty('--el-font-size-extra-large')
  root.style.removeProperty('--el-font-line-height-primary')
  mediaQuery?.removeEventListener('change', syncCollapseByViewport)
  window.removeEventListener('keydown', onGlobalKeydown)
  window.removeEventListener('resize', syncAdminViewport)
  clearHoverLeaveTimer()
  if (clockTimer != null) window.clearInterval(clockTimer)
})
</script>

<style scoped lang="scss">
.main-layout {
  min-height: var(--admin-layout-height);
  position: relative;
  font-family: var(--admin-font);
  font-size: var(--el-font-size-base);
  font-weight: 300;
  letter-spacing: -0.02em;
  overflow-x: hidden;

  &.is-navbar-fixed,
  &.is-sidebar-fixed {
    height: var(--admin-layout-height);
    overflow: hidden;
  }
}

.aside-slot {
  flex-shrink: 0;
  position: relative;
  transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}

.aside-mask {
  position: fixed;
  inset: 0;
  z-index: 90;
  background: var(--el-overlay-color-lighter);
  animation: aside-mask-in 0.2s ease;
}

@keyframes aside-mask-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

.aside {
  border-right: 1px solid var(--el-border-color);
  background: var(--el-bg-color);
  transition:
    width 0.28s cubic-bezier(0.4, 0, 0.2, 1),
    box-shadow 0.28s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;

  &.is-fixed {
    height: var(--admin-layout-height);
    position: sticky;
    top: 0;
    overflow: hidden;
  }

  &.is-overlay-expanded {
    position: fixed;
    left: 0;
    top: 0;
    height: var(--admin-layout-height);
    z-index: 100;
    overflow: hidden;
    box-shadow: var(--el-box-shadow-dark);
  }

  &.is-hover-expanded {
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
  }
}

.aside-menu {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: thin;
  scrollbar-color: var(--el-border-color) transparent;

  &::-webkit-scrollbar {
    width: 2px;
  }

  &::-webkit-scrollbar-thumb {
    background-color: var(--el-border-color);
    border-radius: 2px;
  }
}

.brand {
  height: 60px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 18px;
  font-weight: 300;
  font-size: 1.125rem;
  letter-spacing: -0.03em;
  color: var(--el-color-primary);
  border-bottom: 1px solid var(--el-border-color);
  overflow: hidden;
  white-space: nowrap;
  transition: padding 0.28s cubic-bezier(0.4, 0, 0.2, 1), justify-content 0.28s ease;

  .aside.is-collapsed & {
    justify-content: center;
    padding: 0;
  }
}

.brand-text {
  display: inline-block;
  max-width: 160px;
  opacity: 1;
  transform: translateX(0);
  transition:
    opacity 0.2s ease 0.06s,
    transform 0.28s cubic-bezier(0.4, 0, 0.2, 1),
    max-width 0.28s cubic-bezier(0.4, 0, 0.2, 1);

  &.is-hidden {
    max-width: 0;
    opacity: 0;
    transform: translateX(-6px);
    transition:
      opacity 0.15s ease,
      transform 0.2s ease,
      max-width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
  }
}

.content-shell {
  min-width: 0;
  min-height: 0;

  .is-navbar-fixed &,
  .is-sidebar-fixed & {
    height: var(--admin-layout-height);
  }

  .is-navbar-fixed & {
    overflow: hidden;
  }

  .is-sidebar-fixed:not(.is-navbar-fixed) & {
    overflow-x: hidden;
    overflow-y: auto;
  }
}

.header {
  display: flex;
  align-items: center;
  gap: 16px;
  border-bottom: 1px solid var(--el-border-color);
  background: var(--el-bg-color);
  flex-shrink: 0;

  &.is-fixed {
    position: sticky;
    top: 0;
    z-index: 20;
  }
}

.header-search {
  flex: 0 1 280px;
  max-width: 280px;
  min-width: 0;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  border: 1px solid var(--el-border-color);
  border-radius: 8px;
  background: var(--el-fill-color-light);
  color: var(--el-text-color-secondary);
  cursor: pointer;
  transition: border-color 0.15s ease, background 0.15s ease;

  &:hover {
    border-color: var(--el-color-primary-light-5);
    background: var(--el-fill-color);
  }
}

.header-search__placeholder {
  flex: 1;
  min-width: 0;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 300;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.header-search__kbd {
  flex-shrink: 0;
  padding: 1px 5px;
  border-radius: 4px;
  border: 1px solid var(--el-border-color);
  background: var(--el-bg-color);
  font-size: 0.75rem;
  line-height: 1.4;
  color: var(--el-text-color-placeholder);
  font-family: inherit;
  font-weight: 300;
}

.header-datetime {
  display: flex;
  align-items: baseline;
  gap: 12px;
  flex-shrink: 0;
  padding: 4px 2px;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
  color: var(--el-text-color-regular);
}

.header-datetime__weekday {
  font-size: 0.875rem;
  font-weight: 300;
  color: var(--el-text-color-primary);
}

.header-datetime__time {
  font-size: 0.9375rem;
  font-weight: 300;
  letter-spacing: -0.01em;
  color: var(--el-color-primary);
}

.header-datetime__date {
  font-size: 0.875rem;
  font-weight: 300;
  color: var(--el-text-color-secondary);
}

.header-left,
.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.header-right {
  margin-left: auto;
  gap: 10px;
}

@media (max-width: 991px) {
  .header-search {
    flex-basis: 40px;
    max-width: 40px;
    justify-content: center;
    padding: 6px;
  }

  .header-search__placeholder,
  .header-search__kbd {
    display: none;
  }

  .header-datetime__weekday {
    display: none;
  }
}

@media (max-width: 640px) {
  .header-datetime {
    display: none;
  }
}

.icon-btn {
  padding: 8px;
}

.header-badge {
  :deep(.el-badge__content) {
    transform: translateY(-2px) translateX(2px);
  }
}

.page-title {
  font-size: 1rem;
  font-weight: 300;
  letter-spacing: -0.025em;
}

.user-trigger {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  max-width: 220px;
  outline: none;
}

.user-name {
  flex: 1;
  min-width: 0;
  max-width: 160px;
  font-size: 0.9375rem;
  font-weight: 300;
  letter-spacing: -0.02em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-name__full {
  display: inline;
}

.user-name__short {
  display: none;
}

@media (max-width: 640px) {
  .user-trigger {
    max-width: 120px;
  }

  .user-name {
    max-width: 72px;
  }

  .user-name__full {
    display: none;
  }

  .user-name__short {
    display: inline;
  }
}

.main {
  background: var(--el-bg-color-page);
  min-height: 0;
  overflow-x: hidden;

  .is-navbar-fixed & {
    overflow-x: hidden;
    overflow-y: auto;
  }
}
</style>

<style lang="scss">
/* Admin system typography — áp dụng cả dialog/popover teleport ra body */
html.is-admin-layout {
  --admin-font: "Be Vietnam Pro", "Roboto", "Segoe UI", sans-serif;
  --admin-font-size: 16px;
  --admin-ui-scale: 1;
  --admin-layout-width: 100%;
  --admin-layout-height: 100vh;
  --el-font-family: var(--admin-font);
  --el-font-size-extra-small: 0.8125rem;
  --el-font-size-small: 0.875rem;
  --el-font-size-base: 1rem;
  --el-font-size-medium: 1rem;
  --el-font-size-large: 1.125rem;
  --el-font-size-extra-large: 1.25rem;
  --el-font-line-height-primary: 1.5;
  font-size: var(--admin-font-size);
  zoom: var(--admin-ui-scale);
  /* Width/height được JS sync theo innerWidth/Height để visual luôn = 100% cửa sổ */
  width: var(--admin-layout-width);
  min-height: var(--admin-layout-height);
  overflow-x: hidden;
  background-color: var(--el-bg-color-page);
}

/* Chế độ sáng: chữ đen tuyền */
html.is-admin-layout:not(.dark) {
  --el-text-color-primary: #000000;
  --el-text-color-regular: #000000;
  --el-text-color-secondary: #000000;
  --el-text-color-placeholder: #333333;
  --el-color-black: #000000;
  color: #000000;
}

html.is-admin-layout:not(.dark) body,
html.is-admin-layout:not(.dark) .main-layout,
html.is-admin-layout:not(.dark) .el-menu,
html.is-admin-layout:not(.dark) .el-menu-item,
html.is-admin-layout:not(.dark) .el-table,
html.is-admin-layout:not(.dark) .el-table th.el-table__cell,
html.is-admin-layout:not(.dark) .el-table td.el-table__cell,
html.is-admin-layout:not(.dark) .el-form-item__label,
html.is-admin-layout:not(.dark) .el-dialog,
html.is-admin-layout:not(.dark) .el-dialog__title,
html.is-admin-layout:not(.dark) .el-dropdown-menu,
html.is-admin-layout:not(.dark) .el-pagination,
html.is-admin-layout:not(.dark) .page-title,
html.is-admin-layout:not(.dark) .user-name,
html.is-admin-layout:not(.dark) .header-datetime,
html.is-admin-layout:not(.dark) .header-datetime__weekday,
html.is-admin-layout:not(.dark) .header-datetime__date,
html.is-admin-layout:not(.dark) h1,
html.is-admin-layout:not(.dark) h2,
html.is-admin-layout:not(.dark) h3 {
  color: #000000;
}

html.is-admin-layout:not(.dark) .el-menu-item.is-active {
  color: var(--el-color-primary);
  background-color: var(--el-color-primary-light-9);
}

html.is-admin-layout:not(.dark) .el-menu-item.is-active .el-icon {
  color: var(--el-color-primary);
}

html.is-admin-layout body {
  font-family: var(--admin-font);
  font-size: 1rem;
  font-weight: 300;
  letter-spacing: -0.02em;
  width: 100%;
  min-height: var(--admin-layout-height);
  overflow-x: hidden;
  background: var(--el-bg-color-page);
}

html.is-admin-layout #app {
  width: 100%;
  min-height: var(--admin-layout-height);
  overflow-x: hidden;
  background: var(--el-bg-color-page);
}

html.is-admin-layout .el-button,
html.is-admin-layout .el-menu,
html.is-admin-layout .el-menu-item,
html.is-admin-layout .el-sub-menu__title,
html.is-admin-layout .el-table,
html.is-admin-layout .el-form,
html.is-admin-layout .el-form-item__label,
html.is-admin-layout .el-input,
html.is-admin-layout .el-input__inner,
html.is-admin-layout .el-select,
html.is-admin-layout .el-dialog,
html.is-admin-layout .el-drawer,
html.is-admin-layout .el-dropdown-menu,
html.is-admin-layout .el-pagination,
html.is-admin-layout .el-radio,
html.is-admin-layout .el-checkbox,
html.is-admin-layout .el-textarea,
html.is-admin-layout .main-layout {
  font-family: var(--admin-font);
  font-size: var(--el-font-size-base);
  letter-spacing: -0.02em;
}

html.is-admin-layout .el-button--small,
html.is-admin-layout .el-input--small,
html.is-admin-layout .el-select--small {
  font-size: var(--el-font-size-extra-small);
}

html.is-admin-layout .el-dialog__title,
html.is-admin-layout .el-drawer__title {
  font-size: var(--el-font-size-large);
}

html.is-admin-layout .el-button,
html.is-admin-layout .el-menu-item,
html.is-admin-layout .el-table,
html.is-admin-layout .el-form-item__label,
html.is-admin-layout .el-pagination {
  font-weight: 300;
}

html.is-admin-layout .el-menu-item.is-active,
html.is-admin-layout .el-dialog__title,
html.is-admin-layout h1,
html.is-admin-layout h2,
html.is-admin-layout h3 {
  font-weight: 400;
  letter-spacing: -0.025em;
}

/* Toast ElMessage — nền đậm, chữ trắng dễ đọc (mọi loại, cả API lẫn gọi trực tiếp) */
html.is-admin-layout .el-message {
  min-width: 280px;
  max-width: min(520px, calc(100vw - 32px));
  padding: 12px 16px;
  border-width: 1px;
  border-style: solid;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);

  .el-message__content {
    color: #ffffff;
    font-weight: 400;
    font-size: 0.9375rem;
    line-height: 1.45;
  }

  .el-message__icon {
    color: #ffffff;
  }

  .el-message__closeBtn {
    color: rgba(255, 255, 255, 0.85);

    &:hover {
      color: #ffffff;
    }
  }

  &.el-message--success {
    --el-message-bg-color: #0b6e3f;
    --el-message-border-color: #095c34;
    --el-message-text-color: #ffffff;
    background-color: #0b6e3f !important;
    border-color: #095c34 !important;
  }

  &.el-message--error {
    --el-message-bg-color: #b42318;
    --el-message-border-color: #912018;
    --el-message-text-color: #ffffff;
    background-color: #b42318 !important;
    border-color: #912018 !important;
  }

  &.el-message--warning {
    --el-message-bg-color: #b54708;
    --el-message-border-color: #93370d;
    --el-message-text-color: #ffffff;
    background-color: #b54708 !important;
    border-color: #93370d !important;
  }

  &.el-message--info {
    --el-message-bg-color: #175cd3;
    --el-message-border-color: #1849a9;
    --el-message-text-color: #ffffff;
    background-color: #175cd3 !important;
    border-color: #1849a9 !important;
  }
}
</style>
