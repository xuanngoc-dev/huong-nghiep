<template>
  <section class="profile-info">
    <header class="profile-info__head">
      <h1>Thông tin cá nhân</h1>
      <p class="muted">
        Xem và cập nhật hồ sơ để hệ thống tư vấn ngành học, nghề nghiệp phù hợp hơn.
        Mật khẩu được quản lý tại
        <RouterLink class="profile-info__link" :to="{ name: 'profile-bao-mat' }">
          Bảo mật tài khoản
        </RouterLink>.
      </p>
    </header>

    <QuizFieldsKhaoSatCaNhanTab variant="profile" @saved="onSaved" />
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import QuizFieldsKhaoSatCaNhanTab from '@/components/quiz/QuizFieldsKhaoSatCaNhanTab.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

function onSaved(data) {
  if (!data) return
  auth.updateUser({
    name: data.ho_ten ?? auth.user?.name,
    email: data.email ?? auth.user?.email,
    so_dien_thoai: data.so_dien_thoai ?? auth.user?.so_dien_thoai,
  })
}
</script>

<style scoped>
.profile-info {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  --el-font-family: var(--font);
  display: grid;
  gap: 1rem;
  width: 100%;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.profile-info__head h1 {
  margin: 0 0 0.35rem;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 400;
}

.profile-info__head p {
  margin: 0;
  font-size: 16px;
  font-weight: 300;
  line-height: 1.5;
}

.profile-info__link {
  color: var(--accent);
  font-weight: 400;
  text-decoration: underline;
  text-underline-offset: 0.15em;
}

.profile-info :deep(p),
.profile-info :deep(li),
.profile-info :deep(label),
.profile-info :deep(span),
.profile-info :deep(a),
.profile-info :deep(button),
.profile-info :deep(input),
.profile-info :deep(textarea),
.profile-info :deep(.el-input),
.profile-info :deep(.el-input__inner),
.profile-info :deep(.el-input__wrapper),
.profile-info :deep(.el-select),
.profile-info :deep(.el-select__wrapper),
.profile-info :deep(.el-select__placeholder),
.profile-info :deep(.el-select__selected-item),
.profile-info :deep(.el-select__input),
.profile-info :deep(.el-date-editor),
.profile-info :deep(.el-button),
.profile-info :deep(.el-form-item__label),
.profile-info :deep(.quiz-fields-khao-sat__lead),
.profile-info :deep(.quiz-fields-khao-sat__field),
.profile-info :deep(.quiz-fields-khao-sat__label),
.profile-info :deep(.quiz-fields-khao-sat__card-title),
.profile-info :deep(.quiz-fields-khao-sat__card-desc),
.profile-info :deep(.quiz-fields-khao-sat__success) {
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.profile-info :deep(.quiz-fields-khao-sat__card-title),
.profile-info :deep(h2) {
  font-weight: 400;
}
</style>
