import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { useEffect } from 'react'
import { useProfile, useUpdateProfile } from '../hooks/useAuth'
import { profileSchema, type ProfileFormValues } from '../schemas/auth.schema'
import Button from '../../../shared/components/ui/atomic/Button'
import Input from '../../../shared/components/ui/atomic/Input'

export default function ProfileForm() {
  const profileQuery = useProfile()
  const updateMutation = useUpdateProfile()

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isDirty },
  } = useForm<ProfileFormValues>({
    resolver: zodResolver(profileSchema),
    defaultValues: {
      fullName: '',
      avatarUrl: '',
      locale: 'vi',
    },
  })

  useEffect(() => {
    if (profileQuery.data) {
      reset({
        fullName: profileQuery.data.fullName || '',
        avatarUrl: profileQuery.data.avatarUrl || '',
        locale: profileQuery.data.locale || 'vi',
      })
    }
  }, [profileQuery.data, reset])

  const onSubmit = (values: ProfileFormValues) => {
    updateMutation.mutate({
      fullName: values.fullName,
      avatarUrl: values.avatarUrl || null,
      locale: values.locale,
    })
  }

  if (profileQuery.isLoading) {
    return <p className="text-sm text-udemy-gray">Đang tải hồ sơ...</p>
  }

  if (profileQuery.isError) {
    return (
      <div
        className="rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        role="alert"
      >
        {profileQuery.error.message}
      </div>
    )
  }

  return (
    <div className="w-full max-w-[640px]">
      <h1 className="text-2xl font-bold text-udemy-dark md:text-[2rem]">Hồ sơ cá nhân</h1>
      <p className="mt-2 text-sm text-udemy-gray">
        Cập nhật thông tin hiển thị trên tài khoản của bạn.
      </p>

      {profileQuery.data && (
        <p className="mt-2 text-sm text-udemy-gray">
          Email: <span className="font-medium text-udemy-dark">{profileQuery.data.email}</span>
          {profileQuery.data.emailVerified ? (
            <span className="ml-2 text-green-600">Đã xác minh</span>
          ) : (
            <span className="ml-2 text-amber-600">Chưa xác minh</span>
          )}
        </p>
      )}

      {updateMutation.isSuccess && (
        <div
          className="mt-4 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
          role="status"
        >
          Cập nhật hồ sơ thành công.
        </div>
      )}

      {updateMutation.isError && (
        <div
          className="mt-4 rounded-sm border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          role="alert"
        >
          {updateMutation.error.message}
        </div>
      )}

      <form className="mt-6 space-y-4" onSubmit={handleSubmit(onSubmit)} noValidate>
        <Input
          label="Họ và tên"
          type="text"
          autoComplete="name"
          error={errors.fullName?.message}
          {...register('fullName')}
        />

        <Input
          label="Ảnh đại diện (URL)"
          type="url"
          placeholder="https://example.com/avatar.jpg"
          error={errors.avatarUrl?.message}
          {...register('avatarUrl')}
        />

        <div>
          <label htmlFor="locale" className="mb-1 block text-sm font-medium text-udemy-dark">
            Ngôn ngữ
          </label>
          <select
            id="locale"
            className="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-udemy-purple focus:outline-none"
            {...register('locale')}
          >
            <option value="vi">Tiếng Việt</option>
            <option value="en">English</option>
          </select>
          {errors.locale?.message && (
            <p className="mt-1 text-sm text-red-600">{errors.locale.message}</p>
          )}
        </div>

        <Button
          type="submit"
          className="w-full"
          disabled={updateMutation.isPending || !isDirty}
        >
          {updateMutation.isPending ? 'Đang lưu...' : 'Lưu thay đổi'}
        </Button>
      </form>
    </div>
  )
}
