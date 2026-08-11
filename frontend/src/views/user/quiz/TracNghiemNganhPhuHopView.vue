<template>
  <section class="quiz-fields" aria-labelledby="quiz-fields-title">
    <canvas
      ref="fireworksCanvas"
      class="quiz-fields__fireworks"
      aria-hidden="true"
    />

    <Transition name="quiz-congrats">
      <div
        v-if="showCongrats"
        class="quiz-fields__congrats"
        role="status"
        aria-live="polite"
        :style="celebrationStyle"
      >
        <p class="quiz-fields__congrats-eyebrow">{{ celebration.eyebrow }}</p>
        <p class="quiz-fields__congrats-title">
          <span
            v-for="(ch, i) in congratsTitleChars"
            :key="`${ch}-${i}`"
            class="quiz-fields__congrats-char"
            :style="{ '--i': i }"
          >{{ ch === ' ' ? '\u00A0' : ch }}</span>
        </p>
        <p
          class="quiz-fields__congrats-sub"
          :aria-label="celebration.subtitle.text"
        >
          <span
            v-for="(ch, i) in congratsSubChars"
            :key="`sub-${ch}-${i}`"
            class="quiz-fields__congrats-sub-char"
            :style="{ '--i': i }"
            aria-hidden="true"
          >{{ ch === ' ' ? '\u00A0' : ch }}</span>
        </p>
      </div>
    </Transition>

    <header class="quiz-fields__head">
      <p class="quiz-fields__eyebrow">Bước {{ currentStep }} · Ngành phù hợp</p>
      <h1 id="quiz-fields-title">Ngành phù hợp với bạn</h1>
      <p class="quiz-fields__lead">
        Đây là
        <strong>5 nhóm ngành</strong>
        và
        <strong>5 chuyên ngành</strong>
        phù hợp nhất với bạn, dựa trên tổng điểm các câu đã trả lời trong phiên khảo sát.
      </p>
    </header>

    <div class="quiz-fields__tabs" role="tablist" aria-label="Các phần ngành phù hợp">
      <button
        v-for="tab in tabs"
        :id="`quiz-fields-tab-${tab.id}`"
        :key="tab.id"
        type="button"
        role="tab"
        class="quiz-fields__tab"
        :class="{ 'is-active': activeTab === tab.id }"
        :aria-selected="activeTab === tab.id"
        :aria-controls="`quiz-fields-panel-${tab.id}`"
        :tabindex="activeTab === tab.id ? 0 : -1"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <p v-if="loading" class="muted">Đang tổng hợp kết quả...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div
      v-else
      :id="`quiz-fields-panel-${activeTab}`"
      class="quiz-fields__panel"
      role="tabpanel"
      :aria-labelledby="`quiz-fields-tab-${activeTab}`"
    >
      <QuizFieldsKetQuaTab
        v-if="activeTab === 'ket-qua'"
        :summary="summary"
        :can-go-prev="Boolean(prevStep)"
        :can-go-next="true"
        @prev="goPrev"
        @next="goToTab('nganh-uoc-mo')"
      />
      <QuizFieldsNganhUocMoTab
        v-else-if="activeTab === 'nganh-uoc-mo'"
        :summary="summary"
        :can-go-prev="true"
        :can-go-next="true"
        @prev="goToTab('ket-qua')"
        @next="goToTab('khao-sat')"
      />
      <QuizFieldsKhaoSatCaNhanTab v-else-if="activeTab === 'khao-sat'" />
    </div>
  </section>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { quizFieldsCelebration } from '@/data/quiz-fields-celebration'
import QuizFieldsKetQuaTab from '@/components/quiz/QuizFieldsKetQuaTab.vue'
import QuizFieldsNganhUocMoTab from '@/components/quiz/QuizFieldsNganhUocMoTab.vue'
import QuizFieldsKhaoSatCaNhanTab from '@/components/quiz/QuizFieldsKhaoSatCaNhanTab.vue'
import { useQuizStore } from '@/stores/quiz'

const FIREWORK_COLORS = ['#1f7a4c', '#2f9e66', '#f4b942', '#e85d4c', '#4c8fe8', '#9b59b6']

const tabs = [
  { id: 'ket-qua', label: 'Kết quả trắc nghiệm' },
  { id: 'nganh-uoc-mo', label: 'Ngành học ước mơ' },
  { id: 'khao-sat', label: 'Khảo sát cá nhân' },
]

const celebration = quizFieldsCelebration

function msToSec(ms) {
  return `${(Number(ms) || 0) / 1000}s`
}

