import { lazy } from 'react'
import { createBrowserRouter } from 'react-router'
import { Suspense } from 'react'
import { LoginLayout, RegisterLayout } from '../shared/components/layout/AuthLayout'

const HomePage = lazy(() => import('../pages/HomePage'))
const LoginPage = lazy(() => import('../pages/LoginPage'))
const RegisterPage = lazy(() => import('../pages/RegisterPage'))
const ForgotPassword = lazy(() => import('../pages/ForgotPassword'))
const CourseDetailPage = lazy(() => import('../pages/CourseDetailPage'))
const CourseListPage = lazy(() => import('../pages/CourseListPage'))
const DashboardPage = lazy(() => import('../pages/DashboardPage'))
const MyCoursesPage = lazy(() => import('../pages/MyCoursesPage'))
const ProfilePage = lazy(() => import('../pages/ProfilePage'))
const PublicRoute = lazy(() => import('./PublicRoute'))
const PrivateRoute = lazy(() => import('./PrivateRoute'))

const lazyRoutes = (children: React.ReactNode) => (
  <Suspense fallback={<div className="flex min-h-[50vh] items-center justify-center text-udemy-gray">Loading...</div>}>
    {children}
  </Suspense>
)

const routes = createBrowserRouter([
  {
    path: '/',
    Component: () => lazyRoutes(<PublicRoute />),
    children: [
      {
        path: '/',
        Component: () => lazyRoutes(<HomePage />),
      },
      {
        path: '/course-detail/:id',
        Component: () => lazyRoutes(<CourseDetailPage />),
      },
      {
        path: '/course-list',
        Component: () => lazyRoutes(<CourseListPage />),
      },
    ],
  },
  {
    Component: () => lazyRoutes(<LoginLayout />),
    children: [
      {
        path: '/login',
        Component: () => lazyRoutes(<LoginPage />),
      },
      {
        path: '/forgot-password',
        Component: () => lazyRoutes(<ForgotPassword />),
      },
    ],
  },
  {
    Component: () => lazyRoutes(<RegisterLayout />),
    children: [
      {
        path: '/register',
        Component: () => lazyRoutes(<RegisterPage />),
      },
    ],
  },
  {
    path: '/',
    Component: () => lazyRoutes(<PrivateRoute />),
    children: [
      {
        path: '/dashboard',
        Component: () => lazyRoutes(<DashboardPage />),
      },
      {
        path: '/my-courses',
        Component: () => lazyRoutes(<MyCoursesPage />),
      },
      {
        path: '/profile',
        Component: () => lazyRoutes(<ProfilePage />),
      },
    ],
  },
])

export default routes
