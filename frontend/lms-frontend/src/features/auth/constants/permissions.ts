export const PERMISSIONS = {
  COURSE_VIEW: 'COURSE:VIEW',
  COURSE_CREATE: 'COURSE:CREATE',
} as const

export type Permission = (typeof PERMISSIONS)[keyof typeof PERMISSIONS]

export const ROLES = {
  STUDENT: 'STUDENT',
  INSTRUCTOR: 'INSTRUCTOR',
  ADMIN: 'ADMIN',
} as const

export type Role = (typeof ROLES)[keyof typeof ROLES]
