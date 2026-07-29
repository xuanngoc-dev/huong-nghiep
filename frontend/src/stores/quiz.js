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
  /** Session id phiên làm bài (lưu lịch sử trả lời) */
  const ssid = ref(null)

  /** Cache lịch sử theo ssid */
  const historyLoadedForSsid = ref(null)
  const historyLoading = ref(false)
  /** { [ma]: { questions, answers, answered_count, ... } } */
  const historyByLoai = ref({})
  /** Các mã loại đã trả lời đủ theo lịch sử ssid */
  const historyCompletedLoai = ref([])
  /** Phiên đã lưu vào bảng hoàn thành — không cho cập nhật đáp án */
  const sessionCompleted = ref(false)

  /** Tổng hợp ngành / chuyên ngành theo ssid (bước Ngành phù hợp) */
  const fieldsSummary = ref(null)
  const fieldsSummaryLoadedForSsid = ref(null)
  const fieldsSummaryLoading = ref(false)

  /** Promise khóa chống gọi song song khi reload trang */
  let ensureLoadedPromise = null
  let ensureHistoryPromise = null
  let ensureHistoryPromiseSsid = null
  let ensureFieldsSummaryPromise = null
  let ensureFieldsSummaryPromiseSsid = null

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

  function clearHistoryCache() {
    historyLoadedForSsid.value = null
    historyByLoai.value = {}
    historyCompletedLoai.value = []
    sessionCompleted.value = false
    fieldsSummary.value = null
    fieldsSummaryLoadedForSsid.value = null
  }

  /**
   * Đồng bộ ssid từ route params (khi mở lại link phiên cũ).
   */
  function syncSsidFromRoute(route) {
    const fromRoute = route?.params?.ssid
    if (!fromRoute) return null
    const value = String(fromRoute)
    if (ssid.value !== value) {
      ssid.value = value
      clearHistoryCache()
    }
    return value
  }

  /**
   * Điều hướng bước — luôn kèm ssid sau khi đã bắt đầu phiên.
   */
  function toLocation(step, sessionId = ssid.value) {
    if (!step || step.type === 'start' || step.routeName === 'quiz-start') {
      return { name: 'quiz-start' }
    }

    const id = sessionId || ssid.value
    if (!id) {
      return { name: 'quiz-start' }
    }

    if (step.routeName === 'quiz-loai') {
      return {
        name: 'quiz-loai',
        params: {
          ssid: id,
          maLoaiCauHoi: step.maLoaiCauHoi,
        },
      }
    }

    return {
      name: step.routeName,
      params: { ssid: id },
    }
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
    ssid.value = null
    clearHistoryCache()
  }

  function markStepCompleted(stepId) {
    if (!stepId || completedStepIds.value.includes(stepId)) return
    completedStepIds.value = [...completedStepIds.value, stepId]
  }

  function markLoaiCompleted(maLoaiCauHoi) {
    const ma = normalizeMa(maLoaiCauHoi)
    if (!ma || historyCompletedLoai.value.includes(ma)) return
    historyCompletedLoai.value = [...historyCompletedLoai.value, ma]
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
    if (sessionCompleted.value) return

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

  function setQuestionsAndAnswersForLoai(maLoaiCauHoi, questions, answerMap = {}) {
    const ma = normalizeMa(maLoaiCauHoi)
    questionsByLoai.value = {
      ...questionsByLoai.value,
      [ma]: Array.isArray(questions) ? questions : [],
    }
    answersByLoai.value = {
      ...answersByLoai.value,
      [ma]: { ...answerMap },
    }
  }

  /**
   * Gán dữ liệu lịch sử vào store (câu hỏi + đáp án theo loại).
   */
  function applyHistoryPayload(data) {
    const byLoai = data?.by_loai && typeof data.by_loai === 'object' ? data.by_loai : {}
    historyByLoai.value = byLoai
    historyCompletedLoai.value = Array.isArray(data?.completed_loai)
      ? data.completed_loai.map(normalizeMa).filter(Boolean)
      : []
    sessionCompleted.value = Boolean(data?.da_hoan_thanh)

    const nextQuestions = { ...questionsByLoai.value }
    const nextAnswers = { ...answersByLoai.value }

    for (const [rawMa, group] of Object.entries(byLoai)) {
      const ma = normalizeMa(rawMa || group?.ma_loai_cau_hoi)
      if (!ma) continue

      if (Array.isArray(group?.questions) && group.questions.length) {
        nextQuestions[ma] = group.questions
      }

      const map = { ...(nextAnswers[ma] || {}) }
      for (const item of group?.answers || []) {
        if (item?.cau_hoi_id == null || item?.cau_tra_loi_id == null) continue
        map[item.cau_hoi_id] = item.cau_tra_loi_id
      }
      nextAnswers[ma] = map

      const answeredCount = Object.keys(map).length
      const questionCount = Number(group?.question_count || group?.questions?.length || 0)
      if (
        questionCount > 0 &&
        answeredCount >= questionCount &&
        !historyCompletedLoai.value.includes(ma)
      ) {
        historyCompletedLoai.value = [...historyCompletedLoai.value, ma]
      }
    }

    questionsByLoai.value = nextQuestions
    answersByLoai.value = nextAnswers
    markStepCompleted('start')
  }

  function isLoaiComplete(maLoaiCauHoi) {
    const ma = normalizeMa(maLoaiCauHoi)
    if (!ma) return false

    // Đã hoàn thành theo lịch sử ssid (kể cả khi chưa load câu hỏi của loại đó vào UI)
    if (historyCompletedLoai.value.includes(ma)) return true

    const historyGroup = historyByLoai.value[ma]
    const historyQuestionCount = Number(
      historyGroup?.question_count || historyGroup?.questions?.length || 0,
    )
    const historyAnsweredCount = Number(historyGroup?.answered_count || 0)
    if (historyQuestionCount > 0 && historyAnsweredCount >= historyQuestionCount) {
      return true
    }

    const questions = getQuestions(ma)
    if (!questions.length) return false
    const answers = getAnswers(ma)
    return questions.every((q) => answers[q.id] != null)
  }

  /**
   * Bước đã trả lời đủ — không phụ thuộc vị trí hiện tại.
   * Loại câu hỏi: dựa trên lịch sử ssid + đáp án local.
   */
  function isStepComplete(stepItem) {
    if (!stepItem) return false
    if (stepItem.type === 'start') {
      return Boolean(ssid.value) || completedStepIds.value.includes('start')
    }
    if (stepItem.type === 'loai') {
      return isLoaiComplete(stepItem.maLoaiCauHoi)
    }
    return completedStepIds.value.includes(stepItem.id)
  }

  /**
   * Snapshot đáp án của 1 loại — dùng để log / gửi API.
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
        nganh_hoc_id: q.nganh_hoc_id ?? null,
        chuyen_nganh_id: q.chuyen_nganh_id ?? null,
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
   * Tạo phiên lịch sử trả lời (ssid) khi ấn Bắt đầu.
   * Backend random đủ câu hỏi mọi bước và ghi vào lịch sử.
   */
  async function startHistorySession() {
    const res = await request({
      url: API_PUBLIC.TRAC_NGHIEM_LICH_SU_TRA_LOI.START,
      body: { limit: QUESTIONS_PER_LOAI },
      loading: false,
      silent: true,
    })

    if (!res.ok || !res.data?.ssid) {
      return {
        ok: false,
        message: res.message || 'Không tạo được phiên làm bài.',
      }
    }

    clearHistoryCache()
    ssid.value = res.data.ssid
    applyHistoryPayload(res.data)
    historyLoadedForSsid.value = res.data.ssid
    markStepCompleted('start')
    return { ok: true, ssid: res.data.ssid, data: res.data }
  }

  /**
   * Tải lịch sử trả lời theo ssid rồi hydrate store.
   */
  async function ensureHistoryLoaded(sessionId = ssid.value, { force = false } = {}) {
    const id = sessionId || ssid.value
    if (!id) {
      return { ok: false, message: 'Thiếu phiên làm bài.' }
    }

    if (!force && historyLoadedForSsid.value === id) {
      return {
        ok: true,
        cached: true,
        data: {
          ssid: id,
          by_loai: historyByLoai.value,
          completed_loai: historyCompletedLoai.value,
        },
      }
    }

    if (!force && ensureHistoryPromise && ensureHistoryPromiseSsid === id) {
      return ensureHistoryPromise
    }

    ensureHistoryPromiseSsid = id
    ensureHistoryPromise = (async () => {
      historyLoading.value = true
      try {
        const res = await request({
          url: API_PUBLIC.TRAC_NGHIEM_LICH_SU_TRA_LOI.SHOW(id),
          loading: false,
          silent: true,
        })

        if (!res.ok) {
          return {
            ok: false,
            message: res.message || 'Không tải được lịch sử trả lời.',
          }
        }

        ssid.value = id
        applyHistoryPayload(res.data || {})
        historyLoadedForSsid.value = id
        return { ok: true, data: res.data }
      } finally {
        historyLoading.value = false
      }
    })()

    try {
      return await ensureHistoryPromise
    } finally {
      if (ensureHistoryPromiseSsid === id) {
        ensureHistoryPromise = null
        ensureHistoryPromiseSsid = null
      }
    }
  }

  /**
   * Lưu đáp án của 1 loại câu hỏi vào bảng lịch sử.
   */
  async function saveLoaiAnswers(maLoaiCauHoi, sessionId = ssid.value) {
    const currentSsid = sessionId || ssid.value
    const ma = normalizeMa(maLoaiCauHoi)
    if (!currentSsid) {
      return { ok: false, message: 'Thiếu phiên làm bài. Vui lòng bắt đầu lại.' }
    }

    if (sessionCompleted.value) {
      return {
        ok: false,
        message: 'Phiên trắc nghiệm đã hoàn thành, không thể cập nhật câu trả lời.',
      }
    }

    const payload = buildLoaiPayload(ma)
    const answers = payload.answers
      .filter((item) => item.cau_hoi_id != null && item.cau_tra_loi_id != null)
      .map((item) => ({
        cau_hoi_id: item.cau_hoi_id,
        cau_tra_loi_id: item.cau_tra_loi_id,
      }))

    if (!answers.length) {
      return { ok: false, message: 'Chưa có câu trả lời để lưu.' }
    }

    const res = await request({
      url: API_PUBLIC.TRAC_NGHIEM_LICH_SU_TRA_LOI.SAVE,
      body: {
        ssid: currentSsid,
        answers,
      },
      loading: false,
      silent: true,
    })

    if (!res.ok) {
      return {
        ok: false,
        message: res.message || 'Không lưu được lịch sử trả lời.',
      }
    }

    // Cập nhật cache lịch sử local để stepper tích bước ngay
    const currentQuestions = getQuestions(ma)
    historyByLoai.value = {
      ...historyByLoai.value,
      [ma]: {
        ...(historyByLoai.value[ma] || {}),
        ma_loai_cau_hoi: ma,
        question_count: currentQuestions.length,
        answered_count: answers.length,
        questions: currentQuestions,
        answers: answers.map((item) => ({
          cau_hoi_id: item.cau_hoi_id,
          cau_tra_loi_id: item.cau_tra_loi_id,
        })),
      },
    }

    if (currentQuestions.length > 0 && answers.length >= currentQuestions.length) {
      markLoaiCompleted(ma)
    }

    // Làm mới tổng hợp ngành khi có đáp án mới
    if (fieldsSummaryLoadedForSsid.value === currentSsid) {
      fieldsSummaryLoadedForSsid.value = null
    }

    return { ok: true, data: res.data }
  }

  /**
   * Tổng hợp điểm ngành học / chuyên ngành theo ssid (SUM diem_so).
   */
  async function ensureFieldsSummary(sessionId = ssid.value, { force = false } = {}) {
    const id = sessionId || ssid.value
    if (!id) {
      return { ok: false, message: 'Thiếu phiên làm bài.' }
    }

    if (!force && fieldsSummaryLoadedForSsid.value === id && fieldsSummary.value) {
      return { ok: true, cached: true, data: fieldsSummary.value }
    }

    if (!force && ensureFieldsSummaryPromise && ensureFieldsSummaryPromiseSsid === id) {
      return ensureFieldsSummaryPromise
    }

    ensureFieldsSummaryPromiseSsid = id
    ensureFieldsSummaryPromise = (async () => {
      fieldsSummaryLoading.value = true
      try {
        const res = await request({
          url: API_PUBLIC.TRAC_NGHIEM_LICH_SU_TRA_LOI.TONG_HOP(id),
          loading: false,
          silent: true,
        })

        if (!res.ok) {
          return {
            ok: false,
            message: res.message || 'Không tổng hợp được ngành phù hợp.',
          }
        }

        fieldsSummary.value = res.data || null
        fieldsSummaryLoadedForSsid.value = id
        if (res.data?.da_hoan_thanh) {
          sessionCompleted.value = true
        }
        return { ok: true, data: res.data }
      } finally {
        fieldsSummaryLoading.value = false
      }
    })()

    try {
      return await ensureFieldsSummaryPromise
    } finally {
      if (ensureFieldsSummaryPromiseSsid === id) {
        ensureFieldsSummaryPromise = null
        ensureFieldsSummaryPromiseSsid = null
      }
    }
  }

  /**
   * Tải câu hỏi cho loại từ lịch sử ssid (không random lại).
   */
  async function ensureQuestionsForLoai(maLoaiCauHoi, { force = false } = {}) {
    const ma = normalizeMa(maLoaiCauHoi)
    if (!ma) {
      return { ok: false, message: 'Thiếu mã loại câu hỏi.' }
    }

    if (!ssid.value) {
      return { ok: false, message: 'Thiếu phiên làm bài. Vui lòng bắt đầu lại.' }
    }

    await ensureHistoryLoaded(ssid.value, { force })

    if (!force && questionsByLoai.value[ma]?.length) {
      return { ok: true, questions: questionsByLoai.value[ma], from: 'store' }
    }

    const historyGroup = historyByLoai.value[ma]
    if (historyGroup?.questions?.length) {
      const answerMap = {}
      for (const item of historyGroup.answers || []) {
        if (item?.cau_hoi_id == null || item?.cau_tra_loi_id == null) continue
        answerMap[item.cau_hoi_id] = item.cau_tra_loi_id
      }
      setQuestionsAndAnswersForLoai(ma, historyGroup.questions, answerMap)
      return { ok: true, questions: historyGroup.questions, from: 'history' }
    }

    return {
      ok: false,
      message: 'Phiên này chưa có câu hỏi cho bước hiện tại. Vui lòng bắt đầu lại.',
    }
  }

  async function ensureLoaded({ force = false } = {}) {
    if (loaded.value && !force) return true
    if (!force && ensureLoadedPromise) return ensureLoadedPromise

    ensureLoadedPromise = (async () => {
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
    })()

    try {
      return await ensureLoadedPromise
    } finally {
      ensureLoadedPromise = null
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
    ssid,
    historyLoadedForSsid,
    historyLoading,
    historyByLoai,
    historyCompletedLoai,
    sessionCompleted,
    fieldsSummary,
    fieldsSummaryLoadedForSsid,
    fieldsSummaryLoading,
    steps,
    findStepByRoute,
    resolveCurrentStep,
    syncSsidFromRoute,
    toLocation,
    getAdjacent,
    resetSession,
    markStepCompleted,
    markLoaiCompleted,
    getQuestions,
    getAnswers,
    setAnswer,
    isLoaiComplete,
    isStepComplete,
    buildLoaiPayload,
    startHistorySession,
    ensureHistoryLoaded,
    saveLoaiAnswers,
    ensureFieldsSummary,
    ensureQuestionsForLoai,
    ensureLoaded,
  }
})
