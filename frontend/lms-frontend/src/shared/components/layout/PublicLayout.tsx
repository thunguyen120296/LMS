import { Outlet } from 'react-router'
import PublicFooter from './PublicFooter'
import PublicHeader from './PublicHeader'

export default function PublicLayout() {
  return (
    <div className="flex min-h-screen flex-col bg-white">
      <PublicHeader />
      <main className="flex-1">
        <Outlet />
      </main>
      <PublicFooter />
    </div>
  )
}
