<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <div v-if="logoutLoading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/80 dark:bg-gray-900/80">
      <span class="relative flex items-center justify-center h-24 w-24">
        <svg class="absolute animate-ping-slow h-24 w-24 text-blue-400 dark:text-blue-700 opacity-30" viewBox="0 0 96 96" fill="none"><circle cx="48" cy="48" r="42" stroke="currentColor" stroke-width="6"/></svg>
        <svg class="relative h-16 w-16 text-blue-600 dark:text-blue-400" viewBox="0 0 64 64" fill="none">
          <rect x="27" y="12" width="10" height="40" rx="3" fill="currentColor"/>
          <rect x="12" y="27" width="40" height="10" rx="3" fill="currentColor"/>
        </svg>
      </span>
    </div>
    <!-- Sidebar -->
    <Sidebar v-model:isOpen="isSidebarOpen" />
    
    <div class="lg:pl-64">
      <!-- Header -->
      <header class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white dark:bg-gray-800 shadow">
        <!-- Mobile Menu Button -->
        <button
          type="button"
          class="px-4 text-gray-500 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden"
          @click="toggleSidebar"
        >
          <span class="sr-only">Open sidebar</span>
          <svg
            class="h-6 w-6"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
        </button>

        <!-- Header Content -->
        <div class="flex flex-1 justify-between px-4">
          <div class="flex flex-1">
            <!-- Add your header content here -->
          </div>
          <!-- User Profile Section -->
          <div class="ml-auto mr-4 flex items-center md:ml-6 space-x-4">
            <!-- Notification Icon -->
            <div class="relative">
              <button
                type="button"
                class="relative p-2 text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-full"
                @click="toggleNotifications"
              >
                <span class="sr-only">View notifications</span>
                <BellIcon class="h-6 w-6" />
                <!-- Notification Badge -->
                <span
                  v-if="notificationCount > 0"
                  class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 text-xs text-white flex items-center justify-center"
                >
                  {{ notificationCount > 9 ? '9+' : notificationCount }}
                </span>
              </button>

              <!-- Notification Dropdown -->
              <div
                v-if="isNotificationsOpen"
                class="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                role="menu"
              >
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                  <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Notifications</h3>
                </div>
                <div class="max-h-96 overflow-y-auto">
                  <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    @click="handleNotificationClick(notification)"
                    class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                  >
                    <div class="flex items-start space-x-3">
                      <div class="flex-shrink-0">
                        <div
                          :class="[
                            'h-8 w-8 rounded-full flex items-center justify-center',
                            notification.type === 'success' ? 'bg-green-100 text-green-600' :
                            notification.type === 'warning' ? 'bg-yellow-100 text-yellow-600' :
                            notification.type === 'error' ? 'bg-red-100 text-red-600' :
                            'bg-blue-100 text-blue-600'
                          ]"
                        >
                          <svg
                            v-if="notification.type === 'success'"
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          <svg
                            v-else-if="notification.type === 'warning'"
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                          </svg>
                          <svg
                            v-else-if="notification.type === 'error'"
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          <svg
                            v-else
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                        </div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                          {{ notification.title }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                          {{ notification.message }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                          {{ formatNotificationTime(notification.timestamp) }}
                        </p>
                      </div>
                    </div>
                  </div>
                  <div v-if="notifications.length === 0" class="px-4 py-8 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No notifications</p>
                  </div>
                </div>
                <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                  <button
                    @click="viewAllNotifications"
                    class="w-full text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 text-center"
                  >
                    View all notifications
                  </button>
                </div>
              </div>
            </div>

            <div class="relative ml-3">
              <!-- Profile Button -->
              <div>
                <button
                  type="button"
                  class="flex max-w-xs items-center rounded-full bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                  @click="toggleProfile"
                >
                  <span class="sr-only">Open user menu</span>
                  <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                    {{ userInitials }}
                  </div>
                </button>
              </div>
              
              <!-- Profile Dropdown Menu -->
              <div
                v-if="isProfileOpen"
                class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                role="menu"
              >
                <!-- User Info -->
                <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                  <p class="font-medium truncate" :title="currentUser?.name">{{ currentUser?.first_name + ' ' + currentUser?.middle_name +  ' ' + currentUser?.last_name }}</p>
                  <p class="text-gray-500 dark:text-gray-400 truncate" :title="currentUser?.email">{{ currentUser?.email }}</p>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-700"></div>
                
                <!-- Settings Option -->
                <button
                  type="button"
                  class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700"
                  @click="handleSettingsClick"
                >
                  <div class="flex items-center">
                    <Cog6ToothIcon class="mr-3 h-5 w-5 text-gray-400 flex-shrink-0" />
                    <span class="truncate">Settings</span>
                  </div>
                </button>
                
                <!-- Change Account Option -->
                <button
                  type="button"
                  class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700"
                  @click="goToChangeAccount"
                >
                  <div class="flex items-center">
                    <ArrowPathIcon class="mr-3 h-5 w-5 text-gray-400 flex-shrink-0" />
                    <span class="truncate">Change Account</span>
                  </div>
                </button>

                <!-- Logout Button -->
                <button
                  type="button"
                  class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="handleLogout"
                  :disabled="isLoading"
                >
                  <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-3 h-5 w-5 text-gray-400 flex-shrink-0">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"></path>
                    </svg>
                    <span class="truncate">{{ isLoading ? 'Logging out...' : 'Sign out' }}</span>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
          <router-view></router-view>
        </div>
      </main>
    </div>

    <!-- Notification Modal -->
    <NotificationModal
      :isOpen="isNotificationModalOpen"
      :notification="selectedNotification"
      @close="closeNotificationModal"
    />
  </div>
</template>

<script setup lang="ts">
// Imports
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useClickOutside } from '@/composables/ui/useClickOutside'
import { useUser } from '@/composables/auth/useUser'
import { useLogin, logoutLoading } from '@/composables/auth/useLogin'
import Sidebar from './Sidebar.vue'
import NotificationModal from '@/components/notifications/NotificationModal.vue'
import { BellIcon } from '@heroicons/vue/24/outline'
import { ArrowPathIcon } from '@heroicons/vue/24/outline'
import { Cog6ToothIcon } from '@heroicons/vue/24/outline'

// Store Instances
const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const { userInitials } = useUser()
const { handleLogout } = useLogin()

// State
const isProfileOpen = ref(false)
const isSidebarOpen = ref(false)
const isNotificationsOpen = ref(false)
const notificationCount = ref(3) // Mock notification count
const isNotificationModalOpen = ref(false)
const selectedNotification = ref<any>(null)

// Mock notifications data
const notifications = ref([
  {
    id: '1',
    title: 'New Order Received',
    message: 'A new order has been placed for 50 units of Product XYZ. Please review and process the order as soon as possible.',
    type: 'info',
    timestamp: new Date(Date.now() - 1000 * 60 * 30).toISOString(), // 30 minutes ago
    route: '/orders'
  },
  {
    id: '2',
    title: 'Inventory Low Alert',
    message: 'Product ABC is running low on stock. Current quantity: 25 units. Please reorder soon.',
    type: 'warning',
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 2).toISOString(), // 2 hours ago
    route: '/inventory'
  },
  {
    id: '3',
    title: 'Payment Received',
    message: 'Payment of $2,500.00 has been received for Invoice #INV-2024-001.',
    type: 'success',
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 4).toISOString(), // 4 hours ago
    route: '/invoices'
  },
  {
    id: '4',
    title: 'System Maintenance',
    message: 'Scheduled maintenance will occur tonight from 2:00 AM to 4:00 AM. Some features may be temporarily unavailable.',
    type: 'info',
    timestamp: new Date(Date.now() - 1000 * 60 * 60 * 6).toISOString(), // 6 hours ago
    route: '/notifications'
  }
])

