<template>
  <div class="space-y-6">
    <!-- Welcome Banner -->
    <WelcomeBanner />

    <!-- Stats Grid -->
    <StatsGrid :metrics="metrics" :loading="loadingMetrics" />

    <!-- Recent Activity & Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity -->
      <RecentActivity :activities="recentActivity" :loading="loadingActivity" />

      <!-- System Alerts -->
      <SystemAlerts :alerts="alerts" :loading="loadingAlerts" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { dashboardService } from '@/services/api'

// Components
import WelcomeBanner from '@/components/dashboards/shared/WelcomeBanner.vue'
import StatsGrid from '@/components/dashboards/admin-staff/StatsGrid.vue'
import RecentActivity from '@/components/dashboards/admin-staff/RecentActivity.vue'
import SystemAlerts from '@/components/dashboards/shared/SystemAlerts.vue'

// ── Data ────────────────────────────────────────────────────────
const metrics = ref<any>({
  brands: { total: 0, active: 0, new: 0 },
  manufacturers: { total: 0, active: 0, new: 0 },
  clinics: { total: 0, active: 0, new: 0 },
  orders: { total: 0, new: 0 }
})
const recentActivity = ref<any[]>([])
const alerts = ref<any[]>([])

const loadingMetrics = ref(true)
const loadingActivity = ref(true)
const loadingAlerts = ref(true)

// ── Fetching ─────────────────────────────────────────────────────
onMounted(async () => {
  // 1. Metrics
  try {
    const res = await dashboardService.getMetrics()
    metrics.value = res?.data?.data || metrics.value
  } catch (err) {
    console.error('Failed to load dashboard metrics', err)
  } finally {
    loadingMetrics.value = false
  }

  // 2. Recent Activity
  try {
    const res = await dashboardService.getRecentActivity()
    recentActivity.value = res?.data?.data || []
  } catch (err) {
    console.error('Failed to load recent activity', err)
    recentActivity.value = []
  } finally {
    loadingActivity.value = false
  }

  // 3. System Alerts
  try {
    const res = await dashboardService.getSystemAlerts()
    alerts.value = res?.data?.data || []
  } catch (err) {
    console.error('Failed to load system alerts', err)
    alerts.value = []
  } finally {
    loadingAlerts.value = false
  }
})
</script>
