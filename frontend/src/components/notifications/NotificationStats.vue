<template>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <!-- Total Notifications -->
        <div
            class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
            <div
                class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                <Bell class="w-4 h-4 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white leading-none">{{ total }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total Notifications</p>
            </div>
        </div>

        <!-- Unread Notifications -->
        <div
            class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                <AlertTriangle class="w-4 h-4 text-red-500 dark:text-red-400" />
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white leading-none">{{ unread }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Unread Notifications</p>
            </div>
        </div>

        <!-- Notifications Today -->
        <div
            class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
            <div
                class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                <CalendarCheck class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white leading-none">{{ today }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Notifications Today</p>
            </div>
        </div>

        <!-- This Week's Notifications -->
        <div
            class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
            <div
                class="w-9 h-9 rounded-lg bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                <CalendarDays class="w-4 h-4 text-violet-600 dark:text-violet-400" />
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white leading-none">{{ week }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">This Week's Notifications</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Bell, AlertTriangle, CalendarCheck, CalendarDays } from 'lucide-vue-next'

interface Props {
    total: number
    unread: number
    today: number
    week: number
}

defineProps<Props>()
</script>
