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
                placeholder="Tên ngân hàng, viết tắt, STK, chủ TK..."
                @keyup.enter="handleSearch"
              />
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
        <h2>Quản lý ngân hàng thanh toán</h2>
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

          <CustomTooltip content="Khóa các ngân hàng đang sử dụng" placement="top">
            <span class="action-wrap">
              <el-badge :value="lockableCount" :hidden="!lockableCount" :max="99">
                <CustomButton
                  type="warning"
                  :icon="Lock"
                  :disabled="!lockableCount || isRequestLoading"
                  @click="confirmBulkStatus('ngung_su_dung')"
                >
                  Khóa
                </CustomButton>
              </el-badge>
            </span>
          </CustomTooltip>

          <CustomTooltip content="Mở các ngân hàng đang ngừng sử dụng" placement="top">
            <span class="action-wrap">
              <el-badge :value="unlockableCount" :hidden="!unlockableCount" :max="99">
                <CustomButton
                  type="success"
                  :icon="Unlock"
                  :disabled="!unlockableCount || isRequestLoading"
                  @click="confirmBulkStatus('dang_su_dung')"
                >
                  Mở
                </CustomButton>
              </el-badge>
            </span>
          </CustomTooltip>

          <CustomTooltip content="Thêm ngân hàng thanh toán" placement="top">
            <span class="action-wrap">
              <el-badge :value="0" :hidden="true">
                <CustomButton type="primary" :icon="Plus" @click="openCreate">
                  Thêm mới
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
        <CustomTableColumn label="Logo" width="100" align="center">
          <template #default="{ row }">
            <el-image v-if="row.hinh_anh_logo" :src="row.hinh_anh_logo" :preview-src-list="[row.hinh_anh_logo]"
              fit="contain" class="logo-thumb" preview-teleported />
            <span v-else class="text-muted">—</span>
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="ten_ngan_hang" label="Tên ngân hàng" min-width="180" />
        <CustomTableColumn prop="ten_viet_tat" label="Viết tắt" width="110" />
       
        <CustomTableColumn prop="so_tai_khoan" label="Số tài khoản" width="160" />
        <CustomTableColumn prop="chu_tai_khoan" label="Chủ tài khoản" min-width="160" />
        <CustomTableColumn
          prop="chi_nhanh"
          label="Chi nhánh"
          min-width="160"
          show-overflow-tooltip
        />
        <CustomTableColumn label="Trạng thái" width="200" align="center">
          <template #default="{ row }">
            <div class="status-cell">
              <el-switch
                :model-value="row.trang_thai === 'dang_su_dung'"
                :loading="statusLoadingId === row.id"
                :disabled="statusLoadingId === row.id"
                inline-prompt
                active-text="Mở"
                inactive-text="Khóa"
                @change="(val) => toggleStatus(row, val)"
              />
              <CustomTag
                :type="row.trang_thai === 'dang_su_dung' ? 'success' : 'info'"
                effect="light"
                size="small"
              >
                {{ trangThaiLabel(row.trang_thai) }}
              </CustomTag>
            </div>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Thao tác" width="100" fixed="right" align="center">
          <template #default="{ row }">
            <div class="action-btns">
              <CustomTooltip content="Sửa" placement="top">
                <CustomButton link type="primary" :icon="Edit" @click="openEdit(row)" />
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

    <CustomDialog
      v-model="dialogVisible"
      :title="isEdit ? 'Sửa ngân hàng thanh toán' : 'Thêm ngân hàng thanh toán'"
      :width="720"
    >
      <div class="logo-preview">
        <el-image
          v-if="logoPreviewUrl"
          :src="logoPreviewUrl"
          :preview-src-list="[logoPreviewUrl]"
          fit="contain"
          class="logo-preview__img"
          preview-teleported
        >
          <template #error>
            <div class="logo-preview__placeholder">Không tải được ảnh</div>
          </template>
        </el-image>
        <div v-else class="logo-preview__img logo-preview__placeholder">Logo</div>
      </div>

      <CustomForm ref="formRef" :model="form" :rules="rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="24" :sm="16" :md="16" :lg="16" :xl="16">
            <CustomFormItem label="Tên ngân hàng" prop="ten_ngan_hang">
              <CustomInput
                v-model="form.ten_ngan_hang"
                placeholder="Ví dụ: Ngân hàng TMCP Ngoại thương Việt Nam"
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="8" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Tên viết tắt" prop="ten_viet_tat">
              <CustomInput v-model="form.ten_viet_tat" placeholder="Ví dụ: Vietcombank" maxlength="50" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :span="24">
            <CustomFormItem label="Logo (URL)" prop="hinh_anh_logo">
              <CustomInput
                v-model="form.hinh_anh_logo"
                placeholder="Đường dẫn / URL logo"
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Số tài khoản" prop="so_tai_khoan">
              <CustomInput v-model="form.so_tai_khoan" placeholder="Nhập số tài khoản" maxlength="50" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Chủ tài khoản" prop="chu_tai_khoan">
              <CustomInput
                v-model="form.chu_tai_khoan"
                placeholder="Nhập tên chủ tài khoản"
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="16" :md="16" :lg="16" :xl="16">
            <CustomFormItem label="Chi nhánh" prop="chi_nhanh">
              <CustomInput
                v-model="form.chi_nhanh"
                placeholder="Chi nhánh (không bắt buộc)"
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="8" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Trạng thái" prop="trang_thai">
              <CustomSelect v-model="form.trang_thai" placeholder="Chọn trạng thái" style="width: 100%">
                <CustomOption
                  v-for="opt in trangThaiOptions"
                  :key="opt.value"
                  :label="opt.label"
                  :value="opt.value"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :span="24">
            <CustomFormItem label="Ghi chú" prop="ghi_chu">
              <CustomInput
                v-model="form.ghi_chu"
                type="textarea"
                :rows="3"
                placeholder="Ghi chú (không bắt buộc)"
                maxlength="2000"
                show-word-limit
              />
            </CustomFormItem>
          </CustomCol>
        </CustomRow>
      </CustomForm>

      <template #footer>
        <CustomButton @click="dialogVisible = false">Hủy</CustomButton>
        <CustomButton type="primary" :loading="isRequestLoading" @click="submitForm">
          {{ isEdit ? 'Cập nhật' : 'Tạo mới' }}
        </CustomButton>
      </template>
    </CustomDialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { ElMessageBox } from 'element-plus'
