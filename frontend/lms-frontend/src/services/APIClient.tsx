import axios, {
  type AxiosError,
  type AxiosInstance,
  type InternalAxiosRequestConfig,
} from 'axios'
import { IAM_API_BASE } from './api.config'
import type { ApiResponse } from '../shared/types/api.types'

interface RetryableRequestConfig extends InternalAxiosRequestConfig {
  _retry?: boolean
}

const AUTH_SKIP_PATHS = ['/login', '/register', '/refresh/token']

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
  const { data } = await axios.post<ApiResponse>(
    `${IAM_API_BASE}/refresh/token`,
    {},
    { withCredentials: true },
  )

  if (!data.success) {
    throw new Error(data.message || 'Refresh token thất bại')
  }
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
