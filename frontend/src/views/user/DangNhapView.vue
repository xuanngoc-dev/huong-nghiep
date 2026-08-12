<template>
  <section class="auth-page">
    <form class="card form" @submit.prevent="onSubmit">
      <h1>Đăng nhập</h1>
      <p class="muted">Sử dụng email hoặc số điện thoại đã đăng ký để đăng nhập.</p>

      <label>
        Email hoặc số điện thoại
        <input
          v-model="form.tai_khoan"
          type="text"
          required
          autocomplete="username"
          placeholder="Nhập email hoặc số điện thoại"
        />
      </label>

      <label>
        Mật khẩu
        <div class="password-input">
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            autocomplete="current-password"
            placeholder="Nhập mật khẩu"
          />
          <button
            type="button"
            class="password-toggle"
            :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
            :aria-pressed="showPassword"
            @click.prevent="showPassword = !showPassword"
          >
            <el-icon :size="18">
              <Hide v-if="showPassword" />
              <View v-else />
            </el-icon>
          </button>
        </div>
      </label>

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
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Hide, View } from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const showPassword = ref(false)

const form = reactive({
  tai_khoan: '',
  password: '',
})

async function onSubmit() {
  try {
    await auth.login({
      tai_khoan: form.tai_khoan.trim(),
      password: form.password,
    })

    if (route.query.redirect) {
      router.push(String(route.query.redirect))
      return
    }

    router.push(auth.isAdmin ? { name: 'admin-dashboard' } : { name: 'home' })
  } catch {
    ElMessage.error(auth.error || 'Đăng nhập thất bại.')
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
  width: min(520px, 100%);
}

h1 {
  margin: 0;
}

.password-input {
  position: relative;
}

.password-input input {
  width: 100%;
  padding-right: 2.75rem;
  box-sizing: border-box;
}

.password-toggle {
  position: absolute;
  top: 50%;
  right: 0.65rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--muted);
  cursor: pointer;
  transform: translateY(-50%);
  line-height: 1;
}

.password-toggle:hover,
.password-toggle:focus-visible {
  color: var(--accent);
  outline: none;
}
</style>
