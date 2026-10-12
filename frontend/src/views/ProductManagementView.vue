<template>
    <div class="space-y-4 sm:space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-3">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Product Management</h1>
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 w-full sm:w-auto">
                <button @click="showStats = !showStats"
                    class="flex items-center justify-center w-full sm:w-auto px-4 sm:px-5 py-2.5 sm:py-3 text-sm sm:text-base bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200 whitespace-nowrap">
                    <BarChart2 class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2" />
                    {{ showStats ? 'Hide' : 'Show' }} Stats
                </button>
                <button @click="openAddProductModal"
                    class="flex items-center justify-center w-full sm:w-auto px-4 sm:px-5 py-2.5 sm:py-3 text-sm sm:text-base bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group whitespace-nowrap">
                    <ListPlus class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2 group-hover:scale-110 transition-transform" />
                    New Product
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'grafts'" :class="[
                    activeTab === 'grafts'
                        ? 'border-green-500 text-green-600 dark:text-green-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]">
                    Graft Sizes
                    <span
                        class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200/70 dark:bg-gray-700/70 text-gray-700 dark:text-gray-200">
                        {{ stats.total || 0 }}
                    </span>
                </button>
                <button @click="activeTab = 'other'" :class="[
                    activeTab === 'other'
                        ? 'border-green-500 text-green-600 dark:text-green-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]">
                    Other Products
                    <span
                        class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200/70 dark:bg-gray-700/70 text-gray-700 dark:text-gray-200">
                        {{ otherStats.total || 0 }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- Stats Panel -->
        <TransitionGroup enter-active-class="transition ease-out duration-400" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-300"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ProductStatsPanel v-if="showStats" :key="activeTab" :active-tab="activeTab" :stats="stats"
                :other-stats="otherStats" />
        </TransitionGroup>

        <!-- TABLES -->
        <!-- Graft Sizes view section -->
        <div v-if="activeTab === 'grafts'" class="space-y-6">
            <!-- Filters Card for grafts -->
            <div
                class="bg-white dark:bg-gray-800 p-4 lg:p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <!-- Mobile: Stacked Layout -->
                <div class="lg:hidden space-y-3">
                    <!-- Search and Filters Toggle Row -->
                    <div class="flex flex-col sm:flex-row gap-2">
                        <!-- Search Input -->
                        <div class="relative flex-1">
                            <Search
                                class="absolute left-3 sm:left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 sm:h-5 sm:w-5 text-gray-400 dark:text-gray-500" />
                            <input v-model="searchTerm" type="text" placeholder="Search by brand or size..."
                                class="w-full pl-9 sm:pl-10 pr-10 py-2.5 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900 dark:text-white text-sm transition-all duration-200" />
                            <button v-if="searchTerm" @click="searchTerm = ''"
                                class="absolute right-3 top-2.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Filters Toggle Button -->
                        <button @click="showFilters = !showFilters"
                            class="flex-shrink-0 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium h-[2.7rem] min-h-[2.5rem]">
                            <SlidersHorizontal class="w-4 h-4" />
                            Filters
                            <span v-if="statusFilter !== 'all'" class="w-2 h-2 bg-green-500 rounded-full"></span>
                        </button>
                    </div>

                    <!-- Filter Pills Row -->
                    <div class="flex flex-wrap gap-2">
                        <span v-if="statusFilter !== 'all'"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg text-xs">
                            {{ statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1) }}
                            <button @click="statusFilter = 'all'" class="hover:text-green-900 ml-1">
                                <X class="w-3 h-3" />
                            </button>
                        </span>
                        <span v-if="itemsPerPage !== '10'"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs">
                            {{ itemsPerPage }} per page
                            <button @click="itemsPerPage = '10'" class="hover:text-blue-900 ml-1">
                                <X class="w-3 h-3" />
                            </button>
                        </span>
                    </div>

                    <!-- Expandable Filters -->
                    <div v-if="showFilters" class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                            <div class="flex gap-2">
                                <button @click="statusFilter = 'all'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        statusFilter === 'all' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    All
                                </button>
                                <button @click="statusFilter = 'active'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        statusFilter === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    Active
                                </button>
                                <button @click="statusFilter = 'inactive'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        statusFilter === 'inactive' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    Inactive
                                </button>
                                <button @click="statusFilter = 'archived'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        statusFilter === 'archived' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    Archived
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Items per
                                page</label>
                            <div class="flex gap-2">
                                <button @click="itemsPerPage = '10'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        itemsPerPage === '10' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    10
                                </button>
                                <button @click="itemsPerPage = '25'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        itemsPerPage === '25' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    25
                                </button>
                                <button @click="itemsPerPage = '50'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        itemsPerPage === '50' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    50
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Original Layout -->
                <div class="hidden lg:flex flex-col lg:flex-row gap-6">
                    <div class="flex-1">
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                            <input v-model="searchTerm" type="text" placeholder="Search by brand or size..."
                                class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative">
                            <Funnel
                                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <select v-model="statusFilter"
                                class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                            <ChevronDown
                                class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                        </div>
                        <div class="relative">
                            <select v-model="itemsPerPage"
                                class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                            <ChevronDown
                                class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden space-y-4">
                <div v-if="tableLoader"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-8 h-8 rounded-full border-4 border-gray-200 border-t-green-600 animate-spin">
                        </div>
                        <div class="text-center text-gray-400 py-4 text-sm">Fetching Graft Sizes</div>
                    </div>
                </div>
                <template v-else>
                    <div v-if="filteredGraftRequest.length === 0"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <PencilRuler class="w-10 h-10 mb-1 text-gray-400" />
                            <span class="text-gray-400">No graft sizes found.</span>
                        </div>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="graft in paginatedGrafts" :key="graft.graft_size_id"
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <!-- Card Header -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-0.5">Graft Size</p>
                                        <p class="text-gray-900 dark:text-white font-semibold truncate">{{ graft.size }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ graft.area ? graft.area + 'cm²' : 'No area data' }}
                                        </p>
                                    </div>
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-white/60 dark:bg-white/10',
                                        getStatusClasses(graft.graft_status)]">
                                        <component :is="getStatusIcon(graft.graft_status)" class="w-3 h-3 mr-1" />
                                        {{ getStatusLabel(graft.graft_status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 space-y-3">
                                <!-- Manufacturer & Brand Row -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Manufacturer</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ graft.manufacturer?.manufacturer_name || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Brand</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ graft.brand?.brand_name || 'N/A' }}</p>
                                    </div>
                                </div>

                                <!-- Stock & Price Row -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Stock</p>
                                        <p class="text-sm font-medium"
                                            :class="isLowStock(graft.stock) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">
                                            {{ graft.stock || 0 }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Created</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            formatDate(graft.created_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div
                                class="px-4 pt-2 pb-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-center">
                                <ActionGroup :actions="getGraftActions(graft)" />
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Mobile Pagination -->
                <Pagination v-if="filteredGraftRequest.length > 0" :pagination="graftPagination"
                    @update:page="handleGraftPageChange" />
            </div>

            <!-- Graft Sizes Table Card (Desktop) -->
            <div
                class="hidden lg:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
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
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ graft.manufacturer?.manufacturer_name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ graft.brand?.brand_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ graft.size }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ graft.area ? graft.area + 'cm²' : 'No data found' }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ graft.stock || 0 }} in stock
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200',
                                        getStatusClasses(graft.graft_status)
                                    ]">
                                        <component :is="getStatusIcon(graft.graft_status)" class="w-3 h-3 mr-1.5" />
                                        {{ getStatusLabel(graft.graft_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(graft.created_at) }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                    <ActionGroup :actions="getGraftActions(graft)" size="sm" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <BaseEmptyState v-if="filteredGraftRequest.length === 0 && !tableLoader" :icon="PencilRuler"
                    :searching="isFilteringGrafts" empty-title="No graft sizes yet"
                    empty-description="Get started by adding a new graft size." variant="dark" :bordered="false" />

                <!-- Pagination -->
                <Pagination v-if="filteredGraftRequest.length > 0 && !tableLoader" :pagination="graftPagination"
                    @update:page="handleGraftPageChange" />
            </div>
        </div>

        <!-- Other Products view section -->
        <div v-if="activeTab === 'other'" class="space-y-6">
            <!-- Filters -->
            <div
                class="bg-white dark:bg-gray-800 p-4 lg:p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <!-- Mobile: Stacked Layout -->
                <div class="lg:hidden space-y-3">
                    <!-- Search and Filters Toggle Row -->
                    <div class="flex flex-col sm:flex-row gap-2">
                        <!-- Search Input -->
                        <div class="relative flex-1">
                            <Search
                                class="absolute left-3 sm:left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 sm:h-5 sm:w-5 text-gray-400 dark:text-gray-500" />
                            <input v-model="searchTerm" type="text" placeholder="Search by name or description..."
                                class="w-full pl-9 sm:pl-10 pr-10 py-2.5 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-900 dark:text-white text-sm transition-all duration-200" />
                            <button v-if="searchTerm" @click="searchTerm = ''"
                                class="absolute right-3 top-2.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Filters Toggle Button -->
                        <button @click="showFilters = !showFilters"
                            class="flex-shrink-0 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium h-10 min-h-[2.5rem]">
                            <SlidersHorizontal class="w-4 h-4" />
                            Filters
                            <span v-if="statusFilter !== 'all'" class="w-2 h-2 bg-green-500 rounded-full"></span>
                        </button>
                    </div>

                    <!-- Filter Pills Row -->
                    <div class="flex flex-wrap gap-2">
                        <span v-if="statusFilter !== 'all'"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg text-xs">
                            {{ statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1) }}
                            <button @click="statusFilter = 'all'" class="hover:text-green-900 ml-1">
                                <X class="w-3 h-3" />
                            </button>
                        </span>
                        <span v-if="itemsPerPage !== '10'"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs">
                            {{ itemsPerPage }}/page
                            <button @click="itemsPerPage = '10'" class="hover:text-blue-900 ml-1">
                                <X class="w-3 h-3" />
                            </button>
                        </span>
                    </div>

                    <!-- Expandable Filters -->
                    <div v-if="showFilters" class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                            <div class="flex gap-2">
                                <button @click="statusFilter = 'all'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        statusFilter === 'all' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    All
                                </button>
                                <button @click="statusFilter = 'active'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        statusFilter === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    Active
                                </button>
                                <button @click="statusFilter = 'inactive'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        statusFilter === 'inactive' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    Inactive
                                </button>
                                <button @click="statusFilter = 'archived'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        statusFilter === 'archived' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    Archived
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Items per
                                page</label>
                            <div class="flex gap-2">
                                <button @click="itemsPerPage = '10'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        itemsPerPage === '10' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    10
                                </button>
                                <button @click="itemsPerPage = '25'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        itemsPerPage === '25' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    25
                                </button>
                                <button @click="itemsPerPage = '50'"
                                    :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        itemsPerPage === '50' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                    50
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Original Layout -->
                <div class="hidden lg:flex flex-col lg:flex-row gap-6">
                    <div class="flex-1">
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                            <input v-model="searchTerm" type="text" placeholder="Search by name or description..."
                                class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative">
                            <Funnel
                                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <select v-model="statusFilter"
                                class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                            <ChevronDown
                                class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                        </div>
                        <div class="relative">
                            <select v-model="itemsPerPage"
                                class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-green-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                            <ChevronDown
                                class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden space-y-4">
                <div v-if="otherTableLoader"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-8 h-8 rounded-full border-4 border-gray-200 border-t-green-600 animate-spin">
                        </div>
                        <div class="text-center text-gray-400 py-4 text-sm">Fetching Products</div>
                    </div>
                </div>
                <template v-else>
                    <div v-if="otherProducts.length === 0"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <Package class="w-10 h-10 mb-1 text-gray-400" />
                            <span class="text-gray-400">No products found.</span>
                        </div>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="product in paginatedOtherProducts" :key="product.other_product_id"
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <!-- Card Header -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-0.5">Product Name</p>
                                        <p class="text-gray-900 dark:text-white font-semibold truncate">
                                            {{ product.product_name }}</p>
                                    </div>
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-white/60 dark:bg-white/10',
                                        getStatusClasses(product.status)]">
                                        <component :is="getStatusIcon(product.status)" class="w-3 h-3 mr-1" />
                                        {{ getStatusLabel(product.status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 space-y-3">
                                <!-- Type & Price Row -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Type</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ product.product_type === 0 ? 'Wound Supplies' : product.product_type ===
                                                1 ? 'Devices' : 'Unknown' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Price</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ formatCurrency(product.price) }}</p>
                                    </div>
                                </div>

                                <!-- Stock & Created Row -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Stock</p>
                                        <p class="text-sm font-medium"
                                            :class="isLowStock(product.stock) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">
                                            {{ product.stock || 0 }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Created</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            formatDate(product.created_at) }}</p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div v-if="product.description">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Description</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2">
                                        {{ product.description }}</p>
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div
                                class="px-4 pt-2 pb-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700">
                                <ActionGroup :actions="getOtherProductActions(product)" />
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Mobile Pagination -->
                <Pagination v-if="otherProducts.length > 0" :pagination="otherPagination"
                    @update:page="handleOtherPageChange" />
            </div>

            <!-- Other Products Table Card (Desktop) -->
            <div
                class="hidden lg:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Product Name
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Price
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
                            <TableLoader v-if="otherTableLoader" :colspan="7" />
                            <tr v-else v-for="product in paginatedOtherProducts" :key="product.other_product_id"
                                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                                            <Package class="w-5 h-5" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ product.product_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{
                                        product.product_type === 0
                                            ? 'Wound Supplies'
                                            : product.product_type === 1
                                                ? 'Devices'
                                                : 'Unknown'
                                    }}
                                </td>
                                <td
                                    class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrency(product.price) }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ product.stock || 0 }} in stock
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span :class="[
                                        'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium',
                                        getStatusClasses(product.status)
                                    ]">
                                        <component :is="getStatusIcon(product.status)" class="w-3 h-3 mr-1.5" />
                                        {{ getStatusLabel(product.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(product.created_at) }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                    <ActionGroup :actions="getOtherProductActions(product)" size="sm" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <BaseEmptyState v-if="otherProducts.length === 0 && !otherTableLoader" :icon="Package"
                    :searching="isFilteringOther" empty-title="No other products yet"
                    empty-description="Get started by adding a new product." variant="dark" :bordered="false" />

                <!-- Pagination -->
                <Pagination v-if="otherProducts.length > 0 && !otherTableLoader" :pagination="otherPagination"
                    @update:page="handleOtherPageChange" />
            </div>
        </div>

        <!-- Extracted Modal Components -->
        <GraftDetailsModal v-model="showDetailsModal" :graft="selectedGraftRequest" />

        <OtherProductDetailsModal v-model="showOtherDetailsModal" :product="selectedOtherProduct" />

        <ProductSelectionModal v-model="showAddProductModal" @select-graft="selectGraftProduct"
            @select-other="selectOtherProduct" @cancel="showAddProductModal = false" />

        <GraftFormModal v-model="showFormModal" :is-edit="showEditForm" :form-data="formData" :brands="brandData"
            @submit="handleGraftFormSubmit" @back="goBackToProductSelection" />

        <OtherProductFormModal v-model="showOtherProductModal" :is-edit="isEditingOtherProduct"
            :form-data="otherProductForm" @submit="handleOtherProductFormSubmit" @back="goBackToProductSelection" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick, onUnmounted } from 'vue'
import axios from 'axios'
import TableLoader from '@/components/ui/TableLoader.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { Search, Eye, SquarePen, Trash2, Package, Archive, ArchiveRestore, PencilRuler, ListPlus, BarChart2, CheckCircle2, Funnel, ChevronDown, XCircle, SlidersHorizontal, X } from 'lucide-vue-next'
import BaseEmptyState from '@/components/common/BaseEmptyState.vue'
import api from '@/services/api'
import { formatCurrency, formatNumber } from '@/utils/currency'
import { useProductStatus } from '@/composables/products/useProductStatus'
import { useAuthStore } from '@/stores/auth'
import { confirmAction } from '@/services/alert'
import { useAppToast } from '@/composables/ui/useAppToast'

// Import extracted modal components
import GraftDetailsModal from '@/components/products/modals/GraftDetailsModal.vue'
import OtherProductDetailsModal from '@/components/products/modals/OtherProductDetailsModal.vue'
import ProductSelectionModal from '@/components/products/modals/ProductSelectionModal.vue'
import GraftFormModal from '@/components/products/modals/GraftFormModal.vue'
import OtherProductFormModal from '@/components/products/modals/OtherProductFormModal.vue'
import type { GraftFormData } from '@/components/products/modals/GraftFormModal.vue'
import type { OtherProductFormData } from '@/components/products/modals/OtherProductFormModal.vue'
import ActionGroup, { type ActionConfig } from '@/components/ui/ActionGroup.vue'

// Import extracted table and stats components
import ProductStatsPanel from '@/components/products/ProductStatsPanel.vue'
import GraftSizesTable from '@/components/products/GraftSizesTable.vue'
import OtherProductsTable from '@/components/products/OtherProductsTable.vue'

const activeTab = ref<'grafts' | 'other'>('grafts')
const breakdownIcon = computed(() => {
    return activeTab.value === 'grafts' ? PencilRuler : Package
})

interface GraftSize {
    id?: string
    item_no: string
    size: string
    area: number | null
    price: number | null
    stock: number
}

interface GraftRequest {
    graft_size_id: string
    brand_id: string
    item_no: string
    size: string
    area?: number | null
    price?: number | null
    stock: number
    graft_status: number
    created_at: string
    updated_at: string
    notes?: string
    description?: string
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
    manufacturer?: { manufacturer_id: string; manufacturer_name: string }
}

const graftRequest = ref<GraftRequest[]>([])
const brandData = ref<Brand[]>([])
const itemsPerPage = ref<string>('10')
const showFilters = ref(false)
const currentPage = ref(1)
const totalResults = ref(0)
const tableLoader = ref(false)
const searchTerm = ref('')
const statusFilter = ref('all')
const selectedGraftRequest = ref<GraftRequest | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showStats = ref(false)
const showOtherProductModal = ref(false)
const isEditingOtherProduct = ref(false)

const otherProductForm = ref({
    product_id: undefined as string | undefined,
    product_type: null as number | null,
    product_name: '',
    price: 0,
    stock: 0,
    description: ''
})

// Track focused price fields for formatting
const focusedPriceField = ref<string | null>(null)

const authStore = useAuthStore()
const isStaff = computed(() => authStore.user?.user_role === 1)
const toast = useAppToast()

const {
    getStatusClasses,
    getStatusIcon,
    getStatusLabel,
} = useProductStatus()

const formData = ref({
    brand_id: '',
    graftSizes: [{
        id: undefined as string | undefined,
        item_no: '',
        size: '',
        area: null as number | null,
        price: null as number | null,
        stock: 0
    }] as GraftSize[]
})

const serverStats = ref({
    total: 0,
    active: 0,
    inactive: 0,
    archived: 0,
    brands: [] as { id: string; name: string; count: number }[]
})

const otherStats = ref({
    total: 0,
    active: 0,
    inactive: 0,
    archived: 0,
    types: [] as { type: number; label: string; count: number }[]
})

const otherProducts = ref<any[]>([])
const otherTotalResults = ref(0)
const otherCurrentPage = ref(1)
const otherTableLoader = ref(false)

const stats = computed(() => serverStats.value)
const statsPollingInterval = ref<number | null>(null)
const isLowStock = (stock: number) => stock < 10

const showAddProductModal = ref(false)
const showOtherDetailsModal = ref(false)
const selectedOtherProduct = ref<any>(null)

// ────────────────────────────────────────────────
// Shared Back function for both forms
// ────────────────────────────────────────────────
function goBackToProductSelection() {
    showFormModal.value = false
    showOtherProductModal.value = false
    nextTick(() => {
        showAddProductModal.value = true
    })
}

// ────────────────────────────────────────────────
// Modal / Selection Handlers
// ────────────────────────────────────────────────
function openAddProductModal() {
    showAddProductModal.value = true
}

function viewOtherProduct(product: any) {
    console.log(product)
    selectedOtherProduct.value = product
    showOtherDetailsModal.value = true
}

function editOtherProduct(product: any) {
    otherProductForm.value = {
        product_id: product.other_product_id,
        product_type: product.product_type,
        product_name: product.product_name || '',
        price: Number(product.price) || 0,
        stock: Number(product.stock) || 0,
        description: product.description || ''
    }
    isEditingOtherProduct.value = true
    showOtherProductModal.value = true
}

function selectGraftProduct() {
    showAddProductModal.value = false
    clearForm()
    selectedGraftRequest.value = null
    showCreateForm.value = true
}

function addGraftSize() {
    formData.value.graftSizes.push({
        id: undefined,
        item_no: '',
        size: '',
        area: null,
        price: null,
        stock: 0
    })
}

function removeGraftSize(index: number) {
    formData.value.graftSizes.splice(index, 1)
    if (formData.value.graftSizes.length === 0) {
        addGraftSize()
    }
}

function selectOtherProduct() {
    showAddProductModal.value = false
    isEditingOtherProduct.value = false
    clearOtherProductForm()
    showOtherProductModal.value = true
}

function clearOtherProductForm() {
    otherProductForm.value = {
        product_id: undefined,
        product_type: null,
        product_name: '',
        price: 0,
        stock: 0,
        description: ''
    }
    isEditingOtherProduct.value = false
}

function closeOtherProductForm() {
    showOtherProductModal.value = false
    clearOtherProductForm()
}

const productValidationLabels: Record<string, string> = {
    brand_id: 'Brand',
    product_type: 'Product type',
    product_name: 'Product name',
    price: 'Price',
    stock: 'Stock',
    description: 'Description',
    graftSizes: 'Graft sizes',
    item_no: 'Item number',
    size: 'Size',
    area: 'Area',
    graftStatus: 'Status',
}

function getProductValidationLabel(field: string) {
    const graftSizeMatch = field.match(/^graftSizes\.(\d+)\.(.+)$/)
    if (graftSizeMatch) {
        const rowNumber = Number(graftSizeMatch[1]) + 1
        const fieldLabel = productValidationLabels[graftSizeMatch[2]] || graftSizeMatch[2]
        return `Graft size ${rowNumber} ${fieldLabel}`
    }

    return productValidationLabels[field] || field
        .replace(/([A-Z])/g, ' $1')
        .replace(/[_-]/g, ' ')
        .replace(/^./, character => character.toUpperCase())
}

function getProductValidationMessage(field: string, message: string) {
    const normalizedMessage = message.toLowerCase()

    if (normalizedMessage.includes('required')) return 'This field is required.'
    if (field === 'brand_id' && (normalizedMessage.includes('exists') || normalizedMessage.includes('invalid'))) {
        return 'Select a valid brand.'
    }
    if (field === 'product_type' && normalizedMessage.includes('invalid')) return 'Select a valid product type.'
    if (normalizedMessage.includes('integer')) return 'Enter a whole number.'
    if (normalizedMessage.includes('numeric')) return 'Enter a valid number.'
    if (normalizedMessage.includes('min') || normalizedMessage.includes('at least')) {
        return 'Enter a value of 0 or more.'
    }
    if (field === 'graftSizes' && normalizedMessage.includes('json')) return 'Enter valid graft size details.'

    const escapedField = field.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    const fieldPattern = new RegExp(`^The ${escapedField}( field)? `, 'i')
    return message.replace(fieldPattern, '').replace(/^./, character => character.toUpperCase())
}

function showProductValidationPrompt(messages: string[]) {
    const validationMessage = [
        'Correct the following before saving:',
        ...messages,
        '',
        'Please correct these items and try again.',
    ].join('\n')
    toast.error(validationMessage, 'Please review the form')
}

function showProductRequestError(error: unknown) {
    if (axios.isAxiosError(error)) {
        const status = error.response?.status
        const data = error.response?.data

        if (status === 422 && data?.errors) {
            const messages = Object.entries(data.errors).flatMap(([field, errors]) => {
                const fieldMessages = Array.isArray(errors) ? errors : [errors]
                return fieldMessages.map(message => {
                    const label = getProductValidationLabel(field)
                    const friendlyMessage = getProductValidationMessage(field, String(message))
                    return `- ${label}: ${friendlyMessage}`
                })
            })
            showProductValidationPrompt(messages)
            return
        }

        toast.error(data?.message || `Request failed with status code ${status}`)
        return
    }

    if (error instanceof Error) {
        toast.error(`Error: ${error.message}`)
        return
    }
    toast.error('Something went wrong')
}

function getGraftLabel(graft: { size?: string; item_no?: string | null }) {
    const descriptor = [graft.size, graft.item_no].filter(Boolean).join(' / ')
    return descriptor ? `Graft Size "${descriptor}"` : 'The graft size'
}

function getProductLabel(product: any) {
    return product?.product_name ? `Product "${product.product_name}"` : 'The product'
}

function escapeHtml(value: string) {
    return value.replace(/[&<>'"]/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;',
    }[character] || character))
}

