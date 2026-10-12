<template>
	<PageLoader :visible="pageLoader" />
	<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
		<!-- Sidebar -->
		<Sidebar v-if="!$route.meta.hideSidebar" v-model:isOpen="isSidebarOpen" />

		<div :class="headerWrapperClass">
			<!-- Header -->
			<header v-if="!$route.meta.hideHeader"
				class="sticky top-0 z-40 flex h-16 flex-shrink-0 bg-white dark:bg-gray-800 shadow overflow-visible">
				<!-- Mobile Menu Button -->
				<button type="button"
					class="px-4 text-gray-500 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden"
					@click="toggleSidebar">
					<span class="sr-only">Open sidebar</span>
					<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
						stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							d="M4 6h16M4 12h16M4 18h16" />
					</svg>
				</button>

				<!-- Header Content -->
				<div class="flex flex-1 justify-between px-4" style="overflow: visible;">
					<div class="flex flex-1">
						<!-- Add your header content here -->
					</div>
					<!-- User Profile Section -->
					<div class="ml-auto mr-2 sm:mr-4 flex items-center md:ml-6 space-x-2 sm:space-x-4"
						style="overflow: visible;">
						<!-- Notification Icon -->
						<div class="relative" style="overflow: visible;">
							<button type="button"
								class="relative p-2 text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-full"
								@click="toggleNotifications">
								<span class="sr-only">View notifications</span>
								<BellRing class="h-6 w-6" />
								<span v-if="notificationCount > 0"
									class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 text-xs text-white flex items-center justify-center">
									{{ notificationCount > 9 ? '9+' : notificationCount }}
								</span>
							</button>

							<div v-if="isNotificationsOpen"
								class="fixed sm:absolute inset-x-4 sm:inset-x-auto sm:right-0 z-50 sm:mt-2 w-auto sm:w-96 origin-top sm:origin-top-right rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none max-h-[80vh] sm:max-h-[70vh] overflow-y-auto"
								role="menu">
								<div
									class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
									<h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Notifications</h3>
									<span v-if="notificationCount > 0"
										class="text-xs text-gray-500 dark:text-gray-400">{{ notificationCount }}
										unread</span>
								</div>
								<div class="max-h-96 overflow-y-auto">
									<div v-if="headerNotifLoading" class="px-4 py-6 text-center">
										<div
											class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-500 mx-auto">
										</div>
										<p class="text-xs text-gray-400 mt-2">Loading...</p>
									</div>
									<template v-else>
										<div v-for="notification in headerNotifications" :key="notification.id"
											@click="handleNotificationClick(notification)"
											class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
											:class="!notification.is_read ? 'bg-indigo-50/40 dark:bg-indigo-900/10' : ''">
											<div class="flex items-start space-x-3">
												<div class="flex-shrink-0">
													<div class="h-8 w-8 rounded-full flex items-center justify-center"
														:class="getNotifIconBg(notification.type)">
														<component :is="getNotifIcon(notification.type)" class="h-4 w-4"
															:class="getNotifIconColor(notification.type)" />
													</div>
												</div>
												<div class="flex-1 min-w-0">
													<div class="flex items-center gap-1.5">
														<p
															class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
															{{ notification.title }}</p>
														<span v-if="!notification.is_read"
															class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 flex-shrink-0"></span>
													</div>
													<p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{
														notification.clinic || notification.message }}</p>
													<p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{
														formatNotificationTime(notification.created_at) }}</p>
												</div>
												<span v-if="notification.status"
													class="flex-shrink-0 text-[11px] px-2.5 py-0.5 rounded-full font-medium"
													:class="getNotifStatusClass(notification.status)">
													{{ notification.status }}
												</span>
											</div>
										</div>
										<div v-if="headerNotifications.length === 0" class="px-4 py-8 text-center">
											<BellRing class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
											<p class="text-sm text-gray-500 dark:text-gray-400 mt-2">No new
												notifications</p>
										</div>
									</template>
								</div>
								<div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
									<button @click="viewAllNotifications"
										class="w-full text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 text-center py-1 font-medium">
										View all notifications
									</button>
								</div>
							</div>
						</div>

						<div class="relative ml-3">
							<!-- Profile Button -->
							<div>
								<button type="button"
									class="flex max-w-xs items-center rounded-full bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
									@click="toggleProfile">
									<span class="sr-only">Open user menu</span>
									<div
										class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white">
										{{ userInitials }}
									</div>
								</button>
							</div>

							<!-- Profile Dropdown Menu -->
							<div v-if="isProfileOpen"
								class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
								role="menu">
								<!-- User Info -->
								<div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
									<p class="font-medium truncate" :title="fullUserName">
										{{ fullUserName }}
									</p>
									<span class="text-xs">({{ getUserRoleLabel(currentUser?.user_role) }})</span>
									<p class="text-gray-500 dark:text-gray-400 truncate" :title="currentUser?.email">{{
										currentUser?.email }}</p>
								</div>
								<div class="border-t border-gray-100 dark:border-gray-700"></div>

								<!-- Settings Option -->
								<button type="button"
									class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700"
									@click="handleSettingsClick">
									<div class="flex items-center">
										<Settings class="mr-3 h-5 w-5 text-gray-400 flex-shrink-0" />
										<span class="truncate">Settings</span>
									</div>
								</button>

								<!-- Change Account Option -->
								<!-- <button
								type="button"
								class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700"
								@click="goToChangeAccount"
								>
									<div class="flex items-center">
										<RefreshCcw class="mr-3 h-5 w-5 text-gray-400 flex-shrink-0" />
										<span class="truncate">Change Account</span>
									</div>
								</button> -->

								<!-- Logout Button -->
								<button type="button"
									class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
									@click="handleLogout" :disabled="isLoading">
									<div class="flex items-center">
										<LogOut class="mr-3 h-5 w-5 text-gray-400 flex-shrink-0" />
										<span class="truncate">{{ isLoading ? 'Logging out...' : 'Sign out' }}</span>
									</div>
								</button>
							</div>
						</div>
					</div>
				</div>
			</header>

			<!-- Main Content -->
			<main class="py-6">
				<div class="mx-auto px-4 sm:px-6 lg:px-8">
					<div class="w-full bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<TriangleAlert class="h-6 w-6 text-yellow-600" />
							</div>
							<div class="ml-3">
								<p class="text-sm font-medium text-yellow-900">
									Development Notice: This system is currently under active development. Some features
									may be incomplete or unavailable.
								</p>
							</div>
						</div>
					</div>
					<router-view></router-view>
				</div>
			</main>
		</div>
		<!-- Notification Modal -->
		<NotificationModal :isOpen="isNotificationModalOpen" :notification="selectedNotification"
			@close="closeNotificationModal" />
	</div>
