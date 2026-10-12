<template>
    <div
        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-800">
        <div v-for="n in count" :key="n" class="flex items-center gap-4 px-4 py-4 animate-pulse">
            <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 flex-shrink-0" />
            <div class="flex-1 space-y-2">
                <div class="h-3.5 bg-gray-100 dark:bg-gray-800 rounded w-2/3" />
                <div class="h-3 bg-gray-100 dark:bg-gray-800 rounded w-1/3" />
            </div>
            <div class="w-16 h-5 bg-gray-100 dark:bg-gray-800 rounded-full" />
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    count?: number
}

withDefaults(defineProps<Props>(), {
    count: 6
})
</script>
