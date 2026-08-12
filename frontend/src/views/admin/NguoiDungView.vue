<template>
  <div class="admin-crud">
    <CustomCard shadow="never" class="mb filter-card">
      <CustomForm :model="filters" class="filter-form">
        <CustomRow :gutter="12" class="filter-bar--grid">
          <CustomCol>
            <CustomFormItem label="Từ khóa">
              <CustomInput
                v-model="filters.keyword"
                clearable
                class="filter-control"
                placeholder="Họ tên, email hoặc SĐT..."
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Giới tính">
              <CustomSelect
                v-model="filters.gioi_tinh"
                clearable
                class="filter-control"
                placeholder="Chọn giới tính"
              >
                <CustomOption
                  v-for="opt in gioiTinhOptions"
                  :key="opt"
                  :label="opt"
                  :value="opt"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Trạng thái">
              <CustomSelect
                v-model="filters.trang_thai"
                clearable
                class="filter-control"
                placeholder="Chọn trạng thái"
              >
                <CustomOption
                  v-for="opt in trangThaiOptions"
                  :key="opt.value"
                  :label="opt.label"
                  :value="opt.value"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label=" " class="filter-actions-item">
              <div class="filter-actions">
                <CustomButton
                  type="primary"
                  :icon="Search"
                  :loading="isRequestLoading"
                  @click="handleSearch"
                >
                  Lọc
                </CustomButton>
              </div>
            </CustomFormItem>
          </CustomCol>
        </CustomRow>
      </CustomForm>
    </CustomCard>

    <CustomCard shadow="never">
      <div class="list-toolbar">
        <h2>Danh sách người dùng</h2>
        <div class="list-actions">
          <CustomTooltip content="Xóa đã chọn" placement="top">
            <span class="action-wrap">
              <el-badge :value="selectedCount" :hidden="!selectedCount" :max="99">
                <CustomButton
                  type="danger"
                  :icon="Delete"
                  :disabled="!hasSelection || isRequestLoading"
                  @click="confirmBulkRemove"
                >
                  Xóa
                </CustomButton>
              </el-badge>
            </span>
          </CustomTooltip>

          <CustomTooltip content="Khóa các tài khoản đang hoạt động" placement="top">
            <span class="action-wrap">
              <el-badge :value="lockableCount" :hidden="!lockableCount" :max="99">
                <CustomButton
                  type="warning"
                  :icon="Lock"
                  :disabled="!lockableCount || isRequestLoading"
                  @click="confirmBulkStatus('ngung_hoat_dong')"
                >
                  Khóa
                </CustomButton>
              </el-badge>
            </span>
          </CustomTooltip>

          <CustomTooltip content="Mở các tài khoản đang ngừng hoạt động" placement="top">
            <span class="action-wrap">
              <el-badge :value="unlockableCount" :hidden="!unlockableCount" :max="99">
                <CustomButton
                  type="success"
                  :icon="Unlock"
                  :disabled="!unlockableCount || isRequestLoading"
                  @click="confirmBulkStatus('dang_hoat_dong')"
                >
                  Mở
                </CustomButton>
              </el-badge>
            </span>
          </CustomTooltip>
        </div>
      </div>

      <CustomTable
        ref="tableRef"
        :data="items"
        row-key="id"
        border
        stripe
        empty-text="Chưa có dữ liệu"
        @selection-change="onSelectionChange"
      >
        <CustomTableColumn type="selection" width="48" align="center" />
        <CustomTableColumn label="STT" width="70" align="center">
          <template #default="{ $index }">
            {{ pagination.start + $index + 1 }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="ho_ten" label="Họ tên" min-width="160" show-overflow-tooltip />
        <CustomTableColumn prop="email" label="Email" min-width="200" show-overflow-tooltip />
        <CustomTableColumn prop="so_dien_thoai" label="Số điện thoại" width="160" />
        <CustomTableColumn prop="gioi_tinh" label="Giới tính" width="110" align="center">
          <template #default="{ row }">
            {{ row.gioi_tinh || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="ngay_sinh" label="Ngày sinh" width="120" align="center">
          <template #default="{ row }">
            {{ formatDate(row.ngay_sinh) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="dan_toc" label="Dân tộc" width="120" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.dan_toc || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Trình độ" min-width="180" show-overflow-tooltip>
          <template #default="{ row }">
            {{ trinhDoLabel(row.trinh_do_hoc_van) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="edu_coin" label="Edu Coin" width="110" align="right">
          <template #default="{ row }">
            {{ formatNumber(row.edu_coin) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="xu_he_thong" label="Xu hệ thống" width="120" align="right">
          <template #default="{ row }">
            {{ formatNumber(row.xu_he_thong) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Trạng thái" width="200" align="center">
          <template #default="{ row }">
            <div class="status-cell">
              <el-switch
                :model-value="row.trang_thai === 'dang_hoat_dong'"
                :loading="statusLoadingId === row.id"
                :disabled="statusLoadingId === row.id"
                inline-prompt
                active-text="Mở"
                inactive-text="Khóa"
                @change="(val) => toggleStatus(row, val)"
              />
              <CustomTag
                :type="row.trang_thai === 'dang_hoat_dong' ? 'success' : 'info'"
                effect="light"
                size="small"
              >
                {{ trangThaiLabel(row.trang_thai) }}
              </CustomTag>
            </div>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Ngày đăng ký" width="200" align="center">
          <template #default="{ row }">
            {{ formatDateTime(row.created_at) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Thao tác" width="160" fixed="right" align="center">
          <template #default="{ row }">
            <div class="action-btns">
              <CustomTooltip content="Xem chi tiết" placement="top">
                <CustomButton link type="primary" :icon="View" @click="openDetail(row)" />
              </CustomTooltip>
              <CustomTooltip content="Nạp tiền" placement="top">
                <CustomButton link type="success" :icon="Wallet" @click="openNapTien(row)" />
              </CustomTooltip>
              <CustomTooltip content="Đổi mật khẩu" placement="top">
                <CustomButton link type="warning" :icon="Key" @click="openChangePassword(row)" />
              </CustomTooltip>
              <CustomTooltip content="Xóa" placement="top">
                <CustomButton link type="danger" :icon="Delete" @click="confirmRemove(row)" />
              </CustomTooltip>
            </div>
          </template>
        </CustomTableColumn>
      </CustomTable>

      <CustomPagination
        v-model:start="pagination.start"
        v-model:limit="pagination.limit"
        :total="pagination.total"
        :disabled="isRequestLoading"
        @change="fetchList"
      />
    </CustomCard>

    <CustomDialog v-model="detailVisible" title="Chi tiết người dùng" :width="820">
      <template v-if="detail">
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="Họ tên">{{ detail.ho_ten || '—' }}</el-descriptions-item>
          <el-descriptions-item label="Email">{{ detail.email || '—' }}</el-descriptions-item>
          <el-descriptions-item label="Số điện thoại">
            {{ detail.so_dien_thoai || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Giới tính">
            {{ detail.gioi_tinh || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Ngày sinh">
            {{ formatDate(detail.ngay_sinh) }}
          </el-descriptions-item>
          <el-descriptions-item label="Dân tộc">{{ detail.dan_toc || '—' }}</el-descriptions-item>
          <el-descriptions-item label="Tôn giáo">{{ detail.ton_giao || '—' }}</el-descriptions-item>
          <el-descriptions-item label="Trạng thái">
            {{ trangThaiLabel(detail.trang_thai) }}
          </el-descriptions-item>
          <el-descriptions-item label="Edu Coin">
            {{ formatNumber(detail.edu_coin) }}
          </el-descriptions-item>
          <el-descriptions-item label="Xu hệ thống">
            {{ formatNumber(detail.xu_he_thong) }}
          </el-descriptions-item>
          <el-descriptions-item label="Ngày tạo" :span="2">
            {{ formatDateTime(detail.created_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Trình độ học vấn</h3>
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="Trình độ">
            {{ trinhDoLabel(detail.trinh_do_hoc_van) }}
          </el-descriptions-item>
          <el-descriptions-item label="Trình độ khác">
            {{ detail.trinh_do_hoc_van?.trinh_do_khac || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Điểm học bạ">
            {{ detail.trinh_do_hoc_van?.diem_trung_binh_to_hop_mon?.diemHocBa ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Điểm thi THPT">
            {{ detail.trinh_do_hoc_van?.diem_trung_binh_to_hop_mon?.diemThiTHPT ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Chứng chỉ tiếng Anh" :span="2">
            {{ formatChungChi(detail.trinh_do_hoc_van?.chung_chi_tieng_anh) }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Sức khỏe thể chất</h3>
        <el-descriptions :column="3" border class="detail-block">
          <el-descriptions-item label="Chiều cao (cm)">
            {{ detail.suc_khoe_the_chat?.chieu_cao ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Cân nặng (kg)">
            {{ detail.suc_khoe_the_chat?.can_nang ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Bệnh lý">
            {{ detail.suc_khoe_the_chat?.benh_ly || '—' }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Khả năng tài chính</h3>
        <el-descriptions :column="1" border class="detail-block">
          <el-descriptions-item label="Chi trả một năm học">
            {{ formatMoney(detail.kha_nang_tai_chinh?.chi_tra_mot_nam_hoc) }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Vị trí địa lý</h3>
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="Khu vực muốn theo học">
            {{ khuVucLabel(detail.vi_tri_dia_ly?.khu_vuc_muon_theo_hoc) }}
          </el-descriptions-item>
          <el-descriptions-item label="Tỉnh/TP muốn theo học">
            {{ detail.vi_tri_dia_ly?.tinh_thanh_muon_theo_hoc || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Tỉnh/TP đang sống" :span="2">
            {{ detail.vi_tri_dia_ly?.tinh_thanh_dang_song || '—' }}
          </el-descriptions-item>
        </el-descriptions>
      </template>

      <template #footer>
        <CustomButton type="primary" @click="detailVisible = false">Đóng</CustomButton>
      </template>
    </CustomDialog>

    <CustomDialog
      v-model="passwordVisible"
      title="Đổi mật khẩu"
      :width="680"
      @closed="resetPasswordForm"
    >
      <p v-if="passwordTarget" class="password-target">
        Tài khoản: <strong>{{ passwordTarget.ho_ten }}</strong>
        <span class="muted">({{ passwordTarget.email }})</span>
      </p>

      <CustomForm ref="passwordFormRef" :model="passwordForm" :rules="passwordFormRules">
        <CustomFormItem label="Mật khẩu mới" prop="password">
          <CustomInput
            v-model="passwordForm.password"
            type="password"
            show-password
            autocomplete="new-password"
            placeholder="Nhập mật khẩu mới"
          />
        </CustomFormItem>
        <CustomFormItem label="Xác nhận mật khẩu" prop="password_confirmation">
          <CustomInput
            v-model="passwordForm.password_confirmation"
            type="password"
            show-password
            autocomplete="new-password"
            placeholder="Nhập lại mật khẩu mới"
          />
        </CustomFormItem>
      </CustomForm>

      <template #footer>
        <CustomButton @click="passwordVisible = false">Hủy</CustomButton>
        <CustomButton type="primary" :loading="isRequestLoading" @click="submitChangePassword">
          Đổi mật khẩu
        </CustomButton>
      </template>
    </CustomDialog>

    <NapTienModal v-model="napTienVisible" :user="napTienTarget" />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { ElMessageBox } from 'element-plus'
import { Delete, Key, Lock, Search, Unlock, View, Wallet } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import { API_NGUOI_DUNG } from '@/constants/constant_api'
import NapTienModal from '@/components/admin/NapTienModal.vue'
import {
  CustomButton,
  CustomCard,
  CustomCol,
  CustomDialog,
  CustomForm,
  CustomFormItem,
  CustomInput,
  CustomOption,
  CustomPagination,
  CustomRow,
  CustomSelect,
  CustomTable,
  CustomTableColumn,
  CustomTag,
  CustomTooltip,
} from '@/components/element'

const gioiTinhOptions = ['Nam', 'Nữ', 'Khác']

const trangThaiOptions = [
  { value: 'dang_hoat_dong', label: 'Đang hoạt động' },
  { value: 'ngung_hoat_dong', label: 'Ngừng hoạt động' },
]

const trinhDoOptions = [
  { value: 'tot_nghiep_thpt', label: 'Tốt nghiệp THPT' },
  { value: 'tot_nghiep_thcs', label: 'Tốt nghiệp THCS' },
  { value: 'dang_hoc_thpt', label: 'Đang học THPT' },
  { value: 'trung_cap', label: 'Trung cấp' },
  { value: 'cao_dang', label: 'Cao đẳng' },
  { value: 'dai_hoc', label: 'Đại học' },
  { value: 'khac', label: 'Khác' },
]

const khuVucOptions = [
  { value: 'bac', label: 'Miền Bắc' },
  { value: 'trung', label: 'Miền Trung' },
  { value: 'nam', label: 'Miền Nam' },
]

const tableRef = ref(null)
const items = ref([])
const selectedRows = ref([])
const statusLoadingId = ref(null)
const detailVisible = ref(false)
const detail = ref(null)
const passwordVisible = ref(false)
const passwordTarget = ref(null)
const passwordFormRef = ref(null)
const napTienVisible = ref(false)
const napTienTarget = ref(null)

const passwordForm = reactive({
  password: '',
  password_confirmation: '',
})

const validatePasswordStrength = (_rule, value, callback) => {
  const pwd = value || ''
  if (!pwd) {
    callback(new Error('Vui lòng nhập mật khẩu mới'))
    return
  }

  const checks = {
    minLength: pwd.length >= 8,
    lower: /[a-z]/.test(pwd),
    upper: /[A-Z]/.test(pwd),
    number: /\d/.test(pwd),
    special: /[^A-Za-z0-9]/.test(pwd),
  }

  if (!checks.minLength) {
    callback(new Error('Mật khẩu phải có tối thiểu 8 ký tự'))
    return
  }
  if (!checks.lower) {
    callback(new Error('Mật khẩu phải có ít nhất một chữ thường'))
    return
  }
  if (!checks.upper) {
    callback(new Error('Mật khẩu phải có ít nhất một chữ hoa'))
    return
  }
  if (!checks.number) {
    callback(new Error('Mật khẩu phải có ít nhất một chữ số'))
    return
  }
  if (!checks.special) {
    callback(new Error('Mật khẩu phải có ít nhất một ký tự đặc biệt'))
    return
  }

  callback()
}

const validatePasswordConfirm = (_rule, value, callback) => {
  if (!value) {
    callback(new Error('Vui lòng xác nhận mật khẩu'))
    return
  }
  if (value !== passwordForm.password) {
    callback(new Error('Mật khẩu xác nhận không khớp'))
    return
  }
  callback()
}

const passwordFormRules = {
  password: [{ required: true, validator: validatePasswordStrength, trigger: ['blur', 'change'] }],
  password_confirmation: [
    { required: true, validator: validatePasswordConfirm, trigger: ['blur', 'change'] },
  ],
}

const filters = reactive({
  keyword: '',
  gioi_tinh: '',
  trang_thai: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

const selectedCount = computed(() => selectedRows.value.length)
const hasSelection = computed(() => selectedCount.value > 0)
const selectedIds = computed(() => selectedRows.value.map((row) => row.id))

const lockableRows = computed(() =>
  selectedRows.value.filter((row) => row.trang_thai === 'dang_hoat_dong'),
)
const unlockableRows = computed(() =>
  selectedRows.value.filter((row) => row.trang_thai === 'ngung_hoat_dong'),
)
const lockableCount = computed(() => lockableRows.value.length)
const unlockableCount = computed(() => unlockableRows.value.length)

function onSelectionChange(rows) {
  selectedRows.value = rows
}

function clearSelection() {
  selectedRows.value = []
  tableRef.value?.clearSelection?.()
}

function handleSearch() {
  pagination.start = 0
  fetchList()
}

function trangThaiLabel(value) {
  return trangThaiOptions.find((o) => o.value === value)?.label || value || '—'
}

function formatDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString('vi-VN')
}

function formatDateTime(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('vi-VN')
}

function formatMoney(value) {
  if (value === null || value === undefined || value === '') return '—'
  const num = Number(value)
  if (Number.isNaN(num)) return String(value)
  return `${num.toLocaleString('vi-VN')} đ`
}

function formatNumber(value) {
  const num = Number(value ?? 0)
  if (Number.isNaN(num)) return '0'
  return num.toLocaleString('vi-VN')
}

function trinhDoLabel(trinhDo) {
  const code = trinhDo?.trinh_do_hoc_van
  if (!code) return '—'
  return trinhDoOptions.find((o) => o.value === code)?.label || code
}

function khuVucLabel(value) {
  if (!value) return '—'
  return khuVucOptions.find((o) => o.value === value)?.label || value
}

function formatChungChi(list) {
  if (!Array.isArray(list) || !list.length) return '—'
  const parts = list
    .map((item) => {
      const ten = item?.ten_chung_chi?.trim()
      const diem = item?.diem_chung_chi?.trim()
      if (!ten && !diem) return null
      return diem ? `${ten || '—'} (${diem})` : ten
    })
    .filter(Boolean)
  return parts.length ? parts.join(', ') : '—'
}

async function fetchList() {
  const q = filters.keyword.trim()
  const gioiTinh = filters.gioi_tinh || ''
  const trangThai = filters.trang_thai || ''
  const params = {
    ...(q ? { q } : {}),
    ...(gioiTinh ? { gioi_tinh: gioiTinh } : {}),
    ...(trangThai ? { trang_thai: trangThai } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_NGUOI_DUNG.LIST, params })

  if (!res.ok) {
    items.value = []
    pagination.total = 0
    clearSelection()
    return
  }

  items.value = res.data ?? []
  pagination.total = res.total ?? 0
  clearSelection()
}

async function toggleStatus(row, enabled) {
  const nextStatus = enabled ? 'dang_hoat_dong' : 'ngung_hoat_dong'
  if (row.trang_thai === nextStatus) return

  const prevStatus = row.trang_thai
  row.trang_thai = nextStatus
  statusLoadingId.value = row.id

  const res = await request({
    url: API_NGUOI_DUNG.UPDATE(row.id),
    body: { trang_thai: nextStatus },
    loading: false,
  })

  statusLoadingId.value = null

  if (!res.ok) {
    row.trang_thai = prevStatus
    return
  }

  if (res.data?.trang_thai) {
    row.trang_thai = res.data.trang_thai
  }
}

async function openDetail(row) {
  const res = await request({ url: API_NGUOI_DUNG.SHOW(row.id) })
  if (!res.ok) return

  detail.value = res.data
  detailVisible.value = true
}

function resetPasswordForm() {
  passwordForm.password = ''
  passwordForm.password_confirmation = ''
  passwordTarget.value = null
  passwordFormRef.value?.clearValidate?.()
}

function openChangePassword(row) {
  resetPasswordForm()
  passwordTarget.value = row
  passwordVisible.value = true
}

function openNapTien(row) {
  napTienTarget.value = row
  napTienVisible.value = true
}

async function submitChangePassword() {
  const valid = await passwordFormRef.value?.validate().catch(() => false)
  if (!valid || !passwordTarget.value) return

  const res = await request({
    url: API_NGUOI_DUNG.CHANGE_PASSWORD(passwordTarget.value.id),
    body: {
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation,
    },
  })
  if (!res.ok) return

  passwordVisible.value = false
}

async function confirmRemove(row) {
  try {
    await ElMessageBox.confirm(
      `Xóa người dùng «${row.ho_ten}» (${row.email})? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_NGUOI_DUNG.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} người dùng đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_NGUOI_DUNG.BULK_DELETE,
    body: { ids: selectedIds.value },
  })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkStatus(trangThai) {
  const targetRows = trangThai === 'ngung_hoat_dong' ? lockableRows.value : unlockableRows.value
  if (!targetRows.length) return

  const actionLabel = trangThai === 'ngung_hoat_dong' ? 'khóa' : 'mở'
  const statusLabel = trangThaiLabel(trangThai)
  const ids = targetRows.map((row) => row.id)

  try {
    await ElMessageBox.confirm(
      `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} ${ids.length} người dùng (trạng thái: ${statusLabel})?`,
      `Xác nhận ${actionLabel}`,
      { type: 'warning', confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_NGUOI_DUNG.BULK_STATUS,
    body: { ids, trang_thai: trangThai },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(fetchList)
</script>

<style scoped>
.detail-title {
  margin: 20px 0 10px;
  font-size: 15px;
  font-weight: 600;
}

.detail-block {
  width: 100%;
}

.detail-block :deep(.el-descriptions__label) {
  width: 160px;
  font-weight: 500;
}

.password-target {
  margin: 0 0 16px;
  font-size: 14px;
}

.password-target .muted {
  margin-left: 4px;
  color: var(--el-text-color-secondary);
}
</style>
