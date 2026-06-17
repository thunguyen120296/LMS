import { Outlet } from 'react-router'
import PublicHeader from './PublicHeader'

export default function PrivateLayout({ children }: { children?: React.ReactNode }) {
  return (
    <div className="flex min-h-screen flex-col bg-udemy-light-gray">
      <PublicHeader variant="private" />
      <main className="flex-1">
        {children ?? <Outlet />}
      </main>
    </div>
  )
}
