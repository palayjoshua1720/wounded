<template>
  <div class="space-y-6">
    <!-- New Return Form Card -->
    <div v-if="showForm" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">

      <div v-if="showForm" class="border border-gray-200 dark:border-gray-600 rounded-xl p-6 bg-gray-50 dark:bg-gray-700/30">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Process New Return</h3>
        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Brand *</label>
              <select
                v-model="selectedBrand"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                required
              >
                <option value="">Select Brand</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                  {{ brand.name }} - {{ getManufacturerName(brand.manufacturerId) }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Return Reason *</label>
              <select
                v-model="returnReason"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                required
              >
                <option value="">Select Reason</option>
                <option v-for="reason in returnReasons" :key="reason" :value="reason">{{ reason }}</option>
              </select>
            </div>
          </div>

          <!-- Other Reason Text Field (shown when 'Other' is selected) -->
          <div v-if="returnReason === 'Other'" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Please specify the reason *</label>
            <textarea
              v-model="otherReturnReason"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="Please provide details about the return reason..."
              required
            ></textarea>
          </div>

          <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
            <div class="flex items-center space-x-4 mb-4">
              <button
                type="button"
                @click="manualEntry = false"
                :class="['flex items-center space-x-2 px-4 py-2 rounded-lg transition-colors', !manualEntry ? 'bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-700' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600']"
              >
                <CameraIcon class="w-4 h-4" />
                <span>Upload Return Document</span>
              </button>
              <button
                type="button"
                @click="manualEntry = true"
                :class="['flex items-center space-x-2 px-4 py-2 rounded-lg transition-colors', manualEntry ? 'bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-700' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600']"
              >
                <CubeIcon class="w-4 h-4" />
                <span>Manual Entry</span>
              </button>
            </div>

            <div v-if="!manualEntry" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
              <ArrowUpTrayIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
              <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Return Label or Photo</h4>
              <p class="text-gray-600 dark:text-gray-400 mb-4">
                System will automatically extract serial number, expiry date, and size
              </p>
              <label class="cursor-pointer bg-gradient-to-r from-orange-600 to-red-600 text-white px-6 py-2 rounded-lg hover:from-orange-700 hover:to-red-700 transition-all duration-200 shadow-sm hover:shadow-md">
                <span>Choose File</span>
                <input
                  type="file"
                  accept="image/*,.pdf"
                  @change="handleFileUpload"
                  class="hidden"
                />
              </label>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Supports images (JPG, PNG) and PDF files
              </p>
              <div v-if="ocrLoading" class="mt-4 text-orange-600 dark:text-orange-400">Extracting text from image...</div>
              <!-- Improved OCR Result Quick View with action buttons on the right below the image -->
              <div v-if="ocrResult && !ocrLoading" class="mt-6 flex flex-col md:flex-row items-stretch gap-6 bg-blue-50 dark:bg-blue-900/20 p-5 rounded-xl border border-blue-200 dark:border-blue-700 shadow-sm">
                <div class="flex-1 min-w-0 flex flex-col justify-between">
                  <div>
                    <div class="font-bold text-blue-800 dark:text-blue-200 text-base mb-2 flex items-center gap-2">
                      <span>OCR Result (Quick View)</span>
                    </div>
                    <pre class="whitespace-pre-wrap text-sm text-blue-900 dark:text-blue-100 bg-blue-100 dark:bg-blue-900/40 rounded p-3 border border-blue-200 dark:border-blue-800">{{ ocrResult }}</pre>
                  </div>
                  <div class="mt-3 text-xs text-blue-700 dark:text-blue-300 opacity-80">If the text is unclear, you can reopen the OCR modal for editing.</div>
                </div>
                <div v-if="lastUploadedFileUrl && lastUploadedFile && lastUploadedFile.type && lastUploadedFile.type.startsWith('image/')" class="flex-shrink-0 flex flex-col items-center justify-center">
                  <img :src="lastUploadedFileUrl" alt="Uploaded" class="rounded-lg border border-gray-300 dark:border-gray-700 shadow-md object-contain" />
                  <div class="mt-2 text-xs text-gray-600 dark:text-gray-300 text-center truncate w-40" :title="lastUploadedFile.name">{{ lastUploadedFile.name }}</div>
                  <div class="mt-4 flex gap-3">
                    <button v-if="ocrResult && ocrExtracted" @click="showOcrModal = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Reopen OCR Result</button>
                    <button v-if="ocrResult && ocrExtracted" @click="confirmOcrReturn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Confirm & Add Return</button>
                  </div>
                </div>
              </div>
            </div>

            <form v-else @submit.prevent="handleManualSubmit" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Serial Number *</label>
                  <input
                    v-model="serialNumber"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Enter serial number"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Code</label>
                  <input
                    v-model="productCode"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Enter product code (optional)"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiry Date *</label>
                  <input
                    v-model="expiryDate"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Size *</label>
                  <input
                    v-model="size"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="e.g., 2cm x 2cm"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Manufacturer</label>
                  <input
                    v-model="manufacturer"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Enter manufacturer (optional)"
                  />
                </div>
              </div>
              <div class="flex justify-end space-x-4">
                <button
                  type="button"
                  @click="resetForm"
                  class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-xl hover:from-orange-700 hover:to-red-700 transition-all duration-200 shadow-sm hover:shadow-md"
                >
                  Submit Return
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <div class="flex flex-col lg:flex-row gap-6">
        <div class="flex-1">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
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
              <option value="all">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="processed">Processed</option>
            </select>
            <svg class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
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
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Brand</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Size</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expiry Date</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Return Reason</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entry Type</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Return Date</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="item in filteredReturns" :key="item.id" class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-orange-100 to-red-100 dark:from-orange-900/30 dark:to-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ getBrandName(item.brandId) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.manufacturer || '-' }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.size || '-' }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.expiryDate || '-' }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.returnReason }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <span :class="item.extractedFromImage ? 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'">
                  {{ item.extractedFromImage ? 'Auto-Extracted' : 'Manual Entry' }}
                </span>
              </td>
              <td class="px-6 py-5 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.returnDate }}</div>
              </td>
              <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                  <button @click="viewReturn(item)" 
                    class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-400 rounded-lg transition-all duration-200"
                    title="View Details">
                    <EyeIcon class="w-5 h-5" />
                  </button>
                  <button @click="editReturn(item)" 
                    class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-amber-100 dark:hover:bg-amber-900/30 hover:text-amber-700 dark:hover:text-amber-400 rounded-lg transition-all duration-200"
                    title="Edit Return">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="filteredReturns.length === 0" class="text-center py-16">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
            <ArrowPathIcon class="w-8 h-8 text-gray-400 dark:text-gray-500" />
          </div>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No returns found</h3>
        <p class="text-gray-600 dark:text-gray-400">No return records match your current filters.</p>
      </div>
    </div>
    <BaseModal v-model="showViewModal" title="Return Details" width="max-w-3xl">
      <div v-if="selectedReturn" class="space-y-6">
        <!-- Serial Number Header -->
        <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-xl p-5 text-white shadow-lg">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <p class="text-sm text-orange-100 mb-1">Serial Number</p>
              <p class="text-2xl font-bold font-mono tracking-wide">{{ selectedReturn.serialNumber || '-' }}</p>
              <p class="text-sm text-orange-100 mt-2">Product Code: <span class="font-semibold">{{ selectedReturn.productCode || 'N/A' }}</span></p>
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
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ selectedReturn.manufacturer || 'N/A' }}</p>
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
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.size || '-' }}</p>
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
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedReturn.expiryDate || '-' }}</p>
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

        <!-- Entry Type Card -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mr-2">
              <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Entry Type</h3>
          </div>
          <span :class="selectedReturn.extractedFromImage ? 'inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'">
            {{ selectedReturn.extractedFromImage ? 'Auto-Extracted from Image' : 'Manual Entry' }}
          </span>
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

        <!-- Uploaded File Preview (if exists) -->
        <div v-if="selectedReturn.extractedFromImage && selectedReturn.uploadedFileName && selectedReturn.uploadedFileUrl" 
          class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center mr-2">
              <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Uploaded File</h3>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">{{ selectedReturn.uploadedFileName }}</p>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-900 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
            <template v-if="selectedReturn.uploadedFileType && selectedReturn.uploadedFileType.startsWith('image/')">
              <img :src="selectedReturn.uploadedFileUrl" :alt="selectedReturn.uploadedFileName" 
                class="max-h-64 rounded-lg border border-gray-200 dark:border-gray-700 mx-auto shadow-sm" />
            </template>
            <template v-else-if="selectedReturn.uploadedFileType === 'application/pdf'">
              <iframe :src="selectedReturn.uploadedFileUrl" class="w-full h-64 border rounded-lg" />
            </template>
            <template v-else>
              <a :href="selectedReturn.uploadedFileUrl" target="_blank" 
                class="inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download/View File
              </a>
            </template>
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
    <BaseModal v-model="showOcrModal" title="OCR Extraction Review" width="max-w-4xl">
      <div class="space-y-6">
        <!-- Image Preview -->
        <div v-if="lastUploadedFile && lastUploadedFile.type.startsWith('image/')" 
          class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-2">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Uploaded Image</h3>
          </div>
          <div class="bg-white dark:bg-gray-900 rounded-lg p-3 flex justify-center">
            <img :src="lastUploadedFileUrl" alt="Uploaded" class="max-h-64 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm" />
          </div>
        </div>

        <!-- Recognized Text -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-2">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recognized Text (OCR)</h3>
          </div>
          <pre class="whitespace-pre-wrap text-xs bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-300 font-mono">{{ ocrResult }}</pre>
        </div>

        <!-- Mapped Fields -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
          <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-2">
              <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Extracted Fields (Editable)</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Product Brand *</label>
              <select v-model="ocrSelectedBrand" 
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Select Brand</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Return Reason *</label>
              <select v-model="ocrReturnReason" 
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                <option value="">Select Reason</option>
                <option v-for="reason in returnReasons" :key="reason" :value="reason">{{ reason }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Serial Number</label>
              <input v-model="ocrSerialNumber" :disabled="!ocrExtracted" 
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Product Code</label>
              <input v-model="ocrProductCode" :disabled="!ocrExtracted" 
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Brand Name</label>
              <input v-model="ocrBrand" :disabled="!ocrExtracted" 
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Expiry Date</label>
              <input v-model="ocrExpiryDate" :disabled="!ocrExtracted" 
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Size</label>
              <input v-model="ocrSize" :disabled="!ocrExtracted" 
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Manufacturer</label>
              <input v-model="ocrManufacturer" :disabled="!ocrExtracted" 
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
          </div>

          <!-- Other Reason Text Field for OCR (shown when 'Other' is selected) -->
          <div v-if="ocrReturnReason === 'Other'" class="col-span-2">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Please specify the reason *</label>
            <textarea
              v-model="ocrOtherReason"
              rows="3"
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
              placeholder="Please provide details about the return reason..."
              required
            ></textarea>
          </div>
        </div>
      </div>
      <template #actions>
        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-600">
          <button @click="showOcrModal = false" 
            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
            Close
          </button>
          <button :disabled="!ocrExtracted" v-if="ocrExtracted" @click="confirmOcrReturn" 
            class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Confirm & Add Return
            </span>
          </button>
        </div>
      </template>
    </BaseModal>

    <!-- Edit Return Modal -->
    <BaseModal v-model="showEditModal" title="Edit Return" width="max-w-3xl">
      <div v-if="editingReturn" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Product Brand *</label>
            <select v-model="editingReturn.brandId" 
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option value="">Select Brand</option>
              <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Return Reason *</label>
            <select v-model="editingReturn.returnReason" 
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option value="">Select Reason</option>
              <option v-for="reason in returnReasons" :key="reason" :value="reason">{{ reason }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Serial Number *</label>
            <input v-model="editingReturn.serialNumber" type="text" 
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Product Code</label>
            <input v-model="editingReturn.productCode" type="text" 
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Expiry Date *</label>
            <input v-model="editingReturn.expiryDate" type="date" 
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Size *</label>
            <input v-model="editingReturn.size" type="text" 
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
              placeholder="e.g., 2cm x 2cm" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Manufacturer</label>
            <input v-model="editingReturn.manufacturer" type="text" 
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Return Date *</label>
            <input v-model="editingReturn.returnDate" type="date" 
              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
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
            class="px-5 py-2.5 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-xl hover:from-orange-700 hover:to-red-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium">
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Save Changes
            </span>
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { ArrowPathIcon, ArrowUpTrayIcon, CameraIcon, CubeIcon, CheckCircleIcon, EyeIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import Tesseract from 'tesseract.js'
import BaseModal from '@/components/common/BaseModal.vue'

interface Props {
  returns?: ReturnItem[]
  brands?: Brand[]
  manufacturers?: Manufacturer[]
  showForm?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  returns: () => [],
  brands: () => [],
  manufacturers: () => [],
  showForm: false
})

const emit = defineEmits<{
  'submit-return': [data: any]
  'upload-return-document': [file: File, data: any]
  'view-return': [item: ReturnItem]
  'close-form': []
}>()

interface ReturnItem {
  id: string
  serialNumber: string
  brandId: string
  size: string
  expiryDate: string
  returnReason: string
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

const brands = ref<Brand[]>(props.brands.length > 0 ? props.brands : [
  { id: '1', name: 'MedTech', manufacturerId: '1' },
  { id: '2', name: 'BioMed', manufacturerId: '2' }
])

const manufacturers = ref<Manufacturer[]>(props.manufacturers.length > 0 ? props.manufacturers : [
  { id: '1', name: 'MedTech Solutions' },
  { id: '2', name: 'BioMedical Industries' }
])

const returns = ref<ReturnItem[]>(props.returns.length > 0 ? props.returns : [
  {
    id: '1',
    serialNumber: 'SN123456',
    brandId: '1',
    size: '2cm x 2cm',
    expiryDate: '2024-12-31',
    returnReason: 'Expired Product',
    status: 'pending',
    returnDate: '2025-01-10',
    extractedFromImage: false,
    productCode: 'PRD-001',
    manufacturer: 'MedTech Solutions'
  },
  {
    id: '2',
    serialNumber: 'SN654321',
    brandId: '2',
    size: '3cm x 3cm',
    expiryDate: '2025-03-15',
    returnReason: 'Damaged in Transit',
    status: 'confirmed',
    returnDate: '2025-01-12',
    extractedFromImage: false,
    productCode: 'PRD-002',
    manufacturer: 'BioMedical Industries'
  },
  {
    id: '3',
    serialNumber: 'SN789012',
    brandId: '1',
    size: '4cm x 4cm',
    expiryDate: '2025-06-20',
    returnReason: 'Wrong Size Delivered',
    status: 'processed',
    returnDate: '2025-01-15',
    extractedFromImage: false,
    productCode: 'PRD-003',
    manufacturer: 'MedTech Solutions'
  }
])

const searchQuery = ref('')
const statusFilter = ref('all')

const filteredReturns = computed(() => {
  let filtered = returns.value
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(item => item.status === statusFilter.value)
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    filtered = filtered.filter(item =>
      (item.serialNumber && item.serialNumber.toLowerCase().includes(q)) ||
      (item.productCode && item.productCode.toLowerCase().includes(q)) ||
      (item.size && item.size.toLowerCase().includes(q)) ||
      (item.returnReason && item.returnReason.toLowerCase().includes(q)) ||
      (item.manufacturer && item.manufacturer.toLowerCase().includes(q)) ||
      (getBrandName(item.brandId) && getBrandName(item.brandId).toLowerCase().includes(q))
    )
  }
  return filtered
})

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
    case 'pending': return ArrowUpTrayIcon
    case 'confirmed': return CheckCircleIcon
    case 'processed': return ArrowPathIcon
    default: return ArrowPathIcon
  }
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString()
}

