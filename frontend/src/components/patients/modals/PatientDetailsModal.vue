<template>
    <BaseModal v-model="showModal" title="Patient Information" size="lg">
        <template v-if="patient">
            <!-- Loading Indicator -->
            <div v-if="isLoadingDetails" class="flex items-center justify-center py-8">
                <div class="w-6 h-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin mr-3">
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Loading patient details...</span>
            </div>
            <div class="space-y-4 sm:space-y-6">
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl sm:rounded-2xl p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-5">
                        <div
                            class="h-16 w-16 sm:h-20 sm:w-20 bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-sm flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xl sm:text-2xl mx-auto sm:mx-0">
                            {{ getPatientInitials(patient.patient_name) }}
                        </div>
                        <div class="flex-1 text-center sm:text-left mt-4 sm:mt-0">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ patient.patient_name }}</h2>
                            <div class="flex items-center justify-center sm:justify-start mt-2">
                                <span class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <Mail class="w-4 h-4 mr-1.5 text-gray-400" />
                                    {{ patient.email }}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center justify-center sm:justify-start space-x-2">
                                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 text-xs font-medium rounded-full">
                                    PID: {{ generatePatientPID(patient.patient_id) }}
                                </span>
                                <span v-if="patient.ivr_count > 0"
                                    class="px-3 py-1 bg-purple-100 dark:bg-purple-800 text-purple-700 dark:text-purple-300 text-xs font-medium rounded-full">
                                    {{ patient.ivr_count }} IVR Records
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
                            <div
                                class="h-10 w-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <UserCircle class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Created By</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                    patient.user_name || 'System' }}</p>
                                <p v-if="patient.user_role !== null"
                                    class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ getRoleLabel(patient.user_role) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Created Date -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3 h-full">
                            <div class="h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <Calendar class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="flex flex-col justify-center">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Created Date</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDateTime(patient.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Last Updated -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                                <Clock class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Last Updated</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ patient.updated_at ? formatDate(patient.updated_at) : 'No Records Found' }}</p>
                                <p v-if="patient.updated_by_user_name" class="text-xs text-gray-500 dark:text-gray-400">
                                    by {{ patient.updated_by_user_name }}
                                    <span v-if="patient.updated_by_user_role !== null">({{
                                        getRoleLabel(patient.updated_by_user_role) }})</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- IVR Records -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <ClipboardList class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    IVR Records</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                    patient.ivr_count }} Total Records</p>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Clinician -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                <Stethoscope class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Assigned To</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ patient.assigned_clinician_name || 'Unassigned' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clinic Info (Admin Only) -->
                <div v-if="isAdmin && patient.clinic_name" class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                    <div class="flex items-start space-x-3">
                        <div class="h-10 w-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                            <Hospital class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Associated Clinic</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ patient.clinic_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                This patient is associated with the above clinic facility.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Quick Actions</p>
                    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-3">
                        <button @click="$emit('edit', patient)"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors text-sm font-medium">
                            <FilePenLine class="w-4 h-4 mr-1 sm:mr-2" />
                            <span>Edit Patient</span>
                        </button>
                        <button v-if="!isOfficeStaff && !isClinician" @click="$emit('delete', patient.patient_id)"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors text-sm font-medium">
                            <Trash2 class="w-4 h-4 mr-1 sm:mr-2" />
                            <span>Delete Patient</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
    Mail,
    UserCircle,
    Calendar,
    Clock,
    ClipboardList,
    Stethoscope,
    Hospital,
    FilePenLine,
    Trash2
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
    assigned_clinician_id: number | null
    assigned_clinician_name: string | null
    created_at: string
    updated_at: string
    deleted_at: string | null
    ivr_count: number
}

const props = defineProps<{
    modelValue: boolean
    patient: Patient | null
    isLoadingDetails: boolean
    isClinician: boolean
    isAdmin: boolean
    isOfficeStaff: boolean
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'edit', patient: Patient): void
    (e: 'delete', patientId: number): void
}>()

const showModal = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const getPatientInitials = (name: string) => {
    if (!name) return ''
    const parts = name.split(' ')
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
}

const generatePatientPID = (dbId: number | null): string => {
    if (!dbId) return 'PID-000000'

    const idString = String(dbId)

    let hash = 0
    for (let i = 0; i < idString.length; i++) {
        const char = idString.charCodeAt(i)
        hash = ((hash << 5) - hash) + char
        hash = hash & hash
    }

    const positiveHash = Math.abs(hash)
    const randomNumber = String(positiveHash % 1000000).padStart(6, '0')

    return `PID-${randomNumber}`
}

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-'
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const formatDateTime = (dateStr: string | null) => {
    if (!dateStr) return '-'
    const date = new Date(dateStr)
    const datePart = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    const timePart = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }).replace(' ', '')
    return `${datePart} [${timePart}]`
}

const getRoleLabel = (role: number | null): string => {
    if (role === null || role === undefined) return ''
    switch (role) {
        case 0: return 'Admin'
        case 1: return 'Office Staff'
        case 2: return 'Clinic'
        case 3: return 'Clinician'
        case 4: return 'Manufacturer'
        case 5: return 'Biller'
        default: return 'Unknown'
    }
}
</script>
