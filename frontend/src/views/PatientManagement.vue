<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Patient Management</h1>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl">Manage patient information, view patient details, and track patient records in one centralized dashboard</p>
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
                    Add Patient
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Patient Statistics</h3>

                <!-- Main Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <Users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Patients</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <Calendar class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.newThisMonth }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">New This Month</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                            <Clock class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.recentlyAdded }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Added This Week</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div
                            class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                            <ClipboardList class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.withIvrRecords }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">With IVR Records</p>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="relative">
                <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
                <input v-model="searchTerm" type="text" placeholder="Search by patient name or email..."
                    class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
            </div>
        </div>

        <!-- Patients Table Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Patient
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Created By
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                IVR Records
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
                        <tr v-for="patient in paginatedPatients" :key="patient.patient_id"
                            class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 font-medium text-sm">
                                        {{ getPatientInitials(patient.patient_name) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ patient.patient_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ patient.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-green-100 to-teal-100 dark:from-green-900/30 dark:to-teal-900/30 rounded-full flex items-center justify-center text-green-600 dark:text-green-400 font-medium text-xs">
                                        {{ getUserInitials(patient.user_name) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ patient.user_name || 'System' }}</p>
                                        <p v-if="patient.user_role !== null" class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ getRoleLabel(patient.user_role) }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400">
                                    <ClipboardList class="w-3 h-3 mr-1.5" />
                                    {{ patient.ivr_count }} Records
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(patient.created_at) }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button @click="viewPatient(patient)"
                                        class="p-2 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all duration-200"
                                        title="View Details">
                                        <Eye class="w-4 h-4" />
                                    </button>
                                    <button @click="editPatient(patient)"
                                        class="p-2 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-all duration-200"
                                        title="Edit Patient">
                                        <FilePenLine class="w-4 h-4" />
                                    </button>
                                    <button @click="handleDeletePatient(patient.patient_id)"
                                        class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200"
                                        title="Delete Patient">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredPatients.length === 0 && !tableLoader">
                            <td colspan="5" class="text-center text-gray-400 py-12">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <Users class="w-10 h-10 mb-1" />
                                    <span>No patients found.</span>
                                </div>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="filteredPatients.length > 0"
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

        <!-- Patient Details Modal -->
        <BaseModal v-model="showPatientDetailsModal" title="Patient Information" size="lg">
            <template v-if="selectedPatient">
                <!-- Loading Indicator -->
                <div v-if="isLoadingDetails" class="flex items-center justify-center py-8">
                    <div class="w-6 h-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin mr-3"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Loading patient details...</span>
                </div>
                <div class="space-y-6">
                    <!-- Header Card -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6">
                        <div class="flex items-center space-x-5">
                            <div
                                class="h-20 w-20 bg-white dark:bg-gray-800 rounded-2xl shadow-sm flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-2xl">
                                {{ getPatientInitials(selectedPatient.patient_name) }}
                            </div>
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ selectedPatient.patient_name }}</h2>
                                <div class="flex items-center mt-2 space-x-4">
                                    <span class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <Mail class="w-4 h-4 mr-1.5 text-gray-400" />
                                        {{ selectedPatient.email }}
                                    </span>
                                </div>
                                <div class="mt-3 flex items-center space-x-2">
                                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 text-xs font-medium rounded-full">
                                        ID: #{{ selectedPatient.patient_id }}
                                    </span>
                                    <span v-if="selectedPatient.ivr_count > 0" class="px-3 py-1 bg-purple-100 dark:bg-purple-800 text-purple-700 dark:text-purple-300 text-xs font-medium rounded-full">
                                        {{ selectedPatient.ivr_count }} IVR Records
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Created By -->
                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <UserCircle class="w-5 h-5 text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Created By</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedPatient.user_name || 'System' }}</p>
                                    <p v-if="selectedPatient.user_role !== null" class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ getRoleLabel(selectedPatient.user_role) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Created Date -->
                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                    <Calendar class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Created Date</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(selectedPatient.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Last Updated -->
                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                                    <Clock class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Last Updated</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(selectedPatient.updated_at) }}</p>
                                    <p v-if="selectedPatient.updated_by_user_name" class="text-xs text-gray-500 dark:text-gray-400">
                                        by {{ selectedPatient.updated_by_user_name }}
                                        <span v-if="selectedPatient.updated_by_user_role !== null">({{ getRoleLabel(selectedPatient.updated_by_user_role) }})</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- IVR Records -->
                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <ClipboardList class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">IVR Records</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedPatient.ivr_count }} Total Records</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Quick Actions</p>
                        <div class="flex flex-wrap gap-3">
                            <button @click="editPatient(selectedPatient); selectedPatient = null"
                                class="inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors text-sm font-medium">
                                <FilePenLine class="w-4 h-4 mr-2" />
                                Edit Patient
                            </button>
                            <button @click="handleDeletePatient(selectedPatient.patient_id); selectedPatient = null"
                                class="inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors text-sm font-medium">
                                <Trash2 class="w-4 h-4 mr-2" />
                                Delete Patient
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </BaseModal>

        <!-- Create/Edit Patient Form Modal -->
        <BaseModal v-model="showFormModal" :title="showCreateForm ? 'Add New Patient' : 'Edit Patient'" size="lg">
            <form @submit.prevent="handleSubmitForm" class="space-y-6">
                <!-- Loading Indicator (only shown when editing and loading) -->
                <div v-if="!showCreateForm && isLoadingDetails" class="flex items-center justify-center py-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <div class="w-6 h-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin mr-3"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Loading fresh data...</span>
                </div>
                <!-- Patient Header (only shown when editing) -->
                <div v-if="!showCreateForm && selectedPatient" class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-12 w-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-lg">
                            {{ getPatientInitials(selectedPatient.patient_name) }}
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Editing Patient</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ selectedPatient.patient_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <UserCircle class="w-4 h-4 mr-2 text-gray-400" />
                                Patient Name<span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input v-model="formData.patient_name" type="text" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter patient name" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <Mail class="w-4 h-4 mr-2 text-gray-400" />
                                Email Address<span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input v-model="formData.email" type="email" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter email address" />
                    </div>

                    <!-- Clinic Selection (Admin only when creating) -->
                    <div v-if="showCreateForm && isAdmin">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <Hospital class="w-4 h-4 mr-2 text-gray-400" />
                                Clinic<span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <div class="relative">
                            <select v-model="formData.clinic_id" required
                                :disabled="isLoadingClinics"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                                <option value="" disabled>Select a clinic</option>
                                <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
                                    {{ clinic.name }}
                                </option>
                            </select>
                            <div v-if="isLoadingClinics" class="absolute right-10 top-3.5">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 border-t-blue-600 animate-spin"></div>
                            </div>
                            <ChevronDown v-else class="absolute right-4 top-3.5 h-5 w-5 text-gray-400 pointer-events-none" />
                        </div>
                        <p v-if="clinics.length === 0 && !isLoadingClinics" class="text-xs text-amber-600 mt-1">
                            No clinics available. Please create a clinic first.
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="closeForm"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md">
                        <Save class="w-4 h-4 mr-2" />
                        {{ showCreateForm ? 'Add Patient' : 'Save Changes' }}
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
import { patientService } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import Swal from 'sweetalert2'
import {
    Plus,
    Search,
    Eye,
    FilePenLine,
    Trash2,
    ChevronDown,
    Users,
    BarChart2,
    ClipboardList,
    Mail,
    UserCircle,
    Calendar,
    Clock,
    Save,
    Hospital,
} from 'lucide-vue-next'

