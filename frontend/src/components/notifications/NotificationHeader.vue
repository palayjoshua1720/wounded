<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Notifications</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">System events & clinical alerts</p>
        </div>
        <button v-if="unreadCount" @click="$emit('mark-all-read')" :disabled="markingRead"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors w-fit">
            <CheckCircle v-if="!markingRead" class="w-4 h-4" />
            <Clock v-else class="w-4 h-4 animate-spin" />
            Mark all as read
        </button>
    </div>
</template>

<script setup lang="ts">
import { CheckCircle, Clock } from 'lucide-vue-next'

interface Props {
    unreadCount: number
    markingRead: boolean
}

defineProps<Props>()

defineEmits<{
    (e: 'mark-all-read'): void
}>()
</script>
