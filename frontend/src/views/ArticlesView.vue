<template>
  <section>
    <h1>Bài viết hướng nghiệp</h1>
    <p class="muted">Dữ liệu demo từ API `/articles`.</p>

    <p v-if="loading" class="muted">Đang tải...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else class="grid cols-2">
      <article v-for="item in articles" :key="item.id" class="card">
        <h2>{{ item.title }}</h2>
        <p class="muted">{{ item.excerpt }}</p>
        <p class="date">{{ item.published_at }}</p>
      </article>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { articleApi } from '@/api'

const articles = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const { data } = await articleApi.list()
    articles.value = data.data
  } catch {
    error.value = 'Không tải được bài viết.'
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

.date {
  margin-bottom: 0;
  font-size: 0.85rem;
  color: var(--accent);
}
</style>