interface Patient {
    patient_id: number
    patient_name: string
    email: string
    clinic_id: number | null
    clinic_name: string | null
    user_id: number | null
    user_name: string | null
    user_role: number | null
    updated_by_user_id: number | null
    updated_by_user_name: string | null
    updated_by_user_role: number | null
    created_at: string
    updated_at: string
    deleted_at: string | null
    ivr_count: number
}

const patients = ref<Patient[]>([])
const authStore = useAuthStore()

const searchTerm = ref('')
const tableLoader = ref(false)
const selectedPatient = ref<Patient | null>(null)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalResults = ref(0)
const showStats = ref(false)
const isLoadingDetails = ref(false)

const defaultFormData = {
    patient_name: '',
    email: '',
    clinic_id: '',
}

const formData = ref({ ...defaultFormData })

// Clinics list for admin dropdown
const clinics = ref<{ id: number; name: string }[]>([])
const isLoadingClinics = ref(false)

// Check if current user is admin (role 0)
const isAdmin = computed(() => authStore.user?.user_role === 0)

// Stats
const stats = ref({
    total: 0,
    newThisMonth: 0,
    recentlyAdded: 0,
    withIvrRecords: 0,
})

// Use server results directly
const filteredPatients = computed(() => patients.value)
const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredPatients.value.length / itemsPerPage.value))
})

