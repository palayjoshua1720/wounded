<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
            <router-link to="/notifications"
                class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors flex items-center gap-1">
                View All
                <ChevronRight class="w-5 h-5" />
            </router-link>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="divide-y divide-gray-100 dark:divide-gray-700">
            <div v-for="n in 4" :key="n" class="px-4 py-3.5 animate-pulse flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else-if="activities.length === 0" class="px-4 py-10 text-center">
            <ClockIcon class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" />
            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent activity</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">New system events will appear here.</p>
        </div>

        <!-- Items -->
        <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <div v-for="activity in activities" :key="activity.id"
                class="flex items-start gap-3 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors border-l-2"
                :class="getActivityBorder(activity.type)">

                <!-- Icon -->
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                    :class="getIconBg(activity.type)">
                    <component :is="getActivityIcon(activity.type)" class="w-5 h-5"
                        :class="getIconStyle(activity.type)" />
                </div>

                <!-- Body -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">
                        {{ activity.action }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ activity.clinic }}<span v-if="activity.patient"> · {{ activity.patient }}</span>
                    </p>
                    <div v-if="activity.detail || activity.manufacturer || activity.brands || activity.amount || activity.serial"
                        class="mt-1.5 flex flex-wrap items-center gap-1.5">
                        <span v-if="activity.detail"
                            class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 px-1.5 py-0.5 rounded">
                            {{ activity.detail }}
                        </span>
                        <span v-if="activity.manufacturer"
                            class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <Factory class="w-3 h-3" />{{ activity.manufacturer }}
                        </span>
                        <span v-if="activity.brands"
                            class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <Package class="w-3 h-3" />{{ activity.brands }}
                        </span>
                        <span v-if="activity.amount"
                            class="inline-flex items-center gap-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                            <DollarSign class="w-3 h-3 text-gray-400" />{{ activity.amount }}
                        </span>
                        <span v-if="activity.serial" class="text-xs text-gray-500 dark:text-gray-400">
                            S/N: {{ activity.serial }}
                        </span>
                    </div>
                </div>

                <!-- Right: badge + time -->
                <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                    <span v-if="activity.status"
                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium leading-5"
                        :class="getStatusBadge(activity.status)">
                        {{ activity.status }}
                    </span>
                    <p class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                        {{ timeAgo(activity.timestamp) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ChevronRight, ClockIcon, Factory, Package, DollarSign, Box, FileUp, ShieldCheck, Receipt, Clock } from 'lucide-vue-next'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

interface Activity {
    id: number
    type: string
    action: string
    clinic: string
    patient?: string
    detail?: string
    manufacturer?: string
    brands?: string
    amount?: string
    serial?: string
    status?: string
    timestamp: string | null
}

defineProps<{
    activities: Activity[]
    loading: boolean
}>()

const timeAgo = (str: string | null | undefined) => {
    if (!str) return 'Unknown time'
    const date = dayjs(str)
    return date.isValid() ? date.fromNow() : 'Unknown time'
}

const getActivityIcon = (type: string) => ({
    order: Box,
    usage: FileUp,
    payment: DollarSign,
    ivr: ShieldCheck,
    return: Box,
    invoice: Receipt,
}[type] ?? Clock)

const getIconStyle = (type: string) => ({
    order: 'text-violet-600',
    usage: 'text-yellow-600',
    payment: 'text-green-600',
    ivr: 'text-indigo-600',
    return: 'text-orange-600',
    invoice: 'text-emerald-600',
}[type] ?? 'text-gray-500')

const getActivityBorder = (type: string) => ({
    order: 'border-l-violet-500',
    usage: 'border-l-yellow-500',
    invoice: 'border-l-emerald-500',
    ivr: 'border-l-indigo-500',
    return: 'border-l-orange-500',
    payment: 'border-l-green-500',
}[type] ?? 'border-l-transparent')

const getIconBg = (type: string) => ({
    order: 'bg-violet-100  dark:bg-violet-900/30',
    usage: 'bg-yellow-100  dark:bg-yellow-900/30',
    invoice: 'bg-emerald-100 dark:bg-emerald-900/30',
    ivr: 'bg-indigo-100  dark:bg-indigo-900/30',
    return: 'bg-orange-100  dark:bg-orange-900/30',
    payment: 'bg-green-100   dark:bg-green-900/30',
}[type] ?? 'bg-gray-100 dark:bg-gray-800')

const getStatusBadge = (status: string) => {
    const map: Record<string, string> = {
        delivered: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
        shipped: 'bg-blue-100   text-blue-800   dark:bg-blue-900/30   dark:text-blue-400',
        submitted: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        acknowledged: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'pending review': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        cancelled: 'bg-red-100    text-red-800    dark:bg-red-900/30    dark:text-red-400',
        active: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
        disposed: 'bg-gray-100   text-gray-700   dark:bg-gray-700      dark:text-gray-300',
        eligible: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
        'not eligible': 'bg-red-100    text-red-800    dark:bg-red-900/30    dark:text-red-400',
        paid: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
        overdue: 'bg-red-100    text-red-800    dark:bg-red-900/30    dark:text-red-400',
        partial: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        returned: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        expired: 'bg-red-100    text-red-700    dark:bg-red-900/30    dark:text-red-400',
        used: 'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-400',
        unused: 'bg-gray-100   text-gray-700   dark:bg-gray-700      dark:text-gray-300',
    }
    return map[status?.toLowerCase()] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
}
</script>
