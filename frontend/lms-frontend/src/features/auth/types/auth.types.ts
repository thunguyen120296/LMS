export interface LoginRequest {
  email: string
  password: string
}

export interface RegisterRequest {
  fullName: string
  email: string
  password: string
}

export interface AuthUser {
  id: string
  email: string
  fullName: string
}

export interface MeResponse {
  user_info: AuthUser
  permissions: string[]
  roles: string[]
}

export interface LoginResponse {
  message: string
  user_info: AuthUser
  permissions: string[]
  roles: string[]
}

export interface RegisterResponse {
  userId: string
  email: string
  message: string
}

export interface RefreshTokenResponse {
  message: string
  user_info: AuthUser
  permissions: string[]
  roles: string[]
}

export interface UserProfile {
  id: string
  email: string
  username: string
  firstName: string | null
  lastName: string | null
  fullName: string
  avatarUrl: string | null
  locale: string
  emailVerified: boolean
  createdAt: string
  updatedAt: string
}

export interface UpdateProfileRequest {
  fullName?: string
  firstName?: string
  lastName?: string
  avatarUrl?: string | null
  locale?: string
}
