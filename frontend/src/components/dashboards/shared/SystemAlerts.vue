<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Alerts</h3>
            <span v-if="urgentCount > 0"
                class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                {{ urgentCount }} urgent
            </span>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="divide-y divide-gray-100 dark:divide-gray-700">
            <div v-for="n in 3" :key="n" class="px-4 py-3.5 animate-pulse flex items-start gap-3">
                <div class="w-2 h-2 rounded-full bg-gray-200 dark:bg-gray-700 mt-1.5 flex-shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else-if="alerts.length === 0" class="px-4 py-10 text-center">
            <CheckCircle class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" />
            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">All clear</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">No active alerts at the moment.</p>
        </div>

        <!-- Alert items -->
        <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <div v-for="(alert, index) in alerts" :key="index"
                class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

                <!-- Dot indicator -->
                <div class="w-2 h-2 rounded-full flex-shrink-0 mt-1.5" :class="getAlertDot(alert.type)"></div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">
                        {{ alert.message }}
                    </p>
                    <p class="text-xs mt-0.5 font-medium" :class="getAlertSubColor(alert.type)">
                        {{ alert.detail || alert.time || 'Just now' }}
                    </p>
                </div>

                <span v-if="alert.priority === 'high'" class="flex-shrink-0 self-center text-xs font-medium"
                    :class="getAlertActionClass(alert.type)">
                    Action needed
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { CheckCircle } from 'lucide-vue-next'

interface Alert {
    type: string
    message: string
    detail?: string
    time?: string
    priority?: string
}

const props = defineProps<{
    alerts: Alert[]
    loading: boolean
}>()

const urgentCount = computed(() =>
    props.alerts.filter(a => a.priority === 'high').length
)

const getAlertDot = (type: string) => ({
    error: 'bg-red-500',
    warning: 'bg-amber-500',
    info: 'bg-blue-500',
    success: 'bg-green-500',
}[type] ?? 'bg-gray-400')

const getAlertSubColor = (type: string) => ({
    error: 'text-red-600 dark:text-red-400',
    warning: 'text-amber-700 dark:text-amber-400',
    info: 'text-blue-600 dark:text-blue-400',
    success: 'text-green-600 dark:text-green-400',
}[type] ?? 'text-gray-500 dark:text-gray-400')

const getAlertActionClass = (type: string) => ({
    error: 'text-red-600 dark:text-red-400',
    warning: 'text-amber-700 dark:text-amber-400',
    info: 'text-blue-600 dark:text-blue-400',
}[type] ?? 'text-gray-500 dark:text-gray-400')
</script>
