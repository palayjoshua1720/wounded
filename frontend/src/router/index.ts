import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/layout/AppLayout.vue'
import HomeView from '@/views/HomeView.vue'
import AboutView from '@/views/AboutView.vue'
import LoginView from '@/views/LoginView.vue'
import NotFoundView from '@/views/NotFoundView.vue'
import HomeIcon from '@/components/icons/HomeIcon.vue'
import AboutIcon from '@/components/icons/AboutIcon.vue'
import ForgotPasswordView from '@/views/ForgotPasswordView.vue'
import AdminDashboardView from '@/views/AdminDashboardView.vue'
import ClinicDashboardView from '@/views/ClinicDashboardView.vue'
import InventoryManagementView from '@/views/InventoryManagementView.vue'
import InvoiceManagementView from '@/views/InvoiceManagementView.vue'
import IVRManagementView from '@/views/IVRManagementView.vue'
import NotificationCenterView from '@/views/NotificationCenterView.vue'
import OrderManagementView from '@/views/OrderManagementView.vue'
import ReportCenterView from '@/views/ReportCenterView.vue'
import ReturnManagementView from '@/views/ReturnManagementView.vue'
import UsageLoggingView from '@/views/UsageLoggingView.vue'
import UserManagementView from '@/views/UserManagementView.vue'
import ProfileView from '@/views/ProfileView.vue'
import ChangeAccountView from '@/views/ChangeAccountView.vue'
import SettingsView from '@/views/SettingsView.vue'
import { ClipboardDocumentCheckIcon, Squares2X2Icon, BuildingLibraryIcon, ClipboardDocumentListIcon, ShieldCheckIcon, BellIcon, ShoppingCartIcon, ChartBarIcon, ArrowPathIcon, UsersIcon, CalculatorIcon, CubeIcon } from '@heroicons/vue/24/outline'

// Types
interface NavigationItem {
  name: string
  to: string
  icon: any
}

interface RouteMeta {
  requiresAuth?: boolean
  title?: string
  icon?: any
}

// Route Configuration
const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'home',
        component: HomeView,
        meta: {
          requiresAuth: true,
          title: 'Home',
          icon: HomeIcon
        }
      },
      {
        path: 'about',
        name: 'about',
        component: AboutView,
        meta: {
          requiresAuth: true,
          title: 'About',
          icon: AboutIcon
        }
      },
      {
        path: 'admin-dashboard',
        name: 'admin-dashboard',
        component: AdminDashboardView,
        meta: {
          requiresAuth: true,
          title: 'Dashboard',
          icon: Squares2X2Icon
        }
      },
      {
        path: 'clinic-dashboard',
        name: 'clinic-dashboard',
        component: ClinicDashboardView,
        meta: {
          requiresAuth: true,
          title: 'Clinic Dashboard',
          icon: BuildingLibraryIcon
        }
      },
      {
        path: 'users',
        name: 'users',
        component: UserManagementView,
        meta: {
          requiresAuth: true,
          title: 'User Management',
          icon: UsersIcon
        }
      },
      {
        path: 'orders',
        name: 'orders',
        component: OrderManagementView,
        meta: {
          requiresAuth: true,
          title: 'Order Management',
          icon: ShoppingCartIcon
        }
      },
      {
        path: 'usage',
        name: 'usage',
        component: UsageLoggingView,
        meta: {
          requiresAuth: true,
          title: 'Graft Usage',
          icon: ClipboardDocumentCheckIcon
        }
      },
      {
        path: 'invoices',
        name: 'invoices',
        component: InvoiceManagementView,
        meta: {
          requiresAuth: true,
          title: 'Invoices & Payments',
          icon: ClipboardDocumentListIcon
        }
      },
      {
        path: 'ivr',
        name: 'ivr',
        component: IVRManagementView,
        meta: {
          requiresAuth: true,
          title: 'IVR Management',
          icon: ShieldCheckIcon
        }
      },
      {
        path: 'inventory',
        name: 'inventory',
        component: InventoryManagementView,
        meta: {
          requiresAuth: true,
          title: 'Inventory & Serials',
          icon: CubeIcon
        }
      },
      {
        path: 'notifications',
        name: 'notifications',
        component: NotificationCenterView,
        meta: {
          requiresAuth: true,
          title: 'Notifications',
          icon: BellIcon
        }
      },
      {
        path: 'returns',
        name: 'returns',
        component: ReturnManagementView,
        meta: {
          requiresAuth: true,
          title: 'Return Management',
          icon: ArrowPathIcon
        }
      },
      {
        path: 'profile',
        name: 'profile',
        component: ProfileView,
        meta: {
          requiresAuth: true,
          title: 'Profile',
          icon: UsersIcon
        }
      },
      {
        path: 'smart-selector',
        name: 'smart-selector',
        component: () => import('@/views/SmartGraftSelectorView.vue'),
        meta: {
          requiresAuth: true,
          title: 'Smart Size Calculator',
          icon: CalculatorIcon // You may need to import this icon at the top
        }
      },
      {
        path: 'reports',
        name: 'reports',
        component: ReportCenterView,
        meta: {
          requiresAuth: true,
          title: 'Reports',
          icon: ChartBarIcon
        }
      },
    ]
  },
  {
    path: '/settings',
    name: 'settings',
    component: SettingsView,
    meta: {
      requiresAuth: true,
      title: 'Settings'
    }
  },
  {
    path: '/change-account',
    name: 'change-account',
    component: ChangeAccountView,
    meta: {
      requiresAuth: false,
      title: 'Change Account'
    }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: {
      requiresAuth: false,
      title: 'Login'
    }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPasswordView,
    meta: {
      requiresAuth: false,
      title: 'Forgot Password'
    }
  },
  {
    path: '/500',
    name: 'server-error',
    component: () => import('@/views/ServerErrorView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
    meta: {
      requiresAuth: false,
      title: 'Not Found'
    }
  }
]

