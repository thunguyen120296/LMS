import { useEffect } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { restoreSession } from '../api/auth.api'
import { useAuthStore } from '../store/auth.store'
import { subscribeToAuthSessionChanges } from '../utils/auth.session'
import AuthLoading from '../../../shared/components/ui/display/AuthLoading'

const AUTH_ACTION_PATHS = ['/verify-email', '/reset-password']

interface AuthBootstrapProps {
  children: React.ReactNode
}

function isAuthActionPage(): boolean {
  return AUTH_ACTION_PATHS.includes(window.location.pathname)
}

export default function AuthBootstrap({ children }: AuthBootstrapProps) {
  const queryClient = useQueryClient()
  const setInitialized = useAuthStore((state) => state.setInitialized)
  const skipSessionRestore = isAuthActionPage()

  useEffect(() => {
    return subscribeToAuthSessionChanges(() => {
      void queryClient.invalidateQueries({ queryKey: ['auth', 'session'] })
    })
  }, [queryClient])

  useEffect(() => {
    if (skipSessionRestore) {
      setInitialized(true)
    }
  }, [setInitialized, skipSessionRestore])

  const { isPending } = useQuery({
    queryKey: ['auth', 'session'],
    queryFn: async () => {
      try {
        return await restoreSession()
      } finally {
        setInitialized(true)
      }
    },
    enabled: !skipSessionRestore,
    retry: false,
    staleTime: Infinity,
    refetchOnWindowFocus: false,
  })

  if (!skipSessionRestore && isPending) {
    return <AuthLoading fullScreen />
  }

  return children
}
