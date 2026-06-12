import { Link } from 'react-router'

const FOOTER_LINKS = {
  'Về chúng tôi': ['Giới thiệu', 'Liên hệ', 'Tuyển dụng', 'Blog'],
  'Hỗ trợ': ['Trung tâm trợ giúp', 'Điều khoản', 'Quyền riêng tư', 'Cookie'],
  'Khám phá': ['Khóa học miễn phí', 'Chứng chỉ', 'Doanh nghiệp', 'Giảng viên'],
}

export default function PublicFooter() {
  return (
    <footer className="border-t border-udemy-border bg-udemy-dark text-white">
      <div className="mx-auto max-w-[1340px] px-4 py-12 md:px-6">
        <div className="mb-10 flex flex-wrap items-center justify-between gap-4 border-b border-white/20 pb-8">
          <div>
            <span className="text-2xl font-bold">LMS</span>
            <p className="mt-1 text-sm text-white/70">Nền tảng học trực tuyến hàng đầu</p>
          </div>
          <Link
            to="/register"
            className="rounded-sm border border-white px-4 py-2 text-sm font-bold hover:bg-white/10"
          >
            Bắt đầu học ngay
          </Link>
        </div>

        <div className="grid grid-cols-2 gap-8 md:grid-cols-3 lg:grid-cols-4">
          {Object.entries(FOOTER_LINKS).map(([title, links]) => (
            <div key={title}>
              <h3 className="mb-3 text-sm font-bold">{title}</h3>
              <ul className="space-y-2">
                {links.map((link) => (
                  <li key={link}>
                    <button
                      type="button"
                      className="text-sm text-white/70 hover:text-white hover:underline"
                    >
                      {link}
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        <div className="mt-10 flex flex-col items-center justify-between gap-4 border-t border-white/20 pt-8 sm:flex-row">
          <p className="text-sm text-white/60">
            &copy; {new Date().getFullYear()} LMS Platform. All rights reserved.
          </p>
          <div className="flex gap-4">
            <button type="button" className="text-sm text-white/60 hover:text-white">
              Tiếng Việt
            </button>
            <button type="button" className="text-sm text-white/60 hover:text-white">
              USD
            </button>
          </div>
        </div>
      </div>
    </footer>
  )
}
