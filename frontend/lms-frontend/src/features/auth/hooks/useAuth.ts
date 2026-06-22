import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router'
import { fetchProfile, forgotPassword, loginUser, logoutUser, registerUser, updateProfile } from '../api/auth.api'
import { useAuthStore } from '../store/auth.store'
import type { LoginRequest, RegisterRequest, UpdateProfileRequest } from '../types/auth.types'

export function useAuth() {
  const user = useAuthStore((state) => state.user)
  const permissions = useAuthStore((state) => state.permissions)
  const roles = useAuthStore((state) => state.roles)
  const isInitialized = useAuthStore((state) => state.isInitialized)
  const hasPermission = useAuthStore((state) => state.hasPermission)
  const hasAnyPermission = useAuthStore((state) => state.hasAnyPermission)
  const hasAllPermissions = useAuthStore((state) => state.hasAllPermissions)
  const hasRole = useAuthStore((state) => state.hasRole)
  const hasAnyRole = useAuthStore((state) => state.hasAnyRole)

  return {
    user,
    permissions,
    roles,
    isInitialized,
    isAuthenticated: user !== null,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    hasRole,
    hasAnyRole,
  }
}

export function useLogin() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (payload: LoginRequest) => loginUser(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['auth', 'session'] })
      navigate('/dashboard')
    },
  })
}

export function useRegister() {
  const navigate = useNavigate()

  return useMutation({
    mutationFn: (payload: RegisterRequest) => registerUser(payload),
    onSuccess: (result) => {
      navigate('/check-email', {
        state: { email: result.email, message: result.message, mode: 'verify' },
      })
    },
  })
}

export function useForgotPassword() {
  const navigate = useNavigate()

  return useMutation({
    mutationFn: (email: string) => forgotPassword(email),
    onSuccess: (result, email) => {
      navigate('/check-email', {
        state: { email, message: result.message, mode: 'reset' },
      })
    },
  })
}

export function useLogout() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: logoutUser,
    onSettled: () => {
      queryClient.removeQueries({ queryKey: ['auth', 'session'] })
      navigate('/login', { replace: true })
    },
  })
}

export function usePermission(permission: string) {
  return useAuthStore((state) => state.hasPermission(permission))
}

export function useRole(role: string) {
  return useAuthStore((state) => state.hasRole(role))
}

export function useProfile() {
  return useQuery({
    queryKey: ['auth', 'profile'],
    queryFn: fetchProfile,
  })
}

export function useUpdateProfile() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (payload: UpdateProfileRequest) => updateProfile(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['auth', 'profile'] })
      queryClient.invalidateQueries({ queryKey: ['auth', 'session'] })
    },
  })
}
