<template>
  <div class="edu-overview">
    <article class="edu-overview__balance">
      <div class="edu-overview__balance-icon" aria-hidden="true">
        <el-icon :size="28"><Coin /></el-icon>
      </div>
      <div class="edu-overview__balance-copy">
        <p class="edu-overview__balance-label">Số dư hiện tại</p>
        <p class="edu-overview__balance-value">{{ formatNumber(eduCoin) }}</p>
        <p class="edu-overview__balance-unit">Edu Coin</p>
      </div>
    </article>

    <article class="edu-overview__about">
      <h2>Edu Coin là gì?</h2>
      <p>
        Edu Coin là đơn vị thanh toán trên hệ thống Hướng Nghiệp. Bạn dùng Edu Coin để
        thanh toán cho các lượt lấy kết quả trắc nghiệm.
      </p>
      <ul>
        <li>Mỗi lượt xem kết quả trắc nghiệm sẽ trừ một số Edu Coin tương ứng.</li>
        <li>Số dư được cập nhật ngay sau khi thanh toán thành công.</li>
        <li>Bạn có thể nạp thêm coin ở tab Nạp coin khi số dư không đủ.</li>
      </ul>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Coin } from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const eduCoin = computed(() => Number(auth.user?.edu_coin) || 0)

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}
</script>

<style scoped>
.edu-overview {
  display: grid;
  gap: 1rem;
}

.edu-overview__balance {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.15rem 1.2rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: linear-gradient(180deg, rgba(231, 244, 236, 0.7) 0%, #fff 72%);
}

.edu-overview__balance-icon {
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

.edu-overview__balance-label,
.edu-overview__balance-unit {
  margin: 0;
  color: var(--muted);
  font-size: 16px;
}

.edu-overview__balance-value {
  margin: 0.15rem 0 0.1rem;
  font-size: 1.85rem;
  font-weight: 400;
  letter-spacing: -0.04em;
  line-height: 1.15;
}

.edu-overview__about h2 {
  margin: 0 0 0.55rem;
  font-size: 16px;
  font-weight: 400;
}

.edu-overview__about p,
.edu-overview__about li {
  margin: 0;
  color: var(--muted);
  line-height: 1.6;
}

.edu-overview__about ul {
  margin: 0.75rem 0 0;
  padding-left: 1.15rem;
  display: grid;
  gap: 0.4rem;
}
</style>
