<template>
  <section class="bao-mat">
    <header class="bao-mat__head">
      <h1>Bảo mật tài khoản</h1>
      <p class="muted">Quản lý mật khẩu đăng nhập và mật khẩu thanh toán của bạn.</p>
    </header>

    <div class="bao-mat__tabs" role="tablist" aria-label="Bảo mật tài khoản">
      <button
        v-for="tab in tabs"
        :id="`bao-mat-tab-${tab.id}`"
        :key="tab.id"
        type="button"
        role="tab"
        class="bao-mat__tab"
        :class="{ 'is-active': activeTab === tab.id }"
        :aria-selected="activeTab === tab.id"
        :aria-controls="`bao-mat-panel-${tab.id}`"
        :tabindex="activeTab === tab.id ? 0 : -1"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div
      :id="`bao-mat-panel-${activeTab}`"
      class="bao-mat__panel"
      role="tabpanel"
      :aria-labelledby="`bao-mat-tab-${activeTab}`"
    >
      <div v-show="activeTab === 'dang-nhap'" class="bao-mat__pane">
        <BaoMatDangNhapTab />
      </div>
      <div v-show="activeTab === 'thanh-toan'" class="bao-mat__pane">
        <BaoMatThanhToanTab />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import BaoMatDangNhapTab from '@/components/user/BaoMatDangNhapTab.vue'
import BaoMatThanhToanTab from '@/components/user/BaoMatThanhToanTab.vue'

const activeTab = ref('dang-nhap')

const tabs = [
  { id: 'dang-nhap', label: 'Đăng nhập' },
  { id: 'thanh-toan', label: 'Thanh toán' },
]
</script>

<style scoped>
.bao-mat {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  display: grid;
  gap: 1rem;
  width: 100%;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.bao-mat__head h1 {
  margin: 0 0 0.35rem;
  font-size: 16px;
  font-weight: 400;
}

.bao-mat__head p {
  margin: 0;
}

.bao-mat__tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  padding: 0.3rem;
  background: #f4f7f5;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 4px);
}

.bao-mat__tab {
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

.bao-mat__tab:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.65);
}

.bao-mat__tab.is-active {
  color: var(--accent);
  background: #fff;
  box-shadow: 0 1px 3px rgba(24, 48, 36, 0.08);
  font-weight: 400;
}

.bao-mat__tab:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.bao-mat__panel {
  display: grid;
  gap: 1rem;
}

.bao-mat__pane {
  min-width: 0;
}
</style>
