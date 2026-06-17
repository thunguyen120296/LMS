import type { ReactNode } from 'react'
import { useAuthStore } from '../store/auth.store'

interface PermissionGateProps {
  permission?: string
  anyOf?: string[]
  allOf?: string[]
  role?: string
  anyRole?: string[]
  fallback?: ReactNode
  children: ReactNode
}

export default function PermissionGate({
  permission,
  anyOf,
  allOf,
  role,
  anyRole,
  fallback = null,
  children,
}: PermissionGateProps) {
  const hasPermission = useAuthStore((state) => state.hasPermission)
  const hasAnyPermission = useAuthStore((state) => state.hasAnyPermission)
  const hasAllPermissions = useAuthStore((state) => state.hasAllPermissions)
  const hasRole = useAuthStore((state) => state.hasRole)
  const hasAnyRole = useAuthStore((state) => state.hasAnyRole)

  const allowed =
    (permission ? hasPermission(permission) : true) &&
    (anyOf ? hasAnyPermission(anyOf) : true) &&
    (allOf ? hasAllPermissions(allOf) : true) &&
    (role ? hasRole(role) : true) &&
    (anyRole ? hasAnyRole(anyRole) : true)

  return allowed ? children : fallback
}
