<template>
  <section class="hero-section" aria-labelledby="home-hero-title">
    <div class="container">
      <div class="hero">
        <div class="hero__banners" aria-hidden="true">
          <div
            v-for="(banner, index) in content.banners"
            :key="banner.image"
            class="hero__banner"
            :class="{ 'is-active': index === activeIndex }"
            :style="{ backgroundImage: `url(${banner.image})` }"
          />
          <div class="hero__overlay" />
        </div>

        <div class="hero__inner">
          <div class="hero__layout">
            <div class="hero__copy">
              <p class="hero__brand">{{ content.brand }}</p>
              <p class="hero__eyebrow">{{ content.eyebrow }}</p>
              <h1 id="home-hero-title" class="hero__title">
                <span class="hero__title-text">{{ typedText }}</span>
                <span
                  class="hero__cursor"
                  :class="{ 'is-blink': !isTyping }"
                  aria-hidden="true"
                />
              </h1>
              <p class="hero__desc">
                <span class="hero__desc-full">{{ content.description }}</span>
                <span class="hero__desc-short">{{ content.descriptionShort || content.description }}</span>
              </p>
              <div class="hero__cta">
                <RouterLink class="btn" :to="content.primaryCta.to">
                  {{ content.primaryCta.label }}
                </RouterLink>
                <RouterLink
                  class="btn btn-outline hero__cta-secondary"
                  :to="content.secondaryCta.to"
                >
                  {{ content.secondaryCta.label }}
                </RouterLink>
              </div>
            </div>

            <ul ref="statsRef" class="hero__stats" aria-label="Thống kê">
              <li
                v-for="(stat, index) in content.stats"
                :key="stat.label"
                class="hero__stat"
              >
                <span class="hero__stat-value">
                  {{ displayValues[index] }}{{ stat.suffix }}
                </span>
                <span class="hero__stat-label">
                  <span class="hero__stat-label-full">{{ stat.label }}</span>
                  <span class="hero__stat-label-short">{{ stat.shortLabel || stat.label }}</span>
                </span>
              </li>
            </ul>
          </div>

          <div class="hero__dots" role="tablist" aria-label="Chọn banner">
            <button
              v-for="(banner, index) in content.banners"
              :key="banner.image"
              type="button"
              class="hero__dot"
              :class="{ 'is-active': index === activeIndex }"
              :aria-label="`Banner ${index + 1}`"
              :aria-selected="index === activeIndex"
              @click="goToSlide(index)"
            />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { heroContent } from '@/data/home'

const props = defineProps({
  content: {
    type: Object,
    default: () => heroContent,
  },
})

const prefersReducedMotion = () =>
  typeof window !== 'undefined' &&
  window.matchMedia('(prefers-reduced-motion: reduce)').matches

const banners = computed(() => props.content.banners ?? [])
const activeIndex = ref(0)
const typedText = ref('')
const isTyping = ref(false)
const displayValues = ref(props.content.stats.map(() => 0))
const statsRef = ref(null)
const counted = ref(false)

let typeTimers = []
let countRaf = 0
let statsObserver = null

function clearTypeTimers() {
  typeTimers.forEach((id) => clearTimeout(id))
  typeTimers = []
}

function schedule(fn, delay) {
  const id = setTimeout(fn, delay)
  typeTimers.push(id)
  return id
}

function typeTitle(fullText, onDone) {
  isTyping.value = true
  typedText.value = ''
  let i = 0

  const step = () => {
    typedText.value = fullText.slice(0, i + 1)
    i += 1
    if (i < fullText.length) {
      schedule(step, 38 + Math.random() * 28)
    } else {
      isTyping.value = false
      onDone?.()
    }
  }

  step()
}

function deleteTitle(onDone) {
  isTyping.value = true
  const step = () => {
    if (typedText.value.length === 0) {
      isTyping.value = false
      onDone?.()
      return
    }
    typedText.value = typedText.value.slice(0, -1)
    schedule(step, 22)
  }
  step()
}

function runTypingCycle() {
  clearTypeTimers()
  const list = banners.value
  if (!list.length) return

  const current = list[activeIndex.value]
  if (prefersReducedMotion()) {
    typedText.value = current.title
    isTyping.value = false
    schedule(() => {
      activeIndex.value = (activeIndex.value + 1) % list.length
      typedText.value = list[activeIndex.value].title
      runTypingCycle()
    }, 4500)
    return
  }

  typeTitle(current.title, () => {
    schedule(() => {
      deleteTitle(() => {
        activeIndex.value = (activeIndex.value + 1) % list.length
        schedule(runTypingCycle, 220)
      })
    }, 2600)
  })
}

