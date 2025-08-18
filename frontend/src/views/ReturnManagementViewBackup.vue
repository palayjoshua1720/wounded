<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Return Inventory Management</h1>
        <p class="text-gray-600 dark:text-gray-300">Manage returned inventory items and documentation</p>
      </div>
      <button
        @click="showUploadModal = true"
        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        <ArrowUpTrayIcon class="w-4 h-4 mr-2" />
        Upload Return Documents
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Pending Returns</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ returns.filter(item => item.status === 'pending').length }}
            </p>
          </div>
          <ClockIcon class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Confirmed Returns</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ returns.filter(item => item.status === 'confirmed').length }}
            </p>
          </div>
          <CheckCircleIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Processed Returns</p>
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
              {{ returns.filter(item => item.status === 'processed').length }}
            </p>
          </div>
          <ArrowPathIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="flex-1">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Search returns..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-400 dark:placeholder-gray-500"
          />
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <FunnelIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        <select
          v-model="statusFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="processed">Processed</option>
        </select>
      </div>
    </div>

    <!-- Returns Table -->
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Serial Number
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Product
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Clinic
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Return Reason
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Return Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="returnItem in filteredReturns"
              :key="returnItem.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ returnItem.serial }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ returnItem.productName }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ returnItem.brand }} - {{ returnItem.size }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ returnItem.clinicName }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">{{ returnItem.reason }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(returnItem.status)]">
                  <component :is="getStatusIcon(returnItem.status)" class="w-4 h-4" />
                  <span class="ml-1 capitalize">{{ returnItem.status }}</span>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(returnItem.returnDate) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  @click="openReturnDetails(returnItem)"
                  class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                >
                  View
                </button>
                <div class="inline-flex space-x-1">
                  <button
                    v-if="returnItem.status === 'pending'"
                    @click="handleStatusUpdate(returnItem.id, 'confirmed')"
                    class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded hover:bg-blue-200 dark:hover:bg-blue-800"
                  >
                    Confirm
                  </button>
                  <button
                    v-if="returnItem.status === 'confirmed'"
                    @click="handleStatusUpdate(returnItem.id, 'processed')"
                    class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded hover:bg-green-200 dark:hover:bg-green-800"
                  >
                    Process
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Upload Modal -->
    <BaseModal v-model="showUploadModal" title="Upload Return Documents">
      <div class="space-y-6">
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
          <CameraIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
          <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Photos</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Upload photos of returned items for verification
          </p>
          <input
            ref="photoUpload"
            type="file"
            accept="image/*"
            multiple
            class="hidden"
            @change="handlePhotoUpload"
          />
          <button
            @click="(($refs.photoUpload as HTMLInputElement)?.click())"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer"
          >
            <CameraIcon class="w-4 h-4 mr-2" />
            Choose Photos
          </button>
        </div>

        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
          <DocumentTextIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
          <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Return Labels</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Upload return labels and documentation for serial extraction
          </p>
          <input
            ref="documentUpload"
            type="file"
            accept=".pdf,.jpg,.jpeg,.png"
            multiple
            class="hidden"
            @change="handleDocumentUpload"
          />
          <button
            @click="(($refs.documentUpload as HTMLInputElement)?.click())"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 cursor-pointer"
          >
            <DocumentTextIcon class="w-4 h-4 mr-2" />
            Choose Documents
          </button>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            @click="showUploadModal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Cancel
          </button>
          <button
            @click="processDocuments"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Process Documents
          </button>
        </div>
      </div>
    </BaseModal>

    <!-- Return Details Modal -->
    <BaseModal v-model="showReturnDetailsModal" title="Return Details">
      <template v-if="selectedReturn">
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Number</label>
              <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ selectedReturn.serial }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
              <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(selectedReturn.status)]">
                <component :is="getStatusIcon(selectedReturn.status)" class="w-4 h-4" />
                <span class="ml-1 capitalize">{{ selectedReturn.status }}</span>
              </span>
            </div>
 