import { Link } from 'react-router'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import AuthDivider from './AuthDivider'
import PasswordInput from './PasswordInput'
import SocialAuthButtons from './SocialAuthButtons'
import { useRegister } from '../hooks/useAuth'
import { registerSchema, type RegisterFormValues } from '../schemas/auth.schema'
import Button from '../../../shared/components/ui/atomic/Button'
import Input from '../../../shared/components/ui/atomic/Input'

export default function RegisterForm() {
  const registerMutation = useRegister()

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      fullName: '',
      email: '',
      password: '',
      confirmPassword: '',
      acceptTerms: undefined,
    },
  })

  const onSubmit = (values: RegisterFormValues) => {
    registerMutation.mutate({
      fullName: values.fullName,
      email: values.email,
      password: values.password,
    })
  }

  return (
    <div className="w-full max-w-[440px]">
      <h1 className="text-2xl font-bold text-udemy-dark md:text-[2rem]">
        Đăng ký và bắt đầu học
      </h1>
      <p className="mt-2 text-sm text-udemy-gray">
        Tham gia cộng đồng hàng triệu học viên trên LMS.
      </p>

      {registerMutation.isError && (
        <div
          className="mt-4 rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          role="alert"
        >
          {registerMutation.error.message}
        </div>
      )}

      <form className="mt-6 space-y-4" onSubmit={handleSubmit(onSubmit)} noValidate>
        <Input
          label="Họ và tên"
          type="text"
          autoComplete="name"
          placeholder="Nguyễn Văn A"
          error={errors.fullName?.message}
          {...register('fullName')}
        />

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
          autoComplete="new-password"
          placeholder="Tối thiểu 8 ký tự"
          hint="Gồm ít nhất 8 ký tự, 1 chữ hoa và 1 chữ số"
          error={errors.password?.message}
          {...register('password')}
        />

        <PasswordInput
          label="Xác nhận mật khẩu"
          autoComplete="new-password"
          placeholder="Nhập lại mật khẩu"
          error={errors.confirmPassword?.message}
          {...register('confirmPassword')}
        />

        <label className="flex items-start gap-3">
          <input
            type="checkbox"
            className="mt-1 h-4 w-4 shrink-0 accent-udemy-purple"
            {...register('acceptTerms')}
          />
          <span className="text-sm text-udemy-gray">
            Tôi đồng ý với{' '}
            <button type="button" className="font-bold text-udemy-purple hover:underline">
              Điều khoản sử dụng
            </button>{' '}
            và{' '}
            <button type="button" className="font-bold text-udemy-purple hover:underline">
              Chính sách quyền riêng tư
            </button>{' '}
            của LMS
          </span>
        </label>
        {errors.acceptTerms && (
          <p className="text-xs text-red-600" role="alert">
            {errors.acceptTerms.message}
          </p>
        )}

        <Button type="submit" fullWidth loading={registerMutation.isPending}>
          Đăng ký
        </Button>
      </form>

      <AuthDivider />

      <SocialAuthButtons />

      <p className="mt-6 text-center text-sm text-udemy-gray">
        Đã có tài khoản?{' '}
        <Link
          to="/login"
          className="font-bold text-udemy-purple hover:text-udemy-purple-dark hover:underline"
        >
          Đăng nhập
        </Link>
      </p>
    </div>
  )
}
