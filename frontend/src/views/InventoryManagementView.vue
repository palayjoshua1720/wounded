<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Inventory & Serial Tracking</h1>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl">Manage inventory items, track serial numbers, and monitor usage across all clinics</p>
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
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ getStatusCount('delivered') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Available</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                            <Clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ getStatusCount('expected') }}</p>
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
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('delivered') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Clock class="w-4 h-4 mr-2 text-yellow-500" /> Expected
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('expected') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <CheckCircle2 class="w-4 h-4 mr-2 text-green-500" /> Used
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('used') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <AlertTriangle class="w-4 h-4 mr-2 text-red-500" /> Expired
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('expired') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Repeat2 class="w-4 h-4 mr-2 text-purple-500" /> Partially Used
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('partially_used') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <CornerUpLeft class="w-4 h-4 mr-2 text-gray-500" /> Returned
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('returned') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Package class="w-4 h-4 mr-2 text-indigo-500" /> Unused
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('unused') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Repeat2 class="w-4 h-4 mr-2 text-orange-500" /> Reassigned
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ getStatusCount('reassigned') }}</span>
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
                        <input v-model="searchTerm" type="text" placeholder="Search by serial number, product name, or brand..."
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
        <div v-if="showUsageLogForm" class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Log Product Usage</h3>
                <button @click="showUsageLogForm = false" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                    <X class="w-5 h-5" />
                </button>
            </div>
            <UsageLogForm
                :inventory-items="inventory"
                :clinicians="clinicians"
                :brands="brands"
                @submit="handleUsageLogSubmitAndHide"
                @bulk-upload="handleBulkUpload"
                @cancel="handleUsageLogCancelAndHide"
            />
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
                        <tr v-for="item in filteredInventory" :key="item.id"
                            class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                                        <Package class="w-5 h-5" />
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.productName || 'N/A' }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ getBrandName(item.brandId) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-900 dark:text-white">{{ item.serialNumber }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ getSizeName(item.brandId, item.sizeId) }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ getClinicName(item.clinicId) }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span
                                    :class="['inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200', getStatusColor(item.status)]">
                                    {{ formatStatusText(item.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white" :class="isExpiringSoon(item.expiryDate) ? 'text-red-600' : ''">
                                    {{ formatDate(item.expiryDate) }}
                                    <span v-if="isExpiringSoon(item.expiryDate)" class="text-xs text-red-500 block">Expiring Soon</span>
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
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredInventory.length === 0" class="text-center py-12">
                <div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <Package class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No inventory items found</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Try adjusting your search or filter to find what you're looking for.</p>
            </div>
        </div>

        <!-- Upload Modal -->
        <BaseModal v-model="showUploadModal" title="Upload Log Usage" width="max-w-2xl">
            <div class="space-y-6">
                <!-- Upload Area -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-dashed border-blue-300 dark:border-blue-600 rounded-2xl p-10 text-center transition-all duration-300 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg group">
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-white dark:bg-gray-800 rounded-full shadow-md mb-4 group-hover:scale-110 transition-transform duration-300">
                            <UploadCloud class="w-10 h-10 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Upload Log Usage</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                            Drag and drop your file here, or click to browse
                        </p>
                    </div>
                    
                    <input
                        type="file"
                        accept=".pdf,.csv,.xlsx,.xls"
                        class="hidden"
                        id="file-upload"
                    />
                    <label
                        for="file-upload"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-md hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 cursor-pointer font-medium group-hover:shadow-xl"
                    >
                        <FileText class="w-5 h-5 mr-2" />
                        Choose File
                    </label>
                </div>

                <!-- Supported Formats Info -->
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-5 h-5 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mt-0.5">
                            <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Supported File Formats</h4>
                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <div class="flex items-center">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></span>
                                    PDF (.pdf)
                                </div>
                                <div class="flex items-center">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></span>
                                    Excel (.xlsx)
                                </div>
                                <div class="flex items-center">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></span>
                                    Image Files (.jpg, .png)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #actions>
                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30">
                    <button
                        @click="showUploadModal = false"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-md hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-medium hover:shadow-lg"
                    >
                        Process File
                    </button>
                </div>
            </template>
        </BaseModal>

        <!-- Item Details Modal -->
        <BaseModal v-model="showItemModal" title="Inventory Item Details" width="max-w-4xl">
            <div v-if="selectedItem" class="space-y-6">
                <!-- Serial Number Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-100 mb-1">Serial Number</p>
                            <p class="text-2xl font-bold font-mono tracking-wide">{{ selectedItem.serialNumber }}</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
                            <span :class="`inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold ${getStatusColor(selectedItem.status)} shadow-sm`">
                                {{ formatStatusText(selectedItem.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Product Information Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Product Details Card -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
                                <Package class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Product Details</h3>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Brand</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ getBrandName(selectedItem.brandId) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Size</p>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ getSizeName(selectedItem.brandId, selectedItem.sizeId) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-3">
                                <MapPin class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Location</h3>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Assigned Clinic</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ getClinicName(selectedItem.clinicId) }}</p>
                        </div>
                    </div>

                    <!-- Order Information Card -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mr-3">
                                <FileText class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Order Info</h3>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Order ID</p>
                            <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">#{{ selectedItem.orderId ? selectedItem.orderId.slice(-6).toUpperCase() : 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Date Information Card -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3">
                                <Calendar class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Important Dates</h3>
                        </div>
                        <div class="space-y-3">
                            <div v-if="selectedItem.deliveryDate">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Delivery Date</p>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ formatDate(selectedItem.deliveryDate) }}</p>
                            </div>
                            <div v-if="selectedItem.expiryDate">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Expiry Date</p>
                                <p :class="`text-sm font-semibold ${isExpiringSoon(selectedItem.expiryDate) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'}`">
                                    {{ formatDate(selectedItem.expiryDate) }}
                                </p>
                                <span v-if="isExpiringSoon(selectedItem.expiryDate)" class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                                    <AlertTriangle class="w-3 h-3 mr-1" />
                                    Expiring Soon
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status-specific Alert -->
                <div v-if="selectedItem.status === 'expected'" class="bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl p-4">
                    <div class="flex items-start">
                        <Clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 flex-shrink-0 mt-0.5" />
                        <div>
                            <h4 class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-1">Awaiting Delivery</h4>
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">This item is expected but not yet delivered. Please update the status when received.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Usage History -->
                <div v-if="['used', 'partially_used'].includes(selectedItem.status) && selectedItem.usageLogs && selectedItem.usageLogs.length > 0" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <CheckCircle2 class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                            Usage History
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Date of Service</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Wound Site</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Clinician</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="log in selectedItem.usageLogs" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ log.patientName }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ formatDate(log.dateOfService) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ log.woundSite }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ getClinicianName(log.clinicianId) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ log.notes || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <template #actions>
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-700 px-6 py-5">
                    <div v-if="selectedItem && selectedItem.status !== 'used' && selectedItem.status !== 'expired' && selectedItem.status !== 'returned' && selectedItem.status !== 'reassigned' && selectedItem.status !== 'unused'" class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide mb-3">Quick Actions</h3>
                        <div class="flex flex-wrap gap-3">
                            <button
                                v-if="selectedItem && selectedItem.status === 'expected'"
                                @click="handleStatusUpdate(selectedItem.id, 'delivered')"
                                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-md hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 text-sm font-medium hover:shadow-lg"
                            >
                                <Package class="w-4 h-4 mr-2" />
                                Mark Delivered
                            </button>
                            <button
                                v-if="selectedItem && ['delivered', 'partially_used'].includes(selectedItem.status)"
                                @click="handleStatusUpdate(selectedItem.id, 'used')"
                                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl shadow-md hover:from-green-700 hover:to-emerald-700 transition-all duration-200 text-sm font-medium hover:shadow-lg"
                            >
                                <CheckCircle2 class="w-4 h-4 mr-2" />
                                Mark Used
                            </button>
                            <button
                                v-if="selectedItem && ['delivered', 'partially_used'].includes(selectedItem.status)"
                                @click="handleStatusUpdate(selectedItem.id, 'reassigned')"
                                class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 text-sm font-medium hover:shadow-md"
                            >
                                <Repeat2 class="w-4 h-4 mr-2" />
                                Reassign
                            </button>
                            <button
                                v-if="selectedItem && ['delivered', 'partially_used'].includes(selectedItem.status)"
                                @click="handleStatusUpdate(selectedItem.id, 'returned')"
                                class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 text-sm font-medium hover:shadow-md"
                            >
                                <CornerUpLeft class="w-4 h-4 mr-2" />
                                Return
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </BaseModal>

        <!-- Edit Modal -->
        <BaseModal v-model="showEditModal" title="Edit Inventory Item" width="max-w-3xl">
            <div v-if="editingItem" class="space-y-6">
                <!-- Serial Number (Read-only) -->
                <div class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700/50 dark:to-blue-900/20 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                        Serial Number
                    </label>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
                            <Package class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <input
                            v-model="editingItem.serialNumber"
                            type="text"
                            class="flex-1 px-4 py-3 border-0 bg-transparent font-mono text-lg font-bold text-gray-900 dark:text-white focus:ring-0"
                            readonly
                        />
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 ml-13 flex items-center">
                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        Serial number cannot be changed
                    </p>
                </div>

                <!-- Product Information Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide mb-4 flex items-center">
                        <div class="w-1 h-5 bg-blue-600 rounded-full mr-3"></div>
                        Product Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Brand
                            </label>
                            <select
                                v-model="editingItem.brandId"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            >
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                    {{ brand.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Size
                            </label>
                            <select
                                v-model="editingItem.sizeId"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            >
                                <option v-for="size in getAvailableSizes(editingItem.brandId)" :key="size.id" :value="size.id">
                                    {{ size.size }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Status
                            </label>
                            <select
                                v-model="editingItem.status"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            >
                                <option value="expected">Expected</option>
                                <option value="delivered">Delivered</option>
                                <option value="used">Used</option>
                                <option value="returned">Returned</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                Order ID
                            </label>
                            <input
                                v-model="editingItem.orderId"
                                type="text"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                                placeholder="Enter order ID"
                            />
                        </div>
                    </div>
                </div>

                <!-- Location & Dates Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide mb-4 flex items-center">
                        <div class="w-1 h-5 bg-green-600 rounded-full mr-3"></div>
                        Location & Dates
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                <MapPin class="w-4 h-4 inline mr-1" />
                                Assigned Clinic
                            </label>
                            <select
                                v-model="editingItem.clinicId"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            >
                                <option value="">No Clinic Assigned</option>
                                <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
                                    {{ clinic.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                <Clock class="w-4 h-4 inline mr-1" />
                                Delivery Date
                            </label>
                            <input
                                v-model="editingItem.deliveryDate"
                                type="date"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                <Calendar class="w-4 h-4 inline mr-1" />
                                Expiry Date
                            </label>
                            <input
                                v-model="editingItem.expiryDate"
                                type="date"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <template #actions>
                <div class="flex justify-between items-center px-6 py-4 bg-gray-50 dark:bg-gray-700/30">
                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Changes will be saved immediately&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                    <div class="flex space-x-3">
                        <button
                            @click="showEditModal = false"
                            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 font-medium"
                        >
                            Cancel
                        </button>
                        <button
                            @click="handleSaveEdit"
                            class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
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
                        <p class="font-mono font-bold text-lg text-gray-900 dark:text-white">{{ itemToDelete?.serialNumber }}</p>
                    </div>
                </div>

                <!-- Warning Message -->
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                    <div class="flex items-start">
                        <AlertTriangle class="w-5 h-5 text-red-600 dark:text-red-400 mr-3 flex-shrink-0 mt-0.5" />
                        <div>
                            <h4 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-1">Warning: This action cannot be undone</h4>
                            <p class="text-sm text-red-800 dark:text-red-300">All data associated with this inventory item will be permanently removed from the system.</p>
                        </div>
                    </div>
                </div>
            </div>
            <template #actions>
                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 dark:bg-gray-700/30">
                    <button 
                        @click="showDeleteModal = false" 
                        class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 font-medium"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmDeleteItem" 
                        class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md hover:shadow-lg font-medium focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        <Trash2 class="w-4 h-4 inline mr-2" />
                        Delete Permanently
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import InventoryList from '@/components/Inventory/InventoryList.vue'
import UsageLogForm from '@/components/Inventory/UsageLogForm.vue'
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

interface InventoryItem {
    id: string
    serialNumber: string
    brandId: string
    sizeId: string
    clinicId: string
    status: string
    expiryDate?: string
    usageLogs: any[]
    productName?: string
    orderId?: string
    receivedDate?: string
    usedDate?: string
    deliveryDate?: string
}

// --- ALL SCRIPT LOGIC BELOW REMAINS UNCHANGED ---

// Replace inventory data with React sample data
const inventory = ref<InventoryItem[]>([
  {
    id: 'inv-1',
	productName: 'This is a product name',
    serialNumber: 'SN001234',
    brandId: 'brand-1',
    sizeId: 'size-1',
    orderId: 'order-1',
    clinicId: 'clinic-1',
    status: 'expected',
    expiryDate: '2024-12-31',
    deliveryDate: '',
    usageLogs: []
  },
  {
    id: 'inv-2',
    serialNumber: 'SN001235',
    brandId: 'brand-1',
    sizeId: 'size-1',
    orderId: 'order-1',
    clinicId: 'clinic-1',
    status: 'delivered',
    expiryDate: '2024-12-31',
    deliveryDate: '2024-01-18',
    usageLogs: []
  },
  {
    id: 'inv-3',
    serialNumber: 'SN001236',
    brandId: 'brand-2',
    sizeId: 'size-5',
    orderId: 'order-2',
    clinicId: 'clinic-2',
    status: 'used',
    expiryDate: '2024-11-15',
    deliveryDate: '2024-02-10',
    usageLogs: [
      {
        id: 'usage-1',
        serialNumber: 'SN001236',
        patientName: 'Jane Doe',
        dateOfService: '2024-03-01',
        woundSite: 'Right leg',
        clinicianId: '1',
        notes: 'Healed well'
      },
      {
        id: 'usage-2',
        serialNumber: 'SN001236',
        patientName: 'John Smith',
        dateOfService: '2024-03-15',
        woundSite: 'Left arm',
        clinicianId: '2',
        notes: 'Follow-up required'
      }
    ]
  },
  {
    id: 'inv-4',
    serialNumber: 'SN001237',
    brandId: 'brand-2',
    sizeId: 'size-6',
    orderId: 'order-3',
    clinicId: 'clinic-2',
    status: 'returned',
    expiryDate: '2024-10-10',
    deliveryDate: '2024-03-01',
    usageLogs: []
  },
  {
    id: 'inv-5',
    serialNumber: 'SN001238',
    brandId: 'brand-3',
    sizeId: 'size-8',
    orderId: 'order-4',
    clinicId: 'clinic-1',
    status: 'expired',
    expiryDate: '2024-04-01',
    deliveryDate: '2023-04-01',
    usageLogs: []
  },
  {
    id: 'inv-6',
    serialNumber: 'SN001239',
    brandId: 'brand-3',
    sizeId: 'size-9',
    orderId: 'order-5',
    clinicId: 'clinic-2',
    status: 'partially_used',
    expiryDate: '2024-09-01',
    deliveryDate: '2024-05-01',
    usageLogs: [
      {
        id: 'usage-3',
        serialNumber: 'SN001239',
        patientName: 'Alice Brown',
        dateOfService: '2024-06-01',
        woundSite: 'Back',
        clinicianId: '3',
        notes: 'Partial use'
      }
    ]
  },
  {
    id: 'inv-7',
    serialNumber: 'SN001240',
    brandId: 'brand-1',
    sizeId: 'size-2',
    orderId: 'order-6',
    clinicId: 'clinic-1',
    status: 'reassigned',
    expiryDate: '2024-08-01',
    deliveryDate: '2024-06-01',
    usageLogs: []
  },
  {
    id: 'inv-8',
    serialNumber: 'SN001241',
    brandId: 'brand-2',
    sizeId: 'size-7',
    orderId: 'order-7',
    clinicId: 'clinic-2',
    status: 'unused',
    expiryDate: '2024-12-01',
    deliveryDate: '',
    usageLogs: []
  }
])

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

// Mock data for brands, clinics, and clinicians
const brands = ref([
  {
    id: 'brand-1',
    name: 'DermaGraft Pro',
    manufacturerId: 'mfg-1',
    productType: 'graft',
    asp: 1250.00,
    mue: 4,
    description: 'Advanced dermal graft for wound healing',
    isActive: true,
    sizes: [
      { id: 'size-1', size: '2cm x 2cm', area: 4, price: 1250.00 },
      { id: 'size-2', size: '2cm x 3cm', area: 6, price: 1875.00 },
      { id: 'size-3', size: '3cm x 3cm', area: 9, price: 2812.50 },
      { id: 'size-4', size: '4cm x 4cm', area: 16, price: 5000.00 }
    ]
  },
  {
    id: 'brand-2',
    name: 'HealMatrix Advanced',
    manufacturerId: 'mfg-2',
    productType: 'graft',
    asp: 980.00,
    mue: 6,
    description: 'Matrix-based healing solution',
    isActive: true,
    sizes: [
      { id: 'size-5', size: '2cm x 2cm', area: 4, price: 980.00 },
      { id: 'size-6', size: '2cm x 3cm', area: 6, price: 1470.00 },
      { id: 'size-7', size: '3cm x 4cm', area: 12, price: 2940.00 }
    ]
  },
  {
    id: 'brand-3',
    name: 'Biolab Skin Graft',
    manufacturerId: 'biolab',
    productType: 'graft',
    asp: 850.00,
    mue: 5,
    description: 'Bioengineered skin graft technology',
    isActive: true,
    sizes: [
      { id: 'size-8', size: '2cm x 2cm', area: 4, price: 850.00 },
      { id: 'size-9', size: '3cm x 3cm', area: 9, price: 1912.50 },
      { id: 'size-10', size: '4cm x 3cm', area: 12, price: 2550.00 }
    ]
  }
])

const clinics = ref([
  {
    id: 'clinic-1',
    name: 'Metro Wound Care Center',
    address: '123 Medical Plaza, City, ST 12345',
    phone: '(555) 123-4567',
    email: 'contact@metrowound.com',
    contactPerson: 'Dr. Sarah Johnson',
    licenseNumber: 'WC-12345',
    taxId: '12-3456789',
    isActive: true
  },
  {
    id: 'clinic-2',
    name: 'Advanced Healing Institute',
    address: '456 Healthcare Blvd, City, ST 12345',
    phone: '(555) 987-6543',
    email: 'info@advancedhealing.com',
    contactPerson: 'Dr. Michael Chen',
    licenseNumber: 'WC-67890',
    taxId: '98-7654321',
    isActive: true
  }
])

const clinicians = ref([
    { id: '1', name: 'Dr. Sarah Johnson' },
    { id: '2', name: 'Dr. Michael Chen' },
    { id: '3', name: 'Dr. Emily Rodriguez' },
    { id: '4', name: 'Dr. David Thompson' }
])

const uniqueBrands = computed(() => [...new Set(inventory.value.map(item => getBrandName(item.brandId)))])

const filteredInventory = computed(() => {
    return inventory.value.filter(item => {
        const matchesSearch = item.serialNumber.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        (item.productName?.toLowerCase() || '').includes(searchTerm.value.toLowerCase()) ||
        getBrandName(item.brandId).toLowerCase().includes(searchTerm.value.toLowerCase())
        const matchesStatus = statusFilter.value === 'all' || item.status === statusFilter.value
        const matchesBrand = brandFilter.value === 'all' || getBrandName(item.brandId) === brandFilter.value
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

function getBrandName(brandId: string) {
    const brand = brands.value.find(b => b.id === brandId)
    return brand?.name || 'Unknown Brand'
}

function getSizeName(brandId: string, sizeId: string) {
    const brand = brands.value.find(b => b.id === brandId)
    const size = brand?.sizes.find(s => s.id === sizeId)
    return size?.size || 'Unknown Size'
}

function getAvailableSizes(brandId: string) {
    const brand = brands.value.find(b => b.id === brandId)
    return brand?.sizes || []
}

function getStatusColor(status: string) {
    switch (status) {
        case 'expected': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200'
        case 'delivered': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200'
        case 'used': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
        case 'partially_used': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200'
        case 'returned': return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-200'
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

function handleStatusUpdate(itemId: string, newStatus: InventoryItem['status']) {
    inventory.value = inventory.value.map(item => 
        item.id === itemId 
        ? { ...item, status: newStatus }
        : item
    )
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

function getClinicName(clinicId: string) {
    const clinic = clinics.value.find(c => c.id === clinicId)
    return clinic?.name || 'Unknown Clinic'
}

function getClinicianName(clinicianId: string) {
    const clinician = clinicians.value.find(c => c.id === clinicianId)
    return clinician?.name || 'Unknown Clinician'
}

// Event handlers
function handleViewItem(item: InventoryItem) {
    selectedItem.value = item
    showItemModal.value = true
}

function handleEditItem(item: InventoryItem) {
    editingItem.value = { ...item }
    showEditModal.value = true
}

function handleSaveEdit() {
    if (editingItem.value) {
        // Find and update the item in the inventory
        const itemIndex = inventory.value.findIndex(item => item.id === editingItem.value!.id)
        if (itemIndex !== -1) {
        inventory.value[itemIndex] = { ...editingItem.value }
        console.log('Item updated:', inventory.value[itemIndex])
        }
    }
    showEditModal.value = false
    editingItem.value = null
}

function handleUsageLogSubmit(usageLog: any) {
    console.log('Usage log submitted:', usageLog)

    // Find the inventory item by serial number
    const itemIndex = inventory.value.findIndex(item => item.serialNumber === usageLog.serialNumber)

    if (itemIndex !== -1) {
        // Create a new usage log entry
        const newUsageLog = {
            id: Date.now().toString(),
            serialNumber: usageLog.serialNumber,
            patientName: usageLog.patientName,
            dateOfService: usageLog.dateOfService,
            woundSite: usageLog.woundSite,
            clinicianId: usageLog.clinicianId,
            notes: usageLog.notes,
            createdAt: new Date().toISOString()
        }
        
        // Add the usage log to the item
        inventory.value[itemIndex].usageLogs.push(newUsageLog)
        
        // Do NOT update the status to 'used' after logging usage
        // inventory.value[itemIndex].status = 'used'
        // inventory.value[itemIndex].usedDate = usageLog.dateOfService
        
        console.log('Inventory updated:', inventory.value[itemIndex])
    }

    showUsageLogForm.value = false
}

function handleUsageLogCancel() {
    showUsageLogForm.value = false
}

function handleBulkUpload(file: File) {
    console.log('Bulk upload file:', file)
    // Implement bulk upload functionality
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

function confirmDeleteItem() {
    if (itemToDelete.value) {
        inventory.value = inventory.value.filter(i => i.id !== itemToDelete.value!.id)
        showDeleteModal.value = false
        itemToDelete.value = null
    }
}

// Watch for selectedItem changes to show modal
import { watch } from 'vue'
    watch(selectedItem, (newItem) => {
    showItemModal.value = !!newItem
})
</script>