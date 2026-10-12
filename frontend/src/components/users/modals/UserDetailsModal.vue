<template>
    <BaseModal v-model="showModal" title="User Information" size="lg">
        <template v-if="user">
            <div class="space-y-4 sm:space-y-6">
                <!-- Header Card -->
                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl sm:rounded-2xl p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-5">
                        <div
                            class="h-16 w-16 sm:h-20 sm:w-20 bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-sm flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xl sm:text-2xl mx-auto sm:mx-0">
                            {{ getUserInitials(user) }}
                        </div>
                        <div class="flex-1 text-center sm:text-left mt-4 sm:mt-0">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{
                                formatFullName(user) }}</h2>
                            <div class="flex flex-col sm:flex-row sm:items-center mt-2 sm:space-x-4 space-y-1 sm:space-y-0">
                                <span class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 justify-center sm:justify-start">
                                    <Mail class="w-4 h-4 mr-1.5 text-gray-400" />
                                    {{ user.email }}
                                </span>
                                <span v-if="user.phone"
                                    class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 justify-center sm:justify-start">
                                    <Phone class="w-4 h-4 mr-1.5 text-gray-400" />
                                    {{ user.phone }}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center justify-center sm:justify-start space-x-2">
                                <span
                                    :class="['px-3 py-1 text-xs font-medium rounded-full', getRoleColor(user.role)]">
                                    {{ formatRoleName(user.role) }}
                                </span>
                                <span :class="['px-3 py-1 text-xs font-medium rounded-full',
                                    user.isArchived ? 'bg-orange-100 dark:bg-orange-800 text-orange-700 dark:text-orange-300' :
                                        user.isActive ? 'bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300' :
                                            'bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-300']">
                                    {{ user.isArchived ? 'Archived' : (user.isActive ? 'Active' :
                                        'Inactive') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Role -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <ShieldCheck class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Role</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                    formatRoleName(user.role) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center" :class="user.isArchived ? 'bg-orange-100 dark:bg-orange-900/30' :
                                user.isActive ? 'bg-green-100 dark:bg-green-900/30' :
                                    'bg-red-100 dark:bg-red-900/30'">
                                <component
                                    :is="user.isArchived ? Archive : (user.isActive ? CheckCircle2 : XCircle)"
                                    class="w-5 h-5" :class="user.isArchived ? 'text-orange-600 dark:text-orange-400' :
                                        user.isActive ? 'text-green-600 dark:text-green-400' :
                                            'text-red-600 dark:text-red-400'" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Status</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ user.isArchived ? 'Archived' : (user.isActive ? 'Active' :
                                        'Inactive') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Created Date -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                <Calendar class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Created Date</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                    formatDate(user.createdAt) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- User ID (Front-facing UID) -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <Fingerprint class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    User ID</p>
                                <div class="flex items-center justify-between mt-1">
                                    <code
                                        class="text-sm font-semibold text-gray-900 dark:text-white">{{ generateUserUID(user.id) }}</code>
                                    <button @click="copyId(generateUserUID(user.id))"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                        <Copy class="w-3.5 h-3.5 mr-1.5" />
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Department/Facility Info & Clinic Code (side by side for clinic users) -->
                <div v-if="user.department || user.clinicCode" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Department/Facility Info -->
                    <div v-if="user.department" class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <div
                                class="h-10 w-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <Building class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    <span v-if="user.role === 'clinic'">Managing Clinic Facility</span>
                                    <span v-else-if="user.role === 'clinician'">Works at Clinic Facility</span>
                                    <span v-else-if="user.role === 'manufacturer'">Associated
                                        Manufacturer</span>
                                    <span v-else>Department</span>
                                </p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                    user.department }}</p>
                                <p v-if="user.role === 'clinic'"
                                    class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    This user is the administrator of this clinic facility.
                                </p>
                                <p v-else-if="user.role === 'clinician'"
                                    class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    This clinician provides medical services at this facility.
                                </p>
                                <p v-else-if="user.role === 'manufacturer'"
                                    class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    This user is associated with the specified manufacturer.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Clinic Code -->
                    <div v-if="user.clinicCode" class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                <Hash class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Associated Clinic Code</p>
                                <div class="flex items-center justify-between mt-1">
                                    <code
                                        class="text-sm font-semibold text-gray-900 dark:text-white">{{ user.clinicCode }}</code>
                                    <button @click="copyId(user.clinicCode)"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                        <Copy class="w-3.5 h-3.5 mr-1.5" />
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clinicians Section (for Clinic Admins) -->
                <div v-if="user.role === 'clinic'" class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4">
                    <!-- Accordion Header -->
                    <button @click="isCliniciansAccordionOpen = !isCliniciansAccordionOpen"
                        class="w-full flex items-center justify-between text-left focus:outline-none group">
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-10 w-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                                <Stethoscope class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Clinicians at this Facility</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                    associatedClinicians.length }} Medical Staff</p>
                            </div>
                        </div>
                        <ChevronDown
                            class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 transform"
                            :class="{ 'rotate-180': isCliniciansAccordionOpen }" />
                    </button>

                    <!-- Accordion Content -->
                    <transition enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                        <div v-if="isCliniciansAccordionOpen"
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <div v-if="associatedClinicians.length > 0">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                    Medical staff working at {{ user.department }}
                                </p>
                                <ul class="space-y-3">
                                    <li v-for="clinician in displayedClinicians" :key="clinician.id"
                                        class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-green-100 to-teal-100 dark:from-green-900/30 dark:to-teal-900/30 rounded-full flex items-center justify-center text-green-600 dark:text-green-400 font-medium text-xs">
                                                {{ getUserInitials(clinician) }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                    formatFullName(clinician) }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{
                                                    clinician.email }}</p>
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
                                        {{ showAllClinicians ? 'Show Less' : `Show ${associatedClinicians.length -
                                            initialClinicianCount} More` }}
                                    </button>
                                </div>
                            </div>
                            <div v-else
                                class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-dashed border-gray-300 dark:border-gray-600">
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                    No clinicians are currently assigned to this clinic facility.
                                </p>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Quick Actions -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
                        Quick Actions</p>
                    <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3">
                        <button v-if="canManageUser" @click="$emit('edit', user)"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors text-sm font-medium">
                            <FilePenLine class="w-5 h-5 mr-1 sm:mr-2" />
                            <span class="hidden sm:inline">Edit User</span>
                            <span class="sm:hidden">Edit</span>
                        </button>
                        <button v-if="canManageUser && !user.isArchived"
                            @click="$emit('sendResetLink', user)"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors text-sm font-medium">
                            <KeyRound class="w-5 h-5 mr-1 sm:mr-2" />
                            <span class="hidden sm:inline">Reset Password</span>
                            <span class="sm:hidden">Reset</span>
                        </button>
                        <button v-if="canManageUser && !user.isArchived"
                            @click="$emit('toggleStatus', user.id)"
                            :class="['inline-flex items-center justify-center px-3 sm:px-4 py-2 rounded-lg transition-colors text-sm font-medium',
                                user.isActive
                                    ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/30'
                                    : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/30']">
                            <component :is="user.isActive ? XCircle : CheckCircle2" class="w-5 h-5 mr-1 sm:mr-2" />
                            {{ user.isActive ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button v-if="canManageUser && !user.isArchived"
                            @click="$emit('archive', user.id)"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors text-sm font-medium">
                            <Archive class="w-5 h-5 mr-1 sm:mr-2" />
                            <span class="hidden sm:inline">Archive User</span>
                            <span class="sm:hidden">Archive</span>
                        </button>
                        <button v-else-if="!isOfficeStaff"
                            @click="$emit('delete', user.id)"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors text-sm font-medium">
                            <Trash2 class="w-5 h-5 mr-1 sm:mr-2" />
                            <span class="hidden sm:inline">Delete User</span>
                            <span class="sm:hidden">Delete</span>
                        </button>
                    </div>
                </div>

                <!-- Copy Tooltip -->
                <transition enter-active-class="transition ease-out duration-200"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-100"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95">
                    <div v-if="copiedTooltip"
                        class="fixed bottom-4 right-4 px-4 py-2 text-sm text-white bg-gray-800 dark:bg-gray-900 rounded-lg shadow-lg">
                        Copied to clipboard!
                    </div>
                </transition>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
    Mail,
    Phone,
    ShieldCheck,
    CheckCircle2,
    XCircle,
    Calendar,
    Fingerprint,
    Copy,
    Building,
    Hash,
    Stethoscope,
    ChevronDown,
    FilePenLine,
    Archive,
    Trash2,
    KeyRound
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

const props = defineProps<{
    modelValue: boolean
    user: User | null
    associatedClinicians: User[]
    isOfficeStaff: boolean
}>()

const canManageUser = computed(() => {
    if (!props.user) return false
    if (props.isOfficeStaff) {
        return props.user.role !== 'admin' && props.user.role !== 'office-staff'
    }
    return true
})

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'edit', user: User): void
    (e: 'toggleStatus', userId: string): void
    (e: 'archive', userId: string): void
    (e: 'delete', userId: string): void
    (e: 'sendResetLink', user: User): void
}>()

