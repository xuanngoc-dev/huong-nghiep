<template>
  <div class="bao-mat-login">
    <p class="bao-mat-login__lead muted">
      Đổi mật khẩu đăng nhập để bảo vệ tài khoản. Mật khẩu mới cần đủ mạnh và khác mật khẩu cũ.
    </p>

    <CustomForm
      ref="formRef"
      class="bao-mat-login__form"
      :model="form"
      :rules="rules"
      @submit.prevent="onSubmit"
    >
      <div class="bao-mat-login__fields">
        <CustomFormItem label="Mật khẩu cũ" prop="current_password">
          <CustomInput
            v-model="form.current_password"
            type="password"
            show-password
            maxlength="255"
            autocomplete="current-password"
            placeholder="Nhập mật khẩu cũ"
          />
        </CustomFormItem>

        <CustomFormItem prop="password">
          <template #label>
            <span class="bao-mat-login__label-row">
              Mật khẩu mới
              <CustomTooltip placement="top" :show-after="150">
                <template #content>
                  <div class="bao-mat-login__tip">
                    <p>Mật khẩu cần đáp ứng:</p>
                    <ul>
                      <li>Ít nhất 8 ký tự</li>
                      <li>Có chữ thường</li>
                      <li>Có chữ hoa</li>
                      <li>Có số</li>
                      <li>Có ký tự đặc biệt</li>
                      <li>Khác mật khẩu cũ</li>
                    </ul>
                  </div>
                </template>
                <button
                  type="button"
                  class="bao-mat-login__info"
                  aria-label="Yêu cầu bảo mật mật khẩu"
                  @click.prevent
                >
                  <el-icon :size="16"><InfoFilled /></el-icon>
                </button>
              </CustomTooltip>
            </span>
          </template>
          <CustomInput
            v-model="form.password"
            type="password"
            show-password
            maxlength="255"
            autocomplete="new-password"
            placeholder="Nhập mật khẩu mới"
          />
        </CustomFormItem>

        <CustomFormItem label="Nhập lại mật khẩu mới" prop="password_confirmation">
          <CustomInput
            v-model="form.password_confirmation"
            type="password"
            show-password
            maxlength="255"
            autocomplete="new-password"
            placeholder="Nhập lại mật khẩu mới"
          />
        </CustomFormItem>
      </div>

      <div class="bao-mat-login__actions">
        <button class="btn" type="submit" :disabled="saving">
          {{ saving ? 'Đang lưu...' : 'Cập nhật mật khẩu' }}
        </button>
      </div>
    </CustomForm>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { InfoFilled } from '@element-plus/icons-vue'
import { request } from '@/api'
import {
  CustomForm,
  CustomFormItem,
  CustomInput,
  CustomTooltip,
} from '@/components/element'
import { API_AUTH } from '@/constants/constant_api'

const formRef = ref(null)
const saving = ref(false)

const form = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

function passwordChecks(value) {
  const pwd = String(value || '')
  return {
    minLength: pwd.length >= 8,
    lower: /[a-z]/.test(pwd),
    upper: /[A-Z]/.test(pwd),
    number: /\d/.test(pwd),
    special: /[^A-Za-z0-9]/.test(pwd),
  }
}

function validateCurrentPassword(_rule, value, callback) {
  if (!String(value || '').trim()) {
    callback(new Error('Vui lòng nhập mật khẩu cũ.'))
    return
  }
  callback()
}

function validateNewPassword(_rule, value, callback) {
  const pwd = String(value || '')
  if (!pwd) {
    callback(new Error('Vui lòng nhập mật khẩu mới.'))
    return
  }

  const checks = passwordChecks(pwd)
  if (!checks.minLength) {
    callback(new Error('Mật khẩu phải có tối thiểu 8 ký tự.'))
    return
  }
  if (!checks.lower) {
    callback(new Error('Mật khẩu phải có ít nhất một chữ thường.'))
    return
  }
  if (!checks.upper) {
    callback(new Error('Mật khẩu phải có ít nhất một chữ hoa.'))
    return
  }
  if (!checks.number) {
    callback(new Error('Mật khẩu phải có ít nhất một chữ số.'))
    return
  }
  if (!checks.special) {
    callback(new Error('Mật khẩu phải có ít nhất một ký tự đặc biệt.'))
    return
  }
  if (pwd === form.current_password) {
    callback(new Error('Mật khẩu mới phải khác mật khẩu cũ.'))
    return
  }

  callback()
}

function validatePasswordConfirm(_rule, value, callback) {
  if (!String(value || '')) {
    callback(new Error('Vui lòng nhập lại mật khẩu mới.'))
    return
  }
  if (value !== form.password) {
    callback(new Error('Mật khẩu xác nhận không khớp.'))
    return
  }
  callback()
}

const rules = {
  current_password: [
    { required: true, validator: validateCurrentPassword, trigger: ['blur', 'change'] },
  ],
  password: [
    { required: true, validator: validateNewPassword, trigger: ['blur', 'change'] },
  ],
  password_confirmation: [
    { required: true, validator: validatePasswordConfirm, trigger: ['blur', 'change'] },
  ],
}

watch(
  () => form.password,
  () => {
    if (form.password_confirmation) {
      formRef.value?.validateField('password_confirmation')
    }
  },
)

watch(
  () => form.current_password,
  () => {
    if (form.password) {
      formRef.value?.validateField('password')
    }
  },
)

function resetForm() {
  form.current_password = ''
  form.password = ''
  form.password_confirmation = ''
  formRef.value?.clearValidate()
}

async function onSubmit() {
  if (saving.value) return

  try {
    await formRef.value?.validate()
  } catch {
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

    if (!res.ok) return
    resetForm()
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.bao-mat-login {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  --el-font-family: var(--font);
  display: grid;
  gap: 1rem;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.bao-mat-login__lead {
  margin: 0;
  line-height: 1.5;
}

.bao-mat-login__form {
  width: 100%;
}

.bao-mat-login__fields {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0 1rem;
}

.bao-mat-login__fields :deep(.el-form-item) {
  min-width: 0;
}

.bao-mat-login__label-row {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.bao-mat-login__info {
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

.bao-mat-login__info:hover,
.bao-mat-login__info:focus-visible {
  color: var(--accent);
  outline: none;
}

.bao-mat-login__tip p {
  margin: 0 0 0.35rem;
}

.bao-mat-login__tip ul {
  margin: 0;
  padding-left: 1.1rem;
}

.bao-mat-login__tip li + li {
  margin-top: 0.15rem;
}

.bao-mat-login__actions {
  display: flex;
  justify-content: center;
  margin-top: 0.25rem;
}

@media (min-width: 768px) {
  .bao-mat-login__fields {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.bao-mat-login :deep(.el-form-item__label),
.bao-mat-login :deep(.el-input),
.bao-mat-login :deep(.el-input__inner),
.bao-mat-login :deep(.el-form-item__error),
.bao-mat-login :deep(button) {
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}
</style>
