<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl">Manage system users, their roles, and permissions
                    in one centralized dashboard</p>
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
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
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
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <!-- Role Breakdown -->
                <div class="mt-6">
                    <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Role Breakdown</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <ShieldCheck class="w-4 h-4 mr-2 text-purple-500" /> Admins
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ stats.roles.admin }}</span>
                        </div>
                        <div class="flex items-center justify-between">
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
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row gap-6">
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
                            <option value="admin">Admin</option>
                            <option value="office-staff">Office Staff</option>
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
                        </select>
                        <ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                User
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Role
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
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                            formatFullName(user) }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span
                                    :class="['inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200', getRoleColor(user.role)]">
                                    {{ formatRoleName(user.role) }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span
                                    :class="['inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200', user.isActive ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400']">
                                    <component :is="user.isActive ? CheckCircle2 : XCircle" class="w-3 h-3 mr-1.5" />
                                    {{ user.isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(user.createdAt) }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button @click="selectedUser = user"
                                        class="p-2 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all duration-200"
                                        title="View Details">
                                        <Eye class="w-4 h-4" />
                                    </button>
                                    <button @click="editUser(user)"
                                        class="p-2 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-all duration-200"
                                        title="Edit User">
                                        <FilePenLine class="w-4 h-4" />
                                    </button>
                                    <button @click="handleToggleStatus(user.id)" :class="[
                                        'p-2 rounded-lg transition-all duration-200',
                                        user.isActive
                                            ? 'text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20'
                                            : 'text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'
                                    ]" :title="user.isActive ? 'Deactivate' : 'Activate'">
                                        <component :is="user.isActive ? XCircle : CheckCircle2" class="w-4 h-4" />
                                    </button>
                                    <button v-if="!user.isArchived"
                                        @click="handleArchiveUser(user.id)"
                                        class="p-2 text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-all duration-200"
                                        title="Archive User">
                                        <Archive class="w-4 h-4" />
                                    </button>
                                    <button v-else @click="handleDeleteUser(user.id)"
                                        class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200"
                                        title="Delete User">
                                        <Trash2 class="w-4 h-4" />
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

            <!-- Empty State -->
            <div v-if="filteredUsers.length === 0 && !tableLoader" class="text-center py-12">
                <div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <Users class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No users found</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Try adjusting your search or filter to find
                    what you're looking for.</p>
            </div>

            <!-- Pagination -->
            <div v-if="filteredUsers.length > 0"
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
                                :class="['px-3 py-1.5 text-sm font-medium rounded-lg transition-colors', currentPage === page ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700']">
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

        <!-- User Details Modal -->
        <BaseModal v-model="showUserDetailsModal" title="User Details" size="lg">
            <template v-if="selectedUser">
                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="h-16 w-16 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400 font-medium text-lg">
                            {{ getUserInitials(selectedUser) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ formatFullName(selectedUser)
                                }}</h2>
                            <p class="text-gray-500 dark:text-gray-400">{{ selectedUser.email }}</p>
                            <p v-if="selectedUser.phone" class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                <span class="font-medium">Phone:</span> {{ selectedUser.phone }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Role</label>
                                <span
                                    :class="['inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium', getRoleColor(selectedUser.role)]">
                                    {{ formatRoleName(selectedUser.role) }}
                                </span>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                                <span
                                    :class="['inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium', selectedUser.isActive ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400']">
                                    <component :is="selectedUser.isActive ? CheckCircle2 : XCircle"
                                        class="w-4 h-4 mr-1.5" />
                                    {{ selectedUser.isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            
                            <!-- <div v-if="selectedUser.phone">
                                <label
                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Phone</label>
                                <p class="text-sm text-gray-900 dark:text-white">{{ selectedUser.phone }}</p>
                            </div> -->
                            </div>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Created</label>
                                <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(selectedUser.createdAt)
                                    }}</p>
                            </div>
                            <div v-if="selectedUser.isArchived">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Archive
                                    Status</label>
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400">
                                    <Archive class="w-4 h-4 mr-1.5" />
                                    Archived
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedUser.department" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                            <span v-if="selectedUser.role === 'clinic'">Managing Clinic Facility</span>
                            <span v-else-if="selectedUser.role === 'clinician'">Works at Clinic Facility</span>
                            <span v-else-if="selectedUser.role === 'manufacturer'">Associated Manufacturer</span>
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white font-medium">{{ selectedUser.department }}</p>
                        <p v-if="selectedUser.role === 'clinic'" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            This user is the administrator of this clinic facility.
                        </p>
                        <p v-else-if="selectedUser.role === 'clinician'" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            This clinician provides medical services at this facility.
                        </p>
                         <p v-else-if="selectedUser.role === 'manufacturer'" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            This user is associated with the specified manufacturer.
                        </p>
                    </div>

                    <div v-if="selectedUser.clinicCode" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                            Associated Clinic Code
                        </label>
                        <div class="flex items-center justify-between mt-1 p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <code class="text-sm text-gray-700 dark:text-gray-300 truncate pr-2">{{ selectedUser.clinicCode }}</code>
                            <div class="relative">
                                <button @click="copyId(selectedUser.clinicCode)" 
                                        class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-all duration-200"
                                        title="Copy Code">
                                    <Copy class="w-4 h-4" />
                                </button>
                                <transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="transform opacity-0 scale-95"
                                    enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-100"
                                    leave-from-class="transform opacity-100 scale-100"
                                    leave-to-class="transform opacity-0 scale-95">
                                    <div v-if="copiedTooltip" class="absolute -top-10 right-0 px-2 py-1 text-xs text-white bg-gray-800 dark:bg-gray-900 rounded-md shadow-lg pointer-events-none">
                                        Copied!
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <!-- <div v-if="selectedUser.manufacturerId" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                            Associated Manufacturer ID
                        </label>
                        <div class="flex items-center justify-between mt-1 p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <code class="text-sm text-gray-700 dark:text-gray-300 truncate pr-2">{{ selectedUser.manufacturerId }}</code>
                            <div class="relative">
                                <button @click="copyId(selectedUser.manufacturerId)"
                                        class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-all duration-200"
                                        title="Copy ID">
                                    <Copy class="w-4 h-4" />
                                </button>
                                <transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="transform opacity-0 scale-95"
                                    enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-100"
                                    leave-from-class="transform opacity-100 scale-100"
                                    leave-to-class="transform opacity-0 scale-95">
                                    <div v-if="copiedTooltip" class="absolute -top-10 right-0 px-2 py-1 text-xs text-white bg-gray-800 dark:bg-gray-900 rounded-md shadow-lg pointer-events-none">
                                        Copied!
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div> -->

                    <div v-if="selectedUser.role === 'clinic'" class="pt-6 border-t border-gray-200 dark:border-gray-700">
                        <!-- Accordion Header -->
                        <button @click="isCliniciansAccordionOpen = !isCliniciansAccordionOpen" class="w-full flex items-center justify-between text-left focus:outline-none group p-2 -m-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <h3 class="text-md font-semibold text-gray-900 dark:text-white flex items-center">
                                <Stethoscope class="w-5 h-5 mr-2" :class="associatedClinicians.length > 0 ? 'text-teal-500' : 'text-gray-400'" />
                                Clinicians at this Facility 
                                <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 group-hover:bg-gray-300 dark:group-hover:bg-gray-500 transition-colors">
                                    {{ associatedClinicians.length }}
                                </span>
                            </h3>
                            <ChevronDown class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 transform" :class="{ 'rotate-180': isCliniciansAccordionOpen }" />
                        </button>
                        
                        <!-- Accordion Content -->
                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2">
                            <div v-if="isCliniciansAccordionOpen" class="mt-4">
                                <div v-if="associatedClinicians.length > 0">
                                     <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        Medical staff working at {{ selectedUser.department }}
                                    </p>
                                    <ul class="space-y-3">
                                        <li v-for="clinician in displayedClinicians" :key="clinician.id"
                                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-green-100 to-teal-100 dark:from-green-900/30 dark:to-teal-900/30 rounded-full flex items-center justify-center text-green-600 dark:text-green-400 font-medium text-xs">
                                                    {{ getUserInitials(clinician) }}
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                        formatFullName(clinician) }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ clinician.email }}</p>
                                                </div>
                                            </div>
                                            <span
                                                :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium', clinician.isActive ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400']">
                                                {{ clinician.isActive ? 'Active' : 'Inactive' }}
                                            </span>
                                        </li>
                                    </ul>
                                    <div v-if="associatedClinicians.length > initialClinicianCount" class="mt-3">
                                        <button @click="showAllClinicians = !showAllClinicians"
                                            class="w-full text-center px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            {{ showAllClinicians ? 'Show Less' : `Show ${associatedClinicians.length - initialClinicianCount} More` }}
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 border border-dashed border-gray-300 dark:border-gray-600">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                        No clinicians are currently assigned to this clinic facility.
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                </div>
            </template>
        </BaseModal>

        <!-- Create/Edit User Form Modal -->
        <BaseModal v-model="showFormModal" :title="showCreateForm ? 'Create New User' : 'Edit User'" size="2xl">
            <form @submit.prevent="handleSubmitForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name<span
                                class="text-red-500 ml-1">*</span></label>
                        <input v-model="formData.firstName" type="text" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter first name" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Middle
                            Name</label>
                        <input v-model="formData.middleName" type="text"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter middle name" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name<span
                                class="text-red-500 ml-1">*</span></label>
                        <input v-model="formData.lastName" type="text" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter last name" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email<span
                                class="text-red-500 ml-1">*</span></label>
                        <input v-model="formData.email" type="email" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter email address" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                        <input v-model="formData.phone" type="tel"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter phone number" />
                    </div>
                </div>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role<span
                                class="text-red-500 ml-1">*</span></label>
                        <select v-model="formData.role" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <!-- Admin and Office Staff only visible to admins -->
                            <option v-if="!isOfficeStaff" value="admin">Admin</option>
                            <option v-if="!isOfficeStaff" value="office-staff">Office Staff</option>
                            <!-- Roles available to both admin and office staff -->
                            <option value="clinic">Clinic Administrator</option>
                            <option value="clinician">Clinician (Medical Staff)</option>
                            <option value="manufacturer">Manufacturer</option>
                            <option value="biller">Biller</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status<span
                                class="text-red-500 ml-1">*</span></label>
                        <select v-model="formData.isActive" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </div>
                </div>

                <div v-if="formData.role === 'clinic'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Clinic Facility
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Select the clinic facility this user will manage</p>
                    <select v-model="formData.clinicId" :required="formData.role === 'clinic'"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                        <option disabled value="">Select a clinic facility</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>

                <div v-if="formData.role === 'manufacturer'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Manufacturer<span
                            class="text-red-500 ml-1">*</span></label>
                    <select v-model="formData.manufacturerId" :required="formData.role === 'manufacturer'"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                        <option disabled value="">Select a manufacturer</option>
                        <option v-for="manu in manufacturers" :key="manu.id" :value="manu.id">
                            {{ manu.name }}
                        </option>
                    </select>
                </div>

                <div v-if="formData.role === 'clinician'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Associated Clinic Facility
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Select the clinic facility where this clinician works</p>
                    <select v-model="formData.clinicId" :required="formData.role === 'clinician'"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                        <option disabled value="">Select a clinic facility</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>

                <div v-if="showCreateForm">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password<span
                            class="text-red-500 ml-1">*</span></label>
                    <div class="relative">
                        <input v-model="formData.password" type="text" :required="showCreateForm"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 pr-12"
                            placeholder="Enter or generate a password" />
                        <button type="button" @click="generateRandomPassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400"
                            title="Generate Random Password">
                            <Sparkles class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="closeForm"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md">
                        {{ showCreateForm ? 'Create User' : 'Update User' }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import TableLoader from '@/components/ui/TableLoader.vue'
import { userService } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
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
    Sparkles,
    Factory,
    FileText,
    Building,
    BarChart2,
    ShieldCheck,
    Briefcase,
    Stethoscope,
    Copy,
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

const searchTerm = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')
const tableLoader = ref(false)
const selectedUser = ref<User | null>(null)
const associatedCliniciansData = ref<User[]>([])
const showCreateForm = ref(false)
const showEditForm = ref(false)
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalResults = ref(0)
const showStats = ref(false)
const copiedTooltip = ref(false);
const isCliniciansAccordionOpen = ref(false);
const showAllClinicians = ref(false);
const initialClinicianCount = 3;

// Check if current user is office staff
const isOfficeStaff = computed(() => authStore.currentUser?.user_role === 1)

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
    password: ''
}

