<template>
  <div class="assessments-page">

    <section class="fields" aria-labelledby="assessment-fields-title">
      <div class="fields__head">
        <p class="fields__eyebrow">Phạm vi định hướng</p>
        <h2 id="assessment-fields-title">Nhóm ngành trong trắc nghiệm</h2>
        <p class="fields__desc">
          Khám phá các nhóm ngành nghề được gợi ý sau khi hoàn thành bài trắc nghiệm.
        </p>
      </div>

      <div
        class="fields__slider"
        @mouseenter="pauseAutoplay"
        @mouseleave="resumeAutoplay"
        @focusin="pauseAutoplay"
        @focusout="resumeAutoplay"
      >
        <div class="fields__viewport" aria-live="polite">
          <Transition :name="slideTransition" mode="out-in">
            <article
              :key="activeField.id"
              class="field-card"
              :style="{ '--field-image': `url(${activeField.image})` }"
              :aria-label="activeField.alt || activeField.title"
            >
              <div class="field-card__copy">
                <p class="field-card__index">
                  {{ activeSlide + 1 }} / {{ fields.length }}
                </p>
                <h3>{{ activeField.title }}</h3>
                <p>{{ activeField.description }}</p>
              </div>
            </article>
          </Transition>

          <div class="fields__controls">
            <button
              type="button"
              class="fields__nav fields__nav--prev"
              aria-label="Nhóm ngành trước"
              @click="prevSlide"
            >
              <el-icon :size="16"><ArrowUp /></el-icon>
            </button>

            <div class="fields__dots" role="tablist" aria-label="Chọn nhóm ngành">
              <button
                v-for="(field, index) in fields"
                :key="field.id"
                type="button"
                class="fields__dot"
                :class="{ 'is-active': index === activeSlide }"
                :aria-label="field.title"
                :aria-selected="index === activeSlide"
                @click="selectSlide(index)"
              />
            </div>

            <button
              type="button"
              class="fields__nav fields__nav--next"
              aria-label="Nhóm ngành tiếp theo"
              @click="manualNext"
            >
              <el-icon :size="16"><ArrowDown /></el-icon>
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="intro" aria-labelledby="assessments-intro-title">
      <h1 id="assessments-intro-title" class="intro__title">{{ intro.title }}</h1>
      <div class="intro__body">
        <p class="intro__lead">{{ intro.lead }}</p>

        <h2 class="intro__section-title">{{ intro.sectionTitle }}</h2>
        <p class="intro__text">{{ intro.body }}</p>
        <p class="intro__text">{{ intro.benefitsLead }}</p>

        <ul class="intro__benefits">
          <li v-for="item in intro.benefits" :key="item">{{ item }}</li>
        </ul>

        <button class="btn intro__cta" type="button" @click="startQuiz">
          {{ intro.cta }}
        </button>
      </div>
    </section>
    <section id="assessment-list" class="list" aria-labelledby="assessment-list-title">
      <div class="list__head">
        <p class="list__eyebrow">Bắt đầu ngay</p>
        <h2 id="assessment-list-title">Chọn bài trắc nghiệm</h2>
      </div>

      <p v-if="loading" class="muted">Đang tải...</p>
      <p v-else-if="error" class="error-text">{{ error }}</p>

      <div v-else class="row g-3">
        <div
          v-for="item in assessments"
          :key="item.id"
          class="col-12 col-md-6"
        >
          <article class="assessment-card">
            <h3>{{ item.name }}</h3>
            <p class="muted">{{ item.description }}</p>
            <p class="assessment-card__meta">{{ item.question_count }} câu hỏi</p>
            <button class="btn assessment-card__cta" type="button" @click="startQuiz">
              Làm bài này
            </button>
          </article>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowDown, ArrowUp } from '@element-plus/icons-vue'
import { assessmentApi } from '@/api'
import { assessmentsIntro } from '@/data/assessments'
import assessmentFields from '@/data/assessment-fields.json'

const router = useRouter()
const intro = assessmentsIntro
const fields = assessmentFields

const assessments = ref([])
const loading = ref(true)
const error = ref(null)

const activeSlide = ref(0)
const slideDirection = ref('down')
let autoplayTimer = 0
let resumeTimer = 0

const activeField = computed(() => fields[activeSlide.value])
const slideTransition = computed(() =>
  slideDirection.value === 'down' ? 'slide-down' : 'slide-up',
)

