<template>
  <nav class="quiz-stepper" aria-label="Các bước làm trắc nghiệm">
    <ol class="quiz-stepper__list" :style="{ '--step-count': Math.max(steps.length, 1) }">
      <li
        v-for="item in steps"
        :key="item.id"
        class="quiz-stepper__item"
        :class="{
          'is-active': item.step === currentStep,
          'is-done': item.step < currentStep,
        }"
      >
        <span class="quiz-stepper__node" aria-hidden="true">{{ item.step }}</span>
        <span class="quiz-stepper__label">{{ item.label }}</span>
        <span v-if="item.step === currentStep" class="visually-hidden">
          (đang thực hiện)
        </span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
defineProps({
  steps: {
    type: Array,
    required: true,
  },
  currentStep: {
    type: Number,
    required: true,
  },
})
</script>

<style scoped>
.quiz-stepper {
  width: 100%;
  padding: 0.35rem 0 0.15rem;
}

.quiz-stepper__list {
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.35rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.quiz-stepper__list::before {
  content: '';
  position: absolute;
  top: 1rem;
  left: calc(50% / var(--step-count, 1));
  right: calc(50% / var(--step-count, 1));
  height: 1px;
  background: #d9e2dc;
  z-index: 0;
}

.quiz-stepper__item {
  position: relative;
  z-index: 1;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.55rem;
  min-width: 0;
  text-align: center;
}

.quiz-stepper__node {
  display: grid;
  place-items: center;
  width: 2rem;
  height: 2rem;
  border-radius: 999px;
  border: 1px solid #d5ddd8;
  background: #fff;
  color: #6b7c73;
  font-size: 0.92rem;
  font-weight: 600;
  box-shadow: 0 1px 2px rgba(26, 46, 36, 0.04);
}

.quiz-stepper__label {
  max-width: 7.5rem;
  color: #5c7268;
  font-size: clamp(0.68rem, 1.3vw, 0.82rem);
  font-weight: 500;
  line-height: 1.3;
}

.quiz-stepper__item.is-active .quiz-stepper__node {
  border-color: var(--accent);
  background: var(--accent);
  color: #fff;
  box-shadow: 0 4px 10px rgba(31, 122, 76, 0.22);
}

.quiz-stepper__item.is-active .quiz-stepper__label {
  color: var(--accent);
  font-weight: 700;
}

.quiz-stepper__item.is-done .quiz-stepper__node {
  border-color: var(--accent);
  background: var(--accent-soft);
  color: var(--accent);
}

.quiz-stepper__item.is-done .quiz-stepper__label {
  color: var(--text);
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (max-width: 767px) {
  .quiz-stepper__list {
    gap: 0.15rem;
  }

  .quiz-stepper__list::before {
    left: 1rem;
    right: 1rem;
  }

  .quiz-stepper__node {
    width: 1.7rem;
    height: 1.7rem;
    font-size: 0.8rem;
  }

  .quiz-stepper__label {
    max-width: 4.2rem;
    font-size: 0.62rem;
  }
}
</style>
