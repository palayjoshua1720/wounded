<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Inventory & Serial Tracking</h1>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl">Manage inventory items, track serial numbers, and
                    monitor usage across all clinics</p>
            </div>
            <div class="flex items-center gap-4">
                <button @click="showStatistics = !showStatistics"
                    class="flex items-center px-5 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">
                    <BarChart3 class="w-5 h-5 mr-2" />
                    {{ showStatistics ? 'Hide' : 'Show' }} Stats
                </button>
                <button @click="showUsageLogForm = true"
                    class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
                    <Plus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
                    Log Usage
                </button>
                <button @click="showUploadModal = true"
                    class="flex items-center px-5 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                    <UploadCloud class="w-5 h-5 mr-2" />
                    Upload File
                </button>
            </div>
        </div>

        <!-- Stats Panel -->
        <transition enter-active-class="transition ease-out duration-300"
            enter-from-class="transform opacity-0 -translate-y-4" enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200" leave-from-class="transform opacity-100 translate-y-0"
            leave-to-class="transform opacity-0 -translate-y-4">
            <div v-if="showStatistics"
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Inventory Statistics</h3>

                <!-- Main Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-6">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <Package class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ inventory.length }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Items</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <CheckCircle2 class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ getStatusCount('delivered')
                                }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Available</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                            <Clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ getStatusCount('expected') }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Expected</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                            <AlertTriangle class="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ getExpiringSoonCount() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Expiring Soon</p>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <!-- Status Breakdown -->
                <div class="mt-6">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Status Breakdown</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-8 gap-y-4">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Package class="w-4 h-4 mr-2 text-blue-500" /> Delivered
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('delivered')
                                }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Clock class="w-4 h-4 mr-2 text-yellow-500" /> Expected
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('expected')
                                }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <CheckCircle2 class="w-4 h-4 mr-2 text-green-500" /> Used
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('used')
                                }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <AlertTriangle class="w-4 h-4 mr-2 text-red-500" /> Expired
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('expired')
                                }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Repeat2 class="w-4 h-4 mr-2 text-purple-500" /> Partially Used
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{
                                getStatusCount('partially_used') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <CornerUpLeft class="w-4 h-4 mr-2 text-gray-500" /> Returned
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('returned')
                                }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Package class="w-4 h-4 mr-2 text-indigo-500" /> Unused
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('unused')
                                }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Repeat2 class="w-4 h-4 mr-2 text-orange-500" /> Reassigned
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('reassigned')
                                }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row gap-6">
                <div class="flex-1">
                    <div class="relative">
                        <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                        <input v-model="searchTerm" type="text"
                            placeholder="Search by serial number, product name, or brand..."
                            class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative">
                        <Filter class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
                        <select v-model="statusFilter"
                            class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="all">All Status</option>
                            <option value="expected">Expected</option>
                            <option value="delivered">Delivered</option>
                            <option value="used">Used</option>
                            <option value="partially_used">Partially Used</option>
                            <option value="returned">Returned</option>
                            <option value="expired">Expired</option>
                            <option value="reassigned">Reassigned</option>
                            <option value="unused">Unused</option>
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                    <div class="relative">
                        <select v-model="brandFilter"
                            class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="all">All Brands</option>
                            <option v-for="brand in uniqueBrands" :key="brand" :value="brand">{{ brand }}</option>
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Log Form -->
        <div v-if="showUsageLogForm"
            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Log Product Usage</h3>
                <button @click="showUsageLogForm = false"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                    <X class="w-5 h-5" />
                </button>
            </div>
            <UsageLogForm :inventory-items="inventory" :clinicians="clinicians" :brands="brands"
                :graft-sizes="graftSizes" @submit="handleUsageLogSubmitAndHide" @bulk-upload="handleBulkUpload"
                @cancel="handleUsageLogCancelAndHide" />
        </div>

        <!-- Inventory Table Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Product
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Serial Number
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Brand & Size
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Clinic
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Expiry Date
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <TableLoader v-if="tableLoader" :colspan="7" />
                        <template v-else>
                            <tr v-for="item in filteredInventory" :key="item.id"
                                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                                            <Package class="w-5 h-5" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                                item.brandName || 'N/A' }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{
                                                item.patientName || 'Unknown Patient' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-mono text-gray-900 dark:text-white">{{ item.serialNumber }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ getSizeName(item.brandId,
                                        item.sizeId) }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ getClinicName(item.clinicId)
                                    }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span
                                        :class="['inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200', getStatusColor(item.status)]">
                                        {{ formatStatusText(item.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white"
                                        :class="isExpiringSoon(item.expiryDate) ? 'text-red-600' : ''">
                                        {{ formatDate(item.expiryDate) }}
                                        <span v-if="isExpiringSoon(item.expiryDate)"
                                            class="text-xs text-red-500 block">Expiring Soon</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button @click="handleViewItem(item)"
                                            class="p-2 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all duration-200"
                                            title="View Details">
                                            <Eye class="w-4 h-4" />
                                        </button>
                                        <button @click="handleEditItem(item)"
                                            class="p-2 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-all duration-200"
                                            title="Edit Item">
                                            <FilePenLine class="w-4 h-4" />
                                        </button>
                                        <button @click="handleDeleteItem(item)"
                                            class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200"
                                            title="Delete Item">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredInventory.length === 0 && !tableLoader">
                                <!-- <td colspan="7" class="text-center text-gray-400 py-12">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <Package class="w-10 h-10 mb-1" />
                                        <span>No inventory items found.</span>
                                    </div>
                                </td> -->
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredInventory.length === 0 && !tableLoader" class="text-center py-12">
                <div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <Package class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No inventory items found</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Try adjusting your search or filter to find
                    what you're looking for.</p>
            </div>
        </div>

        <!-- Upload Modal -->
        <BaseModal v-model="showUploadModal" title="Upload Log Usage" width="max-w-4xl">
            <div class="space-y-6">
                <!-- Upload Area -->
                <div v-if="!uploadedFile"
                    class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-dashed border-blue-300 dark:border-blue-600 rounded-2xl p-10 text-center transition-all duration-300 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg group">
                    <div class="mb-6">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 bg-white dark:bg-gray-800 rounded-full shadow-md mb-4 group-hover:scale-110 transition-transform duration-300">
                            <UploadCloud class="w-10 h-10 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Upload Log Usage</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                            Drag and drop your file here, or click to browse
                        </p>
                    </div>

                    <input type="file" @change="handleFileUpload" accept=".pdf,.jpg,.jpeg,.png" class="hidden"
                        id="file-upload" ref="fileInput" />
                    <label for="file-upload"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-md hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 cursor-pointer font-medium group-hover:shadow-xl">
                        <FileText class="w-5 h-5 mr-2" />
                        Choose File
                    </label>
                </div>

                <!-- OCR Processing Display -->
                <div v-if="isProcessingOCR"
                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    </div>
                    <p class="text-center text-gray-700 dark:text-gray-300 font-medium">Processing image with OCR...</p>
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">{{ ocrProgress }}%</p>
                </div>

                <!-- File Preview -->
                <div v-if="uploadedFile && !isProcessingOCR" class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <FileText class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ uploadedFile.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{
                                        formatFileSize(uploadedFile.size) }}
                                    </p>
                                </div>
                            </div>
                            <button @click="clearUpload" class="text-red-600 hover:text-red-700 dark:text-red-400">
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Image Preview -->
                        <div v-if="previewUrl" class="mb-4">
                            <img :src="previewUrl" alt="Preview"
                                class="max-h-64 mx-auto rounded-lg border border-gray-300 dark:border-gray-600" />
                        </div>
                    </div>

                    <!-- Extracted Data Form -->
                    <div
                        class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Extracted Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Serial
                                    Number</label>
                                <input v-model="extractedData.serialNumber" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter serial number" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Patient
                                    Name</label>
                                <input v-model="extractedData.patientName" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter patient name" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of
                                    Service</label>
                                <input v-model="extractedData.dateOfService" type="date"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Wound
                                    Part</label>
                                <input v-model="extractedData.woundSite" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter wound location" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantity
                                    Used</label>
                                <input v-model.number="extractedData.quantityUsed" type="number" min="1"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter quantity" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Patient
                                    ID</label>
                                <input v-model="extractedData.patientId" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter patient ID" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Clinician
                                    ID</label>
                                <input v-model="extractedData.clinicianId" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter clinician ID" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Graft
                                    Size
                                    ID</label>
                                <input v-model="extractedData.graftSizeId" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter graft size ID" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Clinic
                                    ID</label>
                                <input v-model="extractedData.clinicId" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter clinic ID" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expired
                                    At</label>
                                <input v-model="extractedData.expiredAt" type="date"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes/Description</label>
                                <textarea v-model="extractedData.notes" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Enter additional notes"></textarea>
                            </div>
                        </div>

                        <!-- Raw OCR Text (Collapsible) -->
                        <div v-if="rawOcrText" class="mt-4">
                            <button @click="showRawOcrText = !showRawOcrText"
                                class="flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                <ChevronDown :class="{ 'rotate-180': showRawOcrText }"
                                    class="w-4 h-4 mr-1 transition-transform" />
                                {{ showRawOcrText ? 'Hide' : 'Show' }} Raw OCR Text
                            </button>
                            <div v-if="showRawOcrText"
                                class="mt-2 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                                <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{
                                    rawOcrText }}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supported Formats Info -->
                <div v-if="!uploadedFile"
                    class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                    <div class="flex items-start space-x-3">
                        <div
                            class="flex-shrink-0 w-5 h-5 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mt-0.5">
                            <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">OCR will extract text
                                from:
                            </h4>
                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <div class="flex items-center">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></span>
                                    PDF (.pdf)
                                </div>
                                <div class="flex items-center">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></span>
                                    Image Files (.jpg, .png)
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 italic">Tesseract OCR will
                                automatically
                                extract information from your uploaded document</p>
                        </div>
                    </div>
                </div>
            </div>
            <template #actions>
                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30">
                    <button @click="closeUploadModal"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 font-medium">
                        Cancel
                    </button>
                    <button v-if="uploadedFile && !isProcessingOCR" @click="submitExtractedData"
                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-md hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-medium hover:shadow-lg">
                        Submit Usage Log
                    </button>
                </div>
            </template>
        </BaseModal>

        <!-- Item Details Modal -->
        <BaseModal v-model="showItemModal" title="Inventory Item Details" width="max-w-5xl">
            <div v-if="selectedItem" class="space-y-4">
                <!-- Serial Number Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-4 text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-100 mb-1">Serial Number</p>
                            <p class="text-2xl font-bold font-mono tracking-wide">{{ selectedItem.serialNumber }}</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
                            <span
                                :class="`inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold ${getStatusColor(selectedItem.status)} shadow-sm`">
                                {{ formatStatusText(selectedItem.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Product Information Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <!-- Product Details Card -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-2">
                            <div
                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-2">
                                <Package class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                Product Details</h3>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Brand</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                    getBrandName(selectedItem.brandId) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Size</p>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                    getSizeName(selectedItem.brandId, selectedItem.sizeId) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-2">
                            <div
                                class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-2">
                                <MapPin class="w-4 h-4 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                Location
                            </h3>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Assigned Clinic</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                getClinicName(selectedItem.clinicId) }}</p>
                        </div>
                    </div>

                    <!-- Order Information Card -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-2">
                            <div
                                class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mr-2">
                                <FileText class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                            </div>
                            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                Order Info
                            </h3>
                        </div>
                        <div class="space-y-2">
                            <div v-if="selectedItem.orderCode">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Order Code</p>
                                <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">{{
                                    selectedItem.orderCode }}</p>
                            </div>
                            <div v-if="selectedItem.orderId">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Order ID</p>
                                <p class="text-sm font-mono font-medium text-gray-700 dark:text-gray-300">#{{
                                    selectedItem.orderId }}</p>
                            </div>
                            <div v-if="!selectedItem.orderCode && !selectedItem.orderId">
                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">No matching order found</p>
                            </div>
                        </div>
                    </div>

                    <!-- Date Information Card -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-2">
                            <div
                                class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-2">
                                <Calendar class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                Dates</h3>
                        </div>
                        <div class="space-y-2">
                            <div v-if="selectedItem.deliveryDate">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Date of Service</p>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                    formatDate(selectedItem.deliveryDate) }}</p>
                            </div>
                            <div v-if="selectedItem.expiryDate">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Expiry Date</p>
                                <p
                                    :class="`text-sm font-semibold ${isExpiringSoon(selectedItem.expiryDate) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'}`">
                                    {{ formatDate(selectedItem.expiryDate) }}
                                </p>
                                <span v-if="isExpiringSoon(selectedItem.expiryDate)"
                                    class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                                    <AlertTriangle class="w-3 h-3 mr-1" />
                                    Expiring Soon
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Patient Information Card -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-2">
                            <div
                                class="w-8 h-8 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                Patient Info
                            </h3>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Patient Name</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedItem.patientName
                                || 'N/A'
                            }}</p>
                        </div>
                    </div>

                    <!-- Usage Details Card -->
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-2">
                            <div
                                class="w-8 h-8 bg-pink-100 dark:bg-pink-900/30 rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-4 h-4 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                Usage Details
                            </h3>
                        </div>
                        <div class="space-y-2">
                            <div v-if="selectedItem.woundPart">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Wound Part</p>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                    selectedItem.woundPart }}</p>
                            </div>
                            <div v-if="selectedItem.quantity">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Quantity Used</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedItem.quantity
                                }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Description/Notes Card -->
                    <div v-if="selectedItem.description"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-2">
                            <div
                                class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                Description / Notes
                            </h3>
                        </div>
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{
                                selectedItem.description
                                }}</p>
                        </div>
                    </div>

                    <!-- File Upload Card -->
                    <div v-if="selectedItem.filepath"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-2">
                            <div
                                class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mr-2">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                Attached File
                            </h3>
                        </div>
                        <div>
                            <a :href="selectedItem.filepath" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg transition-all duration-200 text-xs font-medium">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View File
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Status-specific Alert -->
                <div v-if="selectedItem.status === 'expected'"
                    class="bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl p-4">
                    <div class="flex items-start">
                        <Clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 flex-shrink-0 mt-0.5" />
                        <div>
                            <h4 class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-1">Awaiting
                                Delivery</h4>
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">This item is expected but not yet
                                delivered.
                                Please update the status when received.</p>
                        </div>
                    </div>
                </div>

                <!-- Usage History -->
                <div v-if="['used', 'partially_used'].includes(selectedItem.status) && selectedItem.usageLogs && selectedItem.usageLogs.length > 0"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div
                        class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <CheckCircle2 class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                            Usage History
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Patient</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Date of Service</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Wound Site</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Clinician</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="log in selectedItem.usageLogs" :key="log.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        {{
                                            log.patientName }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{
                                        formatDate(log.dateOfService) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ log.woundSite }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{
                                        getClinicianName(log.clinicianId) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ log.notes || 'N/A'
                                        }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <template #actions>
                <div
                    class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700/50 border-t border-gray-200 dark:border-gray-600">
                    <div v-if="selectedItem" class="px-6 py-4">
                        <!-- Compact Status Bar with Actions -->
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- Current Status Badge -->
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Status:</span>
                                <span :class="getStatusColor(selectedItem.status)"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm">
                                    {{ formatStatusText(selectedItem.status) }}
                                </span>
                                <!-- Status Description Tooltip Button -->
                                <button @click="showStatusInfo = !showStatusInfo"
                                    class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Quick Actions Dropdown -->
                            <div class="flex items-center gap-2">
                                <!-- Primary Actions (Always Visible) -->
                                <template v-for="action in getPrimaryActions(selectedItem.status).slice(0, 2)"
                                    :key="action.status">
                                    <button @click="handleStatusUpdate(selectedItem.id, action.status)"
                                        :class="action.class"
                                        class="inline-flex items-center px-3 py-2 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 text-xs font-medium">
                                        <component :is="action.icon" class="w-3.5 h-3.5 mr-1.5" />
                                        {{ action.label }}
                                    </button>
                                </template>

                                <!-- More Actions Dropdown -->
                                <div v-if="getSecondaryActions(selectedItem.status).length > 0 || getPrimaryActions(selectedItem.status).length > 2"
                                    class="relative">
                                    <button @click="showMoreActions = !showMoreActions"
                                        class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg shadow-sm hover:shadow-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 text-xs font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                        More Actions
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div v-if="showMoreActions" @click.stop
                                        class="absolute right-0 bottom-full mb-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-xl z-50 overflow-hidden">
                                        <!-- Remaining Primary Actions -->
                                        <template v-for="action in getPrimaryActions(selectedItem.status).slice(2)"
                                            :key="'primary-' + action.status">
                                            <button
                                                @click="handleStatusUpdate(selectedItem.id, action.status); showMoreActions = false"
                                                class="w-full flex items-center px-4 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700">
                                                <component :is="action.icon"
                                                    class="w-4 h-4 mr-3 text-green-600 dark:text-green-400" />
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                    action.label }}</span>
                                            </button>
                                        </template>

                                        <!-- Secondary Actions -->
                                        <div v-if="getSecondaryActions(selectedItem.status).length > 0"
                                            class="border-t-2 border-gray-200 dark:border-gray-600">
                                            <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-700/50">
                                                <span
                                                    class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Other
                                                    Actions</span>
                                            </div>
                                            <template v-for="action in getSecondaryActions(selectedItem.status)"
                                                :key="'secondary-' + action.status">
                                                <button
                                                    @click="handleStatusUpdate(selectedItem.id, action.status); showMoreActions = false"
                                                    class="w-full flex items-center px-4 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700 last:border-0">
                                                    <component :is="action.icon"
                                                        class="w-4 h-4 mr-3 text-gray-600 dark:text-gray-400" />
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{
                                                        action.label }}</span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Info (Collapsible) -->
                        <div v-if="showStatusInfo"
                            class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-blue-900 dark:text-blue-100">{{
                                        getStatusDescription(selectedItem.status).title }}</p>
                                    <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">{{
                                        getStatusDescription(selectedItem.status).description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </BaseModal>

        <!-- Edit Modal -->
        <BaseModal v-model="showEditModal" title="Edit Inventory Item" width="max-w-3xl">
            <div v-if="editingItem" class="space-y-6">
                <!-- Serial Number -->
                <div
                    class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700/50 dark:to-blue-900/20 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                    <label
                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                        Serial Number
                    </label>
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
                            <Package class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <input v-model="editingItem.serialNumber" type="text"
                            class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 font-mono text-lg font-bold text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter serial number" />
                    </div>
                </div>

                <!-- Product Information Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                    <h3
                        class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide mb-4 flex items-center">
                        <div class="w-1 h-5 bg-blue-600 rounded-full mr-3"></div>
                        Usage Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Graft Size
                            </label>
                            <select v-model="editingItem.sizeId"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200">
                                <option value="">No Size Selected</option>
                                <option v-for="size in graftSizes" :key="size.graft_size_id"
                                    :value="size.graft_size_id">
                                    {{ size.size }} ({{ getBrandName(size.brand_id) }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Status
                            </label>
                            <select v-model="editingItem.status"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200">
                                <option value="expected">Expected</option>
                                <option value="delivered">Delivered</option>
                                <option value="used">Used</option>
                                <option value="partially_used">Partially Used</option>
                                <option value="reassigned">Reassigned</option>
                                <option value="unused">Unused</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Date of Service
                            </label>
                            <input v-model="editingItem.deliveryDate" type="date"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Expiry Date
                            </label>
                            <input v-model="editingItem.expiryDate" type="date"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Wound Part
                            </label>
                            <input v-model="editingItem.woundPart" type="text" placeholder="Enter wound part/location"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Quantity Used
                            </label>
                            <input v-model.number="editingItem.quantity" type="number" min="0"
                                placeholder="Enter quantity"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Patient Name
                            </label>
                            <div
                                class="flex items-center px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 mr-2" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white font-medium">{{ editingItem.patientName ||
                                    'N/A'
                                }}</span>
                                <span class="ml-auto text-xs text-gray-500 dark:text-gray-400 italic">Read-only</span>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Notes/Description
                            </label>
                            <textarea v-model="editingItem.productName" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                                placeholder="Enter additional notes or description"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <template #actions>
                <div class="flex justify-between items-center px-6 py-4 bg-gray-50 dark:bg-gray-700/30">
                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Changes will be saved immediately&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                    <div class="flex space-x-3">
                        <button @click="showEditModal = false"
                            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 font-medium">
                            Cancel
                        </button>
                        <button @click="handleSaveEdit"
                            class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </div>
            </template>
        </BaseModal>

        <!-- Delete Modal -->
        <BaseModal v-model="showDeleteModal" title="Delete Inventory Item" width="max-w-md">
            <div class="space-y-6">
                <!-- Warning Icon -->
                <div class="flex justify-center">
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <AlertTriangle class="w-8 h-8 text-red-600 dark:text-red-400" />
                    </div>
                </div>

                <!-- Message -->
                <div class="text-center space-y-2">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Are you sure?</h3>
                    <p class="text-gray-600 dark:text-gray-400">You are about to delete the inventory item:</p>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 mt-3">
                        <p class="font-mono font-bold text-lg text-gray-900 dark:text-white">{{
                            itemToDelete?.serialNumber }}
                        </p>
                    </div>
                </div>

                <!-- Warning Message -->
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                    <div class="flex items-start">
                        <AlertTriangle class="w-5 h-5 text-red-600 dark:text-red-400 mr-3 flex-shrink-0 mt-0.5" />
                        <div>
                            <h4 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-1">Warning: This action
                                cannot be
                                undone</h4>
                            <p class="text-sm text-red-800 dark:text-red-300">All data associated with this inventory
                                item will
                                be permanently removed from the system.</p>
                        </div>
                    </div>
                </div>
            </div>
            <template #actions>
                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30">
                    <button @click="showDeleteModal = false"
                        class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 font-medium">
                        Cancel
                    </button>
                    <button @click="confirmDeleteItem"
                        class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md hover:shadow-lg font-medium focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <Trash2 class="w-4 h-4 inline mr-2" />
                        Delete Permanently
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import InventoryList from '@/components/Inventory/InventoryList.vue'
import UsageLogForm from '@/components/Inventory/UsageLogForm.vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import { inventoryService, graftSizeService, userService, orderService, brandService, patientService } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
// Replaced Heroicons with Lucide icons
import {
    Plus,
    UploadCloud,
    FileText,
    Package,
    MapPin,
    CheckCircle2,
    Calendar,
    Clock,
    Repeat2,
    CornerUpLeft,
    AlertTriangle,
    BarChart3,
    Search,
    Filter,
    ChevronDown,
    Eye,
    FilePenLine,
    Trash2,
    X
} from 'lucide-vue-next';
import Swal from 'sweetalert2'
import { createWorker } from 'tesseract.js'

