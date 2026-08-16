<template>
  <CustomDialog
    v-model="visible"
    :title="dialogTitle"
    :width="1020"
    @closed="resetForm"
  >
    <template v-if="user">
      <CustomForm ref="formRef" :model="form" :rules="rules" label-position="top">
        <CustomRow :gutter="16">
          <CustomCol :xs="24" :sm="8">
            <CustomFormItem label="Số dư hiện tại">
              <CustomInput :model-value="formatGrouped(soDuHienTai)" disabled />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="8">
            <CustomFormItem label="Số coin nạp" prop="so_luong">
              <CustomInput
                :model-value="form.so_luong"
                placeholder="Số Edu Coin nạp"
                inputmode="numeric"
                @update:model-value="onSoLuongInput"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="8">
            <CustomFormItem label="Thành tiền">
              <CustomInput :model-value="formatMoney(thanhTien)" disabled>
                <template #append>đ</template>
              </CustomInput>
            </CustomFormItem>
          </CustomCol>
        </CustomRow>

        <CustomRow :gutter="16">
          <CustomCol :xs="24" :sm="8">
            <CustomFormItem label="Khuyến mại" prop="khuyen_mai">
              <div class="promo-field">
                <CustomSelect v-model="form.loai_khuyen_mai" class="promo-field__type">
                  <CustomOption
                    v-for="opt in loaiKhuyenMaiOptions"
                    :key="opt.value"
                    :label="opt.label"
                    :value="opt.value"
                  />
                </CustomSelect>
                <CustomInput
                  :model-value="form.khuyen_mai"
                  class="promo-field__value"
                  :placeholder="form.loai_khuyen_mai === 'phan_tram' ? 'VD: 20' : 'Số coin KM'"
                  inputmode="numeric"
                  @update:model-value="onKhuyenMaiInput"
                >
                  <template #append>
                    {{ form.loai_khuyen_mai === 'phan_tram' ? '%' : 'coin' }}
                  </template>
                </CustomInput>
              </div>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="8">
            <CustomFormItem label="Số coin khuyến mại">
              <CustomInput :model-value="formatGrouped(soCoinKhuyenMai)" disabled />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="8">
            <CustomFormItem label="Tổng coin nhận được">
              <CustomInput :model-value="formatGrouped(tongCoinNhan)" disabled />
            </CustomFormItem>
          </CustomCol>
        </CustomRow>

        <p class="rate-hint">Tỷ giá: 1 Edu Coin = {{ formatMoney(COIN_RATE) }}đ</p>

        <CustomFormItem label="Kênh thanh toán" prop="hinh_thuc_thanh_toan">
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

        <template v-if="form.hinh_thuc_thanh_toan === 'chuyen_khoan'">
          <CustomFormItem label="Chọn tài khoản ngân hàng" prop="ngan_hang_thanh_toan_id">
            <div v-loading="banksLoading" class="bank-list">
              <button
                v-for="bank in banks"
                :key="bank.id"
                type="button"
                class="bank-card"
                :class="{ 'is-selected': selectedBank?.id === bank.id }"
                :title="bank.ten_viet_tat || bank.ten_ngan_hang"
                @click="selectBank(bank)"
              >
                <el-image
                  v-if="bank.hinh_anh_logo"
                  :src="bank.hinh_anh_logo"
                  fit="contain"
                  class="bank-card__logo"
                />
                <div v-else class="bank-card__logo bank-card__logo--empty">
                  {{ bank.ten_viet_tat || 'NH' }}
                </div>
              </button>
              <p v-if="!banksLoading && !banks.length" class="empty-banks">
                Chưa có ngân hàng đang sử dụng.
              </p>
            </div>
          </CustomFormItem>

          <div v-if="selectedBank && thanhTien > 0" class="transfer-panel">
            <div class="transfer-panel__qr">
              <el-image
                :src="qrUrl"
                fit="contain"
                class="qr-image"
                :alt="`QR ${selectedBank.ten_ngan_hang}`"
              >
                <template #error>
                  <div class="qr-fallback">Không tạo được mã QR</div>
                </template>
              </el-image>
            </div>
            <div class="transfer-panel__info">
              <h4>Thông tin chuyển khoản</h4>
              <dl>
                <div>
                  <dt>Ngân hàng</dt>
                  <dd>{{ selectedBank.ten_ngan_hang }}</dd>
                </div>
                <div>
                  <dt>Số tài khoản</dt>
                  <dd>
                    {{ selectedBank.so_tai_khoan }}
                    <CustomButton link type="primary" size="small" @click="copyText(selectedBank.so_tai_khoan)">
                      Sao chép
                    </CustomButton>
                  </dd>
                </div>
                <div>
                  <dt>Chủ tài khoản</dt>
                  <dd>{{ selectedBank.chu_tai_khoan }}</dd>
                </div>
                <div v-if="selectedBank.chi_nhanh">
                  <dt>Chi nhánh</dt>
                  <dd>{{ selectedBank.chi_nhanh }}</dd>
                </div>
                <div>
                  <dt>Số tiền</dt>
                  <dd>
                    <strong>{{ formatMoney(thanhTien) }}đ</strong>
                    <CustomButton link type="primary" size="small" @click="copyText(String(thanhTien))">
                      Sao chép
                    </CustomButton>
                  </dd>
                </div>
                <div>
                  <dt>Nội dung CK</dt>
                  <dd>
                    {{ transferContent }}
                    <CustomButton link type="primary" size="small" @click="copyText(transferContent)">
                      Sao chép
                    </CustomButton>
                  </dd>
                </div>
              </dl>
            </div>
          </div>
          <p v-else-if="selectedBank && thanhTien <= 0" class="hint-amount">
            Nhập số coin nạp để hiển thị mã QR.
          </p>
        </template>

        <CustomFormItem label="Ghi chú" prop="ghi_chu">
          <CustomInput
            v-model="form.ghi_chu"
            type="textarea"
            :rows="2"
            maxlength="255"
            show-word-limit
            placeholder="Ghi chú thêm (không bắt buộc)"
          />
        </CustomFormItem>
      </CustomForm>
    </template>

    <template #footer>
      <CustomButton @click="visible = false">Hủy</CustomButton>
      <CustomButton
        type="primary"
        :icon="Wallet"
        :loading="submitting"
        :disabled="!canSubmit"
        @click="handleSubmit"
      >
        {{ submitLabel }}
      </CustomButton>
    </template>
  </CustomDialog>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Wallet } from '@element-plus/icons-vue'
