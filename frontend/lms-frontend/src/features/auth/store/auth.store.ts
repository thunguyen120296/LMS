import { create } from 'zustand'
import type { AuthUser } from '../types/auth.types'

interface AuthState {
  user: AuthUser | null
  permissions: string[]
  roles: string[]
  isInitialized: boolean
  setAuth: (user: AuthUser, permissions: string[], roles: string[]) => void
  clearAuth: () => void
  setInitialized: (value: boolean) => void
  isAuthenticated: () => boolean
  hasPermission: (permission: string) => boolean
  hasAnyPermission: (permissions: string[]) => boolean
  hasAllPermissions: (permissions: string[]) => boolean
  hasRole: (role: string) => boolean
  hasAnyRole: (roles: string[]) => boolean
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  permissions: [],
  roles: [],
  isInitialized: false,

  setAuth: (user, permissions, roles) => set({ user, permissions, roles }),

  clearAuth: () => set({ user: null, permissions: [], roles: [] }),

  setInitialized: (value) => set({ isInitialized: value }),

  isAuthenticated: () => get().user !== null,

  hasPermission: (permission) => get().permissions.includes(permission),

  hasAnyPermission: (permissions) =>
    permissions.some((permission) => get().permissions.includes(permission)),

  hasAllPermissions: (permissions) =>
    permissions.every((permission) => get().permissions.includes(permission)),

  hasRole: (role) => get().roles.includes(role),

  hasAnyRole: (roles) => roles.some((role) => get().roles.includes(role)),
}))
