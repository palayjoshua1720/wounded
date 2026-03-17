<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="logoutLoading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/80 dark:bg-gray-900/80">
      <svg class="animate-spin h-16 w-16 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>
    </div>
    <!-- Mobile sidebar -->
    <transition name="fade">
      <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false" />
        <nav class="relative flex flex-col w-full max-w-xs bg-white shadow-xl">
          <div class="flex items-center justify-between px-4 py-6">
            <h1 class="text-xl font-bold text-gray-900">Medical Inventory</h1>
            <button @click="sidebarOpen = false" class="text-gray-500 hover:text-gray-700">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
          <div class="flex-1 px-4 pb-6">
            <router-link
              v-for="item in navigation"
              :key="item.id"
              :to="item.to"
              class="w-full flex items-center px-3 py-3 mb-2 text-left rounded-lg transition-colors"
              :class="isActive(item.to)
                ? 'bg-green-50 text-green-700 border-r-2 border-green-700'
                : 'text-gray-700 hover:bg-gray-50'"
              @click="sidebarOpen = false"
            >
              <component :is="item.icon" class="w-5 h-5 mr-3" />
              {{ item.label }}
            </router-link>
          </div>
        </nav>
      </div>
    </transition>

    <!-- Desktop sidebar -->
    <nav class="hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 lg:w-64 lg:bg-white lg:shadow-lg lg:z-40">
      <div class="flex items-center px-6 py-6 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-900">Medical Inventory</h1>
      </div>
      <div class="flex-1 px-4 py-6">
        <router-link
          v-for="item in navigation"
          :key="item.id"
          :to="item.to"
          class="w-full flex items-center px-3 py-3 mb-2 text-left rounded-lg transition-colors"
          :class="isActive(item.to)
            ? 'bg-green-50 text-green-700 border-r-2 border-green-700'
            : 'text-gray-700 hover:bg-gray-50'"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          {{ item.label }}
        </router-link>
      </div>
    </nav>

    <!-- Main content -->
    <div class="lg:ml-64">
      <!-- Header -->
      <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between px-4 py-4">
          <button
            @click="sidebarOpen = true"
            class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-50"
          >
            <Bars3Icon class="w-6 h-6" />
          </button>
          <div class="flex items-center space-x-4">
            <!-- Notification Icon -->
            <div class="relative">
              <button type="button"
                class="relative p-2 text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 rounded-full"
                @click="toggleNotifications">
                <span class="sr-only">View notifications</span>
                <BellRing class="h-6 w-6" />
                <span v-if="notificationCount > 0"
                  class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 text-xs text-white flex items-center justify-center">
                  {{ notificationCount > 9 ? '9+' : notificationCount }}
                </span>
              </button>

              <div v-if="isNotificationsOpen"
                class="absolute right-0 z-10 mt-2 w-96 origin-top-right rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                role="menu">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                  <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Notifications</h3>
                  <span v-if="notificationCount > 0" class="text-xs text-gray-500 dark:text-gray-400">{{ notificationCount }} unread</span>
                </div>
                <div class="max-h-96 overflow-y-auto">
                  <div v-if="headerNotifLoading" class="px-4 py-6 text-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500 mx-auto"></div>
                    <p class="text-xs text-gray-400 mt-2">Loading...</p>
                  </div>
                  <template v-else>
                    <div v-for="notification in headerNotifications" :key="notification.id"
                      @click="handleNotificationClick(notification)"
                      class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                      :class="!notification.is_read ? 'bg-indigo-50/40 dark:bg-indigo-900/10' : ''">
                      <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                          <div class="h-8 w-8 rounded-full flex items-center justify-center" :class="getNotifIconBg(notification.type)">
                            <component :is="getNotifIcon(notification.type)" class="h-4 w-4" :class="getNotifIconColor(notification.type)" />
                          </div>
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-1.5">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ notification.title }}</p>
                            <span v-if="!notification.is_read" class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 flex-shrink-0"></span>
                          </div>
                          <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ notification.clinic || notification.message }}</p>
                          <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ formatNotificationTime(notification.created_at || notification.createdAt) }}</p>
                        </div>
                        <span v-if="notification.status" class="flex-shrink-0 text-xs px-1.5 py-0.5 rounded-full font-medium" :class="getNotifStatusClass(notification.status)">
                          {{ notification.status }}
                        </span>
                      </div>
                    </div>
                    <div v-if="headerNotifications.length === 0" class="px-4 py-8 text-center">
                      <BellRing class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">No new notifications</p>
                    </div>
                  </template>
                </div>
                <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                  <button @click="viewAllNotifications" class="w-full text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 text-center py-1 font-medium">
                    View all notifications
                  </button>
                </div>
              </div>
            </div>

            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                <UserIcon class="w-5 h-5 text-white" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                <p class="text-xs text-gray-500 capitalize">{{ user?.role }}</p>
              </div>
            </div>
            <button
              @click="logout"
              class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg"
            >
              <ArrowRightOnRectangleIcon class="w-4 h-4 mr-2" />
              Logout
            </button>
          </div>
        </div>
      </header>
      <!-- Page content -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { logoutLoading } from '@/composables/auth/useLogin'
