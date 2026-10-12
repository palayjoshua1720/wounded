<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="relative min-w-[200px] flex-[1]">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input :value="searchTerm"
                    @input="$emit('update:searchTerm', ($event.target as HTMLInputElement).value)"
                    placeholder="Search notifications..."
                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 transition" />
            </div>

            <!-- Filters row -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Type -->
                <select :value="typeFilter"
                    @change="$emit('update:typeFilter', ($event.target as HTMLSelectElement).value)"
                    class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 min-w-[130px]">
                    <option value="all">All Types</option>
                    <option v-for="t in notificationTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>

                <!-- Status -->
                <select :value="statusFilter"
                    @change="$emit('update:statusFilter', ($event.target as HTMLSelectElement).value)"
                    class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 min-w-[120px]">
                    <option value="all">All Status</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>

                <!-- Date range -->
                <div class="flex items-center gap-2">
                    <input type="date" :value="startDate"
                        @input="$emit('update:startDate', ($event.target as HTMLInputElement).value)"
                        class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 w-[130px]" />
                    <span class="text-xs text-gray-400 flex-shrink-0">to</span>
                    <input type="date" :value="endDate"
                        @input="$emit('update:endDate', ($event.target as HTMLInputElement).value)"
                        class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-400 w-[130px]" />
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button @click="$emit('apply')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <Filter class="w-3.5 h-3.5" />
                        Apply
                    </button>
                    <button @click="$emit('reset')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium rounded-lg transition-colors">
                        <RotateCcw class="w-3.5 h-3.5" />
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Search, Filter, RotateCcw } from 'lucide-vue-next'

interface NotificationType {
    value: string
    label: string
}

interface Props {
    searchTerm: string
    typeFilter: string
    statusFilter: string
    startDate: string
    endDate: string
    notificationTypes: NotificationType[]
}

defineProps<Props>()

defineEmits<{
    (e: 'update:searchTerm', value: string): void
    (e: 'update:typeFilter', value: string): void
    (e: 'update:statusFilter', value: string): void
    (e: 'update:startDate', value: string): void
    (e: 'update:endDate', value: string): void
    (e: 'apply'): void
    (e: 'reset'): void
}>()
</script>
