import { useState } from 'react'
import { useNavigate } from 'react-router'
import { POPULAR_SEARCHES } from '../api/home.api'
import heroImage from '../../../assets/hero.png'

export default function HeroSection() {
  const [keyword, setKeyword] = useState('')
  const navigate = useNavigate()

  const handleSearch = (value: string) => {
    const trimmed = value.trim()
    if (trimmed) {
      navigate(`/course-list?q=${encodeURIComponent(trimmed)}`)
    }
  }

  return (
    <section className="relative overflow-hidden bg-udemy-light-gray">
      <div className="mx-auto grid max-w-[1340px] items-center gap-8 px-4 py-10 md:grid-cols-2 md:px-6 md:py-16">
        <div className="relative z-10">
          <h1 className="text-3xl font-bold leading-tight text-udemy-dark md:text-4xl lg:text-[2.75rem]">
            Học không giới hạn
          </h1>
          <p className="mt-4 max-w-lg text-base text-udemy-gray md:text-lg">
            Bắt đầu, chuyển hướng hay thăng tiến sự nghiệp với hàng ngàn khóa học từ các chuyên gia hàng đầu.
          </p>

          <form
            className="mt-6 flex overflow-hidden rounded-sm border border-udemy-dark bg-white shadow-sm"
            onSubmit={(e) => {
              e.preventDefault()
              handleSearch(keyword)
            }}
          >
            <input
              type="search"
              value={keyword}
              onChange={(e) => setKeyword(e.target.value)}
              placeholder="Bạn muốn học gì?"
              className="flex-1 px-4 py-3.5 text-sm outline-none placeholder:text-udemy-gray"
            />
            <button
              type="submit"
              className="bg-udemy-purple px-6 py-3.5 text-sm font-bold text-white transition hover:bg-udemy-purple-dark"
            >
              Tìm kiếm
            </button>
          </form>

          <div className="mt-4 flex flex-wrap items-center gap-2">
            <span className="text-xs text-udemy-gray">Tìm kiếm phổ biến:</span>
            {POPULAR_SEARCHES.map((term) => (
              <button
                key={term}
                type="button"
                onClick={() => handleSearch(term)}
                className="rounded-full border border-udemy-border px-3 py-1 text-xs text-udemy-dark transition hover:border-udemy-purple hover:text-udemy-purple"
              >
                {term}
              </button>
            ))}
          </div>
        </div>

        <div className="relative hidden md:block">
          <img
            src={heroImage}
            alt="Học trực tuyến trên LMS"
            width={600}
            height={400}
            className="mx-auto w-full max-w-md object-contain"
            fetchPriority="high"
          />
        </div>
      </div>
    </section>
  )
}
