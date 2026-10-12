<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
    <div class="fixed inset-0 bg-black/40 transition-opacity" @click="$emit('update:modelValue', false)"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-md flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
      <div class="flex-1 overflow-y-auto p-6">
        <div class="space-y-5">
          <div class="flex justify-center">
            <div class="w-14 h-14 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
              <AlertTriangle class="w-7 h-7 text-red-500 dark:text-red-400" />
            </div>
          </div>
          <div class="text-center space-y-1.5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete this entry?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
          </div>
          <div v-if="entry" class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-2">
            <div class="flex justify-between">
              <span class="text-xs text-gray-500 dark:text-gray-400">Serial Number</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ entry.serial_number }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-xs text-gray-500 dark:text-gray-400">Status</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ entry.status_label }}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
        <button @click="$emit('update:modelValue', false)" class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">Cancel</button>
        <button @click="$emit('confirm')" :disabled="isDeleting" class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium flex items-center gap-2 shadow-md">
          <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
          <Trash2 v-else class="w-4 h-4" />
          Delete
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { AlertTriangle, Loader2, Trash2 } from 'lucide-vue-next'

interface LedgerEntry {
  ledger_id: number
  serial_number: string
  status_label: string
}

defineProps<{
  modelValue: boolean
  entry: LedgerEntry | null
  isDeleting: boolean
}>()

defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'confirm'): void
}>()
</script>
