interface AuthDividerProps {
  label?: string
}

export default function AuthDivider({ label = 'hoặc' }: AuthDividerProps) {
  return (
    <div className="relative my-6">
      <div className="absolute inset-0 flex items-center">
        <div className="w-full border-t border-udemy-border" />
      </div>
      <div className="relative flex justify-center">
        <span className="bg-white px-3 text-sm text-udemy-gray">{label}</span>
      </div>
    </div>
  )
}
