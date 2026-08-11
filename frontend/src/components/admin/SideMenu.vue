<template>
  <el-menu
    :default-active="activeMenu"
    :collapse="collapsed"
    router
    class="side-menu"
    :class="{ 'is-spread': menuSpread && !collapsed }"
  >
    <div
      v-for="(group, groupIndex) in menuGroups"
      :key="group.header || `group-${groupIndex}`"
      class="menu-group"
      :class="{
        'is-open': isGroupOpen(groupIndex),
        'is-active': isGroupActive(groupIndex),
      }"
    >
      <button
        v-if="group.header"
        type="button"
        class="menu-group__toggle"
        :class="{
          'is-collapsed': collapsed,
          'is-active': isGroupActive(groupIndex),
          'is-static': menuSpread,
        }"
        :aria-expanded="isGroupOpen(groupIndex)"
        :aria-current="isGroupActive(groupIndex) ? 'true' : undefined"
        :tabindex="collapsed || menuSpread ? -1 : undefined"
        @click="!collapsed && !menuSpread && toggleGroup(groupIndex)"
      >
        <el-tooltip
          :content="group.header"
          placement="right"
          :disabled="!collapsed"
          :show-after="200"
        >
          <span class="menu-group__face">
            <span class="menu-group__abbr">{{ group.abbr || groupAbbr(group.header) }}</span>
            <span class="menu-group__header">{{ group.header }}</span>
          </span>
        </el-tooltip>
        <el-icon
          v-if="!menuSpread"
          class="menu-group__arrow"
          :class="{ 'is-open': isGroupOpen(groupIndex) }"
        >
          <ArrowDown />
        </el-icon>
      </button>

      <div
        class="menu-group__items"
        :class="{
          'is-expanded': !group.header || isGroupOpen(groupIndex) || collapsed,
        }"
      >
        <div class="menu-group__items-inner">
          <el-tooltip
            v-for="item in group.items"
            :key="item.index"
            :content="item.title"
            placement="right"
            :disabled="!collapsed"
            :show-after="280"
          >
            <el-menu-item :index="item.index">
              <el-icon>
                <component :is="resolveIcon(item.icon)" />
              </el-icon>
              <span class="menu-label" :class="{ 'is-hidden': collapsed }">{{ item.title }}</span>
            </el-menu-item>
          </el-tooltip>
        </div>
      </div>
    </div>
  </el-menu>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import * as Icons from '@element-plus/icons-vue'
import { ArrowDown } from '@element-plus/icons-vue'
import { storeToRefs } from 'pinia'
import menuGroups from '@/data/admin-menu.json'
import { useLayoutStore } from '@/stores/layout'

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
})

const route = useRoute()
const layoutStore = useLayoutStore()
const { menuSpread } = storeToRefs(layoutStore)
const activeMenu = computed(() => route.path)
const collapsed = computed(() => props.collapsed)
const openGroups = ref(new Set())

function findActiveGroupIndex() {
  return menuGroups.findIndex((group) =>
    group.items?.some((item) => item.index === route.path),
  )
}

function openAllGroups() {
  openGroups.value = new Set(
    menuGroups.map((_, index) => index).filter((index) => menuGroups[index].header),
  )
}

function initOpenGroups() {
  if (menuSpread.value) {
    openAllGroups()
    return
  }

  const next = new Set()
  const activeIndex = findActiveGroupIndex()
  if (activeIndex >= 0) {
    next.add(activeIndex)
  } else {
    const first = menuGroups.findIndex((g) => g.header)
    if (first >= 0) next.add(first)
  }
  openGroups.value = next
}

initOpenGroups()

watch(menuSpread, (enabled) => {
  if (enabled) {
    openAllGroups()
  } else {
    initOpenGroups()
  }
})

watch(
  () => route.path,
  () => {
    if (menuSpread.value) return
    const activeIndex = findActiveGroupIndex()
    if (activeIndex < 0) return
    openGroups.value = new Set([activeIndex])
  },
)

function groupAbbr(header) {
  const words = String(header || '').trim().split(/\s+/).filter(Boolean)
  if (!words.length) return ''
  if (words.length === 1) return words[0].slice(0, 3).toUpperCase()
  return words.map((w) => w.charAt(0)).join('').slice(0, 3).toUpperCase()
}

function isGroupOpen(groupIndex) {
  return menuSpread.value || openGroups.value.has(groupIndex)
}

function isGroupActive(groupIndex) {
  return findActiveGroupIndex() === groupIndex
}

