<template>
  <div class="space-y-6">
    <!-- Welcome Banner -->
    <div
      class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-4 rounded-2xl shadow-md flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <div class="w-20 h-20 rounded-full overflow-hidden shadow-md flex items-center justify-center bg-indigo-700">
          <span class="text-lg font-bold text-white">{{ userInitials }}</span>
        </div>
        <div>
          <h2 class="text-xl font-bold">
            Welcome back, {{ currentUser?.first_name }} {{ currentUser?.last_name }}
          </h2>
          <p class="text-sm opacity-90">Here's a quick overview of the system today.</p>
        </div>
      </div>
    </div>

    <!-- Stats Grid – Updated to match Stats Panel style -->
    <div v-if="loadingMetrics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
      <div v-for="n in 4" :key="n"
        class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 animate-pulse">
        <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
        <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
      <div v-for="stat in statsCards" :key="stat.key"
        class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
        <!-- Hover gradient overlay -->
        <div
          class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent dark:from-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />

        <div class="flex items-center gap-4">
          <!-- Icon container -->
          <div
            class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform"
            :class="stat.iconBg">
            <component :is="stat.icon" class="w-7 h-7" :class="stat.iconColor" />
          </div>

          <!-- Text content -->
          <div>
            <p class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
              {{ stat.value }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
              {{ stat.label }}
            </p>
          </div>
        </div>

        <!-- New this month line -->
        <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
          {{ stat.newThisMonth }} new {{ stat.key }} this month
        </div>
      </div>
    </div>

    <!-- Recent Activity & Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity -->
      <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
          <router-link to="/notifications"
            class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors flex items-center gap-1">
            View All
            <ChevronRight
              class="w-4 h-4 stroke-current fill-none stroke-[2] [stroke-linecap:round] [stroke-linejoin:round]" />
          </router-link>
        </div>

        <!-- Loading state -->
        <div v-if="loadingActivity" class="divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="n in 5" :key="n" class="px-6 py-4 animate-pulse flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
            <div class="flex-1 space-y-3">
              <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
            <div class="w-20 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="recentActivity.length === 0" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
          <ClockIcon class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" aria-hidden="true" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent activity</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            New system events will appear here.
          </p>
        </div>

        <!-- Activity items -->
        <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="activity in recentActivity" :key="activity.id"
            class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group"
            :class="getActivityBorder(activity.type)">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                :class="getIconBg(activity.type)">
                <component :is="getActivityIcon(activity.type)" class="w-6 h-6" :class="getIconStyle(activity.type)" />
              </div>
              <div class="flex-1 min-w-0">
                <p
                  class="text-base font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                  {{ activity.action }}
                </p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                  {{ activity.clinic }}
                </p>
              </div>
              <div class="text-right text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                {{ timeAgo(activity.timestamp) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- System Alerts -->
      <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Alerts</h3>
        </div>

        <div v-if="loadingAlerts" class="divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="n in 5" :key="n" class="px-6 py-4 animate-pulse flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
            <div class="flex-1 space-y-3">
              <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
            <div class="w-20 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
          </div>
        </div>

        <div v-else-if="alerts.length === 0" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
          <CheckCircleIcon class="mx-auto h-12 w-12 text-green-500" aria-hidden="true" />
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
                {{ alert.detail || alert.time || 'Just now' }}
              </p>
            </div>

            <div v-if="alert.priority === 'high'" class="text-xs font-medium text-red-600 dark:text-red-400">
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
import { ShoppingCart, Building2, Factory, Package, Clock, CheckCircle, AlertTriangle, XCircle, Info, Box, DollarSign, FileUp, ShieldCheck, Receipt, ChevronRight } from 'lucide-vue-next'
import { useUser } from '@/composables/auth/useUser'
import { useAuthStore } from '@/stores/auth'
import { dashboardService } from '@/services/api'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

// ── Auth & User ────────────────────────────────────────────────
const authStore = useAuthStore()
const { userInitials } = useUser()
const currentUser = computed(() => authStore.currentUser)

// ── Data ───────────────────────────────────────────────────────
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

// ── Time formatter ─────────────────────────────────────────────
const timeAgo = (str: string | null | undefined) => {
  if (!str) return 'Unknown time'
  const date = dayjs(str)
  return date.isValid() ? date.fromNow() : 'Unknown time'
}

// ── Stats Cards (updated visual style) ─────────────────────────
const statsCards = computed(() => [
  {
    key: 'orders',
    label: 'Total Orders',
    value: Number(metrics.value?.orders?.total ?? 0).toLocaleString(),
    newThisMonth: metrics.value?.orders?.new ?? 0,
    icon: ShoppingCart,
    iconColor: 'text-green-600 dark:text-green-400',
    iconBg: 'bg-green-50 dark:bg-green-900/30'
  },
  {
    key: 'clinics',
    label: 'Active Clinics',
    value: Number(metrics.value?.clinics?.active ?? 0).toLocaleString(),
    newThisMonth: metrics.value?.clinics?.new ?? 0,
    icon: Building2,
    iconColor: 'text-blue-600 dark:text-blue-400',
    iconBg: 'bg-blue-50 dark:bg-blue-900/30'
  },
  {
    key: 'manufacturers',
    label: 'Active Manufacturers',
    value: Number(metrics.value?.manufacturers?.active ?? 0).toLocaleString(),
    newThisMonth: metrics.value?.manufacturers?.new ?? 0,
    icon: Factory,
    iconColor: 'text-orange-600 dark:text-orange-400',
    iconBg: 'bg-orange-50 dark:bg-orange-900/30'
  },
  {
    key: 'brands',
    label: 'Active Brands',
    value: Number(metrics.value?.brands?.active ?? 0).toLocaleString(),
    newThisMonth: metrics.value?.brands?.new ?? 0,
    icon: Package,
    iconColor: 'text-purple-600 dark:text-purple-400',
    iconBg: 'bg-purple-50 dark:bg-purple-900/30'
  }
])

// ── Fetching ───────────────────────────────────────────────────
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

// ── Activity helpers ──────────────────────────────
const getActivityIcon = (type: string) => {
  const map: Record<string, any> = {
    order: Box,
    usage: FileUp,
    payment: DollarSign,
    ivr: ShieldCheck,
    return: Box,
    invoice: Receipt
  }
  return map[type] || Clock
}

const getIconStyle = (type: string) => {
  const styles: Record<string, string> = {
    order: 'text-violet-600',
    usage: 'text-yellow-600',
    payment: 'text-green-600',
    ivr: 'text-blue-600',
    return: 'text-orange-600',
    invoice: 'text-red-600'
  }
  return styles[type] || 'text-gray-600'
}

const getActivityBorder = (type: string) => {
  const map: Record<string, string> = {
    order: 'border-l-4 border-violet-500',
    usage: 'border-l-4 border-yellow-500',
    invoice: 'border-l-4 border-emerald-500',
    ivr: 'border-l-4 border-blue-500',
    return: 'border-l-4 border-orange-500'
  }
  return map[type] || ''
}

const getIconBg = (type: string) => {
  const map: Record<string, string> = {
    order: 'bg-violet-100 dark:bg-violet-900/30',
    usage: 'bg-yellow-100 dark:bg-yellow-900/30',
    invoice: 'bg-emerald-100 dark:bg-emerald-900/30',
    ivr: 'bg-blue-100 dark:bg-blue-900/30',
    return: 'bg-orange-100 dark:bg-orange-900/30'
  }
  return map[type] || 'bg-gray-100 dark:bg-gray-800'
}
</script>