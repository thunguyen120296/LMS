import { Link } from 'react-router'
import type { CourseCategory } from '../types/course.types'

interface CategoryPillsProps {
  categories: CourseCategory[]
}

export default function CategoryPills({ categories }: CategoryPillsProps) {
  return (
    <section className="border-b border-udemy-border bg-white py-6">
      <div className="mx-auto max-w-[1340px] px-4 md:px-6">
        <h2 className="mb-4 text-lg font-bold text-udemy-dark">Danh mục phổ biến</h2>
        <div className="flex flex-wrap gap-3">
          {categories.map((cat) => (
            <Link
              key={cat.id}
              to={`/course-list?category=${cat.slug}`}
              className="rounded-full border border-udemy-border px-4 py-2 text-sm font-medium text-udemy-dark transition hover:border-udemy-dark hover:bg-udemy-light-gray"
            >
              {cat.name}
            </Link>
          ))}
        </div>
      </div>
    </section>
  )
}