import { request } from '@/api'
import { API_NGAN_HANG_THANH_TOAN, API_NGUOI_DUNG } from '@/constants/constant_api'
import { taoMaNap } from '@/utils/maGiaoDich'
import {
  CustomButton,
  CustomCol,
  CustomDialog,
  CustomForm,
  CustomFormItem,
  CustomInput,
  CustomOption,
  CustomRow,
  CustomSelect,
} from '@/components/element'

/** 1 Edu Coin = 1000 VND */
const COIN_RATE = 1000

const visible = defineModel({ type: Boolean, default: false })

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['success'])

const hinhThucOptions = [
  { value: 'tien_mat', label: 'Tiền mặt' },
  { value: 'chuyen_khoan', label: 'Chuyển khoản' },
]

const loaiKhuyenMaiOptions = [
  { value: 'phan_tram', label: 'Theo %' },
  { value: 'gia_tri', label: 'Theo giá trị' },
]

const formRef = ref(null)
const banks = ref([])
const banksLoading = ref(false)
const selectedBank = ref(null)
const submitting = ref(false)
const maGiaoDich = ref('')

const form = reactive({
  so_luong: '',
  loai_khuyen_mai: 'phan_tram',
  khuyen_mai: '0',
  hinh_thuc_thanh_toan: 'tien_mat',
  ngan_hang_thanh_toan_id: null,
  ghi_chu: '',
})

/** Chỉ giữ chữ số rồi format nhóm 3 với dấu `.` */
function formatGrouped(value) {
  if (value === '' || value == null) return ''
  const digits = String(value).replace(/\D/g, '')
  if (!digits) return ''
  return Number(digits).toLocaleString('vi-VN')
}

function formatMoney(value) {
  const formatted = formatGrouped(value)
  return formatted || '0'
}

function parseGrouped(value) {
  const digits = String(value ?? '').replace(/\D/g, '')
  if (!digits) return null
  const num = Number(digits)
  return Number.isFinite(num) ? num : null
}

function parseNonNegInt(value) {
  const num = typeof value === 'number' ? value : parseGrouped(value)
  if (num === null || !Number.isInteger(num) || num < 0) return null
  return num
}

function parsePosInt(value) {
  const num = parseNonNegInt(value)
  return num !== null && num >= 1 ? num : null
}

