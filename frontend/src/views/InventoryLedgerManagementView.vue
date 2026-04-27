<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="space-y-2">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Inventory Ledger Management</h1>
      </div>
      <div class="flex items-center gap-4">
        <button @click="showStats = !showStats"
          class="flex items-center px-5 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">
          <BarChart3 class="w-5 h-5 mr-2" />
          {{ showStats ? 'Hide' : 'Show' }} Stats
        </button>
        <button @click="openCreateModal"
          class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
          <Plus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
          Add Entry
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
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ledger Statistics</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
              <Package class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Items</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
              <CheckCircle2 class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.paid }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Paid</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
              <AlertCircle class="w-5 h-5 text-orange-600 dark:text-orange-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.unpaid }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Unpaid</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
              <Activity class="w-5 h-5 text-purple-600 dark:text-purple-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.used }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Used Items</p>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Filters Card - Desktop -->
    <div class="hidden lg:block bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <div class="flex flex-col xl:flex-row gap-4">
        <!-- Search -->
        <div class="relative flex-1">
          <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
          <input v-model="searchTerm" type="text" placeholder="Search by serial number, brand, or clinic..."
            class="w-full pl-12 pr-10 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
          <button v-if="searchTerm" @click="searchTerm = ''" class="absolute right-3 top-3 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <X class="w-4 h-4" />
          </button>
        </div>
        <!-- Filter Dropdowns -->
        <div class="flex flex-wrap gap-2">
          <div class="relative w-44">
            <select v-model="statusFilter"
              class="w-full pl-4 pr-8 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 appearance-none transition-all duration-200">
              <option value="all">All Statuses</option>
              <option value="expected">Expected</option>
              <option value="delivered">Delivered</option>
              <option value="used">Used</option>
              <option value="partially_used">Partially Used</option>
              <option value="reassigned">Reassigned</option>
              <option value="unused">Unused</option>
              <option value="expired">Expired</option>
            </select>
            <svg class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <div class="relative w-44">
            <select v-model="brandFilter"
              class="w-full pl-4 pr-8 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 appearance-none transition-all duration-200">
              <option value="all">All Brands</option>
              <option v-for="brand in brands" :key="brand.id" :value="String(brand.id)">
                {{ brand.brandName }}
              </option>
            </select>
            <svg class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <div class="relative w-44">
            <select v-model="clinicFilter"
              class="w-full pl-4 pr-8 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 appearance-none transition-all duration-200">
              <option value="all">All Clinics</option>
              <option v-for="clinic in clinics" :key="clinic.id" :value="String(clinic.id)">
                {{ clinic.name }}
              </option>
            </select>
            <svg class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <div class="relative w-44">
            <select v-model="invoiceStatusFilter"
              class="w-full pl-4 pr-8 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 appearance-none transition-all duration-200">
              <option value="all">All Invoice Status</option>
              <option value="paid">Paid</option>
              <option value="unpaid">Unpaid</option>
            </select>
            <svg class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Card - Mobile -->
    <div class="lg:hidden bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <!-- Search Row -->
      <div class="relative mb-4">
        <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
        <input v-model="searchTerm" type="text" placeholder="Search by serial, brand, clinic..."
          class="w-full pl-12 pr-10 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
        <button v-if="searchTerm" @click="searchTerm = ''" class="absolute right-3 top-3 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Filter Pills Row -->
      <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-hide">
        <button @click="showFilters = !showFilters"
          class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">
          <SlidersHorizontal class="w-3.5 h-3.5" />
          Filters
          <span v-if="hasActiveFilters" class="w-2 h-2 bg-blue-500 rounded-full"></span>
        </button>
        <span v-if="statusFilter !== 'all'" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs">
          {{ statusFilter.replace('_', ' ') }}
          <button @click="statusFilter = 'all'" class="hover:text-blue-900">
            <X class="w-3 h-3" />
          </button>
        </span>
        <span v-if="brandFilter !== 'all'" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs max-w-[160px]">
          <span class="truncate">{{ getBrandName(brandFilter) }}</span>
          <button @click="brandFilter = 'all'" class="hover:text-blue-900 flex-shrink-0">
            <X class="w-3 h-3" />
          </button>
        </span>
        <span v-if="clinicFilter !== 'all'" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs max-w-[160px]">
          <span class="truncate">{{ getClinicName(clinicFilter) }}</span>
          <button @click="clinicFilter = 'all'" class="hover:text-blue-900 flex-shrink-0">
            <X class="w-3 h-3" />
          </button>
        </span>
        <span v-if="invoiceStatusFilter !== 'all'" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs">
          {{ invoiceStatusFilter }}
          <button @click="invoiceStatusFilter = 'all'" class="hover:text-blue-900">
            <X class="w-3 h-3" />
          </button>
        </span>
      </div>

      <!-- Expandable Filters -->
      <div v-if="showFilters" class="space-y-3 pt-3 mt-3 border-t border-gray-100 dark:border-gray-700">
        <div>
          <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
          <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <button v-for="(label, value) in statusOptions" :key="value"
              @click="statusFilter = statusFilter === String(value) ? 'all' : String(value)"
              :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', statusFilter === String(value) ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
              {{ label }}
            </button>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Brand</label>
          <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <button @click="brandFilter = 'all'"
              :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', brandFilter === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
              All
            </button>
            <button v-for="brand in brands" :key="brand.id"
              @click="brandFilter = String(brand.id)"
              :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors max-w-[140px] truncate', brandFilter === String(brand.id) ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
              {{ brand.brandName }}
            </button>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Clinic</label>
          <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <button @click="clinicFilter = 'all'"
              :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', clinicFilter === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
              All
            </button>
            <button v-for="clinic in clinics" :key="clinic.id"
              @click="clinicFilter = String(clinic.id)"
              :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors max-w-[140px] truncate', clinicFilter === String(clinic.id) ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
              {{ clinic.name }}
            </button>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Invoice</label>
          <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <button @click="invoiceStatusFilter = 'all'"
              :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', invoiceStatusFilter === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
              All
            </button>
            <button @click="invoiceStatusFilter = 'paid'"
              :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', invoiceStatusFilter === 'paid' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
              Paid
            </button>
            <button @click="invoiceStatusFilter = 'unpaid'"
              :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', invoiceStatusFilter === 'unpaid' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
              Unpaid
            </button>
          </div>
        </div>
        <div class="flex items-center justify-between pt-2">
          <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Items per page</label>
          <div class="relative">
            <select v-model="itemsPerPage" @change="currentPage = 1; fetchLedger()"
              class="pl-3 pr-8 py-2 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white appearance-none text-xs font-medium transition-all duration-200">
              <option :value="10">10 per page</option>
              <option :value="25">25 per page</option>
              <option :value="50">50 per page</option>
              <option :value="100">100 per page</option>
            </select>
            <svg class="absolute right-2 top-2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50/80 dark:bg-gray-700/50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Serial No</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Brand</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Size</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order ID</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Used</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-if="isLoading">
              <td colspan="9" class="pt-10 text-center">
                <div class="inline-block">
                  <div class="w-8 h-8 rounded-full border-4 border-gray-200 border-t-red-600 animate-spin mx-auto"></div>
                  <div class="text-center text-gray-400 py-4 text-sm">Fetching Ledger</div>
                </div>
              </td>
            </tr>
            <template v-else>
              <tr v-for="entry in ledgerEntries" :key="entry.ledger_id" class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center">
                      <ClipboardList class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-semibold text-gray-900 dark:text-white font-mono">{{ entry.serial_number }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div v-if="entry.product_type === 'graft'" class="text-sm font-medium text-gray-900 dark:text-white">Graft</div>
                  <div v-else class="text-sm font-medium text-gray-900 dark:text-white">{{ entry.product_name }}</div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div v-if="entry.product_type === 'other_product'" class="text-sm text-gray-400 dark:text-gray-500">Not Applicable</div>
                  <div v-else class="text-sm font-medium text-gray-900 dark:text-white">{{ entry.brand_name ?? '-' }}</div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div v-if="entry.product_type === 'other_product'" class="text-sm text-gray-400 dark:text-gray-500">Not Applicable</div>
                  <div v-else class="text-sm text-gray-700 dark:text-gray-300">{{ entry.size_name ?? '-' }}</div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div v-if="entry.order_number || entry.order_code" class="flex flex-col gap-0.5">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ entry.order_number ?? entry.order_code }}</span>
                    <span v-if="entry.order_number && entry.order_code" class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">{{ entry.order_code }}</span>
                  </div>
                  <span v-else class="text-sm text-gray-400 dark:text-gray-500">No records found</span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="getStatusBadgeClass(entry.status)">
                    {{ entry.status_label }}
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="entry.is_used
                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                      : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'">
                    {{ entry.is_used ? 'Yes' : 'No' }}
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="entry.invoice_status === 'paid'
                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                      : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'">
                    {{ entry.invoice_status === 'paid' ? 'Paid' : 'Unpaid' }}
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="flex items-center gap-1">
                    <button @click="openViewModal(entry)"
                      class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-400 rounded-lg transition-all duration-200"
                      title="View Details">
                      <Eye class="w-5 h-5" />
                    </button>
                    <button @click="openEditModal(entry)"
                      class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-amber-100 dark:hover:bg-amber-900/30 hover:text-amber-700 dark:hover:text-amber-400 rounded-lg transition-all duration-200"
                      title="Edit Entry">
                      <Pencil class="w-5 h-5" />
                    </button>
                    <button @click="confirmDelete(entry)"
                      class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-700 dark:hover:text-red-400 rounded-lg transition-all duration-200"
                      title="Delete Entry">
                      <Trash2 class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Empty State (Desktop) -->
      <div v-if="!isLoading && ledgerEntries.length === 0" class="text-center py-16">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
            <Package class="w-8 h-8 text-gray-400 dark:text-gray-500" />
          </div>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No ledger entries found</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">No records match your current filters.</p>
        <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
          <Plus class="w-4 h-4" />
          Add your first entry
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="ledgerEntries.length > 0" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">Show</span>
          <select v-model="itemsPerPage" @change="currentPage = 1; fetchLedger()"
            class="px-2 py-1 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-sm text-gray-900 dark:text-white">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
          <span class="text-sm text-gray-600 dark:text-gray-400">entries per page</span>
        </div>
        <div class="flex items-center gap-2">
          <button @click="currentPage > 1 && currentPage-- ; fetchLedger()" :disabled="currentPage <= 1"
            class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Previous
          </button>
          <span class="text-sm text-gray-600 dark:text-gray-400">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <button @click="currentPage < totalPages && currentPage++ ; fetchLedger()" :disabled="currentPage >= totalPages"
            class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-4">
      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center">
        <div class="w-8 h-8 rounded-full border-4 border-gray-200 border-t-red-600 animate-spin mx-auto"></div>
        <div class="text-center text-gray-400 py-4 text-sm">Fetching Ledger</div>
      </div>

      <!-- Empty State -->
      <div v-else-if="ledgerEntries.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
            <Package class="w-8 h-8 text-gray-400 dark:text-gray-500" />
          </div>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No ledger entries found</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">No records match your current filters.</p>
        <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
          <Plus class="w-4 h-4" />
          Add your first entry
        </button>
      </div>

      <!-- Ledger Cards -->
      <div v-else class="space-y-4">
        <div v-for="entry in ledgerEntries" :key="entry.ledger_id"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <!-- Card Header with Gradient -->
          <div class="bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-700 dark:to-indigo-800 p-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-white/20 rounded-lg flex items-center justify-center">
                  <ClipboardList class="w-5 h-5 text-white" />
                </div>
                <div>
                  <p class="text-white font-bold font-mono text-sm">{{ entry.serial_number }}</p>
                  <p class="text-blue-100 text-xs">{{ entry.product_name }}</p>
                </div>
              </div>
              <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-white/20 text-white border border-white/30">
                {{ entry.status_label }}
              </span>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-4 space-y-3">
            <!-- Brand & Clinic Row -->
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 flex items-center gap-1">
                  <Tag class="w-3 h-3" /> Brand
                </p>
                <p v-if="entry.product_type === 'other_product'" class="text-sm text-gray-400 dark:text-gray-500">Not Applicable</p>
                <p v-else class="text-sm font-semibold text-gray-900 dark:text-white">{{ entry.brand_name ?? '-' }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 flex items-center gap-1">
                  <MapPin class="w-3 h-3" /> Clinic
                </p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ entry.clinic_name ?? '-' }}</p>
              </div>
            </div>

            <!-- Size & Order ID Row -->
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Size</p>
                <p v-if="entry.product_type === 'other_product'" class="text-sm text-gray-400 dark:text-gray-500">Not Applicable</p>
                <p v-else class="text-sm font-semibold text-gray-900 dark:text-white">{{ entry.size_name ?? '-' }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Order ID</p>
                <p v-if="entry.order_number || entry.order_code" class="text-sm font-semibold text-gray-900 dark:text-white">{{ entry.order_number ?? entry.order_code }}</p>
                <p v-else class="text-sm text-gray-400 dark:text-gray-500">No records found</p>
              </div>
            </div>

            <!-- Used & Invoice Row -->
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Used</p>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium"
                  :class="entry.is_used
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'">
                  {{ entry.is_used ? 'Yes' : 'No' }}
                </span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Invoice</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium"
                  :class="entry.invoice_status === 'paid'
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'">
                  {{ entry.invoice_status === 'paid' ? 'Paid' : 'Unpaid' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Card Actions -->
          <div class="bg-gray-50 dark:bg-gray-700/30 -mx-4 px-4 -mb-4 pb-4 pt-3">
            <div class="flex gap-2">
              <button @click="openViewModal(entry)"
                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-800 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors shadow-sm">
                <Eye class="w-4 h-4" />
                View
              </button>
              <button @click="openEditModal(entry)"
                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2.5 text-sm font-medium text-amber-600 dark:text-amber-400 bg-white dark:bg-gray-800 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-colors shadow-sm">
                <Pencil class="w-4 h-4" />
                Edit
              </button>
              <button @click="confirmDelete(entry)"
                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:bg-gray-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors shadow-sm">
                <Trash2 class="w-4 h-4" />
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Pagination -->
      <div v-if="ledgerEntries.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center justify-between">
        <button @click="currentPage > 1 && currentPage-- ; fetchLedger()" :disabled="currentPage <= 1"
          class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
          Previous
        </button>
        <span class="text-sm text-gray-600 dark:text-gray-400">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button @click="currentPage < totalPages && currentPage++ ; fetchLedger()" :disabled="currentPage >= totalPages"
          class="px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
          Next
        </button>
      </div>
    </div>

    <!-- View Details Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-[60] flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50 transition-opacity" @click="closeViewModal"></div>
      <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col sm:mx-4 my-0 sm:my-8 overflow-hidden">
        <!-- Header -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ledger Entry Details</h3>
          <button @click="closeViewModal" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div v-if="selectedEntry" class="flex-1 overflow-y-auto p-6 space-y-6">
          <!-- Gradient Banner -->
          <div class="relative bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-700 dark:to-indigo-800 rounded-2xl p-5 shadow-md overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12"></div>
            <div class="relative flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <p class="text-blue-100 dark:text-blue-200 text-xs font-medium mb-1">Serial Number</p>
                <p class="text-white text-xl font-bold font-mono break-all">{{ selectedEntry.serial_number }}</p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/20 text-white border border-white/30">
                  {{ selectedEntry.status_label }}
                </span>
                <span v-if="selectedEntry.product_type === 'other_product'"
                  class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/20 text-white border border-white/30">
                  Other Product
                </span>
                <span v-else
                  class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/20 text-white border border-white/30">
                  Graft
                </span>
              </div>
            </div>
          </div>

          <!-- Info Grid -->
          <div class="space-y-4">
            <!-- Top Info Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Product -->
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <Package class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Product</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ selectedEntry.product_name }}</p>
                  </div>
                </div>
              </div>

              <!-- Brand (Graft only) -->
              <div v-if="selectedEntry.product_type === 'graft'" class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <Tag class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Brand</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ selectedEntry.brand_name ?? '-' }}</p>
                  </div>
                </div>
              </div>

              <!-- Clinic (Graft only) -->
              <div v-if="selectedEntry.product_type === 'graft'" class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <MapPin class="w-5 h-5 text-green-600 dark:text-green-400" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Clinic</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ selectedEntry.clinic_name }}</p>
                  </div>
                </div>
              </div>

              <!-- Order ID -->
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                    <ClipboardList class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Order ID</p>
                    <p v-if="selectedEntry.order_number || selectedEntry.order_code" class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                      {{ selectedEntry.order_number ?? selectedEntry.order_code }}
                    </p>
                    <p v-else class="text-sm text-gray-400 dark:text-gray-500">No records found</p>
                  </div>
                </div>
              </div>

              <!-- Size (Graft only) -->
              <div v-if="selectedEntry.product_type === 'graft'" class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 col-span-1 sm:col-span-2">
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                    <ClipboardList class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Size</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ selectedEntry.size_name ?? '-' }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Used & Invoice Side by Side -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Used -->
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                <button
                  v-if="selectedEntry.is_used"
                  @click="showUsedDetails = !showUsedDetails"
                  class="w-full flex items-center space-x-3 text-left cursor-pointer group"
                >
                  <div class="h-10 w-10 rounded-lg flex items-center justify-center bg-green-100 dark:bg-green-900/30">
                    <CheckCircle2 class="w-5 h-5 text-green-600 dark:text-green-400" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Used</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Yes</p>
                  </div>
                  <ChevronDown class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    :class="showUsedDetails ? 'rotate-180' : ''" />
                </button>
                <div v-else class="flex items-center space-x-3">
                  <div class="h-10 w-10 rounded-lg flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                    <X class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Used</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">No</p>
                  </div>
                </div>
                <div v-if="selectedEntry.is_used && showUsedDetails" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                  <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Graft Log</p>
                  <div v-if="selectedEntry.graft_usage_id" class="flex items-center justify-between">
                    <code class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedEntry.graft_usage_id }}</code>
                    <button @click="copyToClipboard(selectedEntry.graft_usage_id, 'graft')"
                      class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                      :class="copiedField === 'graft'
                        ? 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20'
                        : 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30'">
                      <Check v-if="copiedField === 'graft'" class="w-3.5 h-3.5 mr-1.5" />
                      <Copy v-else class="w-3.5 h-3.5 mr-1.5" />
                      {{ copiedField === 'graft' ? 'Copied' : 'Copy' }}
                    </button>
                  </div>
                  <p v-else class="text-sm text-gray-400 dark:text-gray-500">No records found</p>
                </div>
              </div>

              <!-- Invoice -->
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                <button
                  v-if="selectedEntry.invoice_status === 'paid'"
                  @click="showInvoiceDetails = !showInvoiceDetails"
                  class="w-full flex items-center space-x-3 text-left cursor-pointer group"
                >
                  <div class="h-10 w-10 rounded-lg flex items-center justify-center bg-green-100 dark:bg-green-900/30">
                    <DollarSign class="w-5 h-5 text-green-600 dark:text-green-400" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Invoice</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Paid</p>
                  </div>
                  <ChevronDown class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    :class="showInvoiceDetails ? 'rotate-180' : ''" />
                </button>
                <div v-else class="flex items-center space-x-3">
                  <div class="h-10 w-10 rounded-lg flex items-center justify-center bg-orange-100 dark:bg-orange-900/30">
                    <DollarSign class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Invoice</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Unpaid</p>
                  </div>
                </div>
                <div v-if="selectedEntry.invoice_status === 'paid' && showInvoiceDetails" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                  <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Invoice Number</p>
                  <div v-if="selectedEntry.invoice_number" class="flex items-center justify-between">
                    <code class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedEntry.invoice_number }}</code>
                    <button @click="copyToClipboard(selectedEntry.invoice_number, 'invoice')"
                      class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                      :class="copiedField === 'invoice'
                        ? 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20'
                        : 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30'">
                      <Check v-if="copiedField === 'invoice'" class="w-3.5 h-3.5 mr-1.5" />
                      <Copy v-else class="w-3.5 h-3.5 mr-1.5" />
                      {{ copiedField === 'invoice' ? 'Copied' : 'Copy' }}
                    </button>
                  </div>
                  <p v-else class="text-sm text-gray-400 dark:text-gray-500">No records found</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="selectedEntry.notes" class="bg-yellow-50 dark:bg-yellow-900/10 rounded-xl p-4 border border-yellow-100 dark:border-yellow-900/20">
            <p class="text-xs font-medium text-yellow-700 dark:text-yellow-400 uppercase tracking-wide mb-2">Notes</p>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ selectedEntry.notes }}</p>
          </div>

          <!-- Timestamps -->
          <div class="flex flex-col sm:flex-row gap-4 text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-1.5">
              <span>Created: {{ formatDateTime(selectedEntry.created_at) }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span>Updated: {{ formatDateTime(selectedEntry.updated_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
          <button @click="closeViewModal"
            class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
            Close
          </button>
          <button @click="closeViewModal(); openEditModal(selectedEntry!)"
            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg text-sm font-medium flex items-center gap-2">
            <Pencil class="w-4 h-4" />
            Edit Entry
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50 transition-opacity" @click="closeModal"></div>
      <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col sm:mx-4 my-0 sm:my-8 overflow-hidden">
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditing ? 'Edit Ledger Entry' : 'Add Ledger Entry' }}
          </h3>
          <button @click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
          <!-- Section 1: Basic Information -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 space-y-4">
            <div class="flex items-center gap-2 mb-1">
              <Hash class="w-4 h-4 text-blue-500" />
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Basic Information</h4>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Serial Number <span class="text-red-500">*</span></label>
                <input v-model="form.serial_number" type="text" placeholder="e.g. SN-12345"
                  class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                <p v-if="formErrors.serial_number" class="mt-1 text-xs text-red-500">{{ formErrors.serial_number }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Product Type <span class="text-red-500">*</span></label>
                <select v-model="form.product_type" @change="onProductTypeChange"
                  class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="graft">Graft</option>
                  <option value="other_product">Other Product</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Section 2: Product Selection -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 space-y-4">
            <div class="flex items-center gap-2 mb-1">
              <Package class="w-4 h-4 text-purple-500" />
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Product Selection</h4>
            </div>
            <!-- Graft Flow: Clinic → Brand → Product -->
            <template v-if="form.product_type === 'graft'">
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Clinic <span class="text-red-500">*</span></label>
                  <select v-model="form.clinic_id"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select clinic</option>
                    <option v-for="clinic in clinics" :key="clinic.id" :value="String(clinic.id)">
                      {{ clinic.name }}
                    </option>
                  </select>
                  <p v-if="formErrors.clinic_id" class="mt-1 text-xs text-red-500">{{ formErrors.clinic_id }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Brand <span class="text-red-500">*</span></label>
                  <select v-model="form.brand_id" @change="form.product_id = ''"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select brand</option>
                    <option v-for="brand in brands" :key="brand.id" :value="String(brand.id)">
                      {{ brand.brandName }}
                    </option>
                  </select>
                  <p v-if="formErrors.brand_id" class="mt-1 text-xs text-red-500">{{ formErrors.brand_id }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Graft Size <span class="text-red-500">*</span></label>
                  <select v-model="form.product_id" :disabled="!form.brand_id"
                    class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">{{ form.brand_id ? 'Select size' : 'Select brand first' }}</option>
                    <option v-for="prod in filteredGraftProducts" :key="prod.id" :value="prod.id">
                      {{ prod.name }} {{ prod.area ? `(${prod.area} cm²)` : '' }}
                    </option>
                  </select>
                  <p v-if="formErrors.product_id" class="mt-1 text-xs text-red-500">{{ formErrors.product_id }}</p>
                </div>
              </div>
            </template>
            <!-- Other Product Flow -->
            <template v-else>
              <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Product <span class="text-red-500">*</span></label>
                <select v-model="form.product_id"
                  class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="">Select a product</option>
                  <option v-for="prod in otherProducts" :key="prod.id" :value="prod.id">
                    {{ prod.name }} {{ prod.price ? `($${prod.price})` : '' }}
                  </option>
                </select>
                <p v-if="formErrors.product_id" class="mt-1 text-xs text-red-500">{{ formErrors.product_id }}</p>
              </div>
            </template>
          </div>

          <!-- Section 3: Order & Status -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 space-y-4">
            <div class="flex items-center gap-2 mb-1">
              <ClipboardList class="w-4 h-4 text-indigo-500" />
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Order & Status</h4>
            </div>

            <!-- Order ID -->
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Order ID</label>
              <div class="relative">
                <input
                  v-model="orderSearchQuery"
                  @input="performOrderSearch"
                  @focus="onOrderInputFocus()"
                  @blur="hideOrderDropdown()"
                  type="text"
                  placeholder="Type order number to search..."
                  class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                />
                <div class="absolute right-3 top-3">
                  <Loader2 v-if="isSearchingOrders" class="w-4 h-4 text-gray-400 animate-spin" />
                  <button v-else-if="orderSearchQuery || form.order_id" @click="clearOrder" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <X class="w-4 h-4" />
                  </button>
                  <Search v-else class="w-4 h-4 text-gray-400" />
                </div>
                <div v-if="showOrderDropdown && orderSuggestions.length > 0"
                  class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                  <button v-for="order in orderSuggestions" :key="order.id"
                    @click="selectOrder(order)"
                    class="w-full px-4 py-2.5 text-left text-sm text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors first:rounded-t-lg last:rounded-b-lg">
                    <div class="font-medium">{{ order.display }}</div>
                    <div v-if="order.order_code && order.order_code !== order.display" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                      Code: {{ order.order_code }}
                    </div>
                  </button>
                </div>
                <div v-if="showOrderDropdown && !isSearchingOrders && orderSuggestions.length === 0 && orderSearchQuery.trim()"
                  class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                  No orders found
                </div>
              </div>
              <p v-if="form.order_id" class="mt-1.5 text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                <CheckCircle2 class="w-3 h-3" /> Linked to: {{ orderSearchQuery }}
              </p>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">Status <span class="text-red-500">*</span></label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="(label, value) in statusOptions"
                  :key="value"
                  type="button"
                  @click="form.status = Number(value)"
                  :class="[
                    'relative px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 ease-out select-none',
                    'border-2 flex items-center gap-1.5',
                    form.status === Number(value)
                      ? getStatusPillActiveClass(Number(value))
                      : getStatusPillInactiveClass(Number(value))
                  ]"
                >
                  <span
                    :class="[
                      'w-1.5 h-1.5 rounded-full transition-transform duration-200',
                      form.status === Number(value) ? 'scale-110' : 'scale-90 opacity-60'
                    ]"
                    :style="{ backgroundColor: form.status === Number(value) ? 'currentColor' : getStatusDotColor(Number(value)) }"
                  />
                  {{ label }}
                </button>
              </div>
            </div>
          </div>

          <!-- Section 4: Usage & Billing -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 space-y-4">
            <div class="flex items-center gap-2 mb-1">
              <Receipt class="w-4 h-4 text-green-500" />
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Usage & Billing</h4>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Used Toggle -->
              <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Used</label>
                <div class="inline-flex p-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                  <button type="button" @click="form.is_used = true"
                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-all flex items-center gap-1.5',
                      form.is_used
                        ? 'bg-green-500 text-white shadow-sm'
                        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']">
                    <CheckCircle2 class="w-3.5 h-3.5" /> Yes
                  </button>
                  <button type="button" @click="form.is_used = false; form.graft_usage_id = ''"
                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-all flex items-center gap-1.5',
                      !form.is_used
                        ? 'bg-gray-500 text-white shadow-sm'
                        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']">
                    <X class="w-3.5 h-3.5" /> No
                  </button>
                </div>
                <transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
                  <div v-if="form.is_used" class="mt-3 bg-white dark:bg-gray-700 rounded-lg border border-green-200 dark:border-green-800 shadow-sm overflow-hidden">
                    <div class="flex items-start gap-3 p-3">
                      <div class="mt-0.5 flex-shrink-0 w-7 h-7 rounded-md bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                        <Hash class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                      </div>
                      <div class="flex-1 min-w-0">
                        <label class="block text-xs font-semibold text-green-700 dark:text-green-400 mb-1.5">Graft Usage ID</label>
                        <input v-model="form.graft_usage_id" type="text" placeholder="Enter usage reference"
                          class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent" />
                      </div>
                    </div>
                  </div>
                </transition>
              </div>
              <!-- Invoice Toggle -->
              <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Invoice</label>
                <div class="inline-flex p-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                  <button type="button" @click="form.invoice_status = 'paid'"
                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-all flex items-center gap-1.5',
                      form.invoice_status === 'paid'
                        ? 'bg-green-500 text-white shadow-sm'
                        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']">
                    <CheckCircle2 class="w-3.5 h-3.5" /> Paid
                  </button>
                  <button type="button" @click="form.invoice_status = 'unpaid'; form.invoice_id = ''"
                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-all flex items-center gap-1.5',
                      form.invoice_status === 'unpaid'
                        ? 'bg-orange-500 text-white shadow-sm'
                        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600']">
                    <AlertCircle class="w-3.5 h-3.5" /> Unpaid
                  </button>
                </div>
                <transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
                  <div v-if="form.invoice_status === 'paid'" class="mt-3 bg-white dark:bg-gray-700 rounded-lg border border-green-200 dark:border-green-800 shadow-sm overflow-hidden">
                    <div class="flex items-start gap-3 p-3">
                      <div class="mt-0.5 flex-shrink-0 w-7 h-7 rounded-md bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                        <FileText class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                      </div>
                      <div class="flex-1 min-w-0">
                        <label class="block text-xs font-semibold text-green-700 dark:text-green-400 mb-1.5">Select Invoice</label>
                        <select v-model="form.invoice_id"
                          class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent">
                          <option value="">Select invoice</option>
                          <option v-for="inv in invoices" :key="inv.id" :value="String(inv.id)">
                            {{ inv.invoice_number }} ({{ inv.status }})
                          </option>
                        </select>
                      </div>
                    </div>
                  </div>
                </transition>
              </div>
            </div>
          </div>

          <!-- Section 5: Notes -->
          <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-3">
              <FileText class="w-4 h-4 text-yellow-500" />
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Notes</h4>
              <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">Optional</span>
            </div>
            <textarea v-model="form.notes" rows="3" placeholder="Add any additional notes here..."
              class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
          </div>
        </div>
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
          <button @click="closeModal"
            class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
            Cancel
          </button>
          <button @click="saveEntry" :disabled="isSaving"
            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium flex items-center gap-2">
            <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin" />
            <Save v-else class="w-4 h-4" />
            {{ isEditing ? 'Update Entry' : 'Add Entry' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-[60] flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50 transition-opacity" @click="showDeleteModal = false"></div>
      <div class="relative bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-xl shadow-xl w-full max-w-md flex flex-col sm:mx-4 my-0 sm:my-8 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-6">
          <div class="space-y-6">
            <!-- Warning Icon -->
            <div class="flex justify-center">
              <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <AlertTriangle class="w-8 h-8 text-red-600 dark:text-red-400" />
              </div>
            </div>

            <!-- Message -->
            <div class="text-center space-y-2">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Are you sure?</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">You are about to delete this ledger entry:</p>
            </div>

            <!-- Item Details -->
            <div v-if="entryToDelete" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-3">
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Serial Number</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ entryToDelete.serial_number }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ entryToDelete.status_label }}</p>
              </div>
            </div>

            <!-- Warning Message -->
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
              <p class="text-xs font-semibold text-red-800 dark:text-red-400 flex items-center justify-center mb-2">
                <AlertTriangle class="w-4 h-4 mr-1.5" />
                Warning: This action cannot be reverted
              </p>
              <p class="text-xs text-red-700 dark:text-red-300 text-center">
                Once deleted, this ledger entry will be permanently removed from the system.
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex-shrink-0 bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
          <button @click="showDeleteModal = false"
            class="px-5 py-2.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 text-sm font-medium">
            Cancel
          </button>
          <button @click="deleteEntry" :disabled="isDeleting"
            class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md hover:shadow-lg text-sm font-medium flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
            <Trash2 v-else class="w-4 h-4" />
            Delete Entry
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// ============================================================================
// INVENTORY LEDGER MANAGEMENT MODULE - VIEW
// ----------------------------------------------------------------------------
// This view powers the standalone Inventory Ledger Management feature.
// To remove this module, delete this file and remove the route registration.
// ============================================================================

import { ref, computed, watch, onMounted } from 'vue'
import Swal from 'sweetalert2'
import {
  Search, X, Plus, BarChart3, Package, CheckCircle2, AlertCircle, Activity,
  Loader2, Pencil, Trash2, Save, AlertTriangle, SlidersHorizontal, Eye,
  MapPin, Receipt, DollarSign, ClipboardList, Tag, Copy, Check, ChevronDown, Hash, FileText, ArrowRight
} from 'lucide-vue-next'
import { inventoryLedgerService, userService, brandService } from '@/services/api'

// Types
interface LedgerEntry {
  ledger_id: number
  serial_number: string
  product_type: string
  product_id: number
  product_name: string
  brand_id: string | null
  brand_name: string
  size_name: string | null
  clinic_id: string
  clinic_name: string
  order_id: string | null
  order_number: string | null
  order_code: string | null
  status: number
  status_label: string
  status_color: string
  is_used: boolean
  graft_usage_id: string | null
  invoice_status: string
  invoice_id: string | null
  invoice_number: string | null
  notes: string | null
  created_at: string
  updated_at: string
}

interface ProductOption {
  id: string
  type: string
  name: string
  brand_id?: string
  brand_name?: string
  area?: number
  price?: number
}

// State
const ledgerEntries = ref<LedgerEntry[]>([])
const isLoading = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)
const showStats = ref(false)
const showModal = ref(false)
const showDeleteModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const entryToDelete = ref<LedgerEntry | null>(null)
const selectedEntry = ref<LedgerEntry | null>(null)
const showFilters = ref(false)
const showUsedDetails = ref(false)
const showInvoiceDetails = ref(false)

// Filters
const searchTerm = ref('')
const statusFilter = ref('all')
const brandFilter = ref('all')
const clinicFilter = ref('all')
const invoiceStatusFilter = ref('all')

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalPages = ref(1)
const totalResults = ref(0)

// Dropdown data
const brands = ref<any[]>([])
const clinics = ref<any[]>([])
const invoices = ref<any[]>([])
const graftSizeProducts = ref<ProductOption[]>([])
const otherProducts = ref<ProductOption[]>([])

// Order search
const orderSearchQuery = ref('')
const orderSuggestions = ref<any[]>([])
const showOrderDropdown = ref(false)
const isSearchingOrders = ref(false)
let orderSearchTimeout: ReturnType<typeof setTimeout>

// Stats
const stats = ref({
  total: 0,
  paid: 0,
  unpaid: 0,
  used: 0,
})

// Form
const form = ref({
  ledger_id: null as number | null,
  serial_number: '',
  product_type: 'graft' as string,
  product_id: '',
  brand_id: '',
  clinic_id: '',
  order_id: '' as string | number,
  status: 1,
  is_used: false,
  graft_usage_id: '',
  invoice_status: 'unpaid' as string,
  invoice_id: '',
  notes: '',
})

const formErrors = ref<Record<string, string>>({})

const statusOptions: Record<number, string> = {
  0: 'Expected',
  1: 'Delivered',
  2: 'Used',
  3: 'Partially Used',
  4: 'Reassigned',
  5: 'Unused',
  6: 'Expired',
}

const filteredGraftProducts = computed(() => {
  if (!form.value.brand_id) {
    return []
  }
  return graftSizeProducts.value.filter(
    (prod) => String(prod.brand_id) === String(form.value.brand_id)
  )
})

const hasActiveFilters = computed(() => {
  return statusFilter.value !== 'all' ||
    brandFilter.value !== 'all' ||
    clinicFilter.value !== 'all' ||
    invoiceStatusFilter.value !== 'all'
})

function getBrandName(id: string): string {
  const brand = brands.value.find((b) => String(b.id) === String(id))
  return brand?.brandName ?? id
}

function getClinicName(id: string): string {
  const clinic = clinics.value.find((c) => String(c.id) === String(id))
  return clinic?.name ?? id
}

function formatDateTime(dateStr: string): string {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const copiedField = ref<string | null>(null)

async function copyToClipboard(text: string, field: string) {
  try {
    await navigator.clipboard.writeText(text)
    copiedField.value = field
    setTimeout(() => { copiedField.value = null }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

// Order search
async function performOrderSearch() {
  clearTimeout(orderSearchTimeout)
  const query = orderSearchQuery.value.trim()
  if (!query) {
    orderSuggestions.value = []
    showOrderDropdown.value = false
    return
  }
  orderSearchTimeout = setTimeout(async () => {
    isSearchingOrders.value = true
    try {
      const response = await inventoryLedgerService.searchOrders(query, 10)
      orderSuggestions.value = response.data.data || []
      showOrderDropdown.value = orderSuggestions.value.length > 0
    } catch (err) {
      console.error('Order search failed:', err)
      orderSuggestions.value = []
    } finally {
      isSearchingOrders.value = false
    }
  }, 300)
}

function selectOrder(order: any) {
  form.value.order_id = order.id
  orderSearchQuery.value = order.display
  showOrderDropdown.value = false
  orderSuggestions.value = []
}

function clearOrder() {
  form.value.order_id = ''
  orderSearchQuery.value = ''
  showOrderDropdown.value = false
  orderSuggestions.value = []
}

function hideOrderDropdown() {
  setTimeout(() => {
    showOrderDropdown.value = false
  }, 200)
}

function onOrderInputFocus() {
  if (orderSuggestions.value.length > 0) {
    showOrderDropdown.value = true
  }
}

// Badge classes
function getStatusBadgeClass(status: number): string {
  const classes: Record<number, string> = {
    0: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
    1: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    2: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    3: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
    4: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    5: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    6: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
  }
  return classes[status] || classes[1]
}

function getStatusPillActiveClass(status: number): string {
  const classes: Record<number, string> = {
    0: 'bg-yellow-500 border-yellow-500 text-white shadow-md shadow-yellow-200/50 dark:shadow-yellow-900/30 scale-[1.02]',
    1: 'bg-blue-500 border-blue-500 text-white shadow-md shadow-blue-200/50 dark:shadow-blue-900/30 scale-[1.02]',
    2: 'bg-green-500 border-green-500 text-white shadow-md shadow-green-200/50 dark:shadow-green-900/30 scale-[1.02]',
    3: 'bg-orange-500 border-orange-500 text-white shadow-md shadow-orange-200/50 dark:shadow-orange-900/30 scale-[1.02]',
    4: 'bg-purple-500 border-purple-500 text-white shadow-md shadow-purple-200/50 dark:shadow-purple-900/30 scale-[1.02]',
    5: 'bg-gray-500 border-gray-500 text-white shadow-md shadow-gray-200/50 dark:shadow-gray-900/30 scale-[1.02]',
    6: 'bg-red-500 border-red-500 text-white shadow-md shadow-red-200/50 dark:shadow-red-900/30 scale-[1.02]',
  }
  return classes[status] || classes[1]
}

function getStatusPillInactiveClass(status: number): string {
  const classes: Record<number, string> = {
    0: 'bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 hover:border-yellow-300 dark:hover:border-yellow-700',
    1: 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-700',
    2: 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/20 hover:border-green-300 dark:hover:border-green-700',
    3: 'bg-orange-50 dark:bg-orange-900/10 border-orange-200 dark:border-orange-800 text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/20 hover:border-orange-300 dark:hover:border-orange-700',
    4: 'bg-purple-50 dark:bg-purple-900/10 border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-700',
    5: 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500',
    6: 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-700',
  }
  return classes[status] || classes[1]
}

function getStatusDotColor(status: number): string {
  const colors: Record<number, string> = {
    0: '#eab308',
    1: '#3b82f6',
    2: '#22c55e',
    3: '#f97316',
    4: '#a855f7',
    5: '#6b7280',
    6: '#ef4444',
  }
  return colors[status] || colors[1]
}

function onProductTypeChange() {
  form.value.product_id = ''
  form.value.brand_id = ''
  form.value.clinic_id = ''
}

function resetForm() {
  form.value = {
    ledger_id: null,
    serial_number: '',
    product_type: 'graft',
    product_id: '',
    brand_id: '',
    clinic_id: '',
    order_id: '',
    status: 1,
    is_used: false,
    graft_usage_id: '',
    invoice_status: 'unpaid',
    invoice_id: '',
    notes: '',
  }
  formErrors.value = {}
  orderSearchQuery.value = ''
  orderSuggestions.value = []
  showOrderDropdown.value = false
}

function openCreateModal() {
  resetForm()
  isEditing.value = false
  showModal.value = true
}

function openEditModal(entry: LedgerEntry) {
  resetForm()
  form.value = {
    ledger_id: entry.ledger_id,
    serial_number: entry.serial_number,
    product_type: entry.product_type,
    product_id: String(entry.product_id),
    brand_id: entry.brand_id ?? '',
    clinic_id: String(entry.clinic_id),
    order_id: entry.order_id ?? '',
    status: entry.status,
    is_used: entry.is_used,
    graft_usage_id: entry.graft_usage_id ?? '',
    invoice_status: entry.invoice_status,
    invoice_id: entry.invoice_id ?? '',
    notes: entry.notes ?? '',
  }
  if (entry.order_number || entry.order_code) {
    orderSearchQuery.value = entry.order_number ?? entry.order_code ?? ''
  }
  isEditing.value = true
  showModal.value = true
}

function openViewModal(entry: LedgerEntry) {
  selectedEntry.value = entry
  showUsedDetails.value = false
  showInvoiceDetails.value = false
  showViewModal.value = true
}

function closeViewModal() {
  showViewModal.value = false
  selectedEntry.value = null
  showUsedDetails.value = false
  showInvoiceDetails.value = false
}

function closeModal() {
  showModal.value = false
  resetForm()
}

function confirmDelete(entry: LedgerEntry) {
  entryToDelete.value = entry
  showDeleteModal.value = true
}

function validateForm(): boolean {
  const errors: Record<string, string> = {}
  if (!form.value.serial_number.trim()) errors.serial_number = 'Serial number is required'
  if (!form.value.product_id) errors.product_id = 'Product is required'

  if (form.value.product_type === 'graft') {
    if (!form.value.clinic_id) errors.clinic_id = 'Clinic is required'
    if (!form.value.brand_id) errors.brand_id = 'Brand is required'
  }

  formErrors.value = errors
  return Object.keys(errors).length === 0
}

async function saveEntry() {
  if (!validateForm()) return

  isSaving.value = true
  try {
    const payload = {
      serial_number: form.value.serial_number.trim(),
      product_type: form.value.product_type,
      product_id: Number(form.value.product_id),
      brand_id: form.value.brand_id ? Number(form.value.brand_id) : null,
      clinic_id: form.value.clinic_id ? Number(form.value.clinic_id) : null,
      order_id: form.value.order_id ? Number(form.value.order_id) : null,
      status: form.value.status,
      is_used: form.value.is_used,
      graft_usage_id: form.value.graft_usage_id.trim() || null,
      invoice_status: form.value.invoice_status,
      invoice_id: form.value.invoice_id ? Number(form.value.invoice_id) : null,
      notes: form.value.notes.trim() || null,
    }

    if (isEditing.value && form.value.ledger_id) {
      await inventoryLedgerService.update(form.value.ledger_id, payload)
      await Swal.fire({
        title: 'Updated',
        text: 'Ledger entry updated successfully.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      })
    } else {
      await inventoryLedgerService.create(payload)
      await Swal.fire({
        title: 'Created',
        text: 'Ledger entry created successfully.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      })
    }

    closeModal()
    await fetchLedger()
    await fetchStats()
  } catch (error: any) {
    console.error('Save error:', error)
    await Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Failed to save ledger entry.',
      icon: 'error',
      confirmButtonColor: '#2563eb',
    })
  } finally {
    isSaving.value = false
  }
}

async function deleteEntry() {
  if (!entryToDelete.value) return

  isDeleting.value = true
  try {
    await inventoryLedgerService.delete(entryToDelete.value.ledger_id)
    showDeleteModal.value = false
    entryToDelete.value = null
    await Swal.fire({
      title: 'Deleted',
      text: 'Ledger entry deleted successfully.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
    })
    await fetchLedger()
    await fetchStats()
  } catch (error: any) {
    console.error('Delete error:', error)
    await Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Failed to delete ledger entry.',
      icon: 'error',
      confirmButtonColor: '#2563eb',
    })
  } finally {
    isDeleting.value = false
  }
}

async function fetchLedger() {
  isLoading.value = true
  try {
    const params: any = {
      page: currentPage.value,
      per_page: itemsPerPage.value,
    }
    if (searchTerm.value) params.search = searchTerm.value
    if (statusFilter.value !== 'all') params.status = statusFilter.value
    if (brandFilter.value !== 'all') params.brand_id = brandFilter.value
    if (clinicFilter.value !== 'all') params.clinic_id = clinicFilter.value
    if (invoiceStatusFilter.value !== 'all') params.invoice_status = invoiceStatusFilter.value

    const response = await inventoryLedgerService.getAll(params)
    const payload = response.data
    ledgerEntries.value = payload.data || []
    totalResults.value = payload.total || 0
    totalPages.value = payload.last_page || 1
    currentPage.value = payload.current_page || 1
  } catch (error: any) {
    console.error('Failed to fetch ledger:', error)
    await Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Failed to load ledger entries.',
      icon: 'error',
      confirmButtonColor: '#2563eb',
    })
  } finally {
    isLoading.value = false
  }
}

async function fetchStats() {
  try {
    const response = await inventoryLedgerService.getStats()
    stats.value = response.data.data || { total: 0, paid: 0, unpaid: 0, used: 0 }
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

async function fetchInitData() {
  try {
    const response = await inventoryLedgerService.getInitData()
    const data = response.data.data || {}

    // Populate brands
    brands.value = (data.brands || []).map((b: any) => ({ brand_id: b.id, brandName: b.name }))

    // Populate clinics
    clinics.value = (data.clinics || []).map((c: any) => ({ clinic_id: c.id, name: c.name }))

    // Populate products
    graftSizeProducts.value = data.graft_sizes || []
    otherProducts.value = data.other_products || []

    // Populate invoices
    invoices.value = data.invoices || []

    // Populate stats
    stats.value = data.stats || { total: 0, paid: 0, unpaid: 0, used: 0 }
  } catch (error) {
    console.error('Failed to fetch init data:', error)
  }
}

async function fetchBrands() {
  try {
    const response = await brandService.getAllBrands({ per_page: 1000 })
    const brandRows = Array.isArray(response.data?.brandData)
      ? response.data.brandData
      : Array.isArray(response.data?.data)
        ? response.data.data
        : []
    brands.value = brandRows
  } catch (error) {
    console.error('Failed to fetch brands:', error)
  }
}

async function fetchClinics() {
  try {
    const response = await userService.getClinics()
    const clinicRows = Array.isArray(response.data?.user_data)
      ? response.data.user_data
      : Array.isArray(response.data?.data)
        ? response.data.data
        : []
    clinics.value = clinicRows
  } catch (error) {
    console.error('Failed to fetch clinics:', error)
  }
}

async function fetchProducts() {
  try {
    const response = await inventoryLedgerService.getProducts()
    const data = response.data.data || {}
    graftSizeProducts.value = data.graft_sizes || []
    otherProducts.value = data.other_products || []
  } catch (error) {
    console.error('Failed to fetch products:', error)
  }
}

async function fetchInvoices() {
  try {
    const response = await inventoryLedgerService.getInvoices()
    invoices.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch invoices:', error)
  }
}

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>
watch(searchTerm, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchLedger()
  }, 400)
})

watch([statusFilter, brandFilter, clinicFilter, invoiceStatusFilter], () => {
  currentPage.value = 1
  fetchLedger()
})

onMounted(async () => {
  await Promise.all([
    fetchLedger(),
    fetchInitData(),
  ])
})
</script>
