import { memo } from 'react'
import { Link } from 'react-router'
import type { Course } from '../types/course.types'

interface CourseCardProps {
  course: Course
}

function formatPrice(price: number) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

function StarRating({ rating }: { rating: number }) {
  const fullStars = Math.floor(rating)
  const hasHalf = rating - fullStars >= 0.5

  return (
    <span className="inline-flex items-center gap-0.5">
      {[...Array(5)].map((_, i) => (
        <svg
          key={i}
          className={`h-3.5 w-3.5 ${i < fullStars ? 'text-udemy-star' : i === fullStars && hasHalf ? 'text-udemy-star' : 'text-udemy-border'}`}
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      ))}
    </span>
  )
}

const BADGE_LABELS: Record<NonNullable<Course['badge']>, string> = {
  bestseller: 'Bán chạy nhất',
  new: 'Mới',
  hot: 'Hot',
}

function CourseCard({ course }: CourseCardProps) {
  return (
    <Link
      to={`/course-detail/${course.id}`}
      className="group flex w-[240px] shrink-0 flex-col sm:w-[260px]"
    >
      <div className="relative overflow-hidden rounded-sm border border-transparent transition hover:border-udemy-dark">
        <img
          src={course.imageUrl}
          alt={course.title}
          loading="lazy"
          decoding="async"
          width={260}
          height={146}
          className="aspect-video w-full object-cover"
        />
        {course.badge && (
          <span className="absolute left-2 top-2 bg-udemy-badge px-2 py-0.5 text-xs font-bold text-udemy-badge-text">
            {BADGE_LABELS[course.badge]}
          </span>
        )}
      </div>

      <div className="mt-2 flex flex-1 flex-col">
        <h3 className="line-clamp-2 text-sm font-bold leading-snug text-udemy-dark group-hover:text-udemy-purple">
          {course.title}
        </h3>
        <p className="mt-1 text-xs text-udemy-gray">{course.instructor}</p>

        <div className="mt-1 flex items-center gap-1">
          <span className="text-sm font-bold text-udemy-star">{course.rating.toFixed(1)}</span>
          <StarRating rating={course.rating} />
          <span className="text-xs text-udemy-gray">
            ({course.ratingCount.toLocaleString('vi-VN')})
          </span>
        </div>

        <div className="mt-auto flex items-center gap-2 pt-2">
          <span className="text-base font-bold text-udemy-dark">{formatPrice(course.price)}</span>
          {course.originalPrice && (
            <span className="text-sm text-udemy-gray line-through">
              {formatPrice(course.originalPrice)}
            </span>
          )}
        </div>
      </div>
    </Link>
  )
}

export default memo(CourseCard)
