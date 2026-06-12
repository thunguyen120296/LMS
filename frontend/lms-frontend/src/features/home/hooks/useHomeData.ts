import { useQuery } from '@tanstack/react-query'
import { fetchCategories, fetchCourseSections, searchCourses } from '../api/home.api'

export function useCategories() {
  return useQuery({
    queryKey: ['home', 'categories'],
    queryFn: async () => {
      const response = await fetchCategories()
      return response.data
    },
  })
}

export function useCourseSections() {
  return useQuery({
    queryKey: ['home', 'course-sections'],
    queryFn: async () => {
      const response = await fetchCourseSections()
      return response.data
    },
  })
}

export function useCourseSearch(keyword: string) {
  return useQuery({
    queryKey: ['home', 'search', keyword],
    queryFn: async () => {
      const response = await searchCourses(keyword)
      return response.data
    },
    enabled: keyword.trim().length >= 2,
  })
}
