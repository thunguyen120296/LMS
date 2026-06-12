import CategoryPills from './CategoryPills'
import CourseSection from './CourseSection'
import HeroSection from './HeroSection'
import InstructorBanner, { BusinessBanner, TrustBanner } from './PromoBanners'
import { CategoryPillsSkeleton, CourseSectionSkeleton } from './HomeSkeleton'
import { useCategories, useCourseSections } from '../hooks/useHomeData'

export default function HomePageContent() {
  const { data: categories, isLoading: categoriesLoading } = useCategories()
  const { data: sections, isLoading: sectionsLoading } = useCourseSections()

  return (
    <>
      <HeroSection />

      {categoriesLoading ? (
        <CategoryPillsSkeleton />
      ) : (
        categories && <CategoryPills categories={categories} />
      )}

      {sectionsLoading ? (
        <>
          <CourseSectionSkeleton />
          <CourseSectionSkeleton />
        </>
      ) : (
        sections?.map((section) => (
          <CourseSection
            key={section.id}
            title={section.title}
            subtitle={section.subtitle}
            courses={section.courses}
          />
        ))
      )}

      <InstructorBanner />
      <TrustBanner />
      <BusinessBanner />
    </>
  )
}
