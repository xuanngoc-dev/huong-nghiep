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
                placeholder="SSID, mã giao dịch, tên hoặc email..."
                @keyup.enter="handleSearch"
              />
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
        <h2>Lịch sử trắc nghiệm</h2>
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
        <CustomTableColumn prop="ssid" label="SSID" min-width="220" show-overflow-tooltip />
        <CustomTableColumn prop="ma_giao_dich" label="Mã giao dịch" min-width="150" show-overflow-tooltip />
        
        <CustomTableColumn label="Người khảo sát" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <div v-if="row.nguoi_khao_sat">
              <div>{{ row.nguoi_khao_sat.name || '—' }}</div>
              <div class="sub-text">{{ row.nguoi_khao_sat.email || '' }}</div>
            </div>
            <span v-else>—</span>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Nhóm ngành phù hợp nhất" min-width="240" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.top_nhom_nganh?.ten_nhom_nganh || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Điểm nhóm top" width="150" align="center">
          <template #default="{ row }">
            {{ row.top_nhom_nganh?.tong_diem ?? '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Số nhóm" width="100" align="center">
          <template #default="{ row }">
            {{ row.so_nhom_nganh ?? 0 }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Thanh toán" width="150" align="center">
          <template #default="{ row }">
            <CustomTag :type="row.da_thanh_toan ? 'success' : 'info'" effect="light" size="small">
              {{ row.da_thanh_toan ? 'Đã thanh toán' : 'Chưa thanh toán' }}
            </CustomTag>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Trạng thái" width="150" align="center">
          <template #default="{ row }">
            <CustomTag :type="row.trang_thai === 'hoan_thanh' ? 'success' : 'warning'" effect="light" size="small">
              {{ row.trang_thai_label || (row.trang_thai === 'hoan_thanh' ? 'Hoàn thành' : 'Chưa hoàn thành') }}
            </CustomTag>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Hoàn thành lúc" width="180" align="center">
          <template #default="{ row }">
            {{ formatDateTime(row.created_at) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Thao tác" width="100" fixed="right" align="center">
          <template #default="{ row }">
            <div class="action-btns">
              <CustomTooltip content="Xem chi tiết" placement="top">
                <CustomButton link type="primary" :icon="View" @click="openDetail(row)" />
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

    <CustomDialog v-model="detailVisible" title="Chi tiết phiên trắc nghiệm" :width="900">
      <template v-if="detail">
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="SSID">
            {{ detail.ssid || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Mã giao dịch">
            {{ detail.ma_giao_dich || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Người khảo sát">
            {{ detail.nguoi_khao_sat?.name || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Email">
            {{ detail.nguoi_khao_sat?.email || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Tổng điểm">
            {{ detail.tong_diem ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Số câu đã trả lời">
            {{ detail.so_cau_da_tra_loi ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Thanh toán">
            {{ detail.da_thanh_toan ? 'Đã thanh toán' : 'Chưa thanh toán' }}
          </el-descriptions-item>
          <el-descriptions-item label="Hoàn thành lúc">
            {{ formatDateTime(detail.created_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Kết quả nhóm ngành</h3>
        <CustomTable
          :data="detail.nhom_nganh || []"
          row-key="nhom_nganh_id"
          border
          stripe
          empty-text="Không có dữ liệu nhóm ngành"
          class="detail-table"
        >
          <CustomTableColumn label="STT" width="70" align="center">
            <template #default="{ $index }">
              {{ $index + 1 }}
            </template>
          </CustomTableColumn>
          <CustomTableColumn prop="ten_nhom_nganh" label="Nhóm ngành" min-width="260" />
          <CustomTableColumn prop="tong_diem" label="Tổng điểm" width="120" align="center" />
          <CustomTableColumn prop="so_cau" label="Số câu" width="100" align="center" />
        </CustomTable>

        <h3 class="detail-title">Chi tiết kết quả (JSON)</h3>
        <pre class="json-box">{{ formatJson(detail.chi_tiet_ket_qua) }}</pre>

        <h3 class="detail-title">Thông tin thanh toán (JSON)</h3>
        <pre class="json-box">{{ formatJson(detail.thong_tin_thanh_toan) }}</pre>
      </template>

      <template #footer>
        <CustomButton type="primary" @click="detailVisible = false">Đóng</CustomButton>
      </template>
    </CustomDialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { ElMessageBox } from 'element-plus'
import { Delete, Search, View } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import { API_LICH_SU_TRAC_NGHIEM } from '@/constants/constant_api'
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
  CustomTag,
  CustomTooltip,
} from '@/components/element'

const tableRef = ref(null)
const items = ref([])
const selectedRows = ref([])
const detailVisible = ref(false)
const detail = ref(null)

const filters = reactive({
  keyword: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

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

function formatDateTime(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('vi-VN')
}

function formatJson(value) {
  if (value === null || value === undefined || value === '') return '—'
  try {
    return JSON.stringify(value, null, 2)
  } catch {
    return String(value)
  }
}

async function fetchList() {
  const q = filters.keyword.trim()
  const params = {
    ...(q ? { q } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_LICH_SU_TRAC_NGHIEM.LIST, params })

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

async function openDetail(row) {
  const res = await request({ url: API_LICH_SU_TRAC_NGHIEM.SHOW(row.id) })
  if (!res.ok) return

  detail.value = res.data
  detailVisible.value = true
}

async function confirmRemove(row) {
  try {
    await ElMessageBox.confirm(
      `Xóa phiên trắc nghiệm «${row.ssid}»? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_LICH_SU_TRAC_NGHIEM.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} phiên trắc nghiệm đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_LICH_SU_TRAC_NGHIEM.BULK_DELETE,
    body: { ids: selectedIds.value },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(fetchList)
</script>

<style scoped>
.sub-text {
  margin-top: 2px;
  color: var(--el-text-color-secondary);
  font-size: 12px;
}

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

.detail-table {
  width: 100%;
}

.json-box {
  margin: 0;
  padding: 12px 14px;
  max-height: 260px;
  overflow: auto;
  border: 1px solid var(--el-border-color);
  border-radius: 6px;
  background: var(--el-fill-color-light);
  font-size: 12px;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
