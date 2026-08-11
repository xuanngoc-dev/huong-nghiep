<template>
  <div class="quiz-fields-nganh-uoc-mo">
    <p class="quiz-fields-nganh-uoc-mo__lead">
      <template v-if="topNhomLabel">
        Dựa trên nhóm ngành phù hợp nhất
        <strong>“{{ topNhomLabel }}”</strong>,
        hãy chọn ngành học bạn muốn tìm hiểu để xem các trường đại học, học viện
        tuyển sinh ngành đó năm {{ namTuyenSinh }}.
      </template>
      <template v-else>
        Chọn ngành học bạn đang mơ ước để đối chiếu với kết quả trắc nghiệm và xem
        danh sách trường tuyển sinh năm {{ namTuyenSinh }}.
      </template>
    </p>

    <p v-if="!topNhomId" class="quiz-fields-nganh-uoc-mo__empty muted">
      Chưa có nhóm ngành điểm cao nhất từ kết quả trắc nghiệm.
    </p>

    <template v-else>
      <p v-if="nganhLoading" class="muted">Đang tải danh sách ngành học...</p>
      <p v-else-if="nganhError" class="error-text">{{ nganhError }}</p>
      <p v-else-if="!nganhList.length" class="quiz-fields-nganh-uoc-mo__empty muted">
        Chưa có ngành học nào trong nhóm ngành này.
      </p>

      <div v-else class="quiz-fields-nganh-uoc-mo__cards" role="list">
        <button
          v-for="nganh in nganhList"
          :key="nganh.id"
          type="button"
          role="listitem"
          class="quiz-fields-nganh-uoc-mo__card"
          :class="{ 'is-selected': selectedNganhId === nganh.id }"
          :aria-pressed="selectedNganhId === nganh.id"
          @click="selectNganh(nganh)"
        >
          <span class="quiz-fields-nganh-uoc-mo__card-code" v-if="nganh.ma_nganh">
            {{ nganh.ma_nganh }}
          </span>
          <span class="quiz-fields-nganh-uoc-mo__card-name">{{ nganh.ten_nganh }}</span>
        </button>
      </div>

      <section
        v-if="!nganhLoading && !nganhError && nganhList.length"
        class="quiz-fields-nganh-uoc-mo__schools"
        aria-labelledby="quiz-fields-schools-title"
      >
        <h2 id="quiz-fields-schools-title" class="quiz-fields-nganh-uoc-mo__schools-title">
          Danh sách các trường đại học, học viện đào tạo chuyên ngành
        </h2>

        <template v-if="!selectedNganh">
          <div class="quiz-fields-nganh-uoc-mo__schools-waiting" role="status" aria-live="polite">
            <span class="quiz-fields-nganh-uoc-mo__spinner" aria-hidden="true" />
            <p class="quiz-fields-nganh-uoc-mo__schools-waiting-text">
              Chọn ngành học ở trên để hiển thị danh sách trường tuyển sinh...
            </p>
          </div>
        </template>

        <template v-else>
          <p class="quiz-fields-nganh-uoc-mo__schools-lead muted">
            Các trường có tuyển sinh ngành
            <strong>{{ selectedNganh.ten_nganh }}</strong>
            năm {{ namTuyenSinh }}.
          </p>

          <p v-if="truongLoading" class="muted">Đang tải danh sách trường...</p>
          <p v-else-if="truongError" class="error-text">{{ truongError }}</p>
          <p v-else-if="!truongRows.length" class="quiz-fields-nganh-uoc-mo__empty muted">
            Chưa có trường nào tuyển sinh ngành này vào năm {{ namTuyenSinh }}.
          </p>

          <div v-else class="quiz-fields-nganh-uoc-mo__table-wrap">
            <table class="quiz-fields-nganh-uoc-mo__table">
              <thead>
                <tr>
                  <th rowspan="2" scope="col">Tên trường</th>
                  <th rowspan="2" scope="col">Ngành đào tạo</th>
                  <th rowspan="2" scope="col">Chỉ tiêu [{{ namTuyenSinh }}]</th>
                  <th rowspan="2" scope="col">Phương thức xét tuyển</th>
                  <th :colspan="namDiemChuan.length" scope="colgroup">Điểm chuẩn</th>
                </tr>
                <tr>
                  <th
                    v-for="year in namDiemChuan"
                    :key="`diem-${year}`"
                    scope="col"
                  >
                    {{ year }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(row, index) in truongRows"
                  :key="`${row.ma_truong}-${row.chuyen_nganh_tuyen_sinh_id ?? 'x'}-${index}`"
                >
                  <td>{{ row.ten_truong || '—' }}</td>
                  <td>{{ row.ten_nganh_dao_tao || '—' }}</td>
                  <td class="is-num">{{ formatValue(row.chi_tieu) }}</td>
                  <td>{{ row.phuong_thuc_xet_tuyen || '—' }}</td>
                  <td
                    v-for="year in namDiemChuan"
                    :key="`${row.ma_truong}-diem-${year}`"
                    class="is-num"
                  >
                    {{ formatValue(row.diem_chuan?.[String(year)]) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <aside
            v-if="!truongLoading"
            class="quiz-fields-nganh-uoc-mo__next-step"
            aria-labelledby="quiz-fields-next-survey-title"
          >
            <h3 id="quiz-fields-next-survey-title" class="quiz-fields-nganh-uoc-mo__next-step-title">
              <span aria-hidden="true">📝</span>
              Bước tiếp theo: Hoàn thành bảng khảo sát cá nhân
            </h3>
            <p class="quiz-fields-nganh-uoc-mo__next-step-lead">
              Để biết mình phù hợp với trường đại học nào, hãy dành 2–3 phút để hoàn thành bảng
              khảo sát với các thông tin sau:
            </p>
            <ul class="quiz-fields-nganh-uoc-mo__next-step-list">
              <li>
                <span aria-hidden="true">✅</span>
                Điểm trung bình học bạ &amp; kỳ thi THPT
              </li>
              <li>
                <span aria-hidden="true">📍</span>
                Khu vực bạn muốn học (Bắc / Trung / Nam)
              </li>
              <li>
                <span aria-hidden="true">💰</span>
                Khả năng chi trả học phí
              </li>
              <li>
                <span aria-hidden="true">❤️</span>
                Tình trạng sức khỏe, ngoại hình (nếu ngành có yêu cầu đặc biệt)
              </li>
              <li>
                <span aria-hidden="true">👂</span>
                Tôn giáo (nếu muốn lọc trường phù hợp)
              </li>
            </ul>
            <p class="quiz-fields-nganh-uoc-mo__next-step-goal">
              <span aria-hidden="true">🥰</span>
              <span>
                <strong>Mục tiêu:</strong>
                Giúp bạn xác định rõ mức độ phù hợp của từng trường để đưa ra quyết định tốt nhất!
              </span>
            </p>
            <p class="quiz-fields-nganh-uoc-mo__next-step-cta">
              <span aria-hidden="true">⭐</span>
              Bắt đầu khảo sát ngay để cá nhân hóa kết quả cho bạn.
            </p>
          </aside>
        </template>
      </section>
    </template>

    <div class="quiz-fields-nganh-uoc-mo__actions">
      <button class="btn btn-outline" type="button" :disabled="!canGoPrev" @click="emit('prev')">
        Quay lại
      </button>
      <button class="btn" type="button" :disabled="!canGoNext" @click="emit('next')">
        Tiếp tục
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { request } from '@/api'
import { API_PUBLIC } from '@/constants/constant_api'

const props = defineProps({
  summary: {
    type: Object,
    default: null,
  },
  canGoPrev: {
    type: Boolean,
    default: false,
  },
  canGoNext: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['prev', 'next'])

const currentYear = new Date().getFullYear()
const defaultNamTuyenSinh = currentYear - 1
const defaultNamDiemChuan = [currentYear - 3, currentYear - 2, currentYear - 1]

const nganhList = ref([])
const nganhLoading = ref(false)
const nganhError = ref(null)

const selectedNganhId = ref(null)
const truongRows = ref([])
const truongLoading = ref(false)
const truongError = ref(null)
const apiNamTuyenSinh = ref(null)
const apiNamDiemChuan = ref([])

const namTuyenSinh = computed(() => apiNamTuyenSinh.value ?? defaultNamTuyenSinh)
const namDiemChuan = computed(() =>
  apiNamDiemChuan.value.length ? apiNamDiemChuan.value : defaultNamDiemChuan,
)

const topNhom = computed(() => {
  const list = Array.isArray(props.summary?.nhom_nganh) ? props.summary.nhom_nganh : []
  return list[0] || null
})

const topNhomId = computed(() => {
  const id = topNhom.value?.nhom_nganh_id
  return id != null && Number.isFinite(Number(id)) ? Number(id) : null
})

const topNhomLabel = computed(() => topNhom.value?.ten_nhom_nganh?.trim() || '')

const selectedNganh = computed(() => {
  if (selectedNganhId.value == null) return null
  return nganhList.value.find((item) => item.id === selectedNganhId.value) || null
})

function formatValue(value) {
  if (value === null || value === undefined || value === '') return '—'
  return value
}

async function loadNganhList(nhomId) {
  nganhList.value = []
  selectedNganhId.value = null
  truongRows.value = []
  truongError.value = null
  nganhError.value = null
  apiNamTuyenSinh.value = null
  apiNamDiemChuan.value = []

  if (!nhomId) return

  nganhLoading.value = true
  try {
    const res = await request({
      url: API_PUBLIC.NGANH_HOC.LIST,
      params: { nhom_nganh_id: nhomId },
      loading: false,
      silent: true,
    })

    if (!res.ok) {
      nganhError.value = res.message || 'Không tải được danh sách ngành học.'
      return
    }

    nganhList.value = Array.isArray(res.data) ? res.data : []
  } catch {
    nganhError.value = 'Không tải được danh sách ngành học.'
  } finally {
    nganhLoading.value = false
  }
}

async function loadTruongList(nganhId) {
  truongRows.value = []
  truongError.value = null
  apiNamTuyenSinh.value = null
  apiNamDiemChuan.value = []

  if (!nganhId) return

  truongLoading.value = true
  try {
    const res = await request({
      url: API_PUBLIC.NGANH_HOC.TRUONG_TUYEN_SINH(nganhId),
      params: { nam_tuyen_sinh: defaultNamTuyenSinh },
      loading: false,
      silent: true,
    })

    if (!res.ok) {
      truongError.value = res.message || 'Không tải được danh sách trường tuyển sinh.'
      return
    }

    const payload = res.data && typeof res.data === 'object' ? res.data : {}
    truongRows.value = Array.isArray(payload.items) ? payload.items : []
    if (payload.nam_tuyen_sinh != null) {
      apiNamTuyenSinh.value = Number(payload.nam_tuyen_sinh)
    }
    if (Array.isArray(payload.nam_diem_chuan) && payload.nam_diem_chuan.length) {
      apiNamDiemChuan.value = payload.nam_diem_chuan.map((y) => Number(y))
    }
  } catch {
    truongError.value = 'Không tải được danh sách trường tuyển sinh.'
  } finally {
    truongLoading.value = false
  }
}

function selectNganh(nganh) {
  if (!nganh?.id) return
  if (selectedNganhId.value === nganh.id) return
  selectedNganhId.value = nganh.id
  loadTruongList(nganh.id)
}

watch(
  topNhomId,
  (id) => {
    loadNganhList(id)
  },
  { immediate: true },
)
</script>

<style scoped>
.quiz-fields-nganh-uoc-mo__lead {
  margin: 0 0 1.15rem;
  color: var(--muted);
  font-size: 0.98rem;
  line-height: 1.55;
}

.quiz-fields-nganh-uoc-mo__lead strong {
  color: var(--text);
  font-weight: 600;
}

.quiz-fields-nganh-uoc-mo__empty {
  margin: 0;
  padding: 1.25rem 1rem;
  text-align: center;
  border: 1px dashed var(--border);
  border-radius: var(--radius);
  font-size: 0.95rem;
}

.quiz-fields-nganh-uoc-mo__cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 0.75rem;
}

.quiz-fields-nganh-uoc-mo__card {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.35rem;
  margin: 0;
  padding: 0.9rem 1rem;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 2px);
  background: #f8fbf9;
  color: var(--text);
  font: inherit;
  text-align: left;
  cursor: pointer;
  transition:
    border-color 0.18s ease,
    background-color 0.18s ease,
    box-shadow 0.18s ease;
}

.quiz-fields-nganh-uoc-mo__card:hover {
  border-color: #b7d4c4;
  background: #f1f8f4;
}

.quiz-fields-nganh-uoc-mo__card.is-selected {
  border-color: var(--accent);
  background: #eef8f2;
  box-shadow: 0 0 0 1px var(--accent);
}

.quiz-fields-nganh-uoc-mo__card:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.quiz-fields-nganh-uoc-mo__card-code {
  color: var(--accent);
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.quiz-fields-nganh-uoc-mo__card-name {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.4;
}

.quiz-fields-nganh-uoc-mo__schools {
  margin-top: 1.75rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}

.quiz-fields-nganh-uoc-mo__schools-title {
  margin: 0 0 0.55rem;
  font-size: clamp(1.05rem, 2.1vw, 1.25rem);
  font-weight: 600;
  letter-spacing: -0.02em;
  line-height: 1.35;
}

.quiz-fields-nganh-uoc-mo__schools-lead {
  margin: 0 0 1rem;
  font-size: 0.92rem;
  line-height: 1.5;
}

.quiz-fields-nganh-uoc-mo__schools-lead strong {
  color: var(--text);
  font-weight: 600;
}

.quiz-fields-nganh-uoc-mo__schools-waiting {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.85rem;
  min-height: 9rem;
  padding: 1.5rem 1rem;
  border: 1px dashed var(--border);
  border-radius: var(--radius);
  background: #f8fbf9;
  text-align: center;
}

.quiz-fields-nganh-uoc-mo__spinner {
  width: 1.65rem;
  height: 1.65rem;
  border: 2.5px solid #d5e6dc;
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: quiz-nganh-uoc-mo-spin 0.75s linear infinite;
}

.quiz-fields-nganh-uoc-mo__schools-waiting-text {
  margin: 0;
  max-width: 28rem;
  color: var(--muted);
  font-size: 0.95rem;
  line-height: 1.5;
}

@keyframes quiz-nganh-uoc-mo-spin {
  to {
    transform: rotate(360deg);
  }
}

.quiz-fields-nganh-uoc-mo__table-wrap {
  overflow-x: auto;
  border: 1px solid var(--border);
  border-radius: var(--radius);
}

.quiz-fields-nganh-uoc-mo__table {
  width: 100%;
  min-width: 720px;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.quiz-fields-nganh-uoc-mo__table th,
.quiz-fields-nganh-uoc-mo__table td {
  padding: 0.7rem 0.85rem;
  border-bottom: 1px solid var(--border);
  border-right: 1px solid #e8eee9;
  text-align: left;
  vertical-align: top;
  line-height: 1.4;
}

.quiz-fields-nganh-uoc-mo__table th:last-child,
.quiz-fields-nganh-uoc-mo__table td:last-child {
  border-right: 0;
}

.quiz-fields-nganh-uoc-mo__table thead th {
  background: #f4f7f5;
  color: var(--text);
  font-weight: 600;
  white-space: nowrap;
}

.quiz-fields-nganh-uoc-mo__table tbody tr:last-child td {
  border-bottom: 0;
}

.quiz-fields-nganh-uoc-mo__table .is-num {
  text-align: center;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}

.quiz-fields-nganh-uoc-mo__next-step {
  margin-top: 1.25rem;
  padding: 1.15rem 1.25rem;
  border-radius: calc(var(--radius) + 4px);
  background: #fff8e6;
  color: var(--text);
}

.quiz-fields-nganh-uoc-mo__next-step-title {
  margin: 0 0 0.65rem;
  display: flex;
  align-items: flex-start;
  gap: 0.4rem;
  font-size: 1.02rem;
  font-weight: 700;
  line-height: 1.4;
}

.quiz-fields-nganh-uoc-mo__next-step-lead {
  margin: 0 0 0.75rem;
  font-size: 0.95rem;
  line-height: 1.55;
}

.quiz-fields-nganh-uoc-mo__next-step-list {
  margin: 0 0 0.9rem;
  padding: 0;
  list-style: none;
  font-size: 0.95rem;
  line-height: 1.55;
}

.quiz-fields-nganh-uoc-mo__next-step-list li {
  display: flex;
  align-items: flex-start;
  gap: 0.45rem;
}

.quiz-fields-nganh-uoc-mo__next-step-list li + li {
  margin-top: 0.35rem;
}

.quiz-fields-nganh-uoc-mo__next-step-goal,
.quiz-fields-nganh-uoc-mo__next-step-cta {
  margin: 0;
  display: flex;
  align-items: flex-start;
  gap: 0.45rem;
  font-size: 0.95rem;
  line-height: 1.55;
}

.quiz-fields-nganh-uoc-mo__next-step-goal {
  margin-bottom: 0.45rem;
}

.quiz-fields-nganh-uoc-mo__next-step-goal strong {
  font-weight: 700;
}

.quiz-fields-nganh-uoc-mo__next-step-cta {
  font-weight: 600;
}

.error-text {
  margin: 0 0 0.75rem;
  color: #c0392b;
  font-size: 0.92rem;
}

.quiz-fields-nganh-uoc-mo__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: var(--space-btn);
  margin-top: 1.75rem;
}
</style>
