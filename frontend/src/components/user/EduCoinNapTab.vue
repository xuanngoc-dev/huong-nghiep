<template>
  <div class="edu-nap-tab">
  <form class="edu-nap form" @submit.prevent="onSubmit">
    <div class="edu-nap__amount">
      <label>
        Số Edu Coin cần nạp
        <input
          :value="soLuongDisplay"
          inputmode="numeric"
          autocomplete="off"
          placeholder="Từ 1 đến 10.000"
          @input="onSoLuongInput"
        />
      </label>
      <label>
        Số tiền tương ứng
        <div class="edu-nap__input-wrap">
          <input
            :value="soTienDisplay"
            readonly
            tabindex="-1"
            placeholder="Tự tính theo số coin"
          />
          <span class="edu-nap__suffix">đ</span>
        </div>
      </label>
    </div>
    <div class="edu-nap__presets" role="group" aria-label="Chọn nhanh số Edu Coin">
      <button
        v-for="amount in quickAmounts"
        :key="amount"
        type="button"
        class="edu-nap__preset"
        :class="{ 'is-active': soLuong === amount }"
        @click="setSoLuong(amount)"
      >
        {{ formatNumber(amount) }}
      </button>
    </div>
    <p class="edu-nap__rate">
      Tỷ giá: 1 Edu Coin = {{ formatMoney(tyGia) }}đ. Mỗi lần nạp từ 1 đến 10.000 coin.
    </p>

    <fieldset
      class="edu-nap__banks"
      :class="{ 'is-disabled': !canSelectBank }"
      :disabled="!canSelectBank"
    >
      <legend>Chọn ngân hàng nhận chuyển khoản</legend>
      <p v-if="banksLoading" class="muted">Đang tải danh sách ngân hàng...</p>
      <p v-else-if="!banks.length" class="muted">Chưa có ngân hàng đang sử dụng.</p>
      <p v-else-if="!canSelectBank" class="muted">Nhập số Edu Coin (1–10.000) để chọn ngân hàng.</p>
      <div v-if="!banksLoading && banks.length" class="bank-list">
        <button
          v-for="bank in banks"
          :key="bank.id"
          type="button"
          class="bank-card"
          :class="{ 'is-selected': selectedBank?.id === bank.id }"
          :title="bank.ten_viet_tat || bank.ten_ngan_hang"
          @click="selectBank(bank)"
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

    <label>
      Ghi chú
      <textarea
        v-model="ghiChu"
        rows="2"
        maxlength="255"
        placeholder="Ghi chú thêm (không bắt buộc)"
      />
    </label>

    <p v-if="localError" class="error-text">{{ localError }}</p>

    <button class="btn" type="submit" :disabled="!canSubmit">
      {{ submitting ? 'Đang gửi...' : 'Gửi yêu cầu nạp' }}
    </button>
  </form>

  <CustomDialog
    v-model="modalVisible"
    title="Yêu cầu chuyển khoản"
    :width="960"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    class="edu-nap-dialog"
    @closed="onModalClosed"
  >
    <div v-if="pendingNap" class="nap-modal">
      <aside class="nap-modal__timer" aria-live="polite">
        <p class="nap-modal__timer-label">Thời gian còn lại</p>
        <p class="nap-modal__timer-value">{{ countdownLabel }}</p>
        <p class="nap-modal__timer-hint">
          Vui lòng hoàn tất chuyển khoản trước khi hết giờ.
        </p>
      </aside>

      <div class="nap-modal__body">
        <p class="nap-modal__lead">
          Hệ thống đã ghi nhận yêu cầu nạp
          <strong>{{ formatNumber(pendingNap.so_luong_edu_coin) }} Edu Coin</strong>.
          Vui lòng chuyển khoản theo thông tin bên dưới rồi chờ duyệt.
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
                  <strong>{{ formatMoney(pendingNap.so_tien_nap) }}đ</strong>
                  <button
                    type="button"
                    class="copy-btn"
                    @click="copyText(String(pendingNap.so_tien_nap))"
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
          Sai nội dung có thể làm yêu cầu nạp bị chậm hoặc không được ghi nhận.
        </p>
      </div>
    </div>

    <template #footer>
      <button class="btn btn-outline" type="button" @click="closeModal">Đóng</button>
    </template>
  </CustomDialog>

  <CustomDialog
    v-model="successVisible"
    title="Nạp thành công"
    :width="440"
    :close-on-click-modal="false"
    @closed="onSuccessClosed"
  >
    <div class="nap-success">
      <p class="nap-success__lead">Đã nạp thành công. Số dư Edu Coin đã được cập nhật.</p>
      <p v-if="successSoLuong" class="nap-success__detail">
        Bạn vừa nhận <strong>{{ formatNumber(successSoLuong) }} Edu Coin</strong>.
        Số dư hiện tại: <strong>{{ formatNumber(soDuEduCoin) }}</strong>.
      </p>
    </div>
    <template #footer>
      <button class="btn btn-outline" type="button" @click="closeSuccess">Đóng</button>
      <button class="btn" type="button" @click="goToHistory">
        Lịch sử biến động
      </button>
    </template>
  </CustomDialog>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { request } from '@/api'
