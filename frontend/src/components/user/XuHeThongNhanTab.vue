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
      <h2>Nhận xu</h2>
      <p>
        Xu hệ thống được cộng khi bạn hoàn thành các hoạt động trên nền tảng.
        Các lượt nhận xu khả dụng sẽ hiển thị tại đây.
      </p>

      <CustomEmpty description="Chưa có lượt nhận xu">
        <template #image>
          <CustomIcon :size="64" color="var(--el-color-info)">
            <Medal />
          </CustomIcon>
        </template>
        <p class="xu-nhan__hint">Hãy quay lại sau khi có nhiệm vụ hoặc phần thưởng mới.</p>
      </CustomEmpty>
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
import { computed } from 'vue'
import { Medal } from '@element-plus/icons-vue'
import { CustomEmpty, CustomIcon } from '@/components/element'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const xuHeThong = computed(() => Number(auth.user?.xu_he_thong) || 0)

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}
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

.xu-nhan__hint {
  margin: 8px 0 0;
  color: var(--muted);
  font-size: 16px;
}
</style>