interface InventoryItem {
    id: string
    serialNumber: string
    clinicId: string
    clinicName?: string
    status: string
    expiryDate?: string
    usageLogs: any[]
    // Optional / derived fields used by the UI
    productName?: string
    description?: string
    brandId: string
    brandName?: string
    sizeId: string
    sizeName?: string
    orderId?: string
    orderCode?: string
    receivedDate?: string
    usedDate?: string
    deliveryDate?: string
    patientId?: string
    patientName?: string
    woundPart?: string
    filepath?: string
    quantity?: number
}

// Main inventory state now comes from the backend (woundmed_usage_log via /inventory/all)
const inventory = ref<InventoryItem[]>([])
const authStore = useAuthStore()
const graftSizes = ref<Array<{ graft_size_id: string; brand_id: string; size: string; area: number; price: number }>>([])
const orders = ref<Array<any>>([])

const fetchInventory = async () => {
    const response = await inventoryService.getAllInventory()
    const payload = response.data
    const rawItems: any[] = Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : []

    inventory.value = rawItems.map((item: any) => {
        // Match order based on brand, graft size, clinic, and patient
        const matchedOrder = findMatchingOrder(
            item.brandId,
            item.sizeId,
            item.clinicId,
            item.patientId
        )

        return {
            id: String(item.id ?? item.serialNumber),
            serialNumber: String(item.serialNumber ?? ''),
            clinicId: item.clinicId ? String(item.clinicId) : '',
            clinicName: item.clinicName ?? 'Unknown Clinic',
            status: String(item.status ?? 'expected'),
            expiryDate: item.expiryDate ?? item.expiredAt ?? undefined,
            usageLogs: Array.isArray(item.usageLogs) ? item.usageLogs : [],
            // Map some helpful label fields for the UI
            productName: item.description ?? undefined,
            description: item.description ?? undefined,
            deliveryDate: item.dateOfService ?? undefined,
            brandId: String(item.brandId ?? ''),
            brandName: item.brandName ?? 'Unknown Brand',
            sizeId: String(item.sizeId ?? ''),
            sizeName: item.graftSize ?? 'Unknown Size',
            patientId: item.patientId ? String(item.patientId) : undefined,
            patientName: item.patientName ?? undefined,
            woundPart: item.woundPart ?? undefined,
            filepath: item.filepath ?? undefined,
            quantity: item.quantity ?? undefined,
            orderId: matchedOrder?.order_id ? String(matchedOrder.order_id) : undefined,
            orderCode: matchedOrder?.order_code ?? undefined,
        }
    })
}

