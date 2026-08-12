<template>
  <CustomDialog
    v-model="visible"
    title="Nạp tiền"
    :width="560"
    @closed="resetForm"
  >
    <template v-if="user">
      <div class="nap-tien-user">
        <p class="nap-tien-user__name">
          {{ user.ho_ten || '—' }}
          <span class="muted">({{ user.email || '—' }})</span>
        </p>
        <div class="nap-tien-balance">
          <div class="balance-item">
            <span class="balance-label">Edu Coin</span>
            <strong>{{ formatNumber(user.edu_coin) }}</strong>
          </div>
          <div class="balance-item">
            <span class="balance-label">Xu hệ thống</span>
            <strong>{{ formatNumber(user.xu_he_thong) }}</strong>
          </div>
        </div>
      </div>

      <CustomForm ref="formRef" :model="form" :rules="rules" label-position="top">
        <CustomFormItem label="Loại nạp" prop="loai_nap">
          <CustomSelect v-model="form.loai_nap" class="w-full" placeholder="Chọn loại nạp">
            <CustomOption
              v-for="opt in loaiNapOptions"
              :key="opt.value"
              :label="opt.label"
              :value="opt.value"
            />
          </CustomSelect>
        </CustomFormItem>

        <CustomFormItem label="Số lượng" prop="so_luong">
          <CustomInput
            v-model="form.so_luong"
            type="number"
            min="1"
            placeholder="Nhập số lượng cần nạp"
          />
        </CustomFormItem>

        <CustomFormItem label="Hình thức thanh toán" prop="hinh_thuc_thanh_toan">
          <el-radio-group v-model="form.hinh_thuc_thanh_toan" class="payment-methods">
            <el-radio
              v-for="opt in hinhThucOptions"
              :key="opt.value"
              :value="opt.value"
              border
            >
              {{ opt.label }}
            </el-radio>
          </el-radio-group>
        </CustomFormItem>

        <CustomFormItem label="Ghi chú" prop="ghi_chu">
          <CustomInput
            v-model="form.ghi_chu"
            type="textarea"
            :rows="3"
            maxlength="255"
            show-word-limit
            placeholder="Ghi chú thêm (không bắt buộc)"
          />
        </CustomFormItem>
      </CustomForm>
    </template>

    <template #footer>
      <CustomButton @click="visible = false">Hủy</CustomButton>
      <CustomButton type="primary" :icon="Wallet" @click="handleSubmit">
        Xác nhận nạp
      </CustomButton>
    </template>
  </CustomDialog>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { Wallet } from '@element-plus/icons-vue'
import {
  CustomButton,
  CustomDialog,
  CustomForm,
  CustomFormItem,
  CustomInput,
  CustomOption,
  CustomSelect,
} from '@/components/element'

const visible = defineModel({ type: Boolean, default: false })

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['submit'])

const loaiNapOptions = [
  { value: 'edu_coin', label: 'Edu Coin' },
  { value: 'xu_he_thong', label: 'Xu hệ thống' },
]

const hinhThucOptions = [
  { value: 'tien_mat', label: 'Tiền mặt' },
  { value: 'chuyen_khoan', label: 'Chuyển khoản' },
  { value: 'vi_dien_tu', label: 'Ví điện tử' },
  { value: 'khac', label: 'Khác' },
]

const formRef = ref(null)

const form = reactive({
  loai_nap: 'edu_coin',
  so_luong: '',
  hinh_thuc_thanh_toan: 'tien_mat',
  ghi_chu: '',
})

const rules = {
  loai_nap: [{ required: true, message: 'Vui lòng chọn loại nạp', trigger: 'change' }],
  so_luong: [
    { required: true, message: 'Vui lòng nhập số lượng', trigger: 'blur' },
    {
      validator: (_rule, value, callback) => {
        const num = Number(value)
        if (!value && value !== 0) {
          callback(new Error('Vui lòng nhập số lượng'))
          return
        }
        if (!Number.isFinite(num) || !Number.isInteger(num) || num < 1) {
          callback(new Error('Số lượng phải là số nguyên ≥ 1'))
          return
        }
        callback()
      },
      trigger: ['blur', 'change'],
    },
  ],
  hinh_thuc_thanh_toan: [
    { required: true, message: 'Vui lòng chọn hình thức thanh toán', trigger: 'change' },
  ],
}

function formatNumber(value) {
  const num = Number(value ?? 0)
  if (Number.isNaN(num)) return '0'
  return num.toLocaleString('vi-VN')
}

function resetForm() {
  form.loai_nap = 'edu_coin'
  form.so_luong = ''
  form.hinh_thuc_thanh_toan = 'tien_mat'
  form.ghi_chu = ''
  formRef.value?.clearValidate?.()
}

watch(visible, (open) => {
  if (open) resetForm()
})

async function handleSubmit() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid || !props.user) return

  const payload = {
    user_id: props.user.id,
    loai_nap: form.loai_nap,
    so_luong: Number(form.so_luong),
    hinh_thuc_thanh_toan: form.hinh_thuc_thanh_toan,
    ghi_chu: form.ghi_chu.trim() || null,
  }

  emit('submit', payload)
  ElMessage.info('Giao diện nạp tiền đã sẵn sàng — API sẽ được kết nối sau.')
  visible.value = false
}
</script>

<style scoped>
.nap-tien-user {
  margin-bottom: 18px;
  padding: 12px 14px;
  border-radius: 8px;
  background: var(--el-fill-color-light);
}

.nap-tien-user__name {
  margin: 0 0 10px;
  font-size: 14px;
}

.nap-tien-user__name .muted {
  margin-left: 4px;
  color: var(--el-text-color-secondary);
  font-weight: 400;
}

.nap-tien-balance {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.balance-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 120px;
  padding: 8px 12px;
  border-radius: 6px;
  background: var(--el-bg-color);
  border: 1px solid var(--el-border-color-lighter);
}

.balance-label {
  font-size: 12px;
  color: var(--el-text-color-secondary);
}

.balance-item strong {
  font-size: 15px;
  font-weight: 600;
}

.w-full {
  width: 100%;
}

.payment-methods {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.payment-methods :deep(.el-radio) {
  margin-right: 0;
}
</style>
