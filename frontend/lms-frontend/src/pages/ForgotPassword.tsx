import { Link } from 'react-router'

function ForgotPassword() {
  return (
    <div className="w-full max-w-[440px]">
      <h1 className="text-2xl font-bold text-udemy-dark md:text-[2rem]">Quên mật khẩu?</h1>
      <p className="mt-2 text-sm text-udemy-gray">
        Nhập email đã đăng ký, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.
      </p>

      <form
        className="mt-6 space-y-4"
        onSubmit={(e) => e.preventDefault()}
      >
        <div>
          <label htmlFor="email" className="mb-1 block text-sm font-bold text-udemy-dark">
            Email
          </label>
          <input
            id="email"
            type="email"
            placeholder="name@email.com"
            className="w-full rounded-sm border border-udemy-border px-3 py-3 text-sm outline-none focus:border-udemy-purple focus:ring-1 focus:ring-udemy-purple"
          />
        </div>
        <button
          type="submit"
          className="w-full rounded-sm bg-udemy-purple px-4 py-3 text-sm font-bold text-white hover:bg-udemy-purple-dark"
        >
          Gửi liên kết đặt lại
        </button>
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