function toggleGroup(groupIndex) {
  if (menuSpread.value) return
  if (openGroups.value.has(groupIndex)) {
    openGroups.value = new Set()
    return
  }
  openGroups.value = new Set([groupIndex])
}

function resolveIcon(name) {
  return Icons[name] || Icons.Menu
}
</script>

<style scoped lang="scss">
.side-menu {
  border-right: none;
  width: 100%;
  transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1);

  &.is-spread {
    min-height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding-bottom: 12px;
    box-sizing: border-box;

    .menu-group {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      justify-content: center;
      min-height: 0;

      & + & {
        margin-top: 0;
      }
    }

    .menu-group__toggle {
      cursor: default;
      padding-top: 10px;
      padding-bottom: 4px;
    }
  }

  :deep(.el-menu-item),
  :deep(.el-sub-menu__title) {
    height: 40px;
    line-height: 40px;
    transition: padding 0.28s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.15s ease, color 0.15s ease;
  }

  /* Light mode: active item — xanh primary + nền nhạt */
  :global(html:not(.dark)) & {
    :deep(.el-menu-item.is-active) {
      color: var(--el-color-primary);
      background-color: var(--el-color-primary-light-9);

      .el-icon {
        color: var(--el-color-primary);
      }
    }
  }

  .menu-label {
    display: inline-block;
    max-width: 160px;
    opacity: 1;
    white-space: nowrap;
    overflow: hidden;
    vertical-align: middle;
    transition:
      opacity 0.2s ease 0.05s,
      max-width 0.28s cubic-bezier(0.4, 0, 0.2, 1);

    &.is-hidden {
      max-width: 0;
      opacity: 0;
      margin: 0;
      transition:
        opacity 0.12s ease,
        max-width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    }
  }

  &.el-menu--collapse {
    width: 64px;

    :deep(.el-menu-item),
    :deep(.el-sub-menu__title) {
      padding: 0 !important;
      justify-content: center;
    }

    :deep(.el-menu-item .el-icon),
    :deep(.el-sub-menu__title .el-icon) {
      margin: 0;
      width: 24px;
      text-align: center;
    }

    :deep(.el-menu-item span),
    :deep(.el-sub-menu__title span),
    :deep(.el-sub-menu__icon-arrow) {
      display: none !important;
      width: 0;
      height: 0;
      overflow: hidden;
      visibility: hidden;
    }
  }
}

.menu-group {
  & + & {
    margin-top: 4px;
  }

  &__face {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
    max-width: 100%;
  }

  &__abbr {
    display: none;
    flex-shrink: 0;
    min-width: 28px;
    padding: 2px 0;
    font-size: 0.625rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    line-height: 1;
    text-align: center;
    color: var(--el-text-color-secondary);
    user-select: none;
  }

  &__toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    min-height: 35px;
    padding: 14px 16px 6px 20px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: inherit;
    text-align: left;
    box-sizing: border-box;

    &:hover:not(.is-collapsed):not(.is-static) .menu-group__header,
    &:hover:not(.is-collapsed):not(.is-static) .menu-group__arrow {
      color: var(--el-color-primary);
    }

    &.is-active:not(.is-collapsed) {
      .menu-group__header,
      .menu-group__arrow {
        color: var(--el-color-primary);
      }

      .menu-group__abbr {
        color: var(--el-color-primary);
      }
    }

    &.is-collapsed {
      justify-content: center;
      padding: 10px 0 6px;
      cursor: default;

      .menu-group__abbr {
        display: inline-block;
      }

      .menu-group__header,
      .menu-group__arrow {
        display: none;
      }

      &.is-active .menu-group__abbr {
        color: var(--el-color-primary);
      }
    }
  }

  &__header {
    font-size: var(--el-font-size-base);
    font-weight: 400;
    letter-spacing: normal;
    color: var(--el-text-color-regular);
    line-height: 1.2;
    user-select: none;
    transition: color 0.15s ease;
  }

  &__arrow {
    font-size: 0.75rem;
    color: var(--el-text-color-secondary);
    transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1), color 0.15s ease;
    flex-shrink: 0;

    &.is-open {
      transform: rotate(180deg);
    }
  }

  &__items {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.32s cubic-bezier(0.4, 0, 0.2, 1);

    &.is-expanded {
      grid-template-rows: 1fr;
    }
  }

  &__items-inner {
    overflow: hidden;
    min-height: 0;
    opacity: 0;
    transform: translateY(-4px);
    transition:
      opacity 0.22s ease,
      transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);

    .menu-group__items.is-expanded & {
      opacity: 1;
      transform: translateY(0);
      transition-delay: 0.04s;
    }
  }
}
</style>
