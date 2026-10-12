<template>
    <BaseModal :isOpen="isOpen" title="Notification Details" size="lg" @close="$emit('close')">
        <div v-if="notification" class="max-h-[70vh] overflow-y-auto space-y-5 pr-1 sm:space-y-6">
            <!-- Summary -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-4 sm:p-5">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl sm:h-14 sm:w-14"
                        :class="getIconBg(notification.type)">
                        <component :is="getActivityIcon(notification.type)" class="h-5 w-5 sm:h-6 sm:w-6"
                            :class="getIconStyle(notification.type)" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="getIconBg(notification.type) + ' ' + getIconStyle(notification.type)">
                                {{ getTypeLabel(notification.type) }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="notification.isRead
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'">
                                {{ notification.isRead ? 'Read' : 'Unread' }}
                            </span>
                        </div>
                        <h2 class="mt-2 text-lg font-semibold leading-tight text-gray-900 dark:text-white sm:text-xl">
                            {{ notification.title }}
                        </h2>
                        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            {{ notification.message }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notification information -->
            <section>
                <div class="mb-3 flex items-center gap-2">
                    <Clock class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        Notification Information
                    </h3>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Created</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            {{ formatDateTime(notification.createdAt) }}
                        </p>
                    </div>
                    <div v-if="notification.status"
                        class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                        <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                            :class="getStatusBadge(notification.status)">
                            {{ notification.status }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Related details -->
            <section v-if="notification.clinic || notification.patient || notification.detail || notification.brands || notification.manufacturer || notification.amount || notification.serial">
                <div class="mb-3 flex items-center gap-2">
                    <FileText class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        Related Details
                    </h3>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div v-if="notification.clinic"
                        class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Clinic</p>
                        <p class="mt-1 break-words text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.clinic }}
                        </p>
                    </div>
                    <div v-if="notification.patient"
                        class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Patient</p>
                        <p class="mt-1 break-words text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.patient }}
                        </p>
                    </div>
                    <div v-if="notification.detail"
                        class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Detail</p>
                        <p class="mt-1 whitespace-pre-wrap break-words text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.detail }}
                        </p>
                    </div>
                    <div v-if="notification.brands"
                        class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Brand</p>
                        <p class="mt-1 break-words text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.brands }}
                        </p>
                    </div>
                    <div v-if="notification.manufacturer"
                        class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Manufacturer</p>
                        <p class="mt-1 break-words text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.manufacturer }}
                        </p>
                    </div>
                    <div v-if="notification.amount"
                        class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Amount</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ notification.amount }}
                        </p>
                    </div>
                    <div v-if="notification.serial"
                        class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Serial Number</p>
                        <p class="mt-1 break-all font-mono text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.serial }}
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <template #footer>
            <router-link v-if="notification?.actionUrl" :to="notification.actionUrl"
                class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 sm:w-auto"
                @click="$emit('close')">
                Go to {{ getTypeLabel(notification.type) }}
            </router-link>
            <button @click="$emit('close')"
                class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 sm:w-auto">
                Close
            </button>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { Box, FileText, FileUp, DollarSign, ShieldCheck, Receipt, Clock } from 'lucide-vue-next'
import BaseModal from '@/components/ui/BaseModal.vue'
import dayjs from 'dayjs'
import type { Notification } from './NotificationItem.vue'

interface Props {
    isOpen: boolean
    notification: Notification | null
}

defineProps<Props>()

defineEmits<{
    (e: 'close'): void
}>()

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

const getTypeLabel = (type: string) => {
    const map: Record<string, string> = {
        order: 'Order', usage: 'Usage', ivr: 'IVR', invoice: 'Invoice', return: 'Return'
    }
    return map[type] || 'System'
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

const formatDateTime = (date: string) => dayjs(date).format('MMM D, YYYY • h:mm A')
</script>
