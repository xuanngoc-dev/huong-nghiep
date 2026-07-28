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
                placeholder="Tên hoặc mã loại câu hỏi..."
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
        <h2>Quản lý loại câu hỏi</h2>
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

          <CustomTooltip content="Khóa các loại đang sử dụng" placement="top">
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

          <CustomTooltip content="Mở các loại đang ngừng sử dụng" placement="top">
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

          <CustomTooltip content="Thêm loại câu hỏi" placement="top">
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
        <CustomTableColumn prop="ma_loai_cau_hoi" label="Mã loại" width="140" />
        <CustomTableColumn prop="ten_loai_cau_hoi" label="Tên loại câu hỏi" min-width="200" />
        <CustomTableColumn prop="thu_tu_uu_tien" label="Ưu tiên" width="100" align="center" />
        <CustomTableColumn prop="ghi_chu" label="Ghi chú" min-width="180" show-overflow-tooltip />
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
      :title="isEdit ? 'Sửa loại câu hỏi' : 'Thêm loại câu hỏi'"
      :width="720"
    >
      <CustomForm ref="formRef" :model="form" :rules="rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Mã loại câu hỏi" prop="ma_loai_cau_hoi">
              <CustomInput v-model="form.ma_loai_cau_hoi" placeholder="Ví dụ: TN" maxlength="50" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Tên loại câu hỏi" prop="ten_loai_cau_hoi">
              <CustomInput
                v-model="form.ten_loai_cau_hoi"
                placeholder="Nhập tên loại câu hỏi"
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Thứ tự ưu tiên" prop="thu_tu_uu_tien">
              <CustomInput
                v-model="form.thu_tu_uu_tien"
                type="number"
                :min="1"
                step="1"
                placeholder="Từ 1 trở lên"
              />
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
          <CustomCol :span="24">
            <CustomFormItem label="Ghi chú" prop="ghi_chu">
              <CustomInput
                v-model="form.ghi_chu"
                type="textarea"
                :rows="3"
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
import { API_LOAI_CAU_HOI } from '@/constants/constant_api'
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
  ma_loai_cau_hoi: '',
  ten_loai_cau_hoi: '',
  thu_tu_uu_tien: 1,
  ghi_chu: '',
  trang_thai: 'dang_su_dung',
})

const rules = {
  ma_loai_cau_hoi: [
    { required: true, message: 'Vui lòng nhập mã loại câu hỏi', trigger: 'blur' },
    { max: 50, message: 'Tối đa 50 ký tự', trigger: 'blur' },
  ],
  ten_loai_cau_hoi: [
    { required: true, message: 'Vui lòng nhập tên loại câu hỏi', trigger: 'blur' },
    { max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' },
  ],
  thu_tu_uu_tien: [
    { required: true, message: 'Vui lòng nhập thứ tự ưu tiên', trigger: 'blur' },
    {
      validator: (_rule, value, callback) => {
        const num = Number(value)
        if (!Number.isInteger(num) || num < 1) {
          callback(new Error('Thứ tự ưu tiên phải là số tự nhiên từ 1 trở lên'))
          return
        }
        callback()
      },
      trigger: 'blur',
    },
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
    url: API_LOAI_CAU_HOI.UPDATE(row.id),
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
  form.ma_loai_cau_hoi = ''
  form.ten_loai_cau_hoi = ''
  form.thu_tu_uu_tien = 1
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
  form.ma_loai_cau_hoi = row.ma_loai_cau_hoi
  form.ten_loai_cau_hoi = row.ten_loai_cau_hoi
  form.thu_tu_uu_tien = row.thu_tu_uu_tien ?? 1
  form.ghi_chu = row.ghi_chu || ''
  form.trang_thai = row.trang_thai
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

  const res = await request({ url: API_LOAI_CAU_HOI.LIST, params })

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
    ? API_LOAI_CAU_HOI.UPDATE(editingId.value)
    : API_LOAI_CAU_HOI.CREATE
  const body = {
    ma_loai_cau_hoi: form.ma_loai_cau_hoi.trim(),
    ten_loai_cau_hoi: form.ten_loai_cau_hoi.trim(),
    thu_tu_uu_tien: Number(form.thu_tu_uu_tien),
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
      `Xóa loại câu hỏi «${row.ten_loai_cau_hoi}»? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_LOAI_CAU_HOI.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} loại câu hỏi đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_LOAI_CAU_HOI.BULK_DELETE,
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
      `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} ${ids.length} loại câu hỏi (trạng thái: ${statusLabel})?`,
      `Xác nhận ${actionLabel}`,
      { type: 'warning', confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_LOAI_CAU_HOI.BULK_STATUS,
    body: { ids, trang_thai: trangThai },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(fetchList)
</script>