const rules = {
  so_luong: [
    { required: true, message: 'Vui lòng nhập số coin nạp', trigger: 'blur' },
    {
      validator: (_rule, value, callback) => {
        const num = parsePosInt(value)
        if (num === null) {
          callback(new Error('Số coin phải là số nguyên ≥ 1'))
          return
        }
        callback()
      },
      trigger: ['blur', 'change'],
    },
  ],
  khuyen_mai: [
    { required: true, message: 'Vui lòng nhập khuyến mại', trigger: 'blur' },
    {
      validator: (_rule, value, callback) => {
        const num = parseNonNegInt(value)
        if (num === null) {
          callback(new Error('Khuyến mại phải là số nguyên ≥ 0'))
          return
        }
        if (form.loai_khuyen_mai === 'phan_tram' && num > 1000) {
          callback(new Error('Phần trăm khuyến mại tối đa 1000%'))
          return
        }
        callback()
      },
      trigger: ['blur', 'change'],
    },
  ],
  hinh_thuc_thanh_toan: [
    { required: true, message: 'Vui lòng chọn kênh thanh toán', trigger: 'change' },
  ],
  ngan_hang_thanh_toan_id: [
    {
      validator: (_rule, value, callback) => {
        if (form.hinh_thuc_thanh_toan !== 'chuyen_khoan') {
          callback()
          return
        }
        if (!value) {
          callback(new Error('Vui lòng chọn ngân hàng nhận tiền'))
          return
        }
        callback()
      },
      trigger: 'change',
    },
  ],
}

const dialogTitle = computed(() => {
  if (!props.user) return 'Nạp Edu Coin'
  const name = props.user.ho_ten || '—'
  const email = props.user.email || '—'
  return `Nạp Edu Coin — ${name} (${email})`
})

const soDuHienTai = computed(() => {
  const num = Number(props.user?.edu_coin ?? 0)
  return Number.isFinite(num) ? Math.max(0, Math.trunc(num)) : 0
})

const soLuong = computed(() => parsePosInt(form.so_luong) ?? 0)

const khuyenMaiValue = computed(() => parseNonNegInt(form.khuyen_mai) ?? 0)

const soCoinKhuyenMai = computed(() => {
  if (soLuong.value < 1) return 0
  if (form.loai_khuyen_mai === 'phan_tram') {
    return Math.floor((soLuong.value * khuyenMaiValue.value) / 100)
  }
  return khuyenMaiValue.value
})

const tongCoinNhan = computed(() => soLuong.value + soCoinKhuyenMai.value)

/** Thành tiền = số coin nạp × tỷ giá (không gồm khuyến mại) */
const thanhTien = computed(() => soLuong.value * COIN_RATE)

function onSoLuongInput(raw) {
  form.so_luong = formatGrouped(raw)
}

function onKhuyenMaiInput(raw) {
  form.khuyen_mai = formatGrouped(raw)
}

const transferContent = computed(() => maGiaoDich.value || '')

const qrUrl = computed(() => {
  if (!selectedBank.value || thanhTien.value <= 0) return ''
  const bankCode = (selectedBank.value.ten_viet_tat || '').trim().replace(/\s+/g, '')
  const accountNo = (selectedBank.value.so_tai_khoan || '').replace(/\s+/g, '')
  if (!bankCode || !accountNo) return ''

  const params = new URLSearchParams({
    amount: String(thanhTien.value),
    addInfo: transferContent.value,
    accountName: selectedBank.value.chu_tai_khoan || '',
  })

  return `https://img.vietqr.io/image/${encodeURIComponent(bankCode)}-${encodeURIComponent(accountNo)}-compact2.png?${params.toString()}`
})

const canSubmit = computed(() => {
  if (!props.user || tongCoinNhan.value < 1 || submitting.value) return false
  if (form.hinh_thuc_thanh_toan === 'chuyen_khoan' && !selectedBank.value) return false
  return true
})

const submitLabel = computed(() =>
  form.hinh_thuc_thanh_toan === 'chuyen_khoan' ? 'Xác nhận đã nhận CK' : 'Nạp tiền',
)

function selectBank(bank) {
  selectedBank.value = bank
  form.ngan_hang_thanh_toan_id = bank.id
  formRef.value?.clearValidate?.(['ngan_hang_thanh_toan_id'])
}

async function copyText(text) {
  try {
    await navigator.clipboard.writeText(text)
    ElMessage.success('Đã sao chép')
  } catch {
    ElMessage.error('Không sao chép được')
  }
}

async function fetchBanks() {
  banksLoading.value = true
  const res = await request({
    url: API_NGAN_HANG_THANH_TOAN.LIST,
    params: { trang_thai: 'dang_su_dung', start: 0, limit: 100 },
    loading: false,
  })
  banksLoading.value = false

  if (!res.ok) {
    banks.value = []
    return
  }

  banks.value = res.data ?? []
}

function resetForm() {
  form.so_luong = ''
  form.loai_khuyen_mai = 'phan_tram'
  form.khuyen_mai = '0'
  form.hinh_thuc_thanh_toan = 'tien_mat'
  form.ngan_hang_thanh_toan_id = null
  form.ghi_chu = ''
  selectedBank.value = null
  submitting.value = false
  maGiaoDich.value = taoMaNap()
  formRef.value?.clearValidate?.()
}

watch(visible, (open) => {
  if (!open) return
  resetForm()
  fetchBanks()
})

