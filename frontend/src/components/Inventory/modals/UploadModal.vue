<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center">
    <div class="fixed inset-0 bg-black/40 transition-opacity" @click="$emit('close')"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl lg:rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col lg:mx-4 my-0 lg:my-8 overflow-hidden">
      <!-- Header -->
      <div class="flex-shrink-0 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Import Ledger Entries</h3>
        <button @click="$emit('close')" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
          <X class="w-5 h-5" />
        </button>
      </div>
      
      <!-- Body -->
      <div class="flex-1 overflow-y-auto p-6 space-y-6">
        <!-- Instructions -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
          <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2">Excel File Requirements</h4>
          <ul class="text-xs text-blue-800 dark:text-blue-400 space-y-1">
            <li>• Supported formats: .xlsx, .xls, .csv</li>
            <li>• First row must contain column headers</li>
            <li>• Required columns: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">serial_number</code>, <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">product_type</code>, <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">status</code></li>
            <li>• For graft: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">brand_name</code>, <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">graft_size</code> (format: "BrandName - Size"), <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">clinic_name</code></li>
            <li>• For other_product: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">other_product_name</code></li>
            <li>• Optional: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">order_number</code>, <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">is_used</code> (Yes/No), <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">graft_usage_id</code>, <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">invoice_status</code>, <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">invoice_number</code>, <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">notes</code></li>
            <li>• <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">product_type</code> must be "graft" or "other_product"</li>
            <li>• <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">status</code> must be 0-6 (0=Expected, 1=Delivered, 2=Used, 3=Partially Used, 4=Reassigned, 5=Unused, 6=Expired)</li>
          </ul>
          <button
            @click="$emit('downloadTemplate')"
            :disabled="isDownloadingTemplate"
            class="mt-3 flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-xs font-medium rounded-lg transition-colors"
          >
            <Loader2 v-if="isDownloadingTemplate" class="w-3.5 h-3.5 animate-spin" />
            <Download v-else class="w-3.5 h-3.5" />
            {{ isDownloadingTemplate ? 'Downloading...' : 'Download Template' }}
          </button>
        </div>

        <!-- File Upload -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Excel File</label>
          <div class="relative">
            <input
              type="file"
              accept=".xlsx,.xls,.csv"
              @change="handleFileSelect"
              class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 file:cursor-pointer file:transition-colors"
            />
          </div>
          <p v-if="uploadFile" class="mt-2 text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
            <CheckCircle2 class="w-4 h-4" />
            Selected: {{ uploadFile.name }}
          </p>
        </div>

        <!-- Upload Error -->
        <div v-if="uploadError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-start gap-3">
          <AlertCircle class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" />
          <div class="flex-1 min-w-0">
            <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">Import Failed</h4>
            <p class="mt-1 text-sm text-red-700 dark:text-red-400">{{ uploadError }}</p>
          </div>
          <button @click="$emit('clearError')" class="p-1 text-red-400 hover:text-red-600 dark:hover:text-red-300 rounded hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors flex-shrink-0">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Upload Results -->
        <div v-if="uploadResults" class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-3">
          <div class="flex items-center gap-2">
            <CheckCircle2 v-if="uploadResults.failed === 0" class="w-5 h-5 text-green-500" />
            <AlertCircle v-else class="w-5 h-5 text-orange-500" />
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
              {{ uploadResults.failed === 0 ? 'Import Completed Successfully' : 'Import Completed with Errors' }}
            </h4>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div class="bg-white dark:bg-gray-600 rounded-lg p-3 text-center">
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ uploadResults.total_rows }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Rows</p>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
              <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ uploadResults.successful }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Successful</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center">
              <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ uploadResults.failed }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Failed</p>
            </div>
          </div>
          
          <!-- Error Details -->
          <div v-if="uploadResults.errors && uploadResults.errors.length > 0" class="mt-4">
            <h5 class="text-xs font-semibold text-red-600 dark:text-red-400 mb-2">Errors:</h5>
            <div class="max-h-48 overflow-y-auto space-y-2">
              <div v-for="(error, index) in uploadResults.errors" :key="index" class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg p-3">
                <p class="text-xs font-medium text-red-900 dark:text-red-300">Row {{ error.row }} - {{ error.serial_number }}</p>
                <ul class="mt-1 text-xs text-red-700 dark:text-red-400 list-disc list-inside">
                  <li v-for="(msg, i) in error.errors" :key="i">{{ msg }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer -->
      <div class="flex-shrink-0 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
        <button @click="$emit('close')" class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
          Close
        </button>
        <button
          @click="$emit('processUpload')"
          :disabled="!uploadFile || isUploading"
          class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium flex items-center gap-2 transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/30 active:scale-95"
        >
          <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin" />
          <Upload v-else class="w-4 h-4" />
          {{ isUploading ? 'Importing...' : 'Import File' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { X, Loader2, Download, CheckCircle2, AlertCircle, Upload } from 'lucide-vue-next'

defineProps<{
  modelValue: boolean
  uploadFile: File | null
  isUploading: boolean
  uploadResults: any
  uploadError: string | null
  isDownloadingTemplate: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'processUpload'): void
  (e: 'downloadTemplate'): void
  (e: 'clearError'): void
  (e: 'update:uploadFile', file: File | null): void
}>()

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    emit('update:uploadFile', target.files[0])
  }
}
</script>
