<template>
  <section class="quiz-start" aria-labelledby="quiz-start-title">
    <p v-if="loading" class="muted quiz-start__status">Đang chuẩn bị bài trắc nghiệm...</p>
    <p v-else-if="error" class="error-text quiz-start__status">{{ error }}</p>

    <article v-else class="quiz-start__card">
      <header class="quiz-start__head">
        <h1 id="quiz-start-title">
          <span class="quiz-start__title-icon" aria-hidden="true">🎯</span>
          {{ intro.title }}
        </h1>
        <p v-for="(paragraph, index) in intro.paragraphs" :key="index" class="quiz-start__lead">
          {{ paragraph }}
        </p>
      </header>

      <section
        v-for="section in intro.sections"
        :key="section.id"
        class="quiz-start__section"
        :aria-labelledby="`quiz-start-${section.id}`"
      >
        <h2 :id="`quiz-start-${section.id}`" class="quiz-start__section-title">
          <span aria-hidden="true">{{ section.icon }}</span>
          {{ section.title }}
        </h2>

        <ul v-if="section.items?.length" class="quiz-start__list">
          <li v-for="(item, index) in section.items" :key="index">{{ item }}</li>
        </ul>

        <div v-if="section.showScale" class="quiz-start__scale" role="list" aria-label="Thang điểm đánh giá">
          <div
            v-for="level in intro.scale"
            :key="level.key"
            class="quiz-start__scale-item"
            role="listitem"
          >
            <span
              class="quiz-start__scale-box"
              :style="{ backgroundColor: level.color }"
            >
              {{ level.key }}
            </span>
            <span class="quiz-start__scale-points">{{ level.points }}</span>
          </div>
        </div>

        <p v-if="section.lead" class="quiz-start__section-lead">{{ section.lead }}</p>

        <ul v-if="section.scoreGuides?.length" class="quiz-start__list">
          <li v-for="guide in section.scoreGuides" :key="guide.range">
            <strong>{{ guide.range }}:</strong> {{ guide.text }}
          </li>
        </ul>

        <p v-if="section.body" class="quiz-start__section-body">{{ section.body }}</p>
      </section>

      <div class="quiz-start__actions">
        <button
          class="btn quiz-start__cta"
          type="button"
          :disabled="starting || !nextStep"
          @click="goNextStep"
        >
          {{ starting ? 'Đang mở...' : 'Bắt đầu' }}
        </button>
      </div>
    </article>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { quizStartIntro } from '@/data/quiz-start-intro'
import { useQuizStore } from '@/stores/quiz'

const quiz = useQuizStore()
const route = useRoute()
const router = useRouter()

const intro = quizStartIntro
const loading = ref(true)
const error = ref(null)
const starting = ref(false)

const nextStep = computed(() => quiz.getAdjacent(route, 1))

async function prepare() {
  loading.value = true
  error.value = null

  try {
    const ok = await quiz.ensureLoaded()
    if (!ok) {
      error.value = quiz.error || 'Không tải được các bước trắc nghiệm.'
      return
    }
    if (!nextStep.value) {
      error.value = 'Chưa có bước tiếp theo để bắt đầu làm bài.'
    }
  } catch {
    error.value = 'Không tải được bài trắc nghiệm.'
  } finally {
    loading.value = false
  }
}

async function goNextStep() {
  if (!nextStep.value) return
  starting.value = true
  try {
    // Mỗi lần bắt đầu làm bài: xáo lại bộ câu hỏi / đáp án phiên hiện tại
    quiz.resetSession()
    await router.push(quiz.toLocation(nextStep.value))
  } finally {
    starting.value = false
  }
}

onMounted(prepare)
</script>

<style scoped>
.quiz-start {
  margin: 0 auto;
}

.quiz-start__status {
  margin: 0;
}

.quiz-start__card {
  padding: clamp(1.35rem, 3vw, 2rem);
  background: #fff;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 4px);
  box-shadow: var(--shadow);
}

.quiz-start__head {
  margin-bottom: 1.5rem;
}

.quiz-start__head h1 {
  display: flex;
  align-items: flex-start;
  gap: 0.45rem;
  margin: 0 0 0.85rem;
  font-size: clamp(1.35rem, 2.8vw, 1.75rem);
  font-weight: 400;
  letter-spacing: -0.02em;
  line-height: 1.3;
  color: var(--text);
}

.quiz-start__title-icon {
  flex-shrink: 0;
  line-height: 1.25;
}

.quiz-start__lead {
  margin: 0 0 0.65rem;
  color: var(--text);
  line-height: 1.65;
}

.quiz-start__lead:last-child {
  margin-bottom: 0;
}

.quiz-start__section {
  margin-bottom: 1.35rem;
}

.quiz-start__section-title {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin: 0 0 0.65rem;
  font-size: 1.05rem;
  font-weight: 500;
  line-height: 1.4;
  color: var(--text);
}

.quiz-start__list {
  margin: 0;
  padding-left: 1.25rem;
  color: var(--text);
  line-height: 1.65;
}

.quiz-start__list li + li {
  margin-top: 0.35rem;
}

.quiz-start__section-lead,
.quiz-start__section-body {
  margin: 0 0 0.65rem;
  color: var(--text);
  line-height: 1.65;
}

.quiz-start__section-body {
  margin-bottom: 0;
}

.quiz-start__scale {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem 1.35rem;
  margin: 1rem 0 0.25rem;
}

.quiz-start__scale-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
  min-width: 3.25rem;
}

.quiz-start__scale-box {
  display: grid;
  place-items: center;
  width: 2.35rem;
  height: 2.35rem;
  border-radius: 6px;
  color: #fff;
  font-size: 1rem;
  font-weight: 500;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
}

.quiz-start__scale-points {
  color: var(--muted);
  font-size: 0.88rem;
  font-weight: 400;
  white-space: nowrap;
}

.quiz-start__actions {
  display: flex;
  justify-content: center;
  margin-top: 1.75rem;
  padding-top: 0.35rem;
}

.quiz-start__cta {
  min-width: 8.5rem;
  padding: 0.7rem 1.75rem;
  border-radius: 10px;
  font-weight: 500;
}
</style>
