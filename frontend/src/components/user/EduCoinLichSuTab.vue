<template>
  <CustomEmpty description="Lịch sử biến động">
    <template #image>
      <CustomIcon :size="64" color="var(--el-color-info)">
        <Tickets />
      </CustomIcon>
    </template>
    <p class="edu-history__hint">Chức năng đang phát triển</p>
  </CustomEmpty>
</template>

<script setup>
import { Tickets } from '@element-plus/icons-vue'
import { CustomEmpty, CustomIcon } from '@/components/element'
</script>

<style scoped>
.edu-history__hint {
  margin: 8px 0 0;
  color: var(--el-text-color-secondary);
  font-size: 16px;
}
</style>