const paginatedPatients = computed(() => {
    // Since pagination is handled by the backend, we just return all patients
    return filteredPatients.value
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

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getPatientInitials = (name: string) => {
    if (!name) return ''
    const parts = name.split(' ')
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
}

const getUserInitials = (name: string | null) => {
    if (!name) return 'S'
    const parts = name.split(' ')
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
}

const getRoleLabel = (role: number | null): string => {
    if (role === null || role === undefined) return ''
    switch (role) {
        case 0: return 'Admin'
        case 1: return 'Office Staff'
        case 2: return 'Clinic User'
        case 3: return 'Manufacturer'
        default: return 'Unknown'
    }
}

async function handleDeletePatient(patientId: number) {
    const patient = patients.value.find(p => p.patient_id === patientId)
    if (!patient) return

    const result = await Swal.fire({
        title: 'Delete Patient',
        html: `Are you sure you want to delete <strong>${patient.patient_name}</strong>?<br><span style="color: #dc2626;">This action cannot be undone!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    })

    if (!result.isConfirmed) return

    try {
        await patientService.deletePatient(patientId)
        patients.value = patients.value.filter(p => p.patient_id !== patientId)
        await fetchPatientStats()

        await Swal.fire({
            title: 'Deleted!',
            text: 'Patient has been deleted successfully.',
            icon: 'success',
            confirmButtonColor: '#2563eb',
            timer: 2000,
            showConfirmButton: false
        })
    } catch (err) {
        console.error('Failed to delete patient', err)
        await Swal.fire({
            title: 'Error!',
            text: 'Failed to delete patient. Please try again.',
            icon: 'error',
            confirmButtonColor: '#2563eb'
        })
    }
}

async function viewPatient(patient: Patient) {
    // Open modal immediately with basic data for better UX
    selectedPatient.value = patient
    isLoadingDetails.value = true

    try {
        // Fetch full details from server (HIPAA compliant with audit logging)
        const { data } = await patientService.getPatientById(patient.patient_id)

        // Update with full details
        if (data.patient) {
            selectedPatient.value = {
                ...patient,
                ...data.patient
            }
        }
    } catch (error) {
        console.error('Error fetching patient details:', error)
        // Keep the basic data visible even if detail fetch fails
    } finally {
        isLoadingDetails.value = false
    }
}

async function editPatient(patient: Patient) {
    // Open modal immediately with basic data for better UX
    selectedPatient.value = patient
    formData.value = {
        patient_name: patient.patient_name,
        email: patient.email,
        clinic_id: '',
    }
    showEditForm.value = true
    isLoadingDetails.value = true

    try {
        // Fetch full details from server (HIPAA compliant with audit logging)
        const { data } = await patientService.getPatientById(patient.patient_id)

        // Update with fresh data from server
        if (data.patient) {
            const freshData = data.patient
            selectedPatient.value = { ...patient, ...freshData }
            // Update form fields with fresh data
            formData.value = {
                patient_name: freshData.patient_name || patient.patient_name,
                email: freshData.email || patient.email,
                clinic_id: '',
            }
        }
    } catch (error) {
        console.error('Error fetching patient details for edit:', error)
        // Keep the basic data in the form even if detail fetch fails
    } finally {
        isLoadingDetails.value = false
    }
}

async function handleSubmitForm() {
    if (showCreateForm.value) {
        try {
            // Build payload - admin can select clinic, others use backend auto-populate
            const payload: { patient_name: string; email: string; clinic_id?: string } = {
                patient_name: formData.value.patient_name,
                email: formData.value.email,
            }
            // Admin users can specify clinic_id
            if (isAdmin.value && formData.value.clinic_id) {
                payload.clinic_id = formData.value.clinic_id
            }
            const { data } = await patientService.createPatient(payload)
            const p = data.patient
            const newPatient: Patient = {
                patient_id: p.patient_id,
                patient_name: p.patient_name,
                email: p.email,
                clinic_id: p.clinic_id,
                clinic_name: p.clinic_name,
                user_id: p.user_id,
                user_name: p.user_name,
                user_role: p.user_role,
                updated_by_user_id: p.updated_by_user_id,
                updated_by_user_name: p.updated_by_user_name,
                updated_by_user_role: p.updated_by_user_role,
                created_at: p.created_at,
                updated_at: p.updated_at,
                deleted_at: p.deleted_at,
                ivr_count: p.ivr_count || 0,
            }
            patients.value.unshift(newPatient)
            await fetchPatientStats()

            await Swal.fire({
                title: 'Patient Added Successfully!',
                text: `${newPatient.patient_name} has been added to the system.`,
                icon: 'success',
                confirmButtonColor: '#2563eb',
                timer: 2000,
                showConfirmButton: false
            })

            closeForm()
        } catch (err: any) {
            console.error('Failed to create patient', err)
            const errorMessage = err.response?.data?.message || 'Failed to add patient. Please try again.'
            await Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#2563eb'
            })
        }
    } else if (showEditForm.value && selectedPatient.value) {
        try {
            // When editing, only send patient_name and email - clinic_id cannot be changed
            const payload = {
                patient_name: formData.value.patient_name,
                email: formData.value.email,
            }
            const { data } = await patientService.updatePatient(selectedPatient.value.patient_id, payload)
            const p = data.patient
            const updated: Patient = {
                patient_id: p.patient_id,
                patient_name: p.patient_name,
                email: p.email,
                clinic_id: p.clinic_id,
                clinic_name: p.clinic_name,
                user_id: p.user_id,
                user_name: p.user_name,
                user_role: p.user_role,
                updated_by_user_id: p.updated_by_user_id,
                updated_by_user_name: p.updated_by_user_name,
                updated_by_user_role: p.updated_by_user_role,
                created_at: p.created_at,
                updated_at: p.updated_at,
                deleted_at: p.deleted_at,
                ivr_count: p.ivr_count || 0,
            }
            const idx = patients.value.findIndex(x => x.patient_id === updated.patient_id)
            if (idx !== -1) patients.value[idx] = updated
            await fetchPatientStats()

            await Swal.fire({
                title: 'Success!',
                text: 'Patient has been updated successfully.',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                timer: 2000,
                showConfirmButton: false
            })

            closeForm()
        } catch (err: any) {
            console.error('Failed to update patient', err)
            const errorMessage = err.response?.data?.message || 'Failed to update patient. Please try again.'
            await Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#2563eb'
            })
        }
    }
}

function closeForm() {
    showCreateForm.value = false
    showEditForm.value = false
    selectedPatient.value = null
    formData.value = { ...defaultFormData }
}

const fetchClinics = async () => {
    if (!isAdmin.value) return
    isLoadingClinics.value = true
    try {
        const { data } = await patientService.getPatientClinics()
        clinics.value = data.clinics || []
    } catch (err) {
        console.error('Failed to fetch clinics', err)
        clinics.value = []
    } finally {
        isLoadingClinics.value = false
    }
}

// Watch for create form opening to fetch clinics
watch(showCreateForm, (isOpen) => {
    if (isOpen && isAdmin.value) {
        fetchClinics()
    }
})

const fetchPatients = async () => {
    tableLoader.value = true
    try {
        const params = {
            search: searchTerm.value || undefined,
            page: currentPage.value,
        }
        const { data } = await patientService.getPatients(params)
        const incoming: any[] = Array.isArray(data.patients) ? data.patients : []
        patients.value = incoming.map((p) => ({
            patient_id: p.patient_id,
            patient_name: p.patient_name,
            email: p.email,
            clinic_id: p.clinic_id,
            clinic_name: p.clinic_name,
            user_id: p.user_id,
            user_name: p.user_name,
            user_role: p.user_role,
            updated_by_user_id: p.updated_by_user_id,
            updated_by_user_name: p.updated_by_user_name,
            updated_by_user_role: p.updated_by_user_role,
            created_at: p.created_at,
            updated_at: p.updated_at,
            deleted_at: p.deleted_at,
            ivr_count: p.ivr_count || 0,
        }))
        totalResults.value = data.total
        if (data.per_page) itemsPerPage.value = Number(data.per_page)
    } catch (err) {
        console.error('Failed to fetch patients', err)
    } finally {
        tableLoader.value = false
    }
}

const fetchPatientStats = async () => {
    try {
        const { data } = await patientService.getPatientStats()
        stats.value = {
            total: Number(data.total ?? 0),
            newThisMonth: Number(data.new_this_month ?? 0),
            recentlyAdded: Number(data.recently_added ?? 0),
            withIvrRecords: Number(data.with_ivr_records ?? 0),
        }
    } catch (err) {
        console.error('Failed to fetch stats', err)
    }
}



onMounted(() => {
    Promise.all([
        fetchPatients(),
        fetchPatientStats(),
    ]).catch((err) => { console.error('Failed initial load', err) })
})

watch(searchTerm, () => {
    currentPage.value = 1
    fetchPatients().catch((err) => { console.error('Failed to fetch patients on filters change', err) })
})

watch(currentPage, () => {
    fetchPatients().catch((err) => { console.error('Failed to fetch patients on page change', err) })
})

const showFormModal = computed({
    get: () => showCreateForm.value || showEditForm.value,
    set: (value: boolean) => {
        if (!value) {
            closeForm()
        }
    }
})

const showPatientDetailsModal = computed({
    get: () => selectedPatient.value !== null && !showEditForm.value,
    set: (value: boolean) => {
        if (!value) {
            selectedPatient.value = null
        }
    }
})

// HIPAA Compliance: Clear sensitive data from memory when view modal closes
watch(showPatientDetailsModal, (isOpen) => {
    if (!isOpen) {
        // Clear selected patient data after a short delay to allow modal close animation
        setTimeout(() => {
            selectedPatient.value = null
        }, 300)
    }
})

// HIPAA Compliance: Clear sensitive data from memory when edit modal closes
watch(showFormModal, (isOpen) => {
    if (!isOpen) {
        // Clear form data after a short delay to allow modal close animation
        setTimeout(() => {
            selectedPatient.value = null
            formData.value = { ...defaultFormData }
        }, 300)
    }
})
</script>
