<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
            </div>
            <div class="flex items-center gap-4">
                <button @click="showStats = !showStats"
                    class="flex items-center px-5 py-3 bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">
                    <BarChart2 class="w-5 h-5 mr-2" />
                    {{ showStats ? 'Hide' : 'Show' }} Stats
                </button>
                <button @click="showCreateForm = true"
                    class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group">
                    <Plus class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
                    Create User
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Statistics</h3>

                <!-- Main Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <Users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Users</p>
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

                <!-- Role Breakdown -->
                <div class="mt-6">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Role Breakdown</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4">
                        <div v-if="!isOfficeStaff" class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <ShieldCheck class="w-4 h-4 mr-2 text-purple-500" /> Admins
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ stats.roles.admin }}</span>
                        </div>
                        <div v-if="!isOfficeStaff" class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Briefcase class="w-4 h-4 mr-2 text-blue-500" /> Office Staff
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ stats.roles['office-staff']
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Building class="w-4 h-4 mr-2 text-teal-500" /> Clinics
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ stats.roles.clinic }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Stethoscope class="w-4 h-4 mr-2 text-green-500" /> Clinicians
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ stats.roles.clinician }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <Factory class="w-4 h-4 mr-2 text-orange-500" /> Manufacturers
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ stats.roles.manufacturer
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <FileText class="w-4 h-4 mr-2 text-pink-500" /> Billers
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ stats.roles.biller }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-800 p-4 lg:p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <!-- Mobile: Stacked Layout -->
            <div class="lg:hidden space-y-3">
                <!-- Search Input -->
                <div class="relative">
                    <Search class="absolute left-3.5 top-3 h-5 w-5 text-gray-400 dark:text-gray-500" />
                    <input v-model="searchTerm" type="text" placeholder="Search users..."
                        class="w-full pl-11 pr-10 py-2.5 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white text-sm transition-all duration-200" />
                    <button v-if="searchTerm" @click="searchTerm = ''" class="absolute right-3 top-2.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <X class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Filter Pills Row -->
                <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-hide">
                    <button @click="showFilters = !showFilters" 
                        class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">
                        <SlidersHorizontal class="w-3.5 h-3.5" />
                        Filters
                        <span v-if="roleFilter !== 'all' || statusFilter !== 'all'" class="w-2 h-2 bg-blue-500 rounded-full"></span>
                    </button>
                    <span v-if="roleFilter !== 'all'" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs">
                        {{ roleFilter === 'office-staff' ? 'Office' : roleFilter.charAt(0).toUpperCase() + roleFilter.slice(1) }}
                        <button @click="roleFilter = 'all'" class="hover:text-blue-900">
                            <X class="w-3 h-3" />
                        </button>
                    </span>
                    <span v-if="statusFilter !== 'all'" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg text-xs">
                        {{ statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1) }}
                        <button @click="statusFilter = 'all'" class="hover:text-green-900">
                            <X class="w-3 h-3" />
                        </button>
                    </span>
                </div>
                
                <!-- Expandable Filters -->
                <div v-if="showFilters" class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Role</label>
                        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                            <button @click="roleFilter = 'all'" 
                                :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', roleFilter === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                All
                            </button>
                            <button v-if="!isOfficeStaff" @click="roleFilter = 'admin'" 
                                :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', roleFilter === 'admin' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Admin
                            </button>
                            <button v-if="!isOfficeStaff" @click="roleFilter = 'office-staff'" 
                                :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', roleFilter === 'office-staff' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Office
                            </button>
                            <button @click="roleFilter = 'clinic'" 
                                :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', roleFilter === 'clinic' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Clinic
                            </button>
                            <button @click="roleFilter = 'clinician'" 
                                :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', roleFilter === 'clinician' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Clinician
                            </button>
                            <button @click="roleFilter = 'manufacturer'" 
                                :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', roleFilter === 'manufacturer' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Mfg
                            </button>
                            <button @click="roleFilter = 'biller'" 
                                :class="['flex-shrink-0 py-2 px-3 rounded-lg text-xs font-medium transition-colors', roleFilter === 'biller' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Biller
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                        <div class="flex gap-2">
                            <button @click="statusFilter = 'all'" 
                                :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors', statusFilter === 'all' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                All
                            </button>
                            <button @click="statusFilter = 'active'" 
                                :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors', statusFilter === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Active
                            </button>
                            <button @click="statusFilter = 'inactive'" 
                                :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors', statusFilter === 'inactive' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Inactive
                            </button>
                            <button @click="statusFilter = 'archived'" 
                                :class="['flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors', statusFilter === 'archived' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">
                                Archived
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
                        <input v-model="searchTerm" type="text" placeholder="Search by name or email..."
                            class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative">
                        <Filter class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
                        <select v-model="roleFilter"
                            class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="all">All Roles</option>
                            <option v-if="!isOfficeStaff" value="admin">Admin</option>
                            <option v-if="!isOfficeStaff" value="office-staff">Office Staff</option>
                            <option value="clinic">Clinics</option>
                            <option value="clinician">Clinicians</option>
                            <option value="manufacturer">Manufacturer</option>
                            <option value="biller">Biller</option>
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                    <div class="relative">
                        <select v-model="statusFilter"
                            class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="archived">Archived</option>
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden space-y-4">
            <div v-if="tableLoader" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-8 h-8 rounded-full border-4 border-gray-200 border-t-red-600 animate-spin"></div>
                    <div class="text-center text-gray-400 py-4 text-sm">Fetching Users</div>
                </div>
            </div>
            <template v-else>
                <div v-if="filteredUsers.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <Users class="w-10 h-10 mb-1 text-gray-400" />
                        <span class="text-gray-400">No users found.</span>
                    </div>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="user in paginatedUsers" :key="user.id"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <!-- Card Header -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mb-0.5">User Name</p>
                                    <p class="text-gray-900 dark:text-white font-semibold truncate">{{ formatFullName(user) }}</p>
                                </div>
                                <div class="flex items-center gap-2 ml-3">
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-white/60 dark:bg-white/10', 
                                        user.isArchived ? 'text-orange-700 dark:text-orange-300' :
                                            user.isActive ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300']">
                                        {{ user.isArchived ? 'Archived' : (user.isActive ? 'Active' : 'Inactive') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 space-y-3">
                            <!-- Email & Role Row -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Email</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ user.email }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Role</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatRoleName(user.role) }}</p>
                                </div>
                            </div>

                            <!-- Created Date -->
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Created Date</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(user.createdAt) }}</p>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="px-4 pt-2 pb-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="handleViewUser(user)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                    <Eye class="w-5 h-5" />
                                    View
                                </button>
                                <button v-if="canManageUser(user)" @click="editUser(user)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                                    <FilePenLine class="w-5 h-5" />
                                    Edit
                                </button>
                                <button v-if="canManageUser(user) && !user.isArchived" @click="handleArchiveUser(user.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors">
                                    <Archive class="w-5 h-5" />
                                    Archive
                                </button>
                                <button v-else-if="!isOfficeStaff" @click="handleDeleteUser(user.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                    <Trash2 class="w-5 h-5" />
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Mobile Pagination -->
            <Pagination
                v-if="pagination.total > 0"
                :pagination="pagination"
                @update:page="goToPage"
            />
        </div>

        <!-- Users Table Card (Desktop) -->
        <div
            class="hidden lg:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                User
                            </th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Role
                            </th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Created
                            </th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <TableLoader v-if="tableLoader" :colspan="5" />
                        <template v-else>
                            <tr v-for="user in paginatedUsers" :key="user.id"
                                class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 font-medium text-sm">
                                            {{ getUserInitials(user) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ formatFullName(user) }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center">
                                    <span
                                        :class="['inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200', getRoleColor(user.role)]">
                                        {{ formatRoleName(user.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-center">
                                    <span :class="['inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200',
                                        user.isArchived ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400' :
                                            user.isActive ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' :
                                                'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400']">
                                        <component
                                            :is="user.isArchived ? Archive : (user.isActive ? CheckCircle2 : XCircle)"
                                            class="w-3 h-3 mr-1.5" />
                                        {{ user.isArchived ? 'Archived' : (user.isActive ? 'Active' : 'Inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    {{ formatDate(user.createdAt) }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button @click="handleViewUser(user)"
                                            class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-400 rounded-lg transition-all duration-200"
                                            title="View Details">
                                            <Eye class="w-5 h-5" />
                                        </button>
                                        <button v-if="canManageUser(user)" @click="editUser(user)"
                                            class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-amber-100 dark:hover:bg-amber-900/30 hover:text-amber-700 dark:hover:text-amber-400 rounded-lg transition-all duration-200"
                                            title="Edit User">
                                            <FilePenLine class="w-5 h-5" />
                                        </button>
                                        <button v-if="canManageUser(user)" @click="handleToggleStatus(user.id)" :class="[
                                            'inline-flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-200',
                                            user.isActive
                                                ? 'text-gray-400 dark:text-gray-500 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30'
                                                : 'text-gray-400 dark:text-gray-500 hover:text-green-700 dark:hover:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30'
                                        ]" :title="user.isActive ? 'Deactivate' : 'Activate'">
                                            <component :is="user.isActive ? XCircle : CheckCircle2" class="w-5 h-5" />
                                        </button>
                                        <button v-if="canManageUser(user) && !user.isArchived" @click="handleArchiveUser(user.id)"
                                            class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:text-orange-700 dark:hover:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-900/30 rounded-lg transition-all duration-200"
                                            title="Archive User">
                                            <Archive class="w-5 h-5" />
                                        </button>
                                        <button v-else-if="!isOfficeStaff" @click="handleDeleteUser(user.id)"
                                            class="inline-flex items-center justify-center w-9 h-9 text-gray-400 dark:text-gray-500 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-700 dark:hover:text-red-400 rounded-lg transition-all duration-200"
                                            title="Delete User">
                                            <Trash2 class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredUsers.length === 0 && !tableLoader">
                                <td colspan="5" class="text-center text-gray-400 py-12">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <Users class="w-10 h-10 mb-1" />
                                        <span>No users found.</span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="pagination.total > 0"
                :pagination="pagination"
                @update:page="goToPage"
            />
        </div>

        <!-- User Details Modal -->
        <UserDetailsModal
            v-model="showUserDetailsModal"
            :user="selectedUser"
            :associated-clinicians="associatedClinicians"
            :is-office-staff="isOfficeStaff"
            @edit="editUser"
            @toggle-status="handleToggleStatus"
            @archive="handleArchiveUser"
            @delete="handleDeleteUser"
            @send-reset-link="handleSendPasswordResetLink"
        />

        <!-- Create/Edit User Form Modal -->
        <UserFormModal
            v-model="showFormModal"
            :is-create="showCreateForm"
            :form-data="formData"
            :selected-user="selectedUser"
            :departments="departments"
            :manufacturers="manufacturers"
            :is-office-staff="isOfficeStaff"
            :current-user-role="currentUserRole"
            :current-user-clinic-id="currentUserClinicId"
            :current-user-id="currentUserId"
            :is-submitting="isSubmitting"
            @submit="handleSubmitForm"
            @cancel="closeForm"
        />

        <!-- Sudo Confirm Delete Modal -->
        <SudoConfirmModal
            ref="sudoDeleteModalRef"
            v-model="showSudoDeleteModal"
            title="Confirm User Deletion"
            :message="sudoDeleteMessage"
            confirm-button-text="Authorize & Delete"
            variant="danger"
            :is-loading="isDeletingUser"
            @confirm="onSudoDeleteConfirm"
            @cancel="onSudoDeleteCancel"
        />

        <!-- Sudo Confirm Admin Role Modal -->
        <SudoConfirmModal
            ref="sudoAdminModalRef"
            v-model="showSudoAdminModal"
            title="Sudo Authorization Required"
            message="Creating or promoting a user to <strong>Administrator</strong> grants full administrative access.<br><br>Please enter your current account password to authorize this action."
            confirm-button-text="Authorize & Proceed"
            variant="warning"
            :is-loading="isSubmitting"
            @confirm="onSudoAdminConfirm"
            @cancel="onSudoAdminCancel"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import Pagination from '@/components/ui/Pagination.vue'
import UserDetailsModal from '@/components/users/modals/UserDetailsModal.vue'
import UserFormModal from '@/components/users/modals/UserFormModal.vue'
import SudoConfirmModal from '@/components/users/modals/SudoConfirmModal.vue'
import { userService } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { confirmAction } from '@/services/alert'
import { useAppToast } from '@/composables/ui/useAppToast'
import Swal from 'sweetalert2'
import {
    Plus,
    Search,
    Filter,
    Eye,
    FilePenLine,
    CheckCircle2,
    XCircle,
    Trash2,
    ChevronDown,
    Clock,
    Users,
    Archive,
    Factory,
    FileText,
    Building,
    BarChart2,
    ShieldCheck,
    Briefcase,
    Stethoscope,
    SlidersHorizontal,
    X
} from 'lucide-vue-next'

interface User {
    id: string
    email: string
    firstName: string
    middleName?: string
    lastName: string
    phone?: string
    role: 'admin' | 'office-staff' | 'clinic' | 'clinician' | 'manufacturer' | 'biller'
    department?: string
    clinicId?: string
    clinicCode?: string
    manufacturerId?: string
    isActive: boolean
    isArchived: boolean
    lastLogin?: string
    createdAt: string
}

const departments = ref<{ id: string; name: string }[]>([])

const manufacturers = ref<{ id: string; name: string }[]>([])

const users = ref<User[]>([])
const authStore = useAuthStore()
const toast = useAppToast()

const searchTerm = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')
const showFilters = ref(false)
const tableLoader = ref(false)
const selectedUser = ref<User | null>(null)
const associatedCliniciansData = ref<User[]>([])
const showCreateForm = ref(false)
const showEditForm = ref(false)
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalResults = ref(0)
const showStats = ref(false)
const isSubmitting = ref(false)

// Current User context and role hierarchy
const currentUserRole = computed(() => authStore.currentUser?.user_role ?? -1)
const isOfficeStaff = computed(() => currentUserRole.value === 1)
const isClinicAdmin = computed(() => currentUserRole.value === 2)
const currentUserClinicId = computed(() => authStore.currentUser?.clinic_id ? String(authStore.currentUser.clinic_id) : '')
const currentUserId = computed(() => authStore.currentUser?.id ? String(authStore.currentUser.id) : '')

const canManageUser = (user: User) => {
    if (isOfficeStaff.value) {
        return user.role !== 'admin' && user.role !== 'office-staff'
    }
    if (isClinicAdmin.value) {
        return user.role === 'clinician'
    }
    return true
}

const getInitialRole = (): User['role'] => {
    if (isClinicAdmin.value) return 'clinician'
    if (isOfficeStaff.value) return 'clinic'
    return 'office-staff'
}

const defaultFormData = {
    firstName: '',
    middleName: '',
    lastName: '',
    email: '',
    phone: '',
    role: 'office-staff' as User['role'],
    isActive: true,
    clinicId: '',
    manufacturerId: '',
}

const formData = ref({
    ...defaultFormData,
    role: getInitialRole(),
    clinicId: isClinicAdmin.value ? currentUserClinicId.value : '',
});

function escapeHtml(value: string) {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
}

// Global stats from server (not limited by current page)
const serverStats = ref({
    total: 0,
    active: 0,
    inactive: 0,
    archived: 0,
    roles: {
        admin: 0,
        'office-staff': 0,
        clinic: 0,
        clinician: 0,
        manufacturer: 0,
        biller: 0,
    }
})

const stats = computed(() => serverStats.value)

watch(() => formData.value.role, (newRole) => {
    // Clear clinic ID when role changes away from clinic or clinician
    if (newRole !== 'clinic' && newRole !== 'clinician') {
        formData.value.clinicId = '';
    }
    // Clear manufacturer ID when role changes away from manufacturer
    if (newRole !== 'manufacturer') {
        formData.value.manufacturerId = '';
    }
});

// Watch for when a clinic admin user is selected to view details
watch(() => selectedUser.value, (newUser) => {
    if (newUser && newUser.role === 'clinic' && newUser.clinicId) {
        // Fetch all clinicians for this clinic facility
        fetchCliniciansForClinic(newUser.clinicId)
    } else {
        // Clear clinicians data when not viewing a clinic admin
        associatedCliniciansData.value = []
    }
}, { immediate: true });

const associatedClinicians = computed(() => associatedCliniciansData.value)

// Fetch clinicians for a specific clinic
const fetchCliniciansForClinic = async (clinicId: string) => {
    try {
        // Fetch all clinicians (no pagination) for this clinic
        const { data } = await userService.getUsers({
            role: 3, // clinician role
            status: 'all'
        })
        const incoming: any[] = Array.isArray(data.users) ? data.users : []
        const allClinicians = incoming.map((u) => ({
            id: String(u.id),
            email: u.email,
            firstName: u.firstName ?? u.first_name ?? '',
            middleName: u.middleName ?? u.middle_name ?? '',
            lastName: u.lastName ?? u.last_name ?? '',
            phone: u.phone ?? '',
            role: normalizeRole(u.role),
            department: u.department ?? undefined,
            clinicId: u.clinicId ?? u.clinic_id ?? undefined,
            manufacturerId: u.manufacturerId ?? u.manufacturer_id ?? undefined,
            isActive: Boolean(u.isActive),
            isArchived: Boolean(u.isArchived ?? false),
            lastLogin: u.lastLogin ?? undefined,
            createdAt: u.createdAt ?? u.created_at ?? new Date().toISOString(),
        } as User))

        // Filter by clinic ID
        associatedCliniciansData.value = allClinicians.filter(c => c.clinicId === clinicId)

        console.log('Clinic ID:', clinicId)
        console.log('All fetched clinicians:', allClinicians.map(c => ({ id: c.id, name: formatFullName(c), clinicId: c.clinicId })))
        console.log('Filtered clinicians for this clinic:', associatedCliniciansData.value.map(c => ({ id: c.id, name: formatFullName(c), clinicId: c.clinicId })))
    } catch (err) {
        console.error('Failed to fetch clinicians', err)
        associatedCliniciansData.value = []
    }
}

async function handleToggleStatus(userId: string) {
    const user = users.value.find(u => u.id === userId)
    if (!user) return

    const action = user.isActive ? 'deactivate' : 'activate'

    const result = await confirmAction({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`,
        text: `Are you sure you want to ${action} ${formatFullName(user)}?`,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: `Yes, ${action} it!`,
        cancelButtonText: 'Cancel'
    })

    if (!result.isConfirmed) return

    try {
        const { data } = await userService.toggleUserStatus(userId)
        const updated = data.user
        const mapped = {
            id: String(updated.id),
            email: updated.email,
            firstName: updated.firstName ?? updated.first_name ?? '',
            middleName: updated.middleName ?? updated.middle_name ?? '',
            lastName: updated.lastName ?? updated.last_name ?? '',
            phone: updated.phone ?? '',
            role: normalizeRole(updated.role),
            department: updated.department ?? undefined,
            clinicId: updated.clinicId ?? updated.clinic_id ?? undefined,
            manufacturerId: updated.manufacturerId ?? updated.manufacturer_id ?? undefined,
            isActive: Boolean(updated.isActive),
            isArchived: Boolean(updated.isArchived ?? false),
            lastLogin: updated.lastLogin ?? undefined,
            createdAt: updated.createdAt ?? updated.created_at ?? new Date().toISOString(),
        } as User
        const idx = users.value.findIndex(u => u.id === String(updated.id))
        if (idx !== -1) {
            users.value[idx] = mapped
        }
        // If current filter would exclude this user now, refetch to sync page and totals
        if (statusFilter.value !== 'all') {
            await fetchUsers()
        }

        // Close modal before showing success
        selectedUser.value = null

        toast.success(`${formatFullName(mapped)} was ${action}d successfully.`)
    } catch (err) {
        console.error('Failed to toggle user status', err)
        toast.error(`Error trying to ${action} user`)
    }
}

async function handleArchiveUser(userId: string) {
    const user = users.value.find(u => u.id === userId)
    if (!user) return

    const result = await confirmAction({
        title: 'Archive User?',
        text: 'This action will move the user to archived status. You can restore it later if needed.',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, archive it!',
        cancelButtonText: 'Cancel'
    })

    if (!result.isConfirmed) return

    try {
        const { data } = await userService.archiveUser(userId)
        const updated = data.user
        const mapped = {
            id: String(updated.id),
            email: updated.email,
            firstName: updated.firstName ?? updated.first_name ?? '',
            middleName: updated.middleName ?? updated.middle_name ?? '',
            lastName: updated.lastName ?? updated.last_name ?? '',
            phone: updated.phone ?? '',
            role: normalizeRole(updated.role),
            department: updated.department ?? undefined,
            clinicId: updated.clinicId ?? updated.clinic_id ?? undefined,
            manufacturerId: updated.manufacturerId ?? updated.manufacturer_id ?? undefined,
            isActive: Boolean(updated.isActive),
            isArchived: Boolean(updated.isArchived ?? true),
            lastLogin: updated.lastLogin ?? undefined,
            createdAt: updated.createdAt ?? updated.created_at ?? new Date().toISOString(),
        } as User
        const idx = users.value.findIndex(u => u.id === String(updated.id))
        if (idx !== -1) {
            users.value[idx] = mapped
        }
        await fetchUserStats()

        // Close modal before showing success
        selectedUser.value = null

        toast.success(`${formatFullName(mapped)} was archived successfully and can be restored later.`)
    } catch (err) {
        console.error('Failed to archive user', err)
        toast.error('Error trying to archive user')
    }
}

async function handleSendPasswordResetLink(user: User) {
    if (!user) return

    const result = await Swal.fire({
        title: 'Send Password Reset Link',
        text: `Send a secure password reset link to ${user.email}? This will allow the user to establish a new password compliant with security requirements.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, send reset link',
        cancelButtonText: 'Cancel'
    })

    if (!result.isConfirmed) return

    try {
        const response = await userService.sendPasswordResetLink(user.id)
        const msg = response.data?.message || `Password reset link sent to ${user.email}.`
        toast.success(msg)
    } catch (err: any) {
        console.error('Failed to send password reset link', err)
        const msg = err.response?.data?.message || 'Failed to send password reset link. Please try again.'
        toast.error(msg)
    }
}

// --- Sudo Delete Modal State ---
const showSudoDeleteModal = ref(false)
const sudoDeleteMessage = ref('')
const sudoDeleteTargetUserId = ref<string | null>(null)
const isDeletingUser = ref(false)
const sudoDeleteModalRef = ref<InstanceType<typeof SudoConfirmModal> | null>(null)

function handleDeleteUser(userId: string) {
    const user = users.value.find(u => u.id === userId)
    if (!user) return

    sudoDeleteTargetUserId.value = userId
    sudoDeleteMessage.value = `You are about to permanently delete <strong>${formatFullName(user)}</strong>.<br><br>Enter your current account password to authorize this action.`
    showSudoDeleteModal.value = true
}

async function onSudoDeleteConfirm(password: string) {
    if (!sudoDeleteTargetUserId.value) return
    isDeletingUser.value = true

    try {
        await userService.softDeleteUser(sudoDeleteTargetUserId.value, { current_password: password })
        users.value = users.value.filter(user => user.id !== sudoDeleteTargetUserId.value)
        await fetchUserStats()

        // Close modals and reset
        showSudoDeleteModal.value = false
        selectedUser.value = null
        sudoDeleteTargetUserId.value = null

        toast.success('User has been deleted successfully.')
    } catch (err: any) {
        console.error('Failed to delete user', err)
        const errorCode = err.response?.data?.error
        const msg = err.response?.data?.message || 'Failed to delete user. Please try again.'

        if (errorCode === 'password_confirmation_required') {
            sudoDeleteModalRef.value?.setError('Incorrect password. Please try again.')
        } else {
            showSudoDeleteModal.value = false
            toast.error(msg)
        }
    } finally {
        isDeletingUser.value = false
    }
}

function onSudoDeleteCancel() {
    sudoDeleteTargetUserId.value = null
}

async function handleViewUser(user: User) {
    selectedUser.value = user

    try {
        const { data } = await userService.getUser(user.id)
        if (data && data.user) {
            const u = data.user
            selectedUser.value = {
                id: String(u.id),
                email: u.email,
                firstName: u.firstName ?? u.first_name ?? user.firstName,
                middleName: u.middleName ?? u.middle_name ?? user.middleName,
                lastName: u.lastName ?? u.last_name ?? user.lastName,
                phone: u.phone ?? user.phone,
                role: normalizeRole(u.role),
                department: u.department ?? user.department,
                clinicId: u.clinicId ?? u.clinic_id ?? user.clinicId,
                clinicCode: u.clinicCode ?? u.clinic_code ?? user.clinicCode,
                manufacturerId: u.manufacturerId ?? u.manufacturer_id ?? user.manufacturerId,
                isActive: Boolean(u.isActive),
                isArchived: Boolean(u.isArchived ?? false),
                lastLogin: u.lastLogin ?? user.lastLogin,
                createdAt: u.createdAt ?? user.createdAt,
            }
        }
    } catch (err) {
        console.error('Failed to load audited user details', err)
    }
}

async function editUser(user: User) {
    selectedUser.value = user
    formData.value = {
        firstName: user.firstName,
        middleName: user.middleName || '',
        lastName: user.lastName,
        email: user.email,
        phone: user.phone || '',
        role: user.role,
        isActive: user.isActive,
        clinicId: user.clinicId || '',
        manufacturerId: user.manufacturerId ? String(user.manufacturerId) : '',
    }
    showEditForm.value = true

    try {
        const { data } = await userService.getUser(user.id)
        if (data && data.user && data.user.phone) {
            formData.value.phone = data.user.phone
        }
    } catch (err) {
        console.error('Failed to fetch unmasked phone for editing', err)
    }
}

// --- Sudo Admin Modal State ---
const showSudoAdminModal = ref(false)
const sudoAdminModalRef = ref<InstanceType<typeof SudoConfirmModal> | null>(null)
let pendingAdminFormData: any = null
let pendingAdminPassword: string | undefined = undefined

async function onSudoAdminConfirm(password: string) {
    pendingAdminPassword = password
    await handleSubmitForm(pendingAdminFormData)
    if (!pendingAdminFormData) {
        showSudoAdminModal.value = false
    }
}

function onSudoAdminCancel() {
    pendingAdminFormData = null
    pendingAdminPassword = undefined
    showSudoAdminModal.value = false
}

async function handleSubmitForm(submittedData?: any) {
    const dataToUse = submittedData || formData.value

    const isAssigningAdmin = roleToInt(dataToUse.role) === 0 && (showCreateForm.value || (showEditForm.value && selectedUser.value?.role !== 'admin'))

    if (isAssigningAdmin && !pendingAdminPassword) {
        // Store pending data and open the sudo modal
        pendingAdminFormData = dataToUse
        showSudoAdminModal.value = true
        return
    }

    const currentPassword = pendingAdminPassword
    pendingAdminPassword = undefined
    pendingAdminFormData = null
    
    if (showCreateForm.value) {
        isSubmitting.value = true
        try {
            const payload: any = {
                first_name: dataToUse.firstName,
                middle_name: dataToUse.middleName || null,
                last_name: dataToUse.lastName,
                email: dataToUse.email,
                phone: dataToUse.phone || null,
                user_role: roleToInt(dataToUse.role),
                user_status: dataToUse.isActive ? 0 : 1,
                clinic_id: (dataToUse.role === 'clinician' || dataToUse.role === 'clinic') 
                    ? (isClinicAdmin.value ? currentUserClinicId.value : (dataToUse.clinicId || null)) 
                    : null,
                manufacturer_id: isClinicAdmin.value ? null : (dataToUse.role === 'manufacturer' ? (dataToUse.manufacturerId || null) : null),
                current_password: currentPassword,
            }
            const { data } = await userService.createUser(payload)
            const u = data.user
            const newUser: User = {
                id: String(u.id),
                email: u.email,
                firstName: u.firstName ?? u.first_name ?? dataToUse.firstName,
                middleName: u.middleName ?? u.middle_name ?? dataToUse.middleName,
                lastName: u.lastName ?? u.last_name ?? dataToUse.lastName,
                phone: u.phone ?? dataToUse.phone,
                role: normalizeRole(u.role),
                department: u.department ?? undefined,
                manufacturerId: u.manufacturerId ?? u.manufacturer_id ?? (dataToUse.role === 'manufacturer' ? dataToUse.manufacturerId : undefined),
                clinicId: u.clinicId ?? u.clinic_id ?? ((dataToUse.role === 'clinician' || dataToUse.role === 'clinic') ? dataToUse.clinicId : undefined),
                clinicCode: u.clinicCode ?? u.clinic_code ?? undefined,
                isActive: Boolean(u.isActive),
                isArchived: Boolean(u.isArchived ?? false),
                lastLogin: u.lastLogin ?? undefined,
                createdAt: u.createdAt ?? u.created_at ?? new Date().toISOString(),
            }
            users.value.unshift(newUser)
            await fetchUserStats()

            // Close modal before showing success
            closeForm()

            toast.success(`${formatFullName(newUser)} was created successfully. Welcome credentials were sent to ${newUser.email}.`)
        } catch (err: any) {
            console.error('Failed to create user', err)

            // Handle specific validation errors
            const errorMessage = err.response?.data?.message || 'Failed to create user. Please try again.'
            const errorCode = err.response?.data?.error

<<<<<<< Updated upstream
            if (errorCode === 'manufacturer_account_exists') {
                toast.warning(errorMessage, 'Manufacturer account already exists')
            } else if (errorCode === 'clinic_admin_exists') {
                toast.warning(errorMessage, 'Clinic admin already exists')
            } else if (errorCode === 'unauthorized_role_creation') {
                toast.error(errorMessage, 'Unauthorized role creation')
            } else {
                toast.error(errorMessage)
=======
            if (errorCode === 'password_confirmation_required') {
                sudoAdminModalRef.value?.setError('Incorrect password. Please try again.')
                pendingAdminFormData = dataToUse
                return
            } else if (errorCode === 'manufacturer_account_exists') {
                showSudoAdminModal.value = false
                appToast.warning('Manufacturer Account Already Exists: ' + errorMessage)
            } else if (errorCode === 'clinic_admin_exists') {
                showSudoAdminModal.value = false
                appToast.warning('Clinic Admin Already Exists: ' + errorMessage)
            } else if (errorCode === 'unauthorized_role_creation') {
                showSudoAdminModal.value = false
                appToast.error(errorMessage)
            } else {
                showSudoAdminModal.value = false
                appToast.error(errorMessage)
>>>>>>> Stashed changes
            }
        } finally {
            isSubmitting.value = false
        }
    } else if (showEditForm.value && selectedUser.value) {
        isSubmitting.value = true
        try {
            const payload: any = {
                first_name: dataToUse.firstName,
                middle_name: dataToUse.middleName || null,
                last_name: dataToUse.lastName,
                email: dataToUse.email,
                phone: dataToUse.phone || null,
                user_role: roleToInt(dataToUse.role),
                user_status: dataToUse.isActive ? 0 : 1,
                clinic_id: (dataToUse.role === 'clinician' || dataToUse.role === 'clinic') 
                    ? (isClinicAdmin.value ? currentUserClinicId.value : (dataToUse.clinicId || null)) 
                    : null,
                manufacturer_id: isClinicAdmin.value ? null : (dataToUse.role === 'manufacturer' ? (dataToUse.manufacturerId || null) : null),
                current_password: currentPassword,
            }
            const { data } = await userService.updateUser(selectedUser.value.id, payload)
            const u = data.user
            const updated: User = {
                id: String(u.id),
                email: u.email,
                firstName: u.firstName ?? u.first_name ?? dataToUse.firstName,
                middleName: u.middleName ?? u.middle_name ?? dataToUse.middleName,
                lastName: u.lastName ?? u.last_name ?? dataToUse.lastName,
                phone: u.phone ?? dataToUse.phone,
                role: normalizeRole(u.role),
                department: u.department ?? undefined,
                manufacturerId: u.manufacturerId ?? dataToUse.manufacturerId ?? undefined,
                clinicId: u.clinicId ?? u.clinic_id ?? ((dataToUse.role === 'clinician' || dataToUse.role === 'clinic') ? dataToUse.clinicId : undefined),
                clinicCode: u.clinicCode ?? u.clinic_code ?? undefined,
                isActive: Boolean(u.isActive),
                isArchived: Boolean(u.isArchived ?? false),
                lastLogin: u.lastLogin ?? undefined,
                createdAt: u.createdAt ?? u.created_at ?? selectedUser.value.createdAt,
            }
            const idx = users.value.findIndex(x => x.id === updated.id)
            if (idx !== -1) users.value[idx] = updated
            await fetchUserStats()

            // Close modal before showing success
            closeForm()

            toast.success(`${formatFullName(updated)} details were updated successfully.`)
        } catch (err: any) {
            console.error('Failed to update user', err)

            // Handle specific validation errors
            const errorMessage = err.response?.data?.message || 'Failed to update user. Please try again.'
            const errorCode = err.response?.data?.error

<<<<<<< Updated upstream
            if (errorCode === 'manufacturer_account_exists') {
                toast.warning(errorMessage, 'Manufacturer account already exists')
            } else if (errorCode === 'clinic_admin_exists') {
                toast.warning('Clinic Admin Already Exists: ' + errorMessage)
            } else if (errorCode === 'unauthorized_role_assignment' || errorCode === 'unauthorized_role_creation') {
                toast.error('Permission Denied: ' + errorMessage)
            } else if (errorCode === 'unauthorized_clinic_assignment') {
                toast.error('Facility Error: ' + errorMessage)
            } else if (errorCode === 'self_role_modification_prohibited' || errorCode === 'self_status_modification_prohibited') {
                toast.error('Forbidden: ' + errorMessage)
            } else {
                toast.error(errorMessage)
=======
            if (errorCode === 'password_confirmation_required') {
                sudoAdminModalRef.value?.setError('Incorrect password. Please try again.')
                pendingAdminFormData = dataToUse
                return
            } else if (errorCode === 'manufacturer_account_exists') {
                showSudoAdminModal.value = false
                appToast.warning('Manufacturer Account Already Exists: ' + errorMessage)
            } else if (errorCode === 'clinic_admin_exists') {
                showSudoAdminModal.value = false
                appToast.warning('Clinic Admin Already Exists: ' + errorMessage)
            } else if (errorCode === 'unauthorized_role_assignment' || errorCode === 'unauthorized_role_creation') {
                showSudoAdminModal.value = false
                appToast.error('Permission Denied: ' + errorMessage)
            } else if (errorCode === 'unauthorized_clinic_assignment') {
                showSudoAdminModal.value = false
                appToast.error('Facility Error: ' + errorMessage)
            } else if (errorCode === 'self_role_modification_prohibited' || errorCode === 'self_status_modification_prohibited') {
                showSudoAdminModal.value = false
                appToast.error('Forbidden: ' + errorMessage)
            } else {
                showSudoAdminModal.value = false
                appToast.error(errorMessage)
>>>>>>> Stashed changes
            }
        } finally {
            isSubmitting.value = false
        }
    }
}

function closeForm() {
    showCreateForm.value = false
    showEditForm.value = false
    selectedUser.value = null
    showSudoAdminModal.value = false
    pendingAdminFormData = null
    pendingAdminPassword = undefined
    formData.value = {
        ...defaultFormData,
        role: getInitialRole(),
        clinicId: isClinicAdmin.value ? currentUserClinicId.value : '',
    }
}

const formatFullName = (user: { firstName: string, middleName?: string, lastName: string }) => {
    return [user.firstName, user.middleName, user.lastName].filter(Boolean).join(' ');
}

// Use server results directly
const filteredUsers = computed(() => users.value)
const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredUsers.value.length / itemsPerPage.value))
})

// Pagination object for the Pagination component
const pagination = computed(() => ({
    current_page: currentPage.value,
    last_page: totalPages.value,
    per_page: itemsPerPage.value,
    total: filteredUsers.value.length
}))

const paginatedUsers = computed(() => {
    return filteredUsers.value
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

    // Always show first page
    pages.push(1)

    // Show ellipsis after first page if needed
    if (current > siblingCount + 2) {
        pages.push('...')
    }

    // Show pages around current page
    const startPage = Math.max(2, current - siblingCount)
    const endPage = Math.min(total - 1, current + siblingCount)

    for (let i = startPage; i <= endPage; i++) {
        pages.push(i)
    }

    // Show ellipsis before last page if needed
    if (current < total - siblingCount - 1) {
        pages.push('...')
    }

    // Always show last page
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

const getRoleColor = (role: User['role']) => {
    switch (role) {
        case 'admin': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
        case 'office-staff': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
        case 'clinic': return 'bg-teal-100 text-teal-800 dark:bg-teal-900/20 dark:text-teal-400'
        case 'clinician': return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
        case 'manufacturer': return 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
        case 'biller': return 'bg-pink-100 text-pink-800 dark:bg-pink-900/20 dark:text-pink-400'
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
    }
}

const formatRoleName = (role: User['role']) => {
    const roleNames: Record<User['role'], string> = {
        'admin': 'Admin',
        'office-staff': 'Office Staff',
        'clinic': 'Clinic Admin',
        'clinician': 'Clinician',
        'manufacturer': 'Manufacturer',
        'biller': 'Biller'
    }
    return roleNames[role] || role
}

const formatDate = (dateStr: string) => {
    const date = new Date(dateStr)
    const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
    return `${formattedDate} [${formattedTime}]`
}

// Map backend numeric roles to UI role strings
const normalizeRole = (role: unknown): User['role'] => {
    const map: Record<number, User['role']> = {
        0: 'admin',
        1: 'office-staff',
        2: 'clinic',
        3: 'clinician',
        4: 'manufacturer',
        5: 'biller',
    }
    if (typeof role === 'number' && role in map) return map[role]
    if (typeof role === 'string') return role as User['role']
    return 'office-staff'
}

// Map UI role strings to backend numeric roles
const roleToInt = (role: User['role']): number => {
    const map: Record<User['role'], number> = {
        'admin': 0,
        'office-staff': 1,
        'clinic': 2,
        'clinician': 3,
        'manufacturer': 4,
        'biller': 5,
    }
    return map[role]
}

const fetchUsers = async () => {
    tableLoader.value = true
    try {
        const params = {
            search: searchTerm.value || undefined,
            role: roleFilter.value === 'all' ? undefined : roleToInt(roleFilter.value as User['role']),
            status: statusFilter.value,
            page: currentPage.value,
        }
        const { data } = await userService.getUsers(params)
        const incoming: any[] = Array.isArray(data.users) ? data.users : []
        users.value = incoming.map((u) => ({
            id: String(u.id),
            email: u.email,
            firstName: u.firstName ?? u.first_name ?? '',
            middleName: u.middleName ?? u.middle_name ?? '',
            lastName: u.lastName ?? u.last_name ?? '',
            phone: u.phone ?? '',
            role: normalizeRole(u.role),
            department: u.department ?? undefined,
            clinicId: u.clinicId ?? u.clinic_id ?? undefined,
            clinicCode: u.clinicCode ?? u.clinic_code ?? undefined,
            manufacturerId: u.manufacturerId ?? u.manufacturer_id ?? undefined,
            isActive: Boolean(u.isActive),
            isArchived: Boolean(u.isArchived ?? false),
            lastLogin: u.lastLogin ?? undefined,
            createdAt: u.createdAt ?? u.created_at ?? new Date().toISOString(),
        }))
        // Update total results to reflect filtered count from backend
        totalResults.value = data.total
        if (data.per_page) itemsPerPage.value = Number(data.per_page)
    } catch (err) {
        console.error('Failed to fetch users', err)
    } finally {
        tableLoader.value = false
    }
}
const fetchUserStats = async () => {
    try {
        const { data } = await userService.getUserStats()
        serverStats.value = {
            total: Number(data.total ?? 0),
            active: Number(data.active ?? 0),
            inactive: Number(data.inactive ?? 0),
            archived: Number(data.archived ?? 0),
            roles: {
                admin: Number(data.roles?.admin ?? 0),
                'office-staff': Number(data.roles?.['office-staff'] ?? 0),
                clinic: Number(data.roles?.clinic ?? 0),
                clinician: Number(data.roles?.clinician ?? 0),
                manufacturer: Number(data.roles?.manufacturer ?? 0),
                biller: Number(data.roles?.biller ?? 0),
            }
        }
    } catch (err) {
        console.error('Failed to fetch stats', err)
    }
}

const fetchClinics = async () => {
    try {
        const { data } = await userService.getClinics()
        // Handle both possible response structures
        const items = Array.isArray(data)
            ? data
            : (Array.isArray(data?.user_data)
                ? data.user_data
                : (Array.isArray(data?.clinics) ? data.clinics : []))

        departments.value = items.map((c: any) => ({
            id: String(c.clinic_id ?? c.id),
            name: c.clinic_name ?? c.name
        }))

        console.log('Clinics loaded:', departments.value)
    } catch (err) {
        console.error('Failed to fetch clinics', err)
    }
}

const fetchManufacturers = async () => {
    try {
        const { data } = await userService.getManufacturers()
        // Handle both possible response structures
        const items = Array.isArray(data)
            ? data
            : (Array.isArray(data?.manufacturerData)
                ? data.manufacturerData
                : (Array.isArray(data?.manufacturers) ? data.manufacturers : []))

        manufacturers.value = items.map((m: any) => ({
            id: String(m.manufacturer_id ?? m.id),
            name: m.manufacturer_name ?? m.manufacturerName ?? m.name
        }))
    } catch (err) {
        console.error('Failed to fetch manufacturers', err)
    }
}

onMounted(() => {
    Promise.all([
        fetchUsers(),
        fetchUserStats(),
        fetchClinics(),
        fetchManufacturers()
    ]).catch((err) => { console.error('Failed initial load', err) })
})

watch([searchTerm, roleFilter, statusFilter], () => {
    currentPage.value = 1
    fetchUsers().catch((err) => { console.error('Failed to fetch users on filters change', err) })
})

watch(currentPage, () => {
    fetchUsers().catch((err) => { console.error('Failed to fetch users on page change', err) })
})

// Auto-configure role and clinic boundary when opening the create form
watch(showCreateForm, (isOpen) => {
    if (isOpen) {
        if (isClinicAdmin.value) {
            formData.value.role = 'clinician'
            formData.value.clinicId = currentUserClinicId.value
            formData.value.manufacturerId = ''
        } else if (isOfficeStaff.value) {
            formData.value.role = 'clinic'
        }
    }
})

const showFormModal = computed({
    get: () => showCreateForm.value || showEditForm.value,
    set: (value: boolean) => {
        if (!value) {
            closeForm()
        }
    }
})

const showUserDetailsModal = computed({
    get: () => selectedUser.value !== null && !showEditForm.value,
    set: (value: boolean) => {
        if (!value) {
            selectedUser.value = null;
        }
    }
})

const getUserInitials = (user: { firstName: string, lastName: string }) => {
    const firstInitial = user.firstName ? user.firstName.charAt(0) : '';
    const lastInitial = user.lastName ? user.lastName.charAt(0) : '';
    return `${firstInitial}${lastInitial}`.toUpperCase();
}

</script>
