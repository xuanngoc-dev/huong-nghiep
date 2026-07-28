<template>
  <section class="quiz-start" aria-labelledby="quiz-start-title">
    <header class="quiz-start__head">
      <p class="quiz-start__eyebrow">Bước 1 · Bắt đầu</p>
      <h1 id="quiz-start-title">Sẵn sàng khám phá nghề nghiệp phù hợp?</h1>
      <p class="quiz-start__lead">
        Bài trắc nghiệm gồm nhiều phần theo từng loại câu hỏi. Hoàn thành lần lượt từng bước
        để nhận gợi ý ngành nghề phù hợp với bạn.
      </p>
    </header>

    <p v-if="loading" class="muted">Đang chuẩn bị bài trắc nghiệm...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else-if="assessment" class="quiz-start__card">
      <p class="quiz-start__badge">Trắc nghiệm hướng nghiệp</p>
      <h2>{{ assessment.name }}</h2>
      <p>{{ assessment.description }}</p>
      <ul class="quiz-start__points">
        <li>{{ assessment.question_count }} câu hỏi</li>
        <li>Thời gian khoảng 15–20 phút</li>
        <li>{{ contentStepCount }} bước nội dung + trang kết quả</li>
      </ul>
    </div>

    <div class="quiz-start__actions">
      <RouterLink class="btn btn-outline" :to="{ name: 'assessments' }">
        Quay lại giới thiệu
      </RouterLink>
      <button
        class="btn"
        type="button"
        :disabled="!assessmentId || starting || !nextStep"
        @click="goNextStep"
      >
        {{ starting ? 'Đang mở...' : 'Bắt đầu bước tiếp theo' }}
      </button>
    </div>

    <p v-if="!auth.isAuthenticated" class="quiz-start__note muted">
      Bạn có thể làm bài trước. Để lưu kết quả, hãy
      <RouterLink :to="{ name: 'login', query: { redirect: route.fullPath } }">đăng nhập</RouterLink>
      trước khi nộp bài.
    </p>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { assessmentApi } from '@/api'
import { useAuthStore } from '@/stores/auth'
import { useQuizStore } from '@/stores/quiz'

const auth = useAuthStore()
const quiz = useQuizStore()
const route = useRoute()
const router = useRouter()

const assessment = ref(null)
const loading = ref(true)
const error = ref(null)
const starting = ref(false)

const assessmentId = computed(() => assessment.value?.id || null)
const nextStep = computed(() => quiz.getAdjacent(route, 1))
const contentStepCount = computed(
  () => quiz.steps.filter((item) => item.type === 'loai' || item.type === 'fields').length,
)

async function loadAssessment() {
  loading.value = true
  error.value = null

  try {
    await quiz.ensureLoaded()
    const { data } = await assessmentApi.list()
    const list = data.data || []
    assessment.value = list[0] || null
    if (!assessment.value) {
      error.value = 'Chưa có bài trắc nghiệm khả dụng.'
    } else if (quiz.error) {
      error.value = quiz.error
    }
  } catch {
    error.value = 'Không tải được bài trắc nghiệm.'
  } finally {
    loading.value = false
  }
}

async function goNextStep() {
  if (!assessmentId.value || !nextStep.value) return
  starting.value = true
  try {
    await router.push(quiz.toLocation(nextStep.value))
  } finally {
    starting.value = false
  }
}

onMounted(loadAssessment)
</script>

<style scoped>
.quiz-start {
  max-width: 46rem;
  margin: 0 auto;
}

.quiz-start__head {
  margin-bottom: 1.35rem;
}

.quiz-start__eyebrow {
  margin: 0 0 0.4rem;
  color: var(--accent);
  font-weight: 600;
  font-size: 0.9rem;
}

.quiz-start__head h1 {
  margin: 0 0 0.65rem;
  font-size: clamp(1.55rem, 3vw, 2rem);
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.2;
}

.quiz-start__lead {
  margin: 0;
  color: var(--muted);
  line-height: 1.65;
}

.quiz-start__card {
  display: grid;
  gap: 0.55rem;
  margin-bottom: 1.5rem;
  padding: 1.25rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 2px);
  box-shadow: var(--shadow);
}

.quiz-start__badge {
  display: inline-flex;
  width: fit-content;
  margin: 0;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: var(--accent-soft);
  color: var(--accent);
  font-size: 0.75rem;
  font-weight: 700;
}

.quiz-start__card h2 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
}

.quiz-start__card > p {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.quiz-start__points {
  margin: 0.35rem 0 0;
  padding-left: 1.1rem;
  color: var(--text);
  line-height: 1.55;
}

.quiz-start__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-btn);
}

.quiz-start__note {
  margin: 1rem 0 0;
  line-height: 1.55;
}

.quiz-start__note a {
  color: var(--accent);
  font-weight: 600;
}
</style>
