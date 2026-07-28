<script setup>
/**
 * CustomPagination — phân trang offset-based (start / limit / total).
 * Đồng bộ với API: ?start=0&limit=10 → response { data, total }.
 *
 * @example
 * <CustomPagination
 *   v-model:start="pagination.start"
 *   v-model:limit="pagination.limit"
 *   :total="pagination.total"
 *   @change="fetchList"
 * />
 */
import { computed, nextTick, ref } from 'vue'

defineOptions({ name: 'CustomPagination', inheritAttrs: false })

const start = defineModel('start', { type: Number, default: 0 })
const limit = defineModel('limit', { type: Number, default: 10 })

const props = defineProps({
  total: { type: Number, default: 0 },
  pageSizes: {
    type: Array,
    default: () => [10, 20, 50, 100],
  },
  layout: {
    type: String,
    default: 'total, sizes, prev, pager, next, jumper',
  },
  background: { type: Boolean, default: true },
  disabled: { type: Boolean, default: false },
  hideOnSinglePage: { type: Boolean, default: false },
})

const emit = defineEmits(['change'])

const syncing = ref(false)

const currentPage = computed(() => {
  if (limit.value <= 0) return 1
  return Math.floor(start.value / limit.value) + 1
})

function emitChange() {
  emit('change', { start: start.value, limit: limit.value })
}

async function onSizeChange(size) {
  syncing.value = true
  limit.value = size
  start.value = 0
  emitChange()
  await nextTick()
  syncing.value = false
}

function onCurrentChange(page) {
  if (syncing.value) return
  start.value = (Math.max(1, page) - 1) * limit.value
  emitChange()
}
</script>

<template>
  <div class="custom-pagination" v-bind="$attrs">
    <el-pagination
      :current-page="currentPage"
      :page-size="limit"
      :total="props.total"
      :page-sizes="props.pageSizes"
      :layout="props.layout"
      :background="props.background"
      :disabled="props.disabled"
      :hide-on-single-page="props.hideOnSinglePage"
      @size-change="onSizeChange"
      @current-change="onCurrentChange"
    />
  </div>
</template>

<style scoped>
.custom-pagination {
  display: flex;
  justify-content: flex-end;
  flex-wrap: wrap;
  padding-top: 1rem;
}

@media (max-width: 767px) {
  .custom-pagination {
    justify-content: center;
  }

  .custom-pagination :deep(.el-pagination) {
    flex-wrap: wrap;
    justify-content: center;
    row-gap: 0.5rem;
  }
}
</style>
