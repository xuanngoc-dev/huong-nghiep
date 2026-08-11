<template>
  <section class="quiz-take" aria-labelledby="quiz-take-title">
    <header class="quiz-take__head">
      <p class="quiz-take__eyebrow">Bước {{ currentStep }} · {{ currentStepMeta?.label }}</p>
      <h1 id="quiz-take-title">{{ currentStepMeta?.label || 'Đang làm bài' }}</h1>
      <p class="quiz-take__lead">
        <template v-if="isLoaiStep">
          <template v-if="isReadOnly">
            Phiên khảo sát đã hoàn thành — bạn chỉ có thể xem lại đáp án, không thể chỉnh sửa.
          </template>
          <template v-else>
            Chọn mức độ phù hợp nhất với bạn cho từng câu hỏi
            ({{ questions.length }} câu — 2 câu ngẫu nhiên / nhóm ngành).
            Nhấn phím
            <kbd>1</kbd>–<kbd>5</kbd>
            để chọn nhanh,
            mũi tên để chuyển câu.
          </template>
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
            :class="{
              'is-highlight': highlightedQuestionId === question.id,
              'is-focused': focusedQuestionIndex === index,
              'is-shake': shakingQuestionId === question.id,
            }"
            tabindex="-1"
            :data-question-id="question.id"
            @focusin="focusedQuestionIndex = index"
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
                v-for="(answer, answerIndex) in answersOf(question)"
                :key="answer.id"
                type="button"
                class="quiz-take__answer"
                :class="{ 'is-selected': answers[question.id] === answer.id }"
                role="radio"
                :aria-checked="answers[question.id] === answer.id"
                :aria-keyshortcuts="String(answerIndex + 1)"
                :disabled="isReadOnly"
                @click="selectAnswer(question.id, answer.id)"
              >
                <span class="quiz-take__answer-stt" aria-hidden="true">{{ answerIndex + 1 }}.</span>
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
          <li>
            URL:
            <code>/trac-nghiem/{{ route.params.ssid }}/{{ currentStepMeta?.path }}</code>
          </li>
        </ul>
      </div>

      <div class="quiz-take__actions">
        <button class="btn btn-outline" type="button" :disabled="!prevStepMeta" @click="goPrev">
          Quay lại
        </button>
        <button
          class="btn"
          type="button"
          :disabled="!nextStepMeta || saving"
          @click="goNext"
        >
          {{ saving ? 'Đang lưu...' : nextButtonLabel }}
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useQuizStore } from '@/stores/quiz'

const route = useRoute()
const router = useRouter()
const quiz = useQuizStore()
const { answersByLoai, sessionCompleted } = storeToRefs(quiz)

const loading = ref(true)
const error = ref(null)
const assessment = ref(null)
const highlightedQuestionId = ref(null)
const focusedQuestionIndex = ref(-1)
const shakingQuestionId = ref(null)
const saving = ref(false)
/** Thứ tự đáp án đã xáo theo câu hỏi — tạo lại mỗi lần tải trang / đổi bước */
const shuffledAnswersByQuestionId = ref({})
const questionEls = new Map()
let highlightTimer = null
let shakeTimer = null
let focusBootTimer = null

const currentStepMeta = computed(() => quiz.findStepByRoute(route))
const currentStep = computed(() => currentStepMeta.value?.step || 1)
const prevStepMeta = computed(() => quiz.getAdjacent(route, -1))
const nextStepMeta = computed(() => quiz.getAdjacent(route, 1))
const isLoaiStep = computed(() => currentStepMeta.value?.type === 'loai')
const isLastQuestionStep = computed(() => nextStepMeta.value?.type === 'result')
const isReadOnly = computed(() => sessionCompleted.value)

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

function rawAnswersOf(question) {
  return question?.cau_tra_lois || question?.cauTraLois || []
}

