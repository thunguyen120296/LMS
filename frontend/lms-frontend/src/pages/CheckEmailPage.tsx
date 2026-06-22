import { Link, useLocation } from 'react-router'

type CheckEmailState = {
  email?: string
  message?: string
  mode?: 'verify' | 'reset'
}

export default function CheckEmailPage() {
  const location = useLocation()
  const state = (location.state as CheckEmailState | null) ?? {}
  const email = state.email
  const mode = state.mode ?? 'verify'
  const isReset = mode === 'reset'

  return (
    <div className="flex min-h-screen items-center justify-center bg-white px-4">
      <div className="w-full max-w-[440px]">
        <h1 className="text-2xl font-bold text-udemy-dark md:text-[2rem]">
          {isReset ? 'Kiểm tra email của bạn' : 'Kiểm tra email của bạn'}
        </h1>

        {state.message && (
          <div
            className="mt-4 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
            role="status"
          >
            {state.message}
          </div>
        )}

        <p className="mt-4 text-sm text-udemy-gray">
          {isReset ? (
            <>
              Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu đến
              {email ? (
                <>
                  {' '}
                  <span className="font-semibold text-udemy-dark">{email}</span>
                </>
              ) : (
                ' email của bạn'
              )}
              . Vui lòng mở email và nhấn vào liên kết để đặt mật khẩu mới.
            </>
          ) : (
            <>
              Chúng tôi đã gửi liên kết xác minh đến
              {email ? (
                <>
                  {' '}
                  <span className="font-semibold text-udemy-dark">{email}</span>
                </>
              ) : (
                ' email đăng ký của bạn'
              )}
              . Vui lòng mở email và nhấn vào liên kết để kích hoạt tài khoản.
            </>
          )}
        </p>

        <p className="mt-3 text-sm text-udemy-gray">
          {isReset
            ? 'Liên kết có hiệu lực trong 1 giờ. Sau khi đặt lại mật khẩu, bạn có thể đăng nhập bình thường.'
            : 'Sau khi xác minh thành công, bạn sẽ được chuyển đến trang đăng nhập.'}
        </p>

        <p className="mt-6 text-center text-sm text-udemy-gray">
          {isReset ? 'Đã đặt lại mật khẩu?' : 'Đã xác minh?'}{' '}
          <Link
            to="/login"
            className="font-bold text-udemy-purple hover:text-udemy-purple-dark hover:underline"
          >
            Đăng nhập
          </Link>
        </p>
      </div>
    </div>
  )
}