function isExpired(dateString: string) {
  if (!dateString) return false
  const today = new Date()
  const expiry = new Date(dateString)
  // Set expiry to end of day
  expiry.setHours(23, 59, 59, 999)
  return expiry < today
}

const ocrLoading = ref(false)
const ocrResult = ref('')
const ocrExtracted = ref<{
  serialNumber: string,
  productCode: string,
  brand: string,
  expiryDate: string,
  size: string,
  manufacturer: string
} | null>(null)
const showOcrModal = ref(false)

// Computed proxies for v-model binding in modal
const ocrBrand = computed({
  get: () => ocrExtracted.value ? ocrExtracted.value.brand : '',
  set: v => { if (ocrExtracted.value) ocrExtracted.value.brand = v }
})
const ocrSerialNumber = computed({
  get: () => ocrExtracted.value ? ocrExtracted.value.serialNumber : '',
  set: v => { if (ocrExtracted.value) ocrExtracted.value.serialNumber = v }
})
const ocrProductCode = computed({
  get: () => ocrExtracted.value ? ocrExtracted.value.productCode : '',
  set: v => { if (ocrExtracted.value) ocrExtracted.value.productCode = v }
})
const ocrExpiryDate = computed({
  get: () => ocrExtracted.value ? ocrExtracted.value.expiryDate : '',
  set: v => { if (ocrExtracted.value) ocrExtracted.value.expiryDate = v }
})
const ocrSize = computed({
  get: () => ocrExtracted.value ? ocrExtracted.value.size : '',
  set: v => { if (ocrExtracted.value) ocrExtracted.value.size = v }
})
const ocrManufacturer = computed({
  get: () => ocrExtracted.value ? ocrExtracted.value.manufacturer : '',
  set: v => { if (ocrExtracted.value) ocrExtracted.value.manufacturer = v }
})

