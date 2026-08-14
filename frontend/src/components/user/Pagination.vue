<template>
  <nav class="user-pagination" aria-label="Phân trang">
    <p class="user-pagination__total">
      {{ formatNumber(total) }} {{ totalLabel }}
    </p>

    <div class="user-pagination__controls">
      <label class="user-pagination__size">
        <span>Hiển thị</span>
        <select
          :value="limit"
          :disabled="disabled"
          @change="onLimitChange"
        >
          <option v-for="size in pageSizes" :key="size" :value="size">
            {{ size }}
          </option>
        </select>
      </label>

      <div class="user-pagination__pages">
        <button
          type="button"
          class="user-pagination__btn"
          :disabled="disabled || currentPage <= 1"
          aria-label="Trang trước"
          @click="goTo(currentPage - 1)"
        >
          Trước
        </button>

        <button
          v-for="(item, index) in visiblePages"
          :key="`${item}-${index}`"
          type="button"
          class="user-pagination__btn"
          :class="{
            'is-active': item === currentPage,
            'is-ellipsis': item === ELLIPSIS,
          }"
          :disabled="disabled || item === ELLIPSIS"
          :aria-current="item === currentPage ? 'page' : undefined"
          :aria-label="item === ELLIPSIS ? undefined : `Trang ${item}`"
          @click="item !== ELLIPSIS && goTo(item)"
        >
          {{ item === ELLIPSIS ? '…' : item }}
        </button>

        <button
          type="button"
          class="user-pagination__btn"
          :disabled="disabled || currentPage >= pageCount"
          aria-label="Trang sau"
          @click="goTo(currentPage + 1)"
        >
          Sau
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
/**
 * Phân trang offset-based cho trang người dùng.
 * Đồng bộ API: ?start=0&limit=10 → response { data, total, start, limit }.
 *
 * @example
 * <Pagination
 *   v-model:start="start"
 *   v-model:limit="limit"
 *   :total="total"
 *   @change="fetchList"
 * />
 */
import { computed } from 'vue'

defineOptions({ name: 'Pagination' })

const ELLIPSIS = '...'

const start = defineModel('start', { type: Number, default: 0 })
const limit = defineModel('limit', { type: Number, default: 10 })

const props = defineProps({
  total: { type: Number, default: 0 },
  pageSizes: {
    type: Array,
    default: () => [10, 20, 50],
  },
  disabled: { type: Boolean, default: false },
  totalLabel: { type: String, default: 'giao dịch' },
})

const emit = defineEmits(['change'])

const currentPage = computed(() => {
  if (limit.value <= 0) return 1
  return Math.floor(start.value / limit.value) + 1
})

const pageCount = computed(() =>
  Math.max(1, Math.ceil(Math.max(0, props.total) / Math.max(1, limit.value))),
)

const visiblePages = computed(() => {
  const last = pageCount.value
  const current = currentPage.value
  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }

  const pages = new Set([1, last, current, current - 1, current + 1])
  if (current <= 3) {
    pages.add(2)
    pages.add(3)
    pages.add(4)
  }
  if (current >= last - 2) {
    pages.add(last - 1)
    pages.add(last - 2)
    pages.add(last - 3)
  }

  const sorted = [...pages].filter((n) => n >= 1 && n <= last).sort((a, b) => a - b)
  const result = []
  sorted.forEach((page, index) => {
    if (index > 0 && page - sorted[index - 1] > 1) {
      result.push(ELLIPSIS)
    }
    result.push(page)
  })
  return result
})

function emitChange() {
  emit('change', { start: start.value, limit: limit.value })
}

function goTo(page) {
  const next = Math.min(pageCount.value, Math.max(1, Number(page) || 1))
  const nextStart = (next - 1) * limit.value
  if (nextStart === start.value) return
  start.value = nextStart
  emitChange()
}

function onLimitChange(event) {
  const nextLimit = Number(event.target.value)
  if (!Number.isFinite(nextLimit) || nextLimit < 1) return
  limit.value = nextLimit
  start.value = 0
  emitChange()
}

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}
</script>

<style scoped>
.user-pagination {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.user-pagination__total {
  margin: 0;
  color: var(--muted);
}

.user-pagination__controls {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.user-pagination__size {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: var(--muted);
}

.user-pagination__size select {
  padding: 0.4rem 0.65rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: #fff;
  color: var(--text);
  font: inherit;
  cursor: pointer;
}

.user-pagination__size select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.user-pagination__pages {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.35rem;
}

.user-pagination__btn {
  min-width: 2.25rem;
  height: 2.25rem;
  padding: 0 0.7rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: #fff;
  color: var(--text);
  font: inherit;
  font-size: 16px;
  font-weight: 300;
  line-height: 1;
  cursor: pointer;
}

.user-pagination__btn:hover:not(:disabled):not(.is-active) {
  border-color: var(--accent);
  color: var(--accent);
}

.user-pagination__btn.is-active {
  background: var(--accent);
  border-color: var(--accent);
  color: #fff;
}

.user-pagination__btn.is-active:hover:not(:disabled) {
  background: var(--accent);
  border-color: var(--accent);
  color: #fff;
}

.user-pagination__btn.is-ellipsis {
  border-color: transparent;
  background: transparent;
  cursor: default;
  min-width: 1.5rem;
  padding: 0;
}

.user-pagination__btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

@media (max-width: 720px) {
  .user-pagination {
    justify-content: center;
  }

  .user-pagination__controls {
    justify-content: center;
    width: 100%;
  }
}
</style>
