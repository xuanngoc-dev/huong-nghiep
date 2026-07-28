<script setup>
/**
 * CustomCol — wrapper el-col.
 *
 * Mặc định (khi không truyền span / xs / sm / md / lg / xl):
 *   xl/lg → 4  (6 cột / hàng)
 *   md    → 6  (4 cột / hàng)
 *   sm    → 8  (3 cột / hàng)
 *   xs    → 12 (2 cột / hàng)
 *
 * Đặc thù: truyền props để override.
 * Full hàng: `:span="24"` (tự set full mọi breakpoint).
 */
import { computed, useAttrs, useSlots } from 'vue'

defineOptions({ name: 'CustomCol', inheritAttrs: false })

const props = defineProps({
  span: { type: [Number, Object], default: undefined },
  xs: { type: [Number, Object], default: undefined },
  sm: { type: [Number, Object], default: undefined },
  md: { type: [Number, Object], default: undefined },
  lg: { type: [Number, Object], default: undefined },
  xl: { type: [Number, Object], default: undefined },
  offset: { type: Number, default: undefined },
  push: { type: Number, default: undefined },
  pull: { type: Number, default: undefined },
  tag: { type: String, default: 'div' },
})

const attrs = useAttrs()
const slots = useSlots()

const DEFAULT_BREAKPOINTS = Object.freeze({
  xs: 12,
  sm: 8,
  md: 6,
  lg: 4,
  xl: 4,
})

const colProps = computed(() => {
  const hasBreakpoint =
    props.xs != null ||
    props.sm != null ||
    props.md != null ||
    props.lg != null ||
    props.xl != null

  const base = {}
  if (props.offset != null) base.offset = props.offset
  if (props.push != null) base.push = props.push
  if (props.pull != null) base.pull = props.pull
  if (props.tag) base.tag = props.tag

  // Full-width shorthand
  if (props.span === 24 && !hasBreakpoint) {
    return {
      ...base,
      span: 24,
      xs: 24,
      sm: 24,
      md: 24,
      lg: 24,
      xl: 24,
    }
  }

  // Không truyền gì → mặc định responsive 6→2 cột/hàng
  if (props.span == null && !hasBreakpoint) {
    return { ...base, ...DEFAULT_BREAKPOINTS }
  }

  // Có truyền tường minh → tôn trọng (không ép default lên breakpoint còn thiếu)
  return {
    ...base,
    ...(props.span != null ? { span: props.span } : {}),
    ...(props.xs != null ? { xs: props.xs } : {}),
    ...(props.sm != null ? { sm: props.sm } : {}),
    ...(props.md != null ? { md: props.md } : {}),
    ...(props.lg != null ? { lg: props.lg } : {}),
    ...(props.xl != null ? { xl: props.xl } : {}),
  }
})
</script>

<template>
  <el-col v-bind="{ ...colProps, ...attrs }">
    <template v-for="(_, name) in slots" #[name]="slotData">
      <slot :name="name" v-bind="slotData || {}" />
    </template>
  </el-col>
</template>
