interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'outline' | 'ghost'
  loading?: boolean
  fullWidth?: boolean
}

export default function Button({
  children,
  variant = 'primary',
  loading = false,
  fullWidth = false,
  disabled,
  className = '',
  ...props
}: ButtonProps) {
  const base =
    'inline-flex items-center justify-center rounded-sm px-4 py-3 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-60'

  const variants = {
    primary: 'bg-udemy-purple text-white hover:bg-udemy-purple-dark',
    outline: 'border border-udemy-dark bg-white text-udemy-dark hover:bg-udemy-light-gray',
    ghost: 'bg-transparent text-udemy-dark hover:bg-udemy-light-gray',
  }

  return (
    <button
      disabled={disabled || loading}
      className={`${base} ${variants[variant]} ${fullWidth ? 'w-full' : ''} ${className}`}
      {...props}
    >
      {loading ? (
        <span className="inline-flex items-center gap-2">
          <span className="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white" />
          Đang xử lý...
        </span>
      ) : (
        children
      )}
    </button>
  )
}
