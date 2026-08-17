<template>
  <CustomDialog
    v-model="visible"
    :title="dialogTitle"
    :width="dialogWidth"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    class="thanh-toan-quiz-dialog"
    @closed="onModalClosed"
  >
    <div v-if="phase === 'select'" class="pay-select">
      <p class="pay-select__lead">
        Phí xem kết quả: <strong>{{ formatNumber(PHI_EDU_COIN) }} Edu Coin</strong>
        hoặc <strong>{{ formatMoney(PHI_CHUYEN_KHOAN) }}đ</strong>.
      </p>

      <div class="pay-methods" role="radiogroup" aria-label="Hình thức thanh toán">
        <button
          type="button"
          class="pay-method"
          :class="{ 'is-selected': hinhThuc === 'edu_coin' }"
          @click="hinhThuc = 'edu_coin'"
        >
          <CustomIcon :size="22"><Coin /></CustomIcon>
          <span class="pay-method__name">Edu Coin</span>
          <span class="pay-method__amount">{{ formatNumber(PHI_EDU_COIN) }} coin</span>
          <span class="pay-method__hint">Số dư: {{ formatNumber(soDuEduCoin) }}</span>
        </button>
        <button
          type="button"
          class="pay-method"
          :class="{ 'is-selected': hinhThuc === 'chuyen_khoan' }"
          @click="hinhThuc = 'chuyen_khoan'"
        >
          <CustomIcon :size="22"><CreditCard /></CustomIcon>
          <span class="pay-method__name">Chuyển khoản</span>
          <span class="pay-method__amount">{{ formatMoney(PHI_CHUYEN_KHOAN) }}đ</span>
          <span class="pay-method__hint">Quét QR hoặc chuyển khoản</span>
        </button>
      </div>

      <p v-if="hinhThuc === 'edu_coin' && !duEduCoin" class="error-text">
        Số dư Edu Coin không đủ.
        <RouterLink :to="{ name: 'profile-edu-coin' }">Nạp thêm</RouterLink>
      </p>

      <fieldset
        v-if="hinhThuc === 'chuyen_khoan'"
        class="pay-banks"
        :disabled="banksLoading"
      >
        <legend>Chọn ngân hàng nhận chuyển khoản</legend>
        <p v-if="banksLoading" class="muted">Đang tải danh sách ngân hàng...</p>
        <p v-else-if="!banks.length" class="muted">Chưa có ngân hàng đang sử dụng.</p>
        <div v-else class="bank-list">
          <button
            v-for="bank in banks"
            :key="bank.id"
            type="button"
            class="bank-card"
            :class="{ 'is-selected': selectedBank?.id === bank.id }"
            :title="bank.ten_viet_tat || bank.ten_ngan_hang"
            @click="selectedBank = bank"
          >
            <img
              v-if="bank.hinh_anh_logo"
              :src="bank.hinh_anh_logo"
              :alt="bank.ten_ngan_hang"
              class="bank-card__logo"
            />
            <span v-else class="bank-card__logo bank-card__logo--empty">
              {{ bank.ten_viet_tat || 'NH' }}
            </span>
          </button>
        </div>
      </fieldset>
    </div>

    <div v-else-if="pendingPay" class="nap-modal">
      <aside class="nap-modal__timer" aria-live="polite">
        <p class="nap-modal__timer-label">Thời gian còn lại</p>
        <p class="nap-modal__timer-value">{{ countdownLabel }}</p>
        <p class="nap-modal__timer-hint">
          Vui lòng hoàn tất chuyển khoản trước khi hết giờ.
        </p>
      </aside>

      <div class="nap-modal__body">
        <p class="nap-modal__lead">
          <template v-if="pendingPay.id">
            Hệ thống đã ghi nhận yêu cầu thanh toán
            <strong>{{ formatMoney(pendingPay.so_tien_thanh_toan) }}đ</strong>.
            Vui lòng chờ duyệt sau khi chuyển khoản.
          </template>
          <template v-else>
            Vui lòng chuyển khoản
            <strong>{{ formatMoney(pendingPay.so_tien_thanh_toan) }}đ</strong>
            theo thông tin bên dưới, rồi nhấn <strong>Đã chuyển</strong>.
          </template>
        </p>

        <div class="transfer-panel">
          <div class="transfer-panel__qr">
            <img
              v-if="pendingQrUrl"
              :src="pendingQrUrl"
              class="qr-image"
              :alt="`QR ${pendingThongTin.ten_ngan_hang || ''}`"
            />
            <div v-else class="qr-fallback">Không tạo được mã QR</div>
          </div>
          <div class="transfer-panel__info">
            <h2>Thông tin chuyển khoản</h2>
            <dl>
              <div>
                <dt>Ngân hàng</dt>
                <dd>{{ pendingThongTin.ten_ngan_hang || '—' }}</dd>
              </div>
              <div>
                <dt>Số tài khoản</dt>
                <dd>
                  {{ pendingThongTin.so_tai_khoan || '—' }}
                  <button
                    v-if="pendingThongTin.so_tai_khoan"
                    type="button"
                    class="copy-btn"
                    @click="copyText(pendingThongTin.so_tai_khoan)"
                  >
                    Sao chép
                  </button>
                </dd>
              </div>
              <div>
                <dt>Chủ tài khoản</dt>
                <dd>{{ pendingThongTin.chu_tai_khoan || '—' }}</dd>
              </div>
              <div v-if="pendingThongTin.chi_nhanh">
                <dt>Chi nhánh</dt>
                <dd>{{ pendingThongTin.chi_nhanh }}</dd>
              </div>
              <div>
                <dt>Số tiền</dt>
                <dd>
                  <strong>{{ formatMoney(pendingPay.so_tien_thanh_toan) }}đ</strong>
                  <button
                    type="button"
                    class="copy-btn"
                    @click="copyText(String(pendingPay.so_tien_thanh_toan))"
                  >
                    Sao chép
                  </button>
                </dd>
              </div>
              <div>
                <dt>Nội dung CK</dt>
                <dd>
                  {{ noiDungChuyenKhoan || '—' }}
                  <button
                    v-if="noiDungChuyenKhoan"
                    type="button"
                    class="copy-btn"
                    @click="copyText(noiDungChuyenKhoan)"
                  >
                    Sao chép
                  </button>
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <p class="nap-modal__note">
          Lưu ý: vui lòng chuyển khoản <strong>đúng số tiền</strong> và
          <strong>đúng nội dung chuyển khoản</strong> để hệ thống đối soát và duyệt nhanh.
        </p>
      </div>
    </div>

    <template #footer>
      <template v-if="phase === 'select'">
        <button class="btn btn-outline" type="button" @click="closeModal">Hủy</button>
        <button class="btn" type="button" :disabled="!canSubmit" @click="onSubmit">
          {{ submitting ? 'Đang xử lý...' : 'Thanh toán' }}
        </button>
      </template>
      <template v-else>
        <button
          class="btn"
          type="button"
          :disabled="!canConfirmTransfer"
          @click="confirmTransferred"
        >
          {{ confirmTransferLabel }}
        </button>
        <button class="btn btn-outline" type="button" @click="closeModal">Đóng</button>
      </template>
    </template>
  </CustomDialog>

  <CustomDialog
    v-model="passwordVisible"
    title="Mật khẩu thanh toán"
    :width="420"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    @opened="focusOtp"
    @closed="resetPasswordForm"
  >
    <p class="pay-password__lead">
      Nhập mật khẩu thanh toán để trừ
      <strong>{{ formatNumber(PHI_EDU_COIN) }} Edu Coin</strong>.
    </p>
    <div class="pay-password__field">
      <span class="pay-password__label">Mật khẩu thanh toán</span>
      <el-input-otp
        ref="otpRef"
        v-model="matKhauThanhToan"
        class="pay-password__otp"
        :length="6"
        mask
        inputmode="numeric"
        size="large"
        :validator="isPaymentOtpDigit"
        aria-label="Mật khẩu thanh toán"
        @finish="confirmEduCoinPayment"
      />
    </div>
    <template #footer>
      <button class="btn btn-outline" type="button" @click="passwordVisible = false">Hủy</button>
      <button
        class="btn"
        type="button"
        :disabled="!canConfirmPassword"
        @click="confirmEduCoinPayment"
      >
        {{ submitting ? 'Đang xử lý...' : 'Xác nhận' }}
      </button>
    </template>
  </CustomDialog>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Coin, CreditCard } from '@element-plus/icons-vue'
