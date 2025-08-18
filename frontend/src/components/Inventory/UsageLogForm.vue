<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
          <DocumentTextIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Usage Log Entry</h2>
          <p class="text-gray-600 dark:text-gray-400">Record graft usage for patient treatment</p>
        </div>
      </div>
      
      <div class="flex items-center space-x-3">
        <button
          @click="showBulkUpload = !showBulkUpload"
          class="flex items-center space-x-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        >
          <ArrowUpTrayIcon class="w-4 h-4" />
          <span>Bulk Upload</span>
        </button>
      </div>
    </div>

    <div v-if="showBulkUpload" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
      <h3 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Bulk Upload Usage Logs</h3>
      <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
        Upload a CSV or Excel file with usage log data. Required columns: Serial Number, Patient Name, Date of Service, Wound Site, Clinician, Notes
      </p>
      <input
        type="file"
        accept=".csv,.xlsx,.xls"
        @change="handleFileUpload"
        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-600 file:text-white hover:file:bg-blue-700"
      />
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Serial Number *
          </label>
          <select
            v-model="serialNumber"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            required
          >
            <option value="">Select Serial Number</option>
            <option v-for="item in availableSerials" :key="item.serialNumber" :value="item.serialNumber">
              {{ item.serialNumber }} - {{ getItemDetails(item.serialNumber)?.brand }} ({{ getItemDetails(item.serialNumber)?.size }})
            </option>
          </select>
          <div v-if="serialNumber" class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm text-gray-600 dark:text-gray-400">
            {{ getItemDetails(serialNumber)?.brand }} - {{ getItemDetails(serialNumber)?.size }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Patient Name *
          </label>
          <div class="relative">
            <UserIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input
              type="text"
              v-model="patientName"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Enter patient name"
              required
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Date of Service *
          </label>
          <div class="relative">
            <CalendarIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input
              type="date"
              v-model="dateOfService"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              required
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Wound Site *
          </label>
          <div class="relative">
            <MapPinIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <select
              v-model="woundSite"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              required
            >
              <option value="">Select wound site</option>
              <option v-for="site in woundSiteOptions" :key="site" :value="site">{{ site }}</option>
            </select>
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Clinician *
          </label>
          <select
            v-model="clinicianId"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            required
          >
            <option value="">Select Clinician</option>
            <option v-for="clinician in clinicians" :key="clinician.id" :value="clinician.id">{{ clinician.name }}</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Notes
          </label>
          <textarea
            v-model="notes"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Additional notes about the usage..."
          />
        </div>
      </div>

      <div class="flex justify-end space-x-4">
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Log Usage
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
  DocumentTextIcon,
  ArrowUpTrayIcon,
  UserIcon,
  CalendarIcon,
  MapPinIcon
} from '@heroicons/vue/24/outline'

interface InventoryItem {
  id: string
  serialNumber: string
  brandId: string
  sizeId: string
  status: string
  clinicId: string // Ensure clinicId is present
}

interface Clinician {
  id: string
  name: string
}

interface Brand {
  id: string
  name: string
  sizes: Array<{ id: string; size: string }>
}

interface UsageLog {
  serialNumber: string
  patientName: string
  dateOfService: string
  woundSite: string
  clinicianId: string
  notes: string
  clinicId: string // Add clinicId to the log, but not as a form field
}

interface Props {
  inventoryItems: InventoryItem[]
  clinicians: Clinician[]
  brands: Brand[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'submit': [usageLog: Omit<UsageLog, 'id'>]
  'bulk-upload': [file: File]
  'cancel': []
}>()

const serialNumber = ref('')
const patientName = ref('')
const dateOfService = ref('')
const woundSite = ref('')
const clinicianId = ref('')
const notes = ref('')
const showBulkUpload = ref(false)

const availableSerials = computed(() => {
  // Allow all delivered or partially_used serials, regardless of previous usage logs
  return props.inventoryItems.filter(item => 
    item.status === 'delivered' || item.status === 'partially_used'
  )
})

const woundSiteOptions = [
  'Left Heel', 'Right Heel', 'Left Foot', 'Right Foot',
  'Left Ankle', 'Right Ankle', 'Left Leg', 'Right Leg',
  'Left Arm', 'Right Arm', 'Back', 'Chest', 'Other'
]

const getItemDetails = (serialNumber: string) => {
  const item = props.inventoryItems.find(i => i.serialNumber === serialNumber)
  if (!item) return null
  
  const brand = props.brands.find(b => b.id === item.brandId)
  const size = brand?.sizes.find(s => s.id === item.sizeId)
  
  return {
    brand: brand?.name || 'Unknown',
    size: size?.size || 'Unknown'
  }
}

const handleSubmit = () => {
  // Find the selected inventory item to get the clinicId
  const item = props.inventoryItems.find(i => i.serialNumber === serialNumber.value)
  const clinicId = item ? item.clinicId : ''
  emit('submit', {
    serialNumber: serialNumber.value,
    patientName: patientName.value,
    dateOfService: dateOfService.value,
    woundSite: woundSite.value,
    clinicianId: clinicianId.value,
    notes: notes.value,
    clinicId // Pass clinicId from inventory item
  })

  resetForm()
}

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    emit('bulk-upload', file)
    target.value = ''
  }
}

const resetForm = () => {
  serialNumber.value = ''
  patientName.value = ''
  dateOfService.value = ''
  woundSite.value = ''
  clinicianId.value = ''
  notes.value = ''
}

const handleCancel = () => {
  resetForm()
  emit('cancel')
}
</script> 