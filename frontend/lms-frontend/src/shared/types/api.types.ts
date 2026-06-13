export interface ApiMeta {
  page: number
  size: number
  totalItems: number
  totalPages: number
}

export interface ApiFieldError {
  field: string
  message: string
}

export interface ApiResponse<T = unknown> {
  success: boolean
  message: string
  data?: T
  meta?: ApiMeta
  errors?: ApiFieldError[] | null
}
