<template>
  <section class="password-page">
    <header class="password-page__head">
      <h1>Đổi mật khẩu</h1>
      <p class="muted">Dùng mật khẩu mạnh để bảo vệ tài khoản của bạn.</p>
    </header>

    <form class="card form" @submit.prevent="onSubmit">
      <label>
        Mật khẩu hiện tại
        <div class="password-input">
          <input
            v-model="form.current_password"
            :type="visible.current ? 'text' : 'password'"
            required
            autocomplete="current-password"
            placeholder="Nhập mật khẩu hiện tại"
          />
          <button
            type="button"
            class="password-toggle"
            :aria-label="visible.current ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
            :aria-pressed="visible.current"
            @click.prevent="visible.current = !visible.current"
          >
            <el-icon :size="18">
              <Hide v-if="visible.current" />
              <View v-else />
            </el-icon>
          </button>
        </div>
      </label>

      <div class="field-stack">
        <label>
          <span class="label-row">
            Mật khẩu mới
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
              :type="visible.next ? 'text' : 'password'"
              required
              minlength="8"
              autocomplete="new-password"
              placeholder="Nhập mật khẩu mới"
              :aria-invalid="form.password.length > 0 && !passwordValid"
            />
            <button
              type="button"
              class="password-toggle"
              :aria-label="visible.next ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
              :aria-pressed="visible.next"
              @click.prevent="visible.next = !visible.next"
            >
              <el-icon :size="18">
                <Hide v-if="visible.next" />
                <View v-else />
              </el-icon>
            </button>
          </div>
        </label>

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

      <label>
        Xác nhận mật khẩu mới
        <div class="password-input">
          <input
            v-model="form.password_confirmation"
            :type="visible.confirm ? 'text' : 'password'"
            required
            minlength="8"
            autocomplete="new-password"
            placeholder="Nhập lại mật khẩu mới"
          />
          <button
            type="button"
            class="password-toggle"
            :aria-label="visible.confirm ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
            :aria-pressed="visible.confirm"
            @click.prevent="visible.confirm = !visible.confirm"
          >
            <el-icon :size="18">
              <Hide v-if="visible.confirm" />
              <View v-else />
            </el-icon>
          </button>
        </div>
      </label>

      <p v-if="localError" class="error-text">{{ localError }}</p>

      <button class="btn" type="submit" :disabled="saving">
        {{ saving ? 'Đang lưu...' : 'Cập nhật mật khẩu' }}
      </button>
    </form>
  </section>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Hide, InfoFilled, View } from '@element-plus/icons-vue'
import { request } from '@/api'
import { API_AUTH } from '@/constants/constant_api'

const saving = ref(false)
const localError = ref('')
const visible = reactive({
  current: false,
  next: false,
  confirm: false,
})

const form = reactive({
  current_password: '',
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
  if (!form.current_password) return 'Vui lòng nhập mật khẩu hiện tại.'
  if (!passwordValid.value) {
    return 'Mật khẩu cần ít nhất 8 ký tự, gồm chữ thường, chữ hoa, số và ký tự đặc biệt.'
  }
  if (form.password === form.current_password) {
    return 'Mật khẩu mới phải khác mật khẩu hiện tại.'
  }
  if (form.password !== form.password_confirmation) {
    return 'Mật khẩu xác nhận không khớp.'
  }
  return ''
}

function resetForm() {
  form.current_password = ''
  form.password = ''
  form.password_confirmation = ''
}

async function onSubmit() {
  localError.value = ''
  const message = validate()
  if (message) {
    localError.value = message
    return
  }

  saving.value = true
  try {
    const res = await request({
      url: API_AUTH.CHANGE_PASSWORD,
      body: {
        current_password: form.current_password,
        password: form.password,
        password_confirmation: form.password_confirmation,
      },
      successFallback: 'Đổi mật khẩu thành công.',
      errorFallback: 'Không đổi được mật khẩu.',
    })

    if (!res.ok) {
      localError.value = res.message || 'Không đổi được mật khẩu.'
      return
    }

    resetForm()
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.password-page {
  display: grid;
  gap: 1rem;
}

.password-page__head h1 {
  margin: 0 0 0.35rem;
}

.password-page__head p {
  margin: 0;
}

form {
  width: min(32rem, 100%);
}

.field-stack {
  display: grid;
  gap: 0.45rem;
  align-content: start;
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
  height: 0.4rem;
  border-radius: 999px;
  background: var(--border);
  overflow: hidden;
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
