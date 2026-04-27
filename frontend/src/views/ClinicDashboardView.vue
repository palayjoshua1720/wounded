<template>
	<div class="space-y-6">
		<!-- Welcome Banner -->
		<div class="banner-wrapper text-white p-4 rounded-2xl shadow-md flex items-center justify-between">
			<!-- Decorative ghost circles -->
			<span class="banner-circle" style="width:160px;height:160px;top:-60px;right:60px;"></span>
			<span class="banner-circle" style="width:80px;height:80px;bottom:-30px;right:200px;"></span>
			<span class="banner-circle" style="width:40px;height:40px;top:10px;right:240px;"></span>

			<div class="flex items-center gap-3.5 relative z-10">
				<div
					class="w-20 h-20 rounded-full bg-white/20 border border-white/25 flex items-center justify-center flex-shrink-0">
					<span class="text-lg font-bold text-white">{{ userInitials }}</span>
				</div>
				<div>
					<h2 class="text-xl font-bold">
						Welcome back, {{ currentUser?.first_name }} {{ currentUser?.last_name }}
					</h2>
					<p class="text-sm opacity-90">Here's a quick overview of your clinic today.</p>
				</div>
			</div>
			<p class="text-xs opacity-70 hidden sm:block relative z-10">{{ todayLabel }}</p>
		</div>

		<!-- Stats Grid -->
		<div v-if="loadingStats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
			<div v-for="n in 4" :key="n"
				class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 animate-pulse">
				<div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3 mb-3"></div>
				<div class="h-7 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
			</div>
		</div>

		<div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
			<div v-for="stat in statsCards" :key="stat.key"
				class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
				<!-- Hover gradient overlay -->
				<div class="absolute inset-0 bg-gradient-to-br 
        from-blue-600/10 to-transparent 
        dark:from-blue-600/10 to-transparent 
        opacity-0 group-hover:opacity-100 transition-opacity" />

				<!-- Icon + number inline, pill floats top-right -->
				<div class="flex items-start justify-between mb-2.5">
					<div class="flex items-center gap-3">
						<div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform"
							:class="stat.iconBg">
							<component :is="stat.icon" class="w-[18px] h-[18px]" :class="stat.iconColor" />
						</div>
						<div>
							<p class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
								{{ stat.value }}
							</p>
							<p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">{{ stat.label }}</p>
						</div>
					</div>
					<span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0 ml-2"
						:class="stat.trendClass">
						{{ stat.trendLabel }}
					</span>
				</div>
				<!-- Footer with divider -->
				<div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/60">
					<p class="text-xs text-gray-400 dark:text-gray-500">
						{{ stat.recent }}
					</p>
				</div>
			</div>
		</div>

		<!-- Recent Orders & System Alerts -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

			<!-- Recent Orders -->
			<div
				class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
				<div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
					<router-link to="/orders"
						class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors flex items-center gap-1">
						View All
						<ChevronRight class="w-5 h-5" />
					</router-link>
				</div>

				<!-- Loading -->
				<div v-if="loadingOrders" class="divide-y divide-gray-100 dark:divide-gray-700">
					<div v-for="n in 4" :key="n" class="px-4 py-3.5 animate-pulse flex items-center gap-3">
						<div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
						<div class="flex-1 space-y-2">
							<div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
							<div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
						</div>
					</div>
				</div>

				<!-- Empty -->
				<div v-else-if="recentOrders.length === 0" class="px-4 py-10 text-center">
					<ShoppingCart class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" />
					<p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent orders</p>
					<p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">New orders will appear here.</p>
				</div>

				<!-- Items -->
				<div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
					<div v-for="order in recentOrders" :key="order.id"
						class="flex items-start gap-3 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors border-l-2"
						:class="getOrderBorder(order.status)">

						<!-- Icon -->
						<div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
							:class="getOrderIconBg(order.status)">
							<component :is="getOrderIcon(order.status)" class="w-5 h-5"
								:class="getOrderIconStyle(order.status)" />
						</div>

						<!-- Body -->
						<div class="flex-1 min-w-0">
							<p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">
								{{ order.product }}
							</p>
							<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
								Patient: {{ order.patient_name || '—' }}
							</p>
							<div v-if="order.manufacturer !== '—' || order.brands !== '—'"
								class="mt-1.5 flex flex-wrap items-center gap-1.5">
								<span v-if="order.manufacturer !== '—'"
									class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
									<Factory class="w-3 h-3" />{{ order.manufacturer }}
								</span>
								<span v-if="order.brands !== '—'"
									class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
									<Package class="w-3 h-3" />{{ order.brands }}
								</span>
							</div>
						</div>

						<!-- Right: badge + time -->
						<div class="flex flex-col items-end gap-1.5 flex-shrink-0">
							<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium leading-5"
								:class="getStatusBadge(order.status)">
								{{ order.status }}
							</span>
							<p class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
								{{ timeAgo(order.ordered_at) }}
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- System Alerts -->
			<div
				class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
				<div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Alerts</h3>
					<span v-if="urgentCount > 0"
						class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
						{{ urgentCount }} urgent
					</span>
				</div>

				<!-- Loading -->
				<div v-if="loadingAlerts" class="divide-y divide-gray-100 dark:divide-gray-700">
					<div v-for="n in 3" :key="n" class="px-4 py-3.5 animate-pulse flex items-start gap-3">
						<div class="w-2 h-2 rounded-full bg-gray-200 dark:bg-gray-700 mt-1.5 flex-shrink-0"></div>
						<div class="flex-1 space-y-2">
							<div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
							<div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
						</div>
					</div>
				</div>

				<!-- Empty -->
				<div v-else-if="alerts.length === 0" class="px-4 py-10 text-center">
					<CheckCircle class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" />
					<p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">All clear</p>
					<p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">No active alerts at the moment.</p>
				</div>

				<!-- Alert items -->
				<div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
					<div v-for="(alert, index) in alerts" :key="index"
						class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

						<!-- Dot indicator -->
						<div class="w-2 h-2 rounded-full flex-shrink-0 mt-1.5" :class="getAlertDot(alert.type)"></div>

						<!-- Content -->
						<div class="flex-1 min-w-0">
							<p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">
								{{ alert.message }}
							</p>
							<p class="text-xs mt-0.5 font-medium" :class="getAlertSubColor(alert.type)">
								{{ alert.detail || alert.time || 'Just now' }}
							</p>
						</div>

						<span v-if="alert.priority === 'high'" class="flex-shrink-0 self-center text-xs font-medium"
							:class="getAlertActionClass(alert.type)">
							Action needed
						</span>
					</div>
				</div>
			</div>

		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
	ShoppingCart, Clock, CircleCheck, Factory, Package, Truck, CheckCircle,
	Box, ChevronRight
} from 'lucide-vue-next'
import { useUser } from '@/composables/auth/useUser'
import { useAuthStore } from '@/stores/auth'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { clinicDashboardService } from '@/services/api'