const findMatchingOrder = (
    brandId: any,
    graftId: any,
    clinicId: any,
    patientId: any
): any | undefined => {
    if (!brandId || !graftId || !clinicId || !patientId) return undefined

    // Find orders that match all criteria
    return orders.value.find((order: any) => {
        // Check if order has items array
        if (!Array.isArray(order.items)) return false

        // Check basic order-level matches
        const orderMatches =
            String(order.clinic_id) === String(clinicId) &&
            String(order.patient_id) === String(patientId)

        if (!orderMatches) return false

        // Check if any item in the order matches brand and graft size
        return order.items.some((item: any) =>
            String(item.brand_id) === String(brandId) &&
            String(item.graft_id) === String(graftId)
        )
    })
}

onMounted(() => {
    tableLoader.value = true
    Promise.all([
        // Fetch orders first so we can match them
        orderService.getAllOrders({ per_page: 1000 })
            .then(({ data }) => {
                const rows: any[] = Array.isArray(data?.order_data) ? data.order_data : []
                orders.value = rows
            }),
        // Fetch brands
        brandService.getAllBrands({ per_page: 1000 })
            .then(({ data }) => {
                const rows: any[] = Array.isArray(data?.data) ? data.data : []
                brands.value = rows
            }),
        // Fetch clinics
        userService.getClinics()
            .then(({ data }) => {
                const rows: any[] = Array.isArray(data?.clinic_data) ? data.clinic_data : Array.isArray(data) ? data : []
                clinics.value = rows
            }),
        // Fetch graft sizes
        graftSizeService.getAllGraftSizes({ per_page: 1000 })
            .then(({ data }) => {
                const rows: any[] = Array.isArray(data?.graftData) ? data.graftData : []
                graftSizes.value = rows.map((g: any) => ({
                    graft_size_id: String(g.graft_size_id),
                    brand_id: String(g.brand_id),
                    size: g.size,
                    area: Number(g.area ?? 0),
                    price: Number(g.price ?? 0),
                }))
            }),
        // Fetch clinicians
        userService.getClinicians(true)
            .then(({ data }) => {
                const rows: any[] = Array.isArray(data) ? data : []
                clinicians.value = rows.map((c: any) => ({
                    id: String(c.id),
                    name: c.name || 'Unknown Clinician',
                }))
            }),
        // Fetch patients
        patientService.getAllPatients()
            .then(({ data }) => {
                const rows: any[] = Array.isArray(data?.patient_info) ? data.patient_info : Array.isArray(data) ? data : []
                patients.value = rows
            })
    ]).then(() => {
        // Fetch inventory after other data is loaded
        return fetchInventory()
    }).catch((error) => {
        console.error('Failed to load data', error)
    }).finally(() => {
        tableLoader.value = false
    })
})