// Router Instance
const router = createRouter({
  history: createWebHistory(process.env.VUE_APP_BASE_URL),
  routes
})

// Navigation Helper
export const getNavigationItems = (routes: RouteRecordRaw[]): NavigationItem[] => {
  const layoutRoute = routes.find(route => route.path === '/')
  if (!layoutRoute?.children) return []
  const role = localStorage.getItem('mock-role')
  return layoutRoute.children
    .filter(route => {
      if (route.name === 'admin-dashboard') {
        return role === 'admin' || role === 'sales'
      }
      if (route.name === 'clinic-dashboard') {
        return role === 'admin' || role === 'clinic' || role === 'sales'
      }
      if (route.name === 'inventory') {
        return role === 'admin' || role === 'sales'
      }
      if (route.name === 'invoices') {
        return role === 'admin' || role === 'clinic' || role === 'sales'
      }
      if (route.name === 'ivr') {
        return role === 'admin' || role === 'clinic' || role === 'sales'
      }
      if (route.name === 'notifications') {
        return role === 'admin' || role === 'clinic' || role === 'sales'
      }
      if (route.name === 'orders') {
        return role === 'admin' || role === 'clinic' || role === 'sales'
      }
      if (route.name === 'reports') {
        return role === 'admin' || role === 'clinic' || role === 'sales'
      }
      if (route.name === 'returns') {
        return role === 'admin' || role === 'sales'
      }
      if (route.name === 'usage') {
        return role === 'admin' || role === 'clinic' || role === 'sales'
      }
      if (route.name === 'users') {
        return role === 'admin' || role === 'sales'
      }
      if (route.name === 'smart-selector') {
        return true // Show for all roles
      }
      return false
    })
    .map(route => {
      const meta = route.meta as RouteMeta
      let title = meta?.title || String(route.name)
      
      // Role-based title adjustments
      if (role === 'clinic') {
        if (route.name === 'orders') {
          title = 'My Orders'
        } else if (route.name === 'usage') {
          title = 'Usage Logging'
        } else if (route.name === 'invoices') {
          title = 'Invoices'
        } else if (route.name === 'ivr') {
          title = 'IVR Status'
        } else if (route.name === 'reports') {
          title = 'Reports'
        }
      }
      
      return {
        name: title,
        to: `/${route.path}`,
        icon: meta?.icon
      }
    })
}

// Navigation Guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

  // Check if route requires authentication
  if (requiresAuth) {
    // Check if user is authenticated
    const isAuthenticated = await authStore.checkAuth()
    if (!isAuthenticated) {
      next({ name: 'login', query: { redirect: to.fullPath } })
      return
    }
    // Role-based access
    const role = authStore.user?.role || localStorage.getItem('mock-role')
    if (to.name === 'admin-dashboard' && role !== 'admin' && role !== 'sales') {
      // Only admin and sales can access admin-dashboard
      next({ name: 'clinic-dashboard' })
      return
    }
    if (to.name === 'clinic-dashboard' && role !== 'admin' && role !== 'clinic' && role !== 'sales') {
      // Only admin, clinic, and sales can access clinic-dashboard
      next({ name: 'admin-dashboard' })
      return
    }
    if (to.name === 'notifications' && role !== 'admin' && role !== 'clinic' && role !== 'sales') {
      // Only admin, clinic, and sales can access notifications
      next({ name: 'admin-dashboard' })
      return
    }
    if (to.name === 'orders' && role !== 'admin' && role !== 'clinic' && role !== 'sales') {
      // Only admin, clinic, and sales can access orders
      next({ name: 'admin-dashboard' })
      return
    }
    if (to.name === 'reports' && role !== 'admin' && role !== 'clinic' && role !== 'sales') {
      // Only admin, clinic, and sales can access reports
      next({ name: 'admin-dashboard' })
      return
    }
  }

  // If user is authenticated and trying to access login page
  if (to.name === 'login' && authStore.isAuthenticated) {
    const role = authStore.user?.role || localStorage.getItem('mock-role')
    if (role === 'clinic') {
      next({ name: 'clinic-dashboard' })
    } else {
      // admin and sales users go to admin dashboard
      next({ name: 'admin-dashboard' })
    }
    return
  }

  next()
})

export default router 