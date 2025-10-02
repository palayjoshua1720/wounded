<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">

      <div v-if="showForm" class="border border-gray-200 dark:border-gray-600 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Submit New IVR Request</h3>
        
        <form @submit.prevent="handleSubmitIVR" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Patient *
              </label>
              <select
                v-model="selectedPatient"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                required
              >
                <option value="">Select Patient</option>
                <option v-for="patient in patients" :key="patient.id" :value="patient.id">
                  {{ patient.name }} - {{ getClinicName(patient.clinicId) }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Brand *
              </label>
              <select
                v-model="selectedBrand"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                required
              >
                <option value="">Select Brand</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                  {{ brand.name }} - {{ getManufacturerName(brand.manufacturerId) }}
                </option>
              </select>
            </div>
          </div>

          <div v-if="selectedBrand" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">IVR Form Information</h4>
            <div v-if="getIVRFormInfo(selectedBrand)" class="space-y-2">
              <p class="text-sm text-blue-700 dark:text-blue-300">
                <strong>Manufacturer:</strong> {{ getIVRFormInfo(selectedBrand)?.name }}
              </p>
              <p class="text-sm text-blue-700 dark:text-blue-300">
                <strong>Form Type:</strong> {{ getIVRFormInfo(selectedBrand)?.ivrFormType?.toUpperCase() }}
              </p>
              <div class="flex items-center space-x-2">
                <ArrowDownTrayIcon class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                <a
                  :href="getIVRFormInfo(selectedBrand)?.ivrFormTemplate"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline"
                >
                  Download IVR Form Template
                </a>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Notes
            </label>
            <textarea
              v-model="notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Additional notes for this IVR request..."
            />
          </div>

          <div class="flex justify-end space-x-4">
            <button
              type="button"
              @click="showForm = false"
              class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Submit IVR Request
            </button>
          </div>
        </form>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Patient
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Clinic
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Brand
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Submitted
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Expiry
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="request in filteredRequests" :key="request.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ getPatientName(request.patientId) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ getClinicName(request.clinicId) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ getBrandName(request.brandId) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                  {{ formatDate(request.dateSubmitted) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="`inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(request.status)}`">
                  <component :is="getStatusIcon(request.status)" class="w-4 h-4" />
                  <span class="capitalize">{{ request.status.replace('_', ' ') }}</span>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                  {{ request.expiryDate ? formatDate(request.expiryDate) : 'N/A' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <label v-if="request.status === 'pending'" class="cursor-pointer text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 flex items-center space-x-1">
                    <ArrowUpTrayIcon class="w-4 h-4" />
                    <span>Upload Response</span>
                    <input
                      type="file"
                      accept=".pdf,.doc,.docx"
                      @change="handleFileUpload(request.id, $event)"
                      class="hidden"
                    />
                  </label>
                  <button @click="$emit('view-request', request)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 flex items-center space-x-1">
                    <EyeIcon class="w-4 h-4" />
                    <span>View</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="filteredRequests.length === 0" class="text-center py-12">
        <ShieldCheckIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No IVR requests found</h3>
        <p class="text-gray-600 dark:text-gray-400">Submit your first IVR request to get started.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  ShieldCheckIcon,
  PlusIcon,
  ArrowUpTrayIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

interface IVRRequest {
  id: string
  patientId: string
  clinicId: string
  brandId: string
  manufacturerId: string
  status: 'eligible' | 'not_eligible' | 'pending'
  dateSubmitted: string
  expiryDate?: string
  notes?: string
}

interface Patient {
  id: string
  name: string
  clinicId: string
}

interface Brand {
  id: string
  name: string
  manufacturerId: string
}

interface Manufacturer {
  id: string
  name: string
  ivrFormType: string
  ivrFormTemplate: string
}

interface Clinic {
  id: string
  name: string
}

interface Props {
  ivrRequests: IVRRequest[]
  patients: Patient[]
  brands: Brand[]
  manufacturers: Manufacturer[]
  clinics: Clinic[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'submit-ivr': [data: any]
  'update-status': [id: string, status: 'eligible' | 'not_eligible' | 'pending', responseFile?: string]
  'view-request': [request: IVRRequest]
}>()

const showForm = ref(false)
const selectedPatient = ref('')
const selectedBrand = ref('')
const notes = ref('')
const statusFilter = ref('all')
const ivrSearchQuery = ref('')

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'eligible':
      return CheckCircleIcon
    case 'not_eligible':
      return ExclamationTriangleIcon
    case 'pending':
      return ClockIcon
    default:
      return ShieldCheckIcon
  }
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'eligible':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    case 'not_eligible':
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

const getPatientName = (patientId: string) => {
  return props.patients.find(p => p.id === patientId)?.name || 'Unknown Patient'
}

const getBrandName = (brandId: string) => {
  return props.brands.find(b => b.id === brandId)?.name || 'Unknown Brand'
}

const getManufacturerName = (manufacturerId: string) => {
  return props.manufacturers.find(m => m.id === manufacturerId)?.name || 'Unknown Manufacturer'
}

const getClinicName = (clinicId: string) => {
  return props.clinics.find(c => c.id === clinicId)?.name || 'Unknown Clinic'
}

const getIVRFormInfo = (brandId: string) => {
  const brand = props.brands.find(b => b.id === brandId)
  if (!brand) return null
  
  const manufacturer = props.manufacturers.find(m => m.id === brand.manufacturerId)
  return manufacturer
}

const handleSubmitIVR = () => {
  const patient = props.patients.find(p => p.id === selectedPatient.value)
  const brand = props.brands.find(b => b.id === selectedBrand.value)
  
  if (!patient || !brand) return

  emit('submit-ivr', {
    patientId: selectedPatient.value,
    clinicId: patient.clinicId,
    brandId: selectedBrand.value,
    manufacturerId: brand.manufacturerId,
    notes: notes.value
  })

  // Reset form
  selectedPatient.value = ''
  selectedBrand.value = ''
  notes.value = ''
  showForm.value = false
}

const filteredRequests = computed(() => {
  let filtered = props.ivrRequests
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(request => request.status === statusFilter.value)
  }
  if (ivrSearchQuery.value.trim()) {
    const q = ivrSearchQuery.value.trim().toLowerCase()
    filtered = filtered.filter(request =>
      (getPatientName(request.patientId) && getPatientName(request.patientId).toLowerCase().includes(q)) ||
      (getClinicName(request.clinicId) && getClinicName(request.clinicId).toLowerCase().includes(q)) ||
      (getBrandName(request.brandId) && getBrandName(request.brandId).toLowerCase().includes(q)) ||
      (request.status && request.status.toLowerCase().includes(q))
    )
  }
  return filtered
})

const handleFileUpload = (requestId: string, event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    console.log('Uploading response file for request:', requestId, file)
    emit('update-status', requestId, 'eligible', file.name)
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString()
}
</script> 