</template>

<script setup lang="ts">
// Imports
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useClickOutside } from '@/composables/ui/useClickOutside'
import { useUser } from '@/composables/auth/useUser'
import { useLogin } from '@/composables/auth/useLogin'
import { pageLoader } from '@/composables/ui/usePageLoader'
import { notificationService } from '@/services/api'
import Sidebar from './Sidebar.vue'
import PageLoader from '@/components/ui/PageLoader.vue'
import NotificationModal from '@/components/notifications/NotificationModal.vue'
import {
	RefreshCcw, Settings, BellRing, LogOut,
	TriangleAlert, TriangleAlertIcon,
	Box, FileUp, ShieldCheck, Receipt, Clock, DollarSign
} from 'lucide-vue-next';

const route = useRoute()

const headerWrapperClass = computed(() => {
	return route.meta.disableHeaderPadding || route.meta.hideSidebar
		? ''
		: 'lg:pl-64'
})

// Store Instances
const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const { userInitials } = useUser()
const { handleLogout } = useLogin()

// State
const isProfileOpen = ref(false)
const isSidebarOpen = ref(false)
const isNotificationsOpen = ref(false)
const notificationCount = ref(0)
const isNotificationModalOpen = ref(false)
const selectedNotification = ref<any>(null)
const headerNotifications = ref<any[]>([])
const headerNotifLoading = ref(false)
let notifPollInterval: ReturnType<typeof setInterval> | null = null

// Fetch real notifications for the header bell
async function fetchHeaderNotifications() {
	try {
		const today = new Date().toISOString().slice(0, 10)
		const params: any = { per_page: 5, page: 1, start_date: today, end_date: today }

		if ((authStore.user as any)?.user_role === 2 || (authStore.user as any)?.user_role === 3) {
			if ((authStore.user as any)?.clinic_id) {
				params.clinic_id = (authStore.user as any).clinic_id
			}
		}

		const res = await notificationService.getNotifications(params)
		const items = res?.data?.data || []
		headerNotifications.value = items
		notificationCount.value = res?.data?.meta?.unread_count ?? items.filter((n: any) => !n.is_read).length
	} catch (err) {
		console.error('Failed to fetch header notifications:', err)
	}
}

// Notification type → icon mapping
const getNotifIcon = (type: string) => {
	const map: Record<string, any> = { order: Box, usage: FileUp, ivr: ShieldCheck, invoice: Receipt, return: Box, payment: DollarSign }
	return map[type] || Clock
}
const getNotifIconBg = (type: string) => {
	const map: Record<string, string> = { order: 'bg-violet-100 dark:bg-violet-900/30', usage: 'bg-yellow-100 dark:bg-yellow-900/30', ivr: 'bg-blue-100 dark:bg-blue-900/30', invoice: 'bg-emerald-100 dark:bg-emerald-900/30', return: 'bg-orange-100 dark:bg-orange-900/30' }
	return map[type] || 'bg-gray-100 dark:bg-gray-800'
}
const getNotifIconColor = (type: string) => {
	const map: Record<string, string> = { order: 'text-violet-600', usage: 'text-yellow-600', ivr: 'text-blue-600', invoice: 'text-emerald-600', return: 'text-orange-600' }
	return map[type] || 'text-gray-600'
}
const getNotifStatusClass = (status: string) => {
	const s = status?.toLowerCase()
	const map: Record<string, string> = {
		delivered: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
		shipped: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
		submitted: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
		acknowledged: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
		pending: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
		'pending review': 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
		cancelled: 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
		active: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
		eligible: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
		'not eligible': 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
		paid: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
		overdue: 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
		partial: 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
		returned: 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
		expired: 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
		used: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
		unused: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
		disposed: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
	}
	return map[s] || 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
}

