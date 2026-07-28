import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { request } from '@/api'
import { API_PUBLIC } from '@/constants/constant_api'

function normalizeMa(ma) {
  return String(ma || '').trim().toLowerCase()
}

export const useQuizStore = defineStore('quiz', () => {
  const loaiCauHoi = ref([])
  const loading = ref(false)
  const loaded = ref(false)
  const error = ref(null)

  const steps = computed(() => {
    const middle = loaiCauHoi.value.map((item, index) => {
      const ma = normalizeMa(item.ma_loai_cau_hoi)
      return {
        id: `loai-${item.id}`,
        step: index + 2,
        label: item.ten_loai_cau_hoi,
        path: `loai/${ma}`,
        routeName: 'quiz-loai',
        type: 'loai',
        loaiCauHoiId: item.id,
        maLoaiCauHoi: ma,
        thuTuUuTien: item.thu_tu_uu_tien,
      }
    })

    const fieldsStep = middle.length + 2
    const resultStep = middle.length + 3

    return [
      {
        id: 'start',
        step: 1,
        label: 'Bắt đầu',
        path: 'bat-dau',
        routeName: 'quiz-start',
        type: 'start',
      },
      ...middle,
      {
        id: 'fields',
        step: fieldsStep,
        label: 'Ngành phù hợp',
        path: 'nganh-phu-hop',
        routeName: 'quiz-fields',
        type: 'fields',
        section: 'fields',
      },
      {
        id: 'result',
        step: resultStep,
        label: 'Kết quả',
        path: 'ket-qua',
        routeName: 'quiz-result',
        type: 'result',
      },
    ]
  })

  function findStepByRoute(route) {
    if (!route?.name) return null
    if (route.name === 'quiz-start') {
      return steps.value.find((item) => item.type === 'start') || null
    }
    if (route.name === 'quiz-fields') {
      return steps.value.find((item) => item.type === 'fields') || null
    }
    if (route.name === 'quiz-result') {
      return steps.value.find((item) => item.type === 'result') || null
    }
    if (route.name === 'quiz-loai') {
      const ma = normalizeMa(route.params?.maLoaiCauHoi)
      return (
        steps.value.find((item) => item.type === 'loai' && item.maLoaiCauHoi === ma) || null
      )
    }
    return null
  }

  function resolveCurrentStep(route) {
    return findStepByRoute(route)?.step || 1
  }

  function toLocation(step) {
    if (!step) return { name: 'quiz-start' }
    if (step.routeName === 'quiz-loai') {
      return {
        name: 'quiz-loai',
        params: { maLoaiCauHoi: step.maLoaiCauHoi },
      }
    }
    return { name: step.routeName }
  }

  function getAdjacent(route, delta = 1) {
    const current = findStepByRoute(route)
    if (!current) return null
    return steps.value.find((item) => item.step === current.step + delta) || null
  }

  async function ensureLoaded({ force = false } = {}) {
    if (loaded.value && !force) return true
    if (loading.value) return loaded.value

    loading.value = true
    error.value = null

    try {
      const res = await request({
        url: API_PUBLIC.LOAI_CAU_HOI.LIST,
        loading: false,
        silent: true,
      })

      if (!res.ok) {
        error.value = res.message || 'Không tải được danh sách loại câu hỏi.'
        loaiCauHoi.value = []
        loaded.value = false
        return false
      }

      const list = Array.isArray(res.data) ? res.data : []
      loaiCauHoi.value = [...list].sort(
        (a, b) =>
          Number(a.thu_tu_uu_tien || 0) - Number(b.thu_tu_uu_tien || 0) ||
          String(a.ten_loai_cau_hoi || '').localeCompare(String(b.ten_loai_cau_hoi || ''), 'vi'),
      )
      loaded.value = true
      return true
    } catch {
      error.value = 'Không tải được danh sách loại câu hỏi.'
      loaiCauHoi.value = []
      loaded.value = false
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    loaiCauHoi,
    loading,
    loaded,
    error,
    steps,
    findStepByRoute,
    resolveCurrentStep,
    toLocation,
    getAdjacent,
    ensureLoaded,
  }
})
