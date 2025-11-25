<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Returns Management</h1>
			</div>
		</div>
    <ReturnsManagement
      :returns="returns"
      :brands="brands"
      :manufacturers="manufacturers"
      @submit-return="handleSubmitReturn"
      @upload-return-document="handleUploadReturnDocument"
      @view-return="handleViewReturn"
    />
    <BaseModal v-model="showDetailsModal" title="Return Details">
      <div v-if="selectedReturn" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Number</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.serialNumber }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(selectedReturn.status)}`">
                <component :is="getStatusIcon(selectedReturn.status)" class="w-4 h-4" />
                <span class="ml-1 capitalize">{{ selectedReturn.status }}</span>
              </span>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ getBrandName(selectedReturn.brandId) }}</p>
            </div>
            <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Size</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.size }}</p>
          </div>
        </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.expiryDate }}</p>
          </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Return Reason</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.returnReason }}</p>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Return Date</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.returnDate }}</p>
        </div>
      </div>
      <template #actions>
        <button @click="showDetailsModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Close</button>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import ReturnsManagement from '@/components/Returns/ReturnsManagement.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { ArrowPathIcon, ArrowUpTrayIcon, CameraIcon, PackageIcon, CheckCircleIcon, EyeIcon } from '@heroicons/vue/24/outline'

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