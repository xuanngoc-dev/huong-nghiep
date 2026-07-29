<template>
  <section class="quiz-take" aria-labelledby="quiz-take-title">
    <header class="quiz-take__head">
      <p class="quiz-take__eyebrow">Bước {{ currentStep }} · {{ currentStepMeta?.label }}</p>
      <h1 id="quiz-take-title">{{ currentStepMeta?.label || 'Đang làm bài' }}</h1>
      <p class="quiz-take__lead">
        <template v-if="isLoaiStep">
          Chọn mức độ phù hợp nhất với bạn cho từng câu hỏi
          ({{ questions.length }} câu ngẫu nhiên trong nhóm này).
        </template>
        <template v-else>
          {{ assessment?.description || 'Hoàn thành phần câu hỏi của bước này rồi chuyển sang bước tiếp theo.' }}
        </template>
      </p>
    </header>

    <p v-if="loading" class="muted">Đang tải đề bài...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else class="quiz-take__panel">
      <template v-if="isLoaiStep">
        <p v-if="!questions.length" class="error-text">
          Chưa có câu hỏi khả dụng cho loại này.
        </p>

        <ol v-else class="quiz-take__questions">
          <li
            v-for="(question, index) in questions"
            :key="question.id"
            :ref="(el) => setQuestionEl(question.id, el)"
            class="quiz-take__question"
            :class="{ 'is-highlight': highlightedQuestionId === question.id }"
            :tabindex="answers[question.id] == null ? -1 : undefined"
            :data-question-id="question.id"
          >
            <p class="quiz-take__question-text">
              <span class="quiz-take__question-index">{{ index + 1 }}.</span>
              {{ question.noi_dung_cau_hoi }}
            </p>

            <div
              class="quiz-take__answers"
              role="radiogroup"
              :aria-label="`Đáp án câu ${index + 1}`"
            >
              <button
                v-for="answer in answersOf(question)"
                :key="answer.id"
                type="button"
                class="quiz-take__answer"
                :class="{ 'is-selected': answers[question.id] === answer.id }"
                role="radio"
                :aria-checked="answers[question.id] === answer.id"
                @click="selectAnswer(question.id, answer.id)"
              >
                <span class="quiz-take__radio" aria-hidden="true" />
                <span class="quiz-take__answer-label">{{ answer.noi_dung_cau_tra_loi }}</span>
              </button>
            </div>
          </li>
        </ol>

        <p class="quiz-take__progress muted">
          Đã trả lời {{ answeredCount }}/{{ questions.length }}
        </p>
      </template>

      <div v-else class="quiz-take__placeholder">
        <h2>Nội dung bước {{ currentStep }}</h2>
        <p>
          Đây là khung nội dung cho bước <strong>{{ currentStepMeta?.label }}</strong>.
          Phần câu hỏi và chọn đáp án sẽ được bổ sung theo nghiệp vụ từng loại.
        </p>
        <ul>
          <li>
            Section: <code>{{ currentStepMeta?.section || route.meta.quizSection || '—' }}</code>
          </li>
          <li>URL: <code>/trac-nghiem/{{ currentStepMeta?.path }}</code></li>
        </ul>
      </div>

      <div class="quiz-take__actions">
        <button class="btn btn-outline" type="button" :disabled="!prevStepMeta" @click="goPrev">
          Quay lại
        </button>
        <button
          class="btn"
          type="button"
          :disabled="!nextStepMeta"
          @click="goNext"
        >
          {{ nextButtonLabel }}
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useQuizStore, QUESTIONS_PER_LOAI } from '@/stores/quiz'

const route = useRoute()
const router = useRouter()
const quiz = useQuizStore()
const { answersByLoai } = storeToRefs(quiz)

const loading = ref(true)
const error = ref(null)
const assessment = ref(null)
const highlightedQuestionId = ref(null)
const questionEls = new Map()
let highlightTimer = null

const currentStepMeta = computed(() => quiz.findStepByRoute(route))
const currentStep = computed(() => currentStepMeta.value?.step || 1)
const prevStepMeta = computed(() => quiz.getAdjacent(route, -1))
const nextStepMeta = computed(() => quiz.getAdjacent(route, 1))
const isLoaiStep = computed(() => currentStepMeta.value?.type === 'loai')
const isLastQuestionStep = computed(() => nextStepMeta.value?.type === 'result')

const maLoai = computed(() => currentStepMeta.value?.maLoaiCauHoi || '')

const questions = computed(() => {
  if (!isLoaiStep.value) return []
  return quiz.getQuestions(maLoai.value)
})

const answers = computed(() => {
  if (!isLoaiStep.value) return {}
  // phụ thuộc answersByLoai để reactive
  void answersByLoai.value
  return quiz.getAnswers(maLoai.value)
})

const answeredCount = computed(() =>
  questions.value.filter((q) => answers.value[q.id] != null).length,
)

const nextButtonLabel = computed(() => {
  if (isLastQuestionStep.value) return 'Xem kết quả'
  if (nextStepMeta.value?.type === 'fields') return 'Chọn ngành phù hợp'
  return 'Bước tiếp theo'
})

function answersOf(question) {
  return question?.cau_tra_lois || question?.cauTraLois || []
}

function setQuestionEl(questionId, el) {
  if (el) questionEls.set(questionId, el)
  else questionEls.delete(questionId)
}

