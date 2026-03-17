<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
        <p class="text-gray-600 dark:text-gray-400">
          System events &amp; alerts
          <span v-if="unreadCount"
            class="ml-2 px-2.5 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
            {{ unreadCount }} unread
          </span>
        </p>
      </div>
      <button v-if="unreadCount" @click="markAllAsRead"
        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md disabled:opacity-50"
        :disabled="markingRead">
        <CheckCircle v-if="!markingRead" class="w-5 h-5" />
        <Clock v-else class="w-5 h-5 animate-spin" />
        Mark all read
      </button>
    </div>

    <!-- Inline Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
          <Bell class="w-5 h-5 text-blue-600" />
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
        </div>
      </div>
      <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
          <AlertTriangle class="w-5 h-5 text-red-600" />
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ unreadCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Unread</p>
        </div>
      </div>
      <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
          <CalendarCheck class="w-5 h-5 text-green-600" />
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ todayCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Today</p>
        </div>
      </div>
      <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
          <CalendarDays class="w-5 h-5 text-purple-600" />
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ weekCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">This Week</p>
        </div>
      </div>
    </div>

    <!-- Filters & Date Range – unified bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
      <div class="flex flex-col lg:flex-row gap-4">
        <!-- Search -->
        <div class="relative flex-1 min-w-0">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" />
          <input v-model="searchTerm" placeholder="Search notifications..."
            class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
        </div>

        <!-- Type -->
        <select v-model="typeFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm min-w-[130px]">
          <option value="all">All Types</option>
          <option v-for="t in notificationTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
        </select>

        <!-- Read status -->
        <select v-model="statusFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm min-w-[120px]">
          <option value="all">All Status</option>
          <option value="unread">Unread</option>
          <option value="read">Read</option>
        </select>

        <!-- Date range -->
        <div class="flex items-center gap-2">
          <div class="relative">
            <Calendar
              class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" />
            <input type="date" v-model="startDate"
              class="pl-8 pr-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm w-[155px]" />
          </div>
          <span class="text-gray-400 text-sm">to</span>
          <div class="relative">
            <Calendar
              class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" />
            <input type="date" v-model="endDate"
              class="pl-8 pr-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm w-[155px]" />
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex gap-2 shrink-0">
          <button @click="fetchNotifications()"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all text-sm font-medium shadow-sm hover:shadow-md">
            <Filter class="w-4 h-4" />
            Apply
          </button>
          <button @click="clearDateRange"
            class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all text-sm font-medium">
            <RotateCcw class="w-4 h-4" />
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading"
      class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
      <div v-for="n in 6" :key="n" class="px-6 py-4 animate-pulse flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0" />
        <div class="flex-1 space-y-3">
          <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4" />
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2" />
        </div>
        <div class="w-20 h-4 bg-gray-200 dark:bg-gray-700 rounded" />
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="filteredNotifications.length === 0"
      class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
      <BellOff class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" />
      <h3 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">No notifications found</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or date range.</p>
    </div>

    <!-- Notification List -->
    <div v-else
      class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="divide-y divide-gray-100 dark:divide-gray-700">
        <div v-for="notification in filteredNotifications" :key="notification.id"
          class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group border-l-4" :class="[
            getActivityBorder(notification.type),
            !notification.isRead ? 'bg-indigo-50/40 dark:bg-indigo-900/10' : ''
          ]">

          <div class="flex items-start justify-between gap-4">
            <!-- Left: icon + content -->
            <div class="flex items-start gap-3 flex-1 min-w-0">
              <!-- Type icon -->
              <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mt-0.5"
                :class="getIconBg(notification.type)">
                <component :is="getActivityIcon(notification.type)" class="w-5 h-5"
                  :class="getIconStyle(notification.type)" />
              </div>

              <div class="flex-1 min-w-0">
                <!-- Title -->
                <div class="flex items-center gap-2">
                  <p
                    class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                    {{ notification.title }}
                  </p>
                  <!-- Unread dot -->
                  <span v-if="!notification.isRead"
                    class="inline-block w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0" />
                </div>

                <!-- Clinic · Patient -->
                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                  {{ notification.clinic || notification.message }}
                  <span v-if="notification.patient" class="text-gray-400 dark:text-gray-500"> · </span>
                  <span v-if="notification.patient">{{ notification.patient }}</span>
                </p>

                <!-- Detail chips row -->
                <div
                  v-if="notification.detail || notification.manufacturer || notification.brands || notification.amount || notification.serial"
                  class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                  <span v-if="notification.detail"
                    class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-700/50 px-2 py-0.5 rounded-md">
                    {{ notification.detail }}
                  </span>
                  <span v-if="notification.manufacturer" class="inline-flex items-center gap-1">
                    <Factory class="w-3 h-3 text-gray-400" />{{ notification.manufacturer }}
                  </span>
                  <span v-if="notification.brands" class="inline-flex items-center gap-1">
                    <PackageIcon class="w-3 h-3 text-gray-400" />{{ notification.brands }}
                  </span>
                  <span v-if="notification.amount"
                    class="inline-flex items-center gap-1 font-medium text-gray-700 dark:text-gray-300">
                    <DollarSign class="w-3 h-3 text-gray-400" />{{ notification.amount }}
                  </span>
                  <span v-if="notification.serial" class="inline-flex items-center gap-1">
                    S/N: {{ notification.serial }}
                  </span>
                </div>

                <!-- Action links -->
                <div class="flex items-center gap-3 mt-2">
                  <router-link v-if="notification.actionUrl" :to="notification.actionUrl"
                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs font-medium transition-colors">
                    View Details →
                  </router-link>
                  <button v-if="!notification.isRead" @click="markAsRead(notification.id)"
                    class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 text-xs font-medium transition-colors">
                    Mark as Read
                  </button>
                  <button @click="showNotificationDetails(notification)"
                    class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 text-xs font-medium transition-colors">
                    More Info
                  </button>
                </div>
              </div>
            </div>

            <!-- Right: status badge + time -->
            <div class="text-right shrink-0 flex flex-col items-end gap-1.5">
              <span v-if="notification.status"
                class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium leading-5"
                :class="getStatusBadge(notification.status)">
                {{ notification.status }}
              </span>
              <p class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                {{ timeAgo(notification.createdAt) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1"
      class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 px-5 py-3">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Page <span class="font-medium text-gray-900 dark:text-white">{{ meta.current_page }}</span>
        of <span class="font-medium text-gray-900 dark:text-white">{{ meta.last_page }}</span>
        <span class="hidden sm:inline"> · {{ meta.total }} notifications</span>
      </p>
      <div class="flex gap-2">
        <button :disabled="meta.current_page <= 1" @click="goToPage(meta.current_page - 1)" :class="[
          'inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
          meta.current_page <= 1
            ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
            : 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm'
        ]">
          <ChevronLeft class="w-4 h-4" />
          Previous
        </button>
        <button :disabled="meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)" :class="[
          'inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
          meta.current_page >= meta.last_page
            ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
            : 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm'
        ]">
          Next
          <ChevronRight class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Detail Modal (More Info) -->
    <BaseModal :isOpen="showDetailsModal" title="Notification Details" @close="showDetailsModal = false">
      <div v-if="selected" class="space-y-4">
        <!-- Type & Read row -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</p>
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full flex items-center justify-center" :class="getIconBg(selected.type)">
                <component :is="getActivityIcon(selected.type)" class="w-3.5 h-3.5"
                  :class="getIconStyle(selected.type)" />
              </div>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getTypeLabel(selected.type) }}</span>
            </div>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Read Status</p>
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium" :class="selected.isRead
              ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
              : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
              ">
              {{ selected.isRead ? 'Read' : 'Unread' }}
            </span>
          </div>
        </div>

        <!-- Title -->
        <div>
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Title</p>
          <p class="text-sm text-gray-900 dark:text-white">{{ selected.title }}</p>
        </div>

        <!-- Message -->
        <div>
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Message</p>
          <p class="text-sm text-gray-900 dark:text-white">{{ selected.message }}</p>
        </div>

        <!-- Created & Clinic -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Created</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(selected.createdAt) }}</p>
          </div>
          <div v-if="selected.clinic">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Clinic</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.clinic }}</p>
          </div>
        </div>

        <!-- Patient & Detail -->
        <div class="grid grid-cols-2 gap-4">
          <div v-if="selected.patient">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Patient</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.patient }}</p>
          </div>
          <div v-if="selected.detail">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Detail</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.detail }}</p>
          </div>
        </div>

        <!-- Brand & Manufacturer -->
        <div class="grid grid-cols-2 gap-4">
          <div v-if="selected.brands">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Brand</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.brands }}</p>
          </div>
          <div v-if="selected.manufacturer">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Manufacturer</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.manufacturer }}</p>
          </div>
        </div>

        <!-- Amount & Serial -->
        <div class="grid grid-cols-2 gap-4">
          <div v-if="selected.amount">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Amount</p>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selected.amount }}</p>
          </div>
          <div v-if="selected.serial">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Serial Number</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.serial }}</p>
          </div>
        </div>

        <!-- Status -->
        <div v-if="selected.status">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</p>
          <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="getStatusBadge(selected.status)">
            {{ selected.status }}
          </span>
        </div>
      </div>

      <template #footer>
        <router-link v-if="selected?.actionUrl" :to="selected.actionUrl"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors"
          @click="showDetailsModal = false">
          Go to {{ getTypeLabel(selected.type) }}
        </router-link>
        <button @click="showDetailsModal = false"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
          Close
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { notificationService } from '@/services/api'
import BaseModal from '@/components/ui/BaseModal.vue'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

