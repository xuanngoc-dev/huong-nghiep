import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { request } from '@/api'
import { API_PUBLIC } from '@/constants/constant_api'

/** Số câu hỏi ngẫu nhiên lấy ra cho mỗi loại (Holland-style). */
export const QUESTIONS_PER_LOAI = 10

function normalizeMa(ma) {
  return String(ma || '').trim().toLowerCase()
}

export const useQuizStore = defineStore('quiz', () => {
  const loaiCauHoi = ref([])
  const loading = ref(false)
  const loaded = ref(false)
  const error = ref(null)

  /** Câu hỏi đã chọn theo mã loại: { [ma]: Question[] } */
  const questionsByLoai = ref({})
  /** Đáp án đã chọn theo mã loại: { [ma]: { [cauHoiId]: answerId } } */
  const answersByLoai = ref({})
  /** Các bước non-loai đã hoàn thành (start / fields / result) — giữ khi quay lại sửa */
  const completedStepIds = ref([])

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

  function resetSession() {
    questionsByLoai.value = {}
    answersByLoai.value = {}
    completedStepIds.value = []
  }

  function markStepCompleted(stepId) {
    if (!stepId || completedStepIds.value.includes(stepId)) return
    completedStepIds.value = [...completedStepIds.value, stepId]
  }

  function getQuestions(maLoaiCauHoi) {
    const ma = normalizeMa(maLoaiCauHoi)
    return questionsByLoai.value[ma] || []
  }

  function getAnswers(maLoaiCauHoi) {
    const ma = normalizeMa(maLoaiCauHoi)
    return answersByLoai.value[ma] || {}
  }

  function setAnswer(maLoaiCauHoi, cauHoiId, cauTraLoiId) {
    const ma = normalizeMa(maLoaiCauHoi)
    const current = { ...(answersByLoai.value[ma] || {}) }
    if (cauTraLoiId == null) {
      delete current[cauHoiId]
    } else {
      current[cauHoiId] = cauTraLoiId
    }
    answersByLoai.value = {
      ...answersByLoai.value,
      [ma]: current,
    }
  }

  function isLoaiComplete(maLoaiCauHoi) {
    const questions = getQuestions(maLoaiCauHoi)
    if (!questions.length) return false
    const answers = getAnswers(maLoaiCauHoi)
    return questions.every((q) => answers[q.id] != null)
  }

  /**
   * Bước đã trả lời đủ — không phụ thuộc vị trí hiện tại
   * (quay về bước trước sửa vẫn giữ trạng thái bước đã hoàn thành).
   */
  function isStepComplete(stepItem) {
    if (!stepItem) return false
    if (stepItem.type === 'loai') {
      return isLoaiComplete(stepItem.maLoaiCauHoi)
    }
    return completedStepIds.value.includes(stepItem.id)
  }

  /**
   * Snapshot đáp án của 1 loại — dùng để log / gửi API sau này.
   */
  function buildLoaiPayload(maLoaiCauHoi) {
    const ma = normalizeMa(maLoaiCauHoi)
    const questions = getQuestions(ma)
    const answers = getAnswers(ma)

    const items = questions.map((q) => {
      const answerId = answers[q.id] ?? null
      const answer = (q.cau_tra_lois || q.cauTraLois || []).find((a) => a.id === answerId) || null
      return {
        cau_hoi_id: q.id,
        noi_dung_cau_hoi: q.noi_dung_cau_hoi,
        cau_tra_loi_id: answer?.id ?? null,
        noi_dung_cau_tra_loi: answer?.noi_dung_cau_tra_loi ?? null,
        diem: answer?.diem ?? null,
      }
    })

    const totalScore = items.reduce((sum, item) => sum + (Number(item.diem) || 0), 0)

    return {
      ma_loai_cau_hoi: ma,
      so_cau: items.length,
      tong_diem: totalScore,
      answers: items,
    }
  }

  /**
   * Tải N câu ngẫu nhiên cho loại — giữ nguyên bộ đã chọn nếu quay lại bước.
   */
  async function ensureQuestionsForLoai(maLoaiCauHoi, { force = false, limit = QUESTIONS_PER_LOAI } = {}) {
    const ma = normalizeMa(maLoaiCauHoi)
    if (!ma) {
      return { ok: false, message: 'Thiếu mã loại câu hỏi.' }
    }

    if (!force && questionsByLoai.value[ma]?.length) {
      return { ok: true, questions: questionsByLoai.value[ma] }
    }

    const res = await request({
      url: API_PUBLIC.TRAC_NGHIEM_CAU_HOI.LIST,
      params: {
        ma_loai_cau_hoi: ma,
        limit,
      },
      loading: false,
      silent: true,
    })

    if (!res.ok) {
      return {
        ok: false,
        message: res.message || 'Không tải được câu hỏi.',
      }
    }

    const questions = Array.isArray(res.data?.questions) ? res.data.questions : []
    questionsByLoai.value = {
      ...questionsByLoai.value,
      [ma]: questions,
    }

    if (!answersByLoai.value[ma]) {
      answersByLoai.value = {
        ...answersByLoai.value,
        [ma]: {},
      }
    }

    return { ok: true, questions }
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
    questionsByLoai,
    answersByLoai,
    completedStepIds,
    steps,
    findStepByRoute,
    resolveCurrentStep,
    toLocation,
    getAdjacent,
    resetSession,
    markStepCompleted,
    getQuestions,
    getAnswers,
    setAnswer,
    isLoaiComplete,
    isStepComplete,
    buildLoaiPayload,
    ensureQuestionsForLoai,
    ensureLoaded,
  }
})
