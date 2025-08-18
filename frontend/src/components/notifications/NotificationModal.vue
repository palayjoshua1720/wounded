<template>
  <BaseModal
    :isOpen="isOpen"
    @close="$emit('close')"
    title="Notification Details"
    size="md"
  >
    <div class="space-y-4">
      <!-- Notification Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div
              :class="[
                'h-8 w-8 rounded-full flex items-center justify-center',
                notification?.type === 'success' ? 'bg-green-100 text-green-600' :
                notification?.type === 'warning' ? 'bg-yellow-100 text-yellow-600' :
                notification?.type === 'error' ? 'bg-red-100 text-red-600' :
                'bg-blue-100 text-blue-600'
              ]"
            >
              <svg
                v-if="notification?.type === 'success'"
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
              <svg
                v-else-if="notification?.type === 'warning'"
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
              <svg
                v-else-if="notification?.type === 'error'"
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              <svg
                v-else
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ notification?.title }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ formatDate(notification?.timestamp) }}
            </p>
          </div>
        </div>
        <span
          :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            notification?.type === 'success' ? 'bg-green-100 text-green-800' :
            notification?.type === 'warning' ? 'bg-yellow-100 text-yellow-800' :
            notification?.type === 'error' ? 'bg-red-100 text-red-800' :
            'bg-blue-100 text-blue-800'
          ]"
        >
          {{ notification?.type?.toUpperCase() || 'INFO' }}
        </span>
      </div>

      <!-- Notification Content -->
      <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <p class="text-sm text-gray-700 dark:text-gray-300">
          {{ notification?.message }}
        </p>
      </div>

      <!-- Action Buttons -->
      <div v-if="notification?.actions?.length" class="flex space-x-3">
        <button
          v-for="action in notification.actions"
          :key="action.label"
          @click="handleAction(action)"
          :class="[
            'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors',
            action.type === 'primary' 
              ? 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'
          ]"
        >
          {{ action.label }}
        </button>
      </div>

      <!-- Related Information -->
      <div v-if="notification?.relatedInfo" class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
          Related Information
        </h4>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
          <dl class="grid grid-cols-1 gap-2 text-sm">
            <div v-for="(value, key) in notification.relatedInfo" :key="key" class="flex justify-between">
              <dt class="text-gray-500 dark:text-gray-400 font-medium">{{ key }}:</dt>
              <dd class="text-gray-900 dark:text-gray-100">{{ value }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import BaseModal from '@/components/ui/BaseModal.vue'

interface NotificationAction {
  label: string
  type: 'primary' | 'secondary'
  action: string
  route?: string
}

interface Notification {
  id: string
  title: string
  message: string
  type: 'success' | 'warning' | 'error' | 'info'
  timestamp: string
  actions?: NotificationAction[]
  relatedInfo?: Record<string, string>
}

interface Props {
  isOpen: boolean
  notification?: Notification
}

interface Emits {
  (e: 'close'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()
const router = useRouter()

const formatDate = (timestamp?: string) => {
  if (!timestamp) return ''
  return new Date(timestamp).toLocaleString()
}

const handleAction = (action: NotificationAction) => {
  if (action.route) {
    router.push(action.route)
  }
  emit('close')
}
</script> 