// Lucide icons
import {
  Box, FileUp, DollarSign, ShieldCheck, Receipt, Factory,
  Package as PackageIcon, Clock, Search, Calendar, Filter,
  RotateCcw, ChevronLeft, ChevronRight, CheckCircle, BellOff,
  Bell, AlertTriangle, CalendarCheck, CalendarDays
} from 'lucide-vue-next'

// ── Types ───────────────────────────────────────────────────────
interface Notification {
  id: string
  type: string
  title: string
  message: string
  createdAt: string
  isRead: boolean
  actionUrl?: string
  clinic?: string
  patient?: string
  detail?: string
  brands?: string
  manufacturer?: string
  amount?: string
  serial?: string
  status?: string
  priority?: 'high' | 'medium' | 'low'
  additionalInfo?: Record<string, any>
}

interface PaginationMeta {
  current_page: number
  per_page: number
  total: number
  last_page: number
}

// ── State ───────────────────────────────────────────────────────
const authStore = useAuthStore()
const notifications = ref<Notification[]>([])
const loading = ref(true)
const searchTerm = ref('')
const typeFilter = ref('all')
const statusFilter = ref('all')
const startDate = ref('')
const endDate = ref('')
const showDetailsModal = ref(false)
const selected = ref<Notification | null>(null)
const meta = ref<PaginationMeta>({ current_page: 1, per_page: 10, total: 0, last_page: 1 })
const markingRead = ref(false)

