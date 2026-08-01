<template>
  <section class="auth-page">
    <form class="card form" @submit.prevent="onSubmit">
      <h1>Đăng ký</h1>
      <p class="muted">Tạo tài khoản mới để sử dụng hệ thống hướng nghiệp.</p>

      <label>
        Họ tên
        <input v-model="form.name" type="text" required autocomplete="name" />
      </label>

      <label>
        Email
        <input v-model="form.email" type="email" required autocomplete="email" />
      </label>

      <label>
        Mật khẩu
        <input v-model="form.password" type="password" required minlength="8" autocomplete="new-password" />
      </label>

      <label>
        Xác nhận mật khẩu
        <input
          v-model="form.password_confirmation"
          type="password"
          required
          minlength="8"
          autocomplete="new-password"
        />
      </label>

      <p v-if="auth.error" class="error-text">{{ auth.error }}</p>

      <button class="btn" type="submit" :disabled="auth.loading">
        {{ auth.loading ? 'Đang đăng ký...' : 'Đăng ký' }}
      </button>

      <p class="muted">
        Đã có tài khoản?
        <RouterLink to="/login">Đăng nhập</RouterLink>
      </p>
    </form>
  </section>
</template>

<script setup>
import { reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

async function onSubmit() {
  try {
    await auth.register(form)
    router.push({ name: 'home' })
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