function startQuiz() {
  router.push({ name: 'quiz-start' })
}

function goToSlide(index, direction) {
  const max = fields.length - 1
  const next = ((index % fields.length) + fields.length) % fields.length

  if (next === activeSlide.value && !direction) return

  if (direction) {
    slideDirection.value = direction
  } else if (next > activeSlide.value || (activeSlide.value === max && next === 0)) {
    slideDirection.value = 'down'
  } else {
    slideDirection.value = 'up'
  }

  activeSlide.value = next
}

function nextSlide() {
  goToSlide(activeSlide.value + 1, 'down')
}

function manualNext() {
  nextSlide()
  restartAutoplaySoon()
}

function prevSlide() {
  goToSlide(activeSlide.value - 1, 'up')
  restartAutoplaySoon()
}

function selectSlide(index) {
  goToSlide(index)
  restartAutoplaySoon()
}

function startAutoplay() {
  stopAutoplay()
  autoplayTimer = window.setInterval(() => {
    nextSlide()
  }, 4000)
}

function stopAutoplay() {
  if (autoplayTimer) {
    clearInterval(autoplayTimer)
    autoplayTimer = 0
  }
}

function pauseAutoplay() {
  stopAutoplay()
  if (resumeTimer) clearTimeout(resumeTimer)
}

function resumeAutoplay() {
  if (resumeTimer) clearTimeout(resumeTimer)
  resumeTimer = window.setTimeout(() => {
    startAutoplay()
  }, 600)
}

function restartAutoplaySoon() {
  pauseAutoplay()
  resumeAutoplay()
}

onMounted(async () => {
  startAutoplay()

  try {
    const { data } = await assessmentApi.list()
    assessments.value = data.data
  } catch {
    error.value = 'Không tải được danh sách trắc nghiệm.'
  } finally {
    loading.value = false
  }
})

onBeforeUnmount(() => {
  stopAutoplay()
  if (resumeTimer) clearTimeout(resumeTimer)
})
</script>

<style scoped>
.assessments-page {
  display: flex;
  flex-direction: column;
  gap: calc(var(--space-section) * 2);
  padding-bottom: var(--space-section);
}

.intro {
  padding: clamp(1.5rem, 3vw, 2.5rem) 0;
  background:
    radial-gradient(circle at top right, rgba(31, 122, 76, 0.08), transparent 42%),
    linear-gradient(180deg, #f7faf8 0%, #eef5f1 100%);
  border-radius: calc(var(--radius) + 6px);
}

.intro__title {
  margin: 0 0 1.35rem;
  text-align: center;
  font-size: clamp(1.75rem, 3.5vw, 2.35rem);
  font-weight: 600;
  letter-spacing: -0.03em;
  color: var(--text);
}

.intro__body {
  margin: 0 auto;
  padding: 0 clamp(1rem, 3vw, 1.5rem);
}

.intro__lead,
.intro__text {
  margin: 0 0 1rem;
  color: var(--text);
  font-size: 1rem;
  line-height: 1.7;
}

.intro__section-title {
  margin: 1.5rem 0 0.85rem;
  font-size: clamp(1.05rem, 2vw, 1.2rem);
  font-weight: 500;
  line-height: 1.45;
  color: var(--text);
}

.intro__benefits {
  margin: 0 0 1.5rem;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.65rem;
}

.intro__benefits li {
  position: relative;
  padding-left: 1.15rem;
  color: var(--text);
  line-height: 1.55;
}

.intro__benefits li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.55em;
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 50%;
  background: var(--accent);
}

.intro__cta {
  border-radius: 10px;
  padding: 0.7rem 1.35rem;
  font-weight: 500;
}

.fields__head,
.list__head {
  max-width: 36rem;
  margin: 0 auto 1.5rem;
  text-align: center;
}

.fields__eyebrow,
.list__eyebrow {
  margin: 0 0 0.4rem;
  color: var(--accent);
  font-weight: 500;
  font-size: 0.9rem;
}

.fields__head h2,
.list__head h2 {
  margin: 0 0 0.55rem;
  font-size: clamp(1.35rem, 2.8vw, 1.85rem);
  font-weight: 600;
  letter-spacing: -0.03em;
}

