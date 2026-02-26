<template>
	<div class="space-y-6">
		<!-- Welcome Banner -->
		<div
			class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-5 md:p-6 rounded-2xl shadow-md flex items-center justify-between">
			<div class="flex items-center space-x-4 md:space-x-5">
				<div
					class="w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden shadow-md flex items-center justify-center bg-indigo-700 flex-shrink-0">
					<span class="text-xl md:text-2xl font-bold">{{ userInitials }}</span>
				</div>
				<div>
					<h2 class="text-xl md:text-2xl font-bold">
						Welcome back, {{ currentUser?.first_name }} {{ currentUser?.last_name }}
					</h2>
					<p class="text-sm md:text-base opacity-90 mt-1">
						Here's a quick overview of your clinic today.
					</p>
				</div>
			</div>
		</div>

		<!-- Stats Grid -->
		<div v-if="loadingStats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
			<div v-for="n in 4" :key="n"
				class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 animate-pulse">
				<div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-3"></div>
				<div class="h-9 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
			</div>
		</div>
		<div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
			<div v-for="stat in statsCards" :key="stat.key"
				class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
				<!-- Subtle hover overlay -->
				<div
					class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent dark:from-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
				<div class="flex items-center gap-4">
					<div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform"
						:class="stat.iconBg">
						<component :is="stat.icon" class="w-7 h-7" :class="stat.iconColor" />
					</div>
					<div>
						<p class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
							{{ stat.value }}
						</p>
						<p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
							{{ stat.label }}
						</p>
					</div>
				</div>
				<div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
					{{ stat.recent }}
				</div>
			</div>
		</div>

		<!-- Recent Orders + System Alerts -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
			<!-- Recent Orders (unchanged) -->
			<div
				class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
				<div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
				</div>
				<div v-if="loadingOrders" class="divide-y divide-gray-100 dark:divide-gray-700">
					<div v-for="n in 4" :key="n" class="px-6 py-5 animate-pulse flex items-center gap-4">
						<div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
						<div class="flex-1 space-y-3">
							<div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
							<div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
						</div>
					</div>
				</div>
				<div v-else-if="recentOrders.length === 0"
					class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
					<ShoppingCart class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" />
					<h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-white">No recent orders</h3>
					<p class="mt-1 text-sm">New orders will appear here.</p>
				</div>
				<div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
					<div v-for="order in recentOrders" :key="order.id"
						class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group border-l-4"
						:class="getOrderBorder(order.status)">

						<div class="flex items-start justify-between gap-4">
							<div class="flex-1 min-w-0">
								<p
									class="text-base font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
									{{ order.product }}
								</p>
								<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
									Patient: {{ order.patient_name || '—' }}
								</p>

								<!-- Manufacturer & Brands (new) -->
								<div
									class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-x-4 gap-y-1">
									<span v-if="order.manufacturer !== '—'">
										<Factory class="inline w-3.5 h-3.5 mr-1" /><strong>Manufacturer:</strong> {{
											order.manufacturer }}
									</span>
									<span v-if="order.brands !== '—'">
										<Package class="inline w-3.5 h-3.5 mr-1" /><strong>Brand{{
											order.brands.includes(',') ? 's' : '' }}:</strong> {{
												order.brands }}
									</span>
								</div>
							</div>

							<div class="text-right shrink-0">
								<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium"
									:class="getStatusBadge(order.status)">
									{{ order.status }}
								</span>
								<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
									{{ timeAgo(order.ordered_at) }}
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- System Alerts (new, matches Admin Dashboard style) -->
			<div
				class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
				<div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Alerts</h3>
				</div>
				<div v-if="loadingAlerts" class="divide-y divide-gray-100 dark:divide-gray-700">
					<div v-for="n in 4" :key="n" class="px-6 py-5 animate-pulse flex items-center gap-4">
						<div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
						<div class="flex-1 space-y-3">
							<div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
							<div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
						</div>
					</div>
				</div>
				<div v-else-if="alerts.length === 0" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
					<CheckCircle class="mx-auto h-12 w-12 text-green-500" aria-hidden="true" />
					<h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">All clear</h3>
					<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
						No active alerts at the moment.
					</p>
				</div>
				<div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
					<div v-for="(alert, index) in alerts" :key="index"
						class="px-6 py-4 flex items-start gap-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
						:class="{
							'bg-red-50 dark:bg-red-900/20': alert.type === 'error',
							'bg-yellow-50 dark:bg-yellow-900/20': alert.type === 'warning',
							'bg-blue-50 dark:bg-blue-900/20': alert.type === 'info',
							'bg-green-50 dark:bg-green-900/20': alert.type === 'success'
						}">
						<div class="flex-shrink-0 mt-1">
							<AlertTriangle v-if="alert.type === 'warning'" class="w-6 h-6 text-yellow-600" />
							<XCircle v-else-if="alert.type === 'error'" class="w-6 h-6 text-red-600" />
							<Info v-else-if="alert.type === 'info'" class="w-6 h-6 text-blue-600" />
							<CheckCircle v-else-if="alert.type === 'success'" class="w-6 h-6 text-green-600" />
							<Clock v-else class="w-6 h-6 text-gray-600" />
						</div>
						<div class="flex-1">
							<p class="text-sm font-medium text-gray-900 dark:text-white">
								{{ alert.message }}
							</p>
							<p class="mt-1 text-xs font-medium"
								:class="alert.priority === 'high' ? 'text-red-700 dark:text-red-300' : 'text-gray-600 dark:text-gray-300'">
								{{ alert.detail || 'Just now' }}
							</p>
						</div>
						<div v-if="alert.priority === 'high'"
							class="text-xs font-medium text-red-600 dark:text-red-400 shrink-0">
							Action needed
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ShoppingCart, Clock, CircleCheck, Factory, Package, Truck, CheckCircle, AlertTriangle, XCircle, Info } from 'lucide-vue-next'
import { useUser } from '@/composables/auth/useUser'
import { useAuthStore } from '@/stores/auth'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { clinicDashboardService } from '@/services/api'

