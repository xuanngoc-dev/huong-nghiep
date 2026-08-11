<script setup>
/**
 * CustomInput — wrapper el-input.
 * Chỉnh style / hành vi mặc định tại đây để áp dụng toàn CMS.
 * Khi disabled và nội dung bị truncat, hiện tooltip khi hover.
 */
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useAttrs, useSlots, watch } from 'vue'
import CustomTooltip from './CustomTooltip.vue'

defineOptions({ name: 'CustomInput', inheritAttrs: false })

const model = defineModel({ default: undefined })
const inputRef = ref(null)
const slots = useSlots()
const attrs = useAttrs()

const isOverflow = ref(false)
let resizeObserver = null

const isDisabled = computed(() => {
  const d = attrs.disabled
  return d === true || d === '' || d === 'disabled'
})

const tooltipContent = computed(() => {
  if (!isDisabled.value || !isOverflow.value) return ''
  const v = model.value
  if (v == null || v === '') return ''
  return String(v)
})

const tooltipDisabled = computed(() => !tooltipContent.value)

function getNativeInput() {
  const comp = inputRef.value
  if (!comp) return null
  return comp.input || comp.textarea || comp.$el?.querySelector?.('input, textarea') || null
}

function checkOverflow() {
  nextTick(() => {
    if (!isDisabled.value) {
      isOverflow.value = false
      return
    }
    const el = getNativeInput()
    if (!el) {
      isOverflow.value = false
      return
    }
    isOverflow.value =
      el.scrollWidth > el.clientWidth + 1 || el.scrollHeight > el.clientHeight + 1
  })
}

function bindResizeObserver() {
  resizeObserver?.disconnect()
  resizeObserver = null
  const el = getNativeInput()
  if (!el || typeof ResizeObserver === 'undefined') return
  resizeObserver = new ResizeObserver(() => checkOverflow())
  resizeObserver.observe(el)
}

onMounted(() => {
  checkOverflow()
  bindResizeObserver()
})

onBeforeUnmount(() => {
  resizeObserver?.disconnect()
  resizeObserver = null
})

watch([model, isDisabled], () => {
  checkOverflow()
  bindResizeObserver()
})

defineExpose({
  focus: (...args) => inputRef.value?.focus?.(...args),
  blur: (...args) => inputRef.value?.blur?.(...args),
  select: (...args) => inputRef.value?.select?.(...args),
  clear: (...args) => inputRef.value?.clear?.(...args),
})
</script>

<template>
  <CustomTooltip :content="tooltipContent" :disabled="tooltipDisabled" placement="top" :show-after="200">
    <!-- span nhận hover vì input disabled không fire mouse events -->
    <span class="custom-input-tooltip-trigger">
      <el-input ref="inputRef" v-model="model" v-bind="$attrs">
        <template v-for="(_, name) in slots" #[name]="slotData">
          <slot :name="name" v-bind="slotData || {}" />
        </template>
      </el-input>
    </span>
  </CustomTooltip>
</template>

<style scoped>
.custom-input-tooltip-trigger {
  display: inline-block;
  width: 100%;
  max-width: 100%;
  vertical-align: bottom;
}
</style>
