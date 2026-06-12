import { forwardRef, useId, useState } from 'react'
import Input from '../../../shared/components/ui/atomic/Input'

interface PasswordInputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'type'> {
  label: string
  error?: string
  hint?: string
}

const PasswordInput = forwardRef<HTMLInputElement, PasswordInputProps>(
  ({ label, error, hint, className = '', ...props }, ref) => {
    const [visible, setVisible] = useState(false)
    const inputId = useId()

    return (
      <div className="relative w-full">
        <Input
          ref={ref}
          id={inputId}
          label={label}
          error={error}
          hint={hint}
          type={visible ? 'text' : 'password'}
          className={`pr-12 ${className}`}
          {...props}
        />
        <button
          type="button"
          onClick={() => setVisible((v) => !v)}
          className="absolute right-3 top-[38px] text-xs font-bold text-udemy-purple hover:text-udemy-purple-dark"
          aria-label={visible ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'}
        >
          {visible ? 'Ẩn' : 'Hiện'}
        </button>
      </div>
    )
  },
)

PasswordInput.displayName = 'PasswordInput'

export default PasswordInput
