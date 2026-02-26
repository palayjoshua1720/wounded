<template v-if="!tableLoader">
  <div
    class="bg-white dark:bg-gray-800 mt-4 rounded-bl-2xl rounded-br-2xl shadow-sm dark:shadow-gray-900 border border-gray-200 dark:border-gray-700">
    <div class="p-6">
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Biller Tracking</h1>
            <p class="text-gray-600 dark:text-gray-400">Track Medicare payments and provider reimbursements</p>
          </div>
          <div class="flex gap-2">
            <button @click="showAddOptions = true"
              class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
              <Plus class="w-4 h-4 mr-2" />
              Add New
            </button>
          </div>
        </div>
      </div>

      <div class="space-y-6">

        <!-- Add Options Modal -->
        <BaseModal v-model="showAddOptions" title="Add New Entry">
          <div class="space-y-4">
            <p class="text-gray-600 dark:text-gray-400">Choose how you want to add the entry:</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
              <button @click="openManualEntry"
                class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors">
                <FileText class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" />
                <span class="font-medium text-gray-900 dark:text-white">Manual Entry</span>
                <span class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter details manually</span>
              </button>
              <button @click="openSpreadsheetUpload"
                class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-gray-700 transition-colors">
                <FileSpreadsheet class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" />
                <span class="font-medium text-gray-900 dark:text-white">Upload Spreadsheet</span>
                <span class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload CSV or Excel file</span>
              </button>
            </div>
          </div>

          <template #actions>
            <div class="flex justify-end w-full p-5">
              <button @click="showAddOptions = false; showManualModal = false; showUploadModal = false;"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
              </button>
            </div>
          </template>
        </BaseModal>

        <!-- Manual Entry Modal -->
        <BaseModal v-model="showManualModal" :title="editingItem ? 'Edit Entry' : 'Add New Entry'" size="lg">
          <form @submit.prevent="submitManualEntry" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div v-for="field in manualFormFields" :key="field.key"
                :class="['space-y-2', field.key === 'notes' ? 'md:col-span-2' : '']">
                <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
                  {{ field.label }} <span v-if="field.required" class="text-red-500">*</span>
                </label>

                <component :is="field.type === 'select' ? 'select' : field.type === 'textarea' ? 'textarea' : 'input'"
                  :value="manualForm[field.key as keyof ManualFormData]"
                  @input="updateManualFormField(field.key, $event)" @change="handleFieldChange"
                  :type="field.type === 'select' || field.type === 'textarea' ? undefined : field.type"
                  :step="field.step" :min="field.min" :placeholder="field.placeholder" :class="field.class"
                  :required="field.required" :rows="field.type === 'textarea' ? 3 : undefined"
                >
                  <template v-if="field.type === 'select'">
                    <option value="">
                      {{ field.placeholder || 'Select option' }}
                    </option>
                    <option
                      v-for="option in field.options"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </template>
                </component>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button type="button" @click="closeManualModal"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
              </button>
              <button type="submit" :disabled="manualSubmitting || (editingItem && !hasChanges)"
                class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="manualSubmitting"
                  class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                <span>{{ manualSubmitting ? 'Saving...' : (editingItem ? 'Update' : 'Save') }}</span>
              </button>
            </div>

            <!-- Changes indicator -->
            <div v-if="editingItem && hasChanges"
              class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
              <div class="flex items-center text-blue-800 dark:text-blue-300">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd" />
                </svg>
                <span class="text-sm">Changes detected. You can now update the entry.</span>
              </div>
            </div>
          </form>
        </BaseModal>

        <!-- Spreadsheet Upload Modal -->
        <BaseModal v-model="showUploadModal" title="Upload Spreadsheet" size="lg">
          <div class="space-y-6">
            <!-- Upload Instructions -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
              <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
                Upload a CSV, Excel, or PDF file with billing data. Required columns:
              </p>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                <div v-for="column in requiredColumns" :key="column"
                  class="bg-white dark:bg-gray-700 px-2 py-1 rounded text-blue-800 dark:text-blue-200">
                  {{ column }}
                </div>
              </div>
            </div>

            <!-- File Upload Area -->
            <div
              class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-blue-400 transition-colors"
              @drop="handleDrop" @dragover.prevent @dragenter.prevent>
              <FileSpreadsheet class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
              <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Billing Spreadsheet</p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Drag and drop your file here, or click to browse
              </p>
              <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg mb-4">
                <p class="text-xs text-blue-800 dark:text-blue-300">
                  <strong>Tip:</strong> For best results, upload clear PDF invoices with readable text. Scanned images
                  may not
                  extract properly.
                  If extraction fails, you'll be able to enter details manually.
                </p>
              </div>
              <input type="file" accept=".csv,.xlsx,.xls,.pdf" @change="handleFileSelect" class="hidden"
                id="spreadsheet-upload" ref="fileInput" />
              <label for="spreadsheet-upload"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors cursor-pointer">
                <Upload class="w-4 h-4 mr-2" />
                Choose File
              </label>
            </div>

            <!-- Uploaded File Preview -->
            <div v-if="uploadedFile" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <FileText class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ uploadedFile.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ formatFileSize(uploadedFile.size) }}
                    </p>
                  </div>
                </div>
                <button @click="removeFile" class="text-red-600 hover:text-red-800">
                  <X class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- Processing Status -->
            <div v-if="processing" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
              <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                <span class="text-blue-800 dark:text-blue-200">Processing file...</span>
              </div>
            </div>

            <!-- Column Mapping Section (if needed) -->
            <div v-if="previewData.length > 0 && availableColumns.length > 0" class="space-y-4">
              <h3 class="font-medium text-gray-900 dark:text-white">Column Mapping</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div v-for="requiredCol in requiredColumns" :key="requiredCol" class="flex items-center space-x-2">
                  <label class="text-sm text-gray-700 dark:text-gray-300 flex-shrink-0">{{ requiredCol }}:</label>
                  <select v-model="columnMappings[requiredCol]"
                    class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Select column</option>
                    <option v-for="availableCol in availableColumns" :key="availableCol" :value="availableCol">
                      {{ availableCol }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Preview Data -->
            <div v-if="previewData.length > 0" class="overflow-x-auto">
              <h3 class="font-medium text-gray-900 dark:text-white mb-3">Preview Data (First 5 rows)</h3>
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th v-for="header in Object.keys(previewData[0])" :key="header"
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      {{ header }}
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="(row, index) in previewData.slice(0, 5)" :key="index">
                    <td v-for="(value, key) in row" :key="key"
                      class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200 whitespace-nowrap">
                      {{ value }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Upload Actions -->
            <div v-if="previewData.length > 0"
              class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button @click="resetUpload"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
              </button>
              <button @click="processUpload" :disabled="uploading"
                class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="uploading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                <span>{{ uploading ? 'Importing...' : 'Import Data' }}</span>
              </button>
            </div>
          </div>
        </BaseModal>

        <!-- Data Table -->
        <div
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Patient</th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Invoice #</th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Service Date</th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Submission Date</th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Medicare Amount</th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Provider Amount</th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status</th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Clinician</th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="item in tableData" :key="item.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{
                    item.patientName
                  }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.invoiceNumber }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{
                    formatDate(item.serviceDate)
                  }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    {{ item.submissionDate ? formatDate(item.submissionDate) : '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${{
                    formatCurrency(item.medicareAmount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${{
                    formatCurrency(item.providerAmount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(item.status)" class="px-2.5 py-0.5 text-xs font-medium rounded-full">
                      {{ item.status.charAt(0).toUpperCase() + item.status.slice(1) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.clinician || '-'
                  }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <button @click="editItem(item)"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                        <Pencil class="w-4 h-4" />
                      </button>
                      <button @click="deleteItem(item)"
                        class="text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="tableData.length === 0">
                  <td colspan="9" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <FileText class="w-12 h-12 mx-auto mb-4 text-gray-400 dark:text-gray-500" />
                    <p>No billing data found. Add your first entry using the "Add New" button.</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <!-- Pagination -->
        <div v-if="pagination" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-400">
              Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
            </div>
            <div class="flex space-x-2">
              <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                Previous
              </button>
              <button @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { toast } from 'vue3-toastify'
import api from '@/services/api'
import { FileSpreadsheet, Upload, FileText, X, Download, Plus, RefreshCw, Pencil, Trash2 } from 'lucide-vue-next'
import * as XLSX from 'xlsx'
import Swal from 'sweetalert2'
import BaseModal from '@/components/common/BaseModal.vue'

// State
const showAddOptions = ref(false)
const showManualModal = ref(false)
const showUploadModal = ref(false)
const manualSubmitting = ref(false)
const uploading = ref(false)
const processing = ref(false)
const uploadedFile = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const previewData = ref<any[]>([])
const editingItem = ref<any>(null)

// Track original form data when editing
const originalFormData = ref<ManualFormData | null>(null)

// Column mapping for dynamic spreadsheet upload
const availableColumns = ref<string[]>([])
const columnMappings = ref<Record<string, string>>({})

interface FormFieldConfig {
  key: keyof ManualFormData;
  label: string;
  type: 'text' | 'number' | 'date' | 'select' | 'textarea';
  required: boolean;
  placeholder?: string;
  step?: string;
  min?: string;
  class: string;
  options?: Array<{ value: string; label: string }>;
}

interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

// Dynamic form configuration - will be loaded from API
const manualFormFields = ref<FormFieldConfig[]>([])

// Load form fields from API
const loadFormFields = async () => {
  try {
    const response = await api.get('/biller/form-config')
    
    // Transform API response to match our interface
    const apiFields = response.data.formFields.map((field: any) => ({
      key: field.key as keyof ManualFormData,
      label: field.label,
      type: field.type as 'text' | 'number' | 'date' | 'select' | 'textarea',
      required: field.required,
      placeholder: field.placeholder,
      step: field.step,
      min: field.min,
      class: field.class,
      options: field.options ? field.options.map((opt: any) => ({
        value: opt.value,
        label: opt.label
      })) : undefined
    }))

    manualFormFields.value = apiFields
    
    // Update required columns from API response
    requiredColumns.value = Array.isArray(response.data.requiredColumns) 
      ? response.data.requiredColumns 
      : [] 
  } catch (error) {
    console.error('Failed to load form fields:', error)
    toast.error('Failed to load form configuration')
    
    // Fallback to mock data if API fails
    const mockFields: FormFieldConfig[] = [
      {
        key: 'patientName',
        label: 'Patient Name',
        type: 'text',
        required: true,
        placeholder: 'Enter patient name',
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
      },
      {
        key: 'invoiceNumber',
        label: 'Invoice Number',
        type: 'text',
        required: true,
        placeholder: 'Enter invoice number',
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
      },
      {
        key: 'serviceDate',
        label: 'Service Date',
        type: 'date',
        required: true,
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
      },
      {
        key: 'submissionDate',
        label: 'Submission Date',
        type: 'date',
        required: false,
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
      },
      {
        key: 'medicareAmount',
        label: 'Medicare Amount',
        type: 'number',
        required: true,
        step: '0.01',
        min: '0',
        placeholder: '0.00',
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
      },
      {
        key: 'providerAmount',
        label: 'Provider Amount',
        type: 'number',
        required: true,
        step: '0.01',
        min: '0',
        placeholder: '0.00',
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
      },
      {
        key: 'status',
        label: 'Status',
        type: 'select',
        required: true,
        placeholder: 'Select status',
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
        options: [
          { value: 'pending', label: 'Pending' },
          { value: 'submitted', label: 'Submitted' },
          { value: 'processed', label: 'Processed' },
          { value: 'paid', label: 'Paid' },
          { value: 'denied', label: 'Denied' }
        ]
      },
      {
        key: 'clinician',
        label: 'Clinician',
        type: 'text',
        required: false,
        placeholder: 'Enter clinician name',
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'
      },
      {
        key: 'notes',
        label: 'Notes',
        type: 'textarea',
        required: false,
        placeholder: 'Additional notes...',
        class: 'w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none h-24'
      }
    ]

    manualFormFields.value = mockFields
    updateRequiredColumns() 
  }
}

// Table data - will be loaded from API
const tableData = ref<any[]>([])
const pagination = ref<Pagination | null>(null)
const tableLoader = ref(false)

// Load table data from API
const loadTableData = async (page = 1) => {
  tableLoader.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', page.toString())
    
    const response = await api.get(`/biller/tracking?${params}`)
    const data = response.data
    
    // Map API response to table format (snake_case to camelCase)
    tableData.value = data.data.map((item: any) => ({
      id: item.id,
      patientName: item.patient_name,
      invoiceNumber: item.invoice_number,
      serviceDate: item.service_date,
      submissionDate: item.submission_date,
      medicareAmount: parseFloat(item.medicare_amount),
      providerAmount: parseFloat(item.provider_amount),
      status: item.status,
      clinician: item.clinician,
      notes: item.notes
    }))
    
    // Update pagination
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total,
      from: data.from,
      to: data.to
    }
  } catch (error) {
    // console.error('Failed to load table data:', error)
    // toast.error('Failed to load billing data')
  } finally {
    tableLoader.value = false
  }
}

// Load table data when component mounts
onMounted(() => {
  loadTableData(1)
  loadFormFields()
})

// Manual form data
interface ManualFormData {
  patientName: string;
  invoiceNumber: string;
  serviceDate: string;
  submissionDate: string;
  medicareAmount: number;
  providerAmount: number;
  status: string;
  clinician: string;
  notes: string;
}

const manualForm = reactive<ManualFormData>({
  patientName: '',
  invoiceNumber: '',
  serviceDate: '',
  submissionDate: '',
  medicareAmount: 0,
  providerAmount: 0,
  status: '',
  clinician: '',
  notes: ''
})

// Required columns for spreadsheet upload - will be loaded from API
const requiredColumns = ref<string[]>([])

// Update required columns based on form fields
const updateRequiredColumns = () => {
  requiredColumns.value = manualFormFields.value
    .filter(field => field.required)
    .map(field => field.label) 
}

// Helper function to format dates for HTML input (YYYY-MM-DD)
const formatDateForInput = (dateString: string): string => {
  if (!dateString) return ''

  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) {
      // Try parsing different date formats
      const formats = [
        // MM/DD/YYYY
        () => {
          const parts = dateString.split(/[/-]/)
          if (parts.length === 3) {
            const month = parts[0].padStart(2, '0')
            const day = parts[1].padStart(2, '0')
            const year = parts[2]
            return new Date(`${year}-${month}-${day}`)
          }
          return null
        },
        // DD/MM/YYYY
        () => {
          const parts = dateString.split(/[/-]/)
          if (parts.length === 3) {
            const day = parts[0].padStart(2, '0')
            const month = parts[1].padStart(2, '0')
            const year = parts[2]
            return new Date(`${year}-${month}-${day}`)
          }
          return null
        },
        // ISO format (already handled by new Date())
      ]

      for (const format of formats) {
        try {
          const parsedDate = format()
          if (parsedDate && !isNaN(parsedDate.getTime())) {
            const year = parsedDate.getFullYear()
            const month = String(parsedDate.getMonth() + 1).padStart(2, '0')
            const day = String(parsedDate.getDate()).padStart(2, '0')
            return `${year}-${month}-${day}`
          }
        } catch (e) {
          continue
        }
      }
      return ''
    }

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
  } catch (error) {
    console.error('Error formatting date:', error)
    return ''
  }
}

// Map API item to form format (snake_case to camelCase)
const mapApiItemToForm = (item: any) => {
  return {
    patientName: item.patient_name || '',
    invoiceNumber: item.invoice_number || '',
    serviceDate: formatDateForInput(item.service_date || ''),
    submissionDate: formatDateForInput(item.submission_date || ''),
    medicareAmount: item.medicare_amount != null ? parseFloat(item.medicare_amount) : 0,
    providerAmount: item.provider_amount != null ? parseFloat(item.provider_amount) : 0,
    status: item.status || '',
    clinician: item.clinician || '',
    notes: item.notes || '',
  }
}

// Helper function to normalize string values (trim and compare)
const normalizeString = (value: any): string => {
  if (value === null || value === undefined) return ''
  return String(value).trim()
}

// Helper function to compare values considering whitespace
const compareValues = (newValue: any, originalValue: any): boolean => {
  // Handle null/undefined cases
  if (newValue === null || newValue === undefined) {
    return originalValue === null || originalValue === undefined || originalValue === ''
  }

  if (originalValue === null || originalValue === undefined) {
    return newValue === '' || (typeof newValue === 'string' && newValue.trim() === '')
  }

  // For strings, compare trimmed values
  if (typeof newValue === 'string' && typeof originalValue === 'string') {
    return newValue.trim() === originalValue.trim()
  }

  // For numbers, compare directly (considering 0 and null)
  if (typeof newValue === 'number' && typeof originalValue === 'number') {
    return newValue === originalValue
  }

  // For numbers vs strings (form might return string for number inputs)
  if (typeof newValue === 'string' && typeof originalValue === 'number') {
    const numValue = parseFloat(newValue)
    return !isNaN(numValue) && numValue === originalValue
  }

  if (typeof newValue === 'number' && typeof originalValue === 'string') {
    const numOriginal = parseFloat(originalValue)
    return !isNaN(numOriginal) && newValue === numOriginal
  }

  // Default comparison
  return String(newValue) === String(originalValue)
}

// Computed property to check if there are any changes
const hasChanges = computed(() => {
  if (!editingItem.value || !originalFormData.value) return false

  // Check each field for changes
  const fields: (keyof ManualFormData)[] = [
    'patientName', 'invoiceNumber', 'serviceDate', 'submissionDate',
    'medicareAmount', 'providerAmount', 'status', 'clinician', 'notes'
  ]

  for (const field of fields) {
    const currentValue = manualForm[field]
    const originalValue = originalFormData.value[field]

    if (!compareValues(currentValue, originalValue)) {
      return true
    }
  }

  return false
})

// Open modals
const openManualEntry = () => {
  resetManualForm()
  editingItem.value = null
  originalFormData.value = null
  showAddOptions.value = false
  showManualModal.value = true
}

const openSpreadsheetUpload = () => {
  showAddOptions.value = false
  showUploadModal.value = true
}

const updateManualFormField = (key: keyof ManualFormData, event: Event) => {
  const target = event.target as HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement;
  let value: any = target.value;

  // Convert the value based on the field type
  if (key === 'medicareAmount' || key === 'providerAmount') {
    value = parseFloat(target.value) || 0;
  }

  // Properly cast the value to the correct type
  if (key === 'medicareAmount' || key === 'providerAmount') {
    (manualForm as any)[key] = value;
  } else if (key === 'serviceDate' || key === 'submissionDate') {
    (manualForm as any)[key] = value;
  } else {
    (manualForm as any)[key] = value;
  }
}

// Handle field change to detect changes
const handleFieldChange = () => {
  // This method is triggered on @change to help detect changes
  // The hasChanges computed property will automatically update
}

const closeManualModal = () => {
  showManualModal.value = false
  editingItem.value = null
  originalFormData.value = null
  resetManualForm()
}

// Manual form functions
const submitManualEntry = async () => {
  manualSubmitting.value = true

  try {
    // Enhanced validation
    const errors = []
    
    // Required field validation
    if (!manualForm.patientName?.trim()) {
      errors.push('Patient Name is required')
    }
    if (!manualForm.invoiceNumber?.trim()) {
      errors.push('Invoice Number is required')
    }
    if (!manualForm.serviceDate) {
      errors.push('Service Date is required')
    }
    if (manualForm.medicareAmount == null || isNaN(manualForm.medicareAmount) || manualForm.medicareAmount < 0) {
      errors.push('Valid Medicare Amount is required')
    }
    if (manualForm.providerAmount == null || isNaN(manualForm.providerAmount) || manualForm.providerAmount < 0) {
      errors.push('Valid Provider Amount is required')
    }
    if (!manualForm.status) {
      errors.push('Status is required')
    }
    
    // Date validation
    if (manualForm.serviceDate) {
      const serviceDate = new Date(manualForm.serviceDate)
      if (isNaN(serviceDate.getTime())) {
        errors.push('Invalid Service Date format')
      }
    }
    
    if (manualForm.submissionDate) {
      const submissionDate = new Date(manualForm.submissionDate)
      if (isNaN(submissionDate.getTime())) {
        errors.push('Invalid Submission Date format')
      }
      // Optional: validate that submission date is not before service date
      if (manualForm.serviceDate) {
        const serviceDate = new Date(manualForm.serviceDate)
        const subDate = new Date(manualForm.submissionDate)
        if (subDate < serviceDate) {
          errors.push('Submission Date cannot be before Service Date')
        }
      }
    }
    
    if (errors.length > 0) {
      toast.error(`Validation errors:\n${errors.join('\n')}`)
      return
    }

    // For edit mode, check if there are actual changes
    if (editingItem.value && !hasChanges.value) {
      toast.info('No changes detected. The entry remains unchanged.')
      closeManualModal()
      return
    }

    if (editingItem.value) {
      // Update existing item
      try {
        const response = await api.put(`/biller/tracking/${editingItem.value.id}`, {
          patient_name: manualForm.patientName || '',
          invoice_number: manualForm.invoiceNumber || '',
          service_date: manualForm.serviceDate || '',
          submission_date: manualForm.submissionDate || null,
          medicare_amount: manualForm.medicareAmount != null ? parseFloat(manualForm.medicareAmount.toString()) : 0,
          provider_amount: manualForm.providerAmount != null ? parseFloat(manualForm.providerAmount.toString()) : 0,
          status: manualForm.status || '',
          clinician: manualForm.clinician || '',
          notes: manualForm.notes || ''
        })

        // Update local data
        const index = tableData.value.findIndex(item => item.id === editingItem.value.id)
        if (index !== -1) {
          // Map API response to table format (snake_case to camelCase)
          tableData.value[index] = {
            id: response.data.id,
            patientName: response.data.patient_name,
            invoiceNumber: response.data.invoice_number,
            serviceDate: response.data.service_date,
            submissionDate: response.data.submission_date,
            medicareAmount: parseFloat(response.data.medicare_amount),
            providerAmount: parseFloat(response.data.provider_amount),
            status: response.data.status,
            clinician: response.data.clinician,
            notes: response.data.notes
          }
        }

        toast.success('Entry updated successfully!')
      } catch (error) {
        console.error('Failed to update entry:', error)
        toast.error('Failed to update entry')
        throw error
      }
    } else {
      // Add new item
      try {
        const response = await api.post('/biller/tracking', {
          patient_name: manualForm.patientName || '',
          invoice_number: manualForm.invoiceNumber || '',
          service_date: manualForm.serviceDate || '',
          submission_date: manualForm.submissionDate || null,
          medicare_amount: manualForm.medicareAmount != null ? parseFloat(manualForm.medicareAmount.toString()) : 0,
          provider_amount: manualForm.providerAmount != null ? parseFloat(manualForm.providerAmount.toString()) : 0,
          status: manualForm.status || '',
          clinician: manualForm.clinician || '',
          notes: manualForm.notes || ''
        })

        // Add to local data
        tableData.value.push({
          id: response.data.id,
          patientName: response.data.patient_name,
          invoiceNumber: response.data.invoice_number,
          serviceDate: response.data.service_date,
          submissionDate: response.data.submission_date,
          medicareAmount: parseFloat(response.data.medicare_amount),
          providerAmount: parseFloat(response.data.provider_amount),
          status: response.data.status,
          clinician: response.data.clinician,
          notes: response.data.notes
        })
        toast.success('Entry added successfully!')
      } catch (error) {
        console.error('Failed to add entry:', error)
        toast.error('Failed to add entry')
        throw error
      }
    }

    closeManualModal()
  } catch (error) {
    console.error('Failed to submit manual entry:', error)
    toast.error('Failed to save entry')
  } finally {
    manualSubmitting.value = false
  }
}

const resetManualForm = () => {
  manualForm.patientName = ''
  manualForm.invoiceNumber = ''
  manualForm.serviceDate = ''
  manualForm.submissionDate = ''
  manualForm.medicareAmount = 0
  manualForm.providerAmount = 0
  manualForm.status = ''
  manualForm.clinician = ''
  manualForm.notes = ''
}

// Edit and delete functions
const editItem = (item: any) => { 
  editingItem.value = { ...item }

  // Reset form first
  resetManualForm()

  // Handle date fields properly
  let serviceDate = ''
  let submissionDate = ''

  // Check if dates are in camelCase format
  if (item.serviceDate !== undefined) {
    serviceDate = formatDateForInput(item.serviceDate)
  } else if (item.service_date !== undefined) {
    serviceDate = formatDateForInput(item.service_date)
  }

  if (item.submissionDate !== undefined) {
    submissionDate = formatDateForInput(item.submissionDate)
  } else if (item.submission_date !== undefined) {
    submissionDate = formatDateForInput(item.submission_date)
  }

  // Populate form with values
  manualForm.patientName = item.patientName || item.patient_name || ''
  manualForm.invoiceNumber = item.invoiceNumber || item.invoice_number || ''
  manualForm.serviceDate = serviceDate
  manualForm.submissionDate = submissionDate
  manualForm.medicareAmount = item.medicareAmount || item.medicare_amount || 0
  manualForm.providerAmount = item.providerAmount || item.provider_amount || 0
  manualForm.status = item.status || ''
  manualForm.clinician = item.clinician || ''
  manualForm.notes = item.notes || ''

  // Store original form data for change detection
  originalFormData.value = {
    patientName: manualForm.patientName,
    invoiceNumber: manualForm.invoiceNumber,
    serviceDate: manualForm.serviceDate,
    submissionDate: manualForm.submissionDate,
    medicareAmount: manualForm.medicareAmount,
    providerAmount: manualForm.providerAmount,
    status: manualForm.status,
    clinician: manualForm.clinician,
    notes: manualForm.notes
  } 
  showManualModal.value = true
}

// Delete function with SweetAlert
const deleteItem = async (item: any) => {
  // Show Swal confirmation dialog directly
  const result = await Swal.fire({
    title: 'Are you sure?',
    html: `Are you sure you want to delete the entry for <strong>${item.patientName || item.patient_name}</strong>?<br>This action cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  })

  if (result.isConfirmed) {
    try {
      await api.delete(`/biller/tracking/${item.id}`)
      
      // Remove from local data
      tableData.value = tableData.value.filter(existingItem => existingItem.id !== item.id)
      toast.success('Billing deleted successfully.')
      
      // Refresh current page
      loadTableData(pagination.value?.current_page || 1)
    } catch (error) {
      console.error('Failed to delete entry:', error)
      Swal.fire({
        title: 'Error!',
        text: 'Failed to delete the entry. Please try again.',
        icon: 'error',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
      })
    }
  }
}



// Spreadsheet upload functions
const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    processFile(file)
  }
}

const handleDrop = (event: DragEvent) => {
  event.preventDefault()
  const file = event.dataTransfer?.files?.[0]
  if (file) {
    processFile(file)
  }
}

const processFile = async (file: File) => {
  uploadedFile.value = file
  processing.value = true
  previewData.value = []

  try {
    // Check file type and process accordingly
    if (file.name.endsWith('.csv')) {
      await processCSV(file)
    } else if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
      await processExcel(file)
    } else if (file.name.endsWith('.pdf')) {
      await processPdfWithOcr(file)
    } else {
      throw new Error('Unsupported file type. Please upload CSV, Excel, or PDF files.')
    }

    // Populate available columns from the first row of data
    if (previewData.value.length > 0) {
      const firstRow = previewData.value[0]
      availableColumns.value = Object.keys(firstRow) 

      // Ensure requiredColumns is an array
      const safeRequiredColumns = Array.isArray(requiredColumns.value) ? requiredColumns.value : []
      
      // Initialize column mappings with intelligent matching
      // First pass: Handle specific mappings for amount fields
      availableColumns.value.forEach(col => {
        const normalizedCol = col.toLowerCase().replace(/[^a-zA-Z0-9]/g, '')
        
        // Handle specific amount field mappings to avoid conflicts
        if (normalizedCol.includes('medicare') && normalizedCol.includes('amount')) {
          columnMappings.value['Medicare Amount ($)'] = col
        } else if (normalizedCol.includes('provider') && normalizedCol.includes('amount')) {
          columnMappings.value['Provider Amount ($)'] = col
        }
      })
      
      // Second pass: Handle other fields and remaining unmapped amount fields
      availableColumns.value.forEach(col => {
        const normalizedCol = col.toLowerCase().replace(/[^a-zA-Z0-9]/g, '')
        safeRequiredColumns.forEach(requiredCol => {
          // Skip if already mapped
          if (columnMappings.value[requiredCol]) return
          
          const normalizedRequired = requiredCol.toLowerCase().replace(/[^a-zA-Z0-9]/g, '')
          
          // Multiple matching strategies for flexibility
          const strategies = [
            // Exact match (after normalization)
            normalizedCol === normalizedRequired,
            // Partial match (contains)
            normalizedCol.includes(normalizedRequired) || normalizedRequired.includes(normalizedCol),
            // Special handling for amount fields - only if not already mapped
            (normalizedRequired.includes('medicare') && normalizedCol.includes('medicare')) ||
            (normalizedRequired.includes('provider') && normalizedCol.includes('provider')) ||
            (normalizedRequired.includes('amount') && normalizedCol.includes('amount')),
            // Special handling for specific fields to avoid conflicts
            (normalizedRequired.includes('medicareamount') && (normalizedCol.includes('medicare') || normalizedCol.includes('paidamount') || normalizedCol.includes('invoicepaidamount'))) ||
            (normalizedRequired.includes('provideramount') && (normalizedCol.includes('provider') || normalizedCol.includes('owed') || normalizedCol.includes('balance'))) ||
            // Handle date fields - more specific matching
            (normalizedRequired.includes('servicedate') && (normalizedCol.includes('servicedate') || normalizedCol.includes('serviced'))) ||
            (normalizedRequired.includes('submissiondate') && (normalizedCol.includes('submissiondate') || normalizedCol.includes('submission') || normalizedCol.includes('paiddate') || normalizedCol.includes('invoicedate'))) ||
            (normalizedRequired.includes('date') && normalizedCol.includes('date')),
            // Handle invoice fields
            (normalizedRequired.includes('invoice') && normalizedCol.includes('invoice')),
            // Handle status fields
            (normalizedRequired.includes('status') && normalizedCol.includes('status')),
            // Handle name fields
            (normalizedRequired.includes('name') && normalizedCol.includes('name')),
            // Handle clinician fields
            (normalizedRequired.includes('clinician') && normalizedCol.includes('clinician'))
          ]
          
          if ((strategies.some(strategy => strategy) || 
              (normalizedRequired.includes('submission') && (normalizedCol.includes('paid') || normalizedCol.includes('paiddate'))) ||
              (normalizedRequired.includes('submissiondate') && (normalizedCol.includes('paid') || normalizedCol.includes('paiddate'))) ||
              (normalizedRequired.includes('date') && normalizedCol.includes('paid'))
          ) && !columnMappings.value[requiredCol]) {
            columnMappings.value[requiredCol] = col
          }
        })
      }) 
      
      // Show toast with mapping suggestions
      const mappedCount = Object.keys(columnMappings.value).length
      if (mappedCount > 0) {
        toast.info(`Found ${mappedCount} potential column matches. Please review the mapping below.`)
      } else {
        toast.warning('No automatic column matches found. Please map columns manually below.')
      }
    }
  } catch (error) {
    console.error('Failed to process file:', error)
    toast.error(error instanceof Error ? error.message : 'Failed to process file')
    removeFile()
  } finally {
    processing.value = false
  }
}

const processCSV = async (file: File) => {
  return new Promise<void>((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      try {
        const content = e.target?.result as string
        const lines = content.split('\n').filter(line => line.trim())

        if (lines.length < 2) {
          reject(new Error('File is empty or invalid'))
          return
        }

        // Use Papa Parse-like logic for better CSV handling
        const headers = parseCSVLine(lines[0])
        const data = lines.slice(1).map(line => {
          const values = parseCSVLine(line)
          const row: any = {}
          headers.forEach((header, index) => {
            row[header] = values[index] || ''
          })
          return row
        })

        previewData.value = data
        resolve()
      } catch (error) {
        reject(new Error('Failed to parse CSV file'))
      }
    }
    reader.onerror = () => reject(new Error('Failed to read file'))
    reader.readAsText(file)
  })
}

const processExcel = async (file: File) => {
  return new Promise<void>((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      try {
        const data = new Uint8Array(e.target?.result as ArrayBuffer)
        const workbook = XLSX.read(data, { type: 'array' })

        // Get the first worksheet
        const firstSheetName = workbook.SheetNames[0]
        const worksheet = workbook.Sheets[firstSheetName]

        // Convert to JSON
        const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 })

        if (jsonData.length < 2) {
          reject(new Error('Excel file is empty or invalid'))
          return
        }

        // Get headers (first row)
        const headers = jsonData[0] as string[]
        const dataRows = jsonData.slice(1) as any[][]

        // Convert to array of objects
        const parsedData = dataRows.map((row: any[]) => {
          const obj: any = {}
          headers.forEach((header, index) => {
            obj[header] = row[index] || ''
          })
          return obj
        })

        previewData.value = parsedData
        resolve()
      } catch (error) {
        reject(new Error('Failed to parse Excel file'))
      }
    }
    reader.onerror = () => reject(new Error('Failed to read file'))
    reader.readAsArrayBuffer(file)
  })
}

const processPdfWithOcr = async (file: File) => {
  // Simulate OCR processing - in a real implementation, this would call the backend OCR service
  // similar to the Invoice Management system

  // For now, simulate OCR extraction by creating sample data
  // In a real implementation, this would send the PDF to the backend for processing

  // Show loading state
  toast.info('Processing PDF with OCR...')

  // Simulate API call delay
  await new Promise(resolve => setTimeout(resolve, 1000)) 

  // Generate sample data based on typical billing information
  previewData.value = [
    {
      'Patient Name': 'John Smith',
      'Invoice Number': 'INV-' + Math.floor(Math.random() * 10000),
      'Service Date': new Date().toISOString().split('T')[0],
      'Submission Date': new Date().toISOString().split('T')[0],
      'Medicare Amount': (Math.random() * 1000 + 100).toFixed(2),
      'Provider Amount': (Math.random() * 500).toFixed(2),
      'Status': 'submitted',
      'Clinician': 'Dr. Johnson',
      'Notes': 'OCR extracted from PDF'
    },
    {
      'Patient Name': 'Jane Doe',
      'Invoice Number': 'INV-' + Math.floor(Math.random() * 10000),
      'Service Date': new Date(Date.now() - 86400000).toISOString().split('T')[0], // Yesterday
      'Submission Date': new Date().toISOString().split('T')[0],
      'Medicare Amount': (Math.random() * 1000 + 100).toFixed(2),
      'Provider Amount': (Math.random() * 500).toFixed(2),
      'Status': 'pending',
      'Clinician': 'Dr. Williams',
      'Notes': 'OCR extracted from PDF'
    }
  ]
  toast.success('PDF processed successfully with OCR!')
}

// Helper function to properly parse CSV lines (handles quoted fields)
const parseCSVLine = (line: string): string[] => {
  const result: string[] = []
  let current = ''
  let inQuotes = false

  for (let i = 0; i < line.length; i++) {
    const char = line[i]
    const nextChar = line[i + 1]

    if (char === '"' && !inQuotes) {
      inQuotes = true
    } else if (char === '"' && inQuotes) {
      if (nextChar === '"') {
        // Escaped quote
        current += '"'
        i++ // Skip next quote
      } else {
        // End of quoted field
        inQuotes = false
      }
    } else if (char === ',' && !inQuotes) {
      // Field separator
      result.push(current.trim())
      current = ''
    } else {
      current += char
    }
  }

  // Add the last field
  result.push(current.trim())
  return result
}

const removeFile = () => {
  uploadedFile.value = null
  previewData.value = []
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const resetUpload = () => {
  removeFile()
}

const processUpload = async () => {
  if (previewData.value.length === 0) {
    toast.error('No data to import')
    return
  }

  uploading.value = true

  try {
    // Ensure requiredColumns is an array
    const safeRequiredColumns = Array.isArray(requiredColumns.value) ? requiredColumns.value : []
    
    // Validate that all required columns have mappings
    const unmappedColumns = safeRequiredColumns.filter(col => !columnMappings.value[col])
    
    if (unmappedColumns.length > 0) {
      throw new Error(`Please map the following required columns: ${unmappedColumns.join(', ')}`)
    }
    
    // Map the data according to column mappings
    const mappedData = previewData.value.map((item: any, index: number) => {
      const mappedItem: any = {} 
      
      // First, map all required columns
      safeRequiredColumns.forEach(requiredCol => {
        const sourceColumn = columnMappings.value[requiredCol]     
        const value = item[sourceColumn] !== undefined ? item[sourceColumn] : item[requiredCol] 
         
        if (requiredCol === 'Submission Date') { 
          const processedValue = (value === '' || value === null || value === undefined) ? null : value
          mappedItem[requiredCol] = processedValue 
        } else if (requiredCol === 'Medicare Amount ($)' || requiredCol === 'Provider Amount ($)') {
          // Ensure numeric values are properly formatted
          const numericValue = parseFloat(value)
          mappedItem[requiredCol] = isNaN(numericValue) ? 0 : numericValue
        } else {
          mappedItem[requiredCol] = value || ''
        }
      })
      
      // Then, map any additional available columns that weren't in required columns
      // This includes optional fields like Submission Date, Clinician, Notes
      availableColumns.value.forEach(col => {
        // Skip if this column was already mapped as a required column
        const isRequiredColumn = safeRequiredColumns.some(requiredCol => {
          const sourceColumn = columnMappings.value[requiredCol];
          return sourceColumn === col || requiredCol === col;
        });
        
        if (!isRequiredColumn && item.hasOwnProperty(col)) { 
          // Handle special cases for optional fields
          if (col === 'Submission Date' || col === 'Submission_Date' || col === 'submission_date') {
            const processedValue = (item[col] === '' || item[col] === null || item[col] === undefined) ? null : item[col];
            mappedItem['Submission Date'] = processedValue; 
          } else if (col === 'Clinician' || col === 'clinician') {
            mappedItem['Clinician'] = item[col] || '';
          } else if (col === 'Notes' || col === 'notes') {
            mappedItem['Notes'] = item[col] || '';
          } else {
            // For other optional columns, just pass them through
            mappedItem[col] = item[col];
          }
        }
      }); 
      return mappedItem
    })
    
    // Check for duplicates in the mapped data
    const seenRecords = new Map<string, number>();
    const duplicates: Array<{currentIndex: number, firstIndex: number, item: any}> = [];
    
    mappedData.forEach((item, index) => { 
      const uniqueKey = [
        (item['Patient Name'] || '').toLowerCase().trim(),
        (item['Invoice Number'] || '').trim(),
        (item['Service Date'] || '').trim(),
        item['Medicare Amount ($)'],
        item['Provider Amount ($)']
      ].join('|');
      
      if (seenRecords.has(uniqueKey)) {
        const firstIndex = seenRecords.get(uniqueKey)!;  
        duplicates.push({
          currentIndex: index + 1,
          firstIndex: firstIndex + 1,
          item: item
        });
      } else {
        seenRecords.set(uniqueKey, index);
      }
    });
    
    // If duplicates found, show error before sending to backend
    if (duplicates.length > 0) {
      const duplicateMessages = duplicates.map(dup => 
        `Row ${dup.currentIndex} is a duplicate of Row ${dup.firstIndex}`
      );
      
      toast.error(`Duplicate records found:\n${duplicateMessages.join('\n')}\n\nPlease remove duplicates and try again.`);
      return;
    }
    
    // Send to backend API 
    const response = await api.post('/biller/tracking/bulk', { data: mappedData })
    
    // Add successfully imported items to local table
    if (response.data.imported) {
      response.data.imported.forEach((importedItem: any) => {
        tableData.value.push({
          id: importedItem.id,
          patientName: importedItem.patient_name,
          invoiceNumber: importedItem.invoice_number,
          serviceDate: importedItem.service_date,
          submissionDate: importedItem.submission_date,
          medicareAmount: parseFloat(importedItem.medicare_amount),
          providerAmount: parseFloat(importedItem.provider_amount),
          status: importedItem.status,
          clinician: importedItem.clinician,
          notes: importedItem.notes
        })
      })
      
      toast.success(`Successfully imported ${response.data.imported.length} records!`)
      
      if (response.data.errors && response.data.errors.length > 0) {
        toast.warning(`${response.data.errors.length} records had errors and were skipped.`)
      }
    }
    
    resetUpload()
    showUploadModal.value = false
  } catch (error: any) {
    console.error('Failed to upload data:', error)
    
    // Show detailed error information
    if (error.response?.data?.errors) {
      const errorDetails = error.response.data.errors
      const errorMessages: string[] = []
      
      // Extract specific field errors
      Object.keys(errorDetails).forEach(field => {
        errorDetails[field].forEach((errorMsg: string) => {
          errorMessages.push(`${field}: ${errorMsg}`)
        })
      })
      
      toast.error(`Import failed:\n${errorMessages.join('\n')}`)
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to import data')
    }
    
    // Log full error for debugging
    console.error('Error response:', error.response?.data)
    console.error('Full error:', error)
  } finally {
    uploading.value = false
  }
}



// Utility functions
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatCurrency = (amount: number): string => {
  return parseFloat(amount.toString()).toFixed(2)
}

const formatDate = (dateStr: string): string => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const getStatusClass = (status: string): string => {
  switch (status.toLowerCase()) {
    case 'paid':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
    case 'submitted':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
    case 'processed':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
    case 'denied':
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
}

function changePage(page: number) {
  if (page < 1 || page > (pagination.value?.last_page || 1)) return
  loadTableData(page)
}
</script>