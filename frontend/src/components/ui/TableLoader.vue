<template>
    <tr>
        <td :colspan="colspan" class="py-10 text-center align-middle">
            <div class="flex flex-col items-center justify-center">
                <div :class="loaderClass"></div>
                <div class="text-center text-gray-400 py-4 text-sm">Fetching Data</div>
            </div>
        </td>
    </tr>
</template>

<script setup lang="ts">
    defineProps({
        colspan: {
            type: Number,
            required: true
        }
    });

    const loaderClass = [
        "w-8 h-8", // 32px size
        "rounded-full",
        "border-4",
        "border-gray-200", // light gray background
        "border-t-primary", // your theme's primary color
        "animate-spin",
        "mx-auto"
    ];
</script>

<style scoped>
@keyframes spin {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
