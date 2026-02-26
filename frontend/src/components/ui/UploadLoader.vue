<template>
    <div v-if="isVisible" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 animate-in fade-in zoom-in duration-200">
            <div class="flex flex-col items-center space-y-6">
                <div class="relative">
                    <svg class="w-16 h-16 text-blue-600" viewBox="0 0 58 58">
                        <circle cx="29" cy="29" r="26" stroke="currentColor" stroke-opacity="0.2" stroke-width="4"
                            fill="none" />
                        <circle cx="29" cy="29" r="26" stroke="currentColor" stroke-width="4" fill="none"
                            stroke-dasharray="164" :stroke-dashoffset="164 - (164 * progress / 100)"
                            class="origin-center -rotate-90 transition-all duration-300 ease-out" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <component :is="icon" class="w-8 h-8 text-blue-600 animate-pulse" />
                    </div>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ effectiveTitle }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ progress }}% complete
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Package, Factory } from 'lucide-vue-next'

// ─── Define props properly ────────────────────────────────────────────────
const props = defineProps<{
    isVisible: boolean
    progress: number          // 0–100
    title?: string            // optional custom title
    context?: 'brand' | 'manufacturer' | 'general'
}>()

// ─── Computed values ───────────────────────────────────────────────────────
const icon = computed(() => {
    if (props.context === 'manufacturer') return Factory
    // default to brand / general
    return Package
})

const effectiveTitle = computed(() => {
    if (props.title) return props.title

    if (props.context === 'manufacturer') return 'Preparing manufacturer data...'
    if (props.context === 'brand') return 'Preparing brand data...'
    return 'Processing...'
})
</script>