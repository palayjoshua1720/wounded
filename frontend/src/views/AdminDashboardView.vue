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
          <p class="text-sm opacity-90">Here's a quick overview of the system today.</p>
        </div>
      </div>
      <p class="text-xs opacity-70 hidden sm:block relative z-10">{{ todayLabel }}</p>
    </div>

    <!-- Stats Grid -->
    <div v-if="loadingMetrics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
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
            <div
              class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform"
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
          <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0 ml-2" :class="stat.trendClass">
            {{ stat.trendLabel }}
          </span>
        </div>
        <!-- Footer with divider -->
        <div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/60">
          <p class="text-xs text-gray-400 dark:text-gray-500">
            {{ stat.newThisMonth }} new {{ stat.key }} this month
          </p>
        </div>
      </div>
    </div>

    <!-- Recent Activity & Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Recent Activity -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
          <router-link to="/notifications"
            class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors flex items-center gap-1">
            View All
            <ChevronRight class="w-5 h-5" />
          </router-link>
        </div>

        <!-- Loading -->
        <div v-if="loadingActivity" class="divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="n in 4" :key="n" class="px-4 py-3.5 animate-pulse flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
              <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <!-- Empty -->
        <div v-else-if="recentActivity.length === 0" class="px-4 py-10 text-center">
          <ClockIcon class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" />
          <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent activity</p>
          <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">New system events will appear here.</p>
        </div>

        <!-- Items -->
        <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
          <div v-for="activity in recentActivity" :key="activity.id"
            class="flex items-start gap-3 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors border-l-2"
            :class="getActivityBorder(activity.type)">

            <!-- Icon -->
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
              :class="getIconBg(activity.type)">
              <component :is="getActivityIcon(activity.type)" class="w-5 h-5" :class="getIconStyle(activity.type)" />
            </div>

            <!-- Body -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">
                {{ activity.action }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ activity.clinic }}<span v-if="activity.patient"> · {{ activity.patient }}</span>
              </p>
              <div
                v-if="activity.detail || activity.manufacturer || activity.brands || activity.amount || activity.serial"
                class="mt-1.5 flex flex-wrap items-center gap-1.5">
                <span v-if="activity.detail"
                  class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 px-1.5 py-0.5 rounded">
                  {{ activity.detail }}
                </span>
                <span v-if="activity.manufacturer"
                  class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                  <Factory class="w-3 h-3" />{{ activity.manufacturer }}
                </span>
                <span v-if="activity.brands"
                  class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                  <Package class="w-3 h-3" />{{ activity.brands }}
                </span>
                <span v-if="activity.amount"
                  class="inline-flex items-center gap-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                  <DollarSign class="w-3 h-3 text-gray-400" />{{ activity.amount }}
                </span>
                <span v-if="activity.serial" class="text-xs text-gray-500 dark:text-gray-400">
                  S/N: {{ activity.serial }}
                </span>
              </div>
            </div>

            <!-- Right: badge + time -->
            <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
              <span v-if="activity.status" class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium leading-5"
                :class="getStatusBadge(activity.status)">
                {{ activity.status }}
              </span>
              <p class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                {{ timeAgo(activity.timestamp) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- System Alerts -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
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
  ShoppingCart, Building2, Factory, Package, Clock, ClockIcon, Box, DollarSign, FileUp,
  ShieldCheck, Receipt, ChevronRight, CheckCircle
} from 'lucide-vue-next'
import { useUser } from '@/composables/auth/useUser'
import { useAuthStore } from '@/stores/auth'
import { dashboardService } from '@/services/api'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

// ── Auth & User ─────────────────────────────────────────────────
const authStore = useAuthStore()
const { userInitials } = useUser()
const currentUser = computed(() => authStore.currentUser)

// ── Today label ─────────────────────────────────────────────────
const todayLabel = computed(() =>
  dayjs().format('ddd, MMM D, YYYY')
)

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

// ── Time formatter ───────────────────────────────────────────────
const timeAgo = (str: string | null | undefined) => {
  if (!str) return 'Unknown time'
  const date = dayjs(str)
  return date.isValid() ? date.fromNow() : 'Unknown time'
}

// ── Stats Cards ──────────────────────────────────────────────────
const statsCards = computed(() => {
  const mkTrend = (n: number, key: string) =>
    n > 0
      ? { label: `+${n} this mo.`, cls: trendColorMap[key] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }
      : { label: 'No change', cls: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }

  const trendColorMap: Record<string, string> = {
    orders: 'bg-green-50  text-green-700  dark:bg-green-900/30  dark:text-green-400',
    clinics: 'bg-blue-50   text-blue-700   dark:bg-blue-900/30   dark:text-blue-400',
    manufacturers: 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    brands: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  }

  return [
    {
      key: 'orders',
      label: 'Total orders',
      value: Number(metrics.value?.orders?.total ?? 0).toLocaleString(),
      newThisMonth: metrics.value?.orders?.new ?? 0,
      trendLabel: mkTrend(metrics.value?.orders?.new ?? 0, 'orders').label,
      trendClass: mkTrend(metrics.value?.orders?.new ?? 0, 'orders').cls,
      icon: ShoppingCart,
      iconColor: 'text-green-600 dark:text-green-400',
      iconBg: 'bg-green-50 dark:bg-green-900/30',
    },
    {
      key: 'clinics',
      label: 'Active clinics',
      value: Number(metrics.value?.clinics?.active ?? 0).toLocaleString(),
      newThisMonth: metrics.value?.clinics?.new ?? 0,
      trendLabel: mkTrend(metrics.value?.clinics?.new ?? 0, 'clinics').label,
      trendClass: mkTrend(metrics.value?.clinics?.new ?? 0, 'clinics').cls,
      icon: Building2,
      iconColor: 'text-blue-600 dark:text-blue-400',
      iconBg: 'bg-blue-50 dark:bg-blue-900/30',
    },
    {
      key: 'manufacturers',
      label: 'Active manufacturers',
      value: Number(metrics.value?.manufacturers?.active ?? 0).toLocaleString(),
      newThisMonth: metrics.value?.manufacturers?.new ?? 0,
      trendLabel: mkTrend(metrics.value?.manufacturers?.new ?? 0, 'manufacturers').label,
      trendClass: mkTrend(metrics.value?.manufacturers?.new ?? 0, 'manufacturers').cls,
      icon: Factory,
      iconColor: 'text-orange-600 dark:text-orange-400',
      iconBg: 'bg-orange-50 dark:bg-orange-900/30',
    },
    {
      key: 'brands',
      label: 'Active brands',
      value: Number(metrics.value?.brands?.active ?? 0).toLocaleString(),
      newThisMonth: metrics.value?.brands?.new ?? 0,
      trendLabel: mkTrend(metrics.value?.brands?.new ?? 0, 'brands').label,
      trendClass: mkTrend(metrics.value?.brands?.new ?? 0, 'brands').cls,
      icon: Package,
      iconColor: 'text-purple-600 dark:text-purple-400',
      iconBg: 'bg-purple-50 dark:bg-purple-900/30',
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
  error: 'text-red-600   dark:text-red-400',
  warning: 'text-amber-700 dark:text-amber-400',
  info: 'text-blue-600  dark:text-blue-400',
  success: 'text-green-600 dark:text-green-400',
}[type] ?? 'text-gray-500 dark:text-gray-400')

const getAlertActionClass = (type: string) => ({
  error: 'text-red-600   dark:text-red-400',
  warning: 'text-amber-700 dark:text-amber-400',
  info: 'text-blue-600  dark:text-blue-400',
}[type] ?? 'text-gray-500 dark:text-gray-400')

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

// ── Activity helpers ─────────────────────────────────────────────
const getActivityIcon = (type: string) => ({
  order: Box,
  usage: FileUp,
  payment: DollarSign,
  ivr: ShieldCheck,
  return: Box,
  invoice: Receipt,
}[type] ?? Clock)

const getIconStyle = (type: string) => ({
  order: 'text-violet-600',
  usage: 'text-yellow-600',
  payment: 'text-green-600',
  ivr: 'text-indigo-600',
  return: 'text-orange-600',
  invoice: 'text-emerald-600',
}[type] ?? 'text-gray-500')

const getActivityBorder = (type: string) => ({
  order: 'border-l-violet-500',
  usage: 'border-l-yellow-500',
  invoice: 'border-l-emerald-500',
  ivr: 'border-l-indigo-500',
  return: 'border-l-orange-500',
  payment: 'border-l-green-500',
}[type] ?? 'border-l-transparent')

const getIconBg = (type: string) => ({
  order: 'bg-violet-100  dark:bg-violet-900/30',
  usage: 'bg-yellow-100  dark:bg-yellow-900/30',
  invoice: 'bg-emerald-100 dark:bg-emerald-900/30',
  ivr: 'bg-indigo-100  dark:bg-indigo-900/30',
  return: 'bg-orange-100  dark:bg-orange-900/30',
  payment: 'bg-green-100   dark:bg-green-900/30',
}[type] ?? 'bg-gray-100 dark:bg-gray-800')

const getStatusBadge = (status: string) => {
  const map: Record<string, string> = {
    delivered: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
    shipped: 'bg-blue-100   text-blue-800   dark:bg-blue-900/30   dark:text-blue-400',
    submitted: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    acknowledged: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    'pending review': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    cancelled: 'bg-red-100    text-red-800    dark:bg-red-900/30    dark:text-red-400',
    active: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
    disposed: 'bg-gray-100   text-gray-700   dark:bg-gray-700      dark:text-gray-300',
    eligible: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
    'not eligible': 'bg-red-100    text-red-800    dark:bg-red-900/30    dark:text-red-400',
    paid: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
    overdue: 'bg-red-100    text-red-800    dark:bg-red-900/30    dark:text-red-400',
    partial: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    returned: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    expired: 'bg-red-100    text-red-700    dark:bg-red-900/30    dark:text-red-400',
    used: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
    unused: 'bg-gray-100   text-gray-700   dark:bg-gray-700      dark:text-gray-300',
  }
  return map[status?.toLowerCase()] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
}
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