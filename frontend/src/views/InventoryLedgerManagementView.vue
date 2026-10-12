<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="space-y-2">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Inventory Management</h1>
      </div>
      <div class="flex items-center gap-4">
        <button @click="showStats = !showStats"
          class="flex items-center px-5 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200 hover:shadow-sm active:scale-95">
          <BarChart3 class="w-5 h-5 mr-2" />
          {{ showStats ? 'Hide' : 'Show' }} Stats
        </button>
        <button @click="openEntryTypeModal"
          class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/30 group active:scale-95">
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
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-sm hover:bg-white dark:hover:bg-gray-700">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
              <Package class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Items</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-sm hover:bg-white dark:hover:bg-gray-700">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
              <CheckCircle2 class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.paid }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Paid</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-sm hover:bg-white dark:hover:bg-gray-700">
            <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
              <AlertCircle class="w-5 h-5 text-orange-600 dark:text-orange-400" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.unpaid }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Unpaid</p>
            </div>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-sm hover:bg-white dark:hover:bg-gray-700">
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

    <!-- Filters Card -->
    <div class="bg-white dark:bg-gray-800 p-4 lg:p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <!-- Mobile: Stacked Layout -->
      <div class="lg:hidden space-y-3">
        <div class="relative">
          <Search class="absolute left-3.5 top-3 h-5 w-5 text-gray-400 dark:text-gray-500" />
          <input v-model="searchTerm" type="text" placeholder="Search by serial number, brand, or clinic..."
            class="w-full pl-11 pr-10 py-2.5 border border-transparent bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white text-sm transition-all duration-200 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-700/70" />
          <button v-if="searchTerm" @click="searchTerm = ''" class="absolute right-3 top-3 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <X class="w-4 h-4" />
          </button>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div class="relative">
            <Funnel class="absolute left-3 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="statusFilter"
              class="w-full pl-9 pr-7 py-2.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm appearance-none truncate">
              <option value="all">All Statuses</option>
              <option value="expected">Expected</option>
              <option value="delivered">Delivered</option>
              <option value="used">Used</option>
              <option value="partially_used">Partially Used</option>
              <option value="reassigned">Reassigned</option>
              <option value="unused">Unused</option>
              <option value="expired">Expired</option>
            </select>
            <ChevronDown class="absolute right-2 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
          <div class="relative">
            <Funnel class="absolute left-3 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="invoiceStatusFilter"
              class="w-full pl-9 pr-7 py-2.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm appearance-none truncate">
              <option value="all">All Invoice</option>
              <option value="paid">Paid</option>
              <option value="unpaid">Unpaid</option>
            </select>
            <ChevronDown class="absolute right-2 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
          <div class="relative">
            <Funnel class="absolute left-3 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="brandFilter"
              class="w-full pl-9 pr-7 py-2.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm appearance-none truncate">
              <option value="all">All Brands</option>
              <option v-for="brand in brands" :key="brand.brand_id" :value="String(brand.brand_id)">{{ brand.brandName }}</option>
            </select>
            <ChevronDown class="absolute right-2 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
          <div class="relative">
            <Funnel class="absolute left-3 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="clinicFilter"
              class="w-full pl-9 pr-7 py-2.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm appearance-none truncate">
              <option value="all">All Clinics</option>
              <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="String(clinic.clinic_id)">{{ clinic.name }}</option>
            </select>
            <ChevronDown class="absolute right-2 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
        </div>
      </div>

      <!-- Desktop: Horizontal Layout -->
      <div class="hidden lg:flex lg:flex-row lg:items-center gap-4">
        <!-- Search -->
        <div class="relative flex-1">
          <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
          <input v-model="searchTerm" type="text" placeholder="Search by serial number, brand, or clinic..."
            class="w-full pl-12 pr-10 py-3.5 border border-transparent bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 shadow-sm hover:bg-gray-100 dark:hover:bg-gray-700/70" />
          <button v-if="searchTerm" @click="searchTerm = ''" class="absolute right-3 top-3.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <X class="w-4 h-4" />
          </button>
        </div>
        <!-- Filter Dropdowns -->
        <div class="flex flex-wrap gap-2">
          <div class="relative w-44">
            <Funnel class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="statusFilter"
              class="w-full pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200 truncate">
              <option value="all">All Statuses</option>
              <option value="expected">Expected</option>
              <option value="delivered">Delivered</option>
              <option value="used">Used</option>
              <option value="partially_used">Partially Used</option>
              <option value="reassigned">Reassigned</option>
              <option value="unused">Unused</option>
              <option value="expired">Expired</option>
            </select>
            <ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
          <div class="relative w-44">
            <Funnel class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="brandFilter"
              class="w-full pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200 truncate">
              <option value="all">All Brands</option>
              <option v-for="brand in brands" :key="brand.brand_id" :value="String(brand.brand_id)">{{ brand.brandName }}</option>
            </select>
            <ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
          <div class="relative w-44">
            <Funnel class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="clinicFilter"
              class="w-full pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200 truncate">
              <option value="all">All Clinics</option>
              <option v-for="clinic in clinics" :key="clinic.clinic_id" :value="String(clinic.clinic_id)">{{ clinic.name }}</option>
            </select>
            <ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
          <div class="relative w-44">
            <Funnel class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
            <select v-model="invoiceStatusFilter"
              class="w-full pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200 truncate">
              <option value="all">All Invoice</option>
              <option value="paid">Paid</option>
              <option value="unpaid">Unpaid</option>
            </select>
            <ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-3">
      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
        <div class="flex flex-col items-center justify-center">
          <div class="w-8 h-8 rounded-full border-4 border-gray-200 border-t-red-600 animate-spin"></div>
          <div class="text-center text-gray-400 py-4 text-sm">Fetching Ledger Entries</div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="ledgerEntries.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
            <ClipboardList class="w-8 h-8 text-gray-400 dark:text-gray-500" />
          </div>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No entries found</h3>
        <p class="text-gray-600 dark:text-gray-400">No ledger entries match your current filters.</p>
      </div>

      <!-- Ledger Entry Cards -->
      <div v-else class="space-y-3">
        <div v-for="entry in ledgerEntries" :key="entry.ledger_id"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <!-- Card Header with Gradient -->
          <div class="bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-700 dark:to-indigo-800 px-4 py-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-white/20 rounded-lg flex items-center justify-center">
                  <Package class="w-5 h-5 text-white" />
                </div>
                <div class="min-w-0">
                  <p class="text-white font-bold font-mono text-sm truncate">{{ entry.serial_number }}</p>
                  <p class="text-blue-100 text-xs">{{ entry.product_type === 'graft' ? 'Graft' : 'Other Product' }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2 ml-2 flex-shrink-0">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-white/20 text-white">
                  {{ entry.status_label }}
                </span>
              </div>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-4 space-y-3">
            <!-- Product & Clinic Row -->
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Product</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ entry.product_name }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ entry.product_type === 'other_product' ? 'Type' : 'Clinic' }}</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ entry.product_type === 'other_product' ? 'Other Product' : entry.clinic_name }}</p>
              </div>
            </div>

            <!-- Brand & Size Row (graft only) -->
            <div v-if="entry.product_type === 'graft'" class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Brand</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ entry.brand_name || 'No Records Found' }}</p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Size</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ entry.size_name || 'No Records Found' }}</p>
              </div>
            </div>

            <!-- Invoice & Usage Row -->
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Invoice</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="entry.invoice_status === 'paid'
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'">
                  {{ entry.invoice_status === 'paid' ? 'Paid' : 'Unpaid' }}
                </span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Usage</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="entry.is_used
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300'">
                  {{ entry.is_used ? 'Used' : 'Not Used' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Card Actions -->
          <div class="px-4 pb-4">
            <div class="grid grid-cols-3 gap-2">
              <button @click="selectEntry(entry); openEditModal(entry)"
                class="flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                <Pencil class="w-4 h-4" />
                Edit
              </button>
              <button v-if="!isOfficeStaff" @click="confirmDelete(entry)"
                class="flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                <Trash2 class="w-4 h-4" />
                Delete
              </button>
              <button @click="openViewModal(entry)"
                class="flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                <Eye class="w-4 h-4" />
                View
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Pagination -->
      <div v-if="ledgerEntries.length > 0" class="flex items-center justify-between gap-3 px-3 py-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <!-- Prev Button -->
        <button
          :disabled="currentPage <= 1"
          @click="currentPage > 1 && (currentPage--, fetchLedger())"
          class="flex items-center justify-center gap-1.5 flex-1 max-w-[120px] py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
          <ChevronLeft class="w-4 h-4" />
          Prev
        </button>

        <!-- Page Counter -->
        <div class="flex items-center gap-1.5 px-4">
          <span class="text-lg font-bold text-gray-900 dark:text-white">{{ currentPage }}</span>
          <span class="text-gray-400">/</span>
          <span class="text-base text-gray-500 dark:text-gray-400">{{ totalPages }}</span>
        </div>

        <!-- Next Button -->
        <button
          :disabled="currentPage >= totalPages"
          @click="currentPage < totalPages && (currentPage++, fetchLedger())"
          class="flex items-center justify-center gap-1.5 flex-1 max-w-[120px] py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
          Next
          <ChevronRight class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Desktop Card Grid -->
    <div class="hidden lg:block">
      <!-- Loading -->
      <div v-if="isLoading" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 flex flex-col items-center justify-center">
        <div class="w-8 h-8 rounded-full border-4 border-gray-200 dark:border-gray-700 border-t-red-600 animate-spin"></div>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-3">Loading entries...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="ledgerEntries.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
        <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
          <Package class="w-7 h-7 text-gray-400 dark:text-gray-500" />
        </div>
        <p class="text-base font-medium text-gray-700 dark:text-gray-300">No entries found</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Try adjusting your filters or add a new entry.</p>
      </div>

      <!-- Entry Card Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 lg:gap-5">
        <div v-for="entry in ledgerEntries" :key="entry.ledger_id"
          class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:shadow-red-900/5 hover:border-red-200 dark:hover:border-red-800/60 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
          <!-- Clickable Header -->
          <button type="button" @click="openViewModal(entry)" class="w-full text-left p-5 focus:outline-none">
            <div class="flex items-start gap-3 mb-4">
              <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors duration-300"
                :class="entry.product_type === 'graft' ? 'bg-blue-50 dark:bg-blue-900/30 group-hover:bg-blue-600 dark:group-hover:bg-blue-500' : 'bg-purple-50 dark:bg-purple-900/30 group-hover:bg-purple-600 dark:group-hover:bg-purple-500'">
                <Package class="w-6 h-6 transition-colors duration-300" :class="entry.product_type === 'graft' ? 'text-blue-600 dark:text-blue-400 group-hover:text-white' : 'text-purple-600 dark:text-purple-400 group-hover:text-white'" />
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white font-mono tracking-tight truncate">{{ entry.serial_number }}</h3>
                <div class="mt-1.5">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium leading-none" :class="getStatusBadgeClass(entry.status)">{{ entry.status_label }}</span>
                </div>
              </div>
            </div>

            <!-- Stat Pill: Clinic or Product Type -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                <MapPin v-if="entry.product_type === 'graft'" class="w-4 h-4 text-blue-500" />
                <Tag v-else class="w-4 h-4 text-purple-500" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ entry.product_type === 'other_product' ? 'Product Type' : 'Clinic' }}</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ entry.product_type === 'other_product' ? 'Other Product' : entry.clinic_name }}</p>
              </div>
              <ChevronRight class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-red-500 group-hover:translate-x-1 transition-all duration-300 flex-shrink-0" />
            </div>
          </button>

          <!-- Action Footer -->
          <div class="px-5 pb-4 flex items-center gap-2">
            <button type="button" @click="openEditModal(entry)"
              class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
              <Pencil class="w-4 h-4" />
              Edit
            </button>
            <button v-if="!isOfficeStaff" type="button" @click="confirmDelete(entry)"
              class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
              <Trash2 class="w-4 h-4" />
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Desktop Pagination -->
      <div v-if="ledgerEntries.length > 0" class="mt-5 flex items-center justify-between gap-3 px-3 py-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-2 px-2">
          <label class="text-xs text-gray-500 dark:text-gray-400">Per page</label>
          <select v-model="itemsPerPage" @change="currentPage = 1; fetchLedger()"
            class="text-xs bg-gray-50 dark:bg-gray-700/50 border-0 text-gray-700 dark:text-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 px-2 py-1 cursor-pointer">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <span class="text-xs text-gray-400 dark:text-gray-500 ml-3 tabular-nums">{{ totalResults }} total</span>
        </div>
        <nav class="flex items-center gap-2">
          <!-- Previous -->
          <button
            class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            :disabled="currentPage <= 1"
            @click="goToPage(currentPage - 1)"
          >
            Previous
          </button>

          <!-- Page Numbers -->
          <div class="flex items-center gap-1">
            <template v-for="(page, index) in visiblePages" :key="index">
              <span v-if="page === '...'" class="px-2 py-1.5 text-sm text-gray-500 dark:text-gray-400">...</span>
              <button
                v-else
                class="border px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                :class="page === currentPage
                  ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                  : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600 hover:bg-indigo-50 dark:hover:bg-gray-700 hover:text-blue-600'"
                @click="goToPage(page as number)"
              >
                {{ page }}
              </button>
            </template>
          </div>

          <!-- Next -->
          <button
            class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
          >
            Next
          </button>
        </nav>
      </div>
    </div>

    <!-- Entry Type Selection Modal -->
    <EntryTypeModal
      v-model="showEntryTypeModal"
      @select-manual="selectManualEntry"
      @select-upload="selectUploadEntry"
    />

    <!-- Create/Edit Modal -->
    <LedgerFormModal
      v-model="showModal"
      :is-editing="isEditing"
      :form-data="form"
      :form-errors="formErrors"
      :clinics="clinics"
      :brands="brands"
      :other-products="otherProducts"
      :graft-size-products="graftSizeProducts"
      :invoices="invoices"
      :status-options="statusOptions"
      :order-search-query="orderSearchQuery"
      :order-suggestions="orderSuggestions"
      :show-order-dropdown="showOrderDropdown"
      :is-searching-orders="isSearchingOrders"
      :is-saving="isSaving"
      @close="closeModal"
      @save="saveEntry"
      @search-orders="performOrderSearch"
      @select-order="selectOrder"
      @clear-order="clearOrder"
    />

    <!-- View Details Modal -->
    <LedgerViewModal
      v-model="showViewModal"
      :entry="selectedEntry"
      @edit="openEditModal"
    />

    <!-- Delete Confirmation Modal -->
    <DeleteConfirmModal
      v-model="showDeleteModal"
      :entry="entryToDelete"
      :is-deleting="isDeleting"
      @confirm="deleteEntry"
    />


    <!-- Upload File Modal -->
    <UploadModal
      v-model="showUploadModal"
      :upload-file="uploadFile"
      :is-uploading="isUploading"
      :upload-results="uploadResults"
      :upload-error="uploadError"
      :is-downloading-template="isDownloadingTemplate"
      @close="closeUploadModal"
      @process-upload="processUpload"
      @download-template="downloadTemplate"
      @clear-error="uploadError = null"
      @update:upload-file="uploadFile = $event"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useAppToast } from '@/composables/ui/useAppToast'