import { request } from '@/api'
import { CustomDialog, CustomIcon } from '@/components/element'
import { API_PUBLIC } from '@/constants/constant_api'
import { useAuthStore } from '@/stores/auth'
import { taoMaThanhToan } from '@/utils/maGiaoDich'

const PHI_EDU_COIN = 15
const PHI_CHUYEN_KHOAN = 15000
const TRANSFER_WAIT_MINUTES = 5
const TRANSFER_WAIT_SECONDS = TRANSFER_WAIT_MINUTES * 60
const STATUS_POLL_MS = 5000

const props = defineProps({
  phien: { type: Object, default: null },
})

const emit = defineEmits(['paid'])

const visible = defineModel({ type: Boolean, default: false })
const auth = useAuthStore()

const phase = ref('select')
const hinhThuc = ref('edu_coin')
const banks = ref([])
const banksLoading = ref(false)
const selectedBank = ref(null)
const submitting = ref(false)
const pendingPay = ref(null)
const remainingSeconds = ref(TRANSFER_WAIT_SECONDS)
const waitResolved = ref(false)
const confirmingTransfer = ref(false)
const passwordVisible = ref(false)
const matKhauThanhToan = ref('')
const otpRef = ref(null)

let countdownTimer = null
let pollTimer = null

const soDuEduCoin = computed(() => Number(auth.user?.edu_coin) || 0)
const duEduCoin = computed(() => soDuEduCoin.value >= PHI_EDU_COIN)

