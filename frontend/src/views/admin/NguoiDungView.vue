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
                placeholder="Họ tên, email hoặc SĐT..."
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Giới tính">
              <CustomSelect
                v-model="filters.gioi_tinh"
                clearable
                class="filter-control"
                placeholder="Chọn giới tính"
              >
                <CustomOption
                  v-for="opt in gioiTinhOptions"
                  :key="opt"
                  :label="opt"
                  :value="opt"
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
        <h2>Danh sách người dùng</h2>
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
        <CustomTableColumn prop="ho_ten" label="Họ tên" min-width="160" show-overflow-tooltip />
        <CustomTableColumn prop="email" label="Email" min-width="200" show-overflow-tooltip />
        <CustomTableColumn prop="so_dien_thoai" label="Số điện thoại" width="140" />
        <CustomTableColumn prop="gioi_tinh" label="Giới tính" width="110" align="center">
          <template #default="{ row }">
            {{ row.gioi_tinh || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="ngay_sinh" label="Ngày sinh" width="120" align="center">
          <template #default="{ row }">
            {{ formatDate(row.ngay_sinh) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="dan_toc" label="Dân tộc" width="120" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.dan_toc || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Trình độ" min-width="140" show-overflow-tooltip>
          <template #default="{ row }">
            {{ trinhDoLabel(row.trinh_do_hoc_van) }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Ngày tạo" width="160" align="center">
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

    <CustomDialog v-model="detailVisible" title="Chi tiết người dùng" :width="820">
      <template v-if="detail">
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="Họ tên">{{ detail.ho_ten || '—' }}</el-descriptions-item>
          <el-descriptions-item label="Email">{{ detail.email || '—' }}</el-descriptions-item>
          <el-descriptions-item label="Số điện thoại">
            {{ detail.so_dien_thoai || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Giới tính">
            {{ detail.gioi_tinh || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Ngày sinh">
            {{ formatDate(detail.ngay_sinh) }}
          </el-descriptions-item>
          <el-descriptions-item label="Dân tộc">{{ detail.dan_toc || '—' }}</el-descriptions-item>
          <el-descriptions-item label="Tôn giáo">{{ detail.ton_giao || '—' }}</el-descriptions-item>
          <el-descriptions-item label="Ngày tạo">
            {{ formatDateTime(detail.created_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Trình độ học vấn</h3>
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="Trình độ">
            {{ trinhDoLabel(detail.trinh_do_hoc_van) }}
          </el-descriptions-item>
          <el-descriptions-item label="Trình độ khác">
            {{ detail.trinh_do_hoc_van?.trinh_do_khac || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Điểm học bạ">
            {{ detail.trinh_do_hoc_van?.diem_trung_binh_to_hop_mon?.diemHocBa ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Điểm thi THPT">
            {{ detail.trinh_do_hoc_van?.diem_trung_binh_to_hop_mon?.diemThiTHPT ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Chứng chỉ tiếng Anh" :span="2">
            {{ formatChungChi(detail.trinh_do_hoc_van?.chung_chi_tieng_anh) }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Sức khỏe thể chất</h3>
        <el-descriptions :column="3" border class="detail-block">
          <el-descriptions-item label="Chiều cao (cm)">
            {{ detail.suc_khoe_the_chat?.chieu_cao ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Cân nặng (kg)">
            {{ detail.suc_khoe_the_chat?.can_nang ?? '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Bệnh lý">
            {{ detail.suc_khoe_the_chat?.benh_ly || '—' }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Khả năng tài chính</h3>
        <el-descriptions :column="1" border class="detail-block">
          <el-descriptions-item label="Chi trả một năm học">
            {{ formatMoney(detail.kha_nang_tai_chinh?.chi_tra_mot_nam_hoc) }}
          </el-descriptions-item>
        </el-descriptions>

        <h3 class="detail-title">Vị trí địa lý</h3>
        <el-descriptions :column="2" border class="detail-block">
          <el-descriptions-item label="Khu vực muốn theo học">
            {{ khuVucLabel(detail.vi_tri_dia_ly?.khu_vuc_muon_theo_hoc) }}
          </el-descriptions-item>
          <el-descriptions-item label="Tỉnh/TP muốn theo học">
            {{ detail.vi_tri_dia_ly?.tinh_thanh_muon_theo_hoc || '—' }}
          </el-descriptions-item>
          <el-descriptions-item label="Tỉnh/TP đang sống" :span="2">
            {{ detail.vi_tri_dia_ly?.tinh_thanh_dang_song || '—' }}
          </el-descriptions-item>
        </el-descriptions>
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
import { API_NGUOI_DUNG } from '@/constants/constant_api'
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

const gioiTinhOptions = ['Nam', 'Nữ', 'Khác']

const trinhDoOptions = [
  { value: 'tot_nghiep_thpt', label: 'Tốt nghiệp THPT' },
  { value: 'tot_nghiep_thcs', label: 'Tốt nghiệp THCS' },
  { value: 'dang_hoc_thpt', label: 'Đang học THPT' },
  { value: 'trung_cap', label: 'Trung cấp' },
  { value: 'cao_dang', label: 'Cao đẳng' },
  { value: 'dai_hoc', label: 'Đại học' },
  { value: 'khac', label: 'Khác' },
]

const khuVucOptions = [
  { value: 'bac', label: 'Miền Bắc' },
  { value: 'trung', label: 'Miền Trung' },
  { value: 'nam', label: 'Miền Nam' },
]

const tableRef = ref(null)
const items = ref([])
const selectedRows = ref([])
const detailVisible = ref(false)
const detail = ref(null)

const filters = reactive({
  keyword: '',
  gioi_tinh: '',
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

function formatDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString('vi-VN')
}

function formatDateTime(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('vi-VN')
}

function formatMoney(value) {
  if (value === null || value === undefined || value === '') return '—'
  const num = Number(value)
  if (Number.isNaN(num)) return String(value)
  return `${num.toLocaleString('vi-VN')} đ`
}

function trinhDoLabel(trinhDo) {
  const code = trinhDo?.trinh_do_hoc_van
  if (!code) return '—'
  return trinhDoOptions.find((o) => o.value === code)?.label || code
}

function khuVucLabel(value) {
  if (!value) return '—'
  return khuVucOptions.find((o) => o.value === value)?.label || value
}

function formatChungChi(list) {
  if (!Array.isArray(list) || !list.length) return '—'
  const parts = list
    .map((item) => {
      const ten = item?.ten_chung_chi?.trim()
      const diem = item?.diem_chung_chi?.trim()
      if (!ten && !diem) return null
      return diem ? `${ten || '—'} (${diem})` : ten
    })
    .filter(Boolean)
  return parts.length ? parts.join(', ') : '—'
}

async function fetchList() {
  const q = filters.keyword.trim()
  const gioiTinh = filters.gioi_tinh || ''
  const params = {
    ...(q ? { q } : {}),
    ...(gioiTinh ? { gioi_tinh: gioiTinh } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_NGUOI_DUNG.LIST, params })

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
  const res = await request({ url: API_NGUOI_DUNG.SHOW(row.id) })
  if (!res.ok) return

  detail.value = res.data
  detailVisible.value = true
}

async function confirmRemove(row) {
  try {
    await ElMessageBox.confirm(
      `Xóa người dùng «${row.ho_ten}» (${row.email})? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_NGUOI_DUNG.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} người dùng đã chọn? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_NGUOI_DUNG.BULK_DELETE,
    body: { ids: selectedIds.value },
  })
  if (!res.ok) return

  await fetchList()
}

onMounted(fetchList)
</script>

<style scoped>
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
</style>