const searchTerm = ref('')
const statusFilter = ref('all')
const brandFilter = ref('all')
const showUploadModal = ref(false)
const showItemModal = ref(false)
const selectedItem = ref<InventoryItem | null>(null)
const showUsageLogForm = ref(false)
const showEditModal = ref(false)
const editingItem = ref<InventoryItem | null>(null)
const showDeleteModal = ref(false)
const itemToDelete = ref<InventoryItem | null>(null)
const showStatistics = ref(false) // New: Statistics are hidden by default
const tableLoader = ref(false) // Loading state for table
const showMoreActions = ref(false) // Dropdown state for more actions
const showStatusInfo = ref(false) // Toggle for status description

// OCR Upload state
const uploadedFile = ref<File | null>(null)
const previewUrl = ref<string | null>(null)
const isProcessingOCR = ref(false)
const ocrProgress = ref(0)
const rawOcrText = ref('')
const showRawOcrText = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const extractedData = ref({
    serialNumber: '',
    patientId: '',
    patientName: '',
    dateOfService: '',
    woundSite: '',
    clinicianId: '',
    notes: '',
    clinicId: '',
    graftSizeId: '',
    expiredAt: '',
    quantityUsed: 1
})

// Data from backend
const brands = ref<Array<any>>([])
const clinics = ref<Array<any>>([])
const clinicians = ref<Array<{ id: string; name: string }>>([])
const patients = ref<Array<any>>([])

