<template>
  <div class="space-y-6">
    <!-- Filters Card -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <div class="flex flex-col lg:flex-row gap-6">
        <div class="flex-1">
          <div class="relative">
            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
            <input v-model="searchQuery" type="text" placeholder="Search returns by serial number, brand, reason..."
              class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-orange-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
          </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="relative">
            <select
              v-model="statusFilter"
              class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-orange-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200"
            >
              <option value="all">All Entry Types</option>
              <option value="manual">Manual Entry</option>
              <option value="upload">File Upload</option>
            </select>
            <svg class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <div class="relative">
            <select
              v-model="itemsPerPage"
              class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-orange-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200"
            >
              <option value="10">10 per page</option>
              <option value="25">25 per page</option>
              <option value="50">50 per page</option>
            </select>
            <ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
        </div>
      </div>
    </div>

    <!-- Returns Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial Number</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entry Type</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Brand</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Graft</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Return Reason</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Returned Date</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-if="props.loading">
              <td colspan="7" class="pt-10 text-center">
                <div class="inline-block">
                  <div class="w-8 h-8 rounded-full border-4 border-gray-200 border-t-red-600 animate-spin mx-auto"></div>
                  <div class="text-center text-gray-400 py-4 text-sm">Fetching Returns</div>
                </div>
              </td>
            </tr>
            <template v-else>
              <tr v-for="item in returns" :key="item.id" class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.serialNumber || '-' }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.productCode || 'No Code' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <span v-if="item.entryType === 'manual'" 
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Manual
                </span>
                <span v-else-if="item.entryType === 'upload'" 
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                  </svg>
                  Upload
                </span>
                <span v-else class="text-xs text-gray-400 dark:text-gray-500">-</span>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ getBrandName(item.brandId) }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.size || '-' }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.returnReason }}</div>
                <div v-if="item.otherReason" class="text-xs text-gray-500 dark:text-gray-400 italic mt-1">{{ item.otherReason }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(item.returnDate) }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                  <button @click="viewReturn(item)" 
                    class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-400 rounded-lg transition-all duration-200"
                    title="View Details">
                    <Eye class="w-5 h-5" />
                  </button>
                  <button @click="editReturn(item)" 
                    class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-amber-100 dark:hover:bg-amber-900/30 hover:text-amber-700 dark:hover:text-amber-400 rounded-lg transition-all duration-200"
                    title="Edit Return">
                    <Edit3 class="w-5 h-5" />
                  </button>
                  <button @click="confirmDelete(item)" 
                    class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-700 dark:hover:text-red-400 rounded-lg transition-all duration-200"
                    title="Delete Return">
                    <Trash2 class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            </template>
          </tbody>
        </table>
      </div>
      <div v-if="filteredReturns.length === 0 && !props.loading" class="text-center py-16">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
            <RefreshCcw class="w-8 h-8 text-gray-400 dark:text-gray-500" />
          </div>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No returns found</h3>
        <p class="text-gray-600 dark:text-gray-400">No return records match your current filters.</p>
      </div>
      
      <!-- Pagination -->
      <Pagination 
        v-if="pagination.total > 0"
        :pagination="pagination"
        @update:page="changePage"
      />
    </div>
    <BaseModal v-model="showViewModal" title="Return Details" width="max-w-3xl">
      <div v-if="selectedReturn" class="space-y-6">
        <!-- Loading Indicator -->
        <div v-if="isLoadingDetails" class="flex items-center justify-center py-8">
          <div class="w-6 h-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin mr-3"></div>
          <span class="text-sm text-gray-600 dark:text-gray-400">Loading details...</span>
        </div>

        <!-- Blue Banner (Serial Part) - Keep as is -->
        <div class="relative bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-700 dark:to-indigo-800 rounded-2xl p-5 shadow-md overflow-hidden">
          <!-- Simple decorative accent -->
          <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12"></div>
          
          <div class="relative flex items-center justify-between gap-4">
            <!-- Left: Serial Number -->
            <div class="flex-1 min-w-0">
              <p class="text-blue-100 dark:text-blue-200 text-xs font-medium mb-1">Serial Number</p>
              <p class="text-white text-xl font-bold font-mono break-all">{{ selectedReturn.serialNumber || '-' }}</p>
            </div>
            
            <!-- Right: Badges -->
            <div class="flex items-center gap-2 flex-shrink-0">
              <!-- Entry Type Badge -->
              <span v-if="selectedReturn.entryType === 'upload'" 
                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/20 text-white backdrop-blur-sm border border-white/30">
                Upload
              </span>
              <span v-else 
                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/20 text-white backdrop-blur-sm border border-white/30">
                Manual
              </span>
              
              <!-- Product Code Badge -->
              <span v-if="selectedReturn.productCode" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-semibold bg-white/20 text-white backdrop-blur-sm border border-white/30">
                {{ selectedReturn.productCode }}
              </span>
              
              <!-- Return Date Badge -->
              <div class="bg-white/20 backdrop-blur-sm rounded-md px-3 py-1 border border-white/30">
                <p class="text-xs font-semibold text-white">{{ formatDate(selectedReturn.returnDate) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Grid - Patient Management Style -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Brand -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center space-x-3">
              <div class="h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                <Tag class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Brand</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ getBrandName(selectedReturn.brandId) }}</p>
                <p v-if="selectedReturn.manufacturer" class="text-xs text-gray-500 dark:text-gray-400">{{ selectedReturn.manufacturer }}</p>
              </div>
            </div>
          </div>

          <!-- Size -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center space-x-3">
              <div class="h-10 w-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                <Maximize2 class="w-5 h-5 text-purple-600 dark:text-purple-400" />
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Size</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.size || '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Expiry Date -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center space-x-3">
              <div class="h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                <Calendar class="w-5 h-5 text-orange-600 dark:text-orange-400" />
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Expiry Date</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.expiryDate ? formatDate(selectedReturn.expiryDate) : '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Entry Type -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center space-x-3">
              <div class="h-10 w-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                <Upload class="w-5 h-5 text-teal-600 dark:text-teal-400" />
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Entry Type</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.entryType === 'upload' ? 'File Upload' : 'Manual Entry' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Return Reason - Highlighted Section -->
        <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-950/20 dark:to-rose-950/20 rounded-2xl border-2 border-red-200 dark:border-red-900/50 p-5">
          <div class="flex items-start gap-3">
            <div class="h-10 w-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <AlertTriangle class="w-5 h-5 text-red-600 dark:text-red-400" />
            </div>
            <div class="flex-1">
              <p class="text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wide mb-1">Reason for Return</p>
              <p class="text-base font-semibold text-red-900 dark:text-red-100">{{ selectedReturn.returnReason }}</p>
              <div v-if="selectedReturn.otherReason" class="mt-3 pt-3 border-t border-red-200 dark:border-red-900/50">
                <p class="text-xs font-medium text-red-700 dark:text-red-300 mb-1">Additional Details</p>
                <p class="text-sm text-red-800 dark:text-red-200 leading-relaxed">{{ selectedReturn.otherReason }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Uploaded File Preview (if exists) -->
        <div v-if="selectedReturn.extractedFromImage && selectedReturn.uploadedFileName && selectedReturn.uploadedFileUrl">
          <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Uploaded Document</h3>
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center space-x-3 mb-4">
              <div class="h-10 w-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                <ImageIcon class="w-5 h-5 text-teal-600 dark:text-teal-400" />
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.uploadedFileName }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ selectedReturn.uploadedFileType || 'File' }}</p>
              </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
              <template v-if="selectedReturn.uploadedFileType && selectedReturn.uploadedFileType.startsWith('image/')">
                <img :src="selectedReturn.uploadedFileUrl" :alt="selectedReturn.uploadedFileName" 
                  class="max-h-64 mx-auto" />
              </template>
              <template v-else-if="selectedReturn.uploadedFileType === 'application/pdf'">
                <iframe :src="selectedReturn.uploadedFileUrl" class="w-full h-64" />
              </template>
              <template v-else>
                <div class="p-4 text-center">
                  <a :href="selectedReturn.uploadedFileUrl" target="_blank" 
                    class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <Download class="w-4 h-4 mr-2" />
                    Download/View File
                  </a>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
      <template #actions>
        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-600">
          <button @click="showViewModal = false" 
            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            Close
          </button>
        </div>
      </template>
    </BaseModal>

    <!-- Edit Return Modal -->
    <BaseModal v-model="showEditModal" title="Edit Return" width="max-w-2xl">
      <div v-if="editedReturn" class="space-y-6">
        <!-- Loading Indicator -->
        <div v-if="isLoadingDetails" class="flex items-center justify-center py-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
          <div class="w-6 h-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin mr-3"></div>
          <span class="text-sm text-gray-600 dark:text-gray-400">Loading fresh data...</span>
        </div>
        <!-- Serial Number Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-5 text-white shadow-lg">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <p class="text-sm text-blue-100 mb-1">Serial Number {{ editedReturn.entryType === 'manual' ? '(Read-only)' : '' }}</p>
              <p v-if="editedReturn.entryType === 'manual'" class="text-2xl font-bold font-mono tracking-wide">{{ editedReturn.serialNumber || '-' }}</p>
              <input v-else v-model="editSerialNumber" type="text"
                class="text-xl font-bold font-mono tracking-wide bg-white/20 border-2 border-white/30 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 w-full"
                placeholder="Enter serial number" />
            </div>
            <div class="ml-4">
              <span :class="`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium ${
                editedReturn.entryType === 'manual' 
                  ? 'bg-blue-100 text-blue-800' 
                  : 'bg-purple-100 text-purple-800'
              }`">
                {{ editedReturn.entryType === 'manual' ? 'Manual' : 'Upload' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Editable Fields -->
        <div class="space-y-4">
          <!-- Upload Entry: All fields editable -->
          <template v-if="editedReturn.entryType === 'upload'">
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-3">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs text-purple-700 dark:text-purple-300 leading-tight">
                  <strong>Upload Entry:</strong> All fields are editable to correct OCR extraction errors.
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Code</label>
                <input v-model="editProductCode" type="text"
                  class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  placeholder="Enter product code" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Size</label>
                <input v-model="editSize" type="text"
                  class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  placeholder="e.g., 2cm x 2cm" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiry Date</label>
                <input v-model="editExpiryDate" type="date"
                  class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Return Date (Read-only)</label>
                <input :value="formatDate(editedReturn.returnDate)" type="text" disabled
                  class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed" />
              </div>
            </div>
          </template>

          <!-- Manual Entry: Limited fields -->
          <template v-else>
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs text-blue-700 dark:text-blue-300 leading-tight">
                  <strong>Manual Entry:</strong> Serial number, size, and expiry date are from the linked usage log and cannot be changed.
                </p>
              </div>
            </div>
          </template>

          <!-- Brand Selection (Always Editable) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Brand *</label>
            <select v-model="editBrandId"
              class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option value="">Select Brand</option>
              <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
            </select>
          </div>

          <!-- Return Reason (Always Editable) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Return Reason *</label>
            <select v-model="editReturnReason"
              class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option value="">Select Reason</option>
              <option value="Expired Product">Expired Product</option>
              <option value="Damaged in Transit">Damaged in Transit</option>
              <option value="Wrong Size Delivered">Wrong Size Delivered</option>
              <option value="Unused - Patient Cancelled">Unused - Patient Cancelled</option>
              <option value="Quality Issue">Quality Issue</option>
              <option value="Overstock">Overstock</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <!-- Other Reason (if selected) -->
          <div v-if="editReturnReason === 'Other'">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Please specify the reason *</label>
            <textarea v-model="editOtherReason" rows="3"
              class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Please provide details about the return reason..." required></textarea>
          </div>

          <!-- Read-only Info for Manual Entry -->
          <div v-if="editedReturn.entryType === 'manual'" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-2">
            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Read-Only Information (From Usage Log)</h4>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Size</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ editedReturn.size || '-' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Expiry Date</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ editedReturn.expiryDate ? formatDate(editedReturn.expiryDate) : '-' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Product Code</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ editedReturn.productCode || '-' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Return Date</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(editedReturn.returnDate) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #actions>
        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-600">
          <button @click="cancelEdit" 
            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            Cancel
          </button>
          <button @click="saveEdit" 
            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium">
            Save Changes
          </button>
        </div>
      </template>
    </BaseModal>

    <!-- Delete Confirmation Modal -->
    <BaseModal v-model="showDeleteModal" title="Delete Return" width="max-w-md">
      <div class="space-y-6">
        <!-- Warning Icon -->
        <div class="flex justify-center">
          <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
        </div>

        <!-- Message -->
        <div class="text-center space-y-2">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Are you sure?</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">You are about to delete this return record:</p>
        </div>

        <!-- Return Details -->
        <div v-if="returnToDelete" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-3">
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Serial Number</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ returnToDelete.serialNumber }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Brand</p>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ getBrandName(returnToDelete.brandId) }}</p>
          </div>
        </div>

        <!-- Warning Message -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
          <p class="text-xs font-semibold text-red-800 dark:text-red-400 flex items-center justify-center mb-2">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Warning: This action cannot be reverted
          </p>
          <p class="text-xs text-red-700 dark:text-red-300 text-center">
            Once deleted, this return record will be permanently removed from the system.
          </p>
        </div>
      </div>
      <template #actions>
        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-600">
          <button @click="cancelDelete" 
            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            Cancel
          </button>
          <button @click="handleDelete" 
            class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md hover:shadow-lg font-medium flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete Return
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { RefreshCcw, Upload, CheckCircle2, Eye, Search, Edit3, Trash2, Tag, Maximize2, Calendar, AlertTriangle, Image as ImageIcon, Download, ChevronDown } from 'lucide-vue-next'
import BaseModal from '@/components/common/BaseModal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import api from '@/services/api'

interface Props {
  returns?: ReturnItem[]
  brands?: Brand[]
  manufacturers?: Manufacturer[]
  graftSizes?: GraftSize[]
  showForm?: boolean
  loading?: boolean
  pagination?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  itemsPerPage?: number
  currentPage?: number
}

const props = withDefaults(defineProps<Props>(), {
  returns: () => [],
  brands: () => [],
  manufacturers: () => [],
  graftSizes: () => [],
  showForm: false,
  loading: false,
  pagination: () => ({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  }),
  itemsPerPage: 10,
  currentPage: 1
})

const emit = defineEmits<{
  'submit-return': [data: any]
  'view-return': [item: ReturnItem]
  'delete-return': [item: ReturnItem]
  'update:page': [page: number]
  'update:items-per-page': [itemsPerPage: number]
}>()

interface ReturnItem {
  id: string
  serialNumber: string
  entryType?: 'manual' | 'upload'
  brandId: string
  graftSizeId: string
  size: string
  expiryDate: string
  returnReason: string
  otherReason?: string | null // Custom reason when 'Other' is selected
  status: 'pending' | 'confirmed' | 'processed'
  returnDate: string
  extractedFromImage: boolean // Added for new column
  uploadedFileName?: string // Mock uploaded file name
  uploadedFileUrl?: string // Mock uploaded file URL
  uploadedFileType?: string // File MIME type
  productCode?: string // Added for new field
  manufacturer?: string // Added for new field
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

const brands = ref<Brand[]>(props.brands || [])

const manufacturers = ref<Manufacturer[]>(props.manufacturers || [])

const graftSizes = ref<GraftSize[]>(props.graftSizes || [])

const returns = ref<ReturnItem[]>(props.returns || [])

// Use props for server-side pagination
const itemsPerPage = computed({
  get: () => props.itemsPerPage,
  set: (val) => emit('update:items-per-page', val)
})

const currentPage = computed({
  get: () => props.currentPage,
  set: (val) => emit('update:page', val)
})

const pagination = computed(() => props.pagination)

// Watch for prop changes and update local refs
watch(() => props.returns, (newReturns) => {
  returns.value = newReturns
}, { deep: true })

watch(() => props.brands, (newBrands) => {
  brands.value = newBrands
}, { deep: true })

watch(() => props.manufacturers, (newManufacturers) => {
  manufacturers.value = newManufacturers
}, { deep: true })

watch(() => props.graftSizes, (newGraftSizes) => {
  graftSizes.value = newGraftSizes
}, { deep: true })

const searchQuery = ref('')
const statusFilter = ref('all')

// Note: Server-side filtering/searching should be implemented in the backend
// For now, returns are already paginated from the server
const filteredReturns = computed(() => returns.value)

function changePage(page: number) {
  if (page < 1 || page > pagination.value.last_page) return
  emit('update:page', page)
}

function getBrandName(brandId: string) {
  return brands.value.find(b => b.id === brandId)?.name || 'Unknown Brand'
}

function getManufacturerName(manufacturerId: string) {
  return manufacturers.value.find(m => m.id === manufacturerId)?.name || 'Unknown Manufacturer'
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

function formatDate(dateString: string) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function isExpired(dateString: string) {
  if (!dateString) return false
  const today = new Date()
  const expiry = new Date(dateString)
  // Set expiry to end of day
  expiry.setHours(23, 59, 59, 999)
  return expiry < today
}

const showViewModal = ref(false)
const selectedReturn = ref<ReturnItem | null>(null)
const showDeleteModal = ref(false)
const returnToDelete = ref<ReturnItem | null>(null)
const showEditModal = ref(false)
const editedReturn = ref<ReturnItem | null>(null)
const isLoadingDetails = ref(false)

// Edit form fields
const editBrandId = ref('')
const editReturnReason = ref('')
const editOtherReason = ref('')
const editSerialNumber = ref('')
const editProductCode = ref('')
const editExpiryDate = ref('')
const editSize = ref('')

async function viewReturn(item: ReturnItem) {
  // Open modal immediately with basic data for better UX
  selectedReturn.value = item
  showViewModal.value = true
  isLoadingDetails.value = true

  try {
    // Fetch full details from server (HIPAA compliant with audit logging)
    const { data } = await api.get(`/management/returns/${item.id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })

    // Update with full details
    if (data.data) {
      selectedReturn.value = {
        ...item,
        ...data.data
      }
    }
  } catch (error) {
    console.error('Error fetching return details:', error)
    // Keep the basic data visible even if detail fetch fails
  } finally {
    isLoadingDetails.value = false
  }
}

