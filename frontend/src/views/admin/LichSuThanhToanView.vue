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
                placeholder="Tên, email, SĐT, SSID, mã GD..."
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Hình thức">
              <CustomSelect
                v-model="filters.hinh_thuc_thanh_toan"
                clearable
                class="filter-control"
                placeholder="Chọn hình thức"
              >
                <CustomOption
                  v-for="opt in hinhThucOptions"
                  :key="opt.value"
                  :label="opt.label"
                  :value="opt.value"
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
        <h2>Lịch sử thanh toán</h2>
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
        <CustomTableColumn label="Người dùng" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <div v-if="row.nguoi_dung">
              <div>{{ row.nguoi_dung.name || '—' }}</div>
              <div class="sub-text">{{ row.nguoi_dung.email || '' }}</div>
            </div>
            <span v-else>—</span>
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="ssid" label="SSID" min-width="180" show-overflow-tooltip />
        <CustomTableColumn
          prop="ma_giao_dich"
          label="Mã giao dịch"
          min-width="150"
          show-overflow-tooltip
        />
        <CustomTableColumn label="Hình thức" width="140" align="center">
          <template #default="{ row }">
            {{ row.hinh_thuc_thanh_toan_label || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Số tiền" width="140" align="right">
          <template #default="{ row }">
            {{ formatAmount(row) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn
          prop="ten_ngan_hang"
          label="Ngân hàng"
          min-width="160"
          show-overflow-tooltip
        />
        <CustomTableColumn label="Trạng thái" width="140" align="center">
          <template #default="{ row }">
            <CustomTag :type="trangThaiType(row.trang_thai)" effect="light" size="small">
              {{ row.trang_thai_label || '—' }}
            </CustomTag>
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Thời gian" width="180" align="center">
          <template #default="{ row }">
            {{ formatDateTime(row.created_at) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Thao tác" width="120" fixed="right" align="center">
          <template #default="{ row }">
            <div class="action-btns">
              <CustomTooltip content="Xem chi tiết" placement="top">
                <CustomButton link type="primary" :icon="View" @click="openDetail(row)" />
              </CustomTooltip>
              <CustomTooltip
                v-if="row.trang_thai === 'dang_xu_ly'"
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

    <CustomDialog v-model="detailVisible" title="Chi tiết thanh toán" :width="720">
      <template v-if="detail">
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="Người dùng" :span="2">
            {{ detail.nguoi_dung?.name || '—' }}
            <span v-if="detail.nguoi_dung?.email" class="sub-text">
              ({{ detail.nguoi_dung.email }})
            </span>
          </el-descriptions-item>
          <el-descriptions-item label="Số điện thoại">
            {{ detail.nguoi_dung?.so_dien_thoai || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Trạng thái">
            <CustomTag :type="trangThaiType(detail.trang_thai)" effect="light" size="small">
              {{ detail.trang_thai_label || '—' }}
            </CustomTag>
          </el-descriptions-item>
          <el-descriptions-item label="Mã giao dịch" :span="2">
            {{ detail.ma_giao_dich || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="SSID" :span="2">
            {{ detail.ssid || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Hình thức">
            {{ detail.hinh_thuc_thanh_toan_label || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Số tiền">
            {{ formatAmount(detail) }}
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
          <el-descriptions-item label="Thời gian" :span="2">
            {{ formatDateTime(detail.created_at) }}
          </el-descriptions-item>
        </el-descriptions>
      </template>

      <template #footer>
        <CustomButton @click="detailVisible = false">Đóng</CustomButton>
        <CustomButton
          v-if="detail?.trang_thai === 'dang_xu_ly'"
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
import { CircleCheck, Search, View } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import { API_LICH_SU_THANH_TOAN } from '@/constants/constant_api'
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

const hinhThucOptions = [
  { value: 'chuyen_khoan', label: 'Chuyển khoản' },
  { value: 'edu_coin', label: 'Edu Coin' },
]

const trangThaiOptions = [
  { value: 'dang_xu_ly', label: 'Đang xử lý' },
  { value: 'da_hoan_thanh', label: 'Đã hoàn thành' },
]

const filters = reactive({
  keyword: '',
  hinh_thuc_thanh_toan: '',
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
  if (value === 'da_hoan_thanh') return 'success'
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

function formatAmount(row) {
  const amount = Number(row?.so_tien_thanh_toan)
  if (!Number.isFinite(amount)) return '—'
  if (row.hinh_thuc_thanh_toan === 'edu_coin') {
    return `${formatNumber(amount)} coin`
  }
  return `${formatNumber(amount)} đ`
}

function displayName(row) {
  return row?.nguoi_dung?.name || row?.nguoi_dung?.email || `#${row?.id}`
}

async function fetchList() {
  const q = filters.keyword.trim()
  const params = {
    ...(q ? { q } : {}),
    ...(filters.hinh_thuc_thanh_toan ? { hinh_thuc_thanh_toan: filters.hinh_thuc_thanh_toan } : {}),
    ...(filters.trang_thai ? { trang_thai: filters.trang_thai } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_LICH_SU_THANH_TOAN.LIST, params })

  if (!res.ok) {
    items.value = []
    pagination.total = 0
    return
  }

  items.value = res.data ?? []
  pagination.total = res.total ?? 0
}

async function openDetail(row) {
  const res = await request({ url: API_LICH_SU_THANH_TOAN.SHOW(row.id) })
  if (!res.ok) return

  detail.value = res.data
  detailVisible.value = true
}

async function confirmDuyet(row) {
  try {
    await ElMessageBox.confirm(
      `Duyệt thanh toán của «${displayName(row)}»? Phiên trắc nghiệm sẽ được đánh dấu đã thanh toán.`,
      'Xác nhận duyệt',
      { type: 'warning', confirmButtonText: 'Duyệt', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_LICH_SU_THANH_TOAN.DUYET(row.id) })
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
