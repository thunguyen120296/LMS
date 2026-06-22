import { useEffect, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router'
import { verifyEmail } from '../features/auth/api/auth.api'
import Button from '../shared/components/ui/atomic/Button'

type VerifyState = 'loading' | 'success' | 'error'

export default function VerifyEmailPage() {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const key = searchParams.get('key') ?? ''
  const [state, setState] = useState<VerifyState>(key ? 'loading' : 'error')
  const [message, setMessage] = useState(
    key ? 'Đang xác minh email...' : 'Liên kết xác minh không hợp lệ.',
  )

  useEffect(() => {
    if (!key) {
      return
    }

    let cancelled = false

    verifyEmail(key)
      .then((result) => {
        if (cancelled) return
        setState('success')
        setMessage(result.message)
        window.setTimeout(() => {
          navigate('/login', {
            replace: true,
            state: { message: result.message },
          })
        }, 2000)
      })
      .catch((error: Error) => {
        if (cancelled) return
        setState('error')
        setMessage(error.message)
      })

    return () => {
      cancelled = true
    }
  }, [key, navigate])

  return (
    <div className="flex min-h-screen items-center justify-center bg-white px-4">
      <div className="w-full max-w-[440px] text-center">
        <h1 className="text-2xl font-bold text-udemy-dark md:text-[2rem]">Xác minh email</h1>

        {state === 'loading' && (
          <p className="mt-4 text-sm text-udemy-gray" role="status">
            {message}
          </p>
        )}

        {state === 'success' && (
          <div
            className="mt-6 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
            role="status"
          >
            {message}
            <p className="mt-2 text-udemy-gray">Đang chuyển đến trang đăng nhập...</p>
          </div>
        )}

        {state === 'error' && (
          <>
            <div
              className="mt-6 rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
              role="alert"
            >
              {message}
            </div>
            <div className="mt-6">
              <Link to="/login">
                <Button fullWidth>Đến trang đăng nhập</Button>
              </Link>
            </div>
          </>
        )}
      </div>
    </div>
  )
}
