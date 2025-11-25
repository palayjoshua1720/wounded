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
import GraftSizeManagementView from '@/views/GraftSizeManagementView.vue'
import NotificationCenterView from '@/views/NotificationCenterView.vue'
import OrderManagementView from '@/views/OrderManagementView.vue'
import ReportCenterView from '@/views/ReportCenterView.vue'
import ReturnManagementView from '@/views/ReturnManagementView.vue'
import UsageLoggingView from '@/views/UsageLoggingView.vue'
import UserManagementView from '@/views/UserManagementView.vue'
import ClinicianManagementView from '@/views/ClinicianManagementView.vue'
import ClinicManagementView from '@/views/ClinicManagementView.vue'
import ManufacturerManagementView from '@/views/ManufacturerManagementView.vue'
import OrderManagementviewManufacturer from '@/views/OrderManagementviewManufacturer.vue'
import OrderManagementViewClinic from '@/views/OrderManagementViewClinic.vue'
import BrandManagementView from '@/views/BrandManagementView.vue'
import ProfileView from '@/views/ProfileView.vue'
import ChangeAccountView from '@/views/ChangeAccountView.vue'
import ResetPasswordView from '@/views/ResetPasswordView.vue'
import SettingsView from '@/views/SettingsView.vue'
import OrderMagicLinkView from '@/views/OrderMagicLinkView.vue'
import InvalidMagicLinkView from '@/views/InvalidMagicLinkView.vue'

import { ClipboardDocumentCheckIcon, Squares2X2Icon, SquaresPlusIcon, BuildingLibraryIcon, ClipboardDocumentListIcon, ShieldCheckIcon, BellIcon, ChartBarIcon, ArrowPathIcon, CalculatorIcon, CubeIcon, UsersIcon } from '@heroicons/vue/24/outline'
import { pageLoader } from '@/composables/ui/usePageLoader'
import { Factory, Package, PencilRuler, ShoppingCart} from 'lucide-vue-next'

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
					icon: SquaresPlusIcon
				}
			},
			{
				path: 'users',
				name: 'users',
				component: UserManagementView,
				meta: {
					requiresAuth: true,
					title: 'All Users',
					icon: UsersIcon
				}
			},
			{
				path: 'user-clinicians',
				name: 'user-clinicians',
				component: ClinicianManagementView,
				meta: {
					requiresAuth: true,
					title: 'Clinicians',
					icon: UsersIcon
				}
			},
			{
				path: 'clinic-management',
				name: 'clinic-management',
				component: ClinicManagementView,
				meta: {
					requiresAuth: true,
					title: 'Clinics',
					icon: BuildingLibraryIcon
				}
			},
			{
				path: 'manufacturer-management',
				name: 'manufacturer-management',
				component: ManufacturerManagementView,
				meta: {
					requiresAuth: true,
					title: 'Manufacturers',
					icon: Factory
				}
			},
			{
				path: 'brand-management',
				name: 'brand-management',
				component: BrandManagementView,
				meta: {
					requiresAuth: true,
					title: 'Brands',
					icon: Package
				}
			},
			{
				path: 'order-management',
				name: 'order-management',
				component: OrderManagementView,
				meta: {
					requiresAuth: true,
					title: 'Orders',
					icon: ShoppingCart
				}
			},
			{
				path: 'woundmed-order',
				name: 'woundmed-order',
				component: OrderMagicLinkView,
				meta: {
					requiresAuth: false,
					title: 'Order Details',
					icon: ShoppingCart,
					hideSidebar: true,
					hideHeader: true,
					disableLayoutPadding: true,
				}
			},
			{
				path: 'manufacturer/order-management',
				name: 'manufacturer/order-management',
				component: OrderManagementviewManufacturer,
				meta: {
					requiresAuth: true,
					title: 'Orders',
					icon: ShoppingCart
				}
			},
			{
				path: 'clinic/order-management',
				name: 'clinic/order-management',
				component: OrderManagementViewClinic,
				meta: {
					requiresAuth: true,
					title: 'Orders',
					icon: ShoppingCart
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
				path: 'invoice-management',
				name: 'invoice-management',
				component: InvoiceManagementView,
				meta: {
					requiresAuth: true,
					title: 'Invoices & Payments',
					icon: ClipboardDocumentListIcon
				}
			},
			{
				path: 'ivr-management',
				name: 'ivr-management',
				component: IVRManagementView,
				meta: {
					requiresAuth: true,
					title: 'IVR',
					icon: ShieldCheckIcon
				}
			},
			{
				path: 'graft-size',
				name: 'graft-size',
				component: GraftSizeManagementView,
				meta: {
					requiresAuth: true,
					title: 'Graft Size',
					icon: PencilRuler
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
		path: '/reset-password',
		name: 'reset-password',
		component: ResetPasswordView,
		meta: { requiresAuth: false }
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
	},
	{
		path: '/:pathMatch(.*)*',
		name: 'not-found-link',
		component: InvalidMagicLinkView,
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
		'clinic-management': [0, 1, 2],
		'inventory': [0, 1],
		'invoice-management': [0, 1],
		'ivr-management': [0, 1],
		'notifications': [0, 1],
		'order-management': [0, 1],
		'manufacturer/order-management': [4],
		'clinic/order-management': [2],
		'reports': [0, 1],
		'returns': [0, 1],
		'usage': [0, 1],
		'users': [0, 1],
		'smart-selector': [0, 1, 2, 3, 4, 5, 6],
	}

	return layoutRoute.children
		.filter(route => {
			// if (role === 0) {
			// 	return true
			// }

			if (role === 0) {
				return [
					'admin-dashboard',
					'users',
					'clinic-management',
					'user-clinicians',
					'manufacturer-management',
					'clinic-dashboard',
					'ivr-management',
					'order-management',
					'inventory',
					'brand-management',
					'graft-size',
					'invoice-management'
				].includes(route.name as string)
			}

			if (role === 1) {
				return ['admin-dashboard', 'users', 'clinic-management', 'manufacturer-management', 'inventory', 'invoice-management'].includes(route.name as string)
			}

			if (role === 2) {
				return ['clinic-dashboard', 'user-clinicians', 'clinic-management', 'clinic/order-management'].includes(route.name as string)
			}

			// manufacturer
			if (role === 4) {
				return ['manufacturer/order-management'].includes(route.name as string)
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
	pageLoader.value = true
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

router.afterEach(() => {
	pageLoader.value = false
})

export default router 