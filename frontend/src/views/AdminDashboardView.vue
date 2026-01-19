<template>
  <div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-4 rounded-2xl shadow-md flex items-center justify-between">
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

    <!-- Stats Grid - Now Dynamic! -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="stat in stats"
        :key="stat.name"
        class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ stat.name }}</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stat.value }}</p>
          </div>
          <div :class="['p-3 rounded-lg', stat.color.bg]">
            <component :is="stat.icon" :class="['w-6 h-6', stat.color.text]" />
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ stat.newThisMonth }} new this month
          </span>
        </div>
      </div>
    </div>

    <!-- Recent Activity & Alerts (unchanged for now) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
          <router-link to="/notifications" class="text-xs text-blue-600 hover:underline">
            View All
          </router-link>
        </div>
        <div class="space-y-4">
          <div
            v-for="activity in recentActivity"
            :key="activity.id"
            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
          >
            <component
              :is="getActivityIcon(activity.type)"
              class="w-5 h-5"
              :class="getIconStyle(activity.type)"
            />
            <div class="flex-1 ml-3">
              <p class="font-medium text-gray-900 dark:text-white">{{ activity.action }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ activity.clinic }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- System Alerts -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-sm border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Alerts</h3>
        <div class="space-y-4">
          <div v-for="(alert, index) in alerts" :key="index" class="flex items-start space-x-3">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon v-if="alert.type === 'warning'" class="w-5 h-5 text-yellow-500" />
              <XCircleIcon v-else-if="alert.type === 'error'" class="w-5 h-5 text-red-500" />
              <InformationCircleIcon v-else-if="alert.type === 'info'" class="w-5 h-5 text-blue-500" />
              <CheckCircleIcon v-else-if="alert.type === 'success'" class="w-5 h-5 text-green-500" />
            </div>
            <div class="flex-1">
              <p class="text-sm text-gray-900 dark:text-white">{{ alert.message }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ alert.time }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ShoppingCart, Building2, Factory, Package } from 'lucide-vue-next'
import { ExclamationTriangleIcon, InformationCircleIcon, XCircleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import { CubeIcon, CurrencyDollarIcon, DocumentArrowUpIcon, ShieldCheckIcon, ClockIcon } from '@heroicons/vue/24/outline'

import { useUser } from '@/composables/auth/useUser'
import { useAuthStore } from '@/stores/auth'
import { dashboardService } from '@/services/api'

// Auth
const authStore = useAuthStore()
const { userInitials } = useUser()
const currentUser = computed(() => authStore.currentUser)

// Reactive metrics from API
const metrics = ref<any>({
  brands: { total: 0, active: 0, new: 0 },
  manufacturers: { total: 0, active: 0, new: 0 },
  clinics: { total: 0, active: 0, new: 0 },
  orders: { total: 0, new: 0 }
})

// Load real stats on mount
onMounted(async () => {
  try {
    const result = await dashboardService.getMetrics()
    metrics.value = result
  } catch (err) {
    console.error('Failed to load dashboard metrics', err)
  }
})

// Dynamic Stats Cards
const stats = computed(() => [
  {
    name: 'Total Orders',
    value: metrics.value.orders.total.toLocaleString(),
    newThisMonth: metrics.value.orders.new,
    icon: ShoppingCart,
    color: { bg: 'bg-green-50 dark:bg-green-900/20', text: 'text-green-600 dark:text-green-400' }
  },
  {
    name: 'Active Clinics',
    value: metrics.value.clinics.active.toLocaleString(),
    newThisMonth: metrics.value.clinics.new,
    icon: Building2,
    color: { bg: 'bg-blue-50 dark:bg-blue-900/20', text: 'text-blue-600 dark:text-blue-400' }
  },
  {
    name: 'Active Manufacturers',
    value: metrics.value.manufacturers.active.toLocaleString(),
    newThisMonth: metrics.value.manufacturers.new,
    icon: Factory,
    color: { bg: 'bg-orange-50 dark:bg-orange-900/20', text: 'text-orange-600 dark:text-orange-400' }
  },
  {
    name: 'Active Brands',
    value: metrics.value.brands.active.toLocaleString(),
    newThisMonth: metrics.value.brands.new,
    icon: Package,
    color: { bg: 'bg-purple-50 dark:bg-purple-900/20', text: 'text-purple-600 dark:text-purple-400' }
  }
])

// Keep your mock data for Recent Activity & Alerts (or replace later)
const recentActivity = [
  { id: 1, action: 'New order submitted', clinic: 'Metro Wound Care', time: '2 minutes ago', type: 'order' },
  { id: 2, action: 'Usage log uploaded', clinic: 'Advanced Healing', time: '15 minutes ago', type: 'usage' },
  { id: 3, action: 'Invoice payment received', clinic: 'City Medical', time: '1 hour ago', type: 'payment' },
  { id: 4, action: 'IVR approved', clinic: 'Metro Wound Care', time: '2 hours ago', type: 'ivr' },
  { id: 5, action: 'Return processed', clinic: 'Healing Center', time: '3 hours ago', type: 'return' }
]

const alerts = [
  { type: 'warning', message: '12 grafts expiring within 30 days', time: '2 hours ago' },
  { type: 'error', message: '3 usage logs overdue', time: '4 hours ago' },
  { type: 'info', message: '5 IVR requests pending', time: '1 day ago' },
  { type: 'warning', message: '8 invoices unpaid', time: '1 day ago' },
  { type: 'success', message: '1 payment received', time: '1 day ago' }
]

const getActivityIcon = (type: string) => {
  const map: any = {
    order: CubeIcon,
    usage: DocumentArrowUpIcon,
    payment: CurrencyDollarIcon,
    ivr: ShieldCheckIcon,
    return: CubeIcon
  }
  return map[type] || ClockIcon
}

const getIconStyle = (type: string) => {
  const styles: any = {
    order: 'text-violet-600',
    usage: 'text-yellow-600',
    payment: 'text-green-600',
    ivr: 'text-blue-600',
    return: 'text-orange-600'
  }
  return styles[type] || 'text-gray-600'
}
</script>