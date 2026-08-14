<template>
  <div class="profile-layout">
    <aside class="profile-nav" aria-label="Menu trang cá nhân">
      <p class="profile-nav__eyebrow">Tài khoản</p>
      <h1 class="profile-nav__title">Trang cá nhân</h1>
      <nav class="profile-nav__list">
        <template v-for="item in menu" :key="item.name || item.label">
          <div v-if="item.children" class="profile-nav__group">
            <p class="profile-nav__group-label">
              <el-icon :size="18">
                <component :is="item.icon" />
              </el-icon>
              {{ item.label }}
            </p>
            <div class="profile-nav__sub">
              <RouterLink
                v-for="child in item.children"
                :key="child.name"
                :to="{ name: child.name }"
                class="profile-nav__link"
                :class="{ 'is-active': route.name === child.name }"
              >
                <el-icon :size="18">
                  <component :is="child.icon" />
                </el-icon>
                {{ child.label }}
              </RouterLink>
            </div>
          </div>
          <RouterLink
            v-else
            :to="{ name: item.name }"
            class="profile-nav__link"
            :class="{ 'is-active': route.name === item.name }"
          >
            <el-icon :size="18">
              <component :is="item.icon" />
            </el-icon>
            {{ item.label }}
          </RouterLink>
        </template>
      </nav>
    </aside>

    <div class="profile-content card">
      <RouterView />
    </div>
  </div>
</template>

<script setup>
import { RouterLink, RouterView, useRoute } from 'vue-router'
import {
  Coin,
  Document,
  Headset,
  Lock,
  Medal,
  User,
  UserFilled,
  Wallet,
} from '@element-plus/icons-vue'

const route = useRoute()

const menu = [
  { name: 'profile', label: 'Tổng quan', icon: User },
  { name: 'profile-thong-tin', label: 'Thông tin cá nhân', icon: UserFilled },
  { name: 'profile-bao-mat', label: 'Bảo mật tài khoản', icon: Lock },
  {
    label: 'Tài sản',
    icon: Wallet,
    children: [
      { name: 'profile-edu-coin', label: 'Edu Coin', icon: Coin },
      { name: 'profile-xu-he-thong', label: 'Xu hệ thống', icon: Medal },
    ],
  },
  { name: 'profile-lich-su-trac-nghiem', label: 'Lịch sử trắc nghiệm', icon: Document },
  { name: 'profile-ho-tro', label: 'Hỗ trợ', icon: Headset },
]
</script>

<style scoped>
.profile-layout {
  --font: "Be Vietnam Pro", "Source Sans 3", "Roboto", "Segoe UI", sans-serif;
  display: grid;
  gap: 1.25rem;
  align-items: start;
  font-family: var(--font);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: -0.02em;
}

.profile-nav {
  padding: 1.15rem 1.2rem 1.25rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}

.profile-nav__eyebrow {
  margin: 0 0 0.2rem;
  color: var(--accent);
  font-size: 16px;
  font-weight: 300;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.profile-nav__title {
  margin: 0 0 0.9rem;
  font-size: 16px;
  font-weight: 400;
}

.profile-nav__list {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.profile-nav__group {
  display: grid;
  gap: 0.3rem;
}

.profile-nav__group-label {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  margin: 0.35rem 0 0;
  padding: 0.35rem 0.75rem 0.15rem;
  color: var(--text);
  font-size: 16px;
  font-weight: 400;
}

.profile-nav__sub {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  padding-left: 0.85rem;
}

.profile-nav__link {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.65rem 0.75rem;
  border-radius: 10px;
  color: var(--muted);
  font-size: 16px;
  font-weight: 300;
}

.profile-nav__link:hover {
  background: var(--accent-soft);
  color: var(--accent);
}

.profile-nav__link.is-active {
  background: var(--accent-soft);
  color: var(--accent);
  font-weight: 400;
}

.profile-content {
  min-height: 22rem;
}

.profile-content :deep(> *) {
  width: 100%;
}

@media (min-width: 900px) {
  .profile-layout {
    grid-template-columns: 15.5rem minmax(0, 1fr);
    gap: 1.5rem;
  }

  .profile-nav {
    position: sticky;
    top: 4.5rem;
  }
}

@media (max-width: 899px) {
  .profile-nav__list {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .profile-nav__group {
    flex: 1 1 100%;
  }

  .profile-nav__sub {
    flex-direction: row;
    flex-wrap: wrap;
    padding-left: 0;
  }

  .profile-nav__link {
    flex: 1 1 auto;
    justify-content: center;
  }
}
</style>
