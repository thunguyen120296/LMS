import { Link, Outlet } from 'react-router'

interface AuthHeaderProps {
  actionLabel: string
  actionHref: string
}

function AuthHeader({ actionLabel, actionHref }: AuthHeaderProps) {
  return (
    <header className="border-b border-udemy-border bg-white">
      <div className="mx-auto flex h-16 max-w-[1340px] items-center justify-between px-4 md:px-6">
        <Link to="/" className="flex items-center gap-1">
          <span className="text-2xl font-bold tracking-tight text-udemy-dark">LMS</span>
          <span className="hidden text-xs text-udemy-gray sm:inline">Learn</span>
        </Link>

        <Link
          to={actionHref}
          className="rounded-sm border border-udemy-dark px-4 py-2 text-sm font-bold text-udemy-dark transition hover:bg-udemy-light-gray"
        >
          {actionLabel}
        </Link>
      </div>
    </header>
  )
}

export function LoginLayout() {
  return (
    <div className="flex min-h-screen flex-col bg-white">
      <AuthHeader actionLabel="Đăng ký" actionHref="/register" />
      <main className="flex flex-1 items-center justify-center px-4 py-10 md:py-16">
        <Outlet />
      </main>
    </div>
  )
}

export function RegisterLayout() {
  return (
    <div className="flex min-h-screen flex-col bg-white">
      <AuthHeader actionLabel="Đăng nhập" actionHref="/login" />
      <main className="flex flex-1 items-center justify-center px-4 py-10 md:py-16">
        <Outlet />
      </main>
    </div>
  )
}
