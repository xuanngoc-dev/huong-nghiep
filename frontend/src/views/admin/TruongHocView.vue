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
                placeholder="Tên, mã hoặc slug..."
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Loại hình trường">
              <CustomSelect
                v-model="filters.loai_hinh_truong_id"
                clearable
                filterable
                class="filter-control"
                placeholder="Chọn loại hình"
              >
                <CustomOption
                  v-for="opt in loaiTruongOptions"
                  :key="opt.id"
                  :label="`${opt.ma_loai_truong} — ${opt.ten_loai_truong}`"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Hệ đào tạo">
              <CustomSelect
                v-model="filters.he_dao_tao_id"
                clearable
                filterable
                class="filter-control"
                placeholder="Chọn hệ đào tạo"
              >
                <CustomOption
                  v-for="opt in heDaoTaoOptions"
                  :key="opt.id"
                  :label="`${opt.ma_he_dao_tao} — ${opt.ten_he_dao_tao}`"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Tỉnh thành">
              <CustomSelect
                v-model="filters.tinh_thanh_id"
                clearable
                filterable
                class="filter-control"
                placeholder="Chọn tỉnh thành"
              >
                <CustomOption
                  v-for="opt in tinhThanhOptions"
                  :key="opt.id"
                  :label="`${opt.ma_tinh_thanh} — ${opt.ten_tinh_thanh}`"
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
        <h2>Quản lý trường học</h2>
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

          <CustomTooltip content="Khóa các trường đang sử dụng" placement="top">
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

          <CustomTooltip content="Mở các trường đang ngừng sử dụng" placement="top">
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

          <CustomTooltip content="Thêm trường học" placement="top">
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
        <CustomTableColumn prop="ma_truong" label="Mã trường" width="120" />
        <CustomTableColumn prop="ten_truong" label="Tên trường" min-width="220" show-overflow-tooltip />
        <CustomTableColumn label="Loại hình" min-width="140" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.loai_hinh_truong?.ten_loai_truong || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Hệ ĐT" min-width="120" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.he_dao_tao?.ten_he_dao_tao || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Tỉnh thành" min-width="140" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.tinh_thanh?.ten_tinh_thanh || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="so_dien_thoai" label="Điện thoại" width="150" />
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
        <CustomTableColumn label="Thao tác" width="130" fixed="right" align="center">
          <template #default="{ row }">
            <div class="action-btns">
              <CustomTooltip content="Xem" placement="top">
                <CustomButton link type="primary" :icon="View" @click="openView(row)" />
              </CustomTooltip>
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
      :title="dialogTitle"
      :width="1400"
    >
      <CustomForm ref="formRef" :model="form" :rules="isView ? {} : rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Mã trường" prop="ma_truong">
              <CustomInput
                v-model="form.ma_truong"
                placeholder="Ví dụ: BKAHN"
                maxlength="50"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Tên trường" prop="ten_truong">
              <CustomInput
                v-model="form.ten_truong"
                placeholder="Nhập tên trường"
                maxlength="255"
                :disabled="isView"
                @blur="maybeFillSlug"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Tên tiếng Anh" prop="ten_truong_tieng_anh">
              <CustomInput
                v-model="form.ten_truong_tieng_anh"
                placeholder="English name"
                maxlength="255"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Slug" prop="slug_ten_truong">
              <CustomInput
                v-model="form.slug_ten_truong"
                placeholder="Tự tạo nếu để trống"
                maxlength="255"
                :disabled="isView"
                @input="slugTouched = true"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Loại hình trường" prop="loai_hinh_truong_id">
              <CustomSelect
                v-model="form.loai_hinh_truong_id"
                clearable
                filterable
                placeholder="Chọn loại hình"
                style="width: 100%"
                :disabled="isView"
              >
                <CustomOption
                  v-for="opt in loaiTruongOptions"
                  :key="opt.id"
                  :label="`${opt.ma_loai_truong} — ${opt.ten_loai_truong}`"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Hệ đào tạo" prop="he_dao_tao_id">
              <CustomSelect
                v-model="form.he_dao_tao_id"
                clearable
                filterable
                placeholder="Chọn hệ đào tạo"
                style="width: 100%"
                :disabled="isView"
              >
                <CustomOption
                  v-for="opt in heDaoTaoOptions"
                  :key="opt.id"
                  :label="`${opt.ma_he_dao_tao} — ${opt.ten_he_dao_tao}`"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Tỉnh thành" prop="tinh_thanh_id">
              <CustomSelect
                v-model="form.tinh_thanh_id"
                clearable
                filterable
                placeholder="Chọn tỉnh thành"
                style="width: 100%"
                :disabled="isView"
              >
                <CustomOption
                  v-for="opt in tinhThanhOptions"
                  :key="opt.id"
                  :label="`${opt.ma_tinh_thanh} — ${opt.ten_tinh_thanh}`"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Năm học" prop="nam_hoc">
              <CustomInput
                v-model="form.nam_hoc"
                placeholder="Ví dụ: 2025-2026"
                maxlength="20"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Năm thành lập" prop="nam_thanh_lap">
              <CustomInput
                v-model="form.nam_thanh_lap"
                placeholder="Ví dụ: 1956"
                maxlength="4"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Trạng thái" prop="trang_thai">
              <CustomSelect
                v-model="form.trang_thai"
                placeholder="Chọn trạng thái"
                style="width: 100%"
                :disabled="isView"
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
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Thứ tự" prop="thu_tu">
              <CustomInput v-model="form.thu_tu" placeholder="0" :disabled="isView" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Người đại diện" prop="nguoi_dai_dien">
              <CustomInput
                v-model="form.nguoi_dai_dien"
                placeholder="Hiệu trưởng / đại diện"
                maxlength="255"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Mã số thuế" prop="ma_so_thue">
              <CustomInput
                v-model="form.ma_so_thue"
                placeholder="MST (nếu có)"
                maxlength="50"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Điện thoại" prop="so_dien_thoai">
              <CustomInput
                v-model="form.so_dien_thoai"
                placeholder="Số điện thoại"
                maxlength="30"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Hotline" prop="hotline">
              <CustomInput
                v-model="form.hotline"
                placeholder="Hotline tuyển sinh"
                maxlength="30"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Fax" prop="fax">
              <CustomInput v-model="form.fax" placeholder="Fax" maxlength="30" :disabled="isView" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Email" prop="email">
              <CustomInput
                v-model="form.email"
                placeholder="email@truong.edu.vn"
                maxlength="255"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Website" prop="website">
              <CustomInput
                v-model="form.website"
                placeholder="https://..."
                maxlength="255"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Facebook" prop="facebook">
              <CustomInput
                v-model="form.facebook"
                placeholder="https://facebook.com/..."
                maxlength="255"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="YouTube" prop="youtube">
              <CustomInput
                v-model="form.youtube"
                placeholder="https://youtube.com/..."
                maxlength="255"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Logo (URL)" prop="logo">
              <CustomInput
                v-model="form.logo"
                placeholder="Đường dẫn / URL logo"
                maxlength="255"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="6" :xl="4">
            <CustomFormItem label="Địa chỉ" prop="dia_chi">
              <CustomInput
                v-model="form.dia_chi"
                placeholder="Địa chỉ trường"
                maxlength="500"
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :span="24">
            <CustomFormItem label="Mô tả thông tin tuyển sinh" prop="mo_ta_thong_tin_tuyen_sinh">
              <CustomInput
                v-model="form.mo_ta_thong_tin_tuyen_sinh"
                type="textarea"
                :rows="4"
                placeholder="Thông tin tuyển sinh (không bắt buộc)"
                :disabled="isView"
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
                :disabled="isView"
              />
            </CustomFormItem>
          </CustomCol>
        </CustomRow>
      </CustomForm>

      <template #footer>
        <CustomButton @click="dialogVisible = false">
          {{ isView ? 'Đóng' : 'Hủy' }}
        </CustomButton>
        <CustomButton
          v-if="!isView"
          type="primary"
          :loading="isRequestLoading"
          @click="submitForm"
        >
          {{ isEdit ? 'Cập nhật' : 'Tạo mới' }}
        </CustomButton>
      </template>
    </CustomDialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { ElMessageBox } from 'element-plus'
