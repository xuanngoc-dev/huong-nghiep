<template>
  <div class="bao-mat-payment">
    <p class="bao-mat-payment__lead muted">
      {{
        daCaiMatKhau
          ? 'Đổi mật khẩu thanh toán (6 chữ số). Cần nhập mật khẩu đăng nhập để xác nhận.'
          : 'Thiết lập mật khẩu thanh toán (6 chữ số) để xác nhận khi thanh toán bằng Edu Coin. Cần nhập mật khẩu đăng nhập.'
      }}
    </p>

    <CustomForm
      ref="formRef"
      class="bao-mat-payment__form"
      :model="form"
      :rules="rules"
      @submit.prevent="onSubmit"
    >
      <div class="bao-mat-payment__fields">
        <CustomFormItem label="Mật khẩu đăng nhập" prop="current_password">
          <CustomInput
            v-model="form.current_password"
            type="password"
            show-password
            maxlength="255"
            autocomplete="current-password"
            placeholder="Nhập mật khẩu đăng nhập"
          />
        </CustomFormItem>

        <CustomFormItem prop="mat_khau_thanh_toan">
          <template #label>
            <span class="bao-mat-payment__label-row">
              Mật khẩu thanh toán mới
              <CustomTooltip placement="top" :show-after="150">
                <template #content>
                  <div class="bao-mat-payment__tip">
                    <p>Mật khẩu thanh toán cần đáp ứng:</p>
                    <ul>
                      <li>Gồm đúng 6 chữ số</li>
                      <li>Chỉ chứa số từ 0 đến 9</li>
                      <li v-if="daCaiMatKhau">Khác mật khẩu thanh toán hiện tại</li>
                    </ul>
                  </div>
                </template>
                <button
                  type="button"
                  class="bao-mat-payment__info"
                  aria-label="Yêu cầu mật khẩu thanh toán"
                  @click.prevent
                >
                  <el-icon :size="16"><InfoFilled /></el-icon>
                </button>
              </CustomTooltip>
            </span>
          </template>
          <CustomInput
            v-model="form.mat_khau_thanh_toan"
            type="password"
            show-password
            maxlength="6"
            inputmode="numeric"
            autocomplete="off"
            placeholder="Nhập 6 chữ số"
          />
        </CustomFormItem>

        <CustomFormItem label="Nhập lại mật khẩu thanh toán" prop="mat_khau_thanh_toan_confirmation">
          <CustomInput
            v-model="form.mat_khau_thanh_toan_confirmation"
            type="password"
            show-password
            maxlength="6"
            inputmode="numeric"
            autocomplete="off"
            placeholder="Nhập lại 6 chữ số"
          />
        </CustomFormItem>
      </div>

      <div class="bao-mat-payment__actions">
        <button class="btn" type="submit" :disabled="saving">
          {{ saving ? 'Đang lưu...' : 'Cập nhật mật khẩu thanh toán' }}
        </button>
      </div>
    </CustomForm>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { InfoFilled } from '@element-plus/icons-vue'
import { request } from '@/api'
import {
  CustomForm,
  CustomFormItem,
  CustomInput,
  CustomTooltip,
} from '@/components/element'
import { API_AUTH } from '@/constants/constant_api'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const formRef = ref(null)
const saving = ref(false)

const daCaiMatKhau = computed(() => Boolean(auth.user?.da_cai_mat_khau_thanh_toan))

onMounted(() => {
  auth.fetchMe().catch(() => {})
})

const form = reactive({
  current_password: '',
  mat_khau_thanh_toan: '',
  mat_khau_thanh_toan_confirmation: '',
})

function onlyDigits(value) {
  return String(value || '').replace(/\D/g, '').slice(0, 6)
}

function validateLoginPassword(_rule, value, callback) {
  if (!String(value || '').trim()) {
    callback(new Error('Vui lòng nhập mật khẩu đăng nhập.'))
    return
  }
  callback()
}

