<template>
  <div class="space-y-6">
    <!-- Header and Mark All as Read -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
        <p class="text-gray-600 dark:text-gray-400">
          Stay updated with system alerts and important events
          <span v-if="unreadCount > 0" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400">
            {{ unreadCount }} unread
          </span>
        </p>
      </div>
      <button
        v-if="unreadCount > 0"
        @click="markAllAsRead"
        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        <CheckCircleIcon class="w-5 h-5 mr-2" />
        Mark All as Read
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Notifications</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ notifications.length }}</p>
          </div>
          <BellIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Unread</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ unreadCount }}</p>
          </div>
          <ExclamationTriangleIcon class="w-8 h-8 text-red-600 dark:text-red-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ todayCount }}</p>
          </div>
          <InformationCircleIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Week</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ weekCount }}</p>
          </div>
          <CheckCircleIcon class="w-8 h-8 text-purple-600 dark:text-purple-400" />
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-2 md:space-y-0">
      <div class="relative flex-1">
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Search notifications..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        />
        <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400 dark:text-gray-500" />
      </div>
      <select v-model="typeFilter" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        <option value="all">All Types</option>
        <option v-for="type in notificationTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
      </select>
      <select v-model="statusFilter" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        <option value="all">All Status</option>
        <option value="unread">Unread</option>
        <option value="read">Read</option>
      </select>
    </div>

    <!-- Notification List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
      <div
        v-for="notification in filteredNotifications"
        :key="notification.id"
        :class="[
          'flex items-start px-6 py-5 transition-colors',
          notification.isRead
            ? 'bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'
            : 'bg-blue-50 dark:bg-blue-900/20 text-gray-900 dark:text-white border-l-4 border-blue-400 dark:border-blue-500 shadow-sm hover:bg-blue-100 dark:hover:bg-blue-900/30'
        ]"
      >
        <div :class="['flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-4', getTypeColor(notification.type)]">
          <component :is="getTypeIcon(notification.type)" class="w-5 h-5" />
        </div>
        <div class="flex-1">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ notification.title }}</h3>
            <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ formatDate(notification.createdAt) }}</span>
          </div>
          <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ notification.message }}</p>
          <div class="flex items-center space-x-2 mt-2">
            <router-link
              v-if="notification.actionUrl"
              :to="notification.actionUrl"
              class="text-blue-600 dark:text-blue-400 hover:underline text-xs font-medium"
            >
              View Details
            </router-link>
            <button
              v-if="!notification.isRead"
              @click="markAsRead(notification.id)"
              class="text-green-600 dark:text-green-400 hover:underline text-xs font-medium"
            >
              Mark as Read
            </button>
            <button
              @click="showNotificationDetails(notification)"
              class="text-purple-600 dark:text-purple-400 hover:underline text-xs font-medium"
            >
              More Info
            </button>
          </div>
        </div>
      </div>
      <div v-if="filteredNotifications.length === 0" class="text-center py-12 text-gray-400 dark:text-gray-500">
        No notifications found.
      </div>
    </div>
  </div>

  <!-- Notification Details Modal -->
  <BaseModal v-model="showDetailsModal" title="Notification Details">
    <div v-if="selectedNotification" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
          <div class="mt-1 flex items-center">
            <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTypeColor(selectedNotification.type)}`">
              <component :is="getTypeIcon(selectedNotification.type)" class="w-4 h-4 mr-1" />
              {{ getTypeLabel(selectedNotification.type) }}
            </span>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
          <div class="mt-1">
            <span :class="selectedNotification.isRead ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400'">
              {{ selectedNotification.isRead ? 'Read' : 'Unread' }}
            </span>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedNotification.title }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedNotification.message }}</p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDateTime(selectedNotification.createdAt) }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User ID</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedNotification.userId }}</p>
        </div>
      </div>

      <div v-if="selectedNotification.actionUrl">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Action URL</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedNotification.actionUrl }}</p>
      </div>

      <!-- Additional Info based on notification type -->
      <div v-if="getAdditionalInfo(selectedNotification)">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional Information</label>
        <div class="mt-1 text-sm text-gray-900 dark:text-white">
          <div v-for="(value, key) in getAdditionalInfo(selectedNotification)" :key="key" class="mb-1">
            <span class="font-medium">{{ key }}:</span> {{ value }}
          </div>
        </div>
      </div>
    </div>
    <template #actions>
      <button
        @click="showDetailsModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Close
      </button>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
  BellIcon,
  MagnifyingGlassIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  InformationCircleIcon,
  ShieldCheckIcon,
  DocumentTextIcon,
  ReceiptPercentIcon,
  CubeIcon
} from '@heroicons/vue/24/outline'

interface Notification {
  id: string
  userId: string
  type: 'order' | 'invoice' | 'inventory' | 'ivr' | 'usage' | 'system'
  title: string
  message: string
  isRead: boolean
  createdAt: string
  actionUrl?: string
  additionalInfo?: Record<string, any>
}

// Notification type config
const notificationTypes = [
  { value: 'order', label: 'Order', icon: CubeIcon, color: 'bg-blue-100 text-blue-800' },
  { value: 'invoice', label: 'Invoice', icon: ReceiptPercentIcon, color: 'bg-green-100 text-green-800' },
  { value: 'inventory', label: 'Inventory', icon: ExclamationTriangleIcon, color: 'bg-yellow-100 text-yellow-800' },
  { value: 'ivr', label: 'IVR', icon: ShieldCheckIcon, color: 'bg-purple-100 text-purple-800' },
  { value: 'usage', label: 'Usage', icon: DocumentTextIcon, color: 'bg-orange-100 text-orange-800' },
  { value: 'system', label: 'System', icon: InformationCircleIcon, color: 'bg-gray-100 text-gray-800' },
]

// Mock data with additional info
const notifications = ref<Notification[]>([
  {
    id: '1',
    userId: '1',
    type: 'order',
    title: 'New Order Submitted',
    message: "Order ORD-001 has been submitted by St. Mary's Hospital",
    isRead: false,
    createdAt: '2025-01-27T10:30:00Z',
    actionUrl: '/orders/ORD-001',
    additionalInfo: {
      'Order ID': 'ORD-001',
      'Clinic': "St. Mary's Hospital",
      'Total Amount': '$2,500.00',
      'Items': '3 products',
      'Priority': 'Standard'
    }
  },
  {
    id: '2',
    userId: '1',
    type: 'invoice',
    title: 'Invoice Payment Received',
    message: 'Payment received for invoice INV-2025-001 from St. Mary\'s Hospital',
    isRead: false,
    createdAt: '2025-01-27T09:15:00Z',
    actionUrl: '/invoices/INV-2025-001',
    additionalInfo: {
      'Invoice Number': 'INV-2025-001',
      'Payment Amount': '$1,800.00',
      'Payment Method': 'ACH Transfer',
      'Transaction ID': 'TXN-789456',
      'Processed By': 'Auto System'
    }
  },
  {
    id: '3',
    userId: '1',
    type: 'inventory',
    title: 'Low Inventory Alert',
    message: 'Graft Matrix Pro - Size M is running low (5 units remaining)',
    isRead: true,
    createdAt: '2025-01-26T16:45:00Z',
    actionUrl: '/inventory',
    additionalInfo: {
      'Product': 'Graft Matrix Pro',
      'Size': 'Medium',
      'Current Stock': '5 units',
      'Reorder Point': '10 units',
      'Last Restocked': '2025-01-15'
    }
  },
  {
    id: '4',
    userId: '1',
    type: 'ivr',
    title: 'IVR Request Approved',
    message: 'IVR request for John Doe has been approved for MedTech products',
    isRead: true,
    createdAt: '2025-01-26T14:20:00Z',
    actionUrl: '/ivr',
    additionalInfo: {
      'Patient': 'John Doe',
      'Insurance': 'Blue Cross Blue Shield',
      'Brand': 'MedTech',
      'Approved Products': 'All MedTech products',
      'Approved By': 'System Auto-Approval'
    }
  },
  {
    id: '5',
    userId: '1',
    type: 'usage',
    title: 'Usage Log Reminder',
    message: 'Serial GM002 delivered 3 days ago - usage log pending',
    isRead: false,
    createdAt: '2025-01-25T11:00:00Z',
    actionUrl: '/usage',
    additionalInfo: {
      'Serial Number': 'GM002',
      'Product': 'Graft Matrix Pro',
      'Delivery Date': '2025-01-22',
      'Days Since Delivery': '3 days',
      'Clinic': "St. Mary's Hospital"
    }
  },
  {
    id: '6',
    userId: '1',
    type: 'system',
    title: 'System Maintenance',
    message: 'Scheduled maintenance will occur tonight from 2:00 AM - 4:00 AM EST',
    isRead: true,
    createdAt: '2025-01-24T18:30:00Z',
    additionalInfo: {
      'Maintenance Type': 'Scheduled',
      'Duration': '2 hours',
      'Start Time': '2:00 AM EST',
      'End Time': '4:00 AM EST',
      'Affected Services': 'Database optimization, Security updates'
    }
  }
])

const searchTerm = ref('')
const typeFilter = ref('all')
const statusFilter = ref('all')
const showDetailsModal = ref(false)
const selectedNotification = ref<Notification | null>(null)

const markAsRead = (id: string) => {
  notifications.value = notifications.value.map(n =>
    n.id === id ? { ...n, isRead: true } : n
  )
}

const markAllAsRead = () => {
  notifications.value = notifications.value.map(n => ({ ...n, isRead: true }))
}

const showNotificationDetails = (notification: Notification) => {
  selectedNotification.value = notification
  showDetailsModal.value = true
}

const filteredNotifications = computed(() => {
  return notifications.value.filter(n => {
    const matchesSearch = n.title.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      n.message.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesType = typeFilter.value === 'all' || n.type === typeFilter.value
    const matchesStatus = statusFilter.value === 'all' ||
      (statusFilter.value === 'unread' && !n.isRead) ||
      (statusFilter.value === 'read' && n.isRead)
    return matchesSearch && matchesType && matchesStatus
  })
})

const unreadCount = computed(() => notifications.value.filter(n => !n.isRead).length)
const todayCount = computed(() => {
  const today = new Date().toDateString()
  return notifications.value.filter(n => new Date(n.createdAt).toDateString() === today).length
})
const weekCount = computed(() => {
  const weekAgo = new Date()
  weekAgo.setDate(weekAgo.getDate() - 7)
  return notifications.value.filter(n => new Date(n.createdAt) >= weekAgo).length
})

const getTypeIcon = (type: string) => {
  const found = notificationTypes.find(t => t.value === type)
  return found ? found.icon : BellIcon
}

const getTypeColor = (type: string) => {
  const found = notificationTypes.find(t => t.value === type)
  return found ? found.color : 'bg-gray-100 text-gray-800'
}

const getTypeLabel = (type: string) => {
  const found = notificationTypes.find(t => t.value === type)
  return found ? found.label : 'Unknown'
}

const getAdditionalInfo = (notification: Notification) => {
  return notification.additionalInfo || null
}

const formatDate = (dateStr: string) => {
  const date = new Date(dateStr)
  return date.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })
}

const formatDateTime = (dateStr: string) => {
  const date = new Date(dateStr)
  return date.toLocaleString(undefined, { 
    dateStyle: 'full', 
    timeStyle: 'long' 
  })
}
</script> 