import EntryTypeModal from '@/components/Inventory/modals/EntryTypeModal.vue'
import LedgerFormModal from '@/components/Inventory/modals/LedgerFormModal.vue'
import LedgerViewModal from '@/components/Inventory/modals/LedgerViewModal.vue'
import DeleteConfirmModal from '@/components/Inventory/modals/DeleteConfirmModal.vue'
import UploadModal from '@/components/Inventory/modals/UploadModal.vue'
import {
  Search, X, Plus, BarChart3, Package, CheckCircle2, AlertCircle, Activity,
  Pencil, Trash2, SlidersHorizontal, Eye,
  MapPin, DollarSign, ClipboardList, Tag, ChevronDown,
  ChevronLeft, ChevronRight, Funnel, Clock
} from 'lucide-vue-next'
import { inventoryLedgerService, userService, brandService } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

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

// Auth
const authStore = useAuthStore()
const appToast = useAppToast()
const isOfficeStaff = computed(() => authStore.currentUser?.user_role === 1)
const toast = useAppToast()

// State
const ledgerEntries = ref<LedgerEntry[]>([])
const isLoading = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)
const showStats = ref(false)
const showModal = ref(false)
const showEntryTypeModal = ref(false)
const showDeleteModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const entryToDelete = ref<LedgerEntry | null>(null)
const selectedEntry = ref<LedgerEntry | null>(null)
const listContainer = ref<HTMLElement | null>(null)

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

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value || page === currentPage.value) return
  currentPage.value = page
  fetchLedger()
}