// Add a ref to store the last uploaded file for preview
const lastUploadedFile = ref<File | null>(null)
const lastUploadedFileUrl = computed(() => lastUploadedFile.value ? URL.createObjectURL(lastUploadedFile.value) : '')

// Helper to get brand and manufacturer names
function getBrandById(id: string) {
  return brands.value.find(b => b.id === id)
}
function getManufacturerById(id: string) {
  return manufacturers.value.find(m => m.id === id)
}

async function handleFileUpload(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  lastUploadedFile.value = file
  ocrLoading.value = true
  ocrResult.value = ''
  ocrExtracted.value = null
  try {
    const { data } = await Tesseract.recognize(file, 'eng', { logger: m => { /* no-op for now */ } })
    ocrResult.value = data.text
    showOcrModal.value = true
    // Mock extraction: look for patterns in the OCR text
    const serialMatch = ocrResult.value.match(/SN\d{6}/) || ocrResult.value.match(/Serial\s*[:#-]?\s*(\w+)/i)
    const expiryMatch = ocrResult.value.match(/\b(20\d{2}-\d{2}-\d{2})\b/) || ocrResult.value.match(/Expiry\s*[:#-]?\s*(\d{4}-\d{2}-\d{2})/i)
    const sizeMatch = ocrResult.value.match(/(\d+cm\s*x\s*\d+cm)/i)
    // Mock product code extraction
    const productCodeMatch = ocrResult.value.match(/Product\s*Code\s*[:#-]?\s*(\w+)/i)
    const brandObj = getBrandById(selectedBrand.value)
    const manufacturerObj = brandObj ? getManufacturerById(brandObj.manufacturerId) : null
    ocrExtracted.value = {
      serialNumber: serialMatch ? (serialMatch[1] || serialMatch[0]) : 'SN123456',
      productCode: productCodeMatch ? (productCodeMatch[1] || productCodeMatch[0]) : (productCode.value || 'PRD-001'),
      brand: brandObj ? brandObj.name : '',
      expiryDate: expiryMatch ? (expiryMatch[1] || expiryMatch[0]) : '2024-12-31',
      size: sizeMatch ? (sizeMatch[1] || sizeMatch[0]) : '2cm x 2cm',
      manufacturer: manufacturerObj ? manufacturerObj.name : ''
    }
    // Pre-map dropdowns
    ocrSelectedBrand.value = selectedBrand.value
    ocrReturnReason.value = returnReason.value
  } catch (e) {
    ocrResult.value = 'OCR failed.'
    ocrExtracted.value = null
  }
  ocrLoading.value = false
}

// For dropdowns in modal
const ocrSelectedBrand = ref('')
const ocrReturnReason = ref('')
const ocrOtherReason = ref('')

function confirmOcrReturn() {
  if (ocrExtracted.value) {
    // If 'Other' is selected, use the custom reason, otherwise use the selected reason
    const finalReturnReason = ocrReturnReason.value === 'Other' && ocrOtherReason.value
      ? ocrOtherReason.value
      : ocrReturnReason.value

    const newReturn = {
      id: String(Date.now()),
      serialNumber: ocrExtracted.value.serialNumber,
      productCode: ocrExtracted.value.productCode,
      brandId: ocrSelectedBrand.value,
      brand: ocrExtracted.value.brand,
      expiryDate: ocrExtracted.value.expiryDate,
      size: ocrExtracted.value.size,
      manufacturer: ocrExtracted.value.manufacturer,
      returnReason: finalReturnReason,
      status: 'pending' as const,
      returnDate: new Date().toISOString().slice(0, 10),
      extractedFromImage: true
    }
    returns.value.push(newReturn)
    resetForm()
    ocrExtracted.value = null
    ocrResult.value = ''
    ocrOtherReason.value = ''
    showOcrModal.value = false
  }
}

function handleManualSubmit() {
  if (serialNumber.value && expiryDate.value && size.value && returnReason.value && selectedBrand.value) {
    // If 'Other' is selected, use the custom reason, otherwise use the selected reason
    const finalReturnReason = returnReason.value === 'Other' && otherReturnReason.value 
      ? otherReturnReason.value 
      : returnReason.value

    const newReturn = {
      id: String(Date.now()),
      serialNumber: serialNumber.value,
      productCode: productCode.value,
      brandId: selectedBrand.value,
      brand: getBrandById(selectedBrand.value)?.name || '',
      expiryDate: expiryDate.value,
      size: size.value,
      manufacturer: manufacturer.value,
      returnReason: finalReturnReason,
      status: 'pending' as const,
      returnDate: new Date().toISOString().slice(0, 10),
      extractedFromImage: false
    }
    returns.value.push(newReturn)
    resetForm()
  } else {
    alert('Please fill in all required fields.')
  }
}

function updateStatus(id: string, newStatus: 'pending' | 'confirmed' | 'processed') {
  const idx = returns.value.findIndex(item => item.id === id)
  if (idx !== -1) {
    returns.value[idx].status = newStatus
  }
}

function viewReturn(item: ReturnItem) {
  selectedReturn.value = item
  showViewModal.value = true
}

function editReturn(item: ReturnItem) {
  editingReturn.value = { ...item }
  showEditModal.value = true
}

function cancelEdit() {
  editingReturn.value = null
  showEditModal.value = false
}

function saveEdit() {
  if (editingReturn.value) {
    const index = returns.value.findIndex(r => r.id === editingReturn.value!.id)
    if (index !== -1) {
      returns.value[index] = { ...editingReturn.value }
    }
    editingReturn.value = null
    showEditModal.value = false
  }
}

function resetForm() {
  serialNumber.value = ''
  expiryDate.value = ''
  size.value = ''
  returnReason.value = ''
  selectedBrand.value = ''
  manualEntry.value = false
  productCode.value = ''
  manufacturer.value = ''
  otherReturnReason.value = ''
  emit('close-form')
}

const showForm = computed(() => props.showForm)
const manualEntry = ref(false)
const serialNumber = ref('')
const expiryDate = ref('')
const size = ref('')
const returnReason = ref('')
const selectedBrand = ref('')
const otherReturnReason = ref('')

const productCode = ref('')
const manufacturer = ref('')

const returnReasons = [
  'Expired Product',
  'Damaged in Transit',
  'Wrong Size Delivered',
  'Unused - Patient Cancelled',
  'Quality Issue',
  'Overstock',
  'Other'
]

const showViewModal = ref(false)
const selectedReturn = ref<ReturnItem | null>(null)
const showEditModal = ref(false)
const editingReturn = ref<ReturnItem | null>(null)
</script> 