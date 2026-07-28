<template>
  <section>
    <div class="page-head">
      <h1>Quản lý trắc nghiệm</h1>
      <p class="muted">API admin `/admin/assessments`.</p>
    </div>

    <p v-if="loading" class="muted">Đang tải...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <div v-else class="table-wrap card">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Số câu</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.question_count }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { adminApi } from '@/api'

const items = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const { data } = await adminApi.assessments.list()
    items.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Không tải được danh sách.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page-head {
  margin-bottom: 1rem;
}

.table-wrap {
  overflow-x: auto;
  padding: 0;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  text-align: left;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid var(--border);
}

th {
  font-size: 0.85rem;
  color: var(--muted);
  font-weight: 600;
}

tr:last-child td {
  border-bottom: 0;
}
</style>