// ── Type Config (for filters dropdown) ──────────────────────────
const notificationTypes = [
  { value: 'order', label: 'Order' },
  { value: 'usage', label: 'Usage' },
  { value: 'ivr', label: 'IVR' },
  { value: 'invoice', label: 'Invoice' },
  { value: 'return', label: 'Return' }
]

// ── Role-aware route mapping ────────────────────────────────────
const getActionUrl = (type: string): string | undefined => {
  const role = authStore.user?.user_role ?? 0

  const rolePrefix: Record<number, string> = {
    0: 'admin',
    1: 'office-staff',
    2: 'clinic',
    3: 'clinician',
    4: 'manufacturer',
    5: 'biller'
  }

  const prefix = rolePrefix[role] || 'admin'

  const routeMap: Record<string, string> = {
    order: `/${prefix}/order-management`,
    usage: `/${prefix}/inventory`,
    ivr: `/${prefix}/ivr-management`,
    invoice: `/${prefix}/invoice-management`,
    return: '/admin/returns'
  }

  return routeMap[type]
}

// ── Fetch from API ──────────────────────────────────────────────
async function fetchNotifications(page = 1) {
  loading.value = true
  try {
    const params: Record<string, any> = { page, per_page: 10 }
    if (typeFilter.value !== 'all') params.type = typeFilter.value
    if (searchTerm.value) params.search = searchTerm.value
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value) params.end_date = endDate.value

    const userRole = (authStore.user as any)?.user_role
    if ((userRole === 2 || userRole === 3) && (authStore.user as any)?.clinic_id) {
      params.clinic_id = (authStore.user as any).clinic_id
    }

    const res = await notificationService.getNotifications(params)
    const items = res?.data?.data || []

    notifications.value = items.map((a: any) => ({
      id: a.id || `notif_${Date.now()}`,
      type: a.type || 'unknown',
      title: a.title || a.action || 'Activity',
      message: a.message || (a.clinic ? `${a.title} • ${a.clinic}` : a.title || 'No details'),
      createdAt: a.created_at || new Date().toISOString(),
      isRead: a.is_read ?? false,
      actionUrl: a.action_url || getActionUrl(a.type),
      clinic: a.clinic || null,
      patient: a.patient || null,
      detail: a.detail || null,
      brands: a.brands || null,
      manufacturer: a.manufacturer || null,
      amount: a.amount || null,
      serial: a.serial || null,
      status: a.status || null,
      priority: a.priority || undefined,
    }))

    if (res?.data?.meta) meta.value = res.data.meta
  } catch (err) {
    console.error('Failed to load notifications:', err)
  } finally {
    loading.value = false
  }
}

