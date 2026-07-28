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
                placeholder="Tên hoặc mã chuyên ngành..."
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Ngành học">
              <CustomSelect
                v-model="filters.nganh_hoc_id"
                clearable
                filterable
                class="filter-control"
                placeholder="Chọn ngành học"
              >
                <CustomOption
                  v-for="opt in nganhHocOptions"
                  :key="opt.id"
                  :label="`${opt.ma_nganh} — ${opt.ten_nganh}`"
                  :value="opt.id"
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
        <h2>Quản lý chuyên ngành</h2>
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

          <CustomTooltip content="Khóa các chuyên ngành đang sử dụng" placement="top">
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

          <CustomTooltip content="Mở các chuyên ngành đang ngừng sử dụng" placement="top">
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

          <CustomTooltip content="Thêm chuyên ngành" placement="top">
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
        <CustomTableColumn prop="ma_chuyen_nganh" label="Mã CN" width="140" />
        <CustomTableColumn prop="ten_chuyen_nganh" label="Tên chuyên ngành" min-width="200" />
        <CustomTableColumn label="Ngành học" min-width="180" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.nganh_hoc ? `${row.nganh_hoc.ma_nganh} — ${row.nganh_hoc.ten_nganh}` : '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="mo_ta" label="Mô tả" min-width="180" show-overflow-tooltip />
        <CustomTableColumn prop="ghi_chu" label="Ghi chú" min-width="140" show-overflow-tooltip />
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
      :title="isEdit ? 'Sửa chuyên ngành' : 'Thêm chuyên ngành'"
      :width="900"
    >
      <CustomForm ref="formRef" :model="form" :rules="rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <CustomFormItem label="Mã chuyên ngành" prop="ma_chuyen_nganh">
              <CustomInput v-model="form.ma_chuyen_nganh" placeholder="Ví dụ: CNTT-PM" maxlength="50" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <CustomFormItem label="Tên chuyên ngành" prop="ten_chuyen_nganh">
              <CustomInput
                v-model="form.ten_chuyen_nganh"
                placeholder="Nhập tên chuyên ngành"
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <CustomFormItem label="Ngành học" prop="nganh_hoc_id">
              <CustomSelect
                v-model="form.nganh_hoc_id"
                filterable
                placeholder="Chọn ngành học"
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in nganhHocOptions"
                  :key="opt.id"
                  :label="`${opt.ma_nganh} — ${opt.ten_nganh}`"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
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
            <CustomFormItem label="Mô tả" prop="mo_ta">
              <CustomInput
                v-model="form.mo_ta"
                type="textarea"
                :rows="3"
                placeholder="Mô tả (không bắt buộc)"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :span="24">
            <CustomFormItem label="Ghi chú" prop="ghi_chu">
              <CustomInput
                v-model="form.ghi_chu"
                type="textarea"
                :rows="2"
                placeholder="Ghi chú (không bắt buộc)"
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
import { API_CHUYEN_NGANH, API_NGANH_HOC } from '@/constants/constant_api'
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
const nganhHocOptions = ref([])
const selectedRows = ref([])
const statusLoadingId = ref(null)
const dialogVisible = ref(false)
const editingId = ref(null)
const formRef = ref(null)

const filters = reactive({
  keyword: '',
  nganh_hoc_id: '',
  trang_thai: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

const form = reactive({
  ma_chuyen_nganh: '',
  ten_chuyen_nganh: '',
  nganh_hoc_id: null,
  mo_ta: '',
  ghi_chu: '',
  trang_thai: 'dang_su_dung',
})

const rules = {
  ma_chuyen_nganh: [
    { required: true, message: 'Vui lòng nhập mã chuyên ngành', trigger: 'blur' },
    { max: 50, message: 'Tối đa 50 ký tự', trigger: 'blur' },
  ],
  ten_chuyen_nganh: [
    { required: true, message: 'Vui lòng nhập tên chuyên ngành', trigger: 'blur' },
    { max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' },
  ],
  nganh_hoc_id: [{ required: true, message: 'Vui lòng chọn ngành học', trigger: 'change' }],
  trang_thai: [{ required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }],
}

const isEdit = computed(() => editingId.value !== null)
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
    url: API_CHUYEN_NGANH.UPDATE(row.id),
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
  form.ma_chuyen_nganh = ''
  form.ten_chuyen_nganh = ''
  form.nganh_hoc_id = null
  form.mo_ta = ''
  form.ghi_chu = ''
  form.trang_thai = 'dang_su_dung'
  editingId.value = null
}

function openCreate() {
  resetForm()
  dialogVisible.value = true
}

function openEdit(row) {
  editingId.value = row.id
  form.ma_chuyen_nganh = row.ma_chuyen_nganh
  form.ten_chuyen_nganh = row.ten_chuyen_nganh
  form.nganh_hoc_id = row.nganh_hoc_id
  form.mo_ta = row.mo_ta || ''
  form.ghi_chu = row.ghi_chu || ''
  form.trang_thai = row.trang_thai
  dialogVisible.value = true
}

async function fetchNganhHocOptions() {
  const res = await request({
    url: API_NGANH_HOC.LIST,
    params: { start: 0, limit: 500 },
    loading: false,
    silent: true,
  })

  nganhHocOptions.value = res.ok ? (res.data ?? []) : []
}

async function fetchList() {
  const q = filters.keyword.trim()
  const trangThai = filters.trang_thai || ''
  const nganhHocId = filters.nganh_hoc_id || ''
  const params = {
    ...(q ? { q } : {}),
    ...(trangThai ? { trang_thai: trangThai } : {}),
    ...(nganhHocId ? { nganh_hoc_id: nganhHocId } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_CHUYEN_NGANH.LIST, params })

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
    ? API_CHUYEN_NGANH.UPDATE(editingId.value)
    : API_CHUYEN_NGANH.CREATE
  const body = {
    ma_chuyen_nganh: form.ma_chuyen_nganh.trim(),
    ten_chuyen_nganh: form.ten_chuyen_nganh.trim(),
    nganh_hoc_id: form.nganh_hoc_id,
    mo_ta: form.mo_ta?.trim() || null,
    ghi_chu: form.ghi_chu?.trim() || null,
    trang_thai: form.trang_thai,
  }

  const res = await request({ url, body })
  if (!res.ok) return

  dialogVisible.value = false
  await fetchList()
}

async function confirmRemove(row) {
  try {
    await ElMessageBox.confirm(
      `Xóa chuyên ngành «${row.ten_chuyen_nganh}»? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_CHUYEN_NGANH.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} chuyên ngành đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_CHUYEN_NGANH.BULK_DELETE,
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
      `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} ${ids.length} chuyên ngành (trạng thái: ${statusLabel})?`,
      `Xác nhận ${actionLabel}`,
      { type: 'warning', confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_CHUYEN_NGANH.BULK_STATUS,
    body: { ids, trang_thai: trangThai },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(async () => {
  await fetchNganhHocOptions()
  await fetchList()
})
</script>
