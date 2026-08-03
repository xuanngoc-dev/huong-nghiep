<template>
  <div class="quiz-fields-ket-qua">
    <p class="quiz-fields-ket-qua__note">
      Gợi ý: đây là 5 nhóm ngành và chuyên ngành phù hợp nhất với bạn.
    </p>

    <div class="quiz-fields-ket-qua__meta muted">
      <span>Đã chấm {{ summary?.so_cau_da_tra_loi ?? 0 }} câu</span>
      <span aria-hidden="true">·</span>
      <span>Tổng điểm phiên: {{ formatScore(summary?.tong_diem) }}</span>
    </div>

    <QuizFieldsRadarChart
      :nganh-list="nganhList"
      :chuyen-list="chuyenList"
    />

    <section
      v-if="topNganh || topChuyen"
      class="quiz-fields-ket-qua__highlight"
      aria-labelledby="quiz-fields-highlight-title"
    >
      <h2 id="quiz-fields-highlight-title" class="quiz-fields-ket-qua__highlight-title">
        Nhóm ngành phù hợp nhất với bạn
      </h2>

      <div v-if="topNganh" class="quiz-fields-ket-qua__highlight-block">
        <p class="quiz-fields-ket-qua__highlight-label">Nhóm ngành</p>
        <p class="quiz-fields-ket-qua__highlight-name">
          {{ topNganh.ten_nganh || `Ngành #${topNganh.nganh_hoc_id}` }}
          <span v-if="topNganh.ma_nganh" class="quiz-fields-ket-qua__highlight-code">
            ({{ topNganh.ma_nganh }})
          </span>
        </p>
        <p class="quiz-fields-ket-qua__highlight-score muted">
          Tổng điểm: {{ formatScore(topNganh.tong_diem) }}
          · {{ topNganh.so_cau ?? 0 }} câu liên quan
        </p>
        <p class="quiz-fields-ket-qua__highlight-desc">
          Đây là nhóm ngành bạn đạt điểm cao nhất trong phiên khảo sát.
          Kết quả cho thấy định hướng học tập và nghề nghiệp của bạn đang nghiêng về lĩnh vực này.
        </p>
      </div>

      <div v-if="topChuyen" class="quiz-fields-ket-qua__highlight-block">
        <p class="quiz-fields-ket-qua__highlight-label">Chuyên ngành nổi bật</p>
        <p class="quiz-fields-ket-qua__highlight-name">
          {{ topChuyen.ten_chuyen_nganh || `Chuyên ngành #${topChuyen.chuyen_nganh_id}` }}
          <span v-if="topChuyen.ma_chuyen_nganh" class="quiz-fields-ket-qua__highlight-code">
            ({{ topChuyen.ma_chuyen_nganh }})
          </span>
        </p>
        <p class="quiz-fields-ket-qua__highlight-score muted">
          Tổng điểm: {{ formatScore(topChuyen.tong_diem) }}
          · {{ topChuyen.so_cau ?? 0 }} câu liên quan
          <template v-if="topChuyen.ten_nganh">
            · thuộc nhóm {{ topChuyen.ten_nganh }}
          </template>
        </p>
        <p class="quiz-fields-ket-qua__highlight-desc">
          Đây là chuyên ngành cụ thể phù hợp nhất với câu trả lời của bạn.
          Bạn có thể ưu tiên tìm hiểu chương trình đào tạo và cơ hội nghề nghiệp gắn với chuyên ngành này.
        </p>
      </div>
    </section>

    <section
      class="quiz-fields-ket-qua__next"
      aria-labelledby="quiz-fields-next-title"
    >
      <h2 id="quiz-fields-next-title" class="quiz-fields-ket-qua__next-title">
        <span aria-hidden="true">🎯</span>
        Tiếp theo, bạn nên làm gì?
      </h2>

      <p class="quiz-fields-ket-qua__next-lead">
        <span aria-hidden="true">👉</span>
        <template v-if="topGroupLabel">
          Chọn ngành cụ thể trong nhóm
          <strong>“{{ topGroupLabel }}”</strong>
          để:
        </template>
        <template v-else>
          Chọn ngành cụ thể phù hợp với bạn để:
        </template>
      </p>

      <ul class="quiz-fields-ket-qua__next-list">
        <li>Xem danh sách các trường đại học đào tạo chuyên ngành.</li>
        <li>
          So sánh chỉ tiêu tuyển sinh năm gần nhất, Phương thức xét tuyển và điểm chuẩn đầu vào các chuyên ngành.
        </li>
      </ul>

      <div class="quiz-fields-ket-qua__actions">
        <button class="btn btn-outline" type="button" :disabled="!canGoPrev" @click="emit('prev')">
          Quay lại
        </button>
        <button class="btn" type="button" :disabled="!canGoNext" @click="emit('next')">
          Tiếp tục
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import QuizFieldsRadarChart from '@/components/quiz/QuizFieldsRadarChart.vue'