const uniqueBrands = computed(() => {
    const brandNames = inventory.value
        .map(item => item.brandName || 'Unknown Brand')
        .filter(name => name !== 'Unknown Brand')
    return [...new Set(brandNames)]
})

const filteredInventory = computed(() => {
    return inventory.value.filter(item => {
        const brandName = item.brandName || 'Unknown Brand'
        const matchesSearch = item.serialNumber.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            (item.productName?.toLowerCase() || '').includes(searchTerm.value.toLowerCase()) ||
            brandName.toLowerCase().includes(searchTerm.value.toLowerCase())
        const matchesStatus = statusFilter.value === 'all' || item.status === statusFilter.value
        const matchesBrand = brandFilter.value === 'all' || brandName === brandFilter.value
        return matchesSearch && matchesStatus && matchesBrand
    })
})

// New computed properties for statistics
const statusCounts = computed(() => {
    const counts: Record<string, number> = {}
    inventory.value.forEach(item => {
        counts[item.status] = (counts[item.status] || 0) + 1
    })
    return counts
})

// New methods for statistics
function getStatusCount(status: string) {
    return inventory.value.filter(item => item.status === status).length
}

function getExpiringSoonCount() {
    return inventory.value.filter(item =>
        item.expiryDate && isExpiringSoon(item.expiryDate)
    ).length
}

function formatStatusText(status: string) {
    return status.replace('_', ' ').charAt(0).toUpperCase() + status.replace('_', ' ').slice(1)
}

function getBrandName(brandId?: string) {
    // First try to find in current inventory items (which have brandName from backend)
    const item = inventory.value.find(i => i.brandId === brandId)
    if (item?.brandName) return item.brandName

    // Fallback to brands data
    if (!brandId) return 'Unknown Brand'
    const brand = brands.value.find((b: any) => String(b.brand_id) === String(brandId) || String(b.id) === String(brandId))
    return brand?.brand_name || brand?.brandName || 'Unknown Brand'
}

function getSizeName(brandId?: string, sizeId?: string) {
    // First try to find in current inventory items (which have sizeName from backend)
    const item = inventory.value.find(i => i.brandId === brandId && i.sizeId === sizeId)
    if (item?.sizeName) return item.sizeName

    // Fallback to graft sizes data
    if (!brandId || !sizeId) return 'Unknown Size'
    const graftSize = graftSizes.value.find((g: any) =>
        String(g.graft_size_id) === String(sizeId) || String(g.id) === String(sizeId)
    )
    return graftSize?.size || 'Unknown Size'
}

function getAvailableSizes(brandId?: string) {
    if (!brandId) return []
    // Get all graft sizes for this brand
    return graftSizes.value.filter((g: any) =>
        String(g.brand_id) === String(brandId)
    )
}

function getStatusColor(status: string) {
    switch (status) {
        case 'expected': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200'
        case 'delivered': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200'
        case 'used': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
        case 'partially_used': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200'
        case 'reassigned': return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-200'
        case 'expired': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200'
        case 'unused': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200'
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
    }
}

function getStatusIcon(status: string) {
    switch (status) {
        case 'expected': return Clock
        case 'delivered': return Package
        case 'used': return CheckCircle2
        case 'expired': return AlertTriangle
        default: return null
    }
}

function getStatusDescription(status: string): { title: string; description: string } {
    const descriptions: Record<string, { title: string; description: string }> = {
        'expected': {
            title: 'Expected',
            description: 'From initial order - Item is expected to be delivered'
        },
        'delivered': {
            title: 'Delivered',
            description: 'Confirmed by manufacturer and logged - Item has been confirmed and recorded'
        },
        'used': {
            title: 'Used',
            description: 'Marked as used via usage log - Item has been used in patient treatment'
        },
        'partially_used': {
            title: 'Partially Used',
            description: 'Serial used across multiple wounds - This serial number was used for multiple patient wounds'
        },
        'reassigned': {
            title: 'Reassigned',
            description: 'Used for patient other than intended - Item was used for a different patient than originally planned'
        },
        'unused': {
            title: 'Unused',
            description: 'No usage record yet - Item has not been used'
        },
        'expired': {
            title: 'Expired',
            description: 'Time alert for expiration - Item has passed its expiration date'
        }
    }
    return descriptions[status] || { title: status, description: '' }
}

