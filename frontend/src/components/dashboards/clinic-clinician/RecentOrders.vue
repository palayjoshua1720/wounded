<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
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
        <div v-else-if="orders.length === 0" class="px-4 py-10 text-center">
            <ShoppingCart class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" />
            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent orders</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">New orders will appear here.</p>
        </div>

        <!-- Items -->
        <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <div v-for="order in orders" :key="order.id"
                class="flex items-start gap-3 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors border-l-2"
                :class="getOrderBorder(order.status)">

                <!-- Icon -->
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                    :class="getOrderIconBg(order.status)">
                    <component :is="getOrderIcon(order.status)" class="w-5 h-5"
                        :class="getOrderIconStyle(order.status)" />
                </div>

                <!-- Body -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">
                        {{ order.product }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Patient: {{ order.patient_name || '—' }}
                    </p>
                    <div v-if="order.manufacturer !== '—' || order.brands !== '—'"
                        class="mt-1.5 flex flex-wrap items-center gap-1.5">
                        <span v-if="order.manufacturer !== '—'"
                            class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <Factory class="w-3 h-3" />{{ order.manufacturer }}
                        </span>
                        <span v-if="order.brands !== '—'"
                            class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <Package class="w-3 h-3" />{{ order.brands }}
                        </span>
                    </div>
                </div>

                <!-- Right: badge + time -->
                <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium leading-5"
                        :class="getStatusBadge(order.status)">
                        {{ order.status }}
                    </span>
                    <p class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                        {{ timeAgo(order.ordered_at) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ChevronRight, ShoppingCart, Factory, Package, Clock, CircleCheck, Truck, Box } from 'lucide-vue-next'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

interface Order {
    id: number
    product: string
    patient_name?: string
    manufacturer?: string
    brands?: string
    status: string
    ordered_at: string | null
}

defineProps<{
    orders: Order[]
    loading: boolean
}>()

const timeAgo = (value?: string | null) => {
    if (!value) return '—'
    return dayjs(value).fromNow()
}

const getOrderBorder = (status: string) => ({
    delivered: 'border-l-green-500',
    shipped: 'border-l-blue-500',
    submitted: 'border-l-yellow-500',
    acknowledged: 'border-l-indigo-500',
    pending: 'border-l-yellow-500',
    cancelled: 'border-l-red-500',
}[status?.toLowerCase()] ?? 'border-l-transparent')

const getOrderIcon = (status: string) => ({
    delivered: CircleCheck,
    shipped: Truck,
    pending: Clock,
    submitted: Clock,
    acknowledged: Clock,
    cancelled: Box,
}[status?.toLowerCase()] ?? Box)

const getOrderIconStyle = (status: string) => ({
    delivered: 'text-green-600',
    shipped: 'text-blue-600',
    pending: 'text-yellow-600',
    submitted: 'text-yellow-600',
    acknowledged: 'text-indigo-600',
    cancelled: 'text-red-600',
}[status?.toLowerCase()] ?? 'text-gray-500')

const getOrderIconBg = (status: string) => ({
    delivered: 'bg-green-100 dark:bg-green-900/30',
    shipped: 'bg-blue-100 dark:bg-blue-900/30',
    pending: 'bg-yellow-100 dark:bg-yellow-900/30',
    submitted: 'bg-yellow-100 dark:bg-yellow-900/30',
    acknowledged: 'bg-indigo-100 dark:bg-indigo-900/30',
    cancelled: 'bg-red-100 dark:bg-red-900/30',
}[status?.toLowerCase()] ?? 'bg-gray-100 dark:bg-gray-800')

const getStatusBadge = (status: string) => {
    const map: Record<string, string> = {
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        shipped: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        submitted: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        acknowledged: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    }
    return map[status?.toLowerCase()] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
}
</script>
