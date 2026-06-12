export interface LoginRequest {
  email: string
  password: string,
  fullName: string
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

export interface LoginResponse {
  message: string
}

export interface RegisterResponse {
  userId: string
}
