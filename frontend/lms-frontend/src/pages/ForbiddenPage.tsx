import { Link } from 'react-router'

export default function ForbiddenPage() {
  return (
    <div className="mx-auto flex max-w-lg flex-col items-center px-4 py-24 text-center">
      <p className="text-6xl font-bold text-udemy-purple">403</p>
      <h1 className="mt-4 text-2xl font-bold text-udemy-dark">Không có quyền truy cập</h1>
      <p className="mt-2 text-udemy-gray">
        Bạn không có quyền truy cập trang này. Vui lòng liên hệ quản trị viên nếu bạn cho rằng đây là lỗi.
      </p>
      <Link
        to="/dashboard"
        className="mt-8 rounded-sm bg-udemy-purple px-6 py-3 text-sm font-bold text-white hover:bg-udemy-purple/90"
      >
        Về trang chủ
      </Link>
    </div>
  )
}
