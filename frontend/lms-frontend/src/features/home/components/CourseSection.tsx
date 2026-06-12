import { useRef } from 'react'
import CourseCard from './CourseCard'
import type { Course } from '../types/course.types'

interface CourseSectionProps {
  title: string
  subtitle?: string
  courses: Course[]
}

export default function CourseSection({ title, subtitle, courses }: CourseSectionProps) {
  const scrollRef = useRef<HTMLDivElement>(null)

  const scroll = (direction: 'left' | 'right') => {
    const el = scrollRef.current
    if (!el) return
    const amount = direction === 'left' ? -el.clientWidth * 0.8 : el.clientWidth * 0.8
    el.scrollBy({ left: amount, behavior: 'smooth' })
  }

  if (courses.length === 0) return null

  return (
    <section className="py-8 md:py-10">
      <div className="mx-auto max-w-[1340px] px-4 md:px-6">
        <div className="mb-4 flex items-end justify-between gap-4">
          <div>
            <h2 className="text-xl font-bold text-udemy-dark md:text-2xl">{title}</h2>
            {subtitle && (
              <p className="mt-1 text-sm text-udemy-gray">{subtitle}</p>
            )}
          </div>
          <div className="hidden shrink-0 gap-2 sm:flex">
            <button
              type="button"
              onClick={() => scroll('left')}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-udemy-dark bg-white hover:bg-udemy-light-gray"
              aria-label="Cuộn trái"
            >
              <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              type="button"
              onClick={() => scroll('right')}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-udemy-dark bg-white hover:bg-udemy-light-gray"
              aria-label="Cuộn phải"
            >
              <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        <div
          ref={scrollRef}
          className="flex gap-4 overflow-x-auto scroll-smooth pb-2 scrollbar-hide snap-x snap-mandatory"
        >
          {courses.map((course) => (
            <div key={course.id} className="snap-start">
              <CourseCard course={course} />
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
