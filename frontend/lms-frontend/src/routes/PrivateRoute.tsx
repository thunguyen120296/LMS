import { Navigate, Outlet, useLocation, useMatches } from 'react-router'
import PrivateLayout from '../shared/components/layout/PrivateLayout'
import AuthLoading from '../shared/components/ui/display/AuthLoading'
import { useAuthStore } from '../features/auth/store/auth.store'

export interface RouteHandle {
  permission?: string
  anyPermission?: string[]
  role?: string
}

function PrivateRoute() {
  const location = useLocation()
  const user = useAuthStore((state) => state.user)
  const isInitialized = useAuthStore((state) => state.isInitialized)

  if (!isInitialized) {
    return <AuthLoading fullScreen />
  }

  if (!user) {
    return <Navigate to="/login" state={{ from: location.pathname }} replace />
  }

  return (
    <PrivateLayout>
      <PermissionGuard />
    </PrivateLayout>
  )
}

function PermissionGuard() {
  const matches = useMatches()
  const hasPermission = useAuthStore((state) => state.hasPermission)
  const hasAnyPermission = useAuthStore((state) => state.hasAnyPermission)
  const hasRole = useAuthStore((state) => state.hasRole)

  const routeHandle = [...matches]
    .reverse()
    .map((match) => match.handle as RouteHandle | undefined)
    .find((handle) => handle?.permission || handle?.anyPermission || handle?.role)

  if (routeHandle?.permission && !hasPermission(routeHandle.permission)) {
    return <Navigate to="/forbidden" replace />
  }

  if (routeHandle?.anyPermission && !hasAnyPermission(routeHandle.anyPermission)) {
    return <Navigate to="/forbidden" replace />
  }

  if (routeHandle?.role && !hasRole(routeHandle.role)) {
    return <Navigate to="/forbidden" replace />
  }

  return <Outlet />
}

export default PrivateRoute
