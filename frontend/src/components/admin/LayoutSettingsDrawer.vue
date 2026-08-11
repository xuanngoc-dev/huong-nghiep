<template>
  <el-drawer
    v-model="visible"
    title="Cài đặt giao diện"
    direction="rtl"
    size="360px"
  >
    <div class="settings-body">
      <section class="settings-section">
        <h4 class="settings-section__title">Menu bên</h4>

        <div class="settings-row">
          <div class="settings-row__meta">
            <span class="settings-row__label">Thu gọn theo nhóm</span>
            <span class="settings-row__desc">Cho phép mở rộng / thu gọn từng nhóm menu</span>
          </div>
          <el-switch v-model="layoutStore.menuGroupCollapsible" />
        </div>

        <div class="settings-row" :class="{ 'is-disabled': !layoutStore.menuGroupCollapsible }">
          <div class="settings-row__meta">
            <span class="settings-row__label">Chỉ mở một nhóm</span>
            <span class="settings-row__desc">Đóng các nhóm khác khi mở một nhóm mới</span>
          </div>
          <el-switch
            v-model="layoutStore.menuUniqueOpened"
            :disabled="!layoutStore.menuGroupCollapsible"
          />
        </div>

        <div class="settings-row">
          <div class="settings-row__meta">
            <span class="settings-row__label">Hiện tên nhóm menu</span>
            <span class="settings-row__desc">Hiện tiêu đề nhóm; khi thu gọn hiện viết tắt</span>
          </div>
          <el-switch v-model="layoutStore.menuGroupHeaderVisible" />
        </div>

        <div class="settings-row">
          <div class="settings-row__meta">
            <span class="settings-row__label">Đẩy nội dung khi mở menu</span>
            <span class="settings-row__desc">Bật: thu hẹp nội dung. Tắt: menu phủ như drawer</span>
          </div>
          <el-switch v-model="layoutStore.sidebarPushContent" />
        </div>
      </section>

      <section class="settings-section">
        <h4 class="settings-section__title">Thanh điều hướng</h4>

        <div class="settings-row">
          <div class="settings-row__meta">
            <span class="settings-row__label">Vị trí navbar</span>
            <span class="settings-row__desc">Cố định trên cùng, hoặc cuộn theo nội dung</span>
          </div>
          <el-segmented
            v-model="navbarMode"
            :options="navbarOptions"
            size="small"
          />
        </div>

        <div class="settings-row">
          <div class="settings-row__meta">
            <span class="settings-row__label">Sidebar cố định</span>
            <span class="settings-row__desc">Giữ menu bên trái cố định khi cuộn trang</span>
          </div>
          <el-switch v-model="layoutStore.sidebarFixed" />
        </div>
      </section>

      <section class="settings-section">
        <h4 class="settings-section__title">Chữ & thu phóng</h4>

        <div class="settings-row settings-row--stack">
          <div class="settings-row__meta">
            <span class="settings-row__label">Font chữ hệ thống</span>
            <span class="settings-row__desc">Áp dụng cho toàn bộ giao diện quản trị</span>
          </div>
          <el-select
            v-model="layoutStore.fontFamily"
            class="settings-control"
            size="small"
            placeholder="Chọn font"
          >
            <el-option
              v-for="font in fontOptions"
              :key="font.value"
              :label="font.label"
              :value="font.value"
            >
              <span :style="{ fontFamily: font.stack }">{{ font.label }}</span>
            </el-option>
          </el-select>
        </div>

        <div class="settings-row settings-row--stack">
          <div class="settings-row__meta settings-row__meta--inline">
            <div>
              <span class="settings-row__label">Cỡ chữ</span>
              <span class="settings-row__desc">Điều chỉnh kích thước chữ cơ bản của hệ thống</span>
            </div>
            <span class="settings-scale-value">{{ layoutStore.fontSize }}px</span>
          </div>
          <el-slider
            v-model="layoutStore.fontSize"
            class="settings-slider"
            :min="12"
            :max="20"
            :step="1"
            :marks="fontSizeMarks"
          />
          <div class="settings-scale-presets">
            <el-button
              v-for="preset in fontSizePresets"
              :key="preset.value"
              size="small"
              :type="layoutStore.fontSize === preset.value ? 'primary' : 'default'"
              plain
              @click="layoutStore.fontSize = preset.value"
            >
              {{ preset.label }}
            </el-button>
          </div>
        </div>

        <div class="settings-row settings-row--stack">
          <div class="settings-row__meta settings-row__meta--inline">
            <div>
              <span class="settings-row__label">Thu phóng màn hình</span>
              <span class="settings-row__desc">Điều chỉnh mức độ phóng to / thu nhỏ giao diện</span>
            </div>
            <span class="settings-scale-value">{{ layoutStore.uiScale }}%</span>
          </div>
          <el-slider
            v-model="layoutStore.uiScale"
            class="settings-slider"
            :min="80"
            :max="125"
            :step="5"
            :marks="scaleMarks"
          />
          <div class="settings-scale-presets">
            <el-button
              v-for="preset in scalePresets"
              :key="preset"
              size="small"
              :type="layoutStore.uiScale === preset ? 'primary' : 'default'"
              plain
              @click="layoutStore.uiScale = preset"
            >
              {{ preset }}%
            </el-button>
          </div>
        </div>
      </section>

      <section class="settings-section">
        <h4 class="settings-section__title">Khác</h4>
        <div class="settings-row">
          <div class="settings-row__meta">
            <span class="settings-row__label">Chế độ tối</span>
            <span class="settings-row__desc">Bật / tắt giao diện tối</span>
          </div>
          <el-switch v-model="isDark" @change="onDarkChange" />
        </div>
      </section>
    </div>

    <template #footer>
      <div class="settings-footer">
        <el-button @click="layoutStore.reset()">Khôi phục mặc định</el-button>
        <el-button type="primary" @click="visible = false">Đóng</el-button>
      </div>
    </template>
  </el-drawer>