function shuffleList(list) {
  const arr = Array.isArray(list) ? [...list] : []
  for (let i = arr.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  return arr
}

function rebuildShuffledAnswers(questionList) {
  const next = {}
  for (const question of questionList || []) {
    if (question?.id == null) continue
    next[question.id] = shuffleList(rawAnswersOf(question))
  }
  shuffledAnswersByQuestionId.value = next
}

function answersOf(question) {
  if (!question?.id) return []
  return shuffledAnswersByQuestionId.value[question.id] || rawAnswersOf(question)
}

function setQuestionEl(questionId, el) {
  if (el) questionEls.set(questionId, el)
  else questionEls.delete(questionId)
}

function selectAnswer(cauHoiId, cauTraLoiId) {
  if (isReadOnly.value) return

  const index = questions.value.findIndex((q) => q.id === cauHoiId)
  if (index >= 0) focusedQuestionIndex.value = index

  quiz.setAnswer(maLoai.value, cauHoiId, cauTraLoiId)
  if (highlightedQuestionId.value === cauHoiId) {
    highlightedQuestionId.value = null
  }
}

function findFirstUnanswered() {
  return questions.value.find((q) => answers.value[q.id] == null) || null
}

function shakeQuestion(questionId) {
  if (!questionId) return
  shakingQuestionId.value = null
  nextTick(() => {
    shakingQuestionId.value = questionId
    if (shakeTimer) window.clearTimeout(shakeTimer)
    shakeTimer = window.setTimeout(() => {
      if (shakingQuestionId.value === questionId) {
        shakingQuestionId.value = null
      }
    }, 420)
  })
}

function focusQuestion(index, { preferSelected = true } = {}) {
  const list = questions.value
  if (!list.length || index < 0 || index >= list.length) return false

  focusedQuestionIndex.value = index
  const question = list[index]
  const el = questionEls.get(question.id)
  if (!el) return false

  el.scrollIntoView({ behavior: 'smooth', block: 'center' })

  window.setTimeout(() => {
    const answerButtons = el.querySelectorAll('.quiz-take__answer')
    const selected = preferSelected
      ? el.querySelector('.quiz-take__answer.is-selected')
      : null
    const target = selected || answerButtons[0] || el
    target.focus?.({ preventScroll: true })
  }, 160)

  return true
}

function moveQuestionFocus(delta) {
  const list = questions.value
  if (!list.length) return

  const current = focusedQuestionIndex.value >= 0 ? focusedQuestionIndex.value : 0
  const next = current + delta

  if (next < 0) {
    shakeQuestion(list[0].id)
    focusQuestion(0)
    return
  }

  if (next >= list.length) {
    shakeQuestion(list[list.length - 1].id)
    focusQuestion(list.length - 1)
    return
  }

  focusQuestion(next)
}

function selectAnswerByNumber(number) {
  const index = focusedQuestionIndex.value >= 0 ? focusedQuestionIndex.value : 0
  const question = questions.value[index]
  if (!question) return

  const answerList = answersOf(question)
  const answer = answerList[number - 1]
  if (!answer) return

  selectAnswer(question.id, answer.id)

  nextTick(() => {
    const el = questionEls.get(question.id)
    el?.querySelectorAll('.quiz-take__answer')?.[number - 1]?.focus?.({ preventScroll: true })
  })
}

function focusFirstUnanswered() {
  const missing = findFirstUnanswered()
  if (!missing) return false

  const index = questions.value.findIndex((q) => q.id === missing.id)
  if (index < 0) return false

  highlightedQuestionId.value = missing.id
  focusQuestion(index, { preferSelected: false })

  if (highlightTimer) window.clearTimeout(highlightTimer)
  highlightTimer = window.setTimeout(() => {
    if (highlightedQuestionId.value === missing.id) {
      highlightedQuestionId.value = null
    }
  }, 2200)

  return true
}

function isTypingTarget(target) {
  if (!(target instanceof HTMLElement)) return false
  const tag = target.tagName
  return (
    tag === 'INPUT' ||
    tag === 'TEXTAREA' ||
    tag === 'SELECT' ||
    target.isContentEditable
  )
}

function onKeyDown(event) {
  if (!isLoaiStep.value || loading.value || !questions.value.length || isReadOnly.value) return
  if (event.altKey || event.ctrlKey || event.metaKey) return
  if (isTypingTarget(event.target)) return

  if (event.key >= '1' && event.key <= '5') {
    event.preventDefault()
    selectAnswerByNumber(Number(event.key))
    return
  }

  if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
    event.preventDefault()
    moveQuestionFocus(-1)
    return
  }

  if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
    event.preventDefault()
    moveQuestionFocus(1)
  }
}

function bootFocusFirstQuestion() {
  if (focusBootTimer) window.clearTimeout(focusBootTimer)
  focusBootTimer = window.setTimeout(() => {
    if (!isLoaiStep.value || loading.value || !questions.value.length) return
    focusQuestion(0, { preferSelected: false })
  }, 80)
}