import { Delete, Edit, Lock, Plus, Search, Unlock } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import { API_NGAN_HANG_THANH_TOAN } from '@/constants/constant_api'
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

const trangThaiOptions = [
  { value: 'dang_su_dung', label: 'Đang sử dụng' },
  { value: 'ngung_su_dung', label: 'Ngừng sử dụng' },
]

const tableRef = ref(null)
const items = ref([])
const selectedRows = ref([])
const statusLoadingId = ref(null)
const dialogVisible = ref(false)
const editingId = ref(null)
const formRef = ref(null)

const filters = reactive({
  keyword: '',
  trang_thai: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

const form = reactive({
  ten_ngan_hang: '',
  ten_viet_tat: '',
  hinh_anh_logo: '',
  so_tai_khoan: '',
  chu_tai_khoan: '',
  chi_nhanh: '',
  trang_thai: 'dang_su_dung',
  ghi_chu: '',
})

const rules = {
  ten_ngan_hang: [
    { required: true, message: 'Vui lòng nhập tên ngân hàng', trigger: 'blur' },
    { max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' },
  ],
  ten_viet_tat: [{ max: 50, message: 'Tối đa 50 ký tự', trigger: 'blur' }],
  hinh_anh_logo: [{ max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' }],
  so_tai_khoan: [
    { required: true, message: 'Vui lòng nhập số tài khoản', trigger: 'blur' },
    { max: 50, message: 'Tối đa 50 ký tự', trigger: 'blur' },
  ],
  chu_tai_khoan: [
    { required: true, message: 'Vui lòng nhập chủ tài khoản', trigger: 'blur' },
    { max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' },
  ],
  chi_nhanh: [{ max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' }],
  trang_thai: [{ required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }],
  ghi_chu: [{ max: 2000, message: 'Tối đa 2000 ký tự', trigger: 'blur' }],
}

const isEdit = computed(() => editingId.value !== null)
const logoPreviewUrl = computed(() => form.hinh_anh_logo?.trim() || '')
const selectedCount = computed(() => selectedRows.value.length)
const hasSelection = computed(() => selectedCount.value > 0)
const selectedIds = computed(() => selectedRows.value.map((row) => row.id))

const lockableRows = computed(() =>
  selectedRows.value.filter((row) => row.trang_thai === 'dang_su_dung'),
)
const unlockableRows = computed(() =>
  selectedRows.value.filter((row) => row.trang_thai === 'ngung_su_dung'),
)
const lockableCount = computed(() => lockableRows.value.length)
const unlockableCount = computed(() => unlockableRows.value.length)

function trangThaiLabel(value) {
  return trangThaiOptions.find((o) => o.value === value)?.label || value
}

function onSelectionChange(rows) {
  selectedRows.value = rows
}

async function toggleStatus(row, enabled) {
  const nextStatus = enabled ? 'dang_su_dung' : 'ngung_su_dung'
  if (row.trang_thai === nextStatus) return

  const prevStatus = row.trang_thai
  row.trang_thai = nextStatus
  statusLoadingId.value = row.id

  const res = await request({
    url: API_NGAN_HANG_THANH_TOAN.UPDATE(row.id),
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

function clearSelection() {
  selectedRows.value = []
  tableRef.value?.clearSelection?.()
}

function handleSearch() {
  pagination.start = 0
  fetchList()
}

function resetForm() {
  form.ten_ngan_hang = ''
  form.ten_viet_tat = ''
  form.hinh_anh_logo = ''
  form.so_tai_khoan = ''
  form.chu_tai_khoan = ''
  form.chi_nhanh = ''
  form.trang_thai = 'dang_su_dung'
  form.ghi_chu = ''
  editingId.value = null
}

function openCreate() {
  resetForm()
  dialogVisible.value = true
}

function openEdit(row) {
  editingId.value = row.id
  form.ten_ngan_hang = row.ten_ngan_hang
  form.ten_viet_tat = row.ten_viet_tat || ''
  form.hinh_anh_logo = row.hinh_anh_logo || ''
  form.so_tai_khoan = row.so_tai_khoan
  form.chu_tai_khoan = row.chu_tai_khoan
  form.chi_nhanh = row.chi_nhanh || ''
  form.trang_thai = row.trang_thai
  form.ghi_chu = row.ghi_chu || ''
  dialogVisible.value = true
}

async function fetchList() {
  const q = filters.keyword.trim()
  const trangThai = filters.trang_thai || ''
  const params = {
    ...(q ? { q } : {}),
    ...(trangThai ? { trang_thai: trangThai } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_NGAN_HANG_THANH_TOAN.LIST, params })

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

async function submitForm() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  const url = isEdit.value
    ? API_NGAN_HANG_THANH_TOAN.UPDATE(editingId.value)
    : API_NGAN_HANG_THANH_TOAN.CREATE
  const body = {
    ten_ngan_hang: form.ten_ngan_hang.trim(),
    ten_viet_tat: form.ten_viet_tat?.trim() || null,
    hinh_anh_logo: form.hinh_anh_logo?.trim() || null,
    so_tai_khoan: form.so_tai_khoan.trim(),
    chu_tai_khoan: form.chu_tai_khoan.trim(),
    chi_nhanh: form.chi_nhanh?.trim() || null,
    trang_thai: form.trang_thai,
    ghi_chu: form.ghi_chu?.trim() || null,
  }

  const res = await request({ url, body })
  if (!res.ok) return

  dialogVisible.value = false
  await fetchList()
}

async function confirmRemove(row) {
  try {
    await ElMessageBox.confirm(
      `Xóa ngân hàng «${row.ten_ngan_hang}» (${row.so_tai_khoan})? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_NGAN_HANG_THANH_TOAN.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} ngân hàng đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_NGAN_HANG_THANH_TOAN.BULK_DELETE,
    body: { ids: selectedIds.value },
  })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkStatus(trangThai) {
  const targetRows = trangThai === 'ngung_su_dung' ? lockableRows.value : unlockableRows.value
  if (!targetRows.length) return

  const actionLabel = trangThai === 'ngung_su_dung' ? 'khóa' : 'mở'
  const statusLabel = trangThaiLabel(trangThai)
  const ids = targetRows.map((row) => row.id)

  try {
    await ElMessageBox.confirm(
      `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} ${ids.length} ngân hàng (trạng thái: ${statusLabel})?`,
      `Xác nhận ${actionLabel}`,
      { type: 'warning', confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_NGAN_HANG_THANH_TOAN.BULK_STATUS,
    body: { ids, trang_thai: trangThai },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(fetchList)
</script>

<style scoped>
.logo-thumb {
  width: 80px;
  height: 60px;
  border-radius: 6px;
}

.logo-preview {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 16px;
}

.logo-preview__img {
  width: 200px;
  height: 96px;
  border-radius: 12px;
  border: 1px solid var(--el-border-color-lighter);
  background: var(--el-fill-color-blank);
  box-sizing: border-box;
}

.logo-preview__placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  color: var(--el-text-color-placeholder);
  text-align: center;
  padding: 8px;
  box-sizing: border-box;
}

.logo-preview__img.logo-preview__placeholder {
  width: 200px;
  height: 96px;
  flex-shrink: 0;
  border: 2px dashed var(--el-color-primary);
  background: var(--el-color-primary-light-9);
  color: var(--el-color-primary);
}

.text-muted {
  color: var(--el-text-color-placeholder);
}
</style>
