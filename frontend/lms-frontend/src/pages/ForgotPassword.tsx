import { Link } from 'react-router'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { useForgotPassword } from '../features/auth/hooks/useAuth'
import {
  forgotPasswordSchema,
  type ForgotPasswordFormValues,
} from '../features/auth/schemas/auth.schema'
import Button from '../shared/components/ui/atomic/Button'
import Input from '../shared/components/ui/atomic/Input'

function ForgotPassword() {
  const forgotPasswordMutation = useForgotPassword()

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<ForgotPasswordFormValues>({
    resolver: zodResolver(forgotPasswordSchema),
    defaultValues: { email: '' },
  })

  const onSubmit = (values: ForgotPasswordFormValues) => {
    forgotPasswordMutation.mutate(values.email)
  }

  return (
    <div className="w-full max-w-[440px]">
      <h1 className="text-2xl font-bold text-udemy-dark md:text-[2rem]">Quên mật khẩu?</h1>
      <p className="mt-2 text-sm text-udemy-gray">
        Nhập email đã đăng ký, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.
      </p>

      {forgotPasswordMutation.isError && (
        <div
          className="mt-4 rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          role="alert"
        >
          {forgotPasswordMutation.error.message}
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

        <Button type="submit" fullWidth loading={forgotPasswordMutation.isPending}>
          Gửi liên kết đặt lại
        </Button>
      </form>

      <p className="mt-6 text-center text-sm text-udemy-gray">
        <Link to="/login" className="font-bold text-udemy-purple hover:underline">
          Quay lại đăng nhập
        </Link>
      </p>
    </div>
  )
}

export default ForgotPassword
