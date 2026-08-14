<template>
  <section class="xu-he-thong">
    <header class="xu-he-thong__head">
      <h1>Xu hệ thống</h1>
      <p class="muted">Nhận xu và theo dõi biến động số dư xu hệ thống của bạn.</p>
    </header>

    <div class="xu-he-thong__tabs" role="tablist" aria-label="Xu hệ thống">
      <button
        v-for="tab in tabs"
        :id="`xu-he-thong-tab-${tab.id}`"
        :key="tab.id"
        type="button"
        role="tab"
        class="xu-he-thong__tab"
        :class="{ 'is-active': activeTab === tab.id }"
        :aria-selected="activeTab === tab.id"
        :aria-controls="`xu-he-thong-panel-${tab.id}`"
        :tabindex="activeTab === tab.id ? 0 : -1"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div
      :id="`xu-he-thong-panel-${activeTab}`"
      class="xu-he-thong__panel"
      role="tabpanel"
      :aria-labelledby="`xu-he-thong-tab-${activeTab}`"
    >
      <div v-show="activeTab === 'nhan-xu'" class="xu-he-thong__pane">
        <XuHeThongNhanTab />
      </div>
      <div v-show="activeTab === 'lich-su'" class="xu-he-thong__pane">
        <XuHeThongLichSuTab :active="activeTab === 'lich-su'" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import XuHeThongLichSuTab from '@/components/user/XuHeThongLichSuTab.vue'
import XuHeThongNhanTab from '@/components/user/XuHeThongNhanTab.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const activeTab = ref('nhan-xu')

const tabs = [
  { id: 'nhan-xu', label: 'Nhận xu' },
  { id: 'lich-su', label: 'Lịch sử biến động' },
]

onMounted(async () => {
  try {
    await auth.fetchMe()
  } catch {
    // Giữ số dư local nếu /auth/me tạm thời lỗi.
  }
})
</script>

<style scoped>
.xu-he-thong {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  display: grid;
  gap: 1rem;
  width: 100%;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.xu-he-thong__head h1 {
  margin: 0 0 0.35rem;
  font-size: 16px;
  font-weight: 400;
}

.xu-he-thong__head p {
  margin: 0;
}

.xu-he-thong__tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  padding: 0.3rem;
  background: #f4f7f5;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 4px);
}

.xu-he-thong__tab {
  flex: 1 1 auto;
  min-width: max-content;
  margin: 0;
  padding: 0.65rem 1rem;
  border: 0;
  border-radius: var(--radius);
  background: transparent;
  color: var(--muted);
  font: inherit;
  font-size: 16px;
  font-weight: 300;
  line-height: 1.3;
  cursor: pointer;
  transition:
    color 0.18s ease,
    background-color 0.18s ease,
    box-shadow 0.18s ease;
}

.xu-he-thong__tab:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.65);
}

.xu-he-thong__tab.is-active {
  color: var(--accent);
  background: #fff;
  box-shadow: 0 1px 3px rgba(24, 48, 36, 0.08);
  font-weight: 400;
}

.xu-he-thong__tab:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.xu-he-thong__panel {
  display: grid;
  gap: 1rem;
}

.xu-he-thong__pane {
  min-width: 0;
}
</style>
