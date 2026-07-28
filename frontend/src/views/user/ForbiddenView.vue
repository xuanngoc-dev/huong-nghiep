<template>
  <section class="card forbidden">
    <h1>403 — Không có quyền</h1>
    <p class="muted">Tài khoản của bạn không có quyền truy cập khu vực CMS.</p>
    <RouterLink class="btn" to="/">Về trang chủ</RouterLink>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.forbidden {
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
