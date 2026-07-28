<template>
  <section class="audiences" aria-labelledby="home-audiences-title">
    <div class="container">
      <header class="section-head">
        <p class="section-eyebrow">{{ content.eyebrow }}</p>
        <h2 id="home-audiences-title">{{ content.title }}</h2>
        <p class="section-desc">{{ content.description }}</p>
      </header>

      <div class="row g-3">
        <div
          v-for="(item, index) in content.items"
          :key="item.id"
          class="col-12 col-md-6 col-xl-3"
        >
          <article class="audience" :style="{ '--delay': `${index * 70}ms` }">
            <div class="audience__icon" aria-hidden="true">
              <el-icon :size="28"><component :is="item.icon" /></el-icon>
            </div>
            <h3>{{ item.title }}</h3>
            <p>{{ item.description }}</p>
            <RouterLink class="audience__link" :to="item.to">
              Khám phá ngay
              <el-icon :size="14"><ArrowRight /></el-icon>
            </RouterLink>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { ArrowRight } from '@element-plus/icons-vue'
import { audiencesContent } from '@/data/home'

defineProps({
  content: {
    type: Object,
    default: () => audiencesContent,
  },
})
</script>

<style scoped>
.audiences {
  padding: var(--space-section) 0;
  background: transparent;
}

.section-head {
  max-width: 36rem;
  margin-bottom: 2.25rem;
  text-align: center;
  margin-inline: auto;
}

.section-eyebrow {
  margin: 0 0 0.5rem;
  color: var(--accent);
  font-weight: 600;
  font-size: 0.9rem;
}

.section-head h2 {
  margin: 0 0 0.65rem;
  font-size: clamp(1.55rem, 3vw, 2.15rem);
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.2;
}

.section-desc {
  margin: 0;
  color: var(--muted);
  line-height: 1.55;
}

.audience {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  padding: 1.4rem 1.25rem 1.35rem;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 2px);
  background: linear-gradient(180deg, #f9fcfa 0%, #fff 100%);
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  animation: rise-in 0.55s ease both;
  animation-delay: var(--delay);
}

.audience:hover {
  transform: translateY(-4px);
  border-color: #b7d4c4;
  box-shadow: 0 14px 28px rgba(26, 46, 36, 0.08);
}

.audience__icon {
  display: grid;
  place-items: center;
  width: 3rem;
  height: 3rem;
  border-radius: 12px;
  background: var(--accent-soft);
  color: var(--accent);
}

.audience h3 {
  margin: 0.25rem 0 0;
  font-size: 1.1rem;
  font-weight: 700;
}

.audience p {
  margin: 0;
  flex: 1;
  color: var(--muted);
  font-size: 0.92rem;
  line-height: 1.55;
}

.audience__link {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  margin-top: 0.35rem;
  color: var(--accent);
  font-weight: 600;
  font-size: 0.92rem;
}

.audience__link:hover {
  text-decoration: underline;
}

@keyframes rise-in {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .audience {
    animation: none;
  }
  .audience:hover {
    transform: none;
  }
}
</style>
