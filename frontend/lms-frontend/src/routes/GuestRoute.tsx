import { Navigate, Outlet } from 'react-router'
import { useAuthStore } from '../features/auth/store/auth.store'
import AuthLoading from '../shared/components/ui/display/AuthLoading'

function GuestRoute() {
  const user = useAuthStore((state) => state.user)
  const isInitialized = useAuthStore((state) => state.isInitialized)

  if (!isInitialized) {
    return <AuthLoading fullScreen />
  }

  if (user) {
    return <Navigate to="/dashboard" replace />
  }

  return <Outlet />
}

export default GuestRoute
