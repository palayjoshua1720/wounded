<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">IVR Management</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage insurance verification requests and results</p>
      </div>
      <div class="flex space-x-3">
        <button
          class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          @click="showUploadModal = true"
        >
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Upload Results
        </button>
        <button
          class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          @click="showCreateForm = true"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Submit IVR Request
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Requests</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ ivrRecords.length }}</p>
          </div>
          <ShieldCheckIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Eligible</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ ivrRecords.filter(record => record.status === 'eligible').length }}
            </p>
          </div>
          <CheckCircleIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ ivrRecords.filter(record => record.status === 'pending').length }}
            </p>
          </div>
          <ClockIcon class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Not Eligible</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ ivrRecords.filter(record => record.status === 'not_eligible').length }}
            </p>
          </div>
          <ExclamationTriangleIcon class="w-8 h-8 text-red-600 dark:text-red-400" />
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="flex-1">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
          <input
            type="text"
            placeholder="Search by patient, clinic, or insurance..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            v-model="searchTerm"
          />
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <FunnelIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        <select
          v-model="statusFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
          <option value="all">All Status</option>
          <option value="eligible">Eligible</option>
          <option value="not_eligible">Not Eligible</option>
          <option value="pending">Pending</option>
        </select>
        <select
          v-model="brandFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
          <option value="all">All Brands</option>
          <option v-for="brand in uniqueBrands" :key="brand" :value="brand">{{ brand }}</option>
        </select>
      </div>
    </div>

    <!-- IVR Records Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
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
                Insurance
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Submitted
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="record in filteredRecords" :key="record.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ record.patientName }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ record.clinicName }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ record.brand }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ record.insurance }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(record.status)}`">
                  <component :is="getStatusIcon(record.status)" class="w-4 h-4" />
                  <span class="ml-1 capitalize">{{ record.status.replace('_', ' ') }}</span>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(record.submittedDate) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  @click="selectedRecord = record"
                  class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  View
                </button>
                <div class="inline-flex space-x-1">
                  <button
                    v-if="record.status === 'pending'"
                    @click="handleStatusUpdate(record.id, 'eligible')"
                    class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30"
                  >
                    Approve
                  </button>
                  <button
                    v-if="record.status === 'pending'"
                    @click="handleStatusUpdate(record.id, 'not_eligible')"
                    class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                  >
                    Deny
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Upload Modal -->
  <BaseModal v-model="showUploadModal" title="Upload IVR Results">
    <div class="space-y-4">
      <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
        <ShieldCheckIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload IVR Results</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          Upload CSV or Excel files with IVR verification results
        </p>
        <input
          type="file"
          accept=".csv,.xlsx,.xls"
          class="hidden"
          id="ivr-upload"
        />
        <label
          for="ivr-upload"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer"
        >
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Choose File
        </label>
      </div>
    </div>
    <template #actions>
      <button
        @click="showUploadModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Cancel
      </button>
      <button
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        Process File
      </button>
    </template>
  </BaseModal>

  <!-- Create Form Modal -->
  <BaseModal v-model="showCreateForm" title="Submit IVR Request">
    <div class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Patient Name
          </label>
          <input
            v-model="newRequest.patientName"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Enter patient name"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Clinic
          </label>
          <select
            v-model="newRequest.clinicId"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">Select Clinic</option>
            <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
              {{ clinic.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Brand
          </label>
          <select
            v-model="newRequest.brandId"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="">Select Brand</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Insurance Provider
          </label>
          <input
            v-model="newRequest.insurance"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Enter insurance provider"
          />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Notes
        </label>
        <textarea
          v-model="newRequest.notes"
          rows="3"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          placeholder="Additional notes for this IVR request..."
        />
      </div>
    </div>
    <template #actions>
      <button
        @click="showCreateForm = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Cancel
      </button>
      <button
        @click="handleCreateRequest"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        Submit Request
      </button>
    </template>
  </BaseModal>

  <!-- Record Details Modal -->
  <BaseModal v-model="showRecordModal" title="IVR Record Details">
    <div v-if="selectedRecord" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient Name</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedRecord.patientName }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(selectedRecord.status)}`">
            <component :is="getStatusIcon(selectedRecord.status)" class="w-4 h-4" />
            <span class="ml-1 capitalize">{{ selectedRecord.status.replace('_', ' ') }}</span>
          </span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedRecord.clinicName }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedRecord.brand }}</p>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Insurance</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedRecord.insurance }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Submitted Date</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedRecord.submittedDate) }}</p>
        </div>
      </div>
      <div v-if="selectedRecord.notes">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedRecord.notes }}</p>
      </div>
    </div>
    <template #actions>
      <button
        @click="showRecordModal = false"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Close
      </button>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
  ArrowUpTrayIcon,
  PlusIcon,
  ShieldCheckIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  MagnifyingGlassIcon,
  FunnelIcon
} from '@heroicons/vue/24/outline'

interface IVRRecord {
  id: string
  patientName: string
  clinicName: string
  brand: string
  insurance: string
  status: 'eligible' | 'not_eligible' | 'pending'
  submittedDate: string
  notes?: string
}

const mockIVRRecords: IVRRecord[] = [
  {
    id: '1',
    patientName: 'John Smith',
    clinicName: 'Metro Wound Care Center',
    brand: 'MedTech',
    insurance: 'Blue Cross Blue Shield',
    status: 'eligible',
    submittedDate: '2025-01-15',
    notes: 'Approved for coverage under plan benefits'
  },
  {
    id: '2',
    patientName: 'Sarah Johnson',
    clinicName: 'Advanced Healing Institute',
    brand: 'BioMed',
    insurance: 'Aetna',
    status: 'pending',
    submittedDate: '2025-01-20',
    notes: 'Awaiting insurance verification'
  },
  {
    id: '3',
    patientName: 'Michael Chen',
    clinicName: 'City Medical Clinic',
    brand: 'MedTech',
    insurance: 'UnitedHealth',
    status: 'not_eligible',
    submittedDate: '2025-01-18',
    notes: 'Not covered under current plan'
  },
  {
    id: '4',
    patientName: 'Emily Rodriguez',
    clinicName: 'Metro Wound Care Center',
    brand: 'BioMed',
    insurance: 'Cigna',
    status: 'eligible',
    submittedDate: '2025-01-22',
    notes: 'Approved with prior authorization'
  },
  {
    id: '5',
    patientName: 'David Thompson',
    clinicName: 'Advanced Healing Institute',
    brand: 'MedTech',
    insurance: 'Humana',
    status: 'pending',
    submittedDate: '2025-01-25',
    notes: 'Under review by insurance provider'
  }
]

const ivrRecords = ref<IVRRecord[]>([...mockIVRRecords])
const searchTerm = ref('')
const statusFilter = ref('all')
const brandFilter = ref('all')
const showUploadModal = ref(false)
const showCreateForm = ref(false)
const showRecordModal = ref(false)
const selectedRecord = ref<IVRRecord | null>(null)

const newRequest = ref({
  patientName: '',
  clinicId: '',
  brandId: '',
  insurance: '',
  notes: ''
})

// Mock data
const clinics = ref([
  { id: '1', name: 'Metro Wound Care Center' },
  { id: '2', name: 'Advanced Healing Institute' },
  { id: '3', name: 'City Medical Clinic' }
])

const brands = ref([
  { id: '1', name: 'MedTech' },
  { id: '2', name: 'BioMed' }
])

const uniqueBrands = computed(() => [...new Set(ivrRecords.value.map(record => record.brand))])

const filteredRecords = computed(() => {
  return ivrRecords.value.filter(record => {
    const matchesSearch = record.patientName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      record.clinicName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      record.insurance.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesStatus = statusFilter.value === 'all' || record.status === statusFilter.value
    const matchesBrand = brandFilter.value === 'all' || record.brand === brandFilter.value
    return matchesSearch && matchesStatus && matchesBrand
  })
})

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
    default: return ShieldCheckIcon
  }
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString()
}

function handleStatusUpdate(recordId: string, newStatus: 'eligible' | 'not_eligible') {
  const recordIndex = ivrRecords.value.findIndex(record => record.id === recordId)
  if (recordIndex !== -1) {
    ivrRecords.value[recordIndex].status = newStatus
  }
}

function handleCreateRequest() {
  const clinic = clinics.value.find(c => c.id === newRequest.value.clinicId)
  const brand = brands.value.find(b => b.id === newRequest.value.brandId)
  
  if (clinic && brand) {
    const newRecord: IVRRecord = {
      id: Date.now().toString(),
      patientName: newRequest.value.patientName,
      clinicName: clinic.name,
      brand: brand.name,
      insurance: newRequest.value.insurance,
      status: 'pending',
      submittedDate: new Date().toISOString().split('T')[0],
      notes: newRequest.value.notes
    }
    
    ivrRecords.value.push(newRecord)
    
    // Reset form
    newRequest.value = {
      patientName: '',
      clinicId: '',
      brandId: '',
      insurance: '',
      notes: ''
    }
    
    showCreateForm.value = false
  }
}

// Watch for selectedRecord changes to show modal
import { watch } from 'vue'
watch(selectedRecord, (newRecord) => {
  showRecordModal.value = !!newRecord
})
</script> 