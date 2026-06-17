interface AuthLoadingProps {
  fullScreen?: boolean
}

export default function AuthLoading({ fullScreen = false }: AuthLoadingProps) {
  return (
    <div
      className={
        fullScreen
          ? 'flex min-h-screen items-center justify-center bg-white'
          : 'flex min-h-[50vh] items-center justify-center'
      }
    >
      <div className="flex flex-col items-center gap-3">
        <div className="h-8 w-8 animate-spin rounded-full border-2 border-udemy-purple border-t-transparent" />
        <p className="text-sm text-udemy-gray">Đang tải...</p>
      </div>
    </div>
  )
}
