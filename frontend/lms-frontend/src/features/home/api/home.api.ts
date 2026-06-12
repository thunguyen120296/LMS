import type { ApiResponse } from '../../../shared/types/api.types'
import type { Course, CourseCategory, CourseSection } from '../types/course.types'

const MOCK_CATEGORIES: CourseCategory[] = [
  { id: 1, name: 'Phát triển Web', slug: 'web-development' },
  { id: 2, name: 'Kinh doanh', slug: 'business' },
  { id: 3, name: 'Thiết kế', slug: 'design' },
  { id: 4, name: 'Marketing', slug: 'marketing' },
  { id: 5, name: 'IT & Phần mềm', slug: 'it-software' },
  { id: 6, name: 'Data Science', slug: 'data-science' },
  { id: 7, name: 'Ngoại ngữ', slug: 'languages' },
  { id: 8, name: 'Nhiếp ảnh', slug: 'photography' },
]

const MOCK_COURSES: Course[] = [
  {
    id: 1,
    title: 'Java Core từ Zero đến Hero - Lập trình hướng đối tượng',
    instructor: 'Nguyễn Văn An',
    rating: 4.7,
    ratingCount: 12840,
    price: 399000,
    originalPrice: 1299000,
    imageUrl: 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=320&h=180&fit=crop',
    badge: 'bestseller',
    level: 'beginner',
  },
  {
    id: 2,
    title: 'React & TypeScript - Xây dựng ứng dụng web hiện đại',
    instructor: 'Trần Minh Tuấn',
    rating: 4.8,
    ratingCount: 9560,
    price: 449000,
    originalPrice: 1499000,
    imageUrl: 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=320&h=180&fit=crop',
    badge: 'bestseller',
    level: 'intermediate',
  },
  {
    id: 3,
    title: 'Spring Boot Microservices - Kiến trúc hệ thống phân tán',
    instructor: 'Lê Hoàng Nam',
    rating: 4.6,
    ratingCount: 7320,
    price: 499000,
    originalPrice: 1699000,
    imageUrl: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=320&h=180&fit=crop',
    badge: 'hot',
    level: 'advanced',
  },
  {
    id: 4,
    title: 'UI/UX Design Fundamentals - Thiết kế trải nghiệm người dùng',
    instructor: 'Phạm Thu Hà',
    rating: 4.9,
    ratingCount: 15420,
    price: 349000,
    originalPrice: 1199000,
    imageUrl: 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=320&h=180&fit=crop',
    badge: 'bestseller',
    level: 'beginner',
  },
  {
    id: 5,
    title: 'Python cho Data Science & Machine Learning',
    instructor: 'Võ Đức Thắng',
    rating: 4.5,
    ratingCount: 6890,
    price: 429000,
    originalPrice: 1399000,
    imageUrl: 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=320&h=180&fit=crop',
    level: 'intermediate',
  },
  {
    id: 6,
    title: 'Docker & Kubernetes - Triển khai ứng dụng container',
    instructor: 'Hoàng Quốc Bảo',
    rating: 4.7,
    ratingCount: 5120,
    price: 479000,
    originalPrice: 1599000,
    imageUrl: 'https://images.unsplash.com/photo-1605745341112-85968b19335b?w=320&h=180&fit=crop',
    badge: 'new',
    level: 'advanced',
  },
  {
    id: 7,
    title: 'Digital Marketing 360 - Chiến lược marketing online',
    instructor: 'Nguyễn Thị Lan',
    rating: 4.4,
    ratingCount: 8930,
    price: 299000,
    originalPrice: 999000,
    imageUrl: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=320&h=180&fit=crop',
    level: 'beginner',
  },
  {
    id: 8,
    title: 'AWS Cloud Practitioner - Chứng chỉ đám mây cơ bản',
    instructor: 'Đặng Minh Đức',
    rating: 4.6,
    ratingCount: 11200,
    price: 459000,
    originalPrice: 1499000,
    imageUrl: 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=320&h=180&fit=crop',
    badge: 'bestseller',
    level: 'beginner',
  },
  {
    id: 9,
    title: 'Node.js Backend Development - REST API & Authentication',
    instructor: 'Bùi Văn Hùng',
    rating: 4.8,
    ratingCount: 7650,
    price: 419000,
    originalPrice: 1399000,
    imageUrl: 'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=320&h=180&fit=crop',
    badge: 'hot',
    level: 'intermediate',
  },
  {
    id: 10,
    title: 'Figma Masterclass - Thiết kế giao diện chuyên nghiệp',
    instructor: 'Lý Ngọc Mai',
    rating: 4.9,
    ratingCount: 6340,
    price: 329000,
    originalPrice: 1099000,
    imageUrl: 'https://images.unsplash.com/photo-1586717791821-3f44a563fa4c?w=320&h=180&fit=crop',
    badge: 'new',
    level: 'beginner',
  },
  {
    id: 11,
    title: 'SQL & Database Design - Tối ưu truy vấn hiệu suất cao',
    instructor: 'Phan Văn Kiên',
    rating: 4.7,
    ratingCount: 9870,
    price: 379000,
    originalPrice: 1299000,
    imageUrl: 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=320&h=180&fit=crop',
    level: 'intermediate',
  },
  {
    id: 12,
    title: 'Agile & Scrum - Quản lý dự án phần mềm linh hoạt',
    instructor: 'Trương Thị Hương',
    rating: 4.5,
    ratingCount: 4560,
    price: 279000,
    originalPrice: 899000,
    imageUrl: 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=320&h=180&fit=crop',
    level: 'beginner',
  },
]