const TOP_LIMIT = 5

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

const nganhList = computed(() => {
  const list = Array.isArray(props.summary?.nganh_hoc) ? props.summary.nganh_hoc : []
  return list.slice(0, TOP_LIMIT)
})

const chuyenList = computed(() => {
  const list = Array.isArray(props.summary?.chuyen_nganh) ? props.summary.chuyen_nganh : []
  return list.slice(0, TOP_LIMIT)
})

const topNganh = computed(() => nganhList.value[0] || null)
const topChuyen = computed(() => chuyenList.value[0] || null)
const topGroupLabel = computed(() => {
  const name = topNganh.value?.ten_nganh?.trim()
  if (!name) return ''
  const code = topNganh.value?.ma_nganh?.trim()
  return code ? `${name} (${code})` : name
})

function formatScore(value) {
  const n = Number(value)
  if (!Number.isFinite(n)) return '0'
  return Number.isInteger(n) ? String(n) : n.toFixed(1)
}
</script>

<style scoped>
.quiz-fields-ket-qua__note {
  margin: 0 0 0.9rem;
  padding: 0.7rem 0.85rem;
  border-radius: 10px;
  background: #f3faf6;
  border: 1px solid #c5dfd0;
  color: #2f5d45;
  font-size: 0.92rem;
  line-height: 1.45;
}

.quiz-fields-ket-qua__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
  margin: 0 0 1.15rem;
  font-size: 0.92rem;
}

.quiz-fields-ket-qua__highlight {
  margin-top: 1.75rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}

.quiz-fields-ket-qua__highlight-title {
  margin: 0 0 1.1rem;
  font-size: clamp(1.15rem, 2.2vw, 1.35rem);
  font-weight: 600;
  letter-spacing: -0.02em;
  line-height: 1.35;
}

.quiz-fields-ket-qua__highlight-block + .quiz-fields-ket-qua__highlight-block {
  margin-top: 1.25rem;
  padding-top: 1.15rem;
  border-top: 1px dashed #dde5e0;
}

.quiz-fields-ket-qua__highlight-label {
  margin: 0 0 0.35rem;
  color: var(--accent);
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.quiz-fields-ket-qua__highlight-name {
  margin: 0 0 0.35rem;
  font-size: 1.08rem;
  font-weight: 600;
  line-height: 1.4;
  color: var(--text);
}

.quiz-fields-ket-qua__highlight-code {
  color: var(--muted);
  font-weight: 500;
}

.quiz-fields-ket-qua__highlight-score {
  margin: 0 0 0.65rem;
  font-size: 0.9rem;
}

.quiz-fields-ket-qua__highlight-desc {
  margin: 0;
  max-width: 42rem;
  color: var(--muted);
  font-size: 0.95rem;
  line-height: 1.6;
}

.quiz-fields-ket-qua__next {
  margin-top: 1.75rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}

.quiz-fields-ket-qua__next-title {
  margin: 0 0 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.45rem;
  font-size: clamp(1.2rem, 2.4vw, 1.45rem);
  font-weight: 700;
  letter-spacing: -0.02em;
  line-height: 1.35;
}

.quiz-fields-ket-qua__next-lead {
  margin: 0 0 0.85rem;
  display: flex;
  align-items: flex-start;
  gap: 0.4rem;
  max-width: 42rem;
  color: var(--text);
  font-size: 0.98rem;
  line-height: 1.55;
}

.quiz-fields-ket-qua__next-lead strong {
  font-weight: 600;
}

.quiz-fields-ket-qua__next-list {
  margin: 0;
  padding-left: 1.35rem;
  max-width: 42rem;
  color: var(--text);
  font-size: 0.95rem;
  line-height: 1.65;
}

.quiz-fields-ket-qua__next-list li + li {
  margin-top: 0.35rem;
}

.quiz-fields-ket-qua__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: var(--space-btn);
  margin-top: 1.75rem;
}
</style>
