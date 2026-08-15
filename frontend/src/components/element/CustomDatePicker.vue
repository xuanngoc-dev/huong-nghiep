<script setup>
/**
 * CustomDatePicker — wrapper el-date-picker.
 * Cho phép chọn trên lịch hoặc gõ ngày (vd. 27/02/1996, 27-02-1996).
 */
import { onBeforeUnmount, onMounted, ref, useAttrs, useSlots } from 'vue'

defineOptions({ name: 'CustomDatePicker', inheritAttrs: false })

const model = defineModel({ default: undefined })
const pickerRef = ref(null)
const slots = useSlots()
const attrs = useAttrs()

function pad2(value) {
  return String(value).padStart(2, '0')
}

function toYmd(year, month, day) {
  if (!Number.isInteger(year) || !Number.isInteger(month) || !Number.isInteger(day)) {
    return null
  }
  if (year < 1000 || year > 9999 || month < 1 || month > 12 || day < 1 || day > 31) {
    return null
  }
  const date = new Date(year, month - 1, day)
  if (
    date.getFullYear() !== year ||
    date.getMonth() !== month - 1 ||
    date.getDate() !== day
  ) {
    return null
  }
  return `${year}-${pad2(month)}-${pad2(day)}`
}

/** Parse ngày kiểu VN: 27/02/1996, 27-02-1996, 27.02.1996, 27021996, 1996-02-27. */
function parseFlexibleDate(value) {
  const str = String(value || '').trim()
  if (!str) return null

  const iso = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/)
  if (iso) {
    return toYmd(Number(iso[1]), Number(iso[2]), Number(iso[3]))
  }

  const dotted = str.match(/^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$/)
  if (dotted) {
    return toYmd(Number(dotted[3]), Number(dotted[2]), Number(dotted[1]))
  }

  const compact = str.match(/^(\d{2})(\d{2})(\d{4})$/)
  if (compact) {
    return toYmd(Number(compact[3]), Number(compact[2]), Number(compact[1]))
  }

  return null
}

function readRawInput() {
  const input = pickerRef.value?.$el?.querySelector?.('input')
  return String(input?.value || '').trim()
}

function formatModelValue(ymd) {
  const [year, month, day] = ymd.split('-')
  const valueFormat = attrs.valueFormat || attrs['value-format'] || 'DD/MM/YYYY'
  const tokens = { YYYY: year, MM: month, DD: day }
  return String(valueFormat).replace(/YYYY|MM|DD/g, (token) => tokens[token] || token)
}

function isDisabledDate(ymd) {
  const disabled = attrs.disabledDate || attrs['disabled-date']
  if (typeof disabled !== 'function') return false
  return Boolean(disabled(new Date(`${ymd}T00:00:00`)))
}

function syncTypedValue() {
  const raw = readRawInput()
  if (!raw) return

  const ymd = parseFlexibleDate(raw)
  if (!ymd || isDisabledDate(ymd)) return

  model.value = formatModelValue(ymd)
}

function onInnerKeydown(event) {
  if (event.key === 'Enter') {
    syncTypedValue()
  }
}

onMounted(() => {
  const input = pickerRef.value?.$el?.querySelector?.('input')
  input?.addEventListener('keydown', onInnerKeydown)
})

onBeforeUnmount(() => {
  const input = pickerRef.value?.$el?.querySelector?.('input')
  input?.removeEventListener('keydown', onInnerKeydown)
})

defineExpose({
  focus: (...args) => pickerRef.value?.focus?.(...args),
  blur: (...args) => pickerRef.value?.blur?.(...args),
  handleOpen: (...args) => pickerRef.value?.handleOpen?.(...args),
  handleClose: (...args) => pickerRef.value?.handleClose?.(...args),
})
</script>

<template>
  <el-date-picker
    ref="pickerRef"
    v-model="model"
    format="DD/MM/YYYY"
    value-format="DD/MM/YYYY"
    v-bind="$attrs"
    @blur="syncTypedValue"
    @change="syncTypedValue"
  >
    <template v-for="(_, name) in slots" #[name]="slotData">
      <slot :name="name" v-bind="slotData || {}" />
    </template>
  </el-date-picker>
</template>