function goToSlide(index) {
  if (index === activeIndex.value || !banners.value.length) return
  clearTypeTimers()
  activeIndex.value = index
  if (prefersReducedMotion()) {
    typedText.value = banners.value[index].title
    schedule(runTypingCycle, 4500)
    return
  }
  typedText.value = ''
  schedule(runTypingCycle, 120)
}

function easeOutCubic(t) {
  return 1 - (1 - t) ** 3
}

function animateCounts() {
  if (counted.value) return
  counted.value = true

  const stats = props.content.stats
  const duration = prefersReducedMotion() ? 0 : 1600
  const start = performance.now()

  const tick = (now) => {
    const progress = duration === 0 ? 1 : Math.min(1, (now - start) / duration)
    const eased = easeOutCubic(progress)

    displayValues.value = stats.map((stat) => Math.round(stat.value * eased))

    if (progress < 1) {
      countRaf = requestAnimationFrame(tick)
    }
  }

  countRaf = requestAnimationFrame(tick)
}

function observeStats() {
  if (!statsRef.value) return

  if (typeof IntersectionObserver === 'undefined') {
    animateCounts()
    return
  }

  statsObserver = new IntersectionObserver(
    (entries) => {
      if (entries.some((entry) => entry.isIntersecting)) {
        animateCounts()
        statsObserver?.disconnect()
      }
    },
    { threshold: 0.35 },
  )

  statsObserver.observe(statsRef.value)
}

onMounted(() => {
  if (banners.value.length) {
    runTypingCycle()
  }
  observeStats()
})

onBeforeUnmount(() => {
  clearTypeTimers()
  if (countRaf) cancelAnimationFrame(countRaf)
  statsObserver?.disconnect()
})
</script>

<style scoped>
.hero-section {
  padding: var(--space-section) 0;
  background: transparent;
}

.hero {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  color: #fff;
  min-height: clamp(18rem, 42vh, 28rem);
  display: flex;
  align-items: center;
  padding: clamp(1.15rem, 3vw, 2.5rem) clamp(1rem, 2.5vw, 2rem);
  border-radius: calc(var(--radius) + 10px);
  background: #0f3d28;
  box-shadow: 0 18px 40px rgba(31, 122, 76, 0.18);
}

@media (min-width: 768px) {
  .hero {
    min-height: clamp(28rem, 58vh, 36rem);
    padding: clamp(2.5rem, 5vw, 3.75rem) clamp(1.75rem, 3.5vw, 3rem);
  }

  .hero__inner {
    gap: 1.75rem;
  }

  .hero__layout {
    grid-template-columns: minmax(0, 1fr) minmax(8.5rem, 11rem);
    gap: 1.5rem 2rem;
    align-items: center;
  }

  .hero__brand {
    margin-bottom: 0.65rem;
    font-size: clamp(1.2rem, 2.2vw, 1.5rem);
  }

  .hero__eyebrow {
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
  }

  .hero__title {
    min-height: 2.6em;
    font-size: clamp(1.55rem, 3vw, 2.15rem);
  }

  .hero__desc {
    margin: 1rem 0 1.4rem;
    max-width: 38rem;
    font-size: 1.02rem;
    line-height: 1.7;
  }

  .hero__stats {
    gap: 1.1rem;
    padding-left: 1.15rem;
  }

  .hero__stat-value {
    font-size: clamp(1.35rem, 2.4vw, 1.75rem);
  }

  .hero__stat-label {
    font-size: 0.88rem;
  }
}

.hero__banners {
  position: absolute;
  inset: 0;
  z-index: -1;
}

.hero__banner {
  position: absolute;
  inset: 0;
  background-position: center;
  background-size: cover;
  background-repeat: no-repeat;
  opacity: 0;
  transform: scale(1.04);
  transition:
    opacity 1.1s ease,
    transform 6.5s ease;
}

.hero__banner.is-active {
  opacity: 1;
  transform: scale(1);
}

.hero__overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      115deg,
      rgba(10, 42, 28, 0.92) 0%,
      rgba(15, 61, 40, 0.78) 42%,
      rgba(31, 122, 76, 0.55) 100%
    ),
    radial-gradient(ellipse 70% 55% at 90% 15%, rgba(255, 255, 255, 0.14), transparent 55%);
}

.hero__inner {
  position: relative;
  width: 100%;
  display: grid;
  gap: 1.25rem;
}

.hero__layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(6.5rem, 9.5rem);
  gap: 1rem 1.25rem;
  align-items: end;
}

.hero__copy {
  min-width: 0;
  animation: rise-in 0.7s ease both;
}