const celebrationStyle = {
  '--title-enter-ms': msToSec(celebration.title.enterDurationMs),
  '--title-start-ms': msToSec(celebration.title.startDelayMs),
  '--title-stagger-ms': msToSec(celebration.title.staggerMs),
  '--sub-enter-ms': msToSec(celebration.subtitle.enterDurationMs),
  '--sub-start-ms': msToSec(celebration.subtitle.startDelayMs),
  '--sub-stagger-ms': msToSec(celebration.subtitle.staggerMs),
  '--sub-wave-ms': msToSec(celebration.subtitle.waveDurationMs),
  '--sub-wave-start-ms': msToSec(celebration.subtitle.waveStartDelayMs),
  '--sub-wave-stagger-ms': msToSec(celebration.subtitle.waveStaggerMs),
  '--overlay-enter-opacity-ms': msToSec(celebration.overlay.enterOpacityMs),
  '--overlay-enter-transform-ms': msToSec(celebration.overlay.enterTransformMs),
  '--overlay-leave-ms': msToSec(celebration.overlay.leaveMs),
}

const route = useRoute()
const router = useRouter()
const quiz = useQuizStore()
const { fieldsSummary } = storeToRefs(quiz)

const activeTab = ref('ket-qua')
const loading = ref(true)
const error = ref(null)
const fireworksCanvas = ref(null)
const showCongrats = ref(false)
const congratsTitleChars = celebration.title.text.split('')
const congratsSubChars = celebration.subtitle.text.split('')

const currentStep = computed(() => quiz.resolveCurrentStep(route))
const prevStep = computed(() => quiz.getAdjacent(route, -1))
const nextStep = computed(() => quiz.getAdjacent(route, 1))
const summary = computed(() => fieldsSummary.value)

let fireworksRaf = 0
let fireworksResizeHandler = null
let fireworksPlayed = false
let congratsHideTimer = 0

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function clearCongratsTimer() {
  if (congratsHideTimer) {
    window.clearTimeout(congratsHideTimer)
    congratsHideTimer = 0
  }
}

function stopFireworks() {
  if (fireworksRaf) {
    cancelAnimationFrame(fireworksRaf)
    fireworksRaf = 0
  }
  if (fireworksResizeHandler) {
    window.removeEventListener('resize', fireworksResizeHandler)
    fireworksResizeHandler = null
  }
  const canvas = fireworksCanvas.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  ctx?.clearRect(0, 0, canvas.width, canvas.height)
}

function showCelebrationMessage() {
  clearCongratsTimer()
  showCongrats.value = true
  congratsHideTimer = window.setTimeout(() => {
    showCongrats.value = false
    congratsHideTimer = 0
  }, celebration.messageVisibleMs)
}

function launchFireworks() {
  if (fireworksPlayed) return
  fireworksPlayed = true

  if (prefersReducedMotion()) {
    showCelebrationMessage()
    return
  }

  const canvas = fireworksCanvas.value
  if (!canvas) {
    showCelebrationMessage()
    return
  }

  showCelebrationMessage()

  const ctx = canvas.getContext('2d')
  if (!ctx) return

  const dpr = Math.min(window.devicePixelRatio || 1, 2)
  const particles = []
  const rockets = []
  const startedAt = performance.now()
  const durationMs = celebration.fireworksDurationMs

  function resize() {
    const width = window.innerWidth
    const height = window.innerHeight
    canvas.width = Math.floor(width * dpr)
    canvas.height = Math.floor(height * dpr)
    canvas.style.width = `${width}px`
    canvas.style.height = `${height}px`
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0)
  }

  function spawnRocket() {
    const width = window.innerWidth
    const height = window.innerHeight
    rockets.push({
      x: width * (0.15 + Math.random() * 0.7),
      y: height + 10,
      targetY: height * (0.18 + Math.random() * 0.28),
      speed: 7.5 + Math.random() * 3.5,
      color: FIREWORK_COLORS[Math.floor(Math.random() * FIREWORK_COLORS.length)],
      trail: [],
    })
  }

  function explode(x, y, color) {
    const count = 42 + Math.floor(Math.random() * 24)
    for (let i = 0; i < count; i += 1) {
      const angle = (Math.PI * 2 * i) / count + Math.random() * 0.2
      const speed = 2.2 + Math.random() * 4.2
      particles.push({
        x,
        y,
        vx: Math.cos(angle) * speed,
        vy: Math.sin(angle) * speed,
        life: 1,
        decay: 0.012 + Math.random() * 0.016,
        size: 1.6 + Math.random() * 2.2,
        color:
          Math.random() > 0.35
            ? color
            : FIREWORK_COLORS[Math.floor(Math.random() * FIREWORK_COLORS.length)],
      })
    }
  }

  resize()
  fireworksResizeHandler = resize
  window.addEventListener('resize', resize)

  let nextRocketAt = 0
  let rocketCount = 0

  function frame(now) {
    const elapsed = now - startedAt
    const width = window.innerWidth
    const height = window.innerHeight

    ctx.clearRect(0, 0, width, height)
    ctx.fillStyle = 'rgba(255, 255, 255, 0.08)'
    ctx.fillRect(0, 0, width, height)

    if (elapsed < durationMs * 0.72 && now >= nextRocketAt && rocketCount < 10) {
      spawnRocket()
      rocketCount += 1
      nextRocketAt = now + 280 + Math.random() * 320
    }

    for (let i = rockets.length - 1; i >= 0; i -= 1) {
      const rocket = rockets[i]
      rocket.trail.push({ x: rocket.x, y: rocket.y })
      if (rocket.trail.length > 8) rocket.trail.shift()
      rocket.y -= rocket.speed

      for (let t = 0; t < rocket.trail.length; t += 1) {
        const point = rocket.trail[t]
        ctx.beginPath()
        ctx.fillStyle = rocket.color
        ctx.globalAlpha = (t + 1) / rocket.trail.length / 2
        ctx.arc(point.x, point.y, 1.5, 0, Math.PI * 2)
        ctx.fill()
      }
      ctx.globalAlpha = 1
      ctx.beginPath()
      ctx.fillStyle = rocket.color
      ctx.arc(rocket.x, rocket.y, 2.4, 0, Math.PI * 2)
      ctx.fill()

      if (rocket.y <= rocket.targetY) {
        explode(rocket.x, rocket.y, rocket.color)
        rockets.splice(i, 1)
      }
    }

    for (let i = particles.length - 1; i >= 0; i -= 1) {
      const p = particles[i]
      p.vy += 0.045
      p.vx *= 0.992
      p.x += p.vx
      p.y += p.vy
      p.life -= p.decay

      if (p.life <= 0) {
        particles.splice(i, 1)
        continue
      }

      ctx.beginPath()
      ctx.globalAlpha = Math.max(p.life, 0)
      ctx.fillStyle = p.color
      ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2)
      ctx.fill()
    }
    ctx.globalAlpha = 1

    if (elapsed < durationMs || rockets.length || particles.length) {
      fireworksRaf = requestAnimationFrame(frame)
      return
    }

    stopFireworks()
  }

  fireworksRaf = requestAnimationFrame(frame)
}

