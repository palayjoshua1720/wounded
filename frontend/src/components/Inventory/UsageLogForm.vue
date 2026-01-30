<template>
  <div class="space-y-6">
    <!-- Description -->
    <div class="flex items-start space-x-3 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 rounded-r-lg">
      <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <p class="text-sm text-blue-800 dark:text-blue-200">Record graft usage for patient treatment. All fields marked with * are required.</p>
    </div>

    <div v-if="showBulkUpload"
      class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
      <h3 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Bulk Upload Usage Logs</h3>
      <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
        Upload a CSV or Excel file with usage log data. Required columns: Serial Number, Patient Name, Date of Service,
        Wound Site, Clinician, Notes
      </p>
      <input type="file" accept=".csv,.xlsx,.xls" @change="handleFileUpload"
        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-600 file:text-white hover:file:bg-blue-700" />
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-8">
      <!-- Section 1: Product Information -->
      <div class="space-y-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide flex items-center space-x-2">
          <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
          <span>Product Information</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
              Serial Number <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <DocumentTextIcon
                class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
              <input type="text" v-model="serialNumber"
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
                placeholder="Enter serial number" required />
            </div>
          </div>

        <div class="relative space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Patient <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <UserIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input type="text" v-model="patientSearchTerm"
              class="w-full pl-10 pr-24 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
              placeholder="Search patient by name (min 2 chars)" autocomplete="off" required />
            <div class="absolute inset-y-0 right-3 flex items-center space-x-2">
              <span v-if="isSearchingPatients" class="text-xs text-gray-500 dark:text-gray-400">Searching...</span>
              <button v-if="selectedPatient" type="button"
                class="text-xs text-blue-600 dark:text-blue-400 hover:underline" @click="clearSelectedPatient">
                Change
              </button>
            </div>
          </div>
          <p v-if="selectedPatient" class="mt-2 text-xs text-gray-600 dark:text-gray-400">
            Selected: <span class="font-medium">{{ selectedPatient.name }}</span>
          </p>
          <div v-if="selectedPatient && selectedPatient.clinic_name"
            class="mt-2 p-2.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center space-x-2">
            <svg class="w-4 h-4 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-xs text-green-700 dark:text-green-300">
              <span class="font-medium">Patient's Clinic:</span> {{ selectedPatient.clinic_name }}
            </p>
          </div>
          <p v-else class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Select a patient from the dropdown suggestions.
          </p>
          <div v-if="patientOptions.length > 0"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl max-h-48 overflow-y-auto">
            <button type="button" v-for="patient in patientOptions" :key="patient.id"
              class="w-full px-4 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700 last:border-0"
              @click="selectPatient(patient)">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ patient.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                ID: {{ patient.id }}
                <span v-if="patient.clinic_name">• Clinic: {{ patient.clinic_name }}</span>
              </p>
            </button>
          </div>
          <p v-if="patientSearchError" class="mt-2 text-xs text-red-600 dark:text-red-400">
            {{ patientSearchError }}
          </p>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Date of Service <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <CalendarIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input type="date" v-model="dateOfService"
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
              required />
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Wound Site <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <MapPinIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <select v-model="woundSite"
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
              required>
              <option value="">Select wound site</option>
              <option v-for="site in woundSiteOptions" :key="site" :value="site">{{ site }}</option>
            </select>
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Graft <span class="text-red-500">*</span>
          </label>
          <select v-model="graftSizeId"
            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
            required>
            <option value="">Select graft</option>
            <option v-for="graft in graftSizes" :key="graft.graft_size_id" :value="graft.graft_size_id">
              {{ graft.size }} • Area: {{ graft.area }} • Price: {{ graft.price }}
            </option>
          </select>
          <div v-if="graftDetails"
            class="mt-3 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-start space-x-2 mb-2">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div class="flex-1">
                <p class="text-xs font-semibold text-blue-900 dark:text-blue-100 mb-2">Graft Information</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                  <div>
                    <p class="text-blue-600 dark:text-blue-400 font-medium">Brand</p>
                    <p class="text-blue-800 dark:text-blue-200">{{ graftDetails.brand_name }}</p>
                  </div>
                  <div>
                    <p class="text-blue-600 dark:text-blue-400 font-medium">Size</p>
                    <p class="text-blue-800 dark:text-blue-200">{{ graftDetails.size }}</p>
                  </div>
                  <div v-if="graftDetails.manufacturer_name" class="col-span-2">
                    <p class="text-blue-600 dark:text-blue-400 font-medium">Manufacturer</p>
                    <p class="text-blue-800 dark:text-blue-200">{{ graftDetails.manufacturer_name }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Quantity Used <span class="text-red-500">*</span>
          </label>
          <input type="number" min="1" v-model.number="quantityUsed"
            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
            placeholder="Enter quantity used" required />
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Status <span class="text-red-500">*</span>
          </label>
          <select v-model="status"
            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
            required>
            <option value="">Select status</option>
            <!-- <option value="expected">Expected</option> -->
            <option value="delivered">Delivered</option>
            <option value="used">Used</option>
            <option value="partially_used">Partially Used</option>
            <option value="reassigned">Reassigned</option>
            <option value="unused">Unused</option>
            <option value="expired">Expired</option>
          </select>
          <div v-if="status && statusDescriptions[status]" 
            class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <p class="text-xs font-semibold text-blue-900 dark:text-blue-100 mb-1">{{ statusTitles[status] }}</p>
            <p class="text-xs text-blue-700 dark:text-blue-300">{{ statusDescriptions[status] }}</p>
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Expired At
          </label>
          <div class="relative">
            <CalendarIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input type="date" v-model="expiredAt"
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors" />
          </div>
        </div>

        <div class="md:col-span-2 space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Clinician <span class="text-red-500">*</span>
          </label>
          <select v-model="clinicianId"
            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
            required>
            <option value="">Select Clinician</option>
            <option v-for="clinician in clinicians" :key="clinician.id" :value="clinician.id">{{ clinician.name }}
            </option>
          </select>
          <div v-if="selectedClinicianInfo"
            class="mt-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-start space-x-2">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <div class="flex-1">
                <p class="text-xs font-semibold text-green-900 dark:text-green-100 mb-1">Clinician Details</p>
                <p class="text-xs text-green-800 dark:text-green-200">
                  <span class="font-medium">Clinic:</span> {{ selectedClinicianInfo.clinic_name || 'N/A' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="md:col-span-2 space-y-2">
          <label class="block text-sm font-medium text-gray-900 dark:text-gray-200">
            Notes
          </label>
          <textarea v-model="notes" rows="3"
            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors resize-none"
            placeholder="Additional notes about the usage..." />
        </div>
      </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" @click="$emit('cancel')"
          class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
          Cancel
        </button>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 font-medium shadow-sm hover:shadow flex items-center space-x-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <span>Log Usage</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { inventoryService } from '@/services/api'
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
  clinic_id?: string
  clinic_name?: string
}

interface Brand {
  id: string
  name: string
  sizes: Array<{ id: string; size: string }>
}

interface PatientOption {
  id: string
  name: string
  clinic_id?: string
  clinic_name?: string
}

interface UsageLogPayload {
  serialNumber: string
  patientId: string
  patientName: string
  dateOfService: string
  woundSite: string
  clinicianId: string
  notes: string
  clinicId: string
  graftSizeId: string
  expiredAt: string
  quantityUsed: number
  status: string
}

interface Props {
  inventoryItems: InventoryItem[]
  clinicians: Clinician[]
  brands: Brand[]
  graftSizes: Array<{
    graft_size_id: string
    size: string
    area: number
    price: number
  }>
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'submit': [usageLog: UsageLogPayload]
  'bulk-upload': [file: File]
  'cancel': []
}>()

const serialNumber = ref('')
const patientSearchTerm = ref('')
const patientOptions = ref<PatientOption[]>([])
const selectedPatient = ref<PatientOption | null>(null)
const isSearchingPatients = ref(false)
const patientSearchError = ref('')
const dateOfService = ref('')
const woundSite = ref('')
const clinicianId = ref('')
const notes = ref('')
const showBulkUpload = ref(false)
const graftSizeId = ref('')
const expiredAt = ref('')
const status = ref('')
const quantityUsed = ref<number>(1)
const graftDetails = ref<any>(null)

// Computed property for selected clinician information
const selectedClinicianInfo = computed(() => {
  if (!clinicianId.value) return null
  return props.clinicians.find(c => c.id === clinicianId.value)
})

const woundSiteOptions = [
  'Left Heel', 'Right Heel', 'Left Foot', 'Right Foot',
  'Left Ankle', 'Right Ankle', 'Left Leg', 'Right Leg',
  'Left Arm', 'Right Arm', 'Back', 'Chest', 'Other'
]

const statusTitles: Record<string, string> = {
  // 'expected': 'Expected',
  'delivered': 'Delivered',
  'used': 'Used',
  'partially_used': 'Partially Used',
  'reassigned': 'Reassigned',
  'unused': 'Unused',
  'expired': 'Expired'
}

const statusDescriptions: Record<string, string> = {
  // 'expected': 'From initial order - Item is expected to be delivered',
  'delivered': 'Confirmed by manufacturer and logged - Item has been confirmed and recorded',
  'used': 'Marked as used via usage log - Item has been used in patient treatment',
  'partially_used': 'Serial used across multiple wounds - This serial number was used for multiple patient wounds',
  'reassigned': 'Used for patient other than intended - Item was used for a different patient than originally planned',
  'unused': 'No usage record yet - Item has not been used',
  'expired': 'Time alert for expiration - Item has passed its expiration date'
}

const handleSubmit = () => {
  if (!selectedPatient.value) {
    patientSearchError.value = 'Please select a patient from the search results'
    return
  }

  // Find the selected inventory item to get the clinicId
  const item = props.inventoryItems.find(i => i.serialNumber === serialNumber.value)
  const clinicId = item ? item.clinicId : ''
  emit('submit', {
    serialNumber: serialNumber.value,
    patientId: selectedPatient.value.id,
    patientName: selectedPatient.value.name,
    dateOfService: dateOfService.value,
    woundSite: woundSite.value,
    clinicianId: clinicianId.value,
    notes: notes.value,
    clinicId, // Pass clinicId from inventory item
    graftSizeId: graftSizeId.value,
    expiredAt: expiredAt.value,
    quantityUsed: quantityUsed.value || 1,
    status: status.value,
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
  patientSearchTerm.value = ''
  dateOfService.value = ''
  woundSite.value = ''
  clinicianId.value = ''
  notes.value = ''
  selectedPatient.value = null
  patientOptions.value = []
  patientSearchError.value = ''
  graftSizeId.value = ''
  expiredAt.value = ''
  quantityUsed.value = 1
  graftDetails.value = null
  status.value = ''
}

const handleCancel = () => {
  resetForm()
  emit('cancel')
}

const selectPatient = (patient: PatientOption) => {
  selectedPatient.value = patient
  patientSearchTerm.value = patient.name
  patientOptions.value = []
  patientSearchError.value = ''
}

const clearSelectedPatient = () => {
  selectedPatient.value = null
  patientSearchTerm.value = ''
}

watch(patientSearchTerm, async (value) => {
  patientSearchError.value = ''

  if (selectedPatient.value && value === selectedPatient.value.name) {
    return
  }

  if (!value || value.length < 2) {
    patientOptions.value = []
    return
  }

  isSearchingPatients.value = true
  try {
    const { data } = await inventoryService.searchPatients(value)
    const rows = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : []
    patientOptions.value = rows.map((row: any) => ({
      id: String(row.id ?? row.patient_id ?? ''),
      name: row.name ?? row.patient_name ?? '',
      clinic_id: row.clinic_id ?? undefined,
      clinic_name: row.clinic_name ?? undefined
    })).filter((option: PatientOption) => option.id && option.name)
  } catch (error) {
    console.error('Failed to search patients', error)
    patientOptions.value = []
    patientSearchError.value = 'Unable to search patients right now'
  } finally {
    isSearchingPatients.value = false
  }
})

watch(graftSizeId, async (value) => {
  if (!value) {
    graftDetails.value = null
    return
  }

  try {
    const { data } = await inventoryService.getGraftSize(value)
    console.log('Graft details received:', data) // Debug log to check API response
    if (data?.success && data?.data) {
      graftDetails.value = data.data
      console.log('Graft details set:', graftDetails.value) // Verify the data
    }
  } catch (error) {
    console.error('Failed to fetch graft details', error)
    graftDetails.value = null
  }
})
</script>