<template>
  <div class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Notifications</h1>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">System events & clinical alerts</p>
      </div>
      <button v-if="unreadCount" @click="markAllAsRead" :disabled="markingRead"
        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors w-fit">
        <CheckCircle v-if="!markingRead" class="w-4 h-4" />
        <Clock v-else class="w-4 h-4 animate-spin" />
        Mark all as read
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <div
        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
          <Bell class="w-4 h-4 text-blue-600 dark:text-blue-400" />
        </div>
        <div>
          <p class="text-lg font-semibold text-gray-900 dark:text-white leading-none">{{ totalCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total Notifications</p>
        </div>
      </div>
      <div
        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
          <AlertTriangle class="w-4 h-4 text-red-500 dark:text-red-400" />
        </div>
        <div>
          <p class="text-lg font-semibold text-gray-900 dark:text-white leading-none">{{ unreadCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Unread Notifications</p>
        </div>
      </div>
      <div
        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div
          class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
          <CalendarCheck class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
        </div>
        <div>
          <p class="text-lg font-semibold text-gray-900 dark:text-white leading-none">{{ todayCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Notifications Today</p>
        </div>
      </div>
      <div
        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div
          class="w-9 h-9 rounded-lg bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
          <CalendarDays class="w-4 h-4 text-violet-600 dark:text-violet-400" />
        </div>
        <div>
          <p class="text-lg font-semibold text-gray-900 dark:text-white leading-none">{{ weekCount }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">This Week's Notifications</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <div class="flex flex-wrap items-center gap-3">
        <!-- Search -->
        <div class="relative min-w-[200px] flex-[1]">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input v-model="searchTerm" placeholder="Search notifications..."
            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 transition" />
        </div>

        <!-- Filters row -->
        <div class="flex flex-wrap items-center gap-2">
          <!-- Type -->
          <select v-model="typeFilter"
            class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 min-w-[130px]">
            <option value="all">All Types</option>
            <option v-for="t in notificationTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>

          <!-- Status -->
          <select v-model="statusFilter"
            class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 min-w-[120px]">
            <option value="all">All Status</option>
            <option value="unread">Unread</option>
            <option value="read">Read</option>
          </select>

          <!-- Date range -->
          <div class="flex items-center gap-2">
            <input type="date" v-model="startDate"
              class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 w-[130px]" />
            <span class="text-xs text-gray-400 flex-shrink-0">to</span>
            <input type="date" v-model="endDate"
              class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 w-[130px]" />
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <button @click="fetchNotifications()"
              class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
              <Filter class="w-3.5 h-3.5" />
              Apply
            </button>
            <button @click="clearDateRange"
              class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium rounded-lg transition-colors">
              <RotateCcw class="w-3.5 h-3.5" />
              Reset
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading"
      class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-800">
      <div v-for="n in 6" :key="n" class="flex items-center gap-4 px-4 py-4 animate-pulse">
        <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 flex-shrink-0" />
        <div class="flex-1 space-y-2">
          <div class="h-3.5 bg-gray-100 dark:bg-gray-800 rounded w-2/3" />
          <div class="h-3 bg-gray-100 dark:bg-gray-800 rounded w-1/3" />
        </div>
        <div class="w-16 h-5 bg-gray-100 dark:bg-gray-800 rounded-full" />
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="filteredNotifications.length === 0"
      class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 py-16 text-center">
      <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-3">
        <BellOff class="w-5 h-5 text-gray-400" />
      </div>
      <p class="text-sm font-medium text-gray-700 dark:text-gray-300">No notifications found</p>
      <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try adjusting your filters or date range.</p>
    </div>

    <!-- Notification List -->
    <div v-else
      class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-800">
      <div v-for="notification in filteredNotifications" :key="notification.id"
        class="group flex items-stretch transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/50"
        :class="{ 'bg-indigo-50/30 dark:bg-indigo-950/20': !notification.isRead }">
        <!-- Accent bar -->
        <div class="w-[3px] flex-shrink-0 rounded-none" :class="getAccentBar(notification.type)" />

        <!-- Icon + Body -->
        <div class="flex items-start gap-3 flex-1 min-w-0 px-4 py-3.5">
          <!-- Icon -->
          <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center mt-0.5"
            :class="getIconBg(notification.type)">
            <component :is="getActivityIcon(notification.type)" class="w-4 h-4"
              :class="getIconStyle(notification.type)" />
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <!-- Title row -->
            <div class="flex items-center gap-1.5 mb-0.5">
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                {{ notification.title }}
              </p>
              <span v-if="!notification.isRead" class="inline-block w-1.5 h-1.5 rounded-full flex-shrink-0"
                :class="getUnreadDot(notification.type)" />
            </div>

            <!-- Clinic · Patient -->
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1.5 truncate">
              {{ notification.clinic || notification.message }}
              <span v-if="notification.patient" class="mx-1 text-gray-300 dark:text-gray-600">·</span>
              <span v-if="notification.patient">{{ notification.patient }}</span>
            </p>

            <!-- Chips -->
            <div
              v-if="notification.detail || notification.manufacturer || notification.brands || notification.amount || notification.serial"
              class="flex flex-wrap items-center gap-1.5 mb-2">
              <span v-if="notification.detail"
                class="inline-flex items-center text-[11px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                {{ notification.detail }}
              </span>
              <span v-if="notification.manufacturer"
                class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                <Factory class="w-2.5 h-2.5 opacity-50" />{{ notification.manufacturer }}
              </span>
              <span v-if="notification.brands"
                class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                <PackageIcon class="w-2.5 h-2.5 opacity-50" />{{ notification.brands }}
              </span>
              <span v-if="notification.amount"
                class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-medium">
                <DollarSign class="w-2.5 h-2.5 opacity-50" />{{ notification.amount }}
              </span>
              <span v-if="notification.serial"
                class="inline-flex items-center text-[11px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                S/N: {{ notification.serial }}
              </span>
            </div>

            <!-- Action links -->
            <div class="flex items-center gap-3">
              <router-link v-if="notification.actionUrl" :to="notification.actionUrl"
                class="text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                View Details
              </router-link>
              <button v-if="!notification.isRead" @click="markAsRead(notification.id)"
                class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors">
                Mark as Read
              </button>
              <button @click="showNotificationDetails(notification)"
                class="text-[11px] text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                More Info
              </button>
            </div>
          </div>
        </div>

        <!-- Right: badge + time -->
        <div class="hidden sm:flex flex-col items-end justify-start gap-1.5 pr-4 py-3.5 flex-shrink-0">
          <span v-if="notification.status"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium"
            :class="getStatusBadge(notification.status)">
            {{ notification.status }}
          </span>
          <p class="text-[11px] text-gray-400 dark:text-gray-500 whitespace-nowrap">
            {{ timeAgo(notification.createdAt) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1"
      class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-5 py-3">
      <p class="text-xs text-gray-500 dark:text-gray-400">
        Page <span class="font-medium text-gray-800 dark:text-gray-200">{{ meta.current_page }}</span>
        of <span class="font-medium text-gray-800 dark:text-gray-200">{{ meta.last_page }}</span>
        <span class="hidden sm:inline text-gray-400"> · {{ meta.total }} notifications</span>
      </p>
      <div class="flex gap-2">
        <button :disabled="meta.current_page <= 1" @click="goToPage(meta.current_page - 1)" :class="[
          'inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
          meta.current_page <= 1
            ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'
            : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
        ]">
          <ChevronLeft class="w-3.5 h-3.5" /> Previous
        </button>
        <button :disabled="meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)" :class="[
          'inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
          meta.current_page >= meta.last_page
            ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'
            : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
        ]">
          Next
          <ChevronRight class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>

    <!-- Detail Modal -->
    <BaseModal :isOpen="showDetailsModal" title="Notification Details" @close="showDetailsModal = false">
      <div v-if="selected" class="space-y-4">

        <!-- Type & Read Status -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1.5">Type</p>
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full flex items-center justify-center" :class="getIconBg(selected.type)">
                <component :is="getActivityIcon(selected.type)" class="w-3.5 h-3.5"
                  :class="getIconStyle(selected.type)" />
              </div>
              <span class="text-sm text-gray-900 dark:text-white">{{ getTypeLabel(selected.type) }}</span>
            </div>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1.5">Read Status</p>
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium" :class="selected.isRead
              ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
              : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'">
              {{ selected.isRead ? 'Read' : 'Unread' }}
            </span>
          </div>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-800" />

        <!-- Title -->
        <div>
          <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Title</p>
          <p class="text-sm text-gray-900 dark:text-white">{{ selected.title }}</p>
        </div>

        <!-- Message -->
        <div>
          <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Message</p>
          <p class="text-sm text-gray-600 dark:text-gray-300">{{ selected.message }}</p>
        </div>

        <!-- Created & Clinic -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Created</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(selected.createdAt) }}</p>
          </div>
          <div v-if="selected.clinic">
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Clinic</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.clinic }}</p>
          </div>
        </div>

        <!-- Patient & Detail -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-if="selected.patient">
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Patient</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.patient }}</p>
          </div>
          <div v-if="selected.detail">
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Detail</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.detail }}</p>
          </div>
        </div>

        <!-- Brand & Manufacturer -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-if="selected.brands">
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Brand</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.brands }}</p>
          </div>
          <div v-if="selected.manufacturer">
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Manufacturer</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ selected.manufacturer }}</p>
          </div>
        </div>

        <!-- Amount & Serial -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-if="selected.amount">
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Amount</p>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selected.amount }}</p>
          </div>
          <div v-if="selected.serial">
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1">Serial Number</p>
            <p class="text-sm text-gray-900 dark:text-white font-mono">{{ selected.serial }}</p>
          </div>
        </div>

        <!-- Status -->
        <div v-if="selected.status">
          <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1.5">Status</p>
          <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="getStatusBadge(selected.status)">
            {{ selected.status }}
          </span>
        </div>
      </div>

      <template #footer>
        <router-link v-if="selected?.actionUrl" :to="selected.actionUrl"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors"
          @click="showDetailsModal = false">
          Go to {{ getTypeLabel(selected.type) }}
        </router-link>
        <button @click="showDetailsModal = false"
          class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm rounded-lg transition-colors">
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
import Swal from 'sweetalert2'
dayjs.extend(relativeTime)

