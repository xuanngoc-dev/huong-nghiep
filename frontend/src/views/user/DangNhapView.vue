<template>
  <section class="auth-page">
    <form class="card form" @submit.prevent="onSubmit">
      <h1>Đăng nhập</h1>
      <p class="muted">Sử dụng tài khoản đã đăng ký để truy cập API bảo vệ.</p>

      <label>
        Email
        <input v-model="form.email" type="email" required autocomplete="email" />
      </label>

      <label>
        Mật khẩu
        <input v-model="form.password" type="password" required autocomplete="current-password" />
      </label>

      <p v-if="auth.error" class="error-text">{{ auth.error }}</p>

      <button class="btn" type="submit" :disabled="auth.loading">
        {{ auth.loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
      </button>

      <p class="muted">
        Chưa có tài khoản?
        <RouterLink to="/register">Đăng ký</RouterLink>
      </p>
    </form>
  </section>
</template>

<script setup>
import { reactive } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({
  email: 'admin@huongnghiep.local',
  password: 'password',
})

async function onSubmit() {
  try {
    await auth.login(form)

    if (route.query.redirect) {
      router.push(String(route.query.redirect))
      return
    }

    router.push(auth.isAdmin ? { name: 'admin-dashboard' } : { name: 'home' })
  } catch {
    // error đã lưu trong store
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 2rem 1rem;
}

form {
  width: min(420px, 100%);
}

h1 {
  margin: 0;
}
</style>
