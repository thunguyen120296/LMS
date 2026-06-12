function CourseCardSkeleton() {
  return (
    <div className="w-[240px] shrink-0 sm:w-[260px]">
      <div className="skeleton aspect-video w-full rounded-sm" />
      <div className="mt-2 space-y-2">
        <div className="skeleton h-4 w-full rounded" />
        <div className="skeleton h-4 w-3/4 rounded" />
        <div className="skeleton h-3 w-1/2 rounded" />
        <div className="skeleton h-5 w-1/3 rounded" />
      </div>
    </div>
  )
}

export function CourseSectionSkeleton() {
  return (
    <section className="py-8 md:py-10">
      <div className="mx-auto max-w-[1340px] px-4 md:px-6">
        <div className="skeleton mb-6 h-7 w-64 rounded" />
        <div className="flex gap-4 overflow-hidden">
          {Array.from({ length: 4 }).map((_, i) => (
            <CourseCardSkeleton key={i} />
          ))}
        </div>
      </div>
    </section>
  )
}

export function CategoryPillsSkeleton() {
  return (
    <section className="border-b border-udemy-border py-6">
      <div className="mx-auto max-w-[1340px] px-4 md:px-6">
        <div className="skeleton mb-4 h-6 w-48 rounded" />
        <div className="flex flex-wrap gap-3">
          {Array.from({ length: 8 }).map((_, i) => (
            <div key={i} className="skeleton h-10 w-32 rounded-full" />
          ))}
        </div>
      </div>
    </section>
  )
}
