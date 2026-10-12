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
                    {{ stat.newThisMonth }} new {{ stat.key }} this month
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { ShoppingCart, Building2, Factory, Package } from 'lucide-vue-next'

interface Metrics {
    brands: { total: number; active: number; new: number }
    manufacturers: { total: number; active: number; new: number }
    clinics: { total: number; active: number; new: number }
    orders: { total: number; new: number }
}

interface StatsCards {
    key: string
    label: string
    value: string
    newThisMonth: number
    trendLabel: string
    trendClass: string
    icon: typeof ShoppingCart
    iconColor: string
    iconBg: string
}

const props = defineProps<{
    metrics: Metrics
    loading: boolean
}>()

const statsCards = computed<StatsCards[]>(() => {
    const mkTrend = (n: number, key: string) =>
        n > 0
            ? { label: `+${n} this mo.`, cls: trendColorMap[key] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }
            : { label: 'No change', cls: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }

    const trendColorMap: Record<string, string> = {
        orders: 'bg-green-50  text-green-700  dark:bg-green-900/30  dark:text-green-400',
        clinics: 'bg-blue-50   text-blue-700   dark:bg-blue-900/30   dark:text-blue-400',
        manufacturers: 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        brands: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    }

    return [
        {
            key: 'orders',
            label: 'Total orders',
            value: Number(props.metrics?.orders?.total ?? 0).toLocaleString(),
            newThisMonth: props.metrics?.orders?.new ?? 0,
            trendLabel: mkTrend(props.metrics?.orders?.new ?? 0, 'orders').label,
            trendClass: mkTrend(props.metrics?.orders?.new ?? 0, 'orders').cls,
            icon: ShoppingCart,
            iconColor: 'text-green-600 dark:text-green-400',
            iconBg: 'bg-green-50 dark:bg-green-900/30',
        },
        {
            key: 'clinics',
            label: 'Active clinics',
            value: Number(props.metrics?.clinics?.active ?? 0).toLocaleString(),
            newThisMonth: props.metrics?.clinics?.new ?? 0,
            trendLabel: mkTrend(props.metrics?.clinics?.new ?? 0, 'clinics').label,
            trendClass: mkTrend(props.metrics?.clinics?.new ?? 0, 'clinics').cls,
            icon: Building2,
            iconColor: 'text-blue-600 dark:text-blue-400',
            iconBg: 'bg-blue-50 dark:bg-blue-900/30',
        },
        {
            key: 'manufacturers',
            label: 'Active manufacturers',
            value: Number(props.metrics?.manufacturers?.active ?? 0).toLocaleString(),
            newThisMonth: props.metrics?.manufacturers?.new ?? 0,
            trendLabel: mkTrend(props.metrics?.manufacturers?.new ?? 0, 'manufacturers').label,
            trendClass: mkTrend(props.metrics?.manufacturers?.new ?? 0, 'manufacturers').cls,
            icon: Factory,
            iconColor: 'text-orange-600 dark:text-orange-400',
            iconBg: 'bg-orange-50 dark:bg-orange-900/30',
        },
        {
            key: 'brands',
            label: 'Active brands',
            value: Number(props.metrics?.brands?.active ?? 0).toLocaleString(),
            newThisMonth: props.metrics?.brands?.new ?? 0,
            trendLabel: mkTrend(props.metrics?.brands?.new ?? 0, 'brands').label,
            trendClass: mkTrend(props.metrics?.brands?.new ?? 0, 'brands').cls,
            icon: Package,
            iconColor: 'text-purple-600 dark:text-purple-400',
            iconBg: 'bg-purple-50 dark:bg-purple-900/30',
        },
    ]
})
</script>