function getPrimaryActions(currentStatus: string) {
    const actions: Array<{ status: string; label: string; icon: any; class: string }> = []

    switch (currentStatus) {
        case 'expected':
            actions.push(
                { status: 'delivered', label: 'Mark Delivered', icon: Package, class: 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white' },
                { status: 'used', label: 'Mark Used', icon: CheckCircle2, class: 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white' }
            )
            break
        case 'delivered':
            actions.push(
                { status: 'used', label: 'Mark Used', icon: CheckCircle2, class: 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white' },
                { status: 'partially_used', label: 'Mark Partially Used', icon: Repeat2, class: 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white' }
            )
            break
        case 'partially_used':
            actions.push(
                { status: 'used', label: 'Complete Usage', icon: CheckCircle2, class: 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white' }
            )
            break
        case 'unused':
            actions.push(
                { status: 'used', label: 'Mark Used', icon: CheckCircle2, class: 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white' },
                { status: 'partially_used', label: 'Mark Partially Used', icon: Repeat2, class: 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white' }
            )
            break
    }

    return actions
}

function getSecondaryActions(currentStatus: string) {
    const actions: Array<{ status: string; label: string; icon: any; class: string }> = []

    switch (currentStatus) {
        case 'expected':
            actions.push(
                { status: 'partially_used', label: 'Partially Used', icon: Repeat2, class: 'bg-white dark:bg-gray-800 border-2 border-purple-300 dark:border-purple-600 text-purple-700 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20' },
                { status: 'reassigned', label: 'Reassign', icon: Repeat2, class: 'bg-white dark:bg-gray-800 border-2 border-orange-300 dark:border-orange-600 text-orange-700 dark:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20' },
                { status: 'unused', label: 'Mark Unused', icon: Package, class: 'bg-white dark:bg-gray-800 border-2 border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20' },
                { status: 'expired', label: 'Mark Expired', icon: AlertTriangle, class: 'bg-white dark:bg-gray-800 border-2 border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' }
            )
            break
        case 'delivered':
            actions.push(
                { status: 'reassigned', label: 'Reassign', icon: Repeat2, class: 'bg-white dark:bg-gray-800 border-2 border-orange-300 dark:border-orange-600 text-orange-700 dark:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20' },
                { status: 'unused', label: 'Mark Unused', icon: Package, class: 'bg-white dark:bg-gray-800 border-2 border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20' },
                { status: 'expired', label: 'Mark Expired', icon: AlertTriangle, class: 'bg-white dark:bg-gray-800 border-2 border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' }
            )
            break
        case 'partially_used':
            actions.push(
                { status: 'reassigned', label: 'Reassign', icon: Repeat2, class: 'bg-white dark:bg-gray-800 border-2 border-orange-300 dark:border-orange-600 text-orange-700 dark:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20' },
                { status: 'expired', label: 'Mark Expired', icon: AlertTriangle, class: 'bg-white dark:bg-gray-800 border-2 border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' }
            )
            break
        case 'unused':
            actions.push(
                { status: 'reassigned', label: 'Reassign', icon: Repeat2, class: 'bg-white dark:bg-gray-800 border-2 border-orange-300 dark:border-orange-600 text-orange-700 dark:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20' },
                { status: 'expired', label: 'Mark Expired', icon: AlertTriangle, class: 'bg-white dark:bg-gray-800 border-2 border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' }
            )
            break
        case 'used':
            actions.push(
                { status: 'expired', label: 'Mark Expired', icon: AlertTriangle, class: 'bg-white dark:bg-gray-800 border-2 border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' }
            )
            break
        case 'reassigned':
            actions.push(
                { status: 'used', label: 'Mark Used', icon: CheckCircle2, class: 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white' },
                { status: 'expired', label: 'Mark Expired', icon: AlertTriangle, class: 'bg-white dark:bg-gray-800 border-2 border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' }
            )
            break
        case 'expired':
            // No actions available for expired items
            break
    }

    return actions
}

async function handleStatusUpdate(itemId: string, newStatus: InventoryItem['status']) {
    try {
        // Update local state first
        inventory.value = inventory.value.map(item =>
            item.id === itemId
                ? { ...item, status: newStatus }
                : item
        )

        // Extract the usage log ID from the item ID (format: 'inv-{id}')
        const usageLogId = Number(String(itemId).replace(/^inv-/, ''))

        if (!Number.isFinite(usageLogId)) {
            console.error('Invalid usage log id for status update:', itemId)
            return
        }

        // Map status to log_status numeric value
        const statusMap: Record<string, number> = {
            'expected': 0,
            'delivered': 1,
            'used': 2,
            'partially_used': 3,
            'reassigned': 4,
            'unused': 5,
            'expired': 6
        }

        const logStatus = statusMap[newStatus]
        if (logStatus === undefined) {
            console.error('Invalid status:', newStatus)
            return
        }

        // Call the backend API to update status
        await inventoryService.updateInventoryStatus(usageLogId, logStatus)

        await Swal.fire({
            title: 'Success!',
            text: 'Inventory status updated successfully.',
            icon: 'success',
            confirmButtonColor: '#2563eb',
            timer: 1500,
            showConfirmButton: false
        })

        // Close the modal after successful update
        showItemModal.value = false
        selectedItem.value = null
    } catch (error) {
        console.error('Failed to update inventory status:', error)

        await Swal.fire({
            title: 'Error!',
            text: 'Failed to update inventory status. Please try again.',
            icon: 'error',
            confirmButtonColor: '#2563eb'
        })

        // Revert the local state on error
        await fetchInventory()
    }
}

function formatDate(dateString?: string) {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString()
}

function isExpiringSoon(expiryDateString?: string) {
    if (!expiryDateString) return false
    const expiryDate = new Date(expiryDateString)
    const today = new Date()
    const diffTime = Math.abs(expiryDate.getTime() - today.getTime())
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays <= 30 && diffDays > 0
}

function getClinicName(clinicId?: string) {
    const item = inventory.value.find(i => i.clinicId === clinicId)
    if (item?.clinicName) return item.clinicName

    if (!clinicId) return 'Unknown Clinic'
    const clinic = clinics.value.find((c: any) => String(c.clinic_id) === String(clinicId) || String(c.id) === String(clinicId))
    return clinic?.clinic_name || clinic?.name || 'Unknown Clinic'
}

function getClinicianName(clinicianId: string) {
    const clinician = clinicians.value.find(c => c.id === clinicianId)
    return clinician?.name || 'Unknown Clinician'
}

// Event handlers
function handleViewItem(item: InventoryItem) {
    selectedItem.value = item
    showItemModal.value = true
    // Reset dropdown and status info state when opening modal
    showMoreActions.value = false
    showStatusInfo.value = false
}

function handleEditItem(item: InventoryItem) {
    editingItem.value = { ...item }
    showEditModal.value = true
}

// Close dropdown when clicking outside
if (typeof document !== 'undefined') {
    document.addEventListener('click', (event) => {
        const target = event.target as HTMLElement
        if (!target.closest('.relative')) {
            showMoreActions.value = false
        }
    })
}

async function handleSaveEdit() {
    if (!editingItem.value) return

    try {
        const rawId = editingItem.value.id
        const usageLogId = Number(String(rawId).replace(/^inv-/, ''))

        if (!Number.isFinite(usageLogId)) {
            console.error('Invalid usage log id for edit:', rawId)
            return
        }

        const statusMap: Record<string, number> = {
            'expected': 0,
            'delivered': 1,
            'used': 2,
            'partially_used': 3,
            'reassigned': 4,
            'unused': 5,
            'expired': 6
        }

        const payload: any = {}

        if (editingItem.value.serialNumber) {
            payload.serial_number = editingItem.value.serialNumber
        }

        if (editingItem.value.sizeId) {
            payload.graft_size_id = Number(editingItem.value.sizeId)
        }

        if (editingItem.value.deliveryDate) {
            payload.date_of_service = editingItem.value.deliveryDate
        }

        if (editingItem.value.status) {
            payload.log_status = statusMap[editingItem.value.status] ?? 0
        }

        if (editingItem.value.expiryDate) {
            payload.expired_at = editingItem.value.expiryDate
        }

        if (editingItem.value.woundPart) {
            payload.wound_part = editingItem.value.woundPart
        }

        if (editingItem.value.quantity !== undefined) {
            payload.quantity_used = Number(editingItem.value.quantity)
        }

        if (editingItem.value.productName) {
            payload.description = editingItem.value.productName
        }

        await inventoryService.updateUsageLog(usageLogId, payload)

        await fetchInventory()

        await Swal.fire({
            title: 'Success!',
            text: 'Inventory information has been updated successfully.',
            icon: 'success',
            confirmButtonColor: '#2563eb',
            timer: 2000,
            showConfirmButton: false
        })

        console.log('Usage log updated successfully')
    } catch (error) {
        console.error('Failed to update usage log:', error)

        await Swal.fire({
            title: 'Error!',
            text: 'Failed to update inventory information. Please try again.',
            icon: 'error',
            confirmButtonColor: '#2563eb'
        })
    } finally {
        showEditModal.value = false
        editingItem.value = null
    }
}

async function handleUsageLogSubmit(usageLog: {
    serialNumber: string
    patientId: string
    patientName: string
    dateOfService: string
    woundSite: string
    clinicianId: string
    notes: string
    clinicId: string
    graftSizeId: string
    expiredAt: string
    quantityUsed: number
}) {
    console.log('Usage log submitted:', usageLog)

    const currentUserId = authStore.user?.id
    if (!currentUserId) {
        console.error('Cannot log usage: no authenticated user')
        return
    }

    try {
        const payload = {
            serial_number: usageLog.serialNumber,
            patient_id: Number(usageLog.patientId),
            logged_by: Number(currentUserId),
            graft_size_id: usageLog.graftSizeId ? Number(usageLog.graftSizeId) : null,
            date_of_service: usageLog.dateOfService,
            wound_part: usageLog.woundSite,
            log_status: 2, // 2 = used (see getStatusFromLogStatus)
            quantity_used: usageLog.quantityUsed || 1,
            description: usageLog.notes || null,
            expired_at: usageLog.expiredAt || null,
            filepath: null,
        }

        await inventoryService.createUsageLog(payload)

        // Refresh inventory from backend so UI stays in sync
        await fetchInventory()

        console.log('Usage log saved to backend')
    } catch (error) {
        console.error('Failed to save usage log to backend', error)
    } finally {
        showUsageLogForm.value = false
    }
}

function handleUsageLogCancel() {
    showUsageLogForm.value = false
}

function handleUsageLogSubmitAndHide(log: any) {
    handleUsageLogSubmit(log)
    showUsageLogForm.value = false
}
function handleUsageLogCancelAndHide() {
    handleUsageLogCancel()
    showUsageLogForm.value = false
}

function handleDeleteItem(item: InventoryItem) {
    itemToDelete.value = item
    showDeleteModal.value = true
}

async function confirmDeleteItem() {
    if (!itemToDelete.value) return

    try {
        // InventoryController@getInventory uses 'inv-{id}' where {id} is the UsageLog primary key
        const rawId = itemToDelete.value.id
        const usageLogId = Number(String(rawId).replace(/^inv-/, ''))

        if (!Number.isFinite(usageLogId)) {
            console.error('Invalid usage log id for delete:', rawId)
            return
        }

        await inventoryService.deleteUsageLog(usageLogId)

        // Remove from local state
        inventory.value = inventory.value.filter(i => i.id !== itemToDelete.value!.id)
    } catch (error) {
        console.error('Failed to delete inventory item', error)
    } finally {
        showDeleteModal.value = false
        itemToDelete.value = null
    }
}

// OCR Upload Functions
async function handleFileUpload(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    uploadedFile.value = file

    // Create preview URL for images
    if (file.type.startsWith('image/')) {
        previewUrl.value = URL.createObjectURL(file)
        // Process OCR
        await processOCR(file)
    } else if (file.type === 'application/pdf') {
        // For PDFs, we'd need to convert to image first or use PDF.js
        await Swal.fire({
            title: 'PDF Support',
            text: 'PDF processing requires conversion to images. Please upload a JPG or PNG image for now.',
            icon: 'info',
            confirmButtonColor: '#2563eb'
        })
        clearUpload()
    }
}

async function processOCR(file: File) {
    isProcessingOCR.value = true
    ocrProgress.value = 0
    rawOcrText.value = ''

    try {
        const worker = await createWorker('eng', 1, {
            logger: (m: any) => {
                if (m.status === 'recognizing text') {
                    ocrProgress.value = Math.round(m.progress * 100)
                }
            }
        })

        const { data: { text } } = await worker.recognize(file)
        rawOcrText.value = text

        // Extract information from OCR text
        parseOCRText(text)

        await worker.terminate()
    } catch (error) {
        console.error('OCR processing failed:', error)
        await Swal.fire({
            title: 'OCR Error',
            text: 'Failed to extract text from image. Please enter the information manually.',
            icon: 'error',
            confirmButtonColor: '#2563eb'
        })
    } finally {
        isProcessingOCR.value = false
    }
}

function parseOCRText(text: string) {
    // Normalize text first
    const cleanText = text.replace(/\s+/g, ' ').trim();

    const serialPattern = /(?:serial|s\/\?n|serial number|serial no\.?)[:\s-]*([A-Za-z0-9\- ]{3,})/i;
    const datePattern = /\b(\d{1,2}[-/.\s]\d{1,2}[-/.\s]\d{2,4})|\b(\d{4}[-/.\s]\d{1,2}[-/.\s]\d{1,2})/;
    const quantityPattern = /\b(?:quantity|qty|qty\.?|amount)[:\s-]*([0-9]+)/i;
    const patientPattern = /(?:patient|patient name|name)[:\s]*([A-Za-z\s.'-]{2,50})/i;
    const woundPattern = /(?:wound|site|location)[:\s]*([A-Za-z\s.'-]{2,50})/i;
    const clinicianPattern = /(?:clinician|doctor|physician)[:\s]*([A-Za-z\s.'-]{2,50})/i; 
    const expiryPattern = /(?:expiry|exp|expires)[:\s]*([.\d/ -]{4,})/i;

    const graftSizePattern = /(?:size|graft size)[:\s]*([\d\s\w\W]{2,30})/i;
    const clinicPattern = /(?:clinic)[:\s]*([A-Za-z\s.'-]{2,50})/i;

    const serialMatch = cleanText.match(serialPattern);
    if (serialMatch) extractedData.value.serialNumber = serialMatch[1].trim();

    const dateMatch = cleanText.match(datePattern);
    if (dateMatch) {
        const dateStr = dateMatch[0];
        const date = new Date(dateStr);
        if (!isNaN(date.getTime())) {
            extractedData.value.dateOfService = date.toISOString().split('T')[0];
        }
    }

    const quantityMatch = cleanText.match(quantityPattern);
    if (quantityMatch) extractedData.value.quantityUsed = parseInt(quantityMatch[1]);

    const patientMatch = cleanText.match(patientPattern);
    if (patientMatch) extractedData.value.patientName = patientMatch[1].trim();

    const woundMatch = cleanText.match(woundPattern);
    if (woundMatch) extractedData.value.woundSite = woundMatch[1].trim();

    const clinicianMatch = cleanText.match(clinicianPattern);
    if (clinicianMatch) extractedData.value.clinicianId = clinicianMatch[1].trim();

    const expiryMatch = cleanText.match(expiryPattern);
    if (expiryMatch) {
        const expiryStr = expiryMatch[1];
        const expiryDate = new Date(expiryStr);
        if (!isNaN(expiryDate.getTime())) {
            extractedData.value.expiredAt = expiryDate.toISOString().split('T')[0];
        }
    }

    const graftSizeMatch = cleanText.match(graftSizePattern);
    if (graftSizeMatch) extractedData.value.graftSizeId = graftSizeMatch[1].trim();

    const clinicMatch = cleanText.match(clinicPattern);
    if (clinicMatch) extractedData.value.clinicId = clinicMatch[1].trim();

    extractedData.value.notes = text.substring(0, 500);
}


function clearUpload() {
    uploadedFile.value = null
    previewUrl.value = null
    rawOcrText.value = ''
    showRawOcrText.value = false
    isProcessingOCR.value = false
    ocrProgress.value = 0
    extractedData.value = {
        serialNumber: '',
        patientId: '',
        patientName: '',
        dateOfService: '',
        woundSite: '',
        clinicianId: '',
        notes: '',
        clinicId: '',
        graftSizeId: '',
        expiredAt: '',
        quantityUsed: 1
    }
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

function closeUploadModal() {
    clearUpload()
    showUploadModal.value = false
}

async function submitExtractedData() {
    // Validate required fields
    if (!extractedData.value.serialNumber) {
        await Swal.fire({
            title: 'Missing Information',
            text: 'Serial Number is required',
            icon: 'warning',
            confirmButtonColor: '#2563eb'
        })
        return
    }

    if (!extractedData.value.dateOfService) {
        await Swal.fire({
            title: 'Missing Information',
            text: 'Date of Service is required',
            icon: 'warning',
            confirmButtonColor: '#2563eb'
        })
        return
    }

    if (!extractedData.value.woundSite) {
        await Swal.fire({
            title: 'Missing Information',
            text: 'Wound Site is required',
            icon: 'warning',
            confirmButtonColor: '#2563eb'
        })
        return
    }

    try {
        const currentUserId = authStore.user?.id
        if (!currentUserId) {
            await Swal.fire({
                title: 'Error',
                text: 'No authenticated user found',
                icon: 'error',
                confirmButtonColor: '#2563eb'
            })
            return
        }

        // Validate required fields from extractedData
        if (!extractedData.value.patientId) {
            await Swal.fire({
                title: 'Missing Information',
                text: 'Patient is required',
                icon: 'warning',
                confirmButtonColor: '#2563eb'
            })
            return
        }

        if (!extractedData.value.clinicianId) {
            await Swal.fire({
                title: 'Missing Information',
                text: 'Clinician is required',
                icon: 'warning',
                confirmButtonColor: '#2563eb'
            })
            return
        }

        // Create usage log
        const payload = {
            serial_number: extractedData.value.serialNumber,
            patient_id: Number(extractedData.value.patientId),
            logged_by: Number(currentUserId),
            graft_size_id: extractedData.value.graftSizeId ? Number(extractedData.value.graftSizeId) : null,
            date_of_service: extractedData.value.dateOfService,
            wound_part: extractedData.value.woundSite,
            log_status: 2, // Used
            quantity_used: extractedData.value.quantityUsed || 1,
            description: extractedData.value.notes || null,
            expired_at: extractedData.value.expiredAt || null,
            filepath: null,
            clinic_id: extractedData.value.clinicId || null
        }

        await inventoryService.createUsageLog(payload)
        await fetchInventory()

        await Swal.fire({
            title: 'Success!',
            text: 'Usage log created successfully from OCR data.',
            icon: 'success',
            confirmButtonColor: '#2563eb',
            timer: 2000,
            showConfirmButton: false
        })

        closeUploadModal()
    } catch (error) {
        console.error('Failed to create usage log from OCR:', error)
        await Swal.fire({
            title: 'Error',
            text: 'Failed to create usage log. Please try again.',
            icon: 'error',
            confirmButtonColor: '#2563eb'
        })
    }
}

function formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

function handleBulkUpload() {
    showUploadModal.value = true
}

// Watch for selectedItem changes to show modal
import { watch } from 'vue'
watch(selectedItem, (newItem) => {
    showItemModal.value = !!newItem
})
</script>