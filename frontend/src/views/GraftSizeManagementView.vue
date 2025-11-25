<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Graft Sizes Management</h1>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl">Manage graft sizes, their stock levels, and availability
                    in one centralized dashboard</p>
            </div>
            <div class="flex items-center gap-4">
                <button @click="showStats = !showStats"
                    class="flex items-center px-5 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">
                    <BarChart2 class="w-5 h-5 mr-2" />
                    {{ showStats ? 'Hide' : 'Show' }} Stats
                </button>
                <button @click="clearForm(); selectedGraftRequest = null; showCreateForm = true"
                    class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
                    <ListPlus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
                    New Graft Size
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Graft Size Statistics</h3>
                <!-- Main Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-6">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <PencilRuler class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Sizes</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <CheckCircle2 class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                            <XCircle class="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.inactive }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Inactive</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                            <Archive class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.archived }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Archived</p>
                        </div>
                    </div>
                </div>
                <hr class="border-gray-200 dark:border-gray-700">
                <!-- Brand Breakdown -->
                <div class="mt-6">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Brand Breakdown</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4">
                        <div class="flex items-center justify-between" v-for="brand in stats.brands.slice(0, 6)" :key="brand.id">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Package class="w-4 h-4 mr-2 text-purple-500" /> {{ brand.name }}
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ brand.count }}</span>
                        </div>
                    </div>
                    <p v-if="stats.brands.length > 6" class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                        Showing top 6 brands. {{ stats.brands.length - 6 }} more...
                    </p>
                </div>
            </div>
        </transition>
        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row gap-6">
                <div class="flex-1">
                    <div class="relative">
                        <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                        <input v-model="searchTerm" type="text" placeholder="Search by brand or size..."
                            class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative">
                        <Filter class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
                        <select v-model="statusFilter"
                            class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="archived">Archived</option>
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                    <div class="relative">
                        <select v-model="itemsPerPage"
                            class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                </div>
            </div>
        </div>
        <!-- Graft Sizes Table Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Manufacturer - Brand
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Size
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Stock
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Created
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <TableLoader v-if="tableLoader" :colspan="6" />
                        <tr v-else v-for="graft in paginatedGrafts" :key="graft.graft_size_id"
                            class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 font-medium text-sm">
                                        <PencilRuler class="w-5 h-5" />
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ graft.manufacturer?.manufacturer_name || 'N/A' }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ graft.brand?.brand_name || 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ graft.size }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ graft.area ? graft.area + ' cm²' : 'Area N/A' }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ graft.stock || 0 }} in stock
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span
                                    :class="['inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200', getStatusColor(graft.graft_status)]">
                                    <component :is="getStatusIcon(graft.graft_status)" class="w-3 h-3 mr-1.5" />
                                    {{ graftStatus[graft.graft_status as keyof typeof graftStatus].label }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(graft.created_at) }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button @click="selectedGraftRequest = graft"
                                        class="p-2 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all duration-200"
                                        title="View Details">
                                        <Eye class="w-4 h-4" />
                                    </button>
                                    <button @click="editGraft(graft)"
                                        class="p-2 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-all duration-200"
                                        title="Edit Graft">
                                        <SquarePen class="w-4 h-4" />
                                    </button>
                                    <button v-if="graft.graft_status !== 2" @click="confirmToggleStatus(graft)" :class="[
                                        'p-2 rounded-lg transition-all duration-200',
                                        graft.graft_status === 0
                                            ? 'text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20'
                                            : 'text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'
                                    ]" :title="graft.graft_status === 0 ? 'Deactivate' : 'Activate'">
                                        <component :is="graft.graft_status === 0 ? XCircle : CheckCircle2" class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmArchive(graft)" :class="[
                                        'p-2 rounded-lg transition-all duration-200',
                                        graft.graft_status === 2
                                            ? 'text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'
                                            : 'text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20'
                                    ]" :title="graft.graft_status === 2 ? 'Unarchive Graft' : 'Archive Graft'">
                                        <component :is="graft.graft_status === 2 ? ArchiveRestore : Archive" class="w-4 h-4" />
                                    </button>
                                    <button v-if="graft.graft_status === 2" @click="confirmDelete(graft)"
                                        class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200"
                                        title="Delete Archived Graft">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Empty State -->
            <div v-if="filteredGraftRequest.length === 0 && !tableLoader" class="text-center py-12">
                <div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <PencilRuler class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No graft sizes found</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Try adjusting your search or filter to find
                    what you're looking for.</p>
            </div>
            <!-- Pagination -->
            <div v-if="filteredGraftRequest.length > 0 && !tableLoader"
                class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Showing <span class="font-semibold text-gray-800 dark:text-white">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to <span class="font-semibold text-gray-800 dark:text-white">{{ Math.min(currentPage * itemsPerPage, totalResults) }}</span> of <span
                        class="font-semibold text-gray-800 dark:text-white">{{ totalResults }}</span> results
                </p>
                <nav class="flex items-center space-x-2">
                    <button @click="previousPage" :disabled="currentPage === 1"
                        class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Previous
                    </button>
                    <div class="flex items-center space-x-1">
                        <template v-for="(page, index) in paginationNumbers" :key="index">
                            <span v-if="page === '...'"
                                class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">...</span>
                            <button v-else @click="goToPage(page as number)"
                                :class="['px-3 py-1.5 text-sm font-medium rounded-lg transition-colors', currentPage === page ? 'bg-green-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700']">
                                {{ page }}
                            </button>
                        </template>
                    </div>
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Next
                    </button>
                </nav>
            </div>
        </div>
        <!-- Graft Details Modal -->
        <BaseModal v-model="showDetailsModal" title="Graft Size Details" size="lg">
            <template v-if="selectedGraftRequest">
                <div class="space-y-6 animate-fade-in">
                    <!-- Header with Key Info -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-xl flex items-center justify-center text-green-600 dark:text-green-400 font-medium text-lg">
                                <PencilRuler class="w-8 h-8" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white truncate" :aria-label="`Graft Size ID: ${selectedGraftRequest.graft_size_id}`">
                                        {{ selectedGraftRequest.size }}
                                    </h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        Area: {{ selectedGraftRequest.area ? `${selectedGraftRequest.area} cm²` : 'N/A' }} • 
                                        Price: ${{ selectedGraftRequest.price ? selectedGraftRequest.price.toFixed(2) : 'N/A' }}
                                    </p>
                                </div>
                                <span :class="['inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium', getStatusColor(selectedGraftRequest.graft_status)]" :aria-label="`Status: ${graftStatus[selectedGraftRequest.graft_status as keyof typeof graftStatus].label}`">
                                    <component :is="getStatusIcon(selectedGraftRequest.graft_status)" class="w-4 h-4 mr-1.5" />
                                    {{ graftStatus[selectedGraftRequest.graft_status as keyof typeof graftStatus].label }}
                                </span>
                            </div>
                            <!-- Low Stock Alert -->
                            <div v-if="isLowStock(selectedGraftRequest.stock)" class="mt-2 inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800" role="alert">
                                <AlertCircle class="w-3 h-3 mr-1.5" />
                                Low Stock: Only {{ selectedGraftRequest.stock }} remaining
                            </div>
                        </div>
                    </div>

                    <!-- Core Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <Factory class="w-5 h-5 text-green-500 flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Manufacturer</p>
                                    <p class="text-gray-900 dark:text-white truncate">{{ selectedGraftRequest.manufacturer?.manufacturer_name || 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Package class="w-5 h-5 text-purple-500 flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Brand</p>
                                    <p class="text-gray-900 dark:text-white truncate">{{ selectedGraftRequest.brand?.brand_name || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <PackageCheck class="w-5 h-5 text-blue-500 flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock</p>
                                    <p class="text-gray-900 dark:text-white">{{ selectedGraftRequest.stock || 0 }} units available</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <DollarSign class="w-5 h-5 text-green-500 flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Price</p>
                                    <p class="text-gray-900 dark:text-white truncate">{{ selectedGraftRequest.price ? selectedGraftRequest.price.toFixed(2) : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex flex-col md:flex-row text-center">
                            <div class="w-full md:w-1/2 md:border-r border-gray-200 dark:border-gray-700">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Date Created</label>
                                <div class="text-sm text-gray-900 dark:text-white flex items-center justify-center">
                                    <CalendarPlus class="w-4 h-4 mr-1" />
                                    {{ formatDate(selectedGraftRequest.created_at) }}
                                </div>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Last Updated</label>
                                <div class="text-sm text-gray-900 dark:text-white flex items-center justify-center">
                                    <CalendarPlus class="w-4 h-4 mr-1" />
                                    {{ formatDate(selectedGraftRequest.updated_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </BaseModal>
        <!-- Create/Edit Form Modal -->
        <BaseModal v-model="showFormModal" :title="showCreateForm ? 'New Graft Size' : 'Edit Graft Size'" size="2xl">
            <form @submit.prevent="handleSubmitForm" class="space-y-6">
                <div class="sm:col-span-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Brand<span
                                class="text-red-500 ml-1">*</span></label>
                       <select v-model="formData.brand_id" :disabled="!showCreateForm" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option disabled value="">Select a Brand</option>
                            <option
                                v-for="brand in brandData"
                                :key="brand.brand_id"
                                :value="brand.brand_id"
                                :selected="showEditForm && formData.brand_id === brand.brand_id">
                                {{ brand.manufacturer?.manufacturer_name || 'Unknown Manufacturer' }} - {{ brand.brand_name }}
                            </option>
                        </select>
                    </div>
                </div>
                <!-- Graft Sizes -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <PencilRuler class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Graft Sizes<span class="text-red-500">*</span></h3>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="(graftSize, index) in formData.graftSizes"
                            :key="graftSize.id || index"
                            :class="index > 0 ? 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700' : ''"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Size {{ index + 1 }}</span>
                                <button
                                    v-if="formData.graftSizes.length > 1"
                                    type="button"
                                    @click="removeGraftSize(index)"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Size (e.g., 2cm x 2cm)</label>
                                    <div class="relative">
                                        <RulerDimensionLine class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                        <input
                                            v-model="graftSize.size"
                                            type="text"
                                            required
                                            placeholder="2cm x 2cm"
                                            class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                                focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Area (cm²)</label>
                                    <div class="relative">
                                        <Diameter class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                        <input
                                            v-model.number="graftSize.area"
                                            type="number"
                                            required
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                                focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Price</label>
                                    <div class="relative">
                                        <DollarSign class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                        <input
                                            v-model.number="graftSize.price"
                                            type="number"
                                            required
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                                focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Stock</label>
                                    <div class="relative">
                                        <Package class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                                        <input
                                            v-model.number="graftSize.stock"
                                            type="number"
                                            required
                                            min="0"
                                            placeholder="0"
                                            class="mt-1 block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                                focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            v-if="showCreateForm"
                            @click="addGraftSize"
                            class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors w-full sm:w-auto"
                        >
                            <Plus class="w-4 h-4 mr-2" />
                            Add Size
                        </button>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="closeForm"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-md">
                        {{ showCreateForm ? 'Create Graft Size' : 'Update Graft Size' }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'
import BaseModal from '@/components/common/BaseModal.vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import {
    Search, Eye, SquarePen, Trash2, Package, Archive, ArchiveRestore, PencilRuler, ListPlus,
    RulerDimensionLine, Diameter, DollarSign, Plus, BarChart2, CheckCircle2, Filter,
    ChevronDown, Factory, XCircle, AlertCircle, PackageCheck, CalendarPlus, Clock
} from 'lucide-vue-next'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'
interface GraftSize {
    id?: string
    size: string
    area: number | null
    price: number | null
    stock: number
}
interface GraftRequest {
    graft_size_id: string
    brand_id: string
    size: string
    area?: number | null
    price?: number | null
    stock: number
    graft_status: number
    created_at: string
    updated_at: string
    // Nested relations
    brand?: {
        brand_id: string
        brand_name: string
    }
    manufacturer?: {
        manufacturer_id: string
        manufacturer_name: string
    }
}
interface Brand {
    brand_id: string
    brand_name: string
    manufacturer?: {
        manufacturer_id: string
        manufacturer_name: string
    }
}
const graftRequest = ref<GraftRequest[]>([])
const brandData = ref<Brand[]>([])
const itemsPerPage = ref(10)
const currentPage = ref(1)
const totalResults = ref(0)
const tableLoader = ref(false)
const searchTerm = ref('')
const statusFilter = ref('all')
const selectedGraftRequest = ref<GraftRequest | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showStats = ref(false)
const graftStatus = {
    0: { label: 'Active', classes: 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' },
    1: { label: 'Inactive', classes: 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' },
    2: { label: 'Archived', classes: 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400' },
};
const formData = ref({
    brand_id: '',
    graftSizes: [{ size: '', area: null, price: null, stock: 0, id: undefined as string | undefined }] as GraftSize[]
})
const serverStats = ref({
    total: 0,
    active: 0,
    inactive: 0,
    archived: 0,
    brands: [] as { id: string; name: string; count: number }[]
})
const stats = computed(() => serverStats.value)
const isLowStock = (stock: number) => stock < 10 // Threshold: Customize as needed
function addGraftSize() {
    formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0, id: undefined })
}
function removeGraftSize(index: number) {
    formData.value.graftSizes.splice(index, 1)
    if (formData.value.graftSizes.length === 0) {
        formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0, id: undefined })
    }
}
async function editGraft(graft: GraftRequest) {
    selectedGraftRequest.value = graft
    showCreateForm.value = false
    showEditForm.value = true
    await nextTick()
    const brandId = graft.brand_id || (graft.brand?.brand_id || '') // Fallback to nested
    formData.value = {
        brand_id: brandId,
        graftSizes: [{
        id: graft.graft_size_id,
        size: graft.size,
        area: graft.area ?? null,
        price: graft.price ?? null,
        stock: graft.stock
        }]
    }
    // Optional: Validate brand exists in brandData
    if (!brandData.value.some(b => b.brand_id === brandId)) {
        toast.warning('Brand not found in list. Please refresh.')
    }
}
async function confirmDelete(graft: GraftRequest) {
    try {
        const result = await Swal.fire({
            title: "Deleting Graft Size",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc2626",
            cancelButtonColor: "#6b7280",
            confirmButtonText: "Yes, delete it!"
        })
        if (result.isConfirmed) {
            await api.put(`/management/delete/${graft.graft_size_id}/deletegraftsize`)
            await Swal.fire({
                title: "Deleted!",
                text: "Graft Size has been deleted.",
                icon: "success",
                timer: 2000,
                showConfirmButton: false
            })
            toast.success('Graft deleted successfully!')
            await getAllGraftRequests()
            await fetchGraftStats()
        }
    } catch (error) {
        toast.error('Failed to delete Graft.')
    }
}
async function confirmToggleStatus(graft: GraftRequest) {
    const isActive = graft.graft_status === 0
    const action = isActive ? 'deactivate' : 'activate'
    const actionTitle = `${action.charAt(0).toUpperCase() + action.slice(1)} Graft Size`
    const text = isActive
        ? 'Are you sure you want to deactivate this graft size? It will no longer be available for orders.'
        : 'Are you sure you want to activate this graft size? It will be available for orders again.'
    try {
        const result = await Swal.fire({
            title: actionTitle,
            text: text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: isActive ? "#dc2626" : "#16a34a",
            cancelButtonColor: "#6b7280",
            confirmButtonText: `Yes, ${action} it!`,
        })
        if (result.isConfirmed) {
            const endpoint = isActive
                ? `/management/deactivate/${graft.graft_size_id}/deactivategraftsize`
                : `/management/activate/${graft.graft_size_id}/activategraftsize`
            await api.put(endpoint)
            await Swal.fire({
                title: 'Success!',
                text: `Graft Size has been ${action}ed.`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            })
            toast.success(`Graft ${action}ed successfully!`)
            await getAllGraftRequests()
            await fetchGraftStats()
        }
    } catch (error) {
        toast.error(`Failed to ${action} Graft.`)
    }
}
async function confirmArchive(graft: GraftRequest) {
    try {
        const isArchived = graft.graft_status === 2;
        const action = isArchived ? 'unarchive' : 'archive';
        const actionTitle = `${action.charAt(0).toUpperCase() + action.slice(1)} Graft Size`;
        const text = isArchived
            ? 'Are you sure you want to unarchive this graft size? It will be restored to active.'
            : 'Are you sure you want to archive this graft size? It will no longer appear in active or inactive lists.';
        const result = await Swal.fire({
            title: actionTitle,
            text: text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: isArchived ? "#16a34a" : "#ea580c",
            cancelButtonColor: "#6b7280",
            confirmButtonText: `Yes, ${action} it!`
        })
        if (result.isConfirmed) {
            const endpoint = isArchived
                ? `/management/archive/${graft.graft_size_id}/unarchivegraftsize`
                : `/management/archive/${graft.graft_size_id}/archivegraftsize`
            await api.put(endpoint)
            await Swal.fire({
                title: `${action.charAt(0).toUpperCase() + action.slice(1)}ed!`,
                text: `Graft Size has been ${action}ed.`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            })
            toast.success(`Graft ${action}ed successfully!`)
            await getAllGraftRequests()
            await fetchGraftStats()
        }
    } catch (error) {
        toast.error(`Failed to ${graft.graft_status === 2 ? 'unarchive' : 'archive'} Graft.`)
    }
}
async function handleSubmitForm() {
    try {
        if (showCreateForm.value) {
            const validSizes = formData.value.graftSizes.filter(gs => gs.size.trim() !== '')
            if (validSizes.length === 0) {
                toast.error('At least one valid size is required.')
                return
            }
            if (!formData.value.brand_id) {
                toast.error('Brand is required.')
                return
            }
            const payload = {
                brand_id: formData.value.brand_id,
                graftSizes: validSizes.map(gs => ({
                    size: gs.size,
                    area: gs.area ?? 0,
                    price: gs.price ?? 0,
                    stock: gs.stock ?? 0
                }))
            }
            const { data } = await api.post(
                '/management/graft-sizes',
                payload
            )
            toast.success(data.message || 'Graft Size added successfully!')
            await getAllGraftRequests()
            await fetchGraftStats()
        } else if (showEditForm.value) {
            const graftSize = formData.value.graftSizes[0]
            if (!graftSize.size.trim()) {
                toast.error('Size is required.')
                return
            }
            const payload = {
                size: graftSize.size,
                area: graftSize.area ?? 0,
                price: graftSize.price ?? 0,
                stock: graftSize.stock ?? 0,
            }
            const { data } = await api.put(
                `/management/update/${selectedGraftRequest.value?.graft_size_id}/updategraftsize`,
                payload
            )
            toast.success(data.message || 'Graft Size Updated Successfully!')
            await getAllGraftRequests()
            await fetchGraftStats()
        }
        closeForm()
    } catch (err: unknown) {
        if (axios.isAxiosError(err)) {
            const status = err.response?.status
            const data = err.response?.data
            if (status === 422 && data?.errors) {
                const messages = Object.values(data.errors).flat()
                toast.error("Error: " + messages.join("\n"))
            } else {
                toast.error(data?.message || `Request failed with status code ${status}`)
            }
        } else if (err instanceof Error) {
            toast.error("Error: " + err.message)
        } else {
            toast.error("Something went wrong")
        }
    }
}
function closeForm() {
    showCreateForm.value = false
    showEditForm.value = false
    selectedGraftRequest.value = null
    clearForm()
}
function clearForm(){
    formData.value = {
        brand_id: '',
        graftSizes: [{ size: '', area: null, price: null, stock: 0, id: undefined }]
    }
}
const filteredGraftRequest = computed(() => {
    return graftRequest.value.filter(graft => {
        const brandName = graft.brand?.brand_name || ''
        const manufacturerName = graft.manufacturer?.manufacturer_name || ''
        const matchesSearch = brandName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            manufacturerName.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            graft.size.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            (graft.area?.toString().toLowerCase().includes(searchTerm.value.toLowerCase()) ?? false) ||
            (graft.price?.toString().toLowerCase().includes(searchTerm.value.toLowerCase()) ?? false) ||
            graft.stock.toString().toLowerCase().includes(searchTerm.value.toLowerCase())
        const matchesStatus = statusFilter.value === 'all' ||
            (statusFilter.value === 'active' && graft.graft_status === 0) ||
            (statusFilter.value === 'inactive' && graft.graft_status === 1) ||
            (statusFilter.value === 'archived' && graft.graft_status === 2)
        return matchesSearch && matchesStatus
    })
})
const totalPages = computed(() => {
    return Math.max(1, Math.ceil(totalResults.value / itemsPerPage.value))
})
const paginatedGrafts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredGraftRequest.value.slice(start, end)
})
const paginationNumbers = computed(() => {
    const pages = []
    const siblingCount = 1
    const total = totalPages.value
    const current = currentPage.value
    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i)
        }
        return pages
    }
    pages.push(1)
    if (current > siblingCount + 2) {
        pages.push('...')
    }
    const startPage = Math.max(2, current - siblingCount)
    const endPage = Math.min(total - 1, current + siblingCount)
    for (let i = startPage; i <= endPage; i++) {
        pages.push(i)
    }
    if (current < total - siblingCount - 1) {
        pages.push('...')
    }
    pages.push(total)
    return pages
})
function goToPage(page: number) {
    currentPage.value = page
}
function previousPage() {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}
function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}
const getStatusColor = (status: number) => {
    return graftStatus[status]?.classes || 'bg-gray-50 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400'
}
const getStatusIcon = (status: number) => {
    switch (status) {
        case 0: return CheckCircle2
        case 1: return XCircle
        case 2: return Archive
        default: return XCircle
    }
}
const formatDate = (dateStr: string | null) => {
    return dateStr && dateStr !== 'N/A' 
        ? new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        : 'N/A'
}
const showFormModal = computed({
    get: () => showCreateForm.value || showEditForm.value,
    set: (value: boolean) => {
        if (!value) {
            closeForm()
        }
    }
})
const showDetailsModal = computed({
    get: () => selectedGraftRequest.value !== null && !showEditForm.value,
    set: (value: boolean) => {
        if (!value) {
            selectedGraftRequest.value = null
            showEditForm.value = false // Prevent overlap
        }
    }
})
async function fetchGraftStats() {
    try {
        const { data } = await api.get('/management/graft-sizes/stats', {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })
        serverStats.value = {
            total: Number(data.total ?? 0),
            active: Number(data.active ?? 0),
            inactive: Number(data.inactive ?? 0),
            archived: Number(data.archived ?? 0),
            brands: (data.brands || []).map((b: any) => ({
                id: b.brand_id,
                name: b.brand_name,
                count: Number(b.count)
            }))
        }
    } catch (err) {
        console.error('Failed to fetch graft stats', err)
    }
}
async function getAllBrands(){
    tableLoader.value = true
    try {
        const { data } = await api.get(`/management/graft-sizes/getAllBrands`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })
        brandData.value = (data.brand_data || data.brands || data.data || []).map((brand: any) => ({
        brand_id: brand.brand_id,
        brand_name: brand.brand_name,
        manufacturer: brand.manufacturer ? {
            manufacturer_id: brand.manufacturer.manufacturer_id,
            manufacturer_name: brand.manufacturer.manufacturer_name
        } : undefined
        }))
        if (brandData.value.length === 0) {
            toast.warning('No brands available. Create some in Brand Management.')
        }
    } catch (error) {
        console.error('Error fetching brands:', error)
        toast.error('Failed to load brands. Check console.')
        brandData.value = []
    } finally {
        tableLoader.value = false
    }
}
async function getAllGraftRequests(page = 1) {
    tableLoader.value = true
    try {
        let statusParam: number | undefined;
        if (statusFilter.value === 'active') {
            statusParam = 0;
        } else if (statusFilter.value === 'inactive') {
            statusParam = 1;
        } else if (statusFilter.value === 'archived') {
            statusParam = 2;
        }
        const params = {
            page,
            per_page: itemsPerPage.value,
            search: searchTerm.value || undefined,
            status: statusParam
        }
        const { data } = await api.get(`/management/graft-sizes`, { params, headers: {
            Authorization: `Bearer ${localStorage.getItem('auth_token')}`
        }})
        graftRequest.value = data.graftData || []
        totalResults.value = Number(data.meta?.total || 0)
        currentPage.value = Number(data.meta?.current_page || 1)
    } catch (error) {
        console.error('Error fetching graft requests:', error)
        toast.error('Failed to load graft sizes.')
        graftRequest.value = []
        totalResults.value = 0
    } finally {
        tableLoader.value = false
    }
}
onMounted(async () => {
    await Promise.all([
        getAllBrands(),
        getAllGraftRequests(1),
        fetchGraftStats()
    ])
})
watch([searchTerm, statusFilter, itemsPerPage], () => {
    currentPage.value = 1
    getAllGraftRequests(1)
})
watch(currentPage, () => {
    getAllGraftRequests(currentPage.value)
})
</script>
<style scoped>
@keyframes ping-slow {
    0% { transform: scale(1); opacity: 0.3; }
    70% { transform: scale(1.3); opacity: 0; }
    100% { transform: scale(1.3); opacity: 0; }
}
.animate-ping-slow {
    animation: ping-slow 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes fade-in {
    from { opacity: 0; transform: translateY(0.5rem); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>