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
                placeholder="Nội dung câu hỏi..."
                @keyup.enter="handleSearch"
              />
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Nhóm ngành">
              <CustomSelect
                v-model="filters.nhom_nganh_id"
                clearable
                filterable
                class="filter-control"
                placeholder="Chọn nhóm ngành"
              >
                <CustomOption
                  v-for="opt in nhomNganhOptions"
                  :key="opt.id"
                  :label="opt.ten_nhom_nganh"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol>
            <CustomFormItem label="Loại câu hỏi">
              <CustomSelect
                v-model="filters.loai_cau_hoi_id"
                clearable
                filterable
                class="filter-control"
                placeholder="Chọn loại câu hỏi"
              >
                <CustomOption
                  v-for="opt in loaiCauHoiOptions"
                  :key="opt.id"
                  :label="`${opt.ma_loai_cau_hoi} — ${opt.ten_loai_cau_hoi}`"
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
        <h2>Quản lý câu hỏi & đáp án</h2>
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

          <CustomTooltip content="Khóa các câu hỏi đang sử dụng" placement="top">
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

          <CustomTooltip content="Mở các câu hỏi đang ngừng sử dụng" placement="top">
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

          <CustomTooltip content="Thêm câu hỏi & đáp án" placement="top">
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
        @expand-change="onExpandChange"
      >
        <CustomTableColumn type="expand" width="48">
          <template #default="{ row }">
            <div class="expand-answers" v-loading="expandLoadingId === row.id">
              <div v-if="expandLoadingId === row.id" class="expand-answers__hint">
                Đang tải đáp án...
              </div>
              <div
                v-else-if="!(row.cau_tra_lois && row.cau_tra_lois.length)"
                class="expand-answers__hint"
              >
                Chưa có đáp án.
              </div>
              <CustomTable
                v-else
                :data="row.cau_tra_lois"
                row-key="id"
                border
                size="small"
                empty-text="Chưa có đáp án"
                class="expand-answers__table"
              >
                <CustomTableColumn label="STT" width="60" align="center">
                  <template #default="{ $index }">
                    {{ $index + 1 }}
                  </template>
                </CustomTableColumn>
                <CustomTableColumn label="Nội dung câu trả lời" min-width="360">
                  <template #default="{ row: answer }">
                    <CustomInput
                      v-if="editingAnswerId === answer.id"
                      v-model="answerDraft.noi_dung_cau_tra_loi"
                      placeholder="Nội dung câu trả lời"
                      @keyup.enter="saveInlineAnswer(row, answer)"
                    />
                    <span v-else class="expand-answers__text">{{ answer.noi_dung_cau_tra_loi }}</span>
                  </template>
                </CustomTableColumn>
                <CustomTableColumn label="Điểm" width="120" align="center">
                  <template #default="{ row: answer }">
                    <CustomInput
                      v-if="editingAnswerId === answer.id"
                      v-model="answerDraft.diem"
                      type="number"
                      :min="0"
                      :max="10"
                      placeholder="0-10"
                      @keyup.enter="saveInlineAnswer(row, answer)"
                    />
                    <span v-else>{{ answer.diem }}</span>
                  </template>
                </CustomTableColumn>
                <CustomTableColumn label="Thao tác" width="120" align="center">
                  <template #default="{ row: answer }">
                    <div v-if="editingAnswerId === answer.id" class="action-btns">
                      <CustomTooltip content="Lưu" placement="top">
                        <CustomButton
                          link
                          type="success"
                          :icon="Check"
                          :loading="answerSavingId === answer.id"
                          :disabled="answerSavingId === answer.id"
                          @click="saveInlineAnswer(row, answer)"
                        />
                      </CustomTooltip>
                      <CustomTooltip content="Hủy" placement="top">
                        <CustomButton
                          link
                          :icon="Close"
                          :disabled="answerSavingId === answer.id"
                          @click="cancelInlineAnswer"
                        />
                      </CustomTooltip>
                    </div>
                    <div v-else class="action-btns">
                      <CustomTooltip content="Sửa" placement="top">
                        <CustomButton
                          link
                          type="primary"
                          :icon="Edit"
                          :disabled="editingAnswerId !== null || answerDeletingId === answer.id"
                          @click="startInlineAnswer(answer)"
                        />
                      </CustomTooltip>
                      <CustomTooltip content="Xóa đáp án" placement="top">
                        <CustomButton
                          link
                          type="danger"
                          :icon="Delete"
                          :disabled="editingAnswerId !== null || answerDeletingId === answer.id"
                          @click="confirmRemoveAnswer(row, answer)"
                        />
                      </CustomTooltip>
                    </div>
                  </template>
                </CustomTableColumn>
              </CustomTable>
            </div>
          </template>
        </CustomTableColumn>
        <CustomTableColumn type="selection" width="48" align="center" />
        <CustomTableColumn label="STT" width="70" align="center">
          <template #default="{ $index }">
            {{ pagination.start + $index + 1 }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn prop="noi_dung_cau_hoi" label="Nội dung câu hỏi" min-width="240" show-overflow-tooltip />
        <CustomTableColumn label="Nhóm ngành" min-width="180" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.nhom_nganh?.ten_nhom_nganh || '—' }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Loại câu hỏi" min-width="140" show-overflow-tooltip>
          <template #default="{ row }">
            {{
              row.loai_cau_hoi
                ? `${row.loai_cau_hoi.ma_loai_cau_hoi} — ${row.loai_cau_hoi.ten_loai_cau_hoi}`
                : '—'
            }}
          </template>
        </CustomTableColumn>
        <CustomTableColumn label="Số đáp án" width="100" align="center">
          <template #default="{ row }">
            {{ row.cau_tra_lois_count ?? 0 }}
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
      :title="isEdit ? 'Sửa câu hỏi & đáp án' : 'Thêm câu hỏi & đáp án'"
      :width="1000"
    >
      <CustomForm ref="formRef" :model="form" :rules="rules">
        <CustomRow :gutter="16">
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Nhóm ngành" prop="nhom_nganh_id">
              <CustomSelect
                v-model="form.nhom_nganh_id"
                filterable
                placeholder="Chọn nhóm ngành"
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in nhomNganhOptions"
                  :key="opt.id"
                  :label="opt.ten_nhom_nganh"
                  :value="opt.id"
                />
              </CustomSelect>
            </CustomFormItem>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
            <CustomFormItem label="Loại câu hỏi" prop="loai_cau_hoi_id">
              <CustomSelect
                v-model="form.loai_cau_hoi_id"
                filterable
                placeholder="Chọn loại câu hỏi"
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in loaiCauHoiOptions"
                  :key="opt.id"
                  :label="`${opt.ma_loai_cau_hoi} — ${opt.ten_loai_cau_hoi}`"
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
          <CustomCol :span="24">
            <CustomFormItem label="Nội dung câu hỏi" prop="noi_dung_cau_hoi">
              <CustomInput
                v-model="form.noi_dung_cau_hoi"
                type="textarea"
                :rows="3"
                placeholder="Nhập nội dung câu hỏi"
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
              />
            </CustomFormItem>
          </CustomCol>
        </CustomRow>

        <div class="answers-section">
          <div class="answers-header">
            <h3>Câu trả lời</h3>
            <CustomButton type="primary" plain :icon="Plus" @click="addAnswerRow">
              Thêm đáp án
            </CustomButton>
          </div>

          <div v-if="!form.cau_tra_loi.length" class="answers-empty">
            Chưa có đáp án. Bấm «Thêm đáp án» để bổ sung.
          </div>

          <div
            v-for="(answer, index) in form.cau_tra_loi"
            :key="answer._key"
            class="answer-row"
          >
            <div class="answer-row__index">{{ index + 1 }}</div>
            <CustomFormItem
              :prop="`cau_tra_loi.${index}.noi_dung_cau_tra_loi`"
              :rules="answerContentRules"
              label="Nội dung"
              class="answer-row__content"
            >
              <CustomInput
                v-model="answer.noi_dung_cau_tra_loi"
                placeholder="Nội dung câu trả lời"
              />
            </CustomFormItem>
            <CustomFormItem
              :prop="`cau_tra_loi.${index}.diem`"
              :rules="answerScoreRules"
              label="Điểm"
              class="answer-row__score"
            >
              <CustomInput
                v-model="answer.diem"
                type="number"
                :min="0"
                :max="10"
                placeholder="0-10"
              />
            </CustomFormItem>
            <div class="answer-row__remove">
              <CustomTooltip content="Xóa đáp án" placement="top">
                <CustomButton
                  link
                  type="danger"
                  :icon="Delete"
                  @click="removeAnswerRow(index)"
                />
              </CustomTooltip>
            </div>
          </div>
        </div>
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
import { ElMessage, ElMessageBox } from 'element-plus'
import { Delete, Edit, Lock, Plus, Search, Unlock, Check, Close } from '@element-plus/icons-vue'
import { isRequestLoading, request } from '@/api'
import {
  API_LOAI_CAU_HOI,
  API_NHOM_NGANH,
  API_TRAC_NGHIEM_CAU_HOI,
  API_TRAC_NGHIEM_CAU_TRA_LOI,
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

let answerKeySeed = 0
function nextAnswerKey() {
  answerKeySeed += 1
  return `ans-${answerKeySeed}`
}

function createAnswerRow(partial = {}) {
  return {
    id: partial.id ?? null,
    _key: partial._key ?? nextAnswerKey(),
    noi_dung_cau_tra_loi: partial.noi_dung_cau_tra_loi ?? '',
    diem: partial.diem ?? 0,
  }
}

const tableRef = ref(null)
const items = ref([])
const nhomNganhOptions = ref([])
const loaiCauHoiOptions = ref([])
const selectedRows = ref([])
const statusLoadingId = ref(null)
const expandLoadingId = ref(null)
const answerDeletingId = ref(null)
const editingAnswerId = ref(null)
const answerSavingId = ref(null)
const answerDraft = reactive({
  noi_dung_cau_tra_loi: '',
  diem: 0,
})
const dialogVisible = ref(false)
const editingId = ref(null)
const originalAnswerIds = ref([])
const formRef = ref(null)

const filters = reactive({
  keyword: '',
  nhom_nganh_id: '',
  loai_cau_hoi_id: '',
  trang_thai: '',
})

const pagination = reactive({
  start: 0,
  limit: 10,
  total: 0,
})

const form = reactive({
  nhom_nganh_id: null,
  loai_cau_hoi_id: null,
  noi_dung_cau_hoi: '',
  ghi_chu: '',
  trang_thai: 'dang_su_dung',
  cau_tra_loi: [],
})

const rules = {
  nhom_nganh_id: [{ required: true, message: 'Vui lòng chọn nhóm ngành', trigger: 'change' }],
  loai_cau_hoi_id: [{ required: true, message: 'Vui lòng chọn loại câu hỏi', trigger: 'change' }],
  noi_dung_cau_hoi: [{ required: true, message: 'Vui lòng nhập nội dung câu hỏi', trigger: 'blur' }],
  trang_thai: [{ required: true, message: 'Vui lòng chọn trạng thái', trigger: 'change' }],
}

const answerContentRules = [
  { required: true, message: 'Vui lòng nhập nội dung đáp án', trigger: 'blur' },
]

const answerScoreRules = [
  { required: true, message: 'Vui lòng nhập điểm', trigger: 'blur' },
  {
    validator: (_rule, value, callback) => {
      if (value === '' || value === null || value === undefined) {
        callback()
        return
      }
      const num = Number(value)
      if (Number.isNaN(num)) {
        callback(new Error('Điểm không hợp lệ'))
        return
      }
      if (num < 0 || num > 10) {
        callback(new Error('Điểm phải nằm trong khoảng từ 0 đến 10'))
        return
      }
      callback()
    },
    trigger: ['blur', 'change'],
  },
]

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

async function onExpandChange(row, expandedRows) {
  const isExpanded = expandedRows.some((item) => item.id === row.id)
  if (!isExpanded) return
  if (Array.isArray(row.cau_tra_lois)) return

  expandLoadingId.value = row.id
  const res = await request({
    url: API_TRAC_NGHIEM_CAU_HOI.SHOW(row.id),
    loading: false,
    silent: true,
  })
  expandLoadingId.value = null

  if (!res.ok || !res.data) {
    row.cau_tra_lois = []
    return
  }

  row.cau_tra_lois = res.data.cau_tra_lois || []
  row.cau_tra_lois_count = row.cau_tra_lois.length
}

async function confirmRemoveAnswer(questionRow, answer) {
  if (editingAnswerId.value === answer.id) {
    cancelInlineAnswer()
  }

  try {
    await ElMessageBox.confirm(
      'Xóa đáp án này? Thao tác không thể hoàn tác.',
      'Xác nhận xóa đáp án',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  answerDeletingId.value = answer.id
  const res = await request({
    url: API_TRAC_NGHIEM_CAU_TRA_LOI.DELETE(answer.id),
    loading: false,
  })
  answerDeletingId.value = null
  if (!res.ok) return

  questionRow.cau_tra_lois = (questionRow.cau_tra_lois || []).filter((item) => item.id !== answer.id)
  questionRow.cau_tra_lois_count = questionRow.cau_tra_lois.length
}

function startInlineAnswer(answer) {
  editingAnswerId.value = answer.id
  answerDraft.noi_dung_cau_tra_loi = answer.noi_dung_cau_tra_loi ?? ''
  answerDraft.diem = answer.diem ?? 0
}

function cancelInlineAnswer() {
  editingAnswerId.value = null
  answerSavingId.value = null
  answerDraft.noi_dung_cau_tra_loi = ''
  answerDraft.diem = 0
}

async function saveInlineAnswer(questionRow, answer) {
  const noiDung = String(answerDraft.noi_dung_cau_tra_loi || '').trim()
  const diem = Number(answerDraft.diem)

  if (!noiDung) {
    ElMessage.warning('Vui lòng nhập nội dung đáp án.')
    return
  }
  if (Number.isNaN(diem)) {
    ElMessage.warning('Điểm không hợp lệ.')
    return
  }
  if (diem < 0 || diem > 10) {
    ElMessage.warning('Điểm phải nằm trong khoảng từ 0 đến 10.')
    return
  }

  answerSavingId.value = answer.id
  const res = await request({
    url: API_TRAC_NGHIEM_CAU_TRA_LOI.UPDATE(answer.id),
    body: {
      cau_hoi_id: questionRow.id,
      noi_dung_cau_tra_loi: noiDung,
      diem,
    },
    loading: false,
  })
  answerSavingId.value = null
  if (!res.ok) return

  answer.noi_dung_cau_tra_loi = res.data?.noi_dung_cau_tra_loi ?? noiDung
  answer.diem = res.data?.diem ?? diem
  cancelInlineAnswer()
}

function clearSelection() {
  selectedRows.value = []
  tableRef.value?.clearSelection?.()
}

function handleSearch() {
  pagination.start = 0
  fetchList()
}

function addAnswerRow() {
  form.cau_tra_loi.push(createAnswerRow())
}

function removeAnswerRow(index) {
  form.cau_tra_loi.splice(index, 1)
}

function resetForm() {
  form.nhom_nganh_id = null
  form.loai_cau_hoi_id = null
  form.noi_dung_cau_hoi = ''
  form.ghi_chu = ''
  form.trang_thai = 'dang_su_dung'
  form.cau_tra_loi = []
  editingId.value = null
  originalAnswerIds.value = []
}

function openCreate() {
  resetForm()
  form.cau_tra_loi = [createAnswerRow({ diem: 0 })]
  dialogVisible.value = true
}

async function openEdit(row) {
  resetForm()
  editingId.value = row.id

  const res = await request({
    url: API_TRAC_NGHIEM_CAU_HOI.SHOW(row.id),
  })
  if (!res.ok || !res.data) return

  const data = res.data
  form.nhom_nganh_id = data.nhom_nganh_id
  form.loai_cau_hoi_id = data.loai_cau_hoi_id
  form.noi_dung_cau_hoi = data.noi_dung_cau_hoi || ''
  form.ghi_chu = data.ghi_chu || ''
  form.trang_thai = data.trang_thai
  form.cau_tra_loi = (data.cau_tra_lois || []).map((item) =>
    createAnswerRow({
      id: item.id,
      noi_dung_cau_tra_loi: item.noi_dung_cau_tra_loi,
      diem: item.diem,
    }),
  )
  originalAnswerIds.value = form.cau_tra_loi.map((item) => item.id).filter(Boolean)
  dialogVisible.value = true
}

async function fetchOptions() {
  const [nhomRes, loaiRes] = await Promise.all([
    request({
      url: API_NHOM_NGANH.LIST,
      params: { start: 0, limit: 500, trang_thai: 'dang_su_dung' },
      loading: false,
      silent: true,
    }),
    request({
      url: API_LOAI_CAU_HOI.LIST,
      params: { start: 0, limit: 500, trang_thai: 'dang_su_dung' },
      loading: false,
      silent: true,
    }),
  ])

  nhomNganhOptions.value = nhomRes.ok ? (nhomRes.data ?? []) : []
  loaiCauHoiOptions.value = loaiRes.ok ? (loaiRes.data ?? []) : []
}

async function fetchList() {
  const q = filters.keyword.trim()
  const params = {
    ...(q ? { q } : {}),
    ...(filters.trang_thai ? { trang_thai: filters.trang_thai } : {}),
    ...(filters.nhom_nganh_id ? { nhom_nganh_id: filters.nhom_nganh_id } : {}),
    ...(filters.loai_cau_hoi_id ? { loai_cau_hoi_id: filters.loai_cau_hoi_id } : {}),
    start: pagination.start,
    limit: pagination.limit,
  }

  const res = await request({ url: API_TRAC_NGHIEM_CAU_HOI.LIST, params })

  if (!res.ok) {
    items.value = []
    pagination.total = 0
    clearSelection()
    cancelInlineAnswer()
    return
  }

  items.value = res.data ?? []
  pagination.total = res.total ?? 0
  clearSelection()
  cancelInlineAnswer()
}

async function toggleStatus(row, enabled) {
  const nextStatus = enabled ? 'dang_su_dung' : 'ngung_su_dung'
  if (row.trang_thai === nextStatus) return

  const prevStatus = row.trang_thai
  row.trang_thai = nextStatus
  statusLoadingId.value = row.id

  const res = await request({
    url: API_TRAC_NGHIEM_CAU_HOI.UPDATE(row.id),
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

function buildQuestionBody() {
  return {
    nhom_nganh_id: form.nhom_nganh_id,
    loai_cau_hoi_id: form.loai_cau_hoi_id,
    noi_dung_cau_hoi: form.noi_dung_cau_hoi.trim(),
    ghi_chu: form.ghi_chu?.trim() || null,
    trang_thai: form.trang_thai,
  }
}

async function saveAnswers(cauHoiId, { silentSuccess = true } = {}) {
  const currentIds = form.cau_tra_loi.map((item) => item.id).filter(Boolean)
  const removedIds = originalAnswerIds.value.filter((id) => !currentIds.includes(id))

  if (removedIds.length) {
    const delRes = await request({
      url: API_TRAC_NGHIEM_CAU_TRA_LOI.BULK_DELETE,
      body: { ids: removedIds },
      silentSuccess,
    })
    if (!delRes.ok) return false
  }

  for (const answer of form.cau_tra_loi) {
    const body = {
      cau_hoi_id: cauHoiId,
      noi_dung_cau_tra_loi: String(answer.noi_dung_cau_tra_loi || '').trim(),
      diem: Number(answer.diem),
    }

    if (answer.id) {
      const res = await request({
        url: API_TRAC_NGHIEM_CAU_TRA_LOI.UPDATE(answer.id),
        body,
        silentSuccess,
      })
      if (!res.ok) return false
    } else {
      const res = await request({
        url: API_TRAC_NGHIEM_CAU_TRA_LOI.CREATE,
        body,
        silentSuccess,
      })
      if (!res.ok) return false
    }
  }

  return true
}

async function submitForm() {
  const valid = await formRef.value?.validate().catch(() => false)
  if (!valid) return

  if (isEdit.value) {
    const questionRes = await request({
      url: API_TRAC_NGHIEM_CAU_HOI.UPDATE(editingId.value),
      body: buildQuestionBody(),
      silentSuccess: true,
    })
    if (!questionRes.ok) return

    const answersOk = await saveAnswers(editingId.value)
    if (!answersOk) return

    ElMessage.success('Cập nhật câu hỏi & đáp án thành công.')
    dialogVisible.value = false
    await fetchList()
    return
  }

  // Tạo câu hỏi trước, lấy id rồi mới tạo câu trả lời
  const questionRes = await request({
    url: API_TRAC_NGHIEM_CAU_HOI.CREATE,
    body: buildQuestionBody(),
    silentSuccess: true,
  })
  if (!questionRes.ok || !questionRes.data?.id) return

  const cauHoiId = questionRes.data.id
  const answersOk = await saveAnswers(cauHoiId)
  if (!answersOk) {
    ElMessage.warning('Đã tạo câu hỏi nhưng lưu đáp án chưa hoàn tất. Vui lòng sửa lại câu hỏi.')
    dialogVisible.value = false
    await fetchList()
    return
  }

  ElMessage.success('Tạo câu hỏi & đáp án thành công.')
  dialogVisible.value = false
  await fetchList()
}

async function confirmRemove(row) {
  try {
    await ElMessageBox.confirm(
      'Xóa câu hỏi này và toàn bộ đáp án liên quan? Thao tác không thể hoàn tác.',
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({ url: API_TRAC_NGHIEM_CAU_HOI.DELETE(row.id) })
  if (!res.ok) return

  await fetchList()
}

async function confirmBulkRemove() {
  if (!hasSelection.value) return

  try {
    await ElMessageBox.confirm(
      `Xóa ${selectedCount.value} câu hỏi đã chọn (kèm đáp án)? Thao tác không thể hoàn tác.`,
      'Xác nhận xóa',
      { type: 'warning', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_TRAC_NGHIEM_CAU_HOI.BULK_DELETE,
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
      `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} ${ids.length} câu hỏi (trạng thái: ${statusLabel})?`,
      `Xác nhận ${actionLabel}`,
      { type: 'warning', confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' },
    )
  } catch {
    return
  }

  const res = await request({
    url: API_TRAC_NGHIEM_CAU_HOI.BULK_STATUS,
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

<style scoped>
.expand-answers {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem 1.5rem 1rem;
  min-height: 48px;
}

.expand-answers__hint {
  color: var(--el-text-color-secondary);
  font-size: 0.9rem;
  padding: 0.35rem 0;
  width: 100%;
  max-width: 960px;
  text-align: center;
}

.expand-answers__table {
  width: 100%;
  max-width: 960px;
}

.expand-answers__text {
  display: inline-block;
  line-height: 1.5;
  word-break: break-word;
}

.answers-section {
  margin-top: 0.5rem;
  padding-top: 1rem;
  border-top: 1px solid var(--el-border-color-lighter);
}

.answers-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.answers-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.answers-empty {
  color: var(--el-text-color-secondary);
  font-size: 0.9rem;
  padding: 0.5rem 0 0.75rem;
}

.answer-row {
  display: grid;
  grid-template-columns: 32px minmax(0, 1fr) 88px 36px;
  gap: 0.5rem;
  align-items: end;
}

.answer-row__index,
.answer-row__content,
.answer-row__score,
.answer-row__remove {
  margin-bottom: 12px;
}

.answer-row__index,
.answer-row__remove {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 32px;
}

.answer-row__index {
  color: var(--el-text-color-secondary);
  font-weight: 600;
}

@media (min-width: 641px) {
  .answer-row {
    grid-template-columns: 40px minmax(0, 1fr) 120px 40px;
    gap: 0.75rem;
  }
}
</style>
