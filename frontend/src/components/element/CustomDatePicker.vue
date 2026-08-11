<script setup>
/**
 * CustomDatePicker — wrapper el-date-picker.
 */
import { ref, useSlots } from 'vue'

defineOptions({ name: 'CustomDatePicker', inheritAttrs: false })

const model = defineModel({ default: undefined })
const pickerRef = ref(null)
const slots = useSlots()

defineExpose({
  focus: (...args) => pickerRef.value?.focus?.(...args),
  blur: (...args) => pickerRef.value?.blur?.(...args),
  handleOpen: (...args) => pickerRef.value?.handleOpen?.(...args),
  handleClose: (...args) => pickerRef.value?.handleClose?.(...args),
})
</script>

<template>
  <el-date-picker ref="pickerRef" v-model="model" v-bind="$attrs">
    <template v-for="(_, name) in slots" #[name]="slotData">
      <slot :name="name" v-bind="slotData || {}" />
    </template>
  </el-date-picker>
</template>
