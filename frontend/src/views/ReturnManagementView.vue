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
          <BarChart3 class="w-5 h-5 mr-2" />
          {{ showStats ? 'Hide' : 'Show' }} Stats
        </button>
        <button @click="showNewReturnForm = !showNewReturnForm"
          class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
          <Plus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
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
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Returns</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.manual }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Manual Entry</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
              <Upload class="w-5 h-5 text-purple-600 dark:text-purple-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.upload }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">File Upload</p>
            </div>
          </div>
        </div>

        <hr class="border-gray-200 dark:border-gray-700">

        <!-- Return Reasons Breakdown -->
        <div class="mt-6">
          <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Top Return Reasons</h4>
          <div class="space-y-3">
            <div v-for="(reason, index) in stats.topReasons" :key="index" 
              class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors duration-150">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full" :class="getReasonColor(index)"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ reason.reason }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ ((reason.count / stats.total) * 100).toFixed(1) }}%
                </span>
                <span class="font-semibold text-gray-900 dark:text-white px-2 py-0.5 bg-white dark:bg-gray-800 rounded-md text-sm">
                  {{ reason.count }}
                </span>
              </div>
            </div>
            <div v-if="stats.topReasons.length === 0" class="text-center py-4 text-sm text-gray-500 dark:text-gray-400">
              No return reasons available
            </div>
          </div>
        </div>
      </div>
    </transition>
    <ReturnsManagement
      :returns="returns"
      :brands="brands"
      :manufacturers="manufacturers"
      :graft-sizes="graftSizes"
      :loading="isLoadingReturns"
      :pagination="pagination"
      :items-per-page="itemsPerPage"
      :current-page="currentPage"
      @submit-return="handleSubmitReturn"
      @upload-return-document="handleUploadReturnDocument"
      @view-return="handleViewReturn"
      @delete-return="handleDeleteReturn"
      @update:page="handlePageChange"
      @update:items-per-page="handleItemsPerPageChange"
    />