dayjs.extend(relativeTime)

const authStore = useAuthStore()
const { userInitials } = useUser()
const currentUser = computed(() => authStore.currentUser)

const loadingStats = ref(true)
const loadingOrders = ref(true)
const loadingAlerts = ref(true)  // renamed from loadingReminders

const clinicStats = ref({
	orders: {
		total: 0,
		this_month: 0,
		pending: 0,
		shipped: 0,
		delivered: 0,
	},
})

const recentOrders = ref<any[]>([])
const alerts = ref<any[]>([])  // renamed from usageReminders

// ── Computed Stats Cards (unchanged) ───────────────────────────────────────
const statsCards = computed(() => [
	{
		key: 'total-orders',
		label: 'Total Orders',
		value: clinicStats.value.orders?.total?.toLocaleString() ?? '0',
		recent: `${clinicStats.value.orders?.this_month ?? 0} this month`,
		icon: ShoppingCart,
		iconColor: 'text-green-600 dark:text-green-400',
		iconBg: 'bg-green-50 dark:bg-green-900/30'
	},
	{
		key: 'pending-orders',
		label: 'Pending Orders',
		value: clinicStats.value.orders?.pending?.toString() ?? '0',
		recent: 'Submitted & Acknowledged',
		icon: Clock,
		iconColor: 'text-yellow-600 dark:text-yellow-400',
		iconBg: 'bg-yellow-50 dark:bg-yellow-900/30'
	},
	{
		key: 'shipped-orders',
		label: 'Shipped Orders',
		value: clinicStats.value.orders?.shipped?.toString() ?? '0',
		recent: 'In transit',
		icon: Truck,
		iconColor: 'text-blue-600 dark:text-blue-400',
		iconBg: 'bg-blue-50 dark:bg-blue-900/30'
	},
	{
		key: 'delivered-orders',
		label: 'Delivered Orders',
		value: clinicStats.value.orders?.delivered?.toString() ?? '0',
		recent: 'Ready for usage logging',
		icon: CircleCheck,
		iconColor: 'text-emerald-600 dark:text-emerald-400',
		iconBg: 'bg-emerald-50 dark:bg-emerald-900/30'
	}
])

// ── Helpers ────────────────────────────────────────
const getOrderBorder = (status: string) => {
	const map: Record<string, string> = {
		delivered: 'border-green-500',
		shipped: 'border-blue-500',
		pending: 'border-yellow-500',
		cancelled: 'border-red-500'
	}
	return map[status?.toLowerCase()] || 'border-gray-300 dark:border-gray-600'
}

const getStatusBadge = (status: string) => {
	const map: Record<string, string> = {
		delivered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
		shipped: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
		pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
		cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
	}
	return map[status?.toLowerCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const timeAgo = (value?: string | null) => {
	if (!value) return '—'
	return dayjs(value).fromNow()
}

// ── Fetch Data ─────────────────────────────────────────────────
onMounted(async () => {
	try {
		// 1. Overview (orders + recent)
		const overviewRes = await clinicDashboardService.getOverview()
		const data = overviewRes.data?.data || {}

		clinicStats.value = {
			orders: {
				total: data.orders?.total ?? 0,
				this_month: data.orders?.this_month ?? 0,
				pending: data.orders?.pending ?? 0,
				shipped: data.orders?.shipped ?? 0,
				delivered: data.orders?.delivered ?? 0,
			},
		}

		recentOrders.value = data.recent_orders || []

		// 2. Separate alerts call
		const alertsRes = await clinicDashboardService.getSystemAlerts()
		alerts.value = alertsRes?.data?.data || []
	} catch (err) {
		console.error('Dashboard fetch failed:', err)
	} finally {
		loadingStats.value = false
		loadingOrders.value = false
		loadingAlerts.value = false
	}
})
</script>