function getUserRoleLabel(role?: number): string {
	switch (role) {
		case 0: return "Admin";
		case 1: return "Office Staff";
		case 2: return "Clinic";
		case 3: return "Clinician";
		case 4: return "Manufacturer";
		case 5: return "Biller";
		default: return "Unknown Role";
	}
}

// Computed Properties
const currentUser = computed(() => authStore.currentUser)
const isLoading = computed(() => authStore.isLoading)
const isDarkMode = computed(() => themeStore.isDarkMode)

// Full name with conditional middle initial
const fullUserName = computed(() => {
	if (!currentUser.value) return ''
	
	const firstName = currentUser.value.first_name || ''
	const middleName = currentUser.value.middle_name
	const lastName = currentUser.value.last_name || ''
	
	// Build name parts
	const nameParts: string[] = []
	
	if (firstName) nameParts.push(firstName.trim())
	
	// Only add middle initial if middle name exists and is not empty
	if (middleName && typeof middleName === 'string' && middleName.trim()) {
		const middleInitial = middleName.trim().charAt(0).toUpperCase()
		nameParts.push(middleInitial + '.')
	}
	
	if (lastName) nameParts.push(lastName.trim())
	
	return nameParts.join(' ')
})

// Methods
const toggleProfile = () => {
	isProfileOpen.value = !isProfileOpen.value
}

const toggleSidebar = () => {
	isSidebarOpen.value = !isSidebarOpen.value
}

const toggleTheme = () => {
	themeStore.toggleTheme()
}

const toggleNotifications = async () => {
	isNotificationsOpen.value = !isNotificationsOpen.value
	if (isNotificationsOpen.value) {
		headerNotifLoading.value = true
		await fetchHeaderNotifications()
		headerNotifLoading.value = false
	}
}

const handleNotificationClick = async (notification: any) => {
	// Mark as read if unread
	if (!notification.is_read) {
		try {
			await notificationService.markAsRead(notification.id)
			notification.is_read = true
			notificationCount.value = Math.max(0, notificationCount.value - 1)
		} catch (err) {
			console.error('Failed to mark notification as read:', err)
		}
	}

	// Navigate to the relevant management page
	const role = authStore.user?.user_role ?? 0
	const prefixMap: Record<number, string> = { 0: 'admin', 1: 'office-staff', 2: 'clinic', 3: 'clinician', 4: 'manufacturer', 5: 'biller' }
	const prefix = prefixMap[role] || 'admin'
	const routeMap: Record<string, string> = {
		order: `/${prefix}/order-management`,
		usage: `/${prefix}/inventory`,
		ivr: `/${prefix}/ivr-management`,
		invoice: `/${prefix}/invoice-management`,
		return: '/admin/returns'
	}

	const target = routeMap[notification.type]
	if (target) router.push(target)
	isNotificationsOpen.value = false
}

const viewAllNotifications = () => {
	router.push('/notifications')
	isNotificationsOpen.value = false
}

const formatNotificationTime = (timestamp: string) => {
	const now = new Date()
	const notificationTime = new Date(timestamp)
	const diffInMinutes = Math.floor((now.getTime() - notificationTime.getTime()) / (1000 * 60))

	if (diffInMinutes < 1) return 'Just now'
	if (diffInMinutes < 60) return `${diffInMinutes}m ago`

	const diffInHours = Math.floor(diffInMinutes / 60)
	if (diffInHours < 24) return `${diffInHours}h ago`

	const diffInDays = Math.floor(diffInHours / 24)
	if (diffInDays < 7) return `${diffInDays}d ago`

	return notificationTime.toLocaleDateString()
}

const handleProfileClick = () => {
	// Close dropdown and navigate to profile
	isProfileOpen.value = false
	router.push('/profile')
}

const handleSettingsClick = () => {
	isProfileOpen.value = false
	router.push('/settings')
}

const closeNotificationModal = () => {
	isNotificationModalOpen.value = false
	selectedNotification.value = null
}

function goToChangeAccount() {
	router.push('/change-account')
}

// Lifecycle Hooks
const { handleClickOutside: handleProfileClickOutside } = useClickOutside(isProfileOpen)
const { handleClickOutside: handleNotificationsClickOutside } = useClickOutside(isNotificationsOpen)

onMounted(() => {
	document.addEventListener('click', handleProfileClickOutside)
	document.addEventListener('click', handleNotificationsClickOutside)

	// Fetch initial notification count
	fetchHeaderNotifications()

	// Poll every 60 seconds for new notifications
	notifPollInterval = setInterval(fetchHeaderNotifications, 60000)
})

onUnmounted(() => {
	document.removeEventListener('click', handleProfileClickOutside)
	document.removeEventListener('click', handleNotificationsClickOutside)
	if (notifPollInterval) clearInterval(notifPollInterval)
})
</script>