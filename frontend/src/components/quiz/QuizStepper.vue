<template>
  <nav class="quiz-stepper" aria-label="Các bước làm trắc nghiệm">
    <ol class="quiz-stepper__list" :style="{ '--step-count': Math.max(steps.length, 1) }">
      <li
        v-for="item in steps"
        :key="item.id"
        class="quiz-stepper__item"
        :class="{
          'is-active': item.step === currentStep,
          'is-complete': isComplete(item),
        }"
      >
        <span class="quiz-stepper__node-wrap" aria-hidden="true">
          <span
            v-if="item.step === currentStep"
            class="quiz-stepper__ripple"
            aria-hidden="true"
          />
          <span
            v-if="item.step === currentStep"
            class="quiz-stepper__ripple quiz-stepper__ripple--delay"
            aria-hidden="true"
          />
          <span class="quiz-stepper__node">
            <template v-if="isComplete(item)">
              <svg
                class="quiz-stepper__check"
                viewBox="0 0 16 16"
                fill="none"
                aria-hidden="true"
              >
                <path
                  d="M3.2 8.3 6.4 11.5 12.8 4.5"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </template>
            <template v-else>{{ item.step }}</template>
          </span>
        </span>
        <span class="quiz-stepper__label">{{ item.label }}</span>
        <span v-if="item.step === currentStep" class="visually-hidden">
          (đang thực hiện)
        </span>
        <span v-else-if="isComplete(item)" class="visually-hidden">
          (đã trả lời)
        </span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
const props = defineProps({
  steps: {
    type: Array,
    required: true,
  },
  currentStep: {
    type: Number,
    required: true,
  },
  completedIds: {
    type: Array,
    default: () => [],
  },
})

function isComplete(item) {
  return props.completedIds.includes(item.id)
}
</script>

<style scoped>
.quiz-stepper {
  width: 100%;
  padding: 0.85rem 0 0.15rem;
  overflow: visible;
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
  top: 1.2rem;
  left: calc(50% / var(--step-count, 1));
  right: calc(50% / var(--step-count, 1));
  height: 1.5px;
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
  gap: 0.65rem;
  min-width: 0;
  text-align: center;
  overflow: visible;
}

.quiz-stepper__node-wrap {
  position: relative;
  display: grid;
  place-items: center;
  width: 2.4rem;
  height: 2.4rem;
  flex-shrink: 0;
}

.quiz-stepper__node {
  position: relative;
  z-index: 1;
  display: grid;
  place-items: center;
  width: 2.4rem;
  height: 2.4rem;
  border-radius: 999px;
  border: 1px solid #d5ddd8;
  background: #fff;
  color: #6b7c73;
  font-size: 1.02rem;
  font-weight: 500;
  box-shadow: 0 1px 2px rgba(26, 46, 36, 0.04);
  transition:
    border-color 0.25s ease,
    background 0.25s ease,
    color 0.25s ease,
    box-shadow 0.25s ease,
    transform 0.25s ease;
}

.quiz-stepper__check {
  width: 1.05rem;
  height: 1.05rem;
}

.quiz-stepper__ripple {
  position: absolute;
  inset: 0;
  border-radius: 999px;
  border: 1.5px solid rgba(31, 122, 76, 0.45);
  background: rgba(31, 122, 76, 0.08);
  pointer-events: none;
  animation: quiz-stepper-wave 2.2s ease-out infinite;
}

.quiz-stepper__ripple--delay {
  animation-delay: 1.1s;
}

.quiz-stepper__label {
  color: #5c7268;
  font-size: clamp(0.8rem, 1.55vw, 0.95rem);
  font-weight: 400;
  line-height: 1.35;
  transition: color 0.25s ease, font-weight 0.25s ease;
}

.quiz-stepper__item.is-complete .quiz-stepper__node {
  border-color: var(--accent);
  background: var(--accent-soft);
  color: var(--accent);
}

.quiz-stepper__item.is-complete .quiz-stepper__label {
  color: var(--text);
}

.quiz-stepper__item.is-active .quiz-stepper__node {
  border-color: var(--accent);
  background: var(--accent);
  color: #fff;
  box-shadow:
    0 0 0 3px rgba(31, 122, 76, 0.16),
    0 4px 14px rgba(31, 122, 76, 0.28);
  animation: quiz-stepper-pulse 2.2s ease-in-out infinite;
}

.quiz-stepper__item.is-active .quiz-stepper__label {
  color: var(--accent);
  font-weight: 500;
}

.quiz-stepper__item.is-active.is-complete .quiz-stepper__node {
  background: var(--accent);
  color: #fff;
}

@keyframes quiz-stepper-wave {
  0% {
    transform: scale(1);
    opacity: 0.7;
  }
  70% {
    opacity: 0.2;
  }
  100% {
    transform: scale(2.15);
    opacity: 0;
  }
}

@keyframes quiz-stepper-pulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.06);
  }
}

@media (prefers-reduced-motion: reduce) {
  .quiz-stepper__ripple,
  .quiz-stepper__item.is-active .quiz-stepper__node {
    animation: none;
  }

  .quiz-stepper__item.is-active .quiz-stepper__node {
    box-shadow:
      0 0 0 4px rgba(31, 122, 76, 0.18),
      0 4px 10px rgba(31, 122, 76, 0.22);
  }
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
    gap: 0.2rem;
  }

  .quiz-stepper__list::before {
    top: 1.05rem;
    left: 1.1rem;
    right: 1.1rem;
  }

  .quiz-stepper__node-wrap,
  .quiz-stepper__node {
    width: 2.1rem;
    height: 2.1rem;
  }

  .quiz-stepper__node {
    font-size: 0.92rem;
  }

  .quiz-stepper__check {
    width: 0.95rem;
    height: 0.95rem;
  }

  .quiz-stepper__label {
    max-width: 5rem;
    font-size: 0.74rem;
  }
}
</style>
