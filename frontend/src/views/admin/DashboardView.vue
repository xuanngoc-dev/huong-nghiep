<template>
  <div v-loading="loading">
    <div class="page-title">Tổng quan CMS</div>

    <CustomAlert
      class="mt"
      title="Khu vực quản trị dành cho tài khoản role admin."
      type="success"
      show-icon
      :closable="false"
    />

    <CustomRow v-if="!error" :gutter="16" class="mt">
      <CustomCol v-for="item in cards" :key="item.label" :xs="24" :sm="12" :md="8" :lg="8">
        <CustomCard shadow="hover" class="stat-card">
          <div class="stat">
            <div>
              <div class="label">{{ item.label }}</div>
              <div class="value">{{ item.value }}</div>
            </div>
            <CustomIcon :size="28" :color="item.color">
              <component :is="item.icon" />
            </CustomIcon>
          </div>
        </CustomCard>
      </CustomCol>
    </CustomRow>

    <CustomAlert v-else class="mt" :title="error" type="error" show-icon />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Briefcase, Document, EditPen, User, UserFilled } from '@element-plus/icons-vue'
import { adminApi } from '@/api'
import {
  CustomAlert,
  CustomCard,
  CustomCol,
  CustomIcon,
  CustomRow,
} from '@/components/element'

const stats = ref({
  users_count: 0,
  admins_count: 0,
  careers_count: 0,
  articles_count: 0,
  assessments_count: 0,
})
const loading = ref(true)
const error = ref(null)

const cards = computed(() => [
  { label: 'Người dùng', value: stats.value.users_count, icon: User, color: '#409eff' },
  { label: 'Admin', value: stats.value.admins_count, icon: UserFilled, color: '#67c23a' },
  { label: 'Nghề nghiệp', value: stats.value.careers_count, icon: Briefcase, color: '#e6a23c' },
  { label: 'Bài viết', value: stats.value.articles_count, icon: Document, color: '#909399' },
  { label: 'Trắc nghiệm', value: stats.value.assessments_count, icon: EditPen, color: '#f56c6c' },
])

onMounted(async () => {
  try {
    const { data } = await adminApi.dashboard()
    stats.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Không tải được dashboard.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page-title {
  font-size: 1.15rem;
  font-weight: 700;
}

.mt {
  margin-top: 1rem;
}

.stat-card {
  margin-bottom: 1rem;
}

.stat {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.label {
  color: var(--el-text-color-secondary);
  font-size: 0.9rem;
}

.value {
  margin-top: 0.35rem;
  font-size: 1.75rem;
  font-weight: 700;
}
</style>