const dialogTitle = computed(() =>
  phase.value === 'transfer' ? 'Yêu cầu chuyển khoản' : 'Thanh toán kết quả trắc nghiệm',
)
const dialogWidth = computed(() => (phase.value === 'transfer' ? 960 : 560))

const canSubmit = computed(() => {
  if (submitting.value || !props.phien?.id) return false
  if (hinhThuc.value === 'edu_coin') return duEduCoin.value
  return Boolean(selectedBank.value)
})

const canConfirmPassword = computed(() =>
  Boolean(/^\d{6}$/.test(matKhauThanhToan.value) && !submitting.value && props.phien?.id),
)

function isPaymentOtpDigit(value) {
  return /^\d$/.test(String(value || ''))
}

function focusOtp() {
  otpRef.value?.focus?.(0)
}

const hasCreatedRequest = computed(() => Boolean(pendingPay.value?.id))

const canConfirmTransfer = computed(() =>
  Boolean(
    pendingPay.value
    && !hasCreatedRequest.value
    && !confirmingTransfer.value
    && remainingSeconds.value > 0
    && !waitResolved.value,
  ),
)

const confirmTransferLabel = computed(() => {
  if (confirmingTransfer.value) return 'Đang gửi...'
  if (hasCreatedRequest.value) return 'Đã gửi yêu cầu'
  return 'Đã chuyển'
})

const pendingThongTin = computed(() => pendingPay.value?.thong_tin_thanh_toan || {})

const noiDungChuyenKhoan = computed(() =>
  pendingPay.value?.ma_giao_dich
  || pendingThongTin.value.ma_giao_dich
  || pendingThongTin.value.noi_dung_chuyen_khoan
  || '',
)

const countdownLabel = computed(() => {
  const total = Math.max(0, remainingSeconds.value)
  const minutes = Math.floor(total / 60)
  const seconds = total % 60
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
})

const pendingQrUrl = computed(() => {
  const info = pendingThongTin.value
  const amount = Number(pendingPay.value?.so_tien_thanh_toan) || 0
  const bankCode = String(info.ten_viet_tat || '').trim().replace(/\s+/g, '')
  const accountNo = String(info.so_tai_khoan || '').replace(/\s+/g, '')
  if (!bankCode || !accountNo || amount <= 0) return ''

  const params = new URLSearchParams({
    amount: String(amount),
    addInfo: noiDungChuyenKhoan.value,
    accountName: info.chu_tai_khoan || '',
  })

  return `https://img.vietqr.io/image/${encodeURIComponent(bankCode)}-${encodeURIComponent(accountNo)}-compact2.png?${params.toString()}`
})

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}

