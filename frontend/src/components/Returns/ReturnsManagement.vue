<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
          <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
            <ArrowPathIcon class="w-6 h-6 text-orange-600 dark:text-orange-400" />
          </div>
          <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Returns Management</h2>
            <p class="text-gray-600 dark:text-gray-400">Process product returns with automatic data extraction</p>
          </div>
        </div>
        <button
          @click="showForm = !showForm"
          class="flex items-center space-x-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
        >
          <ArrowPathIcon class="w-4 h-4" />
          <span>New Return</span>
        </button>
      </div>

      <div v-if="showForm" class="border border-gray-200 dark:border-gray-600 rounded-lg p-6 mb-6">
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
              <label class="cursor-pointer bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition-colors">
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
                  class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                >
                  Submit Return
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Returns</h3>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" />
            <input v-model="searchQuery" type="text" placeholder="Search returns..." class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <select
            v-model="statusFilter"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="processed">Processed</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Brand</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Size</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expiry Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Return Reason</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entry Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Return Date</th>
              <!-- Hide Status column for now -->
              <!--<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>-->
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="item in filteredReturns" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ item.serialNumber || '-' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Product Code: {{ item.productCode || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ getBrandName(item.brandId) }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Manufacturer: {{ item.manufacturer || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.size || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.expiryDate || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.returnReason }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="item.extractedFromImage ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                  {{ item.extractedFromImage ? 'Auto-Extracted' : 'Manual Entry' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ item.returnDate }}</div>
              </td>
              <!-- Hide Status cell for now -->
              <!--<td class="px-6 py-4 whitespace-nowrap">
                <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(item.status)}`">
                  <component :is="getStatusIcon(item.status)" class="w-4 h-4" />
                  <span class="ml-1 capitalize">{{ item.status }}</span>
                </span>
              </td>-->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button @click="viewReturn(item)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 flex items-center space-x-1">
                    <EyeIcon class="w-4 h-4" />
                    <span>View</span>
                  </button>
                  <!-- Hide Confirm and Process actions for now -->
                  <!--<button v-if="item.status === 'pending'" @click="updateStatus(item.id, 'confirmed')" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 flex items-center space-x-1">
                    <CheckCircleIcon class="w-4 h-4" />
                    <span>Confirm</span>
                  </button>
                  <button v-if="item.status === 'confirmed'" @click="updateStatus(item.id, 'processed')" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 flex items-center space-x-1">
                    <ArrowPathIcon class="w-4 h-4" />
                    <span>Process</span>
                  </button>-->
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="filteredReturns.length === 0" class="text-center py-12">
        <ArrowPathIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No returns found</h3>
        <p class="text-gray-600 dark:text-gray-400">No return records match your current filters.</p>
      </div>
    </div>
    <BaseModal v-model="showViewModal" title="Return Details">
      <div v-if="selectedReturn" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Number</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.serialNumber || '-' }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Product Code: {{ selectedReturn.productCode || '-' }}</p>
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
            <p class="text-xs text-gray-500 dark:text-gray-400">Manufacturer: {{ selectedReturn.manufacturer || '-' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Size</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.size || '-' }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.expiryDate || '-' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Return Reason</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.returnReason }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Entry Type</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.extractedFromImage ? 'Auto-Extracted' : 'Manual Entry' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Return Date</label>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.returnDate }}</p>
          </div>
        </div>
        <div v-if="selectedReturn.extractedFromImage && selectedReturn.uploadedFileName && selectedReturn.uploadedFileUrl" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mt-2">
          <div class="text-sm font-medium text-gray-900 dark:text-white mb-2">Uploaded File Preview</div>
          <div class="text-xs text-gray-600 dark:text-gray-300 mb-2">{{ selectedReturn.uploadedFileName }}</div>
          <template v-if="selectedReturn.uploadedFileType && selectedReturn.uploadedFileType.startsWith('image/')">
            <img :src="selectedReturn.uploadedFileUrl" :alt="selectedReturn.uploadedFileName" class="max-h-64 rounded border border-gray-200 dark:border-gray-700 mx-auto" />
          </template>
          <template v-else-if="selectedReturn.uploadedFileType === 'application/pdf'">
            <iframe :src="selectedReturn.uploadedFileUrl" class="w-full h-64 border rounded" />
          </template>
          <template v-else>
            <a :href="selectedReturn.uploadedFileUrl" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 underline mt-1 inline-block">Download/View (mock)</a>
          </template>
        </div>
      </div>
      <template #actions>
        <button @click="showViewModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Close</button>
      </template>
    </BaseModal>
    <BaseModal v-model="showOcrModal" title="OCR Extraction Review">
      <div class="space-y-4">
        <div v-if="lastUploadedFile && lastUploadedFile.type.startsWith('image/')" class="flex justify-center mb-2">
          <img :src="lastUploadedFileUrl" alt="Uploaded" class="max-h-48 rounded border border-gray-200 dark:border-gray-700" />
        </div>
        <div class="font-bold">Recognized Text:</div>
        <pre class="whitespace-pre-wrap text-xs bg-gray-100 dark:bg-gray-900/20 p-4 rounded">{{ ocrResult }}</pre>
        <div class="font-bold mt-4">Mapped Fields (Editable)</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Product Brand</label>
            <select v-model="ocrSelectedBrand" class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option value="">Select Brand</option>
              <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Return Reason</label>
            <select v-model="ocrReturnReason" class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option value="">Select Reason</option>
              <option v-for="reason in returnReasons" :key="reason" :value="reason">{{ reason }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Serial Number</label>
            <input v-model="ocrSerialNumber" :disabled="!ocrExtracted" class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Product Code</label>
            <input v-model="ocrProductCode" :disabled="!ocrExtracted" class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Brand</label>
            <input v-model="ocrBrand" :disabled="!ocrExtracted" class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
            <input v-model="ocrExpiryDate" :disabled="!ocrExtracted" class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Size</label>
            <input v-model="ocrSize" :disabled="!ocrExtracted" class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Manufacturer</label>
            <input v-model="ocrManufacturer" :disabled="!ocrExtracted" class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <button @click="showOcrModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Close</button>
          <button :disabled="!ocrExtracted" v-if="ocrExtracted" @click="confirmOcrReturn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Confirm & Add Return</button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { ArrowPathIcon, ArrowUpTrayIcon, CameraIcon, CubeIcon, CheckCircleIcon, EyeIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import Tesseract from 'tesseract.js'
import BaseModal from '@/components/common/BaseModal.vue'

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
    returnDate: '2025-01-10',
    extractedFromImage: false, // Added for new column
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
    extractedFromImage: false, // Added for new column
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
    extractedFromImage: false, // Added for new column
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

function confirmOcrReturn() {
  if (ocrExtracted.value) {
    const newReturn = {
      id: String(Date.now()),
      serialNumber: ocrExtracted.value.serialNumber,
      productCode: ocrExtracted.value.productCode,
      brandId: ocrSelectedBrand.value,
      brand: ocrExtracted.value.brand,
      expiryDate: ocrExtracted.value.expiryDate,
      size: ocrExtracted.value.size,
      manufacturer: ocrExtracted.value.manufacturer,
      returnReason: ocrReturnReason.value,
      status: 'pending' as const,
      returnDate: new Date().toISOString().slice(0, 10),
      extractedFromImage: true
    }
    returns.value.push(newReturn)
    resetForm()
    ocrExtracted.value = null
    ocrResult.value = ''
    showOcrModal.value = false
  }
}

function handleManualSubmit() {
  if (serialNumber.value && expiryDate.value && size.value && returnReason.value && selectedBrand.value) {
    const newReturn = {
      id: String(Date.now()),
      serialNumber: serialNumber.value,
      productCode: productCode.value,
      brandId: selectedBrand.value,
      brand: getBrandById(selectedBrand.value)?.name || '',
      expiryDate: expiryDate.value,
      size: size.value,
      manufacturer: manufacturer.value,
      returnReason: returnReason.value,
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

function resetForm() {
  serialNumber.value = ''
  expiryDate.value = ''
  size.value = ''
  returnReason.value = ''
  selectedBrand.value = ''
  manualEntry.value = false
  showForm.value = false
  productCode.value = ''
  manufacturer.value = ''
}

const showForm = ref(false)
const manualEntry = ref(false)
const serialNumber = ref('')
const expiryDate = ref('')
const size = ref('')
const returnReason = ref('')
const selectedBrand = ref('')

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
</script> 