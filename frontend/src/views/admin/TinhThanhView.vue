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
                placeholder="Tên hoặc mã tỉnh thành..."
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Khu vực">
              <CustomSelect
                v-model="filters.khu_vuc_id"
                clearable
                filterable
                class="filter-control"
                placeholder="Chọn khu vực"
              >
                <CustomOption
                  v-for="opt in khuVucOptions"
                  :key="opt.id"
                  :label="`${opt.ma_khu_vuc} — ${opt.ten_khu_vuc}`"
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
        <h2>Quản lý tỉnh thành</h2>
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

          <CustomTooltip content="Khóa các tỉnh thành đang sử dụng" placement="top">
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

          <CustomTooltip content="Mở các tỉnh thành đang ngừng sử dụng" placement="top">
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

          <CustomTooltip content="Thêm tỉnh thành" placement="top">
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
        <CustomTableColumn prop="ma_tinh_thanh" label="Mã tỉnh" width="110" />
        <CustomTableColumn prop="ten_tinh_thanh" label="Tên tỉnh thành" min-width="200" />
        <CustomTableColumn label="Khu vực" min-width="220" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.khu_vuc ? `${row.khu_vuc.ma_khu_vuc} — ${row.khu_vuc.ten_khu_vuc}` : '—' }}
          </template>
        </CustomTableColumn>
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
      :title="isEdit ? 'Sửa tỉnh thành' : 'Thêm tỉnh thành'"
      :width="720"
    >
      <CustomForm ref="formRef" :model="form" :rules="rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Mã tỉnh thành" prop="ma_tinh_thanh">
              <CustomInput v-model="form.ma_tinh_thanh" placeholder="Ví dụ: 1" maxlength="20" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Tên tỉnh thành" prop="ten_tinh_thanh">
              <CustomInput
                v-model="form.ten_tinh_thanh"
                placeholder="Nhập tên tỉnh thành"
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Khu vực" prop="khu_vuc_id">
              <CustomSelect
                v-model="form.khu_vuc_id"
                clearable
                filterable
                placeholder="Chọn khu vực (không bắt buộc)"
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in khuVucOptions"
                  :key="opt.id"
                  :label="`${opt.ma_khu_vuc} — ${opt.ten_khu_vuc}`"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
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
import { API_KHU_VUC, API_TINH_THANH } from '@/constants/constant_api'
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
const khuVucOptions = ref([])
const selectedRows = ref([])
const statusLoadingId = ref(null)
const dialogVisible = ref(false)
const editingId = ref(null)
const formRef = ref(null)

const filters = reactive({
  keyword: '',
  khu_vuc_id: '',
  trang_thai: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

const form = reactive({
  ma_tinh_thanh: '',
  ten_tinh_thanh: '',
  khu_vuc_id: null,
  trang_thai: 'dang_su_dung',
})

const rules = {
  ma_tinh_thanh: [
    { required: true, message: 'Vui lòng nhập mã tỉnh thành', trigger: 'blur' },
    { max: 20, message: 'Tối đa 20 ký tự', trigger: 'blur' },
  ],
  ten_tinh_thanh: [
    { required: true, message: 'Vui lòng nhập tên tỉnh thành', trigger: 'blur' },
    { max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' },
  ],
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
    url: API_TINH_THANH.UPDATE(row.id),
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
  form.ma_tinh_thanh = ''
  form.ten_tinh_thanh = ''
  form.khu_vuc_id = null
  form.trang_thai = 'dang_su_dung'
  editingId.value = null
}

function openCreate() {
  resetForm()
  dialogVisible.value = true
}

function openEdit(row) {
  editingId.value = row.id
  form.ma_tinh_thanh = String(row.ma_tinh_thanh ?? '')
  form.ten_tinh_thanh = row.ten_tinh_thanh
  form.khu_vuc_id = row.khu_vuc_id ?? null
  form.trang_thai = row.trang_thai
  dialogVisible.value = true
}

async function fetchKhuVucOptions() {
  const res = await request({
    url: API_KHU_VUC.LIST,
    params: { start: 0, limit: 500, trang_thai: 'dang_su_dung' },
    loading: false,
    silent: true,
  })

  khuVucOptions.value = res.ok ? (res.data ?? []) : []
}

async function fetchList() {
  const q = filters.keyword.trim()
  const trangThai = filters.trang_thai || ''
  const khuVucId = filters.khu_vuc_id || ''
  const params = {
    ...(q ? { q } : {}),
    ...(trangThai ? { trang_thai: trangThai } : {}),
    ...(khuVucId ? { khu_vuc_id: khuVucId } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_TINH_THANH.LIST, params })

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
    ? API_TINH_THANH.UPDATE(editingId.value)
    : API_TINH_THANH.CREATE
  const body = {
    ma_tinh_thanh: form.ma_tinh_thanh.trim(),
    ten_tinh_thanh: form.ten_tinh_thanh.trim(),
    khu_vuc_id: form.khu_vuc_id || null,
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
      `Xóa tỉnh thành «${row.ten_tinh_thanh}»? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_TINH_THANH.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} tỉnh thành đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_TINH_THANH.BULK_DELETE,
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
      `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} ${ids.length} tỉnh thành (trạng thái: ${statusLabel})?`,
      `Xác nhận ${actionLabel}`,
      { type: 'warning', confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_TINH_THANH.BULK_STATUS,
    body: { ids, trang_thai: trangThai },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(async () => {
  await fetchKhuVucOptions()
  await fetchList()
})
</script>