async function handleOtherProductSubmit() {
    if (
        otherProductForm.value.product_type == null ||
        !otherProductForm.value.product_name?.trim() ||
        otherProductForm.value.price <= 0 ||
        otherProductForm.value.stock < 0
    ) {
        showProductValidationPrompt([
            '- Product details: Complete all required fields and enter valid price and stock values.',
        ])
        return
    }

    try {
        const payload = {
            product_type: otherProductForm.value.product_type,
            product_name: otherProductForm.value.product_name.trim(),
            price: Number(otherProductForm.value.price),
            stock: Number(otherProductForm.value.stock),
            description: otherProductForm.value.description?.trim() || null,
        }

        let response
        if (isEditingOtherProduct.value) {
            if (!otherProductForm.value.product_id) {
                throw new Error('Missing product ID for update')
            }
            response = await api.put(
                `/management/other-products/${otherProductForm.value.product_id}/updateotherproduct`,
                payload
            )
            toast.success(`${getProductLabel(otherProductForm.value)} details were updated successfully.`)
        } else {
            response = await api.post('/management/other-products', payload)
            toast.success(`${getProductLabel(otherProductForm.value)} was created successfully.`)
        }

        if (activeTab.value === 'other') {
            fetchOtherProducts(otherCurrentPage.value)
        }
        fetchOtherStats()
        closeOtherProductForm()
    } catch (err: any) {
        console.error(err)
        showProductRequestError(err)
    }
}

