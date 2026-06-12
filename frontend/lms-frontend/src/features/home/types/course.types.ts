export interface Course {
  id: number
  title: string
  instructor: string
  rating: number
  ratingCount: number
  price: number
  originalPrice?: number
  imageUrl: string
  badge?: 'bestseller' | 'new' | 'hot'
  level?: 'beginner' | 'intermediate' | 'advanced'
}

export interface CourseCategory {
  id: number
  name: string
  slug: string
}

export interface CourseSection {
  id: string
  title: string
  subtitle?: string
  courses: Course[]
}
