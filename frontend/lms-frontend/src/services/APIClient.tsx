import axios, {
  type AxiosError,
  type AxiosInstance,
  type InternalAxiosRequestConfig,
} from 'axios'
import { IAM_API_BASE } from './api.config'
import type { ApiResponse } from '../shared/types/api.types'
import { applyMeToStore, clearAuthSession } from '../features/auth/utils/auth.session'
import type { MeResponse } from '../features/auth/types/auth.types'

interface RetryableRequestConfig extends InternalAxiosRequestConfig {
  _retry?: boolean
}

const AUTH_SKIP_PATHS = ['/login', '/register', '/refresh/token', '/logout', '/verify-email']

let refreshPromise: Promise<void> | null = null

function shouldSkipTokenRefresh(url?: string): boolean {
  if (!url) return false
  return AUTH_SKIP_PATHS.some((path) => url.includes(path))
}

function redirectToLogin() {
  if (window.location.pathname !== '/login') {
    window.location.href = '/login'
  }
}

async function performTokenRefresh(): Promise<void> {
  const refreshResponse = await axios.post<ApiResponse<null>>(
    `${IAM_API_BASE}/refresh/token`,
    {},
    { withCredentials: true },
  )

  if (!refreshResponse.data.success) {
    throw new Error(refreshResponse.data.message || 'Refresh token thất bại')
  }

  const meResponse = await axios.get<ApiResponse<MeResponse>>(`${IAM_API_BASE}/me`, {
    withCredentials: true,
  })

  if (!meResponse.data.success || !meResponse.data.data) {
    throw new Error(meResponse.data.message || 'Không thể lấy thông tin người dùng')
  }

  applyMeToStore(meResponse.data.data)
}

function ensureFreshToken(): Promise<void> {
  if (!refreshPromise) {
    refreshPromise = performTokenRefresh().finally(() => {
      refreshPromise = null
    })
  }

  return refreshPromise
}

function attachAuthInterceptor(instance: AxiosInstance) {
  instance.interceptors.response.use(
    (response) => response,
    async (error: AxiosError) => {
      const originalRequest = error.config as RetryableRequestConfig | undefined

      if (
        !originalRequest ||
        error.response?.status !== 401 ||
        originalRequest._retry ||
        shouldSkipTokenRefresh(originalRequest.url)
      ) {
        return Promise.reject(error)
      }

      originalRequest._retry = true

      try {
        await ensureFreshToken()
        return instance(originalRequest)
      } catch (refreshError) {
        clearAuthSession()
        redirectToLogin()
        return Promise.reject(refreshError)
      }
    },
  )
}

export default function apiClient(baseURL: string): AxiosInstance {
  const instance = axios.create({
    baseURL,
    withCredentials: true,
  })

  attachAuthInterceptor(instance)
  return instance
}
