<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 py-16 text-center">
        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-3">
            <BellOff class="w-5 h-5 text-gray-400" />
        </div>
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">No notifications found</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try adjusting your filters or date range.</p>
    </div>
</template>

<script setup lang="ts">
import { BellOff } from 'lucide-vue-next'
</script>