function formatMoney(value) {
  return formatNumber(value)
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
  if (banks.value.length) return
  banksLoading.value = true
  const res = await request({
    url: API_PUBLIC.NGAN_HANG_THANH_TOAN.LIST,
    loading: false,
    silentSuccess: true,
    errorFallback: 'Không tải được danh sách ngân hàng.',
  })
  banksLoading.value = false
  banks.value = res.ok ? (res.data ?? []) : []
}

function stopWaitTimers() {
  if (countdownTimer) {
    clearInterval(countdownTimer)
    countdownTimer = null
  }
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

function closeModal() {
  visible.value = false
}

function resetState() {
  stopWaitTimers()
  phase.value = 'select'
  hinhThuc.value = 'edu_coin'
  selectedBank.value = null
  submitting.value = false
  pendingPay.value = null
  remainingSeconds.value = TRANSFER_WAIT_SECONDS
  waitResolved.value = false
  confirmingTransfer.value = false
  passwordVisible.value = false
  matKhauThanhToan.value = ''
}

function resetPasswordForm() {
  matKhauThanhToan.value = ''
}

function onModalClosed() {
  resetState()
}

function buildTransferInfo(bank) {
  const maGiaoDich = String(props.phien?.ma_giao_dich || '').trim().toUpperCase() || taoMaThanhToan()

  return {
    ngan_hang_thanh_toan_id: bank.id,
    ten_ngan_hang: bank.ten_ngan_hang,
    ten_viet_tat: bank.ten_viet_tat,
    so_tai_khoan: bank.so_tai_khoan,
    chu_tai_khoan: bank.chu_tai_khoan,
    chi_nhanh: bank.chi_nhanh,
    ma_giao_dich: maGiaoDich,
    noi_dung_chuyen_khoan: maGiaoDich,
  }
}

function startWaitTimers() {
  stopWaitTimers()
  remainingSeconds.value = TRANSFER_WAIT_SECONDS
  waitResolved.value = false

  countdownTimer = setInterval(() => {
    remainingSeconds.value -= 1
    if (remainingSeconds.value <= 0) {
      remainingSeconds.value = 0
      onCountdownExpired()
    }
  }, 1000)
}

function startPollTimer() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
  pollTimer = setInterval(() => {
    pollPendingStatus()
  }, STATUS_POLL_MS)
  pollPendingStatus()
}

async function handleStatusChange(status) {
  if (waitResolved.value) return true
  if (status === 'da_hoan_thanh') {
    waitResolved.value = true
    stopWaitTimers()
    visible.value = false
    emit('paid')
    ElMessage.success('Thanh toán thành công.')
    return true
  }
  return false
}

async function pollPendingStatus() {
  const id = pendingPay.value?.id
  const phienId = props.phien?.id
  if (!id || !phienId || waitResolved.value) return false

  const res = await request({
    url: API_PUBLIC.LICH_SU_TRAC_NGHIEM.THANH_TOAN_SHOW(phienId, id),
    loading: false,
    silent: true,
  })
  if (!res.ok) return false

  pendingPay.value = { ...pendingPay.value, ...res.data }
  return handleStatusChange(res.data?.trang_thai)
}

async function onCountdownExpired() {
  if (waitResolved.value) return
  stopWaitTimers()

  const changed = await pollPendingStatus()
  if (changed || waitResolved.value) return

  waitResolved.value = true
  visible.value = false

  try {
    await ElMessageBox.confirm(
      'Vui lòng thanh toán lại. Nếu đã chuyển khoản mà chưa được duyệt thì liên hệ CSKH để được hỗ trợ.',
      'Hết thời gian chuyển khoản',
      {
        type: 'warning',
        confirmButtonText: 'Đã hiểu',
        showCancelButton: false,
        closeOnClickModal: false,
        closeOnPressEscape: false,
      },
    )
  } catch {
    // Người dùng đóng hộp thoại.
  }
}

function openTransfer(payload, { poll = false } = {}) {
  pendingPay.value = payload
  phase.value = 'transfer'
  startWaitTimers()
  if (poll && payload.id) startPollTimer()
}

