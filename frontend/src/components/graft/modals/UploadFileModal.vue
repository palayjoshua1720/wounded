<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
        <div class="fixed inset-0 bg-black/40 transition-opacity" @click="$emit('update:modelValue', false)"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Graft Log File</h3>
                <button
                    type="button"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    @click="$emit('update:modelValue', false)"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center">
                    <div class="w-14 h-14 rounded-full bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center mx-auto mb-3">
                        <UploadCloud class="w-7 h-7 text-purple-500" />
                    </div>
                    <p class="text-base font-medium text-gray-900 dark:text-white">File upload coming soon</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">This feature is under development. For now, please use Manual Entry to log graft usage.</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium"
                    @click="$emit('update:modelValue', false)"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { X, UploadCloud } from 'lucide-vue-next'

defineProps<{
    modelValue: boolean
}>()

defineEmits<{
    (e: 'update:modelValue', value: boolean): void
}>()
</script>
