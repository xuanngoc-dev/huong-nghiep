<template>
  <section class="card profile">
    <h1>Hồ sơ cá nhân</h1>
    <p v-if="loading" class="muted">Đang tải...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>
    <template v-else-if="auth.user">
      <p><strong>Họ tên:</strong> {{ auth.user.name }}</p>
      <p><strong>Email:</strong> {{ auth.user.email }}</p>
      <p><strong>Vai trò:</strong> {{ auth.role }}</p>
      <p v-if="auth.isAdmin">
        <RouterLink class="btn" to="/admin">Vào CMS</RouterLink>
      </p>
    </template>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    await auth.fetchMe()
  } catch {
    error.value = 'Không tải được thông tin người dùng.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.profile h1 {
  margin-top: 0;
}
</style>
