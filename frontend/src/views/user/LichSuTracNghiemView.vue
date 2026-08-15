<template>
  <section class="quiz-history">
    <header class="quiz-history__head">
      <div>
        <h1>Lịch sử trắc nghiệm</h1>
        <p class="muted">Các phiên làm bài của bạn, từ mới nhất đến cũ nhất.</p>
      </div>
      <RouterLink class="btn" :to="{ name: 'quiz-start' }">Làm bài mới</RouterLink>
    </header>

    <p v-if="loading && !items.length" class="muted">Đang tải lịch sử trắc nghiệm...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>
    <CustomEmpty v-else-if="!items.length" description="Chưa có phiên trắc nghiệm">
      <template #image>
        <CustomIcon :size="64" color="var(--el-color-info)">
          <Document />
        </CustomIcon>
      </template>
      <p class="quiz-history__hint">Hãy làm bài để hệ thống lưu kết quả tại đây.</p>
      <RouterLink class="btn" :to="{ name: 'quiz-start' }">Bắt đầu làm bài</RouterLink>
    </CustomEmpty>

    <template v-else>
      <div class="quiz-history__table-wrap">
        <table class="quiz-history__table">
          <thead>
            <tr>
              <th scope="col">Thời gian</th>
              <th scope="col">Nhóm ngành phù hợp</th>
              <th class="is-num" scope="col">Điểm</th>
              <th scope="col">Thanh toán</th>
              <th scope="col">Trạng thái</th>
              <th scope="col">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in items" :key="row.id">
              <td>{{ formatDateTime(row.created_at) }}</td>
              <td>{{ row.top_nhom_nganh?.ten_nhom_nganh || '—' }}</td>
              <td class="is-num">{{ formatScore(row) }}</td>
              <td>
                <span
                  class="quiz-history__status"
                  :class="paymentStatusClass(row)"
                >
                  {{ paymentStatusLabel(row) }}
                </span>
              </td>
              <td>
                <span
                  class="quiz-history__status"
                  :class="row.trang_thai === 'hoan_thanh' ? 'is-approved' : 'is-pending'"
                >
                  {{ row.trang_thai_label || '—' }}
                </span>
              </td>
              <td>
                <div class="quiz-history__actions">
                  <CustomTooltip
                    :content="row.trang_thai === 'hoan_thanh' ? 'Xem kết quả' : 'Tiếp tục'"
                    placement="top"
                  >
                    <RouterLink
                      class="quiz-history__icon-btn"
                      :to="row.trang_thai === 'hoan_thanh'
                        ? { name: 'quiz-result', params: { ssid: row.ssid } }
                        : { name: 'quiz-fields', params: { ssid: row.ssid } }"
                    >
                      <CustomIcon :size="18">
                        <View v-if="row.trang_thai === 'hoan_thanh'" />
                        <Right v-else />
                      </CustomIcon>
                    </RouterLink>
                  </CustomTooltip>
                  <CustomTooltip
                    :content="row.da_thanh_toan ? 'Đã thanh toán' : 'Thanh toán'"
                    placement="top"
                  >
                    <span>
                      <button
                        type="button"
                        class="quiz-history__icon-btn"
                        :disabled="row.da_thanh_toan"
                        @click="openThanhToan(row)"
                      >
                        <CustomIcon :size="18"><Wallet /></CustomIcon>
                      </button>
                    </span>
                  </CustomTooltip>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        v-model:start="start"
        v-model:limit="limit"
        :total="total"
        :disabled="loading"
        total-label="phiên"
        @change="fetchList"
      />
    </template>

    <ThanhToanTracNghiemModal
      v-model="payVisible"
      :phien="payPhien"
    />
  </section>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { Document, Right, View, Wallet } from '@element-plus/icons-vue'
import { request } from '@/api'
import { CustomEmpty, CustomIcon, CustomTooltip } from '@/components/element'
import Pagination from '@/components/user/Pagination.vue'
import ThanhToanTracNghiemModal from '@/components/user/ThanhToanTracNghiemModal.vue'
import { API_PUBLIC } from '@/constants/constant_api'

