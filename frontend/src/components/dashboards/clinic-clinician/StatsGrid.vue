<template>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
        <div v-for="n in 4" :key="n"
            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 animate-pulse">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3 mb-3"></div>
            <div class="h-7 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
        <div v-for="stat in statsCards" :key="stat.key"
            class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <!-- Hover gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-br 
        from-blue-600/10 to-transparent 
        dark:from-blue-600/10 to-transparent 
        opacity-0 group-hover:opacity-100 transition-opacity" />

            <!-- Icon + number inline, pill floats top-right -->
            <div class="flex items-start justify-between mb-2.5">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform"
                        :class="stat.iconBg">
                        <component :is="stat.icon" class="w-[18px] h-[18px]" :class="stat.iconColor" />
                    </div>
                    <div>
                        <p class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                            {{ stat.value }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">{{ stat.label }}</p>
                    </div>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0 ml-2" :class="stat.trendClass">
                    {{ stat.trendLabel }}
                </span>
            </div>
            <!-- Footer with divider -->
            <div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/60">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ stat.recent }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { ShoppingCart, Clock, Truck, CircleCheck } from 'lucide-vue-next'

interface ClinicStats {
    orders: {
        total: number
        this_month: number
        pending: number
        shipped: number
        delivered: number
    }
}

interface StatsCard {
    key: string
    label: string
    value: string
    recent: string
    trendLabel: string
    trendClass: string
    icon: typeof ShoppingCart
    iconColor: string
    iconBg: string
}

const props = defineProps<{
    clinicStats: ClinicStats
    loading: boolean
}>()

const statsCards = computed<StatsCard[]>(() => {
    const mkTrend = (label: string, cls: string) => ({ trendLabel: label, trendClass: cls })

    return [
        {
            key: 'total-orders',
            label: 'Total Orders',
            value: props.clinicStats?.orders?.total?.toLocaleString() ?? '0',
            recent: `${props.clinicStats?.orders?.this_month ?? 0} new orders this month`,
            ...mkTrend(
                props.clinicStats?.orders?.this_month > 0 ? `+${props.clinicStats.orders.this_month} this mo.` : 'No change',
                props.clinicStats?.orders?.this_month > 0
                    ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                    : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
            ),
            icon: ShoppingCart,
            iconColor: 'text-green-600 dark:text-green-400',
            iconBg: 'bg-green-50 dark:bg-green-900/30',
        },
        {
            key: 'pending-orders',
            label: 'Pending Orders',
            value: props.clinicStats?.orders?.pending?.toString() ?? '0',
            recent: 'Submitted & Acknowledged',
            trendLabel: 'Awaiting',
            trendClass: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            icon: Clock,
            iconColor: 'text-yellow-600 dark:text-yellow-400',
            iconBg: 'bg-yellow-50 dark:bg-yellow-900/30',
        },
        {
            key: 'shipped-orders',
            label: 'Shipped Orders',
            value: props.clinicStats?.orders?.shipped?.toString() ?? '0',
            recent: 'In transit',
            trendLabel: 'In transit',
            trendClass: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            icon: Truck,
            iconColor: 'text-blue-600 dark:text-blue-400',
            iconBg: 'bg-blue-50 dark:bg-blue-900/30',
        },
        {
            key: 'delivered-orders',
            label: 'Delivered Orders',
            value: props.clinicStats?.orders?.delivered?.toString() ?? '0',
            recent: 'Ready for usage logging',
            trendLabel: 'Delivered',
            trendClass: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            icon: CircleCheck,
            iconColor: 'text-emerald-600 dark:text-emerald-400',
            iconBg: 'bg-emerald-50 dark:bg-emerald-900/30',
        },
    ]
})
</script>
