import { useMutation } from '@tanstack/react-query'
import { useNavigate } from 'react-router'
import { loginUser, registerUser } from '../api/auth.api'
import type { LoginRequest, RegisterRequest } from '../types/auth.types'

export function useLogin() {
  const navigate = useNavigate()

  return useMutation({
    mutationFn: (payload: LoginRequest) => loginUser(payload),
    onSuccess: () => {
      navigate('/dashboard')
    },
  })
}

export function useRegister() {
  const navigate = useNavigate()

  return useMutation({
    mutationFn: (payload: RegisterRequest) => registerUser(payload),
    onSuccess: () => {
      navigate('/login', {
        state: { message: 'Đăng ký thành công! Vui lòng đăng nhập.' },
      })
    },
  })
}
