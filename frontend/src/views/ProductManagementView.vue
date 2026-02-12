<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Product Management</h1>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl">Manage products, their stock levels, and
                    availability
                    in one streamlined interface</p>
            </div>
            <div class="flex items-center gap-4">
                <button @click="showStats = !showStats"
                    class="flex items-center px-5 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">
                    <BarChart2 class="w-5 h-5 mr-2" />
                    {{ showStats ? 'Hide' : 'Show' }} Stats
                </button>

                <button @click="openAddProductModal"
                    class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
                    <ListPlus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
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
                        class="ml-2  px-2.5 py-0.5  rounded-full  text-xs font-medium bg-gray-200/70 dark:bg-gray-700/70 text-gray-700 dark:text-gray-200">
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
                        class="ml-2  px-2.5 py-0.5  rounded-full  text-xs font-medium bg-gray-200/70 dark:bg-gray-700/70 text-gray-700 dark:text-gray-200">
                        {{ otherStats.total || 0 }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- Stats Panel – Modern & Polished Version with Hidden Hover Scrollbar -->
        <TransitionGroup enter-active-class="transition ease-out duration-400" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-300"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showStats"
                class="bg-gradient-to-b from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 p-6 md:p-8 rounded-2xl shadow-lg border border-gray-200/60 dark:border-gray-700/60 backdrop-blur-sm">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                        {{ activeTab === 'grafts' ? 'Graft Size Overview' : 'Other Products Overview' }}
                    </h3>
                </div>

                <!-- Main Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
                    <!-- Total -->
                    <div
                        class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent dark:from-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                        <div class="flex items-center gap-4">
                            <div
                                class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <component :is="activeTab === 'grafts' ? PencilRuler : Package"
                                    class="w-7 h-7 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p
                                    class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                                    {{ activeTab === 'grafts' ? stats.total : otherStats.total }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                                    Total {{ activeTab === 'grafts' ? 'Sizes' : 'Products' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Active -->
                    <div
                        class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent dark:from-green-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                        <div class="flex items-center gap-4">
                            <div
                                class="w-14 h-14 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <CheckCircle2 class="w-7 h-7 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p
                                    class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                                    {{ activeTab === 'grafts' ? stats.active : otherStats.active }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Active</p>
                            </div>
                        </div>
                    </div>

                    <!-- Inactive -->
                    <div
                        class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-transparent dark:from-red-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                        <div class="flex items-center gap-4">
                            <div
                                class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <XCircle class="w-7 h-7 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p
                                    class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                                    {{ activeTab === 'grafts' ? stats.inactive : otherStats.inactive }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Inactive</p>
                            </div>
                        </div>
                    </div>

                    <!-- Archived -->
                    <div
                        class="group relative bg-white dark:bg-gray-800 rounded-xl p-5 shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent dark:from-orange-600/10 opacity-0 group-hover:opacity-100 transition-opacity" />
                        <div class="flex items-center gap-4">
                            <div
                                class="w-14 h-14 rounded-2xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <Archive class="w-7 h-7 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <p
                                    class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                                    {{ activeTab === 'grafts' ? stats.archived : otherStats.archived }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Archived</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Breakdown Section – Horizontal Scrollable Carousel with Hover-Only Thin Scrollbar -->
                <div class="mt-8">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <component :is="breakdownIcon" class="w-5 h-5 text-purple-500" />
                        {{ activeTab === 'grafts' ? 'Brand Breakdown' : 'Breakdown by Type' }}
                    </h4>

                    <div v-if="(activeTab === 'grafts' && stats.brands.length > 0) || (activeTab === 'other' && otherStats.types.length > 0)"
                        class="relative">
                        <!-- Scroll container – scrollbar hidden by default, thin & visible only on hover -->
                        <div class="overflow-x-auto pb-4 breakdown-scroll snap-x snap-mandatory">
                            <div class="inline-flex gap-4 min-w-max px-1">
                                <!-- Graft Brands -->
                                <template v-if="activeTab === 'grafts'">
                                    <div v-for="brand in stats.brands.slice(0, 12)" :key="brand.id"
                                        class="snap-start flex-shrink-0 w-64 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-300 overflow-hidden group">
                                        <div class="p-4 flex flex-col h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                                    <Package class="w-4 h-4 mr-2.5 text-purple-500 shrink-0" />
                                                    {{ brand.name }}
                                                </span>
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{
                                                    brand.count }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ Math.round((brand.count / stats.total) * 100) }}% of total
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Other Product Types -->
                                <template v-if="activeTab === 'other'">
                                    <div v-for="t in otherStats.types.slice(0, 12)" :key="t.type"
                                        class="snap-start flex-shrink-0 w-64 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 overflow-hidden group">
                                        <div class="p-4 flex flex-col h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                                    <Package class="w-4 h-4 mr-2.5 text-blue-500 shrink-0" />
                                                    {{ t.label }}
                                                </span>
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ t.count
                                                    }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ Math.round((t.count / otherStats.total) * 100) }}% of total
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Count indicator -->
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 text-center">
                            Showing {{ activeTab === 'grafts' ? Math.min(stats.brands.length, 12) :
                                Math.min(otherStats.types.length, 12) }} of
                            {{ activeTab === 'grafts' ? stats.brands.length : otherStats.types.length }}
                            {{ activeTab === 'grafts' ? 'brands' : 'types' }}
                            <span
                                v-if="(activeTab === 'grafts' && stats.brands.length > 12) || (activeTab === 'other' && otherStats.types.length > 12)"
                                class="ml-1">(scroll to see more)</span>
                        </p>
                    </div>

                    <!-- Empty state -->
                    <div v-else
                        class="text-center py-10 text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                        No breakdown data available yet.
                    </div>
                </div>
            </div>
        </TransitionGroup>

        <!-- TABLES -->
        <!-- Graft Sizes view section -->
        <div v-if="activeTab === 'grafts'" class="space-y-6">
            <!-- Filters Card for grafts -->
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
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
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                                graft.manufacturer?.manufacturer_name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{
                                                graft.brand?.brand_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ graft.size }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ graft.area ? graft.area +
                                        'cm²'
                                        : 'No data found' }}</div>
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
                                        <button v-if="graft.graft_status !== 2" @click="confirmToggleStatus(graft)"
                                            :class="[
                                                'p-2 rounded-lg transition-all duration-200',
                                                graft.graft_status === 0
                                                    ? 'text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20'
                                                    : 'text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'
                                            ]" :title="graft.graft_status === 0 ? 'Deactivate' : 'Activate'">
                                            <component :is="graft.graft_status === 0 ? XCircle : CheckCircle2"
                                                class="w-4 h-4" />
                                        </button>
                                        <button @click="confirmArchive(graft)" :class="[
                                            'p-2 rounded-lg transition-all duration-200',
                                            graft.graft_status === 2
                                                ? 'text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'
                                                : 'text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20'
                                        ]" :title="graft.graft_status === 2 ? 'Unarchive Graft' : 'Archive Graft'">
                                            <component :is="graft.graft_status === 2 ? ArchiveRestore : Archive"
                                                class="w-4 h-4" />
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
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Try adjusting your search or filter to
                        find
                        what you're looking for.</p>
                </div>
                <!-- Pagination -->
                <div v-if="filteredGraftRequest.length > 0 && !tableLoader"
                    class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-semibold text-gray-800 dark:text-white">{{ (currentPage - 1) *
                            itemsPerPage + 1 }}</span> to <span class="font-semibold text-gray-800 dark:text-white">{{
                                Math.min(currentPage * itemsPerPage, totalResults) }}</span> of <span
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
        </div>

        <!-- Other Products view section -->
        <div v-if="activeTab === 'other'" class="space-y-6">

            <!-- Filters (you can keep the same or customize later) -->
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1">
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                            <input v-model="searchTerm" type="text" placeholder="Search by name or description..."
                                class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- You can add product type filter later -->
                        <div class="relative">
                            <select v-model="itemsPerPage"
                                class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
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

            <!-- Other Products Table Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
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
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                                product.product_name }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ product.product_type === 0 ? 'Wound Supplies' : product.product_type === 1
                                        ? 'Devices'
                                        : 'Unknown' }}
                                </td>

                                <td
                                    class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    ${{ product.price?.toFixed(2) || '0.00' }}
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
                                    <div class="flex items-center space-x-2">
                                        <button @click="viewOtherProduct(product)"
                                            class="p-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg"
                                            title="View">
                                            <Eye class="w-4 h-4" />
                                        </button>
                                        <button @click="editOtherProduct(product)"
                                            class="p-2 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg"
                                            title="Edit">
                                            <SquarePen class="w-4 h-4" />
                                        </button>
                                        <button v-if="product.status !== 2" @click="confirmToggleOtherStatus(product)"
                                            :class="[
                                                'p-2 rounded-lg transition-all duration-200',
                                                product.status === 0
                                                    ? 'text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20'
                                                    : 'text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'
                                            ]" :title="product.status === 0 ? 'Deactivate' : 'Activate'">
                                            <component :is="product.status === 0 ? XCircle : CheckCircle2"
                                                class="w-4 h-4" />
                                        </button>

                                        <button @click="confirmArchiveOther(product)" :class="[
                                            'p-2 rounded-lg transition-all duration-200',
                                            product.status === 2
                                                ? 'text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'
                                                : 'text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20'
                                        ]" :title="product.status === 2 ? 'Unarchive Product' : 'Archive Product'">
                                            <component :is="product.status === 2 ? ArchiveRestore : Archive"
                                                class="w-4 h-4" />
                                        </button>

                                        <!-- Delete (only when archived) -->
                                        <button v-if="product.status === 2" @click="confirmDeleteOther(product)"
                                            class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200"
                                            title="Delete Archived Product">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div v-if="otherProducts.length === 0 && !otherTableLoader" class="text-center py-12">
                    <div
                        class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <Package class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No other products found</h3>
                    <p class="text-gray-500 dark:text-gray-400">Try adjusting your search or add a new product.</p>
                </div>

                <!-- Pagination (almost identical to grafts) -->
                <div v-if="otherProducts.length > 0 && !otherTableLoader"
                    class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-semibold">{{ (otherCurrentPage - 1) * itemsPerPage + 1 }}</span> to
                        <span class="font-semibold">{{ Math.min(otherCurrentPage * itemsPerPage, otherTotalResults)
                        }}</span> of
                        <span class="font-semibold">{{ otherTotalResults }}</span> results
                    </p>
                    <nav class="flex items-center space-x-2">
                        <button @click="otherCurrentPage--" :disabled="otherCurrentPage === 1"
                            class="...">Previous</button>
                        <!-- You can reuse or copy the paginationNumbers logic, or simplify to prev/next only for now -->
                        <button @click="otherCurrentPage++"
                            :disabled="otherCurrentPage * itemsPerPage >= otherTotalResults" class="...">Next</button>
                    </nav>
                </div>
            </div>
        </div>

        <!-- VIEW DETAILS -->
        <!-- Graft Details Modal -->
        <BaseModal v-model="showDetailsModal" title="Graft Size Details" size="lg">
            <template v-if="selectedGraftRequest">
                <div class="p-6 space-y-8">
                    <!-- Header Section -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div
                                    class="w-20 h-20 rounded-xl bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 flex items-center justify-center border border-green-200 dark:border-green-700 shadow-sm">
                                    <PencilRuler class="w-10 h-10 text-green-600 dark:text-green-400" />
                                </div>
                            </div>

                            <!-- Title & Status -->
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                                    {{ selectedGraftRequest.size || 'Unnamed Size' }}
                                </h2>
                                <div class="mt-2">
                                    <span :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                        getStatusClasses(selectedGraftRequest.graft_status)
                                    ]">
                                        <span class="w-2 h-2 rounded-full bg-current mr-2"></span>
                                        {{ getStatusLabel(selectedGraftRequest.graft_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Info Badges – Added Item No here -->
                        <div class="flex flex-wrap gap-3">
                            <div
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <Factory class="w-5 h-5 text-indigo-500 dark:text-indigo-400 mr-2" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ selectedGraftRequest.manufacturer?.manufacturer_name || 'No Records Found' }}
                                </span>
                            </div>
                            <div
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <Package class="w-5 h-5 text-purple-500 dark:text-purple-400 mr-2" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ selectedGraftRequest.brand?.brand_name || 'No Records Found' }}
                                </span>
                            </div>
                            <!-- NEW: Item No badge -->
                            <div
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <Tag class="w-5 h-5 text-teal-500 dark:text-teal-400 mr-2" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ selectedGraftRequest.item_no || 'No Records Found' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Key Stats Grid (3 stats as before) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-gray-100 dark:bg-gray-700/70 rounded-xl p-5 text-center shadow-sm">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ selectedGraftRequest.stock || 0 }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">Current Stock</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-5 text-center">
                            <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                                ${{ selectedGraftRequest.price?.toFixed(2) || '0.00' }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Unit Price</div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 text-center">
                            <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ selectedGraftRequest.area ? selectedGraftRequest.area.toFixed(2) + ' cm²' : '—' }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Area</div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div v-if="isLowStock(selectedGraftRequest.stock)"
                        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 flex items-center gap-3 text-yellow-800 dark:text-yellow-300">
                        <AlertCircle class="w-6 h-6 flex-shrink-0" />
                        <span>Low stock alert: only {{ selectedGraftRequest.stock }} units remaining</span>
                    </div>

                    <!-- Notes / Description (optional) -->
                    <div v-if="selectedGraftRequest.notes || selectedGraftRequest.description">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Notes</h3>
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                                {{ selectedGraftRequest.notes || selectedGraftRequest.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Dates Footer -->
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Created At</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                {{ formatDate(selectedGraftRequest.created_at) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Last Updated</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                {{ formatDate(selectedGraftRequest.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #actions>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button @click="showDetailsModal = false"
                        class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        Close
                    </button>
                    <button @click="editGraft(selectedGraftRequest)"
                        class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 transition-colors shadow-sm font-medium">
                        Edit Graft Size
                    </button>
                </div>
            </template>
        </BaseModal>

        <!-- Other Product Details Modal -->
        <BaseModal v-model="showOtherDetailsModal" title="Product Details" size="lg">
            <template v-if="selectedOtherProduct">
                <div class="p-6 space-y-8">
                    <!-- Header Section -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div
                                    class="w-20 h-20 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-800/30 flex items-center justify-center border border-blue-200 dark:border-blue-700 shadow-sm">
                                    <Package class="w-10 h-10 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>

                            <!-- Title & Status -->
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                                    {{ selectedOtherProduct.product_name }}
                                </h2>
                                <div class="mt-2">
                                    <span :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                        getStatusClasses(selectedOtherProduct.status)
                                    ]">
                                        <span class="w-2 h-2 rounded-full bg-current mr-2"></span>
                                        {{ getStatusLabel(selectedOtherProduct.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Info Badges -->
                        <div class="flex flex-wrap gap-3">
                            <div
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{
                                        selectedOtherProduct.product_type === 0
                                            ? 'Wound Supplies'
                                            : selectedOtherProduct.product_type === 1
                                                ? 'Devices'
                                                : 'Unknown Type'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Key Stats Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Stock -->
                        <div class="bg-gray-100 dark:bg-gray-700/70 rounded-xl p-5 text-center shadow-sm">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ selectedOtherProduct.stock || 0 }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">Current Stock</div>
                        </div>

                        <!-- Price -->
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-5 text-center">
                            <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                                ${{ selectedOtherProduct.price?.toFixed(2) || '0.00' }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Unit Price</div>
                        </div>

                        <!-- Availability -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 text-center">
                            <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ selectedOtherProduct.stock > 0 ? 'In Stock' : 'Out of Stock' }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Availability</div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div v-if="isLowStock(selectedOtherProduct.stock)"
                        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 flex items-center gap-3 text-yellow-800 dark:text-yellow-300">
                        <AlertCircle class="w-6 h-6 flex-shrink-0" />
                        <span>Low stock warning: only {{ selectedOtherProduct.stock }} units left</span>
                    </div>

                    <!-- Description -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
                            <p v-if="selectedOtherProduct.description"
                                class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                                {{ selectedOtherProduct.description }}
                            </p>
                            <p v-else class="text-gray-500 dark:text-gray-400 italic">
                                No description provided.
                            </p>
                        </div>
                    </div>

                    <!-- Dates Footer -->
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Created At</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                {{ formatDate(selectedOtherProduct.created_at) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Last Updated</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                {{ formatDate(selectedOtherProduct.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #actions>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button @click="showOtherDetailsModal = false"
                        class="px-6 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        Close
                    </button>
                </div>
            </template>
        </BaseModal>

        <!-- CREATE AND EDIT -->
        <!-- Product Selection Modal -->
        <BaseModal v-model="showAddProductModal" title="Add New Product">
            <div class="space-y-4">
                <p class="text-gray-600 dark:text-gray-400">Choose the type of product you want to add:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <!-- Graft Size Product -->
                    <button @click="selectGraftProduct"
                        class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl hover:border-green-500 hover:bg-green-50 dark:hover:bg-gray-700 transition-all duration-200 group">
                        <PencilRuler
                            class="w-12 h-12 text-green-600 dark:text-green-400 mb-4 group-hover:scale-110 transition-transform" />
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Graft Size</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 mt-2 text-center max-w-xs">
                            Add graft products with multiple sizes, area (cm²), individual pricing, and stock per size
                        </span>
                    </button>

                    <!-- Other / Regular Product -->
                    <button @click="selectOtherProduct"
                        class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 group">
                        <Package
                            class="w-12 h-12 text-blue-600 dark:text-blue-400 mb-4 group-hover:scale-110 transition-transform" />
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Other Product</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 mt-2 text-center max-w-xs">
                            Add consumables, equipment, kits, or any non-graft item with simple name, price, and stock
                        </span>
                    </button>
                </div>
            </div>

            <template #actions>
                <div class="flex justify-end w-full p-5">
                    <button @click="showAddProductModal = false"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                        Cancel
                    </button>
                </div>
            </template>
        </BaseModal>

        <!-- Create/Edit Graft Form Modal -->
        <BaseModal v-model="showFormModal" :title="showCreateForm ? 'New Graft Size(s)' : 'Edit Graft Size'" size="2xl">
            <form @submit.prevent="handleSubmitForm" class="space-y-6">
                <!-- Brand Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Brand <span class="text-red-500 ml-1">*</span>
                    </label>
                    <select v-model="formData.brand_id" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                        <option disabled value="">Select a Brand</option>
                        <option v-for="brand in brandData" :key="brand.brand_id" :value="brand.brand_id">
                            {{ brand.manufacturer?.manufacturer_name || 'Unknown Manufacturer' }} - {{ brand.brand_name
                            }}
                        </option>
                    </select>
                </div>

                <!-- Graft Sizes Section -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <PencilRuler class="w-5 h-5 text-green-500" />
                        <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">
                            Graft Sizes <span class="text-red-500">*</span>
                        </h3>
                    </div>

                    <div class="space-y-6">
                        <div v-for="(graftSize, index) in formData.graftSizes" :key="graftSize.id || index"
                            class="relative p-5 sm:p-6 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                            <!-- Header: entry label + remove button -->
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Size Entry {{ index + 1 }}
                                </span>
                                <button v-if="formData.graftSizes.length > 1" type="button"
                                    @click="removeGraftSize(index)"
                                    class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Fields – responsive horizontal layout -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-5">
                                <!-- Item No -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                        Item No <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <Tag class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input v-model="graftSize.item_no" type="text" required placeholder="GS-001"
                                            class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                    </div>
                                </div>

                                <!-- Size -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                        Size <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <RulerDimensionLine
                                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input v-model="graftSize.size" type="text" required placeholder="2cm × 2cm"
                                            class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                    </div>
                                </div>

                                <!-- Area -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                        Area (cm²) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <Diameter
                                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input v-model.number="graftSize.area" type="number" required min="0"
                                            step="0.01" placeholder="4.00"
                                            class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                    </div>
                                </div>

                                <!-- Price -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                        Price <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <DollarSign
                                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input v-model.number="graftSize.price" type="number" required min="0"
                                            step="0.01" placeholder="0.00"
                                            class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                    </div>
                                </div>

                                <!-- Stock -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                        Stock <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <Package
                                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input v-model.number="graftSize.stock" type="number" required min="0"
                                            placeholder="0"
                                            class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Another Size Button (only in create mode) -->
                    <button v-if="showCreateForm" type="button" @click="addGraftSize"
                        class="mt-4 flex items-center justify-center w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors font-medium">
                        <Plus class="w-5 h-5 mr-2" />
                        Add Another Size
                    </button>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="closeForm"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-md">
                        {{ showCreateForm ? 'Create Graft Size(s)' : 'Update Graft Size' }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Create/Edit Other Product Form Modal -->
        <BaseModal v-model="showOtherProductModal"
            :title="isEditingOtherProduct ? 'Edit Other Product' : 'New Other Product'" size="xl">
            <form @submit.prevent="handleOtherProductSubmit" class="space-y-6">
                <!-- Product Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Product Type <span class="text-red-500">*</span>
                    </label>
                    <select v-model="otherProductForm.product_type" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 ...">
                        <option disabled value="">Select a Product Type</option>
                        <option :value="0">Wound Supplies</option>
                        <option :value="1">Devices</option>
                    </select>
                </div>

                <!-- Product Information -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <!-- Product Name -->
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <PencilLine class="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                            <input v-model="otherProductForm.name" type="text" required
                                placeholder="e.g., Sterile Gloves, Wound Dressing Kit"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                    </div>

                    <!-- Unit Price -->
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <DollarSign class="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                            <input v-model.number="otherProductForm.price" type="number" required min="0" step="0.01"
                                placeholder="0.00"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                    </div>

                    <!-- Stock Quantity -->
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Stock <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <Package class="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                            <input v-model.number="otherProductForm.stock" type="number" required min="0"
                                placeholder="0"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                    </div>
                </div>

                <!-- Description / Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description (optional)
                    </label>
                    <textarea v-model="otherProductForm.description" rows="4"
                        placeholder="Additional details about the product..."
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="closeOtherProductForm"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                        {{ isEditingOtherProduct ? 'Update Product' : 'Create Product' }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick, onUnmounted } from 'vue'
import axios from 'axios'
import BaseModal from '@/components/common/BaseModal.vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import {
    Search, Eye, SquarePen, Trash2, Package, Archive, ArchiveRestore, PencilRuler, ListPlus,
    RulerDimensionLine, Diameter, DollarSign, Plus, BarChart2, CheckCircle2, Filter,
    ChevronDown, Factory, XCircle, AlertCircle, PackageCheck, CalendarPlus, PencilLine, Tag
} from 'lucide-vue-next'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'
import { useProductStatus } from '@/composables/products/useProductStatus'

const activeTab = ref('grafts')
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
const showOtherProductModal = ref(false)
const isEditingOtherProduct = ref(false)
const otherProductForm = ref({
    product_id: '' as string | undefined,
    product_type: null as number | null,
    name: '',
    price: 0,
    stock: 0,
    description: ''
})

const {
    getStatusClasses,
    getStatusIcon,
    getStatusLabel,
} = useProductStatus()

const formData = ref({
    brand_id: '',
    graftSizes: [{
        id: undefined as string | undefined,
        item_no: '',          // ← NEW: Item No field
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
const isLowStock = (stock: number) => stock < 10 // Threshold: Customize as needed

// Add near other modal refs
const showAddProductModal = ref(false)
// Other Product Details Modal
const showOtherDetailsModal = ref(false)
const selectedOtherProduct = ref<any>(null)


// Replace the current "New Product" button click handler logic with this:
function openAddProductModal() {
    showAddProductModal.value = true
}

function viewOtherProduct(product: any) {
    console.log(product);

    selectedOtherProduct.value = product
    showOtherDetailsModal.value = true
}

function editOtherProduct(product: any) {
    otherProductForm.value = {
        product_id: product.other_product_id,
        product_type: product.product_type,
        name: product.product_name || '',
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
    // Existing graft form will open (multi-size support stays exactly as is)
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
        // formData.value.graftSizes.push({ size: '', area: null, price: null, stock: 0, id: undefined })
        addGraftSize()
    }
}

// Open Other Product form (called from selection modal)
function selectOtherProduct() {
    showAddProductModal.value = false
    isEditingOtherProduct.value = false
    clearOtherProductForm()
    showOtherProductModal.value = true
}

// Clear/reset form
function clearOtherProductForm() {
    otherProductForm.value = {
        product_id: undefined,
        product_type: null,
        name: '',
        price: 0,
        stock: 0,
        low_stock_threshold: 10,
        description: ''
    }
    isEditingOtherProduct.value = false
}

// Close modal
function closeOtherProductForm() {
    showOtherProductModal.value = false
    clearOtherProductForm()
}
async function handleOtherProductSubmit() {
    // Basic validation (client-side)
    if (
        !otherProductForm.value.product_type ||
        !otherProductForm.value.name?.trim() ||
        otherProductForm.value.price <= 0 ||
        otherProductForm.value.stock < 0
    ) {
        toast.error('Please fill in all required fields correctly.')
        return
    }

    try {
        const payload = {
            product_type: otherProductForm.value.product_type,
            product_name: otherProductForm.value.name.trim(),
            price: Number(otherProductForm.value.price),
            stock: Number(otherProductForm.value.stock),
            description: otherProductForm.value.description?.trim() || null,
        }

        let response

        if (isEditingOtherProduct.value) {
            // UPDATE
            if (!otherProductForm.value.product_id) {
                throw new Error('Missing product ID for update')
            }

            response = await api.put(
                `/management/other-products/${otherProductForm.value.product_id}/updateotherproduct`,
                payload
            )
            toast.success('Product updated successfully!')
        } else {
            // CREATE
            response = await api.post('/management/other-products', payload)
            toast.success('Product created successfully!')
        }

        // Refresh data
        if (activeTab.value === 'other') {
            fetchOtherProducts(otherCurrentPage.value)   // or page 1 if you prefer reset
        }
        // Optional: refresh stats too
        fetchOtherStats()

        // Close & reset form
        closeOtherProductForm()
    } catch (err: any) {
        console.error(err)
        const msg =
            err.response?.data?.message ||
            err.message ||
            (isEditingOtherProduct.value ? 'Failed to update product.' : 'Failed to create product.')
        toast.error(msg)
    }
}

async function handleOtherProductSubmit11() {
    // Basic client-side check (optional but recommended)
    if (!otherProductForm.value.product_type ||
        !otherProductForm.value.name?.trim() ||
        otherProductForm.value.price <= 0 ||
        otherProductForm.value.stock < 0) {
        toast.error('Please fill in all required fields correctly.')
        return
    }

    try {
        const payload = {
            product_type: otherProductForm.value.product_type,
            product_name: otherProductForm.value.name.trim(),
            price: Number(otherProductForm.value.price),
            stock: Number(otherProductForm.value.stock),
            description: otherProductForm.value.description?.trim() || null,
        }

        let response

        if (isEditingOtherProduct.value) {
            // For edit (you'll implement later)
            response = await api.put(
                `/management/other-products/${otherProductForm.value.product_id}`,
                payload
            )
            toast.success('Product updated successfully!')
        } else {
            // Create new
            response = await api.post('/management/other-products', payload)
            toast.success('Product created successfully!')
        }

        // Optional: refresh list / stats when you have the table
        await fetchOtherProducts()
        // await fetchProductStats()  // or whatever you name it

        closeOtherProductForm()

    } catch (err: any) {
        console.error(err)
        const msg = err.response?.data?.message
            || err.message
            || 'Failed to save product. Please try again.'
        toast.error(msg)
    }
}

async function fetchOtherProducts(page = 1) {
    if (activeTab.value !== 'other') return

    otherTableLoader.value = true
    try {
        const params = {
            page,
            per_page: itemsPerPage.value,
            search: searchTerm.value || undefined,
            // status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
            // → add later when OtherProduct has status field
        }

        const { data } = await api.get('/management/other-products', { params })

        otherProducts.value = data.otherProductData || []
        otherTotalResults.value = Number(data.meta?.total || 0)
        otherCurrentPage.value = Number(data.meta?.current_page || 1)

        console.log(otherProducts.value);

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
    const brandId = graft.brand_id || (graft.brand?.brand_id || '') // Fallback to nested
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
    // Optional: Validate brand exists in brandData
    if (!brandData.value.some(b => b.brand_id === brandId)) {
        toast.warning('Brand not found in list. Please refresh or check Brand Management.')
        // Fallback: Set to empty to prompt selection
        formData.value.brand_id = ''
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
            : 'Are you sure you want to archive this graft size?';
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

// Toggle status (activate / deactivate)
async function confirmToggleOtherStatus(product: any) {
    const isActive = product.status === 0
    const action = isActive ? 'deactivate' : 'activate'
    const actionTitle = `${action.charAt(0).toUpperCase() + action.slice(1)} Product`

    const text = isActive
        ? 'Are you sure you want to deactivate this product? It will no longer be available.'
        : 'Are you sure you want to activate this product? It will become available again.'

    const result = await Swal.fire({
        title: actionTitle,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isActive ? '#dc2626' : '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Yes, ${action} it!`,
    })

    if (result.isConfirmed) {
        try {
            const endpoint = isActive
                ? `/management/other-products/${product.other_product_id}/deactivate`
                : `/management/other-products/${product.other_product_id}/activate`

            await api.put(endpoint)
            toast.success(`Product ${action}d successfully!`)

            // Refresh table & stats
            await fetchOtherProducts(otherCurrentPage.value)
            await fetchOtherStats()
        } catch (err) {
            toast.error(`Failed to ${action} product.`)
        }
    }
}

// Archive / Unarchive
async function confirmArchiveOther(product: any) {
    const isArchived = product.status === 2
    const action = isArchived ? 'unarchive' : 'archive'
    const actionTitle = `${action.charAt(0).toUpperCase() + action.slice(1)} Product`

    const text = isArchived
        ? 'Are you sure you want to unarchive this product? It will be restored to active.'
        : 'Are you sure you want to archive this product?'

    const result = await Swal.fire({
        title: actionTitle,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isArchived ? '#16a34a' : '#ea580c',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Yes, ${action} it!`,
    })

    if (result.isConfirmed) {
        try {
            const endpoint = isArchived
                ? `/management/other-products/${product.other_product_id}/unarchive`
                : `/management/other-products/${product.other_product_id}/archive`

            await api.put(endpoint)
            toast.success(`Product ${action}d successfully!`)

            await fetchOtherProducts(otherCurrentPage.value)
            await fetchOtherStats()
        } catch (err) {
            toast.error(`Failed to ${action} product.`)
        }
    }
}

// Delete (only allowed when archived)
async function confirmDeleteOther(product: any) {
    const result = await Swal.fire({
        title: 'Delete Product',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
    })

    if (result.isConfirmed) {
        try {
            await api.delete(`/management/other-products/${product.other_product_id}`)
            toast.success('Product deleted successfully!')
            await fetchOtherProducts(otherCurrentPage.value)
            await fetchOtherStats()
        } catch (err) {
            toast.error('Failed to delete product.')
        }
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
                    item_no: gs.item_no.trim(),
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

            // Track original brand for comparison
            const originalBrandId = selectedGraftRequest.value?.brand_id || ''

            const payload = {
                brand_id: formData.value.brand_id || originalBrandId,
                item_no: graftSize.item_no.trim(),
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

function clearForm() {
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

const paginatedOtherProducts = computed(() => {
    // Because we fetch paginated from backend, we just return the current page data
    return otherProducts.value
    // If you ever switch to client-side pagination → slice like paginatedGrafts
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

function startStatsPolling() {
    if (statsPollingInterval.value) return; // Already running

    statsPollingInterval.value = setInterval(() => {
        fetchGraftStats();
    }, 30000); // Poll every 30 seconds
}

function stopStatsPolling() {
    if (statsPollingInterval.value) {
        clearInterval(statsPollingInterval.value);
        statsPollingInterval.value = null;
    }
}

watch(showStats, (show) => {
    if (show) {
        fetchGraftStats()           // refresh grafts when panel shown
        if (activeTab.value === 'other') {
            fetchOtherStats()
        }
    }
})

watch(activeTab, (tab) => {
    if (tab === 'other' && showStats.value && otherStats.value.total === 0) {
        fetchOtherStats()
    }
})

watch(showStats, (newVal) => {
    if (newVal) {
        // fetchGraftStats(); 
        startStatsPolling(); // Start polling
    } else {
        stopStatsPolling(); // Stop to save resources
    }
});

watch(activeTab, (newTab) => {
    currentPage.value = 1
    searchTerm.value = ''
})

onUnmounted(() => {
    stopStatsPolling();
});

async function getAllBrands() {
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
        const { data } = await api.get(`/management/graft-sizes`, {
            params, headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
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

onMounted(async () => {
    await Promise.all([
        getAllBrands(),
        getAllGraftRequests(1),
        fetchGraftStats(),
        fetchOtherStats()
    ])
})

watch([searchTerm, statusFilter, itemsPerPage], () => {
    currentPage.value = 1
    getAllGraftRequests(1)
})

watch(currentPage, () => {
    getAllGraftRequests(currentPage.value)
})

// Call when tab changes to 'other' or when filters change while on that tab
watch([activeTab, searchTerm, itemsPerPage, statusFilter], ([newTab]) => {
    if (newTab === 'other') {
        otherCurrentPage.value = 1
        fetchOtherProducts(1)
    }
})

// Also support pagination changes for other tab
watch(otherCurrentPage, (newPage) => {
    if (activeTab.value === 'other') {
        fetchOtherProducts(newPage)
    }
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
    /* Firefox */
    -ms-overflow-style: none;
    /* IE/Edge */
}

.breakdown-scroll::-webkit-scrollbar {
    display: none;
    /* Chrome/Safari/Opera */
}

.breakdown-scroll:hover {
    scrollbar-width: thin;
    scrollbar-color: rgba(107, 114, 128, 0.5) transparent;
    /* gray-500/50 */
}

.dark .breakdown-scroll:hover {
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
    /* gray-400/50 */
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