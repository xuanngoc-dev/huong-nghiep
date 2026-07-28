<template>
  <el-dialog
    v-model="visible"
    width="640px"
    :show-close="false"
    class="quick-search-dialog"
    append-to-body
    @opened="onOpened"
    @closed="resetState"
  >
    <div class="quick-search">
      <div class="quick-search__input-wrap">
        <el-icon class="quick-search__input-icon"><Search /></el-icon>
        <input
          ref="inputRef"
          v-model="keyword"
          type="text"
          class="quick-search__input"
          placeholder="Tìm chức năng CMS..."
          autocomplete="off"
          @keydown.esc="visible = false"
          @keydown.enter.prevent="goFirst"
        />
        <kbd class="quick-search__kbd">ESC</kbd>
      </div>

      <div class="quick-search__results">
        <button
          v-for="item in results"
          :key="item.path"
          type="button"
          class="quick-search__item"
          @click="goTo(item)"
        >
          <span class="quick-search__item-icon">
            <el-icon :size="18">
              <component :is="resolveIcon(item.icon)" />
            </el-icon>
          </span>
          <span class="quick-search__item-body">
            <span class="quick-search__item-title">{{ item.title }}</span>
            <span v-if="item.group" class="quick-search__item-meta">{{ item.group }}</span>
          </span>
          <el-icon class="quick-search__item-arrow"><ArrowRight /></el-icon>
        </button>

        <el-empty
          v-if="!results.length"
          :description="keyword.trim() ? 'Không tìm thấy chức năng' : 'Nhập từ khóa để tìm'"
          :image-size="72"
        />
      </div>
    </div>
  </el-dialog>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import { useRouter } from 'vue-router'
import * as Icons from '@element-plus/icons-vue'
import { ArrowRight, Search } from '@element-plus/icons-vue'
import menuGroups from '@/data/admin-menu.json'

const visible = defineModel({ type: Boolean, default: false })
const router = useRouter()
const keyword = ref('')
const inputRef = ref(null)

const allFunctions = menuGroups.flatMap((group) =>
  (group.items || []).map((item) => ({
    path: item.index,
    title: item.title,
    icon: item.icon,
    group: group.header || '',
  })),
)

const results = computed(() => {
  const q = keyword.value.trim().toLowerCase()
  if (!q) return allFunctions
  return allFunctions.filter(
    (item) =>
      item.title.toLowerCase().includes(q) ||
      item.group.toLowerCase().includes(q),
  )
})

function resolveIcon(name) {
  return Icons[name] || Icons.Menu
}

async function onOpened() {
  await nextTick()
  inputRef.value?.focus()
}

function resetState() {
  keyword.value = ''
}

function goTo(item) {
  visible.value = false
  router.push(item.path)
}

function goFirst() {
  if (results.value[0]) goTo(results.value[0])
}
</script>

<style scoped lang="scss">
.quick-search {
  margin: -8px -4px 0;
}

.quick-search__input-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: 1px solid var(--el-border-color);
  border-radius: 10px;
  background: var(--el-fill-color-light);
}

.quick-search__input-icon {
  color: var(--el-text-color-secondary);
}

.quick-search__input {
  flex: 1;
  min-width: 0;
  border: 0;
  outline: none;
  background: transparent;
  font: inherit;
  color: var(--el-text-color-primary);
}

.quick-search__kbd {
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid var(--el-border-color);
  background: var(--el-bg-color);
  font-size: 11px;
  color: var(--el-text-color-placeholder);
}

.quick-search__results {
  margin-top: 12px;
  max-height: 360px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.quick-search__item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 10px 12px;
  border: 1px solid transparent;
  border-radius: 10px;
  background: transparent;
  cursor: pointer;
  text-align: left;
  color: inherit;

  &:hover {
    background: var(--el-fill-color-light);
    border-color: var(--el-border-color-lighter);
  }
}

.quick-search__item-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  background: var(--el-color-primary-light-9);
  color: var(--el-color-primary);
}

.quick-search__item-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.quick-search__item-title {
  font-weight: 600;
}

.quick-search__item-meta {
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

.quick-search__item-arrow {
  color: var(--el-text-color-placeholder);
}
</style>

<style lang="scss">
.quick-search-dialog {
  .el-dialog__header {
    display: none;
  }

  .el-dialog__body {
    padding: 16px;
  }
}
</style>
