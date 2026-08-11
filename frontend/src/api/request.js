import { computed, ref } from 'vue'
import { ElLoading, ElMessage } from 'element-plus'
import api from './axios'

/** Số request đang chạy (hỗ trợ gọi song song). */
const pendingCount = ref(0)

/** true khi đang có ít nhất 1 request — dùng bind UI nếu cần. */
export const isRequestLoading = computed(() => pendingCount.value > 0)

let loadingInstance = null

function startLoading() {
  pendingCount.value += 1
  if (!loadingInstance) {
    loadingInstance = ElLoading.service({
      lock: true,
      text: 'Đang xử lý...',
      background: 'rgba(0, 0, 0, 0.35)',
    })
  }
}

function stopLoading() {
  pendingCount.value = Math.max(0, pendingCount.value - 1)
  if (pendingCount.value === 0 && loadingInstance) {
    loadingInstance.close()
    loadingInstance = null
  }
}

/**
 * Lấy message lỗi từ body API hoặc axios error.
 * Ưu tiên message do API trả về (tránh "Request failed with status code 429").
 */
export function apiErrorMessage(errOrBody, fallback = 'Đã xảy ra lỗi.') {
  const data = errOrBody?.response?.data

  if (data?.message) return data.message

  const fieldError = Object.values(data?.errors || {})[0]?.[0]
  if (fieldError) return fieldError

  // Body API trực tiếp: { status: boolean, message }
  if (errOrBody && typeof errOrBody === 'object' && typeof errOrBody.status === 'boolean') {
    return errOrBody.message || fallback
  }

  // Bỏ qua message mặc định của Axios (Request failed with status code ...)
  if (errOrBody?.isAxiosError || errOrBody?.response) {
    return fallback
  }

  return errOrBody?.message || fallback
}

/** Class toast API — style nền đậm ở AdminLayout. */
const API_MESSAGE_CLASS = 'api-request-message'

function toastSuccess(message) {
  ElMessage.success({ message, customClass: API_MESSAGE_CLASS })
}

function toastError(message) {
  ElMessage.error({ message, customClass: API_MESSAGE_CLASS })
}

/**
 * Toast theo status — ưu tiên message từ API.
 * @returns {boolean}
 */
export function notifyApi(body, options = {}) {
  const {
    successFallback = 'Thành công.',
    errorFallback = 'Đã xảy ra lỗi.',
    silent = false,
    silentSuccess = false,
    silentError = false,
  } = options

  if (silent) return body?.status === true

  const ok = body?.status === true
  const message = body?.message || (ok ? successFallback : errorFallback)

  if (ok) {
    if (!silentSuccess) toastSuccess(message)
  } else if (!silentError) {
    toastError(message)
  }

  return ok
}

function resolveEndpoint(url) {
  if (!url) {
    throw new Error('request: thiếu url (constant_api).')
  }
  if (typeof url === 'function') {
    throw new Error('request: url là hàm — gọi kèm id trước, vd: API_NGANH_HOC.UPDATE(id).')
  }
  if (typeof url === 'string') {
    return { path: url, method: 'get' }
  }
  if (typeof url === 'object' && url.url) {
    return {
      path: url.url,
      method: String(url.method || 'get').toLowerCase(),
    }
  }
  throw new Error('request: url không hợp lệ.')
}

function buildAxiosCall({ path, method, params, body }) {
  switch (method) {
    case 'get':
      return () => api.get(path, { params })
    case 'delete':
      return () => api.delete(path, { params })
    case 'post':
      return () => api.post(path, body)
    case 'put':
      return () => api.put(path, body)
    case 'patch':
      return () => api.patch(path, body)
    default:
      throw new Error(`request: method không hỗ trợ "${method}".`)
  }
}

async function runRequest(executor, options = {}) {
  const {
    loading = true,
    silent = false,
    silentSuccess = false,
    silentError = false,
    successFallback = 'Thành công.',
    errorFallback = 'Đã xảy ra lỗi.',
  } = options

  if (loading) startLoading()

  try {
    const response = await executor()
    const resBody = response?.data ?? response

    const ok = typeof resBody?.status === 'boolean' ? resBody.status === true : true
    const message = resBody?.message || (ok ? successFallback : errorFallback)

    if (!silent) {
      if (ok && !silentSuccess) toastSuccess(message)
      if (!ok && !silentError) toastError(message)
    }

    return {
      ok,
      status: ok,
      message,
      data: resBody?.data ?? null,
      total: resBody?.total,
      start: resBody?.start,
      limit: resBody?.limit,
      errors: resBody?.errors,
      body: resBody,
      error: null,
    }
  } catch (error) {
    const message = apiErrorMessage(error, errorFallback)
    if (!silent && !silentError) toastError(message)

    return {
      ok: false,
      status: false,
      message,
      data: null,
      total: 0,
      start: undefined,
      limit: undefined,
      errors: error?.response?.data?.errors,
      body: error?.response?.data ?? null,
      error,
    }
  } finally {
    if (loading) stopLoading()
  }
}

/**
 * Gọi API tập trung — tự loading toàn cục + toast theo message API.
 *
 * @example
 * const url = API_NGANH_HOC.LIST
 * const params = { q, start, limit }
 * const res = await request({ url, params })
 *
 * // Tắt loading / toast khi cần:
 * await request({ url, params, loading: false, silent: true })
 *
 * @param {{
 *   url: { url: string, method: string } | string,
 *   params?: object,
 *   body?: object,
 *   loading?: boolean,
 *   silent?: boolean,
 *   silentSuccess?: boolean,
 *   silentError?: boolean,
 *   successFallback?: string,
 *   errorFallback?: string,
 * }} config
 */
export async function request(config = {}) {
  const { url, params, body, ...options } = config
  const { path, method } = resolveEndpoint(url)
  const executor = buildAxiosCall({ path, method, params, body })
  return runRequest(executor, options)
}

/**
 * Shortcut HTTP (không dùng constant_api).
 */
export const http = {
  get: (url, config = {}, options = {}) =>
    request({ url: { url, method: 'get' }, params: config.params, ...options }),
  post: (url, body, config = {}, options = {}) =>
    request({ url: { url, method: 'post' }, body, params: config.params, ...options }),
  put: (url, body, config = {}, options = {}) =>
    request({ url: { url, method: 'put' }, body, params: config.params, ...options }),
  patch: (url, body, config = {}, options = {}) =>
    request({ url: { url, method: 'patch' }, body, params: config.params, ...options }),
  delete: (url, config = {}, options = {}) =>
    request({ url: { url, method: 'delete' }, params: config.params, ...options }),
}

export default request
