<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="space-y-2">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Returns Management</h1>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl">Track and manage product returns with automated processing and real-time status updates</p>
      </div>
      <div class="flex items-center gap-4">
        <button @click="showStats = !showStats"
          class="flex items-center px-5 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          {{ showStats ? 'Hide' : 'Show' }} Stats
        </button>
        <button @click="showNewReturnForm = !showNewReturnForm"
          class="flex items-center px-5 py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-xl hover:from-orange-700 hover:to-red-700 transition-all duration-200 shadow-md hover:shadow-lg group">
          <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Return
        </button>
      </div>
    </div>

    <!-- Stats Panel -->
    <transition enter-active-class="transition ease-out duration-300"
      enter-from-class="transform opacity-0 -translate-y-4" enter-to-class="transform opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200" leave-from-class="transform opacity-100 translate-y-0"
      leave-to-class="transform opacity-0 -translate-y-4">
      <div v-if="showStats"
        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Return Statistics</h3>

        <!-- Main Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Returns</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
              <ArrowUpTrayIcon class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.pending }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
              <CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.processed }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Processed</p>
            </div>
          </div>
        </div>

        <hr class="border-gray-200 dark:border-gray-700">

        <!-- Status Breakdown -->
        <div class="mt-6">
          <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Status Breakdown</h4>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4">
            <div class="flex items-center justify-between">
              <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                <ArrowUpTrayIcon class="w-4 h-4 mr-2 text-yellow-500" /> Pending
              </span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ stats.pending }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                <CheckCircleIcon class="w-4 h-4 mr-2 text-blue-500" /> Confirmed
              </span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ stats.confirmed }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                <ArrowPathIcon class="w-4 h-4 mr-2 text-green-500" /> Processed
              </span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ stats.processed }}</span>
            </div>
          </div>
        </div>
      </div>
    </transition>
    <ReturnsManagement
      :returns="returns"
      :brands="brands"
      :manufacturers="manufacturers"
      :show-form="showNewReturnForm"
      @submit-return="handleSubmitReturn"
      @upload-return-document="handleUploadReturnDocument"
      @view-return="handleViewReturn"
      @close-form="showNewReturnForm = false"
    />
    <BaseModal v-model="showDetailsModal" title="Return Details" width="max-w-3xl">
      <div v-if="selectedReturn" class="space-y-6">
        <!-- Serial Number Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-5 text-white shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-blue-100 mb-1">Serial Number</p>
              <p class="text-2xl font-bold font-mono tracking-wide">{{ selectedReturn.serialNumber }}</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
              <span :class="`inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold ${getStatusColor(selectedReturn.status)} shadow-sm`">
                <component :is="getStatusIcon(selectedReturn.status)" class="w-4 h-4" />
                <span class="capitalize">{{ selectedReturn.status }}</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Brand Card -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center mb-2">
              <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-2">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Brand</h3>
            </div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ getBrandName(selectedReturn.brandId) }}</p>
          </div>

          <!-- Size Card -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center mb-2">
              <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-2">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
              </div>
              <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Size</h3>
            </div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.size }}</p>
          </div>

          <!-- Expiry Date Card -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center mb-2">
              <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mr-2">
                <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Expiry Date</h3>
            </div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.expiryDate }}</p>
          </div>

          <!-- Return Date Card -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center mb-2">
              <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-2">
                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Return Date</h3>
            </div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.returnDate }}</p>
          </div>
        </div>

        <!-- Return Reason Section -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mr-2">
              <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Return Reason</h3>
          </div>
          <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ selectedReturn.returnReason }}</p>
        </div>
      </div>
      <template #actions>
        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-600">
          <button @click="showDetailsModal = false" 
            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            Close
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import ReturnsManagement from '@/components/Returns/ReturnsManagement.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { ArrowPathIcon, ArrowUpTrayIcon, CheckCircleIcon, EyeIcon } from '@heroicons/vue/24/outline'

interface ReturnItem {
  id: string
  serialNumber: string
  brandId: string
  size: string
  expiryDate: string
  returnReason: string
  status: 'pending' | 'confirmed' | 'processed'
  returnDate: string
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

const brands = ref<Brand[]>([
  { id: '1', name: 'MedTech', manufacturerId: '1' },
  { id: '2', name: 'BioMed', manufacturerId: '2' }
])

const manufacturers = ref<Manufacturer[]>([
  { id: '1', name: 'MedTech Solutions' },
  { id: '2', name: 'BioMedical Industries' }
])

const returns = ref<ReturnItem[]>([
  {
    id: '1',
    serialNumber: 'SN123456',
    brandId: '1',
    size: '2cm x 2cm',
    expiryDate: '2024-12-31',
    returnReason: 'Expired Product',
    status: 'pending',
    returnDate: '2025-01-10'
  },
  {
    id: '2',
    serialNumber: 'SN654321',
    brandId: '2',
    size: '3cm x 3cm',
    expiryDate: '2025-03-15',
    returnReason: 'Damaged in Transit',
    status: 'confirmed',
    returnDate: '2025-01-12'
  },
  {
    id: '3',
    serialNumber: 'SN789012',
    brandId: '1',
    size: '4cm x 4cm',
    expiryDate: '2025-06-20',
    returnReason: 'Wrong Size Delivered',
    status: 'processed',
    returnDate: '2025-01-15'
  }
])

const showDetailsModal = ref(false)
const selectedReturn = ref<ReturnItem | null>(null)
const showStats = ref(false)
const showNewReturnForm = ref(false)

// Computed statistics
const stats = computed(() => {
  return {
    total: returns.value.length,
    pending: returns.value.filter(r => r.status === 'pending').length,
    confirmed: returns.value.filter(r => r.status === 'confirmed').length,
    processed: returns.value.filter(r => r.status === 'processed').length
  }
})

function getBrandName(brandId: string) {
  return brands.value.find(b => b.id === brandId)?.name || 'Unknown Brand'
}
function getStatusColor(status: string) {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    case 'confirmed': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
    case 'processed': return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}
function getStatusIcon(status: string) {
  switch (status) {
    case 'pending': return ArrowUpTrayIcon
    case 'confirmed': return CheckCircleIcon
    case 'processed': return ArrowPathIcon
    default: return ArrowPathIcon
  }
}
function handleSubmitReturn(data: any) {
  const newReturn: ReturnItem = {
    id: Date.now().toString(),
    serialNumber: data.serialNumber,
    brandId: data.brandId,
    size: data.size,
    expiryDate: data.expiryDate,
    returnReason: data.returnReason,
    status: 'pending',
    returnDate: new Date().toISOString().split('T')[0]
  }
  returns.value.push(newReturn)
}
function handleUploadReturnDocument(file: File, returnData: any) {
  // Simulate OCR extraction and add a new return
  const newReturn: ReturnItem = {
    id: Date.now().toString(),
    serialNumber: returnData.serialNumber || 'SN' + Math.random().toString().substr(2, 6),
    brandId: returnData.brandId,
    size: returnData.size || '2cm x 2cm',
    expiryDate: returnData.expiryDate || '2024-12-31',
    returnReason: returnData.returnReason,
    status: 'pending',
    returnDate: new Date().toISOString().split('T')[0]
  }
  returns.value.push(newReturn)
}
function handleViewReturn(item: ReturnItem) {
  selectedReturn.value = item
  showDetailsModal.value = true
}
</script> 