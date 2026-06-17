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
