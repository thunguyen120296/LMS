import axios from 'axios'
import apiClient from '../../../services/APIClient'
import { IAM_API_BASE } from '../../../services/api.config'
import type { ApiResponse } from '../../../shared/types/api.types'
import type {
  LoginRequest,
  LoginResponse,
  RegisterRequest,
  RegisterResponse,
} from '../types/auth.types'

const client = apiClient(IAM_API_BASE)

const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms))

export async function loginUser(payload: LoginRequest): Promise<LoginResponse> {
  try {
    const { data } = await client.post<{ message: string }>('/login', {
      username: payload.email,
      password: payload.password,
      fullName: payload.fullName,
    })
    return { message: data.message }
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      throw new Error('Email hoặc mật khẩu không đúng')
    }
    throw new Error('Không thể đăng nhập. Vui lòng thử lại sau.')
  }
}

export async function registerUser(
  payload: RegisterRequest,
): Promise<ApiResponse<RegisterResponse>> {
  await delay(600)

  return {
    success: true,
    message: `Đăng ký tài khoản ${payload.email} thành công`,
    data: { userId: crypto.randomUUID() },
    errors: null,
  }
}
