<template>
  <div class="xu-nhan">
    <article class="xu-nhan__balance">
      <div class="xu-nhan__balance-icon" aria-hidden="true">
        <el-icon :size="28"><Medal /></el-icon>
      </div>
      <div class="xu-nhan__balance-copy">
        <p class="xu-nhan__balance-label">Số dư hiện tại</p>
        <p class="xu-nhan__balance-value">{{ formatNumber(xuHeThong) }}</p>
        <p class="xu-nhan__balance-unit">Xu hệ thống</p>
      </div>
    </article>

    <article class="xu-nhan__claim">
      <h2>Điểm danh tuần này</h2>
      <p>
        Mỗi ngày điểm danh nhận {{ formatNumber(soXuMoiNgay) }} xu hệ thống.
        Chỉ điểm danh được ngày hôm nay, mỗi ngày một lần.
      </p>

      <p v-if="loading && !days.length" class="muted">Đang tải lịch điểm danh...</p>
      <p v-else-if="error" class="error-text">{{ error }}</p>

      <div v-else class="xu-nhan__week" role="list">
        <article
          v-for="day in days"
          :key="day.ngay"
          class="xu-day"
          :class="{
            'is-today': day.is_today,
            'is-claimed': day.da_diem_danh,
            'is-missed': day.is_past && !day.da_diem_danh,
            'is-future': day.is_future,
          }"
          role="listitem"
        >
          <p class="xu-day__thu">{{ day.thu_label }}</p>
          <p class="xu-day__date">{{ day.ngay_label }}</p>
          <p class="xu-day__reward">+{{ formatNumber(soXuMoiNgay) }} xu</p>
          <button
            type="button"
            class="btn xu-day__btn"
            :disabled="!day.co_the_diem_danh || claiming"
            @click="diemDanh(day)"
          >
            {{ buttonLabel(day) }}
          </button>
        </article>
      </div>
    </article>

    <article class="xu-nhan__about">
      <h2>Xu hệ thống là gì?</h2>
      <p>
        Xu hệ thống là điểm thưởng trên Hướng Nghiệp. Bạn nhận xu khi tham gia
        các hoạt động trên nền tảng, khác với Edu Coin dùng để thanh toán.
      </p>
      <ul>
        <li>Số dư xu được cập nhật ngay sau khi nhận thành công.</li>
        <li>Mọi lần cộng hoặc trừ xu đều được ghi lại ở tab Lịch sử biến động.</li>
        <li>Xu hệ thống không dùng để nạp tiền như Edu Coin.</li>
      </ul>
    </article>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Medal } from '@element-plus/icons-vue'
import { request } from '@/api'
import { API_PUBLIC } from '@/constants/constant_api'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const xuHeThong = computed(() => Number(auth.user?.xu_he_thong) || 0)

const days = ref([])
const soXuMoiNgay = ref(5000)
const loading = ref(false)
const claiming = ref(false)
const error = ref('')

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}

function buttonLabel(day) {
  if (claiming.value && day.co_the_diem_danh) return 'Đang nhận...'
  if (day.da_diem_danh) return 'Đã nhận'
  if (day.is_future) return 'Chưa nhận'
  if (day.is_past) return 'Đã bỏ lỡ'
  return 'Nhận'
}

function applyTuan(payload) {
  if (!payload || typeof payload !== 'object') return
  days.value = Array.isArray(payload.days) ? payload.days : []
  soXuMoiNgay.value = Number(payload.so_xu_moi_ngay) || 5000
  if (payload.xu_he_thong != null) {
    auth.updateUser({ xu_he_thong: Number(payload.xu_he_thong) || 0 })
  }
}

async function fetchTuan() {
  loading.value = true
  error.value = ''
  const res = await request({
    url: API_PUBLIC.XU_HE_THONG.TUAN_DIEM_DANH,
    loading: false,
    silent: true,
    errorFallback: 'Không tải được lịch điểm danh.',
  })
  loading.value = false

  if (!res.ok) {
    days.value = []
    error.value = res.message || 'Không tải được lịch điểm danh.'
    return
  }

  applyTuan(res.data)
}

