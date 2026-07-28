<template>
  <section class="hero">
    <div>
      <p class="eyebrow">Hệ thống hướng nghiệp</p>
      <h1>Khám phá nghề nghiệp phù hợp với bạn</h1>
      <p class="muted lead">
        Khung ứng dụng Vue 3 kết nối Laravel API qua Axios — sẵn sàng mở rộng trắc nghiệm,
        danh mục nghề và nội dung tư vấn.
      </p>
      <div class="cta">
        <RouterLink class="btn" to="/assessments">Làm trắc nghiệm</RouterLink>
        <RouterLink class="btn btn-outline" to="/careers">Xem nghề nghiệp</RouterLink>
      </div>
    </div>

    <div class="card status">
      <h2>Trạng thái API</h2>
      <p v-if="loading" class="muted">Đang kiểm tra kết nối...</p>
      <p v-else-if="error" class="error-text">{{ error }}</p>
      <template v-else>
        <p><strong>Status:</strong> {{ health?.status }}</p>
        <p><strong>App:</strong> {{ health?.app }}</p>
        <p class="muted">{{ health?.timestamp }}</p>
      </template>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { healthApi } from '@/api'

const health = ref(null)
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const { data } = await healthApi.check()
    health.value = data
  } catch {
    error.value = 'Không kết nối được backend. Hãy chạy Laravel tại cổng 8000.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.hero {
  display: grid;
  gap: 1.5rem;
  align-items: start;
}

@media (min-width: 900px) {
  .hero {
    grid-template-columns: 1.4fr 0.8fr;
  }
}

.eyebrow {
  color: var(--accent);
  font-weight: 600;
  margin: 0 0 0.5rem;
}

h1 {
  margin: 0;
  font-size: clamp(1.8rem, 4vw, 2.6rem);
  line-height: 1.15;
  letter-spacing: -0.03em;
}

.lead {
  margin: 1rem 0 1.4rem;
  max-width: 36rem;
  line-height: 1.6;
}

.cta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.status h2 {
  margin-top: 0;
  font-size: 1.1rem;
}
</style>
