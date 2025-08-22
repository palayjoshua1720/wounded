<template>
    <div class="flex flex-col items-center justify-center py-6">
        <div :class="loaderClass"></div>
        <div class="text-center text-gray-400 py-4 text-sm">
            Fetching Data
        </div>
    </div>
</template>

<script setup lang="ts">
const loaderClass = [
    "w-8 h-8",
    "rounded-full",
    "border-4",
    "border-gray-200",
    "border-t-primary",
    "animate-spin",
    "mx-auto"
]
</script>

<style scoped>
@keyframes spin {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
