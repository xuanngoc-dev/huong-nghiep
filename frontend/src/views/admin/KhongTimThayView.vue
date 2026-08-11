<template>
  <div class="not-found">
    <CustomEmpty description="404 — Không tìm thấy trang">
      <template #image>
        <CustomIcon :size="72" color="var(--el-color-info)">
          <WarningFilled />
        </CustomIcon>
      </template>
      <p class="hint">Đường dẫn bạn truy cập không tồn tại hoặc đã bị di chuyển.</p>
      <el-button type="primary" @click="goHome">Về Dashboard</el-button>
    </CustomEmpty>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { WarningFilled } from '@element-plus/icons-vue'
import { CustomEmpty, CustomIcon } from '@/components/element'

const router = useRouter()

function goHome() {
  router.push({ name: 'admin-dashboard' })
}
</script>

<style scoped lang="scss">
.not-found {
  min-height: 360px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hint {
  margin: 8px 0 16px;
  color: var(--el-text-color-secondary);
  font-size: 0.875rem;
}
</style>