const items = ref([])
const loading = ref(false)
const error = ref('')
const start = ref(0)
const limit = ref(10)
const total = ref(0)
const payVisible = ref(false)
const payPhien = ref(null)

function formatDateTime(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('vi-VN')
}

function formatScore(row) {
  const score = row.top_nhom_nganh?.tong_diem ?? row.tong_diem
  if (score === null || score === undefined || score === '') return '—'
  return score
}

function paymentStatusLabel(row) {
  if (row.da_thanh_toan) return 'Đã thanh toán'
  if (row.thanh_toan_dang_xu_ly) return 'Đang xử lý'
  return 'Chưa thanh toán'
}

function paymentStatusClass(row) {
  if (row.da_thanh_toan) return 'is-approved'
  return 'is-pending'
}

function openThanhToan(row) {
  if (row.da_thanh_toan) return
  payPhien.value = row
  payVisible.value = true
}

async function fetchList() {
  loading.value = true
  error.value = ''
  const res = await request({
    url: API_PUBLIC.LICH_SU_TRAC_NGHIEM.LIST,
    params: { start: start.value, limit: limit.value },
    loading: false,
    silentSuccess: true,
    errorFallback: 'Không tải được lịch sử trắc nghiệm.',
  })
  loading.value = false

  if (!res.ok) {
    items.value = []
    total.value = 0
    error.value = res.message || 'Không tải được lịch sử trắc nghiệm.'
    return
  }

  items.value = Array.isArray(res.data) ? res.data : []
  total.value = Number(res.total) || 0
}

watch(payVisible, (open) => {
  if (!open) fetchList()
})

onMounted(fetchList)
</script>

<style scoped>
.quiz-history {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  display: grid;
  gap: 1rem;
  width: 100%;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.quiz-history__head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
}

.quiz-history__head h1 {
  margin: 0 0 0.35rem;
  font-size: 16px;
  font-weight: 400;
}

.quiz-history__head p {
  margin: 0;
}

.quiz-history__hint {
  margin: 8px 0 12px;
  color: var(--muted);
  font-size: 16px;
}

.quiz-history__table-wrap {
  overflow-x: auto;
  border: 1px solid var(--border);
  border-radius: var(--radius);
}

.quiz-history__table {
  width: 100%;
  min-width: 760px;
  border-collapse: collapse;
  font: inherit;
  font-size: 16px;
  font-weight: 300;
}

.quiz-history__table th,
.quiz-history__table td {
  padding: 0.75rem 0.9rem;
  border-bottom: 1px solid var(--border);
  border-right: 1px solid #e8eee9;
  text-align: left;
  vertical-align: top;
  line-height: 1.45;
}

.quiz-history__table th:last-child,
.quiz-history__table td:last-child {
  border-right: 0;
}

.quiz-history__table thead th {
  background: #f4f7f5;
  color: var(--text);
  font-weight: 400;
  white-space: nowrap;
}

.quiz-history__table tbody tr:last-child td {
  border-bottom: 0;
}

.quiz-history__table .is-num {
  text-align: right;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}

.quiz-history__status {
  display: inline-flex;
  align-items: center;
  padding: 0.15rem 0.55rem;
  border-radius: 999px;
  font-size: 0.9em;
  white-space: nowrap;
}

.quiz-history__status.is-approved {
  background: var(--accent-soft);
  color: var(--accent);
}

.quiz-history__status.is-pending {
  background: #fff6e5;
  color: #b26a00;
}

.quiz-history__actions {
  display: inline-flex;
  align-items: center;
  gap: 0.15rem;
}

.quiz-history__icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  padding: 0;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: var(--accent);
  cursor: pointer;
}

.quiz-history__icon-btn:hover {
  background: var(--accent-soft);
}

.quiz-history__icon-btn:disabled {
  opacity: 0.4;
  color: var(--muted);
  cursor: not-allowed;
}

.quiz-history__icon-btn:disabled:hover {
  background: transparent;
}
</style>
