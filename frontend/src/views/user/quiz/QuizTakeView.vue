<template>
  <section class="quiz-take" aria-labelledby="quiz-take-title">
    <header class="quiz-take__head">
      <p class="quiz-take__eyebrow">Bước {{ currentStep }} · {{ currentStepMeta?.label }}</p>
      <h1 id="quiz-take-title">{{ currentStepMeta?.label || 'Đang làm bài' }}</h1>
      <p class="quiz-take__lead">
        {{ assessment?.description || 'Hoàn thành phần câu hỏi của bước này rồi chuyển sang bước tiếp theo.' }}
      </p>
    </header>

    <p v-if="loading" class="muted">Đang tải đề bài...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else class="quiz-take__panel">
      <div class="quiz-take__placeholder">
        <h2>Nội dung bước {{ currentStep }}</h2>
        <p>
          Đây là khung nội dung cho bước <strong>{{ currentStepMeta?.label }}</strong>.
          Phần câu hỏi và chọn đáp án sẽ được bổ sung theo nghiệp vụ từng loại.
        </p>
        <ul>
          <li v-if="currentStepMeta?.type === 'loai'">
            Mã loại: <code>{{ currentStepMeta.maLoaiCauHoi }}</code>
          </li>
          <li v-else>
            Section: <code>{{ currentStepMeta?.section || route.meta.quizSection || '—' }}</code>
          </li>
          <li>URL: <code>/trac-nghiem/{{ currentStepMeta?.path }}</code></li>
        </ul>
      </div>

      <div class="quiz-take__actions">
        <button class="btn btn-outline" type="button" :disabled="!prevStepMeta" @click="goPrev">
          Quay lại
        </button>
        <button class="btn" type="button" :disabled="!nextStepMeta" @click="goNext">
          {{ isLastQuestionStep ? 'Xem kết quả' : 'Bước tiếp theo' }}
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { assessmentApi } from '@/api'
import { useQuizStore } from '@/stores/quiz'

const route = useRoute()
const router = useRouter()
const quiz = useQuizStore()

const assessment = ref(null)
const loading = ref(true)
const error = ref(null)

const currentStepMeta = computed(() => quiz.findStepByRoute(route))
const currentStep = computed(() => currentStepMeta.value?.step || 1)
const prevStepMeta = computed(() => quiz.getAdjacent(route, -1))
const nextStepMeta = computed(() => quiz.getAdjacent(route, 1))
const isLastQuestionStep = computed(() => nextStepMeta.value?.type === 'result')

async function loadAssessment() {
  loading.value = true
  error.value = null

  try {
    const ok = await quiz.ensureLoaded()
    if (!ok) {
      error.value = quiz.error || 'Không tải được các bước trắc nghiệm.'
      return
    }

    if (route.name === 'quiz-loai' && !currentStepMeta.value) {
      error.value = 'Không tìm thấy loại câu hỏi tương ứng.'
      await router.replace({ name: 'quiz-start' })
      return
    }

    const { data } = await assessmentApi.list()
    assessment.value = data.data?.[0] || null
    if (!assessment.value) {
      error.value = 'Chưa có bài trắc nghiệm khả dụng.'
    }
  } catch {
    error.value = 'Không tải được bài trắc nghiệm.'
  } finally {
    loading.value = false
  }
}

function goPrev() {
  if (!prevStepMeta.value) return
  router.push(quiz.toLocation(prevStepMeta.value))
}

function goNext() {
  if (!nextStepMeta.value) return
  router.push(quiz.toLocation(nextStepMeta.value))
}

onMounted(loadAssessment)
</script>

<style scoped>
.quiz-take {
  max-width: 52rem;
  margin: 0 auto;
}

.quiz-take__head {
  margin-bottom: 1.35rem;
}

.quiz-take__eyebrow {
  margin: 0 0 0.4rem;
  color: var(--accent);
  font-weight: 600;
  font-size: 0.9rem;
}

.quiz-take__head h1 {
  margin: 0 0 0.55rem;
  font-size: clamp(1.4rem, 2.8vw, 1.85rem);
  font-weight: 800;
  letter-spacing: -0.03em;
}

.quiz-take__lead {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.quiz-take__panel {
  padding: 1.25rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 4px);
  box-shadow: var(--shadow);
}

.quiz-take__placeholder {
  padding: 1.15rem;
  margin-bottom: 1.25rem;
  border-radius: var(--radius);
  background: #f7faf8;
  border: 1px dashed #b7d4c4;
}

.quiz-take__placeholder h2 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
}

.quiz-take__placeholder p,
.quiz-take__placeholder li {
  color: var(--muted);
  line-height: 1.55;
}

.quiz-take__placeholder ul {
  margin: 0.75rem 0 0;
  padding-left: 1.1rem;
}

.quiz-take__placeholder code {
  font-size: 0.88em;
  color: var(--accent);
}

.quiz-take__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-btn);
}
</style>
