<template>
  <section class="quiz-result" aria-labelledby="quiz-result-title">
    <header class="quiz-result__head">
      <p class="quiz-result__eyebrow">Bước {{ currentStep }} · Kết quả</p>
      <h1 id="quiz-result-title">{{ assessment?.name || 'Kết quả bài làm' }}</h1>
      <p class="quiz-result__lead">
        Trang kết quả sẽ hiển thị nhóm ngành phù hợp, điểm mạnh và gợi ý bước tiếp theo.
      </p>
    </header>

    <p v-if="loading" class="muted">Đang tải thông tin bài làm...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else class="quiz-result__panel">
      <div class="quiz-result__placeholder">
        <h2>Khung kết quả đã sẵn sàng</h2>
        <p>
          Logic chấm điểm, biểu đồ Holland/RIASEC và gợi ý ngành nghề sẽ được triển khai
          tại nhóm component quiz này.
        </p>
        <ul>
          <li>URL: <code>/trac-nghiem/ket-qua</code></li>
          <li>API nộp bài: <code>POST /assessments/{id}/submit</code></li>
        </ul>
      </div>

      <div class="quiz-result__actions">
        <RouterLink
          v-if="prevStep"
          class="btn btn-outline"
          :to="quiz.toLocation(prevStep)"
        >
          Quay lại bước trước
        </RouterLink>
        <RouterLink class="btn" :to="{ name: 'careers' }">
          Xem ngành nghề
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { assessmentApi } from '@/api'
import { useQuizStore } from '@/stores/quiz'

const route = useRoute()
const quiz = useQuizStore()

const assessment = ref(null)
const loading = ref(true)
const error = ref(null)

const currentStep = computed(() => quiz.resolveCurrentStep(route))
const prevStep = computed(() => quiz.getAdjacent(route, -1))

async function loadAssessment() {
  loading.value = true
  error.value = null

  try {
    await quiz.ensureLoaded()
    quiz.markStepCompleted('result')
    const { data } = await assessmentApi.list()
    assessment.value = data.data?.[0] || null
  } catch {
    error.value = 'Không tải được thông tin kết quả.'
  } finally {
    loading.value = false
  }
}

onMounted(loadAssessment)
</script>

<style scoped>
.quiz-result {
  max-width: 52rem;
  margin: 0 auto;
}

.quiz-result__head {
  margin-bottom: 1.35rem;
}

.quiz-result__eyebrow {
  margin: 0 0 0.4rem;
  color: var(--accent);
  font-weight: 500;
  font-size: 0.9rem;
}

.quiz-result__head h1 {
  margin: 0 0 0.55rem;
  font-size: clamp(1.4rem, 2.8vw, 1.85rem);
  font-weight: 600;
  letter-spacing: -0.03em;
}

.quiz-result__lead {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.quiz-result__panel {
  padding: 1.25rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 4px);
  box-shadow: var(--shadow);
}

.quiz-result__placeholder {
  padding: 1.15rem;
  margin-bottom: 1.25rem;
  border-radius: var(--radius);
  background: #f7faf8;
  border: 1px dashed #b7d4c4;
}

.quiz-result__placeholder h2 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
}

.quiz-result__placeholder p,
.quiz-result__placeholder li {
  color: var(--muted);
  line-height: 1.55;
}

.quiz-result__placeholder ul {
  margin: 0.75rem 0 0;
  padding-left: 1.1rem;
}

.quiz-result__placeholder code {
  font-size: 0.88em;
  color: var(--accent);
}

.quiz-result__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-btn);
}
</style>