</template>

<script setup>
import { computed } from 'vue'
import { FONT_OPTIONS, useLayoutStore } from '@/stores/layout'

const visible = defineModel({ type: Boolean, default: false })

const props = defineProps({
  dark: { type: Boolean, default: false },
})

const emit = defineEmits(['update:dark'])
const layoutStore = useLayoutStore()
const fontOptions = FONT_OPTIONS

const fontSizePresets = [
  { label: 'Nhỏ', value: 14 },
  { label: 'Vừa', value: 16 },
  { label: 'Lớn', value: 18 },
  { label: 'Rất lớn', value: 20 },
]
const fontSizeMarks = {
  12: '12',
  16: '16',
  20: '20',
}

const scalePresets = [90, 100, 110, 125]
const scaleMarks = {
  80: '80%',
  100: '100%',
  125: '125%',
}

const isDark = computed({
  get: () => props.dark,
  set: (val) => emit('update:dark', val),
})

const navbarOptions = [
  { label: 'Cố định', value: 'fixed' },
  { label: 'Cuộn theo', value: 'scroll' },
]

const navbarMode = computed({
  get: () => (layoutStore.navbarFixed ? 'fixed' : 'scroll'),
  set: (val) => {
    layoutStore.navbarFixed = val === 'fixed'
  },
})

function onDarkChange(val) {
  document.documentElement.classList.toggle('dark', val)
  localStorage.setItem('darkMode', val ? '1' : '0')
}
</script>

<style scoped lang="scss">
.settings-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.settings-section {
  padding: 4px 0 12px;
  border-bottom: 1px solid var(--el-border-color-lighter);

  &:last-child {
    border-bottom: none;
  }

  &__title {
    margin: 0 0 12px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--el-text-color-secondary);
  }
}

.settings-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  padding: 10px 0;

  &.is-disabled {
    opacity: 0.5;
  }

  &--stack {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  &__meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;

    &--inline {
      flex-direction: row;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
    }
  }

  &__label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--el-text-color-primary);
  }

  &__desc {
    display: block;
    font-size: 0.75rem;
    line-height: 1.4;
    color: var(--el-text-color-secondary);
  }
}

.settings-control {
  width: 100%;
}

.settings-slider {
  padding: 0 6px;
  margin-bottom: 4px;
}

.settings-scale-value {
  flex-shrink: 0;
  font-size: 0.8125rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
  color: var(--el-color-primary);
}

.settings-scale-presets {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.settings-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
</style>
