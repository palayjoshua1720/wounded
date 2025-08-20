import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/layout/AppLayout.vue'
import LoginView from '@/views/LoginView.vue'
import NotFoundView from '@/views/NotFoundView.vue'
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
import ClinicManagementView from '@/views/ClinicManagementView.vue'
import ProfileView from '@/views/ProfileView.vue'
import ChangeAccountView from '@/views/ChangeAccountView.vue'
import SettingsView from '@/views/SettingsView.vue'
import { ClipboardDocumentCheckIcon, Squares2X2Icon, BuildingLibraryIcon, ClipboardDocumentListIcon, ShieldCheckIcon, BellIcon, ShoppingCartIcon, ChartBarIcon, ArrowPathIcon, CalculatorIcon, CubeIcon, UsersIcon } from '@heroicons/vue/24/outline'

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
			path: 'clinic',
			name: 'clinic',
			component: ClinicManagementView,
			meta: {
			requiresAuth: true,
				title: 'Clinic Management',
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
	const authStore = useAuthStore()
	const layoutRoute = routes.find(route => route.path === '/')
	if (!layoutRoute?.children) return []

	const role = authStore.user?.user_role ?? 0

	const routeRoles: Record<string, number[]> = {
		'admin-dashboard': [0, 1],
		'clinic-dashboard': [0, 1, 2],
		'clinic': [0, 1, 2],
		'inventory': [0, 1],
		'invoices': [0, 1],
		'ivr': [0, 1],
		'notifications': [0, 1],
		'orders': [0, 1],
		'reports': [0, 1],
		'returns': [0, 1],
		'usage': [0, 1],
		'users': [0, 1],
		'smart-selector': [0, 1, 2, 3, 4, 5, 6],
	}

	return layoutRoute.children
	.filter(route => {
		if (role === 0) {
			return true
		}

		if (role === 2) {
			return ['clinic-dashboard', 'clinic'].includes(route.name as string)
		}

		const allowedRoles = routeRoles[route.name as string]
      	return allowedRoles?.includes(role) ?? false
	})
	.map(route => {
		const meta = route.meta as RouteMeta
		let title = meta?.title || String(route.name)

		// Role-based title adjustments
		if (role === 2) {
			switch (route.name) {
			case 'orders':
				title = 'My Orders'
				break
			case 'usage':
				title = 'Usage Logging'
				break
			case 'invoices':
				title = 'Invoices'
				break
			case 'ivr':
				title = 'IVR Status'
				break
			case 'reports':
				title = 'Reports'
				break
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

	const Admin = 0;
	const OfficeStaff = 1;
	const Clinics = 2;
	const Clinician = 3;
	const Manufacturer = 4;
	const Biller = 5;

	// Check if route requires authentication
	if (requiresAuth) {
		const isAuthenticated = await authStore.checkAuth()
		if (!isAuthenticated) {
			next({ name: 'login', query: { redirect: to.fullPath } })
			return
		}

		// Ensure role is numeric
		const role = authStore.user?.user_role ?? parseInt(localStorage.getItem('user_role') || '-1')

		// Admin Dashboard access → Admin + OfficeStaff
		if (to.name === 'admin-dashboard' && role !== Admin && role !== OfficeStaff) {
			next({ name: 'clinic-dashboard' })
			return
		}

		// Clinic Dashboard access → Admin + OfficeStaff + Clinics
		if (to.name === 'clinic-dashboard' && role !== Admin && role !== OfficeStaff && role !== Clinics) {
			next({ name: 'admin-dashboard' })
			return
		}

		// Notifications / Orders / Reports → Admin + OfficeStaff + Clinics
		if (['notifications', 'orders', 'reports'].includes(to.name?.toString() || '') && role !== Admin && role !== OfficeStaff && role !== Clinics) {
			next({ name: 'admin-dashboard' })
			return
		}
		}

		// If authenticated and going to login page
		if (to.name === 'login' && authStore.isAuthenticated) {
		const role = authStore.user?.user_role ?? parseInt(localStorage.getItem('user_role') || '-1')
		if (role === Clinics) {
			next({ name: 'clinic-dashboard' })
		} else {
			next({ name: 'admin-dashboard' })
		}
		return
	}

	next()
})

export default router 