import {
  Box, FileUp, DollarSign, ShieldCheck, Receipt, Factory,
  Package as PackageIcon, Clock, Search, Filter,
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

// ── Stats (fetched from API for accurate counts across all data) ──
const stats = ref({
  total: 0,
  unread: 0,
  today: 0,
  week: 0
})

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
    0: 'admin', 1: 'office-staff', 2: 'clinic',
    3: 'clinician', 4: 'manufacturer', 5: 'biller'
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

// ── Fetch ───────────────────────────────────────────────────────
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

function goToPage(page: number) { fetchNotifications(page) }

onMounted(async () => {
  await Promise.all([fetchNotifications(), fetchStats()])
})
watch(typeFilter, () => fetchNotifications())

let searchTimeout: ReturnType<typeof setTimeout> | null = null
watch(searchTerm, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchNotifications(), 400)
})

// ── Computed ────────────────────────────────────────────────────
const filteredNotifications = computed(() =>
  notifications.value.filter(n => {
    const matchesStatus =
      statusFilter.value === 'all' ||
      (statusFilter.value === 'unread' && !n.isRead) ||
      (statusFilter.value === 'read' && n.isRead)
    return matchesStatus
  })
)

// Use API stats for accurate counts across all data
const totalCount = computed(() => stats.value.total)
const unreadCount = computed(() => stats.value.unread)
const todayCount = computed(() => stats.value.today)
const weekCount = computed(() => stats.value.week)

