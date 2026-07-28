<template>
  <section>
    <h1>Tổng quan CMS</h1>
    <p class="muted">Khu vực quản trị — chỉ tài khoản role <strong>admin</strong> truy cập được.</p>

    <p v-if="loading" class="muted">Đang tải...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else class="grid cols-3">
      <div class="card stat">
        <p class="label">Người dùng</p>
        <p class="value">{{ stats.users_count }}</p>
      </div>
      <div class="card stat">
        <p class="label">Admin</p>
        <p class="value">{{ stats.admins_count }}</p>
      </div>
      <div class="card stat">
        <p class="label">Nghề nghiệp</p>
        <p class="value">{{ stats.careers_count }}</p>
      </div>
      <div class="card stat">
        <p class="label">Bài viết</p>
        <p class="value">{{ stats.articles_count }}</p>
      </div>
      <div class="card stat">
        <p class="label">Trắc nghiệm</p>
        <p class="value">{{ stats.assessments_count }}</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { adminApi } from '@/api'

const stats = ref({
  users_count: 0,
  admins_count: 0,
  careers_count: 0,
  articles_count: 0,
  assessments_count: 0,
})
const loading = ref(true)
const error = ref(null)

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
.stat .label {
  margin: 0;
  color: var(--muted);
  font-size: 0.9rem;
}

.stat .value {
  margin: 0.35rem 0 0;
  font-size: 1.8rem;
  font-weight: 700;
}
</style>
