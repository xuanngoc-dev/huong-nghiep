<template>
  <section class="edu-coin">
    <header class="edu-coin__head">
      <h1>Edu Coin</h1>
      <p class="muted">Quản lý số dư và theo dõi biến động Edu Coin của bạn.</p>
    </header>

    <div class="edu-coin__tabs" role="tablist" aria-label="Edu Coin">
      <button
        v-for="tab in tabs"
        :id="`edu-coin-tab-${tab.id}`"
        :key="tab.id"
        type="button"
        role="tab"
        class="edu-coin__tab"
        :class="{ 'is-active': activeTab === tab.id }"
        :aria-selected="activeTab === tab.id"
        :aria-controls="`edu-coin-panel-${tab.id}`"
        :tabindex="activeTab === tab.id ? 0 : -1"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div
      :id="`edu-coin-panel-${activeTab}`"
      class="edu-coin__panel"
      role="tabpanel"
      :aria-labelledby="`edu-coin-tab-${activeTab}`"
    >
      <div v-show="activeTab === 'tong-quan'" class="edu-coin__pane">
        <EduCoinTongQuanTab />
      </div>
      <div v-show="activeTab === 'nap-coin'" class="edu-coin__pane">
        <EduCoinNapTab />
      </div>
      <div v-show="activeTab === 'lich-su'" class="edu-coin__pane">
        <EduCoinLichSuTab />
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import EduCoinLichSuTab from '@/components/user/EduCoinLichSuTab.vue'
import EduCoinNapTab from '@/components/user/EduCoinNapTab.vue'
import EduCoinTongQuanTab from '@/components/user/EduCoinTongQuanTab.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const activeTab = ref('tong-quan')

const tabs = [
  { id: 'tong-quan', label: 'Tổng quan' },
  { id: 'nap-coin', label: 'Nạp coin' },
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
.edu-coin {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  display: grid;
  gap: 1rem;
  width: 100%;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.edu-coin__head h1 {
  margin: 0 0 0.35rem;
  font-size: 16px;
  font-weight: 400;
}

.edu-coin__head p {
  margin: 0;
}

.edu-coin__tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  padding: 0.3rem;
  background: #f4f7f5;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 4px);
}

.edu-coin__tab {
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

.edu-coin__tab:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.65);
}

.edu-coin__tab.is-active {
  color: var(--accent);
  background: #fff;
  box-shadow: 0 1px 3px rgba(24, 48, 36, 0.08);
  font-weight: 400;
}

.edu-coin__tab:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.edu-coin__panel {
  display: grid;
  gap: 1rem;
}

.edu-coin__pane {
  min-width: 0;
}
</style>
