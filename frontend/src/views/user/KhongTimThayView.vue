<template>
  <section class="card not-found">
    <h1>404 — Không tìm thấy</h1>
    <p class="muted">Trang bạn truy cập không tồn tại hoặc đã bị di chuyển.</p>
    <RouterLink class="btn" to="/">Về trang chủ</RouterLink>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.not-found {
  max-width: 480px;
  margin: 4rem auto;
  text-align: center;
}

h1 {
  margin-top: 0;
}

.btn {
  margin-top: 1rem;
}
</style>