<!-- New Return Modal -->
    <NewReturnModal
      v-model="showNewReturnForm"
      :brands="brands"
      :manufacturers="manufacturers"
      :graft-sizes="graftSizes"
      :usage-logs="usageLogs"
      @submit-return="handleSubmitReturn"
    />
    <BaseModal v-model="showDetailsModal" title="" width="max-w-5xl">
      <div v-if="selectedReturn" class="space-y-6">
        <!-- Minimalist Header -->
        <div class="relative overflow-hidden">
          <!-- Background Pattern -->
          <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-blue-900/20 dark:to-indigo-900/20"></div>
          <div class="absolute inset-0 opacity-10 dark:opacity-5" style="background-image: radial-gradient(circle at 1px 1px, rgb(0 0 0 / 0.15) 1px, transparent 0); background-size: 20px 20px;"></div>
          
          <!-- Content -->
          <div class="relative px-8 py-6">
            <div class="flex items-start justify-between gap-6">
              <!-- Left: Serial Info -->
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                    <Undo2 class="w-5 h-5 text-white" />
                  </div>
                  <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Return ID</span>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white font-mono tracking-tight mb-3">
                  {{ selectedReturn.serialNumber }}
                </div>
                <div class="flex items-center gap-2">
                  <!-- Entry Type -->
                  <span v-if="selectedReturn.entryType === 'upload'" 
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                    <Upload class="w-3.5 h-3.5" />
                    Upload
                  </span>
                  <span v-else 
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Manual
                  </span>
                  <!-- Status -->
                  <span :class="`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold ${getStatusColor(selectedReturn.status)}`">
                    <component :is="getStatusIcon(selectedReturn.status)" class="w-3.5 h-3.5" />
                    <span class="capitalize">{{ selectedReturn.status }}</span>
                  </span>
                </div>
              </div>
              
              <!-- Right: Return Date -->
              <div class="text-right">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Returned</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ selectedReturn.returnDate }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Clean Card Layout -->
        <div class="px-8 pb-6 space-y-6">
          <!-- Product Details -->
          <div>
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Product Details</h3>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
              <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <!-- Brand Row -->
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Brand</span>
                  </div>
                  <span class="text-base font-semibold text-gray-900 dark:text-white">{{ getBrandName(selectedReturn.brandId) }}</span>
                </div>

                <!-- Product Code Row (conditional) -->
                <div v-if="selectedReturn.productCode" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Product Code</span>
                  </div>
                  <span class="text-base font-semibold text-gray-900 dark:text-white font-mono">{{ selectedReturn.productCode }}</span>
                </div>

                <!-- Manufacturer Row (conditional) -->
                <div v-if="selectedReturn.manufacturer" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Manufacturer</span>
                  </div>
                  <span class="text-base font-semibold text-gray-900 dark:text-white">{{ selectedReturn.manufacturer }}</span>
                </div>

                <!-- Size Row -->
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Size</span>
                  </div>
                  <span class="text-base font-semibold text-gray-900 dark:text-white">{{ selectedReturn.size }}</span>
                </div>

                <!-- Expiry Date Row -->
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Expiry Date</span>
                  </div>
                  <span class="text-base font-semibold text-gray-900 dark:text-white">{{ selectedReturn.expiryDate }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Return Information -->
          <div>
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Return Information</h3>
            <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-950/20 dark:to-rose-950/20 rounded-2xl border-2 border-red-200 dark:border-red-900/50 p-6">
              <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider mb-2">Reason for Return</div>
                  <div class="text-base font-semibold text-red-900 dark:text-red-100">{{ selectedReturn.returnReason }}</div>
                  <div v-if="selectedReturn.otherReason" class="mt-3 pt-3 border-t border-red-200 dark:border-red-900/50">
                    <div class="text-xs font-medium text-red-700 dark:text-red-300 mb-1.5">Additional Details</div>
                    <div class="text-sm text-red-800 dark:text-red-200 leading-relaxed">{{ selectedReturn.otherReason }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #actions>
        <div class="flex justify-end gap-3 px-6 py-4">
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
import { ref, computed, onMounted } from 'vue'
import ReturnsManagement from '@/components/Returns/ReturnsManagement.vue'
import NewReturnModal from '@/components/Returns/NewReturnModal.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { RefreshCcw, Upload, CheckCircle2, Eye, Undo2, BarChart3, Plus } from 'lucide-vue-next'
import api, { inventoryService } from '@/services/api'
import Swal from 'sweetalert2'

interface ReturnItem {
  id: string
  serialNumber: string
  entryType?: 'manual' | 'upload'
  brandId: string
  graftSizeId: string
  size: string
  expiryDate: string
  returnReason: string
  otherReason?: string | null
  status: 'pending' | 'confirmed' | 'processed'
  returnDate: string
  extractedFromImage: boolean
  uploadedFileName?: string
  uploadedFileUrl?: string
  uploadedFileType?: string
  productCode?: string
  manufacturer?: string
  ocrSerialNumber?: string
  ocrExpiryDate?: string
  ocrProductCode?: string
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

interface GraftSize {
  id: string
  size: string
  area: number
  brandId: string
}

interface UsageLog {
  id: string
  reference: string
}

const brands = ref<Brand[]>([])

const manufacturers = ref<Manufacturer[]>([])

const graftSizes = ref<GraftSize[]>([])

const returns = ref<ReturnItem[]>([])

// Pagination state
const itemsPerPage = ref(10)
const currentPage = ref(1)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const usageLogs = ref<UsageLog[]>([])

const showDetailsModal = ref(false)
const selectedReturn = ref<ReturnItem | null>(null)
const showStats = ref(false)
const showNewReturnForm = ref(false)
const isLoadingReturns = ref(false)

// Fetch brands from database
async function fetchBrands() {
  try {
    const { data } = await api.get('/management/brands', {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    // Map the API response to match our component's expected structure
    brands.value = data.brandData.map((brand: any) => ({
      id: brand.id,
      name: brand.brandName,
      manufacturerId: brand.manufacturerId ? brand.manufacturerId.toString() : ''
    }))
  } catch (error) {
    console.error('Error fetching brands:', error)
  }
}

// Fetch manufacturers from database
async function fetchManufacturers() {
  try {
    const { data } = await api.get('/management/manufacturers', {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    manufacturers.value = data.manufacturerData.map((mfr: any) => ({
      id: mfr.id.toString(),
      name: mfr.manufacturerName
    }))
  } catch (error) {
    console.error('Error fetching manufacturers:', error)
  }
}

// Fetch graft sizes from database
async function fetchGraftSizes() {
  try {
    const { data } = await api.get('/management/brands', {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    // Extract graft sizes from all brands
    const allGraftSizes: GraftSize[] = []
    data.brandData.forEach((brand: any) => {
      if (brand.graftSizes && Array.isArray(brand.graftSizes)) {
        brand.graftSizes.forEach((gs: any) => {
          allGraftSizes.push({
            id: gs.id,
            size: gs.size,
            area: gs.area,
            brandId: brand.id
          })
        })
      }
    })
    graftSizes.value = allGraftSizes
  } catch (error) {
    console.error('Error fetching graft sizes:', error)
  }
}

// Fetch returns from database with pagination
async function fetchReturns(page = 1) {
  try {
    isLoadingReturns.value = true
    const { data } = await api.get('/management/returns', {
      params: {
        page: page,
        per_page: itemsPerPage.value
      },
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    // Map the API response to match our component's expected structure
    returns.value = data.returns.map((ret: any) => ({
      id: ret.id,
      // Use OCR serial for upload, usage log serial for manual
      serialNumber: ret.entryType === 'upload' 
        ? (ret.ocrSerialNumber || ret.id) 
        : (ret.serialNumber || ret.id),
      entryType: ret.entryType || 'manual',
      brandId: ret.brandId,
      graftSizeId: ret.graftSizeId, // Include graftSizeId from API
      size: ret.graftSize,
      // Use OCR expiry for upload, usage log expiry for manual
      expiryDate: ret.entryType === 'upload'
        ? (ret.ocrExpiryDate || '')
        : (ret.expiryDate || ''),
      returnReason: ret.reason,
      otherReason: ret.other,
      status: 'pending', // Default status
      returnDate: ret.returnedAt.split(' ')[0],
      extractedFromImage: false, // Default to false
      // Use OCR product code for upload, regular for manual
      productCode: ret.entryType === 'upload'
        ? (ret.ocrProductCode || '')
        : (ret.productCode || ''),
      manufacturer: ret.manufacturerName || '',
      ocrSerialNumber: ret.ocrSerialNumber || '',
      ocrExpiryDate: ret.ocrExpiryDate || '',
      ocrProductCode: ret.ocrProductCode || ''
    }))
    
    // Update pagination metadata from backend
    if (data.meta) {
      pagination.value = {
        current_page: data.meta.current_page,
        last_page: data.meta.last_page,
        per_page: data.meta.per_page,
        total: data.meta.total,
      }
      currentPage.value = data.meta.current_page
    }
  } catch (error) {
    console.error('Error fetching returns:', error)
  } finally {
    isLoadingReturns.value = false
  }
}

// Fetch usage logs from database
async function fetchUsageLogs() {
  try {
    const { data } = await inventoryService.getUsageLogs()
    
    // Transform API response to match expected format
    usageLogs.value = data.data.map((log: any) => ({
      id: log.id?.toString() || '',
      reference: `${log.serialNumber || 'Unknown'} - ${log.patientName || 'Unknown Patient'}`
    }))
  } catch (error) {
    console.error('Error fetching usage logs:', error)
  }
}

// Load data on component mount
onMounted(() => {
  fetchBrands()
  fetchManufacturers()
  fetchGraftSizes()
  fetchReturns()
  fetchUsageLogs()
})

// Computed statistics (use pagination total for accurate count)
const stats = computed(() => {
  const total = pagination.value.total
  const manual = returns.value.filter(r => r.entryType === 'manual').length
  const upload = returns.value.filter(r => r.entryType === 'upload').length
  
  // Count returns by reason
  const reasonCounts: { [key: string]: number } = {}
  returns.value.forEach(r => {
    const reason = r.returnReason || 'Unknown'
    reasonCounts[reason] = (reasonCounts[reason] || 0) + 1
  })
  
  // Sort by count and get top reasons
  const topReasons = Object.entries(reasonCounts)
    .map(([reason, count]) => ({ reason, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 5) // Top 5 reasons
  
  return {
    total,
    manual,
    upload,
    topReasons
  }
})

function getReasonColor(index: number) {
  const colors = [
    'bg-red-500',
    'bg-orange-500',
    'bg-yellow-500',
    'bg-green-500',
    'bg-blue-500'
  ]
  return colors[index] || 'bg-gray-500'
}

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
    case 'pending': return Upload
    case 'confirmed': return CheckCircle2
    case 'processed': return RefreshCcw
    default: return RefreshCcw
  }
}
async function handleSubmitReturn(data: any) {
  try {
    // Check if this is an edit (has id) or new entry
    const isEdit = !!data.id
    
    if (isEdit) {
      // Update existing return
      const updatePayload: any = {
        brandId: data.brandId,
        graftSizeId: data.graftSizeId,
        reason: data.returnReason,
        other: data.otherReason
      }
      
      // For upload entries, include OCR fields
      if (data.entryType === 'upload') {
        updatePayload.ocrSerialNumber = data.serialNumber
        updatePayload.ocrProductCode = data.productCode
        updatePayload.ocrExpiryDate = data.expiryDate
        updatePayload.ocrSize = data.size
      }
      
      // Extract numeric ID from inv- format for backend validation
      let numericGraftLogId = null
      if (data.graftLogId) {
        const extractedId = String(data.graftLogId).replace(/^inv-/, '')
        numericGraftLogId = Number(extractedId)
        if (isNaN(numericGraftLogId) || numericGraftLogId <= 0) {
          numericGraftLogId = null
        }
      }
      
      const submitUpdatePayload = {
        ...updatePayload,
        graftLogId: numericGraftLogId
      }
      
      const response = await api.put(`/management/returns/${data.id}`, submitUpdatePayload, {
        headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
      })
      
      // Update the local array with proper data mapping based on entry type
      const index = returns.value.findIndex(r => r.id === data.id)
      if (index !== -1) {
        const updatedData = response.data.data
        
        returns.value[index] = {
          ...returns.value[index],
          brandId: updatedData.brandId,
          graftSizeId: updatedData.graftSizeId,
          returnReason: updatedData.reason,
          otherReason: updatedData.other,
          // For upload entries, update OCR fields and serial number
          ...(data.entryType === 'upload' && {
            serialNumber: updatedData.ocrSerialNumber || data.serialNumber,
            productCode: updatedData.ocrProductCode || data.productCode,
            expiryDate: updatedData.ocrExpiryDate || data.expiryDate,
            size: data.size
          }),
          // For manual entries, update from backend response (usage log data)
          ...(data.entryType === 'manual' && {
            serialNumber: updatedData.serialNumber || updatedData.id,
            expiryDate: updatedData.expiryDate || '',
            productCode: updatedData.productCode || '',
            size: updatedData.graftSize || ''
          })
        }
      }
      
      console.log('Return updated successfully:', response.data)
      
      // Show success message
      await Swal.fire({
        icon: 'success',
        title: 'Return Updated!',
        text: 'The return has been updated successfully.',
        timer: 2000,
        showConfirmButton: false
      })
    } else {
      // Create new return
      // Map field names to match backend expectations
      // NewReturnModal sends 'reason', backend expects 'reason'
      // NewReturnModal sends 'other', backend expects 'other'
      
      // Extract numeric ID from inv- format for backend validation
      let numericGraftLogId = null
      if (data.graftLogId) {
        const extractedId = String(data.graftLogId).replace(/^inv-/, '')
        numericGraftLogId = Number(extractedId)
        // Validate that we got a valid number
        if (isNaN(numericGraftLogId) || numericGraftLogId <= 0) {
          await Swal.fire({
            icon: 'error',
            title: 'Invalid Usage Log ID',
            text: 'The usage log ID format is invalid. Please select a valid usage log from the dropdown.',
            confirmButtonColor: '#dc2626'
          })
          return
        }
      }
      
      // Ensure we're sending the correct field names
      const submitData = {
        brandId: data.brandId,
        graftSizeId: data.graftSizeId,
        reason: data.reason || data.returnReason, // Handle both field names
        other: data.other || data.otherReason || null,
        entryType: data.entryType || 'manual',
        graftLogId: numericGraftLogId
      }
      
      console.log('Submitting return data:', submitData)
          
      const response = await api.post('/management/returns', submitData, {
        headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
      })
      
      // Add the new return to the local list
      const newReturn: ReturnItem = {
        id: response.data.data.id,
        // Use OCR serial for upload, usage log serial for manual
        serialNumber: response.data.data.entryType === 'upload'
          ? (response.data.data.ocrSerialNumber || response.data.data.id)
          : (response.data.data.serialNumber || response.data.data.id),
        entryType: response.data.data.entryType || 'manual',
        brandId: response.data.data.brandId,
        graftSizeId: response.data.data.graftSizeId,
        size: response.data.data.graftSize,
        // Use OCR expiry for upload, usage log expiry for manual
        expiryDate: response.data.data.entryType === 'upload'
          ? (response.data.data.ocrExpiryDate || '')
          : (response.data.data.expiryDate || ''),
        returnReason: response.data.data.reason,
        otherReason: response.data.data.other,
        status: 'pending',
        returnDate: response.data.data.returnedAt.split(' ')[0],
        extractedFromImage: false,
        // Use OCR product code for upload, regular for manual
        productCode: response.data.data.entryType === 'upload'
          ? (response.data.data.ocrProductCode || '')
          : (response.data.data.productCode || ''),
        manufacturer: response.data.data.manufacturerName || '',
        ocrSerialNumber: response.data.data.ocrSerialNumber || '',
        ocrExpiryDate: response.data.data.ocrExpiryDate || '',
        ocrProductCode: response.data.data.ocrProductCode || ''
      }
      returns.value.push(newReturn)
      
      // Close the modal
      showNewReturnForm.value = false
      
      console.log('Return created successfully:', response.data)
      
      // Show success message
      await Swal.fire({
        icon: 'success',
        title: 'Return Created!',
        text: 'The return has been created successfully.',
        timer: 2000,
        showConfirmButton: false
      })
    }
  } catch (error: any) {
    console.error('Error submitting return:', error)
    
    // Extract error message from response
    let errorMessage = 'An unexpected error occurred. Please try again.'
    let errorDetails = ''
    
    if (error.response?.data) {
      const errorData = error.response.data
      
      // Check if there's a message
      if (errorData.message) {
        errorMessage = errorData.message
      }
      
      // Check if there are validation errors
      if (errorData.errors) {
        const errors = errorData.errors
        const errorList = Object.keys(errors).map(key => {
          const messages = Array.isArray(errors[key]) ? errors[key] : [errors[key]]
          return messages.join(', ')
        })
        errorDetails = errorList.join('\n')
      }
    }
    
    // Show error message with SweetAlert
    await Swal.fire({
      icon: 'error',
      title: data.id ? 'Update Failed' : 'Creation Failed',
      text: errorMessage,
      html: errorDetails ? `<div class="text-left mt-2"><strong>Details:</strong><br/>${errorDetails.split('\n').join('<br/>')}</div>` : errorMessage,
      confirmButtonColor: '#dc2626',
      confirmButtonText: 'OK'
    })
  }
}
// Handle page change from pagination component
function handlePageChange(page: number) {
  currentPage.value = page
  fetchReturns(page)
}

// Handle items per page change
function handleItemsPerPageChange(newItemsPerPage: number) {
  itemsPerPage.value = newItemsPerPage
  currentPage.value = 1
  fetchReturns(1)
}

function handleUploadReturnDocument(file: File, returnData: any) {
  // Simulate OCR extraction and add a new return
  const newReturn: ReturnItem = {
    id: Date.now().toString(),
    serialNumber: returnData.serialNumber || 'SN' + Math.random().toString().substr(2, 6),
    brandId: returnData.brandId,
    graftSizeId: returnData.graftSizeId || '',
    size: returnData.size || '2cm x 2cm',
    expiryDate: returnData.expiryDate || '2024-12-31',
    returnReason: returnData.returnReason,
    otherReason: returnData.otherReason || null,
    status: 'pending',
    returnDate: new Date().toISOString().split('T')[0],
    extractedFromImage: true,
    productCode: returnData.productCode || '',
    manufacturer: returnData.manufacturer || ''
  }
  returns.value.push(newReturn)
}
function handleViewReturn(item: ReturnItem) {
  selectedReturn.value = item
  showDetailsModal.value = true
}

async function handleDeleteReturn(item: ReturnItem) {
  // Show confirmation dialog
  const result = await Swal.fire({
    title: 'Delete Return?',
    html: `Are you sure you want to delete this return?<br/><br/><strong>Serial Number:</strong> ${item.serialNumber}<br/><strong>Brand:</strong> ${getBrandName(item.brandId)}`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  })

  if (!result.isConfirmed) return

  try {
    await api.delete(`/management/returns/${item.id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    
    // Remove the item from the local returns array
    returns.value = returns.value.filter(r => r.id !== item.id)
    
    console.log('Return deleted successfully:', item.id)
    
    // Show success message
    await Swal.fire({
      icon: 'success',
      title: 'Deleted!',
      text: 'The return has been deleted successfully.',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error: any) {
    console.error('Error deleting return:', error)
    
    // Extract error message
    const errorMessage = error.response?.data?.message || 'Failed to delete return. Please try again.'
    
    await Swal.fire({
      icon: 'error',
      title: 'Delete Failed',
      text: errorMessage,
      confirmButtonColor: '#dc2626',
      confirmButtonText: 'OK'
    })
  }
}
</script> 