import {
  UserIcon,
  Bars3Icon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  ShoppingCartIcon,
  DocumentTextIcon,
  ReceiptPercentIcon,
  ShieldCheckIcon,
  BellIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'

import { notificationService } from '@/services/api'
import { useClickOutside } from '@/composables/ui/useClickOutside'
import { BellRing, ShieldCheck, Box, FileUp, Receipt, DollarSign, Clock } from 'lucide-vue-next'

const sidebarOpen = ref(false)
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const user = computed(() => authStore.user)
const logout = () => authStore.logout()

const navigation = computed(() => {
  const base = [
    { id: 'orders', label: 'My Orders', to: '/orders', icon: ShoppingCartIcon },
    { id: 'usage', label: 'Usage Logging', to: '/usage', icon: DocumentTextIcon },
    { id: 'invoices', label: 'Invoices', to: '/invoices', icon: ReceiptPercentIcon },
    { id: 'ivr', label: 'IVR Status', to: '/ivr', icon: ShieldCheckIcon },
    { id: 'notifications', label: 'Notifications', to: '/notifications', icon: BellIcon },
    { id: 'reports', label: 'Reports', to: '/reports', icon: ChartBarIcon },
  ];
  if (user.value && user.value.role === 'clinic') {
    return [
      { id: 'dashboard', label: 'Dashboard', to: '/clinic-dashboard', icon: ChartBarIcon },
      ...base
    ];
  }
  return base;
});

const isActive = (to: string) => route.path === to

const isNotificationsOpen = ref(false)
const notificationCount = ref(0)
const headerNotifications = ref<any[]>([])
const headerNotifLoading = ref(false)
let notifPollInterval: ReturnType<typeof setInterval> | null = null

// Fetch real notifications for the header bell
async function fetchHeaderNotifications() {
  try {
    const today = new Date().toISOString().slice(0, 10)
    const params: any = { per_page: 5, page: 1, start_date: today, end_date: today }
    if ((authStore.user as any)?.clinic_id) {
       params.clinic_id = (authStore.user as any).clinic_id
    }
    const res = await notificationService.getNotifications(params)
    const items = res?.data?.data || []
    headerNotifications.value = items
    notificationCount.value = res?.data?.meta?.unread_count ?? items.filter((n: any) => !n.is_read).length
  } catch (err) {
    console.error('Failed to fetch header notifications:', err)
  }
}

// Notification type → icon mapping
const getNotifIcon = (type: string) => {
  const map: Record<string, any> = { order: Box, usage: FileUp, ivr: ShieldCheck, invoice: Receipt, return: Box, payment: DollarSign }
  return map[type] || Clock
}
const getNotifIconBg = (type: string) => {
  const map: Record<string, string> = { order: 'bg-violet-100 dark:bg-violet-900/30', usage: 'bg-yellow-100 dark:bg-yellow-900/30', ivr: 'bg-blue-100 dark:bg-blue-900/30', invoice: 'bg-emerald-100 dark:bg-emerald-900/30', return: 'bg-orange-100 dark:bg-orange-900/30' }
  return map[type] || 'bg-gray-100 dark:bg-gray-800'
}
const getNotifIconColor = (type: string) => {
  const map: Record<string, string> = { order: 'text-violet-600', usage: 'text-yellow-600', ivr: 'text-blue-600', invoice: 'text-emerald-600', return: 'text-orange-600' }
  return map[type] || 'text-gray-600'
}
const getNotifStatusClass = (status: string) => {
  const s = status?.toLowerCase()
  const map: Record<string, string> = {
    delivered: 'bg-green-100 text-green-700', shipped: 'bg-blue-100 text-blue-700',
    submitted: 'bg-yellow-100 text-yellow-700', pending: 'bg-yellow-100 text-yellow-700',
    acknowledged: 'bg-indigo-100 text-indigo-700', cancelled: 'bg-red-100 text-red-700',
    active: 'bg-green-100 text-green-700', eligible: 'bg-green-100 text-green-700',
    'not eligible': 'bg-red-100 text-red-700', paid: 'bg-green-100 text-green-700',
    overdue: 'bg-red-100 text-red-700'
  }
  return map[s] || 'bg-gray-100 text-gray-700'
}

const toggleNotifications = async () => {
  isNotificationsOpen.value = !isNotificationsOpen.value
  if (isNotificationsOpen.value) {
    headerNotifLoading.value = true
    await fetchHeaderNotifications()
    headerNotifLoading.value = false
  }
}

const handleNotificationClick = async (notification: any) => {
  if (!notification.is_read) {
    try {
      await notificationService.markAsRead(notification.id)
      notification.is_read = true
      notificationCount.value = Math.max(0, notificationCount.value - 1)
    } catch (err) {
      console.error('Failed to mark notification as read:', err)
    }
  }
  const routeMap: Record<string, string> = {
    order: `/orders`,
    usage: `/usage`,
    ivr: `/ivr`,
    invoice: `/invoices`,
    return: `/reports`
  }
  const target = routeMap[notification.type]
  if (target) router.push(target)
  isNotificationsOpen.value = false
}

const viewAllNotifications = () => {
  router.push('/notifications')
  isNotificationsOpen.value = false
}

const formatNotificationTime = (timestamp: string) => {
  if (!timestamp) return ''
  const now = new Date()
  const notificationTime = new Date(timestamp)
  const diffInMinutes = Math.floor((now.getTime() - notificationTime.getTime()) / (1000 * 60))

  if (diffInMinutes < 1) return 'Just now'
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`

  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours}h ago`

  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays}d ago`

  return notificationTime.toLocaleDateString()
}

const { handleClickOutside: handleNotificationsClickOutside } = useClickOutside(isNotificationsOpen)

onMounted(() => {
  document.addEventListener('click', handleNotificationsClickOutside)
  fetchHeaderNotifications()
  notifPollInterval = setInterval(fetchHeaderNotifications, 60000)
})

onUnmounted(() => {
  document.removeEventListener('click', handleNotificationsClickOutside)
  if (notifPollInterval) clearInterval(notifPollInterval)
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style> 