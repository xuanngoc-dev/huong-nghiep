<template>
  <section>
    <h1>Trắc nghiệm hướng nghiệp</h1>
    <p class="muted">Dữ liệu demo từ API `/assessments`.</p>

    <p v-if="loading" class="muted">Đang tải...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else class="grid cols-2">
      <article v-for="item in assessments" :key="item.id" class="card">
        <h2>{{ item.name }}</h2>
        <p class="muted">{{ item.description }}</p>
        <p class="meta">{{ item.question_count }} câu hỏi</p>
      </article>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { assessmentApi } from '@/api'

const assessments = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const { data } = await assessmentApi.list()
    assessments.value = data.data
  } catch {
    error.value = 'Không tải được danh sách trắc nghiệm.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
h2 {
  margin-top: 0;
  font-size: 1.15rem;
}

.meta {
  margin-bottom: 0;
  color: var(--accent);
  font-weight: 600;
  font-size: 0.9rem;
}
</style>
