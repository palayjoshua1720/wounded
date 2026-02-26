<template>
  <div>
    <!-- Choice Modal: Upload vs Manual -->
    <BaseModal v-if="isOpen && !entryMethodSelected" v-model="choiceModalOpen" title="Process New Return">
      <div class="space-y-4">
        <p class="text-gray-600 dark:text-gray-400">Choose how you want to process the return:</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
          <button @click="selectEntryMethod(false)"
            class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors">
            <UploadCloud class="w-12 h-12 text-blue-600 dark:text-blue-400 mb-3" />
            <span class="font-medium text-gray-900 dark:text-white text-lg">Upload Return Document</span>
            <span class="text-sm text-gray-500 dark:text-gray-400 mt-2 text-center">Upload document with OCR
              extraction</span>
          </button>

          <button @click="selectEntryMethod(true)"
            class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-gray-700 transition-colors">
            <Package class="w-12 h-12 text-purple-600 dark:text-purple-400 mb-3" />
            <span class="font-medium text-gray-900 dark:text-white text-lg">Manual Entry</span>
            <span class="text-sm text-gray-500 dark:text-gray-400 mt-2 text-center">Enter return details manually</span>
          </button>
        </div>
      </div>

      <template #actions>
        <div class="flex justify-end w-full p-5">
          <button @click="closeModal"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            Cancel
          </button>
        </div>
      </template>
    </BaseModal>

    <!-- Upload Document Modal -->
    <BaseModal v-if="isOpen && !manualEntry && entryMethodSelected" v-model="showUploadModal"
      title="Upload Return Document" width="max-w-4xl">
      <div class="space-y-6">
        <!-- Brand and Return Reason Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Brand *</label>
            <select v-model="selectedBrand"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              required>
              <option value="">Select Brand</option>
              <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                {{ brand.name }} - {{ getManufacturerName(brand.manufacturerId) }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Return Reason *</label>
            <select v-model="returnReason"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              required>
              <option value="">Select Reason</option>
              <option v-for="reason in returnReasons" :key="reason" :value="reason">{{ reason }}</option>
            </select>
          </div>
        </div>

        <!-- Other Reason Text Field -->
        <div v-if="returnReason === 'Other'" class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Please specify the reason
            *</label>
          <textarea v-model="otherReturnReason" rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Please provide details about the return reason..." required></textarea>
        </div>

        <!-- Upload Document Section -->
        <div
          class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center bg-gray-50 dark:bg-gray-700/30">
          <Upload class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
          <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Return Label or Photo</h4>
          <p class="text-gray-600 dark:text-gray-400 mb-6">
            System will automatically extract serial number, expiry date, and size
          </p>
          <label
            class="cursor-pointer inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md font-medium">
            <UploadCloud class="w-5 h-5 mr-2" />
            <span>Choose File</span>
            <input type="file" accept="image/*,.pdf" @change="handleFileUpload" class="hidden" />
          </label>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
            Supports images (JPG, PNG) and PDF files
          </p>
          <div v-if="ocrLoading" class="mt-6 flex items-center justify-center text-blue-600 dark:text-blue-400">
            <RefreshCcw class="w-5 h-5 mr-2 animate-spin" />
            Extracting text from image...
          </div>

          <!-- OCR Result Quick View -->
          <div v-if="ocrResult && !ocrLoading"
            class="mt-6 bg-blue-50 dark:bg-blue-900/20 p-5 rounded-xl border border-blue-200 dark:border-blue-700">
            <div class="text-left">
              <div class="font-bold text-blue-800 dark:text-blue-200 text-base mb-2">OCR Result</div>
              <pre
                class="whitespace-pre-wrap text-sm text-blue-900 dark:text-blue-100 bg-blue-100 dark:bg-blue-900/40 rounded p-3 border border-blue-200 dark:border-blue-800 max-h-32 overflow-y-auto">
            {{ ocrResult }}</pre>
              <div class="mt-4 flex gap-3 justify-end">
                <button @click="showOcrModal = true"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                  Review & Edit
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #actions>
        <div class="flex justify-between gap-3 px-6 py-4">
          <button type="button" @click="backToChoice"
            class="px-6 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            ← Back
          </button>
          <button type="button" @click="closeModal"
            class="px-6 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            Cancel
          </button>
        </div>
      </template>
    </BaseModal>

    <!-- Manual Entry Modal -->
    <BaseModal v-if="isOpen && manualEntry && entryMethodSelected" v-model="showManualModal" title="Manual Return Entry"
      width="max-w-5xl">
      <div class="space-y-5 py-4">
        <!-- Step 1: Manufacturer Selection (filters everything) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Step 1: Select Manufacturer
            *</label>
          <div class="relative">
            <input v-model="manufacturerSearch" @input="filterManufacturers" @focus="showManufacturerDropdown = true"
              type="text"
              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Search manufacturer..." required autocomplete="off" />
            <!-- Manufacturer Dropdown -->
            <div v-if="showManufacturerDropdown && filteredManufacturers.length > 0"
              class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
              <div v-for="mfr in filteredManufacturers" :key="mfr.id" @click="selectManufacturer(mfr)"
                class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-gray-900 dark:text-white text-sm">
                {{ mfr.name }}
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Brand and Graft Size Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Step 2: Select Brand
              *</label>
            <div class="relative">
              <input v-model="brandSearch" @input="filterBrands" @focus="showBrandDropdown = true" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                placeholder="Search brand..." required autocomplete="off" />
              <!-- Brand Dropdown List -->
              <div v-if="showBrandDropdown && filteredBrands.length > 0"
                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                <div v-for="brand in filteredBrands" :key="brand.id" @click="selectBrand(brand)"
                  class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-gray-900 dark:text-white text-sm">
                  {{ brand.name }}
                </div>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Step 3: Select Graft Size
              *</label>
            <div class="relative">
              <input v-model="graftSizeSearch" @input="filterGraftSizes" @focus="showGraftSizeDropdown = true"
                type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                placeholder="Search graft size..." required autocomplete="off" />
              <!-- Graft Size Dropdown List -->
              <div v-if="showGraftSizeDropdown && filteredGraftSizes.length > 0"
                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                <div v-for="graftSize in filteredGraftSizes" :key="graftSize.id" @click="selectGraftSize(graftSize)"
                  class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-gray-900 dark:text-white text-sm">
                  {{ graftSize.size }} ({{ graftSize.area }} cm²)
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Return Reason -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Step 4: Return Reason
              *</label>
            <select v-model="returnReason"
              class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              required>
              <option value="">Select Reason</option>
              <option v-for="reason in returnReasons" :key="reason" :value="reason">{{ reason }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Link to Usage Log *</label>
            <div class="relative" ref="graftLogDropdownContainer">
              <input
                v-model="graftLogSearch"
                @input="filterGraftLogs"
                @focus="showGraftLogDropdown = true"
                @keydown.enter.prevent="acceptFirstGraftLog"
                type="text"
                autocomplete="off"
                placeholder="Search by usage log ID..."
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                required
                aria-label="Search usage log"
              />

              <!-- Dropdown rendered via teleport to avoid modal overflow issues -->
              <Teleport to="body" v-if="showGraftLogDropdown && filteredGraftLogs.length > 0">
                <div
                  ref="graftLogDropdownEl"
                  class="fixed z-[9999] bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                  :style="graftLogDropdownStyle"
                >
                  <div v-for="log in filteredGraftLogs" :key="log.id" @click="selectGraftLog(log)"
                    class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-gray-900 dark:text-white text-sm">
                    <div class="font-mono">{{ log.id }}</div>
                  </div>
                </div>
              </Teleport>
            </div>
            <input type="hidden" v-model="graftLogId" />
          </div>
        </div>

        <!-- Other Reason Text Field -->
        <div v-if="returnReason === 'Other'">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Please specify the reason
            *</label>
          <textarea v-model="otherReturnReason" rows="3"
            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Please provide details about the return reason..." required></textarea>
        </div>
      </div>

      <template #actions>
        <div class="flex justify-between gap-3 px-6 py-4">
          <button type="button" @click="backToChoice"
            class="px-6 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            ← Back
          </button>
          <div class="flex gap-3">
            <button type="button" @click="closeModal"
              class="px-6 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
              Cancel
            </button>
            <button type="button" @click="handleManualSubmit"
              class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium">
              Submit Return
            </button>
          </div>
        </div>
      </template>
    </BaseModal>

    <!-- OCR Modal -->
    <BaseModal v-model="showOcrModal" title="OCR Extraction Review" width="max-w-4xl">
      <div class="space-y-6">
        <!-- Image Preview -->
        <div v-if="uploadedImagePreview" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Uploaded Image</h3>
          <img :src="uploadedImagePreview" alt="Preview"
            class="max-h-48 mx-auto rounded-lg border border-gray-300 dark:border-gray-600" />
        </div>

        <!-- Recognized Text -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Recognized Text (OCR)</h3>
          <pre
            class="whitespace-pre-wrap text-xs bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-300 font-mono max-h-48 overflow-y-auto">
        {{ ocrResult }}</pre>
        </div>

        <!-- Mapped Fields -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Extracted Fields (Editable)</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Product Brand *</label>
              <select v-model="ocrSelectedBrand"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Select Brand</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Return Reason *</label>
              <select v-model="ocrReturnReason"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Select Reason</option>
                <option v-for="reason in returnReasons" :key="reason" :value="reason">{{ reason }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Serial Number</label>
              <input v-model="ocrSerialNumber"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Product Code</label>
              <input v-model="ocrProductCode"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Expiry Date</label>
              <input v-model="ocrExpiryDate"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Size</label>
              <input v-model="ocrSize"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
            </div>
          </div>

          <!-- Other Reason Text Field for OCR -->
          <div v-if="ocrReturnReason === 'Other'" class="mt-4">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Please specify the reason
              *</label>
            <textarea v-model="ocrOtherReason" rows="3"
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
              placeholder="Please provide details about the return reason..." required></textarea>
          </div>
        </div>
      </div>
      <template #actions>
        <div class="flex justify-end gap-3 px-6 py-4">
          <button @click="showOcrModal = false"
            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            Close
          </button>
          <button @click="confirmOcrReturn"
            class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium">
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Confirm & Add Return
            </span>
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { Upload, Camera, Package, RefreshCcw, UploadCloud } from 'lucide-vue-next'
import Tesseract from 'tesseract.js'

interface Props {
  modelValue: boolean
  brands: Brand[]
  manufacturers: Manufacturer[]
  graftSizes: GraftSize[]
  usageLogs?: { id: string; reference: string }[]
}

interface Brand {
  id: string
  name: string
  manufacturerId: string
}

interface Manufacturer {
  id: string
  name: string
}

interface GraftSize {
  id: string
  size: string
  area: number
  brandId: string
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'submit-return': [data: any]
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const choiceModalOpen = computed({
  get: () => props.modelValue && !entryMethodSelected.value,
  set: (value) => {
    if (!value) {
      emit('update:modelValue', false)
    }
  }
})

// Modal state management
const entryMethodSelected = ref(false)
const manualEntry = ref(false)
const showUploadModal = ref(false)
const showManualModal = ref(false)

// Form fields
const selectedBrand = ref('')
const brandSearch = ref('')
const showBrandDropdown = ref(false)
const filteredBrands = ref<Brand[]>([])
const returnReason = ref('')
const otherReturnReason = ref('')
const selectedGraftSize = ref('')
const graftSizeSearch = ref('')
const showGraftSizeDropdown = ref(false)
const filteredGraftSizes = ref<GraftSize[]>([])
const manufacturer = ref('')
const manufacturerId = ref('')
const manufacturerSearch = ref('')
const showManufacturerDropdown = ref(false)
const filteredManufacturers = ref<Manufacturer[]>([])
const graftLogId = ref('') // Optional link to usage log
// Searchable usage log dropdown
const graftLogSearch = ref('')
const showGraftLogDropdown = ref(false)
const filteredGraftLogs = ref<{ id: string; reference: string }[]>([])
const graftLogDropdownContainer = ref<HTMLElement | null>(null)
const graftLogDropdownEl = ref<HTMLElement | null>(null)

// Dropdown positioning style
const graftLogDropdownStyle = computed(() => {
  if (!graftLogDropdownContainer.value) return {}
  const rect = graftLogDropdownContainer.value.getBoundingClientRect()
  return {
    top: `${rect.bottom + 4}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    maxHeight: '240px'
  }
})

// OCR fields
const ocrLoading = ref(false)
const ocrResult = ref('')
const uploadedImagePreview = ref<string | null>(null)
const ocrExtracted = ref<{
  serialNumber: string
  productCode: string
  expiryDate: string
  size: string
  manufacturer: string
} | null>(null)
const showOcrModal = ref(false)

// OCR form fields
const ocrSelectedBrand = ref('')
const ocrReturnReason = ref('')
const ocrOtherReason = ref('')
const ocrSerialNumber = ref('')
const ocrProductCode = ref('')
const ocrExpiryDate = ref('')
const ocrSize = ref('')

const returnReasons = [
  'Expired Product',
  'Damaged in Transit',
  'Wrong Size Delivered',
  'Unused - Patient Cancelled',
  'Quality Issue',
  'Overstock',
  'Other'
]

function getManufacturerName(manufacturerId: string) {
  return props.manufacturers.find(m => m.id === manufacturerId)?.name || 'Unknown Manufacturer'
}

function getBrandById(id: string) {
  return props.brands.find(b => b.id === id)
}

function getManufacturerById(id: string) {
  return props.manufacturers.find(m => m.id === id)
}

function filterBrands() {
  const search = brandSearch.value.toLowerCase()

  // Filter brands by manufacturer first, then by search term
  let brandsToFilter = props.brands
  if (manufacturerId.value) {
    brandsToFilter = props.brands.filter(b => b.manufacturerId === manufacturerId.value)
  }

  if (search) {
    filteredBrands.value = brandsToFilter.filter(b =>
      b.name.toLowerCase().includes(search)
    )
  } else {
    filteredBrands.value = brandsToFilter
  }
  showBrandDropdown.value = true
  // Removed positioning for now since we're using absolute positioning
}

function selectBrand(brand: Brand) {
  selectedBrand.value = brand.id
  brandSearch.value = brand.name
  showBrandDropdown.value = false
  
  // Clear graft size selection when brand changes
  selectedGraftSize.value = ''
  graftSizeSearch.value = ''
  
  // Filter graft sizes based on selected brand
  filterGraftSizesByBrand(brand.id)
}

function filterGraftSizesByBrand(brandId: string) {
  filteredGraftSizes.value = props.graftSizes.filter(gs => gs.brandId === brandId)
}

function filterGraftSizes() {
  const search = graftSizeSearch.value.toLowerCase()
  const brandGraftSizes = selectedBrand.value
    ? props.graftSizes.filter(gs => gs.brandId === selectedBrand.value)
    : props.graftSizes

  if (search) {
    filteredGraftSizes.value = brandGraftSizes.filter(gs =>
      gs.size.toLowerCase().includes(search) ||
      gs.area.toString().includes(search)
    )
  } else {
    filteredGraftSizes.value = brandGraftSizes
  }
  showGraftSizeDropdown.value = true
  // Removed positioning for now since we're using absolute positioning
}

function selectGraftSize(graftSize: GraftSize) {
  selectedGraftSize.value = graftSize.id
  graftSizeSearch.value = `${graftSize.size} (${graftSize.area} cm²)`
  showGraftSizeDropdown.value = false
}

function filterManufacturers() {
  const search = manufacturerSearch.value.toLowerCase()
  if (search) {
    filteredManufacturers.value = props.manufacturers.filter(m =>
      m.name.toLowerCase().includes(search)
    )
  } else {
    filteredManufacturers.value = props.manufacturers
  }
  showManufacturerDropdown.value = true
  // Removed positioning for now since we're using absolute positioning
}

function selectManufacturer(mfr: Manufacturer) {
  manufacturerId.value = mfr.id
  manufacturer.value = mfr.name
  manufacturerSearch.value = mfr.name
  showManufacturerDropdown.value = false
  
  // Clear brand and graft size selections when manufacturer changes
  selectedBrand.value = ''
  brandSearch.value = ''
  selectedGraftSize.value = ''
  graftSizeSearch.value = ''
  
  // Filter brands based on selected manufacturer
  filterBrands()
}


function filterGraftLogs() {
  const q = graftLogSearch.value.trim().toLowerCase()
  const source = props.usageLogs || []
  if (!q) {
    filteredGraftLogs.value = source.slice(0, 50)
  } else {
    filteredGraftLogs.value = source.filter(l =>
      l.id.toLowerCase().includes(q)
    ).slice(0, 50)
  }
  showGraftLogDropdown.value = true
  // Removed positioning for now since we're using absolute positioning
}

function selectGraftLog(log: { id: string; reference: string }) {
  graftLogId.value = log.id
  graftLogSearch.value = log.reference
  showGraftLogDropdown.value = false
}

// Dropdown positioning function removed - using absolute positioning

function acceptFirstGraftLog() {
  if (filteredGraftLogs.value.length > 0) {
    selectGraftLog(filteredGraftLogs.value[0])
    return
  }

  const q = graftLogSearch.value.trim()
  if (!q) return

  const match = (props.usageLogs || []).find(l =>
    l.id === q || l.reference.toLowerCase() === q.toLowerCase()
  )

  if (match) {
    selectGraftLog(match)
  } else {
    // fallback: allow entering raw id
    graftLogId.value = q
    showGraftLogDropdown.value = false
  }
}

async function handleFileUpload(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  ocrLoading.value = true
  ocrResult.value = ''
  ocrExtracted.value = null

  // Create preview URL for image (not saved, just for display)
  if (file.type.startsWith('image/')) {
    uploadedImagePreview.value = URL.createObjectURL(file)
    console.log('Image preview URL created:', uploadedImagePreview.value)
  }

  try {
    const { data } = await Tesseract.recognize(file, 'eng')
    ocrResult.value = data.text

    // Serial Number / ID (labelled or unlabelled, prefix-agnostic)
    const serialMatch =
      // ID / Code labelled (e.g. Allograft ID: RT24 10222-027)
      ocrResult.value.match(
        /\b(?:ID|CODE|NO|NUMBER)\s*[:#-]?\s*([A-Z0-9][A-Z0-9\s-]{6,})\b/i
      ) ||

      // Complex structured serials (e.g. SB24-2134AQ-00214740, RT24 10222-027)
      ocrResult.value.match(
        /\b[A-Z0-9]{2,6}[-\s]\d{2,6}[A-Z0-9]{0,4}-\d{3,10}\b/i
      ) ||

      // Compact alphanumeric serials (e.g. SN123456, AB12345678)
      ocrResult.value.match(
        /\b[A-Z]{2,5}\d{6,10}\b/i
      );

    // Product Code: Look for patterns like AMQ-5220 or Product Code: XXX
    const productCodeMatch = ocrResult.value.match(/Product\s*Code\s*[:#-]?\s*([A-Z0-9-]+)/i) ||
      ocrResult.value.match(/[A-Z]{2,3}-\d{4}/)

    // Expiry Date: Look for patterns like 2027-03-19 or Expires: YYYY-MM-DD
    const expiryMatch = ocrResult.value.match(/Expires?\s*[:#-]?\s*(\d{4}-\d{2}-\d{2})/i) ||
      ocrResult.value.match(/\b(20\d{2}-\d{2}-\d{2})\b/) ||
      ocrResult.value.match(/Expiry\s*[:#-]?\s*(\d{4}-\d{2}-\d{2})/i)

    // Size: Look for patterns like 2cm x 2cm, 2cmx2cm, 2x2cm, 16mm, etc.
    const sizeMatch = ocrResult.value.match(/Size[:\s]*[:#-]?\s*(\d+(?:\.\d+)?\s*(?:cm|mm)\s*[xX×]\s*\d+(?:\.\d+)?\s*(?:cm|mm))/i) ||
      ocrResult.value.match(/(\d+(?:\.\d+)?\s*(?:cm|mm)\s*[xX×]\s*\d+(?:\.\d+)?\s*(?:cm|mm))/i) ||
      ocrResult.value.match(/(\d+(?:\.\d+)?(?:cm|mm)[xX×]\d+(?:\.\d+)?(?:cm|mm))/i) ||
      ocrResult.value.match(/Size[:\s]*[:#-]?\s*(\d+(?:\.\d+)?\s*[xX×]\s*\d+(?:\.\d+)?\s*(?:cm|mm))/i) ||
      ocrResult.value.match(/(\d+(?:\.\d+)?\s*[xX×]\s*\d+(?:\.\d+)?\s*(?:cm|mm))/i) ||
      ocrResult.value.match(/(\d+(?:\.\d+)?[xX×]\d+(?:\.\d+)?(?:cm|mm))/i) ||
      ocrResult.value.match(/Size[:\s]*[:#-]?\s*(\d+(?:\.\d+)?\s*(?:cm|mm))/i) ||
      ocrResult.value.match(/(\d+(?:\.\d+)?\s*(?:cm|mm))/i);

    // Manufacturer: Look for various manufacturer patterns
    const manufacturerMatch = ocrResult.value.match(/Manufacturer\s*[:#-]?\s*([A-Za-z\s]+)/i) ||
      ocrResult.value.match(/Mfr\s*[:#-]?\s*([A-Za-z\s]+)/i) ||
      ocrResult.value.match(/Made\s*by\s*[:#-]?\s*([A-Za-z\s]+)/i)

    // Populate extracted fields
    ocrSerialNumber.value = serialMatch
      ? normalizeSerial(serialMatch[1] || serialMatch[0])
      : ''
    ocrProductCode.value = productCodeMatch ? (productCodeMatch[1] || productCodeMatch[0]).trim() : ''
    ocrExpiryDate.value = expiryMatch ? (expiryMatch[1] || expiryMatch[0]).trim() : ''
    ocrSize.value = sizeMatch ? (sizeMatch[1] || sizeMatch[0]).trim() : ''

    ocrSelectedBrand.value = selectedBrand.value
    ocrReturnReason.value = returnReason.value

    showOcrModal.value = true
  } catch (e) {
    ocrResult.value = 'OCR failed.'
  }
  ocrLoading.value = false
}

function normalizeSerial(raw: string) {
  return raw
    .toUpperCase()
    .replace(/SERIAL|NO|NUMBER|ID/gi, '')
    .replace(/[–—]/g, '-')
    .replace(/\s+/g, '-')
    .replace(/O/g, '0')
    .replace(/I/g, '1')
    .replace(/[^A-Z0-9-]/g, '')
    .replace(/-+/g, '-')
    .trim()
}

function confirmOcrReturn() {
  // Find the graft size ID that matches the OCR-extracted size
  const matchingGraftSize = props.graftSizes.find(gs =>
    gs.size === ocrSize.value && gs.brandId === ocrSelectedBrand.value
  )

  const returnData = {
    brandId: ocrSelectedBrand.value,
    graftSizeId: matchingGraftSize?.id || null,
    reason: ocrReturnReason.value,
    other: ocrReturnReason.value === 'Other' ? ocrOtherReason.value : null,
    graftLogId: null,
    entryType: 'upload',
    ocrSerialNumber: ocrSerialNumber.value || null,
    ocrExpiryDate: ocrExpiryDate.value || null,
    ocrProductCode: ocrProductCode.value || null
  }

  emit('submit-return', returnData)
  resetForm()
  showOcrModal.value = false
  isOpen.value = false
}

function handleManualSubmit() {
  if (selectedGraftSize.value && returnReason.value && selectedBrand.value && manufacturerId.value && graftLogId.value) {
    const returnData = {
      brandId: selectedBrand.value,
      graftSizeId: selectedGraftSize.value,
      reason: returnReason.value,
      other: returnReason.value === 'Other' ? otherReturnReason.value : null,
      graftLogId: graftLogId.value,
      entryType: 'manual'
    }

    emit('submit-return', returnData)
    resetForm()
    isOpen.value = false
  } else {
    alert('Please fill in all required fields including the usage log ID.')
  }
}

function selectEntryMethod(isManual: boolean) {
  manualEntry.value = isManual
  entryMethodSelected.value = true
  if (isManual) {
    showManualModal.value = true
  } else {
    showUploadModal.value = true
  }
}

function backToChoice() {
  entryMethodSelected.value = false
  showUploadModal.value = false
  showManualModal.value = false
  manualEntry.value = false
}

function resetForm() {
  selectedBrand.value = ''
  brandSearch.value = ''
  showBrandDropdown.value = false
  returnReason.value = ''
  otherReturnReason.value = ''
  entryMethodSelected.value = false
  manualEntry.value = false
  showUploadModal.value = false
  showManualModal.value = false
  selectedGraftSize.value = ''
  graftSizeSearch.value = ''
  showGraftSizeDropdown.value = false
  manufacturer.value = ''
  manufacturerId.value = ''
  manufacturerSearch.value = ''
  showManufacturerDropdown.value = false
  graftLogId.value = ''
  graftLogSearch.value = ''
  showGraftLogDropdown.value = false
  filteredGraftLogs.value = []
  ocrResult.value = ''
  ocrExtracted.value = null
  ocrOtherReason.value = ''
  ocrSerialNumber.value = ''
  ocrProductCode.value = ''
  ocrExpiryDate.value = ''
  ocrSize.value = ''
  // Clean up image preview URL
  if (uploadedImagePreview.value) {
    URL.revokeObjectURL(uploadedImagePreview.value)
    uploadedImagePreview.value = null
  }
}

function closeModal() {
  resetForm()
  isOpen.value = false
}

watch(isOpen, (newVal) => {
  if (!newVal) {
    resetForm()
    // Close all dropdowns when modal closes
    showManufacturerDropdown.value = false
    showBrandDropdown.value = false
    showGraftSizeDropdown.value = false
    showGraftLogDropdown.value = false
  } else {
    // Initialize lists when modal opens
    filteredManufacturers.value = props.manufacturers
    filteredBrands.value = props.brands
    // initialize usage logs suggestions when modal opens
    filteredGraftLogs.value = (props.usageLogs || []).slice(0, 50)
  }
})

// Update suggestions if parent provides usage logs after mount
watch(() => props.usageLogs, (newLogs) => {
  filteredGraftLogs.value = (newLogs || []).slice(0, 50)
})

// Watch manufacturer changes to filter brands
watch(manufacturerId, (newManufacturerId) => {
  if (newManufacturerId) {
    // Filter brands by manufacturer
    filteredBrands.value = props.brands.filter(b => b.manufacturerId === newManufacturerId)
  } else {
    // Show all brands if no manufacturer selected
    filteredBrands.value = props.brands
  }
})

// Watch brand changes to filter graft sizes
watch(selectedBrand, (newBrandId) => {
  if (newBrandId) {
    // Filter graft sizes by brand
    filteredGraftSizes.value = props.graftSizes.filter(gs => gs.brandId === newBrandId)
  } else {
    // Show all graft sizes if no brand selected
    filteredGraftSizes.value = props.graftSizes
  }
})

// Close dropdowns when clicking outside
if (typeof window !== 'undefined') {
  document.addEventListener('click', (e) => {
    const target = e.target as HTMLElement
    // Only close if click is outside dropdown container and not on the input
    if (!target.closest('.relative') && !target.closest('[ref="graftLogDropdownEl"]')) {
      showManufacturerDropdown.value = false
      showBrandDropdown.value = false
      showGraftSizeDropdown.value = false
      showGraftLogDropdown.value = false
    }
  })
}
</script>
