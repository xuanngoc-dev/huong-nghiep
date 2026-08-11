import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

const STORAGE_KEY = 'huong-nghiep-layout-settings'

export const FONT_OPTIONS = [
  {
    value: 'be-vietnam-pro',
    label: 'Be Vietnam Pro',
    stack: '"Be Vietnam Pro", "Roboto", "Segoe UI", sans-serif',
  },
  {
    value: 'roboto',
    label: 'Roboto',
    stack: '"Roboto", "Segoe UI", sans-serif',
  },
  {
    value: 'inter',
    label: 'Inter',
    stack: '"Inter", "Segoe UI", sans-serif',
  },
  {
    value: 'source-sans-3',
    label: 'Source Sans 3',
    stack: '"Source Sans 3", "Segoe UI", sans-serif',
  },
  {
    value: 'system',
    label: 'Font hệ thống',
    stack: 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
  },
]

const FONT_MAP = Object.fromEntries(FONT_OPTIONS.map((item) => [item.value, item.stack]))

const defaults = {
  menuSpread: true,
  navbarFixed: true,
  sidebarPushContent: true,
  fontFamily: 'be-vietnam-pro',
  fontSize: 16,
  uiScale: 100,
}

const FONT_SIZE_SCALE = {
  extraSmall: 13 / 16,
  small: 14 / 16,
  large: 18 / 16,
  extraLarge: 20 / 16,
}

function clampScale(value) {
  const n = Number(value)
  if (!Number.isFinite(n)) return defaults.uiScale
  return Math.min(125, Math.max(80, Math.round(n)))
}

function clampFontSize(value) {
  const n = Number(value)
  if (!Number.isFinite(n)) return defaults.fontSize
  return Math.min(20, Math.max(12, Math.round(n)))
}

function normalizeFontFamily(value) {
  return FONT_MAP[value] ? value : defaults.fontFamily
}

function loadSettings() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return { ...defaults }
    const parsed = { ...defaults, ...JSON.parse(raw) }
    parsed.fontFamily = normalizeFontFamily(parsed.fontFamily)
    parsed.fontSize = clampFontSize(parsed.fontSize)
    parsed.uiScale = clampScale(parsed.uiScale)
    return {
      menuSpread: !!parsed.menuSpread,
      navbarFixed: !!parsed.navbarFixed,
      sidebarPushContent: !!parsed.sidebarPushContent,
      fontFamily: parsed.fontFamily,
      fontSize: parsed.fontSize,
      uiScale: parsed.uiScale,
    }
  } catch {
    return { ...defaults }
  }
}

function applyAppearance(fontFamily, fontSize, uiScale) {
  const root = document.documentElement
  const base = clampFontSize(fontSize)

  // Cỡ chữ gốc trên <html> — mọi rem / var(--el-font-size-*) theo rem sẽ scale theo
  root.style.setProperty('--admin-font', FONT_MAP[fontFamily] || FONT_MAP[defaults.fontFamily])
  root.style.setProperty('--admin-ui-scale', String(clampScale(uiScale) / 100))
  root.style.setProperty('--admin-font-size', `${base}px`)
  root.style.setProperty('--el-font-size-extra-small', `${FONT_SIZE_SCALE.extraSmall}rem`)
  root.style.setProperty('--el-font-size-small', `${FONT_SIZE_SCALE.small}rem`)
  root.style.setProperty('--el-font-size-base', '1rem')
  root.style.setProperty('--el-font-size-medium', '1rem')
  root.style.setProperty('--el-font-size-large', `${FONT_SIZE_SCALE.large}rem`)
  root.style.setProperty('--el-font-size-extra-large', `${FONT_SIZE_SCALE.extraLarge}rem`)
  root.style.setProperty('--el-font-line-height-primary', '1.5')
}

export const useLayoutStore = defineStore('layout', () => {
  const saved = loadSettings()

  const menuSpread = ref(saved.menuSpread)
  const navbarFixed = ref(saved.navbarFixed)
  const sidebarPushContent = ref(saved.sidebarPushContent)
  const fontFamily = ref(saved.fontFamily)
  const fontSize = ref(saved.fontSize)
  const uiScale = ref(saved.uiScale)

  function persist() {
    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify({
        menuSpread: menuSpread.value,
        navbarFixed: navbarFixed.value,
        sidebarPushContent: sidebarPushContent.value,
        fontFamily: fontFamily.value,
        fontSize: fontSize.value,
        uiScale: uiScale.value,
      }),
    )
  }

  function syncAppearance() {
    applyAppearance(fontFamily.value, fontSize.value, uiScale.value)
  }

  watch(
    [
      menuSpread,
      navbarFixed,
      sidebarPushContent,
      fontFamily,
      fontSize,
      uiScale,
    ],
    persist,
  )

  watch([fontFamily, fontSize, uiScale], syncAppearance, { immediate: true })

  function reset() {
    menuSpread.value = defaults.menuSpread
    navbarFixed.value = defaults.navbarFixed
    sidebarPushContent.value = defaults.sidebarPushContent
    fontFamily.value = defaults.fontFamily
    fontSize.value = defaults.fontSize
    uiScale.value = defaults.uiScale
  }

  return {
    menuSpread,
    navbarFixed,
    sidebarPushContent,
    fontFamily,
    fontSize,
    uiScale,
    reset,
    syncAppearance,
  }
})