async function fetchOtherProducts(page = 1) {
    if (activeTab.value !== 'other') return
    otherTableLoader.value = true
    try {
        let statusParam: number | undefined
        if (statusFilter.value === 'active') statusParam = 0
        else if (statusFilter.value === 'inactive') statusParam = 1
        else if (statusFilter.value === 'archived') statusParam = 2

        const params = {
            page,
            per_page: itemsPerPage.value,
            search: searchTerm.value || undefined,
            status: statusParam
        }
        const { data } = await api.get('/management/other-products', { params })
        otherProducts.value = data.otherProductData || []
        otherTotalResults.value = Number(data.meta?.total || 0)
        otherCurrentPage.value = Number(data.meta?.current_page || 1)
    } catch (err) {
        console.error(err)
        toast.error('Failed to load other products')
        otherProducts.value = []
        otherTotalResults.value = 0
    } finally {
        otherTableLoader.value = false
    }
}

async function fetchOtherStats() {
    try {
        const { data } = await api.get('/management/other-products/stats')
        otherStats.value = {
            total: Number(data.total ?? 0),
            active: Number(data.active ?? 0),
            inactive: Number(data.inactive ?? 0),
            archived: Number(data.archived ?? 0),
            types: data.types || []
        }
    } catch (err) {
        console.warn('Failed to load other products stats', err)
        otherStats.value = { total: 0, active: 0, inactive: 0, archived: 0, types: [] }
    }
}