watch(
  () => form.hinh_thuc_thanh_toan,
  (value) => {
    if (value !== 'chuyen_khoan') {
      selectedBank.value = null
      form.ngan_hang_thanh_toan_id = null
    }
  },
)

watch(
  () => form.loai_khuyen_mai,
  () => {
    formRef.value?.validateField?.('khuyen_mai').catch(() => {})
  },
)

async function handleSubmit() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid || !props.user || !canSubmit.value) return

  const promoLabel =
    form.loai_khuyen_mai === 'phan_tram'
      ? `${formatGrouped(khuyenMaiValue.value)}%`
      : `${formatGrouped(khuyenMaiValue.value)} coin`

  const amountLabel = `${formatGrouped(tongCoinNhan.value)} Edu Coin (CK ${formatMoney(thanhTien.value)}đ, KM ${promoLabel})`
  const channelLabel =
    form.hinh_thuc_thanh_toan === 'tien_mat' ? 'tiền mặt' : 'chuyển khoản'

  try {
    await ElMessageBox.confirm(
      `Xác nhận nạp ${amountLabel} cho «${props.user.ho_ten || props.user.email}» qua ${channelLabel}?`,
      'Xác nhận nạp Edu Coin',
      { type: 'warning', confirmButtonText: 'Xác nhận', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  submitting.value = true
  const body = {
    so_coin_nap: soLuong.value,
    loai_khuyen_mai: form.loai_khuyen_mai,
    khuyen_mai: khuyenMaiValue.value,
    kenh_thanh_toan: form.hinh_thuc_thanh_toan,
    ma_giao_dich: maGiaoDich.value,
    ghi_chu: form.ghi_chu.trim() || null,
  }
  if (form.hinh_thuc_thanh_toan === 'chuyen_khoan') {
    body.ngan_hang_thanh_toan_id = form.ngan_hang_thanh_toan_id
  }

  const res = await request({
    url: API_NGUOI_DUNG.NAP_TIEN(props.user.id),
    body,
  })
  submitting.value = false

  if (!res.ok) return

  emit('success', res.data)
  visible.value = false
}
</script>

<style scoped>
.rate-hint {
  margin: -4px 0 14px;
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

.promo-field {
  display: flex;
  gap: 8px;
  width: 100%;
}

.promo-field__type {
  width: 140px;
  flex-shrink: 0;
}

.promo-field__value {
  flex: 1;
  min-width: 0;
}

.hint-amount {
  margin: 6px 0 0;
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

.payment-methods {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.payment-methods :deep(.el-radio) {
  margin-right: 0;
}

.bank-list {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  min-height: 80px;
  width: 100%;
}

.bank-card {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 128px;
  height: 72px;
  padding: 8px;
  border: 1px solid var(--el-border-color);
  border-radius: 10px;
  background: var(--el-bg-color);
  cursor: pointer;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.bank-card:hover {
  border-color: var(--el-color-primary-light-5);
}

.bank-card.is-selected {
  border-color: var(--el-color-primary);
  box-shadow: 0 0 0 1px var(--el-color-primary);
}

.bank-card__logo {
  width: 100%;
  height: 100%;
  border-radius: 6px;
  background: var(--el-fill-color-blank);
}

.bank-card__logo--empty {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  color: var(--el-color-primary);
  background: var(--el-color-primary-light-9);
  border: 1px dashed var(--el-color-primary-light-5);
}

.empty-banks {
  margin: 0;
  width: 100%;
  font-size: 13px;
  color: var(--el-text-color-secondary);
}

.transfer-panel {
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 16px;
  margin: 0 0 16px;
  padding: 14px;
  border-radius: 8px;
  border: 1px solid var(--el-border-color-lighter);
  background: var(--el-fill-color-blank);
}

.transfer-panel__qr {
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-image {
  width: 168px;
  height: 168px;
  border-radius: 8px;
  border: 1px solid var(--el-border-color-lighter);
  background: #fff;
}

.qr-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 168px;
  height: 168px;
  font-size: 12px;
  color: var(--el-text-color-placeholder);
  text-align: center;
  padding: 8px;
}

.transfer-panel__info h4 {
  margin: 0 0 10px;
  font-size: 14px;
}

.transfer-panel__info dl {
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.transfer-panel__info dl > div {
  display: grid;
  grid-template-columns: 110px 1fr;
  gap: 8px;
  font-size: 13px;
}

.transfer-panel__info dt {
  color: var(--el-text-color-secondary);
}

.transfer-panel__info dd {
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px;
  word-break: break-word;
}

@media (max-width: 640px) {
  .promo-field {
    flex-direction: column;
  }

  .promo-field__type {
    width: 100%;
  }

  .transfer-panel {
    grid-template-columns: 1fr;
  }

  .transfer-panel__info dl > div {
    grid-template-columns: 1fr;
    gap: 2px;
  }
}
</style>
