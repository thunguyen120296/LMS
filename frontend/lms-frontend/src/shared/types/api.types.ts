export interface ApiMeta {
  page: number
  size: number
  totalItems: number
  totalPages: number
}

export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
  meta?: ApiMeta
  errors: null | Array<{ field: string; message: string }>
}
