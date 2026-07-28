<template>
  <div class="admin-crud">
    <CustomCard shadow="never" class="mb filter-card">
      <div class="filter-bar">
        <CustomInput
          v-model="filters.keyword"
          clearable
          class="filter-input"
          placeholder="Tìm theo tên hoặc mã hệ đào tạo..."
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
      <div class="list-toolbar">
        <h2>Quản lý hệ đào tạo</h2>
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

          <CustomTooltip content="Thêm hệ đào tạo" placement="top">
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
        <CustomTableColumn prop="ma_he_dao_tao" label="Mã hệ" width="140" />
        <CustomTableColumn prop="ten_he_dao_tao" label="Tên hệ đào tạo" min-width="200" />
        <CustomTableColumn prop="ghi_chu" label="Ghi chú" min-width="220" show-overflow-tooltip />
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
      :title="isEdit ? 'Sửa hệ đào tạo' : 'Thêm hệ đào tạo'"
      :width="720"
    >
      <CustomForm ref="formRef" :model="form" :rules="rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="24" :sm="12">
            <CustomFormItem label="Mã hệ đào tạo" prop="ma_he_dao_tao">
              <CustomInput v-model="form.ma_he_dao_tao" placeholder="Ví dụ: CQ" maxlength="50" />
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="24" :sm="12">
            <CustomFormItem label="Tên hệ đào tạo" prop="ten_he_dao_tao">
              <CustomInput
                v-model="form.ten_he_dao_tao"
                placeholder="Nhập tên hệ đào tạo"
                maxlength="255"
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
import { API_HE_DAO_TAO } from '@/constants/constant_api'
import {
  CustomButton,
  CustomCard,
  CustomCol,
  CustomDialog,
  CustomForm,
  CustomFormItem,
  CustomInput,
  CustomPagination,
  CustomRow,
  CustomTable,
  CustomTableColumn,
  CustomTooltip,
} from '@/components/element'

const tableRef = ref(null)
const items = ref([])
const selectedRows = ref([])
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
  ma_he_dao_tao: '',
  ten_he_dao_tao: '',
  ghi_chu: '',
})

const rules = {
  ma_he_dao_tao: [
    { required: true, message: 'Vui lòng nhập mã hệ đào tạo', trigger: 'blur' },
    { max: 50, message: 'Tối đa 50 ký tự', trigger: 'blur' },
  ],
  ten_he_dao_tao: [
    { required: true, message: 'Vui lòng nhập tên hệ đào tạo', trigger: 'blur' },
    { max: 255, message: 'Tối đa 255 ký tự', trigger: 'blur' },
  ],
}

const isEdit = computed(() => editingId.value !== null)
const selectedCount = computed(() => selectedRows.value.length)
const hasSelection = computed(() => selectedCount.value > 0)
const selectedIds = computed(() => selectedRows.value.map((row) => row.id))

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

function resetFilters() {
  filters.keyword = ''
  pagination.start = 0
  fetchList()
}

function resetForm() {
  form.ma_he_dao_tao = ''
  form.ten_he_dao_tao = ''
  form.ghi_chu = ''
  editingId.value = null
}

function openCreate() {
  resetForm()
  dialogVisible.value = true
}

function openEdit(row) {
  editingId.value = row.id
  form.ma_he_dao_tao = row.ma_he_dao_tao
  form.ten_he_dao_tao = row.ten_he_dao_tao
  form.ghi_chu = row.ghi_chu || ''
  dialogVisible.value = true
}

async function fetchList() {
  const q = filters.keyword.trim()
  const params = {
    ...(q ? { q } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_HE_DAO_TAO.LIST, params })

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
    ? API_HE_DAO_TAO.UPDATE(editingId.value)
    : API_HE_DAO_TAO.CREATE
  const body = {
    ma_he_dao_tao: form.ma_he_dao_tao.trim(),
    ten_he_dao_tao: form.ten_he_dao_tao.trim(),
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
      `Xóa hệ đào tạo «${row.ten_he_dao_tao}»? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_HE_DAO_TAO.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} hệ đào tạo đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_HE_DAO_TAO.BULK_DELETE,
    body: { ids: selectedIds.value },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(fetchList)
</script>
