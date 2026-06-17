import { useQuery } from '@tanstack/react-query'
import { fetchMe } from '../api/auth.api'
import { useAuthStore } from '../store/auth.store'
import { clearAuthSession } from '../utils/auth.session'
import AuthLoading from '../../../shared/components/ui/display/AuthLoading'

interface AuthBootstrapProps {
  children: React.ReactNode
}

export default function AuthBootstrap({ children }: AuthBootstrapProps) {
  const setInitialized = useAuthStore((state) => state.setInitialized)

  const { isPending } = useQuery({
    queryKey: ['auth', 'session'],
    queryFn: async () => {
      try {
        return await fetchMe()
      } catch {
        clearAuthSession()
        return null
      } finally {
        setInitialized(true)
      }
    },
    retry: false,
    staleTime: Infinity,
    refetchOnWindowFocus: false,
  })

  if (isPending) {
    return <AuthLoading fullScreen />
  }

  return children
}