dayjs.extend(relativeTime)

const authStore = useAuthStore()
const { userInitials } = useUser()
const currentUser = computed(() => authStore.currentUser)

// ── Today label ─────────────────────────────────────────────────
const todayLabel = computed(() =>
	dayjs().format('ddd, MMM D, YYYY')
)

const loadingStats = ref(true)
const loadingOrders = ref(true)
const loadingAlerts = ref(true)

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
const alerts = ref<any[]>([])

// ── Computed Stats Cards ────────────────────────────────────────
const statsCards = computed(() => {
	const mkTrend = (label: string, cls: string) => ({ trendLabel: label, trendClass: cls })

	return [
		{
			key: 'total-orders',
			label: 'Total Orders',
			value: clinicStats.value.orders?.total?.toLocaleString() ?? '0',
			recent: `${clinicStats.value.orders?.this_month ?? 0} new orders this month`,
			...mkTrend(
				clinicStats.value.orders?.this_month > 0 ? `+${clinicStats.value.orders.this_month} this mo.` : 'No change',
				clinicStats.value.orders?.this_month > 0
					? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400'
					: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
			),
			icon: ShoppingCart,
			iconColor: 'text-green-600 dark:text-green-400',
			iconBg: 'bg-green-50 dark:bg-green-900/30',
		},
		{
			key: 'pending-orders',
			label: 'Pending Orders',
			value: clinicStats.value.orders?.pending?.toString() ?? '0',
			recent: 'Submitted & Acknowledged',
			trendLabel: 'Awaiting',
			trendClass: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
			icon: Clock,
			iconColor: 'text-yellow-600 dark:text-yellow-400',
			iconBg: 'bg-yellow-50 dark:bg-yellow-900/30',
		},
		{
			key: 'shipped-orders',
			label: 'Shipped Orders',
			value: clinicStats.value.orders?.shipped?.toString() ?? '0',
			recent: 'In transit',
			trendLabel: 'In transit',
			trendClass: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
			icon: Truck,
			iconColor: 'text-blue-600 dark:text-blue-400',
			iconBg: 'bg-blue-50 dark:bg-blue-900/30',
		},
		{
			key: 'delivered-orders',
			label: 'Delivered Orders',
			value: clinicStats.value.orders?.delivered?.toString() ?? '0',
			recent: 'Ready for usage logging',
			trendLabel: 'Delivered',
			trendClass: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
			icon: CircleCheck,
			iconColor: 'text-emerald-600 dark:text-emerald-400',
			iconBg: 'bg-emerald-50 dark:bg-emerald-900/30',
		},
	]
})

