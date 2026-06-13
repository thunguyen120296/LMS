import apiClient from '../../../services/APIClient'
import { IAM_API_BASE } from '../../../services/api.config'
import type { ApiResponse } from '../../../shared/types/api.types'
import { getApiErrorMessage } from '../../../shared/utils/apiError'
import type {
  LoginRequest,
  LoginResponse,
  RefreshTokenResponse,
  RegisterRequest,
  RegisterResponse,
} from '../types/auth.types'

const client = apiClient(IAM_API_BASE)

function assertSuccess<T>(response: ApiResponse<T>, fallbackMessage: string): T {
  if (!response.success) {
    const message =
      response.errors?.map((item) => item.message).join('. ') ||
      response.message ||
      fallbackMessage

    throw new Error(message)
  }

  return response.data as T
}

export async function loginUser(payload: LoginRequest): Promise<LoginResponse> {
  try {
    const { data } = await client.post<ApiResponse<null>>('/login', {
      username: payload.email,
      password: payload.password,
    })

    assertSuccess(data, 'Đăng nhập thất bại')

    return { message: data.message }
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể đăng nhập. Vui lòng thử lại sau.'))
  }
}

export async function refreshToken(): Promise<RefreshTokenResponse> {
  try {
    const { data } = await client.post<ApiResponse<null>>('/refresh/token')

    assertSuccess(data, 'Refresh token thất bại')

    return { message: data.message }
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể làm mới phiên đăng nhập.'))
  }
}

export async function registerUser(payload: RegisterRequest): Promise<RegisterResponse> {
  try {
    const { data } = await client.post<ApiResponse<Omit<RegisterResponse, 'message'>>>('/register', {
      fullName: payload.fullName,
      email: payload.email,
      password: payload.password,
    })

    const result = assertSuccess(data, 'Đăng ký thất bại')

    return {
      userId: result.userId,
      email: result.email,
      message: data.message,
    }
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể đăng ký. Vui lòng thử lại sau.'))
  }
}
