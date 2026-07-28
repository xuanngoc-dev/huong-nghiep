<template>
  <section class="admission" aria-labelledby="home-admission-title">
    <div class="container">
      <div class="admission__banner">
        <div class="admission__glow" aria-hidden="true" />
        <div class="admission__copy">
          <p class="admission__eyebrow">{{ content.eyebrow }}</p>
          <h2 id="home-admission-title">{{ content.title }}</h2>
          <p class="admission__desc">{{ content.description }}</p>
          <div class="admission__cta">
            <RouterLink class="btn admission__btn-primary" :to="content.primaryCta.to">
              {{ content.primaryCta.label }}
            </RouterLink>
            <RouterLink class="btn admission__btn-secondary" :to="content.secondaryCta.to">
              {{ content.secondaryCta.label }}
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { admissionContent } from '@/data/home'

defineProps({
  content: {
    type: Object,
    default: () => admissionContent,
  },
})
</script>

<style scoped>
.admission {
  padding: var(--space-section) 0;
  background: transparent;
}

.admission__banner {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  padding: clamp(1.75rem, 4vw, 2.75rem);
  border-radius: calc(var(--radius) + 10px);
  color: #fff;
  background: linear-gradient(120deg, #156b42 0%, #1f7a4c 45%, #24935a 100%);
  box-shadow: 0 18px 40px rgba(31, 122, 76, 0.22);
  animation: rise-in 0.6s ease both;
}

.admission__glow {
  position: absolute;
  inset: -20% -10% auto auto;
  width: min(420px, 70%);
  aspect-ratio: 1;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.22), transparent 65%);
  z-index: -1;
  animation: glow-pulse 6s ease-in-out infinite alternate;
}

.admission__copy {
  max-width: 40rem;
}

.admission__eyebrow {
  margin: 0 0 0.55rem;
  font-size: 0.9rem;
  font-weight: 600;
  opacity: 0.88;
}

.admission__banner h2 {
  margin: 0 0 0.75rem;
  font-size: clamp(1.35rem, 2.8vw, 1.9rem);
  font-weight: 800;
  letter-spacing: -0.02em;
  line-height: 1.25;
}

.admission__desc {
  margin: 0 0 1.35rem;
  max-width: 36rem;
  line-height: 1.6;
  opacity: 0.9;
}

.admission__cta {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-btn);
}

.admission__btn-primary {
  background: #fff;
  color: var(--accent);
  font-weight: 600;
}

.admission__btn-secondary {
  background: transparent;
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.55);
}

.admission__btn-secondary:hover {
  background: rgba(255, 255, 255, 0.12);
}

@keyframes rise-in {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes glow-pulse {
  from {
    transform: translate(0, 0) scale(1);
    opacity: 0.85;
  }
  to {
    transform: translate(-8%, 6%) scale(1.12);
    opacity: 1;
  }
}

@media (prefers-reduced-motion: reduce) {
  .admission__banner,
  .admission__glow {
    animation: none;
  }
}
</style>
