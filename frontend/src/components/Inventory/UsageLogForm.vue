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

      <!-- <div class="flex items-center space-x-3">
        <button @click="showBulkUpload = !showBulkUpload"
          class="flex items-center space-x-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
          <ArrowUpTrayIcon class="w-4 h-4" />
          <span>Bulk Upload</span>
        </button>
      </div> -->
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

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Serial Number *
          </label>
          <div class="relative">
            <DocumentTextIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input type="text" v-model="serialNumber"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Enter serial number" required />
          </div>
        </div>

        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Patient *
          </label>
          <div class="relative">
            <UserIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input type="text" v-model="patientSearchTerm"
              class="w-full pl-10 pr-24 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
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
          <div v-if="selectedPatient && selectedPatient.clinic_id"
            class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-xs text-green-700 dark:text-green-300">
              <span class="font-medium">Patient's Clinic ID:</span> {{ selectedPatient.clinic_id }}
            </p>
          </div>
          <p v-else class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Select a patient from the dropdown suggestions.
          </p>
          <div v-if="patientOptions.length > 0"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
            <button type="button" v-for="patient in patientOptions" :key="patient.id"
              class="w-full px-4 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700"
              @click="selectPatient(patient)">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ patient.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                ID: {{ patient.id }}
                <span v-if="patient.clinic_id">• Clinic: {{ patient.clinic_id }}</span>
              </p>
            </button>
          </div>
          <p v-if="patientSearchError" class="mt-2 text-xs text-red-600 dark:text-red-400">
            {{ patientSearchError }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Date of Service *
          </label>
          <div class="relative">
            <CalendarIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input type="date" v-model="dateOfService"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              required />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Wound Site *
          </label>
          <div class="relative">
            <MapPinIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <select v-model="woundSite"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              required>
              <option value="">Select wound site</option>
              <option v-for="site in woundSiteOptions" :key="site" :value="site">{{ site }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Graft *
          </label>
          <select v-model="graftSizeId"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            required>
            <option value="">Select graft</option>
            <option v-for="graft in graftSizes" :key="graft.graft_size_id" :value="graft.graft_size_id">
              {{ graft.size }} • Area: {{ graft.area }} • Price: {{ graft.price }}
            </option>
          </select>
          <div v-if="graftDetails"
            class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <p class="text-xs font-semibold text-blue-900 dark:text-blue-100 mb-2">Graft Information</p>
            <div class="space-y-1 text-xs text-blue-700 dark:text-blue-300">
              <p><span class="font-medium">Brand:</span> {{ graftDetails.brand_name }}</p>
              <p><span class="font-medium">Size:</span> {{ graftDetails.size }}</p>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Quantity Used *
          </label>
          <input type="number" min="1" v-model.number="quantityUsed"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Enter quantity used" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Status *
          </label>
          <select v-model="status"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            required>
            <option value="">Select status</option>
            <option value="expected">Expected</option>
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

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Expired At
          </label>
          <div class="relative">
            <CalendarIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" />
            <input type="date" v-model="expiredAt"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Clinician *
          </label>
          <select v-model="clinicianId"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            required>
            <option value="">Select Clinician</option>
            <option v-for="clinician in clinicians" :key="clinician.id" :value="clinician.id">{{ clinician.name }}
            </option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Notes
          </label>
          <textarea v-model="notes" rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Additional notes about the usage..." />
        </div>
      </div>

      <div class="flex justify-end space-x-4">
        <button type="button" @click="$emit('cancel')"
          class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
          Cancel
        </button>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          Log Usage
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

const woundSiteOptions = [
  'Left Heel', 'Right Heel', 'Left Foot', 'Right Foot',
  'Left Ankle', 'Right Ankle', 'Left Leg', 'Right Leg',
  'Left Arm', 'Right Arm', 'Back', 'Chest', 'Other'
]

const statusTitles: Record<string, string> = {
  'expected': 'Expected',
  'delivered': 'Delivered',
  'used': 'Used',
  'partially_used': 'Partially Used',
  'reassigned': 'Reassigned',
  'unused': 'Unused',
  'expired': 'Expired'
}

const statusDescriptions: Record<string, string> = {
  'expected': 'From initial order - Item is expected to be delivered',
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
      clinic_id: row.clinic_id ?? undefined
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
    if (data?.success && data?.data) {
      graftDetails.value = data.data
    }
  } catch (error) {
    console.error('Failed to fetch graft details', error)
    graftDetails.value = null
  }
})
</script>