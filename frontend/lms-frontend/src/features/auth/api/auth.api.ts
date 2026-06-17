import apiClient from '../../../services/APIClient'
import { IAM_API_BASE } from '../../../services/api.config'
import type { ApiResponse } from '../../../shared/types/api.types'
import { getApiErrorMessage } from '../../../shared/utils/apiError'
import type {
  LoginRequest,
  LoginResponse,
  MeResponse,
  RefreshTokenResponse,
  RegisterRequest,
  RegisterResponse,
} from '../types/auth.types'
import { applyMeToStore, clearAuthSession } from '../utils/auth.session'

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

export async function fetchMe(): Promise<MeResponse> {
  try {
    const { data } = await client.get<ApiResponse<MeResponse>>('/me')
    const result = assertSuccess(data, 'Không thể lấy thông tin người dùng')
    applyMeToStore(result)
    return result
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể lấy thông tin người dùng.'))
  }
}

export async function loginUser(payload: LoginRequest): Promise<LoginResponse> {
  try {
    const { data } = await client.post<ApiResponse<null>>('/login', {
      username: payload.email,
      password: payload.password,
    })

    assertSuccess(data, 'Đăng nhập thất bại')
    const me = await fetchMe()

    return {
      message: data.message,
      user_info: me.user_info,
      permissions: me.permissions,
      roles: me.roles,
    }
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể đăng nhập. Vui lòng thử lại sau.'))
  }
}

export async function logoutUser(): Promise<void> {
  try {
    await client.post<ApiResponse<null>>('/logout')
  } catch {
    // Cookie HttpOnly cần server xóa; nếu API lỗi vẫn clear state phía client.
  } finally {
    clearAuthSession()
  }
}

export async function refreshToken(): Promise<RefreshTokenResponse> {
  try {
    const { data } = await client.post<ApiResponse<null>>('/refresh/token')
    assertSuccess(data, 'Refresh token thất bại')
    const me = await fetchMe()

    return {
      message: data.message,
      user_info: me.user_info,
      permissions: me.permissions,
      roles: me.roles,
    }
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