const isCliniciansAccordionOpen = ref(false)
const showAllClinicians = ref(false)
const initialClinicianCount = 3
const copiedTooltip = ref(false)

const showModal = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const displayedClinicians = computed(() => {
    if (showAllClinicians.value) {
        return props.associatedClinicians
    }
    return props.associatedClinicians.slice(0, initialClinicianCount)
})

const getUserInitials = (user: { firstName: string, lastName: string }) => {
    const firstInitial = user.firstName ? user.firstName.charAt(0) : ''
    const lastInitial = user.lastName ? user.lastName.charAt(0) : ''
    return `${firstInitial}${lastInitial}`.toUpperCase()
}

const formatFullName = (user: { firstName: string, middleName?: string, lastName: string }) => {
    return [user.firstName, user.middleName, user.lastName].filter(Boolean).join(' ')
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

const generateUserUID = (dbId: string): string => {
    if (!dbId) return 'UID-000000'
    
    let hash = 0
    for (let i = 0; i < dbId.length; i++) {
        const char = dbId.charCodeAt(i)
        hash = ((hash << 5) - hash) + char
        hash = hash & hash
    }
    
    const positiveHash = Math.abs(hash)
    const randomNumber = String(positiveHash % 1000000).padStart(6, '0')
    
    return `UID-${randomNumber}`
}

const copyId = (id: string | undefined) => {
    if (!id) return
    const textArea = document.createElement('textarea')
    textArea.value = id
    textArea.style.position = 'fixed'
    textArea.style.top = '0'
    textArea.style.left = '0'
    document.body.appendChild(textArea)
    textArea.select()
    try {
        document.execCommand('copy')
        copiedTooltip.value = true
        setTimeout(() => { copiedTooltip.value = false }, 2000)
    } catch (err) {
        console.error('Failed to copy ID: ', err)
    }
    document.body.removeChild(textArea)
}
</script>
