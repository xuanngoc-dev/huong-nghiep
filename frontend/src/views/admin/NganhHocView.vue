<template>
  <div>
    <div class="toolbar">
      <div>
        <h2>Quản lý ngành học</h2>
      </div>
      <CustomButton type="primary" :icon="Plus" @click="openCreate">
        Thêm ngành học
      </CustomButton>
    </div>

    <CustomCard shadow="never" class="mb filter-card">
      <div class="filter-bar">
        <CustomInput
          v-model="filters.keyword"
          clearable
          class="filter-input"
          placeholder="Tìm theo tên hoặc mã ngành..."
          @keyup.enter="handleSearch"
        />
        <div class="filter-actions">
          <CustomButton
            type="primary"
            :icon="Search"
            :loading="isRequestLoading"
            @click="handleSearch"
          >
            Tìm kiếm
          </CustomButton>
          <CustomButton :disabled="isRequestLoading" @click="resetFilters">Đặt lại</CustomButton>
        </div>
      </div>
    </CustomCard>

    <CustomCard shadow="never">
      <CustomTable :data="items" stripe empty-text="Chưa có dữ liệu">
        <CustomTableColumn prop="id" label="ID" width="70" />
        <CustomTableColumn prop="ma_nganh" label="Mã ngành" width="140" />
        <CustomTableColumn prop="ten_nganh" label="Tên ngành" min-width="200" />
        <CustomTableColumn prop="ghi_chu" label="Ghi chú" min-width="180" show-overflow-tooltip />
        <CustomTableColumn label="Trạng thái" width="140">
          <template #default="{ row }">
            <CustomTag :type="row.trang_thai === 'dang_su_dung' ? 'success' : 'info'" effect="light">
              {{ trangThaiLabel(row.trang_thai) }}
            </CustomTag>
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
      :title="isEdit ? 'Sửa ngành học' : 'Thêm ngành học'"
      :width="720"
    >
      <CustomForm ref="formRef" :model="form" :rules="rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="24" :sm="12" :md="8">
            <CustomFormItem label="Mã ngành" prop="ma_nganh">
              <CustomInput v-model="form.ma_nganh" placeholder="Ví dụ: CNTT" maxlength="50" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12" :md="8">
            <CustomFormItem label="Tên ngành" prop="ten_nganh">
              <CustomInput v-model="form.ten_nganh" placeholder="Nhập tên ngành học" maxlength="255" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="24" :md="8">
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
import { Delete, Edit, Plus, Search } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import { API_NGANH_HOC } from '@/constants/constant_api'
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

const items = ref([])
const dialogVisible = ref(false)
const editingId = ref(null)
const formRef = ref(null)

const filters = reactive({
  keyword: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

const form = reactive({
  ma_nganh: '',
  ten_nganh: '',
  ghi_chu: '',
  trang_thai: 'dang_su_dung',
})

const rules = {
  ma_nganh: [
    { required: true, message: 'Vui lòng nhập mã ngành', trigger: 'blur' },
    { max: 50, message: 'Tối đa 50 ký tự', trigger: 'blur' },
  ],
  ten_nganh: [
    { required: true, message: 'Vui lòng nhập tên ngành', trigger: 'blur' },
    { max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' },
  ],
  trang_thai: [{ required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }],
}

const isEdit = computed(() => editingId.value !== null)

function trangThaiLabel(value) {
  return trangThaiOptions.find((o) => o.value === value)?.label || value
}

function handleSearch() {
  pagination.start = 0
  fetchList()
}

function resetFilters() {
  filters.keyword = ''
  pagination.start = 0
  fetchList()
}

function resetForm() {
  form.ma_nganh = ''
  form.ten_nganh = ''
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
  form.ma_nganh = row.ma_nganh
  form.ten_nganh = row.ten_nganh
  form.ghi_chu = row.ghi_chu || ''
  form.trang_thai = row.trang_thai
  dialogVisible.value = true
}

async function fetchList() {
  const q = filters.keyword.trim()
  const url = API_NGANH_HOC.LIST
  const params = {
    ...(q ? { q } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url, params })

  if (!res.ok) {
    items.value = []
    pagination.total = 0
    return
  }

  items.value = res.data ?? []
  pagination.total = res.total ?? 0
}

async function submitForm() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  const url = isEdit.value
    ? API_NGANH_HOC.UPDATE(editingId.value)
    : API_NGANH_HOC.CREATE
  const body = {
    ma_nganh: form.ma_nganh.trim(),
    ten_nganh: form.ten_nganh.trim(),
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
      `Xóa ngành học «${row.ten_nganh}»? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const url = API_NGANH_HOC.DELETE(row.id)
  const res = await request({ url })
  if (!res.ok) return

  await fetchList()
}

onMounted(fetchList)
</script>

<style scoped>
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

h2 {
  margin: 0;
  font-size: 1.2rem;
}

.mb {
  margin-bottom: 1rem;
}

.filter-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.filter-input {
  width: 280px;
  max-width: 100%;
}

.filter-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

@media (max-width: 575px) {
  .filter-input {
    width: 100%;
  }
}

.action-btns {
  display: inline-flex;
  align-items: center;
  gap: 0.15rem;
}
</style>
