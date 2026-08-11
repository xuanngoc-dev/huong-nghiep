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
                placeholder="Mã trường, tên trường, năm học..."
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Trường học">
              <CustomSelect
                v-model="filters.ma_truong"
                clearable
                filterable
                class="filter-control"
                placeholder="Tất cả trường"
              >
                <CustomOption
                  v-for="opt in truongHocOptions"
                  :key="opt.ma_truong"
                  :label="`${opt.ma_truong} — ${opt.ten_truong}`"
                  :value="opt.ma_truong"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Năm học">
              <CustomInput
                v-model="filters.nam_hoc"
                clearable
                class="filter-control"
                placeholder="Ví dụ: 2026-2027"
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Ngành học">
              <CustomSelect
                v-model="filters.nganh_hoc_tuyen_sinh_id"
                clearable
                filterable
                class="filter-control"
                placeholder="Tất cả ngành"
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
        <h2>Quản lý tuyển sinh theo năm</h2>
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

          <CustomTooltip content="Thêm tuyển sinh theo năm" placement="top">
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
        <CustomTableColumn prop="ma_truong" label="Mã trường" width="110" />
        <CustomTableColumn label="Trường học" min-width="180" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.truong_hoc?.ten_truong || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="nam_hoc" label="Năm học" width="110" />
        <CustomTableColumn label="Ngành" min-width="160" show-overflow-tooltip>
          <template #default="{ row }">
            <span v-if="row.nganh_hoc_tuyen_sinh">
              {{ row.nganh_hoc_tuyen_sinh.ten_nganh }}
            </span>
            <span v-else>—</span>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Chuyên ngành" min-width="160" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.chuyen_nganh_tuyen_sinh?.ten_chuyen_nganh || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn
          prop="phuong_thuc_xet_tuyen"
          label="Phương thức XT"
          min-width="140"
          show-overflow-tooltip
        />
        <CustomTableColumn
          prop="to_hop_xet_tuyen"
          label="Tổ hợp XT"
          min-width="120"
          show-overflow-tooltip
        />
        <CustomTableColumn prop="chi_tieu" label="Chỉ tiêu" width="100" align="center">
          <template #default="{ row }">
            {{ row.chi_tieu ?? '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="diem_chuan" label="Điểm chuẩn" width="110" align="center">
          <template #default="{ row }">
            {{ row.diem_chuan ?? '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="ghi_chu" label="Ghi chú" min-width="140" show-overflow-tooltip />
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
      :title="isEdit ? 'Sửa tuyển sinh theo năm' : 'Thêm tuyển sinh theo năm'"
      :width="920"
    >
      <CustomForm ref="formRef" :model="form" :rules="rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Trường học" prop="ma_truong">
              <CustomSelect
                v-model="form.ma_truong"
                filterable
                placeholder="Chọn trường học"
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in truongHocOptions"
                  :key="opt.ma_truong"
                  :label="`${opt.ma_truong} — ${opt.ten_truong}`"
                  :value="opt.ma_truong"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Năm học" prop="nam_hoc">
              <CustomInput
                v-model="form.nam_hoc"
                placeholder="Ví dụ: 2026-2027"
                maxlength="20"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Ngành học tuyển sinh" prop="nganh_hoc_tuyen_sinh_id">
              <CustomSelect
                v-model="form.nganh_hoc_tuyen_sinh_id"
                filterable
                placeholder="Chọn ngành học"
                style="width: 100%"
                @change="onNganhHocChange"
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
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Chuyên ngành tuyển sinh" prop="chuyen_nganh_tuyen_sinh_id">
              <CustomSelect
                v-model="form.chuyen_nganh_tuyen_sinh_id"
                clearable
                filterable
                :disabled="!form.nganh_hoc_tuyen_sinh_id"
                placeholder="Chọn chuyên ngành"
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in filteredChuyenNganhOptions"
                  :key="opt.id"
                  :label="`${opt.ma_chuyen_nganh} — ${opt.ten_chuyen_nganh}`"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Phương thức xét tuyển" prop="phuong_thuc_xet_tuyen">
              <CustomInput
                v-model="form.phuong_thuc_xet_tuyen"
                placeholder="Ví dụ: Điểm thi THPT, Học bạ..."
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Tổ hợp xét tuyển" prop="to_hop_xet_tuyen">
              <CustomInput
                v-model="form.to_hop_xet_tuyen"
                placeholder="Ví dụ: A00, A01, D01"
                maxlength="255"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Chỉ tiêu" prop="chi_tieu">
              <CustomInput
                v-model.number="form.chi_tieu"
                type="number"
                min="0"
                placeholder="Số chỉ tiêu"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
            <CustomFormItem label="Điểm chuẩn" prop="diem_chuan">
              <CustomInput
                v-model.number="form.diem_chuan"
                type="number"
                min="0"
                step="0.01"
                placeholder="Ví dụ: 25.5"
              />
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
import { Delete, Edit, Plus, Search } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import {
  API_CHUYEN_NGANH,
  API_NGANH_HOC,
  API_TRUONG_HOC,
  API_TRUONG_HOC_TUYEN_SINH_THEO_NAM,
} from '@/constants/constant_api'
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
  CustomTooltip,
} from '@/components/element'

const tableRef = ref(null)
const items = ref([])
const truongHocOptions = ref([])
const nganhHocOptions = ref([])
const chuyenNganhOptions = ref([])
const selectedRows = ref([])
const dialogVisible = ref(false)
const editingId = ref(null)
const formRef = ref(null)

const filters = reactive({
  keyword: '',
  ma_truong: '',
  nam_hoc: '',
  nganh_hoc_tuyen_sinh_id: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

const form = reactive({
  ma_truong: '',
  nam_hoc: '',
  nganh_hoc_tuyen_sinh_id: null,
  chuyen_nganh_tuyen_sinh_id: null,
  phuong_thuc_xet_tuyen: '',
  to_hop_xet_tuyen: '',
  chi_tieu: null,
  diem_chuan: null,
  ghi_chu: '',
})

const rules = {
  ma_truong: [{ required: true, message: 'Vui lòng chọn trường học', trigger: 'change' }],
  nam_hoc: [
    { required: true, message: 'Vui lòng nhập năm học', trigger: 'blur' },
    { max: 20, message: 'Tối đa 20 ký tự', trigger: 'blur' },
  ],
  nganh_hoc_tuyen_sinh_id: [
    { required: true, message: 'Vui lòng chọn ngành học tuyển sinh', trigger: 'change' },
  ],
}

const isEdit = computed(() => editingId.value !== null)
const selectedCount = computed(() => selectedRows.value.length)
const hasSelection = computed(() => selectedCount.value > 0)
const selectedIds = computed(() => selectedRows.value.map((row) => row.id))

const filteredChuyenNganhOptions = computed(() => {
  if (!form.nganh_hoc_tuyen_sinh_id) return []
  return chuyenNganhOptions.value.filter(
    (opt) => opt.nganh_hoc_id === form.nganh_hoc_tuyen_sinh_id,
  )
})

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

function onNganhHocChange() {
  form.chuyen_nganh_tuyen_sinh_id = null
}

function resetForm() {
  form.ma_truong = ''
  form.nam_hoc = ''
  form.nganh_hoc_tuyen_sinh_id = null
  form.chuyen_nganh_tuyen_sinh_id = null
  form.phuong_thuc_xet_tuyen = ''
  form.to_hop_xet_tuyen = ''
  form.chi_tieu = null
  form.diem_chuan = null
  form.ghi_chu = ''
  editingId.value = null
}

function openCreate() {
  resetForm()
  dialogVisible.value = true
}

function openEdit(row) {
  editingId.value = row.id
  form.ma_truong = row.ma_truong
  form.nam_hoc = row.nam_hoc
  form.nganh_hoc_tuyen_sinh_id = row.nganh_hoc_tuyen_sinh_id
  form.chuyen_nganh_tuyen_sinh_id = row.chuyen_nganh_tuyen_sinh_id
  form.phuong_thuc_xet_tuyen = row.phuong_thuc_xet_tuyen || ''
  form.to_hop_xet_tuyen = row.to_hop_xet_tuyen || ''
  form.chi_tieu = row.chi_tieu ?? null
  form.diem_chuan = row.diem_chuan ?? null
  form.ghi_chu = row.ghi_chu || ''
  dialogVisible.value = true
}

async function fetchOptions() {
  const [truongRes, nganhRes, chuyenRes] = await Promise.all([
    request({
      url: API_TRUONG_HOC.LIST,
      params: { start: 0, limit: 500 },
      loading: false,
      silent: true,
    }),
    request({
      url: API_NGANH_HOC.LIST,
      params: { start: 0, limit: 500 },
      loading: false,
      silent: true,
    }),
    request({
      url: API_CHUYEN_NGANH.LIST,
      params: { start: 0, limit: 1000 },
      loading: false,
      silent: true,
    }),
  ])

  truongHocOptions.value = truongRes.ok ? (truongRes.data ?? []) : []
  nganhHocOptions.value = nganhRes.ok ? (nganhRes.data ?? []) : []
  chuyenNganhOptions.value = chuyenRes.ok ? (chuyenRes.data ?? []) : []
}

async function fetchList() {
  const q = filters.keyword.trim()
  const maTruong = filters.ma_truong || ''
  const namHoc = filters.nam_hoc.trim()
  const nganhHocId = filters.nganh_hoc_tuyen_sinh_id || ''
  const params = {
    ...(q ? { q } : {}),
    ...(maTruong ? { ma_truong: maTruong } : {}),
    ...(namHoc ? { nam_hoc: namHoc } : {}),
    ...(nganhHocId ? { nganh_hoc_tuyen_sinh_id: nganhHocId } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_TRUONG_HOC_TUYEN_SINH_THEO_NAM.LIST, params })

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
    ? API_TRUONG_HOC_TUYEN_SINH_THEO_NAM.UPDATE(editingId.value)
    : API_TRUONG_HOC_TUYEN_SINH_THEO_NAM.CREATE
  const body = {
    ma_truong: form.ma_truong,
    nam_hoc: form.nam_hoc.trim(),
    nganh_hoc_tuyen_sinh_id: form.nganh_hoc_tuyen_sinh_id,
    chuyen_nganh_tuyen_sinh_id: form.chuyen_nganh_tuyen_sinh_id || null,
    phuong_thuc_xet_tuyen: form.phuong_thuc_xet_tuyen?.trim() || null,
    to_hop_xet_tuyen: form.to_hop_xet_tuyen?.trim() || null,
    chi_tieu: form.chi_tieu === '' || form.chi_tieu === null ? null : Number(form.chi_tieu),
    diem_chuan:
      form.diem_chuan === '' || form.diem_chuan === null ? null : Number(form.diem_chuan),
    ghi_chu: form.ghi_chu?.trim() || null,
  }

  const res = await request({ url, body })
  if (!res.ok) return

  dialogVisible.value = false
  await fetchList()
}

async function confirmRemove(row) {
  const tenTruong = row.truong_hoc?.ten_truong || row.ma_truong
  const tenNganh = row.nganh_hoc_tuyen_sinh?.ten_nganh || ''

  try {
    await ElMessageBox.confirm(
      `Xóa tuyển sinh «${tenTruong}» — ${row.nam_hoc}${tenNganh ? ` — ${tenNganh}` : ''}? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_TRUONG_HOC_TUYEN_SINH_THEO_NAM.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} bản ghi tuyển sinh đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_TRUONG_HOC_TUYEN_SINH_THEO_NAM.BULK_DELETE,
    body: { ids: selectedIds.value },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(async () => {
  await fetchOptions()
  await fetchList()
})
</script>
