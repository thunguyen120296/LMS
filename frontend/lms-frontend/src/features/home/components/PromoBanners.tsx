import { Link } from 'react-router'

export default function InstructorBanner() {
  return (
    <section className="bg-udemy-light-gray py-12 md:py-16">
      <div className="mx-auto max-w-[1340px] px-4 md:px-6">
        <div className="grid items-center gap-8 md:grid-cols-2">
          <div>
            <h2 className="text-2xl font-bold text-udemy-dark md:text-3xl">
              Trở thành giảng viên
            </h2>
            <p className="mt-4 text-base text-udemy-gray">
              Giảng viên trên toàn thế giới dạy hàng triệu học viên trên LMS. Chúng tôi cung cấp công cụ và kiến thức để bạn tạo khóa học chất lượng.
            </p>
            <button
              type="button"
              className="mt-6 rounded-sm border border-udemy-dark px-6 py-3 text-sm font-bold text-udemy-dark transition hover:bg-white"
            >
              Bắt đầu dạy hôm nay
            </button>
          </div>

          <div className="grid grid-cols-2 gap-4">
            {[
              { value: '10K+', label: 'Học viên' },
              { value: '50+', label: 'Khóa học' },
              { value: '4.8', label: 'Đánh giá TB' },
              { value: '100+', label: 'Giảng viên' },
            ].map((stat) => (
              <div
                key={stat.label}
                className="rounded-sm border border-udemy-border bg-white p-5 text-center"
              >
                <p className="text-2xl font-bold text-udemy-purple">{stat.value}</p>
                <p className="mt-1 text-sm text-udemy-gray">{stat.label}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}

export function BusinessBanner() {
  return (
    <section className="border-y border-udemy-border py-12 md:py-16">
      <div className="mx-auto flex max-w-[1340px] flex-col items-start justify-between gap-6 px-4 md:flex-row md:items-center md:px-6">
        <div className="max-w-xl">
          <h2 className="text-2xl font-bold text-udemy-dark md:text-3xl">
            LMS cho Doanh nghiệp
          </h2>
          <p className="mt-3 text-base text-udemy-gray">
            Nâng cao kỹ năng cho đội ngũ với hơn 25.000 khóa học chất lượng cao. Theo dõi tiến độ và báo cáo chi tiết.
          </p>
        </div>
        <Link
          to="/register"
          className="shrink-0 rounded-sm bg-udemy-dark px-6 py-3 text-sm font-bold text-white transition hover:bg-udemy-purple"
        >
          Dùng thử miễn phí
        </Link>
      </div>
    </section>
  )
}

export function TrustBanner() {
  const partners = ['VNG', 'FPT', 'Viettel', 'Techcombank', 'MoMo', 'Tiki']

  return (
    <section className="py-10">
      <div className="mx-auto max-w-[1340px] px-4 md:px-6">
        <p className="mb-6 text-center text-sm text-udemy-gray">
          Được tin dùng bởi các công ty hàng đầu
        </p>
        <div className="flex flex-wrap items-center justify-center gap-8 md:gap-12">
          {partners.map((name) => (
            <span
              key={name}
              className="text-lg font-bold tracking-wider text-udemy-gray/60"
            >
              {name}
            </span>
          ))}
        </div>
      </div>
    </section>
  )
}
