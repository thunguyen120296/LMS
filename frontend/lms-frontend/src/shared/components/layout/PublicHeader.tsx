import { Link } from 'react-router'

const NAV_CATEGORIES = [
  'Phát triển Web',
  'Kinh doanh',
  'Thiết kế',
  'Marketing',
  'IT & Phần mềm',
  'Data Science',
]

export default function PublicHeader() {
  return (
    <header className="sticky top-0 z-50 border-b border-udemy-border bg-white shadow-sm">
      <div className="mx-auto flex h-16 max-w-[1340px] items-center gap-4 px-4 md:px-6">
        <Link to="/" className="flex shrink-0 items-center gap-1">
          <span className="text-2xl font-bold tracking-tight text-udemy-dark">LMS</span>
          <span className="hidden text-xs text-udemy-gray sm:inline">Learn</span>
        </Link>

        <nav className="hidden lg:block">
          <button
            type="button"
            className="flex items-center gap-1 px-3 py-2 text-sm font-medium text-udemy-dark hover:text-udemy-purple"
          >
            Khám phá
            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </nav>

        <div className="hidden flex-1 md:block">
          <form
            className="flex overflow-hidden rounded-full border border-udemy-dark bg-udemy-light-gray"
            onSubmit={(e) => e.preventDefault()}
          >
            <button
              type="submit"
              className="flex items-center px-4 text-udemy-dark hover:text-udemy-purple"
              aria-label="Tìm kiếm"
            >
              <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>
            <input
              type="search"
              placeholder="Tìm kiếm khóa học..."
              className="w-full bg-transparent py-2.5 pr-4 text-sm outline-none placeholder:text-udemy-gray"
            />
          </form>
        </div>

        <div className="ml-auto flex items-center gap-1 sm:gap-2">
          <Link
            to="/course-list"
            className="hidden px-3 py-2 text-sm font-medium text-udemy-dark hover:text-udemy-purple md:block"
          >
            Khóa học
          </Link>
          <button
            type="button"
            className="hidden px-3 py-2 text-sm font-medium text-udemy-dark hover:text-udemy-purple lg:block"
          >
            Dạy trên LMS
          </button>

          <Link
            to="/login"
            className="hidden px-3 py-2 text-sm font-medium text-udemy-dark hover:text-udemy-purple sm:block"
          >
            Đăng nhập
          </Link>
          <Link
            to="/register"
            className="rounded-sm border border-udemy-dark px-3 py-1.5 text-sm font-bold text-udemy-dark hover:bg-udemy-light-gray sm:px-4 sm:py-2"
          >
            Đăng ký
          </Link>

          <button
            type="button"
            className="p-2 text-udemy-dark hover:text-udemy-purple md:hidden"
            aria-label="Menu"
          >
            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>

      <div className="hidden border-t border-udemy-border lg:block">
        <div className="mx-auto flex max-w-[1340px] gap-1 overflow-x-auto px-4 py-2 scrollbar-hide md:px-6">
          {NAV_CATEGORIES.map((cat) => (
            <Link
              key={cat}
              to={`/course-list?category=${encodeURIComponent(cat)}`}
              className="shrink-0 px-3 py-1.5 text-sm text-udemy-dark hover:text-udemy-purple"
            >
              {cat}
            </Link>
          ))}
        </div>
      </div>
    </header>
  )
}