// ── Fetch Stats ─────────────────────────────────────────────────
async function fetchStats() {
  try {
    const userRole = (authStore.user as any)?.user_role
    const params: Record<string, any> = {}
    if ((userRole === 2 || userRole === 3) && (authStore.user as any)?.clinic_id) {
      params.clinic_id = (authStore.user as any).clinic_id
    }

    const res = await notificationService.getNotificationStats(params)
    if (res?.data) {
      stats.value = {
        total: res.data.total ?? 0,
        unread: res.data.unread ?? 0,
        today: res.data.today ?? 0,
        week: res.data.week ?? 0
      }
    }
  } catch (err) {
    console.error('Failed to load notification stats:', err)
  }
}

// ── Helpers ─────────────────────────────────────────────────────
const timeAgo = (str: string | null | undefined) => {
  if (!str) return 'Unknown time'
  const date = dayjs(str)
  return date.isValid() ? date.fromNow() : 'Unknown time'
}

const getActivityIcon = (type: string) => {
  const map: Record<string, any> = {
    order: Box, usage: FileUp, ivr: ShieldCheck, invoice: Receipt, return: Box, payment: DollarSign
  }
  return map[type] || Clock
}

const getIconStyle = (type: string) => {
  const map: Record<string, string> = {
    order: 'text-violet-600 dark:text-violet-400',
    usage: 'text-amber-600  dark:text-amber-400',
    ivr: 'text-blue-600   dark:text-blue-400',
    invoice: 'text-emerald-600 dark:text-emerald-400',
    return: 'text-orange-600 dark:text-orange-400',
    payment: 'text-green-600  dark:text-green-400'
  }
  return map[type] || 'text-gray-500'
}