.fields__desc {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.fields__slider {
  margin: 0 auto;
}

.fields__viewport {
  position: relative;
  width: 100%;
  min-height: clamp(15rem, 32vw, 20rem);
  overflow: hidden;
  border-radius: calc(var(--radius) + 6px);
  box-shadow: 0 16px 36px rgba(26, 46, 36, 0.12);
}

.field-card {
  position: relative;
  isolation: isolate;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: clamp(15rem, 32vw, 20rem);
  padding: clamp(1.25rem, 3vw, 2rem);
  padding-right: 4.25rem;
  color: #fff;
  overflow: hidden;
  background-color: #0f3d28;
  background-image:
    linear-gradient(
      90deg,
      rgba(10, 42, 28, 0.96) 0%,
      rgba(10, 42, 28, 0.88) 28%,
      rgba(10, 42, 28, 0.45) 55%,
      rgba(10, 42, 28, 0.12) 72%,
      transparent 86%
    ),
    var(--field-image);
  background-size: cover;
  background-position: center right;
  background-repeat: no-repeat;
}

.field-card__copy {
  position: relative;
  z-index: 1;
  max-width: 28rem;
  display: grid;
  gap: 0.45rem;
  align-content: center;
}

.field-card__index {
  margin: 0;
  color: rgba(255, 255, 255, 0.78);
  font-size: 0.8rem;
  font-weight: 500;
  letter-spacing: 0.04em;
}

.field-card h3 {
  margin: 0;
  font-size: clamp(1.25rem, 2.6vw, 1.75rem);
  font-weight: 600;
  letter-spacing: -0.02em;
  line-height: 1.25;
}

.field-card p:last-child {
  margin: 0;
  max-width: 24rem;
  color: rgba(255, 255, 255, 0.9);
  font-size: clamp(0.9rem, 1.6vw, 1rem);
  line-height: 1.6;
}

.fields__controls {
  position: absolute;
  top: 50%;
  right: 0.9rem;
  z-index: 3;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
  transform: translateY(-50%);
}

.fields__nav {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.2rem;
  height: 2.2rem;
  border: 1px solid rgba(255, 255, 255, 0.45);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
  cursor: pointer;
  backdrop-filter: blur(6px);
  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.fields__nav:hover {
  background: rgba(255, 255, 255, 0.28);
  border-color: rgba(255, 255, 255, 0.75);
  transform: translateY(-1px);
}

.fields__dots {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.15rem 0;
}

.fields__dot {
  width: 0.45rem;
  height: 0.45rem;
  padding: 0;
  border: 0;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  transition:
    height 0.3s ease,
    background 0.3s ease;
}

.fields__dot.is-active {
  height: 1.25rem;
  background: #fff;
}

/* Vertical slide transitions */
.slide-down-enter-active,
.slide-down-leave-active,
.slide-up-enter-active,
.slide-up-leave-active {
  transition:
    opacity 0.42s ease,
    transform 0.42s ease;
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(1.5rem) scale(0.985);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-1.2rem) scale(0.985);
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(-1.5rem) scale(0.985);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translateY(1.2rem) scale(0.985);
}

.assessment-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
  padding: 1.25rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 2px);
  box-shadow: var(--shadow);
}

.assessment-card h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 500;
}

.assessment-card .muted {
  margin: 0;
  flex: 1;
  line-height: 1.55;
}

.assessment-card__meta {
  margin: 0;
  color: var(--accent);
  font-weight: 500;
  font-size: 0.9rem;
}

.assessment-card__cta {
  margin-top: 0.35rem;
  align-self: flex-start;
  padding: 0.45rem 0.95rem;
  font-size: 0.9rem;
}

@media (max-width: 639px) {
  .fields__viewport,
  .field-card {
    min-height: 16.5rem;
  }

  .field-card {
    background-image:
      linear-gradient(
        180deg,
        rgba(10, 42, 28, 0.94) 0%,
        rgba(10, 42, 28, 0.78) 42%,
        rgba(10, 42, 28, 0.28) 70%,
        transparent 100%
      ),
      var(--field-image);
    background-position: center center;
    padding-right: 3.5rem;
  }

  .field-card__copy {
    max-width: none;
  }

  .fields__controls {
    right: 0.7rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .slide-down-enter-active,
  .slide-down-leave-active,
  .slide-up-enter-active,
  .slide-up-leave-active,
  .fields__dot,
  .fields__nav {
    transition: none;
  }

  .slide-down-enter-from,
  .slide-down-leave-to,
  .slide-up-enter-from,
  .slide-up-leave-to {
    transform: none;
  }
}
</style>
