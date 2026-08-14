<template>
  <div class="xu-history">
    <p v-if="loading && !items.length" class="muted">Đang tải lịch sử biến động...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>
    <CustomEmpty v-else-if="!items.length" description="Chưa có biến động xu hệ thống">
      <template #image>
        <CustomIcon :size="64" color="var(--el-color-info)">
          <Tickets />
        </CustomIcon>
      </template>
      <p class="xu-history__hint">Các lần nhận hoặc trừ xu sẽ hiển thị tại đây.</p>
    </CustomEmpty>

    <template v-else>
      <div class="xu-history__table-wrap">
        <table class="xu-history__table">
          <thead>
            <tr>
              <th scope="col">Thời gian</th>
              <th class="is-num" scope="col">Biến động</th>
              <th class="is-num" scope="col">Số dư trước</th>
              <th class="is-num" scope="col">Số dư sau</th>
              <th scope="col">Ghi chú</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in items" :key="row.id">
              <td>{{ formatDateTime(row.created_at) }}</td>
              <td class="is-num" :class="deltaClass(row.so_luong)">
                {{ formatDelta(row.so_luong) }}
              </td>
              <td class="is-num">{{ formatNumber(row.so_du_truoc) }}</td>
              <td class="is-num">{{ formatNumber(row.so_du_sau) }}</td>
              <td>{{ row.ghi_chu || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        v-model:start="start"
        v-model:limit="limit"
        :total="total"
        :disabled="loading"
        @change="fetchList"
      />
    </template>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { Tickets } from '@element-plus/icons-vue'
import { CustomEmpty, CustomIcon } from '@/components/element'
import Pagination from '@/components/user/Pagination.vue'

const props = defineProps({
  active: { type: Boolean, default: true },
})

const items = ref([])
const loading = ref(false)
const error = ref('')
const start = ref(0)
const limit = ref(10)
const total = ref(0)

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}

function formatDelta(value) {
  const amount = Number(value) || 0
  const formatted = formatNumber(Math.abs(amount))
  if (amount > 0) return `+${formatted}`
  if (amount < 0) return `-${formatted}`
  return formatted
}

function deltaClass(value) {
  const amount = Number(value) || 0
  if (amount > 0) return 'is-plus'
  if (amount < 0) return 'is-minus'
  return ''
}

function formatDateTime(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('vi-VN')
}

async function fetchList() {
  loading.value = false
  error.value = ''
  items.value = []
  total.value = 0
}

onMounted(() => {
  if (props.active) fetchList()
})

watch(
  () => props.active,
  (isActive) => {
    if (!isActive) return
    start.value = 0
    fetchList()
  },
)
</script>

<style scoped>
.xu-history {
  display: grid;
  gap: 1rem;
}

.xu-history__hint {
  margin: 8px 0 0;
  color: var(--muted);
  font-size: 16px;
}

.xu-history__table-wrap {
  overflow-x: auto;
  border: 1px solid var(--border);
  border-radius: var(--radius);
}

.xu-history__table {
  width: 100%;
  min-width: 640px;
  border-collapse: collapse;
  font: inherit;
  font-size: 16px;
  font-weight: 300;
}

.xu-history__table th,
.xu-history__table td {
  padding: 0.75rem 0.9rem;
  border-bottom: 1px solid var(--border);
  border-right: 1px solid #e8eee9;
  text-align: left;
  vertical-align: top;
  line-height: 1.45;
}

.xu-history__table th:last-child,
.xu-history__table td:last-child {
  border-right: 0;
}

.xu-history__table thead th {
  background: #f4f7f5;
  color: var(--text);
  font-weight: 400;
  white-space: nowrap;
}

.xu-history__table tbody tr:last-child td {
  border-bottom: 0;
}

.xu-history__table .is-num {
  text-align: right;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}

.xu-history__table .is-plus {
  color: var(--accent);
  font-weight: 400;
}

.xu-history__table .is-minus {
  color: #c0392b;
  font-weight: 400;
}
</style>
