<script setup>
/**
 * CustomAlert — wrapper el-alert.
 */
import { useSlots } from 'vue'

defineOptions({ name: 'CustomAlert', inheritAttrs: false })

const slots = useSlots()
</script>

<template>
  <el-alert v-bind="$attrs">
    <template v-for="(_, name) in slots" #[name]="slotData">
      <slot :name="name" v-bind="slotData || {}" />
    </template>
  </el-alert>
</template>
