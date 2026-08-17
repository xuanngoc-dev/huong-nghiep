<template>
  <section class="auth-page">
    <form class="card form" @submit.prevent="onSubmit">
      <h1>Đăng ký</h1>
      <p class="muted">Tạo tài khoản mới để sử dụng hệ thống hướng nghiệp.</p>

      <div class="form-fields">
        <label>
          Họ tên
          <input
            v-model="form.name"
            type="text"
            required
            autocomplete="name"
            placeholder="Nhập họ và tên"
          />
        </label>

        <label>
          Email
          <input
            v-model="form.email"
            type="email"
            required
            autocomplete="email"
            placeholder="vidu@email.com"
          />
        </label>

        <label>
          Số điện thoại
          <input
            v-model="form.so_dien_thoai"
            type="tel"
            required
            autocomplete="tel"
            maxlength="30"
            placeholder="09xxxxxxxx"
          />
        </label>

        <label>
          <span class="label-row">
            Mật khẩu
            <el-tooltip placement="top" :show-after="150" popper-class="password-tip-popper">
              <template #content>
                <div class="password-tip">
                  <p>Mật khẩu cần đáp ứng:</p>
                  <ul>
                    <li>Ít nhất 8 ký tự</li>
                    <li>Có chữ thường</li>
                    <li>Có chữ hoa</li>
                    <li>Có số</li>
                    <li>Có ký tự đặc biệt</li>
                  </ul>
                </div>
              </template>
              <button
                type="button"
                class="info-btn"
                aria-label="Yêu cầu bảo mật mật khẩu"
                @click.prevent
              >
                <el-icon :size="16"><InfoFilled /></el-icon>
              </button>
            </el-tooltip>
          </span>
          <div class="password-input">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              minlength="8"
              autocomplete="new-password"
              placeholder="Nhập mật khẩu"
              :aria-invalid="form.password.length > 0 && !passwordValid"
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
            <div
              v-if="form.password"
              class="password-strength"
              role="meter"
              :aria-label="`Độ mạnh mật khẩu: ${passwordStrength.label}`"
              :aria-valuenow="passwordScore"
              aria-valuemin="0"
              aria-valuemax="5"
            >
              <span
                class="password-strength__bar"
                :class="`is-${passwordStrength.level}`"
                :style="{ width: `${(passwordScore / 5) * 100}%` }"
              />
            </div>
          </div>
        </label>

        <label>
          Xác nhận mật khẩu
          <div class="password-input">
            <input
              v-model="form.password_confirmation"
              :type="showPasswordConfirmation ? 'text' : 'password'"
              required
              minlength="8"
              autocomplete="new-password"
              placeholder="Nhập lại mật khẩu"
            />
            <button
              type="button"
              class="password-toggle"
              :aria-label="showPasswordConfirmation ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
              :aria-pressed="showPasswordConfirmation"
              @click.prevent="showPasswordConfirmation = !showPasswordConfirmation"
            >
              <el-icon :size="18">
                <Hide v-if="showPasswordConfirmation" />
                <View v-else />
              </el-icon>
            </button>
          </div>
        </label>
      </div>

      <p v-if="localError || auth.error" class="error-text">{{ localError || auth.error }}</p>

      <button class="btn" type="submit" :disabled="auth.loading">
        {{ auth.loading ? 'Đang đăng ký...' : 'Đăng ký' }}
      </button>

      <p class="muted">
        Đã có tài khoản?
        <RouterLink to="/login">Đăng nhập</RouterLink>
      </p>
      <p class="auth-home-link">
        <RouterLink to="/">
          <el-icon :size="16" aria-hidden="true"><HomeFilled /></el-icon>
          Về trang chủ
        </RouterLink>
      </p>
    </form>
  </section>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { Hide, HomeFilled, InfoFilled, View } from '@element-plus/icons-vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const localError = ref('')
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const form = reactive({
  name: '',
  email: '',
  so_dien_thoai: '',
  password: '',
  password_confirmation: '',
})

const passwordRules = computed(() => {
  const value = form.password || ''
  return {
    minLength: value.length >= 8,
    lower: /[a-z]/.test(value),
    upper: /[A-Z]/.test(value),
    number: /\d/.test(value),
    special: /[^A-Za-z0-9]/.test(value),
  }
})

const passwordScore = computed(() => Object.values(passwordRules.value).filter(Boolean).length)

const passwordValid = computed(() => passwordScore.value === 5)

const passwordStrength = computed(() => {
  const score = passwordScore.value
  if (score <= 1) return { level: 'weak', label: 'Yếu' }
  if (score <= 3) return { level: 'fair', label: 'Trung bình' }
  if (score === 4) return { level: 'good', label: 'Khá' }
  return { level: 'strong', label: 'Mạnh' }
})

function validate() {
  const soDienThoai = String(form.so_dien_thoai || '').trim()

  if (!soDienThoai) return 'Vui lòng nhập số điện thoại.'
  if (!/^[0-9+\s()-]{8,30}$/.test(soDienThoai)) {
    return 'Số điện thoại không hợp lệ.'
  }
  if (!passwordValid.value) {
    return 'Mật khẩu cần ít nhất 8 ký tự, gồm chữ thường, chữ hoa, số và ký tự đặc biệt.'
  }
  if (form.password !== form.password_confirmation) {
    return 'Mật khẩu xác nhận không khớp.'
  }
  return ''
}

async function onSubmit() {
  localError.value = ''
  const message = validate()
  if (message) {
    localError.value = message
    return
  }

  try {
    await auth.register({
      ...form,
      so_dien_thoai: String(form.so_dien_thoai || '').trim(),
    })
    router.push({ name: 'home' })
  } catch {
    // error đã lưu trong store
  }
}
</script>

<style scoped>
.auth-page {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 2rem 1rem;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

form {
  width: min(680px, 100%);
}

h1 {
  margin: 0;
  font-family: var(--font);
  font-size: 1.75rem;
  font-weight: 400;
  color: var(--accent);
  letter-spacing: -0.03em;
}

.auth-page p,
.auth-page label,
.auth-page input,
.auth-page button,
.auth-page span {
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.muted {
  line-height: 1.5;
}

.auth-home-link {
  margin: 0;
  text-align: right;
}

.auth-home-link a {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.form-fields {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.9rem;
}

@media (min-width: 640px) {
  .form-fields {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.label-row {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.info-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--muted);
  cursor: help;
  line-height: 1;
}

.info-btn:hover,
.info-btn:focus-visible {
  color: var(--accent);
  outline: none;
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
  z-index: 1;
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

.password-strength {
  position: absolute;
  left: 0;
  right: 0;
  top: calc(100% + 3px);
  height: 5px;
  border-radius: 999px;
  background: var(--border);
  overflow: hidden;
  pointer-events: none;
}

.password-strength__bar {
  display: block;
  height: 100%;
  border-radius: inherit;
  transition: width 0.2s ease, background-color 0.2s ease;
}

.password-strength__bar.is-weak {
  background: var(--danger);
}

.password-strength__bar.is-fair {
  background: #d97706;
}

.password-strength__bar.is-good {
  background: #2563eb;
}

.password-strength__bar.is-strong {
  background: var(--accent);
}
</style>

<style>
.password-tip-popper {
  font-family: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.password-tip-popper .password-tip p {
  margin: 0 0 0.35rem;
}

.password-tip-popper .password-tip ul {
  margin: 0;
  padding-left: 1.1rem;
}

.password-tip-popper .password-tip li + li {
  margin-top: 0.15rem;
}
</style>