// Compute visible page numbers with ellipsis (matches Pagination.vue)
const visiblePages = computed(() => {
  const current = currentPage.value
  const last = totalPages.value
  const pages: (number | string)[] = []

  if (last <= 7) {
    for (let i = 1; i <= last; i++) pages.push(i)
  } else {
    pages.push(1)
    if (current <= 3) {
      for (let i = 2; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(last)
    } else if (current >= last - 2) {
      pages.push('...')
      for (let i = last - 4; i <= last; i++) pages.push(i)
    } else {
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(last)
    }
  }

  return pages
})

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
const stats = ref({ total: 0, paid: 0, unpaid: 0, used: 0 })

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
  0: 'Expected', 1: 'Delivered', 2: 'Used',
  3: 'Partially Used', 4: 'Reassigned', 5: 'Unused', 6: 'Expired',
}

const filteredGraftProducts = computed(() => {
  if (!form.value.brand_id) return []
  return graftSizeProducts.value.filter((prod) => String(prod.brand_id) === String(form.value.brand_id))
})

const hasActiveFilters = computed(() => {
  return statusFilter.value !== 'all' || brandFilter.value !== 'all' || clinicFilter.value !== 'all' || invoiceStatusFilter.value !== 'all'
})

// ─── Selection ───────────────────────────────────────────────
function selectEntry(entry: LedgerEntry) {
  selectedEntry.value = entry
}

