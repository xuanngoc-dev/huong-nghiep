<template>
  <el-drawer
    v-model="visible"
    direction="rtl"
    size="400px"
    class="notification-drawer"
  >
    <template #header>
      <div class="drawer-header">
        <div class="drawer-header__title">
          <span>Thông báo</span>
          <el-tag v-if="unreadCount" size="small" type="danger" round>
            {{ unreadCount }} mới
          </el-tag>
        </div>
        <el-button v-if="unreadCount" link type="primary" @click="markAllRead">
          Đánh dấu đã đọc
        </el-button>
      </div>
    </template>

    <div class="notification-list">
      <button
        v-for="item in notifications"
        :key="item.id"
        type="button"
        class="notification-item"
        :class="{ 'is-unread': !item.read }"
        @click="markRead(item.id)"
      >
        <div class="notification-item__icon" :class="`is-${item.type}`">
          <el-icon :size="18">
            <component :is="iconMap[item.type] || Bell" />
          </el-icon>
        </div>
        <div class="notification-item__body">
          <div class="notification-item__top">
            <span class="notification-item__title">{{ item.title }}</span>
            <span v-if="!item.read" class="notification-item__dot" />
          </div>
          <p class="notification-item__message">{{ item.message }}</p>
          <span class="notification-item__time">{{ item.time }}</span>
        </div>
      </button>

      <el-empty
        v-if="!notifications.length"
        description="Không có thông báo"
        :image-size="80"
      />
    </div>

    <template #footer>
      <div class="drawer-footer">
        <el-button @click="visible = false">Đóng</el-button>
      </div>
    </template>
  </el-drawer>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Bell, CircleCheck, Document, User, Warning } from '@element-plus/icons-vue'

const visible = defineModel({ type: Boolean, default: false })
const unreadCountModel = defineModel('unreadCount', { type: Number, default: 0 })

const iconMap = {
  success: CircleCheck,
  warning: Warning,
  info: Document,
  user: User,
}

const notifications = ref([
  {
    id: 1,
    type: 'info',
    title: 'Bài viết mới cần duyệt',
    message: 'Có 1 bài viết hướng nghiệp đang chờ xuất bản.',
    time: '10 phút trước',
    read: false,
  },
  {
    id: 2,
    type: 'user',
    title: 'Người dùng mới',
    message: 'Một học sinh vừa đăng ký tài khoản trên hệ thống.',
    time: '1 giờ trước',
    read: false,
  },
  {
    id: 3,
    type: 'success',
    title: 'Đồng bộ CMS thành công',
    message: 'Danh mục nghề nghiệp đã được cập nhật.',
    time: 'Hôm qua',
    read: true,
  },
])

const unreadCount = computed(() => notifications.value.filter((n) => !n.read).length)

watch(unreadCount, (count) => {
  unreadCountModel.value = count
}, { immediate: true })

function markRead(id) {
  const item = notifications.value.find((n) => n.id === id)
  if (item) item.read = true
}

function markAllRead() {
  notifications.value.forEach((n) => {
    n.read = true
  })
}
</script>

<style scoped lang="scss">
.drawer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  width: 100%;
  padding-right: 12px;

  &__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 600;
  }
}

.notification-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.notification-item {
  display: flex;
  gap: 12px;
  width: 100%;
  padding: 12px;
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 10px;
  background: var(--el-bg-color);
  text-align: left;
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease;

  &:hover {
    border-color: var(--el-color-primary-light-5);
  }

  &.is-unread {
    background: var(--el-color-primary-light-9);
    border-color: var(--el-color-primary-light-7);
  }

  &__icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--el-fill-color-light);

    &.is-success {
      background: var(--el-color-success-light-9);
      color: var(--el-color-success);
    }

    &.is-warning {
      background: var(--el-color-warning-light-9);
      color: var(--el-color-warning);
    }

    &.is-info {
      background: var(--el-color-primary-light-9);
      color: var(--el-color-primary);
    }

    &.is-user {
      background: var(--el-color-info-light-9);
      color: var(--el-color-info);
    }
  }

  &__body {
    min-width: 0;
    flex: 1;
  }

  &__top {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  &__title {
    font-size: 14px;
    font-weight: 600;
    color: var(--el-text-color-primary);
  }

  &__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--el-color-danger);
    flex-shrink: 0;
  }

  &__message {
    margin: 4px 0 6px;
    font-size: 13px;
    line-height: 1.45;
    color: var(--el-text-color-regular);
  }

  &__time {
    font-size: 12px;
    color: var(--el-text-color-secondary);
  }
}

.drawer-footer {
  display: flex;
  justify-content: flex-end;
}
</style>
