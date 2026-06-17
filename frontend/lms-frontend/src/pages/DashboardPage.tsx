import { useAuth } from '../features/auth/hooks/useAuth'
import PermissionGate from '../features/auth/components/PermissionGate'
import { PERMISSIONS } from '../features/auth/constants/permissions'

function DashboardPage() {
  const { user } = useAuth()

  return (
    <div className="mx-auto max-w-[1340px] px-4 py-10 md:px-6">
      <h1 className="text-2xl font-bold text-udemy-dark">
        Xin chào, {user?.fullName || user?.email}
      </h1>
      <p className="mt-2 text-udemy-gray">Chào mừng bạn đến với LMS Dashboard.</p>

      <PermissionGate
        permission={PERMISSIONS.COURSE_CREATE}
        fallback={
          <p className="mt-6 text-sm text-udemy-gray">
            Bạn chưa có quyền tạo khóa học. Liên hệ quản trị viên để được cấp quyền INSTRUCTOR.
          </p>
        }
      >
        <button
          type="button"
          className="mt-6 rounded-sm bg-udemy-purple px-4 py-2 text-sm font-bold text-white hover:bg-udemy-purple/90"
        >
          Tạo khóa học mới
        </button>
      </PermissionGate>
    </div>
  )
}

export default DashboardPage
