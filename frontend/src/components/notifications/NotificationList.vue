<template>
    <div
        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-800">
        <NotificationItem v-for="notification in notifications" :key="notification.id" :notification="notification"
            @mark-read="$emit('mark-read', $event)" @show-details="$emit('show-details', $event)" />
    </div>
</template>

<script setup lang="ts">
import NotificationItem, { type Notification } from './NotificationItem.vue'

interface Props {
    notifications: Notification[]
}

defineProps<Props>()

defineEmits<{
    (e: 'mark-read', id: string): void
    (e: 'show-details', notification: Notification): void
}>()
</script>