const formData = ref({ ...defaultFormData });

// Global stats from server (not limited by current page)
const serverStats = ref({
    total: 0,
    active: 0,
    inactive: 0,
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

const displayedClinicians = computed(() => {
    if (showAllClinicians.value) {
        return associatedClinicians.value;
    }
    return associatedClinicians.value.slice(0, initialClinicianCount);
});

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
    const actionTitle = user.isActive ? 'Deactivate User' : 'Activate User'
    
    const result = await Swal.fire({
        title: actionTitle,
        text: `Are you sure you want to ${action} ${formatFullName(user)}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: user.isActive ? '#dc2626' : '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Yes, ${action}!`,
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

        // Success notification
        await Swal.fire({
            title: 'Success!',
            text: `User has been ${action}d successfully.`,
            icon: 'success',
            confirmButtonColor: '#2563eb',
            timer: 2000,
            showConfirmButton: false
        })
    } catch (err) {
        console.error('Failed to toggle user status', err)
        await Swal.fire({
            title: 'Error!',
            text: 'Failed to update user status. Please try again.',
            icon: 'error',
            confirmButtonColor: '#2563eb'
        })
    }
}

async function handleArchiveUser(userId: string) {
    const user = users.value.find(u => u.id === userId)
    if (!user) return

    const result = await Swal.fire({
        title: 'Archive User',
        text: `Are you sure you want to archive ${formatFullName(user)}? This user will be moved to archived status.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ea580c',
        cancelButtonColor: '#6b7280',
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

        // Success notification
        await Swal.fire({
            title: 'Archived!',
            text: 'User has been archived successfully.',
            icon: 'success',
            confirmButtonColor: '#2563eb',
            timer: 2000,
            showConfirmButton: false
        })
    } catch (err) {
        console.error('Failed to archive user', err)
        await Swal.fire({
            title: 'Error!',
            text: 'Failed to archive user. Please try again.',
            icon: 'error',
            confirmButtonColor: '#2563eb'
        })
    }
}

async function handleDeleteUser(userId: string) {
    const user = users.value.find(u => u.id === userId)
    if (!user) return

    const result = await Swal.fire({
        title: 'Delete User',
        html: `Are you sure you want to delete <strong>${formatFullName(user)}</strong>?<br><span style="color: #dc2626;">This action cannot be undone!</span>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    })

    if (!result.isConfirmed) return

    try {
        await userService.softDeleteUser(userId)
        users.value = users.value.filter(user => user.id !== userId)
        await fetchUserStats()

        // Success notification
        await Swal.fire({
            title: 'Deleted!',
            text: 'User has been deleted successfully.',
            icon: 'success',
            confirmButtonColor: '#2563eb',
            timer: 2000,
            showConfirmButton: false
        })
    } catch (err) {
        console.error('Failed to delete user', err)
        await Swal.fire({
            title: 'Error!',
            text: 'Failed to delete user. Please try again.',
            icon: 'error',
            confirmButtonColor: '#2563eb'
        })
    }
}

function editUser(user: User) {
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
        password: ''
    }
    showEditForm.value = true
}

async function handleSubmitForm() {
    if (showCreateForm.value) {
        try {
            const payload: any = {
                first_name: formData.value.firstName,
                middle_name: formData.value.middleName || null,
                last_name: formData.value.lastName,
                email: formData.value.email,
                phone: formData.value.phone || null,
                user_role: roleToInt(formData.value.role),
                user_status: formData.value.isActive ? 0 : 1,
                clinic_id: (formData.value.role === 'clinician' || formData.value.role === 'clinic') ? (formData.value.clinicId || null) : null,
                manufacturer_id: formData.value.role === 'manufacturer' ? (formData.value.manufacturerId || null) : null,
                password: formData.value.password,
                password_confirmation: formData.value.password,
            }
            const { data } = await userService.createUser(payload)
            const u = data.user
            const newUser: User = {
                id: String(u.id),
                email: u.email,
                firstName: u.firstName ?? u.first_name ?? formData.value.firstName,
                middleName: u.middleName ?? u.middle_name ?? formData.value.middleName,
                lastName: u.lastName ?? u.last_name ?? formData.value.lastName,
                phone: u.phone ?? formData.value.phone,
                role: normalizeRole(u.role),
                department: u.department ?? undefined,
                manufacturerId: u.manufacturerId ?? u.manufacturer_id ?? (formData.value.role === 'manufacturer' ? formData.value.manufacturerId : undefined),
                clinicId: u.clinicId ?? u.clinic_id ?? ((formData.value.role === 'clinician' || formData.value.role === 'clinic') ? formData.value.clinicId : undefined),
                clinicCode: u.clinicCode ?? u.clinic_code ?? undefined,
                isActive: Boolean(u.isActive),
                isArchived: Boolean(u.isArchived ?? false),
                lastLogin: u.lastLogin ?? undefined,
                createdAt: u.createdAt ?? u.created_at ?? new Date().toISOString(),
            }
            users.value.unshift(newUser)
            await fetchUserStats()
            
            // Success notification
            await Swal.fire({
                title: 'User Created Successfully!',
                html: `
                    <p class="text-gray-700 dark:text-gray-300 mb-2">The user account has been created.</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">A welcome email with login credentials has been sent to:</p>
                    <p class="text-blue-600 dark:text-blue-400 font-semibold mt-1">${data.user.email}</p>
                `,
                icon: 'success',
                confirmButtonColor: '#2563eb',
                timer: 4000,
                showConfirmButton: true
            })
            
            closeForm()
        } catch (err: any) {
            console.error('Failed to create user', err)
            
            // Handle specific validation errors
            const errorMessage = err.response?.data?.message || 'Failed to create user. Please try again.'
            const errorCode = err.response?.data?.error
            
            let title = 'Error!'
            let icon: 'error' | 'warning' = 'error'
            
            if (errorCode === 'manufacturer_account_exists') {
                title = 'Manufacturer Account Already Exists'
                icon = 'warning'
            } else if (errorCode === 'clinic_admin_exists') {
                title = 'Clinic Admin Already Exists'
                icon = 'warning'
            } else if (errorCode === 'unauthorized_role_creation') {
                title = 'Unauthorized Role Creation'
                icon = 'error'
            }
            
            await Swal.fire({
                title: title,
                text: errorMessage,
                icon: icon,
                confirmButtonColor: '#2563eb'
            })
        }
    } else if (showEditForm.value && selectedUser.value) {
        try {
            const payload: any = {
                first_name: formData.value.firstName,
                middle_name: formData.value.middleName || null,
                last_name: formData.value.lastName,
                email: formData.value.email,
                phone: formData.value.phone || null,
                user_role: roleToInt(formData.value.role),
                user_status: formData.value.isActive ? 0 : 1,
                clinic_id: (formData.value.role === 'clinician' || formData.value.role === 'clinic') ? (formData.value.clinicId || null) : null,
                manufacturer_id: formData.value.role === 'manufacturer' ? (formData.value.manufacturerId || null) : null,
            }
            const { data } = await userService.updateUser(selectedUser.value.id, payload)
            const u = data.user
            const updated: User = {
                id: String(u.id),
                email: u.email,
                firstName: u.firstName ?? u.first_name ?? formData.value.firstName,
                middleName: u.middleName ?? u.middle_name ?? formData.value.middleName,
                lastName: u.lastName ?? u.last_name ?? formData.value.lastName,
                phone: u.phone ?? formData.value.phone,
                role: normalizeRole(u.role),
                department: u.department ?? undefined,
                manufacturerId: u.manufacturerId ?? formData.value.manufacturerId ?? undefined,
                clinicId: u.clinicId ?? u.clinic_id ?? ((formData.value.role === 'clinician' || formData.value.role === 'clinic') ? formData.value.clinicId : undefined),
                clinicCode: u.clinicCode ?? u.clinic_code ?? undefined,
                isActive: Boolean(u.isActive),
                isArchived: Boolean(u.isArchived ?? false),
                lastLogin: u.lastLogin ?? undefined,
                createdAt: u.createdAt ?? u.created_at ?? selectedUser.value.createdAt,
            }
            const idx = users.value.findIndex(x => x.id === updated.id)
            if (idx !== -1) users.value[idx] = updated
            await fetchUserStats()
            
            // Success notification
            await Swal.fire({
                title: 'Success!',
                text: 'User has been updated successfully.',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                timer: 2000,
                showConfirmButton: false
            })
            
            closeForm()
        } catch (err: any) {
            console.error('Failed to update user', err)
            
            // Handle specific validation errors
            const errorMessage = err.response?.data?.message || 'Failed to update user. Please try again.'
            const errorCode = err.response?.data?.error
            
            let title = 'Error!'
            let icon: 'error' | 'warning' = 'error'
            
            if (errorCode === 'manufacturer_account_exists') {
                title = 'Manufacturer Account Already Exists'
                icon = 'warning'
            } else if (errorCode === 'clinic_admin_exists') {
                title = 'Clinic Admin Already Exists'
                icon = 'warning'
            }
            
            await Swal.fire({
                title: title,
                text: errorMessage,
                icon: icon,
                confirmButtonColor: '#2563eb'
            })
        }
    }
}

function closeForm() {
    showCreateForm.value = false
    showEditForm.value = false
    selectedUser.value = null
    formData.value = { ...defaultFormData }
}

const copyId = (id: string | undefined) => {
    if (!id) return;
    const textArea = document.createElement("textarea");
    textArea.value = id;
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '0';
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        copiedTooltip.value = true;
        setTimeout(() => { copiedTooltip.value = false; }, 2000);
    } catch (err) {
        console.error('Failed to copy ID: ', err);
    }
    document.body.removeChild(textArea);
}

const formatFullName = (user: { firstName: string, middleName?: string, lastName: string }) => {
    return [user.firstName, user.middleName, user.lastName].filter(Boolean).join(' ');
}

// Use server results directly
const filteredUsers = computed(() => users.value)

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(totalResults.value / itemsPerPage.value))
})

const paginatedUsers = computed(() => users.value)

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
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
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
    totalResults.value = Number(data.total ?? users.value.length)
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
            isCliniciansAccordionOpen.value = false;
            showAllClinicians.value = false;
        }
    }
})

const getUserInitials = (user: { firstName: string, lastName: string }) => {
    const firstInitial = user.firstName ? user.firstName.charAt(0) : '';
    const lastInitial = user.lastName ? user.lastName.charAt(0) : '';
    return `${firstInitial}${lastInitial}`.toUpperCase();
}

const generateRandomPassword = () => {
    const length = 14;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
    let password = "";
    for (let i = 0, n = charset.length; i < length; ++i) {
        password += charset.charAt(Math.floor(Math.random() * n));
    }
    formData.value.password = password;
}

</script>