import { CustomDialog } from '@/components/element'
import { API_PUBLIC } from '@/constants/constant_api'
import { useAuthStore } from '@/stores/auth'
import { taoMaNap } from '@/utils/maGiaoDich'

const emit = defineEmits(['go-lich-su'])

const DEFAULT_TY_GIA = 1000
const DEFAULT_SO_LUONG = 100
const SO_LUONG_MIN = 1
const SO_LUONG_MAX = 10000
const QUICK_AMOUNTS = [10, 20, 50, 100, 200, 500]
/** Thời gian chờ chuyển khoản trên modal (phút). Đổi số này để tăng/giảm countdown. */
const TRANSFER_WAIT_MINUTES = 5
const TRANSFER_WAIT_SECONDS = TRANSFER_WAIT_MINUTES * 60
const STATUS_POLL_MS = 5000

const auth = useAuthStore()
const banks = ref([])
const banksLoading = ref(false)
const selectedBank = ref(null)
const soLuongDisplay = ref(String(DEFAULT_SO_LUONG))
const quickAmounts = QUICK_AMOUNTS
const ghiChu = ref('')
const submitting = ref(false)
const localError = ref('')
const tyGia = ref(DEFAULT_TY_GIA)
const modalVisible = ref(false)
const pendingNap = ref(null)
const remainingSeconds = ref(TRANSFER_WAIT_SECONDS)
const waitResolved = ref(false)
const successVisible = ref(false)
const successSoLuong = ref(0)

const soDuEduCoin = computed(() => Number(auth.user?.edu_coin) || 0)

let countdownTimer = null
let pollTimer = null

const soLuong = computed(() => {
  const digits = String(soLuongDisplay.value).replace(/\D/g, '')
  if (!digits) return 0
  const num = Number(digits)
  return Number.isFinite(num) ? num : 0
})

const canSelectBank = computed(
  () => soLuong.value >= SO_LUONG_MIN && soLuong.value <= SO_LUONG_MAX,
)

const soTienNap = computed(() => (canSelectBank.value ? soLuong.value * tyGia.value : 0))

const soTienDisplay = computed(() => (canSelectBank.value ? formatMoney(soTienNap.value) : ''))

const canSubmit = computed(() =>
  Boolean(canSelectBank.value && selectedBank.value && !submitting.value && !modalVisible.value),
)

const pendingThongTin = computed(() => pendingNap.value?.thong_tin_thanh_toan || {})