async function thanhToanEduCoin() {
  submitting.value = true
  const res = await request({
    url: API_PUBLIC.LICH_SU_TRAC_NGHIEM.THANH_TOAN(props.phien.id),
    body: {
      hinh_thuc_thanh_toan: 'edu_coin',
      mat_khau_thanh_toan: matKhauThanhToan.value,
    },
    successFallback: 'Thanh toán thành công.',
    errorFallback: 'Không thanh toán được bằng Edu Coin.',
  })
  submitting.value = false
  if (!res.ok) return false

  const soDu = Number(res.body?.so_du_edu_coin)
  if (Number.isFinite(soDu)) {
    auth.updateUser({ edu_coin: soDu })
  } else {
    try {
      await auth.fetchMe()
    } catch {
      // Giữ số dư local nếu /auth/me tạm thời lỗi.
    }
  }

  passwordVisible.value = false
  visible.value = false
  emit('paid')
  return true
}

async function confirmEduCoinPayment() {
  if (!canConfirmPassword.value) return
  await thanhToanEduCoin()
}

async function onSubmit() {
  if (!canSubmit.value) return

  if (hinhThuc.value === 'edu_coin') {
    matKhauThanhToan.value = ''
    passwordVisible.value = true
    return
  }

  const thongTin = buildTransferInfo(selectedBank.value)
  openTransfer({
    so_tien_thanh_toan: PHI_CHUYEN_KHOAN,
    ngan_hang_thanh_toan_id: selectedBank.value.id,
    hinh_thuc_thanh_toan: 'chuyen_khoan',
    ma_giao_dich: thongTin.ma_giao_dich,
    thong_tin_thanh_toan: thongTin,
  })
}

async function confirmTransferred() {
  if (!canConfirmTransfer.value || !pendingPay.value || !props.phien?.id) return

  confirmingTransfer.value = true
  const res = await request({
    url: API_PUBLIC.LICH_SU_TRAC_NGHIEM.THANH_TOAN(props.phien.id),
    body: {
      hinh_thuc_thanh_toan: 'chuyen_khoan',
      ngan_hang_thanh_toan_id: pendingPay.value.ngan_hang_thanh_toan_id,
      ma_giao_dich: pendingPay.value.ma_giao_dich
        || pendingPay.value.thong_tin_thanh_toan?.ma_giao_dich
        || pendingPay.value.thong_tin_thanh_toan?.noi_dung_chuyen_khoan,
    },
    successFallback: 'Đã ghi nhận yêu cầu thanh toán. Vui lòng chờ duyệt.',
    errorFallback: 'Không gửi được yêu cầu thanh toán.',
  })
  confirmingTransfer.value = false
  if (!res.ok) return

  pendingPay.value = {
    ...pendingPay.value,
    ...res.data,
  }
  startPollTimer()
}

watch(visible, (open) => {
  if (!open) return

  const pending = props.phien?.thanh_toan_dang_xu_ly
  if (pending?.id) {
    openTransfer({
      ...pending,
      ngan_hang_thanh_toan_id: pending.thong_tin_thanh_toan?.ngan_hang_thanh_toan_id,
    }, { poll: true })
    return
  }

  phase.value = 'select'
  hinhThuc.value = 'edu_coin'
  selectedBank.value = null
  fetchBanks()
})

onBeforeUnmount(stopWaitTimers)
</script>

<style scoped>
.pay-select {
  display: grid;
  gap: 1rem;
}

