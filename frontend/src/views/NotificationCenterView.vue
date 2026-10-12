<template>
  <div class="space-y-5">
    <!-- Header -->
    <NotificationHeader :unread-count="unreadCount" :marking-read="markingRead" @mark-all-read="markAllAsRead" />

    <!-- Stats -->
    <NotificationStats :total="totalCount" :unread="unreadCount" :today="todayCount" :week="weekCount" />

    <!-- Filters -->
    <NotificationFilters :search-term="searchTerm" :type-filter="typeFilter" :status-filter="statusFilter"
      :start-date="startDate" :end-date="endDate" :notification-types="notificationTypes"
      @update:search-term="searchTerm = $event" @update:type-filter="typeFilter = $event"
      @update:status-filter="statusFilter = $event" @update:start-date="startDate = $event"
      @update:end-date="endDate = $event" @apply="fetchNotifications()" @reset="clearDateRange" />

    <!-- Loading skeleton -->
    <NotificationLoading v-if="loading" :count="6" />

    <!-- Empty state -->
    <NotificationEmptyState v-else-if="filteredNotifications.length === 0" />

    <!-- Notification List -->
    <NotificationList v-else :notifications="filteredNotifications" @mark-read="markAsRead"
      @show-details="showNotificationDetails" />

    <!-- Pagination -->
    <NotificationPagination :meta="meta" @page-change="goToPage" />

    <!-- Detail Modal -->
    <NotificationDetailsModal :is-open="showDetailsModal" :notification="selected" @close="showDetailsModal = false" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { notificationService } from '@/services/api'
import { confirmAction } from '@/services/alert'
import { useAppToast } from '@/composables/ui/useAppToast'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'

// Components
import NotificationHeader from '@/components/notifications/NotificationHeader.vue'
import NotificationStats from '@/components/notifications/NotificationStats.vue'
import NotificationFilters from '@/components/notifications/NotificationFilters.vue'
import NotificationLoading from '@/components/notifications/NotificationLoading.vue'
import NotificationEmptyState from '@/components/notifications/NotificationEmptyState.vue'
import NotificationList from '@/components/notifications/NotificationList.vue'
import NotificationPagination from '@/components/notifications/NotificationPagination.vue'
import NotificationDetailsModal from '@/components/notifications/NotificationDetailsModal.vue'

dayjs.extend(relativeTime)

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
const toast = useAppToast()
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

// ── Actions ─────────────────────────────────────────────────────
const markAsRead = async (id: string) => {
  try {
    const n = notifications.value.find(n => n.id === id)
    if (n) n.isRead = true
    await notificationService.markAsRead(id)
    const notificationLabel = n?.title ? `Notification "${n.title}"` : 'The notification'
    toast.success(`${notificationLabel} was marked as read successfully.`)
  } catch (err) {
    console.error('Failed to mark as read:', err)
  }
}

const markAllAsRead = async () => {
  const unreadIds = notifications.value.filter(n => !n.isRead).map(n => n.id)
  if (!unreadIds.length) return

  const notificationLabel = `${unreadIds.length} notification${unreadIds.length > 1 ? 's' : ''}`
  const result = await confirmAction({
    title: 'Mark Notifications as Read?',
    text: `Are you sure you want to mark ${notificationLabel} as read?`,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, mark all as read!',
    cancelButtonText: 'Cancel'
  })

  if (!result.isConfirmed) return

  markingRead.value = true
  try {
    notifications.value.forEach(n => { n.isRead = true })
    await notificationService.markAllAsRead(unreadIds)
    const successMessage = unreadIds.length === 1
      ? 'The notification was marked as read successfully.'
      : `All ${notificationLabel} were marked as read successfully.`
    toast.success(successMessage)
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
</script>