const noiDungChuyenKhoan = computed(() =>
  pendingNap.value?.ma_giao_dich
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
  const amount = Number(pendingNap.value?.so_tien_nap) || 0
  const bankCode = String(info.ten_viet_tat || '').trim().replace(/\s+/g, '')
  const accountNo = String(info.so_tai_khoan || '').replace(/\s+/g, '')
  if (!bankCode || !accountNo || amount <= 0) return ''

  const params = new URLSearchParams({
    amount: String(amount),
    addInfo: noiDungChuyenKhoan.value,
    accountName: info.chu_tai_khoan || '',
  })

  return `https://img.vietqr.io/image/${encodeURIComponent(bankCode)}-${encodeURIComponent(accountNo)}-compact.png?${params.toString()}`
})

function formatGrouped(value) {
  if (value === '' || value == null) return ''
  const digits = String(value).replace(/\D/g, '')
  if (!digits) return ''
  return Number(digits).toLocaleString('vi-VN')
}

function formatMoney(value) {
  return formatGrouped(value) || '0'
}

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}

function setSoLuong(value) {
  const num = Number(value)
  if (!Number.isFinite(num) || num < 1) {
    soLuongDisplay.value = ''
    return
  }
  soLuongDisplay.value = formatGrouped(Math.min(num, SO_LUONG_MAX))
  localError.value = ''
}

function onSoLuongInput(event) {
  const digits = String(event.target.value).replace(/\D/g, '')
  if (!digits) {
    soLuongDisplay.value = ''
    return
  }
  setSoLuong(digits)
}

function selectBank(bank) {
  if (!canSelectBank.value) return
  selectedBank.value = bank
  localError.value = ''
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
    url: API_PUBLIC.NGAN_HANG_THANH_TOAN.LIST,
    loading: false,
    silentSuccess: true,
    errorFallback: 'Không tải được danh sách ngân hàng.',
  })
  banksLoading.value = false

  if (!res.ok) {
    banks.value = []
    return
  }

  banks.value = res.data ?? []
  const nextRate = Number(res.body?.ty_gia)
  if (Number.isFinite(nextRate) && nextRate > 0) {
    tyGia.value = nextRate
  }
}

function resetForm() {
  soLuongDisplay.value = formatGrouped(DEFAULT_SO_LUONG)
  ghiChu.value = ''
  selectedBank.value = null
  localError.value = ''
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
  modalVisible.value = false
}

function closeSuccess() {
  successVisible.value = false
}

function onSuccessClosed() {
  successSoLuong.value = 0
}

function goToHistory() {
  successVisible.value = false
  emit('go-lich-su')
}

function onModalClosed() {
  stopWaitTimers()
  pendingNap.value = null
  remainingSeconds.value = TRANSFER_WAIT_SECONDS
  waitResolved.value = false
}

async function handleStatusChange(status) {
  if (waitResolved.value) return true
  if (status === 'da_duyet') {
    waitResolved.value = true
    stopWaitTimers()
    successSoLuong.value = Number(pendingNap.value?.so_luong_edu_coin) || 0
    modalVisible.value = false
    try {
      await auth.fetchMe()
    } catch {
      // Giữ số dư local nếu /auth/me tạm thời lỗi.
    }
    await nextTick()
    successVisible.value = true
    return true
  }
  if (status === 'huy_duyet') {
    waitResolved.value = true
    stopWaitTimers()
    modalVisible.value = false
    ElMessage.warning('Yêu cầu nạp đã bị từ chối. Vui lòng nạp lại hoặc liên hệ CSKH.')
    return true
  }
  return false
}

async function pollPendingStatus() {
  const id = pendingNap.value?.id
  if (!id || waitResolved.value) return false

  const res = await request({
    url: API_PUBLIC.NAP_EDU_COIN.SHOW(id),
    loading: false,
    silent: true,
  })
  if (!res.ok) return false

  pendingNap.value = { ...pendingNap.value, ...res.data }
  return handleStatusChange(res.data?.trang_thai)
}

