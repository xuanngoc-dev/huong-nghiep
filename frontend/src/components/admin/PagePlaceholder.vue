<template>
  <div class="page-placeholder">
    <CustomEmpty :description="description">
      <template #image>
        <CustomIcon :size="64" color="var(--el-color-info)">
          <component :is="iconComponent" />
        </CustomIcon>
      </template>
      <p class="hint">Trang đang chờ triển khai giao diện.</p>
    </CustomEmpty>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import * as Icons from '@element-plus/icons-vue'
import { Document } from '@element-plus/icons-vue'
import { CustomEmpty, CustomIcon } from '@/components/element'

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    default: '',
  },
})

const route = useRoute()

const description = computed(
  () => props.title || route.meta.title || 'Đang phát triển',
)

const iconComponent = computed(() => {
  const name = props.icon || route.meta.icon || 'Document'
  return Icons[name] || Document
})
</script>

<style scoped lang="scss">
.page-placeholder {
  min-height: 360px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hint {
  margin: 8px 0 0;
  color: var(--el-text-color-secondary);
  font-size: 13px;
}
</style>
