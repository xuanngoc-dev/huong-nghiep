<template>
  <div class="quiz-layout">
    <header class="quiz-header">
      <div class="container quiz-header__inner">
        <RouterLink to="/" class="quiz-brand" aria-label="Hướng Nghiệp">
          <img
            class="quiz-brand__mark"
            src="/images/logos/logo-mark.svg"
            alt=""
            width="32"
            height="32"
          />
          <span class="quiz-brand__text">Hướng Nghiệp</span>
        </RouterLink>

        <div class="quiz-header__meta">
          <span v-if="title" class="quiz-header__title">{{ title }}</span>
          <RouterLink class="btn btn-outline quiz-header__exit" :to="exitTo">
            Thoát
          </RouterLink>
        </div>
      </div>
    </header>

    <div class="quiz-stepper-wrap">
      <div class="container">
        <QuizStepper :steps="steps" :current-step="currentStep" />
      </div>
    </div>

    <main class="quiz-main">
      <div class="container">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import QuizStepper from '@/components/quiz/QuizStepper.vue'
import { useQuizStore } from '@/stores/quiz'

const route = useRoute()
const quiz = useQuizStore()

const steps = computed(() => quiz.steps)
const currentStep = computed(() => quiz.resolveCurrentStep(route))
const currentMeta = computed(() => quiz.findStepByRoute(route))

const title = computed(() => route.meta.quizTitle || currentMeta.value?.label || '')
const exitTo = computed(() => route.meta.exitTo || { name: 'assessments' })

onMounted(() => {
  quiz.ensureLoaded()
})
</script>

<style scoped>
.quiz-layout {
  min-height: 100vh;
  display: grid;
  grid-template-rows: auto auto 1fr;
  background:
    radial-gradient(circle at top left, rgba(31, 122, 76, 0.08), transparent 40%),
    linear-gradient(180deg, #f7faf8 0%, var(--bg) 100%);
}

.quiz-header {
  position: sticky;
  top: 0;
  z-index: 20;
  background: rgba(247, 250, 248, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border);
}

.quiz-header__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  min-height: 3.25rem;
  padding-top: 0.55rem;
  padding-bottom: 0.55rem;
}

.quiz-brand {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  color: var(--text);
  line-height: 0;
}

.quiz-brand__mark {
  width: 2rem;
  height: 2rem;
}

.quiz-brand__text {
  font-weight: 700;
  font-size: 1.05rem;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.quiz-header__meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.quiz-header__title {
  display: none;
  max-width: 18rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--muted);
  font-size: 0.92rem;
  font-weight: 600;
}

.quiz-header__exit {
  padding: 0.4rem 0.9rem;
  font-size: 0.9rem;
  white-space: nowrap;
}

.quiz-stepper-wrap {
  padding: 1.15rem 0 0.35rem;
  background: rgba(247, 250, 248, 0.72);
  border-bottom: 1px solid rgba(215, 227, 220, 0.7);
}

.quiz-main {
  padding: calc(var(--space-section) * 1.5) 0 calc(var(--space-section) * 2);
}

@media (min-width: 640px) {
  .quiz-header__title {
    display: block;
  }
}

@media (max-width: 639px) {
  .quiz-brand__text {
    display: none;
  }

  .quiz-stepper-wrap {
    padding-top: 0.9rem;
  }
}
</style>
