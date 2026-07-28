<template>
  <section class="services" aria-labelledby="home-services-title">
    <div class="container">
      <header class="section-head">
        <p class="section-eyebrow">{{ content.eyebrow }}</p>
        <h2 id="home-services-title">{{ content.title }}</h2>
        <p class="section-desc">{{ content.description }}</p>
      </header>

      <div class="row g-3">
        <div
          v-for="(item, index) in content.items"
          :key="item.id"
          class="col-12 col-md-4"
        >
          <RouterLink
            :to="item.to"
            class="service"
            :style="{ '--delay': `${index * 80}ms` }"
          >
            <div class="service__icon" aria-hidden="true">
              <el-icon :size="30"><component :is="item.icon" /></el-icon>
            </div>
            <h3>{{ item.title }}</h3>
            <p>{{ item.description }}</p>
            <span class="service__cta">
              {{ item.cta }}
              <el-icon :size="14"><ArrowRight /></el-icon>
            </span>
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { ArrowRight } from '@element-plus/icons-vue'
import { servicesContent } from '@/data/home'

defineProps({
  content: {
    type: Object,
    default: () => servicesContent,
  },
})
</script>

<style scoped>
.services {
  padding: var(--space-section) 0;
  background: transparent;
}

.section-head {
  max-width: 36rem;
  margin: 0 auto 2.25rem;
  text-align: center;
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

.service {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
  padding: 1.5rem 1.35rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 2px);
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  animation: rise-in 0.55s ease both;
  animation-delay: var(--delay);
}

.service:hover {
  transform: translateY(-4px);
  border-color: #b7d4c4;
  box-shadow: 0 16px 32px rgba(26, 46, 36, 0.09);
}

.service__icon {
  display: grid;
  place-items: center;
  width: 3.25rem;
  height: 3.25rem;
  border-radius: 14px;
  background: var(--accent);
  color: #fff;
}

.service h3 {
  margin: 0.2rem 0 0;
  font-size: 1.15rem;
  font-weight: 700;
}

.service p {
  margin: 0;
  flex: 1;
  color: var(--muted);
  font-size: 0.94rem;
  line-height: 1.55;
}

.service__cta {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  margin-top: 0.4rem;
  color: var(--accent);
  font-weight: 600;
  font-size: 0.92rem;
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
  .service {
    animation: none;
  }
  .service:hover {
    transform: none;
  }
}
</style>