// ── Alerts helpers ───────────────────────────────────────────────
const urgentCount = computed(() =>
	alerts.value.filter(a => a.priority === 'high').length
)

const getAlertDot = (type: string) => ({
	error: 'bg-red-500',
	warning: 'bg-amber-500',
	info: 'bg-blue-500',
	success: 'bg-green-500',
}[type] ?? 'bg-gray-400')

const getAlertSubColor = (type: string) => ({
	error: 'text-red-600 dark:text-red-400',
	warning: 'text-amber-700 dark:text-amber-400',
	info: 'text-blue-600 dark:text-blue-400',
	success: 'text-green-600 dark:text-green-400',
}[type] ?? 'text-gray-500 dark:text-gray-400')

const getAlertActionClass = (type: string) => ({
	error: 'text-red-600 dark:text-red-400',
	warning: 'text-amber-700 dark:text-amber-400',
	info: 'text-blue-600 dark:text-blue-400',
}[type] ?? 'text-gray-500 dark:text-gray-400')

// ── Order helpers ────────────────────────────────────────────────
const getOrderBorder = (status: string) => ({
	delivered: 'border-l-green-500',
	shipped: 'border-l-blue-500',
	submitted: 'border-l-yellow-500',
	acknowledged: 'border-l-indigo-500',
	pending: 'border-l-yellow-500',
	cancelled: 'border-l-red-500',
}[status?.toLowerCase()] ?? 'border-l-transparent')

const getOrderIcon = (status: string) => ({
	delivered: CircleCheck,
	shipped: Truck,
	pending: Clock,
	submitted: Clock,
	acknowledged: Clock,
	cancelled: Box,
}[status?.toLowerCase()] ?? Box)

const getOrderIconStyle = (status: string) => ({
	delivered: 'text-green-600',
	shipped: 'text-blue-600',
	pending: 'text-yellow-600',
	submitted: 'text-yellow-600',
	acknowledged: 'text-indigo-600',
	cancelled: 'text-red-600',
}[status?.toLowerCase()] ?? 'text-gray-500')

const getOrderIconBg = (status: string) => ({
	delivered: 'bg-green-100 dark:bg-green-900/30',
	shipped: 'bg-blue-100 dark:bg-blue-900/30',
	pending: 'bg-yellow-100 dark:bg-yellow-900/30',
	submitted: 'bg-yellow-100 dark:bg-yellow-900/30',
	acknowledged: 'bg-indigo-100 dark:bg-indigo-900/30',
	cancelled: 'bg-red-100 dark:bg-red-900/30',
}[status?.toLowerCase()] ?? 'bg-gray-100 dark:bg-gray-800')

const getStatusBadge = (status: string) => {
	const map: Record<string, string> = {
		delivered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
		shipped: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
		submitted: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
		acknowledged: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
		pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
		cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
	}
	return map[status?.toLowerCase()] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
}

const timeAgo = (value?: string | null) => {
	if (!value) return '—'
	return dayjs(value).fromNow()
}

// ── Fetch Data ──────────────────────────────────────────────────
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
	} catch (err) {
		console.error('Dashboard fetch failed:', err)
	} finally {
		loadingStats.value = false
		loadingOrders.value = false
	}

	// 2. Separate alerts call
	try {
		const alertsRes = await clinicDashboardService.getSystemAlerts()
		alerts.value = alertsRes?.data?.data || []
	} catch (err) {
		console.error('Failed to load system alerts', err)
		alerts.value = []
	} finally {
		loadingAlerts.value = false
	}
})
</script>

<style scoped>
.banner-wrapper {
	background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
	position: relative;
	overflow: hidden;
}

.banner-circle {
	position: absolute;
	border-radius: 50%;
	border: 1px solid rgba(255, 255, 255, 0.12);
	pointer-events: none;
}
</style>
