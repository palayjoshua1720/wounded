<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Graft Usage Logging</h1>
        <p class="text-gray-600 dark:text-gray-400">Track and manage graft usage across all clinics</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="showUploadModal = true"
          class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
        >
          <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
          Bulk Upload
        </button>
        <button
          @click="showCreateForm = true"
          class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Log Usage
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Usage Logs</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ usageLogs.length }}</p>
          </div>
          <DocumentTextIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Week</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ thisWeekCount }}
            </p>
          </div>
          <CalendarIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Clinics</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ uniqueClinics.length }}</p>
          </div>
          <MapPinIcon class="w-8 h-8 text-purple-600 dark:text-purple-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Unique Patients</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ uniquePatientsCount }}
            </p>
          </div>
          <UserIcon class="w-8 h-8 text-orange-600 dark:text-orange-400" />
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="flex-1">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Search by serial, patient, or clinician..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          />
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <FunnelIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        <select
          v-model="clinicFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
          <option value="all">All Clinics</option>
          <option v-for="clinic in uniqueClinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
        </select>
      </div>
    </div>

    <!-- Usage Logs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Serial Number
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Patient
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Clinic
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Clinician
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Wound Site
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Usage Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="log in filteredLogs"
              :key="log.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ log.serial }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ log.patientName }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ log.clinicName }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ log.clinicianName }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ log.woundSite }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(log.usageDate) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="selectedLog = log"
                  class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  View Details
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Usage Log Form Modal -->
    <BaseModal v-model="showCreateForm" title="Log Graft Usage">
      <form @submit.prevent="handleCreateLog" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Number<span class="text-red-500">*</span></label>
            <input
              v-model="newLog.serial"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Enter serial number"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usage Date<span class="text-red-500">*</span></label>
            <input
              v-model="newLog.usageDate"
              type="date"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient Name<span class="text-red-500">*</span></label>
            <input
              v-model="newLog.patientName"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Enter patient name"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinician<span class="text-red-500">*</span></label>
            <input
              v-model="newLog.clinicianName"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Enter clinician name"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wound Site<span class="text-red-500">*</span></label>
          <input
            v-model="newLog.woundSite"
            type="text"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Enter wound site location"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
          <textarea
            v-model="newLog.notes"
            rows="3"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            placeholder="Enter any additional notes..."
          />
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="showCreateForm = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Log Usage
          </button>
        </div>
      </form>
    </BaseModal>

    <!-- Bulk Upload Modal -->
    <BaseModal v-model="showUploadModal" title="Bulk Upload Usage Logs">
      <div class="space-y-4">
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
          <DocumentTextIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
          <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Usage Log File</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Supports PDF, CSV, and Excel files for bulk usage log import
          </p>
          <input
            ref="usageUpload"
            type="file"
            accept=".pdf,.csv,.xlsx,.xls"
            class="hidden"
            @change="handleUsageUpload"
          />
          <button
            @click="$refs.usageUpload.click()"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 cursor-pointer"
          >
            <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
            Choose File
          </button>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
          <h4 class="font-medium text-blue-900 dark:text-blue-400 mb-2">File Format Requirements:</h4>
          <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-1">
            <li>• Serial Number (required)</li>
            <li>• Patient Name (required)</li>
            <li>• Usage Date (required)</li>
            <li>• Clinician Name (required)</li>
            <li>• Wound Site (required)</li>
            <li>• Notes (optional)</li>
          </ul>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            @click="showUploadModal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Cancel
          </button>
          <button
            @click="processUsageFile"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            Process File
          </button>
        </div>
      </div>
    </BaseModal>

    <!-- Usage Log Details Modal -->
    <BaseModal v-model="selectedLog" title="Usage Log Details">
      <template v-if="selectedLog">
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Number</label>
              <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.serial }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usage Date</label>
              <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedLog.usageDate) }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient</label>
              <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.patientName }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinician</label>
              <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.clinicianName }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</label>
              <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.clinicName }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wound Site</label>
              <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.woundSite }}</p>
            </div>
          </div>

          <div v-if="selectedLog.notes">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedLog.notes }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logged At</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDateTime(selectedLog.createdAt) }}</p>
          </div>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
  ArrowUpTrayIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  DocumentTextIcon,
  CalendarIcon,
  MapPinIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

interface UsageLog {
  id: string
  serial: string
  patientId: string
  patientName: string
  clinicId: string
  clinicName: string
  clinicianId: string
  clinicianName: string
  usageDate: string
  woundSite: string
  notes?: string
  createdAt: string
}