const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms))

function successResponse<T>(data: T, message: string): ApiResponse<T> {
  return { success: true, message, data, errors: null }
}

export async function fetchCategories(): Promise<ApiResponse<CourseCategory[]>> {
  await delay(200)
  return successResponse(MOCK_CATEGORIES, 'Categories fetched successfully')
}

export async function fetchCourseSections(): Promise<ApiResponse<CourseSection[]>> {
  await delay(400)
  const sections: CourseSection[] = [
    {
      id: 'trending',
      title: 'Học viên đang xem',
      subtitle: 'Khám phá những khóa học được quan tâm nhiều nhất tuần này',
      courses: MOCK_COURSES.slice(0, 6),
    },
    {
      id: 'bestsellers',
      title: 'Bán chạy nhất',
      subtitle: 'Các khóa học được đánh giá cao và mua nhiều nhất',
      courses: MOCK_COURSES.filter((c) => c.badge === 'bestseller'),
    },
    {
      id: 'new',
      title: 'Khóa học mới',
      subtitle: 'Cập nhật kiến thức mới nhất từ các giảng viên hàng đầu',
      courses: MOCK_COURSES.filter((c) => c.badge === 'new' || c.badge === 'hot'),
    },
    {
      id: 'web-dev',
      title: 'Phát triển Web',
      subtitle: 'Từ HTML/CSS cơ bản đến React, Node.js và microservices',
      courses: MOCK_COURSES.filter((_, i) => [0, 1, 2, 8].includes(i)),
    },
  ]
  return successResponse(sections, 'Course sections fetched successfully')
}

export async function searchCourses(keyword: string): Promise<ApiResponse<Course[]>> {
  await delay(300)
  const normalized = keyword.trim().toLowerCase()
  if (!normalized) {
    return successResponse([], 'No search keyword provided')
  }
  const results = MOCK_COURSES.filter(
    (c) =>
      c.title.toLowerCase().includes(normalized) ||
      c.instructor.toLowerCase().includes(normalized),
  )
  return successResponse(results, 'Courses searched successfully')
}

export const POPULAR_SEARCHES = ['React', 'Java', 'Python', 'UI/UX', 'AWS', 'Docker']