// ─── Helpers ─────────────────────────────────────────────────
function getBrandName(id: string): string {
  const brand = brands.value.find((b) => String(b.brand_id) === String(id))
  return brand?.brandName ?? id
}

function getClinicName(id: string): string {
  const clinic = clinics.value.find((c) => String(c.clinic_id) === String(id))
  return clinic?.name ?? id
}

function formatDateTime(dateStr: string): string {
  if (!dateStr) return 'No Records Found'
  const date = new Date(dateStr)
  return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// ─── Status classes ──────────────────────────────────────────
function getStatusBadgeClass(status: number): string {
  const c: Record<number, string> = {
    0: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    1: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    2: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    3: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    4: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    5: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    6: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
  }
  return c[status] || c[1]
}

function getStatusAccentClass(status: number): string {
  const c: Record<number, string> = {
    0: 'bg-gray-400', 1: 'bg-blue-500', 2: 'bg-green-500',
    3: 'bg-amber-500', 4: 'bg-purple-500', 5: 'bg-gray-400', 6: 'bg-red-500',
  }
  return c[status] || c[1]
}

function getStatusPillActiveClass(status: number): string {
  const c: Record<number, string> = {
    0: 'bg-gray-500 border-gray-500 text-white shadow-md shadow-gray-200/50 dark:shadow-gray-900/30 scale-[1.02]',
    1: 'bg-blue-500 border-blue-500 text-white shadow-md shadow-blue-200/50 dark:shadow-blue-900/30 scale-[1.02]',
    2: 'bg-green-500 border-green-500 text-white shadow-md shadow-green-200/50 dark:shadow-green-900/30 scale-[1.02]',
    3: 'bg-amber-500 border-amber-500 text-white shadow-md shadow-amber-200/50 dark:shadow-amber-900/30 scale-[1.02]',
    4: 'bg-purple-500 border-purple-500 text-white shadow-md shadow-purple-200/50 dark:shadow-purple-900/30 scale-[1.02]',
    5: 'bg-gray-500 border-gray-500 text-white shadow-md shadow-gray-200/50 dark:shadow-gray-900/30 scale-[1.02]',
    6: 'bg-red-500 border-red-500 text-white shadow-md shadow-red-200/50 dark:shadow-red-900/30 scale-[1.02]',
  }
  return c[status] || c[1]
}

function getStatusPillInactiveClass(status: number): string {
  const c: Record<number, string> = {
    0: 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:border-gray-300 dark:hover:border-gray-500',
    1: 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-700',
    2: 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/20 hover:border-green-300 dark:hover:border-green-700',
    3: 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/20 hover:border-amber-300 dark:hover:border-amber-700',
    4: 'bg-purple-50 dark:bg-purple-900/10 border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/20 hover:border-purple-300 dark:hover:border-purple-700',
    5: 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:border-gray-300 dark:hover:border-gray-500',
    6: 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-700',
  }
  return c[status] || c[1]
}

function getStatusDotColor(status: number): string {
  const c: Record<number, string> = {
    0: '#9ca3af', 1: '#3b82f6', 2: '#22c55e', 3: '#f59e0b', 4: '#a855f7', 5: '#9ca3af', 6: '#ef4444',
  }
  return c[status] || c[1]
}

// ─── Order search ────────────────────────────────────────────
async function performOrderSearch() {
  clearTimeout(orderSearchTimeout)
  const query = orderSearchQuery.value.trim()
  if (!query) { orderSuggestions.value = []; showOrderDropdown.value = false; return }
  orderSearchTimeout = setTimeout(async () => {
    isSearchingOrders.value = true
    try {
      const response = await inventoryLedgerService.searchOrders(query, 10)
      orderSuggestions.value = response.data.data || []
      showOrderDropdown.value = orderSuggestions.value.length > 0
    } catch { orderSuggestions.value = [] }
    finally { isSearchingOrders.value = false }
  }, 300)
}

function selectOrder(order: any) {
  form.value.order_id = order.id; orderSearchQuery.value = order.display; showOrderDropdown.value = false; orderSuggestions.value = []
}
function clearOrder() { form.value.order_id = ''; orderSearchQuery.value = ''; showOrderDropdown.value = false; orderSuggestions.value = [] }
function hideOrderDropdown() { setTimeout(() => { showOrderDropdown.value = false }, 200) }
function onOrderInputFocus() { if (orderSuggestions.value.length > 0) showOrderDropdown.value = true }

// ─── Form ────────────────────────────────────────────────────
function onProductTypeChange() { form.value.product_id = ''; form.value.brand_id = ''; form.value.clinic_id = '' }

function resetForm() {
  form.value = { ledger_id: null, serial_number: '', product_type: 'graft', product_id: '', brand_id: '', clinic_id: '', order_id: '', status: 1, is_used: false, graft_usage_id: '', invoice_status: 'unpaid', invoice_id: '', notes: '' }
  formErrors.value = {}; orderSearchQuery.value = ''; orderSuggestions.value = []; showOrderDropdown.value = false
}

function openCreateModal() { resetForm(); isEditing.value = false; showModal.value = true }

function openEntryTypeModal() { showEntryTypeModal.value = true }

function selectManualEntry() { showEntryTypeModal.value = false; openCreateModal() }

function openViewModal(entry: LedgerEntry) {
  selectedEntry.value = entry
  showViewModal.value = true
}

function openEditModal(entry: LedgerEntry) {
  resetForm()
  form.value = {
    ledger_id: entry.ledger_id, serial_number: entry.serial_number, product_type: entry.product_type,
    product_id: String(entry.product_id), brand_id: entry.brand_id ?? '', clinic_id: String(entry.clinic_id),
    order_id: entry.order_id ?? '', status: entry.status, is_used: entry.is_used,
    graft_usage_id: entry.graft_usage_id ?? '', invoice_status: entry.invoice_status,
    invoice_id: entry.invoice_id ?? '', notes: entry.notes ?? '',
  }
  if (entry.order_number || entry.order_code) orderSearchQuery.value = entry.order_number ?? entry.order_code ?? ''
  isEditing.value = true; showModal.value = true
}

function closeModal() { showModal.value = false; resetForm() }

function confirmDelete(entry: LedgerEntry) { entryToDelete.value = entry; showDeleteModal.value = true }

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

// ─── Save / Delete ───────────────────────────────────────────
async function saveEntry() {
  if (!validateForm()) return
  isSaving.value = true
  try {
    const payload = {
      serial_number: form.value.serial_number.trim(), product_type: form.value.product_type,
      product_id: Number(form.value.product_id), brand_id: form.value.brand_id ? Number(form.value.brand_id) : null,
      clinic_id: form.value.clinic_id ? Number(form.value.clinic_id) : null, order_id: form.value.order_id ? Number(form.value.order_id) : null,
      status: form.value.status, is_used: form.value.is_used, graft_usage_id: form.value.graft_usage_id.trim() || null,
      invoice_status: form.value.invoice_status, invoice_id: form.value.invoice_id ? Number(form.value.invoice_id) : null,
      notes: form.value.notes.trim() || null,
    }
    if (isEditing.value && form.value.ledger_id) {
      await inventoryLedgerService.update(form.value.ledger_id, payload)
      toast.success('Inventory entry details were updated successfully.')
    } else {
      await inventoryLedgerService.create(payload)
      toast.success('Inventory entry was created successfully.')
    }
    closeModal(); await fetchLedger(); await fetchStats()
  } catch (error: any) {
    console.error('Save error:', error)
    const fieldErrors = error.response?.data?.errors
    if (fieldErrors && typeof fieldErrors === 'object') {
      const mapped: Record<string, string> = {}
      Object.keys(fieldErrors).forEach((key) => {
        const val = fieldErrors[key]
        mapped[key] = Array.isArray(val) ? val[0] : String(val)
      })
      formErrors.value = { ...formErrors.value, ...mapped }
      // Field-level errors are shown inline; skip the toast popup.
    } else {
      toast.error(error.response?.data?.message || 'Failed to save ledger entry.')
    }
  } finally { isSaving.value = false }
}

async function deleteEntry() {
  if (!entryToDelete.value) return
  isDeleting.value = true
  try {
    await inventoryLedgerService.delete(entryToDelete.value.ledger_id)
    showDeleteModal.value = false
    if (selectedEntry.value?.ledger_id === entryToDelete.value.ledger_id) selectedEntry.value = null
    entryToDelete.value = null
    toast.success('Inventory entry was permanently deleted.')
    await fetchLedger(); await fetchStats()
  } catch (error: any) {
    console.error('Delete error:', error)
    toast.error(error.response?.data?.message || 'Failed to delete ledger entry.')
  } finally { isDeleting.value = false }
}

// ─── Data fetching ───────────────────────────────────────────
async function fetchLedger() {
  isLoading.value = true
  try {
    const params: any = { page: currentPage.value, per_page: itemsPerPage.value }
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
    if (selectedEntry.value && !ledgerEntries.value.some(e => e.ledger_id === selectedEntry.value!.ledger_id)) {
      selectedEntry.value = null
    }
  } catch (error: any) {
    console.error('Failed to fetch ledger:', error)
    toast.error(error.response?.data?.message || 'Failed to load inventory entries.')
  } finally { isLoading.value = false }
}

async function fetchStats() {
  try { const response = await inventoryLedgerService.getStats(); stats.value = response.data.data || { total: 0, paid: 0, unpaid: 0, used: 0 } }
  catch (error) { console.error('Failed to fetch stats:', error) }
}

async function fetchInitData() {
  try {
    const response = await inventoryLedgerService.getInitData()
    const data = response.data.data || {}
    brands.value = (data.brands || []).map((b: any) => ({ brand_id: b.id, brandName: b.name }))
    clinics.value = (data.clinics || []).map((c: any) => ({ clinic_id: c.id, name: c.name }))
    graftSizeProducts.value = data.graft_sizes || []
    otherProducts.value = data.other_products || []
    invoices.value = data.invoices || []
    stats.value = data.stats || { total: 0, paid: 0, unpaid: 0, used: 0 }
  } catch (error) { console.error('Failed to fetch init data:', error) }
}

async function fetchBrands() {
  try {
    const response = await brandService.getAllBrands({ per_page: 1000 })
    const brandRows = Array.isArray(response.data?.brandData) ? response.data.brandData : Array.isArray(response.data?.data) ? response.data.data : []
    brands.value = brandRows
  } catch (error) { console.error('Failed to fetch brands:', error) }
}

async function fetchClinics() {
  try {
    const response = await userService.getClinics()
    const clinicRows = Array.isArray(response.data?.user_data) ? response.data.user_data : Array.isArray(response.data?.data) ? response.data.data : []
    clinics.value = clinicRows
  } catch (error) { console.error('Failed to fetch clinics:', error) }
}

async function fetchProducts() {
  try { const response = await inventoryLedgerService.getProducts(); const data = response.data.data || {}; graftSizeProducts.value = data.graft_sizes || []; otherProducts.value = data.other_products || [] }
  catch (error) { console.error('Failed to fetch products:', error) }
}

async function fetchInvoices() {
  try { const response = await inventoryLedgerService.getInvoices(); invoices.value = response.data.data || [] }
  catch (error) { console.error('Failed to fetch invoices:', error) }
}

// ─── Watchers ────────────────────────────────────────────────
let searchTimeout: ReturnType<typeof setTimeout>
watch(searchTerm, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => { currentPage.value = 1; fetchLedger() }, 300) })
watch([statusFilter, brandFilter, clinicFilter, invoiceStatusFilter], () => { currentPage.value = 1; fetchLedger() })