async function diemDanh(day) {
  if (!day?.co_the_diem_danh || claiming.value) return

  claiming.value = true
  const res = await request({
    url: API_PUBLIC.XU_HE_THONG.DIEM_DANH,
    loading: false,
    errorFallback: 'Điểm danh không thành công.',
  })
  claiming.value = false

  if (!res.ok) {
    if (res.data?.tuan) applyTuan(res.data.tuan)
    else await fetchTuan()
    return
  }

  if (res.data?.tuan) {
    applyTuan(res.data.tuan)
  } else {
    if (res.data?.xu_he_thong != null) {
      auth.updateUser({ xu_he_thong: Number(res.data.xu_he_thong) || 0 })
    }
    days.value = days.value.map((item) =>
      item.ngay === day.ngay
        ? { ...item, da_diem_danh: true, co_the_diem_danh: false }
        : item,
    )
  }
}

onMounted(fetchTuan)
</script>

<style scoped>
.xu-nhan {
  display: grid;
  gap: 1rem;
}

.xu-nhan__balance {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.15rem 1.2rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: linear-gradient(180deg, rgba(231, 244, 236, 0.7) 0%, #fff 72%);
}

.xu-nhan__balance-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3.4rem;
  height: 3.4rem;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--accent-soft);
  color: var(--accent);
}

.xu-nhan__balance-label,
.xu-nhan__balance-unit {
  margin: 0;
  color: var(--muted);
  font-size: 16px;
}

.xu-nhan__balance-value {
  margin: 0.15rem 0 0.1rem;
  font-size: 1.85rem;
  font-weight: 400;
  letter-spacing: -0.04em;
  line-height: 1.15;
}

.xu-nhan__claim,
.xu-nhan__about {
  padding: 1.15rem 1.2rem;
  border: 1px solid var(--border);
  border-radius: 12px;
}

.xu-nhan h2 {
  margin: 0 0 0.55rem;
  font-size: 16px;
  font-weight: 400;
}

.xu-nhan p,
.xu-nhan li {
  margin: 0;
  color: var(--muted);
  line-height: 1.6;
}

.xu-nhan__about ul {
  margin: 0.75rem 0 0;
  padding-left: 1.15rem;
  display: grid;
  gap: 0.4rem;
}

.xu-nhan__week {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 0.65rem;
  margin-top: 1rem;
}

.xu-day {
  display: grid;
  justify-items: center;
  gap: 0.25rem;
  padding: 0.85rem 0.55rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: #fff;
  text-align: center;
}

.xu-day.is-today {
  border-color: var(--accent);
  background: linear-gradient(180deg, rgba(231, 244, 236, 0.85) 0%, #fff 70%);
}

.xu-day.is-claimed {
  border-color: var(--accent);
}

.xu-day.is-missed,
.xu-day.is-future {
  background: #f7faf8;
}

.xu-day__thu {
  margin: 0;
  color: var(--text);
  font-weight: 400;
}

.xu-day__date {
  margin: 0;
  font-size: 0.95rem;
}

.xu-day__reward {
  margin: 0.15rem 0 0.45rem;
  color: var(--accent);
  font-weight: 400;
  font-variant-numeric: tabular-nums;
}

.xu-day__btn {
  width: 100%;
  padding: 0.4rem 0.5rem;
  font-size: 0.9rem;
}

.xu-day.is-claimed .xu-day__btn {
  background: var(--accent-soft);
  color: var(--accent);
}

.xu-day.is-missed .xu-day__btn,
.xu-day.is-future .xu-day__btn {
  background: #e8eee9;
  color: var(--muted);
}

@media (max-width: 960px) {
  .xu-nhan__week {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 560px) {
  .xu-nhan__week {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