async function loadSummary() {
  loading.value = true
  error.value = null

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

    const sessionId = String(route.params.ssid || quiz.ssid || '')
    if (!sessionId) {
      quiz.resetSession()
      await router.replace({ name: 'quiz-start' })
      return
    }

    const history = await quiz.ensureHistoryLoaded(sessionId)
    if (!history.ok) {
      quiz.resetSession()
      await router.replace({ name: 'quiz-start' })
      return
    }

    const result = await quiz.ensureFieldsSummary(sessionId, { force: true })
    if (!result.ok) {
      if (/phiên|ssid/i.test(String(result.message || ''))) {
        quiz.resetSession()
        await router.replace({ name: 'quiz-start' })
        return
      }
      error.value = result.message || 'Không tổng hợp được ngành phù hợp.'
      return
    }

    await nextTick()
    launchFireworks()
  } catch {
    error.value = 'Không tải được tổng hợp ngành phù hợp.'
  } finally {
    loading.value = false
  }
}

function goToTab(tabId) {
  if (!tabs.some((tab) => tab.id === tabId)) return
  activeTab.value = tabId
}

function goPrev() {
  if (!prevStep.value) return
  router.push(quiz.toLocation(prevStep.value, route.params.ssid))
}

function goNext() {
  if (!nextStep.value) return
  quiz.markStepCompleted('fields')
  router.push(quiz.toLocation(nextStep.value, route.params.ssid))
}

onMounted(loadSummary)

onUnmounted(() => {
  clearCongratsTimer()
  showCongrats.value = false
  stopFireworks()
})
</script>

<style scoped>
.quiz-fields {
  position: relative;
  margin: 0 auto;
}

.quiz-fields__fireworks {
  position: fixed;
  inset: 0;
  z-index: 40;
  width: 100vw;
  height: 100vh;
  pointer-events: none;
}

.quiz-fields__congrats {
  position: fixed;
  inset: 0;
  z-index: 41;
  display: grid;
  place-content: center;
  justify-items: center;
  gap: 0.55rem;
  padding: 1.5rem;
  text-align: center;
  pointer-events: none;
  background:
    radial-gradient(ellipse at center, rgba(255, 255, 255, 0.72) 0%, rgba(255, 255, 255, 0.28) 42%, transparent 70%);
}

.quiz-fields__congrats-eyebrow {
  margin: 0;
  color: var(--accent);
  font-size: 0.95rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  opacity: 0.92;
}

.quiz-fields__congrats-title {
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  color: #e11d2e;
  font-size: clamp(2rem, 6vw, 3.35rem);
  font-weight: 700;
  letter-spacing: -0.03em;
  line-height: 1.15;
  text-shadow: 0 8px 28px rgba(225, 29, 46, 0.22);
}