onMounted(async () => { await Promise.all([fetchLedger(), fetchInitData()]) })

// Upload state
const showUploadModal = ref(false)
const uploadFile = ref<File | null>(null)
const isUploading = ref(false)
const uploadResults = ref<any>(null)
const uploadError = ref<string | null>(null)
const isDownloadingTemplate = ref(false)

function selectUploadEntry() {
  showEntryTypeModal.value = false
  showUploadModal.value = true
  uploadFile.value = null
  uploadResults.value = null
  uploadError.value = null
}

async function processUpload() {
  if (!uploadFile.value) return
  
  isUploading.value = true
  uploadResults.value = null
  uploadError.value = null
  
  try {
    const formData = new FormData()
    formData.append('file', uploadFile.value)
    
    const response = await inventoryLedgerService.import(formData)
    uploadResults.value = response.data.data
    
    if (uploadResults.value.successful > 0) {
      await fetchLedger()
      await fetchStats()
    }
  } catch (error: any) {
    console.error('Upload error:', error)
    uploadError.value = error.response?.data?.message || 'Failed to process the file. Please check the file format and try again.'
  } finally {
    isUploading.value = false
  }
}

function closeUploadModal() {
  showUploadModal.value = false
  uploadFile.value = null
  uploadResults.value = null
  uploadError.value = null
}

async function downloadTemplate() {
  isDownloadingTemplate.value = true
  try {
    const response = await inventoryLedgerService.downloadTemplate()
    const blob = new Blob([response.data], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'inventory_ledger_import_template.xlsx'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error: any) {
    console.error('Template download error:', error)
    toast.error(error.response?.data?.message || 'Failed to download template.', 'Download failed')
  } finally {
    isDownloadingTemplate.value = false
  }
}

</script>

<style scoped>
.overflow-y-auto::-webkit-scrollbar { width: 5px }
.overflow-y-auto::-webkit-scrollbar-track { background: transparent }
.overflow-y-auto::-webkit-scrollbar-thumb { background-color: rgb(212 212 212 / 0.6); border-radius: 3px }
.overflow-y-auto::-webkit-scrollbar-thumb:hover { background-color: rgb(163 163 163 / 0.8) }
.dark .overflow-y-auto::-webkit-scrollbar-thumb { background-color: rgb(64 64 64 / 0.6) }
.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover { background-color: rgb(82 82 82 / 0.8) }
</style>