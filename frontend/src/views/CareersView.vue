<template>
  <section>
    <h1>Danh mục nghề nghiệp</h1>
    <p class="muted">Dữ liệu demo từ API `/careers`.</p>

    <p v-if="loading" class="muted">Đang tải...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else class="grid cols-3">
      <RouterLink
        v-for="item in careers"
        :key="item.id"
        :to="{ name: 'career-detail', params: { id: item.id } }"
        class="card item"
      >
        <p class="category">{{ item.category }}</p>
        <h2>{{ item.name }}</h2>
        <p class="muted">{{ item.description }}</p>
      </RouterLink>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { careerApi } from '@/api'

const careers = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const { data } = await careerApi.list()
    careers.value = data.data
  } catch {
    error.value = 'Không tải được danh sách nghề nghiệp.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.item h2 {
  margin: 0.35rem 0;
  font-size: 1.15rem;
}

.category {
  margin: 0;
  color: var(--accent);
  font-size: 0.85rem;
  font-weight: 600;
}
</style>
