import { useEffect } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { restoreSession } from '../api/auth.api'
import { useAuthStore } from '../store/auth.store'
import { subscribeToAuthSessionChanges } from '../utils/auth.session'
import AuthLoading from '../../../shared/components/ui/display/AuthLoading'

interface AuthBootstrapProps {
  children: React.ReactNode
}

export default function AuthBootstrap({ children }: AuthBootstrapProps) {
  const queryClient = useQueryClient()
  const setInitialized = useAuthStore((state) => state.setInitialized)

  useEffect(() => {
    return subscribeToAuthSessionChanges(() => {
      void queryClient.invalidateQueries({ queryKey: ['auth', 'session'] })
    })
  }, [queryClient])

  const { isPending } = useQuery({
    queryKey: ['auth', 'session'],
    queryFn: async () => {
      try {
        return await restoreSession()
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