const getIconBg = (type: string) => {
  const map: Record<string, string> = {
    order: 'bg-violet-50  dark:bg-violet-900/30',
    usage: 'bg-amber-50   dark:bg-amber-900/30',
    ivr: 'bg-blue-50    dark:bg-blue-900/30',
    invoice: 'bg-emerald-50 dark:bg-emerald-900/30',
    return: 'bg-orange-50  dark:bg-orange-900/30',
    payment: 'bg-green-50   dark:bg-green-900/30'
  }
  return map[type] || 'bg-gray-100 dark:bg-gray-800'
}

// Left accent bar — slim colored strip per type
const getAccentBar = (type: string) => {
  const map: Record<string, string> = {
    order: 'bg-violet-500',
    usage: 'bg-amber-500',
    ivr: 'bg-blue-500',
    invoice: 'bg-emerald-500',
    return: 'bg-orange-500',
    payment: 'bg-green-500'
  }
  return map[type] || 'bg-gray-300 dark:bg-gray-600'
}

// Unread indicator dot color
const getUnreadDot = (type: string) => {
  const map: Record<string, string> = {
    order: 'bg-violet-500',
    usage: 'bg-amber-500',
    ivr: 'bg-blue-500',
    invoice: 'bg-emerald-500',
    return: 'bg-orange-500',
    payment: 'bg-green-500'
  }
  return map[type] || 'bg-indigo-500'
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
    delivered: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    shipped: 'bg-blue-50    text-blue-700    dark:bg-blue-900/30    dark:text-blue-400',
    submitted: 'bg-amber-50   text-amber-700   dark:bg-amber-900/30   dark:text-amber-400',
    acknowledged: 'bg-indigo-50  text-indigo-700  dark:bg-indigo-900/30  dark:text-indigo-400',
    pending: 'bg-amber-50   text-amber-700   dark:bg-amber-900/30   dark:text-amber-400',
    'pending review': 'bg-amber-50   text-amber-700   dark:bg-amber-900/30   dark:text-amber-400',
    cancelled: 'bg-red-50     text-red-700     dark:bg-red-900/30     dark:text-red-400',
    active: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    disposed: 'bg-gray-100   text-gray-600    dark:bg-gray-800       dark:text-gray-400',
    eligible: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'not eligible': 'bg-red-50     text-red-700     dark:bg-red-900/30     dark:text-red-400',
    paid: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    overdue: 'bg-red-50     text-red-700     dark:bg-red-900/30     dark:text-red-400',
    partial: 'bg-orange-50  text-orange-700  dark:bg-orange-900/30  dark:text-orange-400',
    returned: 'bg-orange-50  text-orange-700  dark:bg-orange-900/30  dark:text-orange-400',
    expired: 'bg-red-50     text-red-700     dark:bg-red-900/30     dark:text-red-400',
    used: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    unused: 'bg-gray-100   text-gray-600    dark:bg-gray-800       dark:text-gray-400',
  }
  return map[s] || 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
}

// ── Actions ─────────────────────────────────────────────────────
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
  if (!unreadIds.length) return

  const result = await Swal.fire({
    title: 'Mark all as read?',
    html: `You are about to mark <span class="font-semibold text-info">${unreadIds.length} notification${unreadIds.length > 1 ? 's' : ''}</span> as read.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#4f46e5',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, mark all as read',
    cancelButtonText: 'Cancel'
  })

  if (!result.isConfirmed) return

  markingRead.value = true
  try {
    notifications.value.forEach(n => { n.isRead = true })
    await notificationService.markAllAsRead(unreadIds)
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'All notifications marked as read',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    })
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
      console.error('Failed to mark as read:', err)
      n.isRead = false
    }
  }
}

const formatDateTime = (date: string) => dayjs(date).format('MMM D, YYYY • h:mm A')
</script>