function validatePaymentPassword(_rule, value, callback) {
  const pin = String(value || '')
  if (!pin) {
    callback(new Error('Vui lòng nhập mật khẩu thanh toán mới.'))
    return
  }
  if (!/^\d{6}$/.test(pin)) {
    callback(new Error('Mật khẩu thanh toán phải là số gồm đúng 6 chữ số.'))
    return
  }
  callback()
}

function validatePaymentPasswordConfirm(_rule, value, callback) {
  if (!String(value || '')) {
    callback(new Error('Vui lòng nhập lại mật khẩu thanh toán mới.'))
    return
  }
  if (value !== form.mat_khau_thanh_toan) {
    callback(new Error('Mật khẩu thanh toán xác nhận không khớp.'))
    return
  }
  callback()
}

const rules = {
  current_password: [
    { required: true, validator: validateLoginPassword, trigger: ['blur', 'change'] },
  ],
  mat_khau_thanh_toan: [
    { required: true, validator: validatePaymentPassword, trigger: ['blur', 'change'] },
  ],
  mat_khau_thanh_toan_confirmation: [
    { required: true, validator: validatePaymentPasswordConfirm, trigger: ['blur', 'change'] },
  ],
}

watch(
  () => form.mat_khau_thanh_toan,
  (value) => {
    const digits = onlyDigits(value)
    if (digits !== value) {
      form.mat_khau_thanh_toan = digits
    }
    if (form.mat_khau_thanh_toan_confirmation) {
      formRef.value?.validateField('mat_khau_thanh_toan_confirmation')
    }
  },
)

watch(
  () => form.mat_khau_thanh_toan_confirmation,
  (value) => {
    const digits = onlyDigits(value)
    if (digits !== value) {
      form.mat_khau_thanh_toan_confirmation = digits
    }
  },
)

function resetForm() {
  form.current_password = ''
  form.mat_khau_thanh_toan = ''
  form.mat_khau_thanh_toan_confirmation = ''
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
      url: API_AUTH.CHANGE_PAYMENT_PASSWORD,
      body: {
        current_password: form.current_password,
        mat_khau_thanh_toan: form.mat_khau_thanh_toan,
        mat_khau_thanh_toan_confirmation: form.mat_khau_thanh_toan_confirmation,
      },
      successFallback: 'Đổi mật khẩu thanh toán thành công.',
      errorFallback: 'Không đổi được mật khẩu thanh toán.',
    })

    if (!res.ok) return

    if (res.data?.da_cai_mat_khau_thanh_toan) {
      auth.updateUser({ da_cai_mat_khau_thanh_toan: true })
    }
    resetForm()
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.bao-mat-payment {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  --el-font-family: var(--font);
  display: grid;
  gap: 1rem;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.bao-mat-payment__lead {
  margin: 0;
  line-height: 1.5;
}

.bao-mat-payment__form {
  width: 100%;
}

.bao-mat-payment__fields {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0 1rem;
}

.bao-mat-payment__fields :deep(.el-form-item) {
  min-width: 0;
}

.bao-mat-payment__label-row {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.bao-mat-payment__info {
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

.bao-mat-payment__info:hover,
.bao-mat-payment__info:focus-visible {
  color: var(--accent);
  outline: none;
}

.bao-mat-payment__tip p {
  margin: 0 0 0.35rem;
}

.bao-mat-payment__tip ul {
  margin: 0;
  padding-left: 1.1rem;
}

.bao-mat-payment__tip li + li {
  margin-top: 0.15rem;
}

.bao-mat-payment__actions {
  display: flex;
  justify-content: center;
  margin-top: 0.25rem;
}

@media (min-width: 768px) {
  .bao-mat-payment__fields {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.bao-mat-payment :deep(.el-form-item__label),
.bao-mat-payment :deep(.el-input),
.bao-mat-payment :deep(.el-input__inner),
.bao-mat-payment :deep(.el-form-item__error),
.bao-mat-payment :deep(button) {
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}
</style>
