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
                placeholder="Tên, email, SĐT người nạp..."
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
        <h2>Lịch sử nạp coin</h2>
      </div>

      <CustomTable
        :data="items"
        row-key="id"
        border
        stripe
        empty-text="Chưa có dữ liệu"
      >
        <CustomTableColumn label="STT" width="70" align="center">
          <template #default="{ $index }">
            {{ pagination.start + $index + 1 }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Người nạp" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <div v-if="row.nguoi_nap">
              <div>{{ row.nguoi_nap.name || '—' }}</div>
              <div class="sub-text">{{ row.nguoi_nap.email || '' }}</div>
            </div>
            <span v-else>—</span>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Số coin" width="110" align="right">
          <template #default="{ row }">
            {{ formatNumber(row.so_luong_edu_coin) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Số tiền" width="130" align="right">
          <template #default="{ row }">
            {{ formatMoney(row.so_tien_nap) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Kênh" width="130" align="center">
          <template #default="{ row }">
            {{ row.kenh_thanh_toan_label || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn
          prop="ten_ngan_hang"
          label="Ngân hàng"
          min-width="160"
          show-overflow-tooltip
        />
        <CustomTableColumn label="Trạng thái" width="130" align="center">
          <template #default="{ row }">
            <CustomTag :type="trangThaiType(row.trang_thai)" effect="light" size="small">
              {{ row.trang_thai_label || '—' }}
            </CustomTag>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Người duyệt" min-width="150" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.nguoi_duyet?.name || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Thời gian" width="180" align="center">
          <template #default="{ row }">
            {{ formatDateTime(row.created_at) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Thao tác" width="140" fixed="right" align="center">
          <template #default="{ row }">
            <div class="action-btns">
              <CustomTooltip content="Xem chi tiết" placement="top">
                <CustomButton link type="primary" :icon="View" @click="openDetail(row)" />
              </CustomTooltip>
              <CustomTooltip
                v-if="row.trang_thai === 'cho_duyet'"
                content="Duyệt"
                placement="top"
              >
                <CustomButton
                  link
                  type="success"
                  :icon="CircleCheck"
                  @click="confirmDuyet(row)"
                />
              </CustomTooltip>
              <CustomTooltip
                v-if="row.trang_thai === 'cho_duyet'"
                content="Từ chối duyệt"
                placement="top"
              >
                <CustomButton
                  link
                  type="danger"
                  :icon="CircleClose"
                  @click="confirmHuyDuyet(row)"
                />
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

    <CustomDialog v-model="detailVisible" title="Chi tiết yêu cầu nạp coin" :width="720">
      <template v-if="detail">
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="Người nạp" :span="2">
            {{ detail.nguoi_nap?.name || '—' }}
            <span v-if="detail.nguoi_nap?.email" class="sub-text">
              ({{ detail.nguoi_nap.email }})
            </span>
          </el-descriptions-item>
          <el-descriptions-item label="Số điện thoại">
            {{ detail.nguoi_nap?.so_dien_thoai || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Trạng thái">
            <CustomTag :type="trangThaiType(detail.trang_thai)" effect="light" size="small">
              {{ detail.trang_thai_label || '—' }}
            </CustomTag>
          </el-descriptions-item>
          <el-descriptions-item label="Số Edu Coin">
            {{ formatNumber(detail.so_luong_edu_coin) }}
          </el-descriptions-item>
          <el-descriptions-item label="Số tiền nạp">
            {{ formatMoney(detail.so_tien_nap) }}
          </el-descriptions-item>
          <el-descriptions-item label="Kênh thanh toán">
            {{ detail.kenh_thanh_toan_label || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Ngân hàng">
            {{ detail.ten_ngan_hang || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Số tài khoản">
            {{ detail.thong_tin_thanh_toan?.so_tai_khoan || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Chủ tài khoản">
            {{ detail.thong_tin_thanh_toan?.chu_tai_khoan || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Nội dung CK" :span="2">
            {{ detail.thong_tin_thanh_toan?.noi_dung_chuyen_khoan || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Người duyệt">
            {{ detail.nguoi_duyet?.name || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Thời gian">
            {{ formatDateTime(detail.created_at) }}
          </el-descriptions-item>
          <el-descriptions-item label="Ghi chú" :span="2">
            {{ detail.ghi_chu || '—' }}
          </el-descriptions-item>
        </el-descriptions>
      </template>

      <template #footer>
        <CustomButton @click="detailVisible = false">Đóng</CustomButton>
        <CustomButton
          v-if="detail?.trang_thai === 'cho_duyet'"
          type="danger"
          :icon="CircleClose"
          @click="confirmHuyDuyet(detail)"
        >
          Từ chối
        </CustomButton>
        <CustomButton
          v-if="detail?.trang_thai === 'cho_duyet'"
          type="success"
          :icon="CircleCheck"
          @click="confirmDuyet(detail)"
        >
          Duyệt
        </CustomButton>
      </template>
    </CustomDialog>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ElMessageBox } from 'element-plus'
import { CircleCheck, CircleClose, Search, View } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import { API_LICH_SU_NAP_COIN } from '@/constants/constant_api'
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

const items = ref([])
const detailVisible = ref(false)
const detail = ref(null)

const trangThaiOptions = [
  { value: 'cho_duyet', label: 'Chờ duyệt' },
  { value: 'da_duyet', label: 'Đã duyệt' },
  { value: 'huy_duyet', label: 'Hủy duyệt' },
]

const filters = reactive({
  keyword: '',
  trang_thai: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

function handleSearch() {
  pagination.start = 0
  fetchList()
}

function trangThaiType(value) {
  if (value === 'da_duyet') return 'success'
  if (value === 'huy_duyet') return 'danger'
  return 'warning'
}

function formatDateTime(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('vi-VN')
}

function formatNumber(value) {
  const num = Number(value ?? 0)
  if (Number.isNaN(num)) return '0'
  return num.toLocaleString('vi-VN')
}

function formatMoney(value) {
  if (value === null || value === undefined || value === '') return '—'
  const num = Number(value)
  if (Number.isNaN(num)) return String(value)
  return `${num.toLocaleString('vi-VN')} đ`
}

function displayName(row) {
  return row?.nguoi_nap?.name || row?.nguoi_nap?.email || `#${row?.id}`
}

async function fetchList() {
  const q = filters.keyword.trim()
  const params = {
    ...(q ? { q } : {}),
    ...(filters.trang_thai ? { trang_thai: filters.trang_thai } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_LICH_SU_NAP_COIN.LIST, params })

  if (!res.ok) {
    items.value = []
    pagination.total = 0
    return
  }

  items.value = res.data ?? []
  pagination.total = res.total ?? 0
}

async function openDetail(row) {
  const res = await request({ url: API_LICH_SU_NAP_COIN.SHOW(row.id) })
  if (!res.ok) return

  detail.value = res.data
  detailVisible.value = true
}

async function confirmDuyet(row) {
  try {
    await ElMessageBox.confirm(
      `Duyệt yêu cầu nạp ${formatNumber(row.so_luong_edu_coin)} Edu Coin của «${displayName(row)}»? Coin sẽ được cộng vào tài khoản.`,
      'Xác nhận duyệt',
      { type: 'warning', confirmButtonText: 'Duyệt', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_LICH_SU_NAP_COIN.DUYET(row.id) })
  if (!res.ok) return

  if (detail.value?.id === row.id) {
    detail.value = res.data
  }
  await fetchList()
}

async function confirmHuyDuyet(row) {
  try {
    await ElMessageBox.confirm(
      `Từ chối yêu cầu nạp ${formatNumber(row.so_luong_edu_coin)} Edu Coin của «${displayName(row)}»?`,
      'Từ chối duyệt',
      { type: 'warning', confirmButtonText: 'Từ chối', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_LICH_SU_NAP_COIN.HUY_DUYET(row.id) })
  if (!res.ok) return

  if (detail.value?.id === row.id) {
    detail.value = res.data
  }
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

.detail-block {
  width: 100%;
}

.detail-block :deep(.el-descriptions__label) {
  width: 160px;
  font-weight: 500;
}
</style>
