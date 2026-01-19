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
import IVRManagementViewManufacturer from '@/views/IVRManagementViewManufacturer.vue'
import OrderManagementviewManufacturer from '@/views/OrderManagementviewManufacturer.vue'
import OrderManagementViewClinic from '@/views/OrderManagementViewClinic.vue'
import BrandManagementView from '@/views/BrandManagementView.vue'
import ProfileView from '@/views/ProfileView.vue'
import ChangeAccountView from '@/views/ChangeAccountView.vue'
import ResetPasswordView from '@/views/ResetPasswordView.vue'
import SettingsView from '@/views/SettingsView.vue'
import OrderMagicLinkView from '@/views/OrderMagicLinkView.vue'
import IVRMagicLinkView from '@/views/IVRMagicLinkView.vue'
import InvalidMagicLinkView from '@/views/InvalidMagicLinkView.vue'
import { pageLoader } from '@/composables/ui/usePageLoader'

// Icons
import { LayoutDashboard, UsersRound, Hospital, Factory, Package, ShieldCheck, ShoppingCart, ClipboardList, PencilRuler, ScanBarcode, BellRing, ChartColumn, CircleUserRound, Calculator, RotateCcw } from 'lucide-vue-next'

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
			// Dashboards
			{
				path: 'admin/dashboard',
				name: 'admin-dashboard',
				component: AdminDashboardView,
				meta: {
					requiresAuth: true,
					title: 'Dashboard',
					icon: LayoutDashboard
				}
			},
			{
				path: 'office-staff/dashboard',
				name: 'office-staff-dashboard',
				component: AdminDashboardView,
				meta: {
					requiresAuth: true,
					title: 'Dashboard',
					icon: LayoutDashboard
				}
			},
			{
				path: 'clinic/dashboard',
				name: 'clinic-dashboard',
				component: ClinicDashboardView,
				meta: {
					requiresAuth: true,
					title: 'Dashboard',
					icon: LayoutDashboard
				}
			},
			{
				path: 'clinician/dashboard',
				name: 'clinician-dashboard',
				component: ClinicDashboardView,
				meta: {
					requiresAuth: true,
					title: 'Dashboard',
					icon: LayoutDashboard
				}
			},
			// User Management
			{
				path: 'admin/users',
				name: 'admin-users',
				component: UserManagementView,
				meta: {
					requiresAuth: true,
					title: 'All Users',
					icon: UsersRound,
					role: 0
				}
			},
			{
				path: 'office-staff/users',
				name: 'office-staff-users',
				component: UserManagementView,
				meta: {
					requiresAuth: true,
					title: 'All Users',
					icon: UsersRound,
					role: 1
				}
			},
			{
				path: 'clinic/users',
				name: 'clinic-users',
				component: UserManagementView,
				meta: {
					requiresAuth: true,
					title: 'Clinicians',
					icon: UsersRound,
					role: 2
				}
			},
			{
				path: 'user-clinicians',
				name: 'user-clinicians',
				component: ClinicianManagementView,
				meta: {
					requiresAuth: true,
					title: 'Clinicians',
					icon: UsersRound
				}
			},
			// *****
			// Clinic/Manufacture/Brand/Graft Size Managements
			{
				path: 'clinic-management',
				name: 'clinic-management',
				component: ClinicManagementView,
				meta: {
					requiresAuth: true,
					title: 'Clinics',
					icon: Hospital
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
				path: 'graft-size-management',
				name: 'graft-size-management',
				component: GraftSizeManagementView,
				meta: {
					requiresAuth: true,
					title: 'Graft Size',
					icon: PencilRuler
				}
			},
			// IVR Management
			{
				path: 'admin/ivr-management',
				name: 'admin-ivr-management',
				component: IVRManagementView,
				meta: {
					requiresAuth: true,
					title: 'IVR',
					icon: ShieldCheck
				}
			},
			{
				path: 'office-staff/ivr-management',
				name: 'office-staff-ivr-management',
				component: IVRManagementView,
				meta: {
					requiresAuth: true,
					title: 'IVR',
					icon: ShieldCheck
				}
			},
			{
				path: 'clinic/ivr-management',
				name: 'clinic-ivr-management',
				component: IVRManagementView,
				meta: {
					requiresAuth: true,
					title: 'IVR',
					icon: ShieldCheck
				}
			},
			{
				path: 'clinician/ivr-management',
				name: 'clinician-ivr-management',
				component: IVRManagementView,
				meta: {
					requiresAuth: true,
					title: 'IVR',
					icon: ShieldCheck
				}
			},
			{
				path: 'manufacturer/ivr-management',
				name: 'manufacturer-ivr-management',
				component: IVRManagementViewManufacturer,
				meta: {
					requiresAuth: true,
					title: 'IVR',
					icon: ShieldCheck
				}
			},
			{
				path: 'biller/ivr-management',
				name: 'biller-ivr-management',
				component: IVRManagementViewManufacturer,
				meta: {
					requiresAuth: true,
					title: 'IVR',
					icon: ShieldCheck
				}
			},
			{
				path: 'woundmed-ivr-request',
				name: 'woundmed-ivr-request',
				component: IVRMagicLinkView,
				meta: {
					requiresAuth: false,
					title: 'IVR Details',
					icon: ShoppingCart,
					hideSidebar: true,
					hideHeader: true,
					disableLayoutPadding: true,
				}
			},
			// Order Management
			{
				path: 'admin/order-management',
				name: 'admin-order-management',
				component: OrderManagementView,
				meta: {
					requiresAuth: true,
					title: 'Orders',
					icon: ShoppingCart
				}
			},
			{
				path: 'office-staff/order-management',
				name: 'office-staff-order-management',
				component: OrderManagementView,
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
				path: 'clinician/order-management',
				name: 'clinician-order-management',
				component: OrderManagementViewClinic,
				meta: {
					requiresAuth: true,
					title: 'Orders',
					icon: ShoppingCart
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
			// Invoice Management
			{
				path: 'admin/invoice-management',
				name: 'admin-invoice-management',
				component: InvoiceManagementView,
				meta: {
					requiresAuth: true,
					title: 'Invoices',
					icon: ClipboardList
				}
			},
			{
				path: 'office-staff/invoice-management',
				name: 'office-staff-invoice-management',
				component: InvoiceManagementView,
				meta: {
					requiresAuth: true,
					title: 'Invoices',
					icon: ClipboardList
				}
			},
			{
				path: 'clinic/invoice-management',
				name: 'clinic-invoice-management',
				component: InvoiceManagementView,
				meta: {
					requiresAuth: true,
					title: 'Invoices',
					icon: ClipboardList
				}
			},
			{
				path: 'clinician/invoice-management',
				name: 'clinician-invoice-management',
				component: InvoiceManagementView,
				meta: {
					requiresAuth: true,
					title: 'Invoices',
					icon: ClipboardList
				}
			},
			{
				path: 'manufacturer/invoice-management',
				name: 'manufacturer-invoice-management',
				component: InvoiceManagementView,
				meta: {
					requiresAuth: true,
					title: 'Invoices',
					icon: ClipboardList
				}
			},
			{
				path: 'biller/invoice-management',
				name: 'biller-invoice-management',
				component: InvoiceManagementView,
				meta: {
					requiresAuth: true,
					title: 'Invoices',
					icon: ClipboardList
				}
			},
			// Inventory Management
			{
				path: 'admin/inventory',
				name: 'admin-inventory',
				component: InventoryManagementView,
				meta: {
					requiresAuth: true,
					title: 'Inventory',
					icon: ScanBarcode,
					role: 0
				}
			},
			{
				path: 'office-staff/inventory',
				name: 'office-staff-inventory',
				component: InventoryManagementView,
				meta: {
					requiresAuth: true,
					title: 'Inventory',
					icon: ScanBarcode,
					role: 1
				}
			},
			{
				path: 'manufacturer/inventory',
				name: 'manufacturer-inventory',
				component: InventoryManagementView,
				meta: {
					requiresAuth: true,
					title: 'Inventory',
					icon: ScanBarcode,
					role: 4
				}
			},
			{
				path: 'biller/inventory',
				name: 'biller-inventory',
				component: InventoryManagementView,
				meta: {
					requiresAuth: true,
					title: 'Inventory',
					icon: ScanBarcode,
					role: 5
				}
			},
			// Others --- Change if needed
			// Return Management
			{
				path: 'admin/returns',
				name: 'returns',
				component: ReturnManagementView,
				meta: {
					requiresAuth: true,
					title: 'Returns',
					icon: RotateCcw,
					role: 0
				}
			},
			// Usage Log Management
			{
				path: 'usage',
				name: 'usage',
				component: UsageLoggingView,
				meta: {
					requiresAuth: true,
					title: 'Graft Usage',
					icon: ClipboardList
				}
			},
			{
				path: 'smart-selector',
				name: 'smart-selector',
				component: () => import('@/views/SmartGraftSelectorView.vue'),
				meta: {
					requiresAuth: true,
					title: 'Smart Size Calculator',
					icon: Calculator
				}
			},
			{
				path: 'reports',
				name: 'reports',
				component: ReportCenterView,
				meta: {
					requiresAuth: true,
					title: 'Reports',
					icon: ChartColumn
				}
			},
			{
				path: 'notifications',
				name: 'notifications',
				component: NotificationCenterView,
				meta: {
					requiresAuth: true,
					title: 'Notifications',
					icon: BellRing
				}
			},
			{
				path: 'profile',
				name: 'profile',
				component: ProfileView,
				meta: {
					requiresAuth: true,
					title: 'Profile',
					icon: CircleUserRound
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
		// Dashboard
		'admin-dashboard': [0],
		'office-staff-dashboard': [1],
		'clinic-dashboard': [2],
		'clinician-dashboard': [3],
		// User Management
		'admin-users': [0],
		'office-staff-users': [1],
		'clinic-users': [2],
		// Clinic/Manufacturers/Brands/Graft Size Management
		'clinic-management': [0, 1],
		'manufacturer-management': [0, 1],
		'brand-management': [0, 1],
		'graft-size-management': [0, 1],
		// IVR Management
		'admin-ivr-management': [0],
		'office-staff-ivr-management': [1],
		'clinic-ivr-management': [2],
		'clinician-ivr-management': [3],
		'manufacturer-ivr-management': [4],
		'biller-ivr-management': [5],
		// Order Management
		'admin-order-management': [0],
		'office-staff-order-management': [1],
		'clinic-order-management': [2],
		'clinician-order-management': [3],
		'manufacturer-order-management': [4],
		// Invoice Management
		'admin-invoice-management': [0],
		'office-staff-invoice-management': [1],
		'clinic-invoice-management': [2],
		'clinician-invoice-management': [3],
		'manufacturer-invoice-management': [4],
		'biller-invoice-management': [5],
		// Inventory Management
		'admin-inventory': [0],
		'office-staff-inventory': [1],
		'manufacturer-inventory': [4],
		'biller-inventory': [5],
		'invoice-management': [0, 1, 2],
		'ivr-management': [0, 1, 2, 4],
		'order-management': [0, 1, 2, 3],
		'manufacturer/order-management': [4],
		'manufacturer/ivr-management': [4],
		'clinic/order-management': [2],
		// Return Management
		'returns': [0,1,2,3],

		// Other Management
		'usage': [],
		'smart-selector': [],
		'reports': [0],
		'notifications': [0, 1],
		'profile': [],
	}

	return layoutRoute.children
		.filter(route => {
			// Admin
			if (role === 0) {
				return [
					'admin-dashboard',
					'admin-users',
					'clinic-management',
					'manufacturer-management',
					'brand-management',
					'graft-size-management',
					'admin-ivr-management',
					'admin-order-management',
					'admin-invoice-management',
					'admin-inventory',
					'returns',
					'reports',
				].includes(route.name as string)
			}

			// Office Staff
			if (role === 1) {
				return [
					'office-staff-dashboard',
					'office-staff-users',
					'clinic-management',
					'manufacturer-management',
					'brand-management',
					'graft-size-management',
					'office-staff-ivr-management',
					'office-staff-order-management',
					'office-staff-invoice-management',
					'office-staff-inventory'
				].includes(route.name as string)
			}

			// Clinics
			if (role === 2) {
				return [
					'clinic-dashboard',
					'clinic-users',
					'clinic-ivr-management',
					'clinic-order-management',
					'clinic-invoice-management'
				].includes(route.name as string)
			}

			// Clinician
			if (role === 3) {
				return [
					'clinician-dashboard',
					'clinician-ivr-management',
					'clinician-order-management',
					'clinician-invoice-management'
				].includes(route.name as string)
			}

			// Manufacturer
			if (role === 4) {
				return [
					'manufacturer-ivr-management',
					'manufacturer-order-management',
					'manufacturer-invoice-management',
					'office-staff-inventory'
				].includes(route.name as string)
			}

			// Biller
			if (role === 5) {
				return [
					'biller-ivr-management',
					'biller-invoice-management',
				].includes(route.name as string)
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
					case 'invoice-management':
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

	// allow access publicly - dili na need lag-en
	if (to.name === 'woundmed-order' || to.name === 'woundmed-ivr-request') {
		next();
		return;
	}

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
		if (to.name === 'clinic-dashboard' && ![Admin, OfficeStaff, Clinics, Clinician].includes(role)) {
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
		if (role === Clinics || role === Clinician) {
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