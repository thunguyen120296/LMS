import { useAuthStore } from '../store/auth.store'
import type { AuthUser } from '../types/auth.types'

export interface MeData {
  user_info: AuthUser
  permissions: string[]
  roles: string[]
}

const AUTH_SYNC_CHANNEL = 'lms-auth-sync'

export function applyMeToStore(data: MeData): void {
  useAuthStore.getState().setAuth(data.user_info, data.permissions, data.roles ?? [])
}

export function clearAuthSession(): void {
  useAuthStore.getState().clearAuth()
}

export function notifyAuthSessionChanged(): void {
  if (typeof BroadcastChannel === 'undefined') {
    return
  }

  new BroadcastChannel(AUTH_SYNC_CHANNEL).postMessage({ type: 'session-changed' })
}

export function subscribeToAuthSessionChanges(onSessionChanged: () => void): () => void {
  if (typeof BroadcastChannel === 'undefined') {
    return () => {}
  }

  const channel = new BroadcastChannel(AUTH_SYNC_CHANNEL)
  channel.onmessage = () => onSessionChanged()

  return () => channel.close()
}
