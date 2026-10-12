<template>
    <div v-if="meta.last_page > 1"
        class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-5 py-3">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Page <span class="font-medium text-gray-800 dark:text-gray-200">{{ meta.current_page }}</span>
            of <span class="font-medium text-gray-800 dark:text-gray-200">{{ meta.last_page }}</span>
            <span class="hidden sm:inline text-gray-400"> · {{ meta.total }} notifications</span>
        </p>
        <div class="flex gap-2">
            <button :disabled="meta.current_page <= 1" @click="$emit('page-change', meta.current_page - 1)" :class="[
                'inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
                meta.current_page <= 1
                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'
                    : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
            ]">
                <ChevronLeft class="w-3.5 h-3.5" /> Previous
            </button>
            <button :disabled="meta.current_page >= meta.last_page" @click="$emit('page-change', meta.current_page + 1)"
                :class="[
                    'inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
                    meta.current_page >= meta.last_page
                        ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'
                        : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
                ]">
                Next
                <ChevronRight class="w-3.5 h-3.5" />
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

interface PaginationMeta {
    current_page: number
    per_page: number
    total: number
    last_page: number
}

interface Props {
    meta: PaginationMeta
}

defineProps<Props>()

defineEmits<{
    (e: 'page-change', page: number): void
}>()
</script>