const usageLogs = ref<UsageLog[]>([
  {
    id: '1',
    serial: 'GM001',
    patientId: '1',
    patientName: 'John Doe',
    clinicId: '1',
    clinicName: 'St. Mary\'s Hospital',
    clinicianId: '1',
    clinicianName: 'Dr. Smith',
    usageDate: '2025-01-27',
    woundSite: 'Left ankle',
    notes: 'Applied to chronic wound, good coverage achieved',
    createdAt: '2025-01-27T10:30:00Z'
  },
  {
    id: '2',
    serial: 'WC003',
    patientId: '2',
    patientName: 'Jane Smith',
    clinicId: '2',
    clinicName: 'General Health Center',
    clinicianId: '2',
    clinicianName: 'Dr. Johnson',
    usageDate: '2025-01-26',
    woundSite: 'Right forearm',
    notes: 'Post-surgical application, healing well',
    createdAt: '2025-01-26T14:15:00Z'
  },
  {
    id: '3',
    serial: 'SK005',
    patientId: '3',
    patientName: 'Mike Wilson',
    clinicId: '1',
    clinicName: 'St. Mary\'s Hospital',
    clinicianId: '3',
    clinicianName: 'Dr. Brown',
    usageDate: '2025-01-25',
    woundSite: 'Left thigh',
    notes: 'Burn treatment, excellent graft adherence',
    createdAt: '2025-01-25T09:45:00Z'
  },
  {
    id: '4',
    serial: 'TC007',
    patientId: '4',
    patientName: 'Sarah Davis',
    clinicId: '3',
    clinicName: 'City Medical Center',
    clinicianId: '4',
    clinicianName: 'Dr. Garcia',
    usageDate: '2025-01-24',
    woundSite: 'Right foot',
    notes: 'Diabetic ulcer treatment, monitoring progress',
    createdAt: '2025-01-24T16:20:00Z'
  },
  {
    id: '5',
    serial: 'BM009',
    patientId: '5',
    patientName: 'Robert Johnson',
    clinicId: '2',
    clinicName: 'General Health Center',
    clinicianId: '5',
    clinicianName: 'Dr. Lee',
    usageDate: '2025-01-23',
    woundSite: 'Left hand',
    notes: 'Trauma reconstruction, graft integration successful',
    createdAt: '2025-01-23T11:30:00Z'
  }
])

const searchTerm = ref('')
const clinicFilter = ref('all')
const showCreateForm = ref(false)
const showUploadModal = ref(false)
const selectedLog = ref<UsageLog | null>(null)

const newLog = ref({
  serial: '',
  usageDate: '',
  patientName: '',
  clinicianName: '',
  woundSite: '',
  notes: ''
})

function handleCreateLog() {
  if (!newLog.value.serial || !newLog.value.usageDate || !newLog.value.patientName || 
      !newLog.value.clinicianName || !newLog.value.woundSite) {
    return
  }

  const now = new Date().toISOString()
  const log: UsageLog = {
    id: `${Math.floor(Math.random() * 100000)}`,
    serial: newLog.value.serial,
    patientId: `${Math.floor(Math.random() * 1000)}`,
    patientName: newLog.value.patientName,
    clinicId: '1', // Default clinic
    clinicName: 'St. Mary\'s Hospital', // Default clinic
    clinicianId: `${Math.floor(Math.random() * 1000)}`,
    clinicianName: newLog.value.clinicianName,
    usageDate: newLog.value.usageDate,
    woundSite: newLog.value.woundSite,
    notes: newLog.value.notes,
    createdAt: now
  }

  usageLogs.value.unshift(log)
  showCreateForm.value = false
  newLog.value = {
    serial: '',
    usageDate: '',
    patientName: '',
    clinicianName: '',
    woundSite: '',
    notes: ''
  }
}

function handleUsageUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files) {
    console.log('Usage file uploaded:', target.files[0]?.name)
    // Handle file upload logic here
  }
}

function processUsageFile() {
  // Simulate file processing
  console.log('Processing usage log file...')
  showUploadModal.value = false
}

const filteredLogs = computed(() => {
  return usageLogs.value.filter(log => {
    const matchesSearch = log.serial.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         log.patientName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         log.clinicianName.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesClinic = clinicFilter.value === 'all' || log.clinicId === clinicFilter.value
    return matchesSearch && matchesClinic
  })
})

const uniqueClinics = computed(() => {
  const clinics = usageLogs.value.map(log => ({ id: log.clinicId, name: log.clinicName }))
  return [...new Set(clinics.map(c => c.id))].map(id => {
    const clinic = clinics.find(c => c.id === id)
    return { id, name: clinic?.name || '' }
  })
})

const thisWeekCount = computed(() => {
  const weekAgo = new Date()
  weekAgo.setDate(weekAgo.getDate() - 7)
  return usageLogs.value.filter(log => {
    const logDate = new Date(log.usageDate)
    return logDate >= weekAgo
  }).length
})

const uniquePatientsCount = computed(() => {
  return new Set(usageLogs.value.map(log => log.patientId)).size
})

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString()
}

const formatDateTime = (dateStr: string) => {
  return new Date(dateStr).toLocaleString()
}
</script> 