.quiz-fields__congrats-char {
  display: inline-block;
  opacity: 0;
  transform: translateY(0.7em) scale(0.86) rotate(-4deg);
  animation: quiz-congrats-char-in var(--title-enter-ms, 0.65s)
    cubic-bezier(0.22, 1, 0.36, 1) forwards;
  animation-delay: calc(var(--title-start-ms, 0.22s) + var(--i) * var(--title-stagger-ms, 0.075s));
}

.quiz-fields__congrats-sub {
  margin: 0.15rem 0 0;
  max-width: 32rem;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  color: #f08a1a;
  font-size: clamp(0.95rem, 2.2vw, 1.08rem);
  font-weight: 500;
  line-height: 1.6;
}

.quiz-fields__congrats-sub-char {
  display: inline-block;
  opacity: 0;
  animation:
    quiz-congrats-sub-in var(--sub-enter-ms, 0.55s) ease forwards,
    quiz-congrats-wave var(--sub-wave-ms, 1.45s) ease-in-out infinite;
  animation-delay:
    calc(var(--sub-start-ms, 1.15s) + var(--i) * var(--sub-stagger-ms, 0.048s)),
    calc(var(--sub-wave-start-ms, 1.85s) + var(--i) * var(--sub-wave-stagger-ms, 0.06s));
}

.quiz-congrats-enter-active {
  transition:
    opacity var(--overlay-enter-opacity-ms, 0.35s) ease,
    transform var(--overlay-enter-transform-ms, 0.45s) cubic-bezier(0.22, 1, 0.36, 1);
}

.quiz-congrats-leave-active {
  transition:
    opacity var(--overlay-leave-ms, 0.55s) ease,
    transform var(--overlay-leave-ms, 0.55s) cubic-bezier(0.4, 0, 0.2, 1),
    filter var(--overlay-leave-ms, 0.55s) ease;
}

.quiz-congrats-enter-from {
  opacity: 0;
  transform: scale(0.92);
}

.quiz-congrats-leave-to {
  opacity: 0;
  transform: scale(1.06) translateY(-0.75rem);
  filter: blur(6px);
}

@keyframes quiz-congrats-char-in {
  0% {
    opacity: 0;
    transform: translateY(0.7em) scale(0.86) rotate(-4deg);
  }
  60% {
    opacity: 1;
    transform: translateY(-0.08em) scale(1.04) rotate(1deg);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1) rotate(0deg);
  }
}

@keyframes quiz-congrats-sub-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes quiz-congrats-wave {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-0.28em);
  }
}

.quiz-fields__head {
  margin-bottom: 1.35rem;
}

.quiz-fields__eyebrow {
  margin: 0 0 0.4rem;
  color: var(--accent);
  font-weight: 500;
  font-size: 0.9rem;
}

.quiz-fields__head h1 {
  margin: 0 0 0.55rem;
  font-size: clamp(1.4rem, 2.8vw, 1.85rem);
  font-weight: 600;
  letter-spacing: -0.03em;
}

.quiz-fields__lead {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.quiz-fields__lead strong {
  color: var(--text);
  font-weight: 600;
}

.quiz-fields__tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin: 0 0 1.15rem;
  padding: 0.3rem;
  background: #f4f7f5;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 4px);
}

.quiz-fields__tab {
  flex: 1 1 auto;
  min-width: max-content;
  margin: 0;
  padding: 0.65rem 1rem;
  border: 0;
  border-radius: var(--radius);
  background: transparent;
  color: var(--muted);
  font: inherit;
  font-size: 0.92rem;
  font-weight: 500;
  line-height: 1.3;
  cursor: pointer;
  transition:
    color 0.18s ease,
    background-color 0.18s ease,
    box-shadow 0.18s ease;
}

.quiz-fields__tab:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.65);
}

.quiz-fields__tab.is-active {
  color: var(--text);
  background: #fff;
  box-shadow: 0 1px 3px rgba(24, 48, 36, 0.08);
  font-weight: 600;
}

.quiz-fields__tab:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.quiz-fields__panel {
  padding: 1.25rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 4px);
  box-shadow: var(--shadow);
}

@media (prefers-reduced-motion: reduce) {
  .quiz-fields__fireworks {
    display: none;
  }

  .quiz-fields__congrats-char,
  .quiz-fields__congrats-sub-char {
    opacity: 1;
    transform: none;
    animation: none;
  }

  .quiz-congrats-enter-active,
  .quiz-congrats-leave-active {
    transition: opacity 0.2s ease;
  }

  .quiz-congrats-enter-from,
  .quiz-congrats-leave-to {
    transform: none;
    filter: none;
  }

  .quiz-fields__tab {
    transition: none;
  }
}
</style>