.hero__brand {
  margin: 0 0 0.45rem;
  font-size: clamp(1.05rem, 2vw, 1.35rem);
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.2;
}

.hero__eyebrow {
  margin: 0 0 0.55rem;
  font-size: clamp(0.78rem, 1.4vw, 0.88rem);
  font-weight: 500;
  opacity: 0.88;
}

.hero__title {
  margin: 0;
  min-height: 2.5em;
  font-size: clamp(1.2rem, 2.6vw, 1.85rem);
  font-weight: 300;
  line-height: 1.22;
  letter-spacing: -0.03em;
}

.hero__title-text {
  white-space: pre-wrap;
}

.hero__cursor {
  display: inline-block;
  width: 0.08em;
  height: 0.92em;
  margin-left: 0.12em;
  vertical-align: -0.08em;
  background: rgba(255, 255, 255, 0.92);
  border-radius: 1px;
}

.hero__cursor.is-blink {
  animation: cursor-blink 0.9s steps(1) infinite;
}

.hero__desc {
  margin: 0.75rem 0 1.1rem;
  max-width: 34rem;
  font-size: clamp(0.85rem, 1.5vw, 0.95rem);
  line-height: 1.6;
  opacity: 0.9;
}

.hero__desc-short,
.hero__stat-label-short {
  display: none;
}

.hero__cta {
  display: flex;
  flex-wrap: nowrap;
  gap: var(--space-btn);
}

.hero__cta .btn {
  background: #fff;
  color: var(--accent);
  font-weight: 600;
  font-size: clamp(0.82rem, 1.4vw, 0.92rem);
  padding: 0.45rem 0.9rem;
  white-space: nowrap;
  flex: 0 1 auto;
}

.hero__cta .btn:hover {
  opacity: 0.95;
}

.hero__cta-secondary {
  background: transparent !important;
  color: #fff !important;
  border-color: rgba(255, 255, 255, 0.55) !important;
}

.hero__cta-secondary:hover {
  background: rgba(255, 255, 255, 0.12) !important;
}

.hero__stats {
  margin: 0;
  padding: 0 0 0 0.85rem;
  list-style: none;
  display: grid;
  gap: 0.75rem;
  border-left: 1px solid rgba(255, 255, 255, 0.22);
  animation: rise-in 0.7s ease 0.15s both;
}

.hero__stat {
  display: grid;
  gap: 0.15rem;
}

.hero__stat-value {
  font-size: clamp(1.15rem, 2.2vw, 1.55rem);
  font-weight: 800;
  letter-spacing: -0.02em;
  font-variant-numeric: tabular-nums;
}

.hero__stat-label {
  font-size: clamp(0.7rem, 1.2vw, 0.8rem);
  opacity: 0.82;
  line-height: 1.3;
}

.hero__dots {
  display: flex;
  gap: 0.55rem;
}

.hero__dot {
  width: 0.55rem;
  height: 0.55rem;
  padding: 0;
  border: 0;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.35);
  cursor: pointer;
  transition:
    width 0.35s ease,
    background 0.35s ease;
}

.hero__dot.is-active {
  width: 1.55rem;
  background: #fff;
}

.hero__dot:focus-visible {
  outline: 2px solid #fff;
  outline-offset: 3px;
}

@media (max-width: 767px) {
  .hero {
    min-height: 0;
    padding: 1.1rem 0.9rem 1.15rem;
  }

  .hero__inner {
    gap: 0.9rem;
  }

  .hero__layout {
    gap: 0.75rem 0.85rem;
    grid-template-columns: minmax(0, 1fr) minmax(5.5rem, 7.5rem);
  }

  .hero__desc {
    margin: 0.55rem 0 0.85rem;
  }

  .hero__desc-full,
  .hero__stat-label-full {
    display: none;
  }

  .hero__desc-short,
  .hero__stat-label-short {
    display: inline;
  }

  .hero__cta {
    gap: 0.5rem;
  }

  .hero__cta .btn {
    flex: 1 1 0;
    justify-content: center;
    padding: 0.4rem 0.55rem;
    font-size: 0.8rem;
  }

  .hero__stats {
    padding-left: 0.65rem;
    gap: 0.55rem;
  }

  .hero__dots {
    justify-content: center;
  }
}

@keyframes rise-in {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes cursor-blink {
  0%,
  45% {
    opacity: 1;
  }
  50%,
  100% {
    opacity: 0;
  }
}

@media (prefers-reduced-motion: reduce) {
  .hero__banner,
  .hero__copy,
  .hero__stats,
  .hero__cursor.is-blink,
  .hero__dot {
    animation: none;
    transition: none;
  }

  .hero__banner {
    transform: none;
  }
}
</style>
