<template>
    <div class="group flex items-stretch transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/50"
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
                <div v-if="notification.detail || notification.manufacturer || notification.brands || notification.amount || notification.serial"
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
                    <button v-if="!notification.isRead" @click="$emit('mark-read', notification.id)"
                        class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors">
                        Mark as Read
                    </button>
                    <button @click="$emit('show-details', notification)"
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
</template>

<script setup lang="ts">
import {
    Box, FileUp, DollarSign, ShieldCheck, Receipt, Factory,
    Package as PackageIcon, Clock
} from 'lucide-vue-next'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime)

export interface Notification {
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

const props = defineProps<{
    notification: Notification
}>()

defineEmits<{
    (e: 'mark-read', id: string): void
    (e: 'show-details', notification: Notification): void
}>()

// Helper functions
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
</script>
