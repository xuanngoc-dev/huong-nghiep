<template>
  <section>
    <RouterLink class="back" to="/careers">← Quay lại</RouterLink>

    <p v-if="loading" class="muted">Đang tải...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <article v-else-if="career" class="card">
      <p class="category">{{ career.category }}</p>
      <h1>{{ career.name }}</h1>
      <p class="muted">{{ career.description }}</p>
    </article>
  </section>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { careerApi } from '@/api'

const route = useRoute()
const career = ref(null)
const loading = ref(true)
const error = ref(null)

async function load() {
  loading.value = true
  error.value = null
  try {
    const { data } = await careerApi.show(route.params.id)
    career.value = data.data
  } catch {
    error.value = 'Không tìm thấy nghề nghiệp.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
watch(() => route.params.id, load)
</script>

<style scoped>
.back {
  display: inline-block;
  margin-bottom: 1rem;
  color: var(--muted);
}

.category {
  margin: 0;
  color: var(--accent);
  font-weight: 500;
}

h1 {
  margin: 0.4rem 0 0.8rem;
}
</style>