.pay-select__lead {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.pay-select__lead strong {
  color: var(--text);
  font-weight: 400;
}

.pay-methods {
  display: grid;
  gap: 0.75rem;
}

.pay-method {
  display: grid;
  grid-template-columns: auto 1fr;
  grid-template-rows: auto auto;
  column-gap: 0.75rem;
  row-gap: 0.1rem;
  padding: 0.9rem 1rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: #fff;
  color: inherit;
  text-align: left;
  cursor: pointer;
  font: inherit;
}

.pay-method :deep(.el-icon) {
  grid-row: 1 / span 3;
  color: var(--accent);
  align-self: center;
}

.pay-method__name {
  font-weight: 400;
}

.pay-method__amount,
.pay-method__hint {
  color: var(--muted);
  font-size: 0.9em;
}

.pay-method:hover,
.pay-method.is-selected {
  border-color: var(--accent);
}

.pay-method.is-selected {
  background: var(--accent-soft);
  box-shadow: 0 0 0 1px var(--accent);
}

.pay-banks {
  margin: 0;
  padding: 0;
  border: 0;
}

.pay-banks legend {
  padding: 0;
  margin: 0 0 0.65rem;
  font-weight: 400;
}

.bank-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.bank-card {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 8.5rem;
  height: 4.5rem;
  padding: 0.5rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: #fff;
  cursor: pointer;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.bank-card:hover,
.bank-card.is-selected {
  border-color: var(--accent);
}

.bank-card.is-selected {
  box-shadow: 0 0 0 1px var(--accent);
  background: var(--accent-soft);
}

.bank-card__logo {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 8px;
}

.bank-card__logo--empty {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  font-size: 0.85rem;
  font-weight: 400;
}

.nap-modal {
  display: grid;
  gap: 1.15rem;
}

.nap-modal__timer {
  display: grid;
  justify-items: center;
  align-content: start;
  gap: 0.35rem;
  padding: 1.1rem 1rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: linear-gradient(180deg, rgba(231, 244, 236, 0.7) 0%, #fff 72%);
  text-align: center;
}

.nap-modal__timer-label,
.nap-modal__timer-hint {
  margin: 0;
  color: var(--muted);
}

.nap-modal__timer-value {
  margin: 0;
  color: var(--accent);
  font-size: 2.4rem;
  font-weight: 400;
  letter-spacing: 0.06em;
  line-height: 1.1;
  font-variant-numeric: tabular-nums;
}

.nap-modal__lead,
.nap-modal__note {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.nap-modal__note {
  padding: 0.75rem 0.9rem;
  border-radius: 10px;
  background: var(--accent-soft);
}

.nap-modal__lead strong,
.nap-modal__note strong {
  color: var(--text);
  font-weight: 400;
}

.transfer-panel {
  display: grid;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: #fff;
}

.transfer-panel__qr {
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-image {
  width: 10.5rem;
  height: 10.5rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: #fff;
}

.qr-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 10.5rem;
  height: 10.5rem;
  padding: 0.5rem;
  color: var(--muted);
  text-align: center;
}

.transfer-panel__info h2 {
  margin: 0 0 0.75rem;
  font-size: 16px;
  font-weight: 400;
}

.transfer-panel__info dl {
  margin: 0;
  display: grid;
  gap: 0.65rem;
}

.transfer-panel__info dl > div {
  display: grid;
  gap: 0.15rem;
}

.transfer-panel__info dt {
  color: var(--muted);
}

.transfer-panel__info dd {
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.35rem;
  word-break: break-word;
}

.copy-btn {
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--accent);
  cursor: pointer;
  font: inherit;
  font-size: 14px;
}

.copy-btn:hover {
  text-decoration: underline;
}

.error-text a {
  color: var(--accent);
  font-weight: 400;
}

.pay-password__lead {
  margin: 0 0 1rem;
  color: var(--muted);
  line-height: 1.55;
}

.pay-password__lead strong {
  color: var(--text);
  font-weight: 400;
}

.pay-password__field {
  display: grid;
  gap: 0.55rem;
}

.pay-password__label {
  font-size: 0.95rem;
  color: var(--text);
}

.pay-password__otp {
  display: flex;
  width: 100%;
  height: 3rem;
  --el-input-otp-gap: 0.45rem;
  --el-input-otp-field-width: 100%;
}

.pay-password__otp :deep(.el-input-otp__input-field) {
  flex: 1 1 0;
  width: auto;
  min-width: 0;
  border-radius: 10px;
}

.pay-password__otp :deep(.el-input-otp__input) {
  font-size: 1.2rem;
  font-weight: 500;
  letter-spacing: 0;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (min-width: 560px) {
  .pay-methods {
    grid-template-columns: 1fr 1fr;
  }
}

@media (min-width: 720px) {
  .nap-modal {
    grid-template-columns: 11.5rem minmax(0, 1fr);
    align-items: start;
  }

  .transfer-panel {
    grid-template-columns: 10.5rem minmax(0, 1fr);
    align-items: start;
  }

  .transfer-panel__info dl > div {
    grid-template-columns: 8.5rem minmax(0, 1fr);
    gap: 0.5rem;
    align-items: start;
  }
}
</style>