import { Delete, Edit, Lock, Plus, Search, Unlock, View } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import {
  API_HE_DAO_TAO,
  API_LOAI_TRUONG,
  API_TINH_THANH,
  API_TRUONG_HOC,
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
  CustomTag,
  CustomTooltip,
} from '@/components/element'

const trangThaiOptions = [
  { value: 'dang_su_dung', label: 'Đang sử dụng' },
  { value: 'ngung_su_dung', label: 'Ngừng sử dụng' },
]

const tableRef = ref(null)
const items = ref([])
const loaiTruongOptions = ref([])
const heDaoTaoOptions = ref([])
const tinhThanhOptions = ref([])
const selectedRows = ref([])
const statusLoadingId = ref(null)
const dialogVisible = ref(false)
const dialogMode = ref('create') // create | edit | view
const editingId = ref(null)
const formRef = ref(null)
const slugTouched = ref(false)

const filters = reactive({
  keyword: '',
  loai_hinh_truong_id: '',
  he_dao_tao_id: '',
  tinh_thanh_id: '',
  trang_thai: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

const form = reactive({
  ma_truong: '',
  ten_truong: '',
  ten_truong_tieng_anh: '',
  slug_ten_truong: '',
  loai_hinh_truong_id: null,
  he_dao_tao_id: null,
  tinh_thanh_id: null,
  nam_hoc: '',
  nam_thanh_lap: '',
  so_dien_thoai: '',
  hotline: '',
  fax: '',
  email: '',
  website: '',
  facebook: '',
  youtube: '',
  logo: '',
  nguoi_dai_dien: '',
  ma_so_thue: '',
  dia_chi: '',
  ghi_chu: '',
  mo_ta_thong_tin_tuyen_sinh: '',
  thu_tu: '0',
  trang_thai: 'dang_su_dung',
})

const rules = {
  ma_truong: [
    { required: true, message: 'Vui lòng nhập mã trường', trigger: 'blur' },
    { max: 50, message: 'Tối đa 50 ký tự', trigger: 'blur' },
  ],
  ten_truong: [
    { required: true, message: 'Vui lòng nhập tên trường', trigger: 'blur' },
    { max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' },
  ],
  email: [{ type: 'email', message: 'Email không hợp lệ', trigger: 'blur' }],
  trang_thai: [{ required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }],
}

const isEdit = computed(() => dialogMode.value === 'edit')
const isView = computed(() => dialogMode.value === 'view')
const dialogTitle = computed(() => {
  if (isView.value) return 'Chi tiết trường học'
  if (isEdit.value) return 'Sửa trường học'
  return 'Thêm trường học'
})
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

function slugify(text) {
  return String(text || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
}

function maybeFillSlug() {
  if (slugTouched.value && form.slug_ten_truong) return
  if (!form.ten_truong.trim()) return
  form.slug_ten_truong = slugify(form.ten_truong)
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
    url: API_TRUONG_HOC.UPDATE(row.id),
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
  form.ma_truong = ''
  form.ten_truong = ''
  form.ten_truong_tieng_anh = ''
  form.slug_ten_truong = ''
  form.loai_hinh_truong_id = null
  form.he_dao_tao_id = null
  form.tinh_thanh_id = null
  form.nam_hoc = ''
  form.nam_thanh_lap = ''
  form.so_dien_thoai = ''
  form.hotline = ''
  form.fax = ''
  form.email = ''
  form.website = ''
  form.facebook = ''
  form.youtube = ''
  form.logo = ''
  form.nguoi_dai_dien = ''
  form.ma_so_thue = ''
  form.dia_chi = ''
  form.ghi_chu = ''
  form.mo_ta_thong_tin_tuyen_sinh = ''
  form.thu_tu = '0'
  form.trang_thai = 'dang_su_dung'
  editingId.value = null
  dialogMode.value = 'create'
  slugTouched.value = false
}

function fillFormFromRow(row) {
  editingId.value = row.id
  form.ma_truong = row.ma_truong || ''
  form.ten_truong = row.ten_truong || ''
  form.ten_truong_tieng_anh = row.ten_truong_tieng_anh || ''
  form.slug_ten_truong = row.slug_ten_truong || ''
  form.loai_hinh_truong_id = row.loai_hinh_truong_id ?? null
  form.he_dao_tao_id = row.he_dao_tao_id ?? null
  form.tinh_thanh_id = row.tinh_thanh_id ?? null
  form.nam_hoc = row.nam_hoc || ''
  form.nam_thanh_lap = row.nam_thanh_lap != null ? String(row.nam_thanh_lap) : ''
  form.so_dien_thoai = row.so_dien_thoai || ''
  form.hotline = row.hotline || ''
  form.fax = row.fax || ''
  form.email = row.email || ''
  form.website = row.website || ''
  form.facebook = row.facebook || ''
  form.youtube = row.youtube || ''
  form.logo = row.logo || ''
  form.nguoi_dai_dien = row.nguoi_dai_dien || ''
  form.ma_so_thue = row.ma_so_thue || ''
  form.dia_chi = row.dia_chi || ''
  form.ghi_chu = row.ghi_chu || ''
  form.mo_ta_thong_tin_tuyen_sinh = row.mo_ta_thong_tin_tuyen_sinh || ''
  form.thu_tu = row.thu_tu != null ? String(row.thu_tu) : '0'
  form.trang_thai = row.trang_thai
  slugTouched.value = true
}

function openCreate() {
  resetForm()
  dialogMode.value = 'create'
  dialogVisible.value = true
}

function openView(row) {
  fillFormFromRow(row)
  dialogMode.value = 'view'
  dialogVisible.value = true
}

function openEdit(row) {
  fillFormFromRow(row)
  dialogMode.value = 'edit'
  dialogVisible.value = true
}

async function fetchOptions() {
  const [loaiRes, heRes, tinhRes] = await Promise.all([
    request({
      url: API_LOAI_TRUONG.LIST,
      params: { start: 0, limit: 500 },
      loading: false,
      silent: true,
    }),
    request({
      url: API_HE_DAO_TAO.LIST,
      params: { start: 0, limit: 500 },
      loading: false,
      silent: true,
    }),
    request({
      url: API_TINH_THANH.LIST,
      params: { start: 0, limit: 500, trang_thai: 'dang_su_dung' },
      loading: false,
      silent: true,
    }),
  ])

  loaiTruongOptions.value = loaiRes.ok ? (loaiRes.data ?? []) : []
  heDaoTaoOptions.value = heRes.ok ? (heRes.data ?? []) : []
  tinhThanhOptions.value = tinhRes.ok ? (tinhRes.data ?? []) : []
}

async function fetchList() {
  const q = filters.keyword.trim()
  const params = {
    ...(q ? { q } : {}),
    ...(filters.trang_thai ? { trang_thai: filters.trang_thai } : {}),
    ...(filters.loai_hinh_truong_id ? { loai_hinh_truong_id: filters.loai_hinh_truong_id } : {}),
    ...(filters.he_dao_tao_id ? { he_dao_tao_id: filters.he_dao_tao_id } : {}),
    ...(filters.tinh_thanh_id ? { tinh_thanh_id: filters.tinh_thanh_id } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_TRUONG_HOC.LIST, params })

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

function toNullableInt(value) {
  if (value === '' || value === null || value === undefined) return null
  const n = Number(value)
  return Number.isFinite(n) ? n : null
}

async function submitForm() {
  if (isView.value) return

  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  maybeFillSlug()

  const url = isEdit.value
    ? API_TRUONG_HOC.UPDATE(editingId.value)
    : API_TRUONG_HOC.CREATE
  const body = {
    ma_truong: form.ma_truong.trim(),
    ten_truong: form.ten_truong.trim(),
    ten_truong_tieng_anh: form.ten_truong_tieng_anh?.trim() || null,
    slug_ten_truong: form.slug_ten_truong?.trim() || null,
    loai_hinh_truong_id: form.loai_hinh_truong_id || null,
    he_dao_tao_id: form.he_dao_tao_id || null,
    tinh_thanh_id: form.tinh_thanh_id || null,
    nam_hoc: form.nam_hoc?.trim() || null,
    nam_thanh_lap: toNullableInt(form.nam_thanh_lap),
    so_dien_thoai: form.so_dien_thoai?.trim() || null,
    hotline: form.hotline?.trim() || null,
    fax: form.fax?.trim() || null,
    email: form.email?.trim() || null,
    website: form.website?.trim() || null,
    facebook: form.facebook?.trim() || null,
    youtube: form.youtube?.trim() || null,
    logo: form.logo?.trim() || null,
    nguoi_dai_dien: form.nguoi_dai_dien?.trim() || null,
    ma_so_thue: form.ma_so_thue?.trim() || null,
    dia_chi: form.dia_chi?.trim() || null,
    ghi_chu: form.ghi_chu?.trim() || null,
    mo_ta_thong_tin_tuyen_sinh: form.mo_ta_thong_tin_tuyen_sinh?.trim() || null,
    thu_tu: toNullableInt(form.thu_tu) ?? 0,
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
      `Xóa trường học «${row.ten_truong}»? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_TRUONG_HOC.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} trường học đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_TRUONG_HOC.BULK_DELETE,
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
      `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} ${ids.length} trường học (trạng thái: ${statusLabel})?`,
      `Xác nhận ${actionLabel}`,
      { type: 'warning', confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_TRUONG_HOC.BULK_STATUS,
    body: { ids, trang_thai: trangThai },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(async () => {
  await fetchOptions()
  await fetchList()
})
</script>
