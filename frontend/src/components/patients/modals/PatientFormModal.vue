<template>
    <BaseModal v-model="showModal" :title="isCreate ? 'Add New Patient' : 'Edit Patient'" size="lg">
        <form @submit.prevent="handleSubmit" class="space-y-4 sm:space-y-6">
            <!-- Loading Indicator (only shown when editing and loading) -->
            <div v-if="!isCreate && isLoadingDetails"
                class="flex items-center justify-center py-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                <div class="w-6 h-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin mr-3">
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Loading fresh data...</span>
            </div>
            <!-- Patient Header (only shown when editing) -->
            <div v-if="!isCreate && selectedPatient" class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-3 sm:p-4">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl shadow-sm flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-base sm:text-lg">
                        {{ getPatientInitials(selectedPatient.patient_name) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Editing Patient</p>
                        <p class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">{{ selectedPatient.patient_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="space-y-4 sm:space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span class="flex items-center">
                            <UserCircle class="w-4 h-4 mr-2 text-gray-400" />
                            Patient Name<span class="text-red-500 ml-1">*</span>
                        </span>
                    </label>
                    <input v-model="localFormData.patient_name" type="text" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                        placeholder="Enter patient name" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span class="flex items-center">
                            <Mail class="w-4 h-4 mr-2 text-gray-400" />
                            Email Address<span class="text-red-500 ml-1">*</span>
                        </span>
                    </label>
                    <input v-model="localFormData.email" type="email" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                        placeholder="Enter email address" />
                </div>

                <!-- Clinic Selection (Admin & Office Staff - both Create and Edit) -->
                <div v-if="isAdmin || isOfficeStaff">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span class="flex items-center">
                            <Hospital class="w-4 h-4 mr-2 text-gray-400" />
                            Clinic<span class="text-red-500 ml-1">*</span>
                        </span>
                    </label>
                    <div class="relative">
                        <select v-model="localFormData.clinic_id" required
                            :disabled="isLoadingClinics"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="" disabled>Select a clinic</option>
                            <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
                                {{ clinic.name }}
                            </option>
                        </select>
                        <div v-if="isLoadingClinics" class="absolute right-10 top-3.5">
                            <div
                                class="w-5 h-5 rounded-full border-2 border-gray-300 border-t-blue-600 animate-spin">
                            </div>
                        </div>
                        <ChevronDown v-else
                            class="absolute right-4 top-3.5 h-5 w-5 text-gray-400 pointer-events-none" />
                    </div>
                    <p v-if="clinics.length === 0 && !isLoadingClinics" class="text-xs text-amber-600 mt-1">
                        No clinics available. Please create a clinic first.
                    </p>
                </div>

                <!-- Assigned Clinician (Clinic users only - both Create and Edit) -->
                <div v-if="isClinicUser">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span class="flex items-center">
                            <Stethoscope class="w-4 h-4 mr-2 text-gray-400" />
                            Assigned To
                            <span class="ml-2 text-xs text-gray-400 font-normal">(optional)</span>
                        </span>
                    </label>
                    <div class="relative">
                        <select v-model="localFormData.assigned_clinician_id"
                            :disabled="isLoadingAssignableClinicians"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option value="">Unassigned</option>
                            <option v-for="c in assignableClinicians" :key="c.id" :value="String(c.id)">
                                {{ c.full_name }}
                            </option>
                        </select>
                        <div v-if="isLoadingAssignableClinicians" class="absolute right-10 top-3.5">
                            <div
                                class="w-5 h-5 rounded-full border-2 border-gray-300 border-t-blue-600 animate-spin">
                            </div>
                        </div>
                        <ChevronDown v-else
                            class="absolute right-4 top-3.5 h-5 w-5 text-gray-400 pointer-events-none" />
                    </div>
                    <p v-if="assignableClinicians.length === 0 && !isLoadingAssignableClinicians" class="text-xs text-amber-600 mt-1">
                        No clinicians available for your clinic.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 sm:gap-0 sm:space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="handleCancel"
                    class="w-full sm:w-auto px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                    Cancel
                </button>
                <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md">
                    <Save class="w-4 h-4 mr-2" />
                    {{ isCreate ? 'Add Patient' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
    UserCircle,
    Mail,
    Hospital,
    Stethoscope,
    ChevronDown,
    Save
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

interface FormData {
    patient_name: string
    email: string
    clinic_id: string
    assigned_clinician_id: string
}

const props = defineProps<{
    modelValue: boolean
    isCreate: boolean
    formData: FormData
    selectedPatient: Patient | null
    clinics: { id: number; name: string }[]
    assignableClinicians: { id: number; full_name: string }[]
    isLoadingDetails: boolean
    isLoadingClinics: boolean
    isLoadingAssignableClinicians: boolean
    isAdmin: boolean
    isOfficeStaff?: boolean
    isClinicUser: boolean
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'submit', data: FormData): void
    (e: 'cancel'): void
}>()

const localFormData = ref<FormData>({ ...props.formData })

const showModal = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

// Watch for prop changes to sync local form data
watch(() => props.formData, (newVal) => {
    localFormData.value = { ...newVal }
}, { deep: true })

const getPatientInitials = (name: string) => {
    if (!name) return ''
    const parts = name.split(' ')
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
}

const handleSubmit = () => {
    emit('submit', { ...localFormData.value })
}

const handleCancel = () => {
    emit('cancel')
}
</script>