// Computed Properties
const currentUser = computed(() => authStore.currentUser)
const isLoading = computed(() => authStore.isLoading)
const isDarkMode = computed(() => themeStore.isDarkMode)

// Methods
const toggleProfile = () => {
  isProfileOpen.value = !isProfileOpen.value
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const toggleTheme = () => {
  themeStore.toggleTheme()
}

const toggleNotifications = () => {
  isNotificationsOpen.value = !isNotificationsOpen.value
}

const handleNotificationClick = (notification: any) => {
  // Navigate to the notification's route
  if (notification.route) {
    router.push(notification.route)
  }
  isNotificationsOpen.value = false
}

const viewAllNotifications = () => {
  router.push('/notifications')
  isNotificationsOpen.value = false
}

const formatNotificationTime = (timestamp: string) => {
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

const handleProfileClick = () => {
  // Close dropdown and navigate to profile
  isProfileOpen.value = false
  router.push('/profile')
}

const handleSettingsClick = () => {
  isProfileOpen.value = false
  router.push('/settings')
}

const closeNotificationModal = () => {
  isNotificationModalOpen.value = false
  selectedNotification.value = null
}

function goToChangeAccount() {
  router.push('/change-account')
}

// Lifecycle Hooks
const { handleClickOutside: handleProfileClickOutside } = useClickOutside(isProfileOpen)
const { handleClickOutside: handleNotificationsClickOutside } = useClickOutside(isNotificationsOpen)

onMounted(() => {
  document.addEventListener('click', handleProfileClickOutside)
  document.addEventListener('click', handleNotificationsClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleProfileClickOutside)
  document.removeEventListener('click', handleNotificationsClickOutside)
})
</script> 

<style scoped>
@keyframes ping-slow {
  0% { transform: scale(1); opacity: 0.3; }
  70% { transform: scale(1.3); opacity: 0; }
  100% { transform: scale(1.3); opacity: 0; }
}
.animate-ping-slow {
  animation: ping-slow 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style> 