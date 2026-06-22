import { useEffect, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import PasswordInput from '../features/auth/components/PasswordInput'
import { resetPassword, validateResetPasswordKey } from '../features/auth/api/auth.api'
import {
  resetPasswordSchema,
  type ResetPasswordFormValues,
} from '../features/auth/schemas/auth.schema'
import Button from '../shared/components/ui/atomic/Button'

type PageState = 'loading' | 'ready' | 'error' | 'success'

export default function ResetPasswordPage() {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const key = searchParams.get('key') ?? ''
  const [pageState, setPageState] = useState<PageState>(key ? 'loading' : 'error')
  const [pageMessage, setPageMessage] = useState(
    key ? 'Đang xác thực liên kết...' : 'Liên kết đặt lại mật khẩu không hợp lệ.',
  )
  const [submitError, setSubmitError] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<ResetPasswordFormValues>({
    resolver: zodResolver(resetPasswordSchema),
    defaultValues: {
      password: '',
      confirmPassword: '',
    },
  })

  useEffect(() => {
    if (!key) {
      return
    }

    let cancelled = false

    validateResetPasswordKey(key)
      .then(() => {
        if (cancelled) return
        setPageState('ready')
        setPageMessage('')
      })
      .catch((error: Error) => {
        if (cancelled) return
        setPageState('error')
        setPageMessage(error.message)
      })

    return () => {
      cancelled = true
    }
  }, [key])

  const onSubmit = async (values: ResetPasswordFormValues) => {
    if (!key) return

    setSubmitError(null)
    setIsSubmitting(true)

    try {
      const result = await resetPassword({
        key,
        password: values.password,
        confirmPassword: values.confirmPassword,
      })
      setPageState('success')
      setPageMessage(result.message)
      window.setTimeout(() => {
        navigate('/login', {
          replace: true,
          state: { message: result.message },
        })
      }, 2000)
    } catch (error) {
      setSubmitError(error instanceof Error ? error.message : 'Không thể đặt lại mật khẩu.')
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-white px-4">
      <div className="w-full max-w-[440px]">
        <h1 className="text-2xl font-bold text-udemy-dark md:text-[2rem]">Đặt lại mật khẩu</h1>

        {pageState === 'loading' && (
          <p className="mt-4 text-sm text-udemy-gray" role="status">
            {pageMessage}
          </p>
        )}

        {pageState === 'ready' && (
          <>
            <p className="mt-2 text-sm text-udemy-gray">Nhập mật khẩu mới cho tài khoản của bạn.</p>

            {submitError && (
              <div
                className="mt-4 rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                role="alert"
              >
                {submitError}
              </div>
            )}

            <form className="mt-6 space-y-4" onSubmit={handleSubmit(onSubmit)} noValidate>
              <PasswordInput
                label="Mật khẩu mới"
                autoComplete="new-password"
                placeholder="Tối thiểu 8 ký tự"
                hint="Gồm ít nhất 8 ký tự, 1 chữ hoa và 1 chữ số"
                error={errors.password?.message}
                {...register('password')}
              />

              <PasswordInput
                label="Xác nhận mật khẩu mới"
                autoComplete="new-password"
                placeholder="Nhập lại mật khẩu"
                error={errors.confirmPassword?.message}
                {...register('confirmPassword')}
              />

              <Button type="submit" fullWidth loading={isSubmitting}>
                Đặt lại mật khẩu
              </Button>
            </form>
          </>
        )}

        {pageState === 'success' && (
          <div
            className="mt-6 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
            role="status"
          >
            {pageMessage}
            <p className="mt-2 text-udemy-gray">Đang chuyển đến trang đăng nhập...</p>
          </div>
        )}

        {pageState === 'error' && (
          <>
            <div
              className="mt-6 rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
              role="alert"
            >
              {pageMessage}
            </div>
            <div className="mt-6">
              <Link to="/forgot-password">
                <Button fullWidth>Yêu cầu liên kết mới</Button>
              </Link>
            </div>
          </>
        )}

        {pageState === 'ready' && (
          <p className="mt-6 text-center text-sm text-udemy-gray">
            <Link to="/login" className="font-bold text-udemy-purple hover:underline">
              Quay lại đăng nhập
            </Link>
          </p>
        )}
      </div>
    </div>
  )
}
