<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory & Serial Tracking</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage inventory and track serial numbers</p>
      </div>
      <button
        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        @click="showUploadModal = true"
      >
        <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
        Upload Delivery File
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Items</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ inventory.length }}</p>
          </div>
          <CubeIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Delivered</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ inventory.filter(item => item.status === 'delivered').length }}
            </p>
          </div>
          <CheckCircleIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Used</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ inventory.filter(item => item.status === 'used').length }}
            </p>
          </div>
          <CubeIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Expired</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ inventory.filter(item => item.status === 'expired').length }}
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
            placeholder="Search by serial, product, or brand..."
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
          <option value="expected">Expected</option>
          <option value="delivered">Delivered</option>
          <option value="used">Used</option>
          <option value="returned">Returned</option>
          <option value="expired">Expired</option>
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

    <!-- Inventory Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Serial Number
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Product
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Expiry Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Clinic
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="item in filteredInventory" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ item.serial }}</div>
                <div v-if="item.orderId" class="text-sm text-gray-500 dark:text-gray-400">Order: {{ item.orderId }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ item.productName }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.brand }} - {{ item.size }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(item.status)}`">
                  <component :is="getStatusIcon(item.status)" class="w-4 h-4" />
                  <span class="ml-1 capitalize">{{ item.status }}</span>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(item.expiryDate) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ item.clinicId || 'Unassigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  @click="selectedItem = item"
                  class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  View
                </button>
                <div class="inline-flex space-x-1">
                  <button
                    v-if="item.status === 'expected'"
                    @click="handleStatusUpdate(item.id, 'delivered')"
                    class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30"
                  >
                    Mark Delivered
                  </button>
                  <button
                    v-if="item.status === 'delivered'"
                    @click="handleStatusUpdate(item.id, 'used')"
                    class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30"
                  >
                    Mark Used
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
  <BaseModal v-model="showUploadModal" title="Upload Delivery File">
    <div class="space-y-4">
      <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
        <DocumentTextIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload manufacturer delivery file</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          Supports PDF, CSV, and Excel files for serial extraction
        </p>
        <input
          type="file"
          accept=".pdf,.csv,.xlsx,.xls"
          class="hidden"
          id="file-upload"
        />
        <label
          for="file-upload"
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

  <!-- Item Details Modal -->
  <BaseModal v-model="showItemModal" title="Inventory Item Details">
    <div v-if="selectedItem" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Number</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedItem.serial }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(selectedItem.status)}`">
            <component :is="getStatusIcon(selectedItem.status)" class="w-4 h-4" />
            <span class="ml-1 capitalize">{{ selectedItem.status }}</span>
          </span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedItem.productName }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand & Size</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedItem.brand }} - {{ selectedItem.size }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedItem.expiryDate) }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic ID</label>
          <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedItem.clinicId || 'Unassigned' }}</p>
        </div>
      </div>

      <div v-if="selectedItem.orderId">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order ID</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedItem.orderId }}</p>
      </div>

      <div v-if="selectedItem.receivedDate">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Received Date</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedItem.receivedDate) }}</p>
      </div>

      <div v-if="selectedItem.usedDate">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Used Date</label>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(selectedItem.usedDate) }}</p>
      </div>
    </div>
    <template #actions>
      <button
        @click="showItemModal = false"
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
import { ArrowUpTrayIcon, MagnifyingGlassIcon, FunnelIcon, CubeIcon, CheckCircleIcon, ExclamationTriangleIcon, ClockIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'

interface InventoryItem {
  id: string
  productId: string
  productName: string
  brand: string
  size: string
  serial: string
  expiryDate: string
  status: 'expected' | 'delivered' | 'used' | 'returned' | 'expired'
  clinicId?: string
  orderId?: string
  receivedDate?: string
  usedDate?: string
}

const mockInventory: InventoryItem[] = [
  {
    id: '1',
    productId: '1',
    productName: 'Graft Matrix Pro',
    brand: 'MedTech',
    size: 'Large',
    serial: 'GM001',
    expiryDate: '2025-12-31',
    status: 'used',
    clinicId: '1',
    orderId: 'ORD-001',
    receivedDate: '2025-01-27',
    usedDate: '2025-01-27'
  },
  {
    id: '2',
    productId: '1',
    productName: 'Graft Matrix Pro',
    brand: 'MedTech',
    size: 'Large',
    serial: 'GM002',
    expiryDate: '2025-12-31',
    status: 'delivered',
    clinicId: '1',
    orderId: 'ORD-001',
    receivedDate: '2025-01-27'
  },
  {
    id: '3',
    productId: '2',
    productName: 'Wound Care Plus',
    brand: 'BioMed',
    size: 'Medium',
    serial: 'WC003',
    expiryDate: '2025-11-30',
    status: 'expected',
    orderId: 'ORD-002'
  },
  {
    id: '4',
    productId: '3',
    productName: 'Skin Graft Advanced',
    brand: 'MedTech',
    size: 'Small',
    serial: 'SG004',
    expiryDate: '2025-10-15',
    status: 'expected'
  },
  {
    id: '5',
    productId: '4',
    productName: 'Tissue Regeneration Kit',
    brand: 'BioMed',
    size: 'Medium',
    serial: 'TR005',
    expiryDate: '2025-09-20',
    status: 'expired',
    clinicId: '2',
    orderId: 'ORD-003',
    receivedDate: '2024-09-15'
  },
  {
    id: '6',
    productId: '5',
    productName: 'Advanced Wound Matrix',
    brand: 'MedTech',
    size: 'Large',
    serial: 'AW006',
    expiryDate: '2026-03-15',
    status: 'delivered',
    clinicId: '3',
    orderId: 'ORD-004',
    receivedDate: '2025-01-30'
  }
]

const inventory = ref<InventoryItem[]>([...mockInventory])
const searchTerm = ref('')
const statusFilter = ref('all')
const brandFilter = ref('all')
const showUploadModal = ref(false)
const showItemModal = ref(false)
const selectedItem = ref<InventoryItem | null>(null)

const uniqueBrands = computed(() => [...new Set(inventory.value.map(item => item.brand))])

const filteredInventory = computed(() => {
  return inventory.value.filter(item => {
    const matchesSearch = item.serial.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      item.productName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      item.brand.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesStatus = statusFilter.value === 'all' || item.status === statusFilter.value
    const matchesBrand = brandFilter.value === 'all' || item.brand === brandFilter.value
    return matchesSearch && matchesStatus && matchesBrand
  })
})

function getStatusColor(status: string) {
  switch (status) {
    case 'expected': return 'bg-yellow-100 text-yellow-800'
    case 'delivered': return 'bg-blue-100 text-blue-800'
    case 'used': return 'bg-green-100 text-green-800'
    case 'returned': return 'bg-purple-100 text-purple-800'
    case 'expired': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

function getStatusIcon(status: string) {
  switch (status) {
    case 'expected': return ClockIcon
    case 'delivered': return CubeIcon
    case 'used': return CheckCircleIcon
    case 'expired': return ExclamationTriangleIcon
    default: return null
  }
}

function handleStatusUpdate(itemId: string, newStatus: InventoryItem['status']) {
  inventory.value = inventory.value.map(item => 
    item.id === itemId 
      ? { ...item, status: newStatus }
      : item
  )
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString()
}

// Watch for selectedItem changes to show modal
import { watch } from 'vue'
watch(selectedItem, (newItem) => {
  showItemModal.value = !!newItem
})
</script> 