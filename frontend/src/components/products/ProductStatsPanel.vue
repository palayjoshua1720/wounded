<template>
    <div
        class="bg-gradient-to-b from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 p-6 md:p-8 rounded-2xl shadow-lg border border-gray-200/60 dark:border-gray-700/60 backdrop-blur-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                {{ activeTab === 'grafts' ? 'Graft Size Overview' : 'Other Products Overview' }}
            </h3>
        </div>

        <!-- Main Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
            <div
                class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent dark:from-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <component :is="activeTab === 'grafts' ? PencilRuler : Package"
                            class="w-7 h-7 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                            {{ activeTab === 'grafts' ? stats.total : otherStats.total }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                            Total {{ activeTab === 'grafts' ? 'Sizes' : 'Products' }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent dark:from-green-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <CheckCircle2 class="w-7 h-7 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <p class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                            {{ activeTab === 'grafts' ? stats.active : otherStats.active }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Active</p>
                    </div>
                </div>
            </div>

            <div
                class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-transparent dark:from-red-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <XCircle class="w-7 h-7 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                            {{ activeTab === 'grafts' ? stats.inactive : otherStats.inactive }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Inactive</p>
                    </div>
                </div>
            </div>

            <div
                class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent dark:from-orange-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <Archive class="w-7 h-7 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <p class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                            {{ activeTab === 'grafts' ? stats.archived : otherStats.archived }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Archived</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Section -->
        <div class="mt-8">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <component :is="breakdownIcon" class="w-5 h-5 text-purple-500" />
                {{ activeTab === 'grafts' ? 'Brand Breakdown' : 'Breakdown by Type' }}
            </h4>

            <div v-if="(activeTab === 'grafts' && stats.brands.length > 0) || (activeTab === 'other' && otherStats.types.length > 0)"
                class="relative">
                <div class="overflow-x-auto pb-4 breakdown-scroll snap-x snap-mandatory">
                    <div class="inline-flex gap-4 min-w-max px-1">
                        <template v-if="activeTab === 'grafts'">
                            <div v-for="brand in stats.brands.slice(0, 12)" :key="brand.id"
                                class="snap-start flex-shrink-0 w-64 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-300 overflow-hidden group">
                                <div class="p-4 flex flex-col h-full">
                                    <div class="flex items-center justify-between mb-3">
                                        <span
                                            class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                            <Package class="w-4 h-4 mr-2.5 text-purple-500 shrink-0" />
                                            {{ brand.name }}
                                        </span>
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ brand.count
                                            }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ Math.round((brand.count / stats.total) * 100) }}% of total
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-if="activeTab === 'other'">
                            <div v-for="t in otherStats.types.slice(0, 12)" :key="t.type"
                                class="snap-start flex-shrink-0 w-64 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 overflow-hidden group">
                                <div class="p-4 flex flex-col h-full">
                                    <div class="flex items-center justify-between mb-3">
                                        <span
                                            class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                            <Package class="w-4 h-4 mr-2.5 text-blue-500 shrink-0" />
                                            {{ t.label }}
                                        </span>
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ t.count
                                            }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ Math.round((t.count / otherStats.total) * 100) }}% of total
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 text-center">
                    Showing
                    {{ activeTab === 'grafts' ? Math.min(stats.brands.length, 12) : Math.min(otherStats.types.length,
                    12) }}
                    of
                    {{ activeTab === 'grafts' ? stats.brands.length : otherStats.types.length }}
                    {{ activeTab === 'grafts' ? 'brands' : 'types' }}
                    <span
                        v-if="(activeTab === 'grafts' && stats.brands.length > 12) || (activeTab === 'other' && otherStats.types.length > 12)"
                        class="ml-1">(scroll to see more)</span>
                </p>
            </div>

            <div v-else
                class="text-center py-10 text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                No breakdown data available yet.
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Package, PencilRuler, CheckCircle2, XCircle, Archive } from 'lucide-vue-next'

interface BrandStat {
    id: string
    name: string
    count: number
}

interface TypeStat {
    type: number
    label: string
    count: number
}

interface Stats {
    total: number
    active: number
    inactive: number
    archived: number
    brands: BrandStat[]
}

interface OtherStats {
    total: number
    active: number
    inactive: number
    archived: number
    types: TypeStat[]
}

const props = defineProps<{
    activeTab: 'grafts' | 'other'
    stats: Stats
    otherStats: OtherStats
}>()

const breakdownIcon = computed(() => {
    return props.activeTab === 'grafts' ? PencilRuler : Package
})
</script>

<style scoped>
.breakdown-scroll {
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.breakdown-scroll::-webkit-scrollbar {
    display: none;
}

.breakdown-scroll:hover {
    scrollbar-width: thin;
    scrollbar-color: rgba(107, 114, 128, 0.5) transparent;
}

.dark .breakdown-scroll:hover {
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.breakdown-scroll:hover::-webkit-scrollbar {
    height: 6px;
}

.breakdown-scroll:hover::-webkit-scrollbar-thumb {
    background: rgba(107, 114, 128, 0.5);
    border-radius: 3px;
}

.dark .breakdown-scroll:hover::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
}
</style>