function selectAnswer(cauHoiId, cauTraLoiId) {
  quiz.setAnswer(maLoai.value, cauHoiId, cauTraLoiId)
  if (highlightedQuestionId.value === cauHoiId) {
    highlightedQuestionId.value = null
  }
}

function findFirstUnanswered() {
  return questions.value.find((q) => answers.value[q.id] == null) || null
}

function focusFirstUnanswered() {
  const missing = findFirstUnanswered()
  if (!missing) return false

  const el = questionEls.get(missing.id)
  if (!el) return false

  highlightedQuestionId.value = missing.id
  el.scrollIntoView({ behavior: 'smooth', block: 'center' })

  // Focus khối câu hỏi để người dùng biết cần trả lời
  window.setTimeout(() => {
    el.focus?.({ preventScroll: true })
    const firstAnswer = el.querySelector('.quiz-take__answer')
    firstAnswer?.focus?.({ preventScroll: true })
  }, 280)

  if (highlightTimer) window.clearTimeout(highlightTimer)
  highlightTimer = window.setTimeout(() => {
    if (highlightedQuestionId.value === missing.id) {
      highlightedQuestionId.value = null
    }
  }, 2200)

  return true
}

async function loadStep() {
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

    if (isLoaiStep.value) {
      const result = await quiz.ensureQuestionsForLoai(maLoai.value, {
        limit: QUESTIONS_PER_LOAI,
      })
      if (!result.ok) {
        error.value = result.message || 'Không tải được câu hỏi.'
        return
      }
      if (!result.questions?.length) {
        error.value = 'Chưa có câu hỏi khả dụng cho loại này.'
      }
      return
    }

    // Bước fields / khác — giữ placeholder
    assessment.value = null
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

  if (isLoaiStep.value) {
    if (!questions.value.length) return
    if (!quiz.isLoaiComplete(maLoai.value)) {
      focusFirstUnanswered()
      return
    }
    const payload = quiz.buildLoaiPayload(maLoai.value)
    console.log('[quiz] hoàn thành loại câu hỏi:', payload)
  } else if (currentStepMeta.value?.id) {
    quiz.markStepCompleted(currentStepMeta.value.id)
  }

  router.push(quiz.toLocation(nextStepMeta.value))
}

watch(
  () => [route.name, route.params?.maLoaiCauHoi],
  () => {
    loadStep()
  },
  { immediate: true },
)
</script>

<style scoped>
.quiz-take {
  margin: 0 auto;
}

.quiz-take__head {
  margin-bottom: 1.35rem;
}

.quiz-take__eyebrow {
  margin: 0 0 0.4rem;
  color: var(--accent);
  font-weight: 500;
  font-size: 0.9rem;
}

.quiz-take__head h1 {
  margin: 0 0 0.55rem;
  font-size: clamp(1.4rem, 2.8vw, 1.85rem);
  font-weight: 600;
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

.quiz-take__questions {
  margin: 0;
  padding: 0;
  list-style: none;
}

.quiz-take__question {
  padding: 1.1rem 0.65rem;
  margin: 0 -0.65rem;
  border-bottom: 1px solid var(--border);
  border-radius: 12px;
  outline: none;
  transition:
    background-color 0.2s ease,
    box-shadow 0.2s ease;
}

.quiz-take__question:first-child {
  padding-top: 0.15rem;
}

.quiz-take__question:last-child {
  border-bottom: 0;
}

.quiz-take__question.is-highlight {
  background: #fff8e8;
  box-shadow: inset 0 0 0 1.5px #e2b35a;
}

.quiz-take__question-text {
  margin: 0 0 0.85rem;
  color: var(--text);
  font-size: 1.02rem;
  font-weight: 500;
  line-height: 1.55;
}

.quiz-take__question-index {
  color: var(--accent);
  margin-right: 0.2rem;
}

.quiz-take__answers {
  display: grid;
  gap: 0.5rem;
}

.quiz-take__answer {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.7rem 0.9rem;
  border: 1px solid #d5ddd8;
  border-radius: 10px;
  background: #fff;
  color: var(--text);
  text-align: left;
  cursor: pointer;
  transition:
    border-color 0.15s ease,
    background-color 0.15s ease,
    box-shadow 0.15s ease;
}

.quiz-take__answer:hover {
  border-color: #9fbfab;
  background: #f7faf8;
}

.quiz-take__answer.is-selected {
  border-color: var(--accent);
  background: var(--accent-soft);
  box-shadow: 0 0 0 1px var(--accent);
}

.quiz-take__radio {
  flex-shrink: 0;
  width: 1.1rem;
  height: 1.1rem;
  border: 2px solid #b7c4bc;
  border-radius: 999px;
  background: #fff;
  box-shadow: inset 0 0 0 0 transparent;
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}

.quiz-take__answer.is-selected .quiz-take__radio {
  border-color: var(--accent);
  box-shadow: inset 0 0 0 0.28rem var(--accent);
}

.quiz-take__answer-label {
  flex: 1;
  line-height: 1.4;
  font-size: 0.95rem;
}

.quiz-take__progress {
  margin: 0.85rem 0 0;
  font-size: 0.9rem;
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
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
}

@media (min-width: 640px) {
  .quiz-take__answers {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