async function onCountdownExpired() {
  if (waitResolved.value) return
  stopWaitTimers()

  const changed = await pollPendingStatus()
  if (changed || waitResolved.value) return

  waitResolved.value = true
  modalVisible.value = false

  try {
    await ElMessageBox.confirm(
      'Vui lòng nạp lại. Nếu đã nạp mà không thành công thì liên hệ CSKH để được hỗ trợ.',
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

async function onSubmit() {
  localError.value = ''
  if (!canSelectBank.value) {
    localError.value = 'Số Edu Coin phải từ 1 đến 10.000.'
    return
  }
  if (!selectedBank.value) {
    localError.value = 'Vui lòng chọn ngân hàng nhận chuyển khoản.'
    return
  }

  submitting.value = true
  const res = await request({
    url: API_PUBLIC.NAP_EDU_COIN.STORE,
    body: {
      so_luong_edu_coin: soLuong.value,
      ngan_hang_thanh_toan_id: selectedBank.value.id,
      ma_giao_dich: taoMaNap(),
      ghi_chu: ghiChu.value.trim() || null,
    },
    successFallback: 'Đã ghi nhận yêu cầu nạp. Vui lòng chuyển khoản và chờ duyệt.',
    errorFallback: 'Không gửi được yêu cầu nạp.',
  })
  submitting.value = false

  if (!res.ok) return

  pendingNap.value = res.data
  resetForm()
  modalVisible.value = true
  startWaitTimers()
  startPollTimer()
}

watch(canSelectBank, (ok) => {
  if (!ok) selectedBank.value = null
})

onMounted(fetchBanks)

onBeforeUnmount(() => {
  stopWaitTimers()
})
</script>

<style scoped>
.edu-nap {
  display: grid;
  gap: 1rem;
}

.edu-nap__amount {
  display: grid;
  gap: 0.75rem;
}

.edu-nap__presets {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.edu-nap__preset {
  min-width: 3.6rem;
  padding: 0.45rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 999px;
  background: #fff;
  color: var(--muted);
  cursor: pointer;
  font: inherit;
  font-size: 16px;
  font-weight: 300;
  line-height: 1.2;
}

.edu-nap__preset:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.edu-nap__preset.is-active {
  border-color: var(--accent);
  background: var(--accent-soft);
  color: var(--accent);
  font-weight: 400;
}

.edu-nap__input-wrap {
  position: relative;
}

.edu-nap__input-wrap input {
  padding-right: 2.25rem;
}

.edu-nap__suffix {
  position: absolute;
  top: 50%;
  right: 0.9rem;
  color: var(--muted);
  transform: translateY(-50%);
  pointer-events: none;
}

.edu-nap__rate {
  margin: 0;
  color: var(--muted);
}

.edu-nap__banks {
  margin: 0;
  padding: 0;
  border: 0;
}

.edu-nap__banks legend {
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

.edu-nap__banks.is-disabled {
  opacity: 0.55;
}

.edu-nap__banks.is-disabled .bank-card {
  cursor: not-allowed;
}

.edu-nap__banks.is-disabled .bank-card:hover {
  border-color: var(--border);
}

.bank-card:hover {
  border-color: var(--accent);
}

.bank-card.is-selected {
  border-color: var(--accent);
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

.edu-nap input,
.edu-nap textarea {
  width: 100%;
  padding: 0.75rem 0.9rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: #fff;
  box-sizing: border-box;
}

.edu-nap input[readonly] {
  background: #f7faf8;
  color: var(--text);
}

.edu-nap textarea {
  resize: vertical;
}

.edu-nap .btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

.nap-success {
  display: grid;
  gap: 0.65rem;
}

.nap-success__lead,
.nap-success__detail {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.nap-success__detail strong {
  color: var(--text);
  font-weight: 400;
}

@media (min-width: 720px) {
  .edu-nap__amount {
    grid-template-columns: minmax(0, 1fr) minmax(12rem, 16rem);
    align-items: end;
  }

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
