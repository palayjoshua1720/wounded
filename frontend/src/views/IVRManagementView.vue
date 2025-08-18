<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">IVR Management</h1>
      </div>
    </div>

    <!-- IVR Management Component -->
    <IVRManagement
      :ivr-requests="ivrRequests"
      :patients="patients"
      :brands="brands"
      :manufacturers="manufacturers"
      :clinics="clinics"
      @submit-ivr="handleSubmitIVR"
      @update-status="handleUpdateStatus"
      @view-request="handleViewRequest"
    />

    <!-- Request Details Modal -->
    <BaseModal v-model="showRequestModal" title="IVR Request Details">
      <div v-if="selectedRequest" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ getPatientName(selectedRequest.patientId) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(selectedRequest.status)}`">
              <component :is="getStatusIcon(selectedRequest.status)" class="w-4 h-4" />
              <span class="ml-1 capitalize">{{ selectedRequest.status.replace('_', ' ') }}</span>
            </span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ getClinicName(selectedRequest.clinicId) }}</p>
          </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ getBrandName(selectedRequest.brandId) }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Submitted Date</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedRequest.dateSubmitted) }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedRequest.expiryDate ? formatDate(selectedRequest.expiryDate) : 'N/A' }}</p>
        </div>
      </div>

        <div v-if="selectedRequest.notes">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedRequest.notes }}</p>
      </div>
    </div>
    <template #actions>
      <button
          @click="showRequestModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Close
      </button>
    </template>
  </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import IVRManagement from '@/components/IVR/IVRManagement.vue'
import { CheckCircleIcon, ClockIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

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

// Mock data
const mockIVRRequests: IVRRequest[] = [
  {
    id: '1',
    patientId: '1',
    clinicId: '1',
    brandId: '1',
    manufacturerId: '1',
    status: 'eligible',
    dateSubmitted: '2025-01-15',
    expiryDate: '2025-04-15',
    notes: 'Approved for coverage under plan benefits'
  },
  {
    id: '2',
    patientId: '2',
    clinicId: '2',
    brandId: '2',
    manufacturerId: '2',
    status: 'pending',
    dateSubmitted: '2025-01-20',
    notes: 'Awaiting insurance verification'
  },
  {
    id: '3',
    patientId: '3',
    clinicId: '3',
    brandId: '1',
    manufacturerId: '1',
    status: 'not_eligible',
    dateSubmitted: '2025-01-18',
    notes: 'Not covered under current plan'
  },
  {
    id: '4',
    patientId: '4',
    clinicId: '1',
    brandId: '2',
    manufacturerId: '2',
    status: 'eligible',
    dateSubmitted: '2025-01-22',
    expiryDate: '2025-04-22',
    notes: 'Approved with prior authorization'
  },
  {
    id: '5',
    patientId: '5',
    clinicId: '2',
    brandId: '1',
    manufacturerId: '1',
    status: 'pending',
    dateSubmitted: '2025-01-25',
    notes: 'Under review by insurance provider'
  }
]

const patients = ref<Patient[]>([
  { id: '1', name: 'John Smith', clinicId: '1' },
  { id: '2', name: 'Sarah Johnson', clinicId: '2' },
  { id: '3', name: 'Michael Chen', clinicId: '3' },
  { id: '4', name: 'Emily Rodriguez', clinicId: '1' },
  { id: '5', name: 'David Thompson', clinicId: '2' }
])

const brands = ref<Brand[]>([
  { id: '1', name: 'MedTech', manufacturerId: '1' },
  { id: '2', name: 'BioMed', manufacturerId: '2' }
])

const manufacturers = ref<Manufacturer[]>([
  {
    id: '1',
    name: 'MedTech Solutions',
    ivrFormType: 'standard',
    ivrFormTemplate: '/forms/medtech-ivr-template.pdf'
  },
  {
    id: '2',
    name: 'BioMedical Industries',
    ivrFormType: 'premium',
    ivrFormTemplate: '/forms/biomed-ivr-template.pdf'
  }
])

const clinics = ref<Clinic[]>([
  { id: '1', name: 'Metro Wound Care Center' },
  { id: '2', name: 'Advanced Healing Institute' },
  { id: '3', name: 'City Medical Clinic' }
])

const ivrRequests = ref<IVRRequest[]>([...mockIVRRequests])
const showRequestModal = ref(false)
const selectedRequest = ref<IVRRequest | null>(null)

function getStatusColor(status: string) {
  switch (status) {
    case 'eligible': return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    case 'not_eligible': return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

function getStatusIcon(status: string) {
  switch (status) {
    case 'eligible': return CheckCircleIcon
    case 'not_eligible': return ExclamationTriangleIcon
    case 'pending': return ClockIcon
    default: return null
  }
}

function getPatientName(patientId: string) {
  return patients.value.find(p => p.id === patientId)?.name || 'Unknown Patient'
}

function getBrandName(brandId: string) {
  return brands.value.find(b => b.id === brandId)?.name || 'Unknown Brand'
}

function getClinicName(clinicId: string) {
  return clinics.value.find(c => c.id === clinicId)?.name || 'Unknown Clinic'
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString()
}

function handleSubmitIVR(data: any) {
  console.log('Submitting IVR request:', data)
  
  const newRequest: IVRRequest = {
    id: Date.now().toString(),
    patientId: data.patientId,
    clinicId: data.clinicId,
    brandId: data.brandId,
    manufacturerId: data.manufacturerId,
    status: 'pending',
    dateSubmitted: new Date().toISOString().split('T')[0],
    notes: data.notes
  }
  
  ivrRequests.value.push(newRequest)
  console.log('IVR request added:', newRequest)
}

function handleUpdateStatus(id: string, status: 'eligible' | 'not_eligible' | 'pending', responseFile?: string) {
  const requestIndex = ivrRequests.value.findIndex(request => request.id === id)
  if (requestIndex !== -1) {
    ivrRequests.value[requestIndex].status = status
    if (status === 'eligible') {
      // Set expiry date to 90 days from now
      const expiryDate = new Date()
      expiryDate.setDate(expiryDate.getDate() + 90)
      ivrRequests.value[requestIndex].expiryDate = expiryDate.toISOString().split('T')[0]
    }
    console.log('IVR request status updated:', ivrRequests.value[requestIndex])
  }
}

function handleViewRequest(request: IVRRequest) {
  selectedRequest.value = request
  showRequestModal.value = true
}

// Watch for selectedRequest changes to show modal
import { watch } from 'vue'
watch(selectedRequest, (newRequest) => {
  showRequestModal.value = !!newRequest
})
</script> 