async function editGraft(graft: GraftRequest) {
    selectedGraftRequest.value = graft
    showCreateForm.value = false
    showEditForm.value = true
    await nextTick()
    const brandId = graft.brand_id || (graft.brand?.brand_id || '')
    formData.value = {
        brand_id: brandId,
        graftSizes: [{
            id: graft.graft_size_id,
            item_no: graft.item_no || '',
            size: graft.size,
            area: graft.area ?? null,
            price: graft.price ?? null,
            stock: graft.stock
        }]
    }
    if (!brandData.value.some(b => b.brand_id === brandId)) {
        toast.warning('Brand not found in list. Please refresh or check Brand Management.')
        formData.value.brand_id = ''
    }
}

async function confirmDelete(graft: GraftRequest) {
    try {
        const result = await confirmAction({
            title: 'Delete Graft Size?',
            html: `
                Are you sure you want to delete <b>${escapeHtml(getGraftLabel(graft))}</b>?<br>
                <span style="color:#d33;">This action cannot be undone!</span>
            `,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        })
        if (result.isConfirmed) {
            await api.put(`/management/delete/${graft.graft_size_id}/deletegraftsize`)
            toast.success(`${getGraftLabel(graft)} was permanently deleted.`)
            await getAllGraftRequests()
            await fetchGraftStats()
        }
    } catch (error) {
        console.error(error)
        showProductRequestError(error)
    }
}

async function confirmToggleStatus(graft: GraftRequest) {
    const isActive = graft.graft_status === 0
    const action = isActive ? 'deactivate' : 'activate'
    try {
        const result = await confirmAction({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} Graft Size?`,
            text: `Are you sure you want to ${action} this graft size?`,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel'
        })
        if (result.isConfirmed) {
            const endpoint = isActive
                ? `/management/deactivate/${graft.graft_size_id}/deactivategraftsize`
                : `/management/activate/${graft.graft_size_id}/activategraftsize`
            await api.put(endpoint)
            toast.success(`${getGraftLabel(graft)} was ${action}d successfully.`)
            await getAllGraftRequests()
            await fetchGraftStats()
        }
    } catch (error) {
        console.error(error)
        showProductRequestError(error)
    }
}

async function confirmArchive(graft: GraftRequest) {
    const isArchived = graft.graft_status === 2
    const action = isArchived ? 'unarchive' : 'archive'
    try {
        const result = await confirmAction({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} Graft Size?`,
            text: isArchived
                ? 'This action will restore the graft size from archived status.'
                : 'This action will move the graft size to archived status. You can restore it later if needed.',
            confirmButtonColor: isArchived ? '#3085d6' : '#d33',
            cancelButtonColor: isArchived ? '#6b7280' : '#3085d6',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel'
        })
        if (result.isConfirmed) {
            const endpoint = isArchived
                ? `/management/archive/${graft.graft_size_id}/unarchivegraftsize`
                : `/management/archive/${graft.graft_size_id}/archivegraftsize`
            await api.put(endpoint)
            if (isArchived) {
                toast.success(`${getGraftLabel(graft)} was successfully restored from archived status.`)
            } else {
                toast.success(`${getGraftLabel(graft)} was archived successfully and can be restored later.`)
            }
            await getAllGraftRequests()
            await fetchGraftStats()
        }
    } catch (error) {
        console.error(error)
        showProductRequestError(error)
    }
}

async function confirmToggleOtherStatus(product: any) {
    const isActive = product.status === 0
    const action = isActive ? 'deactivate' : 'activate'
    try {
        const result = await confirmAction({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} Product?`,
            text: `Are you sure you want to ${action} this product?`,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel'
        })
        if (result.isConfirmed) {
            const endpoint = isActive
                ? `/management/other-products/${product.other_product_id}/deactivate`
                : `/management/other-products/${product.other_product_id}/activate`
            await api.put(endpoint)
            toast.success(`${getProductLabel(product)} was ${action}d successfully.`)
            await fetchOtherProducts(otherCurrentPage.value)
            await fetchOtherStats()
        }
    } catch (error) {
        console.error(error)
        showProductRequestError(error)
    }
}

async function confirmArchiveOther(product: any) {
    const isArchived = product.status === 2
    const action = isArchived ? 'unarchive' : 'archive'
    try {
        const result = await confirmAction({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} Product?`,
            text: isArchived
                ? 'This action will restore the product from archived status.'
                : 'This action will move the product to archived status. You can restore it later if needed.',
            confirmButtonColor: isArchived ? '#3085d6' : '#d33',
            cancelButtonColor: isArchived ? '#6b7280' : '#3085d6',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel'
        })
        if (result.isConfirmed) {
            const endpoint = isArchived
                ? `/management/other-products/${product.other_product_id}/unarchive`
                : `/management/other-products/${product.other_product_id}/archive`
            await api.put(endpoint)
            if (isArchived) {
                toast.success(`${getProductLabel(product)} was successfully restored from archived status.`)
            } else {
                toast.success(`${getProductLabel(product)} was archived successfully and can be restored later.`)
            }
            await fetchOtherProducts(otherCurrentPage.value)
            await fetchOtherStats()
        }
    } catch (error) {
        console.error(error)
        showProductRequestError(error)
    }
}

async function confirmDeleteOther(product: any) {
    try {
        const result = await confirmAction({
            title: 'Delete Product?',
            html: `
                Are you sure you want to delete <b>${escapeHtml(getProductLabel(product))}</b>?<br>
                <span style="color:#d33;">This action cannot be undone!</span>
            `,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        })
        if (result.isConfirmed) {
            await api.delete(`/management/other-products/${product.other_product_id}`)
            toast.success(`${getProductLabel(product)} was permanently deleted.`)
            await fetchOtherProducts(otherCurrentPage.value)
            await fetchOtherStats()
        }
    } catch (error) {
        console.error(error)
        showProductRequestError(error)
    }
}

// Handler for GraftFormModal submit event
async function handleGraftFormSubmit(data: GraftFormData) {
    try {
        if (showCreateForm.value) {
            const validSizes = data.graftSizes.filter(gs => gs.size.trim() !== '')
            if (validSizes.length === 0) {
                showProductValidationPrompt(['- Size: Add at least one valid graft size.'])
                return
            }
            if (!data.brand_id) {
                showProductValidationPrompt(['- Brand: This field is required.'])
                return
            }
            const payload = {
                brand_id: data.brand_id,
                graftSizes: validSizes.map(gs => ({
                    item_no: gs.item_no.trim(),
                    size: gs.size,
                    area: gs.area ?? 0,
                    price: gs.price ?? 0,
                    stock: gs.stock ?? 0
                }))
            }
            const response = await api.post('/management/graft-sizes', payload)
            toast.success(`${getGraftLabel(validSizes[0])} was created successfully.`)
            await getAllGraftRequests()
            await fetchGraftStats()
        } else if (showEditForm.value) {
            const graftSize = data.graftSizes[0]
            if (!graftSize.size.trim()) {
                showProductValidationPrompt(['- Size: This field is required.'])
                return
            }
            const originalBrandId = selectedGraftRequest.value?.brand_id || ''
            const payload = {
                brand_id: data.brand_id || originalBrandId,
                item_no: graftSize.item_no.trim(),
                size: graftSize.size,
                area: graftSize.area ?? 0,
                price: graftSize.price ?? 0,
                stock: graftSize.stock ?? 0,
            }
            const response = await api.put(
                `/management/update/${selectedGraftRequest.value?.graft_size_id}/updategraftsize`,
                payload
            )
            toast.success(`${getGraftLabel({ ...graftSize, size: graftSize.size })} details were updated successfully.`)
            await getAllGraftRequests()
            await fetchGraftStats()
        }
        closeForm()
    } catch (err: unknown) {
        if (axios.isAxiosError(err)) {
            const status = err.response?.status
            const errorData = err.response?.data
            if (status === 422 && errorData?.errors) {
                const messages = Object.entries(errorData.errors).flatMap(([field, errors]) => {
                    const fieldMessages = Array.isArray(errors) ? errors : [errors]
                    return fieldMessages.map(message => {
                        const label = getProductValidationLabel(field)
                        const friendlyMessage = getProductValidationMessage(field, String(message))
                        return `- ${label}: ${friendlyMessage}`
                    })
                })
                showProductValidationPrompt(messages)
            } else {
                showProductRequestError(err)
            }
        } else if (err instanceof Error) {
            showProductRequestError(err)
        } else {
            showProductRequestError(err)
        }
    }
}

// Handler for OtherProductFormModal submit event
async function handleOtherProductFormSubmit(data: OtherProductFormData) {
    if (
        data.product_type == null ||
        !data.product_name?.trim() ||
        data.price <= 0 ||
        data.stock < 0
    ) {
        showProductValidationPrompt([
            '- Product details: Complete all required fields and enter valid price and stock values.',
        ])
        return
    }

    try {
        const payload = {
            product_type: data.product_type,
            product_name: data.product_name.trim(),
            price: Number(data.price),
            stock: Number(data.stock),
            description: data.description?.trim() || null,
        }

        let response
        if (isEditingOtherProduct.value) {
            if (!data.product_id) {
                throw new Error('Missing product ID for update')
            }
            response = await api.put(
                `/management/other-products/${data.product_id}/updateotherproduct`,
                payload
            )
            toast.success(`${getProductLabel(data)} details were updated successfully.`)
        } else {
            response = await api.post('/management/other-products', payload)
            toast.success(`${getProductLabel(data)} was created successfully.`)
        }

        if (activeTab.value === 'other') {
            fetchOtherProducts(otherCurrentPage.value)
        }
        fetchOtherStats()
        closeOtherProductForm()
    } catch (err: any) {
        console.error(err)
        showProductRequestError(err)
    }
}

// ────────────────────────────────────────────────
// Action Group Functions
// ────────────────────────────────────────────────
function getGraftActions(graft: GraftRequest): ActionConfig[] {
    const isActive = graft.graft_status === 0
    const isArchived = graft.graft_status === 2
    const canDelete = isArchived && !isStaff.value

    const actions: ActionConfig[] = [
        {
            id: 'view',
            icon: Eye,
            title: 'View',
            onClick: () => { selectedGraftRequest.value = graft },
            hoverColor: 'green'
        },
        {
            id: 'edit',
            icon: SquarePen,
            title: 'Edit',
            onClick: () => editGraft(graft),
            hoverColor: 'blue'
        },
        {
            id: 'toggle-status',
            icon: isActive ? XCircle : CheckCircle2,
            title: isActive ? 'Deactivate' : 'Activate',
            onClick: () => confirmToggleStatus(graft),
            hoverColor: isActive ? 'red' : 'green',
            variant: isActive ? 'danger' : 'success',
            visible: !isArchived
        },
        {
            id: 'archive',
            icon: isArchived ? ArchiveRestore : Archive,
            title: isArchived ? 'Restore' : 'Archive',
            onClick: () => confirmArchive(graft),
            visible: true,
            hoverColor: isArchived ? 'green' : 'orange'
        }
    ]

    if (canDelete) {
        actions.push({
            id: 'delete',
            icon: Trash2,
            title: 'Delete',
            onClick: () => confirmDelete(graft),
            hoverColor: 'red'
        })
    }

    return actions
}

function getOtherProductActions(product: any): ActionConfig[] {
    const isActive = product.status === 0
    const isArchived = product.status === 2
    const canDelete = isArchived && !isStaff.value

    const actions: ActionConfig[] = [
        {
            id: 'view',
            icon: Eye,
            title: 'View',
            onClick: () => viewOtherProduct(product),
            hoverColor: 'green'
        },
        {
            id: 'edit',
            icon: SquarePen,
            title: 'Edit',
            onClick: () => editOtherProduct(product),
            hoverColor: 'blue'
        },
        {
            id: 'toggle-status',
            icon: isActive ? XCircle : CheckCircle2,
            title: isActive ? 'Deactivate' : 'Activate',
            onClick: () => confirmToggleOtherStatus(product),
            hoverColor: isActive ? 'red' : 'green',
            variant: isActive ? 'danger' : 'success',
            visible: !isArchived
        },
        {
            id: 'archive',
            icon: isArchived ? ArchiveRestore : Archive,
            title: isArchived ? 'Restore' : 'Archive',
            onClick: () => confirmArchiveOther(product),
            visible: true,
            hoverColor: isArchived ? 'green' : 'orange'
        }
    ]

    if (canDelete) {
        actions.push({
            id: 'delete',
            icon: Trash2,
            title: 'Delete',
            onClick: () => confirmDeleteOther(product),
            hoverColor: 'red'
        })
    }

    return actions
}

function clearForm() {
    formData.value = {
        brand_id: '',
        graftSizes: [{ size: '', item_no: '', area: null, price: null, stock: 0, id: undefined }]
    }
}

function closeForm() {
    showCreateForm.value = false
    showEditForm.value = false
    selectedGraftRequest.value = null
    clearForm()
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

const isFilteringGrafts = computed(() => searchTerm.value !== '' || statusFilter.value !== 'all')
const isFilteringOther = computed(() => searchTerm.value !== '' || statusFilter.value !== 'all')

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(totalResults.value / Number(itemsPerPage.value)))
})

const paginatedGrafts = computed(() => {
    return filteredGraftRequest.value
})

const paginatedOtherProducts = computed(() => {
    return otherProducts.value
})

// ────────────────────────────────────────────────
// Pagination handlers for shared Pagination component
// ────────────────────────────────────────────────
const graftPagination = computed(() => ({
    current_page: currentPage.value,
    last_page: totalPages.value,
    per_page: Number(itemsPerPage.value),
    total: totalResults.value
}))

const otherPagination = computed(() => ({
    current_page: otherCurrentPage.value,
    last_page: Math.max(1, Math.ceil(otherTotalResults.value / Number(itemsPerPage.value))),
    per_page: Number(itemsPerPage.value),
    total: otherTotalResults.value
}))

function handleGraftPageChange(page: number) {
    currentPage.value = page
    getAllGraftRequests(page)
}

function handleOtherPageChange(page: number) {
    otherCurrentPage.value = page
    fetchOtherProducts(page)
}

// Format price to 2 decimal places (rounds to number)
const formatPrice = (value: number | null | undefined): number => {
    if (value === null || value === undefined || isNaN(value)) return 0
    return Math.round(value * 100) / 100
}

// Parse formatted price input (removes commas) back to numeric value
const parsePriceInput = (value: string): number => {
    if (!value || value.trim() === '') return 0
    const cleaned = value.replace(/,/g, '').trim()
    const parsed = parseFloat(cleaned)
    return isNaN(parsed) ? 0 : parsed
}

// Format price for display in text input
const formatPriceForDisplay = (value: number | null | undefined): string => {
    if (value === null || value === undefined || isNaN(value)) return ''
    return (Math.round(value * 100) / 100).toFixed(2)
}

const formatDate = (dateStr: string) => {
    const date = new Date(dateStr)
    const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
    return `${formattedDate} [${formattedTime}]`
}

const showFormModal = computed({
    get: () => showCreateForm.value || showEditForm.value,
    set: (value: boolean) => {
        if (!value) closeForm()
    }
})

const showDetailsModal = computed({
    get: () => selectedGraftRequest.value !== null && !showEditForm.value,
    set: (value: boolean) => {
        if (!value) {
            selectedGraftRequest.value = null
            showEditForm.value = false
        }
    }
})

async function fetchGraftStats() {
    try {
        const { data } = await api.get('/management/graft-sizes/stats', {
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
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

function startStatsPolling() {
    if (statsPollingInterval.value) return
    statsPollingInterval.value = setInterval(fetchGraftStats, 30000)
}

function stopStatsPolling() {
    if (statsPollingInterval.value) {
        clearInterval(statsPollingInterval.value)
        statsPollingInterval.value = null
    }
}

async function getAllBrands() {
    tableLoader.value = true
    try {
        const { data } = await api.get(`/management/graft-sizes/getAllBrands`, {
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
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
        let statusParam: number | undefined
        if (statusFilter.value === 'active') statusParam = 0
        else if (statusFilter.value === 'inactive') statusParam = 1
        else if (statusFilter.value === 'archived') statusParam = 2

        const params = {
            page,
            per_page: itemsPerPage.value,
            search: searchTerm.value || undefined,
            status: statusParam
        }
        const { data } = await api.get(`/management/graft-sizes`, {
            params,
            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
        })
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

// ────────────────────────────────────────────────
// Lifecycle & Watchers
// ────────────────────────────────────────────────
onMounted(async () => {
    await Promise.all([
        getAllBrands(),
        getAllGraftRequests(1),
        fetchGraftStats(),
        fetchOtherStats()
    ])
})

watch([searchTerm, statusFilter, itemsPerPage], () => {
    if (activeTab.value === 'grafts') {
        currentPage.value = 1
        getAllGraftRequests(1)
    }
})

watch(currentPage, () => {
    if (activeTab.value === 'grafts') {
        getAllGraftRequests(currentPage.value)
    }
})

watch([activeTab, searchTerm, itemsPerPage, statusFilter], ([newTab]) => {
    if (newTab === 'other') {
        otherCurrentPage.value = 1
        fetchOtherProducts(1)
    } else if (newTab === 'grafts') {
        currentPage.value = 1
        getAllGraftRequests(1)
    }
})

watch(otherCurrentPage, (newPage) => {
    if (activeTab.value === 'other') {
        fetchOtherProducts(newPage)
    }
})

watch(showStats, (newVal) => {
    if (newVal) {
        startStatsPolling()
    } else {
        stopStatsPolling()
    }
})

onUnmounted(() => {
    stopStatsPolling()
})
</script>


<style scoped>
@keyframes ping-slow {
    0% {
        transform: scale(1);
        opacity: 0.3;
    }

    70% {
        transform: scale(1.3);
        opacity: 0;
    }

    100% {
        transform: scale(1.3);
        opacity: 0;
    }
}

.animate-ping-slow {
    animation: ping-slow 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(0.5rem);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

/* Fallback: Hide scrollbar by default, show thin on hover */
.breakdown-scroll {
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.breakdown-scroll::-webkit-scrollbar {
    display: none;
}

.breakdown-scroll:hover {
    scrollbar-width: thin;
    scrollbar-color: rgba(107, 114, 128, 0.5) transparent;
}

.dark .breakdown-scroll:hover {
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.breakdown-scroll:hover::-webkit-scrollbar {
    height: 6px;
}

.breakdown-scroll:hover::-webkit-scrollbar-thumb {
    background: rgba(107, 114, 128, 0.5);
    border-radius: 3px;
}

.dark .breakdown-scroll:hover::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
}
</style>
