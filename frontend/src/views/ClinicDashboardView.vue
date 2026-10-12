<template>
	<div class="space-y-6">
		<!-- Welcome Banner -->
		<WelcomeBanner />

		<!-- Stats Grid -->
		<StatsGrid :clinic-stats="clinicStats" :loading="loadingStats" />

		<!-- Recent Orders & System Alerts -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
			<!-- Recent Orders -->
			<RecentOrders :orders="recentOrders" :loading="loadingOrders" />

			<!-- System Alerts -->
			<SystemAlerts :alerts="alerts" :loading="loadingAlerts" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { clinicDashboardService } from '@/services/api'

// Components
import WelcomeBanner from '@/components/dashboards/shared/WelcomeBanner.vue'
import StatsGrid from '@/components/dashboards/clinic-clinician/StatsGrid.vue'
import RecentOrders from '@/components/dashboards/clinic-clinician/RecentOrders.vue'
import SystemAlerts from '@/components/dashboards/shared/SystemAlerts.vue'

// ── Data ────────────────────────────────────────────────────────
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
