<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
    <div class="fixed inset-0 bg-black/40 transition-opacity" @click="$emit('update:modelValue', false)"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Ledger Entry</h3>
        <button type="button" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" @click="$emit('update:modelValue', false)">
          <X class="w-5 h-5" />
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-6 space-y-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">Choose how you want to create the ledger entry:</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <button type="button"
            class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-700 rounded-xl hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-lg transition-all duration-200"
            @click="$emit('selectManual')">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800/50 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
              <Pencil class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <span class="font-semibold text-gray-900 dark:text-white text-base">Manual Entry</span>
            <span class="text-sm text-gray-500 dark:text-gray-400 mt-1.5 text-center">Enter ledger details manually</span>
          </button>

          <button type="button"
            class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-2 border-purple-200 dark:border-purple-700 rounded-xl hover:border-purple-500 dark:hover:border-purple-500 hover:shadow-lg transition-all duration-200"
            @click="$emit('selectUpload')">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-800/50 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
              <Upload class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <span class="font-semibold text-gray-900 dark:text-white text-base">Upload File</span>
            <span class="text-sm text-gray-500 dark:text-gray-400 mt-1.5 text-center">Upload ledger data via file</span>
          </button>
        </div>
      </div>
      <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
        <button type="button"
          class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium"
          @click="$emit('update:modelValue', false)">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { X, Pencil, Upload } from 'lucide-vue-next'

defineProps<{
  modelValue: boolean
}>()

defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'selectManual'): void
  (e: 'selectUpload'): void
}>()
</script>