async function loadStep() {
  loading.value = true
  error.value = null
  focusedQuestionIndex.value = -1
  shakingQuestionId.value = null

  try {
    quiz.syncSsidFromRoute(route)

    if (route.meta.requiresQuizSession && !route.params.ssid) {
      quiz.resetSession()
      await router.replace({ name: 'quiz-start' })
      return
    }

    const ok = await quiz.ensureLoaded()
    if (!ok) {
      error.value = quiz.error || 'Không tải được các bước trắc nghiệm.'
      return
    }

    if (route.params.ssid) {
      const history = await quiz.ensureHistoryLoaded(String(route.params.ssid))
      if (!history.ok) {
        quiz.resetSession()
        await router.replace({ name: 'quiz-start' })
        return
      }
    }

    if (route.name === 'quiz-loai' && !currentStepMeta.value) {
      await router.replace({ name: 'quiz-start' })
      return
    }

    if (isLoaiStep.value) {
      const result = await quiz.ensureQuestionsForLoai(maLoai.value)
      if (!result.ok) {
        // Thiếu / sai phiên → về bước 1 tạo ssid mới
        if (/phiên/i.test(String(result.message || ''))) {
          quiz.resetSession()
          await router.replace({ name: 'quiz-start' })
          return
        }
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
  router.push(quiz.toLocation(prevStepMeta.value, route.params.ssid))
}

async function goNext() {
  if (!nextStepMeta.value || saving.value) return

  const sessionId = route.params.ssid || quiz.ssid

  if (isLoaiStep.value) {
    if (!questions.value.length) return

    // Phiên đã hoàn thành: chỉ cho xem / chuyển bước, không lưu lại đáp án
    if (!isReadOnly.value) {
      if (!quiz.isLoaiComplete(maLoai.value)) {
        focusFirstUnanswered()
        return
      }

      saving.value = true
      error.value = null
      try {
        const saved = await quiz.saveLoaiAnswers(maLoai.value, sessionId)
        if (!saved.ok) {
          error.value = saved.message || 'Không lưu được câu trả lời.'
          return
        }
      } finally {
        saving.value = false
      }
    }
  } else if (currentStepMeta.value?.id) {
    quiz.markStepCompleted(currentStepMeta.value.id)
  }

  await router.push(quiz.toLocation(nextStepMeta.value, sessionId))
}

watch(
  () => [route.name, route.params?.ssid, route.params?.maLoaiCauHoi],
  () => {
    loadStep()
  },
  { immediate: true },
)

watch(
  () => [loading.value, isLoaiStep.value, questions.value.map((q) => q.id).join(',')],
  ([isLoading, isLoai, ids]) => {
    if (isLoading || !isLoai || !ids) {
      shuffledAnswersByQuestionId.value = {}
      return
    }
    rebuildShuffledAnswers(questions.value)
    nextTick(() => bootFocusFirstQuestion())
  },
)

onMounted(() => {
  window.addEventListener('keydown', onKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', onKeyDown)
  if (highlightTimer) window.clearTimeout(highlightTimer)
  if (shakeTimer) window.clearTimeout(shakeTimer)
  if (focusBootTimer) window.clearTimeout(focusBootTimer)
})
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

.quiz-take__lead kbd {
  display: inline-block;
  min-width: 1.15rem;
  padding: 0.05rem 0.35rem;
  border: 1px solid #c9d5ce;
  border-radius: 5px;
  background: #f4f7f5;
  color: var(--text);
  font-family: inherit;
  font-size: 0.82em;
  font-weight: 500;
  line-height: 1.3;
  text-align: center;
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

.quiz-take__question.is-focused {
  background: #f3faf6;
  box-shadow: inset 0 0 0 1.5px rgba(31, 122, 76, 0.28);
}

.quiz-take__question.is-highlight {
  background: #fff8e8;
  box-shadow: inset 0 0 0 1.5px #e2b35a;
}

.quiz-take__question.is-shake {
  animation: quiz-take-shake 0.42s ease;
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
  gap: 0.65rem;
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

.quiz-take__answer:focus-visible {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 2px rgba(31, 122, 76, 0.2);
}

.quiz-take__answer:disabled {
  cursor: not-allowed;
  opacity: 0.85;
}

.quiz-take__answer:disabled:hover {
  cursor: not-allowed;
  border-color: #d5ddd8;
  background: #fff;
}

.quiz-take__answer.is-selected:disabled:hover {
  border-color: var(--accent);
  background: var(--accent-soft);
}

.quiz-take__answer.is-selected {
  border-color: var(--accent);
  background: var(--accent-soft);
  box-shadow: 0 0 0 1px var(--accent);
}

.quiz-take__answer-stt {
  flex-shrink: 0;
  min-width: 1.15rem;
  color: var(--muted);
  font-weight: 500;
  font-variant-numeric: tabular-nums;
  line-height: 1.2;
}

.quiz-take__answer.is-selected .quiz-take__answer-stt {
  color: var(--accent);
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

@keyframes quiz-take-shake {
  0%,
  100% {
    transform: translateX(0);
  }
  20% {
    transform: translateX(-6px);
  }
  40% {
    transform: translateX(6px);
  }
  60% {
    transform: translateX(-4px);
  }
  80% {
    transform: translateX(4px);
  }
}

@media (prefers-reduced-motion: reduce) {
  .quiz-take__question.is-shake {
    animation: none;
    box-shadow: inset 0 0 0 1.5px rgba(31, 122, 76, 0.45);
  }
}

@media (min-width: 640px) {
  .quiz-take__answers {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
