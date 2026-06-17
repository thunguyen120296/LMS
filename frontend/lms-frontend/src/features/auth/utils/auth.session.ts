import { useAuthStore } from '../store/auth.store'
import type { AuthUser } from '../types/auth.types'

export interface MeData {
  user_info: AuthUser
  permissions: string[]
  roles: string[]
}

export function applyMeToStore(data: MeData): void {
  useAuthStore.getState().setAuth(data.user_info, data.permissions, data.roles ?? [])
}

export function clearAuthSession(): void {
  useAuthStore.getState().clearAuth()
}
