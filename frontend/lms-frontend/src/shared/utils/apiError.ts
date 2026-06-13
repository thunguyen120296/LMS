import axios from 'axios'
import type { ApiResponse } from '../types/api.types'

export function getApiErrorMessage(error: unknown, fallback: string): string {
  if (axios.isAxiosError(error) && error.response?.data) {
    const data = error.response.data as Partial<ApiResponse>

    if (data.errors?.length) {
      return data.errors.map((item) => item.message).join('. ')
    }

    if (typeof data.message === 'string' && data.message.trim()) {
      return data.message
    }
  }

  if (error instanceof Error && error.message.trim()) {
    return error.message
  }

  return fallback
}