async function editReturn(item: ReturnItem) {
  // Open modal immediately with basic data for better UX
  editedReturn.value = { ...item }
  editBrandId.value = item.brandId
  editReturnReason.value = item.returnReason
  editOtherReason.value = item.otherReason || ''
  
  // For upload entries, allow editing all OCR fields (with initial data)
  if (item.entryType === 'upload') {
    editSerialNumber.value = item.serialNumber || ''
    editProductCode.value = item.productCode || ''
    editExpiryDate.value = item.expiryDate || ''
    editSize.value = item.size || ''
  }
  
  showEditModal.value = true
  isLoadingDetails.value = true

  try {
    // Fetch full details from server (HIPAA compliant with audit logging)
    const { data } = await api.get(`/management/returns/${item.id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })

    // Update with fresh data from server
    if (data.data) {
      const freshData = data.data
      editedReturn.value = { ...item, ...freshData }
      
      // Update form fields with fresh data
      editBrandId.value = freshData.brandId || item.brandId
      editReturnReason.value = freshData.reason || item.returnReason
      editOtherReason.value = freshData.other || item.otherReason || ''
      
      // For upload entries, update OCR fields with fresh data
      if (item.entryType === 'upload') {
        editSerialNumber.value = freshData.ocrSerialNumber || freshData.serialNumber || item.serialNumber || ''
        editProductCode.value = freshData.ocrProductCode || freshData.productCode || item.productCode || ''
        editExpiryDate.value = freshData.ocrExpiryDate || freshData.expiryDate || item.expiryDate || ''
        editSize.value = freshData.graftSize || item.size || ''
      }
    }
  } catch (error) {
    console.error('Error fetching return details for edit:', error)
    // Keep the basic data in the form even if detail fetch fails
  } finally {
    isLoadingDetails.value = false
  }
}

function confirmDelete(item: ReturnItem) {
  returnToDelete.value = item
  showDeleteModal.value = true
}

function cancelDelete() {
  showDeleteModal.value = false
  returnToDelete.value = null
}

function handleDelete() {
  if (returnToDelete.value) {
    emit('delete-return', returnToDelete.value)
    showDeleteModal.value = false
    returnToDelete.value = null
  }
}

function saveEdit() {
  if (editedReturn.value) {
    // Update the edited return with new values
    const updatedReturn: any = {
      ...editedReturn.value,
      brandId: editBrandId.value,
      returnReason: editReturnReason.value,
      otherReason: editReturnReason.value === 'Other' ? editOtherReason.value : null
    }
    
    // For manual entries, include graftSizeId
    if (editedReturn.value.entryType === 'manual') {
      updatedReturn.graftSizeId = editedReturn.value.graftSizeId
    }
    
    // For upload entries, include OCR fields in update (no graftSizeId needed)
    if (editedReturn.value.entryType === 'upload') {
      Object.assign(updatedReturn, {
        serialNumber: editSerialNumber.value,
        productCode: editProductCode.value,
        expiryDate: editExpiryDate.value,
        size: editSize.value
      })
    }
    
    // Emit to parent for backend update
    emit('submit-return', updatedReturn)
    showEditModal.value = false
    editedReturn.value = null
  }
}

function cancelEdit() {
  showEditModal.value = false
  editedReturn.value = null
  editBrandId.value = ''
  editReturnReason.value = ''
  editOtherReason.value = ''
  editSerialNumber.value = ''
  editProductCode.value = ''
  editExpiryDate.value = ''
  editSize.value = ''
}

// HIPAA Compliance: Clear sensitive data from memory when view modal closes
watch(showViewModal, (isOpen) => {
  if (!isOpen) {
    // Clear selected return data after a short delay to allow modal close animation
    setTimeout(() => {
      selectedReturn.value = null
    }, 300)
  }
})

// HIPAA Compliance: Clear sensitive data from memory when edit modal closes
watch(showEditModal, (isOpen) => {
  if (!isOpen) {
    // Clear edited return data after a short delay to allow modal close animation
    setTimeout(() => {
      editedReturn.value = null
      // Also clear all form fields
      editBrandId.value = ''
      editReturnReason.value = ''
      editOtherReason.value = ''
      editSerialNumber.value = ''
      editProductCode.value = ''
      editExpiryDate.value = ''
      editSize.value = ''
    }, 300)
  }
})

// Note: Server-side pagination is now used - pagination state comes from parent via props
</script> 