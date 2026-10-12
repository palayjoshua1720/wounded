<template>
    <BaseModal v-model="showModal" :title="isCreate ? 'Create New User' : 'Edit User'" size="2xl">
        <form @submit.prevent="handleSubmit" class="space-y-4 sm:space-y-5">
            <!-- User Header (only shown when editing) -->
            <div v-if="!isCreate && selectedUser"
                class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-3 sm:p-4">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <div
                        class="h-10 w-10 sm:h-12 sm:w-12 bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl shadow-sm flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-base sm:text-lg">
                        {{ getUserInitials(selectedUser) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Editing User</p>
                        <p class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">
                            {{ formatFullName(selectedUser) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="space-y-4 sm:space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <UserCircle class="w-4 h-4 mr-2 text-gray-400" />
                                First Name<span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input v-model="localFormData.firstName" type="text" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter first name" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <UserCircle class="w-4 h-4 mr-2 text-gray-400" />
                                Middle Name
                            </span>
                        </label>
                        <input v-model="localFormData.middleName" type="text"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter middle name" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <UserCircle class="w-4 h-4 mr-2 text-gray-400" />
                                Last Name<span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input v-model="localFormData.lastName" type="text" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter last name" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <Mail class="w-4 h-4 mr-2 text-gray-400" />
                                Email<span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input v-model="localFormData.email" type="email" required
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter email address" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center">
                                <Phone class="w-4 h-4 mr-2 text-gray-400" />
                                Phone
                            </span>
                        </label>
                        <input v-model="localFormData.phone" type="tel"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                            placeholder="Enter phone number" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <span class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <IdCardLanyard class="w-4 h-4 mr-2 text-gray-400" />
                            Role<span class="text-red-500 ml-1">*</span>
                        </span>
                        <div class="relative">
                            <select v-model="localFormData.role" required :disabled="isRoleDisabled"
                                class="w-full px-3 sm:px-4 pr-10 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed">
                                <option v-for="r in availableRoles" :key="r.value" :value="r.value">
                                    {{ r.label }}
                                </option>
                            </select>
                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                        </div>
                        <p v-if="isRoleDisabled" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                            {{ isEditingSelf ? 'You cannot modify your own user role.' : 'User role cannot be modified.' }}
                        </p>
                    </div>
                    <div>
                        <span class="flex items-center mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <Loader class="w-4 h-4 mr-2 text-gray-400" />
                            Status<span class="text-red-500 ml-1">*</span>
                        </span>
                        <div class="relative">
                            <select v-model="localFormData.isActive" required :disabled="isEditingSelf"
                                class="w-full px-3 sm:px-4 pr-10 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed">
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                        </div>
                        <p v-if="isEditingSelf" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                            You cannot modify your own active status.
                        </p>
                    </div>
                </div>

                <div v-if="localFormData.role === 'clinic'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Clinic Facility
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Select the clinic facility this user
                        will
                        manage</p>
                    <div class="relative">
                        <select v-model="localFormData.clinicId" :required="localFormData.role === 'clinic'"
                            class="w-full px-3 sm:px-4 pr-10 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option disabled value="">Select a clinic facility</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                        <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                </div>

                <div v-if="localFormData.role === 'manufacturer'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Manufacturer<span
                            class="text-red-500 ml-1">*</span></label>
                    <div class="relative">
                        <select v-model="localFormData.manufacturerId" :required="localFormData.role === 'manufacturer'"
                            class="w-full px-3 sm:px-4 pr-10 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option disabled value="">Select a manufacturer</option>
                            <option v-for="manu in manufacturers" :key="manu.id" :value="manu.id">
                                {{ manu.name }}
                            </option>
                        </select>
                        <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                </div>

                <div v-if="localFormData.role === 'clinician'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Associated Clinic
                        Facility
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Select the clinic facility where this
                        clinician
                        works</p>
                    <div v-if="isClinicAdmin" class="px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 text-sm flex items-center justify-between">
                        <span class="font-medium">{{ userClinicName }}</span>
                        <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-md font-medium">Your Clinic Facility</span>
                    </div>
                    <div v-else class="relative">
                        <select v-model="localFormData.clinicId" :required="localFormData.role === 'clinician'"
                            class="w-full px-3 sm:px-4 pr-10 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200">
                            <option disabled value="">Select a clinic facility</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                        <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
                    </div>
                </div>

                <div v-if="isCreate">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            The user will receive a welcome email with a link to set their own password.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 sm:space-x-3 sm:gap-0 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="handleCancel" :disabled="isSubmitting"
                    class="w-full sm:w-auto px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    Cancel
                </button>
                <button type="submit" :disabled="isSubmitting"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md disabled:opacity-70 disabled:cursor-not-allowed sm:min-w-[140px]">
                    <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
                    <Save v-else class="w-4 h-4 mr-2" />
                    {{ isSubmitting ? 'Saving...' : (isCreate ? 'Create User' : 'Save Changes') }}
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
    Phone,
    IdCardLanyard,
    Loader,
    ChevronDown,
    Loader2,
    Save
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

interface FormData {
    firstName: string
    middleName: string
    lastName: string
    email: string
    phone: string
    role: 'admin' | 'office-staff' | 'clinic' | 'clinician' | 'manufacturer' | 'biller'
    isActive: boolean
    clinicId: string
    manufacturerId: string
}

const props = withDefaults(defineProps<{
    modelValue: boolean
    isCreate: boolean
    formData: FormData
    selectedUser: User | null
    departments: { id: string; name: string }[]
    manufacturers: { id: string; name: string }[]
    isOfficeStaff?: boolean
    currentUserRole?: number
    currentUserClinicId?: string
    currentUserId?: string
    isSubmitting: boolean
}>(), {
    isOfficeStaff: false,
    currentUserRole: -1,
    currentUserClinicId: '',
    currentUserId: '',
})

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

const isEditingSelf = computed(() => {
    return !props.isCreate && !!props.selectedUser && !!props.currentUserId && String(props.selectedUser.id) === String(props.currentUserId)
})

const isClinicAdmin = computed(() => {
    return props.currentUserRole === 2
})

const isRoleDisabled = computed(() => {
    if (isEditingSelf.value) return true
    // Clinic Admins can only manage clinicians, so when editing a clinician, role cannot be modified
    if (props.currentUserRole === 2 && !props.isCreate) return true
    return false
})

const userClinicName = computed(() => {
    if (props.currentUserClinicId) {
        const found = props.departments.find(d => String(d.id) === String(props.currentUserClinicId))
        if (found) return found.name
    }
    if (props.selectedUser?.clinicId) {
        const found = props.departments.find(d => String(d.id) === String(props.selectedUser?.clinicId))
        if (found) return found.name
    }
    return props.selectedUser?.department || 'Your Assigned Clinic Facility'
})

const getRoleLabel = (role: string) => {
    const labels: Record<string, string> = {
        'admin': 'Administrator',
        'office-staff': 'Office Staff',
        'clinic': 'Clinic Administrator',
        'clinician': 'Clinician (Medical Staff)',
        'manufacturer': 'Manufacturer',
        'biller': 'Biller'
    }
    return labels[role] || role
}

// Available roles strictly enforced by current user's role hierarchy
const availableRoles = computed(() => {
    const role = props.currentUserRole

    // If editing self, only show their current role
    if (isEditingSelf.value) {
        return [
            { value: localFormData.value.role, label: getRoleLabel(localFormData.value.role) }
        ]
    }

    // Clinic Admin (2): Can ONLY assign Clinician (3)
    if (role === 2) {
        return [
            { value: 'clinician', label: 'Clinician (Medical Staff)' }
        ]
    }

    // Office Staff (1): Can assign all except Admin
    if (role === 1 || props.isOfficeStaff) {
        return [
            { value: 'clinic', label: 'Clinic Administrator' },
            { value: 'clinician', label: 'Clinician (Medical Staff)' },
            { value: 'manufacturer', label: 'Manufacturer' },
            { value: 'biller', label: 'Biller' },
        ]
    }

    // Super Admin (0): Can assign all roles
    if (role === 0) {
        return [
            { value: 'admin', label: 'Administrator' },
            { value: 'office-staff', label: 'Office Staff' },
            { value: 'clinic', label: 'Clinic Administrator' },
            { value: 'clinician', label: 'Clinician (Medical Staff)' },
            { value: 'manufacturer', label: 'Manufacturer' },
            { value: 'biller', label: 'Biller' },
        ]
    }

    // Fallback default
    return [
        { value: 'clinician', label: 'Clinician (Medical Staff)' }
    ]
})

// Watch for prop changes to sync local form data
watch(() => props.formData, (newVal) => {
    localFormData.value = { ...newVal }
    if (isClinicAdmin.value && props.currentUserClinicId) {
        localFormData.value.clinicId = props.currentUserClinicId
    }
}, { deep: true })

// Watch for role changes to clear related fields
watch(() => localFormData.value.role, (newRole) => {
    if (newRole !== 'clinic' && newRole !== 'clinician') {
        localFormData.value.clinicId = ''
    } else if (isClinicAdmin.value && props.currentUserClinicId) {
        localFormData.value.clinicId = props.currentUserClinicId
    }
    if (newRole !== 'manufacturer') {
        localFormData.value.manufacturerId = ''
    }
})

const getUserInitials = (user: { firstName: string, lastName: string }) => {
    const firstInitial = user.firstName ? user.firstName.charAt(0) : ''
    const lastInitial = user.lastName ? user.lastName.charAt(0) : ''
    return `${firstInitial}${lastInitial}`.toUpperCase()
}

const formatFullName = (user: { firstName: string, middleName?: string, lastName: string }) => {
    return [user.firstName, user.middleName, user.lastName].filter(Boolean).join(' ')
}

const handleSubmit = () => {
    const submitData = { ...localFormData.value }
    if (isClinicAdmin.value && props.currentUserClinicId) {
        submitData.clinicId = props.currentUserClinicId
    }
    emit('submit', submitData)
}

const handleCancel = () => {
    emit('cancel')
}
</script>
