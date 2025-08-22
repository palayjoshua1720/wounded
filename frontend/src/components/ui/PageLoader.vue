<!-- PageLoader.vue -->
<template>
    <div 
        v-if="visible" 
        class="fixed inset-0 flex items-center justify-center bg-white dark:bg-gray-900/70 z-[9999]"
    >
        <span class="relative flex items-center justify-center h-16 w-16">
            <svg 
                class="absolute animate-ping-slow h-16 w-16 text-blue-400 dark:text-blue-700 opacity-30" 
                viewBox="0 0 64 64" fill="none"
            >
                <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4"/>
            </svg>
            <svg 
                class="relative h-12 w-12 text-blue-600 dark:text-blue-400" 
                viewBox="0 0 64 64" fill="none"
            >
                <rect x="27" y="12" width="10" height="40" rx="3" fill="currentColor"/>
                <rect x="12" y="27" width="40" height="10" rx="3" fill="currentColor"/>
            </svg>
        </span>
    </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
})
</script>

<style scoped>
@keyframes ping-slow {
    0% { transform: scale(1); opacity: 0.3; }
    70% { transform: scale(1.3); opacity: 0; }
    100% { transform: scale(1.3); opacity: 0; }
}
.animate-ping-slow {
    animation: ping-slow 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
