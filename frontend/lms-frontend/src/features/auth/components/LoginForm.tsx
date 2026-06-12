import { Link } from 'react-router'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { useLocation } from 'react-router'
import AuthDivider from './AuthDivider'
import PasswordInput from './PasswordInput'
import SocialAuthButtons from './SocialAuthButtons'
import { useLogin } from '../hooks/useAuth'
import { loginSchema, type LoginFormValues } from '../schemas/auth.schema'
import Button from '../../../shared/components/ui/atomic/Button'
import Input from '../../../shared/components/ui/atomic/Input'

export default function LoginForm() {
  const location = useLocation()
  const successMessage = (location.state as { message?: string } | null)?.message
  const loginMutation = useLogin()

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  })

  const onSubmit = (values: LoginFormValues) => {
    loginMutation.mutate(values)
  }

  return (
    <div className="w-full max-w-[440px]">
      <h1 className="text-2xl font-bold text-udemy-dark md:text-[2rem]">
        Đăng nhập để tiếp tục hành trình học tập
      </h1>

      {successMessage && (
        <div
          className="mt-4 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
          role="status"
        >
          {successMessage}
        </div>
      )}

      {loginMutation.isError && (
        <div
          className="mt-4 rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          role="alert"
        >
          {loginMutation.error.message}
        </div>
      )}

      <form className="mt-6 space-y-4" onSubmit={handleSubmit(onSubmit)} noValidate>
        <Input
          label="Email"
          type="email"
          autoComplete="email"
          placeholder="name@email.com"
          error={errors.email?.message}
          {...register('email')}
        />

        <PasswordInput
          label="Mật khẩu"
          autoComplete="current-password"
          placeholder="Nhập mật khẩu"
          error={errors.password?.message}
          {...register('password')}
        />

        <div className="flex justify-end">
          <Link
            to="/forgot-password"
            className="text-sm font-bold text-udemy-purple hover:text-udemy-purple-dark hover:underline"
          >
            Quên mật khẩu?
          </Link>
        </div>

        <Button type="submit" fullWidth loading={loginMutation.isPending}>
          Đăng nhập
        </Button>
      </form>

      <AuthDivider />

      <SocialAuthButtons />

      <p className="mt-6 text-center text-sm text-udemy-gray">
        Chưa có tài khoản?{' '}
        <Link
          to="/register"
          className="font-bold text-udemy-purple hover:text-udemy-purple-dark hover:underline"
        >
          Đăng ký ngay
        </Link>
      </p>

      <p className="mt-8 text-center text-xs leading-relaxed text-udemy-gray">
        Bằng việc đăng nhập, bạn đồng ý với{' '}
        <button type="button" className="text-udemy-purple hover:underline">
          Điều khoản sử dụng
        </button>{' '}
        và{' '}
        <button type="button" className="text-udemy-purple hover:underline">
          Chính sách quyền riêng tư
        </button>{' '}
        của LMS.
      </p>
    </div>
  )
}
