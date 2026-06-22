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
  UpdateProfileRequest,
  UserProfile,
} from '../types/auth.types'
import {
  applyMeToStore,
  clearAuthSession,
  notifyAuthSessionChanged,
} from '../utils/auth.session'

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

export async function fetchProfile(): Promise<UserProfile> {
  try {
    const { data } = await client.get<ApiResponse<UserProfile>>('/profile')
    return assertSuccess(data, 'Không thể lấy thông tin hồ sơ')
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể lấy thông tin hồ sơ.'))
  }
}

export async function updateProfile(payload: UpdateProfileRequest): Promise<UserProfile> {
  try {
    const { data } = await client.post<ApiResponse<UserProfile>>('/update-profile', payload)
    const profile = assertSuccess(data, 'Không thể cập nhật hồ sơ')
    await fetchMe()
    return profile
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể cập nhật hồ sơ.'))
  }
}

export async function restoreSession(): Promise<MeResponse | null> {
  try {
    return await fetchMe()
  } catch {
    try {
      return await refreshToken()
    } catch {
      clearAuthSession()
      return null
    }
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
    notifyAuthSessionChanged()

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
    notifyAuthSessionChanged()
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

export async function verifyEmail(key: string): Promise<{ message: string }> {
  try {
    const { data } = await client.get<ApiResponse<null>>('/verify-email', {
      params: { key },
    })

    assertSuccess(data, 'Xác minh email thất bại')

    return {
      message: data.message || 'Email đã được xác minh thành công. Vui lòng đăng nhập.',
    }
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể xác minh email. Vui lòng thử lại.'))
  }
}

export async function forgotPassword(email: string): Promise<{ message: string }> {
  try {
    const { data } = await client.post<ApiResponse<null>>('/forgot-password', { email })
    assertSuccess(data, 'Không thể gửi yêu cầu đặt lại mật khẩu')

    return {
      message:
        data.message ||
        'Nếu email tồn tại trong hệ thống, chúng tôi sẽ gửi hướng dẫn tới địa chỉ email của bạn',
    }
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể gửi yêu cầu đặt lại mật khẩu.'))
  }
}

export async function validateResetPasswordKey(key: string): Promise<{ message: string }> {
  try {
    const { data } = await client.get<ApiResponse<null>>('/reset-password/validate', {
      params: { key },
    })
    assertSuccess(data, 'Liên kết đặt lại mật khẩu không hợp lệ')

    return {
      message: data.message || 'Liên kết đặt lại mật khẩu hợp lệ.',
    }
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.'))
  }
}

export async function resetPassword(payload: {
  key: string
  password: string
  confirmPassword: string
}): Promise<{ message: string }> {
  try {
    const { data } = await client.post<ApiResponse<null>>('/reset-password', payload)
    assertSuccess(data, 'Không thể đặt lại mật khẩu')

    return {
      message: data.message || 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.',
    }
  } catch (error) {
    throw new Error(getApiErrorMessage(error, 'Không thể đặt lại mật khẩu. Vui lòng thử lại.'))
  }
}