function clearDateRange() {
  startDate.value = ''
  endDate.value = ''
  searchTerm.value = ''
  typeFilter.value = 'all'
  statusFilter.value = 'all'
  fetchNotifications()
}

function goToPage(page: number) {
  fetchNotifications(page)
}

onMounted(() => fetchNotifications())

watch(typeFilter, () => fetchNotifications())

let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchTerm, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchNotifications(), 400)
})

// ── Computed ────────────────────────────────────────────────────
const filteredNotifications = computed(() => {
  return notifications.value.filter(n => {
    const matchesStatus = statusFilter.value === 'all' ||
      (statusFilter.value === 'unread' && !n.isRead) ||
      (statusFilter.value === 'read' && n.isRead)
    return matchesStatus
  })
})

const totalCount = computed(() => meta.value.total)
const unreadCount = computed(() => notifications.value.filter(n => !n.isRead).length)
const todayCount = computed(() => notifications.value.filter(n => dayjs(n.createdAt).isSame(new Date(), 'day')).length)
const weekCount = computed(() => notifications.value.filter(n => dayjs(n.createdAt).isAfter(dayjs().subtract(7, 'day'))).length)

// ── Time formatter (mirrors dashboard) ──────────────────────────
const timeAgo = (str: string | null | undefined) => {
  if (!str) return 'Unknown time'
  const date = dayjs(str)
  return date.isValid() ? date.fromNow() : 'Unknown time'
}

// ── Visual helpers (same style as Admin Dashboard) ──────────────
const getActivityIcon = (type: string) => {
  const map: Record<string, any> = {
    order: Box,
    usage: FileUp,
    ivr: ShieldCheck,
    invoice: Receipt,
    return: Box,
    payment: DollarSign
  }
  return map[type] || Clock
}

const getIconStyle = (type: string) => {
  const styles: Record<string, string> = {
    order: 'text-violet-600',
    usage: 'text-yellow-600',
    ivr: 'text-blue-600',
    invoice: 'text-red-600',
    return: 'text-orange-600',
    payment: 'text-green-600'
  }
  return styles[type] || 'text-gray-600'
}

const getIconBg = (type: string) => {
  const map: Record<string, string> = {
    order: 'bg-violet-100 dark:bg-violet-900/30',
    usage: 'bg-yellow-100 dark:bg-yellow-900/30',
    ivr: 'bg-blue-100 dark:bg-blue-900/30',
    invoice: 'bg-emerald-100 dark:bg-emerald-900/30',
    return: 'bg-orange-100 dark:bg-orange-900/30'
  }
  return map[type] || 'bg-gray-100 dark:bg-gray-800'
}

const getActivityBorder = (type: string) => {
  const map: Record<string, string> = {
    order: 'border-violet-500',
    usage: 'border-yellow-500',
    ivr: 'border-blue-500',
    invoice: 'border-emerald-500',
    return: 'border-orange-500'
  }
  return map[type] || 'border-transparent'
}

const getTypeLabel = (type: string) => {
  const map: Record<string, string> = {
    order: 'Order', usage: 'Usage', ivr: 'IVR', invoice: 'Invoice', return: 'Return'
  }
  return map[type] || 'System'
}

const getStatusBadge = (status: string) => {
  const s = status?.toLowerCase()
  const map: Record<string, string> = {
    delivered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    shipped: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    submitted: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    acknowledged: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    disposed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    eligible: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    'not eligible': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    partial: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    returned: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    expired: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    unused: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  }
  return map[s] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const markAsRead = async (id: string) => {
  try {
    const n = notifications.value.find(n => n.id === id)
    if (n) n.isRead = true
    await notificationService.markAsRead(id)
  } catch (err) {
    console.error('Failed to mark as read:', err)
  }
}

const markAllAsRead = async () => {
  const unreadIds = notifications.value.filter(n => !n.isRead).map(n => n.id)
  if (unreadIds.length === 0) return

  markingRead.value = true
  try {
    notifications.value.forEach(n => { n.isRead = true })
    await notificationService.markAllAsRead(unreadIds)
  } catch (err) {
    console.error('Failed to mark all as read:', err)
  } finally {
    markingRead.value = false
  }
}

const showNotificationDetails = async (n: Notification) => {
  selected.value = n
  showDetailsModal.value = true

  if (!n.isRead) {
    n.isRead = true
    try {
      await notificationService.markAsRead(n.id)
    } catch (err) {
      console.error('Failed to mark notification as read:', err)
      n.isRead = false
    }
  }
}

const formatDateTime = (date: string) => dayjs(date).format('MMM D, YYYY • h